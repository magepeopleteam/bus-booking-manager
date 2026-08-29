<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Bus List Page Class
 *
 * Handles the modern custom list page for general buses.
 */
class BusListPageClass
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_bus_list_page'), 1);
        add_action('admin_menu', array($this, 'reorder_bus_submenu'), 999);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_init', array($this, 'handle_bus_actions'));
    }

    /**
     * Register the custom list page and replace default
     */
    public function register_bus_list_page()
    {
        // Add custom list page
        add_submenu_page(
            'edit.php?post_type=wbbm_bus',
            __('All Bus Services', 'bus-booking-manager'),
            __('All Bus Services', 'bus-booking-manager'),
            'manage_options',
            'wbbm-bus-list',
            array($this, 'render_bus_list_page')
        );

        // Adjust order: Remove default "All Bus Services"
        remove_submenu_page('edit.php?post_type=wbbm_bus', 'edit.php?post_type=wbbm_bus');
    }

    /**
     * Reorder bus submenu to keep "All Bus Services" list item as first item
     */
    public function reorder_bus_submenu()
    {
        global $submenu;
        $parent_slug = 'edit.php?post_type=wbbm_bus';

        if (!isset($submenu[$parent_slug])) {
            return;
        }

        $bus_menu = $submenu[$parent_slug];
        $list_page_slug = 'wbbm-bus-list';

        $list_item_key = false;
        foreach ($bus_menu as $key => $item) {
            if (isset($item[2]) && $item[2] === $list_page_slug) {
                $list_item_key = $key;
                break;
            }
        }

        if ($list_item_key !== false) {
            $list_item = $bus_menu[$list_item_key];
            unset($bus_menu[$list_item_key]);
            array_unshift($bus_menu, $list_item);
            // Re-index array
            $submenu[$parent_slug] = array_values($bus_menu);
        }
    }

    /**
     * Enqueue CSS and JS
     */
    public function enqueue_assets($hook)
    {
        if (strpos($hook, 'wbbm-bus-list') === false) {
            return;
        }

        wp_enqueue_style('shuttle-list-css', WBTM_PLUGIN_URL . 'assets/admin/shuttle-list.css', array(), time());
        wp_enqueue_script('shuttle-list-js', WBTM_PLUGIN_URL . 'assets/admin/shuttle-list.js', array('jquery'), time(), true);
    }

    /**
     * Handle custom actions like delete
     */
    public function handle_bus_actions()
    {
        if (isset($_GET['page']) && $_GET['page'] === 'wbbm-bus-list' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['post_id'])) {
            $post_id = intval($_GET['post_id']);
            $post = get_post($post_id);

            if (!$post || $post->post_type !== 'wbbm_bus') {
                return;
            }

            if (!current_user_can('delete_post', $post_id)) {
                wp_die(__('You do not have permission to delete this bus.', 'bus-booking-manager'));
            }

            check_admin_referer('delete-bus_' . $post_id);

            wp_trash_post($post_id);

            wp_safe_redirect(admin_url('edit.php?post_type=wbbm_bus&page=wbbm-bus-list&deleted=1'));
            exit;
        }
    }

    /**
     * Render the custom list page
     */

    /**
     * Seats already sold on one bus for one journey date.
     *
     * Counts booking records rather than route segments: this column is a
     * whole-vehicle view, so a seat is treated as taken for the day. The
     * statuses that count as booked follow the same global setting the rest
     * of the plugin uses, so this figure cannot drift from availability.
     *
     * @param int    $bus_id
     * @param string $date Y-m-d
     * @return int
     */
    private static function seats_sold($bus_id, $date)
    {
        static $cache = array();

        $bus_id = absint($bus_id);
        if (!$bus_id || !$date) {
            return 0;
        }

        $key = $bus_id . '|' . $date;
        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $statuses = function_exists('wbbm_seat_booked_on_status') ? wbbm_seat_booked_on_status() : '';
        $statuses = $statuses ? array_filter(array_map('trim', explode(',', $statuses))) : array(1, 2);
        if (!$statuses) {
            $statuses = array(1, 2);
        }

        $bookings = get_posts(array(
            'post_type'      => 'wbbm_booking',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
            'meta_query'     => array(
                'relation' => 'AND',
                array('key' => '_wbbm_bus_id', 'value' => $bus_id, 'compare' => '='),
                array('key' => '_wbbm_journey_date', 'value' => $date, 'compare' => '='),
                array('key' => '_wbbm_status', 'value' => $statuses, 'compare' => 'IN'),
            ),
        ));

        $cache[$key] = count($bookings);

        return $cache[$key];
    }

    public function render_bus_list_page()
    {
        // Filters
        $category = isset($_GET['wbbm_bus_cat']) ? sanitize_text_field($_GET['wbbm_bus_cat']) : '';

        /*
         * Remaining seats only mean something for a given journey date, so the
         * list carries one. It defaults to today and the column reflects it.
         */
        $seat_date = isset($_GET['wbbm_seat_date']) ? sanitize_text_field(wp_unslash($_GET['wbbm_seat_date'])) : '';
        $seat_date_ts = $seat_date ? strtotime($seat_date) : false;
        $seat_date = $seat_date_ts ? gmdate('Y-m-d', $seat_date_ts) : current_time('Y-m-d');
        $stop     = isset($_GET['wbbm_bus_stops']) ? sanitize_text_field($_GET['wbbm_bus_stops']) : '';
        $s        = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $posts_per_page = 20;

        $args = array(
            'post_type'      => 'wbbm_bus',
            'post_status'    => array('publish', 'draft', 'pending', 'private', 'future'),
            's'              => $s,
            'posts_per_page' => $posts_per_page,
            'paged'          => $paged,
        );

        if ($category || $stop) {
            $args['tax_query'] = array('relation' => 'AND');
            if ($category) {
                $args['tax_query'][] = array(
                    'taxonomy' => 'wbbm_bus_cat',
                    'field'    => 'slug',
                    'terms'    => $category,
                );
            }
            if ($stop) {
                $args['tax_query'][] = array(
                    'taxonomy' => 'wbbm_bus_stops',
                    'field'    => 'slug',
                    'terms'    => $stop,
                );
            }
        }

        $query = new WP_Query($args);
        $total_posts = $query->found_posts;
        $total_pages = $query->max_num_pages;

        if (isset($_GET['deleted']) && $_GET['deleted'] === '1') {
            echo '<div class="notice notice-success is-dismissible" style="margin: 20px 20px 0 0;"><p>' . __('Bus moved to trash.', 'bus-booking-manager') . '</p></div>';
        }

        $start_num = ($paged - 1) * $posts_per_page + 1;
        $end_num = min($paged * $posts_per_page, $total_posts);

        // Fetch categories for filter
        $categories = get_terms(array(
            'taxonomy'   => 'wbbm_bus_cat',
            'hide_empty' => false,
        ));

        ?>
        <div class="wrap shuttle-list-wrap">
            <div class="shuttle-list-container-fullwidth">
                <!-- Header Section -->
                <div class="shuttle-list-header">
                    <div class="header-left">
                        <div class="brand-logo">
                            <span class="dashicons fas fa-bus"></span>
                        </div>
                        <div class="header-title-area">
                            <h2><?php _e('All Bus Services', 'bus-booking-manager'); ?></h2>
                        </div>
                    </div>
                    <div class="header-right">
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=wbbm_bus&page=wbbm-bus-edit')); ?>" class="btn btn-primary">
                            <span class="dashicons dashicons-plus"></span> <?php _e('Add New Bus', 'bus-booking-manager'); ?>
                        </a>
                    </div>
                </div>

                <!-- Filters Card -->
                <div class="shuttle-filters-card">
                    <form method="get" action="<?php echo admin_url('edit.php'); ?>" id="shuttle-list-filter-form">
                        <input type="hidden" name="post_type" value="wbbm_bus">
                        <input type="hidden" name="page" value="wbbm-bus-list">

                        <div class="filters-row">
                            <div class="filter-left" style="flex-grow:initial">
                                <div class="filter-group search-group">
                                    <span class="dashicons dashicons-search"></span>
                                    <input type="text" name="s" value="<?php echo esc_attr($s); ?>" placeholder="<?php _e('Search bus...', 'bus-booking-manager'); ?>" class="form-control">
                                </div>
                                <div class="filter-group status-filter-group">
                                    <select name="wbbm_bus_cat" id="status-filter" class="form-control">
                                        <option value=""><?php _e('All Categories', 'bus-booking-manager'); ?></option>
                                        <?php foreach ($categories as $cat) : ?>
                                            <option value="<?php echo esc_attr($cat->slug); ?>" <?php selected($category, $cat->slug); ?>><?php echo esc_html($cat->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="filter-group seat-date-group">
                                    <label class="screen-reader-text" for="wbbm-seat-date"><?php _e('Seats remaining on', 'bus-booking-manager'); ?></label>
                                    <input type="date" id="wbbm-seat-date" name="wbbm_seat_date"
                                           value="<?php echo esc_attr($seat_date); ?>"
                                           title="<?php esc_attr_e('Show seats remaining on this date', 'bus-booking-manager'); ?>"
                                           class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <?php _e('Filter', 'bus-booking-manager'); ?>
                                </button>
                                <?php if ($s || $category || $stop) : ?>
                                    <a href="<?php echo admin_url('edit.php?post_type=wbbm_bus&page=wbbm-bus-list'); ?>" class="btn btn-outline btn-sm" style="text-decoration:none;">
                                        <?php _e('Clear', 'bus-booking-manager'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Table Content -->
                <div class="shuttle-list-table-card">
                    <table class="shuttle-modern-table">
                        <thead>
                            <tr>
                                <th class="col-check"><input type="checkbox" id="select-all"></th>
                                <th class="col-shuttle"><?php _e('Bus Name', 'bus-booking-manager'); ?></th>
                                <th class="col-category"><?php _e('Category & Date', 'bus-booking-manager'); ?></th>
                                <th class="col-stops"><?php _e('Route', 'bus-booking-manager'); ?></th>
                                <th class="col-capacity"><?php _e('Capacity', 'bus-booking-manager'); ?></th>
                                <th class="col-remaining">
                                    <?php _e('Remaining', 'bus-booking-manager'); ?>
                                    <?php // Name the date, so the figures cannot be read as an all-time total. ?>
                                    <span class="col-remaining-date"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($seat_date))); ?></span>
                                </th>
                                <th class="col-status"><?php _e('Status', 'bus-booking-manager'); ?></th>
                                <th class="col-action"><?php _e('Action', 'bus-booking-manager'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($query->have_posts()) : ?>
                                <?php while ($query->have_posts()) :
                                    $query->the_post();
                                    $post_id = get_the_ID();
                                    $thumb_id = get_post_thumbnail_id($post_id);
                                    $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'thumbnail') : '';
                                    $post_categories = wp_get_post_terms($post_id, 'wbbm_bus_cat', array('fields' => 'names'));

                                    // Total Capacity
                                    $capacity = get_post_meta($post_id, 'wbbm_total_seat', true) ?: '—';

                                    // Seats already sold on the selected date.
                                    $total_seats = (int) get_post_meta($post_id, 'wbbm_total_seat', true);
                                    $sold_seats = self::seats_sold($post_id, $seat_date);
                                    $remaining_seats = max(0, $total_seats - $sold_seats);
                                    $remaining_ratio = $total_seats > 0 ? ($remaining_seats / $total_seats) : 1;
                                    $remaining_class = 'is-ok';
                                    if ($total_seats > 0 && $remaining_seats === 0) {
                                        $remaining_class = 'is-full';
                                    } elseif ($remaining_ratio <= 0.25) {
                                        $remaining_class = 'is-low';
                                    }

                                    // Route Stop Names
                                    $route_info = get_post_meta($post_id, 'wbbm_route_info', true);
                                    $route_pieces = array();
                                    if (is_array($route_info)) {
                                        foreach ($route_info as $route_part) {
                                            if (!empty($route_part['place'])) {
                                                $route_pieces[] = $route_part['place'];
                                            }
                                        }
                                    }

                                    $current_status = get_post_status();
                                    $status_label = ucfirst($current_status);
                                    $status_class = 'status-' . $current_status;

                                    $edit_url = admin_url('edit.php?post_type=wbbm_bus&page=wbbm-bus-edit&post_id=' . $post_id);
                                    $delete_url = wp_nonce_url(add_query_arg(array('action' => 'delete', 'post_id' => $post_id)), 'delete-bus_' . $post_id);
                                    ?>
                                    <tr>
                                        <td><input type="checkbox" name="bus_ids[]" value="<?php echo esc_attr($post_id); ?>"></td>
                                        <td class="shuttle-info-cell" style="width: 250px;">
                                            <div class="shuttle-thumb">
                                                <?php if ($thumb_url) : ?>
                                                    <img src="<?php echo esc_url($thumb_url); ?>" alt="">
                                                <?php else : ?>
                                                    <span class="dashicons dashicons-image-flip"></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="shuttle-details">
                                                <div class="shuttle-title"><a href="<?php echo esc_url($edit_url); ?>"><?php the_title(); ?></a></div>
                                                <div class="shuttle-meta" style="font-size:12px; font-weight:normal;">
                                                    <?php
                                                    $bus_no = get_post_meta($post_id, 'wbbm_bus_no', true);
                                                    echo $bus_no ? __('Coach No:', 'bus-booking-manager') . ' ' . esc_html($bus_no) : __('ID:', 'bus-booking-manager') . ' ' . esc_html($post_id);
                                                    ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 500; color: var(--sh-text-main); margin-bottom: 2px;">
                                                <?php echo !empty($post_categories) ? esc_html(implode(', ', $post_categories)) : '—'; ?>
                                            </div>
                                            <div class="shuttle-sub-meta" style="font-size:12px;"><?php echo get_the_date(); ?></div>
                                        </td>
                                        <td>
                                            <div class="shuttle-stops-display">
                                                <?php
                                                if (!empty($route_pieces)) :
                                                    $count = count($route_pieces);
                                                    ?>
                                                    <div class="stops-list-wrapper">
                                                        <div class="stops-visible">
                                                            <?php echo esc_html(implode(' ➝ ', array_slice($route_pieces, 0, 2))); ?>
                                                            <?php if ($count > 2) : ?>
                                                                <button type="button" class="stops-toggle-btn" title="View all stops">...</button>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if ($count > 2) : ?>
                                                            <div class="stops-hidden-content">
                                                                <div class="stops-full-list" style="margin-top: 8px;">
                                                                    <?php echo esc_html(implode(' ➝ ', $route_pieces)); ?>
                                                                </div>
                                                                <button type="button" class="stops-collapse-btn"><?php _e('Collapse', 'bus-booking-manager'); ?></button>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else :
                                                    echo '—';
                                                endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="capacity-info">
                                                <span class="count"><?php echo esc_html($capacity); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($total_seats > 0) : ?>
                                                <div class="remaining-info <?php echo esc_attr($remaining_class); ?>">
                                                    <span class="count"><?php echo esc_html(number_format_i18n($remaining_seats)); ?></span>
                                                    <span class="sub"><?php
                                                        /* translators: 1: seats sold, 2: total seats. */
                                                        echo esc_html(sprintf(__('%1$s of %2$s sold', 'bus-booking-manager'), number_format_i18n($sold_seats), number_format_i18n($total_seats)));
                                                    ?></span>
                                                </div>
                                            <?php else : ?>
                                                <div class="remaining-info"><span class="count">—</span></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="<?php echo esc_url($edit_url); ?>" class="action-btn" title="Edit"><span class="dashicons dashicons-edit"></span></a>
                                                <a href="<?php echo esc_url($delete_url); ?>" class="action-btn delete-btn" title="Delete" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this bus?', 'bus-booking-manager'); ?>');"><span class="dashicons dashicons-trash"></span></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile;
                                wp_reset_postdata(); ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" class="no-results"><?php _e('No buses found.', 'bus-booking-manager'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <?php if ($total_pages > 0) : ?>
                        <div class="shuttle-pagination-area">
                            <div class="pagination-info">
                                <?php printf(__('Showing %d - %d of %d buses', 'bus-booking-manager'), max(1, $start_num), $end_num, $total_posts); ?>
                            </div>
                            <div class="pagination-controls">
                                <?php if ($paged > 1) : ?>
                                    <a href="<?php echo add_query_arg('paged', $paged - 1); ?>" class="page-link prev"><span class="dashicons dashicons-arrow-left-alt2"></span></a>
                                <?php endif; ?>

                                <?php
                                for ($i = 1; $i <= $total_pages; $i++) {
                                    if ($i == $paged) {
                                        echo '<span class="page-link active">' . $i . '</span>';
                                    } elseif ($i == 1 || $i == $total_pages || ($i >= $paged - 1 && $i <= $paged + 1)) {
                                        echo '<a href="' . add_query_arg('paged', $i) . '" class="page-link">' . $i . '</a>';
                                    } elseif ($i == 2 || $i == $total_pages - 1) {
                                        echo '<span class="pager-sep">...</span>';
                                    }
                                }
                                ?>

                                <?php if ($paged < $total_pages) : ?>
                                    <a href="<?php echo add_query_arg('paged', $paged + 1); ?>" class="page-link next"><span class="dashicons dashicons-arrow-right-alt2"></span></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
}

new BusListPageClass();
