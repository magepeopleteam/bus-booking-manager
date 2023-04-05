<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.


// add_action('woocommerce_cart_item_removed', 'wbtm_cart_item_removed', 5, 2);
add_action('template_redirect', 'wbbm_cart_item_have_two_way_route', 10);

// Main Function
function wbbm_cart_item_have_two_way_route() {
    global $woocommerce;
    
    if( is_cart() || is_checkout() ) {

        $items = $woocommerce->cart->get_cart();
        if($items) {
            $item_count = count($items);
            foreach($items as $key => $value) {
                if( $value['is_return'] && $item_count == 1 ) { // If cart item is single and has return route
                    wbbm_update_cart_return_price($key, true); // Update Return Price to original
                    
                } elseif( $value['is_return'] == 1 && $item_count > 1 ) { // If cart item is more than 1 and has return route

                    $start = $value['wbbm_start_stops'];
                    $stop = $value['wbbm_end_stops'];
                    $j_date = $value['wbbm_journey_date'];

                    $has_one_way = wbbm_check_has_one_way($start, $stop, $j_date);
                    
                    if(!$has_one_way) {
                        
                        wbbm_update_cart_return_price($key, true); // Update Return Price to original
                    } else {
                        
                        wbbm_update_cart_return_price($key, false); // Update Return Price to return
                    }
                    
                } elseif( $value['is_return'] == 2 && $item_count > 1 ) { // If cart item is more than 1 and has return route (Cart item delete happend)

                    $start = $value['wbbm_start_stops'];
                    $stop = $value['wbbm_end_stops'];
                    $j_date = $value['wbbm_journey_date'];

                    $has_one_way = wbbm_check_has_one_way($start, $stop, $j_date);
                    
                    if(!$has_one_way) {
                        wbbm_update_cart_return_price($key, true); // Update Return Price to original
                    } else {
                        wbbm_update_cart_return_price($key, false); // Update Return Price to return
                    }
                    
                } else {
                    // Nothing to do!
                }
            }
        }

    }
    
}

// Check One way route is exits or not
function wbbm_check_has_one_way($start, $stop, $j_date) {
    global $woocommerce;

    $items = $woocommerce->cart->get_cart();
    $return = null;
    foreach($items as $key => $value) {

        if($value['wbbm_journey_date']) {
            //$cart_j_date = new DateTime($value['wbbm_journey_date']);
        }

        if( !$value['is_return'] || $value['is_return'] == 2 ) {
            // $j_date = new DateTime($j_date);
            // $j_date = (is_object($j_date) ? $j_date : new DateTime($j_date));

            if( ($start == $value['wbbm_end_stops']) && ($stop == $value['wbbm_start_stops']) ) {
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

    if($return) {
        foreach($cart as $id => $cart_item) {
            if($id == $key) {
                $cart_item['line_subtotal']                                 = $cart_item['total_fare_original'];
                $cart_item['wbbm_tp']                                       = $cart_item['total_fare_original'];
                $cart_item['line_total']                                    = $cart_item['total_fare_original'];

                $cart_item['wbbm_per_adult_price']                          = $cart_item['wbbm_per_adult_price_original'];

                $cart_item['wbbm_total_adult_price']                        = $cart_item['wbbm_total_adult_qt'] * $cart_item['wbbm_per_adult_price'];
                if( $cart_item['wbbm_total_child_qt'] > 0 ) {
                    $cart_item['wbbm_per_child_price']                      = $cart_item['wbbm_per_child_price_original'];
                    $cart_item['wbbm_total_child_price']                    = $cart_item['wbbm_total_child_qt'] * $cart_item['wbbm_per_child_price'];
                }
                if( $cart_item['wbbm_total_infant_qt'] > 0 ) {
                    $cart_item['wbbm_per_infant_price']                      = $cart_item['wbbm_per_infant_price_original'];
                    $cart_item['wbbm_total_infant_price']                    = $cart_item['wbbm_total_infant_qt'] * $cart_item['wbbm_per_infant_price'];
                }
                $cart_item['is_return']                                     = 2;

            WC()->cart->cart_contents[$key] = $cart_item;
            break;
            }
        }
        
    } else {
        
        foreach($cart as $id => $cart_item) {
            if($id == $key) {
                $cart_item['line_subtotal']                                 = $cart_item['total_fare_roundtrip'];
                $cart_item['wbbm_tp']                                       = $cart_item['total_fare_roundtrip'];
                $cart_item['line_total']                                    = $cart_item['total_fare_roundtrip'];

                $cart_item['wbbm_per_adult_price']                          = $cart_item['wbbm_per_adult_price_roundtrip'];
                $cart_item['wbbm_total_adult_price']                        = $cart_item['wbbm_total_adult_qt'] * $cart_item['wbbm_per_adult_price_roundtrip'];
                if( $cart_item['wbbm_total_child_qt'] > 0 ) {
                    $cart_item['wbbm_per_child_price']                      = $cart_item['wbbm_per_child_price_roundtrip'];
                    $cart_item['wbbm_total_child_price']                    = $cart_item['wbbm_total_child_qt'] * $cart_item['wbbm_per_child_price_roundtrip'];
                }
                if( $cart_item['wbbm_total_infant_qt'] > 0 ) {
                    $cart_item['wbbm_per_infant_price']                      = $cart_item['wbbm_per_infant_price_roundtrip'];
                    $cart_item['wbbm_total_infant_price']                    = $cart_item['wbbm_total_infant_qt'] * $cart_item['wbbm_per_infant_price_roundtrip'];
                }
                $cart_item['is_return']                                     = 1;

                WC()->cart->cart_contents[$key] = $cart_item;

                if(!$recall) {
                    $this_start = $cart_item['wbbm_start_stops'];
                    $this_stop = $cart_item['wbbm_end_stops'];
                }
            break;
            }
        }

        if(isset($this_start) && isset($this_stop)) {
            foreach($cart as $id => $cart_item) {

                if( $this_start == $cart_item['wbbm_end_stops'] && $this_stop == $cart_item['wbbm_start_stops'] ) {
                    wbbm_update_cart_return_price($id, false, true);
                }

            }
        }
    }

    WC()->cart->set_session(); // Finaly Update Cart
}