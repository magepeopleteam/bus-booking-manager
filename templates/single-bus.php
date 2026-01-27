<?php if ( wp_is_block_theme() ) {  ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<?php
	$block_content = do_blocks( '
		<!-- wp:group {"layout":{"type":"constrained"}} -->
		<div class="wp-block-group">
		<!-- wp:post-content /-->
		</div>
		<!-- /wp:group -->'
 	);
    wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="wp-site-blocks">
<header class="wp-block-template-part site-header">
    <?php block_header_area(); ?>
</header>
</div>
<?php
} else {
    get_header();	
    the_post();
}

$WbbmSearchClass = new SearchClass;
$WbbmSearchClass->mage_search_form_horizontal(true);
$id = get_the_ID();
$WbbmReturn = false;
$WbbmDate_format = get_option('date_format');
$WbbmBoarding_var = $WbbmReturn ? 'bus_end_route' : 'bus_start_route';
$WbbmDropping_var = $WbbmReturn ? 'bus_start_route' : 'bus_end_route';
$WbbmDate_var = $WbbmReturn ? 'r_date' : 'j_date';
$WbbmJ_date = mage_get_isset($WbbmDate_var);
$WbbmIn_cart = mage_find_product_in_cart();
$Wbbm_type_id = get_post_meta($id, 'wbbm_bus_category', true);
$Wbbm_type_name = '';

if ($Wbbm_type_id != '') {
    $Wbbm_type_array = get_term_by('term_id', $Wbbm_type_id, 'wbbm_bus_cat');
    $Wbbm_type_name = $Wbbm_type_array ? esc_html($Wbbm_type_array->name) : '';
}

// Use sanitized values for query parameters
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$Wbbm_boarding = isset($_GET[$WbbmBoarding_var]) ? sanitize_text_field(wp_unslash($_GET[$WbbmBoarding_var])) : '';
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$Wbbm_dropping = isset($_GET[$WbbmDropping_var]) ? sanitize_text_field(wp_unslash($_GET[$WbbmDropping_var])) : '';
$Wbbm_available_seat = wbbm_intermidiate_available_seat($Wbbm_boarding, $Wbbm_dropping, wbbm_convert_date_to_php(mage_get_isset($WbbmDate_var)));
$Wbbm_wbbm_cart_qty = wbbm_get_cart_item($id, mage_get_isset($WbbmDate_var));
$Wbbm_available_seat -= $Wbbm_wbbm_cart_qty;

// Seat prices
$Wbbm_seat_price_adult = mage_seat_price($id, $Wbbm_boarding, $Wbbm_dropping, 'adult');
$Wbbm_seat_price_child = mage_seat_price($id, $Wbbm_boarding, $Wbbm_dropping, 'child');
$Wbbm_seat_price_infant = mage_seat_price($id, $Wbbm_boarding, $Wbbm_dropping, 'infant');
$Wbbm_seat_price_entire = mage_seat_price($id, $Wbbm_boarding, $Wbbm_dropping, 'entire');
$Wbbm_boarding_time = wbbm_get_datetime(wbbm_boarding_dropping_time(false, $WbbmReturn), 'time');
$Wbbm_dropping_time = wbbm_get_datetime(wbbm_boarding_dropping_time(true, $WbbmReturn), 'time');

$Wbbm_show_off_day = get_post_meta(get_the_ID(), 'show_off_day', true) ?: 'no';
$Wbbm_odd_list = $Wbbm_show_off_day === 'yes' ? mage_odd_list_check(false) : true;
$Wbbm_off_day = $Wbbm_show_off_day === 'yes' ? mage_off_day_check(false) : false;
$wbbm_is_sell_off = get_post_meta($id, 'wbbm_sell_off', true);
$wbbm_seat_available = get_post_meta($id, 'wbbm_seat_available', true);
$wbbm_total_seat = get_post_meta(get_the_ID(), 'wbbm_total_seat', true);
$wbbm_c_time = current_time('timestamp');
$wbbm_is_on_date = false;
$wbbm_bus_on_dates = array();
$wbbm_show_operational_on_day = get_post_meta($id, 'show_operational_on_day', true) ?: 'no';
$wbbm_bus_on_date = get_post_meta($id, 'wbtm_bus_on_date', true);

if ($wbbm_bus_on_date != null && $wbbm_show_operational_on_day === 'yes') {
    $wbbm_bus_on_dates = is_array($wbbm_bus_on_date) ? $wbbm_bus_on_date : [];
    $wbbm_is_on_date = true;
}

