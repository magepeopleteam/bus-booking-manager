<?php

if (!defined('ABSPATH')) {
    die;
}

get_header();
the_post();
$queried_obj = get_queried_object();
$term_id = isset($queried_obj->term_id) ? intval($queried_obj->term_id) : 0;
?>
<div class="mep-events-wrapper">
    <div class="wbbm-bus-list-sec">
        <div class="wbbm_cat-details">
            <h1><?php echo esc_html(get_queried_object()->name); ?></h1>
            <p><?php echo esc_html(get_queried_object()->description); ?></p>
        </div>
        
        <?php
        // Use direct taxonomy query var and limit results to mitigate heavy tax/meta queries
        $args_search_qqq = array(
            'post_type'      => array('wbbm_bus'),
            'posts_per_page' => 200,
            'no_found_rows'  => true,
            'wbbm_bus_cat'   => $term_id,
        );
        $loop = new WP_Query($args_search_qqq);
        
        while ($loop->have_posts()) {
            $loop->the_post(); 
            $bp_arr = get_post_meta(get_the_ID(), 'wbbm_bus_bp_stops', true); 
            $dp_arr = get_post_meta(get_the_ID(), 'wbbm_bus_next_stops', true);
            $price_arr = get_post_meta(get_the_ID(), 'wbbm_bus_prices', true);
            $total_dp = count($dp_arr) - 1;
            $term = get_the_terms(get_the_ID(), 'wbbm_bus_cat');	
        ?>
        <div class="wbbm-bus-lists">
            <div class="bus-thumb">
                <?php the_post_thumbnail('full'); ?>
            </div>
            <h2><?php the_title(); ?></h2>
            <ul>
                <li><strong><?php esc_html_e('Type:', 'bus-booking-manager'); ?></strong> <?php echo esc_html($term[0]->name ?? ''); ?></li>
                <li><strong><?php esc_html_e('Bus No:', 'bus-booking-manager'); ?></strong> <?php echo esc_html(get_post_meta(get_the_ID(), 'wbbm_bus_no', true)); ?></li>
                <li><strong><?php esc_html_e('Total Seat:', 'bus-booking-manager'); ?></strong> <?php echo esc_html(get_post_meta(get_the_ID(), 'wbbm_total_seat', true)); ?></li>
                <li><strong><?php esc_html_e('Start From:', 'bus-booking-manager'); ?></strong> <?php echo esc_html($bp_arr[0]['wbbm_bus_bp_stops_name'] ?? ''); ?></li>
                <li><strong><?php esc_html_e('End at:', 'bus-booking-manager'); ?></strong> <?php echo esc_html($dp_arr[$total_dp]['wbbm_bus_next_stops_name'] ?? ''); ?></li>
                <li><strong><?php esc_html_e('Fare:', 'bus-booking-manager'); ?></strong> <?php echo wp_kses_post(wc_price(wbbm_get_bus_price($start, $end, $price_arr))); ?></li>
            </ul>

            <a href="<?php echo esc_url(get_permalink()); ?>" class='btn wbbm-bus-list-btn'><?php esc_html_e('Book Now', 'bus-booking-manager'); ?></a>
        </div>
        <?php
        }
        wp_reset_postdata(); // Reset post data after custom query
        ?>
    </div>
</div>

<?php
get_footer();
?>
