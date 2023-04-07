<div style="background-color:<?php echo ($search_form_result_b_color != '' ? $search_form_result_b_color : '#b30c3b12'); ?>" class="mage_search_list <?php echo $in_cart ? 'booked' : ''; ?>" data-seat-available="<?php echo $available_seat; ?>">
                    <form action="" method="post">
                        <div class="mage_flex xs_not_flex">
                            <div class="mage_thumb"><?php the_post_thumbnail('full'); ?></div>
                            <div class="mage_flex_equal mage_bus_details">
                                <div class="mage_bus_info">
                                    <h3><a href="<?php echo get_the_permalink($id) ?>"><?php echo the_title(); ?></a></h3>
                                    <p>
                                        <strong><?php echo wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec', __('Type :', 'bus-booking-manager')); ?></strong>
                                        <?php echo $type_name; ?>
                                    </p>
                                    <p>
                                        <strong><?php echo wbbm_get_option('wbbm_boarding_points_text', 'wbbm_label_setting_sec', __('Boarding :', 'bus-booking-manager')); ?></strong>
                                        <?php echo $boarding; ?>
                                        <strong>(<?php echo $boarding_time; ?>)</strong>
                                    </p>
                                    <p>
                                        <strong><?php echo wbbm_get_option('wbbm_dropping_points_text', 'wbbm_label_setting_sec', __('Dropping :', 'bus-booking-manager')); ?></strong>
                                        <?php echo $dropping; ?>
                                        <strong>(<?php echo $dropping_time; ?>)</strong>
                                    </p>
                                    <?php if( $is_sell_off != 'on' ) : ?>
                                        <?php if($seat_available && $seat_available == 'on') : ?>
                                            <p>
                                                <strong><?php echo $available_seat; ?></strong>
                                                <?php echo wbbm_get_option('wbbm_seats_available_text', 'wbbm_label_setting_sec', __('Seat Available', 'bus-booking-manager')); ?>
                                            </p>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if ($in_cart) { ?>
                                        <p class="already_cart"><?php echo wbbm_get_option('wbbm_item_in_cart_text', 'wbbm_label_setting_sec', __('Item has been added to cart', 'bus-booking-manager')); ?>
                                        </p>
                                    <?php } ?>
                                </div>
                                <div class="mage_price_info">
                                    <div class="mage_center_space">
                                        <h3><strong><?php echo wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec', __('Fare', 'bus-booking-manager')); ?></strong></h3>
                                    </div>
                                    <input type="hidden" name="available_quantity" value="<?php echo $available_seat?>">
                                    <div class="mage_center_space">
                                        <div>
                                            <p>
                                                <strong><?php echo wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec', __('Adult', 'bus-booking-manager')); echo ':'; ?></strong>
                                                <?php echo wc_price($seat_price_adult); ?>/
                                                <small><?php echo wbbm_get_option('wbbm_ticket_text', 'wbbm_label_setting_sec', __('Ticket', 'bus-booking-manager')); ?></small>
                                            </p>
                                        </div>
                                        <?php mage_qty_box($seat_price_adult, 'adult_quantity', false); ?>
                                    </div>
                                    <?php
                                    $is_price_zero_allow = get_post_meta($id, 'wbbm_price_zero_allow', true);

                                    if ( ($seat_price_child > 0) || ($is_price_zero_allow == 'on') ) : ?>
                                        <div class="mage_center_space">
                                            <p>
                                                <strong><?php echo wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec', __('Child', 'bus-booking-manager')); echo ':'; ?></strong>
                                                <?php echo wc_price($seat_price_child); ?>/
                                                <small><?php echo wbbm_get_option('wbbm_ticket_text', 'wbbm_label_setting_sec', __('Ticket', 'bus-booking-manager')); ?></small>
                                            </p>
                                            <?php mage_qty_box($seat_price_child, 'child_quantity', false); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ( ($seat_price_infant > 0) || ($is_price_zero_allow == 'on') ) : ?>
                                        <div class="mage_center_space">
                                            <p>
                                                <strong><?php echo wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec', __('Infant', 'bus-booking-manager')); echo ':'; ?></strong>
                                                <?php echo wc_price($seat_price_infant); ?>/
                                                <small><?php echo wbbm_get_option('wbbm_ticket_text', 'wbbm_label_setting_sec', __('Ticket', 'bus-booking-manager')); ?></small>
                                            </p>
                                            <?php mage_qty_box($seat_price_infant, 'infant_quantity', false); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (($available_seat == $total_seat) && ($seat_price_entire > 0) ) : ?>
                                        <div class="mage_center_space">
                                            <p>
                                                <strong><?php echo wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') : _e('Entire Bus', 'bus-booking-manager'); echo ':'; ?></strong>
                                                <?php echo wc_price($seat_price_entire); ?>
                                            </p>
                                            <?php echo wbbm_entire_switch($seat_price_entire, 'entire_quantity', false); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($pickpoints)) : ?>
                                        <div class="mage_center_space">
                                            <div class="mage-form-field mage-form-pickpoint-field">
                                                <label for="mage_pickpoint"><?php echo wbbm_get_option('wbbm_pickuppoint_area_text', 'wbbm_label_setting_sec', __('Select Pickup Area', 'bus-booking-manager')); echo ':'; ?></label>
                                                <select name="mage_pickpoint" class="mage_pickpoint">
                                                    <option value=""><?php echo wbbm_get_option('wbbm_pickuppoint_area_text', 'wbbm_label_setting_sec', __('Select Pickup Area', 'bus-booking-manager')); ?></option>
                                                    <?php
                                                    foreach ($pickpoints as $pickpoint) {
                                                        echo '<option value="' . $pickpoint['pickpoint'] . '->' . $pickpoint['time']. '">' . ucfirst($pickpoint['pickpoint']) . ' <=> ' . $pickpoint['time'] . '</option>';
                                                    } ?>
                                                </select>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="mage_customer_info_area">
                                        <?php
                                        $date = isset($_GET[$date_var]) ? mage_wp_date($_GET[$date_var], 'Y-m-d') : date('Y-m-d');
                                        //$date = isset($_GET[$date_var]) ? wbbm_convert_date_to_php(mage_get_isset($date_var)) : date('Y-m-d');

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
                                    ?>
                                    <?php mage_book_now_area($available_seat);?>
                                </div>
                            </div>
                        </div>
                    </form>
                    <?php do_action('mage_multipurpose_reg'); ?>
                </div>
