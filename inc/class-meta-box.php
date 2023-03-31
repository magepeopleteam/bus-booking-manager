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

        //Remove meta box from sidebar
        add_action('admin_init', array($this, 'wbbm_remove_sidebar_meta_box'));
        /*Bus stop ajax*/
        add_action('wp_ajax_wbtm_add_bus_stope', [$this, 'wbtm_add_bus_stope']);
        add_action('wp_ajax_nopriv_wbtm_add_bus_stope', [$this, 'wbtm_add_bus_stope']);

        /*Bus stop ajax*/
        add_action('wp_ajax_wbtm_add_pickup', [$this, 'wbtm_add_pickup']);
        add_action('wp_ajax_nopriv_wbtm_add_pickup', [$this, 'wbtm_add_pickup']);
    }

    /*Add Bus stop ajax function*/
    public function wbtm_add_bus_stope()
    {
        if (isset($_POST['name'])) {
            $terms = wp_insert_term($_POST['name'], 'wbbm_bus_stops', $args = array('description' => $_POST['description']));

            echo json_encode(array(
                'text' => $_POST['name'],
                'term_id' => $terms['term_id']
            ));
        }
        die();
    }

    /*Add Pickup ajax function*/
    public function wbtm_add_pickup()
    {
        if (isset($_POST['name'])) {
            $terms = wp_insert_term($_POST['name'], 'wbbm_bus_pickpoint', $args = array('description' => $_POST['description']));

            echo json_encode(array(
                'text' => $_POST['name'],
                'term_id' => $terms['term_id']
            ));
        }
        die();
    }


    public function wbbm_add_meta_box_func()
    {
        global $post;
        /*  $values = maybe_unserialize(get_post_meta($post->ID, 'wbbm_bus_next_stops', true));
       echo '<pre>';
       print_r($values);
       echo '</pre>';

        exit;*/



        $cpt_label = wbbm_get_option('wbbm_cpt_label', 'wbbm_general_setting_sec', __('Bus', 'bus-booking-manager'));
        add_meta_box('wbbm-single-settings-meta', $cpt_label . ' ' . __('Settings', 'bus-booking-manager'), array($this, 'wbbm_meta_box_cb'), 'wbbm_bus', 'normal', 'high');
    }

    public function wbbm_meta_box_cb()
    {
        $post_id = get_the_id();
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


        $cpt_label = wbbm_get_option('wbbm_cpt_label', 'wbbm_general_setting_sec', __('Bus', 'bus-booking-manager'));

        ?>

        <li data-target-tabs="#wbtm_ticket_panel" class="active">
            <span class="dashicons dashicons-admin-settings"></span><?php echo $cpt_label . ' ' . __('Configuration', 'bus-booking-manager'); ?>
        </li>

        <li data-target-tabs="#wbtm_routing" class="wbtm_routing_tab">
            <span class="dashicons dashicons-location-alt"></span><?php echo $cpt_label . ' ' . __('Routing', 'bus-booking-manager'); ?>
        </li>

        <li data-target-tabs="#wbtm_seat_price" class="ra_seat_price">
            <span class="dashicons dashicons-money-alt"></span><?php echo $cpt_label . ' ' . __('Seat Price', 'bus-booking-manager'); ?>
        </li>

        <li class="ra_pickuppoint_tab" data-target-tabs="#wbtm_pickuppoint"><span class="dashicons dashicons-flag"></span><?php echo $cpt_label . ' ' . __('Pickup Point', 'bus-booking-manager'); ?>
        </li>

        <li data-target-tabs="#wbtm_bus_off_on_date">
            <span class="dashicons dashicons-calendar-alt"></span><?php echo $cpt_label . ' ' . __('Onday & Offday', 'bus-booking-manager'); ?>
        </li>

        <li data-target-tabs="#wbmm_bus_features">
            <span class="dashicons dashicons-calendar-alt"></span><?php echo $cpt_label . ' ' . __('Features', 'bus-booking-manager'); ?>
        </li>

        <?php if (is_plugin_active('mage-partial-payment-pro/mage_partial_pro.php')) : ?>
            <li data-target-tabs="#wbtm_bus_partial_payment"><span class="dashicons dashicons-calendar-alt"></span><?php echo $cpt_label . ' ' . __('Partial payment', 'bus-booking-manager'); ?>
            </li>
        <?php endif; ?>

        <?php
        /*Hook:  wbbm_after_meta_box_tab_label */
        do_action('wbbm_after_meta_box_tab_label');
        ?>
    <?php
    }

    public function wbbm_add_meta_box_tab_content($post_id)
    {
        $cpt_label = wbbm_get_option('wbbm_cpt_label', 'wbbm_general_setting_sec', __('Bus', 'bus-booking-manager'));
        wp_nonce_field('wbbm_single_bus_settings_nonce', 'wbbm_single_bus_settings_nonce');
        $this->wbbm_bus_configuration();
        $this->wbbm_bus_routing($cpt_label);
        $this->wbbm_bus_pricing($post_id, $cpt_label);
        $this->wbbm_bus_pickuppoint($post_id);
        $this->wbbm_bus_ondayoffday();
        $this->wbbm_bus_features();

        ?>

        <?php do_action('wbbm_after_meta_box_tab_content'); ?>
        <!-- Partial Payment Setting -->
        <div class="mp_tab_item tab-content" data-tab-item="#wbtm_bus_partial_payment">
            <h3><?php echo $cpt_label . ' ' . __('Partial Payment:', 'bus-booking-manager'); ?></h3>
            <hr />
            <?php $this->wbbm_partial_payment_setting(); ?>
        </div>

        <?php
    }

    public function wbbm_partial_payment_setting()
    {
        global $post;
        $values = get_post_custom($post->ID);
        do_action('wcpp_partial_product_settings', $values);
    }

    public function wbbm_bus_configuration()
    {
        global $post;
        $values = get_post_custom($post->ID);
        $bus_ticket_type = get_post_meta($post->ID, 'wbbm_bus_ticket_type_info', true);
        $cpt_label = wbbm_get_option('wbbm_cpt_label', 'wbbm_general_setting_sec', __('Bus', 'bus-booking-manager'));
        $wbbm_bus_category = get_post_meta($post->ID, 'wbbm_bus_category', true);
        $bus_categories = wbbm_get_bus_categories();
        require_once(dirname(__FILE__) . "/clean/layout/bus_configuration.php");
    }

    public function wbbm_bus_routing($cpt_label)
    {
        global $post;

        $wbtm_bus_next_stops = maybe_unserialize(get_post_meta($post->ID, 'wbbm_bus_next_stops', true));

        $wbbm_bus_bp = get_post_meta($post->ID, 'wbbm_bus_bp_stops', true);
        $values = get_post_custom($post->ID);

        $get_terms_default_attributes = array(
            'taxonomy' => 'wbbm_bus_stops',
            'hide_empty' => false
        );
        $terms = get_terms($get_terms_default_attributes);

        require_once(dirname(__FILE__) . "/clean/layout/bus_routing.php");
    }

    public function wbbm_bus_pricing($post_id, $cpt_label)
    {
        global $post;
        $entire_bus_booking = wbbm_get_option('wbbm_entire_bus_booking_switch', 'wbbm_general_setting_sec');
        $wbbm_bus_prices = get_post_meta($post->ID, 'wbbm_bus_prices', true);
        $values = get_post_custom($post->ID);
        $get_terms_default_attributes = array(
            'taxonomy' => 'wbbm_bus_stops',
            'hide_empty' => false
        );
        $show_extra_service = array_key_exists('show_extra_service', $values) ? $values['show_extra_service'][0] : '';

        require_once(dirname(__FILE__) . "/clean/layout/bus_pricing.php");
    }



    public function wbbm_bus_pickuppoint($cpt_label)
    {
        global $post;
        $bus_stops = get_terms(array(
            'taxonomy' => 'wbbm_bus_stops',
            'hide_empty' => false
        ));


        $boarding_points = maybe_unserialize(get_post_meta($post->ID, 'wbbm_bus_bp_stops', true));


        $boarding_points_array = array();
        if ($boarding_points) {
            $boarding_points = array_column($boarding_points, 'wbbm_bus_bp_stops_name');
            foreach ($boarding_points as $item) {
                $boarding_points_array[] = $item;
            }
        }
        $boarding_points_class = ($boarding_points_array == array()) ? 'ra-display-button' : 'ra-display-boarding-point';




        $bus_pickpoints = get_terms(array(
            'taxonomy' => 'wbbm_bus_pickpoint',
            'hide_empty' => false
        ));
        $pickpoints = '';
        if ($bus_pickpoints) {
            foreach ($bus_pickpoints as $points) {
                $pickpoints .= '<option value="' . $points->slug . '">' . str_replace("'", '', $points->name) . '</option>';
            }
        }

        $values = get_post_custom($post->ID);

        $show_pickup_point = array_key_exists('show_pickup_point', $values) ? $values['show_pickup_point'][0] : '';

        require_once(dirname(__FILE__) . "/clean/layout/bus_pickuppoint.php");
    }




    public function wbbm_bus_ondayoffday()
    {
        global $post;
        $values = get_post_custom($post->ID);
        $cpt_label = wbbm_get_option('wbbm_cpt_label', 'wbbm_general_setting_sec', __('Bus', 'bus-booking-manager'));


        $weekly_offday = array_key_exists('weekly_offday', $values) ? maybe_unserialize($values['weekly_offday'][0]) : '';

        if (!is_array($weekly_offday)) {
            $weekly_offday = array();
        }

        $show_operational_on_day = array_key_exists('show_operational_on_day', $values) ? $values['show_operational_on_day'][0] : '';

        $show_off_day = array_key_exists('show_off_day', $values) ? $values['show_off_day'][0] : '';


        require_once(dirname(__FILE__) . "/clean/layout/bus_ondayoffday.php");
    }

    public function wbbm_bus_features()
    {

        $get_terms_features = array(
            'taxonomy' => 'wbbm_bus_feature',
            'hide_empty' => false
        );
        $feature_terms = get_terms($get_terms_features);



        require_once(dirname(__FILE__) . "/clean/layout/bus_features.php");
    }




    public function wbbm_remove_sidebar_meta_box()
    {
        remove_meta_box('wbbm_bus_catdiv', 'wbbm_bus', 'side');
        remove_meta_box('wbbm_bus_pickpointdiv', 'wbbm_bus', 'side');
        remove_meta_box('wbbm_bus_stopsdiv', 'wbbm_bus', 'side');
    }
} // Class End

new WBBMMetaBox();
