<?php

if (!defined('ABSPATH')) {
    exit;  // if direct access
}

class ShuttleBookingAdminClass
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'wbbm_shuttle_booking_menu'));
        add_action('admin_enqueue_scripts', array($this, 'wbbm_enqueue_assets'));
        add_action('admin_init', array($this, 'wbbm_handle_shuttle_booking_export'));
        add_action('admin_init', array($this, 'wbbm_handle_shuttle_booking_delete'));
        add_action('admin_init', array($this, 'wbbm_handle_shuttle_ticket_pdf'));
    }

    public function wbbm_enqueue_assets($hook)
    {
        if (strpos($hook, 'wbbm-shuttle-bookings') !== false) {
            wp_enqueue_style('shuttle-list-css', WBTM_PLUGIN_URL . 'assets/admin/shuttle-list.css', array(), time());
            wp_enqueue_script('shuttle-list-js', WBTM_PLUGIN_URL . 'assets/admin/shuttle-list.js', array('jquery'), time(), true);
        }
    }

    /**
     * Handle Booking Deletion
     */
    public function wbbm_handle_shuttle_booking_delete()
    {
        if (!isset($_GET['page']) || $_GET['page'] !== 'wbbm-shuttle-bookings' || !isset($_GET['action']) || $_GET['action'] !== 'delete') {
            return;
        }

        if (!isset($_GET['booking_id']) || !isset($_GET['_wpnonce'])) {
            return;
        }

        $booking_id = intval($_GET['booking_id']);
        $nonce = $_GET['_wpnonce'];

        if (!wp_verify_nonce($nonce, 'wbbm_delete_booking_' . $booking_id)) {
            wp_die(esc_html__('Security check failed.', 'bus-booking-manager'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to perform this action.', 'bus-booking-manager'));
        }

        $deleted = wp_delete_post($booking_id, true);

        if ($deleted) {
            $redirect_url = remove_query_arg(array('action', 'booking_id', '_wpnonce'), wp_get_referer());
            $redirect_url = add_query_arg('deleted', '1', $redirect_url);
            wp_safe_redirect($redirect_url);
            exit;
        }
    }

    /**
     * Handle Export to Excel (CSV)
     */
    public function wbbm_handle_shuttle_booking_export()
    {
        if (!isset($_GET['page']) || $_GET['page'] !== 'wbbm-shuttle-bookings' || !isset($_GET['export']) || $_GET['export'] !== 'csv') {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'bus-booking-manager'));
        }

        $filename = 'shuttle-bookings-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');

        // Column headers
        fputcsv($output, array(
            __('Booking ID', 'bus-booking-manager'),
            __('Order ID', 'bus-booking-manager'),
            __('Shuttle Name', 'bus-booking-manager'),
            __('Customer Name', 'bus-booking-manager'),
            __('Customer Email', 'bus-booking-manager'),
            __('Customer Phone', 'bus-booking-manager'),
            __('Journey Date', 'bus-booking-manager'),
            __('Journey Time', 'bus-booking-manager'),
            __('Boarding Point', 'bus-booking-manager'),
            __('Pickup Point', 'bus-booking-manager'),
            __('Droping Point', 'bus-booking-manager'),
            __('Dropoff Point', 'bus-booking-manager'),
            __('Passengers', 'bus-booking-manager'),
            __('Total Price', 'bus-booking-manager'),
            __('Status', 'bus-booking-manager'),
            __('Booking Date', 'bus-booking-manager'),
        ));

        $args = $this->get_filtered_query_args(-1);
        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $order_id = get_post_meta($post_id, '_wbbm_order_id', true);
                $shuttle_id = get_post_meta($post_id, '_wbbm_shuttle_id', true);
                $user_name = get_post_meta($post_id, '_wbbm_user_name', true);
                $user_email = get_post_meta($post_id, '_wbbm_user_email', true);
                $user_phone = get_post_meta($post_id, '_wbbm_user_phone', true);
                $journey_date = get_post_meta($post_id, '_wbbm_journey_date', true);
                $journey_time = get_post_meta($post_id, '_wbbm_user_start', true);
                $boarding = get_post_meta($post_id, '_wbbm_boarding_point', true);
                $droping = get_post_meta($post_id, '_wbbm_droping_point', true);
                $pickup_point = get_post_meta($post_id, '_wbbm_pickup_point', true);
                $dropoff_point = get_post_meta($post_id, '_wbbm_dropoff_point', true);
                $seats = get_post_meta($post_id, '_wbbm_seat', true);
                $total_price = get_post_meta($post_id, '_wbbm_total_price', true);
                $status_code = get_post_meta($post_id, '_wbbm_status', true);

                $shuttle_title = $shuttle_id ? get_the_title($shuttle_id) : '—';
                $status_label = $this->wbbm_get_status_label($status_code);

                fputcsv($output, array(
                    $post_id,
                    $order_id,
                    $shuttle_title,
                    $user_name,
                    $user_email,
                    $user_phone,
                    $journey_date,
                    $journey_time,
                    $boarding,
                    $pickup_point,
                    $droping,
                    $dropoff_point,
                    $seats,
                    $total_price,
                    ucfirst($status_label),
                    get_the_date(),
                ));
            }
            wp_reset_postdata();
        }

        fclose($output);
        exit;
    }

    /**
     * Get Filtered Query Args
     */
    private function get_filtered_query_args($posts_per_page = 20, $paged = 1)
    {
        $args = array(
            'post_type'      => 'wbbm_booking',
            'posts_per_page' => $posts_per_page,
            'paged'          => $paged,
            'post_status'    => 'publish',
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => '_wbbm_is_shuttle',
                    'value'   => 'yes',
                    'compare' => '=',
                ),
            ),
        );

        // Filter by Shuttle Name (ID)
        if (!empty($_GET['shuttle_id'])) {
            $args['meta_query'][] = array(
                'key'     => '_wbbm_shuttle_id',
                'value'   => sanitize_text_field($_GET['shuttle_id']),
                'compare' => '=',
            );
        }

        // Filter by Route (Boarding or Droping Point)
        if (!empty($_GET['route'])) {
            $route_term = sanitize_text_field($_GET['route']);
            $args['meta_query'][] = array(
                'relation' => 'OR',
                array(
                    'key'     => '_wbbm_boarding_point',
                    'value'   => $route_term,
                    'compare' => 'LIKE',
                ),
                array(
                    'key'     => '_wbbm_droping_point',
                    'value'   => $route_term,
                    'compare' => 'LIKE',
                ),
            );
        }

        // Filter by Journey Date
        if (!empty($_GET['journey_date'])) {
            $args['meta_query'][] = array(
                'key'     => '_wbbm_journey_date',
                'value'   => sanitize_text_field($_GET['journey_date']),
                'compare' => '=',
            );
        }

        // Filter by Booking Date
        if (!empty($_GET['booking_date'])) {
            $args['date_query'] = array(
                array(
                    'year'  => date('Y', strtotime($_GET['booking_date'])),
                    'month' => date('m', strtotime($_GET['booking_date'])),
                    'day'   => date('d', strtotime($_GET['booking_date'])),
                ),
            );
        }

        return $args;
    }

    /**
     * Register Shuttle Bookings Submenu
     */
    public function wbbm_shuttle_booking_menu()
    {
        add_submenu_page(
            'edit.php?post_type=wbbm_shuttle',
            esc_html__('Shuttle Bookings', 'bus-booking-manager'),
            esc_html__('Shuttle Bookings', 'bus-booking-manager'),
            'manage_options',
            'wbbm-shuttle-bookings',
            array($this, 'render_shuttle_booking_list')
        );
    }

    /**
     * Render Shuttle Booking List
     */
    public function render_shuttle_booking_list()
    {
        global $wbbm;

        $shuttles = get_posts(array(
            'post_type'      => 'wbbm_shuttle',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ));

        $export_url = add_query_arg(array_merge($_GET, array('export' => 'csv')), admin_url('admin.php'));

        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $posts_per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 20;

        $args = $this->get_filtered_query_args($posts_per_page, $paged);
        $query = new WP_Query($args);
        $total_posts = $query->found_posts;
        $total_pages = $query->max_num_pages;

        $start_num = ($paged - 1) * $posts_per_page + 1;
        $end_num = min($paged * $posts_per_page, $total_posts);
        ?>
        <div class="wrap shuttle-list-wrap">
            <div class="shuttle-list-container">
                <!-- Header Section -->
                <div class="shuttle-list-header">
                    <div class="header-left">
                        <div class="brand-logo">
                            <span class="dashicons dashicons-tickets-alt"></span>
                        </div>
                        <div class="header-title-area">
                            <h2><?php esc_html_e('Shuttle Bookings', 'bus-booking-manager'); ?></h2>
                        </div>
                    </div>
                    <div class="header-right">
                        <a href="<?php echo esc_url($export_url); ?>" class="btn btn-outline" style="text-decoration: none;">
                            <span class="dashicons dashicons-download" style="margin-top:2px;"></span> <?php esc_html_e('Export CSV', 'bus-booking-manager'); ?>
                        </a>
                    </div>
                </div>

                <?php if (isset($_GET['deleted']) && $_GET['deleted'] == '1') : ?>
                    <div class="notice notice-success is-dismissible" style="margin: 20px 20px 0 0;">
                        <p><?php esc_html_e('Booking record deleted successfully.', 'bus-booking-manager'); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Filters Card -->
                <div class="shuttle-filters-card">
                    <form method="get" action="" id="shuttle-booking-filter-form">
                        <input type="hidden" name="post_type" value="wbbm_shuttle">
                        <input type="hidden" name="page" value="wbbm-shuttle-bookings">

                        <div class="shuttle-booking-filters-grid">
                            <div class="filter-group">
                                <select name="shuttle_id" id="shuttle_id" class="form-control">
                                    <option value=""><?php esc_html_e('All Shuttles', 'bus-booking-manager'); ?></option>
                                    <?php foreach ($shuttles as $shuttle) : ?>
                                        <option value="<?php echo esc_attr($shuttle->ID); ?>" <?php selected(isset($_GET['shuttle_id']) ? $_GET['shuttle_id'] : '', $shuttle->ID); ?>>
                                            <?php echo esc_html($shuttle->post_title); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-group search-group booking-route-search">
                                <span class="dashicons dashicons-location"></span>
                                <input type="text" name="route" id="route" value="<?php echo isset($_GET['route']) ? esc_attr($_GET['route']) : ''; ?>" placeholder="<?php esc_attr_e('Route (e.g. Airport)...', 'bus-booking-manager'); ?>" class="form-control">
                            </div>
                            <div class="filter-group">
                                <input type="date" name="journey_date" id="journey_date" value="<?php echo isset($_GET['journey_date']) ? esc_attr($_GET['journey_date']) : ''; ?>" class="form-control" placeholder="<?php esc_attr_e('Journey Date', 'bus-booking-manager'); ?>">
                            </div>
                            <div class="filter-group">
                                <input type="date" name="booking_date" id="booking_date" value="<?php echo isset($_GET['booking_date']) ? esc_attr($_GET['booking_date']) : ''; ?>" class="form-control" placeholder="<?php esc_attr_e('Booking Date', 'bus-booking-manager'); ?>">
                            </div>
                        </div>

                        <div class="shuttle-booking-filter-actions">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <?php _e('Filter', 'bus-booking-manager'); ?>
                            </button>
                            <?php if (!empty($_GET['shuttle_id']) || !empty($_GET['route']) || !empty($_GET['journey_date']) || !empty($_GET['booking_date'])) : ?>
                                <a href="<?php echo admin_url('edit.php?post_type=wbbm_shuttle&page=wbbm-shuttle-bookings'); ?>" class="btn btn-outline btn-sm" style="text-decoration:none;">
                                    <?php _e('Clear', 'bus-booking-manager'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Table Content -->
                <div class="shuttle-list-table-card">
                    <table class="shuttle-modern-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th class="col-id"><?php esc_html_e('ID & Order', 'bus-booking-manager'); ?></th>
                                <th class="col-shuttle"><?php esc_html_e('Shuttle', 'bus-booking-manager'); ?></th>
                                <th class="col-customer"><?php esc_html_e('Customer', 'bus-booking-manager'); ?></th>
                                <th class="col-journey"><?php esc_html_e('Journey Details', 'bus-booking-manager'); ?></th>
                                <th class="col-route" style="width: 250px;"><?php esc_html_e('Route', 'bus-booking-manager'); ?></th>
                                <th class="col-passengers"><?php esc_html_e('Seats & Total', 'bus-booking-manager'); ?></th>
                                <th class="col-status"><?php esc_html_e('Status', 'bus-booking-manager'); ?></th>
                                <th class="col-action"><?php esc_html_e('Action', 'bus-booking-manager'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($query->have_posts()) : ?>
                                <?php while ($query->have_posts()) :
                                    $query->the_post();
                                    $post_id = get_the_ID();
                                    $order_id = get_post_meta($post_id, '_wbbm_order_id', true);
                                    $shuttle_id = get_post_meta($post_id, '_wbbm_shuttle_id', true);
                                    $user_name = get_post_meta($post_id, '_wbbm_user_name', true);
                                    $user_email = get_post_meta($post_id, '_wbbm_user_email', true);
                                    $user_phone = get_post_meta($post_id, '_wbbm_user_phone', true);
                                    $journey_date = get_post_meta($post_id, '_wbbm_journey_date', true);
                                    $journey_time = get_post_meta($post_id, '_wbbm_user_start', true);
                                    $boarding = get_post_meta($post_id, '_wbbm_boarding_point', true);
                                    $droping = get_post_meta($post_id, '_wbbm_droping_point', true);
                                    $pickup_point = get_post_meta($post_id, '_wbbm_pickup_point', true);
                                    $dropoff_point = get_post_meta($post_id, '_wbbm_dropoff_point', true);
                                    $seats = get_post_meta($post_id, '_wbbm_seat', true);
                                    $total_price = get_post_meta($post_id, '_wbbm_total_price', true);
                                    $status_code = get_post_meta($post_id, '_wbbm_status', true);

                                    $shuttle_title = $shuttle_id ? get_the_title($shuttle_id) : '—';
                                    $status_label = $this->wbbm_get_status_label($status_code);

                                    $status_class = 'status-draft';
                                    if (strtolower($status_label) === 'completed') {
                                        $status_class = 'status-publish';
                                    } elseif (strtolower($status_label) === 'processing' || strtolower($status_label) === 'pending' || strtolower($status_label) === 'on-hold') {
                                        $status_class = 'status-pending';
                                    } else {
                                        $status_class = 'status-draft';
                                    }

                                    $delete_url = wp_nonce_url(add_query_arg(array('action' => 'delete', 'booking_id' => $post_id)), 'wbbm_delete_booking_' . $post_id);
                                    $pdf_url = add_query_arg(array(
                                        'shuttle_ticket_pdf' => '1',
                                        'booking_id'         => $post_id,
                                    ), admin_url('admin.php'));
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="shuttle-title"><a href="<?php echo esc_url(get_edit_post_link($post_id)); ?>" style="color:var(--sh-primary);text-decoration:none;">#<?php echo esc_html($post_id); ?></a></div>
                                            <div class="shuttle-meta"><?php echo $order_id ? '<a href="' . esc_url(get_edit_post_link($order_id)) . '" style="color:var(--sh-text-soft);">Ord #' . esc_html($order_id) . '</a>' : '—'; ?></div>
                                            <div class="shuttle-sub-meta" style="font-size:11px;"><?php echo get_the_date(); ?></div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: var(--sh-text-main); font-size:14px;">
                                                <?php if ($shuttle_id) : ?>
                                                    <a href="<?php echo esc_url(get_edit_post_link($shuttle_id)); ?>" style="text-decoration: none; color: inherit;"><?php echo esc_html($shuttle_title); ?></a>
                                                <?php else : ?>
                                                    <?php echo esc_html($shuttle_title); ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: var(--sh-text-main); margin-bottom: 2px;"><?php echo esc_html($user_name); ?></div>
                                            <div class="shuttle-sub-meta" style="font-size:12px;"><?php echo esc_html($user_email); ?></div>
                                            <div class="shuttle-sub-meta" style="font-size:12px;"><?php echo esc_html($user_phone); ?></div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 500; color: var(--sh-text-main); margin-bottom: 2px;"><?php echo esc_html($journey_date); ?></div>
                                            <div class="shuttle-sub-meta" style="color: var(--sh-primary); font-weight: 600;"><?php echo esc_html($journey_time); ?></div>
                                        </td>
                                        <td>
                                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                                <div style="font-size: 13px;">
                                                    <span style="color: #64748b; font-weight: bold; font-size: 16px; line-height: 1; vertical-align: middle;">○</span>
                                                    <span style="font-weight: 600; color: var(--sh-text-main);"><?php echo $boarding ? esc_html($boarding) : 'N/A'; ?></span>
                                                    <?php if (!empty($pickup_point)) : ?>
                                                        <br><span style="padding-left: 14px; font-size: 12px; color: var(--sh-text-soft);">↳ <?php echo esc_html($pickup_point); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div style="padding-left: 4px; border-left: 1px dashed #cbd5e1; margin-left: 3px; height: 12px;"></div>
                                                <div style="font-size: 13px;">
                                                    <span style="color: var(--sh-primary); font-weight: bold; font-size: 16px; line-height: 1; vertical-align: middle;">●</span>
                                                    <span style="font-weight: 600; color: var(--sh-text-main);"><?php echo $droping ? esc_html($droping) : 'N/A'; ?></span>
                                                    <?php if (!empty($dropoff_point)) : ?>
                                                        <br><span style="padding-left: 14px; font-size: 12px; color: var(--sh-text-soft);">↳ <?php echo esc_html($dropoff_point); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="capacity-info">
                                                <span class="count" style="margin-bottom: 4px; font-size: 13px; color: var(--sh-text-soft);"><?php echo esc_html($seats); ?> Seats</span>
                                                <span class="count" style="color: var(--sh-text-main); font-weight: 700; font-size: 15px;"><?php echo wc_price($total_price); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html(ucfirst($status_label)); ?></span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php
                                                if (is_object($wbbm) && method_exists($wbbm, 'wbbm_pdf')) {
                                                    ?>
                                                    <a href="<?php echo esc_url($pdf_url); ?>" target="_blank" class="action-btn" title="<?php esc_attr_e('PDF Ticket', 'bus-booking-manager'); ?>">
                                                        <span class="dashicons dashicons-media-document"></span>
                                                    </a>
                                                <?php } ?>
                                                <a href="<?php echo esc_url($delete_url); ?>" class="action-btn delete-btn" title="Delete" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this booking record?', 'bus-booking-manager'); ?>');">
                                                    <span class="dashicons dashicons-trash"></span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile;
                                wp_reset_postdata(); ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="8" class="no-results"><?php _e('No shuttle bookings found.', 'bus-booking-manager'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <?php if ($total_pages > 0) : ?>
                        <div class="shuttle-pagination-area">
                            <div class="pagination-info">
                                <?php printf(__('Showing %d - %d of %d bookings', 'bus-booking-manager'), max(1, $start_num), $end_num, $total_posts); ?>
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

                                <div class="per-page-selector" style="margin-left: 10px;">
                                    <select class="form-control" onchange="window.location.href=this.value;" style="width:100%;height: 36px !important; padding: 4px 10px !important;">
                                        <?php
                                        $limits = array(10, 20, 50, 100);
                                        foreach ($limits as $limit) {
                                            $url = add_query_arg('per_page', $limit);
                                            $url = add_query_arg('paged', 1, $url);
                                            $selected = ($limit == $posts_per_page) ? 'selected' : '';
                                            echo '<option value="' . esc_url($url) . '" ' . $selected . '>' . $limit . ' / page</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    public function wbbm_handle_shuttle_ticket_pdf()
    {
        global $wbbm;

        if (!isset($_GET['shuttle_ticket_pdf']) || $_GET['shuttle_ticket_pdf'] !== '1' || !isset($_GET['booking_id'])) {
            return;
        }

        $booking_id = intval($_GET['booking_id']);

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to perform this action.', 'bus-booking-manager'));
        }

        // Fetch Booking Data
        $order_id = get_post_meta($booking_id, '_wbbm_order_id', true);
        $shuttle_id = get_post_meta($booking_id, '_wbbm_shuttle_id', true);
        $customer_name = get_post_meta($booking_id, '_wbbm_user_name', true);
        $customer_email = get_post_meta($booking_id, '_wbbm_user_email', true);
        $customer_phone = get_post_meta($booking_id, '_wbbm_user_phone', true);
        $journey_date = get_post_meta($booking_id, '_wbbm_journey_date', true);
        $journey_time = get_post_meta($booking_id, '_wbbm_user_start', true);
        $boarding_point = get_post_meta($booking_id, '_wbbm_boarding_point', true);
        $droping_point = get_post_meta($booking_id, '_wbbm_droping_point', true);
        $passengers = get_post_meta($booking_id, '_wbbm_seat', true);
        $total_price = get_post_meta($booking_id, '_wbbm_total_price', true);
        $status_code = get_post_meta($booking_id, '_wbbm_status', true);
        $pickup_point = get_post_meta($booking_id, '_wbbm_pickup_point', true);
        $dropoff_point = get_post_meta($booking_id, '_wbbm_dropoff_point', true);

        $shuttle_post = get_post($shuttle_id);
        $shuttle_name = $shuttle_post ? $shuttle_post->post_title : 'N/A';
        $status_text = $this->wbbm_get_status_label($status_code);

        // Date formatting
        $formatted_date = date_i18n(get_option('date_format'), strtotime($journey_date));
        $currency_symbol = get_woocommerce_currency_symbol();

        // Check for PDF Library
        if (!class_exists('\Mpdf\Mpdf') && !class_exists('mPDF')) {
            // Try to load from MagePeople PDF Support plugin
            $pdf_support_path = WP_PLUGIN_DIR . '/magepeople-pdf-support-master/lib/vendor/autoload.php';
            if (file_exists($pdf_support_path)) {
                require_once $pdf_support_path;
            }
        }

        if (!class_exists('\Mpdf\Mpdf') && !class_exists('mPDF')) {
            wp_die(esc_html__('PDF generation library (mPDF) not found. Please ensure "MagePeople PDF Support" plugin is active.', 'bus-booking-manager'));
        }

        // Initialize mPDF
        try {
            if (class_exists('\Mpdf\Mpdf')) {
                $mpdf = new \Mpdf\Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'autoScriptToLang' => true,
                    'autoLangToFont' => true,
                    'default_font' => 'freeserif'
                ]);
            } else {
                $mpdf = new mPDF('utf-8', 'A4');
                $mpdf->autoScriptToLang = true;
                $mpdf->autoLangToFont = true;
                $mpdf->SetDefaultFont('freeserif');
            }
        } catch (Exception $e) {
            wp_die(esc_html($e->getMessage()));
        }

        // PDF Content
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>

        <head>
            <style>
                body {
                    font-family: 'freeserif', 'dejavusans', sans-serif;
                    color: #333;
                    line-height: 1.6;
                }

                .ticket-container {
                    max-width: 800px;
                    margin: 0 auto;
                    border: 1px solid #eee;
                    padding: 40px;
                    border-radius: 10px;
                    background: #fff;
                }

                .header {
                    border-bottom: 2px solid #0073aa;
                    padding-bottom: 20px;
                    margin-bottom: 30px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }

                .header h1 {
                    color: #0073aa;
                    margin: 0;
                    font-size: 28px;
                    text-transform: uppercase;
                }

                .header .booking-id {
                    font-size: 18px;
                    font-weight: bold;
                    color: #666;
                }

                .section {
                    margin-bottom: 30px;
                }

                .section-title {
                    font-size: 16px;
                    font-weight: bold;
                    color: #0073aa;
                    border-bottom: 1px solid #eee;
                    padding-bottom: 5px;
                    margin-bottom: 15px;
                    text-transform: uppercase;
                }

                .grid {
                    display: flex;
                    flex-wrap: wrap;
                    margin-left: -15px;
                    margin-right: -15px;
                }

                .col {
                    flex: 1;
                    padding: 0 15px;
                    min-width: 250px;
                }

                .info-item {
                    margin-bottom: 10px;
                }

                .info-label {
                    font-size: 12px;
                    color: #888;
                    text-transform: uppercase;
                    display: block;
                }

                .info-value {
                    font-size: 15px;
                    font-weight: bold;
                    color: #333;
                }

                .trip-card {
                    background: #f9f9f9;
                    padding: 20px;
                    border-radius: 8px;
                    margin-bottom: 30px;
                    border-left: 5px solid #0073aa;
                }

                .route-line {
                    margin: 15px 0;
                    border-top: 1px dashed #ccc;
                    position: relative;
                }

                .price-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }

                .price-table th {
                    text-align: left;
                    background: #f2f2f2;
                    padding: 10px;
                    font-size: 14px;
                }

                .price-table td {
                    padding: 10px;
                    border-bottom: 1px solid #eee;
                    font-size: 14px;
                }

                .price-total {
                    font-size: 20px;
                    font-weight: bold;
                    color: #0073aa;
                    text-align: right;
                    margin-top: 20px;
                }

                .status-badge {
                    display: inline-block;
                    padding: 5px 12px;
                    border-radius: 20px;
                    font-size: 12px;
                    font-weight: bold;
                    text-transform: uppercase;
                    background: #e1e1e1;
                }

                .status-completed {
                    background: #d4edda;
                    color: #155724;
                }

                .status-processing {
                    background: #fff3cd;
                    color: #856404;
                }

                .status-pending {
                    background: #d1ecf1;
                    color: #0c5460;
                }

                .footer {
                    text-align: center;
                    margin-top: 50px;
                    font-size: 12px;
                    color: #999;
                    border-top: 1px solid #eee;
                    padding-top: 20px;
                }
            </style>
        </head>

        <body>
            <div class="ticket-container">
                <div class="header">
                    <table width="100%">
                        <tr>
                            <td>
                                <h1><?php _e('Shuttle Ticket', 'bus-booking-manager'); ?></h1>
                            </td>
                            <td align="right">
                                <span class="booking-id"><?php _e('Booking ID:', 'bus-booking-manager'); ?> #<?php echo esc_html($booking_id); ?></span><br>
                                <span style="font-size: 12px; color: #888;"><?php _e('Order ID:', 'bus-booking-manager'); ?> #<?php echo esc_html($order_id); ?></span>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="section">
                    <div class="section-title"><?php _e('Passenger Information', 'bus-booking-manager'); ?></div>
                    <table width="100%">
                        <tr>
                            <td width="33%">
                                <div class="info-item">
                                    <span class="info-label"><?php _e('Name', 'bus-booking-manager'); ?></span>
                                    <p class="info-value"><?php echo esc_html($customer_name); ?></p>
                                </div>
                            </td>
                            <td width="33%">
                                <div class="info-item">
                                    <span class="info-label"><?php _e('Email', 'bus-booking-manager'); ?></span>
                                    <p class="info-value"><?php echo esc_html($customer_email); ?></p>
                                </div>
                            </td>
                            <td width="33%">
                                <div class="info-item">
                                    <span class="info-label"><?php _e('Phone', 'bus-booking-manager'); ?></span>
                                    <p class="info-value"><?php echo esc_html($customer_phone); ?></p>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="section">
                    <div class="section-title"><?php _e('Trip Details', 'bus-booking-manager'); ?></div>
                    <div class="trip-card">
                        <table width="100%">
                            <tr>
                                <td colspan="2">
                                    <div class="info-item" style="margin-bottom: 20px;">
                                        <span class="info-label"><?php _e('Shuttle Service', 'bus-booking-manager'); ?></span>
                                        <span class="info-value" style="font-size: 18px;"><?php echo esc_html($shuttle_name); ?></span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td width="50%">
                                    <div class="info-item">
                                        <span class="info-label"><?php _e('Journey Date', 'bus-booking-manager'); ?></span>
                                        <span class="info-value"><?php echo esc_html($formatted_date); ?></span>
                                    </div>
                                </td>
                                <td width="50%">
                                    <div class="info-item">
                                        <span class="info-label"><?php _e('Pickup Time', 'bus-booking-manager'); ?></span>
                                        <span class="info-value"><?php echo esc_html($journey_time); ?></span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div class="route-line"></div>
                                </td>
                            </tr>
                            <tr>
                                <td width="50%">
                                    <div class="info-item">
                                        <span class="info-label"><?php _e('From (Pickup)', 'bus-booking-manager'); ?></span>
                                        <span class="info-value"><?php echo esc_html($boarding_point); ?></span>
                                        <?php if ($pickup_point && $pickup_point !== 'main') : ?>
                                            <div style="font-size: 12px; color: #666;"><?php echo esc_html($pickup_point); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td width="50%">
                                    <div class="info-item">
                                        <span class="info-label"><?php _e('To (Drop-off)', 'bus-booking-manager'); ?></span>
                                        <span class="info-value"><?php echo esc_html($droping_point); ?></span>
                                        <?php if ($dropoff_point && $dropoff_point !== 'main') : ?>
                                            <div style="font-size: 12px; color: #666;"><?php echo esc_html($dropoff_point); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="section">
                    <div class="section-title"><?php _e('Payment Summary', 'bus-booking-manager'); ?></div>
                    <table class="price-table">
                        <thead>
                            <tr>
                                <th><?php _e('Description', 'bus-booking-manager'); ?></th>
                                <th><?php _e('Quantity', 'bus-booking-manager'); ?></th>
                                <th align="right"><?php _e('Amount', 'bus-booking-manager'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php _e('Shuttle Ticket Booking', 'bus-booking-manager'); ?></td>
                                <td><?php echo esc_html($passengers); ?> <?php _e('Passenger(s)', 'bus-booking-manager'); ?></td>
                                <td align="right"><?php echo $currency_symbol . ' ' . number_format($total_price, 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="price-total">
                        <?php _e('Total Paid:', 'bus-booking-manager'); ?>
                        <?php echo $currency_symbol . ' ' . number_format($total_price, 2); ?>
                    </div>
                    <div style="margin-top: 10px; text-align: right;">
                        <span class="status-badge status-<?php echo esc_attr($status_text); ?>">
                            <?php echo esc_html(ucfirst($status_text)); ?>
                        </span>
                    </div>
                </div>

                <div class="footer">
                    <p><?php _e('Thank you for choosing our shuttle service. Please arrive at the pickup point 15 minutes before departure.', 'bus-booking-manager'); ?></p>
                    <p><?php echo esc_html(get_bloginfo('name')); ?> - <?php echo esc_url(home_url()); ?></p>
                </div>
            </div>
        </body>

        </html>
        <?php
        $html = ob_get_clean();

        if (method_exists($wbbm, 'wbbm_pdf')) {
            $wbbm->wbbm_pdf($html, 'shuttle_ticket_' . $booking_id . '.pdf');
        }
    }

    /**
     * Get Readable Status Label
     */
    private function wbbm_get_status_label($status_code)
    {
        $statuses = array(
            '3' => 'pending',
            '6' => 'on-hold',
            '1' => 'processing',
            '2' => 'completed'
        );

        return isset($statuses[$status_code]) ? $statuses[$status_code] : 'unknown';
    }
}
