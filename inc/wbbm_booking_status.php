<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Booking status labels shared by the Bookings screens.
 *
 * These moved here with the Passenger List when the Bookings page came across
 * from the PRO plugin. PRO still declares the same two functions for its own
 * screens, and either plugin may load first, so both sides are wrapped in
 * function_exists() -- without that, having both active is a fatal redeclare.
 */

if (!function_exists('wbbm_ticket_status_name')) {
    function wbbm_ticket_status_name($id)
    {
        if ($id == 1) {
            return "<span class='wbtm-ticket-hold'>Processing</span>";
        } elseif ($id == 2) {
            return "<span class='wbtm-ticket-confirm'>Completed</span>";
        } elseif ($id == 3) {
            return "<span class='wbtm-ticket-cancelled'>Pending</span>";
        } elseif ($id == 4) {
            return "<span class='wbtm-ticket-cancelled'>Refunded</span>";
        } elseif ($id == 5) {
            return "<span class='wbtm-ticket-cancelled'>Cancelled</span>";
        } elseif ($id == 6) {
            return "<span class='wbtm-ticket-cancelled'>Hold</span>";
        } elseif ($id == 'partially-paid') {
            return "<span class='wbtm-ticket-cancelled'>Partially Paid</span>";
        } elseif ($id == 99) {
            return "<span class='wbtm-ticket-cancelled'>Removed</span>";
        } else {
            return ucfirst($id);
        }
    }
}

if (!function_exists('wbbm_checkin_status_name')) {
    function wbbm_checkin_status_name($id)
    {
        if ($id == 0) {
            return "<span class='wbtm-ticket-hold'>No</span>";
        }
        if ($id == 2) {
            return "<span class='wbtm-ticket-confirm'>Yes</span>";
        }
    }
}
