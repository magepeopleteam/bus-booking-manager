<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.


add_shortcode( 'bus-list', 'wbbm_bus_list' );
function wbbm_bus_list($atts, $content=null){
		$defaults = array(
			"cat"					=> "0",
			"show"					=> "20",
		);
		$params 					= shortcode_atts($defaults, $atts);
		$cat						= $params['cat'];
		$show						= $params['show'];
ob_start();

$paged = get_query_var("paged")?get_query_var("paged"):1;
if($cat>0){
     $args_search_qqq = array (
                     'post_type'        => array( 'wbbm_bus' ),
                     'paged'            => $paged,
                     'posts_per_page'   => $show,
                      'tax_query'       => array(
								array(
							            'taxonomy'  => 'wbbm_bus_cat',
							            'field'     => 'term_id',
							            'terms'     => $cat
							        )
                        )

                );
 }else{
     $args_search_qqq = array (
                     'post_type'        => array( 'wbbm_bus' ),
                     'paged'             => $paged,
                     'posts_per_page'   => $show

                ); 	
 }

 	$loop = new WP_Query( $args_search_qqq );
?>
<div class="wbbm-bus-list-sec wbbm-bus-grid">
	
<?php 
	while ($loop->have_posts()) {
	$loop->the_post(); 
	$bp_arr = get_post_meta(get_the_id(),'wbbm_bus_bp_stops',true); 
	$dp_arr = get_post_meta(get_the_id(),'wbbm_bus_next_stops',true);
	$price_arr = get_post_meta(get_the_id(),'wbbm_bus_prices',true);
	$total_dp = count($dp_arr)-1;
	$term = get_the_terms(get_the_id(),'wbbm_bus_cat');
?>

<div class="wbbm-bus-lists">
	<div class="bus-thumb">
		<?php the_post_thumbnail('full'); ?>
	</div>
	<div class="wbbm-bus-info">
	<h2><?php the_title(); ?></h2>
	<ul>
		<li><strong><?php _e('Type:','bus-booking-manager'); ?></strong> <?php echo $term[0]->name; ?></li>
		<li><strong><?php _e('Bus No:','bus-booking-manager'); ?></strong> <?php echo get_post_meta(get_the_id(),'wbbm_bus_no',true); ?></li>
		<li><strong><?php _e('Total Seat:','bus-booking-manager'); ?></strong> <?php echo get_post_meta(get_the_id(),'wbbm_total_seat',true); ?> </li>
		<li><strong><?php _e('Start From:','bus-booking-manager'); ?></strong> <?php echo $start = $bp_arr[0]['wbbm_bus_bp_stops_name']; ?> </li>
		<li><strong><?php _e('End at:','bus-booking-manager'); ?></strong> <?php echo $end = $dp_arr[$total_dp]['wbbm_bus_next_stops_name']; ?> </li>
		<li><strong><?php _e('Fare:','bus-booking-manager'); ?></strong> <?php wc_price(wbbm_get_bus_price($start,$end, $price_arr)); ?> </li>
	</ul>
<a href="<?php the_permalink(); ?>" class="btn wbbm-btn"><?php _e('Book Now','bus-booking-manager'); ?></a>
	</div>
</div>
<?php
}
?>
<div class="row">
	<div class="col-md-12"><?php
	$pargs = array(
		"current"=>$paged,
		"total"=>$loop->max_num_pages
	);
	echo "<div class='pagination-sec'>".paginate_links($pargs)."</div>";
	?>	
	</div>
</div>
</div>
<?php
$content = ob_get_clean();
return $content;
}









