<?php
if (! defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

// Enqueue Scripts for admin dashboard
add_action('admin_enqueue_scripts', 'wbbm_bus_admin_scripts');
function wbbm_bus_admin_scripts()
{
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-core');
    $plugin_root = dirname(__DIR__);
    $clocklet_ver = file_exists($plugin_root . '/css/clocklet.css') ? filemtime($plugin_root . '/css/clocklet.css') : null;
    $admin_style_ver = file_exists($plugin_root . '/css/admin_style.css') ? filemtime($plugin_root . '/css/admin_style.css') : null;
    $jquery_ui_ver = file_exists($plugin_root . '/css/jquery-ui.css') ? filemtime($plugin_root . '/css/jquery-ui.css') : null;
    wp_enqueue_style('wbbm-clocklet-style', plugin_dir_url(__DIR__) . 'css/clocklet.css', array(), $clocklet_ver);
    wp_enqueue_style('mep-admin-style', plugin_dir_url(__DIR__) . 'css/admin_style.css', array(), $admin_style_ver);
    wp_enqueue_style('mep-jquery-ui-style', plugin_dir_url(__DIR__) . 'css/jquery-ui.css', array(), $jquery_ui_ver);
    wp_enqueue_style('wbbm-font-awesome', plugin_dir_url(__DIR__) . 'assets/admin/fontawesome.min.css', array(), '5.2.0');
    wp_enqueue_script('wbbm-select2-lib', plugin_dir_url(__DIR__) . 'js/select2.full.min.js', array('jquery','jquery-ui-core'), 1, true);
    wp_enqueue_script('multidatepicker-wbbm', plugin_dir_url(__DIR__) . 'assets/admin/multidatespicker.js', array('jquery'), '1.6.9', true);
    wp_enqueue_script('multidatepicker-wbbm');
    wp_enqueue_script('wbbm-clocklet-lib', plugin_dir_url(__DIR__) . 'js/clocklet.js', array('jquery','jquery-ui-core'), 1, true);
    wp_enqueue_script('gmap-scripts', plugin_dir_url(__DIR__) . 'js/mkb-admin.js', array('jquery','jquery-ui-core'), 1, true);
    wp_enqueue_script('wbbm-single-datatabs', plugin_dir_url(__DIR__) . 'js/wbbm-single-datatabs.js', array('jquery'), time(), true);
    wp_enqueue_script('mp_script', plugin_dir_url(__DIR__) . 'js/mp_script.js', array('jquery'), time(), true);
    wp_enqueue_script('wbbm_custom_admin_script', plugin_dir_url(__DIR__) . 'js/wbbm_custom_admin_script.js', array('jquery'), time(), true);
    $custom_style_ver = file_exists($plugin_root . '/css/wbbm-custom-style.css') ? filemtime($plugin_root . '/css/wbbm-custom-style.css') : null;
    $mp_style_ver = file_exists($plugin_root . '/css/mp_style.css') ? filemtime($plugin_root . '/css/mp_style.css') : null;
    $mage_css_ver = file_exists($plugin_root . '/css/mage_css.css') ? filemtime($plugin_root . '/css/mage_css.css') : null;
    wp_enqueue_style('mep-ra-admin-style', plugin_dir_url(__DIR__) . 'css/wbbm-custom-style.css', array(), $custom_style_ver);
    wp_enqueue_style('mp-style', plugin_dir_url(__DIR__) . 'css/mp_style.css', array(), $mp_style_ver);
    wp_enqueue_style('mage_css', plugin_dir_url(__DIR__) . 'css/mage_css.css', array(), $mage_css_ver);

    // Routing admin CSS
    $routing_css_ver = file_exists($plugin_root . '/css/wbbm_routing_admin.css') ? filemtime($plugin_root . '/css/wbbm_routing_admin.css') : null;
    wp_enqueue_style('wbbm-routing-admin', plugin_dir_url(__DIR__) . 'css/wbbm_routing_admin.css', array(), $routing_css_ver);

    wp_enqueue_script('mage_style', plugin_dir_url(__DIR__) . 'js/mage_style.js', array('jquery'), time(), true);

    wp_enqueue_script(
        'wbbm-admin-routing',
        plugin_dir_url(__DIR__) . 'js/wbbm_admin_routing.js',
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

    // Global Toaster Notifications
    wp_enqueue_style('wbbm-toaster-css', plugin_dir_url(__DIR__) . 'assets/admin/wbbm-toaster.css', array(), time());
    wp_enqueue_script('wbbm-toaster-js', plugin_dir_url(__DIR__) . 'assets/admin/bus-toaster.js', array('jquery'), time(), true);
}



function wbbm_add_admin_scripts($hook)
{
    global $post;
    if ($hook == 'post-new.php' || $hook == 'post.php') {
        if ('wbbm_bus' === $post->post_type) {
             wp_enqueue_style('mep-jquery-ui-style', plugin_dir_url(__DIR__) . 'css/jquery-ui.css', array(), '1.11.0');
        }

    }
}

function WbbmdisplayDates($date1, $date2, $format = 'd-m-Y')
{
    $dates = array();
    $current = strtotime($date1);
    $date2 = strtotime($date2);
    $stepVal = '+1 day';
    while ($current <= $date2) {
        $dates[] = gmdate($format, $current);
        $current = strtotime($stepVal, $current);
    }
    return  $dates;
}



add_action('admin_enqueue_scripts', 'wbbm_add_admin_scripts', 10, 1);




// Datepicker code for admin dashboard load in footer section
add_action('admin_footer', 'wbbm_admin_footer_script', 10, 99);
add_action('wp_footer', 'wbbm_admin_footer_script', 10, 99);

function wbbm_admin_footer_script()
{
    global $post;
    ob_start();
}





// Select2 code for admin dashboard load in footer section
add_action('wp_footer', 'wbbm_admin_footer_select_2_script', 10, 99);
function wbbm_admin_footer_select_2_script()
{
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
function wbbm_bus_enqueue_scripts()
{

    $plugin_root = dirname(__DIR__);
    $front_jquery_ui_ver = file_exists($plugin_root . '/css/jquery-ui.css') ? filemtime($plugin_root . '/css/jquery-ui.css') : null;
    $front_style_ver = file_exists($plugin_root . '/css/style.css') ? filemtime($plugin_root . '/css/style.css') : null;
    $front_custom_ver = file_exists($plugin_root . '/css/wbbm-custom-style.css') ? filemtime($plugin_root . '/css/wbbm-custom-style.css') : null;
    $mage_css_ver = file_exists($plugin_root . '/css/mage_css.css') ? filemtime($plugin_root . '/css/mage_css.css') : null;
    wp_enqueue_style('wbbm-jquery-ui-style', plugin_dir_url(__DIR__) . 'css/jquery-ui.css', array(), $front_jquery_ui_ver);
    wp_enqueue_style('wbbm-bus-style', plugin_dir_url(__DIR__) . 'css/style.css', array(), $front_style_ver);
    wp_enqueue_style('wbbm-ra-bus-style', plugin_dir_url(__DIR__) . 'css/wbbm-custom-style.css', array(), $front_custom_ver);
    wp_enqueue_style('wbbm-select2', plugin_dir_url(__DIR__) . 'assets/frontend/select2.min.css', null, '4.0.6');
    wp_enqueue_style('mage_css', plugin_dir_url(__DIR__) . 'css/mage_css.css', array(), $mage_css_ver);

    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-accordion');
    wp_enqueue_script('wbbm-select2-lib', plugin_dir_url(__DIR__) . 'js/select2.full.min.js', array('jquery','jquery-ui-core'), 1, false);
    wp_enqueue_script('ra_script_public', plugin_dir_url(__DIR__) . 'js/wbbm_custom_public_script.js', array(), time(), false);
    wp_enqueue_script('mage_style', plugin_dir_url(__DIR__) . 'js/mage_style.js', array('jquery'), time(), true);
    wp_enqueue_style('font-awesome-css', plugin_dir_url(__DIR__) . 'assets/frontend/fontawesome.min.css', array(), '5.2.0');
    $mpstyles_ver = file_exists($plugin_root . '/css/mpstyles.css') ? filemtime($plugin_root . '/css/mpstyles.css') : null;
    wp_enqueue_style('wbbm-mp-styles', plugin_dir_url(__DIR__) . 'css/mpstyles.css', array(), $mpstyles_ver);

    // Modern search form skin -- loaded last so it wins over mage_css.css.
    $search_modern_css_ver = file_exists($plugin_root . '/css/wbbm-search-modern.css') ? filemtime($plugin_root . '/css/wbbm-search-modern.css') : null;
    $search_modern_js_ver = file_exists($plugin_root . '/js/wbbm-search-modern.js') ? filemtime($plugin_root . '/js/wbbm-search-modern.js') : null;
    wp_enqueue_style('wbbm-search-modern', plugin_dir_url(__DIR__) . 'css/wbbm-search-modern.css', array('mage_css', 'wbbm-mp-styles'), $search_modern_css_ver);
    wp_enqueue_script('wbbm-search-modern', plugin_dir_url(__DIR__) . 'js/wbbm-search-modern.js', array('jquery', 'mage_style'), $search_modern_js_ver, true);
    wp_localize_script('wbbm-search-modern', 'WbbmSearchModern', array(
        'returnPrompt'     => __('Outbound trip selected. Now choose your return bus.', 'bus-booking-manager'),
        'chosenLabel'      => __('Selected', 'bus-booking-manager'),
        'outboundLabel'    => __('Outbound', 'bus-booking-manager'),
        'returnLabel'      => __('Return', 'bus-booking-manager'),
        'subtotalLabel'    => __('Subtotal', 'bus-booking-manager'),
        'totalLabel'       => __('Total', 'bus-booking-manager'),
        'returnTotalLabel' => __('Trip total', 'bus-booking-manager'),
        'bothLegsNote'     => __('Both legs are booked together, under one reference.', 'bus-booking-manager'),
        'yourTripLabel'    => __('Your trip', 'bus-booking-manager'),
        'cartFailed'       => __('Could not add this to the cart. Please try again.', 'bus-booking-manager'),
        'noProduct'        => __('This bus is not connected to WooCommerce yet. Open it in the admin and save it once.', 'bus-booking-manager'),
    ));

    wp_localize_script('mage_style', 'WbbmAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('wbbm_ajax_nonce'),
    ]);
}

// Ajax Issue
add_action('wp_head', 'wbbm_ajax_url', 5);
add_action('admin_head', 'wbbm_ajax_url', 5);
function wbbm_ajax_url()
{
    $use_wc = class_exists('MP_Global_Function') && MP_Global_Function::wbbm_use_wc();
    $currency_symbol = $use_wc && function_exists('get_woocommerce_currency_symbol')
        ? html_entity_decode(get_woocommerce_currency_symbol())
        : get_option('wbbm_currency_symbol', '$');
    $currency_decimal_sep = $use_wc && function_exists('wc_get_price_decimal_separator')
        ? wc_get_price_decimal_separator()
        : get_option('wbbm_price_decimal_sep', '.');
    $currency_thousand_sep = $use_wc && function_exists('wc_get_price_thousand_separator')
        ? wc_get_price_thousand_separator()
        : get_option('wbbm_price_thousand_sep', ',');
    ?>
    <script type="text/javascript">
        var wbtm_ajaxurl = "<?php echo esc_url(admin_url('admin-ajax.php')); ?>";
        var mp_date_format = "";
        const wbbm_currency_symbol = "<?php echo esc_html($currency_symbol); ?>";
const wbbm_currency_position = "<?php echo esc_attr(get_option('woocommerce_currency_pos')); ?>";
const wbbm_currency_decimal = "<?php echo esc_attr($currency_decimal_sep); ?>";
const wbbm_currency_thousands_separator = "<?php echo esc_attr($currency_thousand_sep); ?>";
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

/**
 * Checkout inside the booking drawer.
 *
 * The drawer frames the real WooCommerce checkout so billing, gateways and
 * validation stay WooCommerce's. Only the surrounding theme chrome is taken
 * away: inside a 440px drawer the site header, footer and admin bar are noise,
 * and the header's nav would let someone browse away mid-payment.
 *
 * Covers block themes (header/footer render as template parts) and classic
 * ones (#masthead / .site-header and friends). Nothing is removed from the
 * page itself, so the checkout keeps working if a theme names things
 * differently -- worst case the chrome stays visible.
 */
function wbbm_is_embedded_checkout()
{
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- presentation only.
    if (empty($_GET['wbbm_embed']) || !function_exists('is_checkout')) {
        return false;
    }

    // is_checkout() already covers the order-received page, but list it so the
    // intent is explicit: the confirmation lands inside the drawer too.
    return is_checkout() || (function_exists('is_order_received_page') && is_order_received_page());
}

/**
 * Keep the drawer flag across the checkout -> order received redirect.
 *
 * WooCommerce builds that URL itself, so without this the query arg is lost
 * and the confirmation renders with the site header and footer back.
 */
add_filter('woocommerce_get_checkout_order_received_url', 'wbbm_keep_embed_on_order_received', 10, 1);
function wbbm_keep_embed_on_order_received($url)
{
    return wbbm_request_is_from_drawer() ? add_query_arg('wbbm_embed', '1', $url) : $url;
}

/**
 * Is this request coming from the drawer's framed checkout?
 *
 * The query arg alone is not enough: WooCommerce places the order over AJAX
 * at /?wc-ajax=checkout, a URL that carries none of the page's own args, so
 * the redirect it builds would drop the flag and the confirmation would come
 * back with the site header and footer. The referer is that framed page, so
 * it is what identifies the request.
 *
 * Deliberately not a session flag: that would outlive the booking and strip
 * the chrome from an ordinary checkout visit later in the same session.
 */
function wbbm_request_is_from_drawer()
{
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- presentation only.
    if (!empty($_GET['wbbm_embed']) || !empty($_POST['wbbm_embed'])) {
        return true;
    }

    $referer = wp_get_referer();
    if (!$referer) {
        return false;
    }

    $query = wp_parse_url($referer, PHP_URL_QUERY);
    if (!$query) {
        return false;
    }

    parse_str($query, $args);

    return !empty($args['wbbm_embed']);
}

add_filter('show_admin_bar', function ($show) {
    return wbbm_is_embedded_checkout() ? false : $show;
}, 99);

add_action('wp_head', 'wbbm_embedded_checkout_chrome', 99);
function wbbm_embedded_checkout_chrome()
{
    if (!wbbm_is_embedded_checkout()) {
        return;
    }
    ?>
    <style id="wbbm-embedded-checkout">
        header.wp-block-template-part,
        footer.wp-block-template-part,
        .wp-site-blocks > header,
        .wp-site-blocks > footer,
        #masthead,
        #colophon,
        .site-header,
        .site-footer,
        .wp-block-post-title,
        .woocommerce-breadcrumb,
        #wpadminbar {
            display: none !important;
        }

        html {
            margin-top: 0 !important;
        }

        body {
            padding: 0 !important;
            background: #fff;
        }

        .wp-site-blocks,
        .entry-content,
        .woocommerce {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        /* the drawer is narrow, so the checkout runs as one column */
        .woocommerce-checkout .col2-set .col-1,
        .woocommerce-checkout .col2-set .col-2 {
            float: none;
            width: 100%;
        }
    </style>
    <?php
}

/**
 * Mark pages this plugin renders.
 *
 * Adds body.wbbm-page wherever the plugin owns the content: any of its
 * shortcodes, a single bus, or its own taxonomy archives. The stylesheet then
 * lines the theme's page title up with .mage_container instead of letting the
 * two use different widths and alignments.
 */
add_filter('body_class', 'wbbm_mark_plugin_pages');
function wbbm_mark_plugin_pages($classes)
{
    if (is_admin()) {
        return $classes;
    }

    if (is_singular('wbbm_bus') || is_tax('wbbm_bus_category') || is_tax('wbbm_bus_organizer')) {
        $classes[] = 'wbbm-page';
        return $classes;
    }

    if (is_singular()) {
        $post = get_post();
        $shortcodes = array('bus-search-form', 'bus-search', 'bus-list', 'destination');

        foreach ($shortcodes as $tag) {
            if ($post && has_shortcode((string) $post->post_content, $tag)) {
                $classes[] = 'wbbm-page';
                break;
            }
        }
    }

    return $classes;
}
