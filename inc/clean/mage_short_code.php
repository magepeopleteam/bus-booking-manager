<?php
if (!defined('ABSPATH')) {die;} // Cannot access pages directly.

add_shortcode('bus-search-form', 'mage_bus_search_form');
add_shortcode('bus-search', 'mage_bus_search');
function mage_bus_search_form($atts){
    $defaults = array(
        "style" => false,
        "result-page" => ''
    );
    $params         = shortcode_atts($defaults, $atts);
    $global_target = wbbm_get_option('wbbm_search_result_page', 'wbbm_general_setting_sec','bus-search');
    $target = $params['result-page'] ? $params['result-page'] : $global_target;
    ob_start();
    if($params['style']=='vertical'){
        mage_search_form_vertical($target);
    }
    else{
        mage_search_form_horizontal(false,$target);
    }
    return ob_get_clean();
}




function mage_bus_search($atts){
    $defaults = array(
        "style" => false
    );
    $params         = shortcode_atts($defaults, $atts);
    //ob_clean();
    ob_start();

    if($params['style']=='vertical'){
        mage_search_page_vertical();
    }
    else{
        mage_search_page_horizontal();
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
        while ($loop->have_posts()) {
            $loop->the_post();
            if (mage_odd_list_check(false) && mage_off_day_check(false) && !$return) {
                $j_time = strtotime(mage_get_isset('j_date').' '. boarding_dropping_time(false,false));
                if( $c_time < $j_time){
                    mage_search_item(false);
                }
            }
            if (mage_odd_list_check(true) && mage_off_day_check(true) && $_GET['r_date'] && $return) {
                $j_time = strtotime(mage_get_isset('j_date').' '. boarding_dropping_time(false,false));
                $r_time = strtotime(mage_get_isset('r_date').' '. boarding_dropping_time(false,true));
                if( $j_time < $r_time){
                    mage_search_item(true);
                }
            }
        }
    }
}