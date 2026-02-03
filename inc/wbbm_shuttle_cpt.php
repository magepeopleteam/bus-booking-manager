<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

/**
 * Register Shuttle Custom Post Types
 * 
 * This file registers two custom post types:
 * 1. wbbm_shuttle - For shuttle services (Airport, Hotel, Corporate)
 * 2. wbbm_shuttle_booking - For shuttle booking records
 */

function wbbm_shuttle_cpt()
{
    // Get the custom post type label and slug from options (with shuttle defaults)
    $cpt_label = sanitize_text_field(wbbm_get_option('wbbm_shuttle_cpt_label', 'wbbm_general_setting_sec', __('Shuttle', 'bus-booking-manager')));
    $cpt_slug = sanitize_title(wbbm_get_option('wbbm_shuttle_cpt_slug', 'wbbm_general_setting_sec', __('shuttle', 'bus-booking-manager')));

    // Get general settings
    $general_setting = get_option('wbbm_general_setting_sec') ? maybe_unserialize(get_option('wbbm_general_setting_sec')) : array();

    // Check Gutenberg editor setting
    $editor = isset($general_setting['wbbm_gutenbug_switch']) && $general_setting['wbbm_gutenbug_switch'] === 'on';

    // Labels for the shuttle custom post type
    $labels = array(
        'name'                  => sprintf(
            /* translators: %s: custom post type label */
            _x('%s Services', 'Post type general name', 'bus-booking-manager'),
            esc_html($cpt_label)
        ),
        'singular_name'         => sprintf(
            /* translators: %s: custom post type label */
            _x('%s Service', 'Post type singular name', 'bus-booking-manager'),
            esc_html($cpt_label)
        ),
        'menu_name'             => esc_html($cpt_label) . ' ' . __('Services', 'bus-booking-manager'),
        'name_admin_bar'        => esc_html($cpt_label),
        'add_new'               => __('Add New', 'bus-booking-manager'),
        'add_new_item'          => sprintf(__('Add New %s', 'bus-booking-manager'), esc_html($cpt_label)),
        'new_item'              => sprintf(__('New %s', 'bus-booking-manager'), esc_html($cpt_label)),
        'edit_item'             => sprintf(__('Edit %s', 'bus-booking-manager'), esc_html($cpt_label)),
        'view_item'             => sprintf(__('View %s', 'bus-booking-manager'), esc_html($cpt_label)),
        'all_items'             => sprintf(__('All %s Services', 'bus-booking-manager'), esc_html($cpt_label)),
        'search_items'          => sprintf(__('Search %s', 'bus-booking-manager'), esc_html($cpt_label)),
        'not_found'             => sprintf(__('No %s found', 'bus-booking-manager'), strtolower(esc_html($cpt_label))),
        'not_found_in_trash'    => sprintf(__('No %s found in Trash', 'bus-booking-manager'), strtolower(esc_html($cpt_label))),
    );

    // Arguments for the shuttle custom post type
    $args = array(
        'public'                => true,
        'labels'                => $labels,
        'menu_icon'             => 'dashicons-car',
        'show_in_rest'          => $editor,
        'supports'              => array('title', 'editor', 'thumbnail'),
        'rewrite'               => array('slug' => $cpt_slug),
        'has_archive'           => true,
        'hierarchical'          => false,
        'menu_position'         => 26, // Position after Bus menu
        'show_in_menu'          => true,
        'capability_type'       => 'post',
    );

    // Register the shuttle custom post type
    register_post_type('wbbm_shuttle', $args);

    // Register Shuttle Booking CPT
    $booking_labels = array(
        'name'                  => _x('Shuttle Bookings', 'Post type general name', 'bus-booking-manager'),
        'singular_name'         => _x('Shuttle Booking', 'Post type singular name', 'bus-booking-manager'),
        'menu_name'             => _x('Shuttle Bookings', 'Admin Menu text', 'bus-booking-manager'),
        'name_admin_bar'        => _x('Shuttle Booking', 'Add New on Toolbar', 'bus-booking-manager'),
        'add_new'               => _x('Add New', 'shuttle booking', 'bus-booking-manager'),
        'add_new_item'          => __('Add New Shuttle Booking', 'bus-booking-manager'),
        'new_item'              => __('New Shuttle Booking', 'bus-booking-manager'),
        'edit_item'             => __('Edit Shuttle Booking', 'bus-booking-manager'),
        'view_item'             => __('View Shuttle Booking', 'bus-booking-manager'),
        'all_items'             => __('All Shuttle Bookings', 'bus-booking-manager'),
        'search_items'          => __('Search Shuttle Bookings', 'bus-booking-manager'),
        'not_found'             => __('No shuttle bookings found.', 'bus-booking-manager'),
        'not_found_in_trash'    => __('No shuttle bookings found in Trash.', 'bus-booking-manager'),
    );

    $booking_args = array(
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => 'edit.php?post_type=wbbm_shuttle',
        'query_var'          => true,
        'rewrite'            => array('slug' => 'wbbm-shuttle-booking'),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array('title', 'custom-fields'),
        'labels'             => $booking_labels,
    );

    register_post_type('wbbm_shuttle_booking', $booking_args);
}
add_action('init', 'wbbm_shuttle_cpt');

/**
 * Create default shuttle types and categories
 * This runs once on plugin activation or when taxonomies are first registered
 */
function wbbm_create_default_shuttle_terms()
{
    // Only run once
    if (get_option('wbbm_shuttle_default_terms_created')) {
        return;
    }

    // Create default shuttle types (Airport, Hotel, Corporate)
    $shuttle_types = array('Airport', 'Hotel', 'Corporate');
    foreach ($shuttle_types as $type) {
        if (!term_exists($type, 'wbbm_shuttle_type')) {
            wp_insert_term($type, 'wbbm_shuttle_type');
        }
    }

    // Create default vehicle categories
    $vehicle_cats = array('Van', 'Minibus', 'Bus', 'Luxury Van', 'SUV');
    foreach ($vehicle_cats as $cat) {
        if (!term_exists($cat, 'wbbm_shuttle_cat')) {
            wp_insert_term($cat, 'wbbm_shuttle_cat');
        }
    }

    // Mark as created
    update_option('wbbm_shuttle_default_terms_created', true);
}
add_action('init', 'wbbm_create_default_shuttle_terms', 100);
