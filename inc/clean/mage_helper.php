<?php

if (!defined('ABSPATH')) {
    die;
}

function mage_get_isset($parameter)
{
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    return isset($_GET[$parameter]) ? sanitize_text_field(wp_unslash($_GET[$parameter])) : '';
}

function mage_qty_box($price, $name, $return)
{
    $date = $return ? mage_get_isset('r_date') : mage_get_isset('j_date');
    $available_seat = wbbm_intermidiate_available_seat(
        mage_get_isset('bus_start_route'),
        mage_get_isset('bus_end_route'),
        wbbm_convert_date_to_php($date)
    );
    if ($available_seat > 0) {

        if ($name == 'child_quantity') {
            $ticket_title = wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec', __('Child', 'bus-booking-manager'));
            $ticket_type = 'child';
        } elseif ($name == 'infant_quantity') {
            $ticket_title = wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec', __('Infant', 'bus-booking-manager'));
            $ticket_type = 'infant';
        } else {
            $ticket_title = wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec', __('Adult', 'bus-booking-manager'));
            $ticket_type = 'adult';
        }
?>
        <div class="mage_form_group">
            <div class="mage_flex mage_qty_dec"><span class="fa fa-minus"></span></div>
            <input type="text"
                class="mage_form mage_seat_qty"
                data-ticket-title="<?php echo esc_attr($ticket_title . ' ' . __('Passenger info', 'bus-booking-manager')); ?>"
                data-ticket-type="<?php echo esc_attr($ticket_type); ?>"
                data-price="<?php echo esc_attr($price); ?>"
                name="<?php echo esc_attr($name); ?>"
                value="0"
                min="0"
                max="<?php echo esc_attr($available_seat); ?>"
                <?php echo ($ticket_type === 'adult' ? 'required' : ''); ?> />
            <div class="mage_flex mage_qty_inc"><span class="fa fa-plus"></span></div>
        </div>
    <?php
    }
}

function wbbm_entire_switch($price, $name, $return)
{
    $date = $return ? mage_get_isset('r_date') : mage_get_isset('j_date');
    $available_seat = wbbm_intermidiate_available_seat(
        mage_get_isset('bus_start_route'),
        mage_get_isset('bus_end_route'),
        wbbm_convert_date_to_php($date)
    );

    if ($name == 'entire_quantity') {
        $ticket_title = wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec', __('Entire', 'bus-booking-manager'));
        $ticket_type = 'entire';
    }
    ?>
    <div class="wbbm_entire_switch_wrapper" data-entire-price="<?php echo esc_attr($price); ?>">
        <label class="switch">
            <input type="checkbox"
                id="wbbm_entire_bus"
                name="<?php echo esc_attr($name); ?>"
                value="0"
                data-ticket-title="<?php echo esc_attr($ticket_title . ' ' . __('Passenger info', 'bus-booking-manager')); ?>"
                data-ticket-type="<?php echo esc_attr($ticket_type); ?>"
                data-price="<?php echo esc_attr($price); ?>"
                class="mage_form" />
            <span class="slider round"></span>
        </label>
    </div>
    <?php
}

function wbbm_hidden_input_field($name, $value)
{
    echo '<input type="hidden" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '"/>';
}

function wbbm_cart_qty($name)
{
    $qty_type = ($name == 'adult_quantity') ? 'wbbm_total_adult_qt' : 'wbbm_total_child_qt';
    $product_id = get_the_id();
    $cart = WC()->cart->get_cart();
    foreach ($cart as $cart_item) {
        if ($cart_item['product_id'] == $product_id) {
            return $cart_item[$qty_type] > 0 ? $cart_item[$qty_type] : 0;
        }
    }
    return 0;
}

