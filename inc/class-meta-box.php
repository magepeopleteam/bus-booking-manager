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

        //Save meta
        add_action('save_post', array($this, 'wbbm_single_settings_meta_save'));

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
        <li data-target-tabs="#wbtm_ticket_panel" class="active"><span class="dashicons dashicons-admin-settings"></span><?php echo $cpt_label . ' ' . __('Configuration', 'bus-booking-manager'); ?>
        </li>

        <li data-target-tabs="#wbtm_routing" class="wbtm_routing_tab">
            <span class="dashicons dashicons-location-alt"></span><?php echo $cpt_label . ' ' . __('Routing', 'bus-booking-manager'); ?>
        </li>

        <li data-target-tabs="#wbtm_seat_price" class="ra_seat_price">
            <span class="dashicons dashicons-money-alt"></span><?php echo $cpt_label . ' ' . __('Seat Price', 'bus-booking-manager'); ?>
        </li>

        <li class="ra_pickuppoint_tab" data-target-tabs="#wbtm_pickuppoint"><span class="dashicons dashicons-flag"></span><?php echo $cpt_label . ' ' . __('Pickup Point', 'bus-booking-manager'); ?>
        </li>

        <li data-target-tabs="#wbtm_bus_off_on_date"><span class="dashicons dashicons-calendar-alt"></span><?php echo $cpt_label . ' ' . __('Onday & Offday', 'bus-booking-manager'); ?>
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
        $this->wbbm_bus_ondayoffday()
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


    public function wbbm_single_settings_meta_save($post_id)
    {
        global $post;
        if (
            !isset($_POST['wbbm_single_bus_settings_nonce']) ||
            !wp_verify_nonce($_POST['wbbm_single_bus_settings_nonce'], 'wbbm_single_bus_settings_nonce')
        ) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        /* Bus Price Zero Allow */
        if (isset($_POST['wbbm_price_zero_allow'])) {
            $wbbm_price_zero_allow = strip_tags($_POST['wbbm_price_zero_allow']);
        } else {
            $wbbm_price_zero_allow = 'off';
        }
        $update_seat = update_post_meta($post_id, 'wbbm_price_zero_allow', $wbbm_price_zero_allow);
        /* Bus Sell Off */
        if (isset($_POST['wbbm_sell_off'])) {
            $wbbm_sell_off = strip_tags($_POST['wbbm_sell_off']);
        } else {
            $wbbm_sell_off = 'off';
        }
        update_post_meta($post_id, 'wbbm_sell_off', $wbbm_sell_off);

        /* Bus Seat Available Show */
        if (isset($_POST['wbbm_seat_available'])) {
            $wbbm_seat_available = strip_tags($_POST['wbbm_seat_available']);
        } else {
            $wbbm_seat_available = 'off';
        }
        update_post_meta($post_id, 'wbbm_seat_available', $wbbm_seat_available);

        /* Bus Category, Coach no and Seat */
        $wbbm_bus_category = strip_tags($_POST['wbbm_bus_category']);
        $wbbm_bus_no = strip_tags($_POST['wbbm_bus_no']);
        $wbbm_total_seat = strip_tags($_POST['wbbm_total_seat']);
        $update_seat_stock_status = update_post_meta($post_id, '_manage_stock', 'no');
        $update_price = update_post_meta($post_id, '_price', 0);
        $update_seat5 = update_post_meta($post_id, 'wbbm_bus_no', $wbbm_bus_no);
        $update_seat6 = update_post_meta($post_id, 'wbbm_total_seat', $wbbm_total_seat);
        $update_virtual = update_post_meta($post_id, '_virtual', 'yes');
        $update_bus_category = update_post_meta($post_id, 'wbbm_bus_category', $wbbm_bus_category);






        // Routing
        $bus_boarding_points = array();
        $bus_dropping_points = array();
        $boarding_points = $_POST['wbbm_bus_bp_stops_name'];
        $boarding_time = isset($_POST['wbbm_bus_bp_start_time']) ? $_POST['wbbm_bus_bp_start_time'] : '';
        $dropping_points = $_POST['wbbm_bus_next_stops_name'];
        $dropping_time = isset($_POST['wbbm_bus_next_end_time']) ? $_POST['wbbm_bus_next_end_time'] : '';

        if (!empty($boarding_points)) {
            $i = 0;
            foreach ($boarding_points as $point) {
                if ($point != '') {
                    $bus_bus_bp_stops[$i]['wbbm_bus_bp_stops_name'] = $point;
                    $bus_bus_bp_stops[$i]['wbbm_bus_bp_start_time'] = $boarding_time[$i];
                    $bus_bus_start_time[$i]['wbbm_bus_bp_start_time'] = $boarding_time[$i];
                }
                $i++;
            }
        }

        if (!empty($dropping_points)) {
            $i = 0;
            foreach ($dropping_points as $point) {
                if ($point != '') {
                    $bus_dropping_points[$i]['wbbm_bus_next_stops_name'] = $point;
                    $bus_dropping_points[$i]['wbbm_bus_next_end_time'] = $dropping_time[$i];
                }
                $i++;
            }
        }

        update_post_meta($post_id, 'wbbm_bus_bp_stops', $bus_bus_bp_stops);

        update_post_meta($post_id, 'wbbm_bus_next_stops', $bus_dropping_points);




        // Routing END





        /* Bus Pricing */
        $old = get_post_meta($post_id, 'wbbm_bus_prices', true);
        $new = array();
        $bp_pice_stops = $_POST['wbbm_bus_bp_price_stop'];
        $dp_pice_stops = $_POST['wbbm_bus_dp_price_stop'];
        $the_price = $_POST['wbbm_bus_price'];
        $the_price_roundtrip = $_POST['wbbm_bus_price_roundtrip'];
        $the_price_child = $_POST['wbbm_bus_price_child'];
        $the_price_child_roundtrip = $_POST['wbbm_bus_price_child_roundtrip'];
        $the_price_infant = $_POST['wbbm_bus_price_infant'];
        $the_price_infant_roundtrip = $_POST['wbbm_bus_price_infant_roundtrip'];
        $the_price_entire = $_POST['wbbm_bus_price_entire'];
        $the_price_entire_roundtrip = $_POST['wbbm_bus_price_entire_roundtrip'];
        $order_id = 0;
        if (!empty($bp_pice_stops)) {
            $count = count($bp_pice_stops);
        } else {
            $count = 0;
        }


        for ($i = 0; $i < $count; $i++) {

            if ($bp_pice_stops[$i] != '') :
                $new[$i]['wbbm_bus_bp_price_stop'] = stripslashes(strip_tags($bp_pice_stops[$i]));
            endif;

            if ($dp_pice_stops[$i] != '') :
                $new[$i]['wbbm_bus_dp_price_stop'] = stripslashes(strip_tags($dp_pice_stops[$i]));
            endif;

            if ($the_price[$i] != '') :
                $new[$i]['wbbm_bus_price'] = stripslashes(strip_tags($the_price[$i]));
                $new[$i]['wbbm_bus_price_roundtrip'] = stripslashes(strip_tags($the_price_roundtrip[$i]));
            endif;

            if ($the_price_child[$i] != '') :
                $new[$i]['wbbm_bus_price_child'] = stripslashes(strip_tags($the_price_child[$i]));
                $new[$i]['wbbm_bus_price_child_roundtrip'] = stripslashes(strip_tags($the_price_child_roundtrip[$i]));
            endif;

            if ($the_price_infant[$i] != '') :
                $new[$i]['wbbm_bus_price_infant'] = stripslashes(strip_tags($the_price_infant[$i]));
                $new[$i]['wbbm_bus_price_infant_roundtrip'] = stripslashes(strip_tags($the_price_infant_roundtrip[$i]));
            endif;

            if ($the_price_entire[$i] != '') :
                $new[$i]['wbbm_bus_price_entire'] = stripslashes(strip_tags($the_price_entire[$i]));
                $new[$i]['wbbm_bus_price_entire_roundtrip'] = stripslashes(strip_tags($the_price_entire_roundtrip[$i]));
            endif;
        }

        if (!empty($new) && $new != $old) {
            update_post_meta($post_id, 'wbbm_bus_prices', $new);
        } elseif (empty($new) && $old) {
            delete_post_meta($post_id, 'wbbm_bus_prices', $old);
        }

        // Extra services
        $extra_service_old = get_post_meta($post_id, 'mep_events_extra_prices', true);
        $extra_service_new = array();
        $names = isset($_POST['option_name']) ? $_POST['option_name'] : array();
        $urls = $_POST['option_price'];
        $qty = $_POST['option_qty'];
        $qty_type = $_POST['option_qty_type'];
        $order_id = 0;
        $count = count($names);

        for ($i = 0; $i < $count; $i++) {
            if ($names[$i] != '') :
                $extra_service_new[$i]['option_name'] = stripslashes(strip_tags($names[$i]));
            else :
                continue;
            endif;

            if ($urls[$i] != '') :
                $extra_service_new[$i]['option_price'] = stripslashes(strip_tags($urls[$i]));
            else :
                $extra_service_new[$i]['option_price'] = 0;
            endif;

            if ($qty[$i] != '') :
                $extra_service_new[$i]['option_qty'] = stripslashes(strip_tags($qty[$i]));
            else :
                $extra_service_new[$i]['option_qty'] = 0;
            endif;

            if ($qty_type[$i] != '') :
                $extra_service_new[$i]['option_qty_type'] = stripslashes(strip_tags($qty_type[$i]));
            else :
                $extra_service_new[$i]['option_qty_type'] = 'inputbox';
            endif;
        }

        update_post_meta($post_id, 'mep_events_extra_prices', $extra_service_new ? $extra_service_new : null);
        // Extra services END

        /* Bus Pickuppoint */
        $selected_city_key = 'wbbm_pickpoint_selected_city';
        $selected_pickpoint_name = 'wbbm_selected_pickpoint_name_';
        $selected_pickpoint_time = 'wbbm_selected_pickpoint_time_';

        if (isset($_POST['wbbm_pickpoint_selected_city'])) {
            $selected_city = $_POST['wbbm_pickpoint_selected_city'];

            if (!empty($selected_city)) {

                $selected_city_str = implode(',', $selected_city);

                // If need delete
                $prev_selected_city = get_post_meta($post_id, $selected_city_key, true);
                if ($prev_selected_city) {
                    $prev_selected_city = explode(',', $prev_selected_city);

                    $diff = array_diff($prev_selected_city, $selected_city);
                    if (!empty($diff)) {

                        $diff = array_values($diff);
                        foreach ($diff as $s) {
                            delete_post_meta($post_id, 'wbbm_selected_pickpoint_name_' . $s);
                        }
                    }
                }
                // If need delete END

                update_post_meta($post_id, $selected_city_key, $selected_city_str);

                foreach ($selected_city as $city) {
                    $m_array = array();
                    $i = 0;
                    foreach ($_POST[$selected_pickpoint_name . $city] as $pickpoint) {

                        $m_array[$i] = array(
                            'pickpoint' => $_POST[$selected_pickpoint_name . $city][$i],
                            'time' => $_POST[$selected_pickpoint_time . $city][$i],
                        );

                        $i++;
                    }

                    update_post_meta($post_id, $selected_pickpoint_name . $city, serialize($m_array));
                }
            }
        } else {
            // If need delete
            $prev_selected_city = get_post_meta($post_id, $selected_city_key, true);
            if ($prev_selected_city) {
                $prev_selected_city = explode(',', $prev_selected_city);

                delete_post_meta($post_id, $selected_city_key);

                foreach ($prev_selected_city as $s) {
                    delete_post_meta($post_id, 'wbbm_selected_pickpoint_name_' . $s);
                }
            }
            // If need delete END
        }

        /* Bus Onday & Offday */

        // Offday schedule
        $offday_schedule_array = array();
        $offday_date_from = $_POST['wbtm_od_offdate_from'];
        $offday_date_to = $_POST['wbtm_od_offdate_to'];
        $offday_time_from = $_POST['wbtm_od_offtime_from'];
        $offday_time_to = $_POST['wbtm_od_offtime_to'];

        if (is_array($offday_date_from) && !empty($offday_date_from)) {
            $i = 0;
            for ($i = 0; $i < count($offday_date_from); $i++) {
                if ($offday_date_from[$i] != '') {
                    $offday_schedule_array[$i]['from_date'] = $offday_date_from[$i];
                    $offday_schedule_array[$i]['from_time'] = $offday_time_from[$i];
                    $offday_schedule_array[$i]['to_date']   = $offday_date_to[$i];
                    $offday_schedule_array[$i]['to_time']   = $offday_time_to[$i];
                }
            }
        }
        update_post_meta($post_id, 'wbtm_offday_schedule', $offday_schedule_array);
        // Offday schedule END

        $wbtm_od_start = strip_tags($_POST['wbtm_od_start']);
        $wbtm_od_end = strip_tags($_POST['wbtm_od_end']);
        $wbtm_bus_on_date = $_POST['wbtm_bus_on_date'];

        $od = isset($_POST['weekly_offday']) ? $_POST['weekly_offday'] : '';
        update_post_meta($post_id, 'weekly_offday', $od);

        $show_boarding_points = strip_tags($_POST['show_boarding_points']);
        update_post_meta($post_id, '_virtual', 'yes');
        update_post_meta($post_id, 'wbtm_od_start', $wbtm_od_start);
        update_post_meta($post_id, 'wbtm_od_end', $wbtm_od_end);
        update_post_meta($post_id, 'wbtm_bus_on_date', $wbtm_bus_on_date);
        update_post_meta($post_id, 'show_boarding_points', $show_boarding_points);


        $show_extra_service = isset($_POST['show_extra_service']) ? $_POST['show_extra_service'] : 'no';
        update_post_meta($post_id, 'show_extra_service', $show_extra_service);

        $show_extra_service = isset($_POST['show_pickup_point']) ? $_POST['show_pickup_point'] : 'no';
        update_post_meta($post_id, 'show_pickup_point', $show_extra_service);

        $show_operational_on_day = isset($_POST['show_operational_on_day']) ? $_POST['show_operational_on_day'] : 'no';
        update_post_meta($post_id, 'show_operational_on_day', $show_operational_on_day);

        $show_off_day = isset($_POST['show_off_day']) ? $_POST['show_off_day'] : 'no';
        update_post_meta($post_id, 'show_off_day', $show_off_day);

        // Partial Payment
        do_action('wcpp_partial_settings_saved', $post_id);
        // Partial Payment END
    }

    public function wbbm_remove_sidebar_meta_box()
    {
        remove_meta_box('wbbm_bus_catdiv', 'wbbm_bus', 'side');
        remove_meta_box('wbbm_bus_pickpointdiv', 'wbbm_bus', 'side');
        remove_meta_box('wbbm_bus_stopsdiv', 'wbbm_bus', 'side');
    }
} // Class End

new WBBMMetaBox();
