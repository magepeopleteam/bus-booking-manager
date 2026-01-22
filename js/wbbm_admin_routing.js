// WBBM Admin Settings JavaScript
// Handles routing, pricing, and dynamic form interactions

function wbtm_load_sortable_datepicker(parent, item) {
    if (parent.find(".wbtm_item_insert_before").length > 0) {
        jQuery(item)
            .insertBefore(parent.find(".wbtm_item_insert_before").first())
            .promise()
            .done(function () {
                parent.find(".wbtm_sortable_area").sortable({
                    handle: jQuery(this).find(".wbtm_sortable_button"),
                });
            });
    } else {
        parent
            .find(".wbtm_item_insert")
            .first()
            .append(item)
            .promise()
            .done(function () {
                parent.find(".wbtm_sortable_area").sortable({
                    handle: jQuery(this).find(".wbtm_sortable_button"),
                });
            });
    }
    return true;
}

(function ($) {
    "use strict";
    
    $(document).ready(function () {
        // Initialize sortable areas
        $(".wbtm_sortable_area").sortable({
            handle: ".wbtm_sortable_button",
        });
        
        // Initialize collapse functionality
        addCollapseId();
        
        // Hide all contents on page load
        $(".wbtm_stop_item .wbtm_stop_item_content").hide();

        // Handle collapse toggle - fix for double firing
        $(document).off("click", "[data-collapse-target]").on("click", "[data-collapse-target]", function (e) {
            // Don't toggle if clicking on buttons or inputs inside the header, unless it's the edit button
            if ($(e.target).closest('.wbtm_edit_item_btn').length > 0) {
                // Allow toggle
            } else if ($(e.target).closest('button, input, select, .buttonGroup').length) {
                return;
            }
            
            e.preventDefault(); // Good practice
            e.stopPropagation(); // Stop bubbling
            
            let targetId = $(this).attr("data-collapse-target");
            let content = $('[data-collapse="' + targetId + '"]');
            
            // Toggle content visibility with stop() to clear animation queue
            content.stop(true, true).slideToggle(200);
            
            // Optional: Toggle active class for styling arrows etc.
            $(this).toggleClass("active");
        });
    });

    // Add unique collapse IDs to stop items
    function addCollapseId() {
        let collapseId = 0;
        $(".wbtm_stop_item").each(function (i) {
            $(this)
                .find(".wbtm_stop_item_header")
                .attr("data-collapse-target", "d" + i);
            $(this)
                .find(".wbtm_stop_item_content")
                .attr("data-collapse", "d" + i);
            collapseId = i++;
        });
        $(".wbtm_hidden_item .wbtm_stop_item")
            .find(".wbtm_stop_item_header")
            .attr("data-collapse-target", "d" + collapseId);
        $(".wbtm_hidden_item .wbtm_stop_item")
            .find(".wbtm_stop_item_content")
            .attr("data-collapse", "d" + collapseId);
            // Don't hide the template one, so it can be cloned easily
    }

    // Add new stop item
    $(document).on("click", ".wbtm_add_item", function () {
        addCollapseId();
        $(".wbtm_stop_item:last-child .wbtm_stop_item_content").css(
            "display",
            "block"
        );
        let parent = $(this).closest(".wbtm_settings_area");
        let item = $(this)
            .next($(".wbtm_hidden_content"))
            .find(" .wbtm_hidden_item")
            .html();
        if (!item || item === "undefined" || item === " ") {
            item = parent
                .find(".wbtm_hidden_content")
                .first()
                .find(".wbtm_hidden_item")
                .html();
        }
        wbtm_load_sortable_datepicker(parent, item);
        wbtm_reload_pricing($(".wbtm_settings_pricing_routing"));
        return true;
    });

    // Update header time when input changes
    $(document).on('change input', '[name="wbtm_route_time[]"]', function() {
        let val = $(this).val();
        if(!val) val = '--:-- --';
        $(this).closest('.wbtm_stop_item').find('.wbtm_stop_item_header ._zeroBorder_mp_zero').val(val);
    });

    // Update header place
    $(document).on('change', '[name="wbtm_route_place[]"]', function() {
        let text = $(this).find('option:selected').text();
        // Check if selected is disabled/placeholder
        if ($(this).find('option:selected').is(':disabled')) {
             text = "Add Stop";
        }
        $(this).closest('.wbtm_stop_item').find('.wbtm_header_place').text(text);
    });

    // Update header type
    $(document).on('change', '[name="wbtm_route_type[]"]', function() {
        let val = $(this).val();
        let text = "";
        if (val === 'bp') text = " (Boarding) ";
        else if (val === 'dp') text = " (Dropping) ";
        else if (val === 'both') text = " (Boarding & Dropping) ";
        
        $(this).closest('.wbtm_stop_item').find('.wbtm_header_type').text(text);
    });

    // Remove stop item
    $(document).on("click", ".wbtm_item_remove", function (e) {
        e.preventDefault();
        if (
            confirm(
                "Are You Sure, Remove this row? \n\n 1. Ok : To Remove. \n 2. Cancel : To Cancel."
            )
        ) {
            $(this).closest(".wbtm_remove_area").slideUp(250, function() {
                $(this).remove();
                // Trigger pricing reload after removal
                let parent = $(".wbtm_settings_pricing_routing");
                if (parent.length > 0) {
                    wbtm_reload_pricing(parent);
                }
            });
            return true;
        } else {
            return false;
        }
    });

    // Handle route type change - show/hide next day checkbox
    $(document).on('change', '.wbtm_route_type_select', function () {
        var type = $(this).val();
        var nextDayCheckbox = $(this).closest('.wbtm_stop_item').find('.next-day-dropping-checkbox');
        if (type == 'dp' || type == 'both') {
            nextDayCheckbox.show();
        } else {
            nextDayCheckbox.hide();
        }
        // Trigger pricing reload
        wbtm_reload_pricing($(".wbtm_settings_pricing_routing"));
    });

    // Handle route place/type change - reload pricing
    $(document).on(
        "change",
        '.wbtm_settings_pricing_routing [name="wbtm_route_place[]"], .wbtm_settings_pricing_routing [name="wbtm_route_time[]"]',
        function () {
            wbtm_reload_pricing($(".wbtm_settings_pricing_routing"));
        }
    );

    // Reload pricing table based on route configuration
    function wbtm_reload_pricing(parent) {
        let post_id = $('[name="wbtm_post_id"]').val();
        let target = parent.find(".wbtm_price_setting_area");
        let places = {};
        let types = {};
        let count = 0;
        
        parent.find(".wbtm_stop_item").each(function () {
            let place = $(this).find('[name="wbtm_route_place[]"]').val();
            let time = $(this).find('[name="wbtm_route_time[]"]').val();
            let type = $(this).find('[name="wbtm_route_type[]"]').val();
            if (place && time && type) {
                places[count] = place;
                types[count] = count < 1 ? "bp" : type;
                count++;
            }
        }).promise().done(function () {
            if (count > 1) {
                types[count - 1] = "dp";
                $.ajax({
                    type: "POST",
                    url: WbbmAjaxAdmin.url,
                    data: {
                        action: "wbtm_reload_pricing",
                        post_id: post_id,
                        places: places,
                        types: types,
                        nonce: WbbmAjaxAdmin.nonce
                    },
                    beforeSend: function () {
                        target.html('<div class="wbtm_loader"><span class="fas fa-spinner fa-pulse"></span></div>');
                    },
                    success: function (data) {
                        target.html(data);
                    },
                    error: function (response) {
                        console.log(response);
                    },
                });
            } else {
                target.html('<div class="_dLayout_bgWarning_mZero"><h3>Please add at least 2 stops (1 boarding + 1 dropping).</h3></div>');
            }
        });
    }

    // Expose function globally for external calls
    window.wbtm_reload_pricing = wbtm_reload_pricing;

})(jQuery);
