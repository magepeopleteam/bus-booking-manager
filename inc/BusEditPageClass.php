<?php
if (!defined('ABSPATH')) exit;

/**
 * Bus Edit Page Class
 * 
 * Handles the custom multi-step edit page for buses.
 */
class BusEditPageClass
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_bus_edit_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_init', array($this, 'handle_bus_save'));
        add_filter('post_row_actions', array($this, 'add_custom_edit_link'), 10, 2);

        // AJAX Save
        add_action('wp_ajax_wbbm_save_bus_ajax', array($this, 'handle_bus_save_ajax'));
        add_action('wp_ajax_wbbm_reload_bus_pricing_ajax', array($this, 'handle_reload_pricing_ajax'));

        // Redirection for default Add New page
        add_action('current_screen', array($this, 'redirect_to_custom_edit'));

        // Full-screen body class
        add_filter('admin_body_class', array($this, 'add_full_screen_body_class'));

        // Inline Stop AJAX
        add_action('wp_ajax_wbbm_add_inline_stop', array($this, 'handle_add_inline_stop_ajax'));
        add_action('wp_ajax_wbbm_add_inline_pickpoint', array($this, 'handle_add_inline_pickpoint_ajax'));
        add_action('wp_ajax_wbbm_add_inline_feature', array($this, 'handle_add_inline_feature_ajax'));
    }

    /**
     * Add custom body class for full-screen mode
     */
    public function add_full_screen_body_class($classes)
    {
        $screen = get_current_screen();
        if ($screen && strpos($screen->id, 'wbbm-bus-edit') !== false) {
            $classes .= ' wbbm-full-screen-mode ';
        }
        return $classes;
    }

    /**
     * Redirect default Add New bus page to custom edit page
     */
    public function redirect_to_custom_edit()
    {
        $screen = get_current_screen();
        if ($screen && $screen->id === 'wbbm_bus' && $screen->action === 'add') {
            wp_redirect(admin_url('admin.php?page=wbbm-bus-edit'));
            exit;
        }
    }

    /**
     * Register the custom edit page as a hidden submenu
     */
    public function register_bus_edit_page()
    {
        add_submenu_page(
            null,
            __('Add New', 'bus-booking-manager'),
            __('Add New', 'bus-booking-manager'),
            'manage_options',
            'wbbm-bus-edit',
            array($this, 'render_bus_edit_page')
        );
    }

    /**
     * Add custom edit link to row actions
     */
    public function add_custom_edit_link($actions, $post)
    {
        if ($post->post_type !== 'wbbm_bus') {
            return $actions;
        }

        $custom_edit_url = add_query_arg(
            array(
                'page'      => 'wbbm-bus-edit',
                'post_id'   => $post->ID
            ),
            admin_url('admin.php')
        );

        $actions['custom_edit'] = '<a href="' . esc_url($custom_edit_url) . '" style="color:#f97316;font-weight:bold;">' . __('Advanced Edit', 'bus-booking-manager') . '</a>';

        return $actions;
    }

    /**
     * Handle Bus Save via AJAX
     */
    public function handle_bus_save_ajax()
    {
        $this->handle_bus_save(true);
    }

    /**
     * Handle Bus Save
     */
    public function handle_bus_save($is_ajax = false)
    {
        if (defined('DOING_AJAX') && DOING_AJAX && !$is_ajax) {
            return;
        }

        if (!isset($_POST['wbbm_bus_nonce']) || !wp_verify_nonce($_POST['wbbm_bus_nonce'], 'wbbm_bus_save')) {
            if ($is_ajax) wp_send_json_error('Nonce verification failed');
            return;
        }

        if (!current_user_can('manage_options')) {
            if ($is_ajax) wp_send_json_error('Unauthorized');
            return;
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $title = isset($_POST['bus_title']) ? sanitize_text_field(wp_unslash($_POST['bus_title'])) : '';
        $content = isset($_POST['bus_content']) ? wp_kses_post(wp_unslash($_POST['bus_content'])) : '';
        $post_status = isset($_POST['post_status']) ? sanitize_text_field($_POST['post_status']) : 'publish';

        $post_data = array(
            'post_title'   => $title,
            'post_content' => $content,
            'post_status'  => $post_status,
            'post_type'    => 'wbbm_bus',
        );

        if ($post_id) {
            $post_data['ID'] = $post_id;
            // Remove meta box save actions to avoid recursion
            remove_action('save_post_wbbm_bus', array('AdminMetaBoxClass', 'wbbm_single_settings_meta_save'));
            wp_update_post($post_data);
        } else {
            $post_id = wp_insert_post($post_data);
        }

        if ($post_id) {
            // Save Basic Info (mapped from AdminMetaBoxClass)
            if (isset($_POST['bus_no'])) {
                update_post_meta($post_id, 'wbbm_bus_no', sanitize_text_field($_POST['bus_no']));
            }
            if (isset($_POST['total_seat'])) {
                update_post_meta($post_id, 'wbbm_total_seat', sanitize_text_field($_POST['total_seat']));
            }
            if (isset($_POST['bus_category'])) {
                wp_set_post_terms($post_id, array(intval($_POST['bus_category'])), 'wbbm_bus_cat', false);
                update_post_meta($post_id, 'wbbm_bus_category', sanitize_text_field($_POST['bus_category']));
            }
            if (isset($_POST['price_zero_allow'])) {
                update_post_meta($post_id, 'wbbm_price_zero_allow', sanitize_text_field($_POST['price_zero_allow']));
            } else {
                update_post_meta($post_id, 'wbbm_price_zero_allow', 'off');
            }
            if (isset($_POST['sell_off'])) {
                update_post_meta($post_id, 'wbbm_sell_off', sanitize_text_field($_POST['sell_off']));
            } else {
                update_post_meta($post_id, 'wbbm_sell_off', 'off');
            }
            if (isset($_POST['seat_available'])) {
                update_post_meta($post_id, 'wbbm_seat_available', sanitize_text_field($_POST['seat_available']));
            } else {
                update_post_meta($post_id, 'wbbm_seat_available', 'off');
            }

            // Save Route & Price (Step 2)
            if (isset($_POST['wbtm_route_place'])) {
                $route_info = [];
                $route_places = array_map('sanitize_text_field', wp_unslash($_POST['wbtm_route_place']));
                $route_times = isset($_POST['wbtm_route_time']) ? array_map('sanitize_text_field', wp_unslash($_POST['wbtm_route_time'])) : [];
                $route_types = isset($_POST['wbtm_route_type']) ? array_map('sanitize_text_field', wp_unslash($_POST['wbtm_route_type'])) : [];
                $route_next_day = isset($_POST['wbtm_route_next_day']) ? $_POST['wbtm_route_next_day'] : [];

                // Inline Pickup Points
                $pickup_names = isset($_POST['wbbm_inline_pickpoint_name']) ? $_POST['wbbm_inline_pickpoint_name'] : [];
                $pickup_times = isset($_POST['wbbm_inline_pickpoint_time']) ? $_POST['wbbm_inline_pickpoint_time'] : [];

                $selected_cities = [];

                foreach ($route_places as $i => $place) {
                    if ($place !== '') {
                        $route_info[$i] = [
                            'place' => $place,
                            'time' => isset($route_times[$i]) ? $route_times[$i] : '',
                            'type' => isset($route_types[$i]) ? $route_types[$i] : '',
                            'next_day' => isset($route_next_day[$i]) ? intval($route_next_day[$i]) : 0,
                        ];

                        $selected_cities[] = $place;

                        // Save Inline Pickup Points to legacy keys
                        if (isset($pickup_names[$i])) {
                            $city_slug = sanitize_key(str_replace(' ', '_', strtolower($place)));
                            $pickup_data = [];
                            foreach ($pickup_names[$i] as $k => $name) {
                                if ($name) {
                                    $pickup_data[] = [
                                        'pickpoint' => sanitize_text_field($name),
                                        'time' => isset($pickup_times[$i][$k]) ? sanitize_text_field($pickup_times[$i][$k]) : ''
                                    ];
                                }
                            }
                            update_post_meta($post_id, 'wbbm_selected_pickpoint_name_' . $city_slug, $pickup_data);
                        }
                    }
                }
                update_post_meta($post_id, 'wbbm_route_info', $route_info);
                update_post_meta($post_id, 'wbbm_pickpoint_selected_city', implode(',', array_unique($selected_cities)));

                // Pricing
                $new_prices = [];
                $bp_price_stops = isset($_POST['wbtm_price_bp']) ? array_map('sanitize_text_field', wp_unslash($_POST['wbtm_price_bp'])) : [];
                $dp_price_stops = isset($_POST['wbtm_price_dp']) ? array_map('sanitize_text_field', wp_unslash($_POST['wbtm_price_dp'])) : [];
                $adult_prices = isset($_POST['wbtm_adult_price']) ? array_map('sanitize_text_field', wp_unslash($_POST['wbtm_adult_price'])) : [];
                $child_prices = isset($_POST['wbtm_child_price']) ? array_map('sanitize_text_field', wp_unslash($_POST['wbtm_child_price'])) : [];
                $infant_prices = isset($_POST['wbtm_infant_price']) ? array_map('sanitize_text_field', wp_unslash($_POST['wbtm_infant_price'])) : [];
                $student_prices = isset($_POST['wbtm_student_price']) ? array_map('sanitize_text_field', wp_unslash($_POST['wbtm_student_price'])) : [];

                foreach ($bp_price_stops as $i => $bp_stop) {
                    if ($bp_stop && isset($dp_price_stops[$i])) {
                        $new_prices[$i] = [
                            'wbbm_bus_bp_price_stop' => $bp_stop,
                            'wbbm_bus_dp_price_stop' => $dp_price_stops[$i],
                            'wbbm_bus_price' => isset($adult_prices[$i]) ? $adult_prices[$i] : '',
                            'wbbm_bus_price_child' => isset($child_prices[$i]) ? $child_prices[$i] : '',
                            'wbbm_bus_price_infant' => isset($infant_prices[$i]) ? $infant_prices[$i] : '',
                            'wbbm_bus_price_student' => isset($student_prices[$i]) ? $student_prices[$i] : '',
                        ];
                    }
                }
                update_post_meta($post_id, 'wbbm_bus_prices', $new_prices);
            }

            // Save Pickup Point (Legacy - empty but for safety)
            // if (isset($_POST['wbbm_pickpoint_selected_city'])) { ... } // Replaced above

            // Save Day Schedule (Step 4)
            if (isset($_POST['weekly_offday'])) {
                $weekly_offday = array_map('sanitize_text_field', wp_unslash($_POST['weekly_offday']));
                update_post_meta($post_id, 'weekly_offday', $weekly_offday);
            } else {
                update_post_meta($post_id, 'weekly_offday', []);
            }

            if (isset($_POST['wbtm_od_start'])) {
                update_post_meta($post_id, 'wbtm_od_start', sanitize_text_field($_POST['wbtm_od_start']));
            }
            if (isset($_POST['wbtm_od_end'])) {
                update_post_meta($post_id, 'wbtm_od_end', sanitize_text_field($_POST['wbtm_od_end']));
            }

            if (isset($_POST['wbtm_od_offdate_from'])) {
                $offday_schedule = [];
                $from_dates = array_map('sanitize_text_field', wp_unslash($_POST['wbtm_od_offdate_from']));
                $to_dates = isset($_POST['wbtm_od_offdate_to']) ? array_map('sanitize_text_field', wp_unslash($_POST['wbtm_od_offdate_to'])) : [];
                $from_times = isset($_POST['wbtm_od_offtime_from']) ? array_map('sanitize_text_field', wp_unslash($_POST['wbtm_od_offtime_from'])) : [];
                $to_times = isset($_POST['wbtm_od_offtime_to']) ? array_map('sanitize_text_field', wp_unslash($_POST['wbtm_od_offtime_to'])) : [];

                foreach ($from_dates as $i => $date) {
                    if ($date) {
                        $offday_schedule[] = [
                            'from_date' => $date,
                            'to_date' => isset($to_dates[$i]) ? $to_dates[$i] : '',
                            'from_time' => isset($from_times[$i]) ? $from_times[$i] : '',
                            'to_time' => isset($to_times[$i]) ? $to_times[$i] : ''
                        ];
                    }
                }
                update_post_meta($post_id, 'wbtm_offday_schedule', $offday_schedule);
            }

            // Save Features & Extra Services (Step 4)
            if (isset($_POST['wbbm_features'])) {
                $features = array_map('intval', wp_unslash($_POST['wbbm_features']));
                wp_set_object_terms($post_id, $features, 'wbbm_bus_feature');
                update_post_meta($post_id, 'wbbm_features', $features);
            } else {
                wp_set_object_terms($post_id, [], 'wbbm_bus_feature');
                update_post_meta($post_id, 'wbbm_features', []);
            }

            if (isset($_POST['option_name'])) {
                $extra_services = [];
                $names = array_map('sanitize_text_field', wp_unslash($_POST['option_name']));
                $prices = isset($_POST['option_price']) ? array_map('sanitize_text_field', wp_unslash($_POST['option_price'])) : [];
                $qtys = isset($_POST['option_qty']) ? array_map('sanitize_text_field', wp_unslash($_POST['option_qty'])) : [];
                $types = isset($_POST['option_qty_type']) ? array_map('sanitize_text_field', wp_unslash($_POST['option_qty_type'])) : [];

                foreach ($names as $i => $name) {
                    if ($name) {
                        $extra_services[] = [
                            'option_name' => $name,
                            'option_price' => isset($prices[$i]) ? $prices[$i] : '',
                            'option_qty' => isset($qtys[$i]) ? $qtys[$i] : '',
                            'option_qty_type' => isset($types[$i]) ? $types[$i] : 'fixed'
                        ];
                    }
                }
                update_post_meta($post_id, 'mep_events_extra_prices', $extra_services);
            }

            // Save Tax (Step 6)
            if (isset($_POST['wbtm_bus_tax_status'])) {
                update_post_meta($post_id, 'wbtm_bus_tax_status', sanitize_text_field($_POST['wbtm_bus_tax_status']));
            }
            if (isset($_POST['wbtm_bus_tax_class'])) {
                update_post_meta($post_id, 'wbtm_bus_tax_class', sanitize_text_field($_POST['wbtm_bus_tax_class']));
            }

            // Thumbnail
            if (isset($_POST['bus_thumbnail_id'])) {
                set_post_thumbnail($post_id, intval($_POST['bus_thumbnail_id']));
            }

            // Allow pro plugins to save their fields (e.g. Step 6 Passenger Registration)
            do_action('wbbm_custom_edit_page_save', $post_id);

            // Other default meta
            update_post_meta($post_id, '_virtual', 'yes');
            update_post_meta($post_id, '_sold_individually', 'yes');
            update_post_meta($post_id, '_manage_stock', 'no');
            update_post_meta($post_id, '_price', 0);
        }

        if ($is_ajax) {
            $post = get_post($post_id);
            $status_data = $this->get_status_metadata($post->post_status);
            wp_send_json_success(array(
                'post_id'        => $post_id,
                'status_label'   => $status_data['label'],
                'status_class'   => $status_data['class'],
                'current_status' => $post->post_status
            ));
        }

        // Standard redirect if not AJAX
        $redirect_args = array('page' => 'wbbm-bus-edit', 'post_id' => $post_id, 'saved' => '1');
        wp_redirect(add_query_arg($redirect_args, admin_url('admin.php')));
        exit;
    }

    /**
     * Handle Add Inline Stop via AJAX
     */
    public function handle_add_inline_stop_ajax()
    {
        check_ajax_referer('wbbm_bus_save', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized', 'bus-booking-manager'));
        }

        $term_name = isset($_POST['term_name']) ? sanitize_text_field(wp_unslash($_POST['term_name'])) : '';

        if (empty($term_name)) {
            wp_send_json_error(__('Stop name is required', 'bus-booking-manager'));
        }

        $term = wp_insert_term($term_name, 'wbbm_bus_stops');

        if (is_wp_error($term)) {
            if ($term->get_error_code() === 'term_exists') {
                $existing_term = get_term_by('name', $term_name, 'wbbm_bus_stops');
                wp_send_json_success(array(
                    'term_id' => $existing_term->term_id,
                    'name'    => $existing_term->name,
                    'message' => __('Stop already exists. Added to list.', 'bus-booking-manager')
                ));
            }
            wp_send_json_error($term->get_error_message());
        }

        wp_send_json_success(array(
            'term_id' => $term['term_id'],
            'name'    => $term_name
        ));
    }

    /**
     * Handle Add Inline Pickup Point via AJAX
     */
    public function handle_add_inline_pickpoint_ajax()
    {
        check_ajax_referer('wbbm_bus_save', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized', 'bus-booking-manager'));
        }

        $term_name = isset($_POST['term_name']) ? sanitize_text_field(wp_unslash($_POST['term_name'])) : '';

        if (empty($term_name)) {
            wp_send_json_error(__('Pickup point name is required', 'bus-booking-manager'));
        }

        $term = wp_insert_term($term_name, 'wbbm_bus_pickpoint');

        if (is_wp_error($term)) {
            if ($term->get_error_code() === 'term_exists') {
                $existing_term = get_term_by('name', $term_name, 'wbbm_bus_pickpoint');
                wp_send_json_success(array(
                    'term_id' => $existing_term->term_id,
                    'name'    => $existing_term->name,
                    'message' => __('Pickup point already exists. Added to list.', 'bus-booking-manager')
                ));
            }
            wp_send_json_error($term->get_error_message());
        }

        wp_send_json_success(array(
            'term_id' => $term['term_id'],
            'name'    => $term_name
        ));
    }

    /**
     * Handle Add Inline Feature via AJAX
     */
    public function handle_add_inline_feature_ajax()
    {
        check_ajax_referer('wbbm_bus_save', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized', 'bus-booking-manager'));
        }

        $term_name = isset($_POST['term_name']) ? sanitize_text_field(wp_unslash($_POST['term_name'])) : '';

        if (empty($term_name)) {
            wp_send_json_error(__('Feature name is required', 'bus-booking-manager'));
        }

        $term = wp_insert_term($term_name, 'wbbm_bus_feature');

        if (is_wp_error($term)) {
            if ($term->get_error_code() === 'term_exists') {
                $existing_term = get_term_by('name', $term_name, 'wbbm_bus_feature');
                wp_send_json_success(array(
                    'term_id' => $existing_term->term_id,
                    'name'    => $existing_term->name,
                    'message' => __('Feature already exists. Added to list.', 'bus-booking-manager')
                ));
            }
            wp_send_json_error($term->get_error_message());
        }

        wp_send_json_success(array(
            'term_id' => $term['term_id'],
            'name'    => $term_name
        ));
    }

    /**
     * Handle Reload Pricing Matrix via AJAX
     */
    public function handle_reload_pricing_ajax()
    {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wbbm_bus_save')) {
            wp_send_json_error('Nonce verification failed');
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $places = isset($_POST['places']) ? array_map('sanitize_text_field', wp_unslash($_POST['places'])) : [];
        $types = isset($_POST['types']) ? array_map('sanitize_text_field', wp_unslash($_POST['types'])) : [];

        $route_infos = [];
        foreach ($places as $key => $place) {
            if ($place) {
                $route_infos[$key] = [
                    'place' => $place,
                    'type'  => isset($types[$key]) ? $types[$key] : 'both'
                ];
            }
        }

        ob_start();
        $this->render_pricing_matrix($post_id, $route_infos);
        $html = ob_get_clean();

        wp_send_json_success($html);
    }

    /**
     * Enqueue CSS and JS
     */
    public function enqueue_assets($hook)
    {
        if (strpos($hook, 'wbbm-bus-edit') === false) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_style('wbbm-toaster-css', WBTM_PLUGIN_URL . 'assets/admin/wbbm-toaster.css', array(), time());
        wp_enqueue_style('bus-edit-css', WBTM_PLUGIN_URL . 'assets/admin/bus-edit.css', array(), time());
        wp_enqueue_script('bus-toaster-js', WBTM_PLUGIN_URL . 'assets/admin/bus-toaster.js', array('jquery'), time(), true);
        wp_enqueue_script('bus-edit-js', WBTM_PLUGIN_URL . 'assets/admin/bus-edit.js', array('jquery', 'bus-toaster-js'), time(), true);

        wp_localize_script('bus-edit-js', 'wbbm_bus_edit', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('wbbm_bus_save')
        ));

        wp_enqueue_editor();
    }

    /**
     * Render the custom edit page
     */
    public function render_bus_edit_page()
    {
        $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
        $current_step = isset($_GET['step']) ? intval($_GET['step']) : 1;
        $post = $post_id ? get_post($post_id) : null;

        $current_status = $post ? $post->post_status : 'new';
        $status_data = $this->get_status_metadata($current_status);
        $status_label = $status_data['label'];
        $status_class = $status_data['class'];

        $title = $post ? $post->post_title : '';
        $content = $post ? $post->post_content : '';

        // Basic Info Meta
        $bus_no = $post_id ? get_post_meta($post_id, 'wbbm_bus_no', true) : '';
        $total_seat = $post_id ? get_post_meta($post_id, 'wbbm_total_seat', true) : '';
        $bus_cat_id = $post_id ? get_post_meta($post_id, 'wbbm_bus_category', true) : '';
        $price_zero = $post_id ? get_post_meta($post_id, 'wbbm_price_zero_allow', true) : 'off';
        $sell_off = $post_id ? get_post_meta($post_id, 'wbbm_sell_off', true) : 'off';
        $seat_avail = $post_id ? get_post_meta($post_id, 'wbbm_seat_available', true) : 'on';

        $thumb_id = get_post_thumbnail_id($post_id);
        $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'medium') : '';

        $all_cats = get_terms(array('taxonomy' => 'wbbm_bus_cat', 'hide_empty' => false));
