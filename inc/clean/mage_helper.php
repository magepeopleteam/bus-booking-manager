<?php
function mage_get_isset($parameter) {
    return isset($_GET[$parameter]) ? strip_tags($_GET[$parameter]) : '';
}

function mage_qty_box($price,$name, $return) {
    $date = $return ? mage_get_isset('r_date') : mage_get_isset('j_date');
    if (mage_available_seat($date) > 0) {
        ?>
        <div class="mage_form_group">
            <div class="mage_flex mage_qty_dec"><span class="fa fa-minus"></span></div>
            <input type="text"
                   class="mage_form"
                   data-ticket-title="<?php echo ($name == 'adult_quantity') ? 'Adult' : 'Child';
                   _e(' Passenger info:', 'bus-booking-manager'); ?>"
                   data-ticket-type="<?php echo ($name == 'adult_quantity') ? 'adult' : 'child'; ?>"
                   data-price="<?php echo $price; ?>"
                   name="<?php echo $name; ?>"
                   value="<?php echo cart_qty($name); ?>"
                   min="0"
                   max="<?php echo mage_available_seat($date); ?>"
                   required
            />
            <div class="mage_flex mage_qty_inc"><span class="fa fa-plus"></span></div>
        </div>
        <?php
    }
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
function mage_route_list() {
    $routes = get_terms(array(
        'taxonomy' => 'wbbm_bus_stops',
        'hide_empty' => false,
    ));
    echo '<ul class="mage_route_list">';
    foreach ($routes as $route) {
        echo '<li data-route="' . $route->name . '"><span class="fa fa-map-marker"></span>' . $route->name . '</li>';
    }
    echo '</ul>';
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
        'meta_key' => 'wbbm_bus_start_time',
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
    $date = strtotime(mage_get_isset($return ? 'r_date' : 'j_date'));

    return (($start_date <=$date ) && ($end_date>=$date) ) ? false : true;
}

//off day check
function mage_off_day_check($return) {
    $current_day = 'od_' . date('D', strtotime($return ? mage_get_isset('r_date') : mage_get_isset('j_date')));
    return get_post_meta(get_the_id(), $current_day, true) == 'yes' ? false : true;
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
function mage_available_seat($date) {
    $values = get_post_custom(get_the_id());
    $total_seat = $values['wbbm_total_seat'][0];
    $sold_seat = wbbm_get_available_seat(get_the_id(), $date);
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
function mage_seat_price($id,$start,$end,$adult) {
    $price = get_post_meta($id, 'wbbm_bus_prices', true);
    if (is_array($price) && sizeof($price) > 0) {
        foreach ($price as $key => $val) {
            if ($val['wbbm_bus_bp_price_stop'] == $start && $val['wbbm_bus_dp_price_stop'] == $end) {
                $ticket_type = $adult ? 'wbbm_bus_price' : 'wbbm_bus_price_child';
                return (array_key_exists($ticket_type, $val) && $val[$ticket_type] > 0) ? $val[$ticket_type] : 0;
            }
        }
    }
    return false;
}