//add_shortcode( 'bus-search', 'wbbm_bus_search' );
function wbbm_bus_search($atts, $content=null){
		$defaults = array(
			"cat"					=> "0"
		);
		$params 					= shortcode_atts($defaults, $atts);
		$cat						= $params['cat'];
ob_start();

$start 	= isset( $_GET['bus_start_route'] ) ? strip_tags($_GET['bus_start_route']) : '';
$end 	= isset( $_GET['bus_end_route'] ) ? strip_tags($_GET['bus_end_route']) : '';
$date 	= isset( $_GET['j_date'] ) ? strip_tags($_GET['j_date']) : date('Y-m-d');
$r_date = isset( $_GET['r_date'] ) ? strip_tags($_GET['r_date']) : date('Y-m-d');
$today 	= date('Y-m-d');

$the_rday = date('D', strtotime($r_date));
$rod_name = 'od_'.$the_rday;
?>
<div class="wbbm-search-form-sec">
	<?php do_action( 'woocommerce_before_single_product' ); ?>
	<form action="" method="get">
   <?php wbbm_bus_search_fileds($start,$end,$date,$r_date); //do_action('wbbm_search_fields'); ?>
	</form>
</div>
<div class="wbbm-search-result-list">
<?php
if(isset($_GET['bus_start_route']) && ($_GET['bus_end_route']) && ($_GET['j_date'])){

	$the_day = date('D', strtotime($date));
	$od_name = 'od_'.$the_day;


	     $args_search_qqq = array (
                     'post_type'        => array( 'wbbm_bus' ),
                     'posts_per_page'   => -1,
                     'order'             => 'ASC',
                     'orderby'           => 'meta_value',
                     'meta_key'          => 'wbbm_bus_start_time',
					 'meta_query'    => array(
					    'relation' => 'AND',
					    array(
					        'key'       => 'wbbm_bus_bp_stops',
					        'value'     => $start,
					        'compare'   => 'LIKE',
					    ),

					    array(
					        'key'       => 'wbbm_bus_next_stops',
					        'value'     => $end,
					        'compare'   => 'LIKE',
                        ),
					)

                );


	$loop = new WP_Query($args_search_qqq);
	?>
 <div class="selected_route">
 	 <strong><?php _e('Route','bus-booking-manager'); ?></strong>
    <?php printf( '<span>%s <i class="fa fa-long-arrow-right"></i> %s<span>', $start, $end ); ?> <strong><?php _e('Date:','bus-booking-manager'); ?></strong> <?php echo date('D, d M Y', strtotime($date)); ?>
 </div>
	<?php
	while ($loop->have_posts()) {
	$loop->the_post();
	$values 		= get_post_custom( get_the_id() );
	$term 			= get_the_terms(get_the_id(),'wbbm_bus_cat');

	$total_seat 	= $values['wbbm_total_seat'][0];
	$sold_seat 		= wbbm_get_available_seat(get_the_id(),$date);
	$available_seat = ($total_seat - $sold_seat);

	$price_arr 		= get_post_meta(get_the_id(),'wbbm_bus_prices',true);
	$bus_bp_array 	= get_post_meta(get_the_id(),'wbbm_bus_bp_stops',true);
	$bus_dp_array 	= get_post_meta(get_the_id(),'wbbm_bus_next_stops',true);
	$bp_time 		= wbbm_get_bus_start_time($start, $bus_bp_array);
	$dp_time 		= wbbm_get_bus_end_time($end, $bus_dp_array);
    $od_start_date  = get_post_meta(get_the_id(),'wbtm_od_start',true);
    $od_end_date    = get_post_meta(get_the_id(),'wbtm_od_end',true);
	$oday           = get_post_meta(get_the_id(),$od_name,true);
    $od_range       = wbbm_check_od_in_range($od_start_date, $od_end_date, $date);


   if($od_range =='no'){
	if($oday !='yes'){

$current_date = current_time( 'Y-m-d' );

	$start_time_row = strtotime($bp_time);

	$current_time = current_time( 'timestamp' );

if($date==$current_date){
	if($start_time_row > $current_time && $date==$current_date){

?>

<div class="wbbm-bus-lists <?php echo wbbm_find_product_in_cart(get_the_id()); ?>">
	<div class="bus-thumb">
		<?php the_post_thumbnail('full'); ?>
	</div>
	<div class="wbbm-bus-info">

	<h2><?php the_title(); ?></h2>

	<ul>
		<li><strong><?php _e('Type:','bus-booking-manager'); ?></strong> <?php echo $term[0]->name; ?></li>
		<li><strong><?php _e('Available Seat:','bus-booking-manager'); ?></strong> <?php echo $available_seat; ?> </li>
		<li><strong><?php _e('Start Time:','bus-booking-manager'); ?></strong> <?php echo date('h:i A', strtotime($bp_time)); ?> </li>
		<li><strong><?php _e('End Time:','bus-booking-manager'); ?></strong> <?php echo date('h:i A', strtotime($dp_time)); ?> </li>
		<!-- <li><strong>Fare:</strong> <?php echo get_woocommerce_currency_symbol().wbbm_get_bus_price($start,$end, $price_arr); ?> </li> -->
	</ul>


<?php if(!empty($start) && !empty($end)){ ?>

<div class="wbbm-bus-passenger-info">
<form action="" method='post'>
	<label for="quantity_<?php echo get_the_id(); ?>">

		 <?php
		 if($available_seat>0){
		 	_e('Total Passenger:','bus-booking-manager');
		 wbbm_seat_form($start,$end, $price_arr);
		 ?>

			<button id='bus-booking-btn' type="submit" name="add-to-cart" value="<?php echo esc_attr(get_the_id()); ?>" class="single_add_to_cart_button button alt btn-mep-event-cart"><?php _e('Book Now','bus-booking-manager'); ?></button></label>
			<?php }else{
			_e('No Seat Available','bus-booking-manager');
		} ?>
	<input type="hidden" id='bus_id' name="bus_id" value="<?php echo get_the_id(); ?>">
	<div class='passengger-list' id="divParent_<?php echo get_the_id(); ?>"></div>
<div class='passengger-list' id="divParent_child_<?php echo get_the_id(); ?>"></div>
	<input type="hidden"  name='journey_date' class="text" value='<?php echo $date; ?>'>
	<input type="hidden" name='start_stops' value="<?php echo $start; ?>" class="hidden">
	<input type="hidden" value="<?php echo date('h:i A', strtotime($bp_time)); ?>" name="user_start_time" id='user_start_time'>
	<input type="hidden" name="bus_start_time" value="<?php echo date('h:i A', strtotime($bp_time)); ?>" id='bus_start_time'>
	<input type='hidden' value='<?php echo $end; ?>' name='end_stops'/>
</form>
</div>
<?php }else{
	if(empty($start)){
		?>
		<div class="wbtm-notice"><?php _e('Please Select From Location To Place Order','bus-booking-manager'); ?></div>
		<?php
	}
	if(empty($end)){
		?>
		<div class="wbtm-notice"><?php _e('Please Select Destination Location To Place Order.','bus-booking-manager'); ?></div>
		<?php
	}
} ?>
<script>
jQuery('#quantity_<?php echo get_the_id(); ?>').on('change', function () {
        var input       = jQuery(this).val() || 0;
        var children    = jQuery('#divParent_<?php echo get_the_id(); ?> > div').length || 0;
        if (input < children) {
            jQuery('#divParent_<?php echo get_the_id(); ?>').empty();
            children = 0;
        }
        for (var i = children + 1; i <= input; i++) {
            jQuery('#divParent_<?php echo get_the_id(); ?>').append(
                jQuery('<div/>')
                .attr("id", "newDiv" + i)
                .html("<?php do_action('wbbm_reg_fields'); ?>")
            );
        }
});


jQuery('#child_quantity_<?php echo get_the_id(); ?>').on('change', function () {
        var input       = jQuery(this).val() || 0;
        var children    = jQuery('#divParent_child_<?php echo get_the_id(); ?> > div').length || 0;
        if (input < children) {
            jQuery('#divParent_child_<?php echo get_the_id(); ?>').empty();
            children = 0;
        }
        for (var i = children + 1; i <= input; i++) {
            jQuery('#divParent_child_<?php echo get_the_id(); ?>').append(
                jQuery('<div/>')
                .attr("id", "newDiv" + i)
                .html("<?php do_action('wbbm_reg_fields_child'); ?>")
            );
        }
});
</script>

	</div>
</div>

<?php
}

}else{
	?>

<div class="wbbm-bus-lists <?php echo wbbm_find_product_in_cart(get_the_id()); ?>">
	<div class="bus-thumb">
		<?php the_post_thumbnail('full'); ?>
	</div>
	<div class="wbbm-bus-info">
	<h2><?php the_title(); ?></h2>

<form action="" method='post'>
<div class="wbbm-seat-informations">
<div class="wbbm-fpart">
 <ul>
	<li><strong> <?php echo date('h:i A', strtotime($bp_time)); ?></strong> <i class="fa fa-long-arrow-right"></i> <?php echo $start; ?> </li>
	<li><?php echo date('h:i A', strtotime($dp_time)); ?> <i class="fa fa-long-arrow-right"></i> <?php echo $end; ?></li>
 </ul>
</div>
<div class="wbbm-fpart">
		<ul>
		<li><strong><?php echo $available_seat; ?></strong> <?php _e('Seat Available','bus-booking-manager'); ?>  </li>
		<!-- <li><strong><?php echo get_woocommerce_currency_symbol().wbbm_get_bus_price($start,$end, $price_arr); ?></strong> </li> -->
	</ul>
</div>
<div class="wbbm-fpart wbbm-list-btn-li">
<?php if(!empty($start) && !empty($end)){ ?>
<div class="wbbm-bus-passenger-info">
	<label for="quantity_<?php echo get_the_id(); ?>">

		 <?php
		 if($available_seat>0){
		 	_e('Total Passenger:','bus-booking-manager');
		 wbbm_seat_form($start,$end, $price_arr);
		 ?>

			<button id='bus-booking-btn' type="submit" name="add-to-cart" value="<?php echo esc_attr(get_the_id()); ?>" class="single_add_to_cart_button button alt btn-mep-event-cart"><?php _e('Book Now','bus-booking-manager'); ?></button></label>
			<?php }else{
			_e('No Seat Available','bus-booking-manager');
		} ?>
	<input type="hidden" id='bus_id' name="bus_id" value="<?php echo get_the_id(); ?>">

	<input type="hidden"  name='journey_date' class="text" value='<?php echo $date; ?>'>
	<input type="hidden" name='start_stops' value="<?php echo $start; ?>" class="hidden">
	<input type="hidden" value="<?php echo date('h:i A', strtotime($bp_time)); ?>" name="user_start_time" id='user_start_time'>
	<input type="hidden" name="bus_start_time" value="<?php echo date('h:i A', strtotime($bp_time)); ?>" id='bus_start_time'>
	<input type='hidden' value='<?php echo $end; ?>' name='end_stops'/>
</div>
<?php }else{
	if(empty($start)){
		?>
		<div class="wbtm-notice"><?php _e('Please Select From Location To Place Order.','bus-booking-manager'); ?></div>
		<?php
	}
	if(empty($end)){
		?>
		<div class="wbtm-notice"><?php _e('Please Select Destination Location To Place Order.','bus-booking-manager'); ?></div>
		<?php
	}
} ?>
</div>

</div>
	<div class='passengger-list' id="divParent_<?php echo get_the_id(); ?>"></div>
<div class='passengger-list' id="divParent_child_<?php echo get_the_id(); ?>"></div>
</form>





<script>
jQuery('#quantity_<?php echo get_the_id(); ?>').on('change', function () {
        var input       = jQuery(this).val() || 0;
        var children    = jQuery('#divParent_<?php echo get_the_id(); ?> > div').length || 0;
        if (input < children) {
            jQuery('#divParent_<?php echo get_the_id(); ?>').empty();
            children = 0;
        }
        for (var i = children + 1; i <= input; i++) {
            jQuery('#divParent_<?php echo get_the_id(); ?>').append(
                jQuery('<div/>')
                .attr("id", "newDiv" + i)
                .html("<?php do_action('wbbm_reg_fields'); ?>")
            );
        }
});


jQuery('#child_quantity_<?php echo get_the_id(); ?>').on('change', function () {
        var input       = jQuery(this).val() || 0;
        var children    = jQuery('#divParent_child_<?php echo get_the_id(); ?> > div').length || 0;
        if (input < children) {
            jQuery('#divParent_child_<?php echo get_the_id(); ?>').empty();
            children = 0;
        }
        for (var i = children + 1; i <= input; i++) {
            jQuery('#divParent_child_<?php echo get_the_id(); ?>').append(
                jQuery('<div/>')
                .attr("id", "newDiv" + i)
                .html("<?php do_action('wbbm_reg_fields_child'); ?>")
            );
        }
});
</script>

	</div>
</div>
	<?php
}
}
   }
}
wp_reset_query();




if($r_date>$today){
?>

 <div class="selected_route">
 	 <strong>Route</strong>
    <?php printf( '<span>%s <i class="fa fa-long-arrow-right"></i> %s<span>', $end, $start ); ?> <strong><?php _e('Date:','bus-booking-manager'); ?></strong> <?php echo date('D, d M Y', strtotime($r_date)); ?>
 </div>
<?php
	     $args_search_qqq = array (
                     'post_type'        => array( 'wbbm_bus' ),
                     'posts_per_page'   => -1,
                     'order'             => 'ASC',
                     'orderby'           => 'meta_value',
                     'meta_key'          => 'wbbm_bus_start_time',
					 'meta_query'    => array(
					    'relation' => 'AND',
					    array(
					        'key'       => 'wbbm_bus_bp_stops',
					        'value'     => $end,
					        'compare'   => 'LIKE',
					    ),

					    array(
					        'key'       => 'wbbm_bus_next_stops',
					        'value'     => $start,
					        'compare'   => 'LIKE',
                        ),
					)

                );


	$loop = new WP_Query($args_search_qqq);
	while ($loop->have_posts()) {
	$loop->the_post();
	$values 		= get_post_custom( get_the_id() );
	$term 			= get_the_terms(get_the_id(),'wbbm_bus_cat');

	$total_seat 	= $values['wbbm_total_seat'][0];
	$sold_seat 		= wbbm_get_available_seat(get_the_id(),$r_date);
	$available_seat = ($total_seat - $sold_seat);

	$price_arr 		= get_post_meta(get_the_id(),'wbbm_bus_prices',true);
	$bus_bp_array 	= get_post_meta(get_the_id(),'wbbm_bus_bp_stops',true);
	$bus_dp_array 	= get_post_meta(get_the_id(),'wbbm_bus_next_stops',true);
	$bp_time 		= wbbm_get_bus_start_time($end, $bus_bp_array);
	$dp_time 		= wbbm_get_bus_end_time($start, $bus_dp_array);

	$od_start_date  = get_post_meta(get_the_id(),'wbtm_od_start',true);
    $od_end_date    = get_post_meta(get_the_id(),'wbtm_od_end',true);
	$oday           = get_post_meta(get_the_id(),$rod_name,true);
    $od_range       = wbbm_check_od_in_range($od_start_date, $od_end_date, $r_date);


   if($od_range =='no'){
	if($oday !='yes'){
?>

<div class="wbbm-bus-lists <?php echo wbbm_find_product_in_cart(get_the_id()); ?>">
	<div class="bus-thumb">
		<?php the_post_thumbnail('full'); ?>
	</div>
	<div class="wbbm-bus-info">
	<h2><?php the_title(); ?></h2>


<form action="" method='post'>
<div class="wbbm-seat-informations">
<div class="wbbm-fpart">
 <ul>
	<li><strong> <?php echo date('h:i A', strtotime($bp_time)); ?></strong> <i class="fa fa-long-arrow-right"></i> <?php echo $end; ?> </li>
	<li><?php echo date('h:i A', strtotime($dp_time)); ?> <i class="fa fa-long-arrow-right"></i> <?php echo $start; ?></li>
 </ul>
</div>
<div class="wbbm-fpart">
		<ul>
		<li><strong><?php echo $available_seat; ?></strong> <?php _e('Seat Available','bus-booking-manager'); ?>  </li>
		<!-- <li><strong><?php echo get_woocommerce_currency_symbol().wbbm_get_bus_price($start,$end, $price_arr); ?></strong> </li> -->
	</ul>
</div>
<div class="wbbm-fpart wbbm-list-btn-li">
<?php if(!empty($start) && !empty($end)){ ?>
<div class="wbbm-bus-passenger-info">
	<label for="quantity_<?php echo get_the_id(); ?>">

	 <?php
	 if($available_seat>0){
	 _e('Total Passenger:','bus-booking-manager');
	 wbbm_seat_form($end,$start, $price_arr);
	 ?>

	<button id='bus-booking-btn' type="submit" name="add-to-cart" value="<?php echo esc_attr(get_the_id()); ?>" class="single_add_to_cart_button button alt btn-mep-event-cart"><?php _e('Book Now','bus-booking-manager'); ?></button></label>
	<?php }else{
	_e('No Seat Available','bus-booking-manager');
	} ?>
	<input type="hidden" id='bus_id' name="bus_id" value="<?php echo get_the_id(); ?>">

	<input type="hidden"  name='journey_date' class="text" value='<?php echo $r_date; ?>'>
	<input type="hidden" name='start_stops' value="<?php echo $end; ?>" class="hidden">
	<input type="hidden" value="<?php echo date('h:i A', strtotime($bp_time)); ?>" name="user_start_time" id='user_start_time'>
	<input type="hidden" name="bus_start_time" value="<?php echo date('h:i A', strtotime($bp_time)); ?>" id='bus_start_time'>
	<input type='hidden' value='<?php echo $start; ?>' name='end_stops'/>
</div>
<?php }else{
	if(empty($start)){
		?>
		<div class="wbtm-notice"><?php _e('Please Select From Location To Place Order.','bus-booking-manager'); ?></div>
		<?php
	}
	if(empty($end)){
		?>
		<div class="wbtm-notice"><?php _e('Please Select Destination Location To Place Order.','bus-booking-manager'); ?></div>
		<?php
	}
} ?>
</div>

</div>
	<div class='passengger-list' id="divParent_<?php echo get_the_id(); ?>"></div>
<div class='passengger-list' id="divParent_child_<?php echo get_the_id(); ?>"></div>
</form>




<script>
jQuery('#quantity_<?php echo get_the_id(); ?>').on('change', function () {
        var input       = jQuery(this).val() || 0;
        var children    = jQuery('#divParent_<?php echo get_the_id(); ?> > div').length || 0;
        if (input < children) {
            jQuery('#divParent_<?php echo get_the_id(); ?>').empty();
            children = 0;
        }
        for (var i = children + 1; i <= input; i++) {
            jQuery('#divParent_<?php echo get_the_id(); ?>').append(
                jQuery('<div/>')
                .attr("id", "newDiv" + i)
                .html("<?php do_action('wbbm_reg_fields'); ?>")
            );
        }
});


jQuery('#child_quantity_<?php echo get_the_id(); ?>').on('change', function () {
        var input       = jQuery(this).val() || 0;
        var children    = jQuery('#divParent_child_<?php echo get_the_id(); ?> > div').length || 0;
        if (input < children) {
            jQuery('#divParent_child_<?php echo get_the_id(); ?>').empty();
            children = 0;
        }
        for (var i = children + 1; i <= input; i++) {
            jQuery('#divParent_child_<?php echo get_the_id(); ?>').append(
                jQuery('<div/>')
                .attr("id", "newDiv" + i)
                .html("<?php do_action('wbbm_reg_fields_child'); ?>")
            );
        }
});
</script>

	</div>
</div>

<?php
}
   }
}
wp_reset_query();
}
}

?>
</div>
<?php
$content = ob_get_clean();
return $content;
}



