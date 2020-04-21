<?php
/**
* Plugin Name: Multipurpose Ticket Booking Manager (Bus/Train/Ferry/Boat/Shuttle)
* Plugin URI: http://mage-people.com
* Description: A Complete Bus Ticketig System for WordPress & WooCommerce
* Version: 3.2.2
* Author: MagePeople Team
* Author URI: http://www.mage-people.com/
* Text Domain: bus-booking-manager
* Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.

// function to create passenger list table        
function wbbm_booking_list_table_create() {
global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();
  $table_name = $wpdb->prefix . 'wbbm_bus_booking_list';
  $sql = "CREATE TABLE $table_name (
    booking_id int(15) NOT NULL AUTO_INCREMENT,
    order_id int(9) NOT NULL,  
    bus_id int(9) NOT NULL, 
    user_id int(9) NOT NULL, 
    boarding_point varchar(55) NOT NULL, 
    next_stops text NOT NULL,     
    droping_point varchar(55) NOT NULL, 
    user_name varchar(55) NOT NULL, 
    user_email varchar(55) NOT NULL, 
    user_phone varchar(55) NOT NULL, 
    user_gender varchar(55) NOT NULL, 
    user_address text NOT NULL, 
    user_type varchar(55) NOT NULL, 
    bus_start varchar(55) NOT NULL, 
    user_start varchar(55) NOT NULL,
    total_adult int(9) NOT NULL,  
    per_adult_price int(9) NOT NULL, 
    total_child int(9) NOT NULL, 
    per_child_price int(9) NOT NULL, 
    total_price int(9) NOT NULL,     
    seat varchar(55) NOT NULL,
    journey_date date DEFAULT '0000-00-00' NOT NULL,    
    booking_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,    
    status int(1) NOT NULL,  
    PRIMARY KEY  (booking_id)
  ) $charset_collate;";
  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql );
}
// run the install scripts upon plugin activation
register_activation_hook(__FILE__,'wbbm_booking_list_table_create');

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

require_once(dirname(__FILE__) . "/inc/class-mage-settings.php");
require_once(dirname(__FILE__) . "/inc/wbbm_admin_settings.php");
require_once(dirname(__FILE__) . "/inc/wbbm_cpt.php");
require_once(dirname(__FILE__) . "/inc/wbbm_tax.php");
require_once(dirname(__FILE__) . "/inc/wbbm_bus_ticket_meta.php");
require_once(dirname(__FILE__) . "/inc/wbbm_extra_price.php");
require_once(dirname(__FILE__) . "/inc/wbbm_shortcode.php");
require_once(dirname(__FILE__) . "/inc/wbbm_enque.php");
//added by sumon
    require_once(dirname(__FILE__) . "/inc/clean/mage_short_code.php");
    require_once(dirname(__FILE__) . "/inc/clean/mage_function.php");
    //--------------

// Language Load
add_action( 'init', 'wbbm_language_load');
function wbbm_language_load(){
    $plugin_dir = basename(dirname(__FILE__))."/languages/";
    load_plugin_textdomain( 'bus-booking-manager', false, $plugin_dir );
}



/**
 * Run code only once
 */
