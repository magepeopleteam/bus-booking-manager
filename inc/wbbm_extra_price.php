<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.

function wbbm_add_custom_fields_text_to_cart_item( $cart_item_data, $product_id, $variation_id ) {
  $tp               = get_post_meta($product_id,'_price',true);
  $price_arr        = get_post_meta($product_id,'wbbm_bus_prices',true);
  $new              = array();
  $user             = array();
  $start_stops      = sanitize_text_field($_POST['start_stops']);
  $end_stops        = sanitize_text_field($_POST['end_stops']);
  $journey_date     = sanitize_text_field($_POST['journey_date']);
  $adult_seat       = sanitize_text_field($_POST['adult_quantity']);
  if(isset($_POST['child_quantity'])){
  $total_child_seat = sanitize_text_field($_POST['child_quantity']);
  $child_fare       = mage_seat_price($product_id,$start_stops,$end_stops,false);
  $total_child_fare = $child_fare * $total_child_seat;
  }else{
    $total_child_seat       = 0;
    $child_fare             = 0;  
    $total_child_fare       = 0;  
  }
  $total_seat       = ($adult_seat+$total_child_seat);
  $main_fare        = mage_seat_price($product_id,$start_stops,$end_stops,true);
  $adult_fare       = $main_fare * $adult_seat;
  $total_fare       = ($adult_fare + $total_child_fare);
  $user_start_time  = sanitize_text_field($_POST['user_start_time']);
  $bus_start_time   = sanitize_text_field($_POST['bus_start_time']);
  $bus_id           = sanitize_text_field($_POST['bus_id']);


if(isset($_POST['custom_reg_user']) && ($_POST['custom_reg_user'])=='yes'){


  $wbbm_user_name          = wbbm_array_strip($_POST['wbbm_user_name']);
  $wbbm_user_email         = wbbm_array_strip($_POST['wbbm_user_email']);
  $wbbm_user_phone         = wbbm_array_strip($_POST['wbbm_user_phone']);
  $wbbm_user_address       = wbbm_array_strip($_POST['wbbm_user_address']);
  $wbbm_user_gender        = wbbm_array_strip($_POST['wbbm_user_gender']);
  $wbbm_user_type          = wbbm_array_strip($_POST['wbbm_user_type']);

$count_user = count($wbbm_user_type);
  for ( $iu = 0; $iu < $count_user; $iu++ ) {
    
    if ( $wbbm_user_name[$iu] != '' ) :
      $user[$iu]['wbbm_user_name'] = stripslashes( strip_tags( $wbbm_user_name[$iu] ) );
      endif;

    if ( $wbbm_user_email[$iu] != '' ) :
      $user[$iu]['wbbm_user_email'] = stripslashes( strip_tags( $wbbm_user_email[$iu] ) );
      endif;

    if ( $wbbm_user_phone[$iu] != '' ) :
      $user[$iu]['wbbm_user_phone'] = stripslashes( strip_tags( $wbbm_user_phone[$iu] ) );
      endif;

    if ( $wbbm_user_address[$iu] != '' ) :
      $user[$iu]['wbbm_user_address'] = stripslashes( strip_tags( $wbbm_user_address[$iu] ) );
      endif;

    if ( $wbbm_user_gender[$iu] != '' ) :
      $user[$iu]['wbbm_user_gender'] = stripslashes( strip_tags( $wbbm_user_gender[$iu] ) );
      endif;    

      if ( $wbbm_user_type[$iu] != '' ) :
      $user[$iu]['wbbm_user_type'] = stripslashes( strip_tags( $wbbm_user_type[$iu] ) );
      endif;

    $wbbm_form_builder_data = get_post_meta($product_id, 'wbbm_form_builder_data', true);
    if ( $wbbm_form_builder_data ) {
      foreach ( $wbbm_form_builder_data as $_field ) {          
            $user[$iu][$_field['wbbm_fbc_id']] = stripslashes( strip_tags( $_POST[$_field['wbbm_fbc_id']][$iu] ) );
      }
    }

}
}else{
  $user = array();
}




  $cart_item_data['wbbm_start_stops']         = $start_stops;
  $cart_item_data['wbbm_end_stops']           = $end_stops;
  $cart_item_data['wbbm_journey_date']        = $journey_date;
  $cart_item_data['wbbm_journey_time']        = $user_start_time;
  $cart_item_data['wbbm_bus_time']            = $bus_start_time;
  $cart_item_data['wbbm_total_seats']         = $total_seat;
  $cart_item_data['wbbm_total_adult_qt']      = $adult_seat;
  $cart_item_data['wbbm_per_adult_price']     = $main_fare;
  $cart_item_data['wbbm_total_adult_price']   = $adult_fare;
  $cart_item_data['wbbm_total_child_qt']      = $total_child_seat;
  $cart_item_data['wbbm_total_child_price']   = $total_child_fare;
  $cart_item_data['wbbm_per_child_price']     = $child_fare;
  $cart_item_data['wbbm_passenger_info']      = $user;
  $cart_item_data['wbbm_tp']                  = $total_fare;
  $cart_item_data['wbbm_bus_id']              = $bus_id;
  $cart_item_data['line_total']               = $total_fare;
  $cart_item_data['line_subtotal']            = $total_fare;
  $cart_item_data['quantity']                 = $total_seat;
  $cart_item_data['wbbm_id']                  = $product_id;

  return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'wbbm_add_custom_fields_text_to_cart_item', 10, 3 );