function mage_route_list($drop = false)
{
    $routes = get_terms(array(
        'taxonomy' => 'wbbm_bus_stops',
        'hide_empty' => false,
    ));

    $search_form_dropdown_b_color = esc_attr(wbbm_get_option('wbbm_search_form_dropdown_b_color', 'wbbm_style_setting_sec'));
    $search_form_dropdown_text_color = esc_attr(wbbm_get_option('wbbm_search_form_dropdown_t_color', 'wbbm_style_setting_sec', ''));

    echo '<div class="mage_input_select_list" ' . ($drop ? 'id="wbtm_dropping_point_list"' : '') . ' ';
    if ($search_form_dropdown_b_color) {
        echo 'style="background-color:' . esc_attr($search_form_dropdown_b_color) . '"';
    }
    echo '><ul>';
    foreach ($routes as $route) {
        echo '<li ';
        if ($search_form_dropdown_text_color) {
            echo 'style="color:' . esc_attr($search_form_dropdown_text_color) . '"';
        }
        echo ' data-route="' . esc_attr($route->name) . '"><span class="fa fa-map-marker"></span>' . esc_html($route->name) . '</li>';
    }
    echo '</ul></div>';
}

add_action('wp_ajax_wbbm_load_dropping_point', 'wbbm_load_dropping_point');
add_action('wp_ajax_nopriv_wbbm_load_dropping_point', 'wbbm_load_dropping_point');
function wbbm_load_dropping_point()
{
    check_ajax_referer('wbbm_ajax_nonce', 'nonce');

    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
    $boardingPoint = isset($_POST['boarding_point']) ? MP_Global_Function::wbbm_recursive_sanitize(wp_unslash($_POST['boarding_point'])) : array();
    $category = get_term_by('name', $boardingPoint, 'wbbm_bus_stops');
    $allStopArr = get_terms(array(
        'taxonomy' => 'wbbm_bus_stops',
        'hide_empty' => false
    ));
    $dropingarray = get_term_meta($category->term_id, 'wbbm_bus_routes_name_list', true) ? maybe_unserialize(get_term_meta($category->term_id, 'wbbm_bus_routes_name_list', true)) : array();

    if (is_array($dropingarray) && count($dropingarray) > 0) {
        foreach ($dropingarray as $dp) {
            $name = sanitize_text_field($dp['wbbm_bus_routes_name']);
            echo '<li data-route="' . esc_attr($name) . '"><span class="fa fa-map-marker"></span>' . esc_html($name) . '</li>';
        }
    } else {
        foreach ($allStopArr as $dp) {
            $name = esc_html($dp->name);
            echo '<li data-route="' . esc_attr($name) . '"><span class="fa fa-map-marker"></span>' . esc_html($name) . '</li>';
        }
    }
    wp_die();
}

function mage_bus_list_query($start, $end)
{
    $start = mage_get_isset($start);
    $end = mage_get_isset($end);
    // Avoid unconstrained full-table meta queries where possible:
    // - limit results instead of -1, return only IDs, and disable SQL_CALC_FOUND_ROWS
    // Note: for large datasets consider normalizing stops into a taxonomy or dedicated table.
    return array(
        'post_type' => array('wbbm_bus'),
        // limit results to avoid heavy queries; adjust as needed
        'posts_per_page' => 200,
        'order' => 'ASC',
        'orderby' => 'meta_value',
        'post_status' => array('publish'),
        'no_found_rows' => true,
        'fields' => 'ids',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'wbbm_bus_bp_stops',
                'value' => $start,
                'compare' => 'LIKE',
            ),
            array(
                'key' => 'wbbm_bus_next_stops',
                'value' => $end,
                'compare' => 'LIKE',
            ),
        )
    );
}



// Odd range check
function mage_odd_list_check($return)
{
    $start_date = strtotime(get_post_meta(get_the_ID(), 'wbtm_od_start', true));
    $end_date = strtotime(get_post_meta(get_the_ID(), 'wbtm_od_end', true));
    $date = mage_get_isset($return ? 'r_date' : 'j_date');
    $date = mage_wp_date($date, 'Y-m-d');

    return (($start_date <= $date) && ($end_date >= $date)) ? false : true;
}

