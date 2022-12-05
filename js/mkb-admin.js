(function($) {
    "use strict";

    // Extra service
    $('#add-row').on('click', function(event) {
        event.preventDefault();
        var row = $('.empty-row.screen-reader-text').clone(true);
        row.removeClass('empty-row screen-reader-text');
        row.insertBefore('#repeatable-fieldset-one tbody>tr:last');
        return false;
    });

    $('.remove-row').on('click', function() {
        if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
            $(this).parents('tr').remove();
        } else {
            return false;
        }
    });

    // Extra service END

}(jQuery));