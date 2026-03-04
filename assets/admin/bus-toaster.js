jQuery(document).ready(function ($) {
    'use strict';

    /**
     * Display a toaster notification
     * @param {string} message - The text to display
     * @param {string} type - 'success' (default) or 'delete'
     */
    window.wbbm_show_toast = function (message, type) {
        if (!type) type = 'success';
        
        var icon = type === 'delete' ? 'trash' : 'yes';
        var toastClass = type === 'delete' ? 'bus-toast-delete' : 'bus-toast-success';
        
        var toast = $(
            '<div class="bus-toast ' + toastClass + '">' +
                '<span class="dashicons dashicons-' + icon + '"></span>' +
                '<span class="toast-message">' + message + '</span>' +
            '</div>'
        );

        $('body').append(toast);

        // Animate in
        setTimeout(function() {
            toast.addClass('show');
        }, 10);

        // Remove after 3 seconds
        setTimeout(function() {
            toast.removeClass('show');
            setTimeout(function() {
                toast.remove();
            }, 400);
        }, 3000);
    };

    // --- Global Event Listeners for Generic Triggers ---

    /**
     * Persistent Toast: Set flag before page reload/redirect
     * Usage: add data-wbbm-persist-toast="Message" and optionally data-wbbm-persist-type="delete"
     */
    $(document).on('click', '[data-wbbm-persist-toast]', function() {
        var msg = $(this).attr('data-wbbm-persist-toast');
        var type = $(this).attr('data-wbbm-persist-type') || 'success';
        sessionStorage.setItem('wbbm_pending_toast_msg', msg);
        sessionStorage.setItem('wbbm_pending_toast_type', type);
    });

    /**
     * Instant Toast: Show immediately on click (e.g. for AJAX actions)
     * Usage: add data-wbbm-instant-toast="Message" and optionally data-wbbm-instant-type="success"
     */
    $(document).on('click', '[data-wbbm-instant-toast]', function(e) {
        var msg = $(this).attr('data-wbbm-instant-toast');
        var type = $(this).attr('data-wbbm-instant-type') || 'success';
        window.wbbm_show_toast(msg, type);
    });

    // --- Legacy/Module-Specific Listeners (keeping for backward compatibility) ---
    $(document).on('click', '#bus-type-submit', function() {
        var isEdit = $(this).text().indexOf('Update') !== -1;
        sessionStorage.setItem('wbbm_pending_toast_msg', isEdit ? 'Bus type updated successfully!' : 'Bus type created successfully!');
        sessionStorage.setItem('wbbm_pending_toast_type', 'success');
    });

    $(document).on('click', '.wbbm-delete-bus-type', function() {
        sessionStorage.setItem('wbbm_pending_toast_msg', 'Item deleted successfully!');
        sessionStorage.setItem('wbbm_pending_toast_type', 'success');
    });

    // --- Check for persistent flags on load ---
    var savedMsg = sessionStorage.getItem('wbbm_pending_toast_msg');
    var savedType = sessionStorage.getItem('wbbm_pending_toast_type');

    if (savedMsg) {
        window.wbbm_show_toast(savedMsg, savedType || 'success');
        sessionStorage.removeItem('wbbm_pending_toast_msg');
        sessionStorage.removeItem('wbbm_pending_toast_type');
    }

    // --- Fallback: Check for legacy URL parameters ---
    var urlParams = new URLSearchParams(window.location.search);
    var messageParam = urlParams.get('message');
    var deletedParam = urlParams.get('deleted');

    if (!savedMsg) {
        if (messageParam === 'updated') {
            window.wbbm_show_toast('Bus type updated successfully!');
        } else if (messageParam === 'created') {
            window.wbbm_show_toast('Bus type created successfully!');
        } else if (deletedParam === '1') {
            window.wbbm_show_toast('Item deleted successfully!', 'success');
        }
    }
});
