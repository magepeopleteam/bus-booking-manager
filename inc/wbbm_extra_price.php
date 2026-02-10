<?php
if (!defined('ABSPATH')) {
    die; // Cannot access pages directly.
}

// Add custom price to the cart
add_action('woocommerce_before_calculate_totals', 'wbbm_add_custom_price');
function wbbm_add_custom_price($cart_object)
{
    foreach ($cart_object->cart_contents as $key => $value) {
        $eid = isset($value['wbbm_id']) ? intval($value['wbbm_id']) : 0; // Sanitize ID
        if (get_post_type($eid) == 'wbbm_bus' || get_post_type($eid) == 'wbbm_shuttle') {
            $t_price = isset($value['wbbm_tp']) ? floatval($value['wbbm_tp']) : 0; // Sanitize price
            $total = (float)$t_price;
            $value['data']->set_price($total);
            $value['data']->set_regular_price($total);
            $value['data']->set_sale_price($total);
            $value['data']->set_sold_individually('yes');
        }
    }
}

// Validate checkout
function wbbm_after_checkout_validation()
{
    $cart_items = WC()->cart->get_cart();
    if (sizeof($cart_items) > 0) {
        foreach ($cart_items as $cart_item) {
            $post_id = isset($cart_item['wbbm_id']) ? intval($cart_item['wbbm_id']) : 0; // Sanitize ID
            if (get_post_type($post_id) == 'wbbm_bus') {
                $start_route = isset($cart_item['wbbm_start_stops']) ? sanitize_text_field($cart_item['wbbm_start_stops']) : '';
                $end_route = isset($cart_item['wbbm_end_stops']) ? sanitize_text_field($cart_item['wbbm_end_stops']) : '';
                $date = isset($cart_item['wbbm_journey_date']) ? sanitize_text_field($cart_item['wbbm_journey_date']) : '';
                $available_seat = wbbm_intermidiate_available_seat($start_route, $end_route, wbbm_convert_date_to_php($date), $post_id);

                $adult_qty = isset($cart_item['wbbm_total_adult_qt']) ? intval($cart_item['wbbm_total_adult_qt']) : 0;
                $child_qty = isset($cart_item['wbbm_total_child_qt']) ? intval($cart_item['wbbm_total_child_qt']) : 0;
                $infant_qty = isset($cart_item['wbbm_total_infant_qt']) ? intval($cart_item['wbbm_total_infant_qt']) : 0;
                $wbbm_cart_qty = $adult_qty + $child_qty + $infant_qty;

                if ($available_seat < $wbbm_cart_qty) {
                    WC()->cart->empty_cart();
                    wc_add_notice(__("Sorry, your selected ticket is already booked by another user", 'bus-booking-manager'), 'error');
                }
            }
        }
    }
}
add_action('woocommerce_after_checkout_validation', 'wbbm_after_checkout_validation');

