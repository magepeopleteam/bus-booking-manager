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

        // Update pricing matrix
        updatePricingMatrix();
    });

    // Remove Route
    $(document).on('click', '.wbbm_remove_route', function(e) {
        e.preventDefault();
        if(confirm('Are you sure you want to delete this route and all its stops?')) {
            $(this).closest('.wbbm_route_row').remove();
            updatePricingMatrix();
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
        updatePricingMatrix();
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
                forcePlaceholderSize: true,
                update: function() {
                    updatePricingMatrix();
                }
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
        var direction = $(this).data('direction') || 'forward';
        
        var template = $('#wbbm_schedule_row_template').html();
        template = template.replace(/{{route_id}}/g, routeId);
        template = template.replace(/{{time_index}}/g, timeIndex);
        template = template.replace(/{{direction}}/g, direction);
        
        // Append to sibling container (robust for both structures)
        $(this).siblings('.wbbm_schedule_rows').append(template);
    });

    // Remove Schedule Time
    $(document).on('click', '.wbbm_remove_schedule_row', function(e) {
        e.preventDefault();
        $(this).closest('.wbbm_schedule_row').remove();
    });

    // --- Tabs ---
    $('.mp_tab_menu li').on('click', function() {
        var target = $(this).data('target-tabs');
        
        // Toggle Menu
        $(this).addClass('active').siblings().removeClass('active');
        
        // Toggle Content
        $('.mp_tab_item').removeClass('active');
        $(target).addClass('active');
    });

    // --- Reverse Route (Clone) ---
    $(document).on('click', '.wbbm_reverse_route', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $originalRow = $(this).closest('.wbbm_route_row');
        var originalName = $originalRow.find('.wbbm_route_name_input').val();
        
        // 1. Create New Route
        var routeIndex = new Date().getTime();
        var template = $('#wbbm_route_template').html();
        template = template.replace(/{{route_index}}/g, routeIndex);
        $('#wbbm_routes_container').append(template);
        
        var $newRow = $('#wbbm_routes_container').find('.wbbm_route_row').last();
        
        // 2. Set Name
        var newName = originalName ? "Return: " + originalName : "New Return Route";
        $newRow.find('.wbbm_route_name_input').val(newName);
        $newRow.find('.wbbm_route_title_text').text(newName);
        $newRow.addClass('active');
        $newRow.find('.wbbm_route_body').show(); 
        
        // 3. Process Stops
        var stops = [];
        $originalRow.find('.wbbm_route_stop_row').each(function() {
            var loc = $(this).find('select').val();
            var time = parseFloat($(this).find('input[placeholder="Min"]').val()) || 0;
            var dist = parseFloat($(this).find('input[placeholder="Km"]').val()) || parseFloat($(this).find('input[type="number"]').eq(1).val()) || 0; // Fallback selector
            stops.push({location: loc, time: time, dist: dist});
        });
        
        // If stops found, reverse them
        if (stops.length > 0) {
            // Assuming cumulative stats
            var totalTime = stops[stops.length - 1].time;
            var totalDist = stops[stops.length - 1].dist;
            
            // Iterate backwards
            for (var i = stops.length - 1; i >= 0; i--) {
                var s = stops[i];
                var newTime = totalTime - s.time;
                var newDist = totalDist - s.dist;
                
                // Add Stop
                var stopIndex = new Date().getTime() + (stops.length - i);
                var stopTemplate = $('#wbbm_stop_template').html();
                stopTemplate = stopTemplate.replace(/{{route_index}}/g, routeIndex);
                stopTemplate = stopTemplate.replace(/{{stop_index}}/g, stopIndex);
                
                $newRow.find('.wbbm_route_stops_container').append(stopTemplate);
                var $stopRow = $newRow.find('.wbbm_route_stops_container .wbbm_route_stop_row').last();
                
                $stopRow.find('select').val(s.location);
                $stopRow.find('input[placeholder="Min"]').val(newTime >= 0 ? newTime : 0);
                // Try to find distance input robustly
                $stopRow.find('input').last().prev().val(newDist >= 0 ? Math.round(newDist * 100) / 100 : 0); // Logic relies on DOM order, safer to find by attribute if possible, but placeholder="Km" is safe
                $stopRow.find('input[placeholder="Km"]').val(newDist >= 0 ? Math.round(newDist * 100) / 100 : 0);
            }
        }
        
        initSortable(); // Re-init
        
        $('html, body').animate({
            scrollTop: $newRow.offset().top - 100
        }, 500);

        updatePricingMatrix();
    });

    // --- Real-time Pricing Matrix Update ---

    var updatePricingTimeout;
    function updatePricingMatrix() {
        clearTimeout(updatePricingTimeout);
        updatePricingTimeout = setTimeout(function() {
            var routes = [];
            $('.wbbm_route_row').each(function() {
                var $routeRow = $(this);
                if ($routeRow.data('index') === '{{route_index}}') return;

                var route = {
                    id: $routeRow.find('input[name*="[id]"]').val(),
                    name: $routeRow.find('.wbbm_route_name_input').val(),
                    type: $routeRow.find('select[name*="[type]"]').val(),
                    stops: []
                };

                $routeRow.find('.wbbm_route_stop_row').each(function() {
                    var $stopRow = $(this);
                    var loc = $stopRow.find('select[name*="[location]"]').val();
                    if (loc) {
                        route.stops.push({
                            location: loc
                        });
                    }
                });

                if (route.name) {
                    routes.push(route);
                }
            });

            var $container = $('#wbbm_pricing_matrix_container');
            $container.css('opacity', '0.5');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wbbm_update_pricing_matrix',
                    post_id: $('input[name="wbbm_shuttle_post_id"]').val(),
                    routes: routes,
                    security: $('#wbbm_shuttle_settings_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        $container.html(response.data.html);
                    }
                    $container.css('opacity', '1');
                },
                error: function() {
                    $container.css('opacity', '1');
                }
            });
        }, 500); // Debounce
    }

    // Trigger update on stop location change
    $(document).on('change', '.wbbm_route_stop_row select[name*="[location]"]', function() {
        updatePricingMatrix();
    });

    // Trigger update on route name/type change
    $(document).on('keyup change', '.wbbm_route_name_input, .wbbm_route_row select[name*="[type]"]', function() {
        updatePricingMatrix();
    });
});
