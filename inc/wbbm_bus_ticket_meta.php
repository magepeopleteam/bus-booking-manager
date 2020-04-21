<?php 
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.

add_action( 'add_meta_boxes', 'wbbm_bus_meta_box_add' );
function wbbm_bus_meta_box_add(){

$cpt_label = wbbm_get_option( 'wbbm_cpt_label', 'wbbm_general_setting_sec', __('Bus','bus-booking-manager'));
$cpt_slug = wbbm_get_option( 'wbbm_cpt_slug', 'wbbm_general_setting_sec', __('bus','bus-booking-manager'));

    add_meta_box( 'wbbm-bus-date', __($cpt_label.' Stops Info','bus-booking-manager'), 'wbbm_bus_date_meta_box_cb', 'wbbm_bus', 'normal', 'high' );
    add_meta_box( 'wbbm-bus-price', __($cpt_label.' Pricing','bus-booking-manager'), 'wbbm_bus_pricing_meta_box_cb', 'wbbm_bus', 'normal', 'high' );
    add_meta_box( 'wbbm-bus-info-form', __($cpt_label.' Information','bus-booking-manager'), 'wbbm_bus_info_meta_box', 'wbbm_bus', 'normal', 'high' );
    add_meta_box( 'wbtm-bus-operational-date', 'Operational offday settings', 'wbbm_bus_od_meta_box', 'wbbm_bus', 'normal', 'high' );
}

function wbbm_remove_post_custom_fields() {
  // remove_meta_box( 'tagsdiv-wbbm_seat' , 'wbbm_bus' , 'side' ); 
  remove_meta_box( 'wbbm_seat_typediv' , 'wbbm_bus' , 'side' ); 
  remove_meta_box( 'wbbm_bus_stopsdiv' , 'wbbm_bus' , 'side' ); 
  remove_meta_box( 'wbbm_bus_routediv' , 'wbbm_bus' , 'side' ); 
}
add_action( 'admin_menu' , 'wbbm_remove_post_custom_fields' );

