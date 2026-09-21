<?php

if (! defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

/**
 * Admin menu icon for the Bus Services post type.
 *
 * Dashicons has no bus glyph -- the bus in the plugin's own screens comes
 * from Font Awesome, which the admin menu cannot use -- so the icon ships
 * as an inline SVG. It is drawn in #a7aaad, WordPress's resting menu-icon
 * grey, so it matches its neighbours even if the tinting rule below never
 * applies.
 *
 * @return string A data URI WordPress renders as .wp-menu-image.svg.
 */
function wbbm_bus_menu_icon()
{
    return 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMCAyMCI+PHBhdGggZmlsbD0iI2E3YWFhZCIgZD0iTTUgMWgxMGEzIDMgMCAwIDEgMyAzdjguNWEyLjUgMi41IDAgMCAxLTEuNSAyLjI5VjE3YTEgMSAwIDAgMS0xIDFoLTFhMSAxIDAgMCAxLTEtMXYtMUg2LjV2MWExIDEgMCAwIDEtMSAxaC0xYTEgMSAwIDAgMS0xLTF2LTIuMjFBMi41IDIuNSAwIDAgMSAyIDEyLjVWNGEzIDMgMCAwIDEgMy0zWm0wIDJhMSAxIDAgMCAwLTEgMXYxaDEyVjRhMSAxIDAgMCAwLTEtMUg1Wk00IDd2M2g1LjI1VjdINFptNi43NSAwdjNIMTZWN2gtNS4yNVpNNS43NSAxMS41YTEuMjUgMS4yNSAwIDEgMCAwIDIuNSAxLjI1IDEuMjUgMCAwIDAgMC0yLjVabTguNSAwYTEuMjUgMS4yNSAwIDEgMCAwIDIuNSAxLjI1IDEuMjUgMCAwIDAgMC0yLjVaIi8+PC9zdmc+';
}

/*
 * Make the SVG above follow the admin colour scheme.
 *
 * WordPress paints a data-URI menu icon as a plain background image and never
 * recolours it, so on its own it would stay grey while every neighbouring
 * dashicon turns white on hover and when the menu is open. Re-painting it as a
 * mask lets it take the anchor's colour instead. The inline background-image
 * has to be cleared with !important because WordPress sets it on the element.
 * Browsers without mask support simply keep the grey bitmap, which is why the
 * SVG is drawn in #a7aaad to begin with.
 */
add_action('admin_head', 'wbbm_bus_menu_icon_style');
function wbbm_bus_menu_icon_style()
{
    /*
     * The URI is printed raw on purpose. esc_url() drops it entirely --
     * "data" is not in wp_allowed_protocols() -- which left mask:url("")
     * and, with the background-image already cleared below, no icon at all.
     * The value comes from wbbm_bus_menu_icon(), a fixed base64 string this
     * file builds itself, so there is no untrusted input to escape here.
     */
    $icon = wbbm_bus_menu_icon();
    if (0 !== strpos($icon, 'data:image/svg+xml;base64,')) {
        return;
    }
    ?>
    <style id="wbbm-bus-menu-icon">
        @supports ((-webkit-mask-image: none) or (mask-image: none)) {
            #adminmenu #menu-posts-wbbm_bus .wp-menu-image.svg {
                background-image: none !important;
                background-color: currentColor;
                -webkit-mask: url("<?php echo $icon; ?>") no-repeat center / 20px auto;
                mask: url("<?php echo $icon; ?>") no-repeat center / 20px auto;
            }
        }
    </style>
    <?php
}


// Create MKB CPT
function wbbm_bus_cpt()
{

    // Get the custom post type label and slug from options
    $cpt_label = sanitize_text_field(wbbm_get_option('wbbm_cpt_label', 'wbbm_general_setting_sec', __('Bus', 'bus-booking-manager')));
    $cpt_slug = sanitize_title(wbbm_get_option('wbbm_cpt_slug', 'wbbm_general_setting_sec', __('bus', 'bus-booking-manager')));

    // Get general settings
    $general_setting = get_option('wbbm_general_setting_sec') ? maybe_unserialize(get_option('wbbm_general_setting_sec')) : array();

    // Check Gutenberg editor setting
    $editor = isset($general_setting['wbbm_gutenbug_switch']) && $general_setting['wbbm_gutenbug_switch'] === 'on';

    // Labels for the custom post type
    $labels = array(
        'name'                  => sprintf(
            /* translators: %s: custom post type label */
            _x('%s post type general name', 'Post type general name for translators', 'bus-booking-manager'),
            esc_html($cpt_label)
        ),
        'singular_name'         => sprintf(
            /* translators: %s: custom post type label */
            _x('%s post type singular name', 'Post type general name for translators', 'bus-booking-manager'),
            esc_html($cpt_label)
        ),
        'menu_name'             => esc_html($cpt_label),
        'name_admin_bar'        => esc_html($cpt_label),
    );

    // Arguments for the custom post type
    $args = array(
        'public'                => true,
        'labels'                => $labels,
        'menu_icon'             => wbbm_bus_menu_icon(),
        'show_in_rest'          => $editor,
        'supports'              => array('title', 'editor', 'thumbnail'),
        'rewrite'               => array('slug' => $cpt_slug),
    );

    // Register the custom post type
    register_post_type('wbbm_bus', $args);

    // Register Booking CPT
    $booking_labels = array(
        'name'                  => _x('Bus Bookings', 'Post type general name', 'bus-booking-manager'),
        'singular_name'         => _x('Booking', 'Post type singular name', 'bus-booking-manager'),
        'menu_name'             => _x('Bus Bookings', 'Admin Menu text', 'bus-booking-manager'),
        'name_admin_bar'        => _x('Booking', 'Add New on Toolbar', 'bus-booking-manager'),
    );

    $booking_args = array(
        'public'             => false,
        'show_ui'            => false,
        'show_in_menu'       => false,
        'query_var'          => false,
        'rewrite'            => array('slug' => 'wbbm-booking'),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => null,
        'menu_icon'          => 'dashicons-book',
        'supports'           => array('title', 'custom-fields'),
    );

    register_post_type('wbbm_booking', $booking_args);
}
add_action('init', 'wbbm_bus_cpt');

/**
 * Remove "Add New" submenu from the "Bus" menu
 */
add_action('admin_menu', function () {
    remove_submenu_page('edit.php?post_type=wbbm_bus', 'post-new.php?post_type=wbbm_bus');
}, 999);
