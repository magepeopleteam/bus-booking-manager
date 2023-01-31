<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

function wbbm_add_custom_fields_text_to_cart_item($cart_item_data, $product_id, $variation_id)
{

//    echo '<pre>';print_r($_POST);die;
    $journey_date = $_POST['journey_date'];
    $is_return = 0;
    $return_discount = 0;
    $return_discount = wbbm_cart_has_opposite_route($_POST['start_stops'], $_POST['end_stops'], $journey_date);

    $product_id = get_post_meta($product_id, 'link_wbbm_bus', true) ? get_post_meta($product_id, 'link_wbbm_bus', true) : $product_id;
    $total_seats = get_post_meta($product_id, 'wbbm_total_seat', true);
    $tp = get_post_meta($product_id, '_price', true);
    $price_arr = get_post_meta($product_id, 'wbbm_bus_prices', true);
    $new = array();
    $user = array();
    $start_stops = sanitize_text_field($_POST['start_stops']);
    $end_stops = sanitize_text_field($_POST['end_stops']);
    $adult_seat = sanitize_text_field($_POST['adult_quantity']);
    
    $total_child_fare_original = 0;
    $total_child_fare_roundtrip = 0;
    $child_fare_original = 0;
    $child_fare_roundtrip = 0;
    if (isset($_POST['child_quantity'])) {
        $total_child_seat = sanitize_text_field($_POST['child_quantity']);
        $child_fare = mage_seat_price($product_id, $start_stops, $end_stops, 'child');
        $child_fare_original = mage_seat_price($product_id, $start_stops, $end_stops, 'child');
        $child_fare_roundtrip = mage_seat_price($product_id, $start_stops, $end_stops, 'child', true);
        if ($return_discount > 0) {
            $total_child_fare = $child_fare_roundtrip * $total_child_seat;

            $total_child_fare_original = $child_fare * $total_child_seat;
            $total_child_fare_roundtrip = $child_fare_roundtrip * $total_child_seat;

            $child_fare = $child_fare_roundtrip;
        } else {
            $total_child_fare = $child_fare * $total_child_seat;

            $total_child_fare_original = $child_fare * $total_child_seat;
            $total_child_fare_roundtrip = $child_fare_roundtrip * $total_child_seat;
        }
    } else {
        $total_child_seat = 0;
        $child_fare = 0;
        $total_child_fare = 0;
    }

    $total_infant_fare_original = 0;
    $total_infant_fare_roundtrip = 0;
    $infant_fare_original = 0;
    $infant_fare_roundtrip = 0;
    if (isset($_POST['infant_quantity'])) {
        $total_infant_seat = sanitize_text_field($_POST['infant_quantity']);
        $infant_fare = mage_seat_price($product_id, $start_stops, $end_stops, 'infant');
        $infant_fare_original = mage_seat_price($product_id, $start_stops, $end_stops, 'infant');
        $infant_fare_roundtrip = mage_seat_price($product_id, $start_stops, $end_stops, 'infant', true);
        if ($return_discount > 0) {
            $total_infant_fare = $infant_fare_roundtrip * $total_infant_seat;

            $total_infant_fare_original = $infant_fare * $total_infant_seat;
            $total_infant_fare_roundtrip = $infant_fare_roundtrip * $total_infant_seat;

            $infant_fare = $infant_fare_roundtrip;
        } else {
            $total_infant_fare = $infant_fare * $total_infant_seat;

            $total_infant_fare_original = $infant_fare * $total_infant_seat;
            $total_infant_fare_roundtrip = $infant_fare_roundtrip * $total_infant_seat;
        }
    } else {
        $total_infant_seat = 0;
        $infant_fare = 0;
        $total_infant_fare = 0;
    }

    $total_entire_fare_original = 0;
    $total_entire_fare_roundtrip = 0;
    $entire_fare_original = 0;
    $entire_fare_roundtrip = 0;
    if (isset($_POST['entire_quantity'])) {
        $total_entire_seat = $total_seats;
        $entire_fare = mage_seat_price($product_id, $start_stops, $end_stops, 'entire');
        $entire_fare_original = mage_seat_price($product_id, $start_stops, $end_stops, 'entire');
        $entire_fare_roundtrip = mage_seat_price($product_id, $start_stops, $end_stops, 'entire', true);
        if ($return_discount > 0) {
            $total_entire_fare = $entire_fare_roundtrip;

            $total_entire_fare_original = $entire_fare;
            $total_entire_fare_roundtrip = $entire_fare_roundtrip;

            $entire_fare = $entire_fare_roundtrip;
        } else {
            $total_entire_fare = $entire_fare;

            $total_entire_fare_original = $entire_fare;
            $total_entire_fare_roundtrip = $entire_fare_roundtrip;
        }
    } else {
        $total_entire_seat = 0;
        $entire_fare = 0;
        $total_entire_fare = 0;
    }

    $total_seat = ($adult_seat + $total_child_seat + $total_infant_seat + $total_entire_seat);
    $main_fare = mage_seat_price($product_id, $start_stops, $end_stops, 'adult');
    $main_fare_original = mage_seat_price($product_id, $start_stops, $end_stops, 'adult');
    $main_fare_roundtrip = mage_seat_price($product_id, $start_stops, $end_stops, 'adult', true);

    if ($return_discount > 0) {
        $total_main_fare = $main_fare_roundtrip * $adult_seat;

        $total_main_fare_original = $main_fare * $adult_seat;
        $total_main_fare_roundtrip = $main_fare_roundtrip * $adult_seat;

        $main_fare = $main_fare_roundtrip;
    } else {
        $total_main_fare = $main_fare * $adult_seat;

        $total_main_fare_original = $main_fare * $adult_seat;
        $total_main_fare_roundtrip = $main_fare_roundtrip * $adult_seat;
    }

    $adult_fare = $total_main_fare;

    $total_fare = ($adult_fare + $total_child_fare + $total_infant_fare + $total_entire_fare);
    $total_fare_roundtrip = ($total_main_fare_roundtrip + $total_child_fare_roundtrip + $total_infant_fare_roundtrip + $total_entire_fare_roundtrip);
    $total_fare_original = ($total_main_fare_original + $total_child_fare_original + $total_infant_fare_original + $total_entire_fare_original);

    $user_start_time = sanitize_text_field($_POST['user_start_time']);
    $bus_start_time = sanitize_text_field($_POST['bus_start_time']);
    $bus_id = sanitize_text_field($_POST['bus_id']);

    // Pickup Point
    if (isset($_POST['mage_pickpoint'])) {
        $pickpoint = $_POST['mage_pickpoint'];
    }else{
        $pickpoint = 'n_a';
    }

    if ($return_discount > 0) {
        $is_return = 1;
    }

    $extra_per_bag_price = get_post_meta($product_id, 'wbbm_extra_bag_price', true);
    $extra_per_bag_price = $extra_per_bag_price ? $extra_per_bag_price : 0;
    $extra_bag_price = 0;
    $custom_reg_additional = array();
    if (isset($_POST['custom_reg_user']) && ($_POST['custom_reg_user']) == 'yes') {


        $wbbm_user_name = (isset($_POST['wbbm_user_name'])) ? wbbm_array_strip($_POST['wbbm_user_name']) : '';
        $wbbm_user_email = (isset($_POST['wbbm_user_email'])) ? wbbm_array_strip($_POST['wbbm_user_email']) : '';
        $wbbm_user_phone = (isset($_POST['wbbm_user_phone'])) ? wbbm_array_strip($_POST['wbbm_user_phone']) : '';
        $wbbm_user_address = (isset($_POST['wbbm_user_address'])) ? wbbm_array_strip($_POST['wbbm_user_address']) : '';
        $wbbm_user_gender = (isset($_POST['wbbm_user_gender'])) ? wbbm_array_strip($_POST['wbbm_user_gender']) : '';
        $wbbm_user_type = (isset($_POST['wbbm_user_type'])) ? wbbm_array_strip($_POST['wbbm_user_type']) : '';
        $wbbm_user_dob = (isset($_POST['wbbm_user_dob'])) ? wbbm_array_strip($_POST['wbbm_user_dob']) : '';
        $wbbm_user_nationality = (isset($_POST['wbbm_user_nationality'])) ? wbbm_array_strip($_POST['wbbm_user_nationality']) : '';
        $wbbm_user_flight_arrival_no = (isset($_POST['wbbm_user_flight_arrival_no'])) ? wbbm_array_strip($_POST['wbbm_user_flight_arrival_no']) : '';
        $wbbm_user_flight_departure_no = (isset($_POST['wbbm_user_flight_departure_no'])) ? wbbm_array_strip($_POST['wbbm_user_flight_departure_no']) : '';
        $bag_qty = (isset($_POST['extra_bag_quantity']) ? $_POST['extra_bag_quantity'] : 0);

        
        $count_user = count($wbbm_user_type);
        for ($iu = 0; $iu < $count_user; $iu++) {

            if($wbbm_user_name) {
                if ($wbbm_user_name[$iu] != '') :
                    $user[$iu]['wbbm_user_name'] = stripslashes(strip_tags($wbbm_user_name[$iu]));
                endif;
            }

            if($wbbm_user_email) {
                if ($wbbm_user_email[$iu] != '') :
                    $user[$iu]['wbbm_user_email'] = stripslashes(strip_tags($wbbm_user_email[$iu]));
                endif;
            }

            if($wbbm_user_phone) {
                if ($wbbm_user_phone[$iu] != '') :
                    $user[$iu]['wbbm_user_phone'] = stripslashes(strip_tags($wbbm_user_phone[$iu]));
                endif;
            }

            if($wbbm_user_address) {
                if ($wbbm_user_address[$iu] != '') :
                    $user[$iu]['wbbm_user_address'] = stripslashes(strip_tags($wbbm_user_address[$iu]));
                endif;
            }

            if($wbbm_user_gender) {
                if ($wbbm_user_gender[$iu] != '') :
                    $user[$iu]['wbbm_user_gender'] = stripslashes(strip_tags($wbbm_user_gender[$iu]));
                endif;
            }
            
            if($wbbm_user_type) {
                if ($wbbm_user_type[$iu] != '') :
                    $user[$iu]['wbbm_user_type'] = stripslashes(strip_tags($wbbm_user_type[$iu]));
                endif;
            }

            if($wbbm_user_dob) {
                if ($wbbm_user_dob[$iu] != '') :
                    $user[$iu]['wbbm_user_dob'] = stripslashes(strip_tags($wbbm_user_dob[$iu]));
                endif;
            }

            if($wbbm_user_nationality) {
                if ($wbbm_user_nationality[$iu] != '') :
                    $user[$iu]['wbbm_user_nationality'] = stripslashes(strip_tags($wbbm_user_nationality[$iu]));
                endif;
            }

            if($wbbm_user_flight_arrival_no) {
                if ($wbbm_user_flight_arrival_no[$iu] != '') :
                    $user[$iu]['wbbm_user_flight_arrival_no'] = stripslashes(strip_tags($wbbm_user_flight_arrival_no[$iu]));
                endif;
            }

            if($wbbm_user_flight_departure_no) {
                if ($wbbm_user_flight_departure_no[$iu] != '') :
                    $user[$iu]['wbbm_user_flight_departure_no'] = stripslashes(strip_tags($wbbm_user_flight_departure_no[$iu]));
                endif;
            }

            if ($bag_qty) {
                if ($bag_qty[$iu] != '') :
                    $user[$iu]['extra_bag_quantity'] = stripslashes(strip_tags($bag_qty[$iu]));
                    $user[$iu]['wbtm_extra_bag_price'] = (float)$extra_per_bag_price;

                    $extra_bag_price += (int) $bag_qty[$iu] * (float)$extra_per_bag_price;
                endif;
            }

            // Additional reg builder field
            $reg_form_arr = maybe_unserialize(get_post_meta($product_id, 'wbbm_attendee_reg_form', true));
            
            if (is_array($reg_form_arr) && sizeof($reg_form_arr) > 0) {
                foreach ($reg_form_arr as $builder) {
                    $custom_reg_additional[$iu][] = array(
                        'name' => $builder['field_label'],
                        'value' => (isset($_POST[$builder['field_id']][$iu]) ? $_POST[$builder['field_id']][$iu] : ''),
                    );
                }
            }

        }
    } else {
        // User type
        $r_counter = 0;
        for ($r = 1; $r <= $adult_seat; $r++) {
            $user[$r_counter]['wbbm_user_type'] = 'adult';
            $r_counter++;
        }

        for ($r = 1; $r <= $total_child_seat; $r++) {
            $user[$r_counter]['wbbm_user_type'] = 'child';
            $r_counter++;
        }

        for ($r = 1; $r <= $total_infant_seat; $r++) {
            $user[$r_counter]['wbbm_user_type'] = 'infant';
            $r_counter++;
        }

        for ($r = 1; $r <= $total_entire_seat; $r++) {
            $user[$r_counter]['wbbm_user_type'] = 'entire';
            $r_counter++;
        }
    }

    // Extra Service
    $extra_service_qty = isset($_POST['extra_service_qty']) ? $_POST['extra_service_qty'] : array();
    $extra_services = get_post_meta($bus_id, 'mep_events_extra_prices', true);   
    if(!empty($extra_services)):
        $es_array = array();
        $c = 0;
        $es_price = 0;
        foreach ($extra_services as $field) {
            $es_array[$c] = array(
                'wbbm_es_name' => $field['option_name'],
                'wbbm_es_price' => (int)$field['option_price'],
                'wbbm_es_input_qty' => $extra_service_qty[$c][0],
                'wbbm_es_available_qty' => (int)$field['option_qty'],            
            );
            $es_price += (int)$field['option_price'] * $extra_service_qty[$c][0];
            $c++;
        }
    endif;
    // Extra Service END

    $total_fare = $total_fare + $es_price + $extra_bag_price;


    $cart_item_data['wbbm_start_stops'] = $start_stops;
    $cart_item_data['wbbm_end_stops'] = $end_stops;
    $cart_item_data['wbbm_journey_date'] = $journey_date;
    $cart_item_data['wbbm_journey_time'] = $user_start_time;
    $cart_item_data['wbbm_bus_time'] = $bus_start_time;
    $cart_item_data['wbbm_total_seats'] = $total_seat;

    $cart_item_data['wbbm_total_adult_qt'] = $adult_seat;
    $cart_item_data['wbbm_total_adult_price'] = $adult_fare;
    $cart_item_data['wbbm_per_adult_price'] = $main_fare;
    $cart_item_data['wbbm_per_adult_price_original'] = $main_fare_original;
    $cart_item_data['wbbm_per_adult_price_roundtrip'] = $main_fare_roundtrip;

    $cart_item_data['wbbm_total_child_qt'] = $total_child_seat;
    $cart_item_data['wbbm_total_child_price'] = $total_child_fare;
    $cart_item_data['wbbm_per_child_price'] = $child_fare;
    $cart_item_data['wbbm_per_child_price_original'] = $child_fare_original;
    $cart_item_data['wbbm_per_child_price_roundtrip'] = $child_fare_roundtrip;

    $cart_item_data['wbbm_total_infant_qt'] = $total_infant_seat;
    $cart_item_data['wbbm_total_infant_price'] = $total_infant_fare;
    $cart_item_data['wbbm_per_infant_price'] = $infant_fare;
    $cart_item_data['wbbm_per_infant_price_original'] = $infant_fare_original;
    $cart_item_data['wbbm_per_infant_price_roundtrip'] = $infant_fare_roundtrip;

    $cart_item_data['wbbm_total_entire_qt'] = $total_entire_seat;
    $cart_item_data['wbbm_total_entire_price'] = $total_entire_fare;
    $cart_item_data['wbbm_per_entire_price'] = $entire_fare;
    $cart_item_data['wbbm_per_entire_price_original'] = $entire_fare_original;
    $cart_item_data['wbbm_per_entire_price_roundtrip'] = $entire_fare_roundtrip;

    $cart_item_data['wbbm_passenger_info'] = $user;
    $cart_item_data['wbbm_passenger_info_additional'] = $custom_reg_additional;
    $cart_item_data['wbbm_extra_services'] = $es_array;
    $cart_item_data['wbbm_tp'] = $total_fare;
    $cart_item_data['wbbm_bus_id'] = $bus_id;
    $cart_item_data['line_total'] = $total_fare;
    $cart_item_data['line_subtotal'] = $total_fare;
    $cart_item_data['quantity'] = $total_seat;
    $cart_item_data['wbbm_id'] = $product_id;
    $cart_item_data['is_return'] = $is_return;
    $cart_item_data['total_fare_original'] = $total_fare_original;
    $cart_item_data['total_fare_roundtrip'] = $total_fare_roundtrip;
    $cart_item_data['pickpoint'] = $pickpoint;


//    echo '<pre>';print_r($cart_item_data);die;
    return $cart_item_data;
}

