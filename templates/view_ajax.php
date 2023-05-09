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
                <div class="mage_flex_equal mage_bus_details mage_bus_details-<?php echo get_the_id() ?>">


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