// Off day check
function mage_off_day_check($return)
{
    $get_day = null;
    $id = get_the_ID();
    $j_date = $return ? mage_wp_date(mage_get_isset('r_date'), 'Y-m-d') : mage_wp_date(mage_get_isset('j_date'), 'Y-m-d');

    $weekly_offday = get_post_meta($id, 'weekly_offday', true) ?: array();
    if ($j_date) {
        $j_date_day = strtolower(gmdate('N', strtotime($j_date)));
        if (in_array($j_date_day, $weekly_offday)) {
            $get_day = 'yes';
        }

        return $get_day === 'yes';
    }
    return false;
}

// Check if product is already in cart
function mage_find_product_in_cart()
{
    if (!is_admin()) {
        $product_id = get_the_ID();
        $cart = WC()->cart->get_cart();
        foreach ($cart as $cart_item) {
            if ($cart_item['product_id'] == $product_id) {
                return true;
            }
        }
    }
    return false;
}

// Get available seat
function mage_available_seat($date)
{
    $values = get_post_custom(get_the_ID());
    $total_seat = (int) $values['wbbm_total_seat'][0];
    $sold_seat = (int) wbbm_get_available_seat(get_the_ID(), $date);
    return max(0, $total_seat - $sold_seat);
}

// Get cart item quantity
function wbbm_get_cart_item($bus_id, $date_var)
{
    $wbbm_cart_qty = 0;
    $cart_items = WC()->cart->get_cart();
    if (count($cart_items) > 0) {
        foreach ($cart_items as $cart_item) {
            $post_id = isset($cart_item['wbbm_bus_id']) ? $cart_item['wbbm_bus_id'] : 0;
            $date = isset($cart_item['wbbm_journey_date']) ? $cart_item['wbbm_journey_date'] : '';
            if (get_post_type($post_id) == 'wbbm_bus' && $bus_id == $post_id && strtotime(wbbm_convert_date_to_php($date)) == strtotime(wbbm_convert_date_to_php($date_var))) {
                $adult_qty = isset($cart_item['wbbm_total_adult_qt']) ? (int) $cart_item['wbbm_total_adult_qt'] : 0;
                $child_qty = isset($cart_item['wbbm_total_child_qt']) ? (int) $cart_item['wbbm_total_child_qt'] : 0;
                $infant_qty = isset($cart_item['wbbm_total_infant_qt']) ? (int) $cart_item['wbbm_total_infant_qt'] : 0;
                $wbbm_cart_qty += $adult_qty + $child_qty + $infant_qty;
            }
        }
    }
    return $wbbm_cart_qty;
}

// Get intermediate available seat
function wbbm_intermidiate_available_seat($start, $end, $date, $eid = null): int
{
    $post_id = $eid ?: get_the_ID();
    $values = get_post_custom($post_id);
    $total_seat = (int) $values['wbbm_total_seat'][0];
    $sold_seat = (int) wbbm_get_available_seat_new($post_id, sanitize_text_field($start), sanitize_text_field($end), $date);
    return max(0, $total_seat - $sold_seat);
}

// Boarding dropping time
function wbbm_boarding_dropping_time($drop_time = '', $return = '')
{
    $boarding = mage_get_isset('bus_start_route');
    $dropping = mage_get_isset('bus_end_route');
    $boarding_time = get_post_meta(get_the_ID(), 'wbbm_bus_bp_stops', true);
    $dropping_time = get_post_meta(get_the_ID(), 'wbbm_bus_next_stops', true);
    $start = $return ? $dropping : $boarding;
    $end = $return ? $boarding : $dropping;

    if (is_array($boarding_time) && is_array($dropping_time)) {
        foreach ($boarding_time as $boarding_stop) {
            if ($boarding_stop['wbbm_bus_bp_stops_name'] === $start) {
                foreach ($dropping_time as $dropping_stop) {
                    if ($dropping_stop['wbbm_bus_next_stops_name'] === $end) {
                        $start_time = $boarding_stop['wbbm_bus_bp_start_time'];
                        $end_time = $dropping_stop['wbbm_bus_next_end_time'];
                        return $drop_time ? $end_time : $start_time;
                    }
                }
            }
        }
    }
    return false;
}