function wbbm_bus_date_meta_box_cb($post){
    global $post;
    $wbbm_event_faq  = get_post_meta($post->ID, 'wbbm_bus_next_stops', true);
    $wbbm_bus_bp     = get_post_meta($post->ID, 'wbbm_bus_bp_stops', true);
    $values         = get_post_custom( $post->ID );
    wp_nonce_field( 'wbbm_bus_ticket_type_nonce', 'wbbm_bus_ticket_type_nonce' );
    // echo '<pre>'; print_r( $wbbm_event_faq ); echo '</pre>';

  $get_terms_default_attributes = array (
            'taxonomy' => 'wbbm_bus_stops',
            'hide_empty' => false
    );
  $terms = get_terms($get_terms_default_attributes);
  if($terms){
?>
<script type="text/javascript">
  jQuery(document).ready(function( $ ){
    $( '#add-faq-row' ).on('click', function() {
      var row = $( '.empty-row-faq.screen-reader-text' ).clone(true);
      row.removeClass( 'empty-row-faq screen-reader-text' );
      row.insertBefore( '#repeatable-fieldset-faq-one tbody>tr:last' );
      return false;
    });
    
    $( '.remove-faq-row' ).on('click', function() {
      $(this).parents('tr').remove();
      return false;
    });

    $( '#add-bp-row' ).on('click', function() {
      var row = $( '.empty-row-bp.screen-reader-text' ).clone(true);
      row.removeClass( 'empty-row-bp screen-reader-text' );
      row.insertBefore( '#repeatable-fieldset-bp-one tbody>tr:last' );
      return false;
    });
    
    $( '.remove-bp-row' ).on('click', function() {
      $(this).parents('tr').remove();
      return false;
    });
  });
  </script>

<table id="repeatable-fieldset-bp-one" width="100%">
  <tr>
    <th><?php _e('Boarding Point','bus-booking-manager'); ?></th>
    <th><?php _e('Time','bus-booking-manager'); ?></th>
    <th></th>
  </tr>
  <tbody>
  <?php
  if ( $wbbm_bus_bp ) :
    $count = 0;
  foreach ( $wbbm_bus_bp as $field ) {
  ?>
  <tr>
    <td align="center"><?php echo wbbm_get_next_bus_stops_list('wbbm_bus_bp_stops_name[]','wbbm_bus_bp_stops_name','wbbm_bus_bp_stops',$count); ?></td>
    <td align="center"><input type="text" data-clocklet name='wbbm_bus_bp_start_time[]' value="<?php if($field['wbbm_bus_bp_start_time'] != '') echo esc_attr( $field['wbbm_bus_bp_start_time'] ); ?>" class="text"></td>
    <td align="center"><a class="button remove-faq-row" href="#"><?php _e('Remove','bus-booking-manager'); ?></a></td>
  </tr>
  <?php
  $count++;
  }
  else :
  // show a blank one
 endif; 
 ?>
  
  <!-- empty hidden one for jQuery -->
  <tr class="empty-row-bp screen-reader-text">
    <td align="center"><?php echo wbbm_get_bus_stops_list('wbbm_bus_bp_stops_name[]'); ?></td>
    <td align="center"><input type="text" data-clocklet name='wbbm_bus_bp_start_time[]' value="" class="text"></td>
    <td align="center"><a class="button remove-bp-row" href="#"><?php _e('Remove','bus-booking-manager'); ?></a></td>
  </tr>
  </tbody>
  </table>
  <p><a id="add-bp-row" class="button" href="#"><?php _e('Add More Boarding Point','bus-booking-manager'); ?> </a></p>


<table id="repeatable-fieldset-faq-one" width="100%">
  <tr>
    <th><?php _e('Dropping Point','bus-booking-manager'); ?></th>
    <th><?php _e('Time','bus-booking-manager'); ?></th>
    <th></th>
  </tr>
  <tbody>
  <?php
  if ( $wbbm_event_faq ) :
    $coun = 0;
  foreach ( $wbbm_event_faq as $field ) {
  ?>
  <tr>
    <td align="center"><?php echo wbbm_get_next_bus_stops_list('wbbm_bus_next_stops_name[]','wbbm_bus_next_stops_name','wbbm_bus_next_stops',$coun); ?></td>
    <td align="center"><input type="text" data-clocklet name='wbbm_bus_next_end_time[]' value="<?php if($field['wbbm_bus_next_end_time'] != '') echo esc_attr( $field['wbbm_bus_next_end_time'] ); ?>" class="text"></td>
    <td align="center"><a class="button remove-faq-row" href="#"><?php _e('Remove','bus-booking-manager'); ?></a></td>
  </tr>
  <?php
  $coun++;
  }
  else :
  // show a blank one
 endif; 
 ?>
  
  <!-- empty hidden one for jQuery -->
  <tr class="empty-row-faq screen-reader-text">
    <td align="center"><?php echo wbbm_get_bus_stops_list('wbbm_bus_next_stops_name[]'); ?></td>
    <td align="center"><input type="text" data-clocklet name='wbbm_bus_next_end_time[]' value="" class="text"></td>
    <td align="center"><a class="button remove-faq-row" href="#"><?php _e('Remove','bus-booking-manager'); ?></a></td>
  </tr>
  </tbody>
  </table>
  <p><a id="add-faq-row" class="button" href="#"><?php _e('Add More Droping Point','bus-booking-manager'); ?></a></p>

<?php
}else{
  echo "<div style='padding: 10px 0;text-align: center;background: #d23838;color: #fff;border: 5px solid #ff2d2d;padding: 5;font-size: 16px;display: block;margin: 20px;'>Please Enter some bus stops first. <a style='color:#fff' href='".get_admin_url()."edit-tags.php?taxonomy=wbbm_bus_stops&post_type=wbbm_bus'>Click here for bus stops</a></div>";
}

}




