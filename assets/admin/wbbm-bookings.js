(function($) {
    /**
     * Attach the jQuery UI datepicker once per field.
     *
     * Skips inputs the browser already handles (type="date"), which would
     * otherwise end up with two competing pickers, and fields that are
     * already bound — important because panels are re-rendered in place and
     * this runs again each time.
     */
    function bindDatepicker(selector, options) {
        $(selector)
            .not('[type="date"]')
            .not('.hasDatepicker')
            .datepicker(options || { dateFormat: "yy-mm-dd" });
    }

    function initDatepickers() {
        bindDatepicker(".datepicker_has", { dateFormat: "yy-mm-dd", minDate: 0 });
        bindDatepicker("#journey_date");
        bindDatepicker("#booking_date");
        bindDatepicker("#from_date");
        bindDatepicker("#to_date");
        bindDatepicker("#ja_date");
        // Return date was never bound here, so its picker never opened.
        bindDatepicker("#j_date, #r_date", { dateFormat: "yy-mm-dd", minDate: 0 });
    }

    // Hub panels swap in place; re-bind whatever arrived with the new markup.
    $(document).on('wbbm-hub:tab-loaded', initDatepickers);

    $(document).ready(function() {

        initDatepickers();


        $(document).on("focus", "#user_dob", function () {
            $("#user_dob").datepicker({
                dateFormat: "yy-mm-dd"
            });
        });


        $(document).on('change','[name="bus_id"]',function (e){
            let  $this = $(this);
            let  bus_id = $(this).val();
            let target=$this.closest('.mage-custom-filter-area-outer').find('.mage-custom-filter-area');

            $.ajax({
                url: ajaxurl,
                type: 'post',
                dataType: 'html',
                data: {
                    bus_id: bus_id,
                    action: 'wbbm_custom_field_for_single_bus',
                    nonce: window.wbbmProAdmin ? window.wbbmProAdmin.nonce : ''
                },
                beforeSend: function() {
                    dLoader(target);
                },
                success: function(data) {
                    $('.extra_field_for_single_bus').html(data);
                    $('.extra_field_for_single_bus').parents('.extra-filters-wrapper').show();
                    dLoaderRemove(target);
                }
            });
        });

        // Bus Report Detail
        $('.wbbm_bus_detail--report .wbbm_detail_inside').click(function() {
            let $this = $(this);
            let parent = $this.parents('tr');
            let order_id = $this.parent().attr('data-order-id');

            if (parent.next('.wbbm_report_detail').hasClass('show')) {
                parent.next('.wbbm_report_detail').removeClass('show');
                parent.next('.wbbm_report_detail').hide();
                return;
            }

            $('.wbbm-main-table tbody tr.wbbm_report_detail').each(function() {
                if ($(this).hasClass('show')) {
                    $(this).removeClass('show');
                    $(this).hide();
                }
            });

            $.ajax({
                url: ajaxurl,
                type: 'post',
                dataType: 'html',
                data: {
                    order_id: order_id,
                    action: 'wbbm_get_bus_details',
                    nonce: (typeof wbbmProAdmin !== 'undefined' ? wbbmProAdmin.nonce : ''),
                    from_date: $('#from_date').val() || '',
                    to_date: $('#to_date').val() || ''
                },
                beforeSend: function() {
                    $this.siblings('.wbbm_report_loading').show();
                },
                success: function(data) {
                    if (data) {
                        if (parent.next('.wbbm_report_detail').children().length == 0) {
                            $(data).insertAfter(parent);
                            parent.next('.wbbm_report_detail').slideDown(100);
                        }
                        if (parent.next('.wbbm_report_detail').hasClass('show')) {
                            parent.next('.wbbm_report_detail').hide();
                        } else {
                            parent.next('.wbbm_report_detail').slideDown(100);
                        }
                        parent.next('.wbbm_report_detail').toggleClass('show');

                        $this.siblings('.wbbm_report_loading').hide();
                        // $this.toggleClass('wbbm_report_detail_active');
                    }
                }
            });

        });

        // Export PDF
        $('#wbbm_export_pdf').click(function(e) {
            e.preventDefault();

            var pdf = new jsPDF('p', 'pt', 'A4');
            // source can be HTML-formatted string, or a reference
            // to an actual DOM element from which the text will be scraped.
            source = $('#wbbm_report_table_main')[0];

            // we support special element handlers. Register them with jQuery-style 
            // ID selector for either ID or node name. ("#iAmID", "div", "span" etc.)
            // There is no support for any other type of selectors 
            // (class, of compound) at this time.
            specialElementHandlers = {
                // element with id of "bypass" - jQuery style selector
                '.wbbm_bus_detail--report': function(element, renderer) {
                    // true = "handled elsewhere, bypass text extraction"
                    return true;
                }
            };
            margins = {
                top: 30,
                bottom: 60,
                left: 40,
                width: 800
            };
            // all coords and widths are in jsPDF instance's declared units
            // 'inches' in this case
            pdf.fromHTML(
                source, // HTML string or DOM elem ref.
                margins.left, // x coord
                margins.top, { // y coord
                    'width': margins.width, // max width of content on PDF
                    'elementHandlers': specialElementHandlers
                },

                function(dispose) {
                    // dispose: object with X, Y of the last line add to the PDF 
                    //          this allow the insertion of new lines after html
                    pdf.save('Test.pdf');
                }, margins
            );
        });

        const wbbm_queryString = window.location.search;
        const wbbm_urlParams = new URLSearchParams(wbbm_queryString);
        const wbbm_page = wbbm_urlParams.get('page');

        if (wbbm_page == 'wbbm-reports') {
            $('#bus_id').select2({
                width: 'resolve',
                theme: "classic"
            });
        }
        $('#boarding_point').select2({
            width: 'resolve',
            theme: "classic"
        });
        $('#dropping_point').select2({
            width: 'resolve',
            theme: "classic"
        });

        // Date fields are bound by initDatepickers() above.
        if (document.getElementById('wbbm_attendee_reg_form') != null) {
            jQuery("#wbbm_attendee_reg_form").sortable({
                handle: 'span.button.sort'
            });
        }

    });
})(jQuery);