function wbbm_update_databas_once() {
    global $wpdb;
    if ( get_option( 'wbbm_update_db_once_06' ) != 'completed' ) {                 
        $table = $wpdb->prefix."wbbm_bus_booking_list";
        $myCustomer = $wpdb->get_row( sprintf("SELECT * FROM %s LIMIT 1", $table) );
        if(!isset($myCustomer->user_type)) {
            $wpdb->query( sprintf( "ALTER TABLE %s  
            ADD COLUMN user_type varchar(55) NOT NULL AFTER user_address,  
            ADD COLUMN total_adult int(9) NOT NULL AFTER user_start,  
            ADD COLUMN per_adult_price int(9) NOT NULL AFTER total_adult, 
            ADD COLUMN total_child int(9) NOT NULL AFTER per_adult_price, 
            ADD COLUMN per_child_price int(9) NOT NULL AFTER total_child, 
            ADD COLUMN total_price int(9) NOT NULL AFTER per_child_price", $table) );
        }
        update_option( 'wbbm_update_db_once_06', 'completed' );
    } 
    if ( get_option( 'wbbm_update_db_once_07' ) != 'completed' ) {                 
        $table = $wpdb->prefix."wbbm_bus_booking_list";
        $myCustomer = $wpdb->get_row( sprintf("SELECT * FROM %s LIMIT 1", $table) );
        if(!isset($myCustomer->next_stops)) {
            $wpdb->query( sprintf( "ALTER TABLE %s ADD next_stops text NOT NULL AFTER boarding_point", $table) );
        }
        update_option( 'wbbm_update_db_once_07', 'completed' );
    }       
}
add_action( 'admin_init', 'wbbm_update_databas_once' );








// Function to get page slug
function wbbm_get_page_by_slug($slug) {
    if ($pages = get_pages())
        foreach ($pages as $page)
            if ($slug === $page->post_name) return $page;
    return false;
}

// Cretae pages on plugin activation
function wbbm_page_create() {
        if (! wbbm_get_page_by_slug('bus-search')) {
            $bus_search_page = array(
            'post_type' => 'page',
            'post_name' => 'bus-search',
            'post_title' => 'Bus Search',
            'post_content' => '[bus-search]',
            'post_status' => 'publish',
            );
            wp_insert_post($bus_search_page);
        }
        if (! wbbm_get_page_by_slug('view-ticket')) {
            $view_ticket_page = array(
            'post_type' => 'page',
            'post_name' => 'view-ticket',
            'post_title' => 'View Ticket',
            'post_content' => '[view-ticket]',
            'post_status' => 'publish',
            );
            wp_insert_post($view_ticket_page);
        } 
}
register_activation_hook(__FILE__,'wbbm_page_create');

// Class for Linking with Woocommerce with Bus Pricing 
add_action('plugins_loaded', 'wbbm_load_wc_class');
function wbbm_load_wc_class() {
  if ( class_exists('WC_Product_Data_Store_CPT') ) {
   class WBBM_Product_Data_Store_CPT extends WC_Product_Data_Store_CPT {
    public function read( &$product ) {

        $product->set_defaults();

        if ( ! $product->get_id() || ! ( $post_object = get_post( $product->get_id() ) ) || ! in_array( $post_object->post_type, array( 'wbbm_bus', 'product' ) ) ) { // change birds with your post type
            throw new Exception( __( 'Invalid product.', 'woocommerce' ) );
        }

        $id = $product->get_id();

        $product->set_props( array(
            'name'              => $post_object->post_title,
            'slug'              => $post_object->post_name,
            'date_created'      => 0 < $post_object->post_date_gmt ? wc_string_to_timestamp( $post_object->post_date_gmt ) : null,
            'date_modified'     => 0 < $post_object->post_modified_gmt ? wc_string_to_timestamp( $post_object->post_modified_gmt ) : null,
            'status'            => $post_object->post_status,
            'description'       => $post_object->post_content,
            'short_description' => $post_object->post_excerpt,
            'parent_id'         => $post_object->post_parent,
            'menu_order'        => $post_object->menu_order,
            'reviews_allowed'   => 'open' === $post_object->comment_status,
        ) );
        $this->read_attributes( $product );
        $this->read_downloads( $product );
        $this->read_visibility( $product );
        $this->read_product_data( $product );
        $this->read_extra_data( $product );
        $product->set_object_read( true );
    }

    /**
     * Get the product type based on product ID.
     *
     * @since 3.0.0
     * @param int $product_id
     * @return bool|string
     */
    public function get_product_type( $product_id ) {
        $post_type = get_post_type( $product_id );
        if ( 'product_variation' === $post_type ) {
            return 'variation';
        } elseif ( in_array( $post_type, array( 'wbbm_bus', 'product' ) ) ) { // change birds with your post type
            $terms = get_the_terms( $product_id, 'product_type' );
            return ! empty( $terms ) ? sanitize_title( current( $terms )->name ) : 'simple';
        } else {
            return false;
        }
    }
}




add_filter( 'woocommerce_data_stores', 'wbbm_woocommerce_data_stores' );
function wbbm_woocommerce_data_stores ( $stores ) {     
      $stores['product'] = 'WBBM_Product_Data_Store_CPT';
      return $stores;
  }

  } else {

    add_action('admin_notices', 'wc_not_loaded');

  }
}


add_action('woocommerce_before_checkout_form', 'wbbm_displays_cart_products_feature_image');
function wbbm_displays_cart_products_feature_image() {
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        $item = $cart_item['data'];
    }
}



add_action('restrict_manage_posts', 'wbbm_filter_post_type_by_taxonomy');
function wbbm_filter_post_type_by_taxonomy() {
  global $typenow;
  $post_type = 'wbbm_bus'; // change to your post type
  $taxonomy  = 'wbbm_bus_cat'; // change to your taxonomy
  if ($typenow == $post_type) {
    $selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
    $info_taxonomy = get_taxonomy($taxonomy);
    wp_dropdown_categories(array(
      'show_option_all' => __("Show All {$info_taxonomy->label}"),
      'taxonomy'        => $taxonomy,
      'name'            => $taxonomy,
      'orderby'         => 'name',
      'selected'        => $selected,
      'show_count'      => true,
      'hide_empty'      => true,
    ));
  };
}




add_filter('parse_query', 'wbbm_convert_id_to_term_in_query');
function wbbm_convert_id_to_term_in_query($query) {
  global $pagenow;
  $post_type = 'wbbm_bus'; // change to your post type
  $taxonomy  = 'wbbm_bus_cat'; // change to your taxonomy
  $q_vars    = &$query->query_vars;

  if ( $pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0 ) {
    $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
    $q_vars[$taxonomy] = $term->slug;
  }

}


function wbbm_load_bus_templates($template) {
    global $post;
  if ($post->post_type == "wbbm_bus"){
          $template_name = 'single-bus.php';
          $template_path = 'mage-bus-ticket/';
          $default_path = plugin_dir_path( __FILE__ ) . 'templates/'; 
          $template = locate_template( array($template_path . $template_name) );
        if ( ! $template ) :
          $template = $default_path . $template_name;
        endif;
    return $template;
  }
    return $template;
}
add_filter('single_template', 'wbbm_load_bus_templates');


add_filter('template_include', 'wbbm_taxonomy_set_template');
function wbbm_taxonomy_set_template( $template ){

    if( is_tax('wbbm_bus_cat')){
        $template = plugin_dir_path( __FILE__ ).'templates/taxonomy-category.php';
    }    

    return $template;
}


function wbbm_get_bus_ticket_order_metadata($id,$part){
global $wpdb;
$table_name = $wpdb->prefix . 'woocommerce_order_itemmeta';
$result = $wpdb->get_results( "SELECT * FROM $table_name WHERE order_item_id=$id" );

foreach ( $result as $page )
{
  if (strpos($page->meta_key, '_') !== 0) {
   echo wbbm_get_string_part($page->meta_key,$part).'<br/>';
 }
}

}





function wbbm_get_seat_type($name){
  global $post;
$values = get_post_custom( $post->ID );
$seat_name = $name;
if(array_key_exists($seat_name, $values)){
$type_name = $values[$seat_name][0];
}else{
  $type_name = '';
}

  $get_terms_default_attributes = array (
            'taxonomy' => 'wbbm_seat_type', //empty string(''), false, 0 don't work, and return 
            'hide_empty' => false, //can be 1, '1' too
    );
  $terms = get_terms($get_terms_default_attributes);
if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
  ob_start();
  ?>
    <select name="<?php echo $name; ?>" class='seat_type select2'>
      <?php
    foreach ( $terms as $term ) {
      ?>
        <option value="<?php echo $term->name; ?>" <?php if($type_name==$term->name){ echo "Selected"; } ?> ><?php echo $term->name; ?></option>
        <?php
    }
    ?>
    </select>
  <?php
  
}
$content = ob_get_clean();
return $content;
}




