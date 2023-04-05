<?php
add_action('admin_init', 'wbbm_create_old_bus_product', 10);
function wbbm_create_old_bus_product()
{

    if (get_option('wbbm_create_old_bus_product_01') != 'completed') {
        $args = array(
            'post_type' => 'wbbm_bus',
            'posts_per_page' => -1
        );
        $qr = new WP_Query($args);
        foreach ($qr->posts as $result) {
            $post_id = $result->ID;
            wbbm_create_hidden_event_product($post_id, get_the_title($post_id));
        }
        update_option('wbbm_create_old_bus_product_01', 'completed');
    }
}