// Return fare per ticket
function mage_seat_price($id, $start, $end, $seat_type, $roundtrip = false)
{
    $price = get_post_meta($id, 'wbbm_bus_prices', true);
    if (is_array($price) && count($price) > 0) {
        foreach ($price as $val) {
            if ($val['wbbm_bus_bp_price_stop'] === $start && $val['wbbm_bus_dp_price_stop'] === $end) {
                $ticket_type = '';
                switch ($seat_type) {
                    case 'infant':
                        $ticket_type = 'wbbm_bus_price_infant';
                        break;
                    case 'child':
                        $ticket_type = 'wbbm_bus_price_child';
                        break;
                    case 'entire':
                        $ticket_type = 'wbbm_bus_price_entire';
                        break;
                    default:
                        $ticket_type = 'wbbm_bus_price';
                }

                if ($roundtrip) {
                    $roundtrip_price = $ticket_type . '_roundtrip';
                    return isset($val[$roundtrip_price]) && $val[$roundtrip_price] > 0 ? $val[$roundtrip_price] : (isset($val[$ticket_type]) ? $val[$ticket_type] : 0);
                } else {
                    return isset($val[$ticket_type]) && $val[$ticket_type] > 0 ? $val[$ticket_type] : 0;
                }
            }
        }
    }
    return 0;
}



// Return Discount
function wbbm_cart_has_opposite_route($c_start, $c_stop, $c_j_date, $return = false, $current_r_date = null)
{
    global $woocommerce;

    $items = $woocommerce->cart->get_cart();
    if (count($items) > 0) {
        $wbtm_start_stops_current = sanitize_text_field($c_start);
        $wbtm_end_stops_current = sanitize_text_field($c_stop);
        $journey_date_current = mage_wp_date(sanitize_text_field($c_j_date), 'Y-m-d');

        foreach ($items as $item => $value) {
            if (isset($value['is_return']) && $value['is_return'] == 1) {
                return 0;
            }
        }

        if ($journey_date_current) {
            $journey_date_current = new DateTime($journey_date_current);
        }

        if ($current_r_date) {
            $current_r_date = mage_wp_date(sanitize_text_field($current_r_date), 'Y-m-d');
            $current_r_date = new DateTime($current_r_date);
        }

        foreach ($items as $item => $value) {
            if (isset($value['wbbm_journey_date'])) {
                $cart_j_date = mage_wp_date($value['wbbm_journey_date'], 'Y-m-d');
                $cart_j_date = new DateTime($cart_j_date);
            }

            if ($return && $current_r_date) { // Return
                if ($wbtm_start_stops_current == $value['wbbm_start_stops'] && $wbtm_end_stops_current == $value['wbbm_end_stops']) {
                    return 1;
                } else {
                    return 0;
                }
            } else { // Not return
                if ($wbtm_start_stops_current == $value['wbbm_end_stops'] && $wbtm_end_stops_current == $value['wbbm_start_stops']) {
                    return 1;
                } else {
                    return 0;
                }
            }
        }
    }
    return 0;
}

// Convert 24 to 12 hour time format
function wbbm_time_24_to_12($time)
{
    $t = '';
    if ($time && strpos($time, ':') !== false) {
        $t = explode(':', sanitize_text_field($time));
        $tm = ((int)$t[0] < 12) ? 'am' : 'pm';
        if ($t[0] > 12) {
            $tt = (int)$t[0] - 12;
            $t = $tt . ':' . $t[1] . ' ' . $tm;
        } elseif ($t[0] == '00' || $t[0] == '24') {
            $t = '12' . ':' . $t[1] . ' am';
        } else {
            $t = $t[0] . ':' . $t[1] . ' ' . $tm;
        }
    }

    return $t;
}