function wbbm_get_bus_route_list( $name, $value = '' ) {
global $post;
$values     = get_post_custom( $post->ID );

if($values){
  $values = $values;
}else{
  $values = array();
}




if(array_key_exists($name, $values)){
    $seat_name  = $name;
    $type_name  = $values[$seat_name][0];
  }else{
    $type_name='';
  }
    $terms      = get_terms( array (
        // 'taxonomy' => 'wbbm_bus_route',
        'taxonomy' => 'wbbm_bus_stops',
        'hide_empty' => false,
    ) );

    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) : ob_start(); ?>

        <select required name="<?php echo $name; ?>" class='seat_type select2'>
            
            <option value=""><?php _e('Please Select','bus-booking-manager'); ?></option>
            
            <?php foreach ( $terms as $term ) : 
                $wbbm_bs_show = get_term_meta( $term->term_id, 'wbbm_bs_show', true );
                if($wbbm_bs_show){ $show = $wbbm_bs_show; }else{ $show = 'yes'; }
                if($show=='yes'){
                $selected = $type_name == $term->name ? 'selected' : '';
                
                if( ! empty( $value ) ) $selected = $term->name == $value ? 'selected' : '';
                printf( '<option %s value="%s">%s</option>', $selected, $term->name, $term->name );
}
            endforeach; ?>

        </select>

    <?php endif;

    return ob_get_clean();
}


function wbbm_get_bus_stops_list($name){
  global $post;
$values = get_post_custom( $post->ID );
$seat_name = $name;
if(array_key_exists($seat_name, $values)){
$type_name = $values[$seat_name][0];
}else{
  $type_name = '';
}

  $get_terms_default_attributes = array (
            'taxonomy' => 'wbbm_bus_stops', //empty string(''), false, 0 don't work, and return 
            'hide_empty' => false, //can be 1, '1' too
    );
  $terms = get_terms($get_terms_default_attributes);
if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
  ob_start();
  ?>
    <select name="<?php echo $name; ?>" class='seat_type select2'>
      <option value=""><?php _e('Please Select','bus-booking-manager'); ?></option>
      <?php
    foreach ( $terms as $term ) {
      ?>
        <option value="<?php echo $term->name; ?>" <?php if($type_name==$term->name){ echo "Selected"; } ?> ><?php echo $term->name; ?></option>
        <?php
    }
    ?>
    </select>
  <?php
  
}
$content = ob_get_clean();
return $content;
}



