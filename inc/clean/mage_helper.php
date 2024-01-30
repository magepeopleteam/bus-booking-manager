<?php
function mage_get_isset($parameter) {
    return isset($_GET[$parameter]) ? strip_tags($_GET[$parameter]) : '';
}

function mage_qty_box($price,$name, $return) {
    $date = $return ? mage_get_isset('r_date') : mage_get_isset('j_date');
    $available_seat = wbbm_intermidiate_available_seat($_GET['bus_start_route'], $_GET['bus_end_route'], wbbm_convert_date_to_php($date));
    if ($available_seat > 0) {

        if($name == 'child_quantity') {
            $ticket_title = wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec', __('Child', 'bus-booking-manager'));
            $ticket_type = 'child';
        } elseif($name == 'infant_quantity') {
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
                   class="mage_form mage_seat_qty ra_seat_qty"
                   data-ticket-title="<?php echo $ticket_title.' '.__('Passenger info', 'bus-booking-manager'); ?>"
                   data-ticket-type="<?php echo $ticket_type; ?>"
                   data-price="<?php echo $price; ?>"
                   name="<?php echo $name; ?>"
                   value="0"
                   min="0"
                   max="<?php echo $available_seat; ?>"
                   <?php echo ($ticket_type === 'adult' ? 'required' : '') ?>
            />
            <div class="mage_flex mage_qty_inc"><span class="fa fa-plus"></span></div>
        </div>
        <?php
    }
}

function wbbm_entire_switch($price,$name, $return){
    $date = $return ? mage_get_isset('r_date') : mage_get_isset('j_date');
    $available_seat = wbbm_intermidiate_available_seat($_GET['bus_start_route'], $_GET['bus_end_route'], wbbm_convert_date_to_php($date));

        if($name == 'entire_quantity') {
            $ticket_title = wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec', __('Entire', 'bus-booking-manager'));
            $ticket_type = 'entire';
        }   
    ?>
    <div class="wbbm_entire_switch_wrapper" data-entire-price="<?php echo $price; ?>">
    <label class="switch">
        <input  type="checkbox" 
                id="wbbm_entire_bus" 
                name="<?php echo $name; ?>"
                value="0"
                data-ticket-title="<?php echo $ticket_title.' '.__('Passenger info', 'bus-booking-manager'); ?>"
                data-ticket-type="<?php echo $ticket_type; ?>"
                data-price="<?php echo $price; ?>"
                class="mage_form"
        />
        <span class="slider round"></span>
    </label>
    </div>
    <?php
}

//print hidden input field
function hidden_input_field($name, $value) {
    echo '<input type="hidden" name="' . $name . '" value="' . $value . '"/>';
}

//return cart qty
function cart_qty($name) {
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

//get route list
function mage_route_list($drop = false) {
    $routes = get_terms(array(
        'taxonomy' => 'wbbm_bus_stops',
        'hide_empty' => false,
    ));

    $search_form_dropdown_b_color = wbbm_get_option('wbbm_search_form_dropdown_b_color', 'wbbm_style_setting_sec');

    $search_form_dropdown_text_color = wbbm_get_option('wbbm_search_form_dropdown_t_color', 'wbbm_style_setting_sec');
    $search_form_dropdown_text_color = $search_form_dropdown_text_color ? $search_form_dropdown_text_color : '';

    echo '<div class="mage_input_select_list"'.($drop ? "id=wbtm_dropping_point_list" : "").' '; if($search_form_dropdown_b_color){ echo 'style="background-color:'.$search_form_dropdown_b_color.'"'; } echo '><ul>';
    foreach ($routes as $route) {
        echo '<li '; if($search_form_dropdown_text_color){ echo 'style="color:'.$search_form_dropdown_text_color.'"'; } echo ' data-route="' . $route->name . '"><span class="fa fa-map-marker"></span>' . $route->name . '</li>';
    }
    echo '</ul></div>';
}

// Bus route list Ajax
add_action('wp_ajax_wbtm_load_dropping_point', 'wbtm_load_dropping_point');
add_action('wp_ajax_nopriv_wbtm_load_dropping_point', 'wbtm_load_dropping_point');
function wbtm_load_dropping_point()
{
    $boardingPoint = strip_tags($_POST['boarding_point']);
    $category = get_term_by('name', $boardingPoint, 'wbbm_bus_stops');
    $allStopArr = get_terms(array(
        'taxonomy' => 'wbbm_bus_stops',
        'hide_empty' => false
    ));
    $dropingarray = get_term_meta($category->term_id, 'wbbm_bus_routes_name_list', true) ? maybe_unserialize(get_term_meta($category->term_id, 'wbbm_bus_routes_name_list', true)) : array();

    if (sizeof($dropingarray) > 0) {
        foreach ($dropingarray as $dp) {
            $name = $dp['wbbm_bus_routes_name'];
            echo "<li data-route='$name'><span class='fa fa-map-marker'></span>$name</li>";
        }
    } else {
        foreach ($allStopArr as $dp) {
            $name = $dp->name;
            echo "<li data-route='$name'><span class='fa fa-map-marker'></span>$name</li>";
        }
    }
    die();
}

//query for bus list
function mage_bus_list_query($start, $end) {
    $start = mage_get_isset($start);
    $end = mage_get_isset($end);
    return array(
        'post_type' => array('wbbm_bus'),
        'posts_per_page' => -1,
        'order' => 'ASC',
        'orderby' => 'meta_value',
        'post_status' => array('publish'),
        // 'meta_key' => 'wbbm_bus_start_time',
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

//odd range check
function mage_odd_list_check($return) {
    $start_date = strtotime(get_post_meta(get_the_id(), 'wbtm_od_start', true));
    $end_date = strtotime(get_post_meta(get_the_id(), 'wbtm_od_end', true));
    $date = mage_get_isset($return ? 'r_date' : 'j_date');
    $date = mage_wp_date($date, 'Y-m-d');

    return (($start_date <=$date ) && ($end_date>=$date) ) ? false : true;
}

//off day check
function mage_off_day_check($return) {
    // $current_day = 'od_' . date('D', strtotime($return ? mage_wp_date(mage_get_isset('r_date')) : mage_wp_date(mage_get_isset('j_date'))));
    // return get_post_meta(get_the_id(), $current_day, true) == 'yes' ? false : true;

    $get_day = null;
    $id = get_the_ID();
    $j_date = $return ? mage_wp_date(mage_get_isset('r_date'), 'Y-m-d') : mage_wp_date(mage_get_isset('j_date'), 'Y-m-d');
    
    $weekly_offday = get_post_meta($id, 'weekly_offday', true) ?: array();
    if ($j_date) {
        if ($return) {
            // $weekly_offday = get_post_meta($id, 'weekly_offday_return', true) ?: array();
            $j_date_day = strtolower(date('N', strtotime($j_date)));
            if (in_array($j_date_day, $weekly_offday)) {
                $get_day = 'yes';
            }
        } else {
            $j_date_day = strtolower(date('N', strtotime($j_date)));
            if (in_array($j_date_day, $weekly_offday)) {
                $get_day = 'yes';
            }
        }

        if ($get_day == 'yes') {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

//check already in cart
function mage_find_product_in_cart() {
    if( ! is_admin() ) { 
        $product_id = get_the_id();
        $cart = WC()->cart->get_cart();
        foreach ($cart as $cart_item) {
            if ($cart_item['product_id'] == $product_id) {
                return true;
            }
        }
    return false;
    }
}

// get available seat
function mage_available_seat($date) { echo "<pre>";print_r($date);echo "</pre>";exit;
    $values = get_post_custom(get_the_id());
    $total_seat = $values['wbbm_total_seat'][0];
    $sold_seat = wbbm_get_available_seat(get_the_id(), $date);
    return ($total_seat - $sold_seat) > 0 ? ($total_seat - $sold_seat) : 0;
}

// Get intermidiate available seat
function wbbm_intermidiate_available_seat($start, $end, $date,$eid=null): int
{
    // $post_id = get_the_id()?get_the_id():$eid;
    $post_id = $eid?:get_the_id();
    $values = get_post_custom($post_id);
    $total_seat = (int)$values['wbbm_total_seat'][0];
    $sold_seat = (int)wbbm_get_available_seat_new($post_id, $start, $end, $date); 
    return ($total_seat - $sold_seat) > 0 ? ($total_seat - $sold_seat) : 0;
}

function boarding_dropping_time($drop_time = '', $return = '') {
    $boarding = mage_get_isset('bus_start_route');
    $dropping = mage_get_isset('bus_end_route');
    $boarding_time = get_post_meta(get_the_id(), 'wbbm_bus_bp_stops', true);
    $dropping_time = get_post_meta(get_the_id(), 'wbbm_bus_next_stops', true);
    $start = $return ? $dropping : $boarding;
    $end = $return ? $boarding : $dropping;

    foreach ($boarding_time as $boarding) {
        if ($boarding['wbbm_bus_bp_stops_name'] == $start) {
            foreach ($dropping_time as $dropping) {
                if ($dropping['wbbm_bus_next_stops_name'] == $end) {
                    $start_time = $boarding['wbbm_bus_bp_start_time'];
                    $end_time = $dropping['wbbm_bus_next_end_time'];
                    return $drop_time ? $end_time : $start_time;
                }
            }
        }
    }
    return false;
}

//return fare per ticket
function mage_seat_price($id,$start,$end,$seat_type, $roundtrip = false) {
    $price = get_post_meta($id, 'wbbm_bus_prices', true);
    if (is_array($price) && sizeof($price) > 0) {
        foreach ($price as $key => $val) {
            if ($val['wbbm_bus_bp_price_stop'] == $start && $val['wbbm_bus_dp_price_stop'] == $end) {
                // $ticket_type = $adult ? 'wbbm_bus_price' : 'wbbm_bus_price_child';
                if($seat_type == 'infant') {
                    $ticket_type = 'wbbm_bus_price_infant';
                } elseif($seat_type == 'child') {
                    $ticket_type = 'wbbm_bus_price_child';
                }elseif($seat_type == 'entire') {
                    $ticket_type = 'wbbm_bus_price_entire';
                } else {
                    $ticket_type = 'wbbm_bus_price';
                }

                if($roundtrip) {
                    $r_p = $ticket_type.'_roundtrip';
                    if(array_key_exists($r_p, $val) && $val[$r_p] > 0) {
                        return $val[$r_p];
                    } else {
                        return (array_key_exists($ticket_type, $val) && $val[$ticket_type] > 0) ? $val[$ticket_type] : 0;
                    }
                } else {
                    return (array_key_exists($ticket_type, $val) && $val[$ticket_type] > 0) ? $val[$ticket_type] : 0;
                }
                
            }
        }
    }
    return false;
}

// Return Discount
function wbbm_cart_has_opposite_route($c_start, $c_stop, $c_j_date, $return = false, $current_r_date = null) {
    global $woocommerce;
    
    $items = $woocommerce->cart->get_cart();
    if(count($items) > 0) {

        $wbtm_start_stops_current   = $c_start;
        $wbtm_end_stops_current     = $c_stop;
        $journey_date_current       = mage_wp_date($c_j_date, 'Y-m-d');


        foreach( $items as $item => $value ) {
            if( ($value['is_return'] == 1) ) {
                return 0;
            }
        }

        if($journey_date_current) {
            $journey_date_current = new DateTime($journey_date_current);
        }

        if($current_r_date) {
            $current_r_date = mage_wp_date($current_r_date, 'Y-m-d');
            $current_r_date = new DateTime($current_r_date);
        }


        foreach( $items as $item => $value ) {

            if($value['wbbm_journey_date']) {
                $cart_j_date = mage_wp_date($value['wbbm_journey_date'], 'Y-m-d');
                $cart_j_date = new DateTime($cart_j_date);
            }

            if($return && $current_r_date) { // Return
                if( ($wbtm_start_stops_current == $value['wbbm_start_stops']) && ($wbtm_end_stops_current == $value['wbbm_end_stops']) ) {
                    return 1;
                } else {
                    return 0;
                }
            } else { // Not return
                if( ($wbtm_start_stops_current == $value['wbbm_end_stops']) && ($wbtm_end_stops_current == $value['wbbm_start_stops']) ) {
                    return 1;
                } else {
                    return 0;
                }
            }

        }
    }
}

// Convert 24 to 12 time
function wbbm_time_24_to_12($time) {
    $t = '';
    if($time && strpos($time, ':') !== false) {
        $t = explode(':', $time);
        $tm = ((int)$t[0] < 12) ? 'am' : 'pm';
        if($t[0] > 12) {
            $tt = (int)$t[0] - 12;
            $t = $tt.':'.$t[1].' '.$tm;
        } elseif ($t[0] == '00' || $t[0] == '24') {
            $t = '12'.':'.$t[1].' am';
        } else {
            $t = $t[0].':'.$t[1].' '.$tm;
        }
        // $t = $tm;
    }

    return $t;
}

// Convert date format according to wp date format
function mage_wp_date($date, $format = false) {
    $wp_date_format = get_option('date_format');

    $date = mage_date_format_issue($date);

    if($date && $format) {
        $date = date($format, strtotime($date));

        return $date;
    }


    if($date && $wp_date_format) {
        $date  = date($wp_date_format, strtotime($date));
    }

    return $date;
}
function mage_date_format_issue($date)
{
    $date_format = get_option('date_format');

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

/***********************************
 * Function: Get Bus Categories list
************************************/
function wbbm_get_bus_categories(){
    $terms = get_terms( array(
        'taxonomy' => 'wbbm_bus_cat',
        'hide_empty' => false,
    ) );
    $output = array();
    if ( !empty($terms) ) {
        foreach( $terms as $term ) {
            $output[$term->term_id] = $term->name;
        }       
    }
    return $output;   
}

/***********************************
* Function: Global Color CSS Enqueue
************************************/

add_action('wp_footer','wbbm_global_css_func');
if(!function_exists('wbbm_global_css_func')){
function wbbm_global_css_func(){
    $search_button_bg_color = wbbm_get_option('wbbm_search_button_bg_color', 'wbbm_style_setting_sec');
    $search_button_hover_bg_color = wbbm_get_option('wbbm_search_button_hover_bg_color', 'wbbm_style_setting_sec');
    $wbbm_cart_table_bg_color = wbbm_get_option('wbbm_cart_table_bg_color', 'wbbm_style_setting_sec');
    $wbbm_cart_table_text_color = wbbm_get_option('wbbm_cart_table_text_color', 'wbbm_style_setting_sec');
    $wbbm_sub_total_bg_color = wbbm_get_option('wbbm_sub_total_bg_color', 'wbbm_style_setting_sec');
    
    if($search_button_bg_color || $search_button_hover_bg_color) :
        echo '<style>';

        if($search_button_bg_color) :
        echo '.wbbm-bus-lists a, button.mage_button, .mage-search-brief-row .mage-bus-detail-action { ';
        echo !empty($search_button_bg_color) ?  'background-color:'.$search_button_bg_color.';' : '';
        echo ' } ';
        endif;

        if($search_button_hover_bg_color) :
        echo '.wbbm-bus-lists a:hover, button.mage_button:hover, .mage-search-brief-row .mage-bus-detail-action:hover { ';
        echo !empty($search_button_hover_bg_color) ?  'background-color:'.$search_button_hover_bg_color.';' : '';
        echo ' } ';
        endif;

        if($wbbm_cart_table_bg_color) :
        echo '.mage-bus-booking-wrapper .mage_bus_info, .mage-bus-booking-wrapper .mage_price_info, .mage_bus_info, .mage_price_info { ';
        echo !empty($wbbm_cart_table_bg_color) ?  'background-color:'.$wbbm_cart_table_bg_color.';' : '';
        echo ' } ';
        endif;

        if($wbbm_cart_table_text_color) :
        echo '.mage-bus-booking-wrapper .mage_bus_info, .mage-bus-booking-wrapper .mage_price_info, .mage_search_list .mage_bus_info h3 a, .mage_bus_info, .mage_price_info { ';
        echo !empty($wbbm_cart_table_text_color) ?  'color:'.$wbbm_cart_table_text_color.';' : '';
        echo ' } ';
        endif;

        if($wbbm_sub_total_bg_color) :
        echo '.mage_search_list .mage_book_now_area .mage_sub_total span:nth-child(1) { ';
        echo !empty($wbbm_sub_total_bg_color) ?  'background-color:'.$wbbm_sub_total_bg_color.';' : '';
        echo ' } ';
        endif;

        echo '</style>';
    endif;    
}
}


/***********************************
* Function: Add ID column to category
************************************/
add_filter('manage_edit-wbbm_bus_cat_columns', 'wbbm_bus_cat_custom_column');
function wbbm_bus_cat_custom_column($columns){
    $columns['id'] = 'ID';
    return $columns;
}

add_filter('manage_wbbm_bus_cat_custom_column', 'wbbm_bus_cat_custom_column_callback', 10, 3);
function wbbm_bus_cat_custom_column_callback($content, $column_name, $term_id){
    switch ( $column_name ) {
        case 'id' :
            $content = $term_id;
        break;
    }
    return $content;
}

/***********************************
* Function: Add ID column to bus stops
************************************/
add_filter('manage_edit-wbbm_bus_stops_columns', 'wbbm_bus_stops_custom_column');
function wbbm_bus_stops_custom_column($columns){
    $columns['id'] = 'ID';
    return $columns;
}

add_filter('manage_wbbm_bus_stops_custom_column', 'wbbm_bus_stops_custom_column_callback', 10, 3);
function wbbm_bus_stops_custom_column_callback($content, $column_name, $term_id){
    switch ( $column_name ) {
        case 'id' :
            $content = $term_id;
        break;
    }
    return $content;
}

/*********************************************
* Function: Add ID column to bus pickup point
**********************************************/
add_filter('manage_edit-wbbm_bus_pickpoint_columns', 'wbbm_bus_pickpoint_custom_column');
function wbbm_bus_pickpoint_custom_column($columns){
    $columns['id'] = 'ID';
    return $columns;
}

add_filter('manage_wbbm_bus_pickpoint_custom_column', 'wbbm_bus_pickpoint_custom_column_callback', 10, 3);
function wbbm_bus_pickpoint_custom_column_callback($content, $column_name, $term_id){
    switch ( $column_name ) {
        case 'id' :
            $content = $term_id;
        break;
    }
    return $content;
}

/**************************************
 * Function for extra service
 **************************************/
function wbbm_extra_services_section($bus_id)
{
    $start = isset($_GET['bus_start_route']) ? $_GET['bus_start_route'] : '';
    $end = isset($_GET['bus_end_route']) ? $_GET['bus_end_route'] : '';
    $j_date = isset($_GET['j_date']) ? $_GET['j_date'] : '';


    $is_enable_extra_services = get_post_meta($bus_id, 'show_extra_service', true);
    $extra_services = get_post_meta($bus_id, 'mep_events_extra_prices', true);

    if ($extra_services && $is_enable_extra_services == 'yes') :

    ?>
        <div class="wbbm_extra_service_wrap">
            <h4 class="mar_b bor_tb"><?php echo __('Extra Service', 'bus-booking-manager'); ?></h4>
            <table class='wbbm_extra_service_table'>
                <thead>
                    <tr>
                        <td align="left"><?php echo __('Name:', 'bus-booking-manager'); ?></td>
                        <td class="mage_text_center"><?php echo __('Quantity:', 'bus-booking-manager'); ?></td>
                        <td class="mage_text_center"><?php echo __('Price:', 'bus-booking-manager'); ?></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count_extra = 0;
                    foreach ($extra_services as $field) {
                        $total_extra_service = (int)$field['option_qty'];
                        $qty_type = $field['option_qty_type'];
                        $total_sold = 0;

                        $ext_left = ($total_extra_service - $total_sold);
                        if (!isset($field['option_name']) || !isset($field['option_price'])) {
                            continue;
                        }
                        $actual_price = strip_tags(wc_price($field['option_price']));
                        $data_price = str_replace(get_woocommerce_currency_symbol(), '', $actual_price);
                        $data_price = str_replace(wc_get_price_thousand_separator(), '', $data_price);
                        $data_price = str_replace(wc_get_price_decimal_separator(), '.', $data_price);
                    ?>

                        <tr data-total="0">
                            <td align="Left"><?php echo $field['option_name']; ?>
                                <div class="xtra-item-left"><?php echo $ext_left; ?>
                                    <?php _e('Left:', 'bus-booking-manager'); ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                if ($ext_left > 0) {
                                    if ($qty_type == 'dropdown') { ?>
                                        <select name="extra_service_qty[]" id="eventpxtp_<?php echo $count_extra;
                                                                                            ?>" class='extra-qty-box mage_form_full' data-price='<?php echo $data_price; ?>'>
                                            <option value=""><?php _e('Select extra service', 'bus-booking-manager') ?></option>
                                            <?php for ($i = 1; $i <= $ext_left; $i++) { ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $field['option_name']; ?></option>
                                            <?php } ?>
                                        </select>
                                    <?php } else { ?>
                                        <div class="mage_es_form_qty">
                                            <div class="mage_flex mage_es_qty_minus"><i class="fa fa-minus"></i></div>
                                            <input size="4" inputmode="numeric" type="number" class='extra-qty-box mage_form_full' step='1' name='extra_service_qty[]' data-price='<?php echo wbbm_get_price_including_tax($bus_id, $data_price); ?>' value='0' min="0" max="<?php echo $ext_left; ?>">
                                            <div class="mage_flex mage_es_qty_plus"><i class="fa fa-plus"></i></div>
                                        </div>
                                <?php }
                                } else {
                                    echo __('Not Available', 'bus-booking-manager');
                                } ?>
                            </td>
                            <td class="mage_text_center">
                                <?php
                                $user = get_current_user_id();
                                $user_roles = array();
                                if ($user) {
                                    $user_meta = get_userdata($user);
                                    $user_roles = $user_meta->roles;
                                }

                                if (in_array('bus_sales_agent', $user_roles, true)) {
                                    echo '<input class="extra_service_per_price" type="text" name="extra_service_price[]" value="' . wbbm_get_price_including_tax($bus_id, $field['option_price']) . '" style="width: 80px"/>';
                                    if ($ext_left > 0) { ?>
                                        <p style="display: none;" class="price_jq"><?php echo $data_price > 0 ? $data_price : 0; ?></p>
                                        <input type="hidden" name='extra_service_name[]' value='<?php echo $field['option_name']; ?>'>
                                    <?php }
                                } else {
                                    echo wc_price(wbbm_get_price_including_tax($bus_id, $field['option_price']));
                                    if ($ext_left > 0) { ?>
                                        <p style="display: none;" class="price_jq"><?php echo $data_price > 0 ? $data_price : 0; ?></p>
                                        <input type="hidden" name='extra_service_name[]' value='<?php echo $field['option_name']; ?>'>
                                        <input type="hidden" name='extra_service_price[]' value='<?php echo $field['option_price']; ?>'>
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

    $args = wp_parse_args(
        $args,
        array(
            'qty' => '',
            'price' => '',
        )
    );

    $_product = get_post_meta($bus, 'link_wc_product', true) ? get_post_meta($bus, 'link_wc_product', true) : $bus;

    $qty = '' !== $args['qty'] ? max(0.0, (float)$args['qty']) : 1;

    $product = wc_get_product($_product);


    $tax_with_price = get_option('woocommerce_tax_display_shop');


    if ('' === $price) {
        return '';
    } elseif (empty($qty)) {
        return 0.0;
    }

    $line_price = $price * $qty;
    $return_price = $line_price;

    if ($product->is_taxable()) {


        if (!wc_prices_include_tax()) {
// echo get_option( 'woocommerce_prices_include_tax' );
            $tax_rates = WC_Tax::get_rates($product->get_tax_class());
            $taxes = WC_Tax::calc_tax($line_price, $tax_rates, false);

            // print_r($tax_rates);

            if ('yes' === get_option('woocommerce_tax_round_at_subtotal')) {

                $taxes_total = array_sum($taxes);

            } else {

                $taxes_total = array_sum(array_map('wc_round_tax_total', $taxes));
            }

            $return_price = $tax_with_price == 'excl' ? round($line_price, wc_get_price_decimals()) : round($line_price + $taxes_total, wc_get_price_decimals());


        } else {


            $tax_rates = WC_Tax::get_rates($product->get_tax_class());
            $base_tax_rates = WC_Tax::get_base_tax_rates($product->get_tax_class('unfiltered'));

            /**
             * If the customer is excempt from VAT, remove the taxes here.
             * Either remove the base or the user taxes depending on woocommerce_adjust_non_base_location_prices setting.
             */
            if (!empty(WC()->customer) && WC()->customer->get_is_vat_exempt()) { // @codingStandardsIgnoreLine.
                $remove_taxes = apply_filters('woocommerce_adjust_non_base_location_prices', true) ? WC_Tax::calc_tax($line_price, $base_tax_rates, true) : WC_Tax::calc_tax($line_price, $tax_rates, true);

                if ('yes' === get_option('woocommerce_tax_round_at_subtotal')) {
                    $remove_taxes_total = array_sum($remove_taxes);
                } else {
                    $remove_taxes_total = array_sum(array_map('wc_round_tax_total', $remove_taxes));
                }

                // $return_price = round( $line_price, wc_get_price_decimals() );
                $return_price = round($line_price - $remove_taxes_total, wc_get_price_decimals());
                /**
                 * The woocommerce_adjust_non_base_location_prices filter can stop base taxes being taken off when dealing with out of base locations.
                 * e.g. If a product costs 10 including tax, all users will pay 10 regardless of location and taxes.
                 * This feature is experimental @since 2.4.7 and may change in the future. Use at your risk.
                 */
            } else {
                $base_taxes = WC_Tax::calc_tax($line_price, $base_tax_rates, true);
                $modded_taxes = WC_Tax::calc_tax($line_price - array_sum($base_taxes), $tax_rates, false);

                if ('yes' === get_option('woocommerce_tax_round_at_subtotal')) {
                    $base_taxes_total = array_sum($base_taxes);
                    $modded_taxes_total = array_sum($modded_taxes);
                } else {
                    $base_taxes_total = array_sum(array_map('wc_round_tax_total', $base_taxes));
                    $modded_taxes_total = array_sum(array_map('wc_round_tax_total', $modded_taxes));
                }

                $return_price = $tax_with_price == 'excl' ? round($line_price - $base_taxes_total, wc_get_price_decimals()) : round($line_price - $base_taxes_total + $modded_taxes_total, wc_get_price_decimals());
            }
        }
    }
    // return 0;
    return apply_filters('woocommerce_get_price_including_tax', $return_price, $qty, $product);
}

// Seat booked status
function wbbm_seat_booked_on_status() {
    $seat_booked_status = wbbm_get_option('wbbm_seat_booked_on_order_status', 'wbbm_general_setting_sec', array(1,2));
    $seat_booked_status = $seat_booked_status ? implode(',', $seat_booked_status) : "";

    return $seat_booked_status;
}

/******************************************
Update Seat Book On Status Option value
Developer: Ariful
*******************************************/
add_action('wp_loaded','wbbm_update_seat_book_on_status_global_settings');
function wbbm_update_seat_book_on_status_global_settings(){
    $general_settings = is_array(get_option( 'wbbm_general_setting_sec' )) ? maybe_unserialize(get_option( 'wbbm_general_setting_sec' )) : array();

    $seat_book_on_status_arr = array(
        'wbbm_seat_booked_on_order_status' => array(
            1 => 1,
            2 => 2,
        )
    );

    if (! array_key_exists('wbbm_seat_booked_on_order_status',$general_settings)):
        $marged_arr = array_merge($general_settings,$seat_book_on_status_arr);
        update_option( 'wbbm_general_setting_sec', $marged_arr );
    endif;
}
/*
 * Buffer time calculation. If return true that means bus will show in search list.
 * If return false that means this bus will ignore from search result
 * @param string bp_time: Boarding point time
 * @param date date
 * @return bool
 * */
 function wbbm_buffer_time_calculation($bp_time, $date) {
    $is_allow = false;
    $buffer_time_from_setting = wbbm_get_option('wbbm_buffer_time', 'wbbm_general_setting_sec', 0);
    $bus_start_time = date('H:i:s', strtotime($bp_time));

     if ($buffer_time_from_setting > 0) {
         // Convert bus start time into date format
         $bus_buffer_time = (float) $buffer_time_from_setting;

         // Make bus search date & bus start time as date format
         $start_bus = $date . ' ' . $bus_start_time;

         // $diff = round((strtotime($start_bus) - strtotime(current_time('Y-m-d H:i:s'))) / 3600, 1); // In Hour
         $diff = round((strtotime($start_bus) - strtotime(current_time('Y-m-d H:i:s'))) / 60, 1); // In Minute
         if ($diff >= $bus_buffer_time) {
             $is_allow = true;
         }
     } else {
         $is_allow = true;
     }
    return $is_allow;
 }