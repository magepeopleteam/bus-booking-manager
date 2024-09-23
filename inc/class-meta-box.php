<?php
if (!defined('ABSPATH')) exit;  // if direct access

class WBBMMetaBox
{
    public function __construct()
    {
        // WBBM Metabox
        add_action('add_meta_boxes', array($this, 'wbbm_add_meta_box_func'));

        // Tab lists
        add_action('wbbm_meta_box_tab_label', array($this, 'wbbm_add_meta_box_tab_label'), 20);

        // Tab Contents
        add_action('wbbm_meta_box_tab_content', array($this, 'wbbm_add_meta_box_tab_content'), 10);

        // Remove meta box from sidebar
        add_action('admin_init', array($this, 'wbbm_remove_sidebar_meta_box'));

        // Bus stop ajax
        add_action('wp_ajax_wbtm_add_bus_stope', [$this, 'wbtm_add_bus_stope']);
        add_action('wp_ajax_nopriv_wbtm_add_bus_stope', [$this, 'wbtm_add_bus_stope']);

        // Bus feature ajax
        add_action('wp_ajax_wbtm_add_bus_feature', [$this, 'wbtm_add_bus_feature']);
        add_action('wp_ajax_nopriv_wbtm_add_bus_feature', [$this, 'wbtm_add_bus_feature']);

        // Bus stop ajax
        add_action('wp_ajax_wbtm_add_pickup', [$this, 'wbtm_add_pickup']);
        add_action('wp_ajax_nopriv_wbtm_add_pickup', [$this, 'wbtm_add_pickup']);

        // Additional actions for save hooks
        add_action('edited_wbbm_bus_feature', [$this, 'save_wbbm_bus_feature']);
        add_action('create_wbbm_bus_feature', [$this, 'save_wbbm_bus_feature'], 10, 2);
    }

    /* Add Bus stop ajax function */
    public function wbtm_add_bus_stope()
    {
        check_ajax_referer('wbbm_nonce', 'security');

        if (isset($_POST['name'])) {
            $name = sanitize_text_field($_POST['name']);
            $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';

            $terms = wp_insert_term($name, 'wbbm_bus_stops', array('description' => $description));

            if (is_wp_error($terms)) {
                wp_send_json_error(array(
                    'text' => $name,
                    'term_id' => 'nothing'
                ));
            } else {
                wp_send_json_success(array(
                    'text' => $name,
                    'term_id' => $terms['term_id']
                ));
            }
        }
        wp_die();
    }

    /* Add Bus feature ajax function */
    public function wbtm_add_bus_feature()
    {
        check_ajax_referer('wbbm_nonce', 'security');

        if (isset($_POST['name'])) {
            $name = sanitize_text_field($_POST['name']);
            $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';

            $terms = wp_insert_term($name, 'wbbm_bus_feature', array('description' => $description));

            if (!is_wp_error($terms) && isset($_POST['wbbm_feature_icon'])) {
                $icon = sanitize_text_field($_POST['wbbm_feature_icon']);
                update_term_meta($terms['term_id'], 'feature_icon', $icon);
            }

            ?>
            <p>
                <label class="customCheckboxLabel">
                    <input type="checkbox" name="wbbm_features[<?php echo esc_attr($terms['term_id']); ?>]" value="<?php echo esc_attr($terms['term_id']); ?>">
                    <span class="customCheckbox">
                        <span class="mR_xs <?php echo esc_attr($icon); ?>"></span>
                        <?php echo esc_html($name); ?>
                    </span>
                </label>
            </p>
            <?php
        }
        wp_die();
    }

    /* Add Pickup ajax function */
    public function wbtm_add_pickup()
    {
        check_ajax_referer('wbbm_nonce', 'security');

        if (isset($_POST['name'])) {
            $name = sanitize_text_field($_POST['name']);
            $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';

            $terms = wp_insert_term($name, 'wbbm_bus_pickpoint', array('description' => $description));

            if (is_wp_error($terms)) {
                wp_send_json_error(array(
                    'text' => $name,
                    'term_id' => 'nothing'
                ));
            } else {
                wp_send_json_success(array(
                    'text' => $name,
                    'term_id' => $terms['term_id']
                ));
            }
        }
        wp_die();
    }

    public function wbbm_add_meta_box_func()
    {
        global $post;

        $cpt_label = sanitize_text_field(wbbm_get_option('wbbm_cpt_label', 'wbbm_general_setting_sec', __('Bus', 'bus-booking-manager')));
        add_meta_box('wbbm-single-settings-meta', $cpt_label . ' ' . __('Settings', 'bus-booking-manager'), array($this, 'wbbm_meta_box_cb'), 'wbbm_bus', 'normal', 'high');
    }