function wbbm_get_next_bus_stops_list($name,$data,$list,$coun){
  global $post;
$values = get_post_custom( $post->ID );
$nxt_arr = get_post_meta($post->ID, $list, true);
// print_r($nxt_arr);
$seat_name = $name;
$type_name = $nxt_arr[$coun][$data];

  $get_terms_default_attributes = array (
            'taxonomy' => 'wbbm_bus_stops', //empty string(''), false, 0 don't work, and return 
            'hide_empty' => false, //can be 1, '1' too
    );
  $terms = get_terms($get_terms_default_attributes);
if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
  ob_start();
  ?>
    <select name="<?php echo $name; ?>" class='seat_type select2'>
      <option value=""><?php _e('Please Select','bus-booking-manager'); ?></option>
      <?php
    foreach ( $terms as $term ) {
      ?>
        <option value="<?php echo $term->name; ?>" <?php if($type_name==$term->name){ echo "Selected"; } ?> ><?php echo $term->name; ?></option>
        <?php
    }
    ?>
    </select>
  <?php
  
}
$content = ob_get_clean();
return $content;
}


function wbbm_get_bus_price($start,$end, $array) {
   foreach ($array as $key => $val) {
       if ($val['wbbm_bus_bp_price_stop'] === $start && $val['wbbm_bus_dp_price_stop'] === $end ) {
           return $val['wbbm_bus_price'];
           // return $key;
       }
   }
   return null;
}


function wbbm_get_bus_price_child($start,$end, $array) {
   foreach ($array as $key => $val) {
       if ($val['wbbm_bus_bp_price_stop'] === $start && $val['wbbm_bus_dp_price_stop'] === $end ) {
           
          return isset($val['wbbm_bus_price_child']);
           // return $key;
       }
   }
   return null;
}



function wbbm_get_bus_start_time($start, $array) {
   foreach ($array as $key => $val) {
       if ($val['wbbm_bus_bp_stops_name'] === $start ) {
           return $val['wbbm_bus_bp_start_time'];
           // return $key;
       }
   }
   return null;
}



function wbbm_get_bus_end_time($end, $array) {
   foreach ($array as $key => $val) {
       if ($val['wbbm_bus_next_stops_name'] === $end ) {
           return $val['wbbm_bus_next_end_time'];
           // return $key;
       }
   }
   return null;
}

//add_action('wbbm_search_fields','wbbm_bus_search_fileds');
function wbbm_bus_search_fileds($start,$end,$date,$r_date){
    ob_start();
    ?>
    <div class="search-fields">

      <div class="fields-li">
          <label>
            <i class="fa fa-map-marker" aria-hidden="true"></i> <?php _e('From','bus-booking-manager'); ?>
          <?php echo wbbm_get_bus_route_list( 'bus_start_route', $start ); ?></label>
      </div>

      <div class="fields-li">
          <label>
              <i class="fa fa-map-marker" aria-hidden="true"></i> <?php _e('To:','bus-booking-manager'); ?>
          <?php echo wbbm_get_bus_route_list( 'bus_end_route', $end ); ?>
          </label>
      </div>

          
      <div class="fields-li">
          <label for='j_date'>
             <i class="fa fa-calendar" aria-hidden="true"></i> <?php _e('Date of Journey:','bus-booking-manager'); ?>
          <input type="text" id="j_date" name="j_date" value="<?php echo $date; ?>">
          </label>
      </div>


      <div class="fields-li return-date-sec">
          <label for='r_date'>
            <i class="fa fa-calendar" aria-hidden="true"></i> <?php _e('Return Date:','bus-booking-manager'); ?>
          <input type="text" id="r_date" name="r_date" value="<?php echo $r_date; ?>">
          </label>
      </div>
      <?php 
      if(isset($_GET['bus-r'])){
      $busr = strip_tags($_GET['bus-r']);
      }else{
      $busr = 'oneway';
      }
      ?>
      <div class="fields-li">
        <div class="search-radio-sec">
          <label for="oneway"><input type="radio" <?php if($busr=='oneway'){ echo 'checked'; } ?> id='oneway' name="bus-r" value='oneway'> <?php _e('One Way','bus-booking-manager'); ?></label>
          <label for="return_date"><input type="radio" <?php if($busr=='return'){ echo 'checked'; } ?> id='return_date' name="bus-r" value='return'> <?php _e('Return','bus-booking-manager'); ?></label>
        </div>
        <button type="submit"><i class='fa fa-search'></i> <?php _e('Search','bus-booking-manager'); ?> </button>
      </div>
    </div> 
    <script>
      <?php if(isset($_GET['bus-r']) && $_GET['bus-r']=='oneway'){ ?>
        jQuery('.return-date-sec').hide();
      <?php }elseif(isset($_GET['bus-r']) && $_GET['bus-r']=='return'){ ?>
        jQuery('.return-date-sec').show();
      <?php }else{ ?>
        jQuery('.return-date-sec').hide();
      <?php } ?>
        jQuery('#oneway').on('click', function () {
          jQuery('.return-date-sec').hide();
        }); 
        jQuery('#return_date').on('click', function () {
          jQuery('.return-date-sec').show();
        });      
    </script>   
    <?php
    $content = ob_get_clean();
    echo $content;
}