// Add custom fields to order items
function wbbm_add_custom_fields_text_to_order_items($item, $cart_item_key, $values, $order)
{
    $eid = isset($values['wbbm_id']) ? intval($values['wbbm_id']) : 0; // Sanitize ID
    if (get_post_type($eid) == 'wbbm_bus') {
        // Extract values and sanitize
        $passenger_info = isset($values['wbbm_passenger_info']) ? $values['wbbm_passenger_info'] : [];
        $custom_reg_user = isset($values['custom_reg_user']) ? sanitize_text_field($values['custom_reg_user']) : '';
        $passenger_info_additional = isset($values['wbbm_passenger_info_additional']) ? $values['wbbm_passenger_info_additional'] : [];
        $wbbm_extra_services = isset($values['wbbm_extra_services']) ? $values['wbbm_extra_services'] : [];
        $wbbm_start_stops = isset($values['wbbm_start_stops']) ? sanitize_text_field($values['wbbm_start_stops']) : '';
        $wbbm_end_stops = isset($values['wbbm_end_stops']) ? sanitize_text_field($values['wbbm_end_stops']) : '';
        $wbbm_journey_date = isset($values['wbbm_journey_date']) ? sanitize_text_field($values['wbbm_journey_date']) : '';
        $wbbm_journey_time = isset($values['wbbm_journey_time']) ? sanitize_text_field($values['wbbm_journey_time']) : '';
        $wbbm_bus_start_time = isset($values['wbbm_bus_time']) ? sanitize_text_field($values['wbbm_bus_time']) : '';
        $wbbm_bus_id = isset($values['wbbm_bus_id']) ? intval($values['wbbm_bus_id']) : 0; // Sanitize ID
        $total_adult = isset($values['wbbm_total_adult_qt']) ? intval($values['wbbm_total_adult_qt']) : 0;
        $total_adult_fare = isset($values['wbbm_per_adult_price']) ? floatval($values['wbbm_per_adult_price']) : 0;
        $total_child = isset($values['wbbm_total_child_qt']) ? intval($values['wbbm_total_child_qt']) : 0;
        $total_child_fare = isset($values['wbbm_per_child_price']) ? floatval($values['wbbm_per_child_price']) : 0;
        $total_infant = isset($values['wbbm_total_infant_qt']) ? intval($values['wbbm_total_infant_qt']) : 0;
        $total_infant_fare = isset($values['wbbm_per_infant_price']) ? floatval($values['wbbm_per_infant_price']) : 0;
        $total_entire = isset($values['wbbm_total_entire_qt']) ? intval($values['wbbm_total_entire_qt']) : 0;
        $total_entire_fare = isset($values['wbbm_per_entire_price']) ? floatval($values['wbbm_per_entire_price']) : 0;
        $total_fare = isset($values['wbbm_tp']) ? floatval($values['wbbm_tp']) : 0;
        $pickpoint = (!empty($values['pickpoint']) && $values['pickpoint'] !== 'n_a' && $values['pickpoint'] !== 'N_a') ? ucfirst(sanitize_text_field($values['pickpoint'])) : '';

        $extra_per_bag_price = get_post_meta($eid, 'wbbm_extra_bag_price', true);
        $extra_per_bag_price = $extra_per_bag_price ? $extra_per_bag_price : 0;

        // Add boarding and dropping points
        $boarding_point_label = __('Boarding Point', 'bus-booking-manager');
        $droping_point_label = __('Dropping Point', 'bus-booking-manager');
        $journey_date_label = __('Journey Date', 'bus-booking-manager');
        $journey_time_label = __('Journey Time', 'bus-booking-manager');

        $item->add_meta_data($boarding_point_label, $wbbm_start_stops);
        $item->add_meta_data($droping_point_label, $wbbm_end_stops);
        $item->add_meta_data($journey_date_label, wbbm_get_datetime($wbbm_journey_date, 'date'));
        $item->add_meta_data($journey_time_label, wbbm_get_datetime($wbbm_journey_time, 'time'));
        $item->add_meta_data('_boarding_point', $wbbm_start_stops);
        $item->add_meta_data('_droping_point', $wbbm_end_stops);
        $item->add_meta_data('_journey_date', $wbbm_journey_date);
        $item->add_meta_data('_journey_time', $wbbm_journey_time);

        // Build passenger info content
        $p_content = '';
        if ($custom_reg_user == 'no') {
            $p_content .= '<table style="border: 2px dashed #d3d3d3;margin:0;width: 100%;margin-bottom:10px;">';
            if ($total_adult > 0) {
                $p_content .= '<tr><td style="border:1px solid #f5f5f5;">' . __('Adult', 'bus-booking-manager') . ': (' . wc_price($total_adult_fare) . ' x ' . $total_adult . ') = ' . wc_price($total_adult_fare * $total_adult) . '</td></tr>';
            }
            if ($total_child > 0) {
                $p_content .= '<tr><td style="border:1px solid #f5f5f5;">' . __('Child', 'bus-booking-manager') . ': (' . wc_price($total_child_fare) . ' x ' . $total_child . ') = ' . wc_price($total_child_fare * $total_child) . '</td></tr>';
            }
            if ($total_infant > 0) {
                $p_content .= '<tr><td style="border:1px solid #f5f5f5;">' . __('Infant', 'bus-booking-manager') . ': (' . wc_price($total_infant_fare) . ' x ' . $total_infant . ') = ' . wc_price($total_infant_fare * $total_infant) . '</td></tr>';
            }
            $p_content .= '</table>';
        } else {
            // Additional passenger info
            if (is_array($passenger_info) && sizeof($passenger_info) > 0) {
                $i = 0;
                foreach ($passenger_info as $_passenger) {
                    $p_content .= '<table style="border: 2px dashed #d3d3d3;margin:0;width: 100%;margin-bottom:10px;">';
                    if ($total_adult > 0 && ($_passenger['wbbm_user_type'] == 'adult')) {
                        $p_content .= '<tr><td style="border:1px solid #f5f5f5;">' . __('Adult', 'bus-booking-manager') . ': (' . wc_price($total_adult_fare) . ' x ' . $total_adult . ') = ' . wc_price($total_adult_fare * $total_adult) . '</td></tr>';
                    }
                    if ($total_child > 0 && ($_passenger['wbbm_user_type'] == 'child')) {
                        $p_content .= '<tr><td style="border:1px solid #f5f5f5;">' . __('Child', 'bus-booking-manager') . ': (' . wc_price($total_child_fare) . ' x ' . $total_child . ') = ' . wc_price($total_child_fare * $total_child) . '</td></tr>';
                    }
                    if ($total_infant > 0 && ($_passenger['wbbm_user_type'] == 'infant')) {
                        $p_content .= '<tr><td style="border:1px solid #f5f5f5;">' . __('Infant', 'bus-booking-manager') . ': (' . wc_price($total_infant_fare) . ' x ' . $total_infant . ') = ' . wc_price($total_infant_fare * $total_infant) . '</td></tr>';
                    }
                    if ($total_entire == 1 && $total_entire_fare > 0 && ($_passenger['wbbm_user_type'] == 'entire')) {
                        $p_content .= '<tr><td style="border:1px solid #f5f5f5;">' . __('Entire Bus', 'bus-booking-manager') . ': ' . $total_entire . '</td></tr>';
                    }
                    if (!empty($_passenger['extra_bag_quantity']) && $_passenger['extra_bag_quantity'] > 0) {
                        $p_content .= '<tr><td style="border:1px solid #f5f5f5;">' . __('Extra Bag Qty', 'bus-booking-manager') . ': ' . $_passenger['extra_bag_quantity'] . '</td></tr>';
                    }
                    if (!empty($_passenger['wbtm_extra_bag_price']) && $_passenger['extra_bag_quantity'] > 0) {
                        $p_content .= '<tr><td style="border:1px solid #f5f5f5;">' . __('Extra Bag Price', 'bus-booking-manager') . ': (' . wc_price($extra_per_bag_price) . ' x ' . $_passenger['extra_bag_quantity'] . ') = ' . wc_price((int)$_passenger['extra_bag_quantity'] * (int)$_passenger['wbtm_extra_bag_price']) . '</td></tr>';
                    }
                    if (!empty($_passenger['wbbm_user_name'])) {
                        $p_content .= '<tr><td style="border:1px solid #f5f5f5;">' . __('Name', 'bus-booking-manager') . ': ' . sanitize_text_field($_passenger['wbbm_user_name']) . '</td></tr>';
                    }
                    if (!empty($_passenger['wbbm_user_email'])) {
                        $p_content .= '<tr><td style="border:1px solid #f5f5f5;">' . __('Email', 'bus-booking-manager') . ': ' . sanitize_email($_passenger['wbbm_user_email']) . '</td></tr>';
                    }
                    if (!empty($_passenger['wbbm_user_phone'])) {
                        $p_content .= '<tr><td style="border:1px solid #f5f5f5;">' . __('Phone', 'bus-booking-manager') . ': ' . sanitize_text_field($_passenger['wbbm_user_phone']) . '</td></tr>';
                    }
                    if (!empty($_passenger['wbbm_user_address'])) {
                        $p_content .= '<tr><td style="border:1px solid #f5f5f5;">' . __('Address', 'bus-booking-manager') . ': ' . sanitize_text_field($_passenger['wbbm_user_address']) . '</td></tr>';
                    }
                    if (!empty($_passenger['wbbm_user_gender'])) {
                        $p_content .= '<tr><td style="border:1px solid #f5f5f5;">' . __('Gender', 'bus-booking-manager') . ': ' . sanitize_text_field($_passenger['wbbm_user_gender']) . '</td></tr>';
                    }
                    if (!empty($_passenger['wbbm_user_dob'])) {
                        $p_content .= '<tr><td style="border:1px solid #f5f5f5;">' . __('Date of Birth', 'bus-booking-manager') . ': ' . sanitize_text_field($_passenger['wbbm_user_dob']) . '</td></tr>';
                    }
                    if (!empty($_passenger['wbbm_user_nationality'])) {
                        $p_content .= '<tr><td style="border:1px solid #f5f5f5;">' . __('Nationality', 'bus-booking-manager') . ': ' . sanitize_text_field($_passenger['wbbm_user_nationality']) . '</td></tr>';
                    }
                    if (!empty($_passenger['wbbm_user_flight_arrival_no'])) {
                        $p_content .= '<tr><td style="border:1px solid #f5f5f5;">' . __('Flight Arrival No', 'bus-booking-manager') . ': ' . sanitize_text_field($_passenger['wbbm_user_flight_arrival_no']) . '</td></tr>';
                    }
                    if (!empty($_passenger['wbbm_user_flight_departure_no'])) {
                        $p_content .= '<tr><td style="border:1px solid #f5f5f5;">' . __('Flight Departure No', 'bus-booking-manager') . ': ' . sanitize_text_field($_passenger['wbbm_user_flight_departure_no']) . '</td></tr>';
                    }
                    if (is_array($passenger_info_additional) && sizeof($passenger_info_additional) > 0) {
                        foreach ($passenger_info_additional[$i] as $builder) {
                            $p_content .= '<tr><td style="border:1px solid #f5f5f5;">' . sanitize_text_field($builder['name']) . ': ' . sanitize_text_field($builder['value']) . '</td></tr>';
                        }
                    }
                    $p_content .= '</table>';
                    $i++;
                }
                if (!empty($wbbm_extra_services)) {
                    $p_content .= '<table style="border: 2px dashed #d3d3d3;margin:0;width: 100%;margin-bottom:10px;">';
                    $p_content .= '<tr><th>' . __('Extra Services', 'bus-booking-manager') . '</th></tr>';
                    foreach ($wbbm_extra_services as $wbbm_extra_service) {
                        if ($wbbm_extra_service['wbbm_es_input_qty'] > 0) {
                            $p_content .= '<tr><td style="border:1px solid #f5f5f5;">' . sanitize_text_field($wbbm_extra_service['wbbm_es_name']) . ': (' . wc_price($wbbm_extra_service['wbbm_es_price']) . ' x ' . intval($wbbm_extra_service['wbbm_es_input_qty']) . ') = ' . wc_price((int)$wbbm_extra_service['wbbm_es_price'] * (int)$wbbm_extra_service['wbbm_es_input_qty']) . '</td></tr>';
                        }
                    }
                    $p_content .= '</table>';
                }
            }
        }
        $passenger_info_label = 'Passenger info';

        // Add metadata
        $item->add_meta_data($passenger_info_label, $p_content);
        $item->add_meta_data('Pickpoint', $pickpoint);
        $item->add_meta_data('_Adult', $total_adult);
        $item->add_meta_data('_Child', $total_child);
        $item->add_meta_data('_Infant', $total_infant);
        $item->add_meta_data('_Entire', $total_entire);
        $item->add_meta_data('_adult_per_price', $total_adult_fare);
        $item->add_meta_data('_child_per_price', $total_child_fare);
        $item->add_meta_data('_infant_per_price', $total_infant_fare);
        $item->add_meta_data('_entire_per_price', $total_entire_fare);
        $item->add_meta_data('_total_price', $total_fare);
        $item->add_meta_data('_bus_id', $wbbm_bus_id);
        $item->add_meta_data('_btime', $wbbm_bus_start_time);
        $item->add_meta_data('_wbbm_passenger_info', $passenger_info);
        $item->add_meta_data('_wbbm_passenger_info_additional', $passenger_info_additional);
        $item->add_meta_data('_wbbm_extra_services', $wbbm_extra_services);
    } elseif (get_post_type($eid) == 'wbbm_shuttle') {
        $shuttle_id = isset($values['wbbm_shuttle_id']) ? intval($values['wbbm_shuttle_id']) : 0;
        $route_id   = isset($values['wbbm_route_id']) ? sanitize_text_field($values['wbbm_route_id']) : '';
        $pickup     = isset($values['wbbm_start_stops']) ? sanitize_text_field($values['wbbm_start_stops']) : '';
        $dropoff    = isset($values['wbbm_end_stops']) ? sanitize_text_field($values['wbbm_end_stops']) : '';
        $date       = isset($values['wbbm_journey_date']) ? sanitize_text_field($values['wbbm_journey_date']) : '';
        $time       = isset($values['wbbm_journey_time']) ? sanitize_text_field($values['wbbm_journey_time']) : '';
        $passengers = isset($values['wbbm_total_seat']) ? intval($values['wbbm_total_seat']) : 1;
        $total_price = isset($values['wbbm_tp']) ? floatval($values['wbbm_tp']) : 0;

        $item->add_meta_data(__('Shuttle Route', 'bus-booking-manager'), ($pickup . ' to ' . $dropoff), true);
        $item->add_meta_data(__('Journey Date', 'bus-booking-manager'), $date, true);
        $item->add_meta_data(__('Journey Time', 'bus-booking-manager'), $time, true);
        $item->add_meta_data(__('Passengers', 'bus-booking-manager'), $passengers, true);
        $item->add_meta_data('wbbm_id', $eid, true);
        $item->add_meta_data('wbbm_shuttle_id', $shuttle_id, true);
        $item->add_meta_data('wbbm_tp', $total_price, true);

        $pickup_point = isset($values['wbbm_pickup_point']) ? sanitize_text_field($values['wbbm_pickup_point']) : '';
        $dropoff_point = isset($values['wbbm_dropoff_point']) ? sanitize_text_field($values['wbbm_dropoff_point']) : '';
        $item->add_meta_data('_wbbm_pickup_point', $pickup_point, true);
        $item->add_meta_data('_wbbm_dropoff_point', $dropoff_point, true);
    }
    $item->add_meta_data('_wbbm_bus_id', $eid);
}
add_action('woocommerce_checkout_create_order_line_item', 'wbbm_add_custom_fields_text_to_order_items', 10, 4);

