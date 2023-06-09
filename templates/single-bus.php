<?php
get_header();
the_post();
$SearchClass = new SearchClass;

$SearchClass->mage_search_form_horizontal(true);

$id = get_the_id();
$return = false;
$date_format        = get_option( 'date_format' );
$boarding_var = $return ? 'bus_end_route' : 'bus_start_route';
$dropping_var = $return ? 'bus_start_route' : 'bus_end_route';
$date_var = $return ? 'r_date' : 'j_date';
// $j_date = wbbm_convert_date_to_php(mage_get_isset($date_var));
$j_date = mage_get_isset($date_var);

$in_cart = mage_find_product_in_cart();

$type_id = get_post_meta($id, 'wbbm_bus_category', true);
if($type_id != ''){
    $type_array = get_term_by('term_id', $type_id, 'wbbm_bus_cat');
    $type_name = $type_array->name;
} else {
    $type_name = '';
}

// $available_seat = mage_available_seat(mage_get_isset($date_var));
$available_seat = wbbm_intermidiate_available_seat(@$_GET[$boarding_var], @$_GET[$dropping_var], wbbm_convert_date_to_php(mage_get_isset($date_var)));

$boarding = mage_get_isset($boarding_var);
$dropping = mage_get_isset($dropping_var);

$seat_price_adult = mage_seat_price($id, $boarding, $dropping, 'adult');
$seat_price_child = mage_seat_price($id, $boarding, $dropping, 'child');
$seat_price_infant = mage_seat_price($id, $boarding, $dropping, 'infant');
$seat_price_entire = mage_seat_price($id, $boarding, $dropping, 'entire');

$boarding_time = get_wbbm_datetime(boarding_dropping_time(false, $return),'time');
$dropping_time = get_wbbm_datetime(boarding_dropping_time(true, $return),'time');
$show_off_day = get_post_meta(get_the_ID(), 'show_off_day', true) ?: 'no';
$odd_list = $show_off_day === 'yes' ? mage_odd_list_check(false) : true;
$off_day = $show_off_day === 'yes' ? mage_off_day_check(false) : false;
$is_sell_off = get_post_meta($id, 'wbbm_sell_off', true);
$seat_available = get_post_meta($id, 'wbbm_seat_available', true);
$total_seat = get_post_meta(get_the_id(), 'wbbm_total_seat', true);
$c_time = current_time( 'timestamp' );
$is_on_date = false;
$bus_on_dates = array();
$show_operational_on_day = get_post_meta($id, 'show_operational_on_day', true) ?: 'no';
$bus_on_date = get_post_meta($id, 'wbtm_bus_on_date', true);
if( $bus_on_date != null && $show_operational_on_day === 'yes') {
    $bus_on_dates = explode( ', ', $bus_on_date );
    $is_on_date = true;
}
$is_price_zero_allow = get_post_meta($id, 'wbbm_price_zero_allow', true);
$entire_bus_booking = wbbm_get_option('wbbm_entire_bus_booking_switch', 'wbbm_general_setting_sec');

// Off dates
$off_day_sche       = get_post_meta($id, 'wbtm_offday_schedule',true);
$all_off_dates      = array();
$off_date_status    =  false;
if(! empty($off_day_sche) && $show_off_day === 'yes'):
    foreach ($off_day_sche as $off_day_sch) {

        $begin      = new DateTime($off_day_sch['from_date']);
        $end        = new DateTime($off_day_sch['to_date']);
        $end        = $end->modify( '+1 day' );
        $interval   = new DateInterval('P1D');
        $daterange  = new DatePeriod($begin, $interval ,$end);

        foreach ($daterange as $date) {
            $all_off_dates[] = $date->format('Y-m-d');      
        }
    }

    if(in_array($j_date,$all_off_dates)){
        $off_date_status =  true;
    } else{
        $off_date_status =  false;
    }
endif;
// End Off dates

