<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.

// Create MKB CPT
function wbbm_bus_cpt() {



$cpt_label = wbbm_get_option( 'wbbm_cpt_label', 'wbbm_general_setting_sec', __('Bus','bus-booking-manager'));
$cpt_slug = wbbm_get_option( 'wbbm_cpt_slug', 'wbbm_general_setting_sec', __('bus','bus-booking-manager'));
$general_setting = get_option('wbbm_general_setting_sec') ? maybe_unserialize(get_option('wbbm_general_setting_sec')) : array();
if(isset($general_setting['wbbm_gutenbug_switch']) && $general_setting['wbbm_gutenbug_switch'] == 'on'){
    $editor = true;
} else {
    $editor = false;
}

    $labels = array(
        'name'                  => _x( $cpt_label, 'bus-booking-manager' ),
        'singular_name'         => _x( $cpt_label, 'bus-booking-manager' ),
        'menu_name'             => __( $cpt_label, 'bus-booking-manager' ),
        'name_admin_bar'        => __( $cpt_label, 'bus-booking-manager' ),
    );


    $args = array(
        'public'                => true,
        'labels'                => $labels,
        'menu_icon'             => 'dashicons-tickets-alt',
        'show_in_rest'          => $editor,
        'supports'              => array('title','editor','thumbnail'),
        'rewrite'               => array('slug' => $cpt_slug)

    );
    register_post_type( 'wbbm_bus', $args );


}
add_action( 'init', 'wbbm_bus_cpt' );