function wbbm_get_seat_status($seat,$date,$bus_id,$start){
global $wpdb;
  $table_name = $wpdb->prefix."wbbm_bus_booking_list";
  $total_mobile_users = $wpdb->get_results( "SELECT status FROM $table_name WHERE seat='$seat' AND journey_date='$date' AND bus_id = $bus_id AND ( boarding_point ='$start' OR next_stops LIKE '%$start%' ) ORDER BY booking_id DESC Limit 1 " );
  return $total_mobile_users;
}


function wbbm_get_available_seat($bus_id,$date){
  global $wpdb;
    $table_name = $wpdb->prefix."wbbm_bus_booking_list";
  $total_mobile_users = $wpdb->get_var( "SELECT COUNT(booking_id) FROM $table_name WHERE bus_id=$bus_id AND journey_date='$date' AND (status=2 OR status=1)" );
  return $total_mobile_users;
}

function wbbm_get_order_meta($item_id,$key){
global $wpdb;
  $table_name = $wpdb->prefix."woocommerce_order_itemmeta";
  $sql = 'SELECT meta_value FROM '.$table_name.' WHERE order_item_id ='.$item_id.' AND meta_key="'.$key.'"';
  $results = $wpdb->get_results($sql);
  foreach( $results as $result ) {
     $value = $result->meta_value;
  }
  return $value;
}



function wbbm_get_order_seat_check($bus_id,$order_id,$user_type,$bus_start,$date){
  global $wpdb;
    $table_name = $wpdb->prefix."wbbm_bus_booking_list";
  $total_mobile_users = $wpdb->get_var( "SELECT COUNT(booking_id) FROM $table_name WHERE bus_id=$bus_id AND order_id = $order_id AND bus_start = '$bus_start' AND user_type = '$user_type' AND journey_date='$date' AND (status = 1 OR status = 2 OR status = 3)" );
  return $total_mobile_users;
}

// add_action('init','wwbbm_ch');
function wwbbm_ch(){
global $wpdb,$woocommerce;
$order      = wc_get_order(117);
echo '<pre>';
// print_r($order);
echo $order->status;
echo '</pre>';
if($order->has_status( 'pending' )) {
echo 'Yes';
  }
die();
}

// add_action( 'woocommerce_checkout_order_processed', 'wbbm_order_status_before_payment', 10, 3 );
function wbbm_order_status_before_payment( $order_id, $posted_data, $order ){
    $order->update_status( 'processing' );
}




function wbbm_get_all_stops_after_this($bus_id,$val,$end){
    $start_stops = get_post_meta($bus_id,'wbbm_bus_bp_stops',true);
    $all_stops = array();
    foreach ($start_stops as $_start_stops) {
      $all_stops[] = $_start_stops['wbbm_bus_bp_stops_name'];
    }
    $pos        = array_search($val, $all_stops);
    $pos2       = array_search($end, $all_stops);
    unset($all_stops[$pos]);
    unset($all_stops[$pos2]);
    return $all_stops;
}


function wbbm_add_passenger($order_id,$bus_id,$user_id,$start,$next_stops,$end,$user_name,$user_email,$user_phone,$user_gender,$user_address,$user_type,$b_time,$j_time,$adult,$adult_per_price,$child,$child_per_price,$total_price,$item_quantity,$j_date,$add_datetime,$status){
    global $wpdb;
    $table_name = $wpdb->prefix . 'wbbm_bus_booking_list';
    $add_datetime = current_time("Y-m-d h:i:s");
    $wpdb->insert( 
        $table_name, 
        array( 
            'order_id'        => $order_id, 
            'bus_id'          => $bus_id,
            'user_id'         => $user_id,
            'boarding_point'  => $start,
            'next_stops'      => $next_stops,            
            'droping_point'   => $end,
            'user_name'       => $user_name,
            'user_email'      => $user_email,
            'user_phone'      => $user_phone,
            'user_gender'     => $user_gender,
            'user_address'    => $user_address,
            'user_type'       => $user_type,
            'bus_start'       => $b_time,
            'user_start'      => $j_time,
            'total_adult'     => $adult,
            'per_adult_price' => $adult_per_price,
            'total_child'     => $child,
            'per_child_price' => $child_per_price,
            'total_price'     => $total_price,
            'seat'            => $item_quantity,
            'journey_date'    => $j_date,
            'booking_date'    => $add_datetime,
            'status'          => $status
        ), 
        array( 
            '%d', 
            '%d',
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d', 
            '%d',
            '%d',
            '%d', 
            '%d',                                    
            '%s',
            '%s',
            '%s',
            '%d'
        ) 
     );
     
}



