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
    $('.mage_form_group input.mage_form').on('keyup keydown', function (e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            return false;
        }
        let target = $(this);
        let value = parseInt(target.val());
        mageTicketQty(target, value);
    });
    $('.mage_qty_dec').on('click', function () {
        let target = $(this).siblings('input');
        let value = (parseInt(target.val()) - 1) > 0 ? (parseInt(target.val()) - 1) : 0;
        mageTicketQty(target, value);
    });
    $('.mage_qty_inc').on('click', function () {
        let target = $(this).siblings('input');
        let value = parseInt(target.val()) + 1;
        mageTicketQty(target, value);
    });
    $('button.mage_book_now').on('click', function () {
        let ticket = 0;
        let currentTarget = $(this).parents('.mage_search_list ').find('.mage_form[data-price]')
        currentTarget.each(function (index) {
            ticket += parseInt($(this).val());
        });

        if (ticket > 0) {
            mageSubTotal(currentTarget);
            $(this).siblings('button.single_add_to_cart_button').trigger('click');
        } else {
            currentTarget.addClass('mage_error').trigger('focus');
        }
    });

    function mageTicketQty(target, value) {
        let minSeat = parseInt(target.attr('min'));
        let maxSeat = parseInt(target.attr('max'));
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
        mageError(value,target);
        mageSubTotal(target);
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
            let unitPrice = parseInt($(this).attr('data-price'));
            let ticket = parseInt($(this).val());
            subTotal = subTotal + (unitPrice * ticket > 0 ? unitPrice * ticket : 0);
        });
        currentTarget.find('.mage_sub_total span').html(subTotal);
        mageCustomerInfoForm(currentTarget);
    }

    function mageCustomerInfoForm(target) {
        if(target.children().hasClass('mage_hidden_customer_info_form')) {
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