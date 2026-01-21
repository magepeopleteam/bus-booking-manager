<?php
if (! defined('ABSPATH')) {
    die;
}

function wbbm_run_booking_migration_once()
{
    if (get_option('wbbm_booking_migration_completed_2')) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'wbbm_bus_booking_list';

    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        return;
    }

    $rows = $wpdb->get_results("SELECT * FROM $table_name");

    if (! empty($rows)) {
        foreach ($rows as $row) {
            // Check if booking already exists in post table to prevent duplicates
            $args = array(
                'post_type'  => 'wbbm_booking',
                'meta_query' => array(
                    array(
                        'key'   => '_wbbm_old_booking_id',
                        'value' => $row->booking_id,
                    ),
                ),
                'fields'     => 'ids',
            );
            $existing_bookings = new WP_Query($args);

            if ($existing_bookings->have_posts()) {
                continue;
            }

            $post_title = 'Booking #' . $row->booking_id . ' - ' . $row->user_name;

            $post_data = array(
                'post_title'  => $post_title,
                'post_type'   => 'wbbm_booking',
                'post_status' => 'publish',
                'post_date'   => $row->booking_date,
            );

            $post_id = wp_insert_post($post_data);

            if ($post_id && ! is_wp_error($post_id)) {
                // Map all columns to meta
                update_post_meta($post_id, '_wbbm_old_booking_id', $row->booking_id);
                update_post_meta($post_id, '_wbbm_order_id', $row->order_id);
                update_post_meta($post_id, '_wbbm_bus_id', $row->bus_id);
                update_post_meta($post_id, '_wbbm_user_id', $row->user_id);
                update_post_meta($post_id, '_wbbm_boarding_point', $row->boarding_point);
                update_post_meta($post_id, '_wbbm_droping_point', $row->droping_point);
                update_post_meta($post_id, '_wbbm_next_stops', $row->next_stops); // Already serialized? Check usage.
                update_post_meta($post_id, '_wbbm_user_name', $row->user_name);
                update_post_meta($post_id, '_wbbm_user_email', $row->user_email);
                update_post_meta($post_id, '_wbbm_user_phone', $row->user_phone);
                update_post_meta($post_id, '_wbbm_user_gender', $row->user_gender);
                update_post_meta($post_id, '_wbbm_user_address', $row->user_address);
                update_post_meta($post_id, '_wbbm_user_type', $row->user_type);
                update_post_meta($post_id, '_wbbm_bus_start', $row->bus_start);
                update_post_meta($post_id, '_wbbm_user_start', $row->user_start);
                update_post_meta($post_id, '_wbbm_total_adult', $row->total_adult);
                update_post_meta($post_id, '_wbbm_per_adult_price', $row->per_adult_price);
                update_post_meta($post_id, '_wbbm_total_child', $row->total_child);
                update_post_meta($post_id, '_wbbm_per_child_price', $row->per_child_price);
                update_post_meta($post_id, '_wbbm_total_price', $row->total_price);
                update_post_meta($post_id, '_wbbm_seat', $row->seat);
                update_post_meta($post_id, '_wbbm_journey_date', $row->journey_date);
                update_post_meta($post_id, '_wbbm_status', $row->status);

                // Extra fields
                if (isset($row->pickpoint)) update_post_meta($post_id, '_wbbm_pickpoint', $row->pickpoint);
                if (isset($row->user_dob)) update_post_meta($post_id, '_wbbm_user_dob', $row->user_dob);
                if (isset($row->nationality)) update_post_meta($post_id, '_wbbm_nationality', $row->nationality);
                if (isset($row->flight_arrial_no)) update_post_meta($post_id, '_wbbm_flight_arrial_no', $row->flight_arrial_no);
                if (isset($row->flight_departure_no)) update_post_meta($post_id, '_wbbm_flight_departure_no', $row->flight_departure_no);
                if (isset($row->extra_bag_quantity)) update_post_meta($post_id, '_wbbm_extra_bag_quantity', $row->extra_bag_quantity);
                if (isset($row->total_infant)) update_post_meta($post_id, '_wbbm_total_infant', $row->total_infant);
                if (isset($row->per_infant_price)) update_post_meta($post_id, '_wbbm_per_infant_price', $row->per_infant_price);
                if (isset($row->total_entire)) update_post_meta($post_id, '_wbbm_total_entire', $row->total_entire);
                if (isset($row->per_entire_price)) update_post_meta($post_id, '_wbbm_per_entire_price', $row->per_entire_price);
            }
        }
    }

    update_option('wbbm_booking_migration_completed_2', 1);
}

add_action('admin_init', 'wbbm_run_booking_migration_once');
