<?php
if (!defined('ABSPATH')) exit;  // if direct access

class SearchClass extends CommonClass
{
    public function __construct()
    {

    }

    function mage_search_page_horizontal($boarding,$dropping,$journy_date,$return_date){
        $the_page = sanitize_post( $GLOBALS['wp_the_query']->get_queried_object() );
        $target = $the_page->post_name;
        $this->mage_search_form_horizontal(false,$target,$boarding,$dropping,$journy_date,$return_date);
        if ($boarding && $dropping && $journy_date) {
            ?>
            <div class="mage_container">
                <div class="mage_row">
                    <div class="bus_filter" style="width:25%">
                        hhhhh
                    </div>
                    <div style="width:75%">
                        <?php $this->mage_search_list($boarding,$dropping,$journy_date,$return_date); ?>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    function mage_search_list($boarding,$dropping,$journy_date,$return_date)
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
                <?php echo $boarding; ?>
                <span class="fa fa-long-arrow-right"></span>
                <?php echo $dropping; ?>
                <strong>
                    <?php echo ' | '; echo wbbm_get_option('wbbm_date_text', 'wbbm_label_setting_sec', __('Date', 'bus-booking-manager')); echo ':'; ?>
                </strong>
                <?php echo mage_wp_date($journy_date); ?>
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
            <?php $this->mage_search_bus_list(false,$boarding,$dropping,$journy_date,$return_date); ?>
        </div>

        <?php if ($return_date) { ?>
        <div class="mage_route_title return_title" style="background-color:<?php echo $route_title_bg_color; ?>">
            <div>
                <strong><?php echo wbbm_get_option('wbbm_route_text', 'wbbm_label_setting_sec', __('Route', 'bus-booking-manager')); echo ':'; ?></strong>
                <?php echo $dropping; ?>
                <span class="fa fa-long-arrow-right"></span>
                <?php echo $boarding; ?>
                <strong>
                    <?php echo ' | '; echo wbbm_get_option('wbbm_date_text', 'wbbm_label_setting_sec', __('Date', 'bus-booking-manager')); echo ':'; ?>
                </strong>
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

    function mage_search_bus_list($return,$boarding,$dropping,$journy_date,$return_date){
        do_action( 'woocommerce_before_single_product' );





        if ($boarding && $dropping && ($journy_date || $return_date)) {
            $c_time = current_time( 'timestamp' );
            $start = $return?$dropping:$boarding;
            $end = $return?$boarding:$dropping;
            $date = $return?$return_date:$journy_date;
            $loop = new WP_Query(mage_bus_list_query($start,$end));

            //echo '<pre>';print_r($loop);echo '<pre>';

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


                    $seat_price_adult = mage_seat_price(get_the_ID(), $boarding, $dropping, 'adult');
                    $is_price_zero_allow = get_post_meta(get_the_ID(), 'wbbm_price_zero_allow', true);


                    $show_operational_on_day = get_post_meta(get_the_ID(), 'show_operational_on_day', true) ?: 'no';
                    $bus_on_date = get_post_meta(get_the_ID(), 'wbtm_bus_on_date', true);
                    if($show_operational_on_day === 'yes' && $bus_on_date) {

                        $bus_on_dates = explode( ', ', $bus_on_date );
                        if( in_array( $j_date, $bus_on_dates ) ) {

                            if ($seat_price_adult > 0 || $is_price_zero_allow == 'on') {

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

                                $this->mage_search_item($return,$type_name,$wbbm_features,$boarding,$dropping,$date,$seat_price_adult,$is_price_zero_allow);

                            }


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

                                if ($seat_price_adult > 0 || $is_price_zero_allow == 'on') {

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

                                    $this->mage_search_item($return,$type_name,$wbbm_features,$boarding,$dropping,$date,$seat_price_adult,$is_price_zero_allow);
                                }
                            }
                        } else {
                            if ($seat_price_adult > 0 || $is_price_zero_allow == 'on') {

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

                                $this->mage_search_item($return,$type_name,$wbbm_features,$boarding,$dropping,$date,$seat_price_adult,$is_price_zero_allow);
                            }
                        }

                    }

                }


                ?>

                <div class="temp_filter" style="display: none">
                    <h2>Filters</h2>
                    <h2>Filters By Operator</h2>
                    <ul class="wbbm-bus-list">
                    <?php for($i=0;$i<count($bus_ids);$i++){ ?>
                        <li><input class="individual-bus" value="<?php echo $bus_ids[$i] ?>" type="checkbox">   <?php echo get_the_title($bus_ids[$i]) ?></li>
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


    function mage_search_item($return,$type_name,$wbbm_features,$boarding,$dropping,$date,$seat_price_adult,$is_price_zero_allow)
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



        $in_cart = mage_find_product_in_cart();


        $available_seat = wbbm_intermidiate_available_seat($boarding, $dropping, wbbm_convert_date_to_php($date));

        $seat_price_child = mage_seat_price($id, $boarding, $dropping, 'child');
        $seat_price_infant = mage_seat_price($id, $boarding, $dropping, 'infant');
        $seat_price_entire = mage_seat_price($id, $boarding, $dropping, 'entire');



        $boarding_point_slug = strtolower($boarding);
        $boarding_point_slug = preg_replace('/[^A-Za-z0-9-]/', '_', $boarding_point_slug);

        $coach_no = get_post_meta($id, 'wbbm_bus_no', true);
        $pickpoints = get_post_meta($id, 'wbbm_selected_pickpoint_name_' . $boarding_point_slug, true);

        if ($pickpoints) {
            $pickpoints = unserialize($pickpoints);
        }

        $is_sell_off = get_post_meta($id, 'wbbm_sell_off', true);

  
        $seat_available = get_post_meta($id, 'wbbm_seat_available', true);
        $total_seat = get_post_meta($id, 'wbbm_total_seat', true);

        $general_setting = get_option('wbbm_general_setting_sec') ? maybe_unserialize(get_option('wbbm_general_setting_sec')) : array();
        $search_form_result_b_color = wbbm_get_option('wbbm_search_form_result_b_color', 'wbbm_style_setting_sec');
        $search_list_header_text_color = wbbm_get_option('wbbm_search_list_header_text_color', 'wbbm_style_setting_sec');
        $entire_bus_booking = wbbm_get_option('wbbm_entire_bus_booking_switch', 'wbbm_general_setting_sec');

        $return_or_journey_date = ($date) ? mage_wp_date($date, 'Y-m-d') : date('Y-m-d');


        if ( $mage_bus_search_theme == 'minimal' ){
            require(plugin_dir_path( dirname( __FILE__ ) )  . 'templates/search_result_list_theme_minimal.php');
        }else{
            require(plugin_dir_path( dirname( __FILE__ ) )  . 'templates/search_result_list_theme_default.php');
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


    function mage_search_form_horizontal($single_bus,$target='',$boarding,$dropping,$journy_date,$return_date)
    {
        $search_form_b_color = wbbm_get_option('wbbm_search_form_b_color', 'wbbm_style_setting_sec');
        $wbbm_buy_ticket_text = wbbm_get_option('wbbm_buy_ticket_text', 'wbbm_label_setting_sec',__('Buy Ticket', 'bus-booking-manager'));
        ?>
        <div class="mage_container">
            <div class="search_form_horizontal" style="background-color:<?php echo ($search_form_b_color != '' ? $search_form_b_color : '#b30c3b12'); ?>">
                <?php if($wbbm_buy_ticket_text){ ?>
                    <h2><?php echo  $wbbm_buy_ticket_text; ?></h2>
                <?php } ?>
                <?php $this->search_from_only($single_bus,$target,$boarding,$dropping,$journy_date,$return_date); ?>
            </div>
        </div>
        <?php
    }

    function mage_search_page_vertical($boarding,$dropping,$journy_date,$return_date)
    {
        $target = '';

        if ($boarding && $dropping && $journy_date) {

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
                        <?php $this->mage_search_list($boarding,$dropping,$journy_date,$return_date); ?>
                    </div>
                </div>
            </div>
            <?php
        } else {
            $this->mage_search_form_vertical($target);
        }
    }

    function search_from_only($single_bus,$target,$boarding,$dropping,$journy_date,$return_date) {
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
                    <div class="route-input-wrap">
                        <input data-bus_start_route="<?php echo $boarding; ?>" id="bus_start_route" type="text" class="mage_form_control" name="bus_start_route" value="<?php echo $boarding; ?>" placeholder="<?php _e('Please Select', 'bus-booking-manager'); ?>" autocomplete="off" required/>
                    </div>
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
                    <div class="route-input-wrap">
                        <input data-bus_end_route="<?php echo $dropping; ?>" id="bus_end_route" type="text" class="mage_form_control" name="bus_end_route" value="<?php echo $dropping; ?>" placeholder="<?php _e('Please Select', 'bus-booking-manager'); ?>" autocomplete="off" required/>
                    </div>
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
                <input data-j_date="<?php echo $journy_date; ?>" type="text" class="mage_form_control" id="j_date" readonly name="j_date" value="<?php echo $journy_date; ?>" placeholder="<?php echo wbbm_convert_datepicker_dateformat(); ?>" autocomplete="off" required>
            </div>

            <?php if (!$single_bus) {
                $return = (mage_get_isset('bus-r') == 'oneway') ? false : true; ?>
                <div class="mage_form_list mage_return_date <?php echo $return ? '' : 'mage_hidden' ?>">
                    <label for="r_date">
                        <span class="fa fa-calendar"></span>
                        <?php echo wbbm_get_option('wbbm_return_date_text', 'wbbm_label_setting_sec',__('Return Date (Optional):', 'bus-booking-manager')); ?>
                    </label>
                    <input data-r_date="<?php echo $return_date; ?>" type="text" class="mage_form_control" id="r_date" readonly name="r_date" value="<?php echo $return_date; ?>" autocomplete="off" placeholder="<?php echo wbbm_convert_datepicker_dateformat(); ?>">
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



        if ($boarding && $dropping && $journy_date) {
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

