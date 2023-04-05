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
	//$term = get_the_terms(get_the_id(),'wbbm_bus_cat');
	$start = $bp_arr[0]['wbbm_bus_bp_stops_name'];
    $end = $dp_arr[$total_dp]['wbbm_bus_next_stops_name'];
    $type_id = get_post_meta(get_the_id(), 'wbbm_bus_category', true);
    if($type_id != ''){
        $type_array = get_term_by('term_id', $type_id, 'wbbm_bus_cat');
        $type_name = $type_array->name;
    } else {
        $type_name = '';
    }	
?>

<div class="wbbm-bus-lists">
	<div class="bus-thumb">
		<?php the_post_thumbnail('full'); ?>
	</div>
	<div class="wbbm-bus-info">
	<h2><?php the_title(); ?></h2>
	<ul>
		<li><strong>
		<?php echo wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec') : _e('Type','bus-booking-manager'); echo ':'; ?>
		</strong> <?php echo $type_name; ?></li>
		<li><strong>
		<?php echo wbbm_get_option('wbbm_bus_no_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_bus_no_text', 'wbbm_label_setting_sec') : _e('Bus No','bus-booking-manager'); echo ':'; ?>
		</strong> <?php echo get_post_meta(get_the_id(),'wbbm_bus_no',true); ?></li>
		<li><strong>
		
		<?php echo wbbm_get_option('wbbm_total_seat_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_total_seat_text', 'wbbm_label_setting_sec') : _e('Total Seat','bus-booking-manager'); echo ':'; ?>

		</strong> <?php echo get_post_meta(get_the_id(),'wbbm_total_seat',true); ?> </li>
		<li><strong>
		<?php echo wbbm_get_option('wbbm_start_from_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_start_from_text', 'wbbm_label_setting_sec') : _e('Start From','bus-booking-manager'); echo ':'; ?>	
		</strong> <?php echo $start; ?> </li>
		<li><strong>
		<?php echo wbbm_get_option('wbbm_end_to_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_end_to_text', 'wbbm_label_setting_sec') : _e('End at','bus-booking-manager'); echo ':'; ?>
	  	</strong> <?php echo $end; ?> </li>
		<li><strong>
		<?php echo wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec') : _e('Fare','bus-booking-manager'); echo ':'; ?>	
		</strong> <?php echo wc_price(mage_seat_price(get_the_id(),$start,$end,'adult')); ?> 
	  </li>
	</ul>
<a href="<?php the_permalink(); ?>" class="btn wbbm-btn">
	<?php echo wbbm_get_option('wbbm_book_now_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_book_now_text', 'wbbm_label_setting_sec') : _e('Book Now','bus-booking-manager');
    ?>   
</a>
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