?>
    <div class="mage_container bus_detail">
        <?php do_action( 'wbbm_before_single_product' ); ?>
        <?php do_action( 'woocommerce_before_single_product' ); ?>
        <div class="mage_search_list <?php echo $in_cart ? 'booked' : ''; ?>" data-seat-available="<?php echo $available_seat; ?>">
            <form action="" method="post">
                <div class="mage_flex_equal xs_not_flex">
                    <div class="mage_thumb">

                        <?php

                        if(has_post_thumbnail()) {
                            the_post_thumbnail('full');
                        } else {
                            echo '<img src="'.PLUGIN_ROOT. '/images/bus-placeholder.png'.'" loading="lazy" />';
                        }

                        ?>


                    </div>
                    <div class="mage_bus_details">
                        <div class="mage_bus_info">
                            <h3><?php the_title(); ?></h3>
                            <?php if($type_name){ ?>
                            <p>
                                <strong><?php echo wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec', __('Type', 'bus-booking-manager')); echo ':'; ?></strong>
                                <?php echo $type_name; ?>
                            </p>
                            <?php } ?>
                            <p>
                                <strong><?php echo wbbm_get_option('wbbm_bus_no_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_bus_no_text', 'wbbm_label_setting_sec') : _e('Bus No', 'bus-booking-manager'); echo ':'; ?></strong>
                                <?php echo get_post_meta(get_the_id(), 'wbbm_bus_no', true); ?>
                            </p>
                            <?php if (($seat_price_adult > 0 || $is_price_zero_allow == 'on') && $odd_list && !$off_day) { ?>
                                <p>
                                    <strong><?php echo wbbm_get_option('wbbm_boarding_points_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_boarding_points_text', 'wbbm_label_setting_sec') : _e('Boarding', 'bus-booking-manager'); echo ':'; ?></strong>
                                    <?php echo $boarding; ?>
                                    <strong>(<?php echo $boarding_time; ?>)</strong>
                                </p>
                                <p>
                                    <strong><?php echo wbbm_get_option('wbbm_dropping_points_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_dropping_points_text', 'wbbm_label_setting_sec') : _e('Dropping', 'bus-booking-manager'); echo ':'; ?></strong>
                                    <?php echo $dropping; ?>
                                    <strong>(<?php echo $dropping_time; ?>)</strong>

                                    <?php 
                                        $dropoff_desc = (get_term_by('name', $dropping, 'wbbm_bus_stops') ? get_term_by('name', $dropping, 'wbbm_bus_stops')->description : '');
                                        if($dropoff_desc) {
                                            echo '<span class="wbbm_dropoff-desc wbbm_dropoff-desc-single">'.$dropoff_desc.'</span>';
                                        }
                                    ?>
                                </p>
                            <?php  } ?>
                            <p>
                                <strong><?php echo wbbm_get_option('wbbm_total_seat_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_total_seat_text', 'wbbm_label_setting_sec') : _e('Total Seat', 'bus-booking-manager'); echo ':'; ?></strong>
                                <?php echo get_post_meta(get_the_id(), 'wbbm_total_seat', true); ?>
                            </p>

                            <?php if (($seat_price_adult > 0 || $is_price_zero_allow == 'on') && $odd_list && !$off_day && ($off_date_status == false)) { ?>
                                <?php if( $is_sell_off != 'on' ) : ?>
                                    <?php if($seat_available && $seat_available == 'on') : ?>
                                        <p>
                                            <strong><?php echo $available_seat; ?></strong>
                                            <?php echo wbbm_get_option('wbbm_seats_available_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_seats_available_text', 'wbbm_label_setting_sec') : _e('Seat Available', 'bus-booking-manager'); echo ':'; ?>
                                        </p>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if ($in_cart) { ?>
                                    <p class="already_cart"><span class="fa fa-cart-plus"></span><?php echo wbbm_get_option('wbbm_item_in_cart_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_item_in_cart_text', 'wbbm_label_setting_sec') : _e('Item has been added to cart', 'bus-booking-manager'); ?></p>
                                <?php } ?>
                                <?php
                                $is_price_zero_allow = get_post_meta($id, 'wbbm_price_zero_allow', true);
                                ?>

                                <p><strong><?php echo wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec') : _e('Fare', 'bus-booking-manager'); echo ':'; ?></strong></p>

                                <input type="hidden" name="available_quantity" value="<?php echo $available_seat?>">

                                <div class="mage_center_space mar_b">
                                    <div>
                                        <p>
                                            <strong><?php echo wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') : _e('Adult', 'bus-booking-manager'); echo ':'; ?></strong>
                                            <?php echo wc_price($seat_price_adult); ?>/
                                            <small><?php _e('Ticket', 'bus-booking-manager'); ?></small>
                                        </p>
                                    </div>
                                    <?php mage_qty_box($seat_price_adult, 'adult_quantity', false); ?>
                                </div>

                                <?php
                                if ( ($seat_price_child > 0) || ($is_price_zero_allow == 'on') ) : ?>
                                    <div class="mage_center_space mar_b">
                                        <p>
                                            <strong><?php echo wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') : _e('Child', 'bus-booking-manager'); echo ':'; ?></strong>
                                            <?php echo wc_price($seat_price_child); ?>/
                                            <small><?php _e('Ticket', 'bus-booking-manager'); ?></small>
                                        </p>
                                        <?php mage_qty_box($seat_price_child, 'child_quantity', false); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ( ($seat_price_infant > 0) || ($is_price_zero_allow == 'on') ) : ?>
                                    <div class="mage_center_space mar_b">
                                        <p>
                                            <strong><?php echo wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') : _e('Infant', 'bus-booking-manager'); echo ':'; ?></strong>
                                            <?php echo wc_price($seat_price_infant); ?>/
                                            <small><?php _e('Ticket', 'bus-booking-manager'); ?></small>
                                        </p>
                                        <?php mage_qty_box($seat_price_infant, 'infant_quantity', false); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (($entire_bus_booking == 'on') && ($available_seat == $total_seat) && ($seat_price_entire > 0) ) : ?>
                                    <div class="mage_center_space mar_b">
                                        <p>
                                            <strong><?php echo wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') : _e('Entire Bus', 'bus-booking-manager'); echo ':'; ?></strong>
                                            <?php echo wc_price($seat_price_entire); ?>
                                        </p>
                                        <?php echo wbbm_entire_switch($seat_price_entire, 'entire_quantity', false); ?>
                                    </div>
                                <?php endif; ?>

                            <?php } ?>
                            <?php
                                // Pickup Point
                                $boarding_point = isset($_GET[$boarding_var]) ? strip_tags($_GET[$boarding_var]) : '';
                                $boarding_point_slug = strtolower($boarding_point);
                                $boarding_point_slug = preg_replace('/[^A-Za-z0-9-]/', '_', $boarding_point_slug);
                                $pickpoints = get_post_meta(get_the_id(), 'wbbm_selected_pickpoint_name_'.$boarding_point_slug, true);
                                $is_enable_pickpoint = get_post_meta($id, 'show_pickup_point', true);
                                if($pickpoints && $is_enable_pickpoint == 'yes') {
                                    $pickpoints = is_string($pickpoints) ? maybe_unserialize($pickpoints) : $pickpoints;
                                    if(!empty($pickpoints)) { ?>
                                        <div class="mage-form-field mage-field-inline">
                                            <label for="mage_pickpoint"><?php _e('Select Pickup Area', 'bus-booking-manager'); ?></label>
                                            <select name="mage_pickpoint" class="mage_pickpoint">
                                                <option value=""><?php _e('Select your Pickup Area', 'bus-booking-manager'); ?></option>
                                                <?php
                                                foreach($pickpoints as $pickpoint) {
                                                    $time_html = $pickpoint["time"] ? ' ('.get_wbbm_datetime($pickpoint["time"], 'time').')' : '';
                                                    $time_value = $pickpoint["time"] ? '-'. get_wbbm_datetime($pickpoint["time"], 'time') : '';
                                                    $pick_desc = (get_term_by('name', $pickpoint["pickpoint"], 'wbbm_bus_pickpoint') ? get_term_by('name', $pickpoint["pickpoint"], 'wbbm_bus_pickpoint')->description : '');
                                                    echo '<option value="'. $pickpoint["pickpoint"] . $time_value .'">'. ucfirst($pickpoint["pickpoint"]) . $time_html .'</option>';
                                                    echo ($pick_desc ? '<option disabled>&nbsp;&nbsp; '.$pick_desc.'</option>' : '');
                                                } ?>
                                            </select>
                                        </div>
                                    <?php
                                    }
                                }
                                // Pickup Point END
                            ?>
                            <?php the_content(); ?>
                            <div class="mage_flex_equal">
                                <div>
                                    <h4 class="mar_b bor_tb">
                                        <?php echo wbbm_get_option('wbbm_boarding_points_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_boarding_points_text', 'wbbm_label_setting_sec') : _e('Boarding Points', 'bus-booking-manager'); ?>
                                    </h4>
                                    <ul>
                                        <?php
                                        $start_stops = get_post_meta(get_the_id(), 'wbbm_bus_bp_stops', true);
                                        foreach ($start_stops as $_start_stops) {
                                            echo "<li><span class='fa fa-map-marker mar_r'></span>" . $_start_stops['wbbm_bus_bp_stops_name'] . "</li>";
                                        }
                                        ?>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="mar_b bor_tb">
                                        <?php echo wbbm_get_option('wbbm_dropping_points_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_dropping_points_text', 'wbbm_label_setting_sec') : _e('Dropping Points', 'bus-booking-manager'); ?>
                                    </h4>
                                    <ul>
                                        <?php
                                        $end_stops = get_post_meta(get_the_id(), 'wbbm_bus_next_stops', true);
                                        foreach ($end_stops as $_end_stops) {
                                            echo "<li><span class='fa fa-map-marker mar_r'></span>" . $_end_stops['wbbm_bus_next_stops_name'] . "</li>";
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                            <div class="mage_customer_info_area">
                    <?php

                    $date = isset($_GET[$date_var]) ? mage_wp_date($_GET[$date_var], 'Y-m-d') : date('Y-m-d');
                    $start = isset($_GET[$boarding_var]) ? strip_tags($_GET[$boarding_var]) : '';
                    $end = isset($_GET[$dropping_var]) ? strip_tags($_GET[$dropping_var]) : '';
                    hidden_input_field('bus_id', $id);
                    hidden_input_field('journey_date', $date);
                    hidden_input_field('start_stops', $start);
                    hidden_input_field('end_stops', $end);
                    hidden_input_field('user_start_time', $boarding_time);
                    hidden_input_field('bus_start_time', $dropping_time);
                    ?>
                    <div class="adult"></div>
                    <div class="child"></div>
                    <div class="infant"></div>
                    <div class="entire"></div>
                </div>
                <?php
                // Extra Service Section
                if($available_seat > 0){
                    wbbm_extra_services_section($id);
                }

                // Operatinal on day off day check
                // Offday schedule check
                $bus_stops_times = get_post_meta(get_the_ID(), 'wbbm_bus_bp_stops', true);
                $bus_offday_schedules = get_post_meta(get_the_ID(), 'wbtm_offday_schedule', true);

                $start_time = '';
                foreach ($bus_stops_times as $stop) {
                    if(isset($_GET[$boarding_var])) {
                        if ($stop['wbbm_bus_bp_stops_name'] == $_GET[$boarding_var]) {
                            $start_time = $stop['wbbm_bus_bp_start_time'];
                        }
                    }
                }

                $start_time = wbbm_time_24_to_12($start_time);

                if(wbbm_buffer_time_calculation($start_time, $j_date)) {
                    if ($j_date != '' && $boarding != '' && $dropping != '') {
                        if ($is_on_date) {
                            $j_date_c = mage_wp_date($j_date, 'Y-m-d');
                            if (in_array($j_date_c, $bus_on_dates)) {
                                mage_book_now_area($available_seat);
                            } else {
                                echo '<span class="mage_error" style="display: block;text-align: center;padding: 5px;margin: 10px 0 0 0;">' . date($date_format, strtotime(mage_get_isset($date_var))) . ' Operational Off day !' . '</span>';
                            }
                        } else {

                            $offday_current_bus = false;
                            if (!empty($bus_offday_schedules)) {




                                $s_datetime = new DateTime(mage_wp_date($j_date, 'Y-m-d') . ' ' . $start_time);


                                foreach ($bus_offday_schedules as $item) {



                                    $c_iterate_date_from = mage_wp_date($item['from_date'], 'Y-m-d');


                                    $c_iterate_datetime_from = new DateTime($c_iterate_date_from . ' ' . $item['from_time']);

                                    $c_iterate_date_to = mage_wp_date($item['to_date'], 'Y-m-d');
                                    $c_iterate_datetime_to = new DateTime($c_iterate_date_to . ' ' . $item['to_time']);

                                    if ($s_datetime >= $c_iterate_datetime_from && $s_datetime <= $c_iterate_datetime_to) {
                                        $offday_current_bus = true;
                                        break;
                                    }
                                }
                            }

                            // Check Offday and date
                            if (!$offday_current_bus && !mage_off_day_check($return)) {
                                mage_book_now_area($available_seat);
                            }
                        }
                    }
                }

                ?>
                        </div>
                    </div>
                </div>

            </form>
            <?php do_action('mage_multipurpose_reg'); ?>
        </div>
        <?php do_action('after-single-bus'); ?>
        <?php do_action('wbbm_prevent_form_resubmission'); ?>
    </div>
<?php get_footer(); ?>
