<?php

if (!defined('ABSPATH')) {
    exit;  // if direct access
}

class ShuttleBookingAdminClass
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'wbbm_shuttle_booking_menu'));
        add_action('admin_init', array($this, 'wbbm_handle_shuttle_booking_export'));
        add_action('admin_init', array($this, 'wbbm_handle_shuttle_booking_delete'));
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
                $status_label = $this->get_status_label($status_code);

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
    private function get_filtered_query_args($posts_per_page = 20)
    {
        $args = array(
            'post_type'      => 'wbbm_booking',
            'posts_per_page' => $posts_per_page,
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
        $shuttles = get_posts(array(
            'post_type'      => 'wbbm_shuttle',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ));

        $export_url = add_query_arg(array_merge($_GET, array('export' => 'csv')), admin_url('admin.php'));
?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e('Shuttle Booking List', 'bus-booking-manager'); ?></h1>
            <a href="<?php echo esc_url($export_url); ?>" class="page-title-action"><?php esc_html_e('Export to Excel (CSV)', 'bus-booking-manager'); ?></a>
            <hr class="wp-header-end">

            <?php if (isset($_GET['deleted']) && $_GET['deleted'] == '1') : ?>
                <div class="updated notice is-dismissible">
                    <p><?php esc_html_e('Booking record deleted successfully.', 'bus-booking-manager'); ?></p>
                </div>
            <?php endif; ?>

            <!-- Filter Form -->
            <form method="get" style="margin: 20px 0; display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
                <input type="hidden" name="post_type" value="wbbm_shuttle">
                <input type="hidden" name="page" value="wbbm-shuttle-bookings">

                <div>
                    <label for="shuttle_id"><?php esc_html_e('Shuttle', 'bus-booking-manager'); ?></label><br>
                    <select name="shuttle_id" id="shuttle_id">
                        <option value=""><?php esc_html_e('All Shuttles', 'bus-booking-manager'); ?></option>
                        <?php foreach ($shuttles as $shuttle) : ?>
                            <option value="<?php echo esc_attr($shuttle->ID); ?>" <?php selected(isset($_GET['shuttle_id']) ? $_GET['shuttle_id'] : '', $shuttle->ID); ?>>
                                <?php echo esc_html($shuttle->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="route"><?php esc_html_e('Route (Point)', 'bus-booking-manager'); ?></label><br>
                    <input type="text" name="route" id="route" value="<?php echo isset($_GET['route']) ? esc_attr($_GET['route']) : ''; ?>" placeholder="<?php esc_attr_e('e.g. Airport', 'bus-booking-manager'); ?>">
                </div>

                <div>
                    <label for="journey_date"><?php esc_html_e('Journey Date', 'bus-booking-manager'); ?></label><br>
                    <input type="text" name="journey_date" id="journey_date" value="<?php echo isset($_GET['journey_date']) ? esc_attr($_GET['journey_date']) : ''; ?>">
                </div>

                <div>
                    <label for="booking_date"><?php esc_html_e('Booking Date', 'bus-booking-manager'); ?></label><br>
                    <input type="text" name="booking_date" id="booking_date" value="<?php echo isset($_GET['booking_date']) ? esc_attr($_GET['booking_date']) : ''; ?>">
                </div>

                <div>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Filter', 'bus-booking-manager'); ?></button>
                    <a href="<?php echo admin_url('edit.php?post_type=wbbm_shuttle&page=wbbm-shuttle-bookings'); ?>" class="button"><?php esc_html_e('Reset', 'bus-booking-manager'); ?></a>
                </div>
            </form>

            <table class="wp-list-table widefat fixed striped posts">
                <thead>
                    <tr>
                        <th scope="col" class="manage-column column-id"><?php esc_html_e('Booking ID', 'bus-booking-manager'); ?></th>
                        <th scope="col" class="manage-column column-order"><?php esc_html_e('Order ID', 'bus-booking-manager'); ?></th>
                        <th scope="col" class="manage-column column-shuttle"><?php esc_html_e('Shuttle Name', 'bus-booking-manager'); ?></th>
                        <th scope="col" class="manage-column column-customer"><?php esc_html_e('Customer', 'bus-booking-manager'); ?></th>
                        <th scope="col" class="manage-column column-journey"><?php esc_html_e('Journey Details', 'bus-booking-manager'); ?></th>
                        <th scope="col" class="manage-column column-route"><?php esc_html_e('Route', 'bus-booking-manager'); ?></th>
                        <th scope="col" class="manage-column column-passengers"><?php esc_html_e('Passengers', 'bus-booking-manager'); ?></th>
                        <th scope="col" class="manage-column column-price"><?php esc_html_e('Total Price', 'bus-booking-manager'); ?></th>
                        <th scope="col" class="manage-column column-status"><?php esc_html_e('Status', 'bus-booking-manager'); ?></th>
                        <th scope="col" class="manage-column column-date"><?php esc_html_e('Booking Date', 'bus-booking-manager'); ?></th>
                        <th scope="col" class="manage-column column-actions"><?php esc_html_e('Actions', 'bus-booking-manager'); ?></th>
                    </tr>
                </thead>
                <tbody id="the-list">
                    <?php
                    $args = $this->get_filtered_query_args(20);
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
                            $status_label = $this->get_status_label($status_code);
                    ?>
                            <tr>
                                <td>
                                    <strong><a href="<?php echo esc_url(get_edit_post_link($post_id)); ?>"><?php echo esc_html($post_id); ?></a></strong>
                                </td>
                                <td>
                                    <?php if ($order_id) : ?>
                                        <a href="<?php echo esc_url(get_edit_post_link($order_id)); ?>"><?php echo esc_html($order_id); ?></a>
                                    <?php else : ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($shuttle_id) : ?>
                                        <a href="<?php echo esc_url(get_edit_post_link($shuttle_id)); ?>"><?php echo esc_html($shuttle_title); ?></a>
                                    <?php else : ?>
                                        <?php echo esc_html($shuttle_title); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><strong><?php echo esc_html($user_name); ?></strong></div>
                                    <div class="description"><?php echo esc_html($user_email); ?></div>
                                    <div class="description"><?php echo esc_html($user_phone); ?></div>
                                </td>
                                <td>
                                    <div><?php echo esc_html($journey_date); ?></div>
                                    <div class="description"><?php echo esc_html($journey_time); ?></div>
                                </td>
                                <td>
                                    <div style="display: flex;gap:6px">
                                        <?php if (empty($boarding)) echo 'N/A'; ?>
                                        <div>
                                            <?php echo esc_html($boarding); ?>
                                            <?php if (!empty($pickup_point)) : ?>
                                                <br><small><?php echo esc_html($pickup_point); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <span class="dashicons dashicons-arrow-right-alt" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                        <div>
                                            <?php echo esc_html($droping); ?>
                                            <?php if (!empty($dropoff_point)) : ?>
                                                <br><small><?php echo esc_html($dropoff_point); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo esc_html($seats); ?></td>
                                <td><?php echo wc_price($total_price); ?></td>
                                <td>
                                    <mark class="order-status status-<?php echo sanitize_html_class($status_label); ?>">
                                        <span><?php echo esc_html(ucfirst($status_label)); ?></span>
                                    </mark>
                                </td>
                                <td><?php echo get_the_date(); ?></td>
                                <td>
                                    <?php
                                    $delete_url = add_query_arg(array(
                                        'action'     => 'delete',
                                        'booking_id' => $post_id,
                                        '_wpnonce'   => wp_create_nonce('wbbm_delete_booking_' . $post_id),
                                    ));
                                    ?>
                                    <a href="<?php echo esc_url($delete_url); ?>" class="button button-link-delete" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this booking record?', 'bus-booking-manager'); ?>');" style="color: #a00;border-color:#a00">
                                        <span class="dashicons dashicons-trash" style="font-size: 16px; vertical-align: middle;"></span>
                                        <?php esc_html_e('Delete', 'bus-booking-manager'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php
                        }
                        wp_reset_postdata();
                    } else {
                        ?>
                        <tr>
                            <td colspan="10"><?php esc_html_e('No shuttle bookings found.', 'bus-booking-manager'); ?></td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th scope="col" class="manage-column column-id"><?php esc_html_e('Booking ID', 'bus-booking-manager'); ?></th>
                        <th scope="col" class="manage-column column-order"><?php esc_html_e('Order ID', 'bus-booking-manager'); ?></th>
                        <th scope="col" class="manage-column column-shuttle"><?php esc_html_e('Shuttle Name', 'bus-booking-manager'); ?></th>
                        <th scope="col" class="manage-column column-customer"><?php esc_html_e('Customer', 'bus-booking-manager'); ?></th>
                        <th scope="col" class="manage-column column-journey"><?php esc_html_e('Journey Details', 'bus-booking-manager'); ?></th>
                        <th scope="col" class="manage-column column-route"><?php esc_html_e('Route', 'bus-booking-manager'); ?></th>
                        <th scope="col" class="manage-column column-passengers"><?php esc_html_e('Passengers', 'bus-booking-manager'); ?></th>
                        <th scope="col" class="manage-column column-price"><?php esc_html_e('Total Price', 'bus-booking-manager'); ?></th>
                        <th scope="col" class="manage-column column-status"><?php esc_html_e('Status', 'bus-booking-manager'); ?></th>
                        <th scope="col" class="manage-column column-date"><?php esc_html_e('Booking Date', 'bus-booking-manager'); ?></th>
                        <th scope="col" class="manage-column column-actions"><?php esc_html_e('Actions', 'bus-booking-manager'); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <style>
            .column-id {
                width: 80px;
            }

            .column-order {
                width: 80px;
            }

            .column-shuttle {
                width: 150px;
            }

            .column-passengers {
                width: 100px;
            }

            .column-status mark {
                padding: 2px 8px;
                border-radius: 4px;
                background: #e5e5e5;
                color: #333;
                display: inline-block;
            }

            .column-status mark.status-completed {
                background: #c6e1c6;
                color: #5b841b;
            }

            .column-status mark.status-processing {
                background: #c8d7e1;
                color: #2e4453;
            }

            .column-status mark.status-on-hold {
                background: #f8dda7;
                color: #94660c;
            }

            .column-status mark.status-pending {
                background: #e5e5e5;
                color: #2e4453;
            }
        </style>
<?php
    }

    /**
     * Get Readable Status Label
     */
    private function get_status_label($status_code)
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
