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

        // Security checks
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

        // Bus Price Zero Allow
        $wbbm_price_zero_allow = isset($_POST['wbbm_price_zero_allow']) ? sanitize_text_field($_POST['wbbm_price_zero_allow']) : 'off';
        update_post_meta($post_id, 'wbbm_price_zero_allow', $wbbm_price_zero_allow);

        // Bus Sell Off
        $wbbm_sell_off = isset($_POST['wbbm_sell_off']) ? sanitize_text_field($_POST['wbbm_sell_off']) : 'off';
        update_post_meta($post_id, 'wbbm_sell_off', $wbbm_sell_off);

        // Bus Seat Available Show
        $wbbm_seat_available = isset($_POST['wbbm_seat_available']) ? sanitize_text_field($_POST['wbbm_seat_available']) : 'off';
        update_post_meta($post_id, 'wbbm_seat_available', $wbbm_seat_available);

        // Bus Category, Coach no, and Seat
        $wbbm_bus_category = sanitize_text_field($_POST['wbbm_bus_category']);
        wp_set_post_terms($post_id, $wbbm_bus_category, 'wbbm_bus_cat', false);

        $wbbm_bus_no = sanitize_text_field($_POST['wbbm_bus_no']);
        $wbbm_total_seat = sanitize_text_field($_POST['wbbm_total_seat']);
        update_post_meta($post_id, '_manage_stock', 'no');
        update_post_meta($post_id, '_price', 0);
        update_post_meta($post_id, 'wbbm_bus_no', $wbbm_bus_no);
        update_post_meta($post_id, 'wbbm_total_seat', $wbbm_total_seat);
        update_post_meta($post_id, '_virtual', 'yes');
        update_post_meta($post_id, 'wbbm_bus_category', $wbbm_bus_category);

        // Routing
        $bus_boarding_points = [];
        $bus_dropping_points = [];
        $boarding_points = isset($_POST['wbbm_bus_bp_stops_name']) ? $_POST['wbbm_bus_bp_stops_name'] : [];
        $boarding_time = isset($_POST['wbbm_bus_bp_start_time']) ? $_POST['wbbm_bus_bp_start_time'] : [];
        $dropping_points = isset($_POST['wbbm_bus_next_stops_name']) ? $_POST['wbbm_bus_next_stops_name'] : [];
        $dropping_time = isset($_POST['wbbm_bus_next_end_time']) ? $_POST['wbbm_bus_next_end_time'] : [];

        if (!empty($boarding_points)) {
            foreach ($boarding_points as $i => $point) {
                if ($point !== '') {
                    $bus_boarding_points[$i]['wbbm_bus_bp_stops_name'] = sanitize_text_field($point);
                    $bus_boarding_points[$i]['wbbm_bus_bp_start_time'] = sanitize_text_field($boarding_time[$i]);
                }
            }
        }

        if (!empty($dropping_points)) {
            foreach ($dropping_points as $i => $point) {
                if ($point !== '') {
                    $bus_dropping_points[$i]['wbbm_bus_next_stops_name'] = sanitize_text_field($point);
                    $bus_dropping_points[$i]['wbbm_bus_next_end_time'] = sanitize_text_field($dropping_time[$i]);
                }
            }
        }

        $wbbm_features = isset($_POST['wbbm_features']) ? array_map('sanitize_text_field', $_POST['wbbm_features']) : [];

        update_post_meta($post_id, 'wbbm_features', $wbbm_features);
        update_post_meta($post_id, 'wbbm_bus_bp_stops', $bus_boarding_points);
        update_post_meta($post_id, 'wbbm_bus_next_stops', $bus_dropping_points);

        // Bus Pricing
        $old_prices = get_post_meta($post_id, 'wbbm_bus_prices', true);
        $new_prices = [];

        $bp_price_stops = isset($_POST['wbbm_bus_bp_price_stop']) ? $_POST['wbbm_bus_bp_price_stop'] : [];
        $dp_price_stops = isset($_POST['wbbm_bus_dp_price_stop']) ? $_POST['wbbm_bus_dp_price_stop'] : [];
        $the_price = isset($_POST['wbbm_bus_price']) ? $_POST['wbbm_bus_price'] : [];
        $the_price_roundtrip = isset($_POST['wbbm_bus_price_roundtrip']) ? $_POST['wbbm_bus_price_roundtrip'] : [];
        $the_price_child = isset($_POST['wbbm_bus_price_child']) ? $_POST['wbbm_bus_price_child'] : [];
        $the_price_child_roundtrip = isset($_POST['wbbm_bus_price_child_roundtrip']) ? $_POST['wbbm_bus_price_child_roundtrip'] : [];
        $the_price_infant = isset($_POST['wbbm_bus_price_infant']) ? $_POST['wbbm_bus_price_infant'] : [];
        $the_price_infant_roundtrip = isset($_POST['wbbm_bus_price_infant_roundtrip']) ? $_POST['wbbm_bus_price_infant_roundtrip'] : [];
        $the_price_entire = isset($_POST['wbbm_bus_price_entire']) ? $_POST['wbbm_bus_price_entire'] : [];
        $the_price_entire_roundtrip = isset($_POST['wbbm_bus_price_entire_roundtrip']) ? $_POST['wbbm_bus_price_entire_roundtrip'] : [];

        if (!empty($bp_price_stops)) {
            foreach ($bp_price_stops as $i => $bp_stop) {
                if ($bp_stop && $dp_price_stops[$i] && $the_price[$i]) {
                    $new_prices[$i] = [
                        'wbbm_bus_bp_price_stop' => sanitize_text_field($bp_stop),
                        'wbbm_bus_dp_price_stop' => sanitize_text_field($dp_price_stops[$i]),
                        'wbbm_bus_price' => sanitize_text_field($the_price[$i]),
                        'wbbm_bus_price_roundtrip' => sanitize_text_field($the_price_roundtrip[$i]),
                        'wbbm_bus_price_child' => sanitize_text_field($the_price_child[$i]),
                        'wbbm_bus_price_child_roundtrip' => sanitize_text_field($the_price_child_roundtrip[$i]),
                        'wbbm_bus_price_infant' => sanitize_text_field($the_price_infant[$i]),
                        'wbbm_bus_price_infant_roundtrip' => sanitize_text_field($the_price_infant_roundtrip[$i]),
                        'wbbm_bus_price_entire' => sanitize_text_field($the_price_entire[$i]),
                        'wbbm_bus_price_entire_roundtrip' => sanitize_text_field($the_price_entire_roundtrip[$i]),
                    ];
                }
            }
        }

        if (!empty($new_prices) && $new_prices !== $old_prices) {
            update_post_meta($post_id, 'wbbm_bus_prices', $new_prices);
        } elseif (empty($new_prices) && $old_prices) {
            delete_post_meta($post_id, 'wbbm_bus_prices');
        }

        // Extra services
        $extra_service_old = get_post_meta($post_id, 'mep_events_extra_prices', true);
        $extra_service_new = [];

        $names = isset($_POST['option_name']) ? $_POST['option_name'] : [];
        $urls = isset($_POST['option_price']) ? $_POST['option_price'] : [];
        $qty = isset($_POST['option_qty']) ? $_POST['option_qty'] : [];
        $qty_type = isset($_POST['option_qty_type']) ? $_POST['option_qty_type'] : [];

        foreach ($names as $i => $name) {
            if ($name !== '') {
                $extra_service_new[$i] = [
                    'option_name' => sanitize_text_field($name),
                    'option_price' => sanitize_text_field($urls[$i]),
                    'option_qty' => sanitize_text_field($qty[$i]),
                    'option_qty_type' => sanitize_text_field($qty_type[$i]),
                ];
            }
        }

        update_post_meta($post_id, 'mep_events_extra_prices', $extra_service_new ? $extra_service_new : null);

        // Bus Pickuppoint
        $selected_city_key = 'wbbm_pickpoint_selected_city';
        $selected_pickpoint_name = 'wbbm_selected_pickpoint_name_';
        $selected_pickpoint_time = 'wbbm_selected_pickpoint_time_';

        if (isset($_POST['wbbm_pickpoint_selected_city'])) {
            $selected_city = array_map('sanitize_text_field', $_POST['wbbm_pickpoint_selected_city']);

            if (!empty($selected_city)) {
                $selected_city_str = implode(',', $selected_city);
                $prev_selected_city = get_post_meta($post_id, $selected_city_key, true);

                if ($prev_selected_city) {
                    $prev_selected_city = explode(',', $prev_selected_city);
                    $diff = array_diff($prev_selected_city, $selected_city);

                    if (!empty($diff)) {
                        foreach ($diff as $s) {
                            delete_post_meta($post_id, 'wbbm_selected_pickpoint_name_' . sanitize_key($s));
                        }
                    }
                }

                update_post_meta($post_id, $selected_city_key, $selected_city_str);

                foreach ($selected_city as $city) {
                    $city_name_slug = str_replace(' ', '_', strtolower($city));
                    $m_array = [];

                    foreach ($_POST[$selected_pickpoint_name . $city] as $i => $pickpoint) {
                        $m_array[$i] = [
                            'pickpoint' => sanitize_text_field($pickpoint),
                            'time' => sanitize_text_field($_POST[$selected_pickpoint_time . $city][$i]),
                        ];
                    }

                    update_post_meta($post_id, $selected_pickpoint_name . sanitize_key($city_name_slug), $m_array);
                }
            }
        } else {
            $prev_selected_city = get_post_meta($post_id, $selected_city_key, true);

            if ($prev_selected_city) {
                $prev_selected_city = explode(',', $prev_selected_city);
                delete_post_meta($post_id, $selected_city_key);

                foreach ($prev_selected_city as $s) {
                    delete_post_meta($post_id, 'wbbm_selected_pickpoint_name_' . sanitize_key($s));
                }
            }
        }

        // Bus Onday & Offday
        $offday_schedule_array = [];
        $offday_date_from = isset($_POST['wbtm_od_offdate_from']) ? array_map('sanitize_text_field', $_POST['wbtm_od_offdate_from']) : [];
        $offday_date_to = isset($_POST['wbtm_od_offdate_to']) ? array_map('sanitize_text_field', $_POST['wbtm_od_offdate_to']) : [];
        $offday_time_from = isset($_POST['wbtm_od_offtime_from']) ? array_map('sanitize_text_field', $_POST['wbtm_od_offtime_from']) : [];
        $offday_time_to = isset($_POST['wbtm_od_offtime_to']) ? array_map('sanitize_text_field', $_POST['wbtm_od_offtime_to']) : [];

        if (!empty($offday_date_from)) {
            foreach ($offday_date_from as $i => $date_from) {
                if ($date_from !== '') {
                    $offday_schedule_array[$i] = [
                        'from_date' => $date_from,
                        'from_time' => $offday_time_from[$i],
                        'to_date' => $offday_date_to[$i],
                        'to_time' => $offday_time_to[$i],
                    ];
                }
            }
        }

        update_post_meta($post_id, 'wbtm_offday_schedule', $offday_schedule_array);

        $wbtm_od_start = sanitize_text_field($_POST['wbtm_od_start']);
        $wbtm_od_end = sanitize_text_field($_POST['wbtm_od_end']);
        $wbtm_bus_on_date = isset($_POST['wbtm_bus_on_date']) ? array_map('sanitize_text_field', $_POST['wbtm_bus_on_date']) : '';

        $od = isset($_POST['weekly_offday']) ? array_map('sanitize_text_field', $_POST['weekly_offday']) : [];
        update_post_meta($post_id, 'weekly_offday', $od);

        $show_boarding_points = sanitize_text_field($_POST['show_boarding_points']);
        update_post_meta($post_id, '_virtual', 'yes');
        update_post_meta($post_id, 'wbtm_od_start', $wbtm_od_start);
        update_post_meta($post_id, 'wbtm_od_end', $wbtm_od_end);
        update_post_meta($post_id, 'wbtm_bus_on_date', $wbtm_bus_on_date);
        update_post_meta($post_id, 'show_boarding_points', $show_boarding_points);

        $show_extra_service = isset($_POST['show_extra_service']) ? sanitize_text_field($_POST['show_extra_service']) : 'no';
        update_post_meta($post_id, 'show_extra_service', $show_extra_service);

        $show_pickup_point = isset($_POST['show_pickup_point']) ? sanitize_text_field($_POST['show_pickup_point']) : 'no';
        update_post_meta($post_id, 'show_pickup_point', $show_pickup_point);

        $show_operational_on_day = isset($_POST['show_operational_on_day']) ? sanitize_text_field($_POST['show_operational_on_day']) : 'no';
        update_post_meta($post_id, 'show_operational_on_day', $show_operational_on_day);

        $show_off_day = isset($_POST['show_off_day']) ? sanitize_text_field($_POST['show_off_day']) : 'no';
        update_post_meta($post_id, 'show_off_day', $show_off_day);

        // Partial Payment
        do_action('wcpp_partial_settings_saved', $post_id);

        // Tax Settings
        $tax_status = isset($_POST['_tax_status']) ? sanitize_text_field($_POST['_tax_status']) : '';
        $tax_class = isset($_POST['_tax_class']) ? sanitize_text_field($_POST['_tax_class']) : '';
        update_post_meta($post_id, '_tax_status', $tax_status);
        update_post_meta($post_id, '_tax_class', $tax_class);
    }
}
?>
