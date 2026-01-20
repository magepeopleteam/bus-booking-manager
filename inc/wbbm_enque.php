<?php 
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.

// Enqueue Scripts for admin dashboard
add_action('admin_enqueue_scripts', 'wbbm_bus_admin_scripts');
function wbbm_bus_admin_scripts() {
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-core');   
    $plugin_root = dirname(__DIR__);
    $clocklet_ver = file_exists($plugin_root . '/css/clocklet.css') ? filemtime($plugin_root . '/css/clocklet.css') : null;
    $admin_style_ver = file_exists($plugin_root . '/css/admin_style.css') ? filemtime($plugin_root . '/css/admin_style.css') : null;
    $jquery_ui_ver = file_exists($plugin_root . '/css/jquery-ui.css') ? filemtime($plugin_root . '/css/jquery-ui.css') : null;
    wp_enqueue_style('wbbm-clocklet-style',plugin_dir_url( __DIR__ ).'css/clocklet.css',array(), $clocklet_ver);
    wp_enqueue_style('mep-admin-style',plugin_dir_url( __DIR__ ).'css/admin_style.css',array(), $admin_style_ver);
    wp_enqueue_style('mep-jquery-ui-style',plugin_dir_url( __DIR__ ).'css/jquery-ui.css',array(), $jquery_ui_ver);
    wp_enqueue_style('wbbm-font-awesome', plugin_dir_url( __DIR__ ) . 'assets/admin/fontawesome.min.css', array(), '5.2.0');
    wp_enqueue_script('wbbm-select2-lib',plugin_dir_url( __DIR__ ).'js/select2.full.min.js',array('jquery','jquery-ui-core'),1,true); 
    wp_enqueue_script('multidatepicker-wbbm', plugin_dir_url( __DIR__ ) . 'assets/admin/multidatespicker.js', array('jquery'), '1.6.9', true);
    wp_enqueue_script('multidatepicker-wbbm');
    wp_enqueue_script('wbbm-clocklet-lib',plugin_dir_url( __DIR__ ).'js/clocklet.js',array('jquery','jquery-ui-core'),1,true);
    wp_enqueue_script('gmap-scripts',plugin_dir_url( __DIR__ ).'js/mkb-admin.js',array('jquery','jquery-ui-core'),1,true);
    wp_enqueue_script('wbbm-single-datatabs',plugin_dir_url( __DIR__ ).'js/wbbm-single-datatabs.js',array('jquery'),time(),true);
    wp_enqueue_script('mp_script',plugin_dir_url( __DIR__ ).'js/mp_script.js',array('jquery'),time(),true);
    wp_enqueue_script('wbbm_custom_admin_script',plugin_dir_url( __DIR__ ).'js/wbbm_custom_admin_script.js',array('jquery'),time(),true);
    $custom_style_ver = file_exists($plugin_root . '/css/wbbm-custom-style.css') ? filemtime($plugin_root . '/css/wbbm-custom-style.css') : null;
    $mp_style_ver = file_exists($plugin_root . '/css/mp_style.css') ? filemtime($plugin_root . '/css/mp_style.css') : null;
    $mage_css_ver = file_exists($plugin_root . '/css/mage_css.css') ? filemtime($plugin_root . '/css/mage_css.css') : null;
    wp_enqueue_style('mep-ra-admin-style',plugin_dir_url( __DIR__ ).'css/wbbm-custom-style.css',array(), $custom_style_ver);
    wp_enqueue_style('mp-style',plugin_dir_url( __DIR__ ).'css/mp_style.css',array(), $mp_style_ver);
    wp_enqueue_style('mage_css',plugin_dir_url( __DIR__ ).'css/mage_css.css',array(), $mage_css_ver);
    
    // Routing admin CSS
    $routing_css_ver = file_exists($plugin_root . '/css/wbbm_routing_admin.css') ? filemtime($plugin_root . '/css/wbbm_routing_admin.css') : null;
    wp_enqueue_style('wbbm-routing-admin',plugin_dir_url( __DIR__ ).'css/wbbm_routing_admin.css',array(), $routing_css_ver);
    
    wp_enqueue_script('mage_style',plugin_dir_url( __DIR__ ).'js/mage_style.js',array('jquery'),time(),true);

    wp_enqueue_script('wbbm-admin-routing', 
        plugin_dir_url( __DIR__ ) . 'js/wbbm_admin_routing.js', 
        ['jquery', 'jquery-ui-sortable'], 
        time(), 
        true
    );

    wp_localize_script('wbbm-admin-routing', 'WbbmAjaxAdmin', [
        'url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wbbm_admin_ajax_nonce')
    ]);

    wp_localize_script('wbbm_custom_admin_script', 'WbbmAjaxAdmin', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('wbbm_admin_ajax_nonce'),
    ]);
}