add_action( 'woocommerce_before_calculate_totals', 'wbbm_add_custom_price' );
function wbbm_add_custom_price( $cart_object ) {
    foreach ( $cart_object->cart_contents as $key => $value ) {
$eid = $value['wbbm_id'];
if (get_post_type($eid) == 'wbbm_bus') {         
            $cp = $value['wbbm_tp'];
            $value['data']->set_price($cp);
            $value['data']->set_price($cp);
            $value['data']->set_regular_price($cp);
            $value['data']->set_sale_price($cp);
            $value['data']->set_sold_individually('yes');
            $value['data']->get_price();
          }
    }

}





function wbbm_display_custom_fields_text_cart( $item_data, $cart_item ) {
$eid      = $cart_item['wbbm_id'];
if (get_post_type($eid) == 'wbbm_bus') { 
$total_adult      = $cart_item['wbbm_total_adult_qt'];
$total_adult_fare = $cart_item['wbbm_per_adult_price'];
$total_child      = $cart_item['wbbm_total_child_qt'];
$total_child_fare = $cart_item['wbbm_per_child_price'];
$currency = get_woocommerce_currency_symbol();
// print_r($cart_item);




echo "<ul class='event-custom-price'>"; 
?>
<li>
<?php echo wbbm_get_option('wbbm_select_journey_date_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_select_journey_date_text', 'wbbm_label_setting_sec') : _e('Journey Date:','bus-booking-manager'); ?>
 <?php echo ' '.get_wbbm_datetime($cart_item['wbbm_journey_date'],'date-text'); ?>
</li>
<li>
<?php echo wbbm_get_option('wbbm_starting_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_starting_text', 'wbbm_label_setting_sec') : _e('Journey Time:','bus-booking-manager'); ?>
 <?php echo get_wbbm_datetime($cart_item['wbbm_journey_time'],'time'); ?>
 </li>
<li>
<?php echo wbbm_get_option('wbbm_boarding_points_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_boarding_points_text', 'wbbm_label_setting_sec') : _e('Boarding Point:','bus-booking-manager'); ?>
<?php echo $cart_item['wbbm_start_stops']; ?>
</li>
<li>
<?php echo wbbm_get_option('wbbm_dropping_points_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_dropping_points_text', 'wbbm_label_setting_sec') : _e('Dropping Point:','bus-booking-manager'); ?>

<?php echo $cart_item['wbbm_end_stops']; ?></li>
<li><?php echo wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') : _e('Adult','bus-booking-manager');
echo " (".wc_price($total_adult_fare)." x $total_adult) = ".wc_price($total_adult_fare * $total_adult); ?></li>
<?php if($total_child>0){ ?>
<li>
<?php 
echo wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') : _e('Child','bus-booking-manager');
echo " (".wc_price($total_child_fare)." x $total_child) = ".wc_price($total_child_fare * $total_child); ?></li>
<?php } ?>
<?php ?>
</ul>
<?php
}
  return $item_data;

}
add_filter( 'woocommerce_get_item_data', 'wbbm_display_custom_fields_text_cart', 10, 2 );




function wbbm_add_custom_fields_text_to_order_items( $item, $cart_item_key, $values, $order ) {
$eid      = $values['wbbm_id'];
if (get_post_type($eid) == 'wbbm_bus') { 
$wbbm_passenger_info     = $values['wbbm_passenger_info'];
$wbbm_start_stops        = $values['wbbm_start_stops'];
$wbbm_end_stops          = $values['wbbm_end_stops'];
$wbbm_journey_date       = $values['wbbm_journey_date'];
$wbbm_journey_time       = $values['wbbm_journey_time'];
$wbbm_bus_start_time     = $values['wbbm_bus_time'];
$wbbm_bus_id             = $values['wbbm_bus_id'];
$total_adult             = $values['wbbm_total_adult_qt'];
$total_adult_fare        = $values['wbbm_per_adult_price'];
$total_child             = $values['wbbm_total_child_qt'];
$total_child_fare        = $values['wbbm_per_child_price'];
$total_fare              = $values['wbbm_tp'];
// $timezone                = wp_timezone_string();
// $timestamp               = strtotime( $wbbm_journey_time . ' '. $timezone);
// $jtime                   = wp_date( 'H:i A', $timestamp ); 
$jtime                   = get_wbbm_datetime($wbbm_journey_time,'time-raw'); 



$item->add_meta_data( 'Start',$wbbm_start_stops);
$item->add_meta_data( 'End',$wbbm_end_stops);
$item->add_meta_data( 'Date',$wbbm_journey_date);
$item->add_meta_data( 'Time',$jtime);
$item->add_meta_data( 'Adult',$total_adult);
$item->add_meta_data( 'Child',$total_child);
$item->add_meta_data( '_adult_per_price',$total_adult_fare);
$item->add_meta_data( '_child_per_price',$total_child_fare);
$item->add_meta_data( '_total_price',$total_fare);
$item->add_meta_data( '_bus_id',$wbbm_bus_id);
$item->add_meta_data( '_btime',$jtime);
$item->add_meta_data( '_wbbm_passenger_info',$wbbm_passenger_info);
}
$item->add_meta_data('_wbbm_bus_id',$eid);
}
add_action( 'woocommerce_checkout_create_order_line_item', 'wbbm_add_custom_fields_text_to_order_items', 10, 4 );