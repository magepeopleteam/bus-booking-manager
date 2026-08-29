<?php

defined('ABSPATH') || exit;

/**
 * Settings hub: plugin settings and the guided quick setup in one screen.
 *
 * Both tabs delegate to the classes that already own those screens, so the
 * settings API, its sections, nonces and save handlers are untouched.
 */
final class WBBM_Settings_Hub extends WBBM_Admin_Hub
{
    const PAGE = 'wbbm-settings';

    public function page()
    {
        return self::PAGE;
    }

    public function title()
    {
        return __('Settings', 'bus-booking-manager');
    }

    protected function description()
    {
        return __('Configure booking behaviour, labels, styling and payments.', 'bus-booking-manager');
    }

    protected function icon()
    {
        return 'dashicons-admin-settings';
    }

    protected function register_tabs()
    {
        $tabs = array(
            'general' => array(
                'label'       => __('General Settings', 'bus-booking-manager'),
                'description' => __('Every plugin option, grouped by section.', 'bus-booking-manager'),
                'icon'        => 'dashicons-admin-generic',
                'capability'  => 'manage_options',
                'callback'    => array($this, 'render_settings'),
                'legacy'      => 'wbbm_quick_setup',
            ),
        );

        // Quick Setup only exists once WooCommerce is present.
        if (class_exists('WBTM_Quick_Setup') && WBTM_Quick_Setup::instance()) {
            $tabs['quick-setup'] = array(
                'label'       => __('Quick Setup', 'bus-booking-manager'),
                'description' => __('Guided first-run setup for pages, currency and a sample service.', 'bus-booking-manager'),
                'icon'        => 'dashicons-controls-play',
                'capability'  => 'manage_options',
                'callback'    => array($this, 'render_quick_setup'),
                'legacy'      => array('wbbm_init_quick_setup', 'wbtm_quick_setup'),
            );
        }

        if (!is_plugin_active('bus-booking-manager-pro/wbtm-pro.php')) {
            $tabs['go-pro'] = array(
                'label'      => __('Go PRO', 'bus-booking-manager'),
                'icon'       => 'dashicons-star-filled',
                'capability' => 'manage_options',
                'callback'   => array($this, 'render_go_pro'),
                'legacy'     => 'wbbm_go_pro_page',
            );
        }

        return $tabs;
    }

    public function render_settings()
    {
        $settings = class_exists('MAGE_WBBM_Setting_Controls') ? MAGE_WBBM_Setting_Controls::instance() : null;

        if (!$settings || !method_exists($settings, 'plugin_page')) {
            self::empty_state(
                __('Settings are unavailable', 'bus-booking-manager'),
                __('The settings controller could not be loaded. Try deactivating and reactivating the plugin.', 'bus-booking-manager'),
                'dashicons-warning'
            );
            return;
        }

        $settings->plugin_page();
    }

    public function render_quick_setup()
    {
        $quick = WBTM_Quick_Setup::instance();

        if (!$quick || !method_exists($quick, 'quick_setup')) {
            self::empty_state(
                __('Quick Setup is unavailable', 'bus-booking-manager'),
                __('WooCommerce must be active before the guided setup can run.', 'bus-booking-manager'),
                'dashicons-warning'
            );
            return;
        }

        $quick->quick_setup();
    }

    public function render_go_pro()
    {
        $settings = class_exists('MAGE_WBBM_Setting_Controls') ? MAGE_WBBM_Setting_Controls::instance() : null;

        if ($settings && method_exists($settings, 'wbbm_go_pro_page')) {
            $settings->wbbm_go_pro_page();
            return;
        }

        self::empty_state(__('Upgrade information is unavailable', 'bus-booking-manager'), '', 'dashicons-star-filled');
    }
}

// Boot the settings hub and the plugin-wide admin theme layer.
add_action('plugins_loaded', function () {
    if (class_exists('WBBM_Settings_Hub')) {
        ( new WBBM_Settings_Hub() )->boot();
    }
    if (class_exists('WBBM_Admin_Hub')) {
        WBBM_Admin_Hub::boot_theme();
    }
}, 20);
