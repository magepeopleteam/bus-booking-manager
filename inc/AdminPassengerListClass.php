<?php
if (!defined('ABSPATH')) exit;  // if direct access

class AdminPassengerListClass
{
    /** The instance booted at load time, for hub delegation. */
    private static $instance = null;

    public static function instance()
    {
        return self::$instance ? self::$instance : ( self::$instance = new self() );
    }

    public function __construct() {}

    /*
     * Two parts of this screen belong to the PRO plugin and are simply not
     * rendered without it -- no disabled controls, no upsell. Both are cheap
     * existence checks rather than an is_plugin_active() call, so they stay
     * correct if PRO is ever renamed or loaded differently.
     */

    /** Passenger export: CSV, A4 PDF manifest and thermal receipt. */
    protected function export_available()
    {
        return class_exists('WBBM_Pro_Passenger_Export');
    }

    /**
     * Passenger filtering is a PRO feature. AdminPurchaseTicketClass is used as
     * the probe simply because it is the class PRO always loads on this screen.
     */
    protected function filters_available()
    {
        return class_exists('AdminPurchaseTicketClass');
    }

    /** The View Ticket screen a row's ticket-PDF link points at. */
    protected function ticket_screen_available()
    {
        return function_exists('wbbm_gen_ticket');
    }

    /** Styles for this screen and its hub tab. */
    public function enqueue_assets()
    {
        $on_screen = class_exists('WBBM_Admin_Hub')
            ? WBBM_Admin_Hub::is_module_screen('passenger_list', 'wbbm-bookings', 'passengers', true)
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Screen check only.
            : (!empty($_GET['page']) && 'passenger_list' === sanitize_key(wp_unslash($_GET['page'])));

        if (!$on_screen) {
            return;
        }

        /*
         * This screen renders inside .wrap.wbbm-list-wrap, exactly like the
         * Bus/Stop/Type list screens, and leans on the same design system for
         * its header, table, buttons, badges, filters and pagination. While
         * the page lived in PRO those names were supplied by PRO's
         * admin_style.css, which is loaded across the whole admin -- so with
         * PRO switched off the screen lost its styling. Loading this plugin's
         * own list stylesheet supplies all of it, and because that sheet is
         * scoped to .wrap.wbbm-list-wrap it also wins over PRO's generic
         * copies, so the screen looks the same whether PRO is active or not.
         */
        $sheets = array(
            'wbbm-list-css'    => 'assets/admin/wbbm-list-tables.css',
            'wbbm-admin-pages' => 'assets/admin/wbbm-bookings-pages.css',
        );

        foreach ($sheets as $handle => $rel) {
            $file = WBTM_PLUGIN_DIR . $rel;
            wp_enqueue_style(
                $handle,
                WBTM_PLUGIN_URL . $rel,
                array('dashicons'),
                file_exists($file) ? filemtime($file) : '1.0.0'
            );
        }
    }

    function wbbm_passenger_list_menu()
    {
        add_submenu_page('edit.php?post_type=wbbm_bus', __('Passenger List', 'bus-booking-manager'), __('Passenger List', 'bus-booking-manager'), 'manage_options', 'passenger_list', array($this, 'wbbm_passenger_list'));
    }


