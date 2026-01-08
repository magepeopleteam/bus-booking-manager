<?php
    if ( ! defined( 'ABSPATH' ) ) {
        die;
    }

    class FilterClass extends CommonClass {
        public function __construct() {

        }

        /**
         * Helper: sanitize array of text fields
         *
         * @param array $arr
         * @return array
         */
        protected function sanitize_text_array( $arr ) {
            $arr = (array) $arr;
            return array_map( 'sanitize_text_field', $arr );
        }

        function wbbm_load_bus_templates( $template ) {
            global $post;
            if ( isset( $post ) && $post->post_type == "wbbm_bus" ) {
                $template_name = 'single-bus.php';
                $template_path = 'mage-bus-ticket/';
                $default_path = plugin_dir_path( dirname( __FILE__ ) )  . 'templates/';
                $template = locate_template( array( $template_path . $template_name ) );
                if ( ! $template ) :
                    $template = $default_path . $template_name;
                endif;
                return $template;
            }
            return $template;
        }

        public function wbbm_add_custom_fields_text_to_cart_item( $cart_item_data, $product_id, $variation_id = null ) {

            // Verify nonce
            $nonce = isset($_POST['add_to_cart_custom_nonce']) ? sanitize_text_field(wp_unslash($_POST['add_to_cart_custom_nonce'])) : '';
            if ( ! $nonce || ! wp_verify_nonce($nonce, 'add_to_cart_custom_action') ) {
                wc_add_notice(__('Security check failed. Please try again.', 'your-textdomain'), 'error');
                return $cart_item_data;
            }
            // Normalize POST inputs and sanitize
            $post = ! empty( $_POST ) ? wp_unslash( $_POST ) : array();

            // support product linking
            $product_id = get_post_meta( $product_id, 'link_wbbm_bus', true ) ? get_post_meta( $product_id, 'link_wbbm_bus', true ) : $product_id;

            if ( get_post_type( $product_id ) == "wbbm_bus" ) {

                $journey_date = isset( $post['journey_date'] ) ? sanitize_text_field( $post['journey_date'] ) : '';
                $is_return = 0;
                $return_discount = 0;
                $start_stops = isset( $post['start_stops'] ) ? sanitize_text_field( $post['start_stops'] ) : '';
                $end_stops   = isset( $post['end_stops'] ) ? sanitize_text_field( $post['end_stops'] ) : '';
                $adult_seat  = isset( $post['adult_quantity'] ) ? absint( $post['adult_quantity'] ) : 0;

                // Only call helper if we have required values
                if ( $start_stops && $end_stops && $journey_date ) {
                    $return_discount = wbbm_cart_has_opposite_route( $start_stops, $end_stops, $journey_date );
                }

                $total_seats = get_post_meta( $product_id, 'wbbm_total_seat', true );
                $tp = get_post_meta( $product_id, '_price', true );
                $price_arr = get_post_meta( $product_id, 'wbbm_bus_prices', true );
                $new = array();
                $user = array();

                // Child
                $total_child_fare_original = 0;
                $total_child_fare_roundtrip = 0;
                $child_fare_original = 0;
                $child_fare_roundtrip = 0;

                if ( isset( $post['child_quantity'] ) ) {
                    $total_child_seat = absint( $post['child_quantity'] );
                    $child_fare = mage_seat_price( $product_id, $start_stops, $end_stops, 'child' );
                    $child_fare_original = mage_seat_price( $product_id, $start_stops, $end_stops, 'child' );
                    $child_fare_roundtrip = mage_seat_price( $product_id, $start_stops, $end_stops, 'child', true );
                    if ( $return_discount > 0 ) {
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

                // Infant
                $total_infant_fare_original = 0;
                $total_infant_fare_roundtrip = 0;
                $infant_fare_original = 0;
                $infant_fare_roundtrip = 0;
                if ( isset( $post['infant_quantity'] ) ) {
                    $total_infant_seat = absint( $post['infant_quantity'] );
                    $infant_fare = mage_seat_price( $product_id, $start_stops, $end_stops, 'infant' );
                    $infant_fare_original = mage_seat_price( $product_id, $start_stops, $end_stops, 'infant' );
                    $infant_fare_roundtrip = mage_seat_price( $product_id, $start_stops, $end_stops, 'infant', true );
                    if ( $return_discount > 0 ) {
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

                // Entire
                $total_entire_fare_original = 0;
                $total_entire_fare_roundtrip = 0;
                $entire_fare_original = 0;
                $entire_fare_roundtrip = 0;
                if ( isset( $post['entire_quantity'] ) ) {
                    $total_entire_seat = absint( $total_seats );
                    $entire_fare = mage_seat_price( $product_id, $start_stops, $end_stops, 'entire' );
                    $entire_fare_original = mage_seat_price( $product_id, $start_stops, $end_stops, 'entire' );
                    $entire_fare_roundtrip = mage_seat_price( $product_id, $start_stops, $end_stops, 'entire', true );
                    if ( $return_discount > 0 ) {
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

                $total_seat = ( $adult_seat + $total_child_seat + $total_infant_seat + $total_entire_seat );
                $main_fare = mage_seat_price( $product_id, $start_stops, $end_stops, 'adult' );
                $main_fare_original = mage_seat_price( $product_id, $start_stops, $end_stops, 'adult' );
                $main_fare_roundtrip = mage_seat_price( $product_id, $start_stops, $end_stops, 'adult', true );

                if ( $return_discount > 0 ) {
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

                $total_fare = ( $adult_fare + $total_child_fare + $total_infant_fare + $total_entire_fare );
                $total_fare_roundtrip = ( $total_main_fare_roundtrip + $total_child_fare_roundtrip + $total_infant_fare_roundtrip + $total_entire_fare_roundtrip );
                $total_fare_original = ( $total_main_fare_original + $total_child_fare_original + $total_infant_fare_original + $total_entire_fare_original );

                $user_start_time = isset( $post['user_start_time'] ) ? sanitize_text_field( $post['user_start_time'] ) : '';
                $bus_start_time  = isset( $post['bus_start_time'] ) ? sanitize_text_field( $post['bus_start_time'] ) : '';
                $bus_id          = isset( $post['bus_id'] ) ? absint( $post['bus_id'] ) : 0;

                // Pickup Point
                if ( isset( $post['mage_pickpoint'] ) ) {
                    $pickpoint = sanitize_text_field( $post['mage_pickpoint'] );
                } else {
                    $pickpoint = 'n_a';
                }

                if ( $return_discount > 0 ) {
                    $is_return = 1;
                }

                $extra_per_bag_price = get_post_meta( $product_id, 'wbbm_extra_bag_price', true );
                $extra_per_bag_price = $extra_per_bag_price ? $extra_per_bag_price : 0;
                $extra_bag_price = 0;
                $es_price = 0;
                $custom_reg_additional = array();

                $custom_reg_user_flag = ( isset( $post['custom_reg_user'] ) && $post['custom_reg_user'] === 'yes' ) ? 'yes' : 'no';

                if ( isset( $post['custom_reg_user'] ) && $custom_reg_user_flag === 'yes' ) {

                    $wbbm_user_name = isset( $post['wbbm_user_name'] ) ? $this->sanitize_text_array( $post['wbbm_user_name'] ) : array();
                    $wbbm_user_email = isset( $post['wbbm_user_email'] ) ? $this->sanitize_text_array( $post['wbbm_user_email'] ) : array();
                    $wbbm_user_phone = isset( $post['wbbm_user_phone'] ) ? $this->sanitize_text_array( $post['wbbm_user_phone'] ) : array();
                    $wbbm_user_address = isset( $post['wbbm_user_address'] ) ? $this->sanitize_text_array( $post['wbbm_user_address'] ) : array();
                    $wbbm_user_gender = isset( $post['wbbm_user_gender'] ) ? $this->sanitize_text_array( $post['wbbm_user_gender'] ) : array();
                    $wbbm_user_type = isset( $post['wbbm_user_type'] ) ? $this->sanitize_text_array( $post['wbbm_user_type'] ) : array();
                    $wbbm_user_dob = isset( $post['wbbm_user_dob'] ) ? $this->sanitize_text_array( $post['wbbm_user_dob'] ) : array();
                    $wbbm_user_nationality = isset( $post['wbbm_user_nationality'] ) ? $this->sanitize_text_array( $post['wbbm_user_nationality'] ) : array();
                    $wbbm_user_flight_arrival_no = isset( $post['wbbm_user_flight_arrival_no'] ) ? $this->sanitize_text_array( $post['wbbm_user_flight_arrival_no'] ) : array();
                    $wbbm_user_flight_departure_no = isset( $post['wbbm_user_flight_departure_no'] ) ? $this->sanitize_text_array( $post['wbbm_user_flight_departure_no'] ) : array();
                    $bag_qty = isset( $post['extra_bag_quantity'] ) ? (array) $post['extra_bag_quantity'] : array();

                    $count_user = max( 0, count( $wbbm_user_type ) );
                    for ( $iu = 0; $iu < $count_user; $iu++ ) {

                        if ( ! empty( $wbbm_user_name ) && isset( $wbbm_user_name[ $iu ] ) && $wbbm_user_name[ $iu ] !== '' ) {
                            $user[ $iu ]['wbbm_user_name'] = wp_strip_all_tags( $wbbm_user_name[ $iu ] );
                        }

                        if ( ! empty( $wbbm_user_email ) && isset( $wbbm_user_email[ $iu ] ) && $wbbm_user_email[ $iu ] !== '' ) {
                            $user[ $iu ]['wbbm_user_email'] = wp_strip_all_tags( $wbbm_user_email[ $iu ] );
                        }

                        if ( ! empty( $wbbm_user_phone ) && isset( $wbbm_user_phone[ $iu ] ) && $wbbm_user_phone[ $iu ] !== '' ) {
                            $user[ $iu ]['wbbm_user_phone'] = wp_strip_all_tags( $wbbm_user_phone[ $iu ] );
                        }

                        if ( ! empty( $wbbm_user_address ) && isset( $wbbm_user_address[ $iu ] ) && $wbbm_user_address[ $iu ] !== '' ) {
                            $user[ $iu ]['wbbm_user_address'] = wp_strip_all_tags( $wbbm_user_address[ $iu ] );
                        }

                        if ( ! empty( $wbbm_user_gender ) && isset( $wbbm_user_gender[ $iu ] ) && $wbbm_user_gender[ $iu ] !== '' ) {
                            $user[ $iu ]['wbbm_user_gender'] = wp_strip_all_tags( $wbbm_user_gender[ $iu ] );
                        }

                        if ( ! empty( $wbbm_user_type ) && isset( $wbbm_user_type[ $iu ] ) && $wbbm_user_type[ $iu ] !== '' ) {
                            $user[ $iu ]['wbbm_user_type'] = wp_strip_all_tags( $wbbm_user_type[ $iu ] );
                        }

                        if ( ! empty( $wbbm_user_dob ) && isset( $wbbm_user_dob[ $iu ] ) && $wbbm_user_dob[ $iu ] !== '' ) {
                            $user[ $iu ]['wbbm_user_dob'] = wp_strip_all_tags( $wbbm_user_dob[ $iu ] );
                        }

                        if ( ! empty( $wbbm_user_nationality ) && isset( $wbbm_user_nationality[ $iu ] ) && $wbbm_user_nationality[ $iu ] !== '' ) {
                            $user[ $iu ]['wbbm_user_nationality'] = wp_strip_all_tags( $wbbm_user_nationality[ $iu ] );
                        }

                        if ( ! empty( $wbbm_user_flight_arrival_no ) && isset( $wbbm_user_flight_arrival_no[ $iu ] ) && $wbbm_user_flight_arrival_no[ $iu ] !== '' ) {
                            $user[ $iu ]['wbbm_user_flight_arrival_no'] = wp_strip_all_tags( $wbbm_user_flight_arrival_no[ $iu ] );
                        }

                        if ( ! empty( $wbbm_user_flight_departure_no ) && isset( $wbbm_user_flight_departure_no[ $iu ] ) && $wbbm_user_flight_departure_no[ $iu ] !== '' ) {
                            $user[ $iu ]['wbbm_user_flight_departure_no'] = wp_strip_all_tags( $wbbm_user_flight_departure_no[ $iu ] );
                        }

                        if ( ! empty( $bag_qty ) && isset( $bag_qty[ $iu ] ) && $bag_qty[ $iu ] !== '' ) {
                            $user[ $iu ]['extra_bag_quantity'] = wp_strip_all_tags( $bag_qty[ $iu ] );
                            $user[ $iu ]['wbtm_extra_bag_price'] = (float) $extra_per_bag_price;

                            $extra_bag_price += (int) $bag_qty[ $iu ] * (float) $extra_per_bag_price;
                        }

                        // Additional reg builder field
                        $reg_form_arr = maybe_unserialize( get_post_meta( $product_id, 'wbbm_attendee_reg_form', true ) );

                        if ( is_array( $reg_form_arr ) && sizeof( $reg_form_arr ) > 0 ) {
                            foreach ( $reg_form_arr as $builder ) {
                                $custom_reg_additional[ $iu ][] = array(
                                    'name'  => sanitize_text_field( $builder['field_label'] ),
                                    'value' => isset( $post[ $builder['field_id'] ][ $iu ] ) ? sanitize_text_field( $post[ $builder['field_id'] ][ $iu ] ) : '',
                                );
                            }
                        }
                    }
                } else {
                    // User type - default population
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

                // Extra Service
                $es_array = array();
                $es_price = 0;

                if ( isset( $post['extra_service_qty'] ) ) {

                    $extra_service_qty = (array) $post['extra_service_qty'];
                    $extra_services = get_post_meta( $bus_id, 'mep_events_extra_prices', true );

                    if ( ! empty( $extra_services ) ) :
                        $c = 0;

                        foreach ( $extra_services as $key => $field ) {
                            $input_qty = isset( $extra_service_qty[ $c ] ) ? absint( $extra_service_qty[ $c ] ) : 0;

                            $es_array[ $c ] = array(
                                'wbbm_es_name' => sanitize_text_field( $field['option_name'] ),
                                'wbbm_es_price' => (float) $field['option_price'],
                                'wbbm_es_input_qty' => $input_qty,
                                'wbbm_es_available_qty' => absint( $field['option_qty'] ),
                            );
                            $es_price += (float) $field['option_price'] * $input_qty;
                            $c++;
                        }
                    endif;
                }

                // Extra Service END
                $total = $total_fare + $es_price + $extra_bag_price;

                // Build cart item sanitized data
                $cart_item_data['wbbm_start_stops'] = $start_stops;
                $cart_item_data['wbbm_end_stops'] = $end_stops;
                $cart_item_data['wbbm_journey_date'] = $journey_date;
                $cart_item_data['wbbm_journey_time'] = $user_start_time;
                $cart_item_data['wbbm_bus_time'] = $bus_start_time;
                $cart_item_data['wbbm_total_seats'] = (int) $total_seat;

                $cart_item_data['wbbm_total_adult_qt'] = (int) $adult_seat;
                $cart_item_data['wbbm_total_adult_price'] = (float) $adult_fare;
                $cart_item_data['wbbm_per_adult_price'] = (float) $main_fare;
                $cart_item_data['wbbm_per_adult_price_original'] = (float) $main_fare_original;
                $cart_item_data['wbbm_per_adult_price_roundtrip'] = (float) $main_fare_roundtrip;

                $cart_item_data['wbbm_total_child_qt'] = (int) $total_child_seat;
                $cart_item_data['wbbm_total_child_price'] = (float) $total_child_fare;
                $cart_item_data['wbbm_per_child_price'] = (float) $child_fare;
                $cart_item_data['wbbm_per_child_price_original'] = (float) $child_fare_original;
                $cart_item_data['wbbm_per_child_price_roundtrip'] = (float) $child_fare_roundtrip;

                $cart_item_data['wbbm_total_infant_qt'] = (int) $total_infant_seat;
                $cart_item_data['wbbm_total_infant_price'] = (float) $total_infant_fare;
                $cart_item_data['wbbm_per_infant_price'] = (float) $infant_fare;
                $cart_item_data['wbbm_per_infant_price_original'] = (float) $infant_fare_original;
                $cart_item_data['wbbm_per_infant_price_roundtrip'] = (float) $infant_fare_roundtrip;

                $cart_item_data['wbbm_total_entire_qt'] = (int) $total_entire_seat;
                $cart_item_data['wbbm_total_entire_price'] = (float) $total_entire_fare;
                $cart_item_data['wbbm_per_entire_price'] = (float) $entire_fare;
                $cart_item_data['wbbm_per_entire_price_original'] = (float) $entire_fare_original;
                $cart_item_data['wbbm_per_entire_price_roundtrip'] = (float) $entire_fare_roundtrip;

                $cart_item_data['wbbm_passenger_info'] = $user;
                $cart_item_data['custom_reg_user'] = $custom_reg_user_flag;
                $cart_item_data['wbbm_passenger_info_additional'] = $custom_reg_additional;
                $cart_item_data['wbbm_extra_services'] = $es_array;
                $cart_item_data['wbbm_tp'] = (float) $total;
                $cart_item_data['wbbm_bus_id'] = (int) $bus_id;
                $cart_item_data['line_total'] = (float) $total;
                $cart_item_data['line_subtotal'] = (float) $total;
                $cart_item_data['quantity'] = (int) $total_seat;
                $cart_item_data['wbbm_id'] = (int) $product_id;
                $cart_item_data['is_return'] = (int) $is_return;
                $cart_item_data['total_fare_original'] = (float) $total_fare_original;
                $cart_item_data['total_fare_roundtrip'] = (float) $total_fare_roundtrip;
                $cart_item_data['pickpoint'] = $pickpoint;

                return $cart_item_data;
            }

            return $cart_item_data;
        }

        public function wbbm_display_custom_fields_text_cart( $item_data, $cart_item ) {

            if ( ! is_admin() ) {

                $total_extra_service_qty = 0;
                $eid = isset( $cart_item['wbbm_id'] ) ? absint( $cart_item['wbbm_id'] ) : 0;
                if ( get_post_type( $eid ) == 'wbbm_bus' ) {

                    $total_adult = isset( $cart_item['wbbm_total_adult_qt'] ) ? absint( $cart_item['wbbm_total_adult_qt'] ) : 0;
                    $total_adult_fare = isset( $cart_item['wbbm_per_adult_price'] ) ? (float) $cart_item['wbbm_per_adult_price'] : 0.0;

                    $total_child = isset( $cart_item['wbbm_total_child_qt'] ) ? absint( $cart_item['wbbm_total_child_qt'] ) : 0;
                    $total_child_fare = isset( $cart_item['wbbm_per_child_price'] ) ? (float) $cart_item['wbbm_per_child_price'] : 0.0;

                    $total_infant = isset( $cart_item['wbbm_total_infant_qt'] ) ? absint( $cart_item['wbbm_total_infant_qt'] ) : 0;
                    $total_infant_fare = isset( $cart_item['wbbm_per_infant_price'] ) ? (float) $cart_item['wbbm_per_infant_price'] : 0.0;

                    $total_entire = isset( $cart_item['wbbm_total_entire_qt'] ) ? absint( $cart_item['wbbm_total_entire_qt'] ) : 0;
                    $total_entire_fare = isset( $cart_item['wbbm_per_entire_price'] ) ? (float) $cart_item['wbbm_per_entire_price'] : 0.0;

                    $pickpoint = isset( $cart_item['pickpoint'] ) ? sanitize_text_field( $cart_item['pickpoint'] ) : '';
                    $currency = get_woocommerce_currency_symbol();

                    $passenger_info = isset( $cart_item['wbbm_passenger_info'] ) ? (array) $cart_item['wbbm_passenger_info'] : array();
                    $custom_reg_user = isset( $cart_item['custom_reg_user'] ) ? $cart_item['custom_reg_user'] : 'no';
                    $passenger_info_additional = isset( $cart_item['wbbm_passenger_info_additional'] ) ? (array) $cart_item['wbbm_passenger_info_additional'] : array();

                    $extra_per_bag_price = get_post_meta( $eid, 'wbbm_extra_bag_price', true );
                    $extra_per_bag_price = $extra_per_bag_price ? $extra_per_bag_price : 0;

                    // Check extra service qty
                    $wbbm_extra_services = isset( $cart_item['wbbm_extra_services'] ) ? (array) $cart_item['wbbm_extra_services'] : array();
                    if ( $wbbm_extra_services && is_array( $wbbm_extra_services ) ) {
                        foreach ( $wbbm_extra_services as $exs ) {
                            $total_extra_service_qty += isset( $exs['wbbm_es_input_qty'] ) ? absint( $exs['wbbm_es_input_qty'] ) : 0;
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
                                            $journey_date_label = wbbm_get_option(
                                                'wbbm_select_journey_date_text',
                                                'wbbm_label_setting_sec'
                                            );

                                            echo esc_html(
                                                $journey_date_label
                                                    ? $journey_date_label
                                                    : __( 'Journey Date', 'bus-booking-manager' )
                                            ) . ': ';
                                        ?>
                                    </strong>
                                    <?php echo ' ' . esc_html( get_wbbm_datetime( isset( $cart_item['wbbm_journey_date'] ) ? $cart_item['wbbm_journey_date'] : '', 'date' ) ); ?>
                                </li>
                                <li>
                                    <strong>
                                        <?php
                                            $starting_label = wbbm_get_option( 'wbbm_starting_text', 'wbbm_label_setting_sec' );
                                            echo esc_html( $starting_label ? $starting_label : __( 'Journey Time', 'bus-booking-manager' ) ) . ':';
                                        ?>

                                    </strong>
                                    <?php echo esc_html( get_wbbm_datetime( isset( $cart_item['wbbm_journey_time'] ) ? $cart_item['wbbm_journey_time'] : '', 'time' ) ); ?>
                                </li>
                                <li>
                                    <strong>
                                        <?php
                                            $boarding_point_label = wbbm_get_option(
                                                'wbbm_boarding_points_text',
                                                'wbbm_label_setting_sec'
                                            );

                                            echo esc_html(
                                                $boarding_point_label
                                                    ? $boarding_point_label
                                                    : __( 'Boarding Point', 'bus-booking-manager' )
                                            ) . ': ';
                                        ?>
                                    </strong>
                                    <?php echo esc_html( isset( $cart_item['wbbm_start_stops'] ) ? $cart_item['wbbm_start_stops'] : '' ); ?>
                                </li>
                                <li>
                                    <strong>
                                        <?php
                                            $dropping_point_label = wbbm_get_option(
                                                'wbbm_dropping_points_text',
                                                'wbbm_label_setting_sec'
                                            );

                                            echo esc_html(
                                                $dropping_point_label
                                                    ? $dropping_point_label
                                                    : __( 'Dropping Point', 'bus-booking-manager' )
                                            ) . ': ';
                                        ?>
                                    </strong>
                                    <?php echo esc_html( isset( $cart_item['wbbm_end_stops'] ) ? $cart_item['wbbm_end_stops'] : '' ); ?>
                                </li>

                                <?php if ( $pickpoint && $pickpoint != 'n_a' ) : ?>
                                    <li>
                                        <strong>
                                            <?php echo esc_html__( 'Pickup Area', 'bus-booking-manager' ) . ': '; ?>
                                        </strong>
                                        <?php echo esc_html( ucfirst( $pickpoint ) ); ?> </li>
                                <?php endif; ?>

                            </ul>
                        </div>
                    </div>
                    <?php

                    if ( 'no' === $custom_reg_user ) {
                        ?>

                        <ul class='wbbm-cart-price-table'>

                            <?php if ( $total_adult ) { ?>
                                <li>
                                    <strong>
                                        <?php
                                            $adult_label = wbbm_get_option(
                                                'wbbm_adult_text',
                                                'wbbm_label_setting_sec'
                                            );

                                            echo esc_html(
                                                $adult_label
                                                    ? $adult_label
                                                    : __( 'Adult', 'bus-booking-manager' )
                                            );

                                            echo ' (' . wp_kses_post( wc_price( $total_adult_fare ) ) . ' × ' . esc_html( $total_adult ) . ') = ';
                                        ?>
                                    </strong>
                                    <?php echo wp_kses_post(
                                        wc_price( $total_adult * $total_adult_fare )
                                    ); ?>
                                </li>
                            <?php } ?>

                            <?php if ( $total_child ) { ?>
                                <li>
                                    <strong>
                                        <?php
                                            $child_label = wbbm_get_option(
                                                'wbbm_child_text',
                                                'wbbm_label_setting_sec'
                                            );

                                            echo esc_html(
                                                $child_label
                                                    ? $child_label
                                                    : __( 'Child', 'bus-booking-manager' )
                                            );

                                            echo ' (' . wp_kses_post( wc_price( $total_child_fare ) ) . ' × ' . esc_html( $total_child ) . ') = ';
                                        ?>
                                    </strong>
                                    <?php echo wp_kses_post( wc_price( $total_child * $total_child_fare ) ); ?>
                                </li>
                            <?php } ?>

                            <?php if ( $total_infant ) { ?>
                                <li>
                                    <strong>
                                        <?php
                                            $infant_label = wbbm_get_option(
                                                'wbbm_infant_text',
                                                'wbbm_label_setting_sec'
                                            );

                                            echo esc_html(
                                                $infant_label
                                                    ? $infant_label
                                                    : __( 'Infant', 'bus-booking-manager' )
                                            );

                                            echo ' (' . wp_kses_post( wc_price( $total_infant_fare ) ) . ' × ' . esc_html( $total_infant ) . ') = ';
                                        ?>
                                    </strong>
                                    <?php echo wp_kses_post( wc_price( $total_infant * $total_infant_fare ) ); ?>
                                </li>
                            <?php } ?>

                        </ul>

                        <?php if ( $total_extra_service_qty ) : ?>
                            <li>
                                <strong>
                                    <?php
                                        $extra_services_label = wbbm_get_option(
                                            'wbbm_extra_services_text',
                                            'wbbm_label_setting_sec'
                                        );

                                        echo esc_html(
                                            $extra_services_label
                                                ? $extra_services_label
                                                : __( 'Extra Services', 'bus-booking-manager' )
                                        ) . ': ';
                                    ?>
                                </strong>
                                <ol>
                                    <?php
                                    foreach ( $wbbm_extra_services as $value ) {
                                        $es_name = isset( $value['wbbm_es_name'] ) ? sanitize_text_field( $value['wbbm_es_name'] ) : '';
                                        $es_price_val = isset( $value['wbbm_es_price'] ) ? (float) $value['wbbm_es_price'] : 0.0;
                                        $es_input_qty = isset( $value['wbbm_es_input_qty'] ) ? absint( $value['wbbm_es_input_qty'] ) : 0;
                                        $es_avail_qty = isset( $value['wbbm_es_available_qty'] ) ? absint( $value['wbbm_es_available_qty'] ) : 0;

                                        if ( $es_input_qty > $es_avail_qty ) :
                                            ?>
                                            <li><strong><?php echo esc_html( $es_name ); ?>: </strong><?php esc_html_e( 'Input service quantity has exceeded the limit!', 'bus-booking-manager' ); ?></li>
                                        <?php
                                        else :
                                            if ( $es_input_qty > 0 ) :
                                                ?>
                                                <li>
                                                    <strong><?php echo esc_html( $es_name ); ?>:</strong>
                                                    (<?php echo wp_kses_post( wc_price( $es_price_val ) ); ?> ×
                                                    <?php echo esc_html( $es_input_qty ); ?>)
                                                    =
                                                    <?php
                                                    echo wp_kses_post( wc_price( $es_price_val * $es_input_qty ) );
                                                    ?>
                                                </li>

                                            <?php
                                            endif;
                                        endif;
                                    }
                                    ?>
                                </ol>
                            </li>
                        <?php endif; ?>

                        <?php

                    } else {

                        if ( is_array( $passenger_info ) && sizeof( $passenger_info ) > 0 ) {
                            $i = 0;
                            foreach ( $passenger_info as $_passenger ) {
                                ?>
                                <ul class='wbbm-cart-price-table'>

                                    <?php if ( $total_adult > 0 && isset( $_passenger['wbbm_user_type'] ) && $_passenger['wbbm_user_type'] === 'adult' ) : ?>
                                        <li>
                                            <strong>
                                                <?php
                                                    $adult_label = wbbm_get_option(
                                                        'wbbm_adult_text',
                                                        'wbbm_label_setting_sec'
                                                    );

                                                    echo esc_html(
                                                        $adult_label
                                                            ? $adult_label
                                                            : __( 'Adult', 'bus-booking-manager' )
                                                    );
                                                ?>
                                            </strong>
                                            <?php echo wp_kses_post( wc_price( $total_adult_fare ) ); ?>
                                        </li>
                                    <?php endif; ?>

                                    <?php if ( $total_child > 0 && isset( $_passenger['wbbm_user_type'] ) && $_passenger['wbbm_user_type'] === 'child' ) : ?>
                                        <li>
                                            <strong>
                                                <?php
                                                    $child_label = wbbm_get_option(
                                                        'wbbm_child_text',
                                                        'wbbm_label_setting_sec'
                                                    );

                                                    echo esc_html(
                                                        $child_label
                                                            ? $child_label
                                                            : __( 'Child', 'bus-booking-manager' )
                                                    );
                                                ?>
                                            </strong>
                                            <?php echo wp_kses_post( wc_price( $total_child_fare ) ); ?>
                                        </li>
                                    <?php endif; ?>

                                    <?php if ( $total_infant > 0 && isset( $_passenger['wbbm_user_type'] ) && $_passenger['wbbm_user_type'] === 'infant' ) : ?>
                                        <li>
                                            <strong>
                                                <?php
                                                    $infant_label = wbbm_get_option(
                                                        'wbbm_infant_text',
                                                        'wbbm_label_setting_sec'
                                                    );

                                                    echo esc_html(
                                                        $infant_label
                                                            ? $infant_label
                                                            : __( 'Infant', 'bus-booking-manager' )
                                                    );
                                                ?>
                                            </strong>
                                            <?php echo wp_kses_post( wc_price( $total_infant_fare ) ); ?>
                                        </li>
                                    <?php endif; ?>

                                    <?php if ( $total_entire === 1 && $total_entire_fare > 0 && isset( $_passenger['wbbm_user_type'] ) && $_passenger['wbbm_user_type'] === 'entire' ) : ?>
                                        <li>
                                            <strong>
                                                <?php
                                                    $entire_bus_label = wbbm_get_option(
                                                        'wbbm_entire_bus_text',
                                                        'wbbm_label_setting_sec'
                                                    );

                                                    // Label output
                                                    echo esc_html(
                                                        $entire_bus_label
                                                            ? $entire_bus_label
                                                            : __( 'Entire Bus', 'bus-booking-manager' )
                                                    );

                                                    // Fare calculation output
                                                    echo ' (' 
                                                        . wp_kses_post( wc_price( $total_entire_fare ) ) 
                                                        . ' × ' 
                                                        . esc_html( $total_entire ) 
                                                        . ') = ' 
                                                        . wp_kses_post( wc_price( $total_entire_fare * $total_entire ) );
                                                ?>
                                            </strong>
                                        </li>
                                    <?php endif; ?>


                                    <?php if ( ! empty( $_passenger['extra_bag_quantity'] ) && absint( $_passenger['extra_bag_quantity'] ) > 0 ) : ?>
                                        <li>
                                            <strong>
                                                <?php
                                                    $extra_bag_label = wbbm_get_option(
                                                        'wbbm_extra_bag_text',
                                                        'wbbm_label_setting_sec'
                                                    );

                                                    echo esc_html(
                                                        $extra_bag_label
                                                            ? $extra_bag_label
                                                            : __( 'Extra Bag Qty', 'bus-booking-manager' )
                                                    ) . ': ';
                                                ?>
                                            </strong>
                                            <?php echo esc_html( absint( $_passenger['extra_bag_quantity'] ) ); ?>
                                        </li>
                                    <?php endif; ?>

                                    <?php if ( ! empty( $_passenger['wbtm_extra_bag_price'] ) && isset( $_passenger['extra_bag_quantity'] ) && absint( $_passenger['extra_bag_quantity'] ) > 0 ) : ?>
                                        <li>
                                            <strong>
                                                <?php
                                                    $extra_bag_price_label = wbbm_get_option(
                                                        'wbbm_extra_bag_price_text',
                                                        'wbbm_label_setting_sec'
                                                    );

                                                    echo esc_html(
                                                        $extra_bag_price_label
                                                            ? $extra_bag_price_label
                                                            : __( 'Extra Bag Price', 'bus-booking-manager' )
                                                    ) . ': ';
                                                ?>
                                            </strong>
                                            <?php
                                                echo ' (' 
                                                    . wp_kses_post( wc_price( $extra_per_bag_price ) ) 
                                                    . ' × ' 
                                                    . esc_html( absint( $_passenger['extra_bag_quantity'] ) ) 
                                                    . ') = ' 
                                                    . wp_kses_post( wc_price( (int) $_passenger['wbtm_extra_bag_price'] * (int) $_passenger['extra_bag_quantity'] ) );
                                            ?>
                                        </li>
                                    <?php endif; ?>

                                    <?php if ( ! empty( $_passenger['wbbm_user_name'] ) ) : ?>
                                        <li>
                                            <strong>
                                                <?php
                                                    $name_label = wbbm_get_option( 'wbbm_name_text', 'wbbm_label_setting_sec' );

                                                    echo $name_label 
                                                        ? esc_html( $name_label ) . ': ' 
                                                        : esc_html__( 'Name', 'bus-booking-manager' ) . ': ';
                                                ?>
                                            </strong>
                                            <?php echo esc_html( wp_strip_all_tags( $_passenger['wbbm_user_name'] ) ); ?></li>
                                    <?php endif; ?>

                                    <?php if ( ! empty( $_passenger['wbbm_user_email'] ) ) : ?>
                                        <li>
                                            <strong>
                                                <?php
                                                    $email_label = wbbm_get_option( 'wbbm_email_text', 'wbbm_label_setting_sec' );

                                                    echo $email_label 
                                                        ? esc_html( $email_label ) . ': ' 
                                                        : esc_html__( 'Email', 'bus-booking-manager' ) . ': ';
                                                ?>
                                            </strong>
                                            <?php echo esc_html( wp_strip_all_tags( $_passenger['wbbm_user_email'] ) ); ?></li>
                                    <?php endif; ?>

                                    <?php if ( ! empty( $_passenger['wbbm_user_phone'] ) ) : ?>
                                        <li>
                                            <strong>
                                                <?php

                                                    $phone_text = wbbm_get_option( 'wbbm_phone_text', 'wbbm_label_setting_sec' );

                                                    echo $phone_text
                                                        ? esc_html( $phone_text ) . ': '
                                                        : esc_html__( 'Phone', 'bus-booking-manager' ) . ': ';
                                                    ?>
                                            </strong>
                                            <?php echo esc_attr( wp_strip_all_tags( $_passenger['wbbm_user_phone'] ) ); ?>
                                        </li>
                                    <?php endif; ?>

                                    <?php if ( ! empty( $_passenger['wbbm_user_address'] ) ) : ?>
                                        <li>
                                            <strong>
                                                <?php
                                                    $address_text = wbbm_get_option( 'wbbm_address_text', 'wbbm_label_setting_sec' );

                                                    echo $address_text
                                                        ? esc_html( $address_text ) . ': '
                                                        : esc_html__( 'Address', 'bus-booking-manager' ) . ': ';
                                                    ?>
                                            </strong>
                                            <?php echo esc_html( wp_strip_all_tags( $_passenger['wbbm_user_address'] ) ); ?>
                                        </li>
                                    <?php endif; ?>

                                    <?php if ( ! empty( $_passenger['wbbm_user_gender'] ) ) : ?>
                                        <li>
                                            <strong>
                                                <?php
                                                    $gender_label = wbbm_get_option( 'wbbm_gender_text', 'wbbm_label_setting_sec' );

                                                    echo $gender_label
                                                        ? esc_html( $gender_label ) . ': '
                                                        : esc_html__( 'Gender', 'bus-booking-manager' ) . ': ';
                                                ?>
                                            </strong>
                                            <?php echo esc_html( wp_strip_all_tags( $_passenger['wbbm_user_gender'] ) ); ?>
                                        </li>
                                    <?php endif; ?>

                                    <?php if ( ! empty( $_passenger['wbbm_user_dob'] ) ) : ?>
                                        <li>
                                            <strong>
                                                <?php
                                                    $dob_label = wbbm_get_option( 'wbbm_dofbirth_text', 'wbbm_label_setting_sec' );

                                                    echo $dob_label
                                                        ? esc_html( $dob_label ) . ': '
                                                        : esc_html__( 'Date of Birth', 'bus-booking-manager' ) . ': ';
                                                ?>

                                            </strong>
                                            <?php echo esc_html( wp_strip_all_tags( $_passenger['wbbm_user_dob'] ) ); ?>
                                        </li>
                                    <?php endif; ?>

                                    <?php if ( ! empty( $_passenger['wbbm_user_nationality'] ) ) : ?>
                                        <li>
                                            <strong>
                                                <?php
                                                    $nationality_label = wbbm_get_option( 'wbbm_nationality_text', 'wbbm_label_setting_sec' );

                                                    echo $nationality_label
                                                        ? esc_html( $nationality_label ) . ': '
                                                        : esc_html__( 'Nationality', 'bus-booking-manager' ) . ': ';
                                                ?>

                                            </strong>
                                            <?php echo esc_html( wp_strip_all_tags( $_passenger['wbbm_user_nationality'] ) ); ?>
                                        </li>
                                    <?php endif; ?>

                                    <?php if ( ! empty( $_passenger['wbbm_user_flight_arrival_no'] ) ) : ?>
                                        <li>
                                            <strong>
                                                <?php
                                                    $fa_no_label = wbbm_get_option( 'wbbm_fa_no_text', 'wbbm_label_setting_sec' );

                                                    echo $fa_no_label
                                                        ? esc_html( $fa_no_label ) . ': '
                                                        : esc_html__( 'Flight Arrival No', 'bus-booking-manager' ) . ': ';
                                                ?>

                                            </strong>
                                            <?php echo esc_html( wp_strip_all_tags( $_passenger['wbbm_user_flight_arrival_no'] ) ); ?>
                                        </li>
                                    <?php endif; ?>

                                    <?php if ( ! empty( $_passenger['wbbm_user_flight_departure_no'] ) ) : ?>
                                        <li>
                                            <strong>
                                                <?php
                                                    $fd_no_label = wbbm_get_option( 'wbbm_fd_no_text', 'wbbm_label_setting_sec' );

                                                    echo $fd_no_label
                                                        ? esc_html( $fd_no_label ) . ': '
                                                        : esc_html__( 'Flight Departure No', 'bus-booking-manager' ) . ': ';
                                                ?>

                                            </strong>
                                            <?php echo esc_html( wp_strip_all_tags( $_passenger['wbbm_user_flight_departure_no'] ) ); ?>

                                        </li>
                                    <?php endif; ?>

                                    <?php
                                    if ( is_array( $passenger_info_additional ) && sizeof( $passenger_info_additional ) > 0 && isset( $passenger_info_additional[ $i ] ) ) :
                                        foreach ( $passenger_info_additional[ $i ] as $builder ) :
                                            ?>
                                            <li>
                                                <strong><?php echo esc_html( $builder['name'] ) . ': '; ?></strong>
                                                <?php echo esc_html( $builder['value'] ); ?>
                                            </li>

                                        <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </ul>

                                <?php
                                $i++;
                            }
                        }

                        ?>
                        <!-- Extra Service with passenger info -->
                        <ul>
                            <?php if ( $total_extra_service_qty ) : ?>
                                <li>
                                    <strong>
                                        <?php
                                            $extra_services_label = wbbm_get_option( 'wbbm_extra_services_text', 'wbbm_label_setting_sec' );

                                            echo $extra_services_label
                                                ? esc_html( $extra_services_label ) . ': '
                                                : esc_html__( 'Extra Services', 'bus-booking-manager' ) . ': ';
                                        ?>

                                    </strong>
                                    <ol>
                                        <?php
                                        foreach ( $wbbm_extra_services as $value ) {
                                            $es_name = isset( $value['wbbm_es_name'] ) ? sanitize_text_field( $value['wbbm_es_name'] ) : '';
                                            $es_price_val = isset( $value['wbbm_es_price'] ) ? (float) $value['wbbm_es_price'] : 0.0;
                                            $es_input_qty = isset( $value['wbbm_es_input_qty'] ) ? absint( $value['wbbm_es_input_qty'] ) : 0;
                                            $es_avail_qty = isset( $value['wbbm_es_available_qty'] ) ? absint( $value['wbbm_es_available_qty'] ) : 0;

                                            if ( $es_input_qty > $es_avail_qty ) :
                                                ?>
                                                <li>
                                                    <strong><?php echo esc_html( $es_name ); ?>: </strong>
                                                    <?php echo esc_html__( 'Input service quantity has exceeded the limit!', 'bus-booking-manager' ); ?>
                                                </li>
                                            <?php
                                            else :
                                                if ( $es_input_qty > 0 ) :
                                                    ?>
                                                    <li>
                                                        <strong><?php echo esc_html( $es_name ); ?>: </strong>
                                                        (
                                                            <?php echo wp_kses_post( wc_price( $es_price_val ) ); ?> 
                                                            x 
                                                            <?php echo esc_html( $es_input_qty ); ?>
                                                        ) = 
                                                        <?php echo wp_kses_post( wc_price( $es_price_val * $es_input_qty ) ); ?>
                                                    </li>
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
            $item_data[] = array( 'key' => '', 'value' => ob_get_clean() );
            return $item_data;
        }
    }