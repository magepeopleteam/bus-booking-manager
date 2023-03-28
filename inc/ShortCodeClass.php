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
                        <?php mage_search_list(); ?>
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
                <?php search_from_only($single_bus,$target); ?>
            </div>
        </div>
    <?php }



}