/* =========================================================================
   Counter sale: one customer form per seat.

   The quantity stepper is client-side, so the passenger rows are built to
   match it here. Values already typed are preserved when the count changes,
   and each row is named as an array index so the handler can map a row to
   the seat at the same position.
   ========================================================================= */
(function ($) {
    'use strict';

    var FIELDS = ['wbbm_customer_name', 'wbbm_customer_email', 'wbbm_customer_phone'];

    function seatTotal(scope) {
        var total = 0;
        scope.find('.mage_form[data-price]').each(function () {
            var v = parseInt($(this).val(), 10);
            if (!isNaN(v) && v > 0) { total += v; }
        });
        return total;
    }

    function syncRows(scope) {
        var box = scope.find('[data-wbbm-customer]');
        if (!box.length) { return; }

        var rows = box.find('[data-wbbm-customer-rows]');
        var tpl = box.find('[data-wbbm-customer-template]').get(0);
        if (!rows.length || !tpl) { return; }

        var want = seatTotal(scope);
        var have = rows.children('.wbbm-admin-customer-row').length;

        // Nothing selected yet: keep the block out of the way entirely.
        if (want < 1) {
            box.attr('hidden', 'hidden');
            rows.empty();
            return;
        }
        box.removeAttr('hidden');

        for (var i = have; i < want; i++) {
            rows.append(document.importNode(tpl.content, true));
        }
        if (have > want) {
            rows.children('.wbbm-admin-customer-row').slice(want).remove();
        }

        // Re-index so the POST arrays line up with the seats.
        rows.children('.wbbm-admin-customer-row').each(function (index) {
            var row = $(this);
            row.find('[data-wbbm-customer-no]').text(
                (window.wbbmAdminSale && window.wbbmAdminSale.passenger ? window.wbbmAdminSale.passenger : 'Passenger') + ' ' + (index + 1)
            );
            FIELDS.forEach(function (field) {
                row.find('[data-wbbm-field="' + field + '"]').attr('name', field + '[' + index + ']');
            });
        });
    }

    function syncAll() {
        $('.mage_search_list').each(function () { syncRows($(this)); });
    }

    $(document).on('click', '.mage_qty_inc, .mage_qty_dec', function () {
        var scope = $(this).closest('.mage_search_list');
        // Let the quantity handler finish first.
        window.setTimeout(function () { syncRows(scope); }, 0);
    });

    $(document).on('input change', '.mage_form[data-price]', function () {
        syncRows($(this).closest('.mage_search_list'));
    });

    // Results are rendered in place, so rebuild after a panel swap too.
    $(document).on('wbbm-hub:tab-loaded', syncAll);
    $(document).on('click', '.mage-bus-detail-action', function () {
        var scope = $(this).closest('.mage_search_list');
        window.setTimeout(function () { syncRows(scope); }, 50);
    });

    $(syncAll);
})(jQuery);

/* =========================================================================
   Passenger list export dialog.

   Opens on the Export button, closes on the backdrop, Cancel or Escape, and
   restores focus to the trigger. The form posts natively so the browser
   handles the download; it is marked data-wbbm-native so the hub's AJAX form
   handler leaves it alone.
   ========================================================================= */
(function () {
    'use strict';

    function init() {
        var modal = document.querySelector('[data-wbbm-export-modal]');
        if (!modal || modal.getAttribute('data-wbbm-bound') === '1') { return; }
        modal.setAttribute('data-wbbm-bound', '1');

        var opener = null;

        function open(trigger) {
            opener = trigger || null;
            modal.hidden = false;
            document.body.classList.add('wbbm-export-open');
            var first = modal.querySelector('input[type="radio"]');
            if (first) { first.focus(); }
        }

        function close() {
            modal.hidden = true;
            document.body.classList.remove('wbbm-export-open');
            if (opener) { opener.focus(); opener = null; }
        }

        document.addEventListener('click', function (e) {
            var openBtn = e.target.closest('[data-wbbm-export-open]');
            if (openBtn) {
                e.preventDefault();
                open(openBtn);
                return;
            }
            if (e.target.closest('[data-wbbm-export-close]')) {
                e.preventDefault();
                close();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.hidden) { close(); }
        });

        // Custom from/to only matter for the Custom range option.
        var rangeSelect = modal.querySelector('[data-wbbm-export-range]');
        var customFields = modal.querySelectorAll('[data-wbbm-export-custom]');

        function syncRange() {
            var custom = rangeSelect && rangeSelect.value === 'custom';
            Array.prototype.forEach.call(customFields, function (field) {
                field.hidden = !custom;
                var input = field.querySelector('input');
                // Never post a stale date from a range the user moved away from.
                if (input && !custom) { input.value = ''; }
            });
        }

        if (rangeSelect) {
            rangeSelect.addEventListener('change', syncRange);
            syncRange();
        }

        // The download replaces nothing on screen, so close once it is away.
        var form = modal.querySelector('form');
        if (form) {
            form.addEventListener('submit', function () {
                window.setTimeout(close, 600);
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Panels are re-rendered in place inside the hub.
    document.addEventListener('wbbm-hub:tab-loaded', init);
})();
