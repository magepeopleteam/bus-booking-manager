<?php

if (!defined('ABSPATH')) {
    die;
}

/**
 * Offline booking handler -- the non-WooCommerce equivalent of
 * wbbm_add_passenger_to_db() (woocommerce-bus.php:1394).
 *
 * Reuses the exact same wbbm_booking CPT storage the WooCommerce path
 * already writes to via wbbm_add_passenger() (woocommerce-bus.php), one
 * post per seat -- matching wbbm_add_passenger_to_db()'s own per-passenger
 * loop -- so seat-availability counting (wbbm_get_available_seat_cpt())
 * and everything else that reads wbbm_booking posts keeps working
 * identically for both payment methods.
 *
 * Two entry points share the one processing function below:
 *  - wbbm_ajax_offline_book_now(): the primary path (JS enabled) -- the
 *    "Confirm Booking" modal button in inc/clean/layout/book-now-area.php
 *    posts here so the modal can stay open and show the result itself
 *    instead of navigating away.
 *  - wbbm_handle_offline_book_now(): a plain template_redirect / full-page
 *    POST-back fallback for when JS is unavailable, submitted by the same
 *    hidden button WooCommerce's own add-to-cart flow uses that pattern
 *    for. Redirects (PRG) to a confirmation banner on the bus page.
 */
add_action('wp_ajax_wbbm_offline_book_now', 'wbbm_ajax_offline_book_now');
add_action('wp_ajax_nopriv_wbbm_offline_book_now', 'wbbm_ajax_offline_book_now');
add_action('template_redirect', 'wbbm_handle_offline_book_now');

/**
 * Which of the three Custom Payment Method gateways (Offline/Stripe/
 * PayPal) are switched on right now -- gated on the enable toggle alone,
 * deliberately NOT on whether Stripe/PayPal's API keys are filled in yet.
 * That's so the admin can preview/test the picker (and the flow up to
 * "Confirm") before pasting in real credentials; attempting to actually
 * pay with an unconfigured gateway still fails gracefully and safely --
 * wbbm_ajax_create_stripe_session()/wbbm_ajax_create_paypal_order() (in
 * inc/wbbm-custom-gateway-checkout.php) each re-check their own keys
 * before creating anything, and return a friendly "not available right
 * now" error if they're missing, rather than reaching Stripe/PayPal.
 *
 * WBBM_Settings_Hub::is_gateway_configured() is the separate, STRICTER
 * check (enabled AND has keys) used for the admin-side "ENABLED" vs
 * "NEEDS API KEYS" pill -- that distinction is deliberately admin-only
 * information, not a gate on what customers see.
 *
 * Used by inc/clean/layout/book-now-area.php to decide whether to show a
 * single button (today's default: Offline only) or a method-selector.
 *
 * @return array<string, array{label: string}> Keyed by gateway slug, in a
 *                                              fixed display order.
 */
function wbbm_get_active_custom_gateways()
{
    $settings = get_option('wbbm_payment_settings');
    $settings = is_array($settings) ? $settings : array();
    $active = array();

    $offline_enabled = !array_key_exists('offline_enabled', $settings) || !empty($settings['offline_enabled']);
    if ($offline_enabled) {
        $offline_label = isset($settings['offline_label']) ? trim($settings['offline_label']) : '';
        $active['offline'] = array('label' => '' !== $offline_label ? $offline_label : __('Pay Offline', 'bus-booking-manager'));
    }

    if (!empty($settings['stripe_enabled'])) {
        $active['stripe'] = array('label' => __('Card (Stripe)', 'bus-booking-manager'));
    }

    if (!empty($settings['paypal_enabled'])) {
        $active['paypal'] = array('label' => __('PayPal', 'bus-booking-manager'));
    }

    return $active;
}

/**
 * Validates a booking request and computes authoritative server-side
 * pricing/tax -- everything through the point where a booking WOULD be
 * recorded, but doesn't insert anything yet. Shared by all three Custom
 * Payment Method gateways (Offline, and now Stripe/PayPal in
 * inc/wbbm-custom-gateway-checkout.php) so the validation/pricing logic
 * can't drift between them; each gateway supplies its own finalize
 * behaviour via wbbm_record_pending_booking().
 *
 * Takes a $_POST-shaped array so all entry points can hand it the same
 * super-global (or a normalized copy) without duplicating any logic.
 *
 * @return array{ok: bool, data?: array, error?: string}
 */
