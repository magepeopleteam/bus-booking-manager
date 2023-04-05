<?php 
get_header();
the_post();
$term_id = get_queried_object()->term_id;
?>
<div class="mep-events-wrapper">
<div class="wbbm-bus-list-sec">
<div class="wbbm_cat-details">
	<h1><?php echo get_queried_object()->name; ?></h1>
	<p><?php echo get_queried_object()->description; ?></p>
</div>
<?php
     $args_search_qqq = array (
                     'post_type'        => array( 'wbbm_bus' ),
                     'posts_per_page'   => -1,
                      'tax_query'       => array(
								array(
							            'taxonomy'  => 'wbbm_bus_cat',
							            'field'     => 'term_id',
							            'terms'     => $term_id
							        )
                        )

                );
	 $loop = new WP_Query( $args_search_qqq );
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
	<h2><?php the_title(); ?></h2>
	<ul>
		<li><strong><?php _e('Type:','bus-booking-manager'); ?></strong> <?php echo $term[0]->name; ?></li>
		<li><strong><?php _e('Bus No:','bus-booking-manager'); ?></strong> <?php echo get_post_meta(get_the_id(),'wbbm_bus_no',true); ?></li>
		<li><strong><?php _e('Total Seat:','bus-booking-manager'); ?></strong> <?php echo get_post_meta(get_the_id(),'wbbm_total_seat',true); ?> </li>
		<li><strong><?php _e('Start From:','bus-booking-manager'); ?></strong> <?php echo $start = $bp_arr[0]['wbbm_bus_bp_stops_name'];; ?> </li>
		<li><strong><?php _e('End at:','bus-booking-manager'); ?></strong> <?php echo $end = $dp_arr[$total_dp]['wbbm_bus_next_stops_name'];; ?> </li>
		<li><strong><?php _e('Fare:','bus-booking-manager'); ?></strong> <?php wc_price(wbbm_get_bus_price($start,$end, $price_arr)); ?> </li>
	</ul>
	<a href="<?php the_permalink(); ?>" class='btn wbbm-bus-list-btn'><?php _e('Book Now','bus-booking-manager'); ?></a>
</div>
<?php
}
?>
</div>
<?php
get_footer();
?>