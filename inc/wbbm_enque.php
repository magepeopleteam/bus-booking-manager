<?php 
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.

// Enqueue Scripts for admin dashboard
add_action('admin_enqueue_scripts', 'wbbm_bus_admin_scripts');
function wbbm_bus_admin_scripts() {
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-core');   
    wp_enqueue_style('wbbm-clocklet-style',plugin_dir_url( __DIR__ ).'css/clocklet.css',array());
    wp_enqueue_style('mep-admin-style',plugin_dir_url( __DIR__ ).'css/admin_style.css',array(),time());
    wp_enqueue_style('mep-jquery-ui-style',plugin_dir_url( __DIR__ ).'css/jquery-ui.css',array());
    wp_enqueue_style('font-awesome-css-cdn', "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.2.0/css/all.min.css", null, 1);
    wp_enqueue_script('wbbm-select2-lib',plugin_dir_url( __DIR__ ).'js/select2.full.min.js',array('jquery','jquery-ui-core'),1,true); 
    wp_register_script('multidatepicker-wbbm', 'https://cdn.rawgit.com/dubrox/Multiple-Dates-Picker-for-jQuery-UI/master/jquery-ui.multidatespicker.js', array('jquery'), 1, true);
    wp_enqueue_script('multidatepicker-wbbm');
    wp_enqueue_script('wbbm-clocklet-lib',plugin_dir_url( __DIR__ ).'js/clocklet.js',array('jquery','jquery-ui-core'),1,true);
    wp_enqueue_script('gmap-scripts',plugin_dir_url( __DIR__ ).'js/mkb-admin.js',array('jquery','jquery-ui-core'),1,true);
    wp_enqueue_script('wbbm-single-datatabs',plugin_dir_url( __DIR__ ).'js/wbbm-single-datatabs.js',array('jquery'),time(),true);
    wp_enqueue_script('mp_script',plugin_dir_url( __DIR__ ).'js/mp_script.js',array('jquery'),time(),true);
    wp_enqueue_script('wbbm_custom_admin_script',plugin_dir_url( __DIR__ ).'js/wbbm_custom_admin_script.js',array('jquery'),time(),true);
    wp_enqueue_style('mep-ra-admin-style',plugin_dir_url( __DIR__ ).'css/wbbm-custom-style.css',array(),time());
    wp_enqueue_style('mp-style',plugin_dir_url( __DIR__ ).'css/mp_style.css',array());
    wp_enqueue_style('mage_css',plugin_dir_url( __DIR__ ).'css/mage_css.css',array(), time());
    wp_enqueue_script('mage_style',plugin_dir_url( __DIR__ ).'js/mage_style.js',array('jquery'),time(),true);
}



function wbbm_add_admin_scripts( $hook ) {
    global $post;
    if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
        if ( 'wbbm_bus' === $post->post_type ) { 
             wp_enqueue_style('mep-jquery-ui-style',plugin_dir_url( __DIR__ ).'css/jquery-ui.css',array());
        
        }
    }
}

function displayDates($date1, $date2, $format = 'd-m-Y' ) {
    $dates = array();
    $current = strtotime($date1);
    $date2 = strtotime($date2);
    $stepVal = '+1 day';
    while( $current <= $date2 ) {
        $dates[] = date($format, $current);
        $current = strtotime($stepVal, $current);
    }
    return  $dates;
}



add_action( 'admin_enqueue_scripts', 'wbbm_add_admin_scripts', 10, 1 );




// Datepicker code for admin dashboard load in footer section
add_action('admin_footer','wbbm_admin_footer_script',10,99);
add_action('wp_footer','wbbm_admin_footer_script',10,99);

function wbbm_admin_footer_script(){
    global $post;
    ob_start();
}





// Select2 code for admin dashboard load in footer section
add_action('wp_footer','wbbm_admin_footer_select_2_script',10,99);
function wbbm_admin_footer_select_2_script(){
  ?>
<script type="text/javascript">
jQuery(document).ready(function($){
      jQuery(".select2, #boarding_point, #drp_point").select2();
    });
</script>
  <?php
}







// Enqueue Scripts for frontend
add_action('wp_enqueue_scripts', 'wbbm_bus_enqueue_scripts');
function wbbm_bus_enqueue_scripts() {

    wp_enqueue_style('wbbm-jquery-ui-style',plugin_dir_url( __DIR__ ).'css/jquery-ui.css',array());
    wp_enqueue_style('wbbm-bus-style',plugin_dir_url( __DIR__ ).'css/style.css',array());
    wp_enqueue_style('wbbm-ra-bus-style',plugin_dir_url( __DIR__ ).'css/wbbm-custom-style.css',array());
    wp_enqueue_style ('wbbm-select2-style-cdn',"https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css",null,1);
    wp_enqueue_style('mage_css',plugin_dir_url( __DIR__ ).'css/mage_css.css',array(), time());

    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-accordion');
    wp_enqueue_script('wbbm-select2-lib',plugin_dir_url( __DIR__ ).'js/select2.full.min.js',array('jquery','jquery-ui-core'),1,false);
    wp_enqueue_script('ra_script_public',plugin_dir_url( __DIR__ ).'js/wbbm_custom_public_script.js',array(),time(),false);
    wp_enqueue_script('mage_style',plugin_dir_url( __DIR__ ).'js/mage_style.js',array('jquery'),time(),true);
    wp_enqueue_style('font-awesome-css-cdn', '//cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.3/css/all.min.css', array(), '5.15.3');
}

// Ajax Issue
add_action('wp_head','wbbm_ajax_url',5);
add_action('admin_head','wbbm_ajax_url',5);
function wbbm_ajax_url() {
    ?>
    <script type="text/javascript">
        var wbtm_ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
        const wbbm_currency_symbol = "<?php echo html_entity_decode(get_woocommerce_currency_symbol()); ?>";
        const wbbm_currency_position = "<?php echo get_option('woocommerce_currency_pos'); ?>";
        const wbbm_currency_decimal = "<?php echo wc_get_price_decimal_separator(); ?>";
        const wbbm_currency_thousands_separator = "<?php echo wc_get_price_thousand_separator(); ?>";
        const wbbm_num_of_decimal = "<?php echo get_option('woocommerce_price_num_decimals', 2); ?>";

        // currency format according to WooCommerce setting
        function wbbm_woo_price_format(price) {
            if (typeof price === 'string') {
                price = Number(price);
            }
            price = price.toFixed(2);
            // price = price.toString();
            // price = price.toFixed(wbbm_num_of_decimal);
            let price_text = '';
            if (wbbm_currency_position === 'right') {
                price_text = price + wbbm_currency_symbol;
            } else if (wbbm_currency_position === 'right_space') {
                price_text = price + ' ' + wbbm_currency_symbol;
            } else if (wbbm_currency_position === 'left') {
                price_text = wbbm_currency_symbol + price;
            } else {
                price_text = wbbm_currency_symbol + ' ' + price;
            }
            return price_text;
        }
    </script>
    <?php
}