add_action( 'woocommerce_checkout_order_processed', 'wbbm_add_passenger_to_db',  1, 1  );
function wbbm_add_passenger_to_db($order_id){
global $wpdb;
   // Getting an instance of the order object
    $order      = wc_get_order( $order_id );
    $order_meta = get_post_meta($order_id); 

   # Iterating through each order items (WC_Order_Item_Product objects in WC 3+)
    foreach ( $order->get_items() as $item_id => $item_values ) {
        $product_id = $item_values->get_product_id(); 
        $item_data = $item_values->get_data();
        $product_id = $item_data['product_id'];
        $item_quantity = $item_values->get_quantity();
        $product = get_page_by_title( $item_data['name'], OBJECT, 'wbbm_bus' );
        $event_name = $item_data['name'];
        $event_id = $product->ID;
        $item_id = $item_id;
    // $item_data = $item_values->get_data();
  
$user_id          = $order_meta['_customer_user'][0];
$order_status     = $order->status;
$eid              = wbbm_get_order_meta($item_id,'_wbbm_bus_id');

if (get_post_type($eid) == 'wbbm_bus') { 
$user_info_arr      = wbbm_get_order_meta($item_id,'_wbbm_passenger_info');
$start              = wbbm_get_order_meta($item_id,'Start');
$end                = wbbm_get_order_meta($item_id,'End');
$j_date             = wbbm_get_order_meta($item_id,'Date');
$j_time             = wbbm_get_order_meta($item_id,'Time');
$bus_id             = wbbm_get_order_meta($item_id,'_bus_id');
$b_time             = wbbm_get_order_meta($item_id,'_btime');

$adult             = wbbm_get_order_meta($item_id,'Adult');
$child             = wbbm_get_order_meta($item_id,'Child');
$adult_per_price   = wbbm_get_order_meta($item_id,'_adult_per_price');
$child_per_price   = wbbm_get_order_meta($item_id,'_child_per_price');
$total_price       = wbbm_get_order_meta($item_id,'_total_price');
$next_stops        = maybe_serialize(wbbm_get_all_stops_after_this($bus_id,$start,$end));

$usr_inf            = unserialize($user_info_arr);
$counter            = 0;
$_seats             ='None';

$item_quantity  = ($adult+$child);
// $_seats         =   $item_quantity;
  // foreach ($seats as $_seats) {
for ($x = 1; $x <= $item_quantity; $x++) {

    // if(!empty($_seats)){

      if($usr_inf[$counter]['wbbm_user_name']){
        $user_name = $usr_inf[$counter]['wbbm_user_name'];
      }else{
        $user_name = "";
      }
      if($usr_inf[$counter]['wbbm_user_email']){
        $user_email = $usr_inf[$counter]['wbbm_user_email'];
      }else{
        $user_email = "";
      }
      if($usr_inf[$counter]['wbbm_user_phone']){
        $user_phone = $usr_inf[$counter]['wbbm_user_phone'];
      }else{
        $user_phone = "";
      }
      if($usr_inf[$counter]['wbbm_user_address']){
        $user_address = $usr_inf[$counter]['wbbm_user_address'];
      }else{
        $user_address = "";
      }
      if($usr_inf[$counter]['wbbm_user_gender']){
        $user_gender = $usr_inf[$counter]['wbbm_user_gender'];
      }else{
        $user_gender = "";
      }        
      if($usr_inf[$counter]['wbbm_user_type']){
        $user_type = $usr_inf[$counter]['wbbm_user_type'];
      }else{
        $user_type = "Adult/Child";
      }            
$_seats = $item_quantity;
$check_before_add       = wbbm_get_order_seat_check($bus_id,$order_id,$user_type,$b_time,$j_date);
if($check_before_add==0){
    
        wbbm_add_passenger($order_id,$bus_id,$user_id,$start,$next_stops,$end,$user_name,$user_email,$user_phone,$user_gender,$user_address,$user_type,$b_time,$j_time,$adult,$adult_per_price,$child,$child_per_price,$total_price,$item_quantity,$j_date,$add_datetime,0);
     
    }
    // }
    $counter++;
  }

}

}

}




