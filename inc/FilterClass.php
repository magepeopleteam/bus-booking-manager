<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

		class FilterClass extends CommonClass{
			public function __construct() {

			}

            function wbbm_load_bus_templates($template)
            {
                global $post;
                if ($post->post_type == "wbbm_bus") {
                    $template_name = 'single-bus.php';
                    $template_path = 'mage-bus-ticket/';
                    $default_path = plugin_dir_path( dirname( __FILE__ ) )  . 'templates/';
                    $template = locate_template(array($template_path . $template_name));
                    if (!$template) :
                        $template = $default_path . $template_name;
                    endif;
                    return $template;
                }
                return $template;
            }


            public function wbbm_add_custom_fields_text_to_cart_item($cart_item_data, $product_id, $variation_id = null)
            {

				$product_id = get_post_meta($product_id, 'link_wbbm_bus', true) ? get_post_meta($product_id, 'link_wbbm_bus', true) : $product_id;
				
                if (get_post_type($product_id) == "wbbm_bus") {


                    $journey_date = $_POST['journey_date'];
                    $is_return = 0;
                    $return_discount = 0;
                    $return_discount = wbbm_cart_has_opposite_route($_POST['start_stops'], $_POST['end_stops'], $journey_date);

                    
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
                        $total_child_seat = (int) sanitize_text_field($_POST['child_quantity']);
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
                        $total_infant_seat = (int) sanitize_text_field($_POST['infant_quantity']);
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
                    $es_price = 0;
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
                    $es_array = array();
                    $es_price = 0;

                    if(isset($_POST['extra_service_qty'])) {

                        $extra_service_qty =  $_POST['extra_service_qty'];
                        $extra_services = get_post_meta($bus_id, 'mep_events_extra_prices', true);

                        if (!empty($extra_services)) :
                            $c = 0;

                            foreach ($extra_services as $key => $field) {

                                    $es_array[$c] = array(
                                        'wbbm_es_name' => $field['option_name'],
                                        'wbbm_es_price' => (float)$field['option_price'],
                                        'wbbm_es_input_qty' => (int)$extra_service_qty[$c],
                                        'wbbm_es_available_qty' => (int)$field['option_qty'],
                                    );
                                    $es_price += (float)$field['option_price'] * (int)$extra_service_qty[$c];
                                    $c++;

                            }
                        endif;
                    }

                    // Extra Service END

                    $total = $total_fare + $es_price + $extra_bag_price;


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
                    $cart_item_data['custom_reg_user'] = $_POST['custom_reg_user'];
                    $cart_item_data['wbbm_passenger_info_additional'] = $custom_reg_additional;
                    $cart_item_data['wbbm_extra_services'] = $es_array;
                    $cart_item_data['wbbm_tp'] = $total;
                    $cart_item_data['wbbm_bus_id'] = $bus_id;
                    $cart_item_data['line_total'] = $total;
                    $cart_item_data['line_subtotal'] = $total;
                    $cart_item_data['quantity'] = $total_seat;
                    $cart_item_data['wbbm_id'] = $product_id;
                    $cart_item_data['is_return'] = $is_return;
                    $cart_item_data['total_fare_original'] = $total_fare_original;
                    $cart_item_data['total_fare_roundtrip'] = $total_fare_roundtrip;
                    $cart_item_data['pickpoint'] = $pickpoint;

                    return $cart_item_data;
                }

            }



            public function wbbm_display_custom_fields_text_cart($item_data, $cart_item)
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
                        $custom_reg_user = $cart_item['custom_reg_user'];
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

                        ob_start();
                        ?>
                            <div class="mpStyles">
                                <div class="cart-item-details">
                                <ul>
    <li>
        <strong>
            <?php 
            echo esc_html( wbbm_get_option( 'wbbm_select_journey_date_text', 'wbbm_label_setting_sec' ) ) ? 
                esc_html( wbbm_get_option( 'wbbm_select_journey_date_text', 'wbbm_label_setting_sec' ) ) . ': ' : 
                esc_html__( 'Journey Date', 'bus-booking-manager' ) . ': '; 
            ?>
        </strong>
        <?php echo ' ' . esc_html( get_wbbm_datetime( $cart_item['wbbm_journey_date'], 'date' ) ); ?>
    </li>

    <li>
        <strong>
            <?php 
            echo esc_html( wbbm_get_option( 'wbbm_starting_text', 'wbbm_label_setting_sec' ) ) ? 
                esc_html( wbbm_get_option( 'wbbm_starting_text', 'wbbm_label_setting_sec' ) ) . ': ' : 
                esc_html__( 'Journey Time', 'bus-booking-manager' ) . ': '; 
            ?>
        </strong>
        <?php echo esc_html( get_wbbm_datetime( $cart_item['wbbm_journey_time'], 'time' ) ); ?>
    </li>

    <li>
        <strong>
            <?php 
            echo esc_html( wbbm_get_option( 'wbbm_boarding_points_text', 'wbbm_label_setting_sec' ) ) ? 
                esc_html( wbbm_get_option( 'wbbm_boarding_points_text', 'wbbm_label_setting_sec' ) ) . ': ' : 
                esc_html__( 'Boarding Point', 'bus-booking-manager' ) . ': '; 
            ?>
        </strong>
        <?php echo esc_html( $cart_item['wbbm_start_stops'] ); ?>
    </li>

    <li>
        <strong>
            <?php 
            echo esc_html( wbbm_get_option( 'wbbm_dropping_points_text', 'wbbm_label_setting_sec' ) ) ? 
                esc_html( wbbm_get_option( 'wbbm_dropping_points_text', 'wbbm_label_setting_sec' ) ) . ': ' : 
                esc_html__( 'Dropping Point', 'bus-booking-manager' ) . ': '; 
            ?>
        </strong>
        <?php echo esc_html( $cart_item['wbbm_end_stops'] ); ?>
    </li>

    <?php if ( $pickpoint && $pickpoint != 'n_a' ): ?>
        <li>
            <strong>
                <?php esc_html_e( 'Pickup Area', 'bus-booking-manager' ); ?>:
            </strong>
            <?php echo esc_html( ucfirst( $pickpoint ) ); ?>
        </li>
    <?php endif; ?>
</ul>
                                   
                                </div>
                            </div>
                        <?php

                        if($custom_reg_user=='no'){
                            ?>

                            <ul class='wbbm-cart-price-table'>

                                <?php if ($total_adult){ ?>
                                    <li>
                                        <strong>
                                        <?php $adult_text = wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec'); $adult_text_escaped = esc_html_e($adult_text ? $adult_text : esc_html_e('Adult', 'bus-booking-manager')); $total_adult_fare_formatted = wc_price($total_adult_fare); $total_adult_escaped = esc_html_e($total_adult); echo esc_html_e($adult_text_escaped) . ' (' . esc_html_e($total_adult_fare_formatted) . '*' . esc_html_e($total_adult_escaped) . ') = '; ?>
                                        </strong>
                                        <?php $total_price = $total_adult * $total_adult_fare; $total_price_formatted = wc_price($total_price); echo esc_html_e($total_price_formatted); ?>
                                    </li>
                                <?php } ?>

                                <?php if ($total_child){ ?>
                                    <li>
                                        <strong>
                                        <?php $child_text = wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') ?: esc_html__('Child', 'bus-booking-manager'); $total_fare = wc_price(floatval($total_child_fare)); $total_count = intval($total_child); echo esc_html($child_text) . ' (' . esc_html_e($total_fare) . '*' . esc_html_e($total_count) . ') = '; ?>
                                        </strong>
                                        <?php 
                                            $total = floatval($total_child) * floatval($total_child_fare);
                                            echo wp_kses_post(wc_price($total)); 
                                        ?>
                                    </li>
                                <?php } ?>

                                <?php if ($total_infant){ ?>
                                    <li>
                                        <strong>
                                        <?php $infant_text = wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') ?: esc_html__('Infant', 'bus-booking-manager'); $total_fare = wc_price(floatval($total_infant_fare)); $total_count = intval($total_infant); echo esc_html($infant_text) . ' (' . esc_html_e($total_fare) . '*' . esc_html_e($total_count) . ') = '; ?>
                                        </strong>
                                        <?php $total = floatval($total_infant) * floatval($total_infant_fare); echo wp_kses_post(wc_price($total)); ?>
                                    </li>
                                <?php } ?>

                            </ul>

                            <?php if($total_extra_service_qty): ?>
                                <li>
                                    <strong>
                                    <?php $extra_services_text = wbbm_get_option('wbbm_extra_services_text', 'wbbm_label_setting_sec'); if ($extra_services_text) { echo esc_html($extra_services_text) . ': '; } else { esc_html_e('Extra Services', 'bus-booking-manager'); echo ': '; } ?>
                                    </strong>
                                    <ol>
                                    <?php foreach ($wbbm_extra_services as $value) : ?>
                                        <?php if ($value['wbbm_es_input_qty'] > $value['wbbm_es_available_qty']) : ?>
                                            <li><strong><?php echo esc_html($value['wbbm_es_name']); ?>:</strong> <?php esc_html_e('Input service quantity has exceeded the limit!', 'bus-booking-manager'); ?></li>
                                        <?php elseif ($value['wbbm_es_input_qty'] > 0) : ?>
                                            <li><strong><?php echo esc_html($value['wbbm_es_name']); ?>:</strong> (<?php echo esc_html(wc_price($value['wbbm_es_price'])); ?> x <?php echo esc_html($value['wbbm_es_input_qty']); ?>) = <?php echo esc_html(wc_price((int)$value['wbbm_es_price'] * (int)$value['wbbm_es_input_qty'])); ?></li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>

                                    </ol>
                                </li>
                            <?php endif; ?>

                            <?php

                        }else{

                            if (is_array($passenger_info) && sizeof($passenger_info) > 0) {
                                $i = 0;
                                foreach ($passenger_info as $_passenger) {
                                    ?>
                                    <ul class='wbbm-cart-price-table'>

                                        <?php if ($total_adult > 0 && ($_passenger['wbbm_user_type'] == 'adult')): ?>
                                            <li>
                                                <strong>
                                                    <?php 
                                                    $adult_text = wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec');
                                                    echo esc_html($adult_text ? $adult_text : esc_html_e('Adult', 'bus-booking-manager')); 
                                                    ?>
                                                </strong>
                                                <?php echo esc_html(wc_price($total_adult_fare)); ?>
                                            </li>

                                        <?php endif; ?>

                                        <?php if ($total_child > 0 && ($_passenger['wbbm_user_type'] == 'child')): ?>
                                            <li>
                                                <strong>
                                                    <?php 
                                                    $child_text = wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec');
                                                    echo esc_html($child_text ? $child_text : esc_html_e('Child', 'bus-booking-manager')); 
                                                    ?>
                                                </strong>
                                                <?php echo esc_html(wc_price($total_child_fare)); ?>
                                            </li>

                                        <?php endif; ?>

                                        <?php if ($total_infant > 0 && ($_passenger['wbbm_user_type'] == 'infant')): ?>
                                            <li>
                                                <strong>
                                                    <?php 
                                                    $infant_text = wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec');
                                                    echo esc_html($infant_text ? $infant_text : esc_html_e('Infant', 'bus-booking-manager')); 
                                                    ?>
                                                </strong>
                                                <?php echo esc_html(wc_price($total_infant_fare)); ?>
                                            </li>

                                        <?php endif; ?>

                                        <?php if ($total_entire = 1 && $total_entire_fare > 0 && ($_passenger['wbbm_user_type'] == 'entire')): ?>
                                            <li>
                                                    <strong>
                                                        <?php
                                                        $entire_bus_text = wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec');
                                                        echo esc_html($entire_bus_text ? $entire_bus_text : esc_html_e('Entire Bus', 'bus-booking-manager'));

                                                        // Escaping the dynamic content
                                                        echo " (" . esc_html(wc_price($total_entire_fare)) . " x " . esc_html($total_entire) . ") = " . esc_html(wc_price($total_entire_fare * $total_entire)); 
                                                        ?>
                                                    </strong>
                                                </li>

                                        <?php endif; ?>


                                        <?php if(!empty($_passenger['extra_bag_quantity']) && $_passenger['extra_bag_quantity'] > 0): ?>
                                            <li>
                                                <strong>
                                                    <?php 
                                                    $extra_bag_text = wbbm_get_option('wbbm_extra_bag_text', 'wbbm_label_setting_sec');
                                                    echo esc_html($extra_bag_text ? $extra_bag_text . ': ' : esc_html_e('Extra Bag Qty', 'bus-booking-manager') . ': '); 
                                                    ?>
                                                </strong>
                                                <?php echo esc_html($_passenger['extra_bag_quantity']); ?>
                                            </li>

                                        <?php endif; ?>

                                        <?php if(!empty($_passenger['wbtm_extra_bag_price']) && $_passenger['extra_bag_quantity'] > 0): ?>
                                            <li>
                                                <strong>
                                                    <?php 
                                                    $extra_bag_price_text = wbbm_get_option('wbbm_extra_bag_price_text', 'wbbm_label_setting_sec');
                                                    echo esc_html($extra_bag_price_text ? $extra_bag_price_text . ': ' : esc_html_e('Extra Bag Price', 'bus-booking-manager') . ': '); 
                                                    ?>
                                                </strong>
                                                <?php 
                                                echo " (" . esc_html(wc_price($extra_per_bag_price)) . " x " . esc_html($_passenger['extra_bag_quantity']) . ") = " . esc_html(wc_price((int)$_passenger['wbtm_extra_bag_price'] * (int)$_passenger['extra_bag_quantity'])); 
                                                ?>
                                            </li>

                                        <?php endif; ?>

                                        <?php if(!empty($_passenger['wbbm_user_name'])): ?>
                                            <li>
                                                <strong>
                                                    <?php 
                                                    $name_text = wbbm_get_option('wbbm_name_text', 'wbbm_label_setting_sec');
                                                    echo esc_html($name_text ? $name_text . ': ' : esc_html_e('Name', 'bus-booking-manager') . ': '); 
                                                    ?>
                                                </strong>
                                                <?php echo esc_html($_passenger['wbbm_user_name']); ?>
                                            </li>

                                        <?php endif; ?>

                                        <?php if(!empty($_passenger['wbbm_user_email'])): ?>
                                            <li>
    <strong>
        <?php 
        $email_text = wbbm_get_option('wbbm_email_text', 'wbbm_label_setting_sec');
        echo esc_html($email_text ? $email_text . ': ' : esc_html_e('Email', 'bus-booking-manager') . ': '); 
        ?>
    </strong>
    <?php echo esc_html($_passenger['wbbm_user_email']); ?>
</li>

                                        <?php endif; ?>

                                        <?php if(!empty($_passenger['wbbm_user_phone'])): ?>
                                            <li>
    <strong>
        <?php 
        $phone_text = wbbm_get_option('wbbm_phone_text', 'wbbm_label_setting_sec');
        echo esc_html($phone_text ? $phone_text . ': ' : esc_html_e('Phone', 'bus-booking-manager') . ': '); 
        ?>
    </strong>
    <?php echo esc_html($_passenger['wbbm_user_phone']); ?>
</li>

                                        <?php endif; ?>

                                        <?php if(!empty($_passenger['wbbm_user_address'])): ?>
                                            <li>
    <strong>
        <?php 
        $address_text = wbbm_get_option('wbbm_address_text', 'wbbm_label_setting_sec');
        echo esc_html($address_text ? $address_text . ': ' : esc_html_e('Address', 'bus-booking-manager') . ': '); 
        ?>
    </strong>
    <?php echo esc_html($_passenger['wbbm_user_address']); ?>
</li>

                                        <?php endif; ?>

                                        <?php if(!empty($_passenger['wbbm_user_gender'])): ?>
                                            <li>
    <strong>
        <?php 
        $gender_text = wbbm_get_option('wbbm_gender_text', 'wbbm_label_setting_sec');
        echo esc_html($gender_text ? $gender_text . ': ' : esc_html_e('Gender', 'bus-booking-manager') . ': '); 
        ?>
    </strong>
    <?php echo esc_html($_passenger['wbbm_user_gender']); ?>
</li>

                                        <?php endif; ?>

                                        <?php if(!empty($_passenger['wbbm_user_dob'])): ?>
                                            <li>
    <strong>
        <?php 
        $dob_text = wbbm_get_option('wbbm_dofbirth_text', 'wbbm_label_setting_sec');
        echo esc_html($dob_text ? $dob_text . ': ' : esc_html_e('Date of Birth', 'bus-booking-manager') . ': '); 
        ?>
    </strong>
    <?php echo esc_html($_passenger['wbbm_user_dob']); ?>
</li>

                                        <?php endif; ?>

                                        <?php if(!empty($_passenger['wbbm_user_nationality'])): ?>
                                            <li>
    <strong>
        <?php 
        $nationality_text = wbbm_get_option('wbbm_nationality_text', 'wbbm_label_setting_sec');
        echo esc_html($nationality_text ? $nationality_text . ': ' : esc_html_e('Nationality', 'bus-booking-manager') . ': '); 
        ?>
    </strong>
    <?php echo esc_html($_passenger['wbbm_user_nationality']); ?>
</li>

                                        <?php endif; ?>

                                        <?php if(!empty($_passenger['wbbm_user_flight_arrival_no'])): ?>
                                            <li>
    <strong>
        <?php 
        $fa_no_text = wbbm_get_option('wbbm_fa_no_text', 'wbbm_label_setting_sec');
        echo esc_html($fa_no_text ? $fa_no_text . ': ' : esc_html_e('Flight Arrival No', 'bus-booking-manager') . ': '); 
        ?>
    </strong>
    <?php echo esc_html($_passenger['wbbm_user_flight_arrival_no']); ?>
</li>

                                        <?php endif; ?>

                                        <?php if(!empty($_passenger['wbbm_user_flight_departure_no'])): ?>
                                            <li>
    <strong>
        <?php 
        $fd_no_text = wbbm_get_option('wbbm_fd_no_text', 'wbbm_label_setting_sec');
        echo esc_html($fd_no_text ? $fd_no_text . ': ' : esc_html_e('Flight Departure No', 'bus-booking-manager') . ': '); 
        ?>
    </strong>
    <?php echo esc_html($_passenger['wbbm_user_flight_departure_no']); ?>
</li>

                                        <?php endif; ?>

                                        <?php
                                        if (is_array($passenger_info_additional) && sizeof($passenger_info_additional) > 0):
                                            foreach ($passenger_info_additional[$i] as $builder):
                                                ?>
                                               <li>
    <strong><?php echo esc_html($builder['name']) . ': '; ?></strong>
    <?php echo esc_html($builder['value']); ?>
</li>

                                            <?php
                                            endforeach;
                                        endif;
                                        ?>
                                    </ul>

                                    <?php
                                    /*   if (($cart_item['line_subtotal'] == $cart_item['total_fare_roundtrip']) && $cart_item['is_return'] == 1):
                                           $percent = ($cart_item['total_fare_roundtrip'] * 100) / $cart_item['total_fare_original'];
                                           $percent = 100 - $percent;
                                           echo '<p style="color:#af7a2d;font-size: 13px;line-height: 1em;"><strong>' . esc_html_e('Congratulation!', 'bus-booking-manager') . '</strong> <span> ' . esc_html_e('For a round trip, you got', 'bus-booking-manager') . ' <span style="font-weight:600">' . number_format($percent, 2) . '%</span> ' . esc_html_e('discount on this trip', 'bus-booking-manager') . '</span></p>';
                                       endif;*/
                                    $i++;
                                }
                            }

                            ?>
                            <!-- Extra Service with passenger info -->
                            <ul>
                                <?php if($total_extra_service_qty): ?>
                                    <li>
    <strong>
        <?php 
        $extra_services_text = wbbm_get_option('wbbm_extra_services_text', 'wbbm_label_setting_sec');
        echo esc_html($extra_services_text ? $extra_services_text . ': ' : esc_html_e('Extra Services', 'bus-booking-manager') . ': '); 
        ?>
    </strong>
    <ol>
        <?php
        foreach ($wbbm_extra_services as $value) {
            if ($value['wbbm_es_input_qty'] > $value['wbbm_es_available_qty']) :
                ?>
                <li><strong><?php echo esc_html($value['wbbm_es_name']); ?>: </strong><?php esc_html_e('Input service quantity has exceeded the limit!', 'bus-booking-manager'); ?></li>
            <?php
            else :
                if ($value['wbbm_es_input_qty'] > 0) :
                    ?>
                    <li><strong><?php echo esc_html($value['wbbm_es_name']); ?>: </strong>(<?php echo esc_html(wc_price($value['wbbm_es_price'])); ?> x <?php echo esc_html($value['wbbm_es_input_qty']); ?>) = <?php echo esc_html(wc_price((int)$value['wbbm_es_price'] * (int)$value['wbbm_es_input_qty'])); ?></li>
                <?php
                endif;
            endif;
        }
        ?>
    </ol>
</li>

                                <?php endif; ?>
                            </ul>
                            <?php
                        }

                    }

                }
                $item_data[] = array('key' => '','value'=>ob_get_clean());
                return $item_data;


            }









	}