// Convert date format according to WP date format
function mage_wp_date($date, $format = false)
{
    $wp_date_format = get_option('date_format');
    $date = mage_date_format_issue($date);

    if ($date && $format) {
        $date = gmdate($format, strtotime($date));
        return $date;
    }

    if ($date && $wp_date_format) {
        $date = gmdate($wp_date_format, strtotime($date));
    }

    return $date;
}

function mage_date_format_issue($date)
{
    $date_format = get_option('date_format');
    $date = sanitize_text_field($date);

    if ($date) {
        if ($date_format == 'm/d/Y') {
            $date = str_replace('-', '/', $date);
        }

        if ($date_format == 'd/m/Y') {
            $date = str_replace('/', '-', $date);
        }
    }
    return $date;
}

// Get Bus Categories list
function wbbm_get_bus_categories()
{
    $terms = get_terms(array(
        'taxonomy' => 'wbbm_bus_cat',
        'hide_empty' => false,
    ));
    $output = array();
    if (!empty($terms)) {
        foreach ($terms as $term) {
            $output[$term->term_id] = esc_html($term->name);
        }
    }
    return $output;
}

// Global Color CSS Enqueue
add_action('wp_footer', 'wbbm_global_css_func');
if (!function_exists('wbbm_global_css_func')) {
    function wbbm_global_css_func()
    {
        $search_button_bg_color = wbbm_get_option('wbbm_search_button_bg_color', 'wbbm_style_setting_sec');
        $search_button_hover_bg_color = wbbm_get_option('wbbm_search_button_hover_bg_color', 'wbbm_style_setting_sec');
        $wbbm_cart_table_bg_color = wbbm_get_option('wbbm_cart_table_bg_color', 'wbbm_style_setting_sec');
        $wbbm_cart_table_text_color = wbbm_get_option('wbbm_cart_table_text_color', 'wbbm_style_setting_sec');
        $wbbm_sub_total_bg_color = wbbm_get_option('wbbm_sub_total_bg_color', 'wbbm_style_setting_sec');

        if ($search_button_bg_color || $search_button_hover_bg_color) :
            echo '<style>';
            if ($search_button_bg_color) :
                echo '.wbbm-bus-lists a, button.mage_button, .mage-search-brief-row .mage-bus-detail-action {';
                echo 'background-color:' . esc_attr($search_button_bg_color) . ';';
                echo '}';
            endif;
            if ($search_button_hover_bg_color) :
                echo '.wbbm-bus-lists a:hover, button.mage_button:hover, .mage-search-brief-row .mage-bus-detail-action:hover {';
                echo 'background-color:' . esc_attr($search_button_hover_bg_color) . ';';
                echo '}';
            endif;
            if ($wbbm_cart_table_bg_color) :
                echo '.mage-bus-booking-wrapper .mage_bus_info, .mage-bus-booking-wrapper .mage_price_info, .mage_bus_info, .mage_price_info {';
                echo 'background-color:' . esc_attr($wbbm_cart_table_bg_color) . ';';
                echo '}';
            endif;
            if ($wbbm_cart_table_text_color) :
                echo '.mage-bus-booking-wrapper .mage_bus_info, .mage-bus-booking-wrapper .mage_price_info, .mage_search_list .mage_bus_info h3 a, .mage_bus_info, .mage_price_info {';
                echo 'color:' . esc_attr($wbbm_cart_table_text_color) . ';';
                echo '}';
            endif;
            if ($wbbm_sub_total_bg_color) :
                echo '.mage_search_list .mage_book_now_area .mage_sub_total span:nth-child(1) {';
                echo 'background-color:' . esc_attr($wbbm_sub_total_bg_color) . ';';
                echo '}';
            endif;
            echo '</style>';
        endif;
    }
}

// Add ID column to bus categories
add_filter('manage_edit-wbbm_bus_cat_columns', 'wbbm_bus_cat_custom_column');
function wbbm_bus_cat_custom_column($columns)
{
    $columns['id'] = 'ID';
    return $columns;
}

