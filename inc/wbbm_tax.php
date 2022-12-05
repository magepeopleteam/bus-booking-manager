<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
function wbbm_bus_cpt_tax(){

$cpt_label = wbbm_get_option( 'wbbm_cpt_label', 'wbbm_general_setting_sec', 'Bus');
$cpt_slug = wbbm_get_option( 'wbbm_cpt_slug', 'wbbm_general_setting_sec', 'bus');
	$labels = array(
		'name'                       => _x( $cpt_label.' Types','bus-booking-manager' ),
		'singular_name'              => _x( $cpt_label.' Types','bus-booking-manager' ),
		'menu_name'                  => _x( $cpt_label.' Types','bus-booking-manager' ),
	);

	$args = array(
		'hierarchical'          => true,
		"public" 				=> true,
		'labels'                => $labels,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'show_in_quick_edit'    => false,
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
		'show_in_quick_edit'    => false,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array( 'slug' => $cpt_slug.'-stops' ),
	);
register_taxonomy('wbbm_bus_stops', 'wbbm_bus', $bus_stops_args);

// Register Pickup Point Taxonomy
$bus_pickup_point_label = array(
	'singular_name'              => _x( $cpt_label.' Pickup Points','bus-booking-manager' ),
	'name'                       => _x( $cpt_label.' Pickup Points','bus-booking-manager' ),
);

$bus_pickpoint_args = array(
	'hierarchical'          => true,
	"public" 				=> true,
	'labels'                => $bus_pickup_point_label,
	'show_ui'               => true,
	'show_admin_column'     => true,
	'show_in_quick_edit'    => false,
	'update_count_callback' => '_update_post_term_count',
	'query_var'             => true,
	'rewrite'               => array( 'slug' => $cpt_slug.'-pickpoint' ),
);

register_taxonomy( 'wbbm_bus_pickpoint', 'wbbm_bus', $bus_pickpoint_args );

}
add_action("init","wbbm_bus_cpt_tax",10);

$wbbm_route_point = array(
    array(
        'id'		=> 'wbbm_bus_routes_name_list',
        'title'		=> __('Route Point','bus-booking-manager'),
        'details'	=> __('Please Select Route Point ','bus-booking-manager'),
        'collapsible'=>true,
        'type'		=> 'repeatable',
        'btn_text'	=> 'Add New Route Point',
        'title_field' => 'wbbm_bus_routes_name',
        'fields'    => array(
            array(
                'type'         =>'select',
                'default'      =>'option_1',
                'item_id'      =>'wbbm_bus_routes_name',
                'name'         =>'Stops Name',
                'args'         => 'TAXN_%wbbm_bus_stops%'
            )
        ),
    ),

);

$args = array(
    'taxonomy'       => 'wbbm_bus_stops',
    'options' 	        => $wbbm_route_point,
);

new TaxonomyEdit( $args );