    public function wbbm_meta_box_cb()
    {
        $post_id = get_the_ID();
        $cpt_label = esc_html(wbbm_get_option('wbbm_cpt_label', 'wbbm_general_setting_sec', __('Bus', 'bus-booking-manager')));

        wp_nonce_field('wbbm_single_bus_settings_nonce', 'wbbm_single_bus_settings_nonce');
        ?>

        <div class="mp_event_all_meta_in_tab mp_event_tab_area">
            <div class="mp_tab_menu">
                <ul>
                    <?php do_action('wbbm_meta_box_tab_label', $post_id); ?>
                </ul>
            </div>
            <div class="mp_tab_details">
                <?php do_action('wbbm_meta_box_tab_content', $post_id); ?>
            </div>
        </div>
        <?php
    }

    // Tab lists
    public function wbbm_add_meta_box_tab_label($post_id)
    {
        $cpt_label = esc_html(wbbm_get_option('wbbm_cpt_label', 'wbbm_general_setting_sec', __('Bus', 'bus-booking-manager')));
        ?>

        <li data-target-tabs="#wbtm_ticket_panel" class="active">
            <i class="fas fa-tools"></i> <?php esc_html_e('Configuration', 'bus-booking-manager'); ?>
        </li>

        <li data-target-tabs="#wbtm_routing" class="wbtm_routing_tab">
            <i class="fas fa-route"></i> <?php esc_html_e('Routing', 'bus-booking-manager'); ?>
        </li>

        <li data-target-tabs="#wbtm_seat_price" class="ra_seat_price">
            <i class="fas fa-dollar-sign"></i> <?php esc_html_e('Seat Price', 'bus-booking-manager'); ?>
        </li>

        <li class="ra_pickuppoint_tab" data-target-tabs="#wbtm_pickuppoint">
            <i class="fas fa-map-marker-alt"></i> <?php esc_html_e('Pickup Point', 'bus-booking-manager'); ?>
        </li>

        <li data-target-tabs="#wbtm_bus_off_on_date">
            <i class="far fa-calendar-check"></i> <?php esc_html_e('Onday & Offday', 'bus-booking-manager'); ?>
        </li>

        <li data-target-tabs="#wbmm_bus_features">
            <i class="fas fa-clipboard-list"></i> <?php esc_html_e('Features', 'bus-booking-manager'); ?>
        </li>

        <li data-target-tabs="#wbmm_bus_tax">
            <i class="fas fa-search-dollar"></i> <?php esc_html_e('Tax', 'bus-booking-manager'); ?>
        </li>

        <?php if (is_plugin_active('mage-partial-payment-pro/mage_partial_pro.php')) : ?>
            <li data-target-tabs="#wbtm_bus_partial_payment">
                <i class="fas fa-search-dollar"></i> <?php esc_html_e('Partial Payment', 'bus-booking-manager'); ?>
            </li>
        <?php endif; ?>

        <?php
        do_action('wbbm_after_meta_box_tab_label');
    }

    public function wbbm_add_meta_box_tab_content($post_id)
    {
        $cpt_label = esc_html(wbbm_get_option('wbbm_cpt_label', 'wbbm_general_setting_sec', __('Bus', 'bus-booking-manager')));

        wp_nonce_field('wbbm_single_bus_settings_nonce', 'wbbm_single_bus_settings_nonce');
        $this->wbbm_bus_configuration();
        $this->wbbm_bus_routing($cpt_label);
        $this->wbbm_bus_pricing($post_id, $cpt_label);
        $this->wbbm_bus_pickuppoint($post_id);
        $this->wbbm_bus_ondayoffday();
        $this->wbbm_bus_features();
        $this->wbbm_bus_tax();

        do_action('wbbm_after_meta_box_tab_content');
    }

    // Additional functions remain unchanged, but add sanitization and escaping as shown above.
    
    public function wbbm_remove_sidebar_meta_box()
    {
        remove_meta_box('wbbm_bus_catdiv', 'wbbm_bus', 'side');
        remove_meta_box('wbbm_bus_pickpointdiv', 'wbbm_bus', 'side');
        remove_meta_box('wbbm_bus_stopsdiv', 'wbbm_bus', 'side');
    }
}

new WBBMMetaBox();