add_filter('manage_wbbm_bus_cat_custom_column', 'wbbm_bus_cat_custom_column_callback', 10, 3);
function wbbm_bus_cat_custom_column_callback($content, $column_name, $term_id)
{
    switch ($column_name) {
        case 'id':
            $content = intval($term_id);
            break;
    }
    return $content;
}

// Add ID column to bus stops
add_filter('manage_edit-wbbm_bus_stops_columns', 'wbbm_bus_stops_custom_column');
function wbbm_bus_stops_custom_column($columns)
{
    $columns['id'] = 'ID';
    return $columns;
}

add_filter('manage_wbbm_bus_stops_custom_column', 'wbbm_bus_stops_custom_column_callback', 10, 3);
function wbbm_bus_stops_custom_column_callback($content, $column_name, $term_id)
{
    switch ($column_name) {
        case 'id':
            $content = intval($term_id);
            break;
    }
    return $content;
}


/*********************************************
 * Function: Add ID column to bus pickup point
 **********************************************/
add_filter('manage_edit-wbbm_bus_pickpoint_columns', 'wbbm_bus_pickpoint_custom_column');
function wbbm_bus_pickpoint_custom_column($columns)
{
    $columns['id'] = 'ID';
    return $columns;
}

add_filter('manage_wbbm_bus_pickpoint_custom_column', 'wbbm_bus_pickpoint_custom_column_callback', 10, 3);
function wbbm_bus_pickpoint_custom_column_callback($content, $column_name, $term_id)
{
    switch ($column_name) {
        case 'id':
            $content = intval($term_id);
            break;
    }
    return $content;
}

/**************************************
 * Function for extra service
 **************************************/