add_action('woocommerce_order_status_changed', 'wbbm_bus_ticket_seat_management', 10, 4);
function wbbm_bus_ticket_seat_management( $order_id, $from_status, $to_status, $order ) {
global $wpdb;
   // Getting an instance of the order object
    $order      = wc_get_order( $order_id );
    $order_meta = get_post_meta($order_id); 

   # Iterating through each order items (WC_Order_Item_Product objects in WC 3+)
    foreach ( $order->get_items() as $item_id => $item_values ) {
        $product_id = $item_values->get_product_id(); 
        $item_data = $item_values->get_data();
        $product_id = $item_data['product_id'];
        $item_quantity = $item_values->get_quantity();
        $product = get_page_by_title( $item_data['name'], OBJECT, 'wbbm_bus' );
        $event_name = $item_data['name'];
        $event_id = $product->ID;
        $item_id = $item_id;
    // $item_data = $item_values->get_data();
  
$user_id          = $order_meta['_customer_user'][0];
$order_status     = $order->status;
$eid              = wbbm_get_order_meta($item_id,'_wbbm_bus_id');

if (get_post_type($eid) == 'wbbm_bus') { 


$user_info_arr      = wbbm_get_order_meta($item_id,'_wbbm_passenger_info');
$start              = wbbm_get_order_meta($item_id,'Start');
$end                = wbbm_get_order_meta($item_id,'End');
$j_date             = wbbm_get_order_meta($item_id,'Date');
$j_time             = wbbm_get_order_meta($item_id,'Time');
$bus_id             = wbbm_get_order_meta($item_id,'_bus_id');
$b_time             = wbbm_get_order_meta($item_id,'_btime');

$adult             = wbbm_get_order_meta($item_id,'Adult');
$child             = wbbm_get_order_meta($item_id,'Child');
$adult_per_price   = wbbm_get_order_meta($item_id,'_adult_per_price');
$child_per_price   = wbbm_get_order_meta($item_id,'_child_per_price');
$total_price       = wbbm_get_order_meta($item_id,'_total_price');
$next_stops        = maybe_serialize(wbbm_get_all_stops_after_this($bus_id,$start,$end));


$usr_inf            = unserialize($user_info_arr);
$counter            = 0;
$_seats             ='None';

$item_quantity  = ($adult+$child);
// $_seats         =   $item_quantity;
  // foreach ($seats as $_seats) {
for ($x = 1; $x <= $item_quantity; $x++) {

    // if(!empty($_seats)){

      if($usr_inf[$counter]['wbbm_user_name']){
        $user_name = $usr_inf[$counter]['wbbm_user_name'];
      }else{
        $user_name = "";
      }
      if($usr_inf[$counter]['wbbm_user_email']){
        $user_email = $usr_inf[$counter]['wbbm_user_email'];
      }else{
        $user_email = "";
      }
      if($usr_inf[$counter]['wbbm_user_phone']){
        $user_phone = $usr_inf[$counter]['wbbm_user_phone'];
      }else{
        $user_phone = "";
      }
      if($usr_inf[$counter]['wbbm_user_address']){
        $user_address = $usr_inf[$counter]['wbbm_user_address'];
      }else{
        $user_address = "";
      }
      if($usr_inf[$counter]['wbbm_user_gender']){
        $user_gender = $usr_inf[$counter]['wbbm_user_gender'];
      }else{
        $user_gender = "";
      }        
      if($usr_inf[$counter]['wbbm_user_type']){
        $user_type = $usr_inf[$counter]['wbbm_user_type'];
      }else{
        $user_type = "Adult";
      }            
$_seats = $item_quantity;
$check_before_add       = wbbm_get_order_seat_check($bus_id,$order_id,$user_type,$b_time,$j_date);
    // }
    $counter++;
  }








if($order->has_status( 'processing' ) || $order->has_status( 'pending' ) || $order->has_status( 'on-hold' ) ) {

// if($order_status=='processing'||$order_status=='pending'||$order_status=='on-hold'){

    $status = 1;
    $table_name = $wpdb->prefix . 'wbbm_bus_booking_list';
    $wpdb->query( $wpdb->prepare("UPDATE $table_name 
                SET status = %d 
             WHERE order_id = %d
             AND bus_id = %d",$status, $order_id,$event_id)
    );


  }
  



if($order->has_status( 'cancelled' )) {
    $status = 3;
    $table_name = $wpdb->prefix . 'wbbm_bus_booking_list';
    $wpdb->query( $wpdb->prepare("UPDATE $table_name 
                SET status = %d 
             WHERE order_id = %d
             AND bus_id = %d",$status, $order_id,$event_id)
    );

}



if( $order->has_status( 'completed' )) {

    $status = 2;
    $table_name = $wpdb->prefix . 'wbbm_bus_booking_list';
    $wpdb->query( $wpdb->prepare("UPDATE $table_name 
                SET status = %d 
             WHERE order_id = %d
             AND bus_id = %d",$status, $order_id,$event_id)
    );

}
}
}
}


function wbbm_array_strip($string, $allowed_tags = NULL)
{
    if (is_array($string))
    {
        foreach ($string as $k => $v)
        {
            $string[$k] = wbbm_array_strip($v, $allowed_tags);
        }
        return $string;
    }
    return strip_tags($string, $allowed_tags);
}


function wbbm_find_product_in_cart($id) {
 
    $product_id = $id;
    $in_cart = false;
 
foreach( WC()->cart->get_cart() as $cart_item ) {
   $product_in_cart = $cart_item['product_id'];
   if ( $product_in_cart === $product_id ) $in_cart = true;
}
 
    if ( $in_cart ) {
      return 'into-cart';
    }else{
      return 'not-in-cart';
    }
}



add_action('show_seat_form','wbbm_seat_form');
function wbbm_seat_form($start,$end, $price_arr){
    $id = get_the_id();
    $boarding  = isset( $_GET['bus_start_route'] ) ? strip_tags($_GET['bus_start_route']) : '';
$dropping  = isset( $_GET['bus_end_route'] ) ? strip_tags($_GET['bus_end_route']) : '';
        $seat_price_adult = mage_seat_price($id,$boarding,$dropping,true);
    $seat_price_child = mage_seat_price($id,$boarding,$dropping,false);
  ob_start();
  ?>
                    <div class="seat-no-form"> 
                            <?php  
                            $adult_fare =  wbbm_get_bus_price($start,$end, $price_arr);
                            if($adult_fare>0){
                            ?>   
                            <label for='quantity_<?php echo get_the_id(); ?>'>
                            Adult (<?php //echo get_woocommerce_currency_symbol(); ?><?php echo wc_price($seat_price_adult); ?> )
                                <input type="number" id="quantity_<?php echo get_the_id(); ?>" class="input-text qty text bqty" step="1" min="0" max="<?php echo $available_seat; ?>" name="adult_quantity" value="" title="Qty" size="4" pattern="[0-9]*" inputmode="numeric" required aria-labelledby="" placeholder='0' />
                            </label>
                            <?php 
                            } 
                            $child_fare =  wbbm_get_bus_price_child($start,$end, $price_arr);
                            if($child_fare>0){
                            ?>
                            <label for='child_quantity_<?php echo get_the_id(); ?>'>
                                Child (<?php //echo get_woocommerce_currency_symbol(); ?><?php echo wc_price($seat_price_child); ?>)
                                <input type="number" id="child_quantity_<?php echo get_the_id(); ?>" class="input-text qty text bqty" step="1" min="0" max="<?php echo $available_seat; ?>" name="child_quantity" value="0" title="Qty" size="4" pattern="[0-9]*" inputmode="numeric" required aria-labelledby="" placeholder='0' />
                            </label>
                        <?php } ?>
                    </div>   
  <?php
  $seat_form = ob_get_clean();
  echo $seat_form;
}

function wbbm_check_od_in_range($start_date, $end_date, $j_date){
  // Convert to timestamp
  $start_ts = strtotime($start_date);
  $end_ts = strtotime($end_date);
  $user_ts = strtotime($j_date);

  // Check that user date is between start & end
   if(($user_ts >= $start_ts) && ($user_ts <= $end_ts)){
    return 'yes';
   }else{
    return 'no';
   }
}


add_filter('woocommerce_cart_item_price', 'wbbm_avada_mini_cart_price_fixed', 100, 3);
function wbbm_avada_mini_cart_price_fixed($price,$cart_item,$r){
    $price = wc_price($cart_item['line_total']);
    return $price;
  }



/**
 * The magical Datetime Function, Just call this function where you want display date or time, Pass the date or time and the format this will be return the date or time in the current wordpress saved datetime format and according the timezone.
 */
function get_wbbm_datetime($date,$type){
	$date_format        = get_option( 'date_format' );
	$time_format        = get_option( 'time_format' );
	$wpdatesettings     = $date_format.'  '.$time_format; 
	$timezone           = wp_timezone_string();
	$timestamp          = strtotime( $date . ' '. $timezone);
	
	  if($type == 'date'){
		  return wp_date( $date_format, $timestamp );    
	  }
	  if($type == 'date-time'){
		   return wp_date( $wpdatesettings, $timestamp );    
	  }
	  if($type == 'date-text'){
		   
		  return wp_date( $date_format, $timestamp );    
	  }
	
	  if($type == 'date-time-text'){
		   return wp_date( $wpdatesettings, $timestamp );    
	  }
	  if($type == 'time'){
		  return wp_date( $time_format, $timestamp );
    }
    
	  if($type == 'time-raw'){
		  return wp_date( 'h:i A', $timestamp );
	  }
	  
	  if($type == 'day'){
		   return wp_date( 'd', $timestamp );    
	  }
	  if($type == 'month'){
		   return wp_date( 'M', $timestamp );    
	  }
	}


function wbbm_get_page_list(){
  $args = array(
    'post_type' => 'page',
    'posts_per_page' => -1
  );

  $loop = new WP_Query($args);
  $page = [];
  foreach ($loop->posts as $_page) {
    # code...
    $page[$_page->post_name] = $_page->post_title;
  }
  return $page;
}







}else{
function wbbm_admin_notice_wc_not_active() {
  $class = 'notice notice-error';
  $message = __( 'Multipurpose Ticket Booking Manager  Plugin is Dependent on WooCommerce, But currently WooCommerce is not Active. Please Active WooCommerce plugin first', 'bus-booking-manager' );
  printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
}
add_action( 'admin_notices', 'wbbm_admin_notice_wc_not_active' );
}