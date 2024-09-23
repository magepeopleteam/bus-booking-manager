<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class FilterClass extends CommonClass {
    public function __construct() {
        // Constructor code (if needed)
    }

    function wbbm_load_bus_templates( $template ) {
        global $post;
        if ( isset( $post->post_type ) && $post->post_type == "wbbm_bus" ) {
            $template_name = 'single-bus.php';
            $template_path = 'mage-bus-ticket/';
            $default_path = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/';
            $located_template = locate_template( array( $template_path . $template_name ) );
            if ( ! $located_template ) {
                $template = $default_path . $template_name;
            } else {
                $template = $located_template;
            }
            return $template;
        }
        return $template;
    }

    public function wbbm_add_custom_fields_text_to_cart_item( $cart_item_data, $product_id, $variation_id = null ) {
        $linked_product_id = get_post_meta( $product_id, 'link_wbbm_bus', true );
        $product_id = ! empty( $linked_product_id ) ? $linked_product_id : $product_id;

        if ( get_post_type( $product_id ) == "wbbm_bus" ) {
            // Sanitize input data
            $journey_date    = isset( $_POST['journey_date'] ) ? sanitize_text_field( wp_unslash( $_POST['journey_date'] ) ) : '';
            $start_stops     = isset( $_POST['start_stops'] ) ? sanitize_text_field( wp_unslash( $_POST['start_stops'] ) ) : '';
            $end_stops       = isset( $_POST['end_stops'] ) ? sanitize_text_field( wp_unslash( $_POST['end_stops'] ) ) : '';
            $adult_seat      = isset( $_POST['adult_quantity'] ) ? intval( $_POST['adult_quantity'] ) : 0;

            $is_return       = 0;
            $return_discount  = wbbm_cart_has_opposite_route( $start_stops, $end_stops, $journey_date );

            // Retrieve and sanitize metadata
            $total_seats = intval( get_post_meta( $product_id, 'wbbm_total_seat', true ) );
            $tp          = floatval( get_post_meta( $product_id, '_price', true ) );
            $price_arr   = get_post_meta( $product_id, 'wbbm_bus_prices', true );
            $user        = array();

            // Child Quantity and Fare
            $total_child_fare_original  = 0;
            $total_child_fare_roundtrip  = 0;
            $child_fare_original         = 0;
            $child_fare_roundtrip        = 0;
            $total_child_seat           = 0;

            if ( isset( $_POST['child_quantity'] ) ) {
                $total_child_seat = isset( $_POST['child_quantity'] ) ? intval( $_POST['child_quantity'] ) : 0;
                $child_fare       = mage_seat_price( $product_id, $start_stops, $end_stops, 'child' );
                $child_fare_original = $child_fare;
                $child_fare_roundtrip = mage_seat_price( $product_id, $start_stops, $end_stops, 'child', true );

                if ( $return_discount > 0 ) {
                    $total_child_fare = $child_fare_roundtrip * $total_child_seat;
                } else {
                    $total_child_fare = $child_fare * $total_child_seat;
                }

                $total_child_fare_original = $child_fare * $total_child_seat;
                $total_child_fare_roundtrip = $child_fare_roundtrip * $total_child_seat;
                $child_fare = $return_discount > 0 ? $child_fare_roundtrip : $child_fare;
            }

            // Infant Quantity and Fare
            $total_infant_fare_original  = 0;
            $total_infant_fare_roundtrip  = 0;
            $infant_fare_original         = 0;
            $infant_fare_roundtrip        = 0;
            $total_infant_seat           = 0;

            if ( isset( $_POST['infant_quantity'] ) ) {
                $total_infant_seat = isset( $_POST['infant_quantity'] ) ? intval( $_POST['infant_quantity'] ) : 0;
                $infant_fare = mage_seat_price( $product_id, $start_stops, $end_stops, 'infant' );
                $infant_fare_original = $infant_fare;
                $infant_fare_roundtrip = mage_seat_price( $product_id, $start_stops, $end_stops, 'infant', true );

                if ( $return_discount > 0 ) {
                    $total_infant_fare = $infant_fare_roundtrip * $total_infant_seat;
                } else {
                    $total_infant_fare = $infant_fare * $total_infant_seat;
                }

                $total_infant_fare_original = $infant_fare * $total_infant_seat;
                $total_infant_fare_roundtrip = $infant_fare_roundtrip * $total_infant_seat;
                $infant_fare = $return_discount > 0 ? $infant_fare_roundtrip : $infant_fare;
            }

            // Entire Bus Quantity and Fare
            $total_entire_fare_original  = 0;
            $total_entire_fare_roundtrip  = 0;
            $entire_fare_original         = 0;
            $entire_fare_roundtrip        = 0;
            $total_entire_seat           = 0;

            if ( isset( $_POST['entire_quantity'] ) ) {
                $total_entire_seat = $total_seats;
                $entire_fare = mage_seat_price( $product_id, $start_stops, $end_stops, 'entire' );
                $entire_fare_original = $entire_fare;
                $entire_fare_roundtrip = mage_seat_price( $product_id, $start_stops, $end_stops, 'entire', true );

                if ( $return_discount > 0 ) {
                    $total_entire_fare = $entire_fare_roundtrip;
                } else {
                    $total_entire_fare = $entire_fare;
                }

                $total_entire_fare_original = $entire_fare;
                $total_entire_fare_roundtrip = $entire_fare_roundtrip;
                $entire_fare = $return_discount > 0 ? $entire_fare_roundtrip : $entire_fare;
            }

            $total_seat = ( $adult_seat + $total_child_seat + $total_infant_seat + $total_entire_seat );
            $main_fare = mage_seat_price( $product_id, $start_stops, $end_stops, 'adult' );
            $main_fare_original = $main_fare;
            $main_fare_roundtrip = mage_seat_price( $product_id, $start_stops, $end_stops, 'adult', true );

            if ( $return_discount > 0 ) {
                $total_main_fare = $main_fare_roundtrip * $adult_seat;
            } else {
                $total_main_fare = $main_fare * $adult_seat;
            }

            $total_main_fare_original = $main_fare * $adult_seat;
            $total_main_fare_roundtrip = $main_fare_roundtrip * $adult_seat;
            $adult_fare = $total_main_fare;

            $total_fare = ( $adult_fare + $total_child_fare + $total_infant_fare + $total_entire_fare );
            $total_fare_roundtrip = ( $total_main_fare_roundtrip + $total_child_fare_roundtrip + $total_infant_fare_roundtrip + $total_entire_fare_roundtrip );
            $total_fare_original = ( $total_main_fare_original + $total_child_fare_original + $total_infant_fare_original + $total_entire_fare_original );

            $user_start_time = isset( $_POST['user_start_time'] ) ? sanitize_text_field( wp_unslash( $_POST['user_start_time'] ) ) : '';
            $bus_start_time  = isset( $_POST['bus_start_time'] ) ? sanitize_text_field( wp_unslash( $_POST['bus_start_time'] ) ) : '';
            $bus_id          = isset( $_POST['bus_id'] ) ? sanitize_text_field( wp_unslash( $_POST['bus_id'] ) ) : 0;

            // Pickup Point
            $pickpoint = isset( $_POST['mage_pickpoint'] ) ? sanitize_text_field( wp_unslash( $_POST['mage_pickpoint'] ) ) : 'n_a';

            if ( $return_discount > 0 ) {
                $is_return = 1;
            }

            $extra_per_bag_price = floatval( get_post_meta( $product_id, 'wbbm_extra_bag_price', true ) );
            $extra_bag_price     = 0;
            $es_price            = 0;
            $custom_reg_additional = array();

            if ( isset( $_POST['custom_reg_user'] ) && $_POST['custom_reg_user'] == 'yes' ) {
                // Sanitize user input arrays
                $wbbm_user_name                = isset( $_POST['wbbm_user_name'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wbbm_user_name'] ) ) : array();
                $wbbm_user_email               = isset( $_POST['wbbm_user_email'] ) ? array_map( 'sanitize_email', wp_unslash( $_POST['wbbm_user_email'] ) ) : array();
                $wbbm_user_phone               = isset( $_POST['wbbm_user_phone'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wbbm_user_phone'] ) ) : array();
                $wbbm_user_address             = isset( $_POST['wbbm_user_address'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wbbm_user_address'] ) ) : array();
                $wbbm_user_gender              = isset( $_POST['wbbm_user_gender'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wbbm_user_gender'] ) ) : array();
                $wbbm_user_type                = isset( $_POST['wbbm_user_type'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wbbm_user_type'] ) ) : array();
                $wbbm_user_dob                 = isset( $_POST['wbbm_user_dob'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wbbm_user_dob'] ) ) : array();
                $wbbm_user_nationality         = isset( $_POST['wbbm_user_nationality'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wbbm_user_nationality'] ) ) : array();
                $wbbm_user_flight_arrival_no   = isset( $_POST['wbbm_user_flight_arrival_no'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wbbm_user_flight_arrival_no'] ) ) : array();
                $wbbm_user_flight_departure_no = isset( $_POST['wbbm_user_flight_departure_no'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wbbm_user_flight_departure_no'] ) ) : array();
                $bag_qty                       = isset( $_POST['extra_bag_quantity'] ) ? array_map( 'intval', wp_unslash( $_POST['extra_bag_quantity'] ) ) : array();

                $count_user = count( $wbbm_user_type );
                for ( $iu = 0; $iu < $count_user; $iu++ ) {
                    if ( isset( $wbbm_user_name[ $iu ] ) && $wbbm_user_name[ $iu ] !== '' ) {
                        $user[ $iu ]['wbbm_user_name'] = $wbbm_user_name[ $iu ];
                    }

                    if ( isset( $wbbm_user_email[ $iu ] ) && $wbbm_user_email[ $iu ] !== '' ) {
                        $user[ $iu ]['wbbm_user_email'] = $wbbm_user_email[ $iu ];
                    }

                    if ( isset( $wbbm_user_phone[ $iu ] ) && $wbbm_user_phone[ $iu ] !== '' ) {
                        $user[ $iu ]['wbbm_user_phone'] = $wbbm_user_phone[ $iu ];
                    }

                    if ( isset( $wbbm_user_address[ $iu ] ) && $wbbm_user_address[ $iu ] !== '' ) {
                        $user[ $iu ]['wbbm_user_address'] = $wbbm_user_address[ $iu ];
                    }

                    if ( isset( $wbbm_user_gender[ $iu ] ) && $wbbm_user_gender[ $iu ] !== '' ) {
                        $user[ $iu ]['wbbm_user_gender'] = $wbbm_user_gender[ $iu ];
                    }

                    if ( isset( $wbbm_user_type[ $iu ] ) && $wbbm_user_type[ $iu ] !== '' ) {
                        $user[ $iu ]['wbbm_user_type'] = $wbbm_user_type[ $iu ];
                    }

                    if ( isset( $wbbm_user_dob[ $iu ] ) && $wbbm_user_dob[ $iu ] !== '' ) {
                        $user[ $iu ]['wbbm_user_dob'] = $wbbm_user_dob[ $iu ];
                    }

                    if ( isset( $wbbm_user_nationality[ $iu ] ) && $wbbm_user_nationality[ $iu ] !== '' ) {
                        $user[ $iu ]['wbbm_user_nationality'] = $wbbm_user_nationality[ $iu ];
                    }

                    if ( isset( $wbbm_user_flight_arrival_no[ $iu ] ) && $wbbm_user_flight_arrival_no[ $iu ] !== '' ) {
                        $user[ $iu ]['wbbm_user_flight_arrival_no'] = $wbbm_user_flight_arrival_no[ $iu ];
                    }

                    if ( isset( $wbbm_user_flight_departure_no[ $iu ] ) && $wbbm_user_flight_departure_no[ $iu ] !== '' ) {
                        $user[ $iu ]['wbbm_user_flight_departure_no'] = $wbbm_user_flight_departure_no[ $iu ];
                    }

                    if ( isset( $bag_qty[ $iu ] ) && $bag_qty[ $iu ] !== '' ) {
                        $user[ $iu ]['extra_bag_quantity']   = $bag_qty[ $iu ];
                        $user[ $iu ]['wbtm_extra_bag_price'] = (float) $extra_per_bag_price;

                        $extra_bag_price += $bag_qty[ $iu ] * (float) $extra_per_bag_price;
                    }

                    // Additional registration builder fields
                    $reg_form_arr = maybe_unserialize( get_post_meta( $product_id, 'wbbm_attendee_reg_form', true ) );

                    if ( is_array( $reg_form_arr ) && count( $reg_form_arr ) > 0 ) {
                        foreach ( $reg_form_arr as $builder ) {
                            $field_value = isset( $_POST[ $builder['field_id'] ][ $iu ] ) ? sanitize_text_field( wp_unslash( $_POST[ $builder['field_id'] ][ $iu ] ) ) : '';
                            $custom_reg_additional[ $iu ][] = array(
                                'name'  => sanitize_text_field( $builder['field_label'] ),
                                'value' => $field_value,
                            );
                        }
                    }
                }
            } else {
                // Default user types without custom registration
                $r_counter = 0;
                for ( $r = 1; $r <= $adult_seat; $r++ ) {
                    $user[ $r_counter ]['wbbm_user_type'] = 'adult';
                    $r_counter++;
                }

                for ( $r = 1; $r <= $total_child_seat; $r++ ) {
                    $user[ $r_counter ]['wbbm_user_type'] = 'child';
                    $r_counter++;
                }

                for ( $r = 1; $r <= $total_infant_seat; $r++ ) {
                    $user[ $r_counter ]['wbbm_user_type'] = 'infant';
                    $r_counter++;
                }

                for ( $r = 1; $r <= $total_entire_seat; $r++ ) {
                    $user[ $r_counter ]['wbbm_user_type'] = 'entire';
                    $r_counter++;
                }
            }

            // Extra Services
            $es_array = array();
            $es_price = 0;

            if ( isset( $_POST['extra_service_qty'] ) ) {
                $extra_service_qty = array_map( 'intval', wp_unslash( $_POST['extra_service_qty'] ) );
                $extra_services    = get_post_meta( $bus_id, 'mep_events_extra_prices', true );

                if ( ! empty( $extra_services ) ) {
                    foreach ( $extra_services as $key => $field ) {
                        $option_qty = isset( $field['option_qty'] ) ? intval( $field['option_qty'] ) : 0;
                        $option_price = isset( $field['option_price'] ) ? floatval( $field['option_price'] ) : 0;
                        $option_name = isset( $field['option_name'] ) ? sanitize_text_field( $field['option_name'] ) : '';

                        $input_qty = isset( $extra_service_qty[ $key ] ) ? $extra_service_qty[ $key ] : 0;

                        $es_array[ $key ] = array(
                            'wbbm_es_name'          => $option_name,
                            'wbbm_es_price'         => $option_price,
                            'wbbm_es_input_qty'     => $input_qty,
                            'wbbm_es_available_qty' => $option_qty,
                        );
                        $es_price += $option_price * $input_qty;
                    }
                }
            }

            // Calculate total price
            $total = $total_fare + $es_price + $extra_bag_price;

            // Set cart item data
            $cart_item_data['wbbm_start_stops']               = $start_stops;
            $cart_item_data['wbbm_end_stops']                 = $end_stops;
            $cart_item_data['wbbm_journey_date']              = $journey_date;
            $cart_item_data['wbbm_journey_time']              = $user_start_time;
            $cart_item_data['wbbm_bus_time']                  = $bus_start_time;
            $cart_item_data['wbbm_total_seats']               = $total_seat;

            $cart_item_data['wbbm_total_adult_qt']            = $adult_seat;
            $cart_item_data['wbbm_total_adult_price']         = $adult_fare;
            $cart_item_data['wbbm_per_adult_price']           = $main_fare;
            $cart_item_data['wbbm_per_adult_price_original']  = $main_fare_original;
            $cart_item_data['wbbm_per_adult_price_roundtrip'] = $main_fare_roundtrip;

            $cart_item_data['wbbm_total_child_qt']            = $total_child_seat;
            $cart_item_data['wbbm_total_child_price']         = $total_child_fare;
            $cart_item_data['wbbm_per_child_price']           = $child_fare;
            $cart_item_data['wbbm_per_child_price_original']  = $child_fare_original;
            $cart_item_data['wbbm_per_child_price_roundtrip'] = $child_fare_roundtrip;

            $cart_item_data['wbbm_total_infant_qt']            = $total_infant_seat;
            $cart_item_data['wbbm_total_infant_price']         = $total_infant_fare;
            $cart_item_data['wbbm_per_infant_price']           = $infant_fare;
            $cart_item_data['wbbm_per_infant_price_original']  = $infant_fare_original;
            $cart_item_data['wbbm_per_infant_price_roundtrip'] = $infant_fare_roundtrip;

            $cart_item_data['wbbm_total_entire_qt']            = $total_entire_seat;
            $cart_item_data['wbbm_total_entire_price']         = $total_entire_fare;
            $cart_item_data['wbbm_per_entire_price']           = $entire_fare;
            $cart_item_data['wbbm_per_entire_price_original']  = $entire_fare_original;
            $cart_item_data['wbbm_per_entire_price_roundtrip'] = $entire_fare_roundtrip;

            $cart_item_data['wbbm_passenger_info']             = $user;
            $cart_item_data['custom_reg_user']                 = isset( $_POST['custom_reg_user'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_reg_user'] ) ) : '';
            $cart_item_data['wbbm_passenger_info_additional']  = $custom_reg_additional;
            $cart_item_data['wbbm_extra_services']             = $es_array;
            $cart_item_data['wbbm_tp']                         = $total;
            $cart_item_data['wbbm_bus_id']                     = $bus_id;
            $cart_item_data['line_total']                      = $total;
            $cart_item_data['line_subtotal']                   = $total;
            $cart_item_data['quantity']                        = $total_seat;
            $cart_item_data['wbbm_id']                         = $product_id;
            $cart_item_data['is_return']                       = $is_return;
            $cart_item_data['total_fare_original']             = $total_fare_original;
            $cart_item_data['total_fare_roundtrip']            = $total_fare_roundtrip;
            $cart_item_data['pickpoint']                       = $pickpoint;

            return $cart_item_data;
        }

        return $cart_item_data;
    }




    public function wbbm_display_custom_fields_text_cart( $item_data, $cart_item ) {
    if ( ! is_admin() ) {
        $total_extra_service_qty = 0;
        $eid = isset( $cart_item['wbbm_id'] ) ? intval( $cart_item['wbbm_id'] ) : 0;

        if ( get_post_type( $eid ) == 'wbbm_bus' ) {
            $total_adult = isset( $cart_item['wbbm_total_adult_qt'] ) ? intval( $cart_item['wbbm_total_adult_qt'] ) : 0;
            $total_adult_fare = isset( $cart_item['wbbm_per_adult_price'] ) ? floatval( $cart_item['wbbm_per_adult_price'] ) : 0;

            $total_child = isset( $cart_item['wbbm_total_child_qt'] ) ? intval( $cart_item['wbbm_total_child_qt'] ) : 0;
            $total_child_fare = isset( $cart_item['wbbm_per_child_price'] ) ? floatval( $cart_item['wbbm_per_child_price'] ) : 0;

            $total_infant = isset( $cart_item['wbbm_total_infant_qt'] ) ? intval( $cart_item['wbbm_total_infant_qt'] ) : 0;
            $total_infant_fare = isset( $cart_item['wbbm_per_infant_price'] ) ? floatval( $cart_item['wbbm_per_infant_price'] ) : 0;

            $total_entire = isset( $cart_item['wbbm_total_entire_qt'] ) ? intval( $cart_item['wbbm_total_entire_qt'] ) : 0;
            $total_entire_fare = isset( $cart_item['wbbm_per_entire_price'] ) ? floatval( $cart_item['wbbm_per_entire_price'] ) : 0;

            $pickpoint = isset( $cart_item['pickpoint'] ) ? sanitize_text_field( $cart_item['pickpoint'] ) : '';
            $currency = get_woocommerce_currency_symbol();

            $passenger_info = isset( $cart_item['wbbm_passenger_info'] ) ? $cart_item['wbbm_passenger_info'] : array();
            $custom_reg_user = isset( $cart_item['custom_reg_user'] ) ? sanitize_text_field( $cart_item['custom_reg_user'] ) : 'no';
            $passenger_info_additional = isset( $cart_item['wbbm_passenger_info_additional'] ) ? $cart_item['wbbm_passenger_info_additional'] : array();

            $extra_per_bag_price = floatval( get_post_meta( $eid, 'wbbm_extra_bag_price', true ) );
            $extra_per_bag_price = $extra_per_bag_price ? $extra_per_bag_price : 0;

            // Check extra service qty
            $wbbm_extra_services = isset( $cart_item['wbbm_extra_services'] ) ? $cart_item['wbbm_extra_services'] : array();
            if ( $wbbm_extra_services && is_array( $wbbm_extra_services ) ) {
                foreach ( $wbbm_extra_services as $exs ) {
                    $total_extra_service_qty += isset( $exs['wbbm_es_input_qty'] ) ? intval( $exs['wbbm_es_input_qty'] ) : 0;
                }
            }

            ob_start();
            ?>
            <div class="mpStyles">
                <div class="cart-item-details">
                    <ul>
                        <li>
                            <strong>
                                <?php echo wbbm_get_option( 'wbbm_select_journey_date_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_select_journey_date_text', 'wbbm_label_setting_sec' ) . ': ' : esc_html_e( 'Journey Date', 'bus-booking-manager' ) . ': '; ?>
                            </strong>
                            <?php echo ' ' . get_wbbm_datetime( sanitize_text_field( $cart_item['wbbm_journey_date'] ), 'date' ); ?>
                        </li>
                        <li>
                            <strong>
                                <?php echo wbbm_get_option( 'wbbm_starting_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_starting_text', 'wbbm_label_setting_sec' ) . ': ' : esc_html_e( 'Journey Time', 'bus-booking-manager' ) . ': '; ?>
                            </strong>
                            <?php echo get_wbbm_datetime( sanitize_text_field( $cart_item['wbbm_journey_time'] ), 'time' ); ?>
                        </li>
                        <li>
                            <strong>
                                <?php echo wbbm_get_option( 'wbbm_boarding_points_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_boarding_points_text', 'wbbm_label_setting_sec' ) . ': ' : esc_html_e( 'Boarding Point', 'bus-booking-manager' ) . ': '; ?>
                            </strong>
                            <?php echo esc_html( $cart_item['wbbm_start_stops'] ); ?>
                        </li>
                        <li>
                            <strong>
                                <?php echo wbbm_get_option( 'wbbm_dropping_points_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_dropping_points_text', 'wbbm_label_setting_sec' ) . ': ' : esc_html_e( 'Dropping Point', 'bus-booking-manager' ) . ': '; ?>
                            </strong>
                            <?php echo esc_html( $cart_item['wbbm_end_stops'] ); ?>
                        </li>

                        <?php if ( $pickpoint && $pickpoint !== 'n_a' ): ?>
                            <li>
                                <strong>
                                    <?php echo esc_html_e( 'Pickup Area', 'bus-booking-manager' ) . ': '; ?>
                                </strong>
                                <?php echo ucfirst( esc_html( $pickpoint ) ); ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <?php

            if ( $custom_reg_user === 'no' ) {
                ?>
                <ul class='wbbm-cart-price-table'>
                    <?php if ( $total_adult ) { ?>
                        <li>
                            <strong>
                                <?php echo wbbm_get_option( 'wbbm_adult_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_adult_text', 'wbbm_label_setting_sec' ) : esc_html_e( 'Adult', 'bus-booking-manager' ); echo ' (' . wc_price( $total_adult_fare ) . '*' . $total_adult . ')' . ' = '; ?>
                            </strong>
                            <?php echo wc_price( $total_adult * $total_adult_fare ); ?>
                        </li>
                    <?php } ?>

                    <?php if ( $total_child ) { ?>
                        <li>
                            <strong>
                                <?php echo wbbm_get_option( 'wbbm_child_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_child_text', 'wbbm_label_setting_sec' ) : esc_html_e( 'Child', 'bus-booking-manager' ); echo ' (' . wc_price( $total_child_fare ) . '*' . $total_child . ')' . ' = '; ?>
                            </strong>
                            <?php echo wc_price( $total_child * $total_child_fare ); ?>
                        </li>
                    <?php } ?>

                    <?php if ( $total_infant ) { ?>
                        <li>
                            <strong>
                                <?php echo wbbm_get_option( 'wbbm_infant_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_infant_text', 'wbbm_label_setting_sec' ) : esc_html_e( 'Infant', 'bus-booking-manager' ); echo ' (' . wc_price( $total_infant_fare ) . '*' . $total_infant . ')' . ' = '; ?>
                            </strong>
                            <?php echo wc_price( $total_infant * $total_infant_fare ); ?>
                        </li>
                    <?php } ?>
                </ul>

                <?php if ( $total_extra_service_qty ): ?>
                    <li>
                        <strong>
                            <?php echo wbbm_get_option( 'wbbm_extra_services_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_extra_services_text', 'wbbm_label_setting_sec' ) . ': ' : esc_html_e( 'Extra Services', 'bus-booking-manager' ) . ': '; ?>
                        </strong>
                        <ol>
                            <?php
                            foreach ( $wbbm_extra_services as $value ) {
                                if ( isset( $value['wbbm_es_input_qty'], $value['wbbm_es_available_qty'] ) && $value['wbbm_es_input_qty'] > $value['wbbm_es_available_qty'] ) {
                                    ?>
                                    <li><strong><?php echo esc_html( $value['wbbm_es_name'] ); ?>: </strong><?php esc_html_e( 'Input service quantity has exceeded the limit!', 'bus-booking-manager' ); ?></li>
                                    <?php
                                } else {
                                    if ( isset( $value['wbbm_es_input_qty'] ) && $value['wbbm_es_input_qty'] > 0 ) {
                                        ?>
                                        <li><strong><?php echo esc_html( $value['wbbm_es_name'] ); ?>: </strong>(<?php echo wc_price( floatval( $value['wbbm_es_price'] ) ); ?> x <?php echo intval( $value['wbbm_es_input_qty'] ); ?>) = <?php echo wc_price( floatval( $value['wbbm_es_price'] ) * intval( $value['wbbm_es_input_qty'] ) ); ?></li>
                                        <?php
                                    }
                                }
                            }
                            ?>
                        </ol>
                    </li>
                <?php endif; ?>

                <?php
            } else {
                if ( is_array( $passenger_info ) && count( $passenger_info ) > 0 ) {
                    $i = 0;
                    foreach ( $passenger_info as $_passenger ) {
                        ?>
                        <ul class='wbbm-cart-price-table'>
                            <?php if ( $total_adult > 0 && ( isset( $_passenger['wbbm_user_type'] ) && $_passenger['wbbm_user_type'] == 'adult' ) ): ?>
                                <li>
                                    <strong>
                                        <?php echo wbbm_get_option( 'wbbm_adult_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_adult_text', 'wbbm_label_setting_sec' ) : esc_html_e( 'Adult', 'bus-booking-manager' ); ?>
                                    </strong>
                                    <?php echo wc_price( $total_adult_fare ); ?>
                                </li>
                            <?php endif; ?>

                            <?php if ( $total_child > 0 && ( isset( $_passenger['wbbm_user_type'] ) && $_passenger['wbbm_user_type'] == 'child' ) ): ?>
                                <li>
                                    <strong>
                                        <?php echo wbbm_get_option( 'wbbm_child_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_child_text', 'wbbm_label_setting_sec' ) : esc_html_e( 'Child', 'bus-booking-manager' ); ?>
                                    </strong>
                                    <?php echo wc_price( $total_child_fare ); ?>
                                </li>
                            <?php endif; ?>

                            <?php if ( $total_infant > 0 && ( isset( $_passenger['wbbm_user_type'] ) && $_passenger['wbbm_user_type'] == 'infant' ) ): ?>
                                <li>
                                    <strong>
                                        <?php echo wbbm_get_option( 'wbbm_infant_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_infant_text', 'wbbm_label_setting_sec' ) : esc_html_e( 'Infant', 'bus-booking-manager' ); ?>
                                    </strong>
                                    <?php echo wc_price( $total_infant_fare ); ?>
                                </li>
                            <?php endif; ?>

                            <?php if ( $total_entire == 1 && $total_entire_fare > 0 && ( isset( $_passenger['wbbm_user_type'] ) && $_passenger['wbbm_user_type'] == 'entire' ) ): ?>
                                <li>
                                    <strong>
                                        <?php echo wbbm_get_option( 'wbbm_entire_bus_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_entire_bus_text', 'wbbm_label_setting_sec' ) : esc_html_e( 'Entire Bus', 'bus-booking-manager' ); ?>
                                        <?php echo " (" . wc_price( $total_entire_fare ) . " x " . intval( $total_entire ) . ") = " . wc_price( $total_entire_fare * intval( $total_entire ) ); ?>
                                    </strong>
                                </li>
                            <?php endif; ?>

                            <?php if ( ! empty( $_passenger['extra_bag_quantity'] ) && $_passenger['extra_bag_quantity'] > 0 ): ?>
                                <li>
                                    <strong>
                                        <?php echo wbbm_get_option( 'wbbm_extra_bag_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_extra_bag_text', 'wbbm_label_setting_sec' ) . ': ' : esc_html_e( 'Extra Bag Qty', 'bus-booking-manager' ) . ': '; ?>
                                    </strong>
                                    <?php echo intval( $_passenger['extra_bag_quantity'] ); ?>
                                </li>
                            <?php endif; ?>

                            <?php if ( ! empty( $_passenger['wbtm_extra_bag_price'] ) && $_passenger['extra_bag_quantity'] > 0 ): ?>
                                <li>
                                    <strong>
                                        <?php echo wbbm_get_option( 'wbbm_extra_bag_price_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_extra_bag_price_text', 'wbbm_label_setting_sec' ) . ': ' : esc_html_e( 'Extra Bag Price', 'bus-booking-manager' ) . ': '; ?>
                                    </strong>
                                    <?php echo " (" . wc_price( $extra_per_bag_price ) . " x " . intval( $_passenger['extra_bag_quantity'] ) . ") = " . wc_price( floatval( $_passenger['wbtm_extra_bag_price'] ) * intval( $_passenger['extra_bag_quantity'] ) ); ?>
                                </li>
                            <?php endif; ?>

                            <?php if ( ! empty( $_passenger['wbbm_user_name'] ) ): ?>
                                <li>
                                    <strong>
                                        <?php echo wbbm_get_option( 'wbbm_name_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_name_text', 'wbbm_label_setting_sec' ) . ': ' : esc_html_e( 'Name', 'bus-booking-manager' ) . ': '; ?>
                                    </strong>
                                    <?php echo esc_html( $_passenger['wbbm_user_name'] ); ?>
                                </li>
                            <?php endif; ?>

                            <?php if ( ! empty( $_passenger['wbbm_user_email'] ) ): ?>
                                <li>
                                    <strong>
                                        <?php echo wbbm_get_option( 'wbbm_email_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_email_text', 'wbbm_label_setting_sec' ) . ': ' : esc_html_e( 'Email', 'bus-booking-manager' ) . ': '; ?>
                                    </strong>
                                    <?php echo esc_html( $_passenger['wbbm_user_email'] ); ?>
                                </li>
                            <?php endif; ?>

                            <?php if ( ! empty( $_passenger['wbbm_user_phone'] ) ): ?>
                                <li>
                                    <strong>
                                        <?php echo wbbm_get_option( 'wbbm_phone_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_phone_text', 'wbbm_label_setting_sec' ) . ': ' : esc_html_e( 'Phone', 'bus-booking-manager' ) . ': '; ?>
                                    </strong>
                                    <?php echo esc_html( $_passenger['wbbm_user_phone'] ); ?>
                                </li>
                            <?php endif; ?>

                            <?php if ( ! empty( $_passenger['wbbm_user_address'] ) ): ?>
                                <li>
                                    <strong>
                                        <?php echo wbbm_get_option( 'wbbm_address_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_address_text', 'wbbm_label_setting_sec' ) . ': ' : esc_html_e( 'Address', 'bus-booking-manager' ) . ': '; ?>
                                    </strong>
                                    <?php echo esc_html( $_passenger['wbbm_user_address'] ); ?>
                                </li>
                            <?php endif; ?>

                            <?php if ( ! empty( $_passenger['wbbm_user_gender'] ) ): ?>
                                <li>
                                    <strong>
                                        <?php echo wbbm_get_option( 'wbbm_gender_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_gender_text', 'wbbm_label_setting_sec' ) . ': ' : esc_html_e( 'Gender', 'bus-booking-manager' ) . ': '; ?>
                                    </strong>
                                    <?php echo esc_html( $_passenger['wbbm_user_gender'] ); ?>
                                </li>
                            <?php endif; ?>

                            <?php if ( ! empty( $_passenger['wbbm_user_dob'] ) ): ?>
                                <li>
                                    <strong>
                                        <?php echo wbbm_get_option( 'wbbm_dofbirth_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_dofbirth_text', 'wbbm_label_setting_sec' ) . ': ' : esc_html_e( 'Date of Birth', 'bus-booking-manager' ) . ': '; ?>
                                    </strong>
                                    <?php echo esc_html( $_passenger['wbbm_user_dob'] ); ?>
                                </li>
                            <?php endif; ?>

                            <?php if ( ! empty( $_passenger['wbbm_user_nationality'] ) ): ?>
                                <li>
                                    <strong>
                                        <?php echo wbbm_get_option( 'wbbm_nationality_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_nationality_text', 'wbbm_label_setting_sec' ) . ': ' : esc_html_e( 'Nationality', 'bus-booking-manager' ) . ': '; ?>
                                    </strong>
                                    <?php echo esc_html( $_passenger['wbbm_user_nationality'] ); ?>
                                </li>
                            <?php endif; ?>

                            <?php if ( ! empty( $_passenger['wbbm_user_flight_arrival_no'] ) ): ?>
                                <li>
                                    <strong>
                                        <?php echo wbbm_get_option( 'wbbm_fa_no_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_fa_no_text', 'wbbm_label_setting_sec' ) . ': ' : esc_html_e( 'Flight Arrival No', 'bus-booking-manager' ) . ': '; ?>
                                    </strong>
                                    <?php echo esc_html( $_passenger['wbbm_user_flight_arrival_no'] ); ?>
                                </li>
                            <?php endif; ?>

                            <?php if ( ! empty( $_passenger['wbbm_user_flight_departure_no'] ) ): ?>
                                <li>
                                    <strong>
                                        <?php echo wbbm_get_option( 'wbbm_fd_no_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_fd_no_text', 'wbbm_label_setting_sec' ) . ': ' : esc_html_e( 'Flight Departure No', 'bus-booking-manager' ) . ': '; ?>
                                    </strong>
                                    <?php echo esc_html( $_passenger['wbbm_user_flight_departure_no'] ); ?>
                                </li>
                            <?php endif; ?>

                            <?php
                            if ( is_array( $passenger_info_additional ) && count( $passenger_info_additional ) > 0 ) {
                                foreach ( $passenger_info_additional[ $i ] as $builder ) {
                                    ?>
                                    <li>
                                        <strong><?php echo esc_html( $builder['name'] ) . ': '; ?></strong>
                                        <?php echo esc_html( $builder['value'] ); ?>
                                    </li>
                                    <?php
                                }
                            }
                            ?>
                        </ul>
                        <?php
                        $i++;
                    }
                }

                // Extra Service with passenger info
                ?>
                <ul>
                    <?php if ( $total_extra_service_qty ): ?>
                        <li>
                            <strong>
                                <?php echo wbbm_get_option( 'wbbm_extra_services_text', 'wbbm_label_setting_sec' ) ? wbbm_get_option( 'wbbm_extra_services_text', 'wbbm_label_setting_sec' ) . ': ' : esc_html_e( 'Extra Services', 'bus-booking-manager' ) . ': '; ?>
                            </strong>
                            <ol>
                                <?php
                                foreach ( $wbbm_extra_services as $value ) {
                                    if ( isset( $value['wbbm_es_input_qty'], $value['wbbm_es_available_qty'] ) && $value['wbbm_es_input_qty'] > $value['wbbm_es_available_qty'] ) {
                                        ?>
                                        <li><strong><?php echo esc_html( $value['wbbm_es_name'] ); ?>: </strong><?php esc_html_e( 'Input service quantity has exceeded the limit!', 'bus-booking-manager' ); ?></li>
                                        <?php
                                    } else {
                                        if ( isset( $value['wbbm_es_input_qty'] ) && $value['wbbm_es_input_qty'] > 0 ) {
                                            ?>
                                            <li><strong><?php echo esc_html( $value['wbbm_es_name'] ); ?>: </strong>(<?php echo wc_price( floatval( $value['wbbm_es_price'] ) ); ?> x <?php echo intval( $value['wbbm_es_input_qty'] ); ?>) = <?php echo wc_price( floatval( $value['wbbm_es_price'] ) * intval( $value['wbbm_es_input_qty'] ) ); ?></li>
                                            <?php
                                        }
                                    }
                                }
                                ?>
                            </ol>
                        </li>
                    <?php endif; ?>
                </ul>
                <?php
            }
        }

        $item_data[] = array( 'key' => '', 'value' => ob_get_clean() );
        return $item_data;
        }


	}
}