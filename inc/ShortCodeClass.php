<?php
if (!defined('ABSPATH')) exit;  // if direct access

class ShortCodeClass extends SearchClass
{
    public function __construct()
    {

    }


    function mage_bus_search_form($atts){
        $defaults = array("style" => false, "result-page" => '');
        $params         = shortcode_atts($defaults, $atts);
        $global_target = wbbm_get_option('wbbm_search_result_page', 'wbbm_general_setting_sec','bus-search');
        $target = $params['result-page'] ? $params['result-page'] : $global_target;
        ob_start();
        if($params['style']=='vertical'){
            $this->mage_search_form_vertical($target);
        }
        else{
            $this->mage_search_form_horizontal(false,$target);
        }
        return ob_get_clean();
    }


    function mage_bus_search($atts){
        $defaults = array("style" => false, "theme" => 'minimal',);
        $params         = shortcode_atts($defaults, $atts);
        global $mage_bus_search_theme;
        $mage_bus_search_theme = $params['theme'];
        //ob_clean();
        ob_start();
        if($params['style']=='vertical'){
            $this->mage_search_page_vertical();
        }
        else{
            $this->mage_search_page_horizontal();
        }
        return ob_get_clean();
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
                            mage_search_item($return);
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
                                mage_search_item($return);
                            }
                        } else {
                            $has_bus = true;
                            mage_search_item($return);
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
<<<<<<< HEAD
            </div>
        </div>
    <?php }

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
                <strong>
                    <?php echo wbbm_get_option('wbbm_route_text', 'wbbm_label_setting_sec', __('Route', 'bus-booking-manager'));echo ':'; ?>
                </strong>
                <?php echo mage_get_isset('bus_start_route'); ?>
                <span class="fa fa-long-arrow-right"></span>
                <?php echo mage_get_isset('bus_end_route'); ?>
                <strong>
                    <?php echo ' | '; echo wbbm_get_option('wbbm_date_text', 'wbbm_label_setting_sec', __('Date', 'bus-booking-manager')); echo ':'; ?>
                </strong>
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
                <strong>
                    <?php echo wbbm_get_option('wbbm_route_text', 'wbbm_label_setting_sec', __('Route', 'bus-booking-manager')); echo ':'; ?>
                </strong>
                <?php echo mage_get_isset('bus_end_route'); ?>
                <span class="fa fa-long-arrow-right"></span>
                <?php echo mage_get_isset('bus_start_route'); ?>
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

                        <span><?php echo wbbm_get_option('wbbm_view_text', 'wbbm_label_setting_sec', __('View', 'bus-booking-manager')); ?></span>
                    </div>
                </div>
            <?php } ?>
            <?php mage_search_bus_list(true); ?>
        </div>
        <div class="mage_mini_cart mage_hidden">
            <p><?php echo wbbm_get_option('wbbm_total_text', 'wbbm_label_setting_sec', __('Total', 'bus-booking-manager')); ?></p>
            <p class="mage_total"><strong><span><?php echo wc_price(0); ?></span></strong></p>
        </div>
    <?php }

        do_action('wbbm_prevent_form_resubmission');
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
                        <?php mage_search_list(); ?>
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
                        echo '<div class="mage_input_select_list" '; if($search_form_dropdown_b_color){ echo 'style="background-color:'.$search_form_dropdown_b_color.'"'; } echo '><ul>';
                        foreach ($start_stops as $_start_stops) {
                            echo '<li '; if($search_form_dropdown_text_color){ echo 'style="color:'.$search_form_dropdown_text_color.'"'; } echo ' data-route="' . $_start_stops['wbbm_bus_bp_price_stop'] . '"><span class="fa fa-map-marker"></span>' . $_start_stops['wbbm_bus_bp_price_stop'] . '</li>';
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

    function mage_search_form_vertical($target=''){
        ?>
        <div class="mage_container">
            <div class="mage_search_box_small">
                <h2><?php echo wbbm_get_option('wbbm_buy_ticket_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_buy_ticket_text', 'wbbm_label_setting_sec') : _e('BUY TICKET', 'bus-booking-manager'); ?></h2>
                <?php do_action('mage_search_from_only',false,$target); ?>
=======
>>>>>>> f29f306563aaedc2b09b51a8cc65c59109e1c9aa
            </div>
        </div>
    <?php }



}