function wbbm_validate_offline_booking_request($post)
{
    if (
        !isset($post['wbbm_offline_book_now_nonce'])
        || !wp_verify_nonce(sanitize_text_field(wp_unslash($post['wbbm_offline_book_now_nonce'])), 'wbbm_offline_book_now')
    ) {
        return array('ok' => false, 'error' => 'invalid_nonce');
    }

    $bus_id = isset($post['bus_id']) ? absint($post['bus_id']) : 0;
    $bus = $bus_id ? get_post($bus_id) : null;

    if (!$bus || 'wbbm_bus' !== $bus->post_type) {
        return array('ok' => false, 'error' => 'invalid_bus');
    }

    // Never let a WooCommerce-mode bus be booked through the Custom
    // Payment Method path, even if someone crafts the request directly.
    if (!class_exists('MP_Global_Function') || MP_Global_Function::wbbm_bus_wants_wc($bus_id)) {
        return array('ok' => false, 'error' => 'wrong_payment_mode');
    }

    $name = isset($post['wbbm_offline_name']) ? sanitize_text_field(wp_unslash($post['wbbm_offline_name'])) : '';
    $phone = isset($post['wbbm_offline_phone']) ? sanitize_text_field(wp_unslash($post['wbbm_offline_phone'])) : '';
    $email = isset($post['wbbm_offline_email']) ? sanitize_email(wp_unslash($post['wbbm_offline_email'])) : '';

    if ('' === $name || '' === $phone || !is_email($email)) {
        return array('ok' => false, 'error' => 'missing_contact_info');
    }

    $start = isset($post['start_stops']) ? sanitize_text_field(wp_unslash($post['start_stops'])) : '';
    $end = isset($post['end_stops']) ? sanitize_text_field(wp_unslash($post['end_stops'])) : '';
    $journey_date = isset($post['journey_date']) ? sanitize_text_field(wp_unslash($post['journey_date'])) : '';
    $user_start_time = isset($post['user_start_time']) ? sanitize_text_field(wp_unslash($post['user_start_time'])) : '';
    $bus_start_time = isset($post['bus_start_time']) ? sanitize_text_field(wp_unslash($post['bus_start_time'])) : '';
    $pickpoint = (!empty($post['mage_pickpoint'])) ? sanitize_text_field(wp_unslash($post['mage_pickpoint'])) : 'n_a';

    if (!$start || !$end || !$journey_date) {
        return array('ok' => false, 'error' => 'missing_route_fields');
    }

    $adult = isset($post['adult_quantity']) ? absint($post['adult_quantity']) : 0;
    $child = isset($post['child_quantity']) ? absint($post['child_quantity']) : 0;
    $infant = isset($post['infant_quantity']) ? absint($post['infant_quantity']) : 0;
    $entire = !empty($post['entire_quantity']) ? 1 : 0;

    $total_seat_requested = $entire ? (int) get_post_meta($bus_id, 'wbbm_total_seat', true) : ($adult + $child + $infant);

    if ($total_seat_requested < 1) {
        return array('ok' => false, 'error' => 'no_seats_selected');
    }

    // Re-validate availability server-side -- never trust the client alone.
    $available = function_exists('wbbm_intermidiate_available_seat')
        ? wbbm_intermidiate_available_seat($start, $end, $journey_date, $bus_id)
        : 0;

    if ($total_seat_requested > $available) {
        return array('ok' => false, 'error' => 'sold_out');
    }

    // Authoritative pricing, computed server-side with the same
    // mage_seat_price() primitive FilterClass::wbbm_add_custom_fields_text_to_cart_item()
    // uses for the WooCommerce path (inc/FilterClass.php) -- minus the
    // round-trip discount, which is out of scope for this offline v1 flow.
    $adult_per_price = ($adult && function_exists('mage_seat_price')) ? (float) mage_seat_price($bus_id, $start, $end, 'adult') : 0.0;
    $child_per_price = ($child && function_exists('mage_seat_price')) ? (float) mage_seat_price($bus_id, $start, $end, 'child') : 0.0;
    $infant_per_price = ($infant && function_exists('mage_seat_price')) ? (float) mage_seat_price($bus_id, $start, $end, 'infant') : 0.0;
    $entire_per_price = ($entire && function_exists('mage_seat_price')) ? (float) mage_seat_price($bus_id, $start, $end, 'entire') : 0.0;

    $subtotal = $entire
        ? $entire_per_price
        : (($adult * $adult_per_price) + ($child * $child_per_price) + ($infant * $infant_per_price));

    // The one new piece Tax needed to exist at all: a per-bus offline tax
    // rate (set on the Advanced step's Tax section for Custom Payment
    // Method buses -- see BusEditPageClass::render_step_5_left()).
    $tax_rate = (float) get_post_meta($bus_id, '_wbbm_offline_tax_rate', true);
    $tax_amount = round($subtotal * ($tax_rate / 100), 2);
    $total_price = round($subtotal + $tax_amount, 2);

    $next_stops = function_exists('wbbm_get_all_stops_after_this')
        ? maybe_serialize(wbbm_get_all_stops_after_this($bus_id, $start, $end))
        : '';

    return array(
        'ok'   => true,
        'data' => array(
            'bus_id'           => $bus_id,
            'start'            => $start,
            'end'              => $end,
            'next_stops'       => $next_stops,
            'journey_date'     => $journey_date,
            'user_start_time'  => $user_start_time,
            'bus_start_time'   => $bus_start_time,
            'pickpoint'        => $pickpoint,
            'name'             => $name,
            'phone'            => $phone,
            'email'            => $email,
            'adult'            => $adult,
            'adult_per_price'  => $adult_per_price,
            'child'            => $child,
            'child_per_price'  => $child_per_price,
            'infant'           => $infant,
            'infant_per_price' => $infant_per_price,
            'entire'           => $entire,
            'entire_per_price' => $entire_per_price,
            'item_quantity'    => $entire ? 1 : $total_seat_requested,
            'subtotal'         => $subtotal,
            'tax_rate'         => $tax_rate,
            'tax_amount'       => $tax_amount,
            'total_price'      => $total_price,
        ),
    );
}

