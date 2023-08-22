<?php
if (!defined('ABSPATH')) exit;  // if direct access

class AdminMetaBoxClass extends CommonClass
{
    public function __construct()
    {

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
        update_post_meta($post_id, '_sold_individually', 'yes');
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
        wp_set_post_terms($post_id, $wbbm_bus_category, 'wbbm_bus_cat', false);

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
        $boarding_points = isset($_POST['wbbm_bus_bp_stops_name']) ? $_POST['wbbm_bus_bp_stops_name'] : '';
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


        $wbbm_features = isset($_POST['wbbm_features']) ? $_POST['wbbm_features'] : '';



       // print_r($wbbm_features);exit;


        if (!empty($wbbm_features)) {
            $i = 0;
            foreach ($wbbm_features as $feature) {
                if ($feature != '') {
                    $post_feature[$i] = $feature;
                }
                $i++;
            }
        }

        update_post_meta($post_id, 'wbbm_features', isset($post_feature)?$post_feature:array());

        update_post_meta($post_id, 'wbbm_bus_bp_stops', isset($bus_bus_bp_stops)?$bus_bus_bp_stops:array());

        update_post_meta($post_id, 'wbbm_bus_next_stops', $bus_dropping_points);




        // Routing END





        /* Bus Pricing */
        $old = get_post_meta($post_id, 'wbbm_bus_prices', true);
        $new = array();
        $bp_pice_stops = $_POST['wbbm_bus_bp_price_stop'];
        $dp_pice_stops = $_POST['wbbm_bus_dp_price_stop'];

        $the_price = $_POST['wbbm_bus_price'];
        $the_price_roundtrip = isset($_POST['wbbm_bus_price_roundtrip']) ? $_POST['wbbm_bus_price_roundtrip'] : '';
        $the_price_child = isset($_POST['wbbm_bus_price_child']) ? $_POST['wbbm_bus_price_child'] : '';
        $the_price_child_roundtrip = isset($_POST['wbbm_bus_price_child_roundtrip']) ? $_POST['wbbm_bus_price_child_roundtrip'] : '';
        $the_price_infant = isset($_POST['wbbm_bus_price_infant']) ? $_POST['wbbm_bus_price_infant'] : '';
        $the_price_infant_roundtrip = isset($_POST['wbbm_bus_price_infant_roundtrip']) ? $_POST['wbbm_bus_price_infant_roundtrip'] : '';
        $the_price_entire = isset($_POST['wbbm_bus_price_entire']) ? $_POST['wbbm_bus_price_entire'] : '';
        $the_price_entire_roundtrip = isset($_POST['wbbm_bus_price_entire_roundtrip']) ? $_POST['wbbm_bus_price_entire_roundtrip'] : '';



        $order_id = 0;
        if (!empty($bp_pice_stops)) {
            $count = count($bp_pice_stops);
        } else {
            $count = 0;
        }


        for ($i = 0; $i < $count; $i++) {

            if($bp_pice_stops[$i] && $dp_pice_stops[$i] && $the_price[$i]){

                $new[$i]['wbbm_bus_bp_price_stop'] = stripslashes(strip_tags($bp_pice_stops[$i]));

                $new[$i]['wbbm_bus_dp_price_stop'] = stripslashes(strip_tags($dp_pice_stops[$i]));

                $new[$i]['wbbm_bus_price'] = stripslashes(strip_tags($the_price[$i]));
                $new[$i]['wbbm_bus_price_roundtrip'] = stripslashes(strip_tags($the_price_roundtrip[$i]));

                $new[$i]['wbbm_bus_price_child'] = stripslashes(strip_tags($the_price_child[$i]));

                $new[$i]['wbbm_bus_price_infant'] = stripslashes(strip_tags($the_price_infant[$i]));
                $new[$i]['wbbm_bus_price_infant_roundtrip'] = stripslashes(strip_tags($the_price_infant_roundtrip[$i]));

                $new[$i]['wbbm_bus_price_entire'] = stripslashes(strip_tags($the_price_entire[$i]));
                $new[$i]['wbbm_bus_price_entire_roundtrip'] = stripslashes(strip_tags($the_price_entire_roundtrip[$i]));
            }
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

                    $city_name_slug = str_replace(' ', '_', strtolower($city));

                   // print_r($m_array);die;

                    update_post_meta($post_id, $selected_pickpoint_name . $city_name_slug, $m_array);
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

        // Tax Settings
        $tax_status = isset($_POST['_tax_status']) ? $_POST['_tax_status'] : '';
        $tax_class = isset($_POST['_tax_class']) ? $_POST['_tax_class'] : '';
        update_post_meta($post_id, '_tax_status', $tax_status);
        update_post_meta($post_id, '_tax_class', $tax_class);
    }




}