    function wbbm_passenger_list()
    {
        global $wpdb;
        $class_name = '';
        $seat_booked_on_status = function_exists('wbbm_seat_booked_on_status') ? wbbm_seat_booked_on_status() : '1,2';

        // Pagination Data
        $limit = 50;
        $current_page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $offset = ($current_page * $limit) - $limit;

        // Restore
        if (isset($_GET['req_type']) && $_GET['req_type'] == 'restore') {
            check_admin_referer('wbbm_booking_action');
            $booking_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
            $order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
            if (!$booking_id || get_post_type($booking_id) !== 'wbbm_booking') {
                wp_die(esc_html__('Invalid booking.', 'bus-booking-manager'));
            }
            $order = wc_get_order($order_id);
            $woo_status = $order ? $order->get_meta('_wbbm_status', true) : '';
            update_post_meta($booking_id, '_wbbm_status', $woo_status);
            wp_safe_redirect(admin_url('edit.php?post_type=wbbm_bus&page=passenger_list'));
            exit;
        }

        // Delete row
        if (isset($_GET['req_type']) && $_GET['req_type'] == 'delete' && isset($_GET['id'])) {
            check_admin_referer('wbbm_booking_action');
            $booking_id = absint($_GET['id']);
            if (!$booking_id || get_post_type($booking_id) !== 'wbbm_booking') {
                wp_die(esc_html__('Invalid booking.', 'bus-booking-manager'));
            }
            update_post_meta($booking_id, '_wbbm_status', 99);
            wp_safe_redirect(admin_url('edit.php?post_type=wbbm_bus&page=passenger_list'));
            exit;
        }

        // Delete Permanently
        if (isset($_GET['req_type']) && $_GET['req_type'] == 'delete_permanently' && isset($_GET['id'])) {
            check_admin_referer('wbbm_booking_action');
            $booking_id = absint($_GET['id']);
            if (!$booking_id || get_post_type($booking_id) !== 'wbbm_booking') {
                wp_die(esc_html__('Invalid booking.', 'bus-booking-manager'));
            }
            wp_delete_post($booking_id, true);
            wp_safe_redirect(admin_url('edit.php?post_type=wbbm_bus&page=passenger_list&req_type=removed_list'));
            exit;
        }

        $meta_query = array(
            array(
                'key' => '_wbbm_status',
                'value' => 99,
                'compare' => '!='
            )
        );

        $bus_id_raw = (isset($_GET['bus_id'])) ? $_GET['bus_id'] : '';
        $bus_id = $bus_id_raw;
        if (strpos($bus_id_raw, '-') !== false) {
            $bus_id_arr = explode('-', $bus_id_raw);
            $bus_id = $bus_id_arr[0];
        }
        $mage_meta = get_post_custom($bus_id);

        if ($bus_id) {
            $meta_query[] = array(
                'key' => '_wbbm_bus_id',
                'value' => $bus_id,
                'compare' => '='
            );
        }

        $j_date = (isset($_GET['j_date']) ? strip_tags($_GET['j_date']) : null);
        if ($j_date) {
            $meta_query[] = array(
                'key' => '_wbbm_journey_date',
                'value' => $j_date,
                'compare' => '='
            );
        }

        $b_date = (isset($_GET['b_date']) ? strip_tags($_GET['b_date']) : null);
        if ($b_date) {
            $meta_query[] = array(
                'key' => '_wbbm_booking_date',
                'value' => $b_date,
                'compare' => 'LIKE'
            );
        }

        $user_name = (isset($_GET['user_name']) ? strip_tags($_GET['user_name']) : null);
        if ($user_name) {
            $meta_query[] = array(
                'key' => '_wbbm_user_name',
                'value' => $user_name,
                'compare' => 'LIKE'
            );
        }

        $wbbm_reg_email = (isset($_GET['user_email']) ? strip_tags($_GET['user_email']) : null);
        if ($wbbm_reg_email) {
            $meta_query[] = array(
                'key' => '_wbbm_user_email',
                'value' => $wbbm_reg_email,
                'compare' => '='
            );
        }

        $wbbm_reg_phone = (isset($_GET['user_phone']) ? strip_tags($_GET['user_phone']) : null);
        if ($wbbm_reg_phone) {
            $meta_query[] = array(
                'key' => '_wbbm_user_phone',
                'value' => $wbbm_reg_phone,
                'compare' => '='
            );
        }

        $wbbm_user_gender = (isset($_GET['user_gender']) ? strip_tags($_GET['user_gender']) : null);
        if ($wbbm_user_gender) {
            $meta_query[] = array(
                'key' => '_wbbm_user_gender',
                'value' => $wbbm_user_gender,
                'compare' => '='
            );
        }

        $wbbm_user_dob = (isset($_GET['user_dob']) ? strip_tags($_GET['user_dob']) : null);
        if ($wbbm_user_dob) {
            $meta_query[] = array(
                'key' => '_wbbm_user_dob',
                'value' => $wbbm_user_dob,
                'compare' => '='
            );
        }

        $wbbm_user_address = (isset($_GET['user_address']) ? strip_tags($_GET['user_address']) : null);
        if ($wbbm_user_address) {
            $meta_query[] = array(
                'key' => '_wbbm_user_address',
                'value' => $wbbm_user_address,
                'compare' => 'LIKE'
            );
        }

        $wbbm_user_nationality = (isset($_GET['nationality']) ? strip_tags($_GET['nationality']) : null);
        if ($wbbm_user_nationality) {
            $meta_query[] = array(
                'key' => 'nationality',
                'value' => $wbbm_user_nationality,
                'compare' => '='
            );
        }

        $wbbm_user_flight_arrival_no = (isset($_GET['flight_arrial_no']) ? strip_tags($_GET['flight_arrial_no']) : null);
        if ($wbbm_user_flight_arrival_no) {
            $meta_query[] = array(
                'key' => '_wbbm_flight_arrial_no',
                'value' => $wbbm_user_flight_arrival_no,
                'compare' => '='
            );
        }

        $wbbm_user_flight_departure_no = (isset($_GET['flight_departure_no']) ? strip_tags($_GET['flight_departure_no']) : null);
        if ($wbbm_user_flight_departure_no) {
            $meta_query[] = array(
                'key' => '_wbbm_flight_departure_no',
                'value' => $wbbm_user_flight_departure_no,
                'compare' => '='
            );
        }

        $order_id = (isset($_GET['order_id']) ? strip_tags($_GET['order_id']) : null);
        if ($order_id) {
            $meta_query[] = array(
                'key' => '_wbbm_order_id',
                'value' => $order_id,
                'compare' => '='
            );
        }

        $billing_name = (isset($_GET['billing_name']) ? strip_tags($_GET['billing_name']) : '');
        $billing_email = (isset($_GET['billing_email']) ? strip_tags($_GET['billing_email']) : '');
        $billing_phone = (isset($_GET['billing_phone']) ? strip_tags($_GET['billing_phone']) : '');


        $query_args = array(
            'post_type' => 'wbbm_booking',
            'posts_per_page' => -1,
            'meta_query' => $meta_query,
            'fields' => 'ids',
            'orderby' => 'ID',
            'order' => 'DESC'
        );
        $booking_ids = get_posts($query_args);
        $booking_ids = $this->filter_booking_ids_by_object_post_type($booking_ids, 'wbbm_bus');
        $total_count = count($booking_ids);
        $passger_query = $this->get_passenger_posts_by_ids(array_slice($booking_ids, $offset, $limit));

        // Show Removed List
        if (isset($_GET['req_type']) && ($_GET['req_type'] === 'removed_list')) {
            $query_args['meta_query'] = array(
                array(
                    'key' => '_wbbm_status',
                    'value' => 99,
                    'compare' => '='
                )
            );
            $booking_ids = get_posts($query_args);
            $booking_ids = $this->filter_booking_ids_by_object_post_type($booking_ids, 'wbbm_bus');
            $total_count = count($booking_ids);
            $passger_query = $this->get_passenger_posts_by_ids(array_slice($booking_ids, $offset, $limit));
            $class_name = 'removed-list';
        }

        $filtered_passengers = array();
        foreach ($passger_query as $single) {
            $user_id = get_post_meta($single->ID, 'user_id', true);
            $customer = new WC_Customer($user_id);

            $user_email = $customer->get_email(); // Get account email
            $billing_first_name = $customer->get_billing_first_name();
            $billing_last_name = $customer->get_billing_last_name();
            $billing_phone_u = $customer->get_billing_phone();

            $order = wc_get_order(get_post_meta($single->ID, 'order_id', true));
            if (!empty($order)) {
                foreach ($order->get_items() as $item_id => $item_values) {
                    $single->extra_info = maybe_unserialize(wbbm_get_order_meta($item_id, '_wbbm_passenger_info_additional'));
                }
            }

            $single->billing_email = $user_email;
            $single->billing_name = $billing_first_name . ' ' . $billing_last_name;
            $single->billing_phone = $billing_phone_u;

            // Apply billing filters
            if ($billing_name && (strpos($single->billing_name, $billing_name) === false)) {
                continue;
            }
            if ($billing_email && $single->billing_email != $billing_email) {
                continue;
            }
            if ($billing_phone && $single->billing_phone != $billing_phone) {
                continue;
            }

            // Add meta data as object properties for compatibility
            $meta_fields = array(
                'bus_id',
                'user_id',
                'order_id',
                'user_type',
                'per_adult_price',
                'per_child_price',
                'per_infant_price',
                'bus_start',
                'booking_date',
                'journey_date',
                'boarding_point',
                'droping_point',
                'pickpoint',
                'status',
                'ticket_status',
                'seat',
                'user_name',
                'user_email',
                'user_phone',
                'user_gender',
                'user_dob',
                'user_address',
                'nationality',
                'flight_arrial_no',
                'flight_departure_no'
            );
            $prefix = '_wbbm_';
            foreach ($meta_fields as $field) {
                $single->$field = get_post_meta($single->ID, $prefix . $field, true);
            }
            $single->booking_id = $single->ID; // Assuming post ID is booking_id

            $filtered_passengers[] = $single;
        }

        $passger_query = $filtered_passengers;

?>


        <div class="wrap wbbm-list-wrap mage-custom-filter-area-outer">
            <div class="wbbm-list-container-fullwidth">
                <!-- Header Section -->
                <div class="wbbm-list-header">
                    <div class="header-left">
                        <div class="brand-logo">
                            <span class="dashicons dashicons-groups"></span>
                        </div>
                        <div class="header-title-area">
                            <h2>
                                <?php _e('Passenger List', 'bus-booking-manager'); ?>
                                <?php
                                if (isset($_GET['req_type']) && $_GET['req_type'] == 'removed_list') {
                                    echo ' (' . __('Removed', 'bus-booking-manager') . ')';
                                }
                                ?>
                            </h2>
                        </div>
                    </div>
                    <div class="header-right">
                        <?php // The legacy j_date mirror stays; the filter script writes to it. ?>
                        <input type="hidden" id="ja_date" value="<?php echo esc_attr($j_date ? $j_date : ''); ?>">
                        <?php if ($this->export_available()) : ?>
                            <button type="button" class="btn btn-outline" data-wbbm-export-open>
                                <span class="dashicons dashicons-download" style="margin-top:2px;"></span> <?php _e('Export', 'bus-booking-manager'); ?>
                            </button>
                        <?php else : ?>
                            <?php /* Shown so the feature is discoverable, but inert until PRO is active. */ ?>
                            <button type="button" class="btn btn-outline wbbm-pro-locked" disabled aria-disabled="true"
                                    title="<?php esc_attr_e('Available in the PRO version', 'bus-booking-manager'); ?>">
                                <span class="dashicons dashicons-download" style="margin-top:2px;"></span> <?php _e('Export', 'bus-booking-manager'); ?>
                                <em class="wbbm-pro-badge"><?php esc_html_e('PRO', 'bus-booking-manager'); ?></em>
                            </button>
                        <?php endif; ?>
                        <?php if ($this->filters_available()) : ?>
                            <button type="button" class="btn btn-primary btn-filter-toggle">
                                <span class="dashicons dashicons-filter"></span> <?php _e('Show Filters', 'bus-booking-manager'); ?>
                            </button>
                        <?php else : ?>
                            <button type="button" class="btn btn-primary wbbm-pro-locked" disabled aria-disabled="true"
                                    title="<?php esc_attr_e('Available in the PRO version', 'bus-booking-manager'); ?>">
                                <span class="dashicons dashicons-filter"></span> <?php _e('Show Filters', 'bus-booking-manager'); ?>
                                <em class="wbbm-pro-badge"><?php esc_html_e('PRO', 'bus-booking-manager'); ?></em>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
                $is_filter_empty = (empty($bus_id) && empty($j_date) && empty($b_date) && empty($order_id) && empty($_GET['user_name']) && empty($_GET['user_email']) && empty($_GET['user_phone']));
                ?>
                <?php
                /*
                 * The filter form is a PRO feature and its Show Filters button
                 * is disabled above without PRO, so the form is left out too --
                 * otherwise it could still be driven straight from the URL.
                 * The card itself is then only worth rendering for the removed
                 * list, whose Go Back link lives in it; without this the free
                 * plugin drew an empty card with a stray Go Back button on the
                 * normal view.
                 */
                $wbbm_show_filter_card = ('removed-list' === $class_name) || $this->filters_available();
                ?>
                <?php if ($wbbm_show_filter_card) : ?>
                <div class="wbbm-list-filters-card mage-custom-filter-area <?php echo $is_filter_empty ? 'collapsed' : ''; ?>">
                    <?php if ($class_name != 'removed-list') : ?>
                        <form action="<?php echo get_admin_url(); ?>edit.php?post_type=wbbm_bus&page=passenger_list" method="get">
                            <input type="hidden" name="post_type" value="wbbm_bus">
                            <input type="hidden" name="page" value="passenger_list">

                            <div class="wbbm-list-filter-wrapper">
                                <div class="sh-filter-grid">
                                    <div class="filter-group">
                                        <label><?php _e('Bus:', 'bus-booking-manager'); ?></label>
                                        <select name="bus_id" class="form-control">
                                            <option value=""><?php _e('All Buses', 'bus-booking-manager'); ?></option>
                                            <?php
                                            $args = array(
                                                'post_type' => 'wbbm_bus',
                                                'posts_per_page' => -1
                                            );
                                            $loop = new WP_Query($args);
                                            while ($loop->have_posts()) {
                                                $loop->the_post();
                                                $start = get_post_meta(get_the_id(), 'wbbm_bus_no', true);
                                                $busit = get_the_id();
                                            ?>
                                                <option value="<?php echo $busit; ?>" <?php if ($busit == $bus_id) {
                                                                                            echo 'selected';
                                                                                        } ?>><?php the_title(); ?> - <?php echo $start; ?></option>
                                            <?php
                                            }
                                            wp_reset_postdata();
                                            ?>
                                        </select>
                                    </div>

                                    <div class="filter-group">
                                        <label><?php _e('Journey Date:', 'bus-booking-manager'); ?></label>
                                        <input type="date" id="journey_date" name="j_date" value="<?php echo $j_date; ?>" class="form-control">
                                    </div>

                                    <div class="filter-group">
                                        <label><?php _e('Booking Date:', 'bus-booking-manager'); ?></label>
                                        <input type="date" id="booking_date" name="b_date" value="<?php echo $b_date; ?>" class="form-control">
                                    </div>

                                    <div class="filter-group">
                                        <label><?php _e('Billing Name:', 'bus-booking-manager'); ?></label>
                                        <input type="text" name="billing_name" value="<?php echo $billing_name; ?>" class="form-control" placeholder="<?php _e('Search Name...', 'bus-booking-manager'); ?>">
                                    </div>

                                    <div class="filter-group">
                                        <label><?php _e('Billing Email:', 'bus-booking-manager'); ?></label>
                                        <input type="text" name="billing_email" value="<?php echo $billing_email; ?>" class="form-control" placeholder="<?php _e('Search Email...', 'bus-booking-manager'); ?>">
                                    </div>
                                    <div class="filter-group">
                                        <label><?php _e('Order ID:', 'bus-booking-manager'); ?></label>
                                        <input type="text" name="order_id" value="<?php echo $order_id; ?>" class="form-control" placeholder="<?php _e('Order ID', 'bus-booking-manager'); ?>">
                                    </div>

                                </div>

                                <?php
                                $has_extra_filters = false;
                                if ($bus_id) {
                                    $wbbm_full_name = array_key_exists('wbbm_full_name', $mage_meta) ? strip_tags($mage_meta['wbbm_full_name'][0]) : false;
                                    $wbbm_reg_email = array_key_exists('wbbm_reg_email', $mage_meta) ? strip_tags($mage_meta['wbbm_reg_email'][0]) : false;
                                    $wbbm_reg_phone = array_key_exists('wbbm_reg_phone', $mage_meta) ? strip_tags($mage_meta['wbbm_reg_phone'][0]) : false;
                                    $wbbm_user_gender = array_key_exists('wbbm_user_gender', $mage_meta) ? strip_tags($mage_meta['wbbm_user_gender'][0]) : false;
                                    $wbbm_reg_address = array_key_exists('wbbm_reg_address', $mage_meta) ? strip_tags($mage_meta['wbbm_reg_address'][0]) : false;
                                    $wbbm_user_dob = array_key_exists('wbbm_user_dob', $mage_meta) ? strip_tags($mage_meta['wbbm_user_dob'][0]) : false;
                                    $wbbm_user_nationality = array_key_exists('wbbm_user_nationality', $mage_meta) ? strip_tags($mage_meta['wbbm_user_nationality'][0]) : false;
                                    $wbbm_user_flight_arrival_no = array_key_exists('wbbm_user_flight_arrival_no', $mage_meta) ? strip_tags($mage_meta['wbbm_user_flight_arrival_no'][0]) : false;
                                    $wbbm_user_flight_departure_no = array_key_exists('wbbm_user_flight_departure_no', $mage_meta) ? strip_tags($mage_meta['wbbm_user_flight_departure_no'][0]) : false;
                                    $custom_field = unserialize(get_post_meta($bus_id, 'wbbm_attendee_reg_form', true));

                                    if ($wbbm_full_name || $wbbm_reg_email || $wbbm_reg_phone || $wbbm_user_gender || $wbbm_reg_address || $wbbm_user_dob || $wbbm_user_nationality || $wbbm_user_flight_arrival_no || $wbbm_user_flight_departure_no || $custom_field) {
                                        $has_extra_filters = true;
                                    }
                                }
                                ?>
                                <div class="extra-filters-wrapper" <?php echo !$has_extra_filters ? 'style="display:none;"' : ''; ?>>
                                    <div class="extra-filters-toggle-wrap">
                                        <button type="button" class="btn-extra-filters-toggle">
                                            <span class="dashicons dashicons-filter"></span>
                                            <?php _e('Extra Passenger Filters', 'bus-booking-manager'); ?>
                                            <span class="dashicons dashicons-arrow-down-alt2 toggle-icon"></span>
                                        </button>
                                    </div>

                                    <div class="sh-filter-grid extra-filters-grid extra_field_for_single_bus" style="display: grid; margin-top: 15px; padding-top: 15px; border-top: 1px dashed #e2e8f0;">
                                        <?php if ($bus_id && $has_extra_filters) : ?>
                                            <?php if ($wbbm_full_name) : ?>
                                                <div class="filter-group">
                                                    <label><?php _e('Passenger Name', 'bus-booking-manager'); ?></label>
                                                    <input type="text" name="user_name" value="<?php echo esc_attr(isset($_GET['user_name']) ? wp_unslash($_GET['user_name']) : '') ?>" class="form-control" placeholder="<?php _e('Search...', 'bus-booking-manager'); ?>">
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($wbbm_reg_email) : ?>
                                                <div class="filter-group">
                                                    <label><?php _e('Passenger Email', 'bus-booking-manager'); ?></label>
                                                    <input type="text" name="user_email" value="<?php echo esc_attr(isset($_GET['user_email']) ? wp_unslash($_GET['user_email']) : '') ?>" class="form-control" placeholder="<?php _e('Search...', 'bus-booking-manager'); ?>">
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($wbbm_reg_phone) : ?>
                                                <div class="filter-group">
                                                    <label><?php _e('Passenger Phone', 'bus-booking-manager'); ?></label>
                                                    <input type="text" name="user_phone" value="<?php echo esc_attr(isset($_GET['user_phone']) ? wp_unslash($_GET['user_phone']) : '') ?>" class="form-control" placeholder="<?php _e('Search...', 'bus-booking-manager'); ?>">
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($wbbm_user_gender) : ?>
                                                <div class="filter-group">
                                                    <label><?php _e('Gender', 'bus-booking-manager'); ?></label>
                                                    <input type="text" name="user_gender" value="<?php echo esc_attr(isset($_GET['user_gender']) ? wp_unslash($_GET['user_gender']) : '') ?>" class="form-control" placeholder="<?php _e('Search...', 'bus-booking-manager'); ?>">
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($wbbm_reg_address) : ?>
                                                <div class="filter-group">
                                                    <label><?php _e('Address', 'bus-booking-manager'); ?></label>
                                                    <input type="text" name="user_address" value="<?php echo esc_attr(isset($_GET['user_address']) ? wp_unslash($_GET['user_address']) : '') ?>" class="form-control" placeholder="<?php _e('Search...', 'bus-booking-manager'); ?>">
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($wbbm_user_dob) : ?>
                                                <div class="filter-group">
                                                    <label><?php _e('DOB', 'bus-booking-manager'); ?></label>
                                                    <input type="date" id="user_dob" name="user_dob" value="<?php echo esc_attr(isset($_GET['user_dob']) ? wp_unslash($_GET['user_dob']) : '') ?>" class="form-control">
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($wbbm_user_nationality) : ?>
                                                <div class="filter-group">
                                                    <label><?php _e('Nationality', 'bus-booking-manager'); ?></label>
                                                    <input type="text" name="nationality" value="<?php echo esc_attr(isset($_GET['nationality']) ? wp_unslash($_GET['nationality']) : '') ?>" class="form-control" placeholder="<?php _e('Search...', 'bus-booking-manager'); ?>">
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($wbbm_user_flight_arrival_no) : ?>
                                                <div class="filter-group">
                                                    <label><?php _e('Flight Arrival', 'bus-booking-manager'); ?></label>
                                                    <input type="text" name="flight_arrial_no" value="<?php echo esc_attr(isset($_GET['flight_arrial_no']) ? wp_unslash($_GET['flight_arrial_no']) : '') ?>" class="form-control" placeholder="<?php _e('Search...', 'bus-booking-manager'); ?>">
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($wbbm_user_flight_departure_no) : ?>
                                                <div class="filter-group">
                                                    <label><?php _e('Flight Departure', 'bus-booking-manager'); ?></label>
                                                    <input type="text" name="flight_departure_no" value="<?php echo esc_attr(isset($_GET['flight_departure_no']) ? wp_unslash($_GET['flight_departure_no']) : '') ?>" class="form-control" placeholder="<?php _e('Search...', 'bus-booking-manager'); ?>">
                                                </div>
                                            <?php endif; ?>

                                            <?php
                                            if ($custom_field) {
                                                foreach ($custom_field as $epv) {
                                                    if (isset($epv['field_id']) && ($epv['filed_type'] == 'text')) {
                                            ?>
                                                        <div class="filter-group">
                                                            <label><?php echo $epv['field_label'] ?></label>
                                                            <input type="text" name="<?php echo esc_attr($epv['field_id']); ?>" value="<?php echo esc_attr(isset($_GET[$epv['field_id']]) ? wp_unslash($_GET[$epv['field_id']]) : '') ?>" class="form-control" placeholder="<?php echo esc_attr($epv['field_label']); ?>...">
                                                        </div>
                                            <?php
                                                    }
                                                }
                                            }
                                            ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="sh-filter-actions">
                                    <div class="sh-filter-left">
                                        <a href="<?php echo get_admin_url(); ?>edit.php?post_type=wbbm_bus&page=passenger_list&req_type=removed_list" class="removed-passengers-link">
                                            <span class="dashicons dashicons-trash"></span>
                                            <?php _e('View Removed Passengers', 'bus-booking-manager'); ?>
                                        </a>
                                    </div>
                                    <div class="sh-filter-right">
                                        <a class="btn btn-outline" href="<?php echo admin_url() . 'edit.php?post_type=wbbm_bus&page=passenger_list'; ?>"><?php _e('Reset Filters', 'bus-booking-manager'); ?></a>
                                        <button type="submit" class="btn btn-primary"><?php _e('Apply Filters', 'bus-booking-manager'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php else : ?>
                        <div class="wbbm-admin-header-actions" style="margin-bottom: 20px;">
                            <a href="<?php echo get_admin_url(); ?>edit.php?post_type=wbbm_bus&page=passenger_list" class="wbbm_download_btn" style="padding: 10px 20px; display: inline-flex; align-items: center; gap: 8px;">
                                <span class="dashicons dashicons-undo"></span>
                                <?php _e('Go Back', 'bus-booking-manager'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; // $wbbm_show_filter_card ?>
            </div> <!-- End wbbm-admin-card -->
            <?php
            $passenger_list_custom_field = is_array(get_option('wbbm_passenger_list_field_sec')) ? maybe_unserialize(get_option('wbbm_passenger_list_field_sec')) : array();
            $wc_custom_checkout_fields = !empty($passenger_list_custom_field['custom_fields']) ? $passenger_list_custom_field['custom_fields'] : "";
            $wc_custom_checkout_fields = explode(",", $wc_custom_checkout_fields);

            $default_billing_fields = !empty($passenger_list_custom_field['default_billing_fields_setting']) ? $passenger_list_custom_field['default_billing_fields_setting'] : array();




            ?>
            <!-- Table Content -->
            <div class="wbbm-list-table-card">
                <table class="wbbm-list-modern-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class="col-id"><?php _e('ID & Order', 'bus-booking-manager'); ?></th>
                            <th class="col-bus"><?php _e('Bus', 'bus-booking-manager'); ?></th>
                            <th class="col-customer"><?php _e('Customer', 'bus-booking-manager'); ?></th>
                            <th class="col-journey"><?php _e('Journey Details', 'bus-booking-manager'); ?></th>
                            <th class="col-route"><?php _e('Route', 'bus-booking-manager'); ?></th>
                            <th class="col-passengers"><?php _e('Seats & Total', 'bus-booking-manager'); ?></th>
                            <?php if (isset($_GET['bus_id']) && $_GET['bus_id'] != '') : ?>
                                <th class="col-extra-info"><?php _e('Extra Info', 'bus-booking-manager'); ?></th>
                            <?php endif; ?>
                            <th class="col-status"><?php _e('Status', 'bus-booking-manager'); ?></th>
                            <th class="col-action" style="text-align: right;"><?php _e('Action', 'bus-booking-manager'); ?></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $i = 0;
                        $t = 0;
                        $sl = $offset + 1;
                        $passenger_index = 0;
                        $prev_order_id = '';
                        foreach ($passger_query as $_passger) {

                            $order_id = $_passger->order_id;
                            $passenger_index = ($prev_order_id == $order_id ? ++$passenger_index : 0); // if a order has multiple seats

                            $custom_field = unserialize(get_post_meta($bus_id, 'wbbm_attendee_reg_form', true));

                            $show_passenger = true;
                            if ($bus_id && $custom_field) {
                                $has_search = false;
                                $match_found = false;

                                foreach ($custom_field as $epv) {
                                    if (isset($epv['field_id']) && ($epv['filed_type'] == 'text')) {
                                        $field_id = $epv['field_id'];
                                        $field_search_value = isset($_GET[$field_id]) ? $_GET[$field_id] : '';

                                        if ($field_search_value !== '') {
                                            $has_search = true;
                                            if ($_passger->extra_info && is_array($_passger->extra_info)) {
                                                $extra_info_json = json_encode($_passger->extra_info);
                                                if (strpos($extra_info_json, $field_search_value) !== false) {
                                                    $match_found = true;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }

                                if ($has_search && !$match_found) {
                                    $show_passenger = false;
                                }
                            }

                            if ($show_passenger) {
                                // Map additional properties for extra info display
                                $_passger->user_name = get_post_meta($_passger->ID, '_wbbm_user_name', true);
                                $_passger->user_email = get_post_meta($_passger->ID, '_wbbm_user_email', true);
                                $_passger->user_phone = get_post_meta($_passger->ID, '_wbbm_user_phone', true);
                                $_passger->user_gender = get_post_meta($_passger->ID, '_wbbm_user_gender', true);
                                $_passger->user_dob = get_post_meta($_passger->ID, '_wbbm_user_dob', true);
                                $_passger->user_address = get_post_meta($_passger->ID, '_wbbm_user_address', true);
                                $_passger->nationality = get_post_meta($_passger->ID, '_wbbm_nationality', true);
                                $_passger->flight_arrial_no = get_post_meta($_passger->ID, '_wbbm_flight_arrial_no', true);
                                $_passger->flight_departure_no = get_post_meta($_passger->ID, '_wbbm_flight_departure_no', true);

                                $this->passenger_list($_passger, $class_name, $sl, $default_billing_fields, $wc_custom_checkout_fields, $bus_id, $mage_meta, $t, $passenger_index);
                                $sl++;
                            }

                            $prev_order_id = $order_id; // store current order id for next iteration
                        }
                        ?>
                    </tbody>
                </table>

                <!-- Pagination Area -->
                <div class="wbbm-list-pagination-area">
                    <div class="pagination-info">
                        <?php
                        $total = $total_count;
                        $pages = ceil($total / $limit);
                        printf(__('Showing %d - %d of %d items', 'bus-booking-manager'), max(1, $offset + 1), min($offset + $limit, $total), $total);
                        ?>
                    </div>
                    <div class="pagination-controls">
                        <?php
                        // Using the existing wbbm_pagination function but wrapped in our container
                        echo ($total > $limit) ? wbbm_pagination($current_page, $pages) : '';
                        ?>
                    </div>
                </div>
            </div>
            <?php //echo $t;
            ?>

            <?php
            /*
             * Export dialog. The current filters are carried as hidden fields
             * so the file always matches what the screen is showing, and the
             * form posts to admin-post.php with its own nonce.
             *
             * Export is a PRO feature, so the whole dialog -- including the
             * filter snapshot it would carry -- is skipped when PRO is not
             * active. The markup below references WBBM_Pro_Passenger_Export
             * directly and would fatal without it.
             */
            if ($this->export_available()) :
            $export_filters = array(
                'bus_id'              => isset($_GET['bus_id']) ? sanitize_text_field(wp_unslash($_GET['bus_id'])) : '',
                'j_date'              => $j_date,
                'b_date'              => $b_date,
                'order_id'            => $order_id,
                'user_name'           => isset($_GET['user_name']) ? sanitize_text_field(wp_unslash($_GET['user_name'])) : '',
                'user_email'          => isset($_GET['user_email']) ? sanitize_text_field(wp_unslash($_GET['user_email'])) : '',
                'user_phone'          => isset($_GET['user_phone']) ? sanitize_text_field(wp_unslash($_GET['user_phone'])) : '',
                'user_gender'         => isset($_GET['user_gender']) ? sanitize_text_field(wp_unslash($_GET['user_gender'])) : '',
                'user_dob'            => isset($_GET['user_dob']) ? sanitize_text_field(wp_unslash($_GET['user_dob'])) : '',
                'user_address'        => isset($_GET['user_address']) ? sanitize_text_field(wp_unslash($_GET['user_address'])) : '',
                'flight_arrial_no'    => isset($_GET['flight_arrial_no']) ? sanitize_text_field(wp_unslash($_GET['flight_arrial_no'])) : '',
                'flight_departure_no' => isset($_GET['flight_departure_no']) ? sanitize_text_field(wp_unslash($_GET['flight_departure_no'])) : '',
            );
            $export_active = array_filter($export_filters, static function ($v) { return '' !== $v && null !== $v; });
            ?>
            <div class="wbbm-export-modal" data-wbbm-export-modal hidden>
                <div class="wbbm-export-backdrop" data-wbbm-export-close></div>
                <div class="wbbm-export-dialog" role="dialog" aria-modal="true" aria-labelledby="wbbm-export-title">
                    <header>
                        <div>
                            <h2 id="wbbm-export-title"><?php esc_html_e('Export passengers', 'bus-booking-manager'); ?></h2>
                            <p><?php
                                if ($export_active) {
                                    /* translators: %d: number of active filters. */
                                    echo esc_html(sprintf(_n('Using the %d filter currently applied.', 'Using the %d filters currently applied.', count($export_active), 'bus-booking-manager'), count($export_active)));
                                } else {
                                    esc_html_e('No filters applied — this exports every passenger.', 'bus-booking-manager');
                                }
                            ?></p>
                        </div>
                        <button type="button" class="wbbm-export-close" data-wbbm-export-close aria-label="<?php esc_attr_e('Close', 'bus-booking-manager'); ?>">
                            <span class="dashicons dashicons-no-alt"></span>
                        </button>
                    </header>

                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-wbbm-native="1">
                        <input type="hidden" name="action" value="<?php echo esc_attr(WBBM_Pro_Passenger_Export::ACTION); ?>">
                        <?php wp_nonce_field(WBBM_Pro_Passenger_Export::NONCE); ?>
                        <?php foreach ($export_filters as $key => $value) :
                            // bus_id and j_date are chosen in the dialog itself.
                            if ('bus_id' === $key || 'j_date' === $key) {
                                continue;
                            } ?>
                            <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>">
                        <?php endforeach; ?>

                        <?php
                        $export_buses = get_posts(array(
                            'post_type'      => 'wbbm_bus',
                            'post_status'    => 'publish',
                            'posts_per_page' => -1,
                            'orderby'        => 'title',
                            'order'          => 'ASC',
                        ));
                        $export_bus_selected = isset($_GET['bus_id']) ? sanitize_text_field(wp_unslash($_GET['bus_id'])) : '';
                        ?>
                        <div class="wbbm-export-scope">
                            <div class="wbbm-export-field">
                                <label for="wbbm-export-bus"><?php esc_html_e('Service', 'bus-booking-manager'); ?></label>
                                <select id="wbbm-export-bus" name="bus_id">
                                    <option value=""><?php esc_html_e('All services', 'bus-booking-manager'); ?></option>
                                    <?php foreach ($export_buses as $export_bus) : ?>
                                        <option value="<?php echo esc_attr($export_bus->ID); ?>" <?php selected($export_bus_selected, (string) $export_bus->ID); ?>>
                                            <?php echo esc_html($export_bus->post_title); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="wbbm-export-field">
                                <label for="wbbm-export-range"><?php esc_html_e('Journey date', 'bus-booking-manager'); ?></label>
                                <select id="wbbm-export-range" name="range" data-wbbm-export-range>
                                    <?php foreach (WBBM_Pro_Passenger_Export::ranges() as $range_key => $range_label) : ?>
                                        <option value="<?php echo esc_attr($range_key); ?>"><?php echo esc_html($range_label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <?php // Revealed only when Custom range is chosen. ?>
                            <div class="wbbm-export-field wbbm-export-custom" data-wbbm-export-custom hidden>
                                <label for="wbbm-export-from"><?php esc_html_e('From', 'bus-booking-manager'); ?></label>
                                <input type="date" id="wbbm-export-from" name="range_from">
                            </div>
                            <div class="wbbm-export-field wbbm-export-custom" data-wbbm-export-custom hidden>
                                <label for="wbbm-export-to"><?php esc_html_e('To', 'bus-booking-manager'); ?></label>
                                <input type="date" id="wbbm-export-to" name="range_to">
                            </div>
                        </div>

                        <div class="wbbm-export-formats">
                            <?php $first = true; foreach (WBBM_Pro_Passenger_Export::formats() as $key => $format) : ?>
                                <label class="wbbm-export-option">
                                    <input type="radio" name="format" value="<?php echo esc_attr($key); ?>" <?php checked($first); ?>>
                                    <span class="wbbm-export-option-body">
                                        <span class="dashicons <?php echo esc_attr($format['icon']); ?>"></span>
                                        <span class="wbbm-export-option-text">
                                            <strong><?php echo esc_html($format['label']); ?></strong>
                                            <small><?php echo esc_html($format['note']); ?></small>
                                        </span>
                                    </span>
                                </label>
                            <?php $first = false; endforeach; ?>
                        </div>

                        <footer>
                            <button type="button" class="btn btn-outline" data-wbbm-export-close><?php esc_html_e('Cancel', 'bus-booking-manager'); ?></button>
                            <button type="submit" class="btn btn-primary">
                                <span class="dashicons dashicons-download"></span><?php esc_html_e('Download', 'bus-booking-manager'); ?>
                            </button>
                        </footer>
                    </form>
                </div>
            </div>
            <?php endif; // export_available ?>

        </div>
        <script>
            function deleteConfirm() {
                if (confirm('Do you want to remove it?')) {
                    return true;
                } else {
                    return false;
                }
            }

            if (typeof dLoader !== 'function') {
                window.dLoader = function(target) {
                    if (target && target.length) {
                        target.css('opacity', '0.5').css('pointer-events', 'none');
                    }
                };
            }
            if (typeof dLoaderRemove !== 'function') {
                window.dLoaderRemove = function(target) {
                    if (target && target.length) {
                        target.css('opacity', '1').css('pointer-events', 'auto');
                    }
                };
            }

            document.addEventListener('DOMContentLoaded', function() {
                const toggleBtn = document.querySelector('.btn-extra-filters-toggle');
                const extraGrid = document.querySelector('.extra-filters-grid');
                const toggleIcon = document.querySelector('.toggle-icon');
                const extraWrapper = document.querySelector('.extra-filters-wrapper');

                function updateToggleState() {
                    const hasValue = Array.from(extraGrid.querySelectorAll('input')).some(input => input.value !== '');
                    if (hasValue) {
                        extraGrid.style.display = 'grid';
                        toggleIcon.classList.remove('dashicons-arrow-down-alt2');
                        toggleIcon.classList.add('dashicons-arrow-up-alt2');
                    }
                }

                if (toggleBtn && extraGrid) {
                    updateToggleState();

                    toggleBtn.addEventListener('click', function() {
                        const isHidden = extraGrid.style.display === 'none';
                        extraGrid.style.display = isHidden ? 'grid' : 'none';

                        if (isHidden) {
                            toggleIcon.classList.remove('dashicons-arrow-down-alt2');
                            toggleIcon.classList.add('dashicons-arrow-up-alt2');
                        } else {
                            toggleIcon.classList.remove('dashicons-arrow-up-alt2');
                            toggleIcon.classList.add('dashicons-arrow-down-alt2');
                        }
                    });
                }

                // Handle AJAX updates
                const busSelect = document.querySelector('[name="bus_id"]');
                if (busSelect) {
                    busSelect.addEventListener('change', function() {
                        const observer = new MutationObserver(function(mutations) {
                            if (extraGrid.children.length > 0) {
                                extraWrapper.style.display = 'block';
                                extraGrid.style.display = 'grid';
                                toggleIcon.classList.remove('dashicons-arrow-down-alt2');
                                toggleIcon.classList.add('dashicons-arrow-up-alt2');

                                // Also ensure main filter card is expanded if AJAX content comes in
                                if (filterCard.classList.contains('collapsed')) {
                                    filterCard.classList.remove('collapsed');
                                    updateToggleText();
                                }
                            } else {
                                extraWrapper.style.display = 'none';
                            }
                        });

                        observer.observe(extraGrid, {
                            childList: true
                        });
                        setTimeout(() => observer.disconnect(), 2000);
                    });
                }

                // Filter Card Toggle (Main Area)
                const filterToggleBtn = document.querySelector('.btn-filter-toggle');
                const filterCard = document.querySelector('.wbbm-list-filters-card');

                function updateToggleText() {
                    const isExpanded = !filterCard.classList.contains('collapsed');
                    filterToggleBtn.innerHTML = isExpanded ?
                        '<span class="dashicons dashicons-filter"></span> <?php _e("Hide Filters", "bus-booking-manager"); ?>' :
                        '<span class="dashicons dashicons-filter"></span> <?php _e("Show Filters", "bus-booking-manager"); ?>';

                    if (isExpanded) {
                        filterToggleBtn.classList.remove('btn-primary');
                        filterToggleBtn.classList.add('btn-outline');
                    } else {
                        filterToggleBtn.classList.remove('btn-outline');
                        filterToggleBtn.classList.add('btn-primary');
                    }
                }

                if (filterToggleBtn && filterCard) {
                    updateToggleText();

                    filterToggleBtn.addEventListener('click', function() {
                        filterCard.classList.toggle('collapsed');
                        updateToggleText();
                    });
                }
            });
        </script>
    <?php
    }

    function passenger_list($_passger, $class_name, $sl, $default_billing_fields, $wc_custom_checkout_fields, $bus_id, $mage_meta, $t, $passenger_index)
    {
        $user_id = $_passger->user_id;
        $per_price_key = 'per_' . strtolower($_passger->user_type) . '_price';
        $per_price     = isset($_passger->$per_price_key) ? $_passger->$per_price_key : 0;
        $pin          = $_passger->order_id . "-" . $_passger->booking_id . "-" . $_passger->user_id . "-" . $_passger->bus_id;

        // Get an instance of the WC_Customer Object from the user ID
        $customer = new WC_Customer($user_id);
        $mage_meta = get_post_custom($_passger->bus_id);

        $status_label = wbbm_ticket_status_name($_passger->status);
        $status_class = 'status-draft';
        if (strtolower($status_label) === 'completed' || strtolower($status_label) === 'publish' || strtolower($status_label) === 'confirmed') {
            $status_class = 'status-publish';
        } elseif (strtolower($status_label) === 'processing' || strtolower($status_label) === 'pending' || strtolower($status_label) === 'on-hold') {
            $status_class = 'status-pending';
        }
    ?>
        <tr class="<?php echo $class_name; ?>">
            <td class="col-id">
                <div class="wbbm-list-title"><span style="color:var(--sh-primary);">#<?php echo $_passger->booking_id; ?></span></div>
                <div class="wbbm-list-meta">
                    <a href="<?php echo get_edit_post_link($_passger->order_id); ?>" style="color:var(--sh-text-soft);">Ord #<?php echo $_passger->order_id; ?></a>
                </div>
                <div class="wbbm-list-sub-meta" style="font-size:11px;"><?php echo wbbm_get_datetime($_passger->booking_date, 'date-text'); ?></div>
            </td>

            <td class="col-bus">
                <div class="wbbm-list-title">
                    <a href="<?php echo admin_url('admin.php?page=wbbm-bus-edit&post_id=' . $_passger->bus_id); ?>"><?php echo get_the_title($_passger->bus_id); ?></a>
                </div>
                <div class="wbbm-list-sub-meta"><?php echo get_post_meta($_passger->bus_id, 'wbbm_bus_no', true); ?></div>
            </td>

            <td class="col-customer">
                <?php
                $billing_first_name = $customer->get_billing_first_name();
                $billing_last_name  = $customer->get_billing_last_name();
                $user_email   = $customer->get_email();
                $billing_phone = $customer->get_billing_phone();
                ?>
                <div style="font-weight: 600; color: var(--sh-text-main); margin-bottom: 2px;"><?php echo $billing_first_name . ' ' . $billing_last_name; ?></div>
                <div class="wbbm-list-sub-meta" style="font-size:12px;"><?php echo $user_email; ?></div>
                <div class="wbbm-list-sub-meta" style="font-size:12px;"><?php echo $billing_phone; ?></div>
            </td>

            <td class="col-journey">
                <div style="font-weight: 500; color: var(--sh-text-main); margin-bottom: 2px;"><?php echo wbbm_get_datetime($_passger->journey_date, 'date-text'); ?></div>
                <div class="wbbm-list-sub-meta" style="color: var(--sh-primary); font-weight: 600;"><?php echo wbbm_get_datetime($_passger->bus_start, 'time'); ?></div>
            </td>

            <td class="col-route">
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <div style="font-size: 13px;">
                        <span style="color: #64748b; font-weight: bold; font-size: 16px; line-height: 1; vertical-align: middle;">○</span>
                        <span style="font-weight: 600; color: var(--sh-text-main);"><?php echo $_passger->boarding_point; ?></span>
                        <?php if (!empty($_passger->pickpoint)) : ?>
                            <br><span style="padding-left: 14px; font-size: 12px; color: var(--sh-text-soft);">↳ <?php echo $_passger->pickpoint; ?></span>
                        <?php endif; ?>
                    </div>
                    <div style="padding-left: 4px; border-left: 1px dashed #cbd5e1; margin-left: 3px; height: 12px;"></div>
                    <div style="font-size: 13px;">
                        <span style="color: var(--sh-primary); font-weight: bold; font-size: 16px; line-height: 1; vertical-align: middle;">●</span>
                        <span style="font-weight: 600; color: var(--sh-text-main);"><?php echo $_passger->droping_point; ?></span>
                    </div>
                </div>
            </td>

            <td class="col-passengers">
                <div class="capacity-info">
                    <span class="count" style="margin-bottom: 4px; font-size: 13px; color: var(--sh-text-soft);">
                        <?php
                        if ($_passger->user_type == 'child') {
                            echo wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') : __('Child', 'bus-booking-manager');
                        } elseif ($_passger->user_type == 'infant') {
                            echo wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') : __('Infant', 'bus-booking-manager');
                        } elseif ($_passger->user_type == 'entire') {
                            echo wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') : __('Entire Bus', 'bus-booking-manager');
                        } else {
                            echo wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') : __('Adult', 'bus-booking-manager');
                        }
                        ?>
                    </span>
                    <span class="count" style="color: var(--sh-text-main); font-weight: 700; font-size: 15px;"><?php echo wc_price($per_price); ?></span>
                </div>
            </td>

            <?php if (isset($_GET['bus_id']) && $_GET['bus_id'] != '') : ?>
                <td class="col-extra-info">
                    <div class="extra-info-container" style="font-size: 12px; line-height: 1.4;">
                        <?php
                        $extra_info_html = '';
                        $bus_id_val = $_passger->bus_id;
                        $mage_meta_val = get_post_custom($bus_id_val);

                        // 1. Primary Registration Fields
                        $fields = array(
                            'wbbm_full_name' => __('Name', 'bus-booking-manager'),
                            'wbbm_reg_email' => __('Email', 'bus-booking-manager'),
                            'wbbm_reg_phone' => __('Phone', 'bus-booking-manager'),
                            'wbbm_user_gender' => __('Gender', 'bus-booking-manager'),
                            'wbbm_reg_address' => __('Address', 'bus-booking-manager'),
                            'wbbm_user_dob' => __('DOB', 'bus-booking-manager'),
                            'wbbm_user_nationality' => __('Nationality', 'bus-booking-manager'),
                            'wbbm_user_flight_arrival_no' => __('Arrival', 'bus-booking-manager'),
                            'wbbm_user_flight_departure_no' => __('Departure', 'bus-booking-manager'),
                        );

                        foreach ($fields as $meta_key => $label) {
                            $is_enabled = isset($mage_meta_val[$meta_key]) && $mage_meta_val[$meta_key][0] == 1;
                            if ($is_enabled) {
                                // Map meta key to property name in $_passger
                                $prop_map = array(
                                    'wbbm_full_name' => 'user_name',
                                    'wbbm_reg_email' => 'user_email',
                                    'wbbm_reg_phone' => 'user_phone',
                                    'wbbm_user_gender' => 'user_gender',
                                    'wbbm_reg_address' => 'user_address',
                                    'wbbm_user_dob' => 'user_dob',
                                    'wbbm_user_nationality' => 'nationality',
                                    'wbbm_user_flight_arrival_no' => 'flight_arrial_no',
                                    'wbbm_user_flight_departure_no' => 'flight_departure_no'
                                );
                                $actual_prop = isset($prop_map[$meta_key]) ? $prop_map[$meta_key] : '';
                                if ($actual_prop && !empty($_passger->$actual_prop)) {
                                    $extra_info_html .= '<p style="margin: 0 0 4px 0;"><strong>' . $label . ':</strong> ' . esc_html($_passger->$actual_prop) . '</p>';
                                }
                            }
                        }

                        // 2. Custom Form Builder Fields
                        if (!empty($_passger->extra_info) && is_array($_passger->extra_info)) {
                            $extra_info_arr = array_reverse($_passger->extra_info);
                            if (isset($extra_info_arr[$passenger_index])) {
                                foreach ($extra_info_arr[$passenger_index] as $ext_item) {
                                    if (!empty($ext_item['name']) && !empty($ext_item['value'])) {
                                        $extra_info_html .= '<p style="margin: 0 0 4px 0;"><strong>' . esc_html($ext_item['name']) . ':</strong> ' . esc_html($ext_item['value']) . '</p>';
                                    }
                                }
                            }
                        }

                        echo $extra_info_html ? $extra_info_html : '-';
                        ?>
                    </div>
                </td>
            <?php endif; ?>

            <td class="col-status">
                <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_label; ?></span>
                <div class="wbbm-list-sub-meta" style="font-size: 11px; margin-top: 4px;"><?php echo wbbm_checkin_status_name($_passger->ticket_status); ?></div>
            </td>

            <td class="col-action" style="text-align: right;">
                <div class="action-buttons" style="justify-content: flex-end;">
                    <?php $ticket_status = $_passger->status;
                    if ($ticket_status == 99) : ?>
                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('edit.php?post_type=wbbm_bus&page=passenger_list&req_type=restore&id=' . absint($_passger->booking_id) . '&order_id=' . absint($_passger->order_id)), 'wbbm_booking_action')); ?>" title="<?php esc_attr_e('Restore', 'bus-booking-manager'); ?>" class="action-btn"><span class="dashicons dashicons-undo"></span></a>
                        <a onClick="return deleteConfirm()" href="<?php echo esc_url(wp_nonce_url(admin_url('edit.php?post_type=wbbm_bus&page=passenger_list&req_type=delete_permanently&id=' . absint($_passger->booking_id)), 'wbbm_booking_action')); ?>" title="<?php esc_attr_e('Delete Permanently', 'bus-booking-manager'); ?>" class="action-btn delete-btn"><span class="dashicons dashicons-trash"></span></a>
                    <?php else : ?>
                        <?php if ($this->ticket_screen_available()) : ?>
                            <a href="<?php echo get_admin_url(); ?>edit.php?post_type=wbbm_bus&page=create_ticket&pin=<?php echo $pin; ?>" title="<?php _e('Download Ticket', 'bus-booking-manager'); ?>" class="action-btn"><span class="dashicons dashicons-pdf"></span></a>
                        <?php else : ?>
                            <?php /* Same treatment as Export and Show Filters: visible, inert, marked PRO. */ ?>
                            <span class="action-btn wbbm-pro-locked" aria-disabled="true"
                                  title="<?php esc_attr_e('Download Ticket - available in the PRO version', 'bus-booking-manager'); ?>">
                                <span class="dashicons dashicons-pdf"></span>
                                <em class="wbbm-pro-badge wbbm-pro-badge--dot" aria-label="<?php esc_attr_e('PRO', 'bus-booking-manager'); ?>"><?php esc_html_e('PRO', 'bus-booking-manager'); ?></em>
                            </span>
                        <?php endif; ?>
                        <a onClick="return deleteConfirm()" href="<?php echo esc_url(wp_nonce_url(admin_url('edit.php?post_type=wbbm_bus&page=passenger_list&req_type=delete&id=' . absint($_passger->booking_id)), 'wbbm_booking_action')); ?>" title="<?php esc_attr_e('Delete', 'bus-booking-manager'); ?>" class="action-btn delete-btn"><span class="dashicons dashicons-trash"></span></a>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php
        $t += $per_price;
    }

    private function filter_booking_ids_by_object_post_type($booking_ids, $post_type)
    {
        if (empty($booking_ids) || !$post_type) {
            return array();
        }

        $filtered_ids = array();
        foreach ($booking_ids as $booking_id) {
            $linked_post_id = get_post_meta($booking_id, '_wbbm_bus_id', true);
            if ($linked_post_id && get_post_type($linked_post_id) === $post_type) {
                $filtered_ids[] = $booking_id;
            }
        }

        return $filtered_ids;
    }

    private function get_passenger_posts_by_ids($booking_ids)
    {
        if (empty($booking_ids)) {
            return array();
        }

        return get_posts(array(
            'post_type' => 'wbbm_booking',
            'posts_per_page' => count($booking_ids),
            'post__in' => $booking_ids,
            'orderby' => 'post__in'
        ));
    }

    function billing_heading($default_billing_fields)
    {

        foreach ($default_billing_fields as $default_billing_field) {
            if ($default_billing_field == 'p_name') :
                echo '<th>' . __('Billing Name', 'bus-booking-manager') . '</th>';
            endif;

            if ($default_billing_field == 'p_phone') :
                echo '<th>' . __('Billing Phone', 'bus-booking-manager') . '</th>';
            endif;

            if ($default_billing_field == 'p_email') :
                echo '<th>' . __('Billing Email', 'bus-booking-manager') . '</th>';
            endif;

            if ($default_billing_field == 'p_company') :
                echo '<th>' . __('Company', 'bus-booking-manager') . '</th>';
            endif;

            if ($default_billing_field == 'p_address') :
                echo '<th>' . __('Address', 'bus-booking-manager') . '</th>';
            endif;

            if ($default_billing_field == 'p_city') :
                echo '<th>' . __('City', 'bus-booking-manager') . '</th>';
            endif;

            if ($default_billing_field == 'p_state') :
                echo '<th>' . __('State', 'bus-booking-manager') . '</th>';
            endif;

            if ($default_billing_field == 'p_postcode') :
                echo '<th>' . __('Postcode', 'bus-booking-manager') . '</th>';
            endif;

            if ($default_billing_field == 'p_country') :
                echo '<th>' . __('Country', 'bus-booking-manager') . '</th>';
            endif;

            if ($default_billing_field == 'p_payment_method') :
                echo '<th>' . __('Payment Method', 'bus-booking-manager') . '</th>';
            endif;
        }
    }
    function billing_heading_value($_passger, $customer, $default_billing_fields)
    {
        $user_email   = $customer->get_email(); // Get account email

        // Customer billing information details (from account)
        $billing_first_name = $customer->get_billing_first_name();
        $billing_last_name  = $customer->get_billing_last_name();

        $billing_company    = $customer->get_billing_company();
        $billing_address_1  = $customer->get_billing_address_1();
        $billing_address_2  = $customer->get_billing_address_2();
        $billing_city       = $customer->get_billing_city();
        $billing_state      = $customer->get_billing_state();
        $billing_postcode   = $customer->get_billing_postcode();
        $billing_country    = $customer->get_billing_country();
        $billing_phone      = $customer->get_billing_phone();
        if (!empty($order)) {
            $payment_title      = $order->get_payment_method_title();
        }
        foreach ($default_billing_fields as $default_billing_field) {
            if ($default_billing_field == 'p_name') :
                echo '<td>' . $billing_first_name . ' ' . $billing_last_name . '</td>';

            endif;

            if ($default_billing_field == 'p_phone') :

                echo '<td>' . $billing_phone . '</td>';

            endif;

            if ($default_billing_field == 'p_email') :

                echo '<td>' . $user_email . '</td>';

            endif;

            if ($default_billing_field == 'p_company') :
                echo '<td>' . $billing_company . '</td>';
            endif;

            if ($default_billing_field == 'p_address') :
                echo '<td>' . $billing_address_1 . ' ' . $billing_address_2 . '</td>';
            endif;

            if ($default_billing_field == 'p_city') :
                echo '<td>' . $billing_city . '</td>';
            endif;

            if ($default_billing_field == 'p_state') :
                echo '<td>' . $billing_state . '</td>';
            endif;

            if ($default_billing_field == 'p_postcode') :
                echo '<td>' . $billing_postcode . '</td>';
            endif;

            if ($default_billing_field == 'p_country') :
                echo '<td>' . $billing_country . '</td>';
            endif;

            if ($default_billing_field == 'p_payment_method') :
                echo '<td>' . (isset($payment_title) ? $payment_title : "") . '</td>';
            endif;
        }
    }
    function custom_heading($wc_custom_checkout_fields)
    {

        foreach ($wc_custom_checkout_fields as $wc_custom_checkout_field) {
            if ($wc_custom_checkout_field) {
                echo '<th>' . $wc_custom_checkout_field . '</th>';
            }
        }
    }
    function custom_heading_value($mage_meta)
    {
        //ggg
    }
    function seven_register_form_heading($bus_id, $mage_meta)
    {
        if ($bus_id) {
            if ($mage_meta) {
                $seven_register_form_heading = '';

                $gender = array_key_exists('wbbm_full_name', $mage_meta) ? strip_tags($mage_meta['wbbm_full_name'][0]) : false;
                if ($gender) {
                    $seven_register_form_heading .= '<th>' . 'Name' . '</th>';
                }

                $gender = array_key_exists('wbbm_reg_email', $mage_meta) ? strip_tags($mage_meta['wbbm_reg_email'][0]) : false;
                if ($gender) {
                    $seven_register_form_heading .= '<th>' . 'Email' . '</th>';
                }

                $gender = array_key_exists('wbbm_reg_phone', $mage_meta) ? strip_tags($mage_meta['wbbm_reg_phone'][0]) : false;
                if ($gender) {
                    $seven_register_form_heading .= '<th>' . 'Phone' . '</th>';
                }

                $gender = array_key_exists('wbbm_user_gender', $mage_meta) ? strip_tags($mage_meta['wbbm_user_gender'][0]) : false;
                if ($gender) {
                    $seven_register_form_heading .= '<th>' . 'Gender' . '</th>';
                }

                $wbbm_reg_address = array_key_exists('wbbm_reg_address', $mage_meta) ? strip_tags($mage_meta['wbbm_reg_address'][0]) : false;
                if ($wbbm_reg_address) {
                    $seven_register_form_heading .= '<th>' . 'Address' . '</th>';
                }


                $gender = array_key_exists('wbbm_user_dob', $mage_meta) ? strip_tags($mage_meta['wbbm_user_dob'][0]) : false;
                if ($gender) {
                    $seven_register_form_heading .= '<th>' . 'DOB' . '</th>';
                }

                $gender = array_key_exists('wbbm_user_nationality', $mage_meta) ? strip_tags($mage_meta['wbbm_user_nationality'][0]) : false;
                if ($gender) {
                    $seven_register_form_heading .= '<th>' . 'Nationality' . '</th>';
                }

                $gender = array_key_exists('wbbm_user_flight_arrival_no', $mage_meta) ? strip_tags($mage_meta['wbbm_user_flight_arrival_no'][0]) : false;
                if ($gender) {
                    $seven_register_form_heading .= '<th>' . 'Flight Arrival' . '</th>';
                }

                $gender = array_key_exists('wbbm_user_flight_departure_no', $mage_meta) ? strip_tags($mage_meta['wbbm_user_flight_departure_no'][0]) : false;
                if ($gender) {
                    $seven_register_form_heading .= '<th>' . 'Flight Departure' . '</th>';
                }

                if (array_key_exists('wbbm_user_extra_bag', $mage_meta)) {
                    $extra_bag = strip_tags($mage_meta['wbbm_user_extra_bag'][0]);
                    $extra_bag_qty = isset($mage_meta['wbbm_extra_bag_price']) ? strip_tags($mage_meta['wbbm_extra_bag_price'][0]) : 0;
                    if ($extra_bag && $extra_bag_qty) {
                        $seven_register_form_heading          .= '<th>' . 'Extra Bag' . '</th>';
                    }
                }

                echo $seven_register_form_heading;
            }
        }
    }
    function seven_register_form_value($bus_id, $mage_meta, $_passger, $passenger_index)
    {
        if ($_passger->order_id) {
            $order = wc_get_order($_passger->order_id);
            if ($order) {
                foreach ($order->get_items() as $item_id => $item_values) {
                    $passenger_info = maybe_unserialize(wbbm_get_order_meta($item_id, '_wbbm_passenger_info'));
                    // $passenger_info_additional = maybe_unserialize(wbbm_get_order_meta($item_id, '_wbbm_passenger_info_additional'));
                    // $extra_services = maybe_unserialize(wbbm_get_order_meta($item_id, '_wbbm_extra_services'));
                }
            }
        }
        if ($bus_id && $mage_meta) {
            $seven_register_form_value = '';

            $wbbm_full_name = array_key_exists('wbbm_full_name', $mage_meta) ? strip_tags($mage_meta['wbbm_full_name'][0]) : false;
            if ($wbbm_full_name) {
                $seven_register_form_value .= "<td>" . $_passger->user_name . "</td>";
            }

            $wbbm_reg_email = array_key_exists('wbbm_reg_email', $mage_meta) ? strip_tags($mage_meta['wbbm_reg_email'][0]) : false;
            if ($wbbm_reg_email) {
                $seven_register_form_value .= "<td>" . $_passger->user_email . "</td>";
            }

            $wbbm_reg_phone = array_key_exists('wbbm_reg_phone', $mage_meta) ? strip_tags($mage_meta['wbbm_reg_phone'][0]) : false;
            if ($wbbm_reg_phone) {
                $seven_register_form_value .= "<td>" . $_passger->user_phone . "</td>";
            }

            $wbbm_user_gender = array_key_exists('wbbm_user_gender', $mage_meta) ? strip_tags($mage_meta['wbbm_user_gender'][0]) : false;
            if ($wbbm_user_gender) {
                $seven_register_form_value .= "<td>" . $_passger->user_gender . "</td>";
            }

            $wbbm_reg_address = array_key_exists('wbbm_reg_address', $mage_meta) ? strip_tags($mage_meta['wbbm_reg_address'][0]) : false;
            if ($wbbm_reg_address) {
                $seven_register_form_value .= "<td>" . $_passger->user_address . "</td>";
            }




            $dob = array_key_exists('wbbm_user_dob', $mage_meta) ? strip_tags($mage_meta['wbbm_user_dob'][0]) : false;
            if ($dob) {
                $seven_register_form_value .= "<td>" . $_passger->user_dob . "</td>";
            }


            $nationality = array_key_exists('wbbm_user_nationality', $mage_meta) ? strip_tags($mage_meta['wbbm_user_nationality'][0]) : false;
            if ($nationality) {
                $seven_register_form_value .= "<td>" . $_passger->nationality . "</td>";
            }


            $flight_arrial_no = array_key_exists('wbbm_user_flight_arrival_no', $mage_meta) ? strip_tags($mage_meta['wbbm_user_flight_arrival_no'][0]) : false;
            if ($flight_arrial_no) {
                $seven_register_form_value .= "<td>" . $_passger->flight_arrial_no . "</td>";
            }

            $flight_departure_no = array_key_exists('wbbm_user_flight_departure_no', $mage_meta) ? strip_tags($mage_meta['wbbm_user_flight_departure_no'][0]) : false;
            if ($flight_departure_no) {
                $seven_register_form_value .= "<td>" . $_passger->flight_departure_no . "</td>";
            }

            // Extra bag column
            $extra_bag_field = array_key_exists('wbbm_user_extra_bag', $mage_meta) ? strip_tags($mage_meta['wbbm_user_extra_bag'][0]) : false;
            if ($passenger_info && $extra_bag_field) {
                $passenger_info = array_reverse($passenger_info);
                $extra_bag_html = '-';
                if (isset($passenger_info[$passenger_index]['extra_bag_quantity'])) {
                    if (!$passenger_info[$passenger_index]['extra_bag_quantity']) {
                        $extra_bag_html = '-';
                    } elseif ($passenger_info[$passenger_index]['extra_bag_quantity'] > 1) {
                        $extra_bag_html = $passenger_info[$passenger_index]['extra_bag_quantity'] . ' pcs';
                    } else {
                        $extra_bag_html = $passenger_info[$passenger_index]['extra_bag_quantity'] . ' pc';
                    }
                    $seven_register_form_value .= "<td>" . $extra_bag_html . "</td>";
                } else {
                    $seven_register_form_value .= "<td>" . $extra_bag_html . "</td>";
                }

                // $seven_register_form_value .= "<td>" . $extra_bag_html . "</td>";
            }


            echo $seven_register_form_value;
        } else {
            $seven_register_form_value = '';

            $wbbm_full_name = array_key_exists('wbbm_full_name', $mage_meta) ? strip_tags($mage_meta['wbbm_full_name'][0]) : false;
            if ($wbbm_full_name) {
                // $seven_register_form_value .= "<td>" . $_passger->user_name . "</td>";
                if ($_passger->user_name) {
                    $seven_register_form_value .= "<p><strong>" . __('Full name', 'bus-booking-manager') . ":</strong> " . $_passger->user_name . "</p>";
                }
            }

            $wbbm_reg_email = array_key_exists('wbbm_reg_email', $mage_meta) ? strip_tags($mage_meta['wbbm_reg_email'][0]) : false;
            if ($wbbm_reg_email) {
                // $seven_register_form_value .= "<td>" . $_passger->user_email . "</td>";
                if ($_passger->user_email) {
                    $seven_register_form_value .= "<p><strong>" . __('Email', 'bus-booking-manager') . ":</strong> " . $_passger->user_email . "</p>";
                }
            }

            $wbbm_reg_phone = array_key_exists('wbbm_reg_phone', $mage_meta) ? strip_tags($mage_meta['wbbm_reg_phone'][0]) : false;
            if ($wbbm_reg_phone) {
                // $seven_register_form_value .= "<td>" . $_passger->user_phone . "</td>";
                if ($_passger->user_phone) {
                    $seven_register_form_value .= "<p><strong>" . __('Phone', 'bus-booking-manager') . ":</strong> " . $_passger->user_phone . "</p>";
                }
            }

            $wbbm_user_gender = array_key_exists('wbbm_user_gender', $mage_meta) ? strip_tags($mage_meta['wbbm_user_gender'][0]) : false;
            if ($wbbm_user_gender) {
                // $seven_register_form_value .= "<td>" . $_passger->user_gender . "</td>";
                if ($_passger->user_gender) {
                    $seven_register_form_value .= "<p><strong>" . __('Gender', 'bus-booking-manager') . ":</strong> " . $_passger->user_gender . "</p>";
                }
            }

            $wbbm_reg_address = array_key_exists('wbbm_reg_address', $mage_meta) ? strip_tags($mage_meta['wbbm_reg_address'][0]) : false;
            if ($wbbm_reg_address) {
                // $seven_register_form_value .= "<td>" . $_passger->user_address . "</td>";
                if ($_passger->user_address) {
                    $seven_register_form_value .= "<p><strong>" . __('Address', 'bus-booking-manager') . ":</strong> " . $_passger->user_address . "</p>";
                }
            }




            $dob = array_key_exists('wbbm_user_dob', $mage_meta) ? strip_tags($mage_meta['wbbm_user_dob'][0]) : false;
            if ($dob) {
                $seven_register_form_value .= "<td>" . $_passger->user_dob . "</td>";
                if ($_passger->user_dob) {
                    $seven_register_form_value .= "<p><strong>" . __('Date of birth', 'bus-booking-manager') . ":</strong> " . $_passger->user_dob . "</p>";
                }
            }


            $nationality = array_key_exists('wbbm_user_nationality', $mage_meta) ? strip_tags($mage_meta['wbbm_user_nationality'][0]) : false;
            if ($nationality) {
                // $seven_register_form_value .= "<td>" . $_passger->nationality . "</td>";
                if ($_passger->nationality) {
                    $seven_register_form_value .= "<p><strong>" . __('Nationality', 'bus-booking-manager') . ":</strong> " . $_passger->nationality . "</p>";
                }
            }


            $flight_arrial_no = array_key_exists('wbbm_user_flight_arrival_no', $mage_meta) ? strip_tags($mage_meta['wbbm_user_flight_arrival_no'][0]) : false;
            if ($flight_arrial_no) {
                // $seven_register_form_value .= "<td>" . $_passger->flight_arrial_no . "</td>";
                if ($_passger->flight_arrial_no) {
                    $seven_register_form_value .= "<p><strong>" . __('Flight arrival no', 'bus-booking-manager') . ":</strong> " . $_passger->flight_arrial_no . "</p>";
                }
            }

            $flight_departure_no = array_key_exists('wbbm_user_flight_departure_no', $mage_meta) ? strip_tags($mage_meta['wbbm_user_flight_departure_no'][0]) : false;
            if ($flight_departure_no) {
                // $seven_register_form_value .= "<td>" . $_passger->flight_departure_no . "</td>";
                if ($_passger->flight_departure_no) {
                    $seven_register_form_value .= "<p><strong>" . __('Flight departure no', 'bus-booking-manager') . ":</strong> " . $_passger->flight_departure_no . "</p>";
                }
            }

            $extra_bag_field = array_key_exists('wbbm_user_extra_bag', $mage_meta) ? strip_tags($mage_meta['wbbm_user_extra_bag'][0]) : false;
            if ($passenger_info && $extra_bag_field) {
                $passenger_info = array_reverse($passenger_info);
                $extra_bag_html = '-';
                if (isset($passenger_info[$passenger_index]['extra_bag_quantity'])) {
                    if (!$passenger_info[$passenger_index]['extra_bag_quantity']) {
                        $extra_bag_html = '';
                    } elseif ($passenger_info[$passenger_index]['extra_bag_quantity'] > 1) {
                        $extra_bag_html = $passenger_info[$passenger_index]['extra_bag_quantity'] . ' pcs';
                    } else {
                        $extra_bag_html = $passenger_info[$passenger_index]['extra_bag_quantity'] . ' pc';
                    }
                    // $seven_register_form_value .= "<<p><strong>>" . $extra_bag_html . "</td>";
                    if ($extra_bag_html) {
                        $seven_register_form_value .= "<p><strong>" . __('Extra bag', 'bus-booking-manager') . ":</strong> " . $extra_bag_html . "</p>";
                    }
                }
            }


            $seven_register_form_value .= $this->form_builder_custom_value($bus_id, $_passger, $passenger_index); // Custom form builder data of reg form

            echo "<td>" . ($seven_register_form_value ? $seven_register_form_value : '-') . "</td>";
        }
    }

    function form_builder_custom_value($bus_id, $_passger, $passenger_index)
    {
        if ($bus_id) {
            $form_builder_custom_heading = '';

            $custom_field = unserialize(get_post_meta($bus_id, 'wbbm_attendee_reg_form', true));

            $order = wc_get_order($_passger->order_id);
            $passenger_info_additional = array();
            if (!empty($order)) {
                foreach ($order->get_items() as $item_id => $item_values) {
                    $passenger_info_additional = maybe_unserialize(wbbm_get_order_meta($item_id, '_wbbm_passenger_info_additional'));
                }
            }

            if ($custom_field) {
                foreach ($custom_field as $epv) {
                    if ($epv['field_label']) {
                        $id = $this->searchForId($epv['field_label'], $passenger_info_additional, $passenger_index);
                        $form_builder_custom_heading .= '<td>' . $id . '</td>';
                    }
                }
            }


            echo $form_builder_custom_heading;
        } else {
            $form_builder_custom_heading = '';
            if ($_passger->extra_info) {
                $extra_info_arr = array_reverse($_passger->extra_info); // reverse list for fix serialization
                foreach ($extra_info_arr[$passenger_index] as $ext_item) {
                    $form_builder_custom_heading .= "<p><strong>" . $ext_item['name'] . ":</strong> " . $ext_item['value'] . "</p>";
                }
            }

            return $form_builder_custom_heading;
        }
    }

    function searchForId($id, $passenger_info_additional, $passenger_index)
    {
        if (isset($passenger_info_additional[$passenger_index])) {
            $d = '-';
            $passenger_info_additional = array_reverse($passenger_info_additional);
            foreach ($passenger_info_additional[$passenger_index] as $p) {
                if ($id == $p['name']) {
                    $d = $p['value'];
                    break;
                }
            }
            return $d;
        }
        return '-';
    }


    function form_builder_custom_heading($bus_id)
    {
        $form_builder_custom_heading = '';
        if ($bus_id) {
            $custom_field = unserialize(get_post_meta($bus_id, 'wbbm_attendee_reg_form', true));
            if ($custom_field) {
                foreach ($custom_field as $epv) {
                    if ($epv['field_id']) {
                        $form_builder_custom_heading .= '<th>' . $epv['field_label'] . '</th>';
                    }
                }
            }
        } else {
            $form_builder_custom_heading .= "<th>" . __('Extra Info', 'addon-bus--ticket-booking-with-seat-pro') . "</th>";
        }
        echo $form_builder_custom_heading;
    }


    function wbbm_custom_field_for_single_bus()
    {
        check_ajax_referer('wbbm_pro_admin', 'nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('Unauthorized', 'bus-booking-manager'), 403);
        }

        $bus_id = isset($_POST['bus_id']) ? absint($_POST['bus_id']) : 0;

        if (!$bus_id || get_post_type($bus_id) !== 'wbbm_bus' || !current_user_can('edit_post', $bus_id)) {
            wp_send_json_error(__('Invalid bus.', 'bus-booking-manager'), 400);
        }

        $mage_meta = get_post_custom($bus_id);

        ob_start();
        $wbbm_full_name = array_key_exists('wbbm_full_name', $mage_meta) ? strip_tags($mage_meta['wbbm_full_name'][0]) : false;

        if ($wbbm_full_name) {
        ?>

            <div class="filter-group">
                <label><?php _e('Passenger Name', 'bus-booking-manager'); ?></label>
                <input type="text" name="user_name" value="<?php echo esc_attr(isset($_GET['user_name']) ? wp_unslash($_GET['user_name']) : '') ?>" class="form-control" placeholder="<?php _e('Search...', 'bus-booking-manager'); ?>">
            </div>

        <?php
        }

        $wbbm_reg_email = array_key_exists('wbbm_reg_email', $mage_meta) ? strip_tags($mage_meta['wbbm_reg_email'][0]) : false;
        if ($wbbm_reg_email) {
        ?>

            <div class="filter-group">
                <label><?php _e('Passenger Email', 'bus-booking-manager'); ?></label>
                <input type="text" name="user_email" value="<?php echo esc_attr(isset($_GET['user_email']) ? wp_unslash($_GET['user_email']) : '') ?>" class="form-control" placeholder="<?php _e('Search...', 'bus-booking-manager'); ?>">
            </div>

        <?php

        }

        $wbbm_reg_phone = array_key_exists('wbbm_reg_phone', $mage_meta) ? strip_tags($mage_meta['wbbm_reg_phone'][0]) : false;
        if ($wbbm_reg_phone) {
        ?>

            <div class="filter-group">
                <label><?php _e('Passenger Phone', 'bus-booking-manager'); ?></label>
                <input type="text" name="user_phone" value="<?php echo esc_attr(isset($_GET['user_phone']) ? wp_unslash($_GET['user_phone']) : '') ?>" class="form-control" placeholder="<?php _e('Search...', 'bus-booking-manager'); ?>">
            </div>

        <?php
        }

        $wbbm_user_gender = array_key_exists('wbbm_user_gender', $mage_meta) ? strip_tags($mage_meta['wbbm_user_gender'][0]) : false;
        if ($wbbm_user_gender) {
        ?>

            <div class="filter-group">
                <label><?php _e('Gender', 'bus-booking-manager'); ?></label>
                <input type="text" name="user_gender" value="<?php echo esc_attr(isset($_GET['user_gender']) ? wp_unslash($_GET['user_gender']) : '') ?>" class="form-control" placeholder="<?php _e('Search...', 'bus-booking-manager'); ?>">
            </div>

        <?php
        }


        $wbbm_user_dob = array_key_exists('wbbm_user_dob', $mage_meta) ? strip_tags($mage_meta['wbbm_user_dob'][0]) : false;
        if ($wbbm_user_dob) {
        ?>

            <div class="filter-group">
                <label><?php _e('DOB', 'bus-booking-manager'); ?></label>
                <input type="date" id="user_dob" name="user_dob" value="<?php echo esc_attr(isset($_GET['user_dob']) ? wp_unslash($_GET['user_dob']) : '') ?>" class="form-control">
            </div>

        <?php
        }

        $wbbm_reg_address = array_key_exists('wbbm_reg_address', $mage_meta) ? strip_tags($mage_meta['wbbm_reg_address'][0]) : false;
        if ($wbbm_reg_address) {
        ?>

            <div class="filter-group">
                <label><?php _e('Address', 'bus-booking-manager'); ?></label>
                <input type="text" name="user_address" value="<?php echo esc_attr(isset($_GET['user_address']) ? wp_unslash($_GET['user_address']) : '') ?>" class="form-control" placeholder="<?php _e('Search...', 'bus-booking-manager'); ?>">
            </div>

        <?php
        }

        $wbbm_user_nationality = array_key_exists('wbbm_user_nationality', $mage_meta) ? strip_tags($mage_meta['wbbm_user_nationality'][0]) : false;
        if ($wbbm_user_nationality) {
        ?>

            <div class="filter-group">
                <label><?php _e('Nationality', 'bus-booking-manager'); ?></label>
                <input type="text" name="nationality" value="<?php echo esc_attr(isset($_GET['nationality']) ? wp_unslash($_GET['nationality']) : '') ?>" class="form-control" placeholder="<?php _e('Search...', 'bus-booking-manager'); ?>">
            </div>

        <?php
        }

        $wbbm_user_flight_arrival_no = array_key_exists('wbbm_user_flight_arrival_no', $mage_meta) ? strip_tags($mage_meta['wbbm_user_flight_arrival_no'][0]) : false;
        if ($wbbm_user_flight_arrival_no) {
        ?>

            <div class="filter-group">
                <label><?php _e('Flight Arrival', 'bus-booking-manager'); ?></label>
                <input type="text" name="flight_arrial_no" value="<?php echo esc_attr(isset($_GET['flight_arrial_no']) ? wp_unslash($_GET['flight_arrial_no']) : '') ?>" class="form-control" placeholder="<?php _e('Search...', 'bus-booking-manager'); ?>">
            </div>

        <?php
        }

        $wbbm_user_flight_departure_no = array_key_exists('wbbm_user_flight_departure_no', $mage_meta) ? strip_tags($mage_meta['wbbm_user_flight_departure_no'][0]) : false;
        if ($wbbm_user_flight_departure_no) {
        ?>

            <div class="filter-group">
                <label><?php _e('Flight Departure', 'bus-booking-manager'); ?></label>
                <input type="text" name="flight_departure_no" value="<?php echo esc_attr(isset($_GET['flight_departure_no']) ? wp_unslash($_GET['flight_departure_no']) : '') ?>" class="form-control" placeholder="<?php _e('Search...', 'bus-booking-manager'); ?>">
            </div>

            <?php
        }

        $custom_field = unserialize(get_post_meta($bus_id, 'wbbm_attendee_reg_form', true));
        if ($custom_field) {
            foreach ($custom_field as $epv) {
                if (isset($epv['field_id']) && ($epv['filed_type'] == 'text')) {
            ?>
                    <div class="filter-group">
                        <label><?php echo esc_html($epv['field_label']); ?></label>
                        <input type="text" name="<?php echo esc_attr($epv['field_id']); ?>" value="" class="form-control" placeholder="<?php echo esc_attr($epv['field_label']); ?>...">
                    </div>
<?php
                }
            }
        }

        $output = ob_get_clean();
        echo $output;

        die;
    }
}
