<?php
get_header();
the_post();
mage_search_form_horizontal(true);
$id = get_the_id();
$return = false;
$date_format        = get_option( 'date_format' );
$boarding_var = $return ? 'bus_end_route' : 'bus_start_route';
$dropping_var = $return ? 'bus_start_route' : 'bus_end_route';
$date_var = $return ? 'r_date' : 'j_date';
$in_cart = mage_find_product_in_cart();

$term = get_the_terms($id, 'wbbm_bus_cat');
$type = $term[0]->name;

$available_seat = mage_available_seat(mage_get_isset($date_var));

$boarding = mage_get_isset($boarding_var);
$dropping = mage_get_isset($dropping_var);

$seat_price_adult = mage_seat_price($id, $boarding, $dropping, true);
$seat_price_child = mage_seat_price($id, $boarding, $dropping, false);

$boarding_time = get_wbbm_datetime(boarding_dropping_time(false, $return),'time');
$dropping_time = get_wbbm_datetime(boarding_dropping_time(true, $return),'time');
$odd_list = mage_odd_list_check(false);
$off_day = mage_off_day_check(false);

?>
    <div class="mage_container">
        <div class="mage_search_list <?php echo $in_cart ? 'booked' : ''; ?>">
            <form action="" method="post">
                <div class="mage_flex_equal xs_not_flex">
                    <div class="mage_thumb"><?php the_post_thumbnail('full'); ?></div>
                    <div class="mage_bus_details">
                        <div class="mage_bus_info">
                            <h3><?php the_title(); ?></h3>
                            <p>
                                <strong><?php _e('Type :', 'bus-booking-manager'); ?></strong>
                                <?php echo $type; ?>
                            </p>
                            <p>
                                <strong><?php echo wbbm_get_option('wbbm_bus_no_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_bus_no_text', 'wbbm_label_setting_sec') : _e('Bus No:', 'bus-booking-manager'); ?></strong>
                                <?php echo get_post_meta(get_the_id(), 'wbbm_bus_no', true); ?>
                            </p>
                            <?php if ($seat_price_adult > 0 && $odd_list && $off_day) { ?>
                                <p>
                                    <strong><?php _e('Boarding : ', 'bus-booking-manager'); ?></strong>
                                    <?php echo $boarding; ?>
                                    <strong>(<?php echo $boarding_time; ?>)</strong>
                                </p>
                                <p>
                                    <strong><?php _e('Dropping : ', 'bus-booking-manager'); ?></strong>
                                    <?php echo $dropping; ?>
                                    <strong>(<?php echo $dropping_time; ?>)</strong>
                                </p>
                            <?php } ?>
                            <p>
                                <strong><?php echo wbbm_get_option('wbbm_total_seat_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_total_seat_text', 'wbbm_label_setting_sec') : _e('Total Seat:', 'bus-booking-manager'); ?></strong>
                                <?php echo get_post_meta(get_the_id(), 'wbbm_total_seat', true); ?>
                            </p>
                            <?php if ($seat_price_adult > 0 && $odd_list && $off_day) { ?>
                                <p>
                                    <strong><?php echo $available_seat; ?></strong>
                                    <?php _e('Seat Available', 'bus-booking-manager'); ?>
                                </p>
                                <?php if ($in_cart) { ?>
                                    <p class="already_cart"><span class="fa fa-cart-plus"></span><?php _e('Item has been added to cart', 'bus-booking-manager'); ?></p>
                                <?php } ?>
                                <?php if ($seat_price_child > 0) { ?>
                                    <p><strong><?php _e('Fare : ', 'bus-booking-manager'); ?></strong></p>
                                    <div class="mage_center_space mar_b">
                                        <div>
                                            <p>
                                                <?php _e('Adult : ', 'bus-booking-manager'); ?>
                                                <strong><?php echo wc_price($seat_price_adult); ?></strong>/
                                                <small><?php _e('Ticket', 'bus-booking-manager'); ?></small>
                                            </p>
                                        </div>
                                        <?php mage_qty_box($seat_price_adult, 'adult_quantity', false); ?>
                                    </div>
                                    <div class="mage_center_space mar_b">
                                        <p>
                                            <?php _e('Child : ', 'bus-booking-manager'); ?>
                                            <strong><?php echo wc_price($seat_price_child); ?></strong>/
                                            <small><?php _e('Ticket', 'bus-booking-manager'); ?></small>
                                        </p>
                                        <?php mage_qty_box($seat_price_child, 'child_quantity', false); ?>
                                    </div>
                                <?php } else { ?>
                                    <div class="mage_center_space mar_b">
                                        <div>
                                            <p>
                                                <strong><?php _e('Fare : ', 'bus-booking-manager'); ?></strong>
                                                <strong><?php echo wc_price($seat_price_adult); ?></strong>/
                                                <small><?php _e('Ticket', 'bus-booking-manager'); ?></small>
                                            </p>
                                        </div>
                                        <?php mage_qty_box($seat_price_adult, 'adult_quantity', false); ?>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                            <?php the_content(); ?>
                            <div class="mage_flex_equal">
                                <div>
                                    <h4 class="mar_b bor_tb">
                                        <?php echo wbbm_get_option('wbbm_boarding_points_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_boarding_points_text', 'wbbm_label_setting_sec') : _e('Boarding Ponints', 'bus-booking-manager'); ?>
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
                                        <?php echo wbbm_get_option('wbbm_dropping_points_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_dropping_points_text', 'wbbm_label_setting_sec') : _e('Dropping Ponints', 'bus-booking-manager'); ?>
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
                <?php
                if ($seat_price_adult > 0 && $odd_list && $off_day) {
                    mage_book_now_area();
                }
                if(mage_get_isset($date_var) &&(!$odd_list || !$off_day)){
                   echo '<span class="mage_error" style="display: block;text-align: center;padding: 5px;margin: 10px 0 0 0;">'.date($date_format,strtotime(mage_get_isset($date_var))).' Operational Off day !'.'</span>';
                }
                ?>
            </form>
            <?php do_action('mage_multipurpose_reg'); ?>
        </div>
    </div>
<?php get_footer(); ?>