function wbbm_bus_pricing_meta_box_cb($post){
    global $post;
    $wbbm_bus_prices  = get_post_meta($post->ID, 'wbbm_bus_prices', true);
    $values         = get_post_custom( $post->ID );
    wp_nonce_field( 'wbbm_bus_price_nonce', 'wbbm_bus_price_nonce' );
    // echo '<pre>'; print_r( $wbbm_event_faq ); echo '</pre>';
      $get_terms_default_attributes = array (
            'taxonomy' => 'wbbm_bus_stops',
            'hide_empty' => false
    );
  $terms = get_terms($get_terms_default_attributes);
  if($terms){
?>



<script type="text/javascript">
  jQuery(document).ready(function( $ ){


    $( '#add-price-row' ).on('click', function() {
      var row = $( '.empty-row-price.screen-reader-text' ).clone(true);
      row.removeClass( 'empty-row-price screen-reader-text' );
      row.insertBefore( '#repeatable-fieldset-price-one tbody>tr:last' );
      return false;
    });
    
    $( '.remove-price-row' ).on('click', function() {
      $(this).parents('tr').remove();
      return false;
    });

  });
  </script>




<table id="repeatable-fieldset-price-one" width="100%">
  <tr>
    <th><?php _e('Boarding Point','bus-booking-manager'); ?></th>
    <th><?php _e('Dropping Point','bus-booking-manager'); ?></th>
    <th><?php _e('Adult Fare','bus-booking-manager'); ?></th>
    <th><?php _e('Child Fare','bus-booking-manager'); ?></th>
    <th></th>
  </tr>
  <tbody>
  <?php
  if ( $wbbm_bus_prices ) :
    $coun = 0;
  foreach ( $wbbm_bus_prices as $field ) {
  ?>
  <tr>
    <td><?php echo wbbm_get_next_bus_stops_list('wbbm_bus_bp_price_stop[]','wbbm_bus_bp_price_stop','wbbm_bus_prices',$coun); ?></td>

    <td><?php echo wbbm_get_next_bus_stops_list('wbbm_bus_dp_price_stop[]','wbbm_bus_dp_price_stop','wbbm_bus_prices',$coun); ?></td>

    <td><input type="number" name='wbbm_bus_price[]' value="<?php if($field['wbbm_bus_price'] != '') echo esc_attr( $field['wbbm_bus_price'] ); ?>" class="text"></td>

    <td><input type="number" name='wbbm_bus_price_child[]' value="<?php if($field['wbbm_bus_price_child'] != ''){ echo esc_attr( $field['wbbm_bus_price_child'] ); } else{ echo 0; } ?>" class="text"></td>
    
    <td><a class="button remove-price-row" href="#"><?php _e('Remove','bus-booking-manager'); ?></a></td>
  </tr>
  <?php
  $coun++;
  }
  else :
  // show a blank one
 endif; 
 ?>
  
  <!-- empty hidden one for jQuery -->
  <tr class="empty-row-price screen-reader-text">
    <td><?php echo wbbm_get_bus_stops_list('wbbm_bus_bp_price_stop[]'); ?></td>
    <td><?php echo wbbm_get_bus_stops_list('wbbm_bus_dp_price_stop[]'); ?></td>
    <td><input type="number" name='wbbm_bus_price[]' value="" class="text"></td>
    <td><input type="number" name='wbbm_bus_price_child[]' value="" class="text"></td>
    <td><a class="button remove-price-row" href="#"><?php _e('Remove','bus-booking-manager'); ?></a></td>
  </tr>
  </tbody>
  </table>
  <p><a id="add-price-row" class="button" href="#"><?php _e('Add More Price','bus-booking-manager'); ?></a></p>

<?php
}else{
  echo "<div style='padding: 10px 0;text-align: center;background: #d23838;color: #fff;border: 5px solid #ff2d2d;padding: 5;font-size: 16px;display: block;margin: 20px;'>Please Enter some bus stops first. <a style='color:#fff' href='".get_admin_url()."edit-tags.php?taxonomy=wbbm_bus_stops&post_type=wbbm_bus'>Click here for bus stops</a></div>";
}

}


