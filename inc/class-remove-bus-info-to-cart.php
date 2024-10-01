<?php
if (!defined('ABSPATH')) {
    die; // Cannot access pages directly.
}

// Add action to check cart item for two-way route
add_action('template_redirect', 'wbbm_cart_item_have_two_way_route', 10);

// Main Function
function wbbm_cart_item_have_two_way_route() {
    global $woocommerce;

    if (is_cart() || is_checkout()) {
        $items = $woocommerce->cart->get_cart();
        if ($items) {
            $item_count = count($items);
            foreach ($items as $key => $value) {
                // Ensure values are sanitized before processing
                $is_return = isset($value['is_return']) ? intval($value['is_return']) : 0;
                $start = isset($value['wbbm_start_stops']) ? sanitize_text_field($value['wbbm_start_stops']) : '';
                $stop = isset($value['wbbm_end_stops']) ? sanitize_text_field($value['wbbm_end_stops']) : '';
                $j_date = isset($value['wbbm_journey_date']) ? sanitize_text_field($value['wbbm_journey_date']) : '';

                if ($is_return && $item_count == 1) { // If cart item is single and has return route
                    wbbm_update_cart_return_price($key, true); // Update Return Price to original
                } elseif ($is_return == 1 && $item_count > 1) { // If cart item is more than 1 and has return route
                    $has_one_way = wbbm_check_has_one_way($start, $stop, $j_date);

                    if (!$has_one_way) {
                        wbbm_update_cart_return_price($key, true); // Update Return Price to original
                    } else {
                        wbbm_update_cart_return_price($key, false); // Update Return Price to return
                    }
                } elseif ($is_return == 2 && $item_count > 1) { // If cart item is more than 1 and has return route (Cart item delete happened)
                    $has_one_way = wbbm_check_has_one_way($start, $stop, $j_date);

                    if (!$has_one_way) {
                        wbbm_update_cart_return_price($key, true); // Update Return Price to original
                    } else {
                        wbbm_update_cart_return_price($key, false); // Update Return Price to return
                    }
                }
            }
        }
    }
}

// Check One way route is exists or not
function wbbm_check_has_one_way($start, $stop, $j_date) {
    global $woocommerce;

    $items = $woocommerce->cart->get_cart();
    $return = null;

    foreach ($items as $key => $value) {
        $cart_j_date = isset($value['wbbm_journey_date']) ? sanitize_text_field($value['wbbm_journey_date']) : '';

        if (!$value['is_return'] || $value['is_return'] == 2) {
            if ($start == $value['wbbm_end_stops'] && $stop == $value['wbbm_start_stops']) {
                $return = 1;
                break;
            } else {
                $return = 0;
            }
        } else {
            $return = 0;
        }
    }

    return $return;
}

// Update Return Price
function wbbm_update_cart_return_price($key, $return, $recall = false) {
    $cart = WC()->cart->cart_contents;

    if ($return) {
        foreach ($cart as $id => $cart_item) {
            if ($id == $key) {
                $cart_item['line_subtotal'] = isset($cart_item['total_fare_original']) ? floatval($cart_item['total_fare_original']) : 0;
                $cart_item['wbbm_tp'] = $cart_item['line_subtotal'];
                $cart_item['line_total'] = $cart_item['line_subtotal'];

                $cart_item['wbbm_per_adult_price'] = isset($cart_item['wbbm_per_adult_price_original']) ? floatval($cart_item['wbbm_per_adult_price_original']) : 0;

                $cart_item['wbbm_total_adult_price'] = $cart_item['wbbm_total_adult_qt'] * $cart_item['wbbm_per_adult_price'];
                
                if ($cart_item['wbbm_total_child_qt'] > 0) {
                    $cart_item['wbbm_per_child_price'] = isset($cart_item['wbbm_per_child_price_original']) ? floatval($cart_item['wbbm_per_child_price_original']) : 0;
                    $cart_item['wbbm_total_child_price'] = $cart_item['wbbm_total_child_qt'] * $cart_item['wbbm_per_child_price'];
                }
                
                if ($cart_item['wbbm_total_infant_qt'] > 0) {
                    $cart_item['wbbm_per_infant_price'] = isset($cart_item['wbbm_per_infant_price_original']) ? floatval($cart_item['wbbm_per_infant_price_original']) : 0;
                    $cart_item['wbbm_total_infant_price'] = $cart_item['wbbm_total_infant_qt'] * $cart_item['wbbm_per_infant_price'];
                }
                
                $cart_item['is_return'] = 2;

                WC()->cart->cart_contents[$key] = $cart_item;
                break;
            }
        }
    } else {
        foreach ($cart as $id => $cart_item) {
            if ($id == $key) {
                $cart_item['line_subtotal'] = isset($cart_item['total_fare_roundtrip']) ? floatval($cart_item['total_fare_roundtrip']) : 0;
                $cart_item['wbbm_tp'] = $cart_item['line_subtotal'];
                $cart_item['line_total'] = $cart_item['line_subtotal'];

                $cart_item['wbbm_per_adult_price'] = isset($cart_item['wbbm_per_adult_price_roundtrip']) ? floatval($cart_item['wbbm_per_adult_price_roundtrip']) : 0;
                $cart_item['wbbm_total_adult_price'] = $cart_item['wbbm_total_adult_qt'] * $cart_item['wbbm_per_adult_price_roundtrip'];
                
                if ($cart_item['wbbm_total_child_qt'] > 0) {
                    $cart_item['wbbm_per_child_price'] = isset($cart_item['wbbm_per_child_price_roundtrip']) ? floatval($cart_item['wbbm_per_child_price_roundtrip']) : 0;
                    $cart_item['wbbm_total_child_price'] = $cart_item['wbbm_total_child_qt'] * $cart_item['wbbm_per_child_price_roundtrip'];
                }
                
                if ($cart_item['wbbm_total_infant_qt'] > 0) {
                    $cart_item['wbbm_per_infant_price'] = isset($cart_item['wbbm_per_infant_price_roundtrip']) ? floatval($cart_item['wbbm_per_infant_price_roundtrip']) : 0;
                    $cart_item['wbbm_total_infant_price'] = $cart_item['wbbm_total_infant_qt'] * $cart_item['wbbm_per_infant_price_roundtrip'];
                }
                
                $cart_item['is_return'] = 1;

                WC()->cart->cart_contents[$key] = $cart_item;

                if (!$recall) {
                    $this_start = $cart_item['wbbm_start_stops'];
                    $this_stop = $cart_item['wbbm_end_stops'];
                }
                break;
            }
        }

        if (isset($this_start) && isset($this_stop)) {
            foreach ($cart as $id => $cart_item) {
                if ($this_start == $cart_item['wbbm_end_stops'] && $this_stop == $cart_item['wbbm_start_stops']) {
                    wbbm_update_cart_return_price($id, false, true);
                }
            }
        }
    }

    WC()->cart->set_session(); // Finally update cart
}