$wbbm_is_price_zero_allow = get_post_meta($id, 'wbbm_price_zero_allow', true);
$wbbm_entire_bus_booking = wbbm_get_option('wbbm_entire_bus_booking_switch', 'wbbm_general_setting_sec');

// Off dates
$wbbm_off_day_sche = get_post_meta($id, 'wbtm_offday_schedule', true);
$wbbm_all_off_dates = [];
$wbbm_off_date_status = false;

if (!empty($wbbm_off_day_sche) && $Wbbm_show_off_day === 'yes') {
    foreach ($wbbm_off_day_sche as $wbbm_off_day_sch) {
        $wbbm_begin = new DateTime($wbbm_off_day_sch['from_date']);
        $wbbm_end = new DateTime($wbbm_off_day_sch['to_date']);
        $wbbm_end = $wbbm_end->modify('+1 day');
        $wbbm_interval = new DateInterval('P1D');
        $wbbm_daterange = new DatePeriod($wbbm_begin, $wbbm_interval, $wbbm_end);
        foreach ($wbbm_daterange as $wbbm_date) {
            $wbbm_all_off_dates[] = $wbbm_date->format('Y-m-d');
        }
    }
    if (in_array($WbbmJ_date, $wbbm_all_off_dates)) {
        $wbbm_off_date_status = true;
    }
}
?>
<div class="mage_container bus_detail">
    <div class="mage_container bus_detail_page_wrapper"> <!-- Added Wrapper -->
    <?php do_action('wbbm_before_single_product'); ?>
    <?php do_action('wbbm_woocommerce_before_single_product'); ?>
    <?php 
        if ( function_exists('wc_print_notices') ) {
            wc_print_notices(); 
        }
    ?>
    <div class="mage_search_list <?php echo esc_attr($WbbmIn_cart ? 'booked' : ''); ?>" data-seat-available="<?php echo esc_attr($Wbbm_available_seat); ?>">
        <form action="" method="post">
            <?php wp_nonce_field('mage_book_now_area', 'mage_book_now_area_nonce'); ?>
            <div class="mage_flex_equal xs_not_flex">
                <div class="mage_thumb">
                    <?php
                    if (has_post_thumbnail()) {
                        the_post_thumbnail('full');
                    } else {
                        echo '<img src="' . esc_url(PLUGIN_ROOT . '/images/bus-placeholder.png') . '" loading="lazy" />';
                    }
                    ?>
                </div>
                <div class="mage_bus_details">
                    <div class="mage_bus_info">
                        <h3><?php the_title(); ?></h3>
                        <?php if ($Wbbm_type_name) { ?>
                            <p>
                                <strong><?php echo esc_html(wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec', __('Type', 'bus-booking-manager'))) . ':'; ?></strong>
                                <?php echo esc_html($Wbbm_type_name); ?>
                            </p>
                        <?php } ?>
                        <p>
                            <strong><?php echo esc_html(wbbm_get_option('wbbm_bus_no_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_bus_no_text', 'wbbm_label_setting_sec') : __('Bus No', 'bus-booking-manager')) . ':'; ?></strong>
                            <?php echo esc_html(get_post_meta(get_the_ID(), 'wbbm_bus_no', true)); ?>
                        </p>
                        <?php if (($Wbbm_seat_price_adult > 0 || $wbbm_is_price_zero_allow == 'on') && $Wbbm_odd_list && !$Wbbm_off_day) { ?>
                            <p>
                                <strong><?php echo esc_html(wbbm_get_option('wbbm_boarding_points_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_boarding_points_text', 'wbbm_label_setting_sec') : __('Boarding', 'bus-booking-manager')) . ':'; ?></strong>
                                <?php echo esc_html($Wbbm_boarding); ?>
                                <strong>(<?php echo esc_html($Wbbm_boarding_time); ?>)</strong>
                            </p>
                            <p>
                                <strong><?php echo esc_html(wbbm_get_option('wbbm_dropping_points_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_dropping_points_text', 'wbbm_label_setting_sec') : __('Dropping', 'bus-booking-manager')) . ':'; ?></strong>
                                <?php echo esc_html($Wbbm_boarding); ?>
                                <strong>(<?php echo esc_html($Wbbm_dropping_time); ?>)</strong>
                                <?php
                                $wbbm_dropoff_desc = (get_term_by('name', $Wbbm_boarding, 'wbbm_bus_stops') ? get_term_by('name', $Wbbm_boarding, 'wbbm_bus_stops')->description : '');
                                if ($wbbm_dropoff_desc) {
                                    echo '<span class="wbbm_dropoff-desc wbbm_dropoff-desc-single">' . esc_html($wbbm_dropoff_desc) . '</span>';
                                }
                                ?>
                            </p>
                        <?php } ?>
                        <p>
                            <strong><?php echo esc_html(wbbm_get_option('wbbm_total_seat_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_total_seat_text', 'wbbm_label_setting_sec') : __('Total Seat', 'bus-booking-manager')) . ':'; ?></strong>
                            <?php echo esc_html(get_post_meta(get_the_ID(), 'wbbm_total_seat', true)); ?>
                        </p>
                        <?php if (($Wbbm_seat_price_adult > 0 || $wbbm_is_price_zero_allow == 'on') && $Wbbm_odd_list && !$Wbbm_off_day && ($wbbm_off_date_status == false)) { ?>
                            <?php if ($wbbm_is_sell_off != 'on') : ?>
                                <?php if ($wbbm_seat_available && $wbbm_seat_available == 'on') : ?>
                                    <p>
                                        <strong><?php echo esc_html($Wbbm_available_seat); ?></strong>
                                        <?php echo esc_html(wbbm_get_option('wbbm_seats_available_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_seats_available_text', 'wbbm_label_setting_sec') : __('Seat Available', 'bus-booking-manager')) . ':'; ?>
                                    </p>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ($WbbmIn_cart) { ?>
                                <p class="already_cart"><span class="fa fa-cart-plus"></span><?php echo esc_html(wbbm_get_option('wbbm_item_in_cart_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_item_in_cart_text', 'wbbm_label_setting_sec') : __('Item has been added to cart', 'bus-booking-manager')); ?></p>
                            <?php } ?>
                            <p><strong><?php echo esc_html(wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec') : __('Fare', 'bus-booking-manager')) . ':'; ?></strong></p>
                            <input type="hidden" name="available_quantity" value="<?php echo esc_attr($Wbbm_available_seat); ?>">
                            <div class="mage_center_space mar_b">
                                <div>
                                    <p>
                                        <strong><?php echo esc_html(wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') : __('Adult', 'bus-booking-manager')) . ':'; ?></strong>
                                        <?php echo wp_kses_post(wc_price($Wbbm_seat_price_adult)); ?>/
                                        <small><?php esc_html_e('Ticket', 'bus-booking-manager'); ?></small>
                                    </p>
                                </div>
                                <?php mage_qty_box($Wbbm_seat_price_adult, 'adult_quantity', false); ?>
                            </div>
                            <?php if (($Wbbm_seat_price_child > 0) || ($wbbm_is_price_zero_allow == 'on')) : ?>
                                <div class="mage_center_space mar_b">
                                    <p>
                                        <strong><?php echo esc_html(wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') : __('Child', 'bus-booking-manager')) . ':'; ?></strong>
                                        <?php echo wp_kses_post(wc_price($Wbbm_seat_price_child)); ?>/
                                        <small><?php esc_html_e('Ticket', 'bus-booking-manager'); ?></small>
                                    </p>
                                    <?php mage_qty_box($Wbbm_seat_price_child, 'child_quantity', false); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (($Wbbm_seat_price_infant > 0) || ($wbbm_is_price_zero_allow == 'on')) : ?>
                                <div class="mage_center_space mar_b">
                                    <p>
                                        <strong><?php echo esc_html(wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') : __('Infant', 'bus-booking-manager')) . ':'; ?></strong>
                                        <?php echo wp_kses_post(wc_price($Wbbm_seat_price_infant)); ?>/
                                        <small><?php esc_html_e('Ticket', 'bus-booking-manager'); ?></small>
                                    </p>
                                    <?php mage_qty_box($Wbbm_seat_price_infant, 'infant_quantity', false); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (($wbbm_entire_bus_booking == 'on') && ($Wbbm_available_seat == $wbbm_total_seat) && ($Wbbm_seat_price_entire > 0)) : ?>
                                <div class="mage_center_space mar_b">
                                    <p>
                                        <strong><?php echo esc_html(wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') : __('Entire Bus', 'bus-booking-manager')) . ':'; ?></strong>
                                        <?php echo esc_html(wc_price($Wbbm_seat_price_entire)); ?>
                                    </p>
                                    <?php echo wp_kses_post(wbbm_entire_switch($Wbbm_seat_price_entire, 'entire_quantity', false)); ?>
                                </div>
                            <?php endif; ?>
                        <?php } ?>
                        <?php
                        // Pickup Point
                        $wbbm_boarding_point = $Wbbm_boarding;
                        $wbbm_boarding_point_slug = strtolower($wbbm_boarding_point);
                        $wbbm_boarding_point_slug = preg_replace('/[^A-Za-z0-9-]/', '_', $wbbm_boarding_point_slug);
                        $wbbm_pickpoints = get_post_meta(get_the_ID(), 'wbbm_selected_pickpoint_name_' . $wbbm_boarding_point_slug, true);
                        $wbbm_is_enable_pickpoint = get_post_meta($id, 'show_pickup_point', true);
                        if ($wbbm_pickpoints && $wbbm_is_enable_pickpoint == 'yes') {
                            $wbbm_pickpoints = is_string($wbbm_pickpoints) ? maybe_unserialize($wbbm_pickpoints) : $wbbm_pickpoints;
                            if (!empty($wbbm_pickpoints)) { ?>
                                <div class="mage-form-field mage-field-inline">
                                    <label for="mage_pickpoint"><?php esc_html_e('Select Pickup Area', 'bus-booking-manager'); ?></label>
                                    <select name="mage_pickpoint" class="mage_pickpoint">
                                        <option value=""><?php esc_html_e('Select your Pickup Area', 'bus-booking-manager'); ?></option>
                                        <?php
                                        foreach ($wbbm_pickpoints as $wbbm_pickpoint) {
                                            $wbbm_time_html = $wbbm_pickpoint["time"] ? ' (' . esc_html(wbbm_get_datetime($wbbm_pickpoint["time"], 'time')) . ')' : '';
                                            $wbbm_time_value = $wbbm_pickpoint["time"] ? '-' . esc_html(wbbm_get_datetime($wbbm_pickpoint["time"], 'time')) : '';
                                            $wbbm_pick_desc = (get_term_by('name', $wbbm_pickpoint["pickpoint"], 'wbbm_bus_pickpoint') ? get_term_by('name', $wbbm_pickpoint["pickpoint"], 'wbbm_bus_pickpoint')->description : '');
                                            echo '<option value="' . esc_attr($wbbm_pickpoint["pickpoint"] . $wbbm_time_value) . '">' . esc_html(ucfirst($wbbm_pickpoint["pickpoint"])) . esc_html($wbbm_time_html) . '</option>';
                                            echo ($wbbm_pick_desc ? '<option disabled>&nbsp;&nbsp; ' . esc_html($wbbm_pick_desc) . '</option>' : '');
                                        } ?>
                                    </select>
                                </div>
                        <?php }
                        }
                        // Pickup Point END
                        ?>
                        <?php the_content(); ?>
                        <div class="mage_flex_equal">
                            <div>
                                <h4 class="mar_b bor_tb">
                                    <?php echo esc_html(wbbm_get_option('wbbm_boarding_points_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_boarding_points_text', 'wbbm_label_setting_sec') : __('Boarding Points', 'bus-booking-manager')); ?>
                                </h4>
                                <ul>
                                    <?php
                                    $wbbm_start_stops = get_post_meta(get_the_ID(), 'wbbm_bus_bp_stops', true);
                                    foreach ($wbbm_start_stops as $wbbm__start_stops) {
                                        echo "<li><span class='fa fa-map-marker mar_r'></span>" . esc_html($wbbm__start_stops['wbbm_bus_bp_stops_name']) . "</li>";
                                    }
                                    ?>
                                </ul>
                            </div>
                            <div>
                                <h4 class="mar_b bor_tb">
                                    <?php echo esc_html(wbbm_get_option('wbbm_dropping_points_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_dropping_points_text', 'wbbm_label_setting_sec') : __('Dropping Points', 'bus-booking-manager')); ?>
                                </h4>
                                <ul>
                                    <?php
                                    $wbbm_end_stops = get_post_meta(get_the_ID(), 'wbbm_bus_next_stops', true);
                                    foreach ($wbbm_end_stops as $wbbm__end_stops) {
                                        echo "<li><span class='fa fa-map-marker mar_r'></span>" . esc_html($wbbm__end_stops['wbbm_bus_next_stops_name']) . "</li>";
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                        <div class="mage_customer_info_area">
                            <?php
                            $wbbm_date = $WbbmJ_date ? mage_wp_date($WbbmJ_date, 'Y-m-d') : gmdate('Y-m-d');
                            $wbbm_start = $Wbbm_boarding;
                            $wbbm_end = $Wbbm_boarding;
                            wbbm_hidden_input_field('bus_id', $id);
                            wbbm_hidden_input_field('journey_date', $wbbm_date);
                            wbbm_hidden_input_field('start_stops', $wbbm_start);
                            wbbm_hidden_input_field('end_stops', $wbbm_end);
                            wbbm_hidden_input_field('user_start_time', $Wbbm_boarding_time);
                            wbbm_hidden_input_field('bus_start_time', $Wbbm_dropping_time);
                            ?>
                            <div class="adult"></div>
                            <div class="child"></div>
                            <div class="infant"></div>
                            <div class="entire"></div>
                        </div>
                        <?php
                        // Extra Service Section
                        if ($Wbbm_available_seat > 0) {
                            wbbm_extra_services_section($id);
                        }
                        // Operational on day off day check
                        $wbbm_bus_stops_times = get_post_meta(get_the_ID(), 'wbbm_bus_bp_stops', true);
                        $wbbm_bus_offday_schedules = get_post_meta(get_the_ID(), 'wbtm_offday_schedule', true);
                        $wbbm_start_time = '';
                        foreach ($wbbm_bus_stops_times as $wbbm_stop) {
                            if ($Wbbm_boarding) {
                                if ($wbbm_stop['wbbm_bus_bp_stops_name'] == $Wbbm_boarding) {
                                    $wbbm_start_time = $wbbm_stop['wbbm_bus_bp_start_time'];
                                }
                            }
                        }
                        $wbbm_start_time = wbbm_time_24_to_12($wbbm_start_time);
                        if (wbbm_buffer_time_calculation($wbbm_start_time, $WbbmJ_date)) {
                            if ($WbbmJ_date != '' && $Wbbm_boarding != '' && $Wbbm_boarding != '') {
                                if ($wbbm_is_on_date) {
                                    $WbbmJ_date_c = mage_wp_date($WbbmJ_date, 'Y-m-d');
                                    if (in_array($WbbmJ_date_c, $wbbm_bus_on_dates)) {
                                        mage_book_now_area($Wbbm_available_seat);
                                    } else {
                                        echo '<span class="mage_error" style="display: block;text-align: center;padding: 5px;margin: 10px 0 0 0;">' . esc_html(gmdate($WbbmDate_format, strtotime(mage_get_isset($WbbmDate_var)))) . ' Operational Off day!' . '</span>';
                                    }
                                } else {
                                    $wbbm_offday_current_bus = false;
                                    if (!empty($wbbm_bus_offday_schedules)) {
                                        $wbbm_s_datetime = new DateTime(mage_wp_date($WbbmJ_date, 'Y-m-d') . ' ' . $wbbm_start_time);
                                        foreach ($wbbm_bus_offday_schedules as $wbbm_item) {
                                            $wbbm_c_iterate_date_from = mage_wp_date($wbbm_item['from_date'], 'Y-m-d');
                                            $wbbm_c_iterate_datetime_from = new DateTime($wbbm_c_iterate_date_from . ' ' . $wbbm_item['from_time']);
                                            $wbbm_c_iterate_date_to = mage_wp_date($wbbm_item['to_date'], 'Y-m-d');
                                            $wbbm_c_iterate_datetime_to = new DateTime($wbbm_c_iterate_date_to . ' ' . $wbbm_item['to_time']);
                                            if ($wbbm_s_datetime >= $wbbm_c_iterate_datetime_from && $wbbm_s_datetime <= $wbbm_c_iterate_datetime_to) {
                                                $wbbm_offday_current_bus = true;
                                                break;
                                            }
                                        }
                                    }
                                    // Check Offday and date
                                    if (!$wbbm_offday_current_bus && !mage_off_day_check($WbbmReturn)) {
                                        mage_book_now_area($Wbbm_available_seat);
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
    <?php do_action('wbbm_after_single_bus'); ?>
    <?php do_action('wbbm_prevent_form_resubmission'); ?>
    </div> <!-- Close bus_detail_page_wrapper -->
</div>

<?php if ( wp_is_block_theme() ) {
// Code for block themes goes here.
?>

<footer class="wp-block-template-part">
    <?php block_footer_area(); ?>
</footer>
<?php wp_footer(); ?>
</body>    
<?php
} else {
    get_footer();
}