add_action('save_post', 'wbbm_bus_pricing_save');
function wbbm_bus_pricing_save($post_id) {
  global $wpdb;

  if ( ! isset( $_POST['wbbm_bus_price_nonce'] ) ||
  ! wp_verify_nonce( $_POST['wbbm_bus_price_nonce'], 'wbbm_bus_price_nonce' ) )
    return;
  
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
    return;
  
  if (!current_user_can('edit_post', $post_id))
    return;
  
  $old = get_post_meta($post_id, 'wbbm_bus_prices', true);
  $new = array();

  $bp_pice_stops   = $_POST['wbbm_bus_bp_price_stop'];
  $dp_pice_stops   = $_POST['wbbm_bus_dp_price_stop'];
  $the_price       = $_POST['wbbm_bus_price'];
  $the_price_child = $_POST['wbbm_bus_price_child'];
  
  $order_id = 0;
  $count = count( $bp_pice_stops );
  
  for ( $i = 0; $i < $count; $i++ ) {
    
    if ( $bp_pice_stops[$i] != '' ) :
      $new[$i]['wbbm_bus_bp_price_stop'] = stripslashes( strip_tags( $bp_pice_stops[$i] ) );
      endif;

    if ( $dp_pice_stops[$i] != '' ) :
      $new[$i]['wbbm_bus_dp_price_stop'] = stripslashes( strip_tags( $dp_pice_stops[$i] ) );
      endif;

    if ( $the_price[$i] != '' ) :
      $new[$i]['wbbm_bus_price'] = stripslashes( strip_tags( $the_price[$i] ) );
      endif;

    if ( $the_price_child[$i] != '' ) :
      $new[$i]['wbbm_bus_price_child'] = stripslashes( strip_tags( $the_price_child[$i] ) );
      endif;

  }

  if ( !empty( $new ) && $new != $old )
    update_post_meta( $post_id, 'wbbm_bus_prices', $new );
  elseif ( empty($new) && $old )
    delete_post_meta( $post_id, 'wbbm_bus_prices', $old );
}










add_action('save_post', 'wbbm_bus_boarding_points_save');
function wbbm_bus_boarding_points_save($post_id) {
  global $wpdb;

  if ( ! isset( $_POST['wbbm_bus_ticket_type_nonce'] ) ||
  ! wp_verify_nonce( $_POST['wbbm_bus_ticket_type_nonce'], 'wbbm_bus_ticket_type_nonce' ) )
    return;
  
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
    return;
  
  if (!current_user_can('edit_post', $post_id))
    return;
  
  $old = get_post_meta($post_id, 'wbbm_bus_bp_stops', true);
  $new = array();

  $bp_stops  = $_POST['wbbm_bus_bp_stops_name'];
  $start_t  = $_POST['wbbm_bus_bp_start_time'];
  


  $order_id = 0;
  $count = count( $bp_stops );
  
  for ( $i = 0; $i < $count; $i++ ) {
    
    if ( $bp_stops[$i] != '' ) :
      $new[$i]['wbbm_bus_bp_stops_name'] = stripslashes( strip_tags( $bp_stops[$i] ) );
      endif;

    if ( $start_t[$i] != '' ) :
      $new[$i]['wbbm_bus_bp_start_time'] = stripslashes( strip_tags( $start_t[$i] ) );
      endif;
  }

$bstart_time = $start_t[0];
update_post_meta( $post_id, 'wbbm_bus_start_time', $bstart_time );
  if ( !empty( $new ) && $new != $old ){
    update_post_meta( $post_id, 'wbbm_bus_bp_stops', $new );
  }elseif ( empty($new) && $old ){
    delete_post_meta( $post_id, 'wbbm_bus_bp_stops', $old );
    update_post_meta( $post_id, 'wbbm_bus_start_time', '' );
  }
}


