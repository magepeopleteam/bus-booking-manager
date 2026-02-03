<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

/**
 * Register Shuttle Taxonomies
 * 
 * This file registers taxonomies for the shuttle service:
 * 1. wbbm_shuttle_type - Shuttle types (Airport, Hotel, Corporate)
 * 2. wbbm_shuttle_cat - Vehicle categories (Van, Minibus, Bus, etc.)
 * 3. wbbm_shuttle_stops - Shuttle stops/locations
 */

function wbbm_shuttle_taxonomies()
{
    $cpt_label = sanitize_text_field(wbbm_get_option('wbbm_shuttle_cpt_label', 'wbbm_general_setting_sec', __('Shuttle', 'bus-booking-manager')));
    $cpt_slug = sanitize_title(wbbm_get_option('wbbm_shuttle_cpt_slug', 'wbbm_general_setting_sec', __('shuttle', 'bus-booking-manager')));

    // 1. Shuttle Type Taxonomy (Airport, Hotel, Corporate)
    $type_labels = array(
        'name'                       => sprintf(
            /* translators: %s: custom post type label */
            _x('%s Types', 'Taxonomy general name', 'bus-booking-manager'),
            esc_html($cpt_label)
        ),
        'singular_name'              => sprintf(
            /* translators: %s: custom post type label */
            _x('%s Type', 'Taxonomy singular name', 'bus-booking-manager'),
            esc_html($cpt_label)
        ),
        'menu_name'                  => sprintf(
            /* translators: %s: custom post type label */
            _x('%s Types', 'Taxonomy menu name', 'bus-booking-manager'),
            esc_html($cpt_label)
        ),
        'all_items'                  => __('All Shuttle Types', 'bus-booking-manager'),
        'edit_item'                  => __('Edit Shuttle Type', 'bus-booking-manager'),
        'view_item'                  => __('View Shuttle Type', 'bus-booking-manager'),
        'update_item'                => __('Update Shuttle Type', 'bus-booking-manager'),
        'add_new_item'               => __('Add New Shuttle Type', 'bus-booking-manager'),
        'new_item_name'              => __('New Shuttle Type Name', 'bus-booking-manager'),
        'search_items'               => __('Search Shuttle Types', 'bus-booking-manager'),
    );

    $type_args = array(
        'hierarchical'          => true,
        'public'                => true,
        'labels'                => $type_labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'show_in_quick_edit'    => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array('slug' => $cpt_slug . '-type'),
        'show_in_rest'          => false,
        'meta_box_cb'           => false,
    );
    register_taxonomy('wbbm_shuttle_type', 'wbbm_shuttle', $type_args);

    // 2. Shuttle Category/Vehicle Type Taxonomy
    $cat_labels = array(
        'name'                       => sprintf(
            /* translators: %s: custom post type label */
            _x('%s Categories', 'Taxonomy general name', 'bus-booking-manager'),
            esc_html($cpt_label)
        ),
        'singular_name'              => sprintf(
            /* translators: %s: custom post type label */
            _x('%s Category', 'Taxonomy singular name', 'bus-booking-manager'),
            esc_html($cpt_label)
        ),
        'menu_name'                  => sprintf(
            /* translators: %s: custom post type label */
            _x('Vehicle Categories', 'Taxonomy menu name', 'bus-booking-manager'),
            esc_html($cpt_label)
        ),
        'all_items'                  => __('All Categories', 'bus-booking-manager'),
        'edit_item'                  => __('Edit Category', 'bus-booking-manager'),
        'view_item'                  => __('View Category', 'bus-booking-manager'),
        'update_item'                => __('Update Category', 'bus-booking-manager'),
        'add_new_item'               => __('Add New Category', 'bus-booking-manager'),
        'new_item_name'              => __('New Category Name', 'bus-booking-manager'),
        'search_items'               => __('Search Categories', 'bus-booking-manager'),
    );

    $cat_args = array(
        'hierarchical'          => true,
        'public'                => true,
        'labels'                => $cat_labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'show_in_quick_edit'    => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array('slug' => $cpt_slug . '-category'),
        'show_in_rest'          => false,
        'meta_box_cb'           => false,
    );
    register_taxonomy('wbbm_shuttle_cat', 'wbbm_shuttle', $cat_args);

    // 3. Shuttle Stops Taxonomy
    $stops_labels = array(
        'name'                       => sprintf(
            /* translators: %s: custom post type label */
            _x('%s Stops', 'Taxonomy general name', 'bus-booking-manager'),
            esc_html($cpt_label)
        ),
        'singular_name'              => sprintf(
            /* translators: %s: custom post type label */
            _x('%s Stop', 'Taxonomy singular name', 'bus-booking-manager'),
            esc_html($cpt_label)
        ),
        'menu_name'                  => sprintf(
            /* translators: %s: custom post type label */
            _x('%s Stops', 'Taxonomy menu name', 'bus-booking-manager'),
            esc_html($cpt_label)
        ),
        'all_items'                  => __('All Shuttle Stops', 'bus-booking-manager'),
        'edit_item'                  => __('Edit Shuttle Stop', 'bus-booking-manager'),
        'view_item'                  => __('View Shuttle Stop', 'bus-booking-manager'),
        'update_item'                => __('Update Shuttle Stop', 'bus-booking-manager'),
        'add_new_item'               => __('Add New Shuttle Stop', 'bus-booking-manager'),
        'new_item_name'              => __('New Shuttle Stop Name', 'bus-booking-manager'),
        'search_items'               => __('Search Shuttle Stops', 'bus-booking-manager'),
    );

    $stops_args = array(
        'hierarchical'          => true,
        'public'                => true,
        'labels'                => $stops_labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'show_in_quick_edit'    => false,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array('slug' => $cpt_slug . '-stops'),
        'show_in_rest'          => true,
    );
    register_taxonomy('wbbm_shuttle_stops', 'wbbm_shuttle', $stops_args);
}
add_action('init', 'wbbm_shuttle_taxonomies', 0);