function wbbm_extra_services_section($bus_id)
{
    $start = mage_get_isset('bus_start_route');
    $end = mage_get_isset('bus_end_route');
    $j_date = mage_get_isset('j_date');

    $is_enable_extra_services = get_post_meta($bus_id, 'show_extra_service', true);
    $extra_services = get_post_meta($bus_id, 'mep_events_extra_prices', true);

    if ($extra_services && $is_enable_extra_services === 'yes') :
    ?>
        <div class="wbbm_extra_service_wrap">
            <h4 class="mar_b bor_tb"><?php echo esc_html(__('Extra Service', 'bus-booking-manager')); ?></h4>
            <table class='wbbm_extra_service_table'>
                <thead>
                    <tr>
                        <td align="left"><?php echo esc_html(__('Name:', 'bus-booking-manager')); ?></td>
                        <td class="mage_text_center"><?php echo esc_html(__('Quantity:', 'bus-booking-manager')); ?></td>
                        <td class="mage_text_center"><?php echo esc_html(__('Price:', 'bus-booking-manager')); ?></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count_extra = 0;
                    foreach ($extra_services as $field) {
                        $total_extra_service = isset($field['option_qty']) ? (int)$field['option_qty'] : 0;
                        $qty_type = isset($field['option_qty_type']) ? sanitize_text_field($field['option_qty_type']) : '';
                        $total_sold = 0;

                        $ext_left = ($total_extra_service - $total_sold);
                        if (!isset($field['option_name']) || !isset($field['option_price'])) {
                            continue;
                        }
                        $actual_price = wp_strip_all_tags(wc_price($field['option_price']));
                        $data_price = floatval(str_replace(array(get_woocommerce_currency_symbol(), wc_get_price_thousand_separator(), wc_get_price_decimal_separator()), '', $actual_price));
                    ?>

                        <tr data-total="0">
                            <td align="left"><?php echo esc_html($field['option_name']); ?>
                                <div class="xtra-item-left"><?php echo esc_html($ext_left); ?> <?php echo esc_html(__('Left:', 'bus-booking-manager')); ?></div>
                            </td>

                            <td>
                                <?php
                                if ($ext_left > 0) {
                                    if ($qty_type === 'dropdown') { ?>
                                        <select name="extra_service_qty[]" id="eventpxtp_<?php echo esc_attr($count_extra); ?>" class='extra-qty-box mage_form_full' data-price='<?php echo esc_attr($data_price); ?>'>
                                            <option value=""><?php echo esc_html(__('Select extra service', 'bus-booking-manager')); ?></option>
                                            <?php for ($i = 1; $i <= $ext_left; $i++) { ?>
                                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i . ' ' . $field['option_name']); ?></option>
                                            <?php } ?>
                                        </select>
                                    <?php } else { ?>
                                        <div class="mage_es_form_qty">
                                            <div class="mage_flex mage_es_qty_minus"><i class="fa fa-minus"></i></div>
                                            <input size="4" inputmode="numeric" type="number" class='extra-qty-box mage_form_full' step='1' name='extra_service_qty[]' data-price='<?php echo wp_kses_post(wbbm_get_price_including_tax($bus_id, $data_price)); ?>' value='0' min="0" max="<?php echo esc_html($ext_left); ?>">
                                            <div class="mage_flex mage_es_qty_plus"><i class="fa fa-plus"></i></div>
                                        </div>
                                <?php }
                                } else {
                                    echo esc_html(__('Not Available', 'bus-booking-manager'));
                                } ?>
                            </td>
                            <td class="mage_text_center">
                                <?php
                                $user = get_current_user_id();
                                $WBBM_USER_ROLES = $user ? wp_get_current_user()->roles : array();

                                if (in_array('bus_sales_agent', $WBBM_USER_ROLES, true)) {
                                    echo '<input class="extra_service_per_price" type="text" name="extra_service_price[]" value="' . wp_kses_post(wbbm_get_price_including_tax($bus_id, $field['option_price'])) . '" style="width: 80px"/>';

                                    if ($ext_left > 0) { ?>
                                        <p style="display: none;" class="price_jq"><?php echo esc_html($data_price > 0 ? $data_price : 0); ?></p>
                                        <input type="hidden" name='extra_service_name[]' value='<?php echo esc_attr($field['option_name']); ?>'>
                                    <?php }
                                } else {
                                    echo wp_kses_post(wc_price(wbbm_get_price_including_tax($bus_id, $field['option_price'])));

                                    if ($ext_left > 0) { ?>
                                        <p style="display: none;" class="price_jq"><?php echo esc_html($data_price > 0 ? $data_price : 0); ?></p>
                                        <input type="hidden" name='extra_service_name[]' value='<?php echo esc_attr($field['option_name']); ?>'>
                                        <input type="hidden" name='extra_service_price[]' value='<?php echo esc_attr($field['option_price']); ?>'>
                                <?php }
                                }
                                ?>
                            </td>
                        </tr>
                    <?php
                        $count_extra++;
                    }
                    ?>
                </tbody>
            </table>
        </div>
<?php
    endif;
}

// Extra services END

