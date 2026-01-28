jQuery(document).ready(function($) {
    
    // --- Route Management ---

    // Add New Route
    $(document).on('click', '.wbbm_add_route', function(e) {
        e.preventDefault();
        
        // Generate unique index for this route
        var routeIndex = new Date().getTime();
        
        // Get template and replace placeholders
        var template = $('#wbbm_route_template').html();
        template = template.replace(/{{route_index}}/g, routeIndex);
        
        // Append to container
        $('#wbbm_routes_container').append(template);
        
        // Initialize any plugins for the new route (e.g., sortable)
        initSortable();
    });

    // Remove Route
    $(document).on('click', '.wbbm_remove_route', function(e) {
        e.preventDefault();
        if(confirm('Are you sure you want to delete this route and all its stops?')) {
            $(this).closest('.wbbm_route_row').remove();
        }
    });

    // Add Stop to Route
    $(document).on('click', '.wbbm_add_route_stop', function(e) {
        e.preventDefault();
        
        var $routeRow = $(this).closest('.wbbm_route_row');
        var routeIndex = $routeRow.data('index');
        var stopIndex = new Date().getTime();
        
        // Get template
        var template = $('#wbbm_stop_template').html();
        template = template.replace(/{{route_index}}/g, routeIndex);
        template = template.replace(/{{stop_index}}/g, stopIndex);
        
        // Append to stops container
        $routeRow.find('.wbbm_route_stops_container').append(template);
        
        // Initialize Select2 for the new dropdown
        // $routeRow.find('.wbbm_route_stops_container .select2').last().select2();
    });

    // Remove Stop
    $(document).on('click', '.wbbm_remove_route_stop', function(e) {
        e.preventDefault();
        $(this).closest('.wbbm_route_stop_row').remove();
    });

    // Toggle Route Accordion
    $(document).on('click', '.wbbm_route_header', function(e) {
        // Don't toggle if clicking form elements or buttons
        if ($(e.target).closest('input, select, button, a').length) {
            return;
        }
        
        $(this).closest('.wbbm_route_row').toggleClass('active');
        $(this).siblings('.wbbm_route_body').slideToggle();
    });

    // Initialize Sortable for Stops
    function initSortable() {
        if($.fn.sortable) {
            $('.wbbm_route_stops_container').sortable({
                handle: '.wbbm_stop_drag_handle',
                placeholder: 'wbbm_stop_placeholder',
                forcePlaceholderSize: true
            });
        }
    }

    // Initial run
    initSortable();
    
    // Auto-update Route Title based on input
    $(document).on('keyup', '.wbbm_route_name_input', function() {
        var val = $(this).val();
        $(this).closest('.wbbm_route_row').find('.wbbm_route_title_text').text(val || 'New Route');
    });

    // --- Schedule Management ---

    // Add Schedule Time
    $(document).on('click', '.wbbm_add_schedule_time', function(e) {
        e.preventDefault();
        
        var $block = $(this).closest('.wbbm_route_schedule_block');
        var routeId = $block.data('route-id');
        var timeIndex = new Date().getTime();
        
        var template = $('#wbbm_schedule_row_template').html();
        template = template.replace(/{{route_id}}/g, routeId);
        template = template.replace(/{{time_index}}/g, timeIndex);
        
        $block.find('.wbbm_schedule_rows').append(template);
    });

    // Remove Schedule Time
    $(document).on('click', '.wbbm_remove_schedule_row', function(e) {
        e.preventDefault();
        $(this).closest('.wbbm_schedule_row').remove();
    });

});