//add_shortcode( 'bus-search-form', 'wbbm_bus_search_form' );
function wbbm_bus_search_form($atts, $content=null){
		$defaults = array(
			"cat"					=> "0"
		);
		$params 					= shortcode_atts($defaults, $atts);
		$cat						= $params['cat'];
ob_start();

$start 	= isset( $_GET['bus_start_route'] ) ? strip_tags($_GET['bus_start_route']) : '';
$end 	= isset( $_GET['bus_end_route'] ) ? strip_tags($_GET['bus_end_route']) : '';
$date 	= isset( $_GET['j_date'] ) ? strip_tags($_GET['j_date']) : date('Y-m-d');
$r_date 	= isset( $_GET['r_date'] ) ? strip_tags($_GET['r_date']) : date('Y-m-d');

?>
<div class="wbbm-search-form-fields-sec wbbm-single-search-form">
    <h2><?php _e('BUY TICKET','bus-booking-manager'); ?></h2>
	<form action="<?php echo get_site_url(); ?>/bus-search/" method="get">
        <?php wbbm_bus_search_fileds($start,$end,$date,$r_date); //do_action('wbbm_search_fields'); ?>
	</form>
</div>
<?php
$content = ob_get_clean();
return $content;
}


add_shortcode( 'destination', 'wbbm_bus_popular_destination' );
function wbbm_bus_popular_destination($atts, $content=null){
		$defaults = array(
			"from"					=> "",
			"to"					=> "",
			"text"					=> "",
			"image"					=> "",
			"journey"				=> date('Y-m-d'),
			"return"				=> date('Y-m-d')
		);
		$params 					= shortcode_atts($defaults, $atts);
		$from						= $params['from'];
		$to							= $params['to'];
		$image						= $params['image'];
		$text						= $params['text'];
		$journey					= $params['journey'];
		$return						= $params['return'];
ob_start();
?>
<a href="<?php echo get_site_url(); ?>/bus-search?bus_start_route=<?php echo $from; ?>&bus_end_route=<?php echo $to; ?>&j_date=<?php echo $journey; ?>&r_date=<?php echo $return; ?>"><?php echo $text; ?></a>
<?php
$content = ob_get_clean();
return $content;
}