add_filter('woocommerce_add_cart_item_data', 'wbbm_add_custom_fields_text_to_cart_item', 10, 3);


add_action('woocommerce_before_calculate_totals', 'wbbm_add_custom_price');
function wbbm_add_custom_price($cart_object)
{

    foreach ($cart_object->cart_contents as $key => $value) {
        $eid = $value['wbbm_id'];
        if (get_post_type($eid) == 'wbbm_bus') {
            $t_price = $value['wbbm_tp'];
            
            $p_info = $value['wbbm_passenger_info'];
            $ext_services = ($value['wbbm_extra_services'])?$value['wbbm_extra_services']:[];
            $ext_bag_price = 0;
            $ext_service_price = 0;
            
            foreach ($p_info as $key => $p_inf) {
                if(!empty($p_inf['extra_bag_quantity']) && $p_inf['extra_bag_quantity'] > 0){
                    $ext_bag_price += (float)$p_inf['wbtm_extra_bag_price'] * (int)$p_inf['extra_bag_quantity'];
                }   
            }

            foreach ($ext_services as $ext_service) {
                if(!empty($ext_service['wbbm_es_input_qty']) && $ext_service['wbbm_es_input_qty'] > 0){
                    $ext_service_price += (int)$ext_service['wbbm_es_price'] * (int)$ext_service['wbbm_es_input_qty'];
                }   
            }

            // if($ext_bag_price > 0 || $ext_service_price > 0){
            //     $total = (int)$t_price + (int)$ext_bag_price + (int)$ext_service_price;
            // }
            // else{
            //     $total = (int)$t_price;
            // }
            $total = (float)$t_price;

            $value['data']->set_price($total);
            $value['data']->set_regular_price($total);
            $value['data']->set_sale_price($total);
            $value['data']->set_sold_individually('yes');
            $value['data']->get_price();
        }
    }

}