/**
 * Records a validated booking request (the $data half of
 * wbbm_validate_offline_booking_request()'s return) as one wbbm_booking
 * post per seat -- matches wbbm_add_passenger_to_db()'s own per-passenger
 * loop (woocommerce-bus.php:1493), which is what
 * wbbm_get_available_seat_cpt()'s seat-counting query expects: it counts
 * matching posts, with a single entire-bus post already understood to
 * consume every seat.
 *
 * $gateway is one of 'offline'|'stripe'|'paypal' -- stamped onto every
 * inserted post as _wbbm_payment_gateway so the return handlers (and the
 * admin bookings list) know which flow a pending booking belongs to.
 * $payment_status lets Stripe/PayPal record as 'pending' until their
 * return trip confirms payment, while Offline still records straight to
 * 'pending' meaning "awaiting manual confirmation" (unchanged behaviour).
 *
 * $order_id / $leg_index / $leg_count exist for the multi-leg path in
 * wbbm_record_pending_booking_legs(): a return trip is ONE booking made of
 * two legs, so both legs must land under the same order id rather than each
 * minting its own. Left at their defaults this behaves exactly as it always
 * did -- a fresh order id, a single leg numbered 0 of 1.
 *
 * @return array{ok: bool, ids?: int[], summary?: array, error?: string}
 */
