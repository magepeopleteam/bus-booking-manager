<?php
if (!defined('ABSPATH')) exit;  // if direct access

class SearchClass extends CommonClass
{
    public function __construct()
    {

    }

    function mage_search_page_horizontal(){
        $the_page = sanitize_post( $GLOBALS['wp_the_query']->get_queried_object() );
        $target = $the_page->post_name;
        $this->mage_search_form_horizontal(false,$target);
        if (isset($_GET['bus_start_route']) && ($_GET['bus_end_route']) && ($_GET['j_date'])) {
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

    function mage_search_list()
    {
        global $mage_bus_search_theme;
        $cpt_label = wbbm_get_option('wbbm_cpt_label', 'wbbm_general_setting_sec', 'Bus');
        $route_title_bg_color = wbbm_get_option('wbbm_search_route_title_b_color', 'wbbm_style_setting_sec');
        $route_title_bg_color = $route_title_bg_color ? $route_title_bg_color : '#727272';
        $route_title_color = wbbm_get_option('wbbm_search_route_title_color', 'wbbm_style_setting_sec');
        $route_title_color = $route_title_color ? $route_title_color : '#fff';
        $search_list_header_b_color = wbbm_get_option('wbbm_search_list_header_b_color', 'wbbm_style_setting_sec');
        $general_setting = get_option('wbbm_general_setting_sec') ? maybe_unserialize(get_option('wbbm_general_setting_sec')) : array();

        $wbbm_type_column_switch = array(
            'wbbm_type_column_switch' => 'on'
        );
        if (! array_key_exists('wbbm_type_column_switch',$general_setting)) {
            $marged_arr = array_merge($general_setting, $wbbm_type_column_switch);
            update_option('wbbm_general_setting_sec', $marged_arr);
        }

        $wbbm_seat_column_switch = array(
            'wbbm_seat_column_switch' => 'on'
        );
        if (! array_key_exists('wbbm_seat_column_switch',$general_setting)) {
            $marged_arr2 = array_merge($general_setting, $wbbm_seat_column_switch);
            update_option('wbbm_general_setting_sec', $marged_arr2);
        }

        $general_setting = get_option('wbbm_general_setting_sec') ? maybe_unserialize(get_option('wbbm_general_setting_sec')) : array();

        ?>
        <div class="mage_route_title" style="background-color:<?php echo $route_title_bg_color; ?>;color:<?php echo $route_title_color; ?>">
            <div>
                <strong><?php echo wbbm_get_option('wbbm_route_text', 'wbbm_label_setting_sec', __('Route', 'bus-booking-manager')); echo ':'; ?></strong>
                <?php echo mage_get_isset('bus_start_route'); ?>
                <span class="fa fa-long-arrow-right"></span>
                <?php echo mage_get_isset('bus_end_route'); ?>
                <strong><?php echo ' | ';
                    echo wbbm_get_option('wbbm_date_text', 'wbbm_label_setting_sec', __('Date', 'bus-booking-manager'));
                    echo ':'; ?></strong>
                <?php echo mage_wp_date(mage_get_isset('j_date')); ?>
            </div>
        </div>
        <div class="mage-search-res-wrapper">
            <?php do_action('woocommerce_before_single_product'); ?>
            <?php if ($mage_bus_search_theme == 'minimal') { ?>
                <div class="mage-search-res-header" style="background-color:<?php echo ($search_list_header_b_color != '' ? $search_list_header_b_color : '#EA2330'); ?>">

                    <div class="mage-search-res-header--img">
                        <span><?php echo wbbm_get_option('wbbm_bus_image_text', 'wbbm_label_setting_sec', __('Bus Image', 'bus-booking-manager')); ?></span>
                    </div>
                    <div class="mage-search-res-header--left">
                        <span><?php echo wbbm_get_option('wbbm_bus_name_text', 'wbbm_label_setting_sec', __('Bus Name', 'bus-booking-manager')); ?></span>
                        <span><?php echo wbbm_get_option('wbbm_schedule_text', 'wbbm_label_setting_sec', __('Schedule', 'bus-booking-manager')); ?></span>
                    </div>
                    <div class="mage-search-res-header--right">
                        <?php if ((isset($general_setting['wbbm_type_column_switch']) && $general_setting['wbbm_type_column_switch'] == 'on') || !isset($general_setting['wbbm_type_column_switch'])) { ?>
                            <span><?php echo wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec', __('Type', 'bus-booking-manager')); ?></span>
                        <?php  } ?>
                        <span><?php echo wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec', __('Fare', 'bus-booking-manager')); ?></span>

                        <?php if ((isset($general_setting['wbbm_seat_column_switch']) && $general_setting['wbbm_seat_column_switch'] == 'on') || !isset($general_setting['wbbm_seat_column_switch'])) { ?>
                            <span><?php echo wbbm_get_option('wbbm_seats_available_text', 'wbbm_label_setting_sec', __('Seat Available', 'bus-booking-manager')); ?></span>
                        <?php  } ?>

                        <span><?php echo wbbm_get_option('wbbm_view_text', 'wbbm_label_setting_sec', __('View', 'bus-booking-manager')); ?></span>
                    </div>
                </div>
            <?php } ?>
            <?php $this->mage_search_bus_list(false); ?>
            <!-- <div class="mage-search-res-wrapper--footer"></div> -->
        </div>

        <?php if (isset($_GET['r_date']) && $_GET['r_date'] != '') { ?>
        <div class="mage_route_title return_title" style="background-color:<?php echo $route_title_bg_color; ?>">
            <div>
                <strong><?php echo wbbm_get_option('wbbm_route_text', 'wbbm_label_setting_sec', __('Route', 'bus-booking-manager'));
                    echo ':'; ?></strong>
                <?php echo mage_get_isset('bus_end_route'); ?>
                <span class="fa fa-long-arrow-right"></span>
                <?php echo mage_get_isset('bus_start_route'); ?>
                <strong><?php echo ' | ';
                    echo wbbm_get_option('wbbm_date_text', 'wbbm_label_setting_sec', __('Date', 'bus-booking-manager'));
                    echo ':'; ?></strong>
                <?php echo mage_wp_date(mage_get_isset('r_date')); ?>
            </div>
        </div>
        <div class="mage-search-res-wrapper">
            <?php if ($mage_bus_search_theme == 'minimal') { ?>
                <div class="mage-search-res-header">
                    <div class="mage-search-res-header--img">
                        <span><?php echo wbbm_get_option('wbbm_bus_image_text', 'wbbm_label_setting_sec', __('Bus Image', 'bus-booking-manager')); ?></span>
                    </div>
                    <div class="mage-search-res-header--left">
                        <span><?php echo wbbm_get_option('wbbm_bus_name_text', 'wbbm_label_setting_sec', __('Bus Name', 'bus-booking-manager')); ?></span>
                        <span><?php echo wbbm_get_option('wbbm_schedule_text', 'wbbm_label_setting_sec', __('Schedule', 'bus-booking-manager')); ?></span>
                    </div>
                    <div class="mage-search-res-header--right">
                        <?php if (isset($general_setting['wbbm_type_column_switch']) && $general_setting['wbbm_type_column_switch'] == 'on' || isset($general_setting['wbbm_type_column_switch'])) { ?>
                            <span><?php echo wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec', __('Type', 'bus-booking-manager')); ?></span>
                        <?php  } ?>

                        <span><?php echo wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec', __('Fare', 'bus-booking-manager')); ?></span>

                        <?php if (isset($general_setting['wbbm_seat_column_switch']) && $general_setting['wbbm_seat_column_switch'] == 'on') { ?>
                            <span><?php echo wbbm_get_option('wbbm_seats_available_text', 'wbbm_label_setting_sec', __('Seat Available', 'bus-booking-manager')); ?></span>
                        <?php  } ?>

                        <span><?php echo wbbm_get_option('wbbm_view_text', 'wbbm_label_setting_sec', __('View', 'bus-booking-manager')); ?></span>
                    </div>
                </div>
            <?php } ?>
            <?php $this->mage_search_bus_list(true); ?>
        </div>
        <div class="mage_mini_cart mage_hidden">
            <p><?php echo wbbm_get_option('wbbm_total_text', 'wbbm_label_setting_sec', __('Total', 'bus-booking-manager')); ?></p>
            <p class="mage_total"><strong><span><?php echo wc_price(0); ?></span></strong></p>
        </div>
    <?php }

        do_action('wbbm_prevent_form_resubmission');
    }

    function mage_search_bus_list($return){
        do_action( 'woocommerce_before_single_product' );
        if (isset($_GET['bus_start_route']) && isset($_GET['bus_end_route']) && (isset($_GET['j_date']) || isset($_GET['r_date']))) {
            $c_time = current_time( 'timestamp' );
            $start = $return?'bus_end_route':'bus_start_route';
            $end = $return?'bus_start_route':'bus_end_route';
            $loop = new WP_Query(mage_bus_list_query($start,$end));
            $has_bus = false;
            if($loop->post_count == 0){
                ?>
                <div class='wbbm_error' style='text-align:center;padding: 10px;color: red;'>
                    <span><?php echo __('Sorry, No','bus-booking-manager').' '.wbbm_get_option( 'wbbm_cpt_label', 'wbbm_general_setting_sec', 'Bus').' '.__('Found','bus-booking-manager'); ?></span>
                </div>
                <?php
            } else {

                $j_date = $return ? $_GET['r_date'] : $_GET['j_date'];
                $j_date = mage_wp_date($j_date, 'Y-m-d');

                while ($loop->have_posts()) {
                    $loop->the_post();
                    $bus_stops_times = get_post_meta(get_the_ID(), 'wbbm_bus_bp_stops', true);
                    $start_time = '';
                    foreach($bus_stops_times as $stop) {
                        if($stop['wbbm_bus_bp_stops_name'] == $_GET[$start]) {
                            $start_time = isset($stop['wbbm_bus_bp_start_time'])?$stop['wbbm_bus_bp_start_time']:'';
                        }
                    }

                    // Buffer time
                    if(!wbbm_buffer_time_calculation($start_time, $j_date)) continue;
                    // Buffer time END

                    $start_time = wbbm_time_24_to_12($start_time); // convert time

                    $is_on_date = false;
                    $bus_on_dates = array();
                    $show_operational_on_day = get_post_meta(get_the_ID(), 'show_operational_on_day', true) ?: 'no';
                    $bus_on_date = get_post_meta(get_the_ID(), 'wbtm_bus_on_date', true);
                    if($show_operational_on_day === 'yes' && $bus_on_date) {
                        $bus_on_dates = explode( ', ', $bus_on_date );
                        if( in_array( $j_date, $bus_on_dates ) ) {
                            $has_bus = true;
                            $this->mage_search_item($return);
                        }
                    } else {
                        // Offday schedule check
                        $bus_offday_schedules = get_post_meta(get_the_ID(), 'wbtm_offday_schedule', true);

                        $offday_current_bus = false;
                        if(!empty($bus_offday_schedules)) {
                            $s_datetime = new DateTime( $j_date.' '.$start_time );

                            foreach($bus_offday_schedules as $item) {

                                $c_iterate_date_from = wbbm_convert_date_to_php($item['from_date']);
                                $c_iterate_datetime_from = new DateTime( $c_iterate_date_from.' '.$item['from_time'] );

                                $c_iterate_date_to = wbbm_convert_date_to_php($item['to_date']);
                                $c_iterate_datetime_to = new DateTime( $c_iterate_date_to.' '.$item['to_time'] );

                                if( $s_datetime >= $c_iterate_datetime_from && $s_datetime <= $c_iterate_datetime_to ) {
                                    $offday_current_bus = true;
                                    break;
                                }
                            }
                        }

                        // Check Offday and date
                        $show_off_day = get_post_meta(get_the_ID(), 'show_off_day', true) ?: 'no';
                        if($show_off_day === 'yes') {
                            if( (!$offday_current_bus && !mage_off_day_check($return)) ) {
                                $has_bus = true;
                                $this->mage_search_item($return);
                            }
                        } else {
                            $has_bus = true;
                            $this->mage_search_item($return);
                        }

                    }

                }

                if( !$has_bus ) { // Bus available
                    ?>
                    <div class='wbbm_error' style='text-align:center;padding: 10px;color: red;'>
                        <span><?php echo __('Sorry, No','bus-booking-manager').' '.wbbm_get_option( 'wbbm_cpt_label', 'wbbm_general_setting_sec', 'Bus').' '.__('Found','bus-booking-manager'); ?></span>
                    </div>
                    <?php
                }
            }
        }
    }


    function mage_search_item($return)
    {
        global $mage_bus_search_theme;
        $id = get_the_id();
        $search_date = (isset($_GET['j_date']) ? $_GET['j_date'] : '');
        $current_date = date('Y-m-d');


        $boarding_time = boarding_dropping_time(false, $return);
        $dropping_time = boarding_dropping_time(true, $return);

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


        $available_seat = wbbm_intermidiate_available_seat($_GET[$boarding_var], $_GET[$dropping_var], wbbm_convert_date_to_php(mage_get_isset($date_var)));

        //echo "<pre>";print_r($available_seat);echo "</pre>";exit;

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
                                <?php if($wbbm_features){ ?>
                                <p class="wbbm-feature-icon">
                                    <?php foreach ($wbbm_features as $feature_id){ ?>
                                        <span class="customCheckbox"><span title="<?php echo get_term($feature_id)->name ?>" class="mR_xs <?php echo get_term_meta($feature_id, 'feature_icon', true) ?>"></span></span>
                                    <?php } ?>
                                </p>
                                <?php } ?>
                            </div>
                            <div>
                                <?php echo '<p class="mage-bus-stopage"><span class="dashicons dashicons-location"></span> '. wbbm_get_option('wbbm_from_text', 'wbbm_label_setting_sec', __('From: ', 'bus-booking-manager')) .' '. $boarding . ' ( ' . get_wbbm_datetime($boarding_time, 'time') . ' )</p>'; ?>
                                <?php echo '<p class="mage-bus-stopage"><span class="dashicons dashicons-location"></span> '. wbbm_get_option('wbbm_to_text', 'wbbm_label_setting_sec', __('To: ', 'bus-booking-manager')).' '. $dropping . ' ( ' . get_wbbm_datetime($dropping_time, 'time') . ' )</p>'; ?>
                            </div>
                        </div>
                        <div class="mage-search-res-header--right">
                            <?php if((isset($general_setting['wbbm_type_column_switch']) && $general_setting['wbbm_type_column_switch'] == 'on') || !isset($general_setting['wbbm_type_column_switch'])){ ?>
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
                                        <?php if($type_name) : ?>
                                            <p>
                                                <strong><?php echo wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec', __('Type', 'bus-booking-manager')); ?>
                                                </strong>:
                                                <?php echo $type_name; ?>
                                            </p>
                                        <?php endif; ?>
                                        <p>
                                            <strong><?php echo wbbm_get_option('wbbm_boarding_points_text', 'wbbm_label_setting_sec', __('Boarding', 'bus-booking-manager')); ?>
                                            </strong>:
                                            <?php echo $boarding; ?>
                                            <strong>(<?php echo get_wbbm_datetime($boarding_time, 'time'); ?>)</strong>

                                            <?php 
                                                $boarding_desc = (get_term_by('name', $boarding, 'wbbm_bus_stops') ? get_term_by('name', $boarding, 'wbbm_bus_stops')->description : '');
                                                if($boarding_desc) {
                                                    echo '<span class="wbbm_dropoff-desc">'.$boarding_desc.'</span>';
                                                }
                                            ?> 
                                        </p>
                                        <p>
                                            <strong><?php echo wbbm_get_option('wbbm_dropping_points_text', 'wbbm_label_setting_sec', __('Dropping', 'bus-booking-manager')); ?>
                                            </strong>:
                                            <?php echo $dropping; ?>
                                            <strong>(<?php echo get_wbbm_datetime($dropping_time, 'time'); ?>)</strong>
                                            
                                            <?php 
                                                $dropoff_desc = (get_term_by('name', $dropping, 'wbbm_bus_stops') ? get_term_by('name', $dropping, 'wbbm_bus_stops')->description : '');
                                                if($dropoff_desc) {
                                                    echo '<span class="wbbm_dropoff-desc">'.$dropoff_desc.'</span>';
                                                }
                                            ?>                                           
                                        </p>
                                        <p>
                                            <strong><?php echo wbbm_get_option('wbbm_date_text', 'wbbm_label_setting_sec', __('Date', 'bus-booking-manager')); ?>
                                            </strong>:
                                            <?php echo ($return)?mage_wp_date(mage_get_isset('r_date')):mage_wp_date(mage_get_isset('j_date')) ?>
                                        </p>
                                        <p>
                                            <strong><?php echo wbbm_get_option('wbbm_starting_text', 'wbbm_label_setting_sec', __('Start Time', 'bus-booking-manager')); ?>
                                            </strong>:
                                            <?php echo get_wbbm_datetime($boarding_time, 'time'); ?>
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

                                        <input type="hidden" name="available_quantity" value="<?php echo $available_seat?>">
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

                                        <?php if (($entire_bus_booking == 'on') && ($available_seat == $total_seat) && ($seat_price_entire > 0) ) : ?>
                                            <div class="mage_center_space">
                                                <p>
                                                    <strong><?php echo wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') : _e('Entire Bus', 'bus-booking-manager'); echo ':'; ?></strong>
                                                    <?php echo wc_price($seat_price_entire); ?>
                                                </p>
                                                <?php echo wbbm_entire_switch($seat_price_entire, 'entire_quantity', false); ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($pickpoints) && $is_enable_pickpoint == 'yes') : ?>
                                            <div class="mage_center_space">
                                                <div class="mage-form-field mage-form-pickpoint-field">
                                                    <strong><label for="mage_pickpoint"><?php _e('Select Pickup Area', 'bus-booking-manager'); echo ':'; ?></label></strong>
                                                    <select name="mage_pickpoint" id="mage_pickpoint">
                                                        <option value=""><?php _e('Select your Pickup Area', 'bus-booking-manager'); ?></option>
                                                        <?php
                                                        foreach ($pickpoints as $pickpoint) {
                                                            $time_html = $pickpoint["time"] ? ' ('.get_wbbm_datetime($pickpoint["time"], 'time').')' : '';
                                                            $time_value = $pickpoint["time"] ? '-'. get_wbbm_datetime($pickpoint["time"], 'time') : '';
                                                            $pick_desc = (get_term_by('name', $pickpoint["pickpoint"], 'wbbm_bus_pickpoint') ? get_term_by('name', $pickpoint["pickpoint"], 'wbbm_bus_pickpoint')->description : '');
                                                            echo '<option value="'. $pickpoint["pickpoint"] . $time_value .'">'. ucfirst($pickpoint["pickpoint"]) . $time_html .'</option>';
                                                            echo ($pick_desc ? '<option disabled>&nbsp;&nbsp; '.$pick_desc.'</option>' : '');
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
                                    <?php if($type_name) : ?>
                                        <p>
                                            <strong><?php echo wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec', __('Type :', 'bus-booking-manager')); ?></strong>
                                            <?php echo $type_name; ?>
                                        </p>
                                    <?php endif; ?>
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

                                    <?php if (!empty($pickpoints) && $is_enable_pickpoint == 'yes') : ?>
                                        <div class="mage_center_space">
                                            <div class="mage-form-field mage-form-pickpoint-field">
                                                <label for="mage_pickpoint"><?php echo wbbm_get_option('wbbm_pickuppoint_area_text', 'wbbm_label_setting_sec', __('Select Pickup Area', 'bus-booking-manager')); echo ':'; ?></label>
                                                <select name="mage_pickpoint" class="mage_pickpoint">
                                                    <option value=""><?php echo wbbm_get_option('wbbm_pickuppoint_area_text', 'wbbm_label_setting_sec', __('Select Pickup Area', 'bus-booking-manager')); ?></option>
                                                    <?php
                                                    foreach ($pickpoints as $pickpoint) {
                                                        $time_html = $pickpoint["time"] ? ' ('.get_wbbm_datetime($pickpoint["time"], 'time').')' : '';
                                                        $time_value = $pickpoint["time"] ? '-'. get_wbbm_datetime($pickpoint["time"], 'time') : '';
                                                        $pick_desc = (get_term_by('name', $pickpoint["pickpoint"], 'wbbm_bus_pickpoint') ? get_term_by('name', $pickpoint["pickpoint"], 'wbbm_bus_pickpoint')->description : '');
                                                        echo '<option value="'. $pickpoint["pickpoint"] . $time_value .'">'. ucfirst($pickpoint["pickpoint"]) . $time_html .'</option>';
                                                        echo ($pick_desc ? '<option disabled>&nbsp;&nbsp; '.$pick_desc.'</option>' : '');
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

    function mage_search_form_vertical($target=''){
        ?>
        <div class="mage_container">
            <div class="mage_search_box_small">
                <h2>
                    <?php echo wbbm_get_option('wbbm_buy_ticket_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_buy_ticket_text', 'wbbm_label_setting_sec') : _e('BUY TICKET', 'bus-booking-manager'); ?>
                </h2>
                <?php do_action('mage_search_from_only',false,$target); ?>
            </div>
        </div>
        <?php
    }


    function mage_search_form_horizontal($single_bus,$target='')
    {
        $search_form_b_color = wbbm_get_option('wbbm_search_form_b_color', 'wbbm_style_setting_sec');
        $wbbm_buy_ticket_text = wbbm_get_option('wbbm_buy_ticket_text', 'wbbm_label_setting_sec',__('Buy Ticket', 'bus-booking-manager'));
        ?>
        <div class="mage_container">
            <div class="search_form_horizontal" style="background-color:<?php echo ($search_form_b_color != '' ? $search_form_b_color : '#b30c3b12'); ?>">
                <?php if($wbbm_buy_ticket_text){ ?>
                    <h2><?php echo  $wbbm_buy_ticket_text; ?></h2>
                <?php } ?>
                <?php $this->search_from_only($single_bus,$target); ?>
            </div>
        </div>
        <?php
    }

    function mage_search_page_vertical()
    {
        $target = '';

        if (isset($_GET['bus_start_route']) && ($_GET['bus_end_route']) && ($_GET['j_date'])) {

            ?>
            <div class="mage_container">
                <div class="mage_row">
                    <div class="mage_search_box_sidebar">
                        <div class="mage_sidebar_search_form">
                            <h2><?php echo wbbm_get_option('wbbm_buy_ticket_text', 'wbbm_label_setting_sec',__('BUY TICKET', 'bus-booking-manager')); ?></h2>
                            <?php do_action('mage_search_from_only',$target); ?>
                        </div>
                    </div>
                    <div class="mage_search_area">
                        <?php $this->mage_search_list(); ?>
                    </div>
                </div>
            </div>
            <?php
        } else {
            $this->mage_search_form_vertical($target);
        }
    }

    function search_from_only($single_bus,$target) {
        $search_form_dropdown_b_color = wbbm_get_option('wbbm_search_form_dropdown_b_color', 'wbbm_style_setting_sec');

        $search_form_dropdown_text_color = wbbm_get_option('wbbm_search_form_dropdown_t_color', 'wbbm_style_setting_sec');
        $search_form_dropdown_text_color = $search_form_dropdown_text_color ? $search_form_dropdown_text_color : '';

        $wbbm_bus_prices = get_post_meta(get_the_id(),'wbbm_bus_prices',true);

        //echo '<pre>';print_r($wbbm_bus_prices);echo '<pre>';
        ?>




        <form action="<?php echo $single_bus ? '' : get_site_url() .'/'. $target.'/'; ?>" method="get" class="mage_form">
            <?php do_action('active_date',$single_bus,get_the_ID()) ?>
            <div class="mage_form_list">
                <label for="bus_start_route">
                    <span class="fa fa-map-marker"></span>
                    <?php echo wbbm_get_option('wbbm_from_text', 'wbbm_label_setting_sec',__('From :', 'bus-booking-manager')); ?>
                </label>

                <div class="mage_input_select mage_bus_boarding_point">
                    <div class="route-input-wrap"><input id="bus_start_route" type="text" class="mage_form_control" name="bus_start_route" value="<?php echo mage_get_isset('bus_start_route'); ?>" placeholder="<?php _e('Please Select', 'bus-booking-manager'); ?>" autocomplete="off" required/></div>
                    <?php
                    if($single_bus){

                        $start_stops = get_post_meta(get_the_id(),'wbbm_bus_prices',true) ? get_post_meta(get_the_id(), 'wbbm_bus_prices', true) : array();
                        if($start_stops) {
                            $start_stops = array_values(
                                array_reduce($start_stops, function ($r, $a) {
                                    if (!isset($r[$a['wbbm_bus_bp_price_stop']])) $r[$a['wbbm_bus_bp_price_stop']] = $a;
                                    return $r;
                                }, [])
                            );

                            echo '<div class="mage_input_select_list" ';
                            if ($search_form_dropdown_b_color) {
                                echo 'style="background-color:' . $search_form_dropdown_b_color . '"';
                            }
                            echo '><ul>';


                            if ($start_stops) {
                                foreach ($start_stops as $_start_stops) {

                                    echo '<li ';
                                    if ($search_form_dropdown_text_color) {
                                        echo 'style="color:' . $search_form_dropdown_text_color . '"';
                                    }
                                    echo ' data-route="' . $_start_stops['wbbm_bus_bp_price_stop'] . '">
 
                                <span class="fa fa-map-marker"></span>' . $_start_stops['wbbm_bus_bp_price_stop'] . '</li>';
                                }
                            }

                            echo '</ul></div>';
                        }
                    }else {
                        mage_route_list();
                    }
                    ?>
                </div>

            </div>
            <div class="mage_form_list">
                <label for="bus_end_route">
                    <span class="fa fa-map-marker"></span>
                    <span id="wbtm_show_msg"></span>
                    <?php echo wbbm_get_option('wbbm_to_text', 'wbbm_label_setting_sec',__('To :', 'bus-booking-manager')); ?>
                </label>
                <div class="mage_input_select mage_bus_dropping_point">
                    <div class="route-input-wrap"><input id="bus_end_route" type="text" class="mage_form_control" name="bus_end_route" value="<?php echo mage_get_isset('bus_end_route'); ?>" placeholder="<?php _e('Please Select', 'bus-booking-manager'); ?>" autocomplete="off" required/></div>
                    <?php
                    if($single_bus){
                        $end_stops = get_post_meta(get_the_id(),'wbbm_bus_prices',true);

                        $end_stops = array_values(
                            array_reduce($end_stops, function($r, $a){
                                if (!isset($r[$a['wbbm_bus_dp_price_stop']])) $r[$a['wbbm_bus_dp_price_stop']] = $a;
                                return $r;
                            }, [])
                        );


                        echo '<div class="mage_input_select_list_static"><ul class="">';
                        foreach ($end_stops as $_end_stops) {
                            echo '<li data-route="' . $_end_stops['wbbm_bus_dp_price_stop'] . '"><span class="fa fa-map-marker"></span>' . $_end_stops['wbbm_bus_dp_price_stop'] . '</li>';
                        }
                        echo '</ul></div>';
                    }else {
                        mage_route_list(true);
                    }
                    ?>
                </div>
            </div>
            <div class="mage_form_list">
                <label for="j_date">
                    <span class="fa fa-calendar"></span>
                    <?php echo wbbm_get_option('wbbm_date_of_journey_text', 'wbbm_label_setting_sec',__('Date of Journey :', 'bus-booking-manager')); ?>
                </label>
                <input type="text" class="mage_form_control" id="j_date" readonly name="j_date" value="<?php echo mage_get_isset('j_date'); ?>" placeholder="<?php echo wbbm_convert_datepicker_dateformat(); ?>" autocomplete="off" required>
            </div>
            <?php if (!$single_bus) {
                $return = (mage_get_isset('bus-r') == 'oneway') ? false : true; ?>
                <div class="mage_form_list mage_return_date <?php echo $return ? '' : 'mage_hidden' ?>">
                    <label for="r_date">
                        <span class="fa fa-calendar"></span>
                        <?php echo wbbm_get_option('wbbm_return_date_text', 'wbbm_label_setting_sec',__('Return Date (Optional):', 'bus-booking-manager')); ?>
                    </label>
                    <input type="text" class="mage_form_control" id="r_date" readonly name="r_date" value="<?php echo mage_get_isset('r_date'); ?>" autocomplete="off" placeholder="<?php echo wbbm_convert_datepicker_dateformat(); ?>">
                </div>
            <?php } ?>
            <div class="mage_form_list">
                <div class="mage_form_radio">
                    <?php if (!$single_bus) { ?>
                        <label for="one_way">
                            <input type="radio" name="bus-r" value='oneway' id="one_way" <?php echo $return ? '' : 'checked' ?> />
                            <?php echo wbbm_get_option('wbbm_one_way_text', 'wbbm_label_setting_sec',__('One Way', 'bus-booking-manager')); ?>
                        </label>
                        <label for="return">
                            <input type="radio" name="bus-r" value='return' id="return" <?php echo $return ? 'checked' : '' ?>/>
                            <?php echo wbbm_get_option('wbbm_return_text', 'wbbm_label_setting_sec',__('Return', 'bus-booking-manager')); ?>
                        </label>
                    <?php } else {
                        echo '<label>&nbsp;</label>';
                    } ?>
                </div>
                <div class="mage_form_search">
                    <button type="submit" class="mage_button">
                        <span class="fa fa-search"></span>
                        <?php echo wbbm_get_option('wbbm_search_buses_text', 'wbbm_label_setting_sec', __('Search', 'bus-booking-manager')); ?>
                    </button>
                </div>
            </div>
        </form>

        <?php



        if (isset($_GET['bus_start_route']) && ($_GET['bus_end_route']) && ($_GET['j_date'])) {
            do_action('mage_next_date',false, true, $target);
        }
    }

    function wbbm_prevent_form_resubmission_fun()
    {
        ?>
        <script>
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        </script>
        <?php
    }




}

