<?php

if (!defined('ABSPATH')) {
    die;
}

function mage_book_now_area($available_seat = null)
{
    $currency_pos = sanitize_text_field(get_option('woocommerce_currency_pos'));
    $is_sell_off = sanitize_text_field(get_post_meta(get_the_ID(), 'wbbm_sell_off', true));

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $search_date = (isset($_GET['j_date']) ? sanitize_text_field(wp_unslash($_GET['j_date'])) : '');
    $current_date = gmdate('Y-m-d');

    $boarding_time = sanitize_text_field(wbbm_get_datetime(wbbm_boarding_dropping_time(false, false), 'time'));

    // If Current time is greater than bus time
    // Bus should not be shown in search result
    if ($current_date === $search_date) {
        $search_timestamp = strtotime($search_date . ' ' . $boarding_time);
        if (current_time('timestamp') >= $search_timestamp) {
            return;
        }
    }
    ?>
    <div class="mage_flex mage_book_now_area">
        <div class="mage_thumb mage-notification-area">
            <p class="mage-notification mage-seat-available">
                <?php
                /* translators: show available seats number */
                printf(esc_html__('Only %s Seat Available', 'bus-booking-manager'), esc_html(intval($available_seat)));
                ?>
            </p>
        </div>
        <div class="mage_flex_equal">
            <div class="mage_sub_price">
                <div class="mage_sub_total">
                    <div>
                        <?php echo esc_html(wbbm_get_option('wbbm_sub_total_text', 'wbbm_label_setting_sec', __('Sub Total', 'bus-booking-manager')));
                        echo esc_html(':'); ?>
                    </div>
                    <span class="mage_subtotal_figure">0</span>
                </div>
            </div>
            <?php if ($is_sell_off != 'on') :
                do_action('wbbm_before_add_cart_btn', get_the_ID());
                ?>

                <?php
                $wbbm_wants_wc = class_exists('MP_Global_Function') && MP_Global_Function::wbbm_bus_wants_wc(get_the_ID());
                ?>

                <div class="mage_book_now mage_center_space">
                    <button type="button" class="mage_button mage_book_now <?php if ($available_seat == 0) {
                                                                                echo 'cursor-disabled';
                                                                           } ?>">
                        <?php echo esc_html(wbbm_get_option('wbbm_book_now_text', 'wbbm_label_setting_sec', __('Book Now', 'bus-booking-manager'))); ?>
                    </button>

                    <?php if ($wbbm_wants_wc) : ?>
                        <?php
                        // Nonce field for mage_book_now_area action — used for server-side verification
                        wp_nonce_field('mage_book_now_area', 'mage_book_now_area_nonce');

                        $wbbm_wc_product_id = sanitize_text_field(get_post_meta(get_the_ID(), 'link_wc_product', true));
                        $wbbm_checkout_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : '';
                        // rendered without the theme's header and footer -- see
                        // wbbm_embedded_checkout_chrome() in inc/wbbm_enque.php
                        $wbbm_checkout_url = $wbbm_checkout_url ? add_query_arg('wbbm_embed', '1', $wbbm_checkout_url) : '';
                        ?>

                        <button type="submit" class="mage_hidden single_add_to_cart_button" name="add-to-cart" value="<?php echo esc_attr($wbbm_wc_product_id); ?>">
                        </button>

                        <?php
                        /*
                         * WooCommerce buses get the same drawer as offline ones, minus
                         * the payment-method step: WooCommerce collects the method, the
                         * billing details and the payment itself at its own checkout,
                         * which loads into the second stage below once the seats are in
                         * the cart. Reuses the .mage_offline_modal classes so the drawer
                         * chrome is the one already styled.
                         */
                        ?>
                        <div class="mage_offline_modal wbbm-wc-drawer"
                             data-wbbm-checkout-url="<?php echo esc_url($wbbm_checkout_url); ?>"
                             data-wbbm-product-id="<?php echo esc_attr($wbbm_wc_product_id); ?>">
                            <div class="mage_offline_modal_box">
                                <div class="mage_offline_modal_head">
                                    <strong class="mage_offline_modal_title"><?php echo esc_html(get_the_title()); ?></strong>
                                    <button type="button" class="mage_offline_modal_close" aria-label="<?php esc_attr_e('Close', 'bus-booking-manager'); ?>">&times;</button>
                                </div>

                                <div class="mage_offline_modal_body">
                                    <div class="wbbm-wc-stage wbbm-wc-stage-review">
                                        <div class="mage_offline_summary">
                                            <h4><?php esc_html_e('Booking Summary', 'bus-booking-manager'); ?></h4>
                                            <div class="wbbm-wc-summary"></div>
                                        </div>
                                        <p class="wbbm-wc-note"><?php esc_html_e('Payment details are taken at the next step.', 'bus-booking-manager'); ?></p>
                                        <p class="mage_offline_submit_error" style="display:none;"></p>
                                    </div>

                                    <div class="wbbm-wc-stage wbbm-wc-stage-checkout" hidden>
                                        <iframe class="wbbm-wc-checkout-frame" title="<?php esc_attr_e('Checkout', 'bus-booking-manager'); ?>" src="about:blank"></iframe>
                                    </div>
                                </div>

                                <div class="mage_offline_modal_foot wbbm-wc-foot-review">
                                    <button type="button" class="mage_button mage_offline_modal_cancel"><?php esc_html_e('Cancel', 'bus-booking-manager'); ?></button>
                                    <button type="button" class="mage_button wbbm-wc-confirm"><?php esc_html_e('Confirm Booking', 'bus-booking-manager'); ?></button>
                                </div>

                                <div class="mage_offline_modal_foot wbbm-wc-foot-checkout" hidden>
                                    <button type="button" class="mage_button mage_offline_modal_cancel"><?php esc_html_e('Back to results', 'bus-booking-manager'); ?></button>
                                </div>
                            </div>
                        </div>
                    <?php else : ?>
                        <?php
                        $wbbm_pay_settings = get_option('wbbm_payment_settings');
                        $wbbm_pay_settings = is_array($wbbm_pay_settings) ? $wbbm_pay_settings : array();
                        $wbbm_offline_label = !empty($wbbm_pay_settings['offline_label']) ? $wbbm_pay_settings['offline_label'] : __('Pay Offline', 'bus-booking-manager');
                        $wbbm_offline_instructions = !empty($wbbm_pay_settings['offline_instructions']) ? $wbbm_pay_settings['offline_instructions'] : '';
                        $wbbm_offline_tax_rate = (float) get_post_meta(get_the_ID(), '_wbbm_offline_tax_rate', true);

                        // Which of Offline/Stripe/PayPal can actually take a
                        // payment right now -- if it's exactly one (today's
                        // default: Offline only), behavior stays byte-for-byte
                        // what it always was: no selector, no branching. Only
                        // when there's an actual choice does the method-selector
                        // below appear at all.
                        $wbbm_active_gateways = function_exists('wbbm_get_active_custom_gateways') ? wbbm_get_active_custom_gateways() : array();
                        $wbbm_gateway_count = count($wbbm_active_gateways);
                        $wbbm_default_gateway = $wbbm_gateway_count ? array_key_first($wbbm_active_gateways) : 'offline';
                        // Font Awesome, not dashicons -- fontawesome.min.css
                        // is already enqueued for every frontend page
                        // (wbbm_bus_enqueue_scripts()), while dashicons only
                        // loads here if the admin bar happens to be showing.
                        // fa-cc-stripe is a payment-brand glyph, which only
                        // exists in the Brands font -- using "fas" (Solid)
                        // for it renders a blank box, same as fa-paypal
                        // needing "fab" rather than "fas" below it.
                        $wbbm_gateway_icons = array(
                            'offline' => 'fas fa-money-bill-wave',
                            'stripe'  => 'fab fa-cc-stripe',
                            'paypal'  => 'fab fa-paypal',
                        );

                        // This same function can render once per bus card on a
                        // search-results page (inc/SearchClass.php), not just
                        // once on a single bus page -- Stripe.js only needs
                        // loading, and the publishable key only needs handing
                        // to the browser, once per page regardless of how many
                        // booking modals exist on it.
                        static $wbbm_stripe_js_printed = false;
                        if (isset($wbbm_active_gateways['stripe']) && !$wbbm_stripe_js_printed) {
                            $wbbm_stripe_js_printed = true;
                            ?>
                            <script src="https://js.stripe.com/v3/"></script>
                            <script>window.WbbmStripePK = <?php echo wp_json_encode($wbbm_pay_settings['stripe_publishable_key'] ?? ''); ?>;</script>
                            <?php
                        }
                        ?>
                        <?php
                        // Non-WooCommerce path: same field set the WooCommerce
                        // add-to-cart flow already collects from this same
                        // surrounding <form> (bus_id/journey_date/start_stops/
                        // etc. rendered further up in templates/single-bus.php),
                        // handled by inc/wbbm-offline-booking.php /
                        // inc/wbbm-custom-gateway-checkout.php instead of a
                        // WC cart -- see those files' docblocks for the full
                        // data flow. "Book Now" opens the confirmation modal
                        // below (mage_style.js) instead of submitting
                        // immediately; its own "Confirm Booking" button is what
                        // actually triggers this hidden submit button (Offline
                        // only -- Stripe/PayPal never submit this form, they go
                        // via AJAX + a redirect to the provider's own page).
                        wp_nonce_field('wbbm_offline_book_now', 'wbbm_offline_book_now_nonce');
                        ?>
                        <input type="hidden" class="mage_offline_tax_rate" value="<?php echo esc_attr($wbbm_offline_tax_rate); ?>">
                        <button type="submit" class="mage_hidden wbbm-offline-book-btn" name="wbbm_offline_book_now" value="1">
                        </button>

                        <?php if (0 === $wbbm_gateway_count) : ?>
                            <p class="mage_offline_unavailable"><?php esc_html_e('Booking is temporarily unavailable for this bus -- please contact us.', 'bus-booking-manager'); ?></p>
                        <?php else : ?>
                        <div class="mage_offline_modal" id="mage_offline_modal">
                            <div class="mage_offline_modal_box">
                                <div class="mage_offline_modal_head">
                                    <?php /* Bus info in the modal itself, not just the header behind
                                            it -- "Choose Payment Method" moved to sit directly above the
                                            picker further down (see below), so this title can be about
                                            which bus this booking actually is instead. */ ?>
                                    <strong class="mage_offline_modal_title"><?php echo esc_html(get_the_title()); ?></strong>
                                    <button type="button" class="mage_offline_modal_close" aria-label="<?php esc_attr_e('Close', 'bus-booking-manager'); ?>">&times;</button>
                                </div>
                                <div class="mage_offline_modal_body">
                                    <?php /* Booking form (summary + contact fields) -- swapped out for
                                            .mage_offline_modal_result once a booking is confirmed, rather
                                            than closing the modal (see js/mage_style.js). */ ?>
                                    <div class="mage_offline_modal_form">
                                        <?php if ($wbbm_gateway_count <= 1) : ?>
                                            <input type="hidden" name="wbbm_selected_gateway" value="<?php echo esc_attr($wbbm_default_gateway); ?>">
                                        <?php endif; ?>

                                        <?php if ($wbbm_offline_instructions) : ?>
                                            <p class="mage_offline_instructions" data-gateway="offline" <?php echo ('offline' === $wbbm_default_gateway) ? '' : 'style="display:none"'; ?>><?php echo esc_html($wbbm_offline_instructions); ?></p>
                                        <?php endif; ?>

                                        <div class="mage_offline_summary">
                                            <h4><?php esc_html_e('Booking Summary', 'bus-booking-manager'); ?></h4>
                                            <div class="mage_offline_summary_row mage_offline_summary_bus">
                                                <span><?php esc_html_e('Bus', 'bus-booking-manager'); ?></span>
                                                <span class="mage_offline_summary_bus_val"><?php echo esc_html(get_the_title()); ?></span>
                                            </div>
                                            <div class="mage_offline_summary_row mage_offline_summary_route">
                                                <span><?php esc_html_e('Route', 'bus-booking-manager'); ?></span>
                                                <span class="mage_offline_summary_route_val">—</span>
                                            </div>
                                            <div class="mage_offline_summary_row mage_offline_summary_date">
                                                <span><?php esc_html_e('Date', 'bus-booking-manager'); ?></span>
                                                <span class="mage_offline_summary_date_val">—</span>
                                            </div>
                                            <div class="mage_offline_summary_row mage_offline_summary_time">
                                                <span><?php esc_html_e('Time', 'bus-booking-manager'); ?></span>
                                                <span class="mage_offline_summary_time_val">—</span>
                                            </div>
                                            <div class="mage_offline_summary_rows"></div>
                                            <div class="mage_offline_summary_row mage_offline_summary_subtotal">
                                                <span><?php esc_html_e('Subtotal', 'bus-booking-manager'); ?></span>
                                                <span class="mage_offline_summary_subtotal_val">0</span>
                                            </div>
                                            <div class="mage_offline_summary_row mage_offline_summary_tax" style="display:none;">
                                                <span class="mage_offline_summary_tax_label"><?php esc_html_e('Tax', 'bus-booking-manager'); ?></span>
                                                <span class="mage_offline_summary_tax_val">0</span>
                                            </div>
                                            <div class="mage_offline_summary_row mage_offline_summary_total">
                                                <span><?php esc_html_e('Total', 'bus-booking-manager'); ?></span>
                                                <span class="mage_offline_summary_total_val">0</span>
                                            </div>
                                        </div>

                                        <div class="mage_offline_contact_fields">
                                            <input type="text" name="wbbm_offline_name" class="mage_offline_input" placeholder="<?php esc_attr_e('Your name', 'bus-booking-manager'); ?>" required>
                                            <input type="tel" name="wbbm_offline_phone" class="mage_offline_input" placeholder="<?php esc_attr_e('Phone number', 'bus-booking-manager'); ?>" required>
                                            <input type="email" name="wbbm_offline_email" class="mage_offline_input" placeholder="<?php esc_attr_e('Email address', 'bus-booking-manager'); ?>" required>
                                        </div>

                                        <?php /* Right above the footer buttons, on purpose -- picking a
                                                payment method is the last decision before "Confirm", so it
                                                reads as part of that action rather than mixed in above the
                                                fare summary. Only rendered at all when there's an actual
                                                choice (see $wbbm_gateway_count above). */ ?>
                                        <?php if ($wbbm_gateway_count > 1) : ?>
                                            <h4 class="mage_offline_gateway_picker_heading"><?php esc_html_e('Choose Payment Method', 'bus-booking-manager'); ?></h4>
                                            <div class="mage_offline_gateway_picker" data-selected="<?php echo esc_attr($wbbm_default_gateway); ?>">
                                                <?php foreach ($wbbm_active_gateways as $wbbm_gw_slug => $wbbm_gw_info) : ?>
                                                    <label class="mage_offline_gateway_card <?php echo $wbbm_gw_slug === $wbbm_default_gateway ? 'is-selected' : ''; ?>">
                                                        <input type="radio" name="wbbm_selected_gateway" value="<?php echo esc_attr($wbbm_gw_slug); ?>" <?php checked($wbbm_gw_slug, $wbbm_default_gateway); ?>>
                                                        <span class="<?php echo esc_attr(isset($wbbm_gateway_icons[$wbbm_gw_slug]) ? $wbbm_gateway_icons[$wbbm_gw_slug] : 'fas fa-money-bill-wave'); ?>"></span>
                                                        <span><?php echo esc_html($wbbm_gw_info['label']); ?></span>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (isset($wbbm_active_gateways['stripe'])) : ?>
                                            <?php /* Card fields collected right here via Stripe Elements
                                                    (js/mage_style.js) instead of redirecting to Stripe's own
                                                    page -- hidden unless Stripe is the selected gateway (or
                                                    is the only gateway, in which case it's the only card in
                                                    the form and always relevant). */ ?>
                                            <div class="mage_stripe_card_box" data-gateway="stripe" <?php echo ('stripe' === $wbbm_default_gateway) ? '' : 'style="display:none"'; ?>>
                                                <label class="mage_stripe_card_label"><?php esc_html_e('Card details', 'bus-booking-manager'); ?></label>
                                                <div class="mage_stripe_card_element"></div>
                                                <p class="mage_stripe_card_errors" role="alert"></p>
                                            </div>
                                        <?php endif; ?>

                                        <p class="mage_offline_submit_error" style="display:none;"></p>
                                    </div>

                                    <?php /* Populated by JS from the AJAX response -- see
                                            js/mage_style.js's wbbmShowOfflineBookingResult(). */ ?>
                                    <div class="mage_offline_modal_result" style="display:none;"></div>
                                </div>
                                <div class="mage_offline_modal_foot mage_offline_modal_foot_form">
                                    <button type="button" class="mage_button mage_offline_modal_cancel"><?php esc_html_e('Cancel', 'bus-booking-manager'); ?></button>
                                    <button type="button" class="mage_button mage_offline_confirm_btn"><?php esc_html_e('Confirm Booking', 'bus-booking-manager'); ?></button>
                                </div>
                                <div class="mage_offline_modal_foot mage_offline_modal_foot_result" style="display:none;">
                                    <button type="button" class="mage_button mage_offline_modal_close_btn"><?php esc_html_e('Close', 'bus-booking-manager'); ?></button>
                                </div>
                            </div>
                        </div>
                        <?php endif; // 0 === $wbbm_gateway_count ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
