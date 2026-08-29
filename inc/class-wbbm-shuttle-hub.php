<?php

defined('ABSPATH') || exit;

/**
 * Shuttle hub: shuttle services, their taxonomies and bookings in one screen.
 *
 * Lives under the shuttle CPT menu and delegates every tab to the list-page
 * class that already owns it, so row actions, nonces and handlers are intact.
 */
final class WBBM_Shuttle_Hub extends WBBM_Admin_Hub
{
    const PAGE = 'wbbm-shuttle-manager';

    public function page()
    {
        return self::PAGE;
    }

    protected function parent_slug()
    {
        return 'edit.php?post_type=wbbm_shuttle';
    }

    public function title()
    {
        return __('Shuttle Manager', 'bus-booking-manager');
    }

    protected function description()
    {
        return __('Shuttle services, categories, stops and bookings in one place.', 'bus-booking-manager');
    }

    protected function icon()
    {
        return 'dashicons-car';
    }

    protected function register_tabs()
    {
        $map = array(
            'shuttles' => array(
                'label'       => __('Shuttles', 'bus-booking-manager'),
                'description' => __('Every shuttle service you offer.', 'bus-booking-manager'),
                'icon'        => 'dashicons-car',
                'class'       => 'ShuttleListPageClass',
                'method'      => 'render_shuttle_list_page',
                'legacy'      => 'wbbm-shuttle-list',
            ),
            'types' => array(
                'label'       => __('Types', 'bus-booking-manager'),
                'description' => __('Vehicle classes used to group shuttles.', 'bus-booking-manager'),
                'icon'        => 'dashicons-tag',
                'class'       => 'ShuttleTypeListPageClass',
                'method'      => 'render_shuttle_type_list_page',
                'legacy'      => 'wbbm-shuttle-type-list',
            ),
            'categories' => array(
                'label'       => __('Categories', 'bus-booking-manager'),
                'description' => __('Categories used to organise shuttle listings.', 'bus-booking-manager'),
                'icon'        => 'dashicons-category',
                'class'       => 'ShuttleCatListPageClass',
                'method'      => 'render_shuttle_cat_list_page',
                'legacy'      => 'wbbm-shuttle-cat-list',
            ),
            'stops' => array(
                'label'       => __('Stops', 'bus-booking-manager'),
                'description' => __('Pickup and drop-off points available to shuttles.', 'bus-booking-manager'),
                'icon'        => 'dashicons-location',
                'class'       => 'ShuttleStopListPageClass',
                'method'      => 'render_shuttle_stop_list_page',
                'legacy'      => 'wbbm-shuttle-stop-list',
            ),
            'bookings' => array(
                'label'       => __('Bookings', 'bus-booking-manager'),
                'description' => __('Shuttle reservations and their status.', 'bus-booking-manager'),
                'icon'        => 'dashicons-tickets-alt',
                'class'       => 'ShuttleBookingAdminClass',
                'method'      => 'render_shuttle_booking_list',
                'legacy'      => 'wbbm-shuttle-bookings',
            ),
        );

        $tabs = array();
        foreach ($map as $key => $item) {
            $class = $item['class'];
            if (!class_exists($class) || !is_callable(array($class, 'instance'))) {
                continue;
            }
            $instance = call_user_func(array($class, 'instance'));
            if (!$instance || !method_exists($instance, $item['method'])) {
                continue;
            }

            $tabs[$key] = array(
                'label'       => $item['label'],
                'description' => $item['description'],
                'icon'        => $item['icon'],
                'capability'  => 'manage_options',
                'callback'    => array($instance, $item['method']),
                'legacy'      => $item['legacy'],
            );
        }

        return $tabs;
    }
}

// Boot the shuttle hub.
add_action('plugins_loaded', function () {
    if (class_exists('WBBM_Shuttle_Hub')) {
        ( new WBBM_Shuttle_Hub() )->boot();
    }
}, 20);