?>
        <div class="wrap bus-edit-wrap">
            <div class="bus-container">
                <div class="bus-edit-header">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <a href="<?php echo admin_url('edit.php?post_type=wbbm_bus&page=wbbm-bus-list'); ?>" class="back-btn">
                            <span class="dashicons dashicons-arrow-left-alt"></span>
                            <?php _e('Back to List', 'bus-booking-manager'); ?>
                        </a>
                        <h2><?php echo $post_id ? __('Edit Bus', 'bus-booking-manager') . ': ' . esc_html($title) : __('Add New Bus', 'bus-booking-manager'); ?></h2>
                        <span class="bus-status-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span>
                    </div>
                    <div class="header-actions">
                        <button type="button" id="save-bus-draft" class="btn btn-secondary"><?php _e('Save as Draft', 'bus-booking-manager'); ?></button>
                        <button type="button" id="save-bus-publish" class="btn btn-primary"><?php echo ($current_status === 'publish') ? __('Save', 'bus-booking-manager') : __('Publish', 'bus-booking-manager'); ?></button>
                    </div>
                </div>

                <!-- Steps Navigation -->
                <div class="bus-steps-nav">
                    <div class="bus-steps-list">
                        <?php
                        $steps = array(
                            1 => __('Basic', 'bus-booking-manager'),
                            2 => __('Route & Price', 'bus-booking-manager'),
                            3 => __('Day Schedule', 'bus-booking-manager'),
                            4 => __('Features', 'bus-booking-manager'),
                            5 => __('Tax', 'bus-booking-manager'),
                            6 => __('Custom Fields', 'bus-booking-manager')
                        );
                        foreach ($steps as $step_id => $label): ?>
                            <div class="step-item <?php echo $current_step === $step_id ? 'active' : ($current_step > $step_id ? 'completed' : ''); ?>" data-step="<?php echo $step_id; ?>">
                                <div class="step-number"><?php echo $current_step > $step_id ? '✓' : $step_id; ?></div>
                                <div class="step-label">
                                    <?php echo $label; ?>
                                    <?php if ($step_id === 6): ?>
                                        <span class="pro-badge-nav">PRO</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <form id="bus-edit-form" method="post" action="">
                    <?php wp_nonce_field('wbbm_bus_save', 'wbbm_bus_nonce'); ?>
                    <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
                    <input type="hidden" name="post_status" id="post_status" value="<?php echo esc_attr($current_status); ?>">
                    <input type="hidden" name="current_step" value="<?php echo esc_attr($current_step); ?>">

                    <!-- Step 1: Basic Info -->
                    <div class="bus-step-content <?php echo $current_step === 1 ? 'active' : ''; ?>" id="step-1-content">
                        <div class="bus-edit-content">
                            <div class="bus-edit-left">
                                <div class="bus-card">
                                    <div class="form-group">
                                        <label for="bus_title"><?php _e('Bus Name', 'bus-booking-manager'); ?> <span class="required">*</span></label>
                                        <input type="text" name="bus_title" id="bus_title" class="form-control" value="<?php echo esc_attr($title); ?>" placeholder="<?php _e('Enter bus name', 'bus-booking-manager'); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label><?php _e('Description', 'bus-booking-manager'); ?></label>
                                        <?php wp_editor($content, 'bus_content', array('textarea_name' => 'bus_content', 'media_buttons' => true, 'textarea_rows' => 10)); ?>
                                    </div>
                                </div>

                                <div class="bus-card">
                                    <h3><?php _e('Main Configuration', 'bus-booking-manager'); ?></h3>
                                    <div class="bus-grid">
                                        <div class="form-group">
                                            <label><?php _e('Type', 'bus-booking-manager'); ?> <span class="required">*</span></label>
                                            <select name="bus_category" class="form-control" required>
                                                <option value=""><?php _e('Select Type', 'bus-booking-manager'); ?></option>
                                                <?php foreach ($all_cats as $cat) : ?>
                                                    <option value="<?php echo esc_attr($cat->term_id); ?>" <?php selected($cat->term_id, $bus_cat_id); ?>><?php echo esc_html($cat->name); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label><?php _e('Coach No', 'bus-booking-manager'); ?></label>
                                            <input type="text" name="bus_no" class="form-control" value="<?php echo esc_attr($bus_no); ?>" placeholder="e.g. BD-001">
                                        </div>
                                        <div class="form-group">
                                            <label><?php _e('Total Seat', 'bus-booking-manager'); ?> <span class="required">*</span></label>
                                            <input type="number" name="total_seat" class="form-control" value="<?php echo esc_attr($total_seat); ?>" min="1" required>
                                        </div>
                                    </div>
                                    <div class="bus-switches">
                                        <div class="switch-group">
                                            <div class="switch-label">
                                                <strong><?php _e('Price Zero Allow', 'bus-booking-manager'); ?></strong>
                                                <p><?php _e('Show zero price option as ticket type', 'bus-booking-manager'); ?></p>
                                            </div>
                                            <label class="bus-switch">
                                                <input type="checkbox" name="price_zero_allow" value="on" <?php checked($price_zero, 'on'); ?>>
                                                <span class="slider round"></span>
                                            </label>
                                        </div>
                                        <div class="switch-group">
                                            <div class="switch-label">
                                                <strong><?php _e('Sell Off', 'bus-booking-manager'); ?></strong>
                                                <p><?php _e('Turn off ticket selling for this bus', 'bus-booking-manager'); ?></p>
                                            </div>
                                            <label class="bus-switch">
                                                <input type="checkbox" name="sell_off" value="on" <?php checked($sell_off, 'on'); ?>>
                                                <span class="slider round"></span>
                                            </label>
                                        </div>
                                        <div class="switch-group">
                                            <div class="switch-label">
                                                <strong><?php _e('Show Seat Available', 'bus-booking-manager'); ?></strong>
                                                <p><?php _e('Display ticket availability status', 'bus-booking-manager'); ?></p>
                                            </div>
                                            <label class="bus-switch">
                                                <input type="checkbox" name="seat_available" value="on" <?php checked($seat_avail, 'on'); ?>>
                                                <span class="slider round"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bus-edit-right">
                                <div class="bus-card">
                                    <h3><?php _e('Thumbnail', 'bus-booking-manager'); ?></h3>
                                    <div class="bus-thumbnail-box" id="set-bus-thumbnail">
                                        <?php if ($thumb_url) : ?>
                                            <div class="bus-thumbnail-preview">
                                                <img src="<?php echo esc_url($thumb_url); ?>" alt="">
                                                <p><?php _e('Click to change image', 'bus-booking-manager'); ?></p>
                                            </div>
                                        <?php else : ?>
                                            <div class="bus-thumbnail-placeholder">
                                                <span class="dashicons dashicons-images-alt2"></span>
                                                <p><?php _e('Click to set bus image', 'bus-booking-manager'); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <input type="hidden" name="bus_thumbnail_id" id="bus_thumbnail_id" value="<?php echo esc_attr($thumb_id); ?>">
                                </div>

                                <div class="bus-card">
                                    <h3><?php _e('Bus Stops', 'bus-booking-manager'); ?></h3>
                                    <div class="inline-taxonomy-list-wrap">
                                        <ul class="inline-taxonomy-list" id="bus-stops-list">
                                            <?php
                                            $all_stops = get_terms(array('taxonomy' => 'wbbm_bus_stops', 'hide_empty' => false));
                                            if (!is_wp_error($all_stops) && !empty($all_stops)) :
                                                foreach ($all_stops as $stop) : ?>
                                                    <li data-id="<?php echo esc_attr($stop->term_id); ?>">
                                                        <span class="dashicons dashicons-location"></span>
                                                        <span class="stop-name"><?php echo esc_html($stop->name); ?></span>
                                                    </li>
                                                <?php endforeach;
                                            else : ?>
                                                <li class="no-stops"><?php _e('No stops added yet.', 'bus-booking-manager'); ?></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                    <div class="inline-taxonomy-add">
                                        <div class="form-group" style="margin-bottom: 10px;">
                                            <input type="text" id="new-stop-name" class="form-control" placeholder="<?php _e('Enter new stop name', 'bus-booking-manager'); ?>">
                                        </div>
                                        <button type="button" id="add-inline-stop-btn" class="btn btn-secondary btn-block" style="width: 100%; justify-content: center;">
                                            <span class="dashicons dashicons-plus"></span> <?php _e('Add New Stop', 'bus-booking-manager'); ?>
                                        </button>
                                        <div id="inline-stop-message" style="margin-top: 10px; font-size: 12px; font-weight: 600;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- Step 2: Route & Price (Includes Pickup Points) -->
                    <div class="bus-step-content <?php echo $current_step === 2 ? 'active' : ''; ?>" id="step-2-content">
                        <?php $this->render_step_2($post_id); ?>
                    </div>

                    <!-- Step 3: Day Schedule -->
                    <div class="bus-step-content <?php echo $current_step === 3 ? 'active' : ''; ?>" id="step-3-content">
                        <?php $this->render_step_3($post_id); ?>
                    </div>

                    <!-- Step 4: Features -->
                    <div class="bus-step-content <?php echo $current_step === 4 ? 'active' : ''; ?>" id="step-4-content">
                        <?php $this->render_step_4($post_id); ?>
                    </div>

                    <!-- Step 5: Tax -->
                    <div class="bus-step-content <?php echo $current_step === 5 ? 'active' : ''; ?>" id="step-5-content">
                        <?php $this->render_step_5($post_id); ?>
                    </div>

                    <!-- Step 6: Passenger List -->
                    <div class="bus-step-content <?php echo $current_step === 6 ? 'active' : ''; ?>" id="step-6-content">
                        <?php $this->render_step_6($post_id); ?>

                    </div>

            </div>

            <div class="bus-edit-footer">
                <div class="saving-status"></div>
                <div class="footer-buttons">
                    <button type="button" class="btn btn-secondary prev-step" style="display: none;">&larr; <?php _e('Previous', 'bus-booking-manager'); ?></button>
                    <button type="button" class="btn btn-primary next-step"><?php _e('Save & Next', 'bus-booking-manager'); ?> &rarr;</button>
                    <button type="submit" name="save_bus" class="btn btn-primary final-save" style="display: none;"><?php _e('Final Save & Finish', 'bus-booking-manager'); ?> &checkmark;</button>
                </div>
            </div>
            </form>
        </div>
        </div>
    <?php
    }

    /**
     * Render Step 2: Route & Price
     */
    private function render_step_2($post_id)
    {
        $route_info = get_post_meta($post_id, 'wbbm_route_info', true) ?: [];
        $bus_stops = get_terms(array('taxonomy' => 'wbbm_bus_stops', 'hide_empty' => false));
        $pickpoints = get_terms(array('taxonomy' => 'wbbm_bus_pickpoint', 'hide_empty' => false));
    ?>
        <div class="bus-edit-content">
            <div class="bus-edit-left">
                <div class="bus-card" data-pickpoints-options='<?php echo esc_attr(json_encode($pickpoints)); ?>'>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="margin: 0; border: none; padding: 0;"><?php _e('Route Management', 'bus-booking-manager'); ?></h3>
                        <button type="button" class="btn btn-secondary btn-sm add-route-item">
                            <span class="dashicons dashicons-plus"></span> <?php _e('Add Stop', 'bus-booking-manager'); ?>
                        </button>
                    </div>

                    <div id="route-items-container" class="route-sortable">
                        <?php if (!empty($route_info)) : ?>
                            <?php foreach ($route_info as $index => $item) : ?>
                                <?php $this->render_route_item($index, $item, $bus_stops, $pickpoints); ?>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <?php $this->render_route_item(0, [], $bus_stops, $pickpoints); ?>
                        <?php endif; ?>
                    </div>

                    <!-- Template for new items -->
                    <script type="text/template" id="route-item-template">
                        <?php $this->render_route_item('{{index}}', [], $bus_stops, $pickpoints); ?>
                    </script>
                </div>

                <div class="bus-card">
                    <h3><?php _e('Pricing Matrix', 'bus-booking-manager'); ?></h3>
                    <div id="pricing-matrix-container">
                        <?php $this->render_pricing_matrix($post_id, $route_info); ?>
                    </div>
                </div>
            </div>

            <div class="bus-edit-right">
                <div class="bus-card">
                    <h3><?php _e('Pickup Points', 'bus-booking-manager'); ?></h3>
                    <div class="inline-taxonomy-list-wrap">
                        <ul class="inline-taxonomy-list" id="sidebar-pickpoints-list">
                            <?php
                            if (!is_wp_error($pickpoints) && !empty($pickpoints)) :
                                foreach ($pickpoints as $point) : ?>
                                    <li data-id="<?php echo esc_attr($point->term_id); ?>">
                                        <span class="dashicons dashicons-location-alt"></span>
                                        <span class="point-name"><?php echo esc_html($point->name); ?></span>
                                    </li>
                                <?php endforeach;
                            else : ?>
                                <li class="no-points"><?php _e('No pickup points yet.', 'bus-booking-manager'); ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="inline-taxonomy-add">
                        <div class="form-group" style="margin-bottom: 10px;">
                            <input type="text" id="new-pickpoint-name" class="form-control" placeholder="<?php _e('Enter pickup point', 'bus-booking-manager'); ?>">
                        </div>
                        <button type="button" id="add-inline-pickpoint-btn" class="btn btn-secondary btn-block" style="width: 100%; justify-content: center;">
                            <span class="dashicons dashicons-plus"></span> <?php _e('Add Pickup Point', 'bus-booking-manager'); ?>
                        </button>
                    </div>
                </div>

                <div class="bus-card">
                    <h3><?php _e('Quick Help', 'bus-booking-manager'); ?></h3>
                    <p style="font-size: 13px; color: var(--bus-text-light);">
                        <?php _e('Add stops in chronological order. Mark each stop as Boarding, Dropping, or Both. The pricing matrix will automatically update based on your selected stops.', 'bus-booking-manager'); ?>
                    </p>
                </div>
            </div>
        </div>
    <?php
    }

    /**
     * Render Step 3: Pickup Point
     */
    /**
     * Rename the existing render_step functions to match new order
     */

    /**
     * Render Step 4: Features
     */
    private function render_step_4($post_id)
    {
        $selected_features = wp_get_object_terms($post_id, 'wbbm_bus_feature', array('fields' => 'ids')) ?: [];
        $extra_services = get_post_meta($post_id, 'mep_events_extra_prices', true) ?: [];

        $available_features = get_terms(array(
            'taxonomy'   => 'wbbm_bus_feature',
            'hide_empty' => false,
        ));
    ?>
        <div class="bus-edit-content">
            <div class="bus-edit-left">
                <div class="bus-card">
                    <h3><?php _e('Bus Features', 'bus-booking-manager'); ?></h3>
                    <div class="features-grid">
                        <?php if (!is_wp_error($available_features) && !empty($available_features)) : ?>
                            <?php foreach ($available_features as $term) :
                                $icon = get_term_meta($term->term_id, 'feature_icon', true) ?: 'fas fa-star';
                                $is_active = in_array($term->term_id, $selected_features);
                            ?>
                                <label class="feature-item <?php echo $is_active ? 'active' : ''; ?>">
                                    <input type="checkbox" name="wbbm_features[]" value="<?php echo $term->term_id; ?>" <?php checked($is_active); ?>>
                                    <div class="feature-content">
                                        <span class="<?php echo esc_attr($icon); ?>"></span>
                                        <span><?php echo esc_html($term->name); ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p><?php _e('No features found. Please add them in Bus > Bus Feature.', 'bus-booking-manager'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- <div class="bus-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="margin: 0; border: none; padding: 0;"><?php //_e('Extra Services', 'bus-booking-manager'); 
                                                                            ?></h3>
                        <button type="button" class="btn btn-secondary btn-sm add-extra-service">
                            <span class="dashicons dashicons-plus"></span> <?php //_e('Add Extra Service', 'bus-booking-manager'); 
                                                                            ?>
                        </button>
                    </div>

                    <div id="extra-services-container">
                        <?php //if (!empty($extra_services)) : 
                        ?>
                            <?php //foreach ($extra_services as $service) : 
                            ?>
                                <?php //$this->render_extra_service_item($service); 
                                ?>
                            <?php //endforeach; 
                            ?>
                        <?php //else : 
                        ?>
                            <?php //$this->render_extra_service_item(); 
                            ?>
                        <?php //endif; 
                        ?>
                    </div>
                    

                    <!-- Template for new items -->
                <!-- <script type="text/template" id="extra-service-template">
                        <?php //$this->render_extra_service_item(); 
                        ?>
                    </script> -->
                <!-- </div> -->

            </div>

            <div class="bus-edit-right">
                <div class="bus-card">
                    <h3><?php _e('Bus Features', 'bus-booking-manager'); ?></h3>
                    <div class="inline-taxonomy-list-wrap">
                        <ul class="inline-taxonomy-list" id="sidebar-features-list">
                            <?php
                            if (!is_wp_error($available_features) && !empty($available_features)) :
                                foreach ($available_features as $term) : ?>
                                    <li data-id="<?php echo esc_attr($term->term_id); ?>">
                                        <span class="dashicons dashicons-star-filled"></span>
                                        <span class="feature-name"><?php echo esc_html($term->name); ?></span>
                                    </li>
                                <?php endforeach;
                            else : ?>
                                <li class="no-features"><?php _e('No features yet.', 'bus-booking-manager'); ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="inline-taxonomy-add">
                        <div class="form-group" style="margin-bottom: 10px;">
                            <input type="text" id="new-feature-name" class="form-control" placeholder="<?php _e('Enter feature name', 'bus-booking-manager'); ?>">
                        </div>
                        <button type="button" id="add-inline-feature-btn" class="btn btn-secondary btn-block" style="width: 100%; justify-content: center;">
                            <span class="dashicons dashicons-plus"></span> <?php _e('Add Feature', 'bus-booking-manager'); ?>
                        </button>
                    </div>
                </div>

                <div class="bus-card">
                    <h3><?php _e('Aesthetics', 'bus-booking-manager'); ?></h3>
                    <p style="font-size: 13px; color: var(--bus-text-light);">
                        <?php _e('Features are displayed as icons on the bus details page. Extra services can be selected by passengers during booking.', 'bus-booking-manager'); ?>
                    </p>
                </div>
            </div>
        </div>
    <?php
    }

    /**
     * Render Step 6: Custom Fields
     */
    private function render_step_6($post_id)
    {
    ?>
        <div class="bus-edit-content">
            <div class="bus-edit-left" style="width: 100%;">
                <div class="bus-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="margin: 0; border: none; padding: 0;"><?php _e('Passenger Registration', 'bus-booking-manager'); ?></h3>
                    </div>

                    <?php 
                    if (has_action('wbbm_after_meta_box_tab_content')) {
                        do_action('wbbm_after_meta_box_tab_content', $post_id);
                    } else {
                        ?>
                        <div class="pro-placeholder-content">
                            <div class="pro-placeholder-inner">
                                <div class="pro-icon-wrap">
                                    <span class="dashicons dashicons-lock"></span>
                                    <span class="pro-tag"><?php _e('PRO', 'bus-booking-manager'); ?></span>
                                </div>
                                <h2><?php _e('Passenger Registration & Custom Fields', 'bus-booking-manager'); ?></h2>
                                <p><?php _e('This feature requires the Bus Booking Manager PRO version. Unlock advanced passenger registration, custom fields, and more.', 'bus-booking-manager'); ?></p>
                                <a href="#" target="_blank" class="btn btn-primary btn-pro-upgrade">
                                    <span class="dashicons dashicons-external"></span>
                                    <?php _e('Upgrade to PRO', 'bus-booking-manager'); ?>
                                </a>
                            </div>
                        </div>
                        <?php
                    }
                    ?>

                </div>
            </div>
        </div>
    <?php
    }

    /**
     * Helper to get status label
     */
    private function wbbm_get_status_label($status)
    {
        $statuses = array(
            'wc-pending'    => _x('Pending payment', 'Order status', 'bus-booking-manager'),
            'wc-processing' => _x('Processing', 'Order status', 'bus-booking-manager'),
            'wc-on-hold'    => _x('On hold', 'Order status', 'bus-booking-manager'),
            'wc-completed'  => _x('Completed', 'Order status', 'bus-booking-manager'),
            'wc-cancelled'  => _x('Cancelled', 'Order status', 'bus-booking-manager'),
            'wc-refunded'   => _x('Refunded', 'Order status', 'bus-booking-manager'),
            'wc-failed'     => _x('Failed', 'Order status', 'bus-booking-manager'),
            'pending'       => __('Pending', 'bus-booking-manager'),
            'publish'       => __('Published', 'bus-booking-manager'),
        );

        return isset($statuses[$status]) ? $statuses[$status] : $status;
    }

    /**
     * Render Step 5: Tax
     */
    private function render_step_5($post_id)
    {
        $tax_status = get_post_meta($post_id, 'wbtm_bus_tax_status', true) ?: 'none';
        $tax_class = get_post_meta($post_id, 'wbtm_bus_tax_class', true) ?: '';

        $tax_classes = WC_Tax::get_tax_classes();
    ?>
        <div class="bus-edit-content">
            <div class="bus-edit-left">
                <div class="bus-card">
                    <h3><?php _e('Tax Configuration', 'bus-booking-manager'); ?></h3>
                    <div class="bus-grid">
                        <div class="form-group">
                            <label for="wbtm_bus_tax_status"><?php _e('Tax Status', 'bus-booking-manager'); ?></label>
                            <select name="wbtm_bus_tax_status" id="wbtm_bus_tax_status" class="form-control">
                                <option value="taxable" <?php selected($tax_status, 'taxable'); ?>><?php _e('Taxable', 'bus-booking-manager'); ?></option>
                                <option value="shipping" <?php selected($tax_status, 'shipping'); ?>><?php _e('Shipping only', 'bus-booking-manager'); ?></option>
                                <option value="none" <?php selected($tax_status, 'none'); ?>><?php _e('None', 'bus-booking-manager'); ?></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="wbtm_bus_tax_class"><?php _e('Tax Class', 'bus-booking-manager'); ?></label>
                            <select name="wbtm_bus_tax_class" id="wbtm_bus_tax_class" class="form-control">
                                <option value="" <?php selected($tax_class, ''); ?>><?php _e('Standard', 'bus-booking-manager'); ?></option>
                                <?php foreach ($tax_classes as $class) : ?>
                                    <option value="<?php echo esc_attr(sanitize_title($class)); ?>" <?php selected($tax_class, sanitize_title($class)); ?>><?php echo esc_html($class); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bus-edit-right">
                <div class="bus-card">
                    <h3><?php _e('Tax Info', 'bus-booking-manager'); ?></h3>
                    <p style="font-size: 13px; color: var(--bus-text-light);">
                        <?php _e('Configure how taxes should be applied to this bus service. This integrates with standard WooCommerce tax settings.', 'bus-booking-manager'); ?>
                    </p>
                </div>
            </div>
        </div>
    <?php
    }

    /**
     * Render a single extra service item
     */
    private function render_extra_service_item($data = [])
    {
        $name = isset($data['option_name']) ? $data['option_name'] : '';
        $price = isset($data['option_price']) ? $data['option_price'] : '';
        $qty = isset($data['option_qty']) ? $data['option_qty'] : '';
        $type = isset($data['option_qty_type']) ? $data['option_qty_type'] : 'fixed';
    ?>
        <div class="extra-service-item">
            <div class="bus-grid" style="grid-template-columns: 2fr 1fr 1fr 1fr 40px !important;">
                <div class="form-group">
                    <label><?php _e('Service Name', 'bus-booking-manager'); ?></label>
                    <input type="text" name="option_name[]" class="form-control" value="<?php echo esc_attr($name); ?>" placeholder="e.g. Lunch Box">
                </div>
                <div class="form-group">
                    <label><?php _e('Price', 'bus-booking-manager'); ?></label>
                    <input type="number" step="0.01" name="option_price[]" class="form-control" value="<?php echo esc_attr($price); ?>" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label><?php _e('Qty/Seat', 'bus-booking-manager'); ?></label>
                    <input type="number" name="option_qty[]" class="form-control" value="<?php echo esc_attr($qty); ?>" placeholder="1">
                </div>
                <div class="form-group">
                    <label><?php _e('Type', 'bus-booking-manager'); ?></label>
                    <select name="option_qty_type[]" class="form-control">
                        <option value="fixed" <?php selected($type, 'fixed'); ?>><?php _e('Fixed', 'bus-booking-manager'); ?></option>
                        <option value="per_seat" <?php selected($type, 'per_seat'); ?>><?php _e('Per Seat', 'bus-booking-manager'); ?></option>
                        <option value="per_person" <?php selected($type, 'per_person'); ?>><?php _e('Per Person', 'bus-booking-manager'); ?></option>
                    </select>
                </div>
                <div class="form-group" style="display: flex; align-items: flex-end;">
                    <button type="button" class="btn btn-secondary remove-extra-service" style="color:#ef4444;"><span class="dashicons dashicons-trash"></span></button>
                </div>
            </div>
        </div>
    <?php
    }

    /**
     * Render Step 3: Day Schedule
     */
    private function render_step_3($post_id)
    {
        $weekly_offday = get_post_meta($post_id, 'weekly_offday', true) ?: [];
        $od_start = get_post_meta($post_id, 'wbtm_od_start', true);
        $od_end = get_post_meta($post_id, 'wbtm_od_end', true);
        $offday_schedule = get_post_meta($post_id, 'wbtm_offday_schedule', true) ?: [];

        $days = [
            '1' => __('Monday', 'bus-booking-manager'),
            '2' => __('Tuesday', 'bus-booking-manager'),
            '3' => __('Wednesday', 'bus-booking-manager'),
            '4' => __('Thursday', 'bus-booking-manager'),
            '5' => __('Friday', 'bus-booking-manager'),
            '6' => __('Saturday', 'bus-booking-manager'),
            '7' => __('Sunday', 'bus-booking-manager'),
        ];
    ?>
        <div class="bus-edit-content">
            <div class="bus-edit-left">
                <div class="bus-card">
                    <h3><?php _e('Operating Days', 'bus-booking-manager'); ?></h3>
                    <p style="margin-bottom: 20px; color: var(--bus-text-light);"><?php _e('Select the days of the week when this bus service is active.', 'bus-booking-manager'); ?></p>
                    <div class="days-selector">
                        <?php foreach ($days as $value => $label) : ?>
                            <label class="day-checkbox">
                                <input type="checkbox" name="weekly_offday[]" value="<?php echo $value; ?>" <?php echo in_array($value, $weekly_offday) ? 'checked' : ''; ?>>
                                <span><?php echo $label; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bus-card">
                    <h3><?php _e('Operational Date Range', 'bus-booking-manager'); ?></h3>
                    <div class="bus-grid">
                        <div class="form-group">
                            <label><?php _e('Service Start Date', 'bus-booking-manager'); ?></label>
                            <input type="date" name="wbtm_od_start" class="form-control" value="<?php echo esc_attr($od_start); ?>">
                        </div>
                        <div class="form-group">
                            <label><?php _e('Service End Date', 'bus-booking-manager'); ?></label>
                            <input type="date" name="wbtm_od_end" class="form-control" value="<?php echo esc_attr($od_end); ?>">
                        </div>
                    </div>
                </div>

                <div class="bus-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="margin: 0; border: none; padding: 0;"><?php _e('Off-day Schedule', 'bus-booking-manager'); ?></h3>
                        <button type="button" class="btn btn-secondary btn-sm add-offday-item">
                            <span class="dashicons dashicons-plus"></span> <?php _e('Add Off-day Slot', 'bus-booking-manager'); ?>
                        </button>
                    </div>

                    <div id="offday-items-container">
                        <?php if (!empty($offday_schedule)) : ?>
                            <?php foreach ($offday_schedule as $item) : ?>
                                <?php $this->render_offday_item($item); ?>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <?php $this->render_offday_item(); ?>
                        <?php endif; ?>
                    </div>

                    <!-- Template for new items -->
                    <script type="text/template" id="offday-item-template">
                        <?php $this->render_offday_item(); ?>
                    </script>
                </div>
            </div>

            <div class="bus-edit-right">
                <div class="bus-card">
                    <h3><?php _e('Schedule Info', 'bus-booking-manager'); ?></h3>
                    <ul style="font-size: 13px; color: var(--bus-text-light); padding-left: 15px;">
                        <li><?php _e('Operating Days define the weekly routine.', 'bus-booking-manager'); ?></li>
                        <li><?php _e('Date Range defines the long-term availability.', 'bus-booking-manager'); ?></li>
                        <li><?php _e('Off-day Schedule is for specific dates/times like holidays.', 'bus-booking-manager'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    <?php
    }

    /**
     * Render a single off-day repeater item
     */
    private function render_offday_item($data = [])
    {
        $from_date = isset($data['from_date']) ? $data['from_date'] : '';
        $to_date = isset($data['to_date']) ? $data['to_date'] : '';
        $from_time = isset($data['from_time']) ? $data['from_time'] : '';
        $to_time = isset($data['to_time']) ? $data['to_time'] : '';
    ?>
        <div class="offday-item">
            <div class="bus-grid" style="grid-template-columns: 1fr 1fr 1fr 1fr 40px !important;">
                <div class="form-group">
                    <label><?php _e('From Date', 'bus-booking-manager'); ?></label>
                    <input type="date" name="wbtm_od_offdate_from[]" class="form-control" value="<?php echo esc_attr($from_date); ?>">
                </div>
                <div class="form-group">
                    <label><?php _e('To Date', 'bus-booking-manager'); ?></label>
                    <input type="date" name="wbtm_od_offdate_to[]" class="form-control" value="<?php echo esc_attr($to_date); ?>">
                </div>
                <div class="form-group">
                    <label><?php _e('From Time', 'bus-booking-manager'); ?></label>
                    <input type="time" name="wbtm_od_offtime_from[]" class="form-control" value="<?php echo esc_attr($from_time); ?>">
                </div>
                <div class="form-group">
                    <label><?php _e('To Time', 'bus-booking-manager'); ?></label>
                    <input type="time" name="wbtm_od_offtime_to[]" class="form-control" value="<?php echo esc_attr($to_time); ?>">
                </div>
                <div class="form-group" style="display: flex; align-items: flex-end;">
                    <button type="button" class="btn btn-secondary remove-offday-item" style="color:#ef4444;padding:7px"><span class="dashicons dashicons-trash"></span></button>
                </div>
            </div>
        </div>
    <?php
    }


    /**
     * Render a single route item (stop)
     */
    private function render_route_item($index, $data = [], $bus_stops = [], $pickpoints = [])
    {
        $place = isset($data['place']) ? $data['place'] : '';
        $time = isset($data['time']) ? $data['time'] : '';
        $type = isset($data['type']) ? $data['type'] : 'both';
        $next_day = isset($data['next_day']) ? $data['next_day'] : 0;
    ?>
        <div class="route-item" data-index="<?php echo $index; ?>">
            <div class="route-item-header">
                <span class="dashicons dashicons-menu drag-handle"></span>
                <span class="stop-name-display"><?php echo $place ?: __('New Stop', 'bus-booking-manager'); ?></span>
                <div class="route-item-actions">
                    <button type="button" class="remove-route-item"><span class="dashicons dashicons-trash"></span></button>
                    <span class="dashicons dashicons-arrow-down-alt2 toggle-route-item"></span>
                </div>
            </div>
            <div class="route-item-body">
                <div class="bus-grid">
                    <div class="form-group">
                        <label><?php _e('Stop', 'bus-booking-manager'); ?></label>
                        <select name="wbtm_route_place[]" class="form-control route-place-select">
                            <option value=""><?php _e('Select Stop', 'bus-booking-manager'); ?></option>
                            <?php foreach ($bus_stops as $stop) : ?>
                                <option value="<?php echo esc_attr($stop->name); ?>" <?php selected($stop->name, $place); ?>><?php echo esc_html($stop->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><?php _e('Time', 'bus-booking-manager'); ?></label>
                        <input type="time" name="wbtm_route_time[]" class="form-control" value="<?php echo esc_attr($time); ?>">
                    </div>
                    <div class="form-group">
                        <label><?php _e('Type', 'bus-booking-manager'); ?></label>
                        <select name="wbtm_route_type[]" class="form-control route-type-select">
                            <option value="bp" <?php selected($type, 'bp'); ?>><?php _e('Boarding', 'bus-booking-manager'); ?></option>
                            <option value="dp" <?php selected($type, 'dp'); ?>><?php _e('Dropping', 'bus-booking-manager'); ?></option>
                            <option value="both" <?php selected($type, 'both'); ?>><?php _e('Both', 'bus-booking-manager'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="next-day-wrap" style="<?php echo ($type === 'dp' || $type === 'both') ? '' : 'display:none;'; ?>">
                    <label>
                        <input type="checkbox" name="wbtm_route_next_day[<?php echo $index; ?>]" value="1" <?php checked($next_day, 1); ?>>
                        <?php _e('Next Day Dropping', 'bus-booking-manager'); ?>
                    </label>
                </div>

                <!-- Pickup Points Integration -->
                <div class="route-pickup-points-wrap">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <h4><?php _e('Pickup Points', 'bus-booking-manager'); ?></h4>
                        <button type="button" class="btn btn-secondary btn-sm add-inline-pickup-item" data-stop-index="<?php echo $index; ?>">
                            <span class="dashicons dashicons-plus"></span> <?php _e('Add Point', 'bus-booking-manager'); ?>
                        </button>
                    </div>
                    <div class="pickup-points-list" data-stop-index="<?php echo $index; ?>">
                        <?php
                        $city_slug = $place ? sanitize_key(str_replace(' ', '_', strtolower($place))) : '';
                        $pickup_data = $city_slug ? get_post_meta(get_the_ID(), 'wbbm_selected_pickpoint_name_' . $city_slug, true) : [];
                        if (empty($pickup_data)) {
                            $pickup_data = [['pickpoint' => '', 'time' => '']];
                        }
                        foreach ($pickup_data as $p) :
                        ?>
                            <div class="pickup-point-item" style="display: flex; gap: 10px; margin-bottom: 8px; align-items: center;">
                                <select name="wbbm_inline_pickpoint_name[<?php echo $index; ?>][]" class="form-control sm" style="width:320px">
                                    <option value=""><?php _e('Select Point', 'bus-booking-manager'); ?></option>
                                    <?php foreach ($pickpoints as $point) : ?>
                                        <option value="<?php echo esc_attr($point->name); ?>" <?php selected($point->name, $p['pickpoint']); ?>><?php echo esc_html($point->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="time" name="wbbm_inline_pickpoint_time[<?php echo $index; ?>][]" class="form-control sm" value="<?php echo esc_attr($p['time']); ?>" style="width:auto">
                                <button type="button" class="btn btn-secondary btn-sm remove-inline-pickup-item" style="color: #ef4444; justify-self: end;padding:7px;"><span class="dashicons dashicons-trash"></span></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    /**
     * Render Pricing Matrix
     */
    private function render_pricing_matrix($post_id, $route_infos = [])
    {
        $price_infos = get_post_meta($post_id, 'wbbm_bus_prices', true) ?: [];
        $all_pairs = [];

        if (!empty($route_infos)) {
            foreach ($route_infos as $i => $start) {
                if ($start['type'] === 'bp' || $start['type'] === 'both') {
                    for ($j = $i + 1; $j < count($route_infos); $j++) {
                        $end = $route_infos[$j];
                        if ($end['type'] === 'dp' || $end['type'] === 'both') {
                            $pair_prices = [
                                'adult'   => '',
                                'child'   => '',
                                'student' => '',
                                'infant'  => ''
                            ];

                            // Try to find existing price
                            foreach ($price_infos as $p) {
                                if ($p['wbbm_bus_bp_price_stop'] === $start['place'] && $p['wbbm_bus_dp_price_stop'] === $end['place']) {
                                    $pair_prices = [
                                        'adult'   => isset($p['wbbm_bus_price']) ? $p['wbbm_bus_price'] : '',
                                        'child'   => isset($p['wbbm_bus_price_child']) ? $p['wbbm_bus_price_child'] : '',
                                        'student' => isset($p['wbbm_bus_price_student']) ? $p['wbbm_bus_price_student'] : '',
                                        'infant'  => isset($p['wbbm_bus_price_infant']) ? $p['wbbm_bus_price_infant'] : ''
                                    ];
                                    break;
                                }
                            }

                            $all_pairs[] = [
                                'from'   => $start['place'],
                                'to'     => $end['place'],
                                'prices' => $pair_prices
                            ];
                        }
                    }
                }
            }
        }

        if (empty($all_pairs)) {
            echo '<div class="alert alert-warning">' . __('Please add at least one Boarding and one Dropping stop to see the pricing matrix.', 'bus-booking-manager') . '</div>';
            return;
        }
    ?>
        <div class="pricing-matrix-table-wrap">
            <table class="pricing-matrix-table">
                <thead>
                    <tr>
                        <th><?php _e('Route (From ➝ To)', 'bus-booking-manager'); ?></th>
                        <th><?php _e('Adult', 'bus-booking-manager'); ?></th>
                        <th><?php _e('Child', 'bus-booking-manager'); ?></th>
                        <th><?php _e('Student', 'bus-booking-manager'); ?></th>
                        <th><?php _e('Infant', 'bus-booking-manager'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_pairs as $pair) : ?>
                        <tr>
                            <td>
                                <div class="route-pair">
                                    <span class="from"><?php echo esc_html($pair['from']); ?></span>
                                    <span class="dashicons dashicons-arrow-right-alt"></span>
                                    <span class="to"><?php echo esc_html($pair['to']); ?></span>
                                    <input type="hidden" name="wbtm_price_bp[]" value="<?php echo esc_attr($pair['from']); ?>">
                                    <input type="hidden" name="wbtm_price_dp[]" value="<?php echo esc_attr($pair['to']); ?>">
                                </div>
                            </td>
                            <td><input type="number" step="0.01" name="wbtm_adult_price[]" value="<?php echo esc_attr($pair['prices']['adult']); ?>" class="form-control sm"></td>
                            <td><input type="number" step="0.01" name="wbtm_child_price[]" value="<?php echo esc_attr($pair['prices']['child']); ?>" class="form-control sm"></td>
                            <td><input type="number" step="0.01" name="wbtm_student_price[]" value="<?php echo esc_attr($pair['prices']['student']); ?>" class="form-control sm"></td>
                            <td><input type="number" step="0.01" name="wbtm_infant_price[]" value="<?php echo esc_attr($pair['prices']['infant']); ?>" class="form-control sm"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
<?php
    }
    /**
     * Get status metadata (label and class)
     */
    private function get_status_metadata($status)
    {
        $label = '';
        $class = '';

        switch ($status) {
            case 'publish':
                $label = __('Published', 'bus-booking-manager');
                $class = 'status-publish';
                break;
            case 'draft':
                $label = __('Draft', 'bus-booking-manager');
                $class = 'status-draft';
                break;
            case 'new':
                $label = __('New', 'bus-booking-manager');
                $class = 'status-new';
                break;
            default:
                $label = ucfirst($status);
                $class = 'status-' . $status;
                break;
        }

        return array('label' => $label, 'class' => $class);
    }
}

new BusEditPageClass();
