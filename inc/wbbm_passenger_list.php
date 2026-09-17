<?php

if (!defined('ABSPATH')) { exit; }

/*
 * The passenger-list screen this file used to render was superseded by
 * AdminPassengerListClass. Its menu registration and add_submenu_page call
 * were already commented out, so wbbm_passenger_list() had been unreachable
 * for some time while still carrying $_GET['id'] straight into UPDATE
 * statements with no nonce or capability check. It is removed here.
 *
 * wbbm_pagination() below is still live: AdminPassengerListClass calls it.
 */
/*
* $current_page = Current Page
* $pages = Total page number
* return all button link
*/
if (!function_exists('wbbm_pagination')) :
function wbbm_pagination($current_page, $pages)
{
    /*
     * bus_id and j_date used to be concatenated into the href unescaped, so a
     * crafted value closed the attribute and injected markup. Build the URL
     * with add_query_arg and escape it.
     */
    $current_page = max(1, (int) $current_page);
    $pages        = max(1, (int) $pages);

    $args = array('post_type' => 'wbbm_bus', 'page' => 'passenger_list');

    $bus_id = isset($_GET['bus_id']) ? sanitize_text_field(wp_unslash($_GET['bus_id'])) : '';
    if ($bus_id !== '') {
        $parts = explode('-', $bus_id);
        $args['bus_id'] = absint($parts[0]);
    }

    $j_date = isset($_GET['j_date']) ? sanitize_text_field(wp_unslash($_GET['j_date'])) : '';
    if ($j_date !== '') {
        $args['j_date'] = $j_date;
    }

    $link = function ($paged) use ($args) {
        return esc_url(add_query_arg(array_merge($args, array('paged' => (int) $paged)), admin_url('edit.php')));
    };

    if ($current_page > 1) {
        $prevlink = '<a class="mage_paginate_link" href="' . $link(1) . '" title="' . esc_attr__('First page', 'bus-booking-manager') . '">&laquo;</a> '
                  . '<a class="mage_paginate_link" href="' . $link($current_page - 1) . '" title="' . esc_attr__('Previous page', 'bus-booking-manager') . '">&lsaquo;</a>';
    } else {
        $prevlink = '<span class="disabled">&laquo;</span> <span class="disabled">&lsaquo;</span>';
    }

    if ($current_page < $pages) {
        $nextlink = '<a class="mage_paginate_link" href="' . $link($current_page + 1) . '" title="' . esc_attr__('Next page', 'bus-booking-manager') . '">&rsaquo;</a> '
                  . '<a class="mage_paginate_link" href="' . $link($pages) . '" title="' . esc_attr__('Last page', 'bus-booking-manager') . '">&raquo;</a>';
    } else {
        $nextlink = '<span class="disabled">&rsaquo;</span> <span class="disabled">&raquo;</span>';
    }

    return '<div class="mage-pagination"><p>' . $prevlink
         . ' ' . esc_html__('Page', 'bus-booking-manager') . ' ' . (int) $current_page
         . ' ' . esc_html__('of', 'bus-booking-manager') . ' ' . (int) $pages . ' '
         . $nextlink . ' </p></div>';
}
endif;
