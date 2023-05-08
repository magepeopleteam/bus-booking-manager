<div style="background-color:<?php echo($search_form_result_b_color != '' ? $search_form_result_b_color : '#b30c3b12'); ?>" class="mage_search_list theme_minimal <?php echo $in_cart ? 'booked' : ''; ?>" data-seat-available="<?php echo $available_seat; ?>">
    <div class="mage-search-brief-row" style="color:<?php echo($search_list_header_text_color != '' ? $search_list_header_text_color : '#000'); ?>">
        <div class="mage-search-res-header--img">
            <?php if (has_post_thumbnail()) {
                the_post_thumbnail('full');
            } else {
                echo '<img src="' . PLUGIN_ROOT . '/images/bus-placeholder.png' . '" loading="lazy" />';
            }
            ?>
        </div>

        <div class="mage-search-res-header--left">
            <div class="mage-bus-title">
                <a class="bus-title" href="<?php echo get_the_permalink($id) ?>"><?php echo the_title(); ?></a>
                <span><?php echo $coach_no; ?></span>
                <?php if ($wbbm_features) { ?>
                    <p class="wbbm-feature-icon">
                        <?php foreach ($wbbm_features as $feature_id) { ?>
                            <span class="customCheckbox">
                                <span title="<?php echo get_term($feature_id)->name ?>" class="mR_xs <?php echo get_term_meta($feature_id, 'feature_icon', true) ?>"></span>
                            </span>
                        <?php } ?>
                    </p>
                <?php } ?>
            </div>
            <div>
                <?php echo '<p class="mage-bus-stopage"><span class="dashicons dashicons-location"></span> ' . wbbm_get_option('wbbm_from_text', 'wbbm_label_setting_sec', __('From: ', 'bus-booking-manager')) . ' ' . $boarding . ' ( ' . get_wbbm_datetime($boarding_time, 'time') . ' )</p>'; ?>
                <?php echo '<p class="mage-bus-stopage"><span class="dashicons dashicons-location"></span> ' . wbbm_get_option('wbbm_to_text', 'wbbm_label_setting_sec', __('To: ', 'bus-booking-manager')) . ' ' . $dropping . ' ( ' . get_wbbm_datetime($dropping_time, 'time') . ' )</p>'; ?>
            </div>
        </div>

        <div class="mage-search-res-header--right">
            <?php if (isset($general_setting['wbbm_type_column_switch']) && $general_setting['wbbm_type_column_switch'] == 'on') { ?>
                <div>
                    <strong class="mage-sm-show"><?php echo wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec', __('Type', 'bus-booking-manager')); ?></strong>
                    <span><?php echo $type_name; ?></span>
                </div>
            <?php } ?>
            <div>
                <strong class="mage-sm-show">
                    <?php echo wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec', __('Fare', 'bus-booking-manager')); ?>
                </strong>
                <?php echo wc_price($seat_price_adult); ?> / <?php echo wbbm_get_option('wbbm_seat_text', 'wbbm_label_setting_sec', __('Seat', 'bus-booking-manager')); ?>
            </div>

            <?php if (isset($general_setting['wbbm_seat_column_switch']) && $general_setting['wbbm_seat_column_switch'] == 'on') { ?>

                <?php if ($seat_available && $seat_available == 'on') : ?>
                    <div>
                        <strong class="mage-sm-show">
                            <?php echo wbbm_get_option('wbbm_seats_available_text', 'wbbm_label_setting_sec', __('Seat Available', 'bus-booking-manager')); ?>
                        </strong>
                        <?php if ($is_sell_off != 'on') {
                            if ($available_seat > 0) {
                                echo '<p>' . $available_seat . '</p>';
                            } else {
                                echo '<p class="mage-sm-text">' . wbbm_get_option('wbbm_no_seat_available_text', 'wbbm_label_setting_sec', __('No Seat Available', 'bus-booking-manager')) . '</p>';
                            }
                        } ?>
                    </div>
                <?php else : ?>
                    <div>-</div>
                <?php endif; ?>
            <?php } ?>
        </div>
    </div>

    <div class="wbbm-bus-details-options">
        <ul>
            <li class="wbbm-bus-gallery">Bus Photos</li>
            <li class="wbbm-bus-term-conditions">Bus Terms & Conditions</li>
            <li data-bus_id="<?php echo get_the_id() ?>" data-return="<?php echo $return ?>" data-available_seat="<?php echo $available_seat ?>" data-boarding="<?php echo $boarding ?>" data-dropping="<?php echo $dropping ?>" data-bus_type="<?php echo $type_name ?>" data-boarding_time="<?php echo $boarding_time ?>" data-dropping_time="<?php echo $dropping_time ?>" data-in_cart="<?php echo $in_cart ?>" class="mage-bus-detail-action">
                <?php echo wbbm_get_option('wbbm_view_text', 'wbbm_label_setting_sec', __('View', 'bus-booking-manager')); ?>
            </li>
        </ul>
    </div>


    <div class="mage-bus-booking-wrapper">
        <form action="" method="post">
            <div class="mage_flex xs_not_flex">
                <div class="mage_flex_equal mage_bus_details">
                    <div class="mage_bus_info">
                        <h3><a href="<?php echo get_the_permalink($id) ?>"><?php echo the_title(); ?></a>
                        </h3>
                        <p>
                            <strong><?php echo wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec', __('Type', 'bus-booking-manager')); ?>
                            </strong>:
                            <?php echo $type_name; ?>
                        </p>
                        <p>
                            <strong><?php echo wbbm_get_option('wbbm_boarding_points_text', 'wbbm_label_setting_sec', __('Boarding', 'bus-booking-manager')); ?>
                            </strong>:
                            <?php echo $boarding; ?>
                            <strong>(<?php echo get_wbbm_datetime($boarding_time, 'time'); ?>)</strong>
                        </p>
                        <p>
                            <strong><?php echo wbbm_get_option('wbbm_dropping_points_text', 'wbbm_label_setting_sec', __('Dropping', 'bus-booking-manager')); ?>
                            </strong>:
                            <?php echo $dropping; ?>
                            <strong>(<?php echo get_wbbm_datetime($dropping_time, 'time'); ?>)</strong>
                        </p>
                        <p>
                            <strong><?php echo wbbm_get_option('wbbm_date_text', 'wbbm_label_setting_sec', __('Date', 'bus-booking-manager')); ?>
                            </strong>:
                            <?php echo ($return) ? mage_wp_date(mage_get_isset('r_date')) : mage_wp_date(mage_get_isset('j_date')) ?>
                        </p>
                        <p>
                            <strong><?php echo wbbm_get_option('wbbm_starting_text', 'wbbm_label_setting_sec', __('Start Time', 'bus-booking-manager')); ?>
                            </strong>:
                            <?php echo $boarding_time; ?>
                        </p>
                        <p>
                            <strong><?php echo wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec', __('Fare', 'bus-booking-manager')); ?>
                            </strong>:
                            <?php echo wc_price($seat_price_adult) . ' / ' . wbbm_get_option('wbbm_seat_text', 'wbbm_label_setting_sec', __('Seat', 'bus-booking-manager')); ?>
                        </p>
                        <?php if ($in_cart) { ?>
                            <p class="already_cart"><?php echo wbbm_get_option('wbbm_item_in_cart_text', 'wbbm_label_setting_sec', __('Item has been added to cart', 'bus-booking-manager')); ?>
                            </p>
                        <?php } ?>
                    </div>
                    <div class="mage_price_info">
                        <div class="mage_center_space">
                            <h3>
                                <strong><?php echo wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec', __('Fare', 'bus-booking-manager')); ?></strong>
                            </h3>
                        </div>
                        <div class="mage_center_space">
                            <div>
                                <p>
                                    <strong><?php echo wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec', __('Adult :', 'bus-booking-manager')); ?></strong>
                                    <?php echo wc_price($seat_price_adult); ?>/
                                    <small><?php echo wbbm_get_option('wbbm_ticket_text', 'wbbm_label_setting_sec', __('Ticket', 'bus-booking-manager')); ?></small>
                                </p>
                            </div>
                            <?php mage_qty_box($seat_price_adult, 'adult_quantity', false); ?>
                        </div>
                        <?php
                        $is_price_zero_allow = get_post_meta($id, 'wbbm_price_zero_allow', true);

                        if (($seat_price_child > 0) || ($is_price_zero_allow == 'on')) : ?>
                            <div class="mage_center_space">
                                <p>
                                    <strong><?php echo wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec', __('Child :', 'bus-booking-manager')); ?></strong>
                                    <?php echo wc_price($seat_price_child); ?>/
                                    <small><?php echo wbbm_get_option('wbbm_ticket_text', 'wbbm_label_setting_sec', __('Ticket', 'bus-booking-manager')); ?></small>
                                </p>
                                <?php mage_qty_box($seat_price_child, 'child_quantity', false); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (($seat_price_infant > 0) || ($is_price_zero_allow == 'on')) : ?>
                            <div class="mage_center_space">
                                <p>
                                    <strong><?php echo wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec', __('Infant :', 'bus-booking-manager')); ?></strong>
                                    <?php echo wc_price($seat_price_infant); ?>/
                                    <small><?php echo wbbm_get_option('wbbm_ticket_text', 'wbbm_label_setting_sec', __('Ticket', 'bus-booking-manager')); ?></small>
                                </p>
                                <?php mage_qty_box($seat_price_infant, 'infant_quantity', false); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (($entire_bus_booking == 'on') && ($available_seat == $total_seat) && ($seat_price_entire > 0)) : ?>
                            <div class="mage_center_space">
                                <p>
                                    <strong><?php echo wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') : _e('Entire Bus', 'bus-booking-manager');
                                        echo ':'; ?></strong>
                                    <?php echo wc_price($seat_price_entire); ?>
                                </p>
                                <?php echo wbbm_entire_switch($seat_price_entire, 'entire_quantity', false); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($pickpoints)) : ?>
                            <div class="mage_center_space">
                                <div class="mage-form-field mage-form-pickpoint-field">
                                    <strong><label
                                                for="mage_pickpoint"><?php _e('Select Pickup Area', 'bus-booking-manager');
                                            echo ':'; ?></label></strong>
                                    <select name="mage_pickpoint" id="mage_pickpoint">
                                        <option value=""><?php _e('Select your Pickup Area', 'bus-booking-manager'); ?></option>
                                        <?php
                                        foreach ($pickpoints as $pickpoint) {
                                            echo '<option value="' . $pickpoint['pickpoint'] . '->' . $pickpoint['time'] . '">' . ucfirst($pickpoint['pickpoint']) . ' <=> ' . $pickpoint['time'] . '</option>';
                                        } ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>

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

    <div class="mage-bus-booking-gallery" style="display: none">
        <div class="mpStyle">
            <?php do_action('add_super_slider',$id,'wbbm_image_gallery'); ?>
        </div>
    </div>

    <div class="mage-bus-booking-term-conditions" style="display: none">
        <?php $term_condition = get_post_meta($id, 'wbbm_term_condition', true); ?>
        <p><?php echo $term_condition ?></p>
    </div>

    <?php do_action('mage_multipurpose_reg'); ?>
</div>