add_action('save_post', 'wbbm_bus_droping_stops_save');
function wbbm_bus_droping_stops_save($post_id) {
  global $wpdb;

  if ( ! isset( $_POST['wbbm_bus_ticket_type_nonce'] ) ||
  ! wp_verify_nonce( $_POST['wbbm_bus_ticket_type_nonce'], 'wbbm_bus_ticket_type_nonce' ) )
    return;
  
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
    return;
  
  if (!current_user_can('edit_post', $post_id))
    return;
  
  $old = get_post_meta($post_id, 'wbbm_bus_next_stops', true);
  $new = array();

  $stops  = $_POST['wbbm_bus_next_stops_name'];
  $end_t  = $_POST['wbbm_bus_next_end_time'];
  


  $order_id = 0;
  $count = count( $stops );
  
  for ( $i = 0; $i < $count; $i++ ) {
    
    if ( $stops[$i] != '' ) :
      $new[$i]['wbbm_bus_next_stops_name'] = stripslashes( strip_tags( $stops[$i] ) );
      endif;

    if ( $end_t[$i] != '' ) :
      $new[$i]['wbbm_bus_next_end_time'] = stripslashes( strip_tags( $end_t[$i] ) );
      endif;

    $opt_name =  $post_id.str_replace(' ', '', $names[$i]);

    // update_post_meta( $post_id, "wbbm_xtra_$opt_name",0 );

  }

  if ( !empty( $new ) && $new != $old )
    update_post_meta( $post_id, 'wbbm_bus_next_stops', $new );
  elseif ( empty($new) && $old )
    delete_post_meta( $post_id, 'wbbm_bus_next_stops', $old );
}


function wbbm_bus_info_meta_box($post){
$values = get_post_custom( $post->ID );
$bus_ticket_type = get_post_meta($post->ID, 'wbbm_bus_ticket_type_info', true);
wp_nonce_field( 'wbbm_bus_ticket_type_nonce', 'wbbm_bus_ticket_type_nonce' );
// print_r($values);
?>

<div class='sec'>
    <label for="wbbm_ev_98">  
      <?php _e('Coach No','bus-booking-manager'); ?>
    <span><input id='wbbm_ev_98' type="text" name='wbbm_bus_no' value='<?php if(array_key_exists('wbbm_bus_no', $values)){ echo $values['wbbm_bus_no'][0]; } ?>'/>   </span></label>
</div>

<div class='sec'>
    <label for="wbbm_ev_99">  
      <?php _e('Total Seat','bus-booking-manager'); ?>
    <span><input id='wbbm_ev_99' type="text" name='wbbm_total_seat' value='<?php if(array_key_exists('wbbm_total_seat', $values)){ echo $values['wbbm_total_seat'][0]; } ?>'/>   </span></label>
</div>



  <script type="text/javascript">
  jQuery(document).ready(function( $ ){
    $( '#add-row-t' ).on('click', function() {
      var row = $( '.empty-row-t.screen-reader-text' ).clone(true);
      row.removeClass( 'empty-row-t screen-reader-text' );
      row.insertBefore( '#repeatable-fieldset-one-t tbody>tr:last' );
      return false;
    });
    
    $( '.remove-row-t' ).on('click', function() {
      $(this).parents('tr').remove();
      return false;
    });
  });
  </script>
  <?php
  
  if ( $bus_ticket_type ) :
  
  foreach ( $bus_ticket_type as $field ) {
    $qty_t_type = esc_attr( $field['ticket_type_qty_t_type'] );
  ?>
  <tr>
    <td><input type="text" class="widefat" name="ticket_type_name[]" value="<?php if($field['ticket_type_name'] != '') echo esc_attr( $field['ticket_type_name'] ); ?>" /></td>

    <td><input type="number" class="widefat" name="ticket_type_price[]" value="<?php if ($field['ticket_type_price'] != '') echo esc_attr( $field['ticket_type_price'] ); else echo ''; ?>" /></td>

    <td><input type="number" class="widefat" name="ticket_type_qty[]" value="<?php if ($field['ticket_type_qty'] != '') echo esc_attr( $field['ticket_type_qty'] ); else echo ''; ?>" /></td>

<td><select name="ticket_type_qty_t_type[]" id="mep_ev_9800kj8" class=''>
    <option value="inputbox" <?php if($qty_t_type=='inputbox'){ echo "Selected"; } ?>><?php _e('Input Box','bus-booking-manager'); ?></option>
    <option value="dropdown" <?php if($qty_t_type=='dropdown'){ echo "Selected"; } ?>><?php _e('Dropdown List','bus-booking-manager'); ?></option>
</select></td>

    <td><a class="button remove-row-t" href="#"<?php _e('Remove','bus-booking-manager'); ?></a></td>
  </tr>
  <?php
  }
  else :
  // show a blank one
 endif; 
 ?>
<?php
}