function wbbm_display_custom_fields_text_cart($item_data, $cart_item)
{
    if(!is_admin()){

        $total_extra_service_qty = 0;
        $eid = $cart_item['wbbm_id'];
        if (get_post_type($eid) == 'wbbm_bus') {
            $total_adult = $cart_item['wbbm_total_adult_qt'];
            $total_adult_fare = $cart_item['wbbm_per_adult_price'];
            $total_child = $cart_item['wbbm_total_child_qt'];
            $total_child_fare = $cart_item['wbbm_per_child_price'];

            $total_infant = $cart_item['wbbm_total_infant_qt'];
            $total_infant_fare = $cart_item['wbbm_per_infant_price'];

            $total_entire = $cart_item['wbbm_total_entire_qt'];
            $total_entire_fare = $cart_item['wbbm_per_entire_price'];

            $pickpoint = $cart_item['pickpoint'];
            $currency = get_woocommerce_currency_symbol();

            $passenger_info = $cart_item['wbbm_passenger_info'];
            $passenger_info_additional = $cart_item['wbbm_passenger_info_additional'];

            $extra_per_bag_price = get_post_meta($eid, 'wbbm_extra_bag_price', true);
            $extra_per_bag_price = $extra_per_bag_price ? $extra_per_bag_price : 0;

            // Check extra service qty
            $wbbm_extra_services = $cart_item['wbbm_extra_services'];
            if($wbbm_extra_services && is_array($wbbm_extra_services)) {
                foreach($wbbm_extra_services as $exs) {
                    $total_extra_service_qty += (int) $exs['wbbm_es_input_qty'];
                }
            }

            if (is_array($passenger_info) && sizeof($passenger_info) > 0) {
                $i = 0;
                foreach ($passenger_info as $_passenger) {
                    ?>
                    <ul class='wbbm-cart-price-table'>
                        <li>
                            <strong>
                                <?php echo wbbm_get_option('wbbm_select_journey_date_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_select_journey_date_text', 'wbbm_label_setting_sec') . ': ' : _e('Journey Date', 'bus-booking-manager') . ': '; ?>
                            </strong>
                            <?php echo ' ' . get_wbbm_datetime($cart_item['wbbm_journey_date'], 'date'); ?>
                        </li>
                        <li>
                            <strong>
                                <?php echo wbbm_get_option('wbbm_starting_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_starting_text', 'wbbm_label_setting_sec') . ': ' : _e('Journey Time', 'bus-booking-manager') . ': '; ?>
                            </strong>
                            <?php echo get_wbbm_datetime($cart_item['wbbm_journey_time'], 'time'); ?>
                        </li>
                        <li>
                            <strong>
                                <?php echo wbbm_get_option('wbbm_boarding_points_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_boarding_points_text', 'wbbm_label_setting_sec') . ': ' : _e('Boarding Point', 'bus-booking-manager') . ': '; ?>
                            </strong>
                            <?php echo $cart_item['wbbm_start_stops']; ?>
                        </li>
                        <li>
                            <strong>
                                <?php echo wbbm_get_option('wbbm_dropping_points_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_dropping_points_text', 'wbbm_label_setting_sec') . ': ' : _e('Dropping Point', 'bus-booking-manager') . ': '; ?>
                            </strong>
                            <?php echo $cart_item['wbbm_end_stops']; ?>
                        </li>

                        <?php if ($pickpoint && $pickpoint != 'n_a'): ?>
                            <li>
                                <strong>
                                    <?php echo __('Pickup Area', 'bus-booking-manager') . ': '; ?>
                                </strong>
                                <?php echo ucfirst($pickpoint); ?> </li>
                        <?php endif; ?>

                        <?php if ($total_adult > 0 && ($_passenger['wbbm_user_type'] == 'adult')): ?>
                            <li>
                                <strong>
                                    <?php echo wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') : _e('Adult', 'bus-booking-manager');
                                    echo " (" . wc_price($total_adult_fare) . " x ".$total_adult.") = " . wc_price($total_adult_fare * $total_adult); ?>
                                </strong>
                            </li>
                        <?php endif; ?>

                        <?php if ($total_child > 0 && ($_passenger['wbbm_user_type'] == 'child')): ?>
                            <li>
                                <strong>
                                    <?php
                                    echo wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') : _e('Child', 'bus-booking-manager');
                                    echo " (" . wc_price($total_child_fare) . " x ".$total_child.") = " . wc_price($total_child_fare * $total_child); ?>
                                </strong>
                            </li>
                        <?php endif; ?>

                        <?php if ($total_infant > 0 && ($_passenger['wbbm_user_type'] == 'infant')): ?>
                            <li>
                                <strong>
                                    <?php
                                    echo wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') : _e('Infant', 'bus-booking-manager');
                                    echo " (" . wc_price($total_infant_fare) . " x ".$total_infant.") = " . wc_price($total_infant_fare * $total_infant); ?>
                                </strong>
                            </li>
                        <?php endif; ?>

                        <?php if ($total_entire = 1 && $total_entire_fare > 0 && ($_passenger['wbbm_user_type'] == 'entire')): ?>
                            <li>
                                <strong>
                                    <?php
                                    echo wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') : _e('Entire Bus', 'bus-booking-manager');
                                    echo " (" . wc_price($total_entire_fare) . " x ".$total_entire.") = " . wc_price($total_entire_fare * $total_entire); ?>
                                </strong>
                            </li>
                        <?php endif; ?>

                        <?php if(!empty($_passenger['extra_bag_quantity']) && $_passenger['extra_bag_quantity'] > 0): ?>
                            <li>
                                <strong>
                                    <?php echo wbbm_get_option('wbbm_extra_bag_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_extra_bag_text', 'wbbm_label_setting_sec') . ': ' : _e('Extra Bag Qty', 'bus-booking-manager') . ': '; ?>
                                </strong>
                                <?php echo $_passenger['extra_bag_quantity']; ?>
                            </li>
                        <?php endif; ?>

                        <?php if(!empty($_passenger['wbtm_extra_bag_price']) && $_passenger['extra_bag_quantity'] > 0): ?>
                            <li>
                                <strong>
                                    <?php echo wbbm_get_option('wbbm_extra_bag_price_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_extra_bag_price_text', 'wbbm_label_setting_sec') . ': ' : _e('Extra Bag Price', 'bus-booking-manager') . ': '; ?>
                                </strong>
                                <?php echo " (" . wc_price($extra_per_bag_price) . " x ".$_passenger['extra_bag_quantity'].") = " . wc_price((int)$_passenger['wbtm_extra_bag_price'] * (int)$_passenger['extra_bag_quantity']); ?>
                            </li>
                        <?php endif; ?>

                        <?php if($total_extra_service_qty && $i == 0): ?>
                            <li>
                                <strong>
                                    <?php echo wbbm_get_option('wbbm_extra_services_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_extra_services_text', 'wbbm_label_setting_sec') . ': ' : _e('Extra Services', 'bus-booking-manager') . ': '; ?>
                                </strong>
                                <ol>
                                    <?php
                                    foreach ($wbbm_extra_services as $value) {
                                        if($value['wbbm_es_input_qty'] > $value['wbbm_es_available_qty']):
                                            ?>
                                            <li><strong><?php echo $value['wbbm_es_name']; ?>: </strong><?php esc_html_e('Input service quantity has exceeded the limit!','bus-booking-manager'); ?></li>
                                        <?php
                                        else:
                                            if($value['wbbm_es_input_qty'] > 0):
                                                ?>
                                                <li><strong><?php echo $value['wbbm_es_name']; ?>: </strong>(<?php echo wc_price($value['wbbm_es_price']); ?> x <?php echo $value['wbbm_es_input_qty']; ?>) = <?php echo wc_price((int)$value['wbbm_es_price'] * (int)$value['wbbm_es_input_qty']); ?></li>
                                            <?php
                                            endif;
                                        endif;
                                    }
                                    ?>
                                </ol>
                            </li>
                        <?php endif; ?>

                        <?php if(!empty($_passenger['wbbm_user_name'])): ?>
                            <li>
                                <strong>
                                    <?php echo wbbm_get_option('wbbm_name_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_name_text', 'wbbm_label_setting_sec') . ': ' : _e('Name', 'bus-booking-manager') . ': '; ?>
                                </strong>
                                <?php echo $_passenger['wbbm_user_name']; ?></li>
                        <?php endif; ?>

                        <?php if(!empty($_passenger['wbbm_user_email'])): ?>
                            <li>
                                <strong>
                                    <?php echo wbbm_get_option('wbbm_email_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_email_text', 'wbbm_label_setting_sec') . ': ' : _e('Email', 'bus-booking-manager') . ': '; ?>
                                </strong>
                                <?php echo $_passenger['wbbm_user_email']; ?></li>
                        <?php endif; ?>

                        <?php if(!empty($_passenger['wbbm_user_phone'])): ?>
                            <li>
                                <strong>
                                    <?php echo wbbm_get_option('wbbm_phone_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_phone_text', 'wbbm_label_setting_sec') . ': ' : _e('Phone', 'bus-booking-manager') . ': '; ?>
                                </strong>
                                <?php echo $_passenger['wbbm_user_phone']; ?>
                            </li>
                        <?php endif; ?>

                        <?php if(!empty($_passenger['wbbm_user_address'])): ?>
                            <li>
                                <strong>
                                    <?php echo wbbm_get_option('wbbm_address_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_address_text', 'wbbm_label_setting_sec') . ': ' : _e('Address', 'bus-booking-manager') . ': '; ?>
                                </strong>
                                <?php echo $_passenger['wbbm_user_address']; ?>
                            </li>
                        <?php endif; ?>

                        <?php if(!empty($_passenger['wbbm_user_gender'])): ?>
                            <li>
                                <strong>
                                    <?php echo wbbm_get_option('wbbm_gender_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_gender_text', 'wbbm_label_setting_sec') . ': ' : _e('Gender', 'bus-booking-manager') . ': '; ?>
                                </strong>
                                <?php echo $_passenger['wbbm_user_gender']; ?>
                            </li>
                        <?php endif; ?>

                        <?php if(!empty($_passenger['wbbm_user_dob'])): ?>
                            <li>
                                <strong>
                                    <?php echo wbbm_get_option('wbbm_dofbirth_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_dofbirth_text', 'wbbm_label_setting_sec') . ': ' : _e('Date of Birth', 'bus-booking-manager') . ': '; ?>
                                </strong>
                                <?php echo $_passenger['wbbm_user_dob']; ?>
                            </li>
                        <?php endif; ?>

                        <?php if(!empty($_passenger['wbbm_user_nationality'])): ?>
                            <li>
                                <strong>
                                    <?php echo wbbm_get_option('wbbm_nationality_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_nationality_text', 'wbbm_label_setting_sec') . ': ' : _e('Nationality', 'bus-booking-manager') . ': '; ?>
                                </strong>
                                <?php echo $_passenger['wbbm_user_nationality']; ?>
                            </li>
                        <?php endif; ?>

                        <?php if(!empty($_passenger['wbbm_user_flight_arrival_no'])): ?>
                            <li>
                                <strong>
                                    <?php echo wbbm_get_option('wbbm_fa_no_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_fa_no_text', 'wbbm_label_setting_sec') . ': ' : _e('Flight Arrival No', 'bus-booking-manager') . ': '; ?>
                                </strong>
                                <?php echo $_passenger['wbbm_user_flight_arrival_no']; ?>
                            </li>
                        <?php endif; ?>

                        <?php if(!empty($_passenger['wbbm_user_flight_departure_no'])): ?>
                            <li>
                                <strong>
                                    <?php echo wbbm_get_option('wbbm_fd_no_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_fd_no_text', 'wbbm_label_setting_sec') . ': ' : _e('Flight Departure No', 'bus-booking-manager') . ': '; ?>
                                </strong>
                                <?php echo $_passenger['wbbm_user_flight_departure_no']; ?>
                            </li>
                        <?php endif; ?>

                        <?php
                        if (is_array($passenger_info_additional) && sizeof($passenger_info_additional) > 0):
                            foreach ($passenger_info_additional[$i] as $builder):
                                ?>
                                <li>
                                    <strong><?php echo $builder['name'] . ': '; ?></strong>
                                    <?php echo $builder['value']; ?>
                                </li>
                            <?php
                            endforeach;
                        endif;
                        ?>
                    </ul>

                    <?php
                    if (($cart_item['line_subtotal'] == $cart_item['total_fare_roundtrip']) && $cart_item['is_return'] == 1):
                        $percent = ($cart_item['total_fare_roundtrip'] * 100) / $cart_item['total_fare_original'];
                        $percent = 100 - $percent;
                        echo '<p style="color:#af7a2d;font-size: 13px;line-height: 1em;"><strong>' . __('Congratulation!', 'bus-booking-manager') . '</strong> <span> ' . __('For a round trip, you got', 'bus-booking-manager') . ' <span style="font-weight:600">' . number_format($percent, 2) . '%</span> ' . __('discount on this trip', 'bus-booking-manager') . '</span></p>';
                    endif;
                    $i++;
                }
            }

        }


    }

    return $item_data;



}

