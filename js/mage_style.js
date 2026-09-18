(function ($) {
    "use strict";
    $('#one_way').on('click', function () {
        $('.mage_return_date').slideUp(300).removeClass('mage_hidden').find('input').val('');
    });
    $('#return').on('click', function () {
        $('.mage_return_date').slideDown(300).removeClass('mage_hidden');
    });
    $('.mage_input_select .mage_route_list li').on('hover , click', function () {
        let route = $(this).attr('data-route');
        $(this).parents('.mage_input_select').find('input').val(route);
    });
    $('.mage_input_select input').on({
        keyup: function () {
            let input = $(this).val().toLowerCase();
            $(this).siblings('ul.mage_route_list').find('li').filter(function () {
                $(this).toggle($(this).attr('data-route').toLowerCase().indexOf(input) > -1);
            });
        },
        blur: function () {
            let input = $(this).val().toLowerCase();
            let flag = 0;
            $(this).siblings('ul.mage_route_list').find('li').filter(function () {
                if ($(this).attr('data-route').toLowerCase() === input) {
                    flag = 1;
                }
            });
            if (flag < 1) {
                $(this).val('');
            }
        },
        click: function () {
            $(this).siblings('ul.mage_route_list').find('li').slideDown(200);
        }
    });
    /*
     * Delegated from document so these keep working on markup that arrives
     * after page load. Search results are rendered in place in the admin
     * booking screen, and direct bindings were lost on every re-render:
     * the quantity still changed (another script handles that) but the
     * sub total was never recalculated, so it sat at 0.
     */
    $(document).on('keyup keydown', '.mage_form_group input.mage_form', function (e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            return false;
        }
        let target = $(this);
        let value = parseInt(target.val());
        mageTicketQty(target, value);
    });
    $(document).on('click', '.mage_qty_dec', function () {
        let target = $(this).siblings('input');
        let value = (parseInt(target.val()) - 1) > 0 ? (parseInt(target.val()) - 1) : 0;
        target.trigger('input');
        mageTicketQty(target, value);
    });
    $(document).on('click', '.mage_qty_inc', function () {
        let target = $(this).siblings('input');
        let value = target.val() ? parseInt(target.val()) + 1 : 1;
        target.trigger('input');
        mageTicketQty(target, value);
    });

    // Extra Service Price
    $(document).on('change', '.extra-qty-box', function () {
        const target = $(this);
        let value = target.find('option:selected').val();
        if (value == undefined) {
            value = target.val();
        }
        mageExtServiceQty(target, value);
    });
    $(document).on('click', '.mage_es_qty_minus', function () {
        const target = $(this).siblings('input');
        const value = parseInt(target.val()) - 1;
        target.trigger('input');
        mageExtServiceQty(target, value);
    });
    $(document).on('click', '.mage_es_qty_plus', function () {
        const target = $(this).siblings('input');
        const value = parseInt(target.val()) + 1;
        target.trigger('input');
        mageExtServiceQty(target, value);
    });
    // Extra Service Price END

    // Extra Bag price qty change
    $(document).on('keyup keydown', '.mage_customer_info_area .mage_eb_form_qty .extra_bag_qty', function (e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            return false;
        }
        let target = $(this);
        let value = parseInt(target.val());
        mageExtBagQty(target, value);
    });
    $(document).on('click', '.mage_customer_info_area .mage_eb_form_qty .mage_eb_qty_minus', function () {
        const target = $(this).siblings('input');
        const value = parseInt(target.val()) - 1;
        mageExtBagQty(target, value);
    })
    $(document).on('click', '.mage_customer_info_area .mage_eb_form_qty .mage_eb_qty_plus', function () {
        const target = $(this).siblings('input');
        const value = parseInt(target.val()) + 1;
        mageExtBagQty(target, value);
    })
    // Extra Bag price qty change END

    /*
     * Delegated: the button is type="button" and does nothing on its own --
     * this handler validates and then triggers the hidden submit. Bound
     * directly it was lost whenever results were re-rendered in place, which
     * left Book Now completely inert after an in-page search.
     */
    $(document).on('click', 'button.mage_book_now', function () {

        $(this).parents('.mage_search_list').find('.mage-seat-available').hide();
        let ticket = 0;
        let currentTarget = $(this).parents('.mage_search_list').find('.mage_form[data-price]');
        let seat_available = $(this).parents('.mage_search_list').attr('data-seat-available');
        currentTarget.each(function (index) {
            ticket += $(this).val() ? parseInt($(this).val()) : 0;
        });
        var pic_ele = $(this).parents('.mage_search_list').find('select[name="mage_pickpoint"]');
        var pickpoint = $(this).parents('.mage_search_list').find('select[name="mage_pickpoint"] option:selected').val();

        if (ticket > parseInt(seat_available)) {
            $(this).parents('.mage_search_list').find('.mage-seat-available').show();
            return false;
        }
        if (ticket > 0) {
            // Pickup Point Validation
            if (pic_ele.children().length > 0) {
                if (pickpoint != '') { // Pass
                    mageSubTotal(currentTarget);
                    wbbmProceedToBook($(this));
                } else { // Fail
                    $(this).parents('.mage_search_list').find('select[name="mage_pickpoint"]').addClass('mage_error').trigger('focus');
                }
            } else {
                mageSubTotal(currentTarget);
                wbbmProceedToBook($(this));
            }
        } else {
            currentTarget.addClass('mage_error').trigger('focus');
        }
    });

    /*
     * WooCommerce buses submit .single_add_to_cart_button immediately, same
     * as always. Offline-payment buses instead open the confirmation modal
     * (inc/clean/layout/book-now-area.php) -- only one of the two hidden
     * submit buttons is ever rendered for a given bus, so which one exists
     * decides which path runs. The modal's own "Confirm Booking" button is
     * what actually triggers .wbbm-offline-book-btn.
     */
    function wbbmProceedToBook($bookNowBtn) {
        var $searchList = $bookNowBtn.parents('.mage_search_list');

        /*
         * Return trips hand off here: on an outbound card the customer is
         * moved to the return list to pick that leg instead of booking this
         * one straight away. The hook lives in js/wbbm-search-modern.js and
         * returns true only when it took over, so one-way searches and the
         * return list itself fall through to the normal path below.
         */
        if (typeof window.wbbmAdvanceToReturnLeg === 'function' && window.wbbmAdvanceToReturnLeg($searchList)) {
            return;
        }

        var $offlineBtn = $searchList.find('button.wbbm-offline-book-btn');

        if ($offlineBtn.length) {
            wbbmOpenOfflineModal($searchList);
            return;
        }

        /*
         * WooCommerce buses open the same drawer and add to the cart from
         * there, so the customer sees what they are buying before the
         * checkout loads. The hook lives in js/wbbm-search-modern.js and
         * returns true only when it took over; without it, or without a
         * drawer on this bus, the original submit still runs.
         */
        if (typeof window.wbbmOpenWcDrawer === 'function' && window.wbbmOpenWcDrawer($searchList)) {
            return;
        }

        $bookNowBtn.siblings('button.single_add_to_cart_button').trigger('click');
    }

    // Mirrors mageSubTotal()'s three loops (seat price, extra service, extra
    // bag) but returns a plain number instead of writing formatted currency
    // text into the DOM -- the offline modal needs a real number to add tax
    // on top of, not a pre-formatted "$50.00" string to re-parse.
    function wbbmCalcSubtotal($searchList) {
        var subtotal = 0;
        $searchList.find('.mage_form[data-price]').each(function () {
            var unitPrice = parseFloat($(this).attr('data-price')) || 0;
            var qty = parseFloat($(this).val()) || 0;
            if (unitPrice * qty > 0) { subtotal += unitPrice * qty; }
        });
        $searchList.find('.wbbm_extra_service_table tbody tr').each(function () {
            var es = $(this).find('.extra-qty-box');
            var unitPrice = parseFloat(es.attr('data-price')) || 0;
            var qty = parseFloat(es.val()) || 0;
            if (unitPrice * qty > 0) { subtotal += unitPrice * qty; }
        });
        $searchList.find('.mage_form_list').each(function () {
            var extBag = $(this).find('.extra_bag_qty');
            var unitPrice = parseFloat(extBag.attr('data-price')) || 0;
            var qty = parseFloat(extBag.val()) || 0;
            if (unitPrice * qty > 0) { subtotal += unitPrice * qty; }
        });
        return subtotal;
    }

    function wbbmOfflineFieldVal($searchList, name) {
        var $el = $searchList.find('[name="' + name + '"]').first();
        return $el.length ? $el.val() : '';
    }

    function wbbmResetOfflineModal($modal) {
        // Back to the form state every time the modal opens, in case a
        // previous booking's result (or error) is still showing from last
        // time -- it should never carry over into a fresh attempt.
        $modal.find('.mage_offline_modal_form').show();
        $modal.find('.mage_offline_modal_result').hide().empty();
        $modal.find('.mage_offline_modal_foot_form').show();
        $modal.find('.mage_offline_modal_foot_result').hide();
        $modal.find('.mage_offline_submit_error').hide().empty();
        $modal.find('.mage_offline_input').removeClass('mage_error');
        wbbmSyncGatewayUI($modal);
    }

    var wbbmConfirmBtnLabels = {
        offline: 'Confirm Booking',
        stripe: 'Pay with Card',
        paypal: 'Pay with PayPal'
    };

    // One Stripe.js instance + one `card` Element for the whole page --
    // Stripe Elements don't need to be recreated per booking attempt, they
    // just get mounted into whichever modal's container is relevant right
    // now (there's only ever one .mage_offline_modal open at a time) and
    // moved (via unmount + mount) if a different modal opens later, e.g. on
    // a search-results page with several bus cards. A `card` Element (not
    // client_secret-bound) can be created before any PaymentIntent exists;
    // the client_secret only gets used later, at confirmCardPayment() time.
    var wbbmStripe = null;
    var wbbmStripeCard = null;
    var wbbmStripeMountedEl = null;

    function wbbmGetStripeCard() {
        if (wbbmStripeCard) { return wbbmStripeCard; }
        if (typeof Stripe === 'undefined' || !window.WbbmStripePK) { return null; }
        wbbmStripe = Stripe(window.WbbmStripePK);
        wbbmStripeCard = wbbmStripe.elements().create('card', {
            style: { base: { fontSize: '14px', color: '#1f2937' }, invalid: { color: '#ef4444' } }
        });
        wbbmStripeCard.on('change', function (event) {
            // event.error / cleared text lives per-modal (a different modal
            // might be showing a stale error from last time otherwise),
            // found via the Element's own current mount point.
            var $errBox = wbbmStripeMountedEl ? $(wbbmStripeMountedEl).siblings('.mage_stripe_card_errors') : $();
            $errBox.text(event.error ? event.error.message : '');
        });
        return wbbmStripeCard;
    }

    function wbbmMountStripeCard($container) {
        var card = wbbmGetStripeCard();
        if (!card || !$container.length) { return; }
        var node = $container.get(0);
        if (wbbmStripeMountedEl === node) { return; }
        card.mount(node);
        wbbmStripeMountedEl = node;
    }

    // Reflects whichever gateway is currently selected (the radio picker,
    // when there's an actual choice, or the single hidden input when there
    // isn't) into the parts of the modal that depend on it: the Offline
    // instructions paragraph, the Stripe card box, and the Confirm button's
    // label. Called on open and on every picker change, never assumes a
    // paint that already happened is still current.
    function wbbmSyncGatewayUI($modal) {
        var gateway = $modal.find('input[name="wbbm_selected_gateway"]:checked').val()
            || $modal.find('input[name="wbbm_selected_gateway"]').val()
            || 'offline';

        $modal.find('.mage_offline_instructions').toggle(gateway === 'offline');
        $modal.find('.mage_offline_confirm_btn').text(wbbmConfirmBtnLabels[gateway] || wbbmConfirmBtnLabels.offline);

        var $stripeBox = $modal.find('.mage_stripe_card_box');
        if ($stripeBox.length) {
            $stripeBox.toggle(gateway === 'stripe');
            if (gateway === 'stripe') {
                wbbmMountStripeCard($modal.find('.mage_stripe_card_element'));
            }
        }

        return gateway;
    }

    $(document).on('change', '.mage_offline_gateway_picker input[type="radio"]', function () {
        var $picker = $(this).parents('.mage_offline_gateway_picker');
        var $modal = $(this).parents('.mage_offline_modal');
        $picker.attr('data-selected', $(this).val());
        $picker.find('.mage_offline_gateway_card').removeClass('is-selected');
        $(this).parents('.mage_offline_gateway_card').addClass('is-selected');
        wbbmSyncGatewayUI($modal);
    });

    function wbbmOpenOfflineModal($searchList) {
        var $modal = $searchList.find('.mage_offline_modal');
        if (!$modal.length) { return; }

        // Marked open BEFORE reset/sync below (which can mount the Stripe
        // card Element into this modal) -- Stripe.js needs the container to
        // actually have layout/dimensions at mount time, which it won't
        // while the modal is still display:none.
        $modal.addClass('is-open');

        wbbmResetOfflineModal($modal);

        var subtotal = wbbmCalcSubtotal($searchList);
        var taxRate = parseFloat($searchList.find('.mage_offline_tax_rate').val()) || 0;
        var taxAmount = subtotal * (taxRate / 100);
        var total = subtotal + taxAmount;

        var start = wbbmOfflineFieldVal($searchList, 'start_stops');
        var end = wbbmOfflineFieldVal($searchList, 'end_stops');
        var journeyDate = wbbmOfflineFieldVal($searchList, 'journey_date');
        var startTime = wbbmOfflineFieldVal($searchList, 'user_start_time');

        $modal.find('.mage_offline_summary_route_val').text(start && end ? (start + ' → ' + end) : '—');
        $modal.find('.mage_offline_summary_date_val').text(journeyDate || '—');
        $modal.find('.mage_offline_summary_time_val').text(startTime || '—');

        var $rows = $modal.find('.mage_offline_summary_rows').empty();
        $searchList.find('.mage_form[data-price]').each(function () {
            var qty = parseInt($(this).val(), 10) || 0;
            if (qty < 1) { return; }
            var title = $(this).attr('data-ticket-title') || '';
            var unitPrice = parseFloat($(this).attr('data-price')) || 0;
            $rows.append(
                '<div class="mage_offline_summary_row">' +
                    '<span>' + title + ' x ' + qty + '</span>' +
                    '<span>' + wbbm_woo_price_format(unitPrice * qty) + '</span>' +
                '</div>'
            );
        });

        $modal.find('.mage_offline_summary_subtotal_val').text(wbbm_woo_price_format(subtotal));
        var $taxRow = $modal.find('.mage_offline_summary_tax');
        if (taxRate > 0) {
            $taxRow.show();
            $modal.find('.mage_offline_summary_tax_label').text('Tax (' + taxRate + '%)');
            $modal.find('.mage_offline_summary_tax_val').text(wbbm_woo_price_format(taxAmount));
        } else {
            $taxRow.hide();
        }
        $modal.find('.mage_offline_summary_total_val').text(wbbm_woo_price_format(total));
    }

    // The header "x" and the dedicated result-state "Close" button both
    // just hide the modal -- neither one submits or resets anything, so a
    // customer can reopen it (Book Now again) and pick up where the form
    // last was, or see the same result again if they already booked.
    $(document).on('click', '.mage_offline_modal_close, .mage_offline_modal_cancel, .mage_offline_modal_close_btn', function () {
        $(this).parents('.mage_offline_modal').removeClass('is-open');
    });
    $(document).on('click', '.mage_offline_modal', function (e) {
        if (e.target === this) { $(this).removeClass('is-open'); }
    });
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') { $('.mage_offline_modal.is-open').removeClass('is-open'); }
    });

    var wbbmOfflineErrorMessages = {
        invalid_nonce: 'Your session expired -- please close this and try again.',
        invalid_bus: 'This bus could not be found.',
        wrong_payment_mode: 'This bus is not set up for offline booking.',
        missing_contact_info: 'Please fill in your name, phone and a valid email address.',
        missing_route_fields: 'Please choose your boarding and dropping points and travel date.',
        no_seats_selected: 'Please select at least one seat.',
        sold_out: 'Sorry, not enough seats are available for this selection.',
        insert_failed: 'Something went wrong recording your booking -- please try again.',
        stripe_not_configured: 'Card payment is not available right now -- please choose another payment method.',
        paypal_not_configured: 'PayPal is not available right now -- please choose another payment method.',
        stripe_intent_failed: 'Could not start the card payment -- please try again.',
        paypal_order_failed: 'Could not start the PayPal payment -- please try again.',
        payment_not_completed: 'Payment could not be confirmed -- please try again, or use a different card.',
        booking_not_found: 'We could not find that booking -- please try again.',
        invalid_request: 'Something went wrong -- please try again.'
    };

    // Everything below goes into .html(), and the summary values come back
    // from the server (bus name, stop names, the reference) -- run them
    // through here so a stop called `<b>` renders as text instead of markup.
    // Prices are deliberately NOT escaped: wbbm_woo_price_format() bakes in
    // the WooCommerce currency symbol, which can legitimately be an HTML
    // entity (`&pound;`), and escaping would print the entity source.
    function wbbmEscHtml(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function wbbmRenderOfflineResult($modal, summary) {
        var rows = [
            ['Bus', wbbmEscHtml(summary.bus_name)],
            ['Route', wbbmEscHtml(summary.start) + ' <span class="wbbm-offline-route-arrow" aria-hidden="true">→</span> ' + wbbmEscHtml(summary.end)],
            ['Journey date', wbbmEscHtml(summary.journey_date)]
        ];

        var seatBits = [];
        if (summary.entire) {
            seatBits.push('Entire bus');
        } else {
            if (summary.adult) { seatBits.push(summary.adult + ' adult'); }
            if (summary.child) { seatBits.push(summary.child + ' child'); }
            if (summary.infant) { seatBits.push(summary.infant + ' infant'); }
        }
        rows.push(['Seats', wbbmEscHtml(seatBits.join(', '))]);
        rows.push(['Subtotal', wbbm_woo_price_format(summary.subtotal)]);
        if (parseFloat(summary.tax_amount) > 0) {
            rows.push(['Tax (' + wbbmEscHtml(summary.tax_rate) + '%)', wbbm_woo_price_format(summary.tax_amount)]);
        }

        // Offline is still "we'll follow up" (payment_status stays pending
        // until the site owner confirms it manually); Stripe/PayPal have
        // already actually charged/captured by the time this renders, so
        // the wording should say so rather than implying anything is still
        // owed or pending.
        //
        // Either way the *booking* itself is recorded, so the headline says
        // so plainly and the pill underneath carries the honest payment
        // state -- the customer should never have to read a paragraph to
        // find out whether they got a seat.
        var isOffline = ('offline' === summary.gateway || !summary.gateway);
        var heading = isOffline ? 'Booking confirmed' : 'Payment confirmed';
        var note = isOffline
            ? 'Your seats are reserved. We\'ll contact you shortly to confirm payment.'
            : 'Your booking is complete and your payment has gone through.';
        var pillLabel = isOffline ? 'Payment pending' : 'Paid in full';
        var pillState = isOffline ? 'is-pending' : 'is-paid';

        // The check draws itself in (see .wbbm-offline-success-mark in
        // css/wbbm-search-modern.css); the whole block is announced at once
        // via role="status" so a screen reader hears the outcome too, not
        // just the sighted celebration.
        var html = '<div class="wbbm-offline-success" role="status">' +
            '<span class="wbbm-offline-success-mark" aria-hidden="true">' +
                '<svg viewBox="0 0 52 52" focusable="false">' +
                    '<circle class="wbbm-offline-success-mark-ring" cx="26" cy="26" r="24"/>' +
                    '<path class="wbbm-offline-success-mark-tick" d="M15 27.5 L22.5 35 L37.5 19"/>' +
                '</svg>' +
            '</span>' +
            '<h3 class="mage_offline_result_heading">' + heading + '</h3>' +
            '<p class="wbbm-offline-success-note">' + note + '</p>' +
            '<span class="wbbm-offline-success-pill ' + pillState + '">' + pillLabel + '</span>' +
            '<span class="wbbm-offline-success-ref">' +
                '<small>Booking reference</small>' +
                '<strong>#' + wbbmEscHtml(summary.reference) + '</strong>' +
            '</span>' +
        '</div>' +
            '<table class="wbbm-offline-booking-details">';
        rows.forEach(function (row) {
            html += '<tr><th>' + row[0] + '</th><td>' + row[1] + '</td></tr>';
        });
        html += '<tr class="wbbm-offline-booking-total"><th>Total</th><td>' + wbbm_woo_price_format(summary.total_price) + '</td></tr>' +
            '</table>';

        $modal.find('.mage_offline_modal_result').html(html).show();
        $modal.find('.mage_offline_modal_form').hide();
        $modal.find('.mage_offline_modal_foot_form').hide();
        $modal.find('.mage_offline_modal_foot_result').show();
    }

    $(document).on('click', '.mage_offline_confirm_btn', function () {
        var $btn = $(this);
        var $modal = $btn.parents('.mage_offline_modal');
        var $searchList = $modal.parents('.mage_search_list');
        var $fields = $modal.find('.mage_offline_input');
        var $errorMsg = $modal.find('.mage_offline_submit_error').hide().empty();
        var valid = true;

        $fields.each(function () {
            if (!$(this).val() || !$(this).val().trim()) {
                $(this).addClass('mage_error');
                valid = false;
            } else {
                $(this).removeClass('mage_error');
            }
        });

        if (!valid) { return; }

        $modal.find('.mage_offline_input[name="wbbm_offline_email"]').each(function () {
            // Same basic shape check the "email" input type already nudges
            // toward -- a real is_email() check still happens server-side
            // in inc/wbbm-offline-booking.php regardless.
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test($(this).val().trim())) {
                $(this).addClass('mage_error');
                valid = false;
            }
        });

        if (!valid) { return; }

        var gateway = wbbmSyncGatewayUI($modal);

        if ('stripe' === gateway && !wbbmGetStripeCard()) {
            $errorMsg.text('Card payment could not load -- please refresh and try again.').show();
            return;
        }

        var originalText = $btn.text();
        $btn.prop('disabled', true).text(gateway === 'paypal' ? 'Redirecting…' : 'Processing…');

        function resetButton() { $btn.prop('disabled', false).text(originalText); }
        function showError(code) {
            resetButton();
            $errorMsg.text(wbbmOfflineErrorMessages[code] || 'Something went wrong -- please try again.').show();
        }

        // The same field set feeds all three gateways -- Offline validates
        // and records in one step (inc/wbbm-offline-booking.php); Stripe
        // validates + records as pending, then hands back a PaymentIntent
        // client_secret to confirm right here with Stripe.js; PayPal
        // validates + records as pending, then hands back a hosted approval
        // URL to redirect to (inc/wbbm-custom-gateway-checkout.php).
        var postData = {
            action: 'wbbm_offline_book_now',
            wbbm_offline_book_now: 1,
            wbbm_offline_book_now_nonce: $searchList.find('input[name="wbbm_offline_book_now_nonce"]').val(),
            bus_id: wbbmOfflineFieldVal($searchList, 'bus_id'),
            journey_date: wbbmOfflineFieldVal($searchList, 'journey_date'),
            start_stops: wbbmOfflineFieldVal($searchList, 'start_stops'),
            end_stops: wbbmOfflineFieldVal($searchList, 'end_stops'),
            user_start_time: wbbmOfflineFieldVal($searchList, 'user_start_time'),
            bus_start_time: wbbmOfflineFieldVal($searchList, 'bus_start_time'),
            mage_pickpoint: $searchList.find('select[name="mage_pickpoint"]').val(),
            adult_quantity: $searchList.find('[name="adult_quantity"]').val(),
            child_quantity: $searchList.find('[name="child_quantity"]').val(),
            infant_quantity: $searchList.find('[name="infant_quantity"]').val(),
            entire_quantity: $searchList.find('[name="entire_quantity"]').is(':checked') ? 1 : 0,
            wbbm_offline_name: $modal.find('[name="wbbm_offline_name"]').val(),
            wbbm_offline_phone: $modal.find('[name="wbbm_offline_phone"]').val(),
            wbbm_offline_email: $modal.find('[name="wbbm_offline_email"]').val()
        };

        if ('stripe' === gateway) {
            postData.action = 'wbbm_create_stripe_payment_intent';
        } else if ('paypal' === gateway) {
            postData.action = 'wbbm_create_paypal_order';
        }

        $.ajax({
            url: WbbmAjax.ajax_url,
            type: 'POST',
            data: postData,
            dataType: 'json'
        }).done(function (response) {
            if (!response || !response.success) {
                showError(response && response.data && response.data.code);
                return;
            }

            if ('offline' === gateway) {
                resetButton();
                wbbmRenderOfflineResult($modal, response.data);
                return;
            }

            if ('paypal' === gateway) {
                // The browser leaves the site here, same as any real
                // checkout -- the button stays disabled ("Redirecting…")
                // since there's nothing left to do on this page before the
                // navigation happens.
                window.location.href = response.data.redirect;
                return;
            }

            // Stripe: confirm the card right here -- this is the one call
            // that can prompt the customer for 3-D-Secure (Stripe.js shows
            // that itself, inline, as part of this same promise) and is the
            // only place actual card details are involved, handled entirely
            // by Stripe.js/Elements and sent straight to Stripe.
            wbbmStripe.confirmCardPayment(response.data.client_secret, {
                payment_method: {
                    card: wbbmStripeCard,
                    billing_details: {
                        name: postData.wbbm_offline_name,
                        email: postData.wbbm_offline_email,
                        phone: postData.wbbm_offline_phone
                    }
                }
            }).then(function (result) {
                if (result.error) {
                    showError(null);
                    $errorMsg.text(result.error.message || 'Card payment failed -- please try again.').show();
                    return;
                }
                if (!result.paymentIntent || 'succeeded' !== result.paymentIntent.status) {
                    showError('payment_not_completed');
                    return;
                }

                // Never trust confirmCardPayment()'s client-side result
                // alone -- re-verify server-side before showing this as a
                // real confirmed booking.
                $.ajax({
                    url: WbbmAjax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wbbm_confirm_stripe_payment',
                        reference: response.data.reference,
                        payment_intent_id: result.paymentIntent.id
                    },
                    dataType: 'json'
                }).done(function (confirmRes) {
                    resetButton();
                    if (confirmRes && confirmRes.success) {
                        wbbmRenderOfflineResult($modal, confirmRes.data);
                    } else {
                        showError(confirmRes && confirmRes.data && confirmRes.data.code);
                    }
                }).fail(function () {
                    showError(null);
                    $errorMsg.text('Payment went through, but we could not confirm it here -- please contact us with your card statement.').show();
                });
            });
        }).fail(function () {
            resetButton();
            $errorMsg.text('Connection error -- please try again.').show();
        });
    });

    // Search Form Actions
    $(document).on('click', function (event) {
        let selectUl = $('.mage_input_select_list');
        if (!$(event.target).parents().hasClass('mage_input_select') && selectUl.is(':visible')) {
            let target = $('.mage_input_select input');
            target.each(function (index) {
                let input = $(this).val().toLowerCase();
                let flag = 0;
                $(this).parents('.mage_input_select').find('li').filter(function () {
                    if ($(this).attr('data-route').toLowerCase() === input) {
                        flag = 1;
                        mage_bus_dropping_point(selectUl);
                    }
                });
                if (flag < 1) {
                    $(this).val('');
                }
            });
            selectUl.slideUp(200);
        }
    });
    $(document).on({
        keyup: function () {
            console.log('kkkdk')
            let input = $(this).val().toLowerCase();
            $(this).parents('.mage_input_select').find('.mage_input_select_list').find('li').filter(function () {
                $(this).toggle($(this).attr('data-route').toLowerCase().indexOf(input) > -1);
            });
        },
        click: function () {
            $(this).parents('.mage_input_select').find('.mage_input_select_list').slideDown(200);
            $(this).parents('.mage_input_select').find('.mage_input_select_list_static').slideDown(200);
            $(this).parents('.route-input-wrap').addClass('activeMageSelect');
            if ($(this).parents('.mage_input_select').hasClass('mage_bus_boarding_point')) {
                if ($('.mage_bus_dropping_point').find('.route-input-wrap').hasClass('activeMageSelect')) {
                    $('.mage_bus_dropping_point').find('.route-input-wrap').removeClass('activeMageSelect');
                    $('.mage_bus_dropping_point').find('.mage_input_select_list').slideUp(200);
                    $('.mage_bus_dropping_point').find('.mage_input_select_list_static').slideUp(200);
                }
            }
        },
        blur: function () {
            $(this).parents('.route-input-wrap').removeClass('activeMageSelect');
        }
    }, '.mage_input_select input');

    $(document).on({
        click: function () {
            let route = $(this).attr('data-route');
            $(this).parents('.mage_input_select_list').slideUp(200).parents('.mage_input_select').find('input').val(route);
            mage_bus_dropping_point($(this));
            $(this).parents('.mage_input_select_list').siblings('.route-input-wrap').removeClass('activeMageSelect');
        }
    }, '.mage_input_select_list li');

    $(document).on({
        click: function () {
            let route = $(this).attr('data-route');
            $(this).parents('.mage_input_select_list_static').slideUp(200).parents('.mage_input_select').find('input').val(route);
            mage_bus_dropping_point($(this));
            $(this).parents('.mage_input_select_list_static').siblings('.route-input-wrap').removeClass('activeMageSelect');
        }
    }, '.mage_input_select_list_static li');

    // Minimul Design Script
    $(document).on({
        click: function () {
            var $this = $(this);
            $this.parents('.mage-search-brief-row').toggleClass('opened');
            $this.parents('.mage-search-brief-row').siblings('.mage-bus-booking-wrapper').slideToggle('fast');
        }
    }, '.mage-bus-detail-action');
    // Minimul Design Script END

    $(document).ready(function () {
        $('.wbbm_entire_switch_wrapper #wbbm_entire_bus').click(function (e) {
            const $this = $(this);
            const priceInfoEl = $this.parents('.mage_search_list');
            const price = priceInfoEl.find('.wbbm_entire_switch_wrapper').attr('data-entire-price')
            if ($(this)[0].hasAttribute('checked')) {
                $(this).attr('checked', false);
                $(this).val('0');
                $('input[name=adult_quantity]').closest('.mage_center_space').show();
                $('input[name=child_quantity]').closest('.mage_center_space').show();
                $('input[name=infant_quantity]').closest('.mage_center_space').show();
                $('div.entire').hide();
            }
            else {
                $(this).attr('checked', true);
                $(this).val('1');
                $('input[name=adult_quantity]').closest('.mage_center_space').hide();
                $('input[name=child_quantity]').closest('.mage_center_space').hide();
                $('input[name=infant_quantity]').closest('.mage_center_space').hide();

                priceInfoEl.find('.mage_seat_qty').val(0);
                const infoArea = priceInfoEl.find('.mage_customer_info_area');
                infoArea.find('.adult').empty().hide();
                infoArea.find('.child').empty().hide();
                infoArea.find('.infant').empty().hide();
                let passenger_info_title = $('#wbbm_entire_bus').attr('data-ticket-title');

                let passenger_info_form = $('.mage_hidden_customer_info_form').html();
                $('div.entire').html(passenger_info_form);
                let passenger_info_user_type = $('div.entire .mage_form_list input[name="wbbm_user_type[]"]');
                $('div.entire .mage_form_list .mage_form_list_title h4').html(passenger_info_title);
                $(passenger_info_user_type).val('entire');
                $('div.entire .mage_form_list').show();
                $('div.entire').show();
            }
            mageSubTotal($(this));

        });
    })

    function mage_bus_dropping_point(target) {
        if (target.parents().hasClass('mage_bus_boarding_point')) {
            var boarding_point = target.attr('data-route');

            if (boarding_point != undefined) {
                $.ajax({
                    type: 'POST',
                    // url: wbtm_ajax.wbtm_ajaxurl,
                    url: WbbmAjax.ajax_url,
                    data: { "action": "wbbm_load_dropping_point", "boarding_point": boarding_point, nonce: WbbmAjax.nonce },
                    beforeSend: function () {
                        $('#bus_end_route').val('');
                        $('#wbtm_dropping_point_list').slideUp(200);
                        $('#wbtm_show_msg').show();
                        $('#wbtm_show_msg').html('<span>Loading..</span>');
                    },
                    success: function (data) {
                        $('#wbtm_show_msg').hide();
                        $('#bus_end_route').val('');
                        $('.mage_bus_dropping_point .mage_input_select_list ul').html(data);
                        // $('#wbtm_dropping_point_list').slideDown(200);
                        $('#bus_end_route').trigger('click');
                        $('#wbtm_dropping_point_list').siblings('.route-input-wrap').addClass('activeMageSelect');
                    }
                });
                return false;
            }
        }
    }

    function mageExtServiceQty(target, value) {
        let minSeat = parseInt(target.attr('min'));
        let maxSeat = parseInt(target.attr('max'));
        target.siblings('.mage_qty_inc , .mage_qty_dec').removeClass('mage_disabled');
        if (value < minSeat || isNaN(value) || value === 0) {
            value = minSeat;
            target.siblings('.mage_qty_dec').addClass('mage_disabled');
        }
        if (value > maxSeat) {
            value = maxSeat;
            target.siblings('.mage_es_qty_plus').addClass('mage_disabled');
        }
        target.val(value);
        mageError(value, target);
        mageSubTotal(target);
    }

    function mageTicketQty(target, value) {
        let minSeat = parseInt(target.attr('min'));
        let maxSeat = get_max_qty(target);
        target.siblings('.mage_qty_inc , .mage_qty_dec').removeClass('mage_disabled');
        if (value < minSeat || isNaN(value) || value === 0) {
            value = minSeat;
            target.siblings('.mage_qty_dec').addClass('mage_disabled');
        }
        if (value > maxSeat) {
            value = maxSeat;
            target.siblings('.mage_qty_inc').addClass('mage_disabled');
        }
        target.val(value);
        mageError(value, target);
        mageSubTotal(target);
    }
    function get_max_qty(target) {
        let available_quantity = parseInt($('[name="available_quantity"]').val()) || 0;
        let adult_quantity = parseInt($('[name="adult_quantity"]').val()) || 0;
        let child_quantity = parseInt($('[name="child_quantity"]').val()) || 0;
        let infant_quantity = parseInt($('[name="infant_quantity"]').val()) || 0;
        return available_quantity - (adult_quantity + child_quantity + infant_quantity) + (parseInt(target.val()) || 0);
    }

    function mageExtBagQty(target, value) {
        let minSeat = parseInt(target.attr('min'));
        let maxSeat = parseInt(target.attr('max'));
        target.siblings('.mage_qty_inc , .mage_qty_dec').removeClass('mage_disabled');
        if (value < minSeat || isNaN(value) || value === 0) {
            value = minSeat;
            target.siblings('.mage_eb_qty_minus').addClass('mage_disabled');
        }
        if (value > maxSeat) {
            value = maxSeat;
            target.siblings('.mage_eb_qty_plus').addClass('mage_disabled');
            return;
        }
        target.val(value);
        const perExtBagPrice = target.attr('data-price');
        mageError(value, target);
        mageSubTotal(target)
    }

    function mageError(value, target) {
        if (value > 0) {
            target.removeClass('mage_error');
        } else {
            target.addClass('mage_error').trigger('focus');
        }
    }
    function mageSubTotal(target) {

        let currentTarget = target.parents('.mage_search_list');
        let subTotal = 0;
        currentTarget.find('.mage_form[data-price]').each(function (index) {
            let unitPrice = parseFloat($(this).attr('data-price'));
            let ticket = parseFloat($(this).val());
            subTotal = subTotal + (unitPrice * ticket > 0 ? unitPrice * ticket : 0);
        });

        // Extra Service
        currentTarget.find('.wbbm_extra_service_table tbody tr').each(function () {
            const es = $(this).find('.extra-qty-box');
            const esUnitPrice = parseFloat(es.attr('data-price'));
            const esQty = parseFloat(es.val());
            subTotal = subTotal + (esUnitPrice * esQty > 0 ? esUnitPrice * esQty : 0);
        });
        // Extra Service END

        // Ext Bag Price
        currentTarget.find('.mage_form_list').each(function () {
            const extBag = $(this).find('.extra_bag_qty');
            const extBagUnitPrice = parseFloat(extBag.attr('data-price'));
            const extQty = parseFloat(extBag.val());
            subTotal = subTotal + (extBagUnitPrice * extQty > 0 ? extBagUnitPrice * extQty : 0);
        })
        // Ext Bag Price END
        currentTarget.find('.mage_sub_total span').html(wbbm_woo_price_format(subTotal));
        mageCustomerInfoForm(currentTarget);
    }

    function mageCustomerInfoForm(target) {
        if (target.children().hasClass('mage_hidden_customer_info_form')) {
            target.find('.mage_form[data-price]').each(function (index) {
                let mageTicketType = $(this).attr('data-ticket-type');
                let mageTargetClass = '.' + mageTicketType;
                let currentTarget = target.find(mageTargetClass);
                let ticketQTy = parseInt($(this).val());
                if (ticketQTy < 1) {
                    currentTarget.empty().slideUp(500);
                } else {
                    let currentFormLength = currentTarget.find('.mage_form_list').length;
                    if (currentFormLength < ticketQTy) {
                        let mageTicketTitle = $(this).attr('data-ticket-title');
                        for (let i = currentFormLength; i < ticketQTy; i++) {
                            target.find('.mage_hidden_customer_info_form h4').html(mageTicketTitle + (i + 1));
                            target.find('.mage_hidden_customer_info_form input[name="wbbm_user_type[]"]').val(mageTicketType);
                            let mageFormInfo = target.find('.mage_hidden_customer_info_form').html();
                            currentTarget.append(mageFormInfo).slideDown('fast').find('.mage_form_list').slideDown(500);
                        }
                    }
                    if (currentFormLength > ticketQTy) {

                        while (currentFormLength > ticketQTy) {
                            currentTarget.find('.mage_form_list:last-child').slideUp(500).remove();
                            currentFormLength = currentTarget.find('.mage_form_list').length;
                        }
                    }
                }
            });
        }
        else {
            target.find('.mage_customer_info_area .child').html('<input type="hidden" name="custom_reg_user" value="no" />');
        }
    }


}(jQuery));