add_action('save_post','wbbm_bus_meta_save');
function wbbm_bus_meta_save($post_id){
    global $post; 
if($post){    
    $pid = $post->ID;
    if ($post->post_type != 'wbbm_bus'){
        return;
    }
    $wbbm_bus_no                      = strip_tags($_POST['wbbm_bus_no']);
    $wbbm_total_seat                  = strip_tags($_POST['wbbm_total_seat']);
    $update_seat_stock_status         = update_post_meta( $pid, '_manage_stock', 'no');
    $update_price                     = update_post_meta( $pid, '_price', 0);
    $update_seat5                     = update_post_meta( $pid, 'wbbm_bus_no', $wbbm_bus_no);
    $update_seat6                     = update_post_meta( $pid, 'wbbm_total_seat', $wbbm_total_seat);  
    $update_virtual                   = update_post_meta( $pid, '_virtual', 'yes');
}
}





function wbbm_bus_od_meta_box($post){
  $values = get_post_custom( $post->ID );
  // print_r($values);
  ?>
  <div class='sec'>
      <label for="od_start">  
  Offday Start Date
      <span><input type="text" id='od_start' name='wbtm_od_start' value='<?php if(array_key_exists('wbtm_od_start', $values)){ echo $values['wbtm_od_start'][0]; } ?>'/>   </span></label>
  </div>
  
  <div class='sec'>
      <label for="od_end">  
  Offday End date
      <span><input type="text" id='od_end' name='wbtm_od_end' value='<?php if(array_key_exists('wbtm_od_end', $values)){ echo $values['wbtm_od_end'][0]; } ?>'/>   </span></label>
  </div>
  <div class='sec'>
    <label for='sun'>
  <input type="checkbox" id='sun' style="text-align: left;width: auto;"  name="od_sun" value='yes' <?php if(array_key_exists('od_Sun', $values)){ if($values['od_Sun'][0]=='yes'){ echo 'Checked'; } } ?>/> <?php _e('Sunday','bus-ticket-booking-with-seat-reservation'); ?>
  </label>
    <label for='mon'>
  <input type="checkbox" style="text-align: left;width: auto;"  name="od_mon" value='yes' id='mon' <?php if(array_key_exists('od_Mon', $values)){ if($values['od_Mon'][0]=='yes'){ echo 'Checked'; } } ?>> <?php _e('Monday','bus-ticket-booking-with-seat-reservation'); ?>
    </label>
    <label for='tue'>
  <input type="checkbox" style="text-align: left;width: auto;"  name="od_tue" value='yes' id='tue' <?php if(array_key_exists('od_Tue', $values)){ if($values['od_Tue'][0]=='yes'){ echo 'Checked'; } } ?>> <?php _e('Tuesday','bus-ticket-booking-with-seat-reservation'); ?>
    </label>
    <label for='wed'>
  <input type="checkbox" style="text-align: left;width: auto;"  name="od_wed" value='yes' id='wed' <?php if(array_key_exists('od_Wed', $values)){ if($values['od_Wed'][0]=='yes'){ echo 'Checked'; } } ?>> <?php _e('Wednesday','bus-ticket-booking-with-seat-reservation'); ?>
    </label>
    <label for='thu'>
  <input type="checkbox" style="text-align: left;width: auto;"  name="od_thu" value='yes' id='thu' <?php if(array_key_exists('od_Thu', $values)){ if($values['od_Thu'][0]=='yes'){ echo 'Checked'; } } ?>> <?php _e('Thursday','bus-ticket-booking-with-seat-reservation'); ?>
    </label>
    <label for='fri'>
  <input type="checkbox" style="text-align: left;width: auto;"  name="od_fri" value='yes' id='fri' <?php if(array_key_exists('od_Fri', $values)){ if($values['od_Fri'][0]=='yes'){ echo 'Checked'; } } ?>> <?php _e('Friday','bus-ticket-booking-with-seat-reservation'); ?>
   </label>
    <label for='sat'>
  <input type="checkbox" style="text-align: left;width: auto;"  name="od_sat" value='yes' id='sat' <?php if(array_key_exists('od_Sat', $values)){ if($values['od_Sat'][0]=='yes'){ echo 'Checked'; } } ?>> <?php _e('Saturday','bus-ticket-booking-with-seat-reservation'); ?>
  </label>
  </div>


  <script type="text/javascript">
jQuery(document).ready(function($){

      jQuery( "#od_start" ).datepicker({
        dateFormat: "yy-mm-dd",
        minDate:0
      });
      jQuery( "#od_end" ).datepicker({
        dateFormat: "yy-mm-dd",
        // minDate:0
      });

      // jQuery( "#ja_date" ).datepicker({
      //   dateFormat: "yy-mm-dd"
      // });
      // jQuery(".the_select select").select2();
    });
</script>



  <?php
  }