add_filter('woocommerce_get_item_data', 'wbbm_display_custom_fields_text_cart', 10, 2);


function wbbm_add_custom_fields_text_to_order_items($item, $cart_item_key, $values, $order)
{
    $eid = $values['wbbm_id'];
    if (get_post_type($eid) == 'wbbm_bus') {
        $passenger_info             = $values['wbbm_passenger_info'];
        $passenger_info_additional  = $values['wbbm_passenger_info_additional'];
        $wbbm_extra_services        = $values['wbbm_extra_services'];
        $wbbm_start_stops           = $values['wbbm_start_stops'];
        $wbbm_end_stops             = $values['wbbm_end_stops'];
        $wbbm_journey_date          = $values['wbbm_journey_date'];
        $wbbm_journey_time          = $values['wbbm_journey_time'];
        $wbbm_bus_start_time        = $values['wbbm_bus_time'];
        $wbbm_bus_id                = $values['wbbm_bus_id'];
        $total_adult                = $values['wbbm_total_adult_qt'];
        $total_adult_fare           = $values['wbbm_per_adult_price'];
        $total_child                = $values['wbbm_total_child_qt'];
        $total_child_fare           = $values['wbbm_per_child_price'];
        $total_infant               = $values['wbbm_total_infant_qt'];
        $total_infant_fare          = $values['wbbm_per_infant_price'];
        $total_entire               = $values['wbbm_total_entire_qt'];
        $total_entire_fare          = $values['wbbm_per_entire_price'];        
        $total_fare                 = $values['wbbm_tp'];
        if($values['pickpoint'] != 'n_a' && $values['pickpoint'] != 'N_a'):
        $pickpoint                  = ucfirst($values['pickpoint']);
        else:
        $pickpoint                  = '';   
        endif;
        $extra_per_bag_price        = get_post_meta($eid, 'wbbm_extra_bag_price', true);
        $extra_per_bag_price        = $extra_per_bag_price ? $extra_per_bag_price : 0;
        // $timezone                = wp_timezone_string();
        // $timestamp               = strtotime( $wbbm_journey_time . ' '. $timezone);
        // $jtime                   = wp_date( 'H:i A', $timestamp ); 
        $jtime                      = $wbbm_journey_time;

        $adult_label            = wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') : __('Adult','bus-booking-manager');
        $child_label            = wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') : __('Child','bus-booking-manager');
        $infant_label           = wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') : __('Infant','bus-booking-manager');
        $entire_label           = wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') : __('Entire Bus','bus-booking-manager');
        $passenger_info_label   = wbbm_get_option('wbbm_psngrnfo_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_psngrnfo_text', 'wbbm_label_setting_sec') : __('Passenger Information','bus-booking-manager');
        $extra_bag_qty_label    = wbbm_get_option('wbbm_extra_bag_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_extra_bag_text', 'wbbm_label_setting_sec') : __('Extra Bag Qty', 'bus-booking-manager');
        $extra_bag_price_label  = wbbm_get_option('wbbm_extra_bag_price_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_extra_bag_price_text', 'wbbm_label_setting_sec') : __('Extra Bag Price', 'bus-booking-manager');
        $name_label             = wbbm_get_option('wbbm_name_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_name_text', 'wbbm_label_setting_sec') : __('Name', 'bus-booking-manager');
        $email_label            = wbbm_get_option('wbbm_email_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_email_text', 'wbbm_label_setting_sec') : __('Email', 'bus-booking-manager');
        $phone_label            = wbbm_get_option('wbbm_phone_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_phone_text', 'wbbm_label_setting_sec') : __('Phone', 'bus-booking-manager');
        $address_label          = wbbm_get_option('wbbm_address_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_address_text', 'wbbm_label_setting_sec')  : __('Address', 'bus-booking-manager');
        $gender_label           = wbbm_get_option('wbbm_gender_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_gender_text', 'wbbm_label_setting_sec') : __('Gender', 'bus-booking-manager');
        $dofbirth_label         = wbbm_get_option('wbbm_dofbirth_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_dofbirth_text', 'wbbm_label_setting_sec') : __('Date of Birth', 'bus-booking-manager');
        $nationality_label      = wbbm_get_option('wbbm_nationality_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_nationality_text', 'wbbm_label_setting_sec') : __('Nationality', 'bus-booking-manager');
        $fa_no_label            = wbbm_get_option('wbbm_fa_no_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_fa_no_text', 'wbbm_label_setting_sec') : __('Flight Arrival No', 'bus-booking-manager');
        $fd_no_label            = wbbm_get_option('wbbm_fd_no_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_fd_no_text', 'wbbm_label_setting_sec') : __('Flight Departure No', 'bus-booking-manager');
        $extra_services_label   = wbbm_get_option('wbbm_extra_services_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_extra_services_text', 'wbbm_label_setting_sec') : __('Extra Services', 'bus-booking-manager');
        
        $boarding_point_label = __('Boarding Point', 'bus-booking-manager');
        $droping_point_label = __('Dropping Point', 'bus-booking-manager');
        $journey_date_label = __('Journey Date', 'bus-booking-manager');
        $journey_time_label = __('Journey Time', 'bus-booking-manager');

        $item->add_meta_data($boarding_point_label, $wbbm_start_stops);
        $item->add_meta_data($droping_point_label, $wbbm_end_stops);
        $item->add_meta_data($journey_date_label, get_wbbm_datetime($wbbm_journey_date, 'date'));
        $item->add_meta_data($journey_time_label, get_wbbm_datetime($jtime, 'time'));

        $item->add_meta_data('_boarding_point', $wbbm_start_stops);
        $item->add_meta_data('_droping_point', $wbbm_end_stops);
        $item->add_meta_data('_journey_date', $wbbm_journey_date);
        $item->add_meta_data('_journey_time', $jtime);

        if (is_array($passenger_info) && sizeof($passenger_info) > 0) {
            $p_content = '';
            $i = 0;
            foreach ($passenger_info as $_passenger) {

                $p_content .= '<table style="border: 2px dashed #d3d3d3;margin:0;width: 100%;margin-bottom:10px;">';

            if ($total_adult > 0 && ($_passenger['wbbm_user_type'] == 'adult')):
                $p_content .='<tr>';
                $p_content .='<td style="border:1px solid #f5f5f5;">';                
                $p_content .= $adult_label.': '. ' (' . wc_price($total_adult_fare) . ' x '.$total_adult.') = ' . wc_price($total_adult_fare * $total_adult);
                $p_content .='</td>';    
                $p_content .='</tr>';                                
            endif;

            if ($total_child > 0 && ($_passenger['wbbm_user_type'] == 'child')):
                $p_content .='<tr>';
                $p_content .='<td style="border:1px solid #f5f5f5;">';                
                $p_content .= $child_label.': '. ' (' . wc_price($total_child_fare) . ' x '.$total_child.') = ' . wc_price($total_child_fare * $total_child);
                $p_content .='</td>';    
                $p_content .='</tr>';                     
            endif;

            if ($total_infant > 0 && ($_passenger['wbbm_user_type'] == 'infant')):
                $p_content .='<tr>';
                $p_content .='<td style="border:1px solid #f5f5f5;">';                
                $p_content .= $infant_label.': '. ' (' . wc_price($total_infant_fare) . ' x '.$total_infant.') = ' . wc_price($total_infant_fare * $total_infant);
                $p_content .='</td>';    
                $p_content .='</tr>';                    
            endif;

            if ($total_entire = 1 && $total_entire_fare > 0 && ($_passenger['wbbm_user_type'] == 'entire')): 
                $p_content .='<tr>';
                $p_content .='<td style="border:1px solid #f5f5f5;">';                
                $p_content .= $entire_label.': '.$total_entire;
                $p_content .='</td>';    
                $p_content .='</tr>';                
            endif;

            if(!empty($_passenger['extra_bag_quantity']) && $_passenger['extra_bag_quantity'] > 0):
                $p_content .='<tr>';
                $p_content .='<td style="border:1px solid #f5f5f5;">';                
                $p_content .= $extra_bag_qty_label.': '.$_passenger['extra_bag_quantity'];
                $p_content .='</td>';    
                $p_content .='</tr>';                
            endif;

            if(!empty($_passenger['wbtm_extra_bag_price']) && $_passenger['extra_bag_quantity'] > 0):
                $p_content .='<tr>';
                $p_content .='<td style="border:1px solid #f5f5f5;">';                
                $p_content .= $extra_bag_price_label.': '. ' (' . wc_price($extra_per_bag_price) . ' x '.$_passenger['extra_bag_quantity'].') = ' . wc_price((int)$_passenger['extra_bag_quantity'] * (int)$_passenger['wbtm_extra_bag_price']);
                $p_content .='</td>';    
                $p_content .='</tr>';                
            endif;

            if(!empty($_passenger['wbbm_user_name'])):
                $p_content .='<tr>';
                $p_content .='<td style="border:1px solid #f5f5f5;">';                
                $p_content .= $name_label.': '.$_passenger['wbbm_user_name'];
                $p_content .='</td>';    
                $p_content .='</tr>';                
            endif;

            if(!empty($_passenger['wbbm_user_email'])):
                $p_content .='<tr>';
                $p_content .='<td style="border:1px solid #f5f5f5;">';                
                $p_content .= $email_label.': '.$_passenger['wbbm_user_email'];
                $p_content .='</td>';    
                $p_content .='</tr>';                
            endif;
            
            if(!empty($_passenger['wbbm_user_phone'])):
                $p_content .='<tr>';
                $p_content .='<td style="border:1px solid #f5f5f5;">';                
                $p_content .= $phone_label.': '.$_passenger['wbbm_user_phone'];
                $p_content .='</td>';    
                $p_content .='</tr>';                
            endif;
            
            if(!empty($_passenger['wbbm_user_address'])):
                $p_content .='<tr>';
                $p_content .='<td style="border:1px solid #f5f5f5;">';                
                $p_content .= $address_label.': '.$_passenger['wbbm_user_address'];
                $p_content .='</td>';    
                $p_content .='</tr>';                
            endif;            

            if(!empty($_passenger['wbbm_user_gender'])):
                $p_content .='<tr>';
                $p_content .='<td style="border:1px solid #f5f5f5;">';                
                $p_content .= $gender_label.': '.$_passenger['wbbm_user_gender'];
                $p_content .='</td>';    
                $p_content .='</tr>';                
            endif;

            if(!empty($_passenger['wbbm_user_dob'])):
                $p_content .='<tr>';
                $p_content .='<td style="border:1px solid #f5f5f5;">';                
                $p_content .= $dofbirth_label.': '.$_passenger['wbbm_user_dob'];
                $p_content .='</td>';    
                $p_content .='</tr>';                
            endif;

            if(!empty($_passenger['wbbm_user_nationality'])):
                $p_content .='<tr>';
                $p_content .='<td style="border:1px solid #f5f5f5;">';                
                $p_content .= $nationality_label.': '.$_passenger['wbbm_user_nationality'];
                $p_content .='</td>';    
                $p_content .='</tr>';                
            endif;

            if(!empty($_passenger['wbbm_user_flight_arrival_no'])):
                $p_content .='<tr>';
                $p_content .='<td style="border:1px solid #f5f5f5;">';                
                $p_content .= $fa_no_label.': '.$_passenger['wbbm_user_flight_arrival_no'];
                $p_content .='</td>';    
                $p_content .='</tr>';                
            endif;
            
            if(!empty($_passenger['wbbm_user_flight_departure_no'])):
                $p_content .='<tr>';
                $p_content .='<td style="border:1px solid #f5f5f5;">';                
                $p_content .= $fd_no_label.': '.$_passenger['wbbm_user_flight_departure_no'];
                $p_content .='</td>';    
                $p_content .='</tr>';                
            endif;
            
            if (is_array($passenger_info_additional) && sizeof($passenger_info_additional) > 0):
                foreach ($passenger_info_additional[$i] as $builder):
                $p_content .='<tr>';
                $p_content .='<td style="border:1px solid #f5f5f5;">';                
                $p_content .= $builder['name'].': '.$builder['value'];
                $p_content .='</td>';    
                $p_content .='</tr>';                    
                endforeach;
            endif;            

                $p_content .= '</table>';
                $i++;
            }

            if(!empty($wbbm_extra_services)):
            $p_content .= '<table style="border: 2px dashed #d3d3d3;margin:0;width: 100%;margin-bottom:10px;">';
            $p_content .='<tr><th>'.$extra_services_label.'</th></tr>';

            foreach ($wbbm_extra_services as $wbbm_extra_service) {
                if($wbbm_extra_service['wbbm_es_input_qty'] > 0):
                $p_content .='<tr>';
                $p_content .='<td style="border:1px solid #f5f5f5;">';                
                $p_content .= $wbbm_extra_service['wbbm_es_name'].': '. ' (' . wc_price($wbbm_extra_service['wbbm_es_price']) . ' x '.$wbbm_extra_service['wbbm_es_input_qty'].') = ' . wc_price((int)$wbbm_extra_service['wbbm_es_price'] * (int)$wbbm_extra_service['wbbm_es_input_qty']);
                $p_content .='</td>';    
                $p_content .='</tr>';
                endif;
            }
  
            $p_content .= '</table>';
            endif;

            $item->add_meta_data($passenger_info_label, $p_content);
        }                


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
        $item->add_meta_data('_btime', $jtime);
        $item->add_meta_data('_wbbm_passenger_info', $passenger_info );
        $item->add_meta_data('_wbbm_passenger_info_additional', $passenger_info_additional );
        $item->add_meta_data('_wbbm_extra_services', $wbbm_extra_services );
    }
    $item->add_meta_data('_wbbm_bus_id', $eid);
}

add_action('woocommerce_checkout_create_order_line_item', 'wbbm_add_custom_fields_text_to_order_items', 10, 4);

function add_the_date_validation( $passed ) {

    $eid = $_POST['bus_id'];
    if (get_post_type($eid) == 'wbbm_bus') {
        $return = false;
        $boarding_var = $return ? 'bus_end_route' : 'bus_start_route';
        $dropping_var = $return ? 'bus_start_route' : 'bus_end_route';
        $date_var = $return ? 'r_date' : 'j_date';
        $available_seat = wbbm_intermidiate_available_seat(@$_GET[$boarding_var], @$_GET[$dropping_var], wbbm_convert_date_to_php(mage_get_isset($date_var)),$eid);

        $total_booking_seat = $_POST['adult_quantity'] + $_POST['child_quantity']  + $_POST['infant_quantity'] ;

        //echo $available_seat.' '.$total_booking_seat;

        if($available_seat<$total_booking_seat){
            wc_add_notice( __( 'You have booked more than available seats', 'bus-booking-manager' ), 'error' );
            $passed = false;
        }
    }
    return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'add_the_date_validation', 10, 5 );