<?php

defined('ABSPATH') || exit;

/**
 * Bookings hub: passengers, counter sales, tickets and reports in one screen.
 *
 * Each tab delegates to the class that already owns that screen. Booking
 * creation, CSV export, ticket generation and report queries are unchanged.
 */
final class WBBM_Bookings_Hub extends WBBM_Admin_Hub
{
    const PAGE = 'wbbm-bookings';

    public function page()
    {
        return self::PAGE;
    }

    public function title()
    {
        return __('Bookings', 'bus-booking-manager');
    }

    protected function description()
    {
        return __('Passenger manifests, counter sales, tickets and sales reports.', 'bus-booking-manager');
    }

    protected function icon()
    {
        return 'dashicons-tickets-alt';
    }

    protected function menu_position()
    {
        return 3;
    }

    protected function register_tabs()
    {
        $tabs = array();

        if (class_exists('AdminPassengerListClass')) {
            $passengers = AdminPassengerListClass::instance();
            $tabs['passengers'] = array(
                'label'       => __('Passenger List', 'bus-booking-manager'),
                'description' => __('Who is travelling, on which service, on which date.', 'bus-booking-manager'),
                'icon'        => 'dashicons-groups',
                'capability'  => 'manage_options',
                'callback'    => array($passengers, 'wbbm_passenger_list'),
                'legacy'      => 'passenger_list',
            );
        }

        if (class_exists('AdminPurchaseTicketClass')) {
            $purchase = AdminPurchaseTicketClass::instance();
            $tabs['purchase'] = array(
                'label'       => __('Purchase Ticket', 'bus-booking-manager'),
                'description' => __('Search a route and book a seat on behalf of a customer.', 'bus-booking-manager'),
                'icon'        => 'dashicons-cart',
                'capability'  => 'manage_options',
                'callback'    => array($purchase, 'wbbm_admin_purchase_ticket'),
                'legacy'      => 'admin_purchase_ticket',
            );
        }

        if (function_exists('wbbm_gen_ticket')) {
            $tabs['ticket'] = array(
                'label'       => __('View Ticket', 'bus-booking-manager'),
                'description' => __('Look up and print an issued ticket.', 'bus-booking-manager'),
                'icon'        => 'dashicons-media-default',
                'capability'  => 'manage_options',
                'callback'    => 'wbbm_gen_ticket',
                'legacy'      => 'create_ticket',
            );
        }

        $report = class_exists('WbbmReport') ? WbbmReport::instance() : null;
        if ($report) {
            $tabs['reports'] = array(
                'label'       => __('Reports', 'bus-booking-manager'),
                'description' => __('Sales, tickets and revenue across every bus booking.', 'bus-booking-manager'),
                'icon'        => 'dashicons-chart-bar',
                'capability'  => $report->capability(),
                'callback'    => array($report, 'wbbm_reports_entry_point'),
                'legacy'      => 'wbbm-reports',
            );
        }

        return $tabs;
    }
}

/*
 * Boot the Bookings page.
 *
 * Only the Passenger List ships with this plugin. Purchase Ticket, View Ticket
 * and Reports stay in the PRO plugin, and register_tabs() above already probes
 * for each of them with class_exists()/function_exists() -- so those three tabs
 * simply appear once PRO is active and are absent otherwise. Priority 21 runs
 * after PRO has pulled its own classes in at plugins_loaded, so a PRO install
 * gets the full four-tab page on the very first paint.
 */
add_action('plugins_loaded', function () {
    if (!class_exists('WBBM_Admin_Hub') || !class_exists('AdminPassengerListClass')) {
        return;
    }

    $passengers = AdminPassengerListClass::instance();

    // The standalone screen stays registered: the hub removes only its menu
    // row, so old bookmarks, form actions and nonce targets keep resolving.
    add_action('admin_menu', array($passengers, 'wbbm_passenger_list_menu'));
    add_action('admin_enqueue_scripts', array($passengers, 'enqueue_assets'));
    add_action('wp_ajax_wbbm_custom_field_for_single_bus', array($passengers, 'wbbm_custom_field_for_single_bus'));

    add_action('admin_enqueue_scripts', 'wbbm_bookings_enqueue_script');

    ( new WBBM_Bookings_Hub() )->boot();
}, 21);

/**
 * Scripts for the Bookings screens.
 *
 * The handle is the one PRO has always used. PRO registers the same handle for
 * its own screens, and a handle can only be registered once, so whichever
 * plugin gets there first supplies the file and the other call is a no-op --
 * that is what keeps the two copies from both loading and double-binding every
 * click handler on the page.
 */
function wbbm_bookings_enqueue_script()
{
    $on_screen = class_exists('WBBM_Admin_Hub')
        && WBBM_Admin_Hub::is_module_screen('passenger_list', WBBM_Bookings_Hub::PAGE, 'passengers', true);

    if (!$on_screen) {
        return;
    }

    /*
     * PRO registers this same handle across the whole admin, and its copy
     * declares a jsPDF dependency that the Reports tab's "Export PDF" button
     * needs. If PRO got here first, leave its registration alone rather than
     * racing it -- ours would drop jsPDF and break that button. Our own copy
     * deliberately does not pull jsPDF in: the only screen this plugin serves
     * on its own is the Passenger List, which has no PDF control.
     */
    if (!wp_script_is('wbbm-admin-script', 'registered')) {
        $rel  = 'assets/admin/wbbm-bookings.js';
        $file = WBTM_PLUGIN_DIR . $rel;

        wp_register_script(
            'wbbm-admin-script',
            WBTM_PLUGIN_URL . $rel,
            array('jquery'),
            file_exists($file) ? filemtime($file) : '1.0.0',
            true
        );
    }

    wp_enqueue_script('wbbm-admin-script');

    wp_localize_script('wbbm-admin-script', 'wbbmProAdmin', array(
        'nonce' => wp_create_nonce('wbbm_pro_admin'),
    ));
}
