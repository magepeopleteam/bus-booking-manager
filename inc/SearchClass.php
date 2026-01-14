<?php
	if (!defined('ABSPATH'))
		exit;  // if direct access

	class SearchClass extends CommonClass {
		public function __construct() {
		}

		function mage_search_page_horizontal() {

			$the_page = sanitize_post($GLOBALS['wp_the_query']->get_queried_object());
			$target = sanitize_title($the_page->post_name);
			$this->mage_search_form_horizontal(false, $target);

            
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if (isset($_GET['bus_start_route'], $_GET['bus_end_route'], $_GET['j_date'])) {
                // Safely get the nonce from the request (accept GET or POST)
                $nonce = isset($_REQUEST['bus_search_nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['bus_search_nonce'])) : '';

                // Verify the nonce
                if ( ! $nonce || ! wp_verify_nonce($nonce, 'bus_search_nonce_action') ) {
                    wc_add_notice(__('Security check failed. Please try again. 111', 'bus-booking-manager'), 'error');
                    return false;
                }
               
				?>
				<div class="mage_container">
					<div class="mage_row">
						<div style="width:100%">
							<?php $this->mage_search_list(); ?>
						</div>
					</div>
				</div>
				<?php
			}
}

function mage_search_list() {
    global $mage_bus_search_theme;

    $cpt_label = wbbm_get_option('wbbm_cpt_label', 'wbbm_general_setting_sec', 'Bus');
    $route_title_bg_color = wbbm_get_option('wbbm_search_route_title_b_color', 'wbbm_style_setting_sec');
    $route_title_color = wbbm_get_option('wbbm_search_route_title_color', 'wbbm_style_setting_sec') ?: '#fff';
    $search_list_header_b_color = wbbm_get_option('wbbm_search_list_header_b_color', 'wbbm_style_setting_sec');
    $general_setting = get_option('wbbm_general_setting_sec') ? maybe_unserialize(get_option('wbbm_general_setting_sec')) : array();

    // Ensure options are set correctly
    $wbbm_type_column_switch = ['wbbm_type_column_switch' => 'on'];
    if (!array_key_exists('wbbm_type_column_switch', $general_setting)) {
        $general_setting = array_merge($general_setting, $wbbm_type_column_switch);
        update_option('wbbm_general_setting_sec', $general_setting);
    }

    $wbbm_seat_column_switch = ['wbbm_seat_column_switch' => 'on'];
    if (!array_key_exists('wbbm_seat_column_switch', $general_setting)) {
        $general_setting = array_merge($general_setting, $wbbm_seat_column_switch);
        update_option('wbbm_general_setting_sec', $general_setting);
    }

    $general_setting = get_option('wbbm_general_setting_sec') ? maybe_unserialize(get_option('wbbm_general_setting_sec')) : array();
    ?>
    <div class="mage_route_title" style="background-color: <?php echo esc_attr($route_title_bg_color); ?>; color: <?php echo esc_attr($route_title_color); ?>">
        <div>
            <strong>
                <?php
                    $route_label = wbbm_get_option(
                        'wbbm_route_text',
                        'wbbm_label_setting_sec',
                        __( 'Route', 'bus-booking-manager' )
                    );

                    echo esc_html( $route_label ) . ':';
                ?>

            </strong>
            <?php echo esc_html(mage_get_isset('bus_start_route')); ?>
            <span class="fa fa-long-arrow-right"></span>
            <?php echo esc_html(mage_get_isset('bus_end_route')); ?>
            <strong><?php echo ' | '; echo esc_html(wbbm_get_option('wbbm_date_text', 'wbbm_label_setting_sec', __('Date', 'bus-booking-manager'))); echo ':'; ?></strong>
            <?php echo esc_html(mage_wp_date(mage_get_isset('j_date'))); ?>
        </div>
    </div>
    <div class="mage-search-res-wrapper">
        <?php do_action('woocommerce_before_single_product'); ?>
        <?php if ($mage_bus_search_theme == 'minimal') { ?>
            <div class="mage-search-res-header" style="background-color: <?php echo esc_attr($search_list_header_b_color ?: '#EA2330'); ?>;">
                <div class="mage-search-res-header--img">
                    <span><?php echo esc_html(wbbm_get_option('wbbm_bus_image_text', 'wbbm_label_setting_sec', __('Bus Image', 'bus-booking-manager'))); ?></span>
                </div>
                <div class="mage-search-res-header--left">
                    <span><?php echo esc_html(wbbm_get_option('wbbm_bus_name_text', 'wbbm_label_setting_sec', __('Bus Name', 'bus-booking-manager'))); ?></span>
                    <span><?php echo esc_html(wbbm_get_option('wbbm_schedule_text', 'wbbm_label_setting_sec', __('Schedule', 'bus-booking-manager'))); ?></span>
                </div>
                <div class="mage-search-res-header--right">
                    <?php if ((isset($general_setting['wbbm_type_column_switch']) && $general_setting['wbbm_type_column_switch'] == 'on') || !isset($general_setting['wbbm_type_column_switch'])) { ?>
                        <span><?php echo esc_html(wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec', __('Type', 'bus-booking-manager'))); ?></span>
                    <?php } ?>
                    <span><?php echo esc_html(wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec', __('Fare', 'bus-booking-manager'))); ?></span>
                    <?php if ((isset($general_setting['wbbm_seat_column_switch']) && $general_setting['wbbm_seat_column_switch'] == 'on') || !isset($general_setting['wbbm_seat_column_switch'])) { ?>
                        <span><?php echo esc_html(wbbm_get_option('wbbm_seats_available_text', 'wbbm_label_setting_sec', __('Seat Available', 'bus-booking-manager'))); ?></span>
                    <?php } ?>
                    <span><?php echo esc_html(wbbm_get_option('wbbm_view_text', 'wbbm_label_setting_sec', __('View', 'bus-booking-manager'))); ?></span>
                </div>
            </div>
        <?php } ?>
        <?php $this->mage_search_bus_list(false); ?>
    </div>
    <?php if (isset($_GET['r_date']) && $_GET['r_date'] !== '' && $_GET['r_date'] !== 'yy-mm-dd') {
        // Safely get the nonce from the request (accept GET or POST)
        $nonce = isset($_REQUEST['bus_search_nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['bus_search_nonce'])) : '';

        // Verify the nonce
        if ( ! $nonce || ! wp_verify_nonce($nonce, 'bus_search_nonce_action') ) {
            wc_add_notice(__('Security check failed. Please try again. 222', 'bus-booking-manager'), 'error');
            return false;
        }
    ?>
        <div class="mage_route_title return_title" style="background-color: <?php echo esc_attr($route_title_bg_color); ?>;">
            <div>
                <strong><?php echo esc_html(wbbm_get_option('wbbm_route_text', 'wbbm_label_setting_sec', __('Route', 'bus-booking-manager'))); echo ':'; ?></strong>
                <?php echo esc_html(mage_get_isset('bus_end_route')); ?>
                <span class="fa fa-long-arrow-right"></span>
                <?php echo esc_html(mage_get_isset('bus_start_route')); ?>
                <strong><?php echo ' | '; echo esc_html(wbbm_get_option('wbbm_date_text', 'wbbm_label_setting_sec', __('Date', 'bus-booking-manager'))); echo ':'; ?></strong>
                <?php echo esc_html(mage_wp_date(mage_get_isset('r_date'))); ?>
            </div>
        </div>
        <div class="mage-search-res-wrapper">
            <?php if ($mage_bus_search_theme == 'minimal') { ?>
                <div class="mage-search-res-header">
                    <div class="mage-search-res-header--img">
                        <span><?php echo esc_html(wbbm_get_option('wbbm_bus_image_text', 'wbbm_label_setting_sec', __('Bus Image', 'bus-booking-manager'))); ?></span>
                    </div>
                    <div class="mage-search-res-header--left">
                        <span><?php echo esc_html(wbbm_get_option('wbbm_bus_name_text', 'wbbm_label_setting_sec', esc_html(__('Bus Name', 'bus-booking-manager')))); ?></span>
                        <span><?php echo esc_html(wbbm_get_option('wbbm_schedule_text', 'wbbm_label_setting_sec', esc_html(__('Schedule', 'bus-booking-manager')))); ?></span>
                    </div>
                    <div class="mage-search-res-header--right">
                        <?php if (isset($general_setting['wbbm_type_column_switch']) && $general_setting['wbbm_type_column_switch'] == 'on') { ?>
                            <span><?php echo esc_html(wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec', esc_html(__('Type', 'bus-booking-manager')))); ?></span>
                        <?php } ?>
                        <span><?php echo esc_html(wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec', __('Fare', 'bus-booking-manager'))); ?></span>
                        <?php if (isset($general_setting['wbbm_seat_column_switch']) && $general_setting['wbbm_seat_column_switch'] == 'on') { ?>
                            <span><?php echo esc_html(wbbm_get_option('wbbm_seats_available_text', 'wbbm_label_setting_sec', __('Seat Available', 'bus-booking-manager'))); ?></span>
                        <?php } ?>
                        <span><?php echo esc_html(wbbm_get_option('wbbm_view_text', 'wbbm_label_setting_sec', __('View', 'bus-booking-manager'))); ?></span>
                    </div>
                </div>
            <?php } ?>
            <?php $this->mage_search_bus_list(true); ?>
        </div>
        <div class="mage_mini_cart mage_hidden">
            <p><?php echo esc_html(wbbm_get_option('wbbm_total_text', 'wbbm_label_setting_sec', __('Total', 'bus-booking-manager'))); ?></p>
            <p class="mage_total"><strong><span><?php echo wp_kses_post(wc_price(0)); ?></span></strong></p>
        </div>
    <?php }
    do_action('wbbm_prevent_form_resubmission');
}

function mage_search_bus_list($return) {
    // Safely get the nonce from the request (accept GET or POST)
    $nonce = isset($_REQUEST['bus_search_nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['bus_search_nonce'])) : '';

    // Verify the nonce
    if ( ! $nonce || ! wp_verify_nonce($nonce, 'bus_search_nonce_action') ) {
        wc_add_notice(__('Security check failed. Please try again. 333', 'bus-booking-manager'), 'error');
        return false;
    }
    do_action('woocommerce_before_single_product');

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (isset($_GET['bus_start_route'], $_GET['bus_end_route']) && (isset($_GET['j_date']) || isset($_GET['r_date']))) {
        $c_time = current_time('timestamp');
        $start = $return ? 'bus_end_route' : 'bus_start_route';
        $end = $return ? 'bus_start_route' : 'bus_end_route';
        
        // Sanitize input
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $start_route = isset($_GET[$start]) ? sanitize_text_field((wp_unslash($_GET[$start]))) : '';
        $end_route = isset($_GET[$end]) ? sanitize_text_field(wp_unslash($_GET[$end])) : '';
        $r_date = isset($_GET['r_date']) ? sanitize_text_field(wp_unslash($_GET['r_date'])) : '';
        $input_j_date = isset($_GET['j_date']) ? sanitize_text_field(wp_unslash($_GET['j_date'])) : '';
        $j_date = $return ? $r_date : $input_j_date;
        $j_date = mage_wp_date($j_date, 'Y-m-d');

        $loop = new WP_Query(mage_bus_list_query($start, $end));
        $has_bus = false;

        if ($loop->post_count == 0) {
            ?>
            <div class='wbbm_error' style='text-align:center;padding: 10px;color: red;'>
                <span><?php echo esc_html__('Sorry, No', 'bus-booking-manager') . ' ' . esc_html(wbbm_get_option('wbbm_cpt_label', 'wbbm_general_setting_sec', 'Bus')) . ' ' . esc_html__('Found', 'bus-booking-manager'); ?></span>
            </div>
            <?php
        } else {
            while ($loop->have_posts()) {
                $loop->the_post();
                $bus_stops_times = get_post_meta(get_the_ID(), 'wbbm_bus_bp_stops', true);
                $start_time = '';

                foreach ($bus_stops_times as $stop) {
                    if (isset($stop['wbbm_bus_bp_stops_name']) && $stop['wbbm_bus_bp_stops_name'] === $start_route) {
                        $start_time = isset($stop['wbbm_bus_bp_start_time']) ? $stop['wbbm_bus_bp_start_time'] : '';
                    }
                }

                // Buffer time
                if (!wbbm_buffer_time_calculation($start_time, $j_date)) {
                    continue;
                }

                // Convert time to 12-hour format
                $start_time = wbbm_time_24_to_12($start_time);
                $show_operational_on_day = get_post_meta(get_the_ID(), 'show_operational_on_day', true) ?: 'no';
                $bus_on_date = get_post_meta(get_the_ID(), 'wbtm_bus_on_date', true);
                $bus_on_dates = [];

                if ($show_operational_on_day === 'yes' && $bus_on_date) {
                    $bus_on_dates = is_array($bus_on_date) ? $bus_on_date : explode(', ', $bus_on_date);
                    if (in_array($j_date, $bus_on_dates)) {
                        $has_bus = true;
                        $this->mage_search_item($return);
                    }
                } else {
                    // Offday schedule check
                    $bus_offday_schedules = get_post_meta(get_the_ID(), 'wbtm_offday_schedule', true);
                    $offday_current_bus = false;

                    if (!empty($bus_offday_schedules)) {
                        $s_datetime = new DateTime($j_date . ' ' . $start_time);
                        foreach ($bus_offday_schedules as $item) {
                            $c_iterate_date_from = wbbm_convert_date_to_php($item['from_date']);
                            $c_iterate_datetime_from = new DateTime($c_iterate_date_from . ' ' . $item['from_time']);
                            $c_iterate_date_to = wbbm_convert_date_to_php($item['to_date']);
                            $c_iterate_datetime_to = new DateTime($c_iterate_date_to . ' ' . $item['to_time']);

                            if ($s_datetime >= $c_iterate_datetime_from && $s_datetime <= $c_iterate_datetime_to) {
                                $offday_current_bus = true;
                                break;
                            }
                        }
                    }

                    // Check Offday and date
                    $show_off_day = get_post_meta(get_the_ID(), 'show_off_day', true) ?: 'no';
                    if ($show_off_day === 'yes') {
                        if ((!$offday_current_bus && !mage_off_day_check($return))) {
                            $has_bus = true;
                            $this->mage_search_item($return);
                        }
                    } else {
                        $has_bus = true;
                        $this->mage_search_item($return);
                    }
                }
            }

            if (!$has_bus) {
                ?>
                <div class='wbbm_error' style='text-align:center;padding: 10px;color: red;'>
                    <span><?php echo esc_html__('Sorry, No', 'bus-booking-manager') . ' ' . esc_html(wbbm_get_option('wbbm_cpt_label', 'wbbm_general_setting_sec', 'Bus')) . ' ' . esc_html__('Found', 'bus-booking-manager'); ?></span>
                </div>
                <?php
            }
        }
        wp_reset_postdata(); // Reset post data after custom query
    }
}

function mage_search_item($return) {
    global $mage_bus_search_theme;

    // Safely get the nonce from the request (accept GET or POST)
    $nonce = isset($_REQUEST['bus_search_nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['bus_search_nonce'])) : '';

    // Verify the nonce
    if ( ! $nonce || ! wp_verify_nonce($nonce, 'bus_search_nonce_action') ) {
        wc_add_notice(__('Security check failed. Please try again. 44', 'bus-booking-manager'), 'error');
        return false;
    }

    $id = get_the_ID();
    $search_date = isset($_GET['j_date']) ? sanitize_text_field(wp_unslash($_GET['j_date'])) : '';
    $current_date = gmdate('Y-m-d');
    $boarding_time = boarding_dropping_time(false, $return);
    $dropping_time = boarding_dropping_time(true, $return);

    if ($current_date === $search_date) {
        $search_timestamp = strtotime($search_date . ' ' . $boarding_time);
        if (current_time('timestamp') >= $search_timestamp) {
            return;
        }
    }

    $boarding_var = $return ? 'bus_end_route' : 'bus_start_route';
    $dropping_var = $return ? 'bus_start_route' : 'bus_end_route';
    $date_var = $return ? 'r_date' : 'j_date';
    $in_cart = mage_find_product_in_cart();
    $type_id = get_post_meta($id, 'wbbm_bus_category', true);
    $type_name = '';

    if ($type_id != '') {
        $type_array = get_term_by('term_id', $type_id, 'wbbm_bus_cat');
        $type_name = $type_array ? $type_array->name : '';
    }

    $input_boarding_var = isset($_GET[$boarding_var]) ? sanitize_text_field(wp_unslash($_GET[$boarding_var])) : '';
    $input_dropping_var = isset($_GET[$dropping_var]) ? sanitize_text_field(wp_unslash($_GET[$dropping_var])) : '';

    $available_seat = wbbm_intermidiate_available_seat(
        $input_boarding_var,
        $input_dropping_var,
        wbbm_convert_date_to_php(mage_get_isset($date_var))
    );
    $cart_qty = wbbm_get_cart_item($id, mage_get_isset($date_var));
    $available_seat -= $cart_qty;
    $boarding = mage_get_isset($boarding_var);
    $dropping = mage_get_isset($dropping_var);

    $seat_price_adult = mage_seat_price($id, $boarding, $dropping, 'adult');
    $seat_price_child = mage_seat_price($id, $boarding, $dropping, 'child');
    $seat_price_infant = mage_seat_price($id, $boarding, $dropping, 'infant');
    $seat_price_entire = mage_seat_price($id, $boarding, $dropping, 'entire'); 
    $boarding_point = $input_boarding_var;
    $boarding_point_slug = strtolower(preg_replace('/[^A-Za-z0-9-]/', '_', $boarding_point));
    $coach_no = get_post_meta($id, 'wbbm_bus_no', true);
    $is_enable_pickpoint = get_post_meta($id, 'show_pickup_point', true);
    $pickpoints = get_post_meta($id, 'wbbm_selected_pickpoint_name_' . $boarding_point_slug, true);
    $pickpoints = is_string($pickpoints) ? maybe_unserialize($pickpoints) : $pickpoints;
    $is_sell_off = get_post_meta($id, 'wbbm_sell_off', true);
    $wbbm_features = get_post_meta($id, 'wbbm_features', true);
    $seat_available = get_post_meta($id, 'wbbm_seat_available', true);
    $total_seat = get_post_meta($id, 'wbbm_total_seat', true);
    $is_price_zero_allow = get_post_meta($id, 'wbbm_price_zero_allow', true);
    $general_setting = get_option('wbbm_general_setting_sec') ? maybe_unserialize(get_option('wbbm_general_setting_sec')) : array();
    $search_form_result_b_color = wbbm_get_option('wbbm_search_form_result_b_color', 'wbbm_style_setting_sec');
    $search_list_header_text_color = wbbm_get_option('wbbm_search_list_header_text_color', 'wbbm_style_setting_sec');
    $entire_bus_booking = wbbm_get_option('wbbm_entire_bus_booking_switch', 'wbbm_general_setting_sec');

    if ($seat_price_adult > 0 || $is_price_zero_allow == 'on') {
        if ($mage_bus_search_theme == 'minimal') : // Minimal theme design
            ?>
            <div style="background-color: <?php echo esc_attr($search_form_result_b_color ?: '#b30c3b12'); ?>" class="mage_search_list theme_minimal <?php echo esc_attr($in_cart ? 'booked' : ''); ?>" data-seat-available="<?php echo esc_attr($available_seat); ?>">
                <div class="mage-search-brief-row" style="color: <?php echo esc_attr($search_list_header_text_color ?: '#000'); ?>;">
                    <div class="mage-search-res-header--img">
                        <?php
                        if (has_post_thumbnail()) {
                            the_post_thumbnail('full');
                        } else {
                            echo '<img src="' . esc_url(PLUGIN_ROOT . '/images/bus-placeholder.png') . '" loading="lazy" />';
                        }
                        ?>
                    </div>
                    <div class="mage-search-res-header--left">
                        <div class="mage-bus-title">
                            <a class="bus-title" href="<?php echo esc_url(get_the_permalink($id)); ?>"><?php echo esc_html(get_the_title($id)); ?></a>
                            <span><?php echo esc_html($coach_no); ?></span>
                            <?php if ($wbbm_features) { ?>
                                <p class="wbbm-feature-icon">
                                    <?php foreach ($wbbm_features as $feature_id) { ?>
                                        <span class="customCheckbox"><span title="<?php echo esc_attr(get_term($feature_id)->name); ?>" class="mR_xs <?php echo esc_attr(get_term_meta($feature_id, 'feature_icon', true)); ?>"></span></span>
                                    <?php } ?>
                                </p>
                            <?php } ?>
                        </div>
                        <div>
                            <?php
                            echo '<p class="mage-bus-stopage"><span class="dashicons dashicons-location"></span> ' .
                                esc_html(wbbm_get_option('wbbm_from_text', 'wbbm_label_setting_sec', __('From: ', 'bus-booking-manager'))) . ' ' .
                                esc_html($boarding) . ' ( ' .
                                esc_html(get_wbbm_datetime($boarding_time, 'time')) . ' )</p>';
                            ?>

                            <?php
                            echo '<p class="mage-bus-stopage"><span class="dashicons dashicons-location"></span> ' .
                                esc_html(wbbm_get_option('wbbm_to_text', 'wbbm_label_setting_sec', __('To: ', 'bus-booking-manager'))) . ' ' .
                                esc_html($dropping) . ' ( ' .
                                esc_html(get_wbbm_datetime($dropping_time, 'time')) . ' )</p>';
                            ?>
                        </div>
                    </div>
                    <div class="mage-search-res-header--right">
                        <?php if ((isset($general_setting['wbbm_type_column_switch']) && $general_setting['wbbm_type_column_switch'] == 'on') || !isset($general_setting['wbbm_type_column_switch'])) { ?>
                            <div>
                                <strong class="mage-sm-show"><?php echo esc_html(wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec', __('Type', 'bus-booking-manager'))); ?></strong>
                                <span><?php echo esc_html($type_name); ?></span>
                            </div>
                        <?php } ?>
                        <div>
                            <strong class="mage-sm-show"><?php echo esc_html(wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec', __('Fare', 'bus-booking-manager'))); ?></strong>
                            <?php echo wp_kses_post(wc_price($seat_price_adult)); ?> / <?php echo esc_html(wbbm_get_option('wbbm_seat_text', 'wbbm_label_setting_sec', __('Seat', 'bus-booking-manager'))); ?>
                        </div>
                        <?php if (isset($general_setting['wbbm_seat_column_switch']) && $general_setting['wbbm_seat_column_switch'] == 'on') { ?>
                            <?php if ($seat_available && $seat_available == 'on') : ?>
                                <div>
                                    <strong class="mage-sm-show"><?php echo esc_html(wbbm_get_option('wbbm_seats_available_text', 'wbbm_label_setting_sec', __('Seat Available', 'bus-booking-manager'))); ?></strong>
                                    <?php if ($is_sell_off != 'on') {
                                        if ($available_seat > 0) {
                                            echo '<p>' . esc_html($available_seat) . '</p>';
                                        } else {
                                            echo '<p class="mage-sm-text">' . esc_html(wbbm_get_option('wbbm_no_seat_available_text', 'wbbm_label_setting_sec', __('No Seat Available', 'bus-booking-manager'))) . '</p>';
                                        }
                                    } ?>
                                </div>
                            <?php else : ?>
                                <div>-</div>
                            <?php endif; ?>
                        <?php } ?>
                        <div>
                            <button class="mage-bus-detail-action"><?php echo esc_html(wbbm_get_option('wbbm_view_text', 'wbbm_label_setting_sec', __('View', 'bus-booking-manager'))); ?></button>
                        </div>
                    </div>
                </div>
                <div class="mage-bus-booking-wrapper">
                    <form action="" method="post">
                        <div class="mage_flex xs_not_flex">
                            <div class="mage_flex_equal mage_bus_details">
                                <div class="mage_bus_info">
                                    <h3><a href="<?php echo esc_url(get_the_permalink($id)); ?>"><?php echo esc_html(get_the_title()); ?></a></h3>
                                    <?php if ($type_name) : ?>
                                        <p>
                                            <strong><?php echo esc_html(wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec', __('Type', 'bus-booking-manager'))); ?></strong>:
                                            <?php echo esc_html($type_name); ?>
                                        </p>
                                    <?php endif; ?>
                                    <p>
                                        <strong><?php echo esc_html(wbbm_get_option('wbbm_boarding_points_text', 'wbbm_label_setting_sec', __('Boarding', 'bus-booking-manager'))); ?></strong>:
                                        <?php echo esc_html($boarding); ?>
                                        <strong>(<?php echo esc_html(get_wbbm_datetime($boarding_time, 'time')); ?>)</strong>
                                        <?php
                                        $boarding_desc = (get_term_by('name', $boarding, 'wbbm_bus_stops') ? get_term_by('name', $boarding, 'wbbm_bus_stops')->description : '');
                                        if ($boarding_desc) {
                                            echo '<span class="wbbm_dropoff-desc">' . esc_html($boarding_desc) . '</span>';
                                        }
                                        ?>
                                    </p>
                                    <p>
                                        <strong><?php echo esc_html(wbbm_get_option('wbbm_dropping_points_text', 'wbbm_label_setting_sec', __('Dropping', 'bus-booking-manager'))); ?></strong>:
                                        <?php echo esc_html($dropping); ?>
                                        <strong>(<?php echo esc_html(get_wbbm_datetime($dropping_time, 'time')); ?>)</strong>
                                        <?php
                                        $dropoff_desc = (get_term_by('name', $dropping, 'wbbm_bus_stops') ? get_term_by('name', $dropping, 'wbbm_bus_stops')->description : '');
                                        if ($dropoff_desc) {
                                            echo '<span class="wbbm_dropoff-desc">' . esc_html($dropoff_desc) . '</span>';
                                        }
                                        ?>
                                    </p>
                                    <p>
                                        <strong><?php echo esc_html(wbbm_get_option('wbbm_date_text', 'wbbm_label_setting_sec', __('Date', 'bus-booking-manager'))); ?></strong>:
                                        <?php echo esc_html($return ? mage_wp_date(mage_get_isset('r_date')) : mage_wp_date(mage_get_isset('j_date'))); ?>
                                    </p>
                                    <p>
                                        <strong><?php echo esc_html(wbbm_get_option('wbbm_starting_text', 'wbbm_label_setting_sec', __('Start Time', 'bus-booking-manager'))); ?></strong>:
                                        <?php echo esc_html(get_wbbm_datetime($boarding_time, 'time')); ?>
                                    </p>
                                    <p>
                                        <strong><?php echo esc_html(wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec', __('Fare', 'bus-booking-manager'))); ?></strong>:
                                        <?php echo wp_kses_post(wc_price($seat_price_adult)) . ' / ' . esc_html(wbbm_get_option('wbbm_seat_text', 'wbbm_label_setting_sec', __('Seat', 'bus-booking-manager'))); ?>
                                    </p>
                                    <?php if ($in_cart) { ?>
                                        <p class="already_cart"><?php echo esc_html(wbbm_get_option('wbbm_item_in_cart_text', 'wbbm_label_setting_sec', __('Item has been added to cart', 'bus-booking-manager'))); ?></p>
                                    <?php } ?>
                                </div>
                                <div class="mage_price_info">
                                    <div class="mage_center_space">
                                        <h3><strong><?php echo esc_html(wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec', __('Fare', 'bus-booking-manager'))); ?></strong></h3>
                                    </div>
                                    <div class="mage_center_space">
                                        <div>
                                            <p>
                                                <strong><?php echo esc_html(wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec', __('Adult :', 'bus-booking-manager'))); ?></strong>
                                                <?php echo wp_kses_post(wc_price($seat_price_adult)); ?>/
                                                <small><?php echo esc_html(wbbm_get_option('wbbm_ticket_text', 'wbbm_label_setting_sec', __('Ticket', 'bus-booking-manager'))); ?></small>
                                            </p>
                                        </div>
                                        <?php mage_qty_box($seat_price_adult, 'adult_quantity', false); ?>
                                    </div>
                                    <input type="hidden" name="available_quantity" value="<?php echo esc_attr($available_seat); ?>">
                                    <?php
                                    $is_price_zero_allow = get_post_meta($id, 'wbbm_price_zero_allow', true);
                                    if (($seat_price_child > 0) || ($is_price_zero_allow == 'on')) : ?>
                                        <div class="mage_center_space">
                                            <p>
                                                <strong><?php echo esc_html(wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec', __('Child :', 'bus-booking-manager'))); ?></strong>
                                                <?php echo wp_kses_post(wc_price($seat_price_child)); ?>/
                                                <small><?php echo esc_html(wbbm_get_option('wbbm_ticket_text', 'wbbm_label_setting_sec', __('Ticket', 'bus-booking-manager'))); ?></small>
                                            </p>
                                            <?php mage_qty_box($seat_price_child, 'child_quantity', false); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (($seat_price_infant > 0) || ($is_price_zero_allow == 'on')) : ?>
                                        <div class="mage_center_space">
                                            <p>
                                                <strong><?php echo esc_html(wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec', __('Infant :', 'bus-booking-manager'))); ?></strong>
                                                <?php echo wp_kses_post(wc_price($seat_price_infant)); ?>/
                                                <small><?php echo esc_html(wbbm_get_option('wbbm_ticket_text', 'wbbm_label_setting_sec', __('Ticket', 'bus-booking-manager'))); ?></small>
                                            </p>
                                            <?php mage_qty_box($seat_price_infant, 'infant_quantity', false); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (($entire_bus_booking == 'on') && ($available_seat == $total_seat) && ($seat_price_entire > 0)) : ?>
                                        <div class="mage_center_space">
                                            <p>
                                                <strong><?php echo esc_html(wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec')) ? esc_html(wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec')) : esc_html__('Entire Bus', 'bus-booking-manager') . ':'; ?></strong>
                                                <?php echo wp_kses_post(wc_price($seat_price_entire)); ?>
                                            </p>
                                            <?php echo wp_kses_post(wbbm_entire_switch($seat_price_entire, 'entire_quantity', false)); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($pickpoints) && $is_enable_pickpoint == 'yes') : ?>
                                        <div class="mage_center_space">
                                            <div class="mage-form-field mage-form-pickpoint-field">
                                                <strong><label for="mage_pickpoint"><?php esc_html_e('Select Pickup Area', 'bus-booking-manager'); echo ':'; ?></label></strong>
                                                <select name="mage_pickpoint" id="mage_pickpoint">
                                                    <option value=""><?php esc_html_e('Select your Pickup Area', 'bus-booking-manager'); ?></option>
                                                    <?php
                                                    foreach ($pickpoints as $pickpoint) {
                                                        $time_html = $pickpoint["time"] ? ' (' . get_wbbm_datetime($pickpoint["time"], 'time') . ')' : '';
                                                        $time_value = $pickpoint["time"] ? '-' . get_wbbm_datetime($pickpoint["time"], 'time') : '';
                                                        $pick_desc = (get_term_by('name', $pickpoint["pickpoint"], 'wbbm_bus_pickpoint') ? get_term_by('name', $pickpoint["pickpoint"], 'wbbm_bus_pickpoint')->description : '');
                                                        echo '<option value="' . esc_attr($pickpoint["pickpoint"]) . esc_attr($time_value) . '">' . esc_html(ucfirst($pickpoint["pickpoint"])) . esc_html($time_html) . '</option>';
                                                        echo ($pick_desc ? '<option disabled>&nbsp;&nbsp; ' . esc_html($pick_desc) . '</option>' : '');
                                                    } ?>
                                                </select>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="mage_customer_info_area">
                                        <?php
                                        $date = isset($_GET[$date_var]) ? mage_wp_date(sanitize_text_field(wp_unslash($_GET[$date_var])), 'Y-m-d') : gmdate('Y-m-d');
                                        $start = $input_boarding_var;
                                        $end = $input_dropping_var;
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
                                    if ($available_seat > 0) {
                                        wbbm_extra_services_section($id);
                                    }
                                    ?>
                                    <?php mage_book_now_area($available_seat); ?>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <?php do_action('mage_multipurpose_reg'); ?>
            </div>
        <?php
        else : // Default theme design
            ?>
            <div style="background-color: <?php echo esc_attr($search_form_result_b_color ?: '#b30c3b12'); ?>" class="mage_search_list <?php echo esc_attr($in_cart ? 'booked' : ''); ?>" data-seat-available="<?php echo esc_attr($available_seat); ?>">
                <form action="" method="post">
                    <div class="mage_flex xs_not_flex">
                        <div class="mage_thumb"><?php the_post_thumbnail('full'); ?></div>
                        <div class="mage_flex_equal mage_bus_details">
                            <div class="mage_bus_info">
                                <h3><a href="<?php echo esc_url(get_the_permalink($id)); ?>"><?php echo esc_html(get_the_title()); ?></a></h3>
                                <?php if ($type_name) : ?>
                                    <p>
                                        <strong><?php echo esc_html(wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec', __('Type :', 'bus-booking-manager'))); ?></strong>
                                        <?php echo esc_html($type_name); ?>
                                    </p>
                                <?php endif; ?>
                                <p>
                                    <strong><?php echo esc_html(wbbm_get_option('wbbm_boarding_points_text', 'wbbm_label_setting_sec', __('Boarding :', 'bus-booking-manager'))); ?></strong>
                                    <?php echo esc_html($boarding); ?>
                                    <strong>(<?php echo esc_html($boarding_time); ?>)</strong>
                                </p>
                                <p>
                                    <strong><?php echo esc_html(wbbm_get_option('wbbm_dropping_points_text', 'wbbm_label_setting_sec', __('Dropping :', 'bus-booking-manager'))); ?></strong>
                                    <?php echo esc_html($dropping); ?>
                                    <strong>(<?php echo esc_html($dropping_time); ?>)</strong>
                                </p>
                                <?php if ($is_sell_off != 'on') : ?>
                                    <?php if ($seat_available && $seat_available == 'on') : ?>
                                        <p>
                                            <strong><?php echo esc_html($available_seat); ?></strong>
                                            <?php echo esc_html(wbbm_get_option('wbbm_seats_available_text', 'wbbm_label_setting_sec', __('Seat Available', 'bus-booking-manager'))); ?>
                                        </p>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if ($in_cart) { ?>
                                    <p class="already_cart"><?php echo esc_html(wbbm_get_option('wbbm_item_in_cart_text', 'wbbm_label_setting_sec', __('Item has been added to cart', 'bus-booking-manager'))); ?></p>
                                <?php } ?>
                            </div>
                            <div class="mage_price_info">
                                <div class="mage_center_space">
                                    <h3><strong><?php echo esc_html(wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec', __('Fare', 'bus-booking-manager'))); ?></strong></h3>
                                </div>
                                <input type="hidden" name="available_quantity" value="<?php echo esc_attr($available_seat); ?>">
                                <div class="mage_center_space">
                                    <div>
                                        <p>
                                            <strong><?php echo esc_html(wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec', __('Adult', 'bus-booking-manager'))); echo ':'; ?></strong>
                                            <?php echo wp_kses_post(wc_price($seat_price_adult)); ?>/
                                            <small><?php echo esc_html(wbbm_get_option('wbbm_ticket_text', 'wbbm_label_setting_sec', __('Ticket', 'bus-booking-manager'))); ?></small>
                                        </p>
                                    </div>
                                    <?php mage_qty_box($seat_price_adult, 'adult_quantity', false); ?>
                                </div>
                                <?php
                                $is_price_zero_allow = get_post_meta($id, 'wbbm_price_zero_allow', true);
                                if (($seat_price_child > 0) || ($is_price_zero_allow == 'on')) : ?>
                                    <div class="mage_center_space">
                                        <p>
                                            <strong><?php echo esc_html(wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec', __('Child', 'bus-booking-manager'))); echo ':'; ?></strong>
                                            <?php echo wp_kses_post(wc_price($seat_price_child)); ?>/
                                            <small><?php echo esc_html(wbbm_get_option('wbbm_ticket_text', 'wbbm_label_setting_sec', __('Ticket', 'bus-booking-manager'))); ?></small>
                                        </p>
                                        <?php mage_qty_box($seat_price_child, 'child_quantity', false); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (($seat_price_infant > 0) || ($is_price_zero_allow == 'on')) : ?>
                                    <div class="mage_center_space">
                                        <p>
                                            <strong><?php echo esc_html(wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec', __('Infant', 'bus-booking-manager'))); echo ':'; ?></strong>
                                            <?php echo wp_kses_post(wc_price($seat_price_infant)); ?>/
                                            <small><?php echo esc_html(wbbm_get_option('wbbm_ticket_text', 'wbbm_label_setting_sec', __('Ticket', 'bus-booking-manager'))); ?></small>
                                        </p>
                                        <?php mage_qty_box($seat_price_infant, 'infant_quantity', false); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (($available_seat == $total_seat) && ($seat_price_entire > 0)) : ?>
                                    <div class="mage_center_space">
                                        <p>
                                            <strong><?php echo esc_html(wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec')) ? esc_html(wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec')) : esc_html__('Entire Bus', 'bus-booking-manager') . ':'; ?></strong>
                                            <?php echo wp_kses_post(wc_price($seat_price_entire)); ?>
                                        </p>
                                        <?php echo wp_kses_post(wbbm_entire_switch($seat_price_entire, 'entire_quantity', false)); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($pickpoints) && $is_enable_pickpoint == 'yes') : ?>
                                    <div class="mage_center_space">
                                        <div class="mage-form-field mage-form-pickpoint-field">
                                            <label for="mage_pickpoint"><?php echo esc_html(wbbm_get_option('wbbm_pickuppoint_area_text', 'wbbm_label_setting_sec', __('Select Pickup Area', 'bus-booking-manager'))); echo ':'; ?></label>
                                            <select name="mage_pickpoint" class="mage_pickpoint">
                                                <option value=""><?php echo esc_html(wbbm_get_option('wbbm_pickuppoint_area_text', 'wbbm_label_setting_sec', __('Select Pickup Area', 'bus-booking-manager'))); ?></option>
                                                <?php
                                                foreach ($pickpoints as $pickpoint) {
                                                    $time_html = $pickpoint["time"] ? ' (' . get_wbbm_datetime($pickpoint["time"], 'time') . ')' : '';
                                                    $time_value = $pickpoint["time"] ? '-' . get_wbbm_datetime($pickpoint["time"], 'time') : '';
                                                    $pick_desc = (get_term_by('name', $pickpoint["pickpoint"], 'wbbm_bus_pickpoint') ? get_term_by('name', $pickpoint["pickpoint"], 'wbbm_bus_pickpoint')->description : '');
                                                    echo '<option value="' . esc_attr($pickpoint["pickpoint"]) . esc_attr($time_value) . '">' . esc_html(ucfirst($pickpoint["pickpoint"])) . esc_html($time_html) . '</option>';
                                                    echo ($pick_desc ? '<option disabled>&nbsp;&nbsp; ' . esc_html($pick_desc) . '</option>' : '');
                                                } ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="mage_customer_info_area">
                                    <?php
                                    $date = isset($_GET[$date_var]) ? mage_wp_date(sanitize_text_field(wp_unslash($_GET[$date_var])), 'Y-m-d') : gmdate('Y-m-d');
                                    $start = $input_boarding_var;
                                    $end = $input_dropping_var;
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
                                if ($available_seat > 0) {
                                    wbbm_extra_services_section($id);
                                }
                                ?>
                                <?php mage_book_now_area($available_seat); ?>
                            </div>
                        </div>
                    </div>
                </form>
                <?php do_action('mage_multipurpose_reg'); ?>
            </div>
        <?php
        endif;
    }
}

function mage_search_form_vertical($target = '') {
    ?>
    <div class="mage_container">
        <div class="mage_search_box_small">
            <h2>
                <?php 
                // Get the ticket text option, defaulting to a safe translation
                echo esc_html(wbbm_get_option('wbbm_buy_ticket_text', 'wbbm_label_setting_sec', __('BUY TICKET', 'bus-booking-manager'))); 
                ?>
            </h2>
            <?php 
            // Call the action with sanitized parameters
            do_action('mage_search_from_only', false, sanitize_text_field($target)); 
            ?>
        </div>
    </div>
    <?php
}

function mage_search_form_horizontal($single_bus, $target = '') {
    // Sanitize background color
    $search_form_b_color = sanitize_hex_color(wbbm_get_option('wbbm_search_form_b_color', 'wbbm_style_setting_sec'));
    
    // Get and escape the buy ticket text option
    $wbbm_buy_ticket_text = esc_html(wbbm_get_option('wbbm_buy_ticket_text', 'wbbm_label_setting_sec', __('Buy Ticket', 'bus-booking-manager')));
    ?>
    <div class="mage_container">
        <div class="search_form_horizontal" style="background-color: <?php echo esc_attr($search_form_b_color ? $search_form_b_color : '#b30c3b12'); ?>;">
            <?php if ($wbbm_buy_ticket_text) { ?>
                <h2><?php echo esc_html($wbbm_buy_ticket_text); ?></h2>
            <?php } ?>
            <?php $this->search_from_only($single_bus, sanitize_text_field($target)); ?>
        </div>
    </div>
    <?php
}

function mage_search_page_vertical() {
    $target = '';
    if (isset($_GET['bus_start_route'], $_GET['bus_end_route'], $_GET['j_date'])) {
        // Safely get the nonce from the request (accept GET or POST)
        $nonce = isset($_REQUEST['bus_search_nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['bus_search_nonce'])) : '';

        // Verify the nonce
        if ( ! $nonce || ! wp_verify_nonce($nonce, 'bus_search_nonce_action') ) {
            wc_add_notice(__('Security check failed. Please try again.', 'bus-booking-manager'), 'error');
            return false;
        }
        // Sanitize input from the query parameters
        $bus_start_route = isset($_GET['bus_start_route']) ? sanitize_text_field(wp_unslash($_GET['bus_start_route'])) : '';
        $bus_end_route = isset($_GET['bus_end_route']) ? sanitize_text_field(wp_unslash($_GET['bus_end_route'])) : '';
        $journey_date = isset($_GET['j_date']) ? sanitize_text_field(wp_unslash($_GET['j_date'])) : '';
        ?>
        <div class="mage_container">
            <div class="mage_row">
                <div class="mage_search_box_sidebar">
                    <div class="mage_sidebar_search_form">
                        <h2><?php echo esc_html(wbbm_get_option('wbbm_buy_ticket_text', 'wbbm_label_setting_sec', __('BUY TICKET', 'bus-booking-manager'))); ?></h2>
                        <?php do_action('mage_search_from_only', false, sanitize_text_field($target)); ?>
                    </div>
                </div>
                <div class="mage_search_area">
                    <?php $this->mage_search_list(); ?>
                </div>
            </div>
        </div>
        <?php
    } else {
        $this->mage_search_form_vertical(sanitize_text_field($target));
    }
}

function search_from_only($single_bus, $target) {
    $search_form_dropdown_b_color = wbbm_get_option('wbbm_search_form_dropdown_b_color', 'wbbm_style_setting_sec');
    $search_form_dropdown_text_color = wbbm_get_option('wbbm_search_form_dropdown_t_color', 'wbbm_style_setting_sec');
    $search_form_dropdown_text_color = $search_form_dropdown_text_color ? $search_form_dropdown_text_color : '';
    $wbbm_bus_prices = get_post_meta(get_the_ID(), 'wbbm_bus_prices', true);
    ?>
    <form action="<?php echo esc_url($single_bus ? '' : get_site_url() . '/' . sanitize_title($target) . '/'); ?>" method="get" class="mage_form">
        <?php do_action('active_date', $single_bus, get_the_ID());
        wp_nonce_field('bus_search_nonce_action', 'bus_search_nonce');
        ?>
        <div class="mage_form_list">
            <label for="bus_start_route">
                <span class="fa fa-map-marker"></span>
                <?php echo esc_html(wbbm_get_option('wbbm_from_text', 'wbbm_label_setting_sec', __('From :', 'bus-booking-manager'))); ?>
            </label>
            <div class="mage_input_select mage_bus_boarding_point">
                <div class="route-input-wrap">
                    <input id="bus_start_route" type="text" class="mage_form_control" name="bus_start_route" value="<?php echo esc_attr(mage_get_isset('bus_start_route')); ?>" placeholder="<?php esc_html_e('Please Select', 'bus-booking-manager'); ?>" autocomplete="off" required/>
                </div>
                <?php
                if ($single_bus) {
                    $start_stops = get_post_meta(get_the_ID(), 'wbbm_bus_prices', true) ?: [];
                    if ($start_stops) {
                        $start_stops = array_values(array_reduce($start_stops, function ($r, $a) {
                            if (!isset($r[$a['wbbm_bus_bp_price_stop']])) {
                                $r[$a['wbbm_bus_bp_price_stop']] = $a;
                            }
                            return $r;
                        }, []));
                        echo '<div class="mage_input_select_list"';
                        if ($search_form_dropdown_b_color) {
                            echo ' style="background-color:' . esc_attr($search_form_dropdown_b_color) . '"';
                        }
                        echo '><ul>';
                        foreach ($start_stops as $_start_stops) {
                            echo '<li ' . ( $search_form_dropdown_text_color ? 'style="color:' . esc_attr($search_form_dropdown_text_color) . '"' : '' ) . ' data-route="' . esc_attr($_start_stops['wbbm_bus_bp_price_stop']) . '">
                                    <span class="fa fa-map-marker"></span>' . esc_html($_start_stops['wbbm_bus_bp_price_stop']) . '</li>';
                        }
                        echo '</ul></div>';
                    }
                } else {
                    mage_route_list();
                }
                ?>
            </div>
        </div>
        <div class="mage_form_list">
            <label for="bus_end_route">
                <span class="fa fa-map-marker"></span>
                <span id="wbtm_show_msg"></span>
                <?php echo esc_html(wbbm_get_option('wbbm_to_text', 'wbbm_label_setting_sec', __('To :', 'bus-booking-manager'))); ?>
            </label>
            <div class="mage_input_select mage_bus_dropping_point">
                <div class="route-input-wrap">
                    <input id="bus_end_route" type="text" class="mage_form_control" name="bus_end_route" value="<?php echo esc_attr(mage_get_isset('bus_end_route')); ?>" placeholder="<?php esc_html_e('Please Select', 'bus-booking-manager'); ?>" autocomplete="off" required/>
                </div>
                <?php
                if ($single_bus) {
                    $end_stops = get_post_meta(get_the_ID(), 'wbbm_bus_prices', true);
                    $end_stops = array_values(array_reduce($end_stops, function ($r, $a) {
                        if (!isset($r[$a['wbbm_bus_dp_price_stop']])) {
                            $r[$a['wbbm_bus_dp_price_stop']] = $a;
                        }
                        return $r;
                    }, []));
                    echo '<div class="mage_input_select_list_static"><ul class="">';
                    foreach ($end_stops as $_end_stops) {
                        echo '<li data-route="' . esc_attr($_end_stops['wbbm_bus_dp_price_stop']) . '"><span class="fa fa-map-marker"></span>' . esc_html($_end_stops['wbbm_bus_dp_price_stop']) . '</li>';
                    }
                    echo '</ul></div>';
                } else {
                    mage_route_list(true);
                }
                ?>
            </div>
        </div>
        <div class="mage_form_list">
            <label for="j_date">
                <span class="fa fa-calendar"></span>
                <?php echo esc_html(wbbm_get_option('wbbm_date_of_journey_text', 'wbbm_label_setting_sec', __('Date of Journey :', 'bus-booking-manager'))); ?>
            </label>
            <input type="text" class="mage_form_control" id="j_date" readonly name="j_date" value="<?php echo esc_attr(mage_get_isset('j_date')); ?>" placeholder="<?php echo esc_html(wbbm_convert_datepicker_dateformat()); ?>" autocomplete="off" required>
        </div>
        <?php if (!$single_bus) {
            $return = (mage_get_isset('bus-r') == 'oneway') ? false : true; ?>
            <div class="mage_form_list mage_return_date <?php echo esc_attr($return ? '' : 'mage_hidden'); ?>">
                <label for="r_date">
                    <span class="fa fa-calendar"></span>
                    <?php echo esc_html(wbbm_get_option('wbbm_return_date_text', 'wbbm_label_setting_sec', __('Return Date (Optional):', 'bus-booking-manager'))); ?>
                </label>
                <input type="text" class="mage_form_control" id="r_date" readonly name="r_date" value="<?php echo esc_attr(mage_get_isset('r_date')); ?>" autocomplete="off" placeholder="<?php echo esc_html(wbbm_convert_datepicker_dateformat()); ?>">
            </div>
        <?php } ?>
        <div class="mage_form_list">
            <div class="mage_form_radio">
                <?php if (!$single_bus) { ?>
                    <label for="one_way">
                        <input type="radio" name="bus-r" value='oneway' id="one_way" <?php echo esc_attr($return ? '' : 'checked' ); ?> />
                        <?php echo esc_html(wbbm_get_option('wbbm_one_way_text', 'wbbm_label_setting_sec', __('One Way', 'bus-booking-manager'))); ?>
                    </label>
                    <label for="return">
                        <input type="radio" name="bus-r" value='return' id="return" <?php echo esc_attr($return ? 'checked' : '' );?>/>
                        <?php echo esc_html(wbbm_get_option('wbbm_return_text', 'wbbm_label_setting_sec', __('Return', 'bus-booking-manager'))); ?>
                    </label>
                <?php } else {
                    echo '<label>&nbsp;</label>';
                } ?>
            </div>
            <div class="mage_form_search">
                <button type="submit" class="mage_button">
                    <span class="fa fa-search"></span>
                    <?php echo esc_html(wbbm_get_option('wbbm_search_buses_text', 'wbbm_label_setting_sec', __('Search', 'bus-booking-manager'))); ?>
                </button>
            </div>
        </div>
    </form>
    <?php
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (isset($_GET['bus_start_route'], $_GET['bus_end_route'], $_GET['j_date'])) {
        // Safely get the nonce from the request (accept GET or POST)
        // $nonce = isset($_REQUEST['bus_search_nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['bus_search_nonce'])) : '';

        // // Verify the nonce
        // if ( ! $nonce || ! wp_verify_nonce($nonce, 'bus_search_nonce_action') ) {
        //     wc_add_notice(__('Security check failed. Please try again. 666', 'bus-booking-manager'), 'error');
        //     return false;
        // }
        // Show Next dates list
        do_action('mage_next_date', false, true, $target);
    }
}

function wbbm_prevent_form_resubmission_fun() {
    ?>
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
    <?php
}

	}
