<?php

if (!defined('ABSPATH')) {
    die;
}

add_action('admin_init', 'wbbm_create_old_bus_product', 10);

function wbbm_create_old_bus_product()
{
    // Check if the process has already been completed
    if (get_option('wbbm_create_old_bus_product_01') !== 'completed') {
        $args = array(
            'post_type'      => 'wbbm_bus',
            'posts_per_page' => -1,
        );
        $qr = new WP_Query($args);

        foreach ($qr->posts as $result) {
            $post_id = intval($result->ID); // Ensure post ID is an integer
            $post_title = sanitize_text_field(get_the_title($post_id)); // Sanitize the post title
            wbbm_create_hidden_event_product($post_id, $post_title);
        }

        update_option('wbbm_create_old_bus_product_01', 'completed');
    }
}
