<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Booking list admin page. Lists bookings taken through the custom payment
 * flow -- WooCommerce orders live in WooCommerce > Orders.
 *
 * No admin page anywhere in this plugin lists individual bookings at all
 * today -- WooCommerce's own Orders screen is the only booking-visibility
 * surface, and only for WooCommerce-mode buses. Without this page, an
 * Offline Payment booking's _wbbm_payment_status would stay 'pending'
 * forever with no way to confirm it once the admin has actually collected
 * payment (bank transfer, cash, etc. -- see inc/wbbm-offline-booking.php).
 *
 * Follows the exact conventions already established by BusListPageClass.php:
 * a hand-rolled HTML table (no WP_List_Table subclass) styled by
 * assets/admin/wbbm-list-tables.css, and a nonce'd query-arg row action
 * processed in admin_init (the same pattern as that class's delete-bus
 * action) instead of AJAX/bulk-actions.
 *
 * Storage model note: inc/wbbm-offline-booking.php inserts one wbbm_booking
 * post per seat (matching wbbm_add_passenger_to_db()'s own per-passenger
 * loop), all sharing one negative, timestamp-derived _wbbm_order_id. This
 * page groups by that id so a 3-seat booking shows as one row, and "Mark as
 * Paid" flips every post in the group together -- never just one seat of a
 * multi-seat booking.
 */
