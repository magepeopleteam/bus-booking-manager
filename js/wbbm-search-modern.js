/**
 * Modern search form behaviour.
 *
 * Only the pieces the redesign adds: the swap button, the segmented
 * trip-type control, and the datepicker skin. Route lookup, the dropping
 * point AJAX and the return-leg toggle stay in mage_style.js.
 */
(function ($) {
    'use strict';

    var SCOPE = '.wbbm-modern-search';

    /**
     * Keep focus where it is. The route inputs clear themselves on blur
     * unless the value is re-set by a list click, so letting the swap
     * button take focus would wipe the field we are about to read.
     */
    $(document).on('mousedown', SCOPE + ' .wbbm-swap', function (e) {
        e.preventDefault();
    });

    $(document).on('click', SCOPE + ' .wbbm-swap', function (e) {
        e.preventDefault();

        var $form = $(this).closest('form');
        var $from = $form.find('[name="bus_start_route"]').first();
        var $to = $form.find('[name="bus_end_route"]').first();
        var from = $from.val();
        var to = $to.val();

        if (!from && !to) {
            return;
        }

        $form.find('.mage_input_select_list, .mage_input_select_list_static').stop(true, true).hide();
        $form.find('.route-input-wrap').removeClass('activeMageSelect');

        $from.val(to);
        $to.val(from);

        // Sites that load dropping points over AJAX blank the second field
        // while the request is in flight; put the value back once it settles.
        var restore = function () {
            if ($to.val() !== from) {
                $to.val(from);
            }
        };
        setTimeout(restore, 60);
        setTimeout(restore, 500);

        $from.trigger('change');
        $to.trigger('change');
    });

    /**
     * Segmented trip type. :has() handles this on its own in current
     * browsers; the class keeps older ones in step.
     */
    function syncTripType() {
        $(SCOPE + ' .mage_form_radio label').each(function () {
            $(this).toggleClass('is-active', $(this).find('input[type="radio"]').is(':checked'));
        });

        // The return cell is animated away rather than removed, so the grid
        // is told to close the gap instead of leaving an empty column.
        $(SCOPE + ' form.mage_form').each(function () {
            var oneWay = $(this).find('input[name="bus-r"]:checked').val() === 'oneway';
            $(this).find('.wbbm-fields').toggleClass('wbbm-no-return', oneWay);
        });
    }

    $(document).on('change', SCOPE + ' input[name="bus-r"]', function () {
        syncTripType();

        // mage_style.js animates the return leg with slideUp/slideDown, which
        // leaves display:block inline and flattens the cell's own layout.
        var $leg = $('.mage_return_date');
        setTimeout(function () {
            if ($leg.is(':visible')) {
                $leg.css('display', '');
            }
        }, 360);
    });

    /**
     * The date fields display the site's own date format while the form
     * submits Y-m-d, so every pick is mirrored into the hidden input beside
     * it. Doing it here rather than with the datepicker's altField keeps the
     * seven picker setups in wbbm_custom_public_script.js untouched.
     */
    var ISO_FORMAT = 'yy-mm-dd';

    function pickerFormat() {
        return $('#all_date_picker_info').data('date_format') || ISO_FORMAT;
    }

    function syncIsoDate($display) {
        var $hidden = $('#' + $display.attr('id') + '_iso');
        if (!$hidden.length) {
            return;
        }

        var value = $.trim($display.val() || '');
        if (!value) {
            $hidden.val('');
            return;
        }

        try {
            $hidden.val($.datepicker.formatDate(ISO_FORMAT, $.datepicker.parseDate(pickerFormat(), value)));
        } catch (e) {
            // an unparseable value is left for the server to reject rather
            // than silently dropped
            $hidden.val(value);
        }
    }

    /**
     * The return field's placeholder is rendered as tomorrow; once a journey
     * date is picked it becomes the day after that one, so the hint stays
     * truthful rather than pointing at a date already in the past.
     */
    function syncReturnPlaceholder() {
        var $journey = $('#j_date');
        var $return = $('#r_date');
        if (!$journey.length || !$return.length) {
            return;
        }

        var value = $.trim($journey.val() || '');
        if (!value) {
            return;
        }

        try {
            var next = $.datepicker.parseDate(pickerFormat(), value);
            next.setDate(next.getDate() + 1);
            $return.attr('placeholder', $.datepicker.formatDate(pickerFormat(), next));
        } catch (e) {
            // leave the server-rendered placeholder alone
        }
    }

    $(document).on('change', '#j_date, #r_date', function () {
        syncIsoDate($(this));
    });

    $(document).on('change', '#j_date', function () {
        syncReturnPlaceholder();
    });

    /**
     * jQuery UI appends one shared picker to <body>, so the skin is put on
     * and taken off as the field that opened it changes.
     */
    $(document).on('focus click', '.hasDatepicker', function () {
        var modern = $(this).closest(SCOPE).length > 0;
        setTimeout(function () {
            $('#ui-datepicker-div').toggleClass('wbbm-datepicker-modern', modern);
        }, 0);
    });

    /**
     * Return trips: pick both legs before booking either.
     *
     * mage_style.js's wbbmProceedToBook() calls this before it opens the
     * booking drawer. Returning true means "handled, do not book" -- which
     * happens only for a card in the outbound list of a search that actually
     * rendered a return list. The customer is moved to the return list and
     * the outbound card is marked as held so they can find it again.
     *
     * The validation the customer sees (seat count, availability, pick-up
     * point) has already run by the time this is called.
     */
    function returnWrapper() {
        var $wrappers = $('.mage-search-res-wrapper');
        if ($wrappers.length < 2) {
            return null;
        }

        var $candidate = $wrappers.eq(1);
        return $candidate.find('.mage_search_list').length ? $candidate : null;
    }

    function strings() {
        return window.WbbmSearchModern || {};
    }

    /* ---- reading a leg's selection off its card ---- */

    var outboundSelection = null;
    // The outbound card itself, not just what was read off it. WooCommerce
    // mode adds each leg to the cart by posting that leg's own <form>, so the
    // element has to be reachable when the RETURN leg is confirmed.
    var outboundCard = null;

    function fieldVal($searchList, name) {
        var $el = $searchList.find('[name="' + name + '"]').first();
        return $el.length ? $el.val() : '';
    }

    function money(value) {
        return typeof window.wbbm_woo_price_format === 'function'
            ? window.wbbm_woo_price_format(value)
            : (Math.round(value * 100) / 100).toFixed(2);
    }

    /**
     * Same three sources mage_style.js's wbbmCalcSubtotal() adds up -- seats,
     * extra services and extra bags -- so the figure shown here matches the
     * one the drawer works out for itself.
     */
    function legSubtotal($searchList) {
        var subtotal = 0;

        $searchList.find('.mage_form[data-price]').each(function () {
            subtotal += (parseFloat($(this).attr('data-price')) || 0) * (parseFloat($(this).val()) || 0);
        });
        $searchList.find('.wbbm_extra_service_table tbody tr .extra-qty-box').each(function () {
            subtotal += (parseFloat($(this).attr('data-price')) || 0) * (parseFloat($(this).val()) || 0);
        });
        $searchList.find('.mage_form_list .extra_bag_qty').each(function () {
            subtotal += (parseFloat($(this).attr('data-price')) || 0) * (parseFloat($(this).val()) || 0);
        });

        return subtotal;
    }

    function readSelection($searchList) {
        var tickets = [];

        $searchList.find('.mage_form[data-price]').each(function () {
            var qty = parseInt($(this).val(), 10) || 0;
            if (qty < 1) {
                return;
            }
            tickets.push({
                label: ($(this).attr('data-ticket-title') || '') + ' \u00d7 ' + qty,
                amount: (parseFloat($(this).attr('data-price')) || 0) * qty
            });
        });

        var start = fieldVal($searchList, 'start_stops');
        var end = fieldVal($searchList, 'end_stops');

        return {
            bus: $.trim($searchList.find('.bus-title').first().text()),
            route: start && end ? start + ' \u2192 ' + end : '',
            date: fieldVal($searchList, 'journey_date'),
            time: fieldVal($searchList, 'user_start_time'),
            tickets: tickets,
            subtotal: legSubtotal($searchList),
            // Each bus carries its own offline tax rate, so the drawer cannot
            // apply the return leg's rate to both legs when it adds them up.
            taxRate: parseFloat($searchList.find('.mage_offline_tax_rate').val()) || 0,
            // What it takes to actually BOOK this leg, as opposed to describe
            // it. Held on the outbound card so the return leg's Confirm can
            // post both legs in one request -- without this the outbound was
            // only ever drawn on screen, never submitted, and the customer
            // ended up with a one-way booking.
            payload: legPayload($searchList)
        };
    }

    /**
     * The per-leg half of the offline booking POST. Deliberately mirrors the
     * per-leg field names in mage_style.js's postData, because the server
     * hands each leg straight to the same single-leg validator that has
     * always run (wbbm_validate_offline_booking_request()).
     */
    function legPayload($searchList) {
        return {
            bus_id: fieldVal($searchList, 'bus_id'),
            journey_date: fieldVal($searchList, 'journey_date'),
            start_stops: fieldVal($searchList, 'start_stops'),
            end_stops: fieldVal($searchList, 'end_stops'),
            user_start_time: fieldVal($searchList, 'user_start_time'),
            bus_start_time: fieldVal($searchList, 'bus_start_time'),
            mage_pickpoint: $searchList.find('select[name="mage_pickpoint"]').val() || '',
            adult_quantity: $searchList.find('[name="adult_quantity"]').val() || 0,
            child_quantity: $searchList.find('[name="child_quantity"]').val() || 0,
            infant_quantity: $searchList.find('[name="infant_quantity"]').val() || 0,
            entire_quantity: $searchList.find('[name="entire_quantity"]').is(':checked') ? 1 : 0
        };
    }

    /**
     * The held outbound leg, for mage_style.js's Confirm handler. Returns null
     * on a one-way search, or when the card being confirmed IS the outbound --
     * in both cases there is only one leg and it posts on its own.
     */
    window.wbbmHeldOutboundLeg = function ($searchList) {
        var $return = returnWrapper();

        if (!outboundSelection || !$return || !$searchList || !$searchList.length) {
            return null;
        }

        if (!$.contains($return[0], $searchList[0])) {
            return null;
        }

        return outboundSelection.payload;
    };

    /**
     * The held outbound card, for the WooCommerce drawer's Confirm. Returns
     * null on a one-way search, or when the card being confirmed IS the
     * outbound -- in both cases there is only one leg to add.
     */
    window.wbbmHeldOutboundCard = function ($searchList) {
        var $return = returnWrapper();

        if (!outboundCard || !$return || !$searchList || !$searchList.length) {
            return null;
        }

        if (!$.contains($return[0], $searchList[0])) {
            return null;
        }

        return outboundCard;
    };

    /** Forgets the held leg once its booking is recorded, so a second search starts clean. */
    window.wbbmClearHeldOutboundLeg = function () {
        outboundSelection = null;
        outboundCard = null;
        $('.mage_search_list').removeClass('wbbm-leg-chosen');
        $('.wbbm-leg-badge').remove();
        $('.wbbm-return-prompt').remove();
    };

    /** A compact card describing one leg, used in the prompt and the drawer. */
    function legCard(selection, tag) {
        var $card = $('<div class="wbbm-leg-card"></div>');
        var meta = [selection.route, selection.date, selection.time].filter(Boolean).join(' \u00b7 ');

        $('<span class="wbbm-leg-card-tag"></span>').text(tag).appendTo($card);
        $('<span class="wbbm-leg-card-bus"></span>').text(selection.bus).appendTo($card);

        if (meta) {
            $('<span class="wbbm-leg-card-meta"></span>').text(meta).appendTo($card);
        }

        if (selection.tickets.length) {
            var $lines = $('<ul class="wbbm-leg-card-lines"></ul>').appendTo($card);
            selection.tickets.forEach(function (ticket) {
                $('<li></li>')
                    .append($('<span></span>').text(ticket.label))
                    .append($('<span></span>').text(money(ticket.amount)))
                    .appendTo($lines);
            });
        }

        $('<div class="wbbm-leg-card-total"></div>')
            .append($('<span></span>').text(strings().subtotalLabel || 'Subtotal'))
            .append($('<span></span>').text(money(selection.subtotal)))
            .appendTo($card);

        return $card;
    }

    function markOutboundChosen($searchList) {
        $('.mage_search_list').removeClass('wbbm-leg-chosen');
        $('.wbbm-leg-badge').remove();

        $searchList.addClass('wbbm-leg-chosen');
        $searchList.find('.mage-bus-title').first().append(
            $('<span class="wbbm-leg-badge"></span>').text(strings().chosenLabel || 'Selected')
        );

        /*
         * Collapse the leg once it is held: the choice is made, so the seat
         * and fare panel is just in the way of the return list below. Mirrors
         * what .mage-bus-detail-action's own toggle does (mage_style.js:657)
         * so clicking View afterwards still opens it again.
         */
        $searchList.find('.mage-search-brief-row').removeClass('opened');
        // stop(true, true) first: jQuery queues animations per element, so a
        // Book Now during View's open animation would otherwise leave this
        // collapse waiting behind it
        $searchList.find('.mage-bus-booking-wrapper').stop(true, true).slideUp('fast');
    }

    function showReturnPrompt($wrapper) {
        $wrapper.find('.wbbm-return-prompt').remove();

        var $prompt = $('<div class="wbbm-return-prompt" role="status"></div>').prependTo($wrapper);
        $('<p class="wbbm-return-prompt-title"></p>')
            .text(strings().returnPrompt || 'Outbound trip selected. Now choose your return bus.')
            .appendTo($prompt);

        if (outboundSelection) {
            $prompt.append(legCard(outboundSelection, strings().outboundLabel || 'Outbound'));
        }
    }

    function scrollToElement($el) {
        var top = $el.offset().top - 24;
        var $bar = $('#wpadminbar');
        if ($bar.length) {
            top -= $bar.outerHeight();
        }
        $('html, body').animate({ scrollTop: Math.max(top, 0) }, 400);
    }

    window.wbbmAdvanceToReturnLeg = function ($searchList) {
        var $return = returnWrapper();
        if (!$return || !$searchList || !$searchList.length) {
            return false;
        }

        // only cards in the outbound list hand off; the return list books
        var $outbound = $('.mage-search-res-wrapper').first();
        if (!$.contains($outbound[0], $searchList[0])) {
            return false;
        }

        // a card that is already held books on the next click, otherwise the
        // outbound leg could never be confirmed at all
        if ($searchList.hasClass('wbbm-leg-chosen')) {
            return false;
        }

        outboundSelection = readSelection($searchList);
        outboundCard = $searchList;

        markOutboundChosen($searchList);
        showReturnPrompt($return);
        scrollToElement($return.find('.wbbm-return-prompt'));

        return true;
    };

    /**
     * Both legs in the drawer.
     *
     * This runs after mage_style.js's own click handler (it binds first, so
     * it wins the delegation order), by which point the drawer is open and
     * its summary filled in for the return bus. The held outbound leg is
     * inserted above it, and the existing rows get a "Return" heading.
     *
     * The Total stays the return's own figure and is relabelled to say so:
     * this drawer confirms one booking, and quietly adding the outbound into
     * the total would misstate what the customer is about to pay.
     */
    function showBothLegsInDrawer($searchList) {
        /*
         * The Custom Payment modal only. The WooCommerce drawer carries the
         * .mage_offline_modal class too (it reuses that modal's chrome -- see
         * inc/clean/layout/book-now-area.php), so an unfiltered lookup matched
         * it as well and inserted a second Outbound card after its <h4>, on top
         * of the pair wbbmOpenWcDrawer() had already rendered into
         * .wbbm-wc-summary. That is what showed the outbound leg twice.
         */
        var $modal = $searchList.find('.mage_offline_modal').not('.wbbm-wc-drawer');
        if (!$modal.length || !$modal.hasClass('is-open')) {
            return;
        }

        var $summary = $modal.find('.mage_offline_summary');
        var plainRows = '.mage_offline_summary_bus, .mage_offline_summary_route,'
            + ' .mage_offline_summary_date, .mage_offline_summary_time,'
            + ' .mage_offline_summary_rows, .mage_offline_summary_subtotal';

        // always start from the drawer's own markup, so a reopen that does not
        // qualify gets the plain rows back
        $summary.find('.wbbm-drawer-leg').remove();
        $summary.find(plainRows).removeClass('wbbm-row-replaced');

        var $totalLabel = $summary.find('.mage_offline_summary_total > span').first();
        var $return = returnWrapper();
        var isReturnLeg = $return && $.contains($return[0], $searchList[0]);

        if (!outboundSelection || !isReturnLeg) {
            $totalLabel.text(strings().totalLabel || 'Total');
            return;
        }

        var $outbound = $('<div class="wbbm-drawer-leg"></div>')
            .append(legCard(outboundSelection, strings().outboundLabel || 'Outbound'))
            .append($('<p class="wbbm-leg-card-note"></p>').text(strings().bothLegsNote || ''));

        $summary.find('h4').after($outbound);

        /*
         * The leg being booked gets the same card as the outbound, built from
         * its own row so the two read as a pair. The drawer's plain rows say
         * the same thing, so they are hidden rather than left duplicated --
         * tax and total stay, they are the figures being charged.
         */
        $summary.find('.mage_offline_summary_bus').before(
            $('<div class="wbbm-drawer-leg"></div>').append(
                legCard(readSelection($searchList), strings().returnLabel || 'Return')
            )
        );
        $summary.find(plainRows).addClass('wbbm-row-replaced');

        /*
         * Both legs are booked and charged together now, so the drawer has to
         * show what the customer will actually owe. mage_style.js filled these
         * figures in for the return leg alone a moment ago (wbbmOpenOfflineModal
         * runs first); the outbound's own subtotal and its own bus's tax rate
         * are added on top.
         *
         * This is a preview -- wbbm_validate_booking_legs() recomputes every
         * figure server-side and that is what is charged and recorded.
         */
        var outSubtotal = parseFloat(outboundSelection.subtotal) || 0;
        var outTax = outSubtotal * ((parseFloat(outboundSelection.taxRate) || 0) / 100);

        addToAmountRow($summary.find('.mage_offline_summary_subtotal_val'), outSubtotal);
        addToAmountRow($summary.find('.mage_offline_summary_tax_val'), outTax);
        addToAmountRow($summary.find('.mage_offline_summary_total_val'), outSubtotal + outTax);

        // The tax row hides itself when the return leg is untaxed, but the
        // outbound leg may still be taxed -- show it if there is tax to show.
        if (outTax > 0) {
            $summary.find('.mage_offline_summary_tax').show();
        }

        $totalLabel.text(strings().returnTotalLabel || 'Trip total');
    }

    /**
     * Adds an amount to a currency cell the drawer has already written, keeping
     * the site's own currency formatting. The cell's own number is recovered by
     * stripping everything that is not part of one -- reading it back beats
     * re-deriving the return leg's figures here and risking the two disagreeing.
     */
    function addToAmountRow($cell, amount) {
        if (!$cell.length || !amount) {
            return;
        }

        var current = parseFloat(String($cell.text()).replace(/[^0-9.\-]/g, '')) || 0;
        $cell.text(money(current + amount));
    }

    $(document).on('click', 'button.mage_book_now', function () {
        showBothLegsInDrawer($(this).closest('.mage_search_list'));
    });

    /* ------------------------------------------- WooCommerce booking drawer */
    /*
     * mage_style.js's wbbmProceedToBook() calls this for a bus set to
     * WooCommerce. Returning true means "handled": the drawer opens on a
     * summary of what is being booked, and Confirm posts the booking form so
     * WooCommerce adds the seats to the cart, then loads its own checkout into
     * the drawer's second stage for billing and payment.
     *
     * There is no payment-method step here on purpose -- WooCommerce owns the
     * method, the billing fields and the payment.
     */

    function wcDrawer($searchList) {
        var $drawer = $searchList.find('.wbbm-wc-drawer');
        return $drawer.length ? $drawer : null;
    }

    function showStage($drawer, stage) {
        $drawer.find('.wbbm-wc-stage').attr('hidden', true);
        $drawer.find('.wbbm-wc-stage-' + stage).removeAttr('hidden');
        $drawer.find('.mage_offline_modal_foot').attr('hidden', true);
        $drawer.find('.wbbm-wc-foot-' + stage).removeAttr('hidden');
    }

    window.wbbmOpenWcDrawer = function ($searchList) {
        var $drawer = wcDrawer($searchList);
        if (!$drawer) {
            return false;
        }

        /*
         * Both legs, when this is the return leg of a return trip -- the drawer
         * has to show what is actually going into the cart, and Confirm now
         * adds the held outbound as well as this one.
         */
        var $return = returnWrapper();
        var isReturnLeg = $return && $.contains($return[0], $searchList[0]);
        var $summary = $drawer.find('.wbbm-wc-summary').empty();
        var thisLeg = readSelection($searchList);
        var bothLegs = !!(outboundSelection && isReturnLeg);

        $summary.toggleClass('is-multi-leg', bothLegs);

        if (bothLegs) {
            $summary
                .append(legCard(outboundSelection, strings().outboundLabel || 'Outbound'))
                .append(legCard(thisLeg, strings().returnLabel || 'Return'))
                .append(
                    // Both legs go into one cart and are paid for together, so
                    // the drawer has to show the figure that will actually be
                    // charged -- two subtotals and no total leaves the customer
                    // adding them up. WooCommerce recalculates authoritatively
                    // at checkout; this is the preview.
                    $('<div class="wbbm-wc-trip-total"></div>')
                        .append($('<span></span>').text(strings().returnTotalLabel || 'Trip total'))
                        .append($('<span></span>').text(
                            money((parseFloat(outboundSelection.subtotal) || 0) + (parseFloat(thisLeg.subtotal) || 0))
                        ))
                );
        } else {
            $summary.append(legCard(thisLeg, strings().yourTripLabel || 'Your trip'));
        }

        $drawer.find('.mage_offline_submit_error').hide().empty();
        $drawer.find('.wbbm-wc-checkout-frame').attr('src', 'about:blank');
        showStage($drawer, 'review');
        $drawer.addClass('is-open');

        return true;
    };

    $(document).on('click', '.wbbm-wc-confirm', function () {
        var $button = $(this);
        var $drawer = $button.closest('.wbbm-wc-drawer');
        var $form = $drawer.closest('form');
        var $searchList = $drawer.closest('.mage_search_list');
        var $error = $drawer.find('.mage_offline_submit_error').hide().empty();

        if (!$form.length) {
            return;
        }

        $button.prop('disabled', true).addClass('is-busy');

        /*
         * A return trip is two buses. The outbound leg was held back when the
         * customer picked it (wbbmAdvanceToReturnLeg) and never added to the
         * cart -- which is why only the return bus used to reach checkout.
         *
         * Each bus card carries its own <form> and its own linked product, so
         * both legs are added by posting their own forms, outbound first.
         */
        var $heldOutbound = (typeof window.wbbmHeldOutboundCard === 'function')
            ? window.wbbmHeldOutboundCard($searchList)
            : null;

        var legs = [];

        if ($heldOutbound && $heldOutbound.length) {
            legs.push({
                label: strings().outboundLabel || 'Outbound',
                $form: $heldOutbound.find('form').first(),
                productId: $heldOutbound.find('.wbbm-wc-drawer').attr('data-wbbm-product-id') || ''
            });
        }

        legs.push({
            label: strings().returnLabel || 'Return',
            $form: $form,
            productId: $drawer.attr('data-wbbm-product-id') || ''
        });

        function failWith(message) {
            $error.text(message).show();
            $button.prop('disabled', false).removeClass('is-busy');
        }

        var missing = legs.filter(function (leg) {
            return !leg.productId || !leg.$form.length;
        });

        if (missing.length) {
            // Checked before anything is added, so a return trip whose outbound
            // bus has no linked product never half-fills the cart.
            failWith(strings().noProduct || 'This bus is not connected to WooCommerce yet. Open it in the admin and save it once.');
            return;
        }

        /*
         * Strictly sequential. WooCommerce's cart lives in the session, and two
         * add-to-cart posts in flight at once can each write back a cart that
         * never saw the other -- silently dropping a leg, which is the very bug
         * being fixed here.
         */
        function addLeg(index) {
            if (index >= legs.length) {
                var url = $drawer.attr('data-wbbm-checkout-url');
                if (!url) {
                    failWith(strings().cartFailed || 'Could not add this to the cart. Please try again.');
                    return;
                }

                // Both legs are in the cart; forget the held one so re-opening
                // the drawer cannot add the outbound a second time.
                if (typeof window.wbbmClearHeldOutboundLeg === 'function') {
                    window.wbbmClearHeldOutboundLeg();
                }

                $drawer.find('.wbbm-wc-checkout-frame').attr('src', url);
                showStage($drawer, 'checkout');
                $button.prop('disabled', false).removeClass('is-busy');
                return;
            }

            var leg = legs[index];

            /*
             * The same POST the hidden submit would have made, minus the
             * navigation: a submit button's name/value is not part of FormData,
             * so add-to-cart is appended by hand. Everything else -- seats,
             * stops, journey date, passenger rows -- rides along exactly as
             * before.
             */
            var data = new window.FormData(leg.$form[0]);
            data.append('add-to-cart', leg.productId);

            window.fetch(window.location.href, {
                method: 'POST',
                body: data,
                credentials: 'same-origin'
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.text();
                })
                .then(function () {
                    addLeg(index + 1);
                })
                .catch(function () {
                    // Name the leg: on a two-bus booking "could not add this to
                    // the cart" leaves the customer with no idea which failed,
                    // or whether the other one made it.
                    var message = strings().cartFailed || 'Could not add this to the cart. Please try again.';
                    failWith(legs.length > 1 ? (leg.label + ': ' + message) : message);
                });
        }

        addLeg(0);
    });

    $(function () {
        syncTripType();
        $(SCOPE + ' .mage_return_date').css('display', '');
        $('#j_date, #r_date').each(function () {
            syncIsoDate($(this));
        });
        syncReturnPlaceholder();
    });
})(jQuery);
