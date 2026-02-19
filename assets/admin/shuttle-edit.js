jQuery(document).ready(function ($) {
    var isSaving = false;
    var initialFormData = '';

    // Initialize initialFormData
    setTimeout(function () {
        initialFormData = getCleanSerializedData();
    }, 1500); // Wait for potential dynamic loads or skeleton removal

    // Remove skeleton loaders after a "loading" period
    setTimeout(function () {
        $('.skeleton').removeClass('skeleton skeleton-text skeleton-btn');
    }, 1200);

    // Thumbnail selection
    $('#set-post-thumbnail').on('click', function (e) {
        e.preventDefault();

        var frame = wp.media({
            title: 'Select or Upload Thumbnail',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#shuttle_thumbnail_id').val(attachment.id);

            var html = '<div class="shuttle-thumbnail-preview">' +
                '<img src="' + attachment.url + '" alt="">' +
                '<p style="margin-top: 10px; color: #7c3aed;">Click to change image</p>' +
                '</div>';

            $('#set-post-thumbnail').html(html);
        });

        frame.open();
    });

    /**
     * Tag Selector Logic
     */
    var $search = $('#shuttle_stop_search');
    var $dropdown = $('#shuttle_stop_dropdown');
    var $results = $('#shuttle_stop_results');
    var $selectedContainer = $('#shuttle_selected_stops_tags');
    var $hiddenInputs = $('#shuttle_stops_hidden_inputs');

    // Show dropdown on focus
    $search.on('focus click', function () {
        $dropdown.fadeIn(200);
        filterStops($(this).val());
    });

    // Filter stops
    $search.on('input', function () {
        filterStops($(this).val());
    });

    function filterStops(query) {
        query = query.toLowerCase().trim();
        var hasVisible = false;

        $results.find('.shuttle-dropdown-item').each(function () {
            var name = $(this).data('name').toLowerCase();
            var id = $(this).data('id');

            // Don't show already selected stops
            var isSelected = $hiddenInputs.find('input[value="' + id + '"]').length > 0;

            if (name.indexOf(query) > -1 && !isSelected) {
                $(this).show();
                hasVisible = true;
            } else {
                $(this).hide();
            }
        });

        if (hasVisible) {
            $('.shuttle-no-data').hide();
        } else {
            $('.shuttle-no-data').show();
        }
    }

    // Hide dropdown on click outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.shuttle-tag-input-wrapper').length) {
            $dropdown.fadeOut(200);
        }
    });

    // Select Stop from dropdown
    $(document).on('click', '.shuttle-dropdown-item', function () {
        var id = $(this).data('id');
        var name = $(this).data('name');

        addStopTag(id, name);
        $search.val('').focus();
        filterStops('');
    });

    function addStopTag(id, name) {
        if ($hiddenInputs.find('input[value="' + id + '"]').length) return;

        // Add Tag Pill
        var tagHtml = '<span class="shuttle-tag" data-id="' + id + '">' +
            name + ' <i class="dashicons dashicons-dismiss remove-tag"></i></span>';
        $selectedContainer.append(tagHtml);

        // Add Hidden Input
        $hiddenInputs.append('<input type="hidden" name="shuttle_stops[]" value="' + id + '" data-name="' + name + '">');

        // Sync Step 2 Options
        updateRouteStopOptions();
    }

    // Remove Tag
    $(document).on('click', '.shuttle-tag .remove-tag', function () {
        var $tag = $(this).closest('.shuttle-tag');
        var id = $tag.data('id');

        $tag.remove();
        $hiddenInputs.find('input[value="' + id + '"]').remove();

        // Sync Step 2 Options
        updateRouteStopOptions();

        // Trigger filter to show it back in dropdown if it was hidden
        filterStops($search.val());
    });

    /**
     * Inline Add Stop Logic
     */
    $('#trigger_add_stop').on('click', function () {
        $dropdown.hide();
        $('#inline_add_stop_form').slideDown();
        $('#new_stop_name_input').focus();
    });

    $('#cancel_add_stop').on('click', function () {
        $('#inline_add_stop_form').slideUp();
        $('#new_stop_name_input').val('');
    });

    $('#confirm_add_stop').on('click', function () {
        var stopName = $('#new_stop_name_input').val().trim();
        if (!stopName) {
            alert('Please enter a stop name.');
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true).text('...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wbbm_add_shuttle_stop_ajax',
                stop_name: stopName,
                nonce: $('#wbbm_shuttle_nonce').val()
            },
            success: function (response) {
                btn.prop('disabled', false).text('Create Stop');
                if (response.success) {
                    $('#inline_add_stop_form').slideUp();
                    $('#new_stop_name_input').val('');

                    // Add to dropdown (so it's available later)
                    var itemHtml = '<div class="shuttle-dropdown-item" data-id="' + response.data.term_id + '" data-name="' + response.data.name + '">' +
                        response.data.name + '</div>';
                    $results.append(itemHtml);

                    // Select it immediately
                    addStopTag(response.data.term_id, response.data.name);

                    // Trigger a background save
                    saveShuttleAjax();
                } else {
                    alert(response.data);
                }
            },
            error: function () {
                btn.prop('disabled', false).text('Create Stop');
                alert('An error occurred.');
            }
        });
    });

    /**
     * Step Navigation (Smooth switching)
     */
    function switchStep(step) {
        if (step == 2) {
            updateRouteStopOptions();
        }

        // Update Nav
        $('.step-item').removeClass('active');
        $('.step-item[data-step="' + step + '"]').addClass('active');

        $('.step-item').each(function () {
            var s = $(this).data('step');
            if (s < step) {
                $(this).addClass('completed').find('.step-number').text('✓');
            } else {
                $(this).removeClass('completed').find('.step-number').text(s);
            }
        });

        // Update Content
        $('.shuttle-step-content').hide();
        $('#step-' + step + '-content').fadeIn(300).addClass('active');

        // Update hidden field
        $('input[name="current_step"]').val(step);

        if (step == 3) {
            refreshPricingMatrix();
        }
        if (step == 4) {
            refreshSchedule();
        }
    }

    /**
     * Refresh Pricing Matrix via AJAX
     */
    function refreshPricingMatrix() {
        var $container = $('.shuttle-pricing-container');

        // Ensure loader exists
        if ($container.find('.shuttle-pricing-loader').length === 0) {
            $container.prepend('<div class="shuttle-pricing-loader"><span class="dashicons dashicons-update spin" style="font-size: 32px; width: 32px; height: 32px;"></span><p>Refreshing pricing matrix...</p></div>');
        }

        $container.addClass('loading');

        var form = $('#shuttle-edit-form');
        var formData = form.serialize();
        formData += '&action=wbbm_get_shuttle_pricing_matrix_ajax&nonce=' + $('#wbbm_shuttle_nonce').val();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function (response) {
                $container.removeClass('loading');
                if (response.success) {
                    // Temporarily remove loader to update content, then put it back
                    var $loader = $container.find('.shuttle-pricing-loader').detach();
                    $container.html(response.data);
                    $container.prepend($loader);

                    // Update initial state baseline now that new fields are loaded
                    setTimeout(function () {
                        initialFormData = getCleanSerializedData();
                    }, 500);
                }
            },
            error: function () {
                $container.removeClass('loading');
            }
        });
    }

    /**
     * Refresh Schedule via AJAX
     */
    function refreshSchedule() {
        var $container = $('.shuttle-schedule-container');

        $container.addClass('loading');

        var form = $('#shuttle-edit-form');
        var formData = form.serialize();
        formData += '&action=wbbm_get_shuttle_schedule_ajax&nonce=' + $('#wbbm_shuttle_nonce').val();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function (response) {
                $container.removeClass('loading');
                if (response.success) {
                    $container.html(response.data);

                    // Update initial state baseline now that new fields are loaded
                    setTimeout(function () {
                        initialFormData = getCleanSerializedData();
                    }, 500);
                }
            },
            error: function () {
                $container.removeClass('loading');
            }
        });
    }

    /**
     * Validate current step fields
     */
    function validateStep(step) {
        var isValid = true;
        var $content = $('#step-' + step + '-content');

        // Clear previous errors
        $content.find('.form-group').removeClass('has-error');
        $content.find('.form-control').removeClass('invalid');
        $content.find('.error-message').remove();

        // Check required fields
        $content.find('[required]').each(function () {
            var $field = $(this);
            var value = $field.val();

            if (!value || (typeof value === 'string' && value.trim() === '')) {
                isValid = false;
                $field.addClass('invalid');
                $field.closest('.form-group').addClass('has-error');

                if ($field.closest('.form-group').find('.error-message').length === 0) {
                    $field.after('<span class="error-message">This field is required.</span>');
                }
            }
        });

        if (!isValid) {
            // Scroll to first error
            $('html, body').animate({
                scrollTop: $content.find('.invalid:first').offset().top - 150
            }, 200);
        }

        return isValid;
    }

    // Dynamic Filter for Route Stops
    function updateRouteStopOptions() {
        var selectedStops = [];
        $hiddenInputs.find('input').each(function () {
            selectedStops.push({
                id: $(this).val(),
                name: $(this).data('name') || $(this).attr('data-name') || $(this).parent().find('.shuttle-tag[data-id="' + $(this).val() + '"]').text().trim()
            });
        });

        // Fallback to getting name from tag if data-name is missing (should not be)
        if (selectedStops.length > 0 && !selectedStops[0].name) {
            selectedStops = [];
            $('.shuttle-tag').each(function () {
                selectedStops.push({
                    id: $(this).data('id'),
                    name: $(this).text().trim()
                });
            });
        }

        // Update existing selects in Step 2
        $('#step-2-content select[name*="[location]"]').each(function () {
            var currentVal = $(this).val();
            var optionsHtml = '<option value="">Select Stop</option>';

            selectedStops.forEach(function (stop) {
                var selected = (currentVal == stop.name) ? 'selected' : '';
                optionsHtml += '<option value="' + stop.name + '" ' + selected + '>' + stop.name + '</option>';
            });

            $(this).html(optionsHtml);
        });

        // Update Template Select
        var templateHtml = $('#wbbm_stop_template').html();
        var $temp = $('<div>').html(templateHtml);
        var $select = $temp.find('select[name*="[location]"]');

        var optionsHtml = '<option value="">Select Stop</option>';
        selectedStops.forEach(function (stop) {
            optionsHtml += '<option value="' + stop.name + '">' + stop.name + '</option>';
        });

        $select.html(optionsHtml);
        $('#wbbm_stop_template').html($temp.html());
    }

    // AJAX Save Function
    function saveShuttleAjax(callback) {
        if (isSaving) return;
        isSaving = true;

        // Sync WP Editor
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('shuttle_content')) {
            tinyMCE.get('shuttle_content').save();
        }

        var form = $('#shuttle-edit-form');
        var formData = form.serialize();
        formData += '&action=wbbm_save_shuttle_ajax';

        // Update initial data after successful save
        var currentFormData = $('#shuttle-edit-form').serialize();

        // Disable buttons
        $('.btn-primary, .next-step, .step-item').css('pointer-events', 'none').css('opacity', '0.7');

        var $headerH2 = $('.shuttle-edit-header h2');
        if ($headerH2.find('.saving-text').length === 0) {
            $headerH2.append('<span class="saving-text" style="font-size: 12px; margin-left: 10px; color: #64748b;">(Saving...)</span>');
        }

        var $footerProgress = $('.shuttle-saving-progress');
        $footerProgress.html('<span class="dashicons dashicons-update spin"></span> Saving...').addClass('active');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function (response) {
                isSaving = false;
                $('.btn-primary, .next-step, .step-item').css('pointer-events', 'auto').css('opacity', '1');

                if (response.success) {
                    $('.saving-text').text('(Saved)');
                    $footerProgress.html('<span class="dashicons dashicons-yes"></span> Saved');

                    // Update initial form data to current state after success (using clean data)
                    initialFormData = getCleanSerializedData();

                    if (response.data && response.data.post_id) {
                        $('input[name="post_id"]').val(response.data.post_id);
                    }

                    setTimeout(function () {
                        $('.saving-text').fadeOut(function () { $(this).remove(); });
                        $footerProgress.removeClass('active');
                    }, 2000);

                    if (callback) callback();
                } else {
                    $('.saving-text').text('(Save failed)');
                    $footerProgress.html('<span class="dashicons dashicons-warning"></span> Error');
                    $('.btn-primary, .next-step, .step-item').css('pointer-events', 'auto').css('opacity', '1');
                }
            },
            error: function () {
                isSaving = false;
                $('.saving-text').text('(Connection error)');
                $footerProgress.html('<span class="dashicons dashicons-warning"></span> Error');
                $('.btn-primary, .next-step, .step-item').css('pointer-events', 'auto').css('opacity', '1');
                setTimeout(function () { $('.saving-text').remove(); }, 2000);
            }
        });
    }

    // Save as Draft Logic
    $('#save-shuttle-draft').on('click', function (e) {
        e.preventDefault();
        $('#post_status').val('draft');
        saveShuttleAjax(function () {
            // Update the status badge dynamically
            $('.shuttle-status-badge')
                .text('Draft')
                .removeClass('status-publish status-new')
                .addClass('status-draft');
        });
    });

    /**
     * Get serialized form data without navigation fields
     */
    function getCleanSerializedData() {
        var params = $('#shuttle-edit-form').serializeArray();
        // Filter out fields that don't represent persistent data
        var cleanParams = params.filter(function (item) {
            return item.name !== 'current_step' && item.name !== 'next_step_val' && item.name !== 'wbbm_shuttle_nonce';
        });
        return $.param(cleanParams);
    }

    /**
     * Check if form data has changed
     */
    function hasDataChanged() {
        // Sync WP Editor
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('shuttle_content')) {
            tinyMCE.get('shuttle_content').save();
        }

        var currentData = getCleanSerializedData();
        return currentData !== initialFormData;
    }

    $('.next-step').on('click', function () {
        var currentStep = $('input[name="current_step"]').val();
        if (!validateStep(currentStep)) return;

        var nextStep = $(this).data('next');

        // Ensure status stays publish if we're moving forward in steps (or keep current if it was draft)
        var currentStatus = $('#post_status').val();

        // Save only if changed
        if (hasDataChanged()) {
            saveShuttleAjax(function () {
                switchStep(nextStep);
            });
        } else {
            switchStep(nextStep);
        }
    });

    $('.prev-step').on('click', function () {
        var prevStep = $(this).data('prev');

        // Save only if changed (User requested for both ways)
        if (hasDataChanged()) {
            saveShuttleAjax(function () {
                switchStep(prevStep);
            });
        } else {
            switchStep(prevStep);
        }
    });

    $('.step-item').on('click', function () {
        var step = $(this).data('step');
        var currentStep = $('input[name="current_step"]').val();
        if (step == currentStep) return;

        // Only validate if moving forward
        if (step > currentStep) {
            if (!validateStep(currentStep)) return;
        }

        // Save only if changed
        if (hasDataChanged()) {
            saveShuttleAjax(function () {
                switchStep(step);
            });
        } else {
            switchStep(step);
        }
    });

    // Route Stop Management
    $('.wbbm_add_route_stop').on('click', function () {
        var container = $('#wbbm_route_stops_container');
        var template = $('#wbbm_stop_template').html();
        var index = container.find('.wbbm_route_stop_row').length;

        var html = template.replace(/{{stop_index}}/g, index);
        container.append(html);

        // Ensure new select has correct options
        updateRouteStopOptions();
    });

    $(document).on('click', '.wbbm_remove_route_stop', function () {
        if (confirm('Are you sure you want to remove this stop?')) {
            $(this).closest('.wbbm_route_stop_row').remove();
            reindexStops();
        }
    });

    $(document).on('click', '.wbbm_stop_points_toggle', function () {
        $(this).next('.wbbm_stop_points_wrapper').slideToggle();
    });

    function reindexStops() {
        $('#wbbm_route_stops_container .wbbm_route_stop_row').each(function (idx) {
            $(this).attr('data-index', idx);
            $(this).find('select, input, textarea').each(function () {
                var name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/\[stops\]\[\d+\]/, '[stops][' + idx + ']'));
                }
            });
        });
    }

    // Make stops sortable if jQuery UI is available
    if ($.fn.sortable) {
        $('#wbbm_route_stops_container').sortable({
            handle: '.wbbm_stop_drag_handle',
            update: function () {
                reindexStops();
            }
        });
    }

    // Initial call to sync if redirected back
    if ($('input[name="current_step"]').val() == '2') {
        updateRouteStopOptions();
    }

    // Form submit validation
    $('#shuttle-edit-form').on('submit', function (e) {
        var currentStep = $('input[name="current_step"]').val();
        if (!validateStep(currentStep)) {
            e.preventDefault();
            return false;
        }
        // When submitting the form via the main Publish/Save button, ensure status is 'publish'
        $('#post_status').val('publish');
    });

});