function wbbm_get_price_including_tax($bus, $price, $args = array())
{
    $args = wp_parse_args($args, array(
        'qty' => '',
        'price' => '',
    ));

    $_product = get_post_meta($bus, 'link_wc_product', true) ? get_post_meta($bus, 'link_wc_product', true) : $bus;
    $qty = !empty($args['qty']) ? max(0.0, (float)$args['qty']) : 1;
    $product = wc_get_product($_product);
    $tax_with_price = get_option('woocommerce_tax_display_shop');

    if ($price === '') {
        return '';
    } elseif (empty($qty)) {
        return 0.0;
    }

    $line_price = $price * $qty;
    $return_price = $line_price;

    if ($product && $product->is_taxable()) {
        if (!wc_prices_include_tax()) {
            $tax_rates = WC_Tax::get_rates($product->get_tax_class());
            $taxes = WC_Tax::calc_tax($line_price, $tax_rates, false);

            $taxes_total = ('yes' === get_option('woocommerce_tax_round_at_subtotal')) ? array_sum($taxes) : array_sum(array_map('wc_round_tax_total', $taxes));
            $return_price = $tax_with_price === 'excl' ? round($line_price, wc_get_price_decimals()) : round($line_price + $taxes_total, wc_get_price_decimals());
        } else {
            $tax_rates = WC_Tax::get_rates($product->get_tax_class());
            $base_tax_rates = WC_Tax::get_base_tax_rates($product->get_tax_class('unfiltered'));

            if (!empty(WC()->customer) && WC()->customer->get_is_vat_exempt()) {
                // phpcs:ignore WordPress.NamingConventions.ValidHookName
                $remove_taxes = apply_filters('woocommerce_adjust_non_base_location_prices', true) ? WC_Tax::calc_tax($line_price, $base_tax_rates, true) : WC_Tax::calc_tax($line_price, $tax_rates, true);

                $remove_taxes_total = ('yes' === get_option('woocommerce_tax_round_at_subtotal')) ? array_sum($remove_taxes) : array_sum(array_map('wc_round_tax_total', $remove_taxes));
                $return_price = round($line_price - $remove_taxes_total, wc_get_price_decimals());
            } else {
                $base_taxes = WC_Tax::calc_tax($line_price, $base_tax_rates, true);
                $modded_taxes = WC_Tax::calc_tax($line_price - array_sum($base_taxes), $tax_rates, false);

                $base_taxes_total = ('yes' === get_option('woocommerce_tax_round_at_subtotal')) ? array_sum($base_taxes) : array_sum(array_map('wc_round_tax_total', $base_taxes));
                $modded_taxes_total = ('yes' === get_option('woocommerce_tax_round_at_subtotal')) ? array_sum($modded_taxes) : array_sum(array_map('wc_round_tax_total', $modded_taxes));

                $return_price = $tax_with_price === 'excl' ? round($line_price - $base_taxes_total, wc_get_price_decimals()) : round($line_price - $base_taxes_total + $modded_taxes_total, wc_get_price_decimals());
            }
        }
    }
    // phpcs:ignore WordPress.NamingConventions.ValidHookName
    return apply_filters('woocommerce_get_price_including_tax', $return_price, $qty, $product);
}

// Seat booked status
function wbbm_seat_booked_on_status()
{
    $seat_booked_status = wbbm_get_option('wbbm_seat_booked_on_order_status', 'wbbm_general_setting_sec', array(1, 2));
    return is_array($seat_booked_status) ? implode(',', array_map('sanitize_text_field', $seat_booked_status)) : '';
}

/******************************************
Update Seat Book On Status Option value
Developer: Ariful
 *******************************************/
add_action('wp_loaded', 'wbbm_update_seat_book_on_status_global_settings');
function wbbm_update_seat_book_on_status_global_settings()
{
    $general_settings = is_array(get_option('wbbm_general_setting_sec')) ? maybe_unserialize(get_option('wbbm_general_setting_sec')) : array();
    $seat_book_on_status_arr = array(
        'wbbm_seat_booked_on_order_status' => array(
            1 => 1,
            2 => 2,
        ),
    );

    if (!array_key_exists('wbbm_seat_booked_on_order_status', $general_settings)) {
        $marged_arr = array_merge($general_settings, $seat_book_on_status_arr);
        update_option('wbbm_general_setting_sec', $marged_arr);
    }
}

/*
 * Buffer time calculation. If return true that means bus will show in search list.
 * If return false that means this bus will ignore from search result
 * @param string bp_time: Boarding point time
 * @param date date
 * @return bool
 * */
function wbbm_buffer_time_calculation($bp_time, $date)
{
    $is_allow = false;
    $buffer_time_from_setting = floatval(wbbm_get_option('wbbm_buffer_time', 'wbbm_general_setting_sec', 0));
    $bus_start_time = gmdate('H:i:s', strtotime(sanitize_text_field($bp_time)));

    if ($buffer_time_from_setting > 0) {
        $start_bus = sanitize_text_field($date) . ' ' . $bus_start_time;
        $diff = round((strtotime($start_bus) - strtotime(current_time('Y-m-d H:i:s'))) / 60, 1); // In Minute

        if ($diff >= $bus_buffer_time) {
            $is_allow = true;
        }
    } else {
        $is_allow = true;
    }
    return $is_allow;
}