function wbbm_record_pending_booking($data, $gateway = 'offline', $payment_status = 'pending', $order_id = null, $leg_index = 0, $leg_count = 1)
{
    // A negative, timestamp-derived placeholder keeps Custom Payment
    // Method bookings trivially distinguishable from real WooCommerce
    // order ids (always positive post ids) in anything that later reads
    // _wbbm_order_id.
    $order_id = (null === $order_id) ? -time() : (int) $order_id;
    $item_quantity = $data['item_quantity'];
    $inserted_ids = array();

    /*
     * Which ticket type each seat is. The loop below inserts one post per
     * seat, filled adults first, then children, then infants -- the same
     * per-passenger typing wbbm_add_passenger_to_db() does on the
     * WooCommerce path (woocommerce-bus.php).
     *
     * This matters beyond labelling: the Passenger List prices a seat by
     * looking up _wbbm_per_{type}_price from _wbbm_user_type. This used to
     * pass the literal 'offline' here -- the payment method, which is the
     * LAST argument, not this one -- so every Custom Payment Method seat
     * looked for a "per_offline_price" that does not exist and was shown
     * as 0.00 under a defaulted "Adult" label.
     */
    $seat_types = $data['entire']
        ? array('entire')
        : array_merge(
            array_fill(0, (int) $data['adult'], 'adult'),
            array_fill(0, (int) $data['child'], 'child'),
            array_fill(0, (int) $data['infant'], 'infant')
        );

    for ($i = 0; $i < $item_quantity; $i++) {
        $post_id = wbbm_add_passenger(
            $order_id,
            $data['bus_id'],
            get_current_user_id(),
            $data['start'],
            $data['next_stops'],
            $data['end'],
            $data['name'],
            $data['email'],
            $data['phone'],
            '',   // user_gender -- not collected by the Custom Payment Method v1 form
            '',   // user_dob
            '',   // nationality
            '',   // flight_arrival_no
            '',   // flight_departure_no
            0,    // extra_bag_quantity
            '',   // user_address
            isset($seat_types[$i]) ? $seat_types[$i] : 'adult',
            $data['bus_start_time'],
            $data['user_start_time'],
            $data['adult'],
            $data['adult_per_price'],
            $data['child'],
            $data['child_per_price'],
            $data['infant'],
            $data['infant_per_price'],
            $data['entire'],
            $data['entire_per_price'],
            $data['total_price'],
            $item_quantity,
            $data['journey_date'],
            current_time('Y-m-d h:i:s'),
            $data['pickpoint'],
            1, // $status: occupies a seat immediately, matches the WC "processing" equivalent
            $gateway,
            $payment_status
        );

        if ($post_id && !is_wp_error($post_id)) {
            $inserted_ids[] = $post_id;
            update_post_meta($post_id, '_wbbm_tax_amount', $data['tax_amount']);
            update_post_meta($post_id, '_wbbm_tax_rate', $data['tax_rate']);
            // Which leg of the booking this seat belongs to. Anything reading
            // these posts back -- the admin list's grouping, the Stripe/PayPal
            // return-trip summary rebuild -- needs this to tell "two legs of
            // one return trip" apart from "two seats on one bus", since both
            // look identical through _wbbm_order_id alone.
            update_post_meta($post_id, '_wbbm_leg_index', (int) $leg_index);
            update_post_meta($post_id, '_wbbm_leg_count', (int) $leg_count);
            // wbbm_add_passenger() already stamps _wbbm_order_id and
            // _wbbm_payment_method ($gateway, passed above) -- this is an
            // explicit alias so a reader looking for "which gateway" finds
            // one obviously-named meta key without needing to know that
            // _wbbm_payment_method doubles as it.
            update_post_meta($post_id, '_wbbm_payment_gateway', $gateway);
        }
    }

    if (empty($inserted_ids)) {
        return array('ok' => false, 'error' => 'insert_failed');
    }

    $summary = array(
        'reference'    => absint(abs($order_id)),
        'order_id'     => $order_id,
        'bus_name'     => get_the_title($data['bus_id']),
        'start'        => $data['start'],
        'end'          => $data['end'],
        'journey_date' => $data['journey_date'],
        'adult'        => $data['adult'],
        'child'        => $data['child'],
        'infant'       => $data['infant'],
        'entire'       => $data['entire'],
        'seat_count'   => $item_quantity,
        'name'         => $data['name'],
        'phone'        => $data['phone'],
        'email'        => $data['email'],
        'subtotal'     => $data['subtotal'],
        'tax_rate'     => $data['tax_rate'],
        'tax_amount'   => $data['tax_amount'],
        'total_price'  => $data['total_price'],
        'gateway'      => $gateway,
    );

    /**
     * Extension seam, mirroring how the WooCommerce path has its own set
     * of hooks to react to a completed order. Fired once per booking
     * regardless of gateway -- Stripe/PayPal fire it again indirectly via
     * wbbm_offline_booking_confirmed when payment is actually verified.
     */
    do_action('wbbm_offline_booking_created', $inserted_ids, $summary);

    return array('ok' => true, 'ids' => $inserted_ids, 'summary' => $summary);
}

