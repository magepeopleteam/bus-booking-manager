<?php
if (!defined('ABSPATH')) exit;

/**
 * Shuttle Edit Page Class
 * 
 * Handles the custom multi-step edit page for shuttles.
 */
class ShuttleEditPageClass
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_shuttle_edit_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_init', array($this, 'handle_shuttle_save'));
        add_filter('post_row_actions', array($this, 'add_custom_edit_link'), 10, 2);

        // AJAX Save
        add_action('wp_ajax_wbbm_save_shuttle_ajax', array($this, 'handle_shuttle_save_ajax'));
        add_action('wp_ajax_wbbm_add_shuttle_stop_ajax', array($this, 'handle_add_shuttle_stop_ajax'));
        add_action('wp_ajax_wbbm_get_shuttle_pricing_matrix_ajax', array($this, 'handle_get_shuttle_pricing_matrix_ajax'));
        add_action('wp_ajax_wbbm_get_shuttle_schedule_ajax', array($this, 'handle_get_shuttle_schedule_ajax'));

        // Redirection for default Add New page
        add_action('current_screen', array($this, 'redirect_to_custom_edit'));
    }

    /**
     * Redirect default Add New shuttle page to custom edit page
     */
    public function redirect_to_custom_edit()
    {
        $screen = get_current_screen();
        if ($screen && $screen->id === 'wbbm_shuttle' && $screen->action === 'add') {
            wp_redirect(admin_url('admin.php?page=wbbm-shuttle-edit'));
            exit;
        }
    }

    /**
     * Register the custom edit page as a hidden submenu
     */
    public function register_shuttle_edit_page()
    {
        // Create a hidden page by setting parent slug to null
        add_submenu_page(
            null,
            __('Add New', 'bus-booking-manager'),
            __('Add New', 'bus-booking-manager'),
            'manage_options',
            'wbbm-shuttle-edit',
            array($this, 'render_shuttle_edit_page')
        );
    }

    /**
     * Add custom edit link to row actions
     */
    public function add_custom_edit_link($actions, $post)
    {
        if ($post->post_type !== 'wbbm_shuttle') {
            return $actions;
        }

        $custom_edit_url = add_query_arg(
            array(
                'page'      => 'wbbm-shuttle-edit',
                'post_id'   => $post->ID
            ),
            admin_url('admin.php')
        );

        $actions['custom_edit'] = '<a href="' . esc_url($custom_edit_url) . '" style="color:#7c3aed;font-weight:bold;">' . __('Advanced Edit', 'bus-booking-manager') . '</a>';

        return $actions;
    }

    /**
     * Handle Add Shuttle Stop via AJAX
     */
    public function handle_add_shuttle_stop_ajax()
    {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wbbm_shuttle_save')) {
            wp_send_json_error('Nonce verification failed');
        }

        $stop_name = isset($_POST['stop_name']) ? sanitize_text_field($_POST['stop_name']) : '';

        if (empty($stop_name)) {
            wp_send_json_error('Stop name is required');
        }

        if (term_exists($stop_name, 'wbbm_shuttle_stops')) {
            wp_send_json_error('Stop already exists');
        }

        $term = wp_insert_term($stop_name, 'wbbm_shuttle_stops');

        if (is_wp_error($term)) {
            wp_send_json_error($term->get_error_message());
        }

        wp_send_json_success(array(
            'term_id' => $term['term_id'],
            'name'    => $stop_name
        ));
    }

    /**
     * Handle Get Pricing Matrix via AJAX
     */
    public function handle_get_shuttle_pricing_matrix_ajax()
    {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wbbm_shuttle_save')) {
            wp_send_json_error('Nonce verification failed');
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        // Use unsaved data from POST if available to reflect immediate changes
        $routes = array();
        if (isset($_POST['wbbm_shuttle_routes']) && is_array($_POST['wbbm_shuttle_routes'])) {
            // We need to sanitize and format correctly for render_pricing_matrix
            foreach ($_POST['wbbm_shuttle_routes'] as $route) {
                if (!empty($route['name'])) {
                    $clean_route = array(
                        'name' => sanitize_text_field($route['name']),
                        'type' => isset($route['type']) ? sanitize_text_field($route['type']) : 'one-way',
                        'id'   => isset($route['id']) ? sanitize_text_field($route['id']) : uniqid('route_'),
                        'stops' => array()
                    );

                    if (isset($route['stops']) && is_array($route['stops'])) {
                        foreach ($route['stops'] as $stop) {
                            $clean_route['stops'][] = array(
                                'location' => sanitize_text_field($stop['location']),
                                'time_offset' => sanitize_text_field($stop['time_offset']),
                                'distance' => sanitize_text_field($stop['distance']),
                                'pickup_points' => isset($stop['pickup_points']) ? sanitize_textarea_field($stop['pickup_points']) : '',
                                'dropoff_points' => isset($stop['dropoff_points']) ? sanitize_textarea_field($stop['dropoff_points']) : ''
                            );
                        }
                    }
                    $routes[] = $clean_route;
                }
            }
        } else if ($post_id) {
            $routes = maybe_unserialize(get_post_meta($post_id, 'wbbm_shuttle_routes', true)) ?: array();
        }

        $pricing = $post_id ? (maybe_unserialize(get_post_meta($post_id, 'wbbm_shuttle_pricing', true)) ?: array()) : array();

        ob_start();
        $this->render_pricing_matrix($routes, $pricing);
        $html = ob_get_clean();

        wp_send_json_success($html);
    }

    /**
     * Handle Get Schedule via AJAX
     */
    public function handle_get_shuttle_schedule_ajax()
    {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wbbm_shuttle_save')) {
            wp_send_json_error('Nonce verification failed');
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        // Use unsaved data from POST if available to reflect immediate changes
        $routes = array();
        if (isset($_POST['wbbm_shuttle_routes']) && is_array($_POST['wbbm_shuttle_routes'])) {
            foreach ($_POST['wbbm_shuttle_routes'] as $route) {
                if (!empty($route['name'])) {
                    $clean_route = array(
                        'name' => sanitize_text_field($route['name']),
                        'type' => isset($route['type']) ? sanitize_text_field($route['type']) : 'one-way',
                        'id'   => isset($route['id']) ? sanitize_text_field($route['id']) : uniqid('route_'),
                        'stops' => array()
                    );

                    if (isset($route['stops']) && is_array($route['stops'])) {
                        foreach ($route['stops'] as $stop) {
                            $clean_route['stops'][] = array(
                                'location' => sanitize_text_field($stop['location']),
                                'time_offset' => sanitize_text_field($stop['time_offset']),
                                'distance' => sanitize_text_field($stop['distance']),
                                'pickup_points' => isset($stop['pickup_points']) ? sanitize_textarea_field($stop['pickup_points']) : '',
                                'dropoff_points' => isset($stop['dropoff_points']) ? sanitize_textarea_field($stop['dropoff_points']) : ''
                            );
                        }
                    }
                    $routes[] = $clean_route;
                }
            }
        } else if ($post_id) {
            $routes = maybe_unserialize(get_post_meta($post_id, 'wbbm_shuttle_routes', true)) ?: array();
        }

        $schedule = $post_id ? (maybe_unserialize(get_post_meta($post_id, 'wbbm_shuttle_schedule', true)) ?: array()) : array();

        ob_start();
        $this->render_schedule_layout($routes, $schedule);
        $html = ob_get_clean();

        wp_send_json_success($html);
    }

    /**
     * Handle Shuttle Save via AJAX
     */
    public function handle_shuttle_save_ajax()
    {
        $this->handle_shuttle_save(true);
    }

    /**
     * Handle Shuttle Save
     */
    public function handle_shuttle_save($is_ajax = false)
    {
        // If it's an AJAX call but we're not inside the AJAX handler, or vice versa, skip
        if (defined('DOING_AJAX') && DOING_AJAX && !$is_ajax) {
            return;
        }

        if (!isset($_POST['wbbm_shuttle_nonce']) || !wp_verify_nonce($_POST['wbbm_shuttle_nonce'], 'wbbm_shuttle_save')) {
            if ($is_ajax) wp_send_json_error('Nonce verification failed');
            return;
        }

        if (!current_user_can('manage_options')) {
            if ($is_ajax) wp_send_json_error('Unauthorized');
            return;
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $title = isset($_POST['shuttle_title']) ? sanitize_text_field(wp_unslash($_POST['shuttle_title'])) : '';
        $content = isset($_POST['shuttle_content']) ? wp_kses_post(wp_unslash($_POST['shuttle_content'])) : '';

        $post_status = isset($_POST['post_status']) ? sanitize_text_field($_POST['post_status']) : 'publish';

        $post_data = array(
            'post_title'   => $title,
            'post_content' => $content,
            'post_status'  => $post_status,
            'post_type'    => 'wbbm_shuttle',
        );

        if ($post_id) {
            $existing_post = get_post($post_id);
            $needs_update = false;

            if ($existing_post->post_title !== $title || $existing_post->post_content !== $content || $existing_post->post_status !== $post_status) {
                $needs_update = true;
            }

            if ($needs_update) {
                $post_data['ID'] = $post_id;
                // Disable redundant hooks from other classes during our custom save
                remove_all_actions('save_post_wbbm_shuttle');
                wp_update_post($post_data);
            }
        } else {
            $post_id = wp_insert_post($post_data);
        }

        if ($post_id) {
            // Save Basic Meta
            if (isset($_POST['vehicle_number'])) {
                update_post_meta($post_id, 'wbbm_shuttle_vehicle_number', sanitize_text_field($_POST['vehicle_number']));
            }
            if (isset($_POST['capacity'])) {
                update_post_meta($post_id, 'wbbm_shuttle_capacity', intval($_POST['capacity']));
            }

            // Save Taxonomies
            if (!empty($_POST['shuttle_type'])) {
                wp_set_post_terms($post_id, array(intval($_POST['shuttle_type'])), 'wbbm_shuttle_type', false);
            }
            if (!empty($_POST['vehicle_cat'])) {
                wp_set_post_terms($post_id, array(intval($_POST['vehicle_cat'])), 'wbbm_shuttle_cat', false);
            }
            if (!empty($_POST['shuttle_stops']) && is_array($_POST['shuttle_stops'])) {
                wp_set_post_terms($post_id, array_map('intval', $_POST['shuttle_stops']), 'wbbm_shuttle_stops', false);
            }

            // Save Thumbnail
            if (isset($_POST['shuttle_thumbnail_id'])) {
                set_post_thumbnail($post_id, intval($_POST['shuttle_thumbnail_id']));
            }

            // Save Routes (One route for one shuttle)
            if (isset($_POST['wbbm_shuttle_routes']) && is_array($_POST['wbbm_shuttle_routes'])) {
                $routes = array();
                foreach ($_POST['wbbm_shuttle_routes'] as $route) {
                    if (!empty($route['name'])) {
                        $clean_route = array(
                            'name' => sanitize_text_field($route['name']),
                            'type' => isset($route['type']) ? sanitize_text_field($route['type']) : 'one-way',
                            'id'   => isset($route['id']) ? sanitize_text_field($route['id']) : uniqid('route_'),
                            'stops' => array()
                        );

                        if (isset($route['stops']) && is_array($route['stops'])) {
                            foreach ($route['stops'] as $stop) {
                                $clean_route['stops'][] = array(
                                    'location' => sanitize_text_field($stop['location']),
                                    'time_offset' => sanitize_text_field($stop['time_offset']),
                                    'distance' => sanitize_text_field($stop['distance']),
                                    'pickup_points' => isset($stop['pickup_points']) ? sanitize_textarea_field($stop['pickup_points']) : '',
                                    'dropoff_points' => isset($stop['dropoff_points']) ? sanitize_textarea_field($stop['dropoff_points']) : ''
                                );
                            }
                        }
                        $routes[] = $clean_route;
                        break; // Only one route allowed
                    }
                }
                update_post_meta($post_id, 'wbbm_shuttle_routes', $routes);
            }

            // Save Pricing
            $pricing = array();
            if (isset($_POST['wbbm_shuttle_pricing']) && is_array($_POST['wbbm_shuttle_pricing'])) {
                if (isset($_POST['wbbm_shuttle_pricing']['routes'])) {
                    foreach ($_POST['wbbm_shuttle_pricing']['routes'] as $r_id => $stops) {
                        foreach ($stops as $origin => $dests) {
                            foreach ($dests as $dest => $price) {
                                if (is_array($price)) {
                                    $p_data = array();
                                    if (isset($price['oneway']) && $price['oneway'] !== '') $p_data['oneway'] = sanitize_text_field($price['oneway']);
                                    if (isset($price['roundtrip']) && $price['roundtrip'] !== '') $p_data['roundtrip'] = sanitize_text_field($price['roundtrip']);

                                    if (!empty($p_data)) {
                                        $pricing['routes'][$r_id][sanitize_text_field($origin)][sanitize_text_field($dest)] = $p_data;
                                    }
                                } else {
                                    if ($price !== '') {
                                        $pricing['routes'][$r_id][sanitize_text_field($origin)][sanitize_text_field($dest)] = sanitize_text_field($price);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            update_post_meta($post_id, 'wbbm_shuttle_pricing', $pricing);
        }

        // Save Schedule
        $schedule = array();
        if (isset($_POST['wbbm_shuttle_schedule']) && is_array($_POST['wbbm_shuttle_schedule'])) {
            // Sanitize schedule data
            foreach ($_POST['wbbm_shuttle_schedule'] as $r_id => $r_data) {
                if (!is_array($r_data)) continue;

                foreach (['forward', 'return'] as $dir) {
                    if (isset($r_data[$dir]) && is_array($r_data[$dir])) {
                        foreach ($r_data[$dir] as $idx => $time_data) {
                            if (empty($time_data['time'])) continue;

                            $schedule[sanitize_text_field($r_id)][$dir][] = array(
                                'time' => sanitize_text_field($time_data['time']),
                                'days' => isset($time_data['days']) ? array_map('sanitize_text_field', (array)$time_data['days']) : array()
                            );
                        }
                    }
                }
            }
            update_post_meta($post_id, 'wbbm_shuttle_schedule', $schedule);
        }

        // Mark as virtual for WC
        update_post_meta($post_id, '_virtual', 'yes');
        update_post_meta($post_id, '_sold_individually', 'yes');

        if ($is_ajax) {
            wp_send_json_success(array('post_id' => $post_id));
        }

        $next_step = isset($_POST['next_step_val']) ? intval($_POST['next_step_val']) : 0;
        $redirect_args = array('page' => 'wbbm-shuttle-edit', 'post_id' => $post_id, 'saved' => '1');
        if ($next_step) {
            $redirect_args['step'] = $next_step;
        } else if (isset($_POST['current_step'])) {
            $redirect_args['step'] = intval($_POST['current_step']);
        }

        // Redirect to avoid resubmission
        wp_redirect(add_query_arg($redirect_args, admin_url('edit.php?post_type=wbbm_shuttle')));
        exit;
    }

    /**
     * Enqueue CSS and JS
     */
    public function enqueue_assets($hook)
    {
        if (strpos($hook, 'wbbm-shuttle-edit') === false) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_style('shuttle-edit-css', WBTM_PLUGIN_URL . 'assets/admin/shuttle-edit.css', array(), time());
        wp_enqueue_script('shuttle-edit-js', WBTM_PLUGIN_URL . 'assets/admin/shuttle-edit.js', array('jquery'), time(), true);

        // Required for the WP Editor in AJAX or dynamic contexts if needed
        wp_enqueue_editor();
    }

    /**
     * Render the custom edit page
     */
    public function render_shuttle_edit_page()
    {
        $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
        $current_step = isset($_GET['step']) ? intval($_GET['step']) : 1;
        $post = $post_id ? get_post($post_id) : null;

        if (isset($_GET['saved']) && $_GET['saved'] == '1') {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Shuttle saved successfully.', 'bus-booking-manager') . '</p></div>';
        }

        $current_status = $post ? $post->post_status : 'new';
        $status_label = '';
        $status_class = '';

        switch ($current_status) {
            case 'publish':
                $status_label = __('Published', 'bus-booking-manager');
                $status_class = 'status-publish';
                break;
            case 'draft':
                $status_label = __('Draft', 'bus-booking-manager');
                $status_class = 'status-draft';
                break;
            case 'pending':
                $status_label = __('Pending', 'bus-booking-manager');
                $status_class = 'status-pending';
                break;
            case 'future':
                $status_label = __('Scheduled', 'bus-booking-manager');
                $status_class = 'status-future';
                break;
            case 'private':
                $status_label = __('Private', 'bus-booking-manager');
                $status_class = 'status-private';
                break;
            case 'new':
            case 'auto-draft':
            default:
                $status_label = __('New', 'bus-booking-manager');
                $status_class = 'status-new';
                break;
        }

        $is_published = ($current_status === 'publish');
        $primary_btn_label = $is_published ? __('Save', 'bus-booking-manager') : __('Publish', 'bus-booking-manager');
        $next_step_label = $is_published ? __('Save & Next step', 'bus-booking-manager') : __('Publish & Next step', 'bus-booking-manager');
        $finish_btn_label = $is_published ? __('Save Shuttle', 'bus-booking-manager') : __('Publish Shuttle', 'bus-booking-manager');

        $title = $post ? $post->post_title : '';
        $content = $post ? $post->post_content : '';

        // Basic Meta
        $shuttle_type = $post_id ? wp_get_post_terms($post_id, 'wbbm_shuttle_type', array('fields' => 'ids')) : array();
        $shuttle_cat = $post_id ? wp_get_post_terms($post_id, 'wbbm_shuttle_cat', array('fields' => 'ids')) : array();
        $capacity = $post_id ? get_post_meta($post_id, 'wbbm_shuttle_capacity', true) : '';
        $vehicle_number = $post_id ? get_post_meta($post_id, 'wbbm_shuttle_vehicle_number', true) : '';

        // Routes
        $routes = $post_id ? (maybe_unserialize(get_post_meta($post_id, 'wbbm_shuttle_routes', true)) ?: array()) : array();
        $route = !empty($routes) ? $routes[0] : array('name' => '', 'type' => 'one-way', 'id' => uniqid('route_'), 'stops' => array());

        // Pricing
        $pricing = $post_id ? (maybe_unserialize(get_post_meta($post_id, 'wbbm_shuttle_pricing', true)) ?: array()) : array();

        // Schedule
        $schedule = $post_id ? (maybe_unserialize(get_post_meta($post_id, 'wbbm_shuttle_schedule', true)) ?: array()) : array();

        // Thumbnail
        $thumb_id = get_post_thumbnail_id($post_id);
        $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'medium') : '';

        // All Terms for select
        $all_types = get_terms(array('taxonomy' => 'wbbm_shuttle_type', 'hide_empty' => false));
        $all_cats = get_terms(array('taxonomy' => 'wbbm_shuttle_cat', 'hide_empty' => false));
        $all_stops = get_terms(array('taxonomy' => 'wbbm_shuttle_stops', 'hide_empty' => false));

        $selected_stops = $post_id ? wp_get_post_terms($post_id, 'wbbm_shuttle_stops', array('fields' => 'ids')) : array();

?>
        <div class="wrap shuttle-edit-wrap">
            <div class="shuttle-container">
                <div class="shuttle-edit-header">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <a href="<?php echo admin_url('edit.php?post_type=wbbm_shuttle&page=wbbm-shuttle-list'); ?>" class="back-btn skeleton skeleton-btn">
                            <span class="dashicons dashicons-arrow-left-alt"></span>
                        </a>
                        <h2 class="skeleton skeleton-text"><?php echo $post_id ? __('Edit Shuttle', 'bus-booking-manager') . ': ' . esc_html($title) : __('Add New Shuttle', 'bus-booking-manager'); ?></h2>
                        <span class="shuttle-status-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span>
                    </div>
                    <div class="header-actions">
                        <button type="button" id="save-shuttle-draft" class="btn btn-secondary skeleton skeleton-btn"><?php _e('Save as Draft', 'bus-booking-manager'); ?></button>
                        <button type="submit" form="shuttle-edit-form" class="btn btn-primary skeleton skeleton-btn"><?php echo esc_html($primary_btn_label); ?></button>
                    </div>
                </div>

                <!-- Steps Navigation -->
                <div class="shuttle-steps-nav">
                    <?php if ($post_id) : ?>
                        <div class="shuttle-nav-title">
                            <span class="skeleton skeleton-text"><?php echo esc_html($title); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="shuttle-steps-list">
                        <div class="step-item <?php echo $current_step === 1 ? 'active' : ($current_step > 1 ? 'completed' : ''); ?>" data-step="1">
                            <div class="step-number"><?php echo $current_step > 1 ? '✓' : '1'; ?></div>
                            <div class="step-label"><?php _e('Basic Info', 'bus-booking-manager'); ?></div>
                        </div>
                        <div class="step-item <?php echo $current_step === 2 ? 'active' : ($current_step > 2 ? 'completed' : ''); ?>" data-step="2">
                            <div class="step-number"><?php echo $current_step > 2 ? '✓' : '2'; ?></div>
                            <div class="step-label"><?php _e('Route', 'bus-booking-manager'); ?></div>
                        </div>
                        <div class="step-item <?php echo $current_step === 3 ? 'active' : ($current_step > 3 ? 'completed' : ''); ?>" data-step="3">
                            <div class="step-number"><?php echo $current_step > 3 ? '✓' : '3'; ?></div>
                            <div class="step-label"><?php _e('Pricing', 'bus-booking-manager'); ?></div>
                        </div>
                        <div class="step-item <?php echo $current_step === 4 ? 'active' : ($current_step > 4 ? 'completed' : ''); ?>" data-step="4">
                            <div class="step-number"><?php echo $current_step > 4 ? '✓' : '4'; ?></div>
                            <div class="step-label"><?php _e('Schedule', 'bus-booking-manager'); ?></div>
                        </div>
                    </div>
                </div>

                <form id="shuttle-edit-form" method="post" action="">
                    <?php wp_nonce_field('wbbm_shuttle_save', 'wbbm_shuttle_nonce'); ?>
                    <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
                    <input type="hidden" name="post_status" id="post_status" value="<?php echo esc_attr($current_status); ?>">
                    <input type="hidden" name="current_step" value="<?php echo esc_attr($current_step); ?>">
                    <input type="hidden" name="next_step_val" id="next_step_val" value="0">

                    <!-- Step 1: Basic Info -->
                    <div class="shuttle-step-content <?php echo $current_step === 1 ? 'active' : ''; ?>" id="step-1-content">
                        <div class="shuttle-edit-content">
                            <!-- Left Column -->
                            <div class="shuttle-edit-left">
                                <div class="shuttle-card">
                                    <div class="form-group">
                                        <label for="shuttle_title"><?php _e('Shuttle Title', 'bus-booking-manager'); ?> <span class="required">*</span></label>
                                        <input type="text" name="shuttle_title" id="shuttle_title" class="form-control" value="<?php echo esc_attr($title); ?>" placeholder="<?php _e('Enter shuttle title', 'bus-booking-manager'); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label><?php _e('Description', 'bus-booking-manager'); ?></label>
                                        <?php
                                        wp_editor($content, 'shuttle_content', array(
                                            'textarea_name' => 'shuttle_content',
                                            'media_buttons' => true,
                                            'textarea_rows' => 10,
                                            'teeny'         => false,
                                        ));
                                        ?>
                                    </div>
                                </div>

                                <div class="shuttle-card">
                                    <h3><?php _e('Shuttle Basic Info', 'bus-booking-manager'); ?></h3>
                                    <div class="shuttle-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                        <div class="form-group">
                                            <label><?php _e('Shuttle Type', 'bus-booking-manager'); ?> <span class="required">*</span></label>
                                            <select name="shuttle_type" class="form-control" required>
                                                <option value=""><?php _e('Select Type', 'bus-booking-manager'); ?></option>
                                                <?php foreach ($all_types as $type) : ?>
                                                    <option value="<?php echo esc_attr($type->term_id); ?>" <?php selected(in_array($type->term_id, $shuttle_type)); ?>>
                                                        <?php echo esc_html($type->name); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label><?php _e('Vehicle Category', 'bus-booking-manager'); ?></label>
                                            <select name="vehicle_cat" class="form-control">
                                                <option value=""><?php _e('Select Category', 'bus-booking-manager'); ?></option>
                                                <?php foreach ($all_cats as $cat) : ?>
                                                    <option value="<?php echo esc_attr($cat->term_id); ?>" <?php selected(in_array($cat->term_id, $shuttle_cat)); ?>>
                                                        <?php echo esc_html($cat->name); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label><?php _e('Vehicle Number', 'bus-booking-manager'); ?></label>
                                            <input type="text" name="vehicle_number" class="form-control" value="<?php echo esc_attr($vehicle_number); ?>" placeholder="e.g. SH-001">
                                        </div>
                                        <div class="form-group">
                                            <label><?php _e('Capacity (Seats)', 'bus-booking-manager'); ?> <span class="required">*</span></label>
                                            <input type="number" name="capacity" class="form-control" value="<?php echo esc_attr($capacity); ?>" min="1" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="shuttle-edit-right">
                                <div class="shuttle-card">
                                    <h3><?php _e('Thumbnail', 'bus-booking-manager'); ?></h3>
                                    <div class="shuttle-thumbnail-box" id="set-post-thumbnail">
                                        <?php if ($thumb_url) : ?>
                                            <div class="shuttle-thumbnail-preview">
                                                <img src="<?php echo esc_url($thumb_url); ?>" alt="">
                                                <p style="margin-top: 10px; color: #7c3aed;"><?php _e('Click to change image', 'bus-booking-manager'); ?></p>
                                            </div>
                                        <?php else : ?>
                                            <div class="shuttle-thumbnail-placeholder">
                                                <span class="dashicons dashicons-images-alt2" style="font-size: 48px; width: 48px; height: 48px; color: #cbd5e1;"></span>
                                                <p><?php _e('Click to set featured image', 'bus-booking-manager'); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <input type="hidden" name="shuttle_thumbnail_id" id="shuttle_thumbnail_id" value="<?php echo esc_attr($thumb_id); ?>">
                                </div>

                                <div class="shuttle-card">
                                    <h3><?php _e('Bus Stops', 'bus-booking-manager'); ?></h3>
                                    <div class="shuttle-tag-selector-container">
                                        <div class="shuttle-selected-tags" id="shuttle_selected_stops_tags">
                                            <?php
                                            foreach ($all_stops as $stop) {
                                                if (in_array($stop->term_id, $selected_stops)) {
                                                    echo '<span class="shuttle-tag" data-id="' . esc_attr($stop->term_id) . '">' . esc_html($stop->name) . ' <i class="dashicons dashicons-dismiss remove-tag"></i></span>';
                                                }
                                            }
                                            ?>
                                        </div>

                                        <div class="shuttle-tag-input-wrapper">
                                            <input type="text" id="shuttle_stop_search" class="form-control" placeholder="<?php _e('Select stops', 'bus-booking-manager'); ?>">
                                            <span class="dashicons dashicons-search search-icon"></span>

                                            <div class="shuttle-tag-dropdown" id="shuttle_stop_dropdown">
                                                <div class="shuttle-dropdown-results" id="shuttle_stop_results">
                                                    <?php if (!empty($all_stops)) : ?>
                                                        <?php foreach ($all_stops as $stop) : ?>
                                                            <div class="shuttle-dropdown-item" data-id="<?php echo esc_attr($stop->term_id); ?>" data-name="<?php echo esc_attr($stop->name); ?>">
                                                                <?php echo esc_html($stop->name); ?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php else : ?>
                                                        <div class="shuttle-no-data">
                                                            <span class="dashicons dashicons-inbox"></span>
                                                            <p><?php _e('No data', 'bus-booking-manager'); ?></p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="shuttle-dropdown-footer">
                                                    <button type="button" class="add-new-tag-btn" id="trigger_add_stop">
                                                        <span class="dashicons dashicons-plus"></span>
                                                        <?php _e('Add New Stop', 'bus-booking-manager'); ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Hidden inputs for actual data submission -->
                                        <div id="shuttle_stops_hidden_inputs">
                                            <?php foreach ($selected_stops as $stop_id) : ?>
                                                <input type="hidden" name="shuttle_stops[]" value="<?php echo esc_attr($stop_id); ?>">
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <!-- Hidden Stop Creation Form (Alternative to inline if we want to follow the image exactly) -->
                                    <div id="inline_add_stop_form" style="display:none; margin-top:10px; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; background: #fff;">
                                        <input type="text" id="new_stop_name_input" class="form-control" placeholder="<?php _e('Enter stop name', 'bus-booking-manager'); ?>" style="margin-bottom:10px;">
                                        <div style="display:flex; gap:10px; justify-content: flex-end;">
                                            <button type="button" class="btn btn-secondary btn-sm" id="cancel_add_stop"><?php _e('Cancel', 'bus-booking-manager'); ?></button>
                                            <button type="button" class="btn btn-primary btn-sm" id="confirm_add_stop"><?php _e('Create Stop', 'bus-booking-manager'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="shuttle-edit-footer">
                            <div style="display: flex; align-items: center; gap: 10px; color: #64748b;">
                                <span><?php _e('Save & next step for route management', 'bus-booking-manager'); ?></span>
                            </div>
                            <div class="shuttle-saving-progress"></div>
                            <button type="button" class="btn btn-primary next-step" data-next="2"><?php echo esc_html($next_step_label); ?> &rarr;</button>
                        </div>
                    </div>

                    <!-- Step 2: Route Management -->
                    <div class="shuttle-step-content <?php echo $current_step === 2 ? 'active' : ''; ?>" id="step-2-content">
                        <div class="shuttle-card">
                            <h3><?php _e('Route Configuration', 'bus-booking-manager'); ?></h3>
                            <p class="description"><?php _e('Configure the route for this shuttle service. Define stops in order.', 'bus-booking-manager'); ?></p>

                            <div class="wbbm_route_fields_grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px;">
                                <div class="form-group">
                                    <label><?php _e('Route Name', 'bus-booking-manager'); ?> <span class="required">*</span></label>
                                    <input type="text" name="wbbm_shuttle_routes[0][name]" class="form-control" value="<?php echo esc_attr($route['name']); ?>" placeholder="e.g. Airport to Downtown" required>
                                    <input type="hidden" name="wbbm_shuttle_routes[0][id]" value="<?php echo esc_attr($route['id']); ?>">
                                </div>
                                <div class="form-group">
                                    <label><?php _e('Route Type', 'bus-booking-manager'); ?></label>
                                    <select name="wbbm_shuttle_routes[0][type]" class="form-control">
                                        <option value="one-way" <?php selected($route['type'], 'one-way'); ?>><?php _e('One Way', 'bus-booking-manager'); ?></option>
                                        <!-- <option value="round-trip" <?php //selected($route['type'], 'round-trip'); 
                                                                        ?>><?php //_e('Round Trip', 'bus-booking-manager'); 
                                                                            ?></option> -->
                                    </select>
                                </div>
                            </div>

                            <div class="wbbm_route_stops_wrapper">
                                <div class="wbbm_route_stops_header" style="display: flex; gap: 15px; margin-bottom: 10px; font-weight: bold; padding: 0 40px;">
                                    <div style="flex: 2;"><?php _e('Location', 'bus-booking-manager'); ?></div>
                                    <div style="flex: 1;"><?php _e('Time Offset (min)', 'bus-booking-manager'); ?></div>
                                    <div style="flex: 1;"><?php _e('Distance (km)', 'bus-booking-manager'); ?></div>
                                    <div style="width: 40px;"></div>
                                </div>

                                <div class="wbbm_route_stops_container" id="wbbm_route_stops_container">
                                    <?php
                                    $stops = isset($route['stops']) ? $route['stops'] : array();
                                    if (!empty($stops)) {
                                        foreach ($stops as $idx => $stop) {
                                            $this->render_route_stop_row(0, $idx, $stop, $all_stops);
                                        }
                                    }
                                    ?>
                                </div>

                                <button type="button" class="btn btn-secondary wbbm_add_route_stop" style="margin-top: 20px;display:flex;align-items: center;gap: 5px">
                                    <span class="dashicons dashicons-plus-alt2"></span> <?php _e('Add New Stop', 'bus-booking-manager'); ?>
                                </button>
                            </div>
                        </div>

                        <div class="shuttle-edit-footer">
                            <div class="shuttle-saving-progress"></div>
                            <button type="button" class="btn btn-secondary prev-step" data-prev="1">&larr; <?php _e('Back', 'bus-booking-manager'); ?></button>
                            <button type="button" class="btn btn-primary next-step" data-next="3"><?php echo esc_html($next_step_label); ?> &rarr;</button>
                        </div>
                    </div>

                    <!-- Step 3: Pricing -->
                    <div class="shuttle-step-content <?php echo $current_step === 3 ? 'active' : ''; ?>" id="step-3-content">
                        <div class="shuttle-card">
                            <h3><?php _e('Pricing & Tickets', 'bus-booking-manager'); ?></h3>
                            <div class="shuttle-pricing-container">
                                <?php $this->render_pricing_matrix($routes, $pricing); ?>
                            </div>
                        </div>
                        <div class="shuttle-edit-footer">
                            <div class="shuttle-saving-progress"></div>
                            <button type="button" class="btn btn-secondary prev-step" data-prev="2">&larr; <?php _e('Back', 'bus-booking-manager'); ?></button>
                            <button type="button" class="btn btn-primary next-step" data-next="4"><?php echo esc_html($next_step_label); ?> &rarr;</button>
                        </div>
                    </div>

                    <!-- Step 4: Schedule -->
                    <div class="shuttle-step-content <?php echo $current_step === 4 ? 'active' : ''; ?>" id="step-4-content">
                        <div class="shuttle-card">
                            <h3><?php _e('Schedule & Availability', 'bus-booking-manager'); ?></h3>
                            <p class="description"><?php _e('Set the departure times and availability days.', 'bus-booking-manager'); ?></p>
                            <div class="shuttle-schedule-container">
                                <?php $this->render_schedule_layout($routes, $schedule); ?>
                            </div>
                        </div>
                        <div class="shuttle-edit-footer">
                            <div class="shuttle-saving-progress"></div>
                            <button type="button" class="btn btn-secondary prev-step" data-prev="3">&larr; <?php _e('Back', 'bus-booking-manager'); ?></button>
                            <button type="submit" class="btn btn-success finish-step"><?php echo esc_html($finish_btn_label); ?> &check;</button>
                        </div>
                    </div>
                </form>
            </div><!-- .shuttle-container -->
        </div>

        <!-- Templates -->
        <script type="text/html" id="wbbm_stop_template">
            <?php $this->render_route_stop_row(0, '{{stop_index}}', array(), $all_stops); ?>
        </script>

        <script type="text/html" id="wbbm_schedule_row_template">
            <?php $this->render_schedule_row('{{route_id}}', '{{time_index}}', array(), '{{direction}}'); ?>
        </script>
    <?php
    }

    /**
     * Render a single stop row
     */
    private function render_route_stop_row($route_index, $stop_index, $data, $all_stops)
    {
        $location = isset($data['location']) ? $data['location'] : '';
        $time_offset = isset($data['time_offset']) ? $data['time_offset'] : '0';
        $distance = isset($data['distance']) ? $data['distance'] : '0';
        $pickup_points = isset($data['pickup_points']) ? $data['pickup_points'] : '';
        $dropoff_points = isset($data['dropoff_points']) ? $data['dropoff_points'] : '';
    ?>
        <div class="wbbm_route_stop_row" data-index="<?php echo esc_attr($stop_index); ?>">
            <span class="wbbm_stop_drag_handle dashicons dashicons-move"></span>

            <div style="flex: 2;">
                <select name="wbbm_shuttle_routes[<?php echo esc_attr($route_index); ?>][stops][<?php echo esc_attr($stop_index); ?>][location]" class="form-control" required>
                    <option value=""><?php _e('Select Stop', 'bus-booking-manager'); ?></option>
                    <?php foreach ($all_stops as $stop) : ?>
                        <option value="<?php echo esc_attr($stop->name); ?>" <?php selected($location, $stop->name); ?>>
                            <?php echo esc_html($stop->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="wbbm_stop_points_toggle"><?php _e('+ Pickup/Drop-off Points', 'bus-booking-manager'); ?></div>

                <div class="wbbm_stop_points_wrapper">
                    <div style="margin-bottom: 10px;">
                        <label><?php _e('Pickup Points (One per line)', 'bus-booking-manager'); ?></label>
                        <textarea name="wbbm_shuttle_routes[<?php echo esc_attr($route_index); ?>][stops][<?php echo esc_attr($stop_index); ?>][pickup_points]" class="form-control" rows="3"><?php echo esc_textarea($pickup_points); ?></textarea>
                    </div>
                    <div>
                        <label><?php _e('Drop-off Points (One per line)', 'bus-booking-manager'); ?></label>
                        <textarea name="wbbm_shuttle_routes[<?php echo esc_attr($route_index); ?>][stops][<?php echo esc_attr($stop_index); ?>][dropoff_points]" class="form-control" rows="3"><?php echo esc_textarea($dropoff_points); ?></textarea>
                    </div>
                </div>
            </div>

            <div style="flex: 1;">
                <input type="number" name="wbbm_shuttle_routes[<?php echo esc_attr($route_index); ?>][stops][<?php echo esc_attr($stop_index); ?>][time_offset]" class="form-control" value="<?php echo esc_attr($time_offset); ?>" min="0">
            </div>

            <div style="flex: 1;">
                <input type="number" name="wbbm_shuttle_routes[<?php echo esc_attr($route_index); ?>][stops][<?php echo esc_attr($stop_index); ?>][distance]" class="form-control" value="<?php echo esc_attr($distance); ?>" min="0" step="0.1">
            </div>

            <div style="width: 40px; text-align: right; display: flex; align-items: flex-end; justify-content: flex-end;">
                <button type="button" class="btn btn-icon btn-outline-danger wbbm_remove_route_stop" title="<?php _e('Remove Stop', 'bus-booking-manager'); ?>" style="margin-top:8px">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Render the pricing matrix HTML
     */
    private function render_pricing_matrix($routes, $pricing)
    {
        if (empty($routes) || (isset($routes[0]['stops']) && count($routes[0]['stops']) < 2)) : ?>
            <div class="shuttle-empty-state">
                <div class="empty-state-icon">
                    <span class="dashicons dashicons-warning"></span>
                </div>
                <h3><?php _e('Route Not Configured', 'bus-booking-manager'); ?></h3>
                <p><?php _e('Please set up at least two stops in the "Route" step before configuring prices.', 'bus-booking-manager'); ?></p>
                <button type="button" class="btn btn-secondary prev-step" data-prev="2"><?php _e('Go to Route Configuration', 'bus-booking-manager'); ?></button>
            </div>
        <?php else : ?>
            <p class="description" style="margin-bottom: 20px;">
                <?php echo esc_html(__('Set prices for each stop-to-stop combination. Leaving a field empty implies service is not available for that segment.', 'bus-booking-manager')); ?>
            </p>

            <?php foreach ($routes as $route) :
                $route_id = isset($route['id']) ? $route['id'] : '';
                if (!$route_id) continue;

                $route_stops = isset($route['stops']) ? $route['stops'] : array();
                if (count($route_stops) < 2) continue;
            ?>
                <div class="shuttle-pricing-block">
                    <div class="shuttle-pricing-header">
                        <h4><?php printf(esc_html__('Route: %s', 'bus-booking-manager'), esc_html($route['name'])); ?></h4>
                    </div>
                    <div class="shuttle-table-wrapper">
                        <table class="shuttle-pricing-table">
                            <thead>
                                <tr>
                                    <th><?php _e('From \ To', 'bus-booking-manager'); ?></th>
                                    <?php for ($i = 1; $i < count($route_stops); $i++) {
                                        echo '<th>' . (isset($route_stops[$i]['location']) ? esc_html($route_stops[$i]['location']) : __('Unknown Stop', 'bus-booking-manager')) . '</th>';
                                    } ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                for ($i = 0; $i < count($route_stops) - 1; $i++) {
                                    $origin = isset($route_stops[$i]['location']) ? $route_stops[$i]['location'] : '';
                                    if (!$origin) continue;

                                    echo '<tr>';
                                    echo '<td class="stop-origin-cell">' . esc_html($origin) . '</td>';

                                    for ($j = 1; $j < count($route_stops); $j++) {
                                        if ($j > $i) {
                                            $dest = isset($route_stops[$j]['location']) ? $route_stops[$j]['location'] : '';
                                            if (!$dest) {
                                                echo '<td>-</td>';
                                                continue;
                                            }

                                            $val_oneway = '';
                                            $val_roundtrip = '';

                                            if (isset($pricing['routes'][$route_id][$origin][$dest])) {
                                                $p = $pricing['routes'][$route_id][$origin][$dest];
                                                if (is_array($p)) {
                                                    $val_oneway = isset($p['oneway']) ? $p['oneway'] : '';
                                                    $val_roundtrip = isset($p['roundtrip']) ? $p['roundtrip'] : '';
                                                } else {
                                                    $val_oneway = $p;
                                                }
                                            }

                                            echo '<td>';

                                            // One Way Price
                                            $field_name_oneway = 'wbbm_shuttle_pricing[routes][' . $route_id . '][' . esc_attr($origin) . '][' . esc_attr($dest) . '][oneway]';
                                            echo '<div class="price-input-group">';
                                            echo '<span class="price-prefix">' . get_woocommerce_currency_symbol() . '</span>';
                                            echo '<input type="number" step="0.01" name="' . $field_name_oneway . '" value="' . esc_attr($val_oneway) . '" placeholder="' . __('One Way', 'bus-booking-manager') . '" class="form-control price-control" style="border-radius:0!important">';
                                            echo '</div>';

                                            // Round Trip Price (if applicable)
                                            if (isset($route['type']) && $route['type'] === 'round-trip') {
                                                $field_name_roundtrip = 'wbbm_shuttle_pricing[routes][' . $route_id . '][' . esc_attr($origin) . '][' . esc_attr($dest) . '][roundtrip]';
                                                echo '<div class="price-input-group mt-2">';
                                                echo '<span class="price-prefix">' . get_woocommerce_currency_symbol() . '</span>';
                                                echo '<input type="number" step="0.01" name="' . $field_name_roundtrip . '" value="' . esc_attr($val_roundtrip) . '" placeholder="' . __('Round Trip', 'bus-booking-manager') . '" class="form-control price-control">';
                                                echo '</div>';
                                            }

                                            echo '</td>';
                                        } else {
                                            echo '<td class="cell-disabled"></td>';
                                        }
                                    }
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif;
    }

    /**
     * Render Schedule Layout
     */
    public function render_schedule_layout($routes, $schedule)
    {
        if (empty($routes)) : ?>
            <div class="shuttle-empty-state">
                <div class="empty-state-icon">
                    <span class="dashicons dashicons-warning"></span>
                </div>
                <h3><?php _e('Route Not Configured', 'bus-booking-manager'); ?></h3>
                <p><?php _e('Please set up at least two stops in the "Route" step before configuring schedules.', 'bus-booking-manager'); ?></p>
                <button type="button" class="btn btn-secondary prev-step" data-prev="2"><?php _e('Go to Route Configuration', 'bus-booking-manager'); ?></button>
            </div>
        <?php else : ?>
            <p class="description" style="margin-bottom: 20px;">
                <?php echo esc_html(__('Define departure times and operating days for each route.', 'bus-booking-manager')); ?>
            </p>

            <?php foreach ($routes as $route) :
                $route_id = isset($route['id']) ? $route['id'] : '';
                if (!$route_id) continue;

                $route_stops = isset($route['stops']) ? $route['stops'] : array();
                if (count($route_stops) < 2) continue;

                $route_schedule = isset($schedule[$route_id]) ? $schedule[$route_id] : array();
            ?>
                <div class="shuttle-schedule-block" data-route-id="<?php echo esc_attr($route_id); ?>">
                    <div class="shuttle-schedule-header">
                        <h4><?php printf(esc_html__('Route: %s', 'bus-booking-manager'), esc_html($route['name'])); ?></h4>
                    </div>

                    <div class="shuttle-schedule-section" data-direction="forward">
                        <div class="schedule-section-header">
                            <h5><span class="dashicons dashicons-arrow-right-alt"></span> <?php _e('Forward Journey Schedule', 'bus-booking-manager'); ?></h5>
                        </div>
                        <div class="shuttle-schedule-rows" data-direction="forward">
                            <?php
                            $forward_schedule = (isset($route_schedule['forward']) && is_array($route_schedule['forward'])) ? $route_schedule['forward'] : array();
                            // Always show at least one row, use the first one if multiple exist (though user wants one)
                            $first_forward = !empty($forward_schedule) ? $forward_schedule[0] : array();
                            $this->render_schedule_row($route_id, 0, $first_forward, 'forward');
                            ?>
                        </div>
                    </div>

                    <?php if (isset($route['type']) && $route['type'] === 'round-trip') : ?>
                        <div class="shuttle-schedule-section mt-4" data-direction="return">
                            <div class="schedule-section-header">
                                <h5><span class="dashicons dashicons-arrow-left-alt"></span> <?php _e('Return Journey Schedule', 'bus-booking-manager'); ?></h5>
                            </div>
                            <div class="shuttle-schedule-rows" data-direction="return">
                                <?php
                                $return_schedule = (isset($route_schedule['return']) && is_array($route_schedule['return'])) ? $route_schedule['return'] : array();
                                $first_return = !empty($return_schedule) ? $return_schedule[0] : array();
                                $this->render_schedule_row($route_id, 0, $first_return, 'return');
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif;
    }

    /**
     * Render Schedule Row
     */
    public function render_schedule_row($route_id, $index, $data, $direction = 'forward')
    {
        $time = isset($data['time']) ? $data['time'] : '';
        $days = isset($data['days']) ? $data['days'] : array();

        $days_of_week = array(
            'mon' => __('Mon', 'bus-booking-manager'),
            'tue' => __('Tue', 'bus-booking-manager'),
            'wed' => __('Wed', 'bus-booking-manager'),
            'thu' => __('Thu', 'bus-booking-manager'),
            'fri' => __('Fri', 'bus-booking-manager'),
            'sat' => __('Sat', 'bus-booking-manager'),
            'sun' => __('Sun', 'bus-booking-manager'),
        );

        $name_prefix = "wbbm_shuttle_schedule[{$route_id}][{$direction}][{$index}]";
        ?>
        <div class="shuttle-schedule-row">
            <div class="schedule-time-col">
                <div class="input-group">
                    <input type="text" name="<?php echo $name_prefix; ?>[time]" class="form-control wbbm_time_picker" data-clocklet value="<?php echo esc_attr($time); ?>" placeholder="08:00 AM" required>
                    <span class="input-group-text"><span class="dashicons dashicons-clock"></span></span>
                </div>
            </div>
            <div class="schedule-days-col">
                <div class="day-selector">
                    <?php foreach ($days_of_week as $key => $label) : ?>
                        <label class="day-chip">
                            <input type="checkbox" name="<?php echo $name_prefix; ?>[days][]" value="<?php echo esc_attr($key); ?>" <?php checked(in_array($key, $days)); ?>>
                            <span><?php echo esc_html($label); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
<?php
    }
}

new ShuttleEditPageClass();
