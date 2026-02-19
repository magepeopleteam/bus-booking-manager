jQuery(document).ready(function ($) {
    // Select All Checkboxes
    $('#select-all').on('change', function () {
        $('.shuttle-modern-table tbody input[type="checkbox"]').prop('checked', $(this).prop('checked'));
    });

    // Handle more button click (placeholder for dropdown)
    $('.more-btn').on('click', function (e) {
        e.stopPropagation();
        alert('Action menu coming soon');
    });

    // Auto-submit status filter on change
    $('#status-filter').on('change', function () {
        $('#shuttle-list-filter-form').submit();
    });
});