/* ----------------------------------------------------------------------
 * Multi-leg bookings.
 *
 * A return trip is one booking made of two legs on two different buses.
 * The single-leg functions above stay exactly as they were -- these wrap
 * them, so a one-way booking still takes the identical code path and the
 * per-leg validation and pricing rules can't drift between one leg and two.
 * -------------------------------------------------------------------- */

/** The fields that describe one leg; everything else on the request is shared. */
function wbbm_booking_leg_fields()
{
    return array(
        'bus_id',
        'journey_date',
        'start_stops',
        'end_stops',
        'user_start_time',
        'bus_start_time',
        'mage_pickpoint',
        'adult_quantity',
        'child_quantity',
        'infant_quantity',
        'entire_quantity',
    );
}

/**
 * Splits a request into one POST-shaped array per leg, each carrying the
 * shared fields (nonce, contact details) so it can be handed straight to
 * wbbm_validate_offline_booking_request() untouched.
 *
 * A request with no `legs` key is a single leg described by the flat POST --
 * which is what the no-JS fallback still submits, and what every request
 * looked like before return trips were bookable together.
 *
 * @return array<int, array> Never empty.
 */
function wbbm_extract_booking_legs($post)
{
    $leg_fields = wbbm_booking_leg_fields();
    $shared = $post;
    foreach ($leg_fields as $field) {
        unset($shared[$field]);
    }

    if (empty($post['legs']) || !is_array($post['legs'])) {
        return array($post);
    }

    /**
     * The UI only ever posts an outbound and a return. The cap is here
     * because `legs` arrives from the browser: without it a crafted request
     * could ask the server to price and insert an unbounded number of legs
     * in one go. Filterable so a future multi-city flow can raise it
     * deliberately rather than by accident.
     */
    $max_legs = (int) apply_filters('wbbm_max_booking_legs', 2);

    $legs = array();
    foreach ($post['legs'] as $leg) {
        if (!is_array($leg)) {
            continue;
        }

        if (count($legs) >= $max_legs) {
            break;
        }

        $normalized = $shared;
        foreach ($leg_fields as $field) {
            if (isset($leg[$field])) {
                $normalized[$field] = $leg[$field];
            }
        }
        $legs[] = $normalized;
    }

    return $legs ? $legs : array($post);
}

/**
 * Validates every leg before anything is recorded -- all or nothing. If one
 * leg is sold out the customer gets told which, and no half-booked return
 * trip is left behind for the operator to unpick by hand.
 *
 * @return array{ok: bool, legs?: array[], totals?: array, error?: string, leg?: int}
 */
