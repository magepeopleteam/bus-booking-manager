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
    /**
     * Boarding check-in state of a booking, as a badge.
     *
     * PRO writes _wbbm_ticket_status = 2 when a ticket is scanned at the
     * door. A booking that has never been scanned has no meta at all, and
     * under PHP 8 an empty string no longer compares equal to 0 -- which
     * used to leave this returning nothing and the column simply blank.
     * Anything that is not an explicit 2 is "not checked in yet".
     *
     * @param mixed $id A _wbbm_ticket_status value.
     * @return string
     */
    function wbbm_checkin_status_name($id)
    {
        if (2 == $id) {
            return "<span class='wbtm-ticket-confirm'>" . esc_html__('Yes', 'bus-booking-manager') . "</span>";
        }

        return "<span class='wbtm-ticket-hold'>" . esc_html__('No', 'bus-booking-manager') . "</span>";
    }
}

/**
 * How a booking was paid, as a label and a badge class.
 *
 * Both Bookings screens show this now -- the Bookings list in its own Payment
 * column, the Passenger List above the ticket status -- so the mapping lives
 * here rather than being copied into each. Guarded like its neighbours above:
 * PRO may declare the same helper and either plugin may load first.
 *
 * @param string $method A _wbbm_payment_method value.
 * @return array{label: string, class: string}
 */
if (!function_exists('wbbm_payment_method_meta')) {
    function wbbm_payment_method_meta($method)
    {
        $method = strtolower(trim((string) $method));

        $map = array(
            'woocommerce' => array('label' => __('WooCommerce', 'bus-booking-manager'), 'class' => 'wbbm-pay-wc'),
            'offline'     => array('label' => __('Pay Offline', 'bus-booking-manager'), 'class' => 'wbbm-pay-offline'),
            'stripe'      => array('label' => __('Card (Stripe)', 'bus-booking-manager'), 'class' => 'wbbm-pay-card'),
            'paypal'      => array('label' => __('PayPal', 'bus-booking-manager'), 'class' => 'wbbm-pay-card'),
        );

        if (isset($map[$method])) {
            return $map[$method];
        }

        // Bookings written before the payment method was recorded have no
        // value at all. They predate the Custom Payment Method gateways, so
        // WooCommerce is what they were -- but say so as a guess, not a fact.
        if ('' === $method) {
            return array('label' => __('WooCommerce', 'bus-booking-manager'), 'class' => 'wbbm-pay-wc');
        }

        return array('label' => ucfirst($method), 'class' => 'wbbm-pay-offline');
    }
}