function wbbm_add_admin_scripts( $hook ) {
    global $post;
    if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
        if ( 'wbbm_bus' === $post->post_type ) { 
             wp_enqueue_style('mep-jquery-ui-style',plugin_dir_url( __DIR__ ).'css/jquery-ui.css',array(), '1.11.0');
        
        }
    }
}

function WbbmdisplayDates($date1, $date2, $format = 'd-m-Y' ) {
    $dates = array();
    $current = strtotime($date1);
    $date2 = strtotime($date2);
    $stepVal = '+1 day';
    while( $current <= $date2 ) {
        $dates[] = gmdate($format, $current);
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

    $plugin_root = dirname(__DIR__);
    $front_jquery_ui_ver = file_exists($plugin_root . '/css/jquery-ui.css') ? filemtime($plugin_root . '/css/jquery-ui.css') : null;
    $front_style_ver = file_exists($plugin_root . '/css/style.css') ? filemtime($plugin_root . '/css/style.css') : null;
    $front_custom_ver = file_exists($plugin_root . '/css/wbbm-custom-style.css') ? filemtime($plugin_root . '/css/wbbm-custom-style.css') : null;
    $mage_css_ver = file_exists($plugin_root . '/css/mage_css.css') ? filemtime($plugin_root . '/css/mage_css.css') : null;
    wp_enqueue_style('wbbm-jquery-ui-style',plugin_dir_url( __DIR__ ).'css/jquery-ui.css',array(), $front_jquery_ui_ver);
    wp_enqueue_style('wbbm-bus-style',plugin_dir_url( __DIR__ ).'css/style.css',array(), $front_style_ver);
    wp_enqueue_style('wbbm-ra-bus-style',plugin_dir_url( __DIR__ ).'css/wbbm-custom-style.css',array(), $front_custom_ver);
    wp_enqueue_style ('wbbm-select2',plugin_dir_url( __DIR__ ).'assets/frontend/select2.min.css',null,'4.0.6');
    wp_enqueue_style('mage_css',plugin_dir_url( __DIR__ ).'css/mage_css.css',array(), $mage_css_ver);

    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-accordion');
    wp_enqueue_script('wbbm-select2-lib',plugin_dir_url( __DIR__ ).'js/select2.full.min.js',array('jquery','jquery-ui-core'),1,false);
    wp_enqueue_script('ra_script_public',plugin_dir_url( __DIR__ ).'js/wbbm_custom_public_script.js',array(),time(),false);
    wp_enqueue_script('mage_style',plugin_dir_url( __DIR__ ).'js/mage_style.js',array('jquery'),time(),true);
    wp_enqueue_style('font-awesome-css', plugin_dir_url( __DIR__ ).'assets/frontend/fontawesome.min.css', array(), '5.2.0');
    $mpstyles_ver = file_exists($plugin_root . '/css/mpstyles.css') ? filemtime($plugin_root . '/css/mpstyles.css') : null;
    wp_enqueue_style('wbbm-mp-styles',plugin_dir_url( __DIR__ ).'css/mpstyles.css',array(), $mpstyles_ver);

    wp_localize_script( 'mage_style', 'WbbmAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('wbbm_ajax_nonce'),
    ]);
}

// Ajax Issue
add_action('wp_head','wbbm_ajax_url',5);
add_action('admin_head','wbbm_ajax_url',5);
function wbbm_ajax_url() {
    ?>
    <script type="text/javascript">
        var wbtm_ajaxurl = "<?php echo esc_url(admin_url('admin-ajax.php')); ?>";
        var mp_date_format = "";
        const wbbm_currency_symbol = "<?php echo esc_html(html_entity_decode(get_woocommerce_currency_symbol())); ?>";
const wbbm_currency_position = "<?php echo esc_attr(get_option('woocommerce_currency_pos')); ?>";
const wbbm_currency_decimal = "<?php echo esc_attr(wc_get_price_decimal_separator()); ?>";
const wbbm_currency_thousands_separator = "<?php echo esc_attr(wc_get_price_thousand_separator()); ?>";
const wbbm_num_of_decimal = "<?php echo esc_attr(get_option('woocommerce_price_num_decimals', 2)); ?>";


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