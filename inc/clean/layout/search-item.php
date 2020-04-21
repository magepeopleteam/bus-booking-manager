<?php
function mage_search_item($return) {
    $id = get_the_id();
    $boarding_var       = $return ? 'bus_end_route' : 'bus_start_route';
    $dropping_var       = $return ? 'bus_start_route' : 'bus_end_route';
    $date_var           = $return ? 'r_date' : 'j_date';
    $in_cart            = mage_find_product_in_cart();

    $term               = get_the_terms($id, 'wbbm_bus_cat');
    $type               = $term[0]->name;

    $available_seat     = mage_available_seat(mage_get_isset($date_var));

    $boarding           = mage_get_isset($boarding_var);
    $dropping           = mage_get_isset($dropping_var);

    $seat_price_adult   = mage_seat_price($id,$boarding,$dropping,true);
    $seat_price_child   = mage_seat_price($id,$boarding,$dropping,false);

    $boarding_time      = get_wbbm_datetime(boarding_dropping_time(false, $return),'time');
    $dropping_time      = get_wbbm_datetime(boarding_dropping_time(true, $return),'time');
    
    if ($seat_price_adult > 0) { ?>
        <div class="mage_search_list <?php echo $in_cart ? 'booked' : ''; ?>">
            <form action="" method="post">
                <div class="mage_flex xs_not_flex">
                    <div class="mage_thumb"><?php the_post_thumbnail('full'); ?></div>
                    <div class="mage_flex_equal mage_bus_details">
                        <div class="mage_bus_info">
                            <h3><a href="<?php echo get_the_permalink($id) ?>"><?php echo the_title(); ?></a></h3>
                            <p>
                                <strong><?php echo wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec',__('Type :', 'bus-booking-manager')); ?></strong>
                                <?php echo $type; ?>
                            </p>
                            <p>
                                <strong><?php echo wbbm_get_option('wbbm_boarding_points_text', 'wbbm_label_setting_sec',__('Boarding :', 'bus-booking-manager')); ?></strong>
                                <?php echo $boarding; ?>
                                <strong>(<?php echo $boarding_time; ?>)</strong>
                            </p>
                            <p>
                                <strong><?php echo wbbm_get_option('wbbm_dropping_points_text', 'wbbm_label_setting_sec',__('Dropping :', 'bus-booking-manager')); ?></strong>
                                <?php echo $dropping; ?>
                                <strong>(<?php echo $dropping_time; ?>)</strong>
                            </p>
                            <p>
                                <strong><?php echo $available_seat; ?></strong>
                                <?php echo wbbm_get_option('wbbm_seats_available_text', 'wbbm_label_setting_sec',__('Seat Available', 'bus-booking-manager')); ?>
                            </p>
                            <?php if ($in_cart) { ?>
                                <p class="already_cart"><span class="fa fa-cart-plus"></span><?php echo wbbm_get_option('wbbm_item_in_cart_text', 'wbbm_label_setting_sec',__('Item has been added to cart', 'bus-booking-manager')); ?></p>
                            <?php } ?>
                        </div>
                        <div class="mage_price_info">
                            <?php if ($seat_price_child > 0) { ?>
                                <p><strong><?php echo wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec',__('Fare :', 'bus-booking-manager')); ?></strong></p>
                                <div class="mage_center_space">
                                    <div>
                                        <p>
                                            <?php echo wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec',__('Adult :', 'bus-booking-manager')); ?>
                                            <strong><?php echo wc_price($seat_price_adult); ?></strong>/
                                            <small><?php echo wbbm_get_option('wbbm_ticket_text', 'wbbm_label_setting_sec',__('Ticket', 'bus-booking-manager')); ?></small>
                                        </p>
                                    </div>
                                    <?php mage_qty_box($seat_price_adult,'adult_quantity', false); ?>
                                </div>
                                <div class="mage_center_space">
                                    <p>
                                        <?php echo wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec',__('Child :', 'bus-booking-manager')); ?>
                                        <strong><?php echo wc_price($seat_price_child); ?></strong>/
                                        <small><?php echo wbbm_get_option('wbbm_ticket_text', 'wbbm_label_setting_sec',__('Ticket', 'bus-booking-manager')); ?></small>
                                    </p>
                                    <?php mage_qty_box($seat_price_child,'child_quantity', false); ?>
                                </div>
                            <?php } else { ?>
                                <div class="mage_center_space">
                                    <div>
                                        <p>
                                            <strong><?php echo wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec',__('Fare :', 'bus-booking-manager')); ?></strong>
                                            <strong><?php echo wc_price($seat_price_adult); ?></strong>/
                                            <small><?php echo wbbm_get_option('wbbm_ticket_text', 'wbbm_label_setting_sec',__('Ticket', 'bus-booking-manager')); ?></small>
                                        </p>
                                    </div>
                                    <?php mage_qty_box($seat_price_adult,'adult_quantity', false); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="mage_customer_info_area">
                    <?php
                    $date = isset($_GET[$date_var]) ? strip_tags($_GET[$date_var]) : date('Y-m-d');
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
                </div>
                <?php mage_book_now_area(); ?>
            </form>
            <?php do_action('mage_multipurpose_reg'); ?>
        </div>
        <?php
    }
}