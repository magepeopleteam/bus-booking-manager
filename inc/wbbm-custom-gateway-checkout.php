<?php

if (!defined('ABSPATH')) {
    die;
}

/**
 * Real payment processing for the two "Custom Payment Method" gateways
 * that aren't Offline: Stripe and PayPal.
 *
 * Stripe: card fields are collected IN-PAGE via Stripe Elements (a `card`
 * Element mounted into the booking modal) and confirmed with a
 * PaymentIntent -- the customer never leaves the site. Card data itself
 * still never touches this server: Stripe.js tokenizes it directly with
 * Stripe in the browser, this server only ever sees a PaymentIntent id.
 *
 * PayPal: still the hosted-redirect pattern -- create an order server-side,
 * send the customer to PayPal's own approval page, verify on their return
 * trip. PayPal doesn't offer an equivalent low-PCI-burden embeddable card
 * field the way Stripe does, so redirect remains the right shape for it.
 *
 * Shares validation/pricing with the Offline flow via
 * wbbm_validate_offline_booking_request() and shares booking storage via
 * wbbm_record_pending_booking() (both in inc/wbbm-offline-booking.php,
 * loaded before this file -- see the require order in woocommerce-bus.php)
 * so all three gateways can never disagree about what a booking costs or
 * how it's stored.
 *
 * No webhooks in this first pass -- Stripe's confirmCardPayment() result is
 * re-verified server-side (wbbm_ajax_confirm_stripe_payment()) before
 * anything is marked confirmed, and PayPal's return-URL capture call is
 * still the sole confirmation mechanism there. A customer who pays but
 * never completes the round trip (closes the tab mid-3-D-Secure, etc.)
 * leaves their booking `pending` exactly like an Offline booking waiting
 * on manual confirmation -- flagged as a follow-up hardening step, not
 * solved here.
 */
add_action('wp_ajax_wbbm_create_stripe_payment_intent', 'wbbm_ajax_create_stripe_payment_intent');
add_action('wp_ajax_nopriv_wbbm_create_stripe_payment_intent', 'wbbm_ajax_create_stripe_payment_intent');
add_action('wp_ajax_wbbm_confirm_stripe_payment', 'wbbm_ajax_confirm_stripe_payment');
add_action('wp_ajax_nopriv_wbbm_confirm_stripe_payment', 'wbbm_ajax_confirm_stripe_payment');
add_action('wp_ajax_wbbm_create_paypal_order', 'wbbm_ajax_create_paypal_order');
add_action('wp_ajax_nopriv_wbbm_create_paypal_order', 'wbbm_ajax_create_paypal_order');
add_action('template_redirect', 'wbbm_handle_paypal_return');

function wbbm_cgw_settings()
{
    $settings = get_option('wbbm_payment_settings');
    return is_array($settings) ? $settings : array();
}

/* ----------------------------------------------------------------------
 * Stripe -- thin wrapper over wp_remote_request(), no SDK (this plugin
 * has no Composer runtime dependency to hang one off).
 * -------------------------------------------------------------------- */

/**
 * @param string $method 'GET'|'POST'
 * @param string $path   e.g. 'payment_intents' or 'payment_intents/pi_123'
 * @param array  $args   form fields for POST (Stripe's API is form-encoded,
 *                        including nested arrays like payment_method_types[0])
 * @return array|WP_Error Decoded JSON body, or WP_Error on transport/API failure.
 */