// Validate added to cart
function wbbm_add_the_date_validation($passed)
{
    // Verify nonce: accept either the existing 'add_to_cart_custom_nonce' or the
    // new 'mage_book_now_area_nonce' for the book-now template.
    $nonce_custom = isset($_POST['add_to_cart_custom_nonce']) ? sanitize_text_field(wp_unslash($_POST['add_to_cart_custom_nonce'])) : '';
    $nonce_mage = isset($_POST['mage_book_now_area_nonce']) ? sanitize_text_field(wp_unslash($_POST['mage_book_now_area_nonce'])) : '';

    $nonce_shuttle = isset($_POST['wbbm_shuttle_nonce']) ? sanitize_text_field(wp_unslash($_POST['wbbm_shuttle_nonce'])) : '';

    $custom_ok = $nonce_custom && wp_verify_nonce($nonce_custom, 'add_to_cart_custom_action');
    $mage_ok = $nonce_mage && wp_verify_nonce($nonce_mage, 'mage_book_now_area');
    $shuttle_ok = $nonce_shuttle && wp_verify_nonce($nonce_shuttle, 'wbbm_shuttle_add_to_cart');

    if (! $custom_ok && ! $mage_ok && ! $shuttle_ok) {
        // If no valid nonce, we don't return false because we don't want to block
        // other products. But for wbbm_bus or wbbm_shuttle, the next check will handle it.
        if (isset($_POST['bus_id']) || isset($_POST['shuttle_id'])) {
            wc_add_notice(__('Security check failed. Please try again.', 'bus-booking-manager'), 'error');
            return false;
        }
        return $passed;
    }

    if (isset($_POST['bus_id']) || isset($_POST['shuttle_id'])) {
        $eid = isset($_POST['bus_id']) ? intval($_POST['bus_id']) : intval($_POST['shuttle_id']);
        if (get_post_type($eid) == 'wbbm_bus') {
            $return = false;
            $boarding_var = $return ? 'bus_end_route' : 'bus_start_route';
            $dropping_var = $return ? 'bus_start_route' : 'bus_end_route';
            $date_var = $return ? 'r_date' : 'j_date';

            $boarding_var_get = isset($_POST['start_stops']) ? sanitize_text_field(wp_unslash($_POST['start_stops'])) : (isset($_GET[$boarding_var]) ? sanitize_text_field(wp_unslash($_GET[$boarding_var])) : '');
            $dropping_var_get = isset($_POST['end_stops']) ? sanitize_text_field(wp_unslash($_POST['end_stops'])) : (isset($_GET[$dropping_var]) ? sanitize_text_field(wp_unslash($_GET[$dropping_var])) : '');
            $journey_date = isset($_POST['journey_date']) ? sanitize_text_field(wp_unslash($_POST['journey_date'])) : (isset($_GET[$date_var]) ? sanitize_text_field(wp_unslash($_GET[$date_var])) : '');

            if (empty($journey_date)) {
                wc_add_notice(__('Journey date is missing.', 'bus-booking-manager'), 'error');
                return false;
            }

            $available_seat = wbbm_intermidiate_available_seat($boarding_var_get, $dropping_var_get, wbbm_convert_date_to_php($journey_date), $eid);

            // Subtract quantity already in cart for this bus and date
            $cart_qty = wbbm_get_cart_item($eid, $journey_date);
            $available_seat -= $cart_qty;

            $adult_qty = isset($_POST['adult_quantity']) ? intval($_POST['adult_quantity']) : 0;
            $child_qty = isset($_POST['child_quantity']) ? intval($_POST['child_quantity']) : 0;
            $infant_qty = isset($_POST['infant_quantity']) ? intval($_POST['infant_quantity']) : 0;
            $total_booking_seat = $adult_qty + $child_qty + $infant_qty;

            // Prevent adding to cart if already exists for the same date
            if ($cart_qty > 0) {
                wc_add_notice(sprintf(__('You cannot add another "%s" to your cart for the same date.', 'bus-booking-manager'), get_the_title($eid)), 'error');
                return false;
            }

            if ($available_seat < $total_booking_seat) {
                wc_add_notice(__('You have booked more than available seats', 'bus-booking-manager'), 'error');
                $passed = false;
            }
        } elseif (get_post_type($eid) == 'wbbm_shuttle') {
            // Shuttle booking validation at checkout
            $shuttle_id = $eid;
            $date       = isset($_POST['date']) ? sanitize_text_field(wp_unslash($_POST['date'])) : '';
            $route_id   = isset($_POST['route_id']) ? sanitize_text_field(wp_unslash($_POST['route_id'])) : '';
            $pickup     = isset($_POST['pickup']) ? sanitize_text_field(wp_unslash($_POST['pickup'])) : '';
            $dropoff    = isset($_POST['dropoff']) ? sanitize_text_field(wp_unslash($_POST['dropoff'])) : '';
            $passengers = isset($_POST['passengers']) ? absint($_POST['passengers']) : 1;

            if (empty($date) || empty($pickup) || empty($dropoff) || $passengers <= 0) {
                wc_add_notice(__('Invalid shuttle booking data. Please try again.', 'bus-booking-manager'), 'error');
                return false;
            }

            // Recheck seat availability at checkout (race condition protection)
            $available_seats = wbbm_shuttle_available_seats($shuttle_id, $date, $route_id, $pickup, $dropoff);

            if ($available_seats < $passengers) {
                wc_add_notice(
                    sprintf(
                        __('Sorry, only %d seat(s) available for this shuttle. Please reduce the number of passengers or select a different date.', 'bus-booking-manager'),
                        $available_seats
                    ),
                    'error'
                );
                return false;
            }
        }
    }
    return $passed;
}
add_filter('woocommerce_add_to_cart_validation', 'wbbm_add_the_date_validation', 10, 5);