class BusOfflineBookingListPageClass
{
    const PAGE_SLUG = 'wbbm-offline-bookings';

    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_page'));
        add_action('admin_menu', array($this, 'hide_menu_row'), 999);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_init', array($this, 'handle_actions'));
    }

    public function register_page()
    {
        add_submenu_page(
            'edit.php?post_type=wbbm_bus',
            __('Booking list', 'bus-booking-manager'),
            __('Booking list', 'bus-booking-manager'),
            'manage_options',
            self::PAGE_SLUG,
            array($this, 'render_page')
        );
    }

    /**
     * Drop this page's row from the menu.
     *
     * The Bookings hub is the "Booking list" entry users see now. The page
     * stays registered rather than being dropped altogether so its existing
     * URLs, row actions and nonce targets keep resolving, and so its
     * capability check still runs -- exactly how WBBM_Admin_Hub retires the
     * screens it absorbs.
     */
    public function hide_menu_row()
    {
        remove_submenu_page('edit.php?post_type=wbbm_bus', self::PAGE_SLUG);
    }

    public function enqueue_assets($hook)
    {
        if (strpos((string) $hook, self::PAGE_SLUG) === false) {
            return;
        }

        wp_enqueue_style('wbbm-list-css', WBTM_PLUGIN_URL . 'assets/admin/wbbm-list-tables.css', array(), time());
    }

    /**
     * "Mark as Paid" row action -- same nonce'd query-arg + admin_init
     * pattern as BusListPageClass::handle_bus_actions()'s delete-bus action.
     */
    public function handle_actions()
    {
        if (
            !isset($_GET['page'], $_GET['action'], $_GET['order_id'])
            || self::PAGE_SLUG !== $_GET['page']
            || 'mark_paid' !== $_GET['action']
        ) {
            return;
        }

        $order_id = sanitize_text_field(wp_unslash($_GET['order_id']));

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to update this booking.', 'bus-booking-manager'));
        }

        check_admin_referer('mark-paid-order_' . $order_id);

        $posts = get_posts(array(
            'post_type'      => 'wbbm_booking',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
            'meta_query'     => array(
                array('key' => '_wbbm_order_id', 'value' => $order_id, 'compare' => '='),
                array('key' => '_wbbm_payment_method', 'value' => 'offline', 'compare' => '='),
            ),
        ));

        foreach ($posts as $post_id) {
            update_post_meta($post_id, '_wbbm_payment_status', 'confirmed');
        }

        wp_safe_redirect(admin_url('edit.php?post_type=wbbm_bus&page=' . self::PAGE_SLUG . '&marked_paid=1'));
        exit;
    }

    /**
     * One wbbm_booking post per seat, all sharing one _wbbm_order_id -- fetch
     * them and group by that id so a multi-seat booking shows as one row.
     *
     * Every flow writes these posts, so no gateway filter: a WooCommerce
     * booking is the same shape with _wbbm_payment_method = 'woocommerce'.
     * The post type is the scope -- these are bus bookings, so unrelated
     * WooCommerce orders never appear here.
     */
    private function get_grouped_bookings()
    {
        $posts = get_posts(array(
            'post_type'      => 'wbbm_booking',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'no_found_rows'  => true,
        ));

        $groups = array();

        foreach ($posts as $post) {
            $order_id = get_post_meta($post->ID, '_wbbm_order_id', true);
            $order_id = $order_id ? $order_id : 'post-' . $post->ID;

            if (!isset($groups[$order_id])) {
                $groups[$order_id] = array(
                    'order_id'       => $order_id,
                    'post'           => $post,
                    'seat_count'     => 0,
                    'legs'           => array(),
                    'bus_id'         => (int) get_post_meta($post->ID, '_wbbm_bus_id', true),
                    'user_name'      => get_post_meta($post->ID, '_wbbm_user_name', true),
                    'user_phone'     => get_post_meta($post->ID, '_wbbm_user_phone', true),
                    'journey_date'   => get_post_meta($post->ID, '_wbbm_journey_date', true),
                    'boarding_point' => get_post_meta($post->ID, '_wbbm_boarding_point', true),
                    'droping_point'  => get_post_meta($post->ID, '_wbbm_droping_point', true),
                    'total_price'    => (float) get_post_meta($post->ID, '_wbbm_total_price', true),
                    'tax_amount'     => (float) get_post_meta($post->ID, '_wbbm_tax_amount', true),
                    'payment_status' => get_post_meta($post->ID, '_wbbm_payment_status', true) ?: 'pending',
                    'payment_method' => get_post_meta($post->ID, '_wbbm_payment_method', true) ?: 'offline',
                    'booking_date'   => get_post_meta($post->ID, '_wbbm_booking_date', true),
                );

                /*
                 * A return trip is one booking on two buses, so the group's
                 * money comes from the whole-booking meta rather than from
                 * this one post -- _wbbm_total_price is only ever the LEG's
                 * total, and on a two-leg booking it would understate the row
                 * by exactly the other leg. Bookings written before return
                 * trips could be booked together have no whole-booking meta
                 * and correctly keep using their single leg's figures.
                 */
                $booking_total = get_post_meta($post->ID, '_wbbm_booking_total', true);
                if ('' !== $booking_total) {
                    $groups[$order_id]['total_price'] = (float) $booking_total;
                    $groups[$order_id]['tax_amount'] = (float) get_post_meta($post->ID, '_wbbm_booking_tax', true);
                }
            }

            /*
             * Bucket the group's posts by leg so the row can name every bus
             * on the booking. Seats and legs are indistinguishable through
             * _wbbm_order_id alone -- both are just more posts under one id.
             */
            $leg_index = (int) get_post_meta($post->ID, '_wbbm_leg_index', true);
            if (!isset($groups[$order_id]['legs'][$leg_index])) {
                $groups[$order_id]['legs'][$leg_index] = array(
                    'bus_id'         => (int) get_post_meta($post->ID, '_wbbm_bus_id', true),
                    'journey_date'   => get_post_meta($post->ID, '_wbbm_journey_date', true),
                    'boarding_point' => get_post_meta($post->ID, '_wbbm_boarding_point', true),
                    'droping_point'  => get_post_meta($post->ID, '_wbbm_droping_point', true),
                    'seat_count'     => 0,
                );
            }

            $groups[$order_id]['legs'][$leg_index]['seat_count']++;
            $groups[$order_id]['seat_count']++;
        }

        foreach ($groups as &$group) {
            ksort($group['legs']);
            $group['legs'] = array_values($group['legs']);
        }
        unset($group);

        return array_values($groups);
    }

    /** How a booking was paid, for the Payment column. */
    /**
     * Delegates to the shared helper in inc/wbbm_booking_status.php -- the
     * Passenger List shows the same badge, and one mapping keeps the two
     * screens from drifting apart as gateways are added.
     */
    private function payment_method_metadata($method)
    {
        return wbbm_payment_method_meta($method);
    }

    private function payment_status_metadata($status)
    {
        $map = array(
            'pending'   => array('label' => __('Pending', 'bus-booking-manager'), 'class' => 'status-pending'),
            'confirmed' => array('label' => __('Confirmed', 'bus-booking-manager'), 'class' => 'status-confirmed'),
            'cancelled' => array('label' => __('Cancelled', 'bus-booking-manager'), 'class' => 'status-cancelled'),
        );

        return isset($map[$status]) ? $map[$status] : array('label' => ucfirst($status), 'class' => 'status-pending');
    }

    public function render_page()
    {
        $groups = $this->get_grouped_bookings();
        $total = count($groups);

        if (isset($_GET['marked_paid']) && '1' === $_GET['marked_paid']) {
            echo '<div class="notice notice-success is-dismissible" style="margin: 20px 20px 0 0;"><p>' . esc_html__('Booking marked as paid.', 'bus-booking-manager') . '</p></div>';
        }
        ?>
        <div class="wrap wbbm-list-wrap">
            <div class="wbbm-list-container-fullwidth">
                <div class="wbbm-list-header">
                    <div class="header-left">
                        <div class="brand-logo">
                            <span class="dashicons dashicons-money-alt"></span>
                        </div>
                        <div class="header-title-area">
                            <h2>
                                <?php esc_html_e('Booking list', 'bus-booking-manager'); ?>
                                <span class="list-count-badge"><?php echo esc_html(number_format_i18n($total)); ?></span>
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="wbbm-list-table-card">
                    <div class="wbbm-list-table-scroll">
                        <table class="wbbm-list-modern-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Bus', 'bus-booking-manager'); ?></th>
                                    <th><?php esc_html_e('Passenger', 'bus-booking-manager'); ?></th>
                                    <th><?php esc_html_e('Route', 'bus-booking-manager'); ?></th>
                                    <th><?php esc_html_e('Journey Date', 'bus-booking-manager'); ?></th>
                                    <th><?php esc_html_e('Seats', 'bus-booking-manager'); ?></th>
                                    <th><?php esc_html_e('Total (Tax)', 'bus-booking-manager'); ?></th>
                                    <th><?php esc_html_e('Paid via', 'bus-booking-manager'); ?></th>
                                    <th><?php esc_html_e('Payment', 'bus-booking-manager'); ?></th>
                                    <th><?php esc_html_e('Action', 'bus-booking-manager'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($groups)) : ?>
                                    <tr>
                                        <td colspan="8"><?php esc_html_e('No offline bookings yet.', 'bus-booking-manager'); ?></td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($groups as $group) : ?>
                                        <?php
                                        $status_meta = $this->payment_status_metadata($group['payment_status']);
                                        $mark_paid_url = wp_nonce_url(
                                            add_query_arg(array(
                                                'post_type' => 'wbbm_bus',
                                                'page'      => self::PAGE_SLUG,
                                                'action'    => 'mark_paid',
                                                'order_id'  => $group['order_id'],
                                            ), admin_url('edit.php')),
                                            'mark-paid-order_' . $group['order_id']
                                        );
                                        ?>
                                        <?php $method_meta = $this->payment_method_metadata($group['payment_method']); ?>
                                        <?php
                                        /*
                                         * A return trip is two legs under one booking, so the Bus,
                                         * Route and Date columns list every leg rather than only the
                                         * first -- showing just one of the two buses is what made a
                                         * return booking look like it had lost a bus.
                                         */
                                        $legs = !empty($group['legs']) ? $group['legs'] : array(array(
                                            'bus_id'         => $group['bus_id'],
                                            'journey_date'   => $group['journey_date'],
                                            'boarding_point' => $group['boarding_point'],
                                            'droping_point'  => $group['droping_point'],
                                            'seat_count'     => $group['seat_count'],
                                        ));
                                        $multi_leg = count($legs) > 1;
                                        ?>
                                        <tr>
                                            <td>
                                                <?php foreach ($legs as $i => $leg) : ?>
                                                    <?php $leg_title = $leg['bus_id'] ? get_the_title($leg['bus_id']) : ''; ?>
                                                    <div class="wbbm-leg-line">
                                                        <?php if ($multi_leg) : ?>
                                                            <span class="wbbm-leg-tag"><?php echo esc_html(0 === $i ? __('Outbound', 'bus-booking-manager') : __('Return', 'bus-booking-manager')); ?></span>
                                                        <?php endif; ?>
                                                        <?php echo esc_html($leg_title ?: __('(deleted bus)', 'bus-booking-manager')); ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo esc_html($group['user_name']); ?></strong><br>
                                                <span class="description"><?php echo esc_html($group['user_phone']); ?></span>
                                            </td>
                                            <td>
                                                <?php foreach ($legs as $leg) : ?>
                                                    <div class="wbbm-leg-line"><?php echo esc_html($leg['boarding_point'] . ' → ' . $leg['droping_point']); ?></div>
                                                <?php endforeach; ?>
                                            </td>
                                            <td>
                                                <?php foreach ($legs as $leg) : ?>
                                                    <div class="wbbm-leg-line"><?php echo esc_html($leg['journey_date']); ?></div>
                                                <?php endforeach; ?>
                                            </td>
                                            <td><?php echo esc_html($group['seat_count']); ?></td>
                                            <td>
                                                <?php echo esc_html(number_format_i18n($group['total_price'], 2)); ?>
                                                <?php if ($group['tax_amount'] > 0) : ?>
                                                    <br><span class="description">(<?php esc_html_e('tax', 'bus-booking-manager'); ?> <?php echo esc_html(number_format_i18n($group['tax_amount'], 2)); ?>)</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="wbbm-method-badge <?php echo esc_attr($method_meta['class']); ?>"><?php echo esc_html($method_meta['label']); ?></span></td>
                                            <td><span class="status-badge <?php echo esc_attr($status_meta['class']); ?>"><?php echo esc_html($status_meta['label']); ?></span></td>
                                            <td>
                                                <?php
                                                /*
                                                 * Only an offline booking is settled by hand. A card or
                                                 * WooCommerce booking is marked paid by its gateway, and
                                                 * handle_mark_paid() refuses those anyway -- so no button.
                                                 */
                                                ?>
                                                <?php if ('pending' === $group['payment_status'] && 'offline' === $group['payment_method']) : ?>
                                                    <a href="<?php echo esc_url($mark_paid_url); ?>" class="wbbm-mark-paid">
                                                        <span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
                                                        <?php esc_html_e('Mark as Paid', 'bus-booking-manager'); ?>
                                                    </a>
                                                <?php elseif ('woocommerce' === $group['payment_method'] && function_exists('wc_get_order') && wc_get_order($group['order_id'])) : ?>
                                                    <a href="<?php echo esc_url(get_edit_post_link($group['order_id'])); ?>" class="wbbm-view-order">
                                                        <?php esc_html_e('View order', 'bus-booking-manager'); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

new BusOfflineBookingListPageClass();