function wbbm_stripe_request($method, $path, $args = array())
{
    $settings = wbbm_cgw_settings();
    $secret_key = isset($settings['stripe_secret_key']) ? trim($settings['stripe_secret_key']) : '';

    if ('' === $secret_key) {
        return new WP_Error('stripe_not_configured', __('Stripe secret key is not set.', 'bus-booking-manager'));
    }

    $url = 'https://api.stripe.com/v1/' . ltrim($path, '/');
    $request_args = array(
        'method'  => $method,
        'timeout' => 20,
        'headers' => array(
            'Authorization' => 'Bearer ' . $secret_key,
        ),
    );

    if ('GET' === strtoupper($method)) {
        if (!empty($args)) {
            $url = add_query_arg($args, $url);
        }
    } else {
        $request_args['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
        $request_args['body'] = http_build_query($args);
    }

    $response = wp_remote_request($url, $request_args);

    if (is_wp_error($response)) {
        return $response;
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if ($code < 200 || $code >= 300) {
        $message = isset($body['error']['message']) ? $body['error']['message'] : __('Stripe request failed.', 'bus-booking-manager');
        return new WP_Error('stripe_error', $message, $body);
    }

    return is_array($body) ? $body : array();
}

/* ----------------------------------------------------------------------
 * PayPal -- OAuth2 client-credentials token (cached), then Orders v2.
 * -------------------------------------------------------------------- */

function wbbm_paypal_base_url()
{
    $settings = wbbm_cgw_settings();
    $sandbox = !array_key_exists('paypal_sandbox_mode', $settings) || !empty($settings['paypal_sandbox_mode']);
    return $sandbox ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
}

/**
 * @return string|WP_Error Bearer token, or WP_Error on failure.
 */
function wbbm_paypal_get_access_token()
{
    $settings = wbbm_cgw_settings();
    $client_id = isset($settings['paypal_client_id']) ? trim($settings['paypal_client_id']) : '';
    $secret = isset($settings['paypal_secret']) ? trim($settings['paypal_secret']) : '';

    if ('' === $client_id || '' === $secret) {
        return new WP_Error('paypal_not_configured', __('PayPal Client ID/Secret is not set.', 'bus-booking-manager'));
    }

    // Cache key includes the base URL so flipping Sandbox mode never
    // serves a stale token from the other environment.
    $cache_key = 'wbbm_paypal_token_' . md5($client_id . '|' . wbbm_paypal_base_url());
    $cached = get_transient($cache_key);
    if ($cached) {
        return $cached;
    }

    $response = wp_remote_post(wbbm_paypal_base_url() . '/v1/oauth2/token', array(
        'timeout' => 20,
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode($client_id . ':' . $secret),
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ),
        'body' => array('grant_type' => 'client_credentials'),
    ));

    if (is_wp_error($response)) {
        return $response;
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if ($code < 200 || $code >= 300 || empty($body['access_token'])) {
        $message = isset($body['error_description']) ? $body['error_description'] : __('Could not authenticate with PayPal.', 'bus-booking-manager');
        return new WP_Error('paypal_auth_failed', $message, $body);
    }

    $expires_in = isset($body['expires_in']) ? (int) $body['expires_in'] : 300;
    // A minute of slack so the token never expires mid-request.
    set_transient($cache_key, $body['access_token'], max(60, $expires_in - 60));

    return $body['access_token'];
}

/**
 * @param string $method 'GET'|'POST'
 * @param string $path   e.g. 'v2/checkout/orders' or 'v2/checkout/orders/{id}/capture'
 * @param array  $args   request body, JSON-encoded
 * @return array|WP_Error Decoded JSON body, or WP_Error on transport/auth/API failure.
 */
function wbbm_paypal_request($method, $path, $args = array())
{
    $token = wbbm_paypal_get_access_token();
    if (is_wp_error($token)) {
        return $token;
    }

    $response = wp_remote_request(wbbm_paypal_base_url() . '/' . ltrim($path, '/'), array(
        'method'  => $method,
        'timeout' => 20,
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ),
        'body' => empty($args) ? null : wp_json_encode($args),
    ));

    if (is_wp_error($response)) {
        return $response;
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if ($code < 200 || $code >= 300) {
        $message = isset($body['message']) ? $body['message'] : __('PayPal request failed.', 'bus-booking-manager');
        return new WP_Error('paypal_error', $message, $body);
    }

    return is_array($body) ? $body : array();
}

/* ----------------------------------------------------------------------
 * Shared helpers: locating a Custom Payment Method booking's posts on the
 * return trip, and rebuilding the same $summary shape
 * wbbm_record_pending_booking() produced, purely from post meta -- the
 * AJAX request that created the booking is long gone by the time the
 * customer is redirected back from Stripe/PayPal.
 * -------------------------------------------------------------------- */

function wbbm_cgw_find_booking_posts($reference, $gateway)
{
    $order_id = -absint($reference);

    return get_posts(array(
        'post_type'      => 'wbbm_booking',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => array(
            array('key' => '_wbbm_order_id', 'value' => $order_id),
            array('key' => '_wbbm_payment_gateway', 'value' => $gateway),
        ),
    ));
}

function wbbm_cgw_summary_from_posts($ids, $gateway)
{
    if (empty($ids)) {
        return null;
    }

    $first = $ids[0];
    $tax_amount = (float) get_post_meta($first, '_wbbm_tax_amount', true);
    $total_price = (float) get_post_meta($first, '_wbbm_total_price', true);

    return array(
        'reference'    => absint(abs((int) get_post_meta($first, '_wbbm_order_id', true))),
        'bus_name'     => get_the_title((int) get_post_meta($first, '_wbbm_bus_id', true)),
        'start'        => get_post_meta($first, '_wbbm_boarding_point', true),
        'end'          => get_post_meta($first, '_wbbm_droping_point', true),
        'journey_date' => get_post_meta($first, '_wbbm_journey_date', true),
        'adult'        => (int) get_post_meta($first, '_wbbm_total_adult', true),
        'child'        => (int) get_post_meta($first, '_wbbm_total_child', true),
        'infant'       => (int) get_post_meta($first, '_wbbm_total_infant', true),
        'entire'       => (int) get_post_meta($first, '_wbbm_total_entire', true),
        'seat_count'   => (int) get_post_meta($first, '_wbbm_seat', true),
        'name'         => get_post_meta($first, '_wbbm_user_name', true),
        'phone'        => get_post_meta($first, '_wbbm_user_phone', true),
        'email'        => get_post_meta($first, '_wbbm_user_email', true),
        'subtotal'     => round($total_price - $tax_amount, 2),
        'tax_rate'     => (float) get_post_meta($first, '_wbbm_tax_rate', true),
        'tax_amount'   => $tax_amount,
        'total_price'  => $total_price,
        'gateway'      => $gateway,
    );
}

function wbbm_cgw_confirm_posts($ids)
{
    foreach ($ids as $post_id) {
        update_post_meta($post_id, '_wbbm_payment_status', 'confirmed');
    }
}

/**
 * Undoes wbbm_record_pending_booking() when the session/order that was
 * supposed to follow it never gets created (a bad API key, the provider's
 * API being down, etc.) -- without this, a failed Stripe/PayPal request
 * would still leave the seat(s) occupied by a booking that can never be
 * paid for or confirmed, silently shrinking availability for no reason.
 * Force-deleted (bypassing Trash) since these posts never represented a
 * real booking attempt reaching a provider.
 */
function wbbm_cgw_release_booking($ids)
{
    foreach ($ids as $post_id) {
        wp_delete_post($post_id, true);
    }
}

/* ----------------------------------------------------------------------
 * Stripe: record the booking as pending, create a PaymentIntent, and hand
 * its client_secret back to the browser -- Stripe.js takes over from
 * there (js/mage_style.js), confirming the card Element against that
 * client_secret directly with Stripe (including any 3-D-Secure challenge,
 * handled entirely by Stripe.js). Nothing here ever sees card details.
 * -------------------------------------------------------------------- */

function wbbm_ajax_create_stripe_payment_intent()
{
    $validated = wbbm_validate_offline_booking_request($_POST); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce checked inside wbbm_validate_offline_booking_request()

    if (empty($validated['ok'])) {
        wp_send_json_error(array('code' => isset($validated['error']) ? $validated['error'] : 'unknown'));
    }

    $settings = wbbm_cgw_settings();
    if (empty($settings['stripe_enabled']) || empty($settings['stripe_secret_key']) || empty($settings['stripe_publishable_key'])) {
        wp_send_json_error(array('code' => 'stripe_not_configured'));
    }

    $recorded = wbbm_record_pending_booking($validated['data'], 'stripe', 'pending');
    if (empty($recorded['ok'])) {
        wp_send_json_error(array('code' => isset($recorded['error']) ? $recorded['error'] : 'insert_failed'));
    }

    $currency = strtolower(!empty($settings['currency_code']) ? $settings['currency_code'] : 'usd');
    $amount_minor = (int) round($recorded['summary']['total_price'] * 100);

    $intent = wbbm_stripe_request('POST', 'payment_intents', array(
        'amount'                => $amount_minor,
        'currency'              => $currency,
        'receipt_email'         => $recorded['summary']['email'],
        'description'           => sprintf(
            /* translators: %s: bus name */
            __('Bus booking -- %s', 'bus-booking-manager'),
            $recorded['summary']['bus_name']
        ),
        // Card only -- this server only ever confirms a `card` Element,
        // not Stripe's broader automatic_payment_methods set (which can
        // include redirect-based methods the current frontend flow has
        // no return-trip handling for).
        'payment_method_types'  => array('card'),
        'metadata'              => array('wbbm_reference' => (string) $recorded['summary']['reference']),
    ));

    if (is_wp_error($intent) || empty($intent['id']) || empty($intent['client_secret'])) {
        wbbm_cgw_release_booking($recorded['ids']);
        wp_send_json_error(array('code' => 'stripe_intent_failed'));
    }

    foreach ($recorded['ids'] as $post_id) {
        update_post_meta($post_id, '_wbbm_gateway_ref', $intent['id']);
    }

    wp_send_json_success(array(
        'client_secret'   => $intent['client_secret'],
        'publishable_key' => $settings['stripe_publishable_key'],
        'reference'       => $recorded['summary']['reference'],
    ));
}

/**
 * Called by the frontend right after Stripe.js's confirmCardPayment()
 * resolves with a `succeeded` status -- never trusts that client-reported
 * status on its own (a tampered response, a bug in the JS, etc. would
 * otherwise be enough to mark a booking paid for free), so it re-fetches
 * the PaymentIntent from Stripe directly and only confirms if Stripe
 * itself says it's `succeeded`.
 */
function wbbm_ajax_confirm_stripe_payment()
{
    $reference = isset($_POST['reference']) ? absint($_POST['reference']) : 0;
    $intent_id = isset($_POST['payment_intent_id']) ? sanitize_text_field(wp_unslash($_POST['payment_intent_id'])) : '';

    if (!$reference || !$intent_id) {
        wp_send_json_error(array('code' => 'invalid_request'));
    }

    $ids = wbbm_cgw_find_booking_posts($reference, 'stripe');
    if (empty($ids)) {
        wp_send_json_error(array('code' => 'booking_not_found'));
    }

    // The PaymentIntent id has to match the one this booking was actually
    // created for -- otherwise a client could quote any other succeeded
    // PaymentIntent id (even one paid by someone else, for a different
    // amount) against this booking's reference.
    $stored_ref = get_post_meta($ids[0], '_wbbm_gateway_ref', true);
    if ($stored_ref !== $intent_id) {
        wp_send_json_error(array('code' => 'payment_not_completed'));
    }

    $intent = wbbm_stripe_request('GET', 'payment_intents/' . rawurlencode($intent_id));

    if (is_wp_error($intent) || empty($intent['status']) || 'succeeded' !== $intent['status']) {
        wp_send_json_error(array('code' => 'payment_not_completed'));
    }

    wbbm_cgw_confirm_posts($ids);
    $summary = wbbm_cgw_summary_from_posts($ids, 'stripe');

    /** Same extension seam the Offline flow fires on creation -- fired here on confirmation instead, since a Stripe/PayPal booking isn't confirmed until payment actually clears. */
    do_action('wbbm_offline_booking_confirmed', $ids, $summary);

    wp_send_json_success($summary);
}

function wbbm_ajax_create_paypal_order()
{
    $validated = wbbm_validate_offline_booking_request($_POST); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce checked inside wbbm_validate_offline_booking_request()

    if (empty($validated['ok'])) {
        wp_send_json_error(array('code' => isset($validated['error']) ? $validated['error'] : 'unknown'));
    }

    $settings = wbbm_cgw_settings();
    if (empty($settings['paypal_enabled']) || empty($settings['paypal_client_id']) || empty($settings['paypal_secret'])) {
        wp_send_json_error(array('code' => 'paypal_not_configured'));
    }

    $recorded = wbbm_record_pending_booking($validated['data'], 'paypal', 'pending');
    if (empty($recorded['ok'])) {
        wp_send_json_error(array('code' => isset($recorded['error']) ? $recorded['error'] : 'insert_failed'));
    }

    $bus_url = get_permalink((int) $validated['data']['bus_id']);
    $reference = $recorded['summary']['reference'];

    // PayPal appends its own token/PayerID query args to whatever
    // return_url we give it, so a plain add_query_arg() is fine here --
    // unlike Stripe's success_url, there's no literal placeholder to
    // protect from encoding.
    $return_url = add_query_arg(array('wbbm_paypal_return' => 1, 'wbbm_ref' => $reference), $bus_url);
    $cancel_url = add_query_arg(array('wbbm_offline_error' => 'payment_cancelled'), $bus_url);

    $currency = !empty($settings['currency_code']) ? strtoupper($settings['currency_code']) : 'USD';
    $amount = number_format((float) $recorded['summary']['total_price'], 2, '.', '');

    $order = wbbm_paypal_request('POST', 'v2/checkout/orders', array(
        'intent'              => 'CAPTURE',
        'purchase_units'      => array(
            array(
                'reference_id' => (string) $reference,
                /* translators: %s: bus name */
                'description'  => sprintf(__('Bus booking -- %s', 'bus-booking-manager'), $recorded['summary']['bus_name']),
                'amount'       => array(
                    'currency_code' => $currency,
                    'value'         => $amount,
                ),
            ),
        ),
        'application_context' => array(
            'return_url' => $return_url,
            'cancel_url' => $cancel_url,
            'user_action' => 'PAY_NOW',
        ),
    ));

    if (is_wp_error($order) || empty($order['id']) || empty($order['links'])) {
        wbbm_cgw_release_booking($recorded['ids']);
        wp_send_json_error(array('code' => 'paypal_order_failed'));
    }

    $approve_url = '';
    foreach ($order['links'] as $link) {
        if (isset($link['rel'], $link['href']) && 'approve' === $link['rel']) {
            $approve_url = $link['href'];
            break;
        }
    }

    if (!$approve_url) {
        wbbm_cgw_release_booking($recorded['ids']);
        wp_send_json_error(array('code' => 'paypal_order_failed'));
    }

    foreach ($recorded['ids'] as $post_id) {
        update_post_meta($post_id, '_wbbm_gateway_ref', $order['id']);
    }

    wp_send_json_success(array('redirect' => $approve_url));
}

/* ----------------------------------------------------------------------
 * Return trip (PayPal only -- Stripe never leaves the page, see
 * wbbm_ajax_confirm_stripe_payment() above): verify payment actually
 * happened before confirming.
 * -------------------------------------------------------------------- */

function wbbm_handle_paypal_return()
{
    if (!isset($_GET['wbbm_paypal_return'], $_GET['wbbm_ref'])) {
        return;
    }

    $reference = absint(wp_unslash($_GET['wbbm_ref']));
    $ids = wbbm_cgw_find_booking_posts($reference, 'paypal');
    if (empty($ids)) {
        wbbm_redirect_offline_booking_error('booking_not_found');
    }

    $order_id = get_post_meta($ids[0], '_wbbm_gateway_ref', true);
    if (!$order_id) {
        wbbm_redirect_offline_booking_error('booking_not_found');
    }

    $capture = wbbm_paypal_request('POST', 'v2/checkout/orders/' . rawurlencode($order_id) . '/capture');

    if (is_wp_error($capture) || empty($capture['status']) || 'COMPLETED' !== $capture['status']) {
        wbbm_redirect_offline_booking_error('payment_not_completed');
    }

    wbbm_cgw_confirm_posts($ids);
    $summary = wbbm_cgw_summary_from_posts($ids, 'paypal');

    do_action('wbbm_offline_booking_confirmed', $ids, $summary);

    wbbm_redirect_offline_booking_success($summary);
}
