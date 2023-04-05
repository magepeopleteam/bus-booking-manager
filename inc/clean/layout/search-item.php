<?php
function mage_search_item($return)
{
    global $mage_bus_search_theme;    
    $id = get_the_id();
    $search_date = (isset($_GET['j_date']) ? $_GET['j_date'] : '');
    $current_date = date('Y-m-d');

//    $boarding_time = get_wbbm_datetime(boarding_dropping_time(false, $return), 'time');
//    $dropping_time = get_wbbm_datetime(boarding_dropping_time(true, $return), 'time');
    $boarding_time = boarding_dropping_time(false, $return);
    $dropping_time = boarding_dropping_time(true, $return);
    $style = !empty($style) ? $style : '';
    // If Current time is greater than bus time
    // Bus should not be shown in search result
    if($current_date === $search_date) {
        $search_timestamp = strtotime($search_date.' '.$boarding_time);
        if(current_time('timestamp') >= $search_timestamp ) {
            return;
        }
    }

    $boarding_var = $return ? 'bus_end_route' : 'bus_start_route';
    $dropping_var = $return ? 'bus_start_route' : 'bus_end_route';
    $date_var = $return ? 'r_date' : 'j_date';
    $in_cart = mage_find_product_in_cart();

    $type_id = get_post_meta($id, 'wbbm_bus_category', true);
    if($type_id != ''){
        $type_array = get_term_by('term_id', $type_id, 'wbbm_bus_cat');
        $type_name = $type_array->name;
    } else {
        $type_name = '';
    }

    // $available_seat = mage_available_seat(wbbm_convert_date_to_php(mage_get_isset($date_var)));
    $available_seat = wbbm_intermidiate_available_seat($_GET[$boarding_var], $_GET[$dropping_var], wbbm_convert_date_to_php(mage_get_isset($date_var)));
    $total_seats = get_post_meta($id, 'wbbm_total_seat', true);

    $boarding = mage_get_isset($boarding_var);
    $dropping = mage_get_isset($dropping_var);

    $seat_price_adult = mage_seat_price($id, $boarding, $dropping, 'adult');
    $seat_price_child = mage_seat_price($id, $boarding, $dropping, 'child');
    $seat_price_infant = mage_seat_price($id, $boarding, $dropping, 'infant');
    $seat_price_entire = mage_seat_price($id, $boarding, $dropping, 'entire');

    $boarding_point = isset($_GET[$boarding_var]) ? strip_tags($_GET[$boarding_var]) : '';

    $boarding_point_slug = strtolower($boarding_point);
    $boarding_point_slug = preg_replace('/[^A-Za-z0-9-]/', '_', $boarding_point_slug);

    $coach_no = get_post_meta($id, 'wbbm_bus_no', true);
    $pickpoints = get_post_meta($id, 'wbbm_selected_pickpoint_name_' . strtolower($boarding_point_slug), true);
    
    if ($pickpoints) {
        $pickpoints = unserialize($pickpoints);
    }

    $is_sell_off = get_post_meta($id, 'wbbm_sell_off', true);
    $seat_available = get_post_meta($id, 'wbbm_seat_available', true);
    $total_seat = get_post_meta($id, 'wbbm_total_seat', true);
    $is_price_zero_allow = get_post_meta($id, 'wbbm_price_zero_allow', true);
    $general_setting = get_option('wbbm_general_setting_sec') ? maybe_unserialize(get_option('wbbm_general_setting_sec')) : array();                                 
    $search_form_result_b_color = wbbm_get_option('wbbm_search_form_result_b_color', 'wbbm_style_setting_sec');
    $search_list_header_text_color = wbbm_get_option('wbbm_search_list_header_text_color', 'wbbm_style_setting_sec');
    $entire_bus_booking = wbbm_get_option('wbbm_entire_bus_booking_switch', 'wbbm_general_setting_sec');
    if ($seat_price_adult > 0 || $is_price_zero_allow == 'on') {
        if ( $mage_bus_search_theme == 'minimal' ) : // Minimal theme design
            ?>
            <div style="background-color:<?php echo ($search_form_result_b_color != '' ? $search_form_result_b_color : '#b30c3b12'); ?>" class="mage_search_list theme_minimal <?php echo $in_cart ? 'booked' : ''; ?>" data-seat-available="<?php echo $available_seat; ?>">
                <div class="mage-search-brief-row" style="color:<?php echo ($search_list_header_text_color != '' ? $search_list_header_text_color : '#000'); ?>">
                    <div class="mage-search-res-header--img">
                        <?php 
                            if(has_post_thumbnail()) {
                                the_post_thumbnail('full');
                            } else {
                                echo '<img src="'.PLUGIN_ROOT. '/images/bus-placeholder.png'.'" loading="lazy" />';
                            }
                        ?>
                    </div>
                    <div class="mage-search-res-header--left">
                        <div class="mage-bus-title">
                            <a class="bus-title" href="<?php echo get_the_permalink($id) ?>"><?php echo the_title(); ?></a>
                            <span><?php echo $coach_no; ?></span>
                        </div>
                        <div>
                            <?php echo '<p class="mage-bus-stopage"><span class="dashicons dashicons-location"></span> '. wbbm_get_option('wbbm_from_text', 'wbbm_label_setting_sec', __('From: ', 'bus-booking-manager')) .' '. $boarding . ' ( ' . get_wbbm_datetime($boarding_time, 'time') . ' )</p>'; ?>
                            <?php echo '<p class="mage-bus-stopage"><span class="dashicons dashicons-location"></span> '. wbbm_get_option('wbbm_to_text', 'wbbm_label_setting_sec', __('To: ', 'bus-booking-manager')).' '. $dropping . ' ( ' . get_wbbm_datetime($dropping_time, 'time') . ' )</p>'; ?>
                        </div>
                    </div>
                    <div class="mage-search-res-header--right">
                    <?php if(isset($general_setting['wbbm_type_column_switch']) && $general_setting['wbbm_type_column_switch'] == 'on'){ ?>
                        <div>
                            <strong class="mage-sm-show"><?php echo wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec', __('Type', 'bus-booking-manager')); ?></strong>
                            <span><?php echo $type_name; ?></span>
                        </div>
                    <?php  } ?>    
                        <div><strong class="mage-sm-show"><?php echo wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec', __('Fare', 'bus-booking-manager')); ?></strong><?php echo wc_price($seat_price_adult); ?> / <?php echo wbbm_get_option('wbbm_seat_text', 'wbbm_label_setting_sec', __('Seat', 'bus-booking-manager')); ?></div>

                        <?php if(isset($general_setting['wbbm_seat_column_switch']) && $general_setting['wbbm_seat_column_switch'] == 'on'){ ?>

                        <?php if($seat_available && $seat_available == 'on') : ?>
                            <div><strong class="mage-sm-show"><?php echo wbbm_get_option('wbbm_seats_available_text', 'wbbm_label_setting_sec', __('Seat Available', 'bus-booking-manager')); ?></strong>
                            <?php if( $is_sell_off != 'on' ){
                                if($available_seat > 0){
                                    echo '<p>'.$available_seat.'</p>';
                                } 
                                else {
                                    echo '<p class="mage-sm-text">'.wbbm_get_option('wbbm_no_seat_available_text', 'wbbm_label_setting_sec', __('No Seat Available', 'bus-booking-manager')).'</p>';
                                }

                            } ?>
                            </div>
                        <?php else : ?>
                            <div>-</div>
                        <?php endif; ?>
                        <?php  } ?>
                        <div>
                            <button class="mage-bus-detail-action"><?php echo wbbm_get_option('wbbm_view_text', 'wbbm_label_setting_sec', __('View', 'bus-booking-manager')); ?></button>
                        </div>
                    </div>
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
                                        <?php echo ($return)?mage_wp_date(mage_get_isset('r_date')):mage_wp_date(mage_get_isset('j_date')) ?>
                                    </p>
                                    <p>
                                        <strong><?php echo wbbm_get_option('wbbm_starting_text', 'wbbm_label_setting_sec', __('Start Time', 'bus-booking-manager')); ?>
                                        </strong>:
                                        <?php echo $boarding_time; ?>
                                    </p>
                                    <p>
                                        <strong><?php echo wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec', __('Fare', 'bus-booking-manager')); ?>
                                        </strong>:
                                        <?php echo wc_price($seat_price_adult) . ' / '. wbbm_get_option('wbbm_seat_text', 'wbbm_label_setting_sec', __('Seat', 'bus-booking-manager')); ?>
                                    </p>
                                    <?php if ($in_cart) { ?>
                                        <p class="already_cart"><?php echo wbbm_get_option('wbbm_item_in_cart_text', 'wbbm_label_setting_sec', __('Item has been added to cart', 'bus-booking-manager')); ?>
                                        </p>
                                    <?php } ?>
                                </div>
                                <div class="mage_price_info">
                                    <div class="mage_center_space">
                                        <h3><strong><?php echo wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec', __('Fare', 'bus-booking-manager')); ?></strong></h3>
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
                                    
									if ( ($seat_price_child > 0) || ($is_price_zero_allow == 'on') ) : ?>
                                        <div class="mage_center_space">
                                            <p>
                                            <strong><?php echo wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec', __('Child :', 'bus-booking-manager')); ?></strong>
                                                <?php echo wc_price($seat_price_child); ?>/
                                                <small><?php echo wbbm_get_option('wbbm_ticket_text', 'wbbm_label_setting_sec', __('Ticket', 'bus-booking-manager')); ?></small>
                                            </p>
                                            <?php mage_qty_box($seat_price_child, 'child_quantity', false); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ( ($seat_price_infant > 0) || ($is_price_zero_allow == 'on') ) : ?>
                                        <div class="mage_center_space">
                                            <p>
                                            <strong><?php echo wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec', __('Infant :', 'bus-booking-manager')); ?></strong>
                                                <?php echo wc_price($seat_price_infant); ?>/
                                                <small><?php echo wbbm_get_option('wbbm_ticket_text', 'wbbm_label_setting_sec', __('Ticket', 'bus-booking-manager')); ?></small>
                                            </p>
                                            <?php mage_qty_box($seat_price_infant, 'infant_quantity', false); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (($entire_bus_booking == 'on') && ($available_seat == $total_seat) && ($seat_price_entire > 0) && ($is_price_zero_allow == 'off') ) : ?>
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
                                            <strong><label for="mage_pickpoint"><?php _e('Select Pickup Area', 'bus-booking-manager'); echo ':'; ?></label></strong>
                                                <select name="mage_pickpoint" id="mage_pickpoint">
                                                    <option value=""><?php _e('Select your Pickup Area', 'bus-booking-manager'); ?></option>
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

                                <?php if (($available_seat == $total_seat) && ($seat_price_entire > 0) && ($is_price_zero_allow == 'off') ) : ?>
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
        <?php
        endif;
    }
}