function wbbm_validate_booking_legs($post)
{
    $legs = array();
    $claimed = array();
    $subtotal = 0.0;
    $tax_amount = 0.0;
    $total_price = 0.0;

    foreach (wbbm_extract_booking_legs($post) as $index => $leg_post) {
        $validated = wbbm_validate_offline_booking_request($leg_post);

        if (empty($validated['ok'])) {
            return array(
                'ok'    => false,
                'error' => isset($validated['error']) ? $validated['error'] : 'invalid_request',
                'leg'   => $index,
            );
        }

        $data = $validated['data'];

        /*
         * Each leg's availability was checked against the database as it
         * stands right now, which is correct for two legs on two buses but
         * would happily sell the same seats twice if both legs are on the
         * same bus on the same day. Nothing has been inserted yet, so the
         * second check would still see the first leg's seats as free.
         *
         * Subtracting what earlier legs already claimed closes that. It is
         * deliberately conservative: two legs on one bus and date are only
         * genuinely in conflict when their segments overlap, and this
         * refuses whenever the combined count exceeds the tighter of the
         * two availabilities. Refusing a rare legitimate booking is the
         * right side to err on against overselling a real seat.
         */
        $key = $data['bus_id'] . '|' . $data['journey_date'];
        $requested = $data['entire']
            ? (int) get_post_meta($data['bus_id'], 'wbbm_total_seat', true)
            : ($data['adult'] + $data['child'] + $data['infant']);
        $prior = isset($claimed[$key]) ? $claimed[$key] : 0;

        if ($prior > 0) {
            $available = function_exists('wbbm_intermidiate_available_seat')
                ? wbbm_intermidiate_available_seat($data['start'], $data['end'], $data['journey_date'], $data['bus_id'])
                : 0;

            if (($requested + $prior) > $available) {
                return array('ok' => false, 'error' => 'sold_out', 'leg' => $index);
            }
        }

        $claimed[$key] = $prior + $requested;

        $subtotal += (float) $data['subtotal'];
        $tax_amount += (float) $data['tax_amount'];
        $total_price += (float) $data['total_price'];

        $legs[] = $data;
    }

    if (empty($legs)) {
        return array('ok' => false, 'error' => 'invalid_request');
    }

    return array(
        'ok'     => true,
        'legs'   => $legs,
        'totals' => array(
            'subtotal'    => round($subtotal, 2),
            'tax_amount'  => round($tax_amount, 2),
            'total_price' => round($total_price, 2),
        ),
    );
}

/**
 * Records every validated leg under one order id, so the confirmation and
 * the admin Bookings list both see a single booking covering both buses.
 *
 * If any leg fails to insert, every leg already inserted is released again
 * (wbbm_cgw_release_booking(), inc/wbbm-custom-gateway-checkout.php) rather
 * than leaving a one-way booking the customer never asked for. Validation
 * has already passed at this point, so this only fires on a genuine
 * database failure -- but a half-written return trip is exactly the mess
 * that is expensive to find later.
 *
 * @return array{ok: bool, ids?: int[], summary?: array, error?: string}
 */
function wbbm_record_pending_booking_legs($legs, $totals, $gateway = 'offline', $payment_status = 'pending')
{
    $order_id = -time();
    $leg_count = count($legs);
    $all_ids = array();
    $leg_summaries = array();

    foreach ($legs as $index => $data) {
        $recorded = wbbm_record_pending_booking($data, $gateway, $payment_status, $order_id, $index, $leg_count);

        if (empty($recorded['ok'])) {
            if ($all_ids && function_exists('wbbm_cgw_release_booking')) {
                wbbm_cgw_release_booking($all_ids);
            }

            return array('ok' => false, 'error' => isset($recorded['error']) ? $recorded['error'] : 'insert_failed');
        }

        $all_ids = array_merge($all_ids, $recorded['ids']);
        $leg_summaries[] = $recorded['summary'];
    }

    // The whole-booking figures go on every post so anything reading one
    // seat back knows what the customer actually owes, without having to
    // re-query its siblings. _wbbm_total_price stays the LEG's total --
    // unchanged meaning, so single-leg readers elsewhere keep working.
    foreach ($all_ids as $post_id) {
        update_post_meta($post_id, '_wbbm_booking_subtotal', $totals['subtotal']);
        update_post_meta($post_id, '_wbbm_booking_tax', $totals['tax_amount']);
        update_post_meta($post_id, '_wbbm_booking_total', $totals['total_price']);
    }

    $first = $leg_summaries[0];

    // Leg 0 stays at the top level so every existing reader of this summary
    // -- the Stripe/PayPal descriptions, the no-JS confirmation banner --
    // keeps finding the fields it expects. The whole trip is in 'legs'.
    $summary = array_merge($first, array(
        'legs'        => $leg_summaries,
        'leg_count'   => $leg_count,
        'subtotal'    => $totals['subtotal'],
        'tax_amount'  => $totals['tax_amount'],
        'total_price' => $totals['total_price'],
        'seat_count'  => array_sum(wp_list_pluck($leg_summaries, 'seat_count')),
    ));

    do_action('wbbm_offline_booking_legs_created', $all_ids, $summary);

    return array('ok' => true, 'ids' => $all_ids, 'summary' => $summary);
}

