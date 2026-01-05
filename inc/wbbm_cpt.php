<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.

// Create MKB CPT
function wbbm_bus_cpt() {

    // Get the custom post type label and slug from options
    $cpt_label = sanitize_text_field(wbbm_get_option('wbbm_cpt_label', 'wbbm_general_setting_sec', __('Bus', 'bus-booking-manager')));
    $cpt_slug = sanitize_title(wbbm_get_option('wbbm_cpt_slug', 'wbbm_general_setting_sec', __('bus', 'bus-booking-manager')));
    
    // Get general settings
    $general_setting = get_option('wbbm_general_setting_sec') ? maybe_unserialize(get_option('wbbm_general_setting_sec')) : array();
    
    // Check Gutenberg editor setting
    $editor = isset($general_setting['wbbm_gutenbug_switch']) && $general_setting['wbbm_gutenbug_switch'] === 'on';

    // Labels for the custom post type
    $labels = array(
        'name'                  => sprintf(
                                        /* translators: %s: custom post type label */
                                        _x('%s post type general name', 'Post type general name for translators', 'bus-booking-manager'), esc_html($cpt_label)
                                    ),
        'singular_name'         => sprintf(
                                    /* translators: %s: custom post type label */
                                    _x('%s post type singular name','Post type general name for translators', 'bus-booking-manager'), esc_html($cpt_label)),
        'menu_name'             => esc_html($cpt_label),
        'name_admin_bar'        => esc_html($cpt_label),
    );

    // Arguments for the custom post type
    $args = array(
        'public'                => true,
        'labels'                => $labels,
        'menu_icon'             => 'dashicons-tickets-alt',
        'show_in_rest'          => $editor,
        'supports'              => array('title', 'editor', 'thumbnail'),
        'rewrite'               => array('slug' => $cpt_slug),
    );

    // Register the custom post type
    register_post_type('wbbm_bus', $args);
}
add_action('init', 'wbbm_bus_cpt');
