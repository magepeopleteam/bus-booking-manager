<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.

add_shortcode( 'bus-list', 'wbbm_bus_list' );
function wbbm_bus_list($atts, $content=null) {
    $defaults = array(
        "cat"      => "0",
        "show"     => "20",
    );
    $params = shortcode_atts($defaults, $atts);
    $cat = intval($params['cat']); // Sanitize category input
    $show = intval($params['show']); // Sanitize show input
    ob_start();

    $paged = get_query_var("paged") ? get_query_var("paged") : 1;

    if ($cat > 0) {
        $args_search_qqq = array(
            'post_type'      => array('wbbm_bus'),
            'paged'          => $paged,
            'posts_per_page' => $show,
            'tax_query'      => array(
                array(
                    'taxonomy' => 'wbbm_bus_cat',
                    'field'    => 'term_id',
                    'terms'    => $cat,
                )
            )
        );
    } else {
        $args_search_qqq = array(
            'post_type'      => array('wbbm_bus'),
            'paged'          => $paged,
            'posts_per_page' => $show
        );
    }

    $loop = new WP_Query($args_search_qqq);
    ?>
    <div class="wbbm-bus-list-sec wbbm-bus-grid">
    <?php 
    while ($loop->have_posts()) {
        $loop->the_post(); 
        $bp_arr = get_post_meta(get_the_id(), 'wbbm_bus_bp_stops', true); 
        $dp_arr = get_post_meta(get_the_id(), 'wbbm_bus_next_stops', true);
        $price_arr = get_post_meta(get_the_id(), 'wbbm_bus_prices', true);
        $total_dp = count($dp_arr) - 1;

        $start = !empty($bp_arr[0]['wbbm_bus_bp_stops_name']) ? sanitize_text_field($bp_arr[0]['wbbm_bus_bp_stops_name']) : '';
        $end = !empty($dp_arr[$total_dp]['wbbm_bus_next_stops_name']) ? sanitize_text_field($dp_arr[$total_dp]['wbbm_bus_next_stops_name']) : '';
        
        $type_id = get_post_meta(get_the_id(), 'wbbm_bus_category', true);
        $type_name = '';
        if ($type_id != '') {
            $type_array = get_term_by('term_id', $type_id, 'wbbm_bus_cat');
            $type_name = !empty($type_array) ? sanitize_text_field($type_array->name) : '';
        } 
    ?>
    <div class="wbbm-bus-lists">
        <div class="bus-thumb">
            <?php the_post_thumbnail('full'); ?>
        </div>
        <div class="wbbm-bus-info">
            <h2><?php the_title(); ?></h2>
            <ul>
                <li><strong>
                <?php echo esc_html(wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec', __('Type','bus-booking-manager'))); ?>:</strong> <?php echo esc_html($type_name); ?></li>
                <li><strong>
                <?php echo esc_html(wbbm_get_option('wbbm_bus_no_text', 'wbbm_label_setting_sec', __('Bus No','bus-booking-manager'))); ?>:</strong> <?php echo esc_html(get_post_meta(get_the_id(), 'wbbm_bus_no', true)); ?></li>
                <li><strong>
                <?php echo esc_html(wbbm_get_option('wbbm_total_seat_text', 'wbbm_label_setting_sec', __('Total Seat','bus-booking-manager'))); ?>:</strong> <?php echo esc_html(get_post_meta(get_the_id(), 'wbbm_total_seat', true)); ?></li>
                <li><strong>
                <?php echo esc_html(wbbm_get_option('wbbm_start_from_text', 'wbbm_label_setting_sec', __('Start From','bus-booking-manager'))); ?>:</strong> <?php echo esc_html($start); ?></li>
                <li><strong>
                <?php echo esc_html(wbbm_get_option('wbbmesc_html_end_to_text', 'wbbm_label_setting_sec', __('End at','bus-booking-manager'))); ?>:</strong> <?php echo esc_html($end); ?></li>
                <li><strong>
                <?php echo esc_html(wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec', __('Fare','bus-booking-manager'))); ?>:</strong> <?php echo wc_price(mage_seat_price(get_the_id(), $start, $end, 'adult')); ?></li>
            </ul>
            <a href="<?php echo esc_url(get_permalink()); ?>" class="btn wbbm-btn">
                <?php echo esc_html(wbbm_get_option('wbbm_book_now_text', 'wbbm_label_setting_sec', __('Book Now','bus-booking-manager'))); ?>
            </a>
        </div>
    </div>
    <?php
    }
    ?>
    <div class="row">
        <div class="col-md-12">
            <?php
            $pargs = array(
                "current" => $paged,
                "total"   => $loop->max_num_pages
            );
            echo "<div class='pagination-sec'>" . wp_kses_post(paginate_links($pargs)) . "</div>";
            ?>  
        </div>
    </div>
    </div>
    <?php
    $content = ob_get_clean();
    return $content;
}

add_shortcode( 'destination', 'wbbm_bus_popular_destination' );
function wbbm_bus_popular_destination($atts, $content=null) {
    $defaults = array(
        "from"     => "",
        "to"       => "",
        "text"     => "",
        "image"    => "",
        "journey"  => date('Y-m-d'),
        "return"   => date('Y-m-d')
    );
    $params = shortcode_atts($defaults, $atts);
    $from = sanitize_text_field($params['from']);
    $to = sanitize_text_field($params['to']);
    $image = sanitize_text_field($params['image']);
    $text = sanitize_text_field($params['text']);
    $journey = sanitize_text_field($params['journey']);
    $return = sanitize_text_field($params['return']);
    ob_start();
    ?>
    <a href="<?php echo esc_url(get_site_url() . '/bus-search?bus_start_route=' . urlencode($from) . '&busesc_html_end_route=' . urlencode($to) . '&j_date=' . urlencode($journey) . '&r_date=' . urlencode($return)); ?>"> 
        <?php echo esc_html($text); ?> 
    </a>
    <?php
    $content = ob_get_clean();
    return $content;
}