/**
 * Thin wrapper kept for backward compatibility with this file's two
 * original callers (the AJAX handler and the no-JS fallback below) --
 * Offline payment still means "record as pending, site owner confirms by
 * hand", so validate + record is the whole flow, unlike Stripe/PayPal
 * which insert their own AJAX handlers in
 * inc/wbbm-custom-gateway-checkout.php around these same two functions.
 *
 * @return array{ok: bool, summary?: array, error?: string, leg?: int}
 */
function wbbm_process_offline_booking($post)
{
    $validated = wbbm_validate_booking_legs($post);
    if (empty($validated['ok'])) {
        return array(
            'ok'    => false,
            'error' => $validated['error'],
            'leg'   => isset($validated['leg']) ? $validated['leg'] : 0,
        );
    }

    $recorded = wbbm_record_pending_booking_legs($validated['legs'], $validated['totals'], 'offline', 'pending');
    if (empty($recorded['ok'])) {
        return array('ok' => false, 'error' => $recorded['error']);
    }

    return array('ok' => true, 'summary' => $recorded['summary']);
}

/**
 * AJAX entry point -- the modal in inc/clean/layout/book-now-area.php
 * posts here so it can render the result in place instead of the page
 * navigating away, per the "don't close the popup automatically, show the
 * booking details in the popup" request.
 */
function wbbm_ajax_offline_book_now()
{
    $result = wbbm_process_offline_booking($_POST); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce checked inside wbbm_process_offline_booking()

    if (!empty($result['ok'])) {
        wp_send_json_success($result['summary']);
    }

    // `leg` lets the modal say which leg of a return trip failed -- "the
    // return leg is sold out" is actionable in a way that a bare
    // "sold out" on a two-bus booking is not.
    wp_send_json_error(array(
        'code' => isset($result['error']) ? $result['error'] : 'unknown',
        'leg'  => isset($result['leg']) ? (int) $result['leg'] : 0,
    ));
}

/**
 * Full-page POST-back fallback for when JS is unavailable -- the hidden
 * submit button still exists for this reason, matching the WooCommerce
 * add-to-cart button's own always-a-real-<button> approach.
 */
function wbbm_handle_offline_book_now()
{
    if (!isset($_POST['wbbm_offline_book_now'])) {
        return;
    }

    $result = wbbm_process_offline_booking($_POST); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce checked inside wbbm_process_offline_booking()

    if (!empty($result['ok'])) {
        wbbm_redirect_offline_booking_success($result['summary']);
    }

    wbbm_redirect_offline_booking_error(isset($result['error']) ? $result['error'] : 'unknown');
}

function wbbm_current_url_without(array $args)
{
    $url = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : home_url('/');
    return remove_query_arg($args, $url);
}

/**
 * The confirmation page is a redirect (PRG, so refreshing never resubmits
 * the booking), which loses $_POST -- the booking summary is handed over
 * via a short-lived transient instead of cramming it all into the query
 * string, the same shape of problem WooCommerce solves with its own
 * order-received page reading the order back by id. Only used by the
 * no-JS fallback path -- the AJAX path returns the summary directly.
 */
function wbbm_redirect_offline_booking_success($summary)
{
    $token = wp_generate_password(20, false, false);
    set_transient('wbbm_offline_booking_' . $token, $summary, HOUR_IN_SECONDS);

    $url = add_query_arg('wbbm_offline_booked', $token, wbbm_current_url_without(array('wbbm_offline_error')));
    wp_safe_redirect($url);
    exit;
}

function wbbm_redirect_offline_booking_error($reason)
{
    $url = add_query_arg('wbbm_offline_error', sanitize_key($reason), wbbm_current_url_without(array('wbbm_offline_booked')));
    wp_safe_redirect($url);
    exit;
}
