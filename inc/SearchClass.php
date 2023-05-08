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
                    <div class="bus_filter" style="width:25%">
                        hhhhh
                    </div>
                    <div style="width:75%">
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
                        <?php if (isset($general_setting['wbbm_type_column_switch']) && $general_setting['wbbm_type_column_switch'] == 'on') { ?>
                            <span><?php echo wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec', __('Type', 'bus-booking-manager')); ?></span>
                        <?php  } ?>
                        <span><?php echo wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec', __('Fare', 'bus-booking-manager')); ?></span>

                        <?php if (isset($general_setting['wbbm_seat_column_switch']) && $general_setting['wbbm_seat_column_switch'] == 'on') { ?>
                            <span><?php echo wbbm_get_option('wbbm_seats_available_text', 'wbbm_label_setting_sec', __('Seat Available', 'bus-booking-manager')); ?></span>
                        <?php  } ?>

                    </div>
                </div>
            <?php } ?>
            <?php $this->mage_search_bus_list(false); ?>
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
                        <?php if (isset($general_setting['wbbm_type_column_switch']) && $general_setting['wbbm_type_column_switch'] == 'on') { ?>
                            <span><?php echo wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec', __('Type', 'bus-booking-manager')); ?></span>
                        <?php  } ?>

                        <span><?php echo wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec', __('Fare', 'bus-booking-manager')); ?></span>

                        <?php if (isset($general_setting['wbbm_seat_column_switch']) && $general_setting['wbbm_seat_column_switch'] == 'on') { ?>
                            <span><?php echo wbbm_get_option('wbbm_seats_available_text', 'wbbm_label_setting_sec', __('Seat Available', 'bus-booking-manager')); ?></span>
                        <?php  } ?>

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

                $bus_ids= [];
                $bus_types= [];
                $bus_features =[];
                $boarding_points =[];

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


                    $type_id = get_post_meta(get_the_ID(), 'wbbm_bus_category', true);
                    if($type_id != ''){
                        $type_array = get_term_by('term_id', $type_id, 'wbbm_bus_cat');
                        $type_name = $type_array->name;
                    } else {
                        $type_name = '';
                    }

                    $wbbm_features = get_post_meta(get_the_ID(), 'wbbm_features', true);

                    $wbbm_boarding_points = $bus_stops_times[0]['wbbm_bus_bp_stops_name'];


                    $show_operational_on_day = get_post_meta(get_the_ID(), 'show_operational_on_day', true) ?: 'no';
                    $bus_on_date = get_post_meta(get_the_ID(), 'wbtm_bus_on_date', true);
                    if($show_operational_on_day === 'yes' && $bus_on_date) {
                        $bus_on_dates = explode( ', ', $bus_on_date );
                        if( in_array( $j_date, $bus_on_dates ) ) {
                            $bus_ids[] =   get_the_ID();
                            if((!in_array($type_name, $bus_types))){
                                $bus_types[] = $type_name;
                            }

                             if ($wbbm_features) {
                                   foreach ($wbbm_features as $feature_id) {
                                       $bus_features['name'] = get_term($feature_id)->name;
                                       $bus_features['feature_icon'] = get_term_meta($feature_id, 'feature_icon', true);
                                   }
                             }

                            if((!in_array($wbbm_boarding_points,$boarding_points ))){
                                $boarding_points[] = $wbbm_boarding_points;
                            }





                            $has_bus = true;
                            $this->mage_search_item($return,$type_name,$wbbm_features);
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
                                $bus_ids[] =   get_the_ID();
                                if((!in_array($type_name, $bus_types,$wbbm_features))){
                                    $bus_types[] = $type_name;
                                }

                                if ($wbbm_features) {
                                    foreach ($wbbm_features as $feature_id) {
                                        $bus_features['name'] = get_term($feature_id)->name;
                                        $bus_features['feature_icon'] = get_term_meta($feature_id, 'feature_icon', true);
                                    }
                                }

                                if((!in_array($wbbm_boarding_points,$boarding_points ))){
                                    $boarding_points[] = $wbbm_boarding_points;
                                }

                                $has_bus = true;
                                $this->mage_search_item($return,$type_name,$wbbm_features);
                            }
                        } else {
                            $bus_ids[] =   get_the_ID();
                            if((!in_array($type_name, $bus_types))){
                                $bus_types[] = $type_name;
                            }

                            if ($wbbm_features) {
                                foreach ($wbbm_features as $feature_id) {
                                    $bus_features[$feature_id]['name'] = get_term($feature_id)->name;
                                    $bus_features[$feature_id]['feature_icon'] = get_term_meta($feature_id, 'feature_icon', true);
                                }
                            }

                            if((!in_array($wbbm_boarding_points,$boarding_points ))){
                                $boarding_points[] = $wbbm_boarding_points;
                            }

                            $has_bus = true;
                            $this->mage_search_item($return,$type_name,$wbbm_features);
                        }

                    }

                }


                ?>

                <div class="temp_filter" style="display: none">
                    <h2>Filters</h2>
                    <h2>Filters By Operator</h2>
                    <ul>
                    <?php for($i=0;$i<count($bus_ids);$i++){ ?>
                        <li><input type="checkbox">   <?php echo get_the_title($bus_ids[$i]) ?></li>
                    <?php } ?>
                    </ul>

                    <h2>Bus Type</h2>
                    <ul>
                        <?php for($i=0;$i<count($bus_types);$i++){ ?>
                            <li><input type="checkbox">  <?php echo $bus_types[$i] ?></li>
                        <?php } ?>
                    </ul>

                    <h2>Boarding Points</h2>
                    <ul>
                        <?php for($i=0;$i<count($boarding_points);$i++){ ?>
                            <li><input type="checkbox">  <?php echo $boarding_points[$i] ?></li>
                        <?php } ?>
                    </ul>




                    <h2>Bus Features</h2>
                    <ul>
                        <?php foreach($bus_features as $bus_feature){ ?>
                            <li><input type="checkbox"> </span> <?php echo $bus_feature['name'] ?></li>
                        <?php } ?>
                    </ul>

                </div>

                <?php


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


    function mage_search_item($return,$type_name,$wbbm_features)
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




        $available_seat = wbbm_intermidiate_available_seat($_GET[$boarding_var], $_GET[$dropping_var], wbbm_convert_date_to_php(mage_get_isset($date_var)));


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
        $pickpoints = get_post_meta($id, 'wbbm_selected_pickpoint_name_' . $boarding_point_slug, true);

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
            if ( $mage_bus_search_theme == 'minimal' ){
                ?>
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

                <?php
            }else{
                require(plugin_dir_path( dirname( __FILE__ ) )  . 'templates/search_result_list_theme_default.php');
            }
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

                        $start_stops = get_post_meta(get_the_id(),'wbbm_bus_prices',true);
                        $start_stops = array_values(
                            array_reduce($start_stops, function($r, $a){
                                if (!isset($r[$a['wbbm_bus_bp_price_stop']])) $r[$a['wbbm_bus_bp_price_stop']] = $a;
                                return $r;
                            }, [])
                        );

                        echo '<div class="mage_input_select_list" '; if($search_form_dropdown_b_color){ echo 'style="background-color:'.$search_form_dropdown_b_color.'"'; } echo '><ul>';


                        if($start_stops) {
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