add_action('save_post','wbbm_bus_od_info_save');
function wbbm_bus_od_info_save($post_id){
global $post; 
if($post){    
    $pid = $post->ID;
    if ($post->post_type != 'wbbm_bus'){
        return;
    }
    $wbtm_od_start         = strip_tags($_POST['wbtm_od_start']);
    $wbtm_od_end           = strip_tags($_POST['wbtm_od_end']);
    $od_sun                = strip_tags($_POST['od_sun']);
    $od_mon                = strip_tags($_POST['od_mon']);
    $od_tue                = strip_tags($_POST['od_tue']);
    $od_wed                = strip_tags($_POST['od_wed']);
    $od_thu                = strip_tags($_POST['od_thu']);
    $od_fri                = strip_tags($_POST['od_fri']);
    $od_sat                = strip_tags($_POST['od_sat']);
    $show_boarding_points  = strip_tags($_POST['show_boarding_points']);
    $update_virtual         = update_post_meta( $pid, '_virtual', 'yes');
    $update_wbtm_od_start   = update_post_meta( $pid, 'wbtm_od_start', $wbtm_od_start);
    $update_wbtm_od_end     = update_post_meta( $pid, 'wbtm_od_end', $wbtm_od_end);
    $update_wbtm_od_sun     = update_post_meta( $pid, 'od_Sun', $od_sun);
    $update_wbtm_od_mon     = update_post_meta( $pid, 'od_Mon', $od_mon);
    $update_wbtm_od_tue     = update_post_meta( $pid, 'od_Tue', $od_tue);
    $update_wbtm_od_wed     = update_post_meta( $pid, 'od_Wed', $od_wed);
    $update_wbtm_od_thu     = update_post_meta( $pid, 'od_Thu', $od_thu);
    $update_wbtm_od_fri     = update_post_meta( $pid, 'od_Fri', $od_fri);
    $update_wbtm_od_sat     = update_post_meta( $pid, 'od_Sat', $od_sat);
    $update_wbtm_show_boarding_points   = update_post_meta( $pid, 'show_boarding_points', $show_boarding_points);
    
}
}