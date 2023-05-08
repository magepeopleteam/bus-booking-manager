<?php
if (!defined('ABSPATH')) exit;  // if direct access

class AjaxClass extends CommonClass
{
    public function __construct()
    {

    }


    function wbbm_bus_detail(){

        $bus_id = $_POST['bus_id'];
        $return = $_POST['bus_return'];
        $available_seat = $_POST['available_seat'];
        $boarding = $_POST['boarding'];
        $dropping = $_POST['dropping'];
        $bus_type = $_POST['bus_type'];
        $boarding_time = $_POST['boarding_time'];
        $dropping_time = $_POST['dropping_time'];
        $in_cart = $_POST['in_cart'];



        $seat_price_adult = mage_seat_price($bus_id, $boarding, $dropping, 'adult');
        $seat_price_child = mage_seat_price($bus_id, $boarding, $dropping, 'child');
        $seat_price_infant = mage_seat_price($bus_id, $boarding, $dropping, 'infant');
        $seat_price_entire = mage_seat_price($bus_id, $boarding, $dropping, 'entire');

        $entire_bus_booking = wbbm_get_option('wbbm_entire_bus_booking_switch', 'wbbm_general_setting_sec');

        $total_seat = get_post_meta($bus_id, 'wbbm_total_seat', true);


        ?>

        <div class="mage_bus_info">
            <h3><a href="<?php echo get_the_permalink($bus_id) ?>"><?php echo get_the_title($bus_id); ?></a></h3>
            <p>
                <strong><?php echo wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec', __('Type', 'bus-booking-manager')); ?>
                </strong>:
                <?php echo $bus_type; ?>
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
                <?php $this->mage_qty_box($seat_price_adult, 'adult_quantity', false,$available_seat); ?>
            </div>
            <input type="hidden" name="available_quantity" value="<?php echo $available_seat?>">
            <?php
            $is_price_zero_allow = get_post_meta($bus_id, 'wbbm_price_zero_allow', true);

            if (($seat_price_child > 0) || ($is_price_zero_allow == 'on')) : ?>
                <div class="mage_center_space">
                    <p>
                        <strong><?php echo wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec', __('Child :', 'bus-booking-manager')); ?></strong>
                        <?php echo wc_price($seat_price_child); ?>/
                        <small><?php echo wbbm_get_option('wbbm_ticket_text', 'wbbm_label_setting_sec', __('Ticket', 'bus-booking-manager')); ?></small>
                    </p>
                    <?php $this->mage_qty_box($seat_price_child, 'child_quantity', false,$available_seat); ?>
                </div>
            <?php endif; ?>

            <?php if (($seat_price_infant > 0) || ($is_price_zero_allow == 'on')) : ?>
                <div class="mage_center_space">
                    <p>
                        <strong><?php echo wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec', __('Infant :', 'bus-booking-manager')); ?></strong>
                        <?php echo wc_price($seat_price_infant); ?>/
                        <small><?php echo wbbm_get_option('wbbm_ticket_text', 'wbbm_label_setting_sec', __('Ticket', 'bus-booking-manager')); ?></small>
                    </p>
                    <?php $this->mage_qty_box($seat_price_infant, 'infant_quantity', false,$available_seat); ?>
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
                wbbm_extra_services_section($bus_id);
            }
            ?>
            <?php $this->mage_book_now_area($available_seat,$bus_id); ?>
        </div>
        <?php
        die;
    }


    function mage_qty_box($price,$name, $return,$available_seat) {

        if ($available_seat > 0) {

            if($name == 'child_quantity') {
                $ticket_title = wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec', __('Child', 'bus-booking-manager'));
                $ticket_type = 'child';
            } elseif($name == 'infant_quantity') {
                $ticket_title = wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec', __('Infant', 'bus-booking-manager'));
                $ticket_type = 'infant';
            } else {
                $ticket_title = wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec', __('Adult', 'bus-booking-manager'));
                $ticket_type = 'adult';
            }
            ?>
            <div class="mage_form_group">
                <div class="mage_flex mage_qty_dec"><span class="fa fa-minus"></span></div>
                <input type="text"
                       class="mage_form mage_seat_qty ra_seat_qty"
                       data-ticket-title="<?php echo $ticket_title.' '.__('Passenger info', 'bus-booking-manager'); ?>"
                       data-ticket-type="<?php echo $ticket_type; ?>"
                       data-price="<?php echo $price; ?>"
                       name="<?php echo $name; ?>"
                       value="<?php echo $_POST[$name]??0; ?>"
                       min="0"
                       max="<?php echo $available_seat; ?>"
                       required
                />
                <div class="mage_flex mage_qty_inc"><span class="fa fa-plus"></span></div>
            </div>
            <?php
        }
    }


    function mage_book_now_area($available_seat = null,$bus_id){
        $currency_pos = get_option( 'woocommerce_currency_pos' );
        $is_sell_off = get_post_meta($bus_id, 'wbbm_sell_off', true);

        $search_date = (isset($_GET['j_date']) ? $_GET['j_date'] : '');
        $current_date = date('Y-m-d');

        $boarding_time = get_wbbm_datetime(boarding_dropping_time(false, false), 'time');
        // If Current time is greater than bus time
        // Bus should not be shown in search result
        if($current_date === $search_date) {
            $search_timestamp = strtotime($search_date.' '.$boarding_time);
            if(current_time('timestamp') >= $search_timestamp ) {
                return;
            }
        }
        ?>
        <div class="mage_flex mage_book_now_area">
            <div class="mage_thumb mage-notification-area">
                <p class="mage-notification mage-seat-available"><?php _e('Only '.$available_seat.' Seat Available', 'bus-booking-manager'); ?></p>
            </div>
            <div class="mage_flex_equal">
                <div class="mage_sub_price">
                    <div class="mage_sub_total"><div><?php echo wbbm_get_option('wbbm_sub_total_text', 'wbbm_label_setting_sec',__('Sub Total', 'bus-booking-manager')); echo ':'; ?></div> <span class="mage_subtotal_figure">0</span> </div>
                </div>
                <?php if( $is_sell_off != 'on' ) :

                    do_action('wbbm_before_add_cart_btn', $bus_id);
                    ?>

                    <div class="mage_book_now mage_center_space">
                        <button type="button" class="mage_button mage_book_now <?php if($available_seat == 0){ echo 'cursor-disabled'; } ?>"><?php  echo wbbm_get_option('wbbm_book_now_text', 'wbbm_label_setting_sec',__('Book Now', 'bus-booking-manager')); ?></button>
                        <button type="submit" class="mage_hidden single_add_to_cart_button" name="add-to-cart" value="<?php echo $bus_id; ?>"></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }






}

