<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
function wbbm_bus_cpt_tax(){

$cpt_label = wbbm_get_option( 'wbbm_cpt_label', 'wbbm_general_setting_sec', 'Bus');
$cpt_slug = wbbm_get_option( 'wbbm_cpt_slug', 'wbbm_general_setting_sec', 'bus');
	$labels = array(
		'name'                       => _x( $cpt_label.' Category','bus-booking-manager' ),
		'singular_name'              => _x( $cpt_label.' Category','bus-booking-manager' ),
		'menu_name'                  => __( 'Category', 'bus-booking-manager' ),
	);

	$args = array(
		'hierarchical'          => true,
		"public" 				=> true,
		'labels'                => $labels,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array( 'slug' => $cpt_slug.'-category' ),
	);
register_taxonomy('wbbm_bus_cat', 'wbbm_bus', $args);



	$bus_stops_labels = array(
		'singular_name'              => _x( $cpt_label.' Stops','bus-booking-manager' ),
		'name'                       => _x( $cpt_label.' Stops','bus-booking-manager' ),
	);

	$bus_stops_args = array(
		'hierarchical'          => true,
		"public" 				=> true,
		'labels'                => $bus_stops_labels,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array( 'slug' => $cpt_slug.'-stops' ),
	);
register_taxonomy('wbbm_bus_stops', 'wbbm_bus', $bus_stops_args);
}
add_action("init","wbbm_bus_cpt_tax",10);