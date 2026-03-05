jQuery(document).ready(function ($) {
    'use strict';

    const form = $('#bus-edit-form');
    const savingStatus = $('.saving-status');
    let isSaving = false;

    // --- Required Field Validation ---
    function validateCurrentStep() {
        const activeStep = $('.bus-step-content.active');
        const requiredFields = activeStep.find('[required]');
        let isValid = true;

        // Clear previous errors
        activeStep.find('.field-error').removeClass('field-error');
        activeStep.find('.field-error-msg').remove();

        requiredFields.each(function () {
            const $field = $(this);
            const value = $field.val();

            if (!value || (typeof value === 'string' && value.trim() === '')) {
                isValid = false;
                $field.addClass('field-error');

                // Add error message below the field
                const $formGroup = $field.closest('.form-group');
                if ($formGroup.length && !$formGroup.find('.field-error-msg').length) {
                    const label = $formGroup.find('label').first().text().replace('*', '').trim();
                    $formGroup.append('<span class="field-error-msg">' + label + ' is required</span>');
                }
            }
        });

        if (!isValid) {
            // Show warning toast
            showValidationWarning('Please fill in all required fields before proceeding.');

            // Scroll to first error
            const firstError = activeStep.find('.field-error').first();
            if (firstError.length) {
                $('html, body').animate({
                    scrollTop: firstError.offset().top - 150
                }, 300);
                firstError.focus();
            }
        }

        return isValid;
    }

    function showValidationWarning(message) {
        // Remove existing warning
        $('.bus-validation-toast').remove();

        const toast = $('<div class="bus-validation-toast"><span class="dashicons dashicons-warning"></span> ' + message + '</div>');
        $('body').append(toast);

        setTimeout(function () { toast.addClass('show'); }, 10);
        setTimeout(function () {
            toast.removeClass('show');
            setTimeout(function () { toast.remove(); }, 300);
        }, 4000);
    }

    // Clear error on field input
    $(document).on('input change', '.field-error', function () {
        const $field = $(this);
        if ($field.val() && $field.val().trim() !== '') {
            $field.removeClass('field-error');
            $field.closest('.form-group').find('.field-error-msg').remove();
        }
    });

    // --- Step Transitions ---
    $('.next-step').on('click', function (e) {
        e.preventDefault();
        const nextStep = $(this).data('next');

        if (!validateCurrentStep()) return;

        // Save before moving to next step
        saveBusData(false, function () {
            goToStep(nextStep);
        });
    });

    $('.step-item').on('click', function () {
        const step = $(this).data('step');
        const currentActive = $('.step-item.active').data('step');

        if (step < currentActive || $(this).hasClass('completed')) {
            goToStep(step);
        } else if (step > currentActive) {
            if (!validateCurrentStep()) return;
            saveBusData(false, function () {
                goToStep(step);
            });
        }
    });

    function goToStep(step) {
        step = parseInt(step);
        $('.step-item').removeClass('active');
        $(`.step-item[data-step="${step}"]`).addClass('active');

        // Mark previous steps as completed
        $('.step-item').each(function () {
            const s = $(this).data('step');
            if (s < step) {
                $(this).addClass('completed');
            }
        });

        $('.bus-step-content').removeClass('active');
        $(`#step-${step}-content`).addClass('active');

        // Update Footer Buttons
        const footer = $('.bus-edit-footer');
        const nextBtn = footer.find('.next-step');
        const prevBtn = footer.find('.prev-step');
        const finalSaveBtn = footer.find('.final-save');

        if (step === 1) {
            prevBtn.hide();
        } else {
            prevBtn.show().data('prev', step - 1);
        }

        if (step === 6) {
            nextBtn.hide();
            finalSaveBtn.show();
        } else {
            nextBtn.show().data('next', step + 1);
            finalSaveBtn.hide();
        }

        // Update URL
        const url = new URL(window.location.href);
        url.searchParams.set('step', step);
        window.history.pushState({}, '', url);
    }

    // --- AJAX Save Function ---
    function saveBusData(isDraft = false, callback = null) {
        if (isSaving) return;

        if (isDraft) {
            $('#post_status').val('draft');
        }

        const formData = new FormData(form[0]);
        formData.append('action', 'wbbm_save_bus_ajax');

        // Add content from WP Editor if it exists
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('bus_content')) {
            formData.set('bus_content', tinyMCE.get('bus_content').getContent());
        }

        isSaving = true;
        savingStatus.html('<span class="spinner is-active" style="float:none; margin:0 5px 0 0;"></span> Saving...');

        $.ajax({
            url: wbbm_bus_edit.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                isSaving = false;
                if (response.success) {
                    savingStatus.html('<span style="color:green;">✓ Saved successfully</span>');

                    // Update post_id in hidden field if it was a new post
                    if (response.data.post_id) {
                        $('input[name="post_id"]').val(response.data.post_id);

                        // Update URL if post_id was 0
                        const url = new URL(window.location.href);
                        if (!url.searchParams.get('post_id')) {
                            url.searchParams.set('post_id', response.data.post_id);
                            window.history.pushState({}, '', url);
                        }
                    }

                    if (callback) callback();

                    setTimeout(() => {
                        savingStatus.empty();
                    }, 3000);
                } else {
                    savingStatus.html('<span style="color:red;">Error: ' + response.data + '</span>');
                }
            },
            error: function () {
                isSaving = false;
                savingStatus.html('<span style="color:red;">Server error occurred</span>');
            }
        });
    }

    // --- Step 2: Route & Price ---
    $(document).on('click', '.add-route-item', function () {
        const container = $('#route-items-container');
        const template = $('#route-item-template').html();
        const index = container.find('.route-item').length;

        const newItem = $(template.replace(/{{index}}/g, index));
        container.append(newItem);
        newItem.find('.route-item-body').slideDown();
        newItem.find('.toggle-route-item').addClass('active');

        initSortable();
    });

    $(document).on('click', '.remove-route-item', function () {
        if (confirm('Are you sure you want to remove this stop?')) {
            $(this).closest('.route-item').fadeOut(300, function () {
                $(this).remove();
                reloadPricingMatrix();
            });
        }
    });

    $(document).on('click', '.toggle-route-item, .route-item-header', function (e) {
        if ($(e.target).closest('.remove-route-item').length) return;

        const item = $(this).closest('.route-item');
        const body = item.find('.route-item-body');
        const icon = item.find('.toggle-route-item');

        body.slideToggle();
        icon.toggleClass('active');
    });

    $(document).on('change', '.route-place-select', function () {
        const name = $(this).find('option:selected').text();
        const header = $(this).closest('.route-item').find('.stop-name-display');
        header.text(name || 'New Stop');
        reloadPricingMatrix();
    });

    $(document).on('change', '.route-type-select', function () {
        const type = $(this).val();
        const nextDayWrap = $(this).closest('.route-item').find('.next-day-wrap');

        if (type === 'dp' || type === 'both') {
            nextDayWrap.slideDown();
        } else {
            nextDayWrap.slideUp();
        }
        reloadPricingMatrix();
    });

    function reloadPricingMatrix() {
        const places = [];
        const types = [];
        $('.route-place-select').each(function () {
            places.push($(this).val());
        });
        $('.route-type-select').each(function () {
            types.push($(this).val());
        });

        const matrixContainer = $('#pricing-matrix-container');
        matrixContainer.addClass('loading');

        $.ajax({
            url: wbbm_bus_edit.ajax_url,
            type: 'POST',
            data: {
                action: 'wbbm_reload_bus_pricing_ajax',
                nonce: wbbm_bus_edit.nonce,
                post_id: $('input[name="post_id"]').val(),
                places: places,
                types: types
            },
            success: function (response) {
                matrixContainer.removeClass('loading');
                if (response.success) {
                    matrixContainer.html(response.data);
                }
            }
        });
    }

    function initSortable() {
        if ($.fn.sortable) {
            $('.route-sortable').sortable({
                handle: '.drag-handle',
                update: function () {
                    reloadPricingMatrix();
                }
            });
        }
    }

    initSortable();

    // --- Pickup Points (Inline inside Route Items) ---
    $(document).on('click', '.add-inline-pickup-item', function () {
        const stopIndex = $(this).data('stop-index');
        const list = $(this).closest('.route-pickup-points-wrap').find('.pickup-points-list');
        const optionsData = $(this).closest('.bus-card').data('pickpoints-options') || [];

        let optionsHtml = '<option value="">Select Point</option>';
        optionsData.forEach(option => {
            optionsHtml += `<option value="${option.name}">${option.name}</option>`;
        });

        const html = `
            <div class="pickup-point-item" style="display: grid; grid-template-columns: 1fr 1fr 40px; gap: 10px; margin-bottom: 8px; align-items: center;">
                <select name="wbbm_inline_pickpoint_name[${stopIndex}][]" class="form-control sm">
                    ${optionsHtml}
                </select>
                <input type="time" name="wbbm_inline_pickpoint_time[${stopIndex}][]" class="form-control sm" value="">
                <button type="button" class="btn btn-secondary btn-sm remove-inline-pickup-item" style="color: #ef4444; justify-self: end;"><span class="dashicons dashicons-trash"></span></button>
            </div>
        `;
        list.append(html);
    });

    $(document).on('click', '.remove-inline-pickup-item', function () {
        $(this).closest('.pickup-point-item').remove();
    });

    // --- Step 3: Day Schedule ---
    $(document).on('click', '.add-offday-item', function () {
        const container = $('#offday-items-container');
        const template = $('#offday-item-template').html();
        container.append(template);
    });

    $(document).on('click', '.remove-offday-item', function () {
        const list = $('#offday-items-container');
        if (list.find('.offday-item').length > 1) {
            $(this).closest('.offday-item').remove();
        } else {
            $(this).closest('.offday-item').find('input').val('');
        }
    });

    // --- Transitions ---
    $('.prev-step').on('click', function (e) {
        e.preventDefault();
        const prevStep = $(this).data('prev');
        goToStep(prevStep);
    });

    // --- Step 4: Features ---
    $(document).on('click', '.feature-item', function () {
        const checkbox = $(this).find('input[type="checkbox"]');
        $(this).toggleClass('active', checkbox.prop('checked'));
    });

    $(document).on('click', '.add-extra-service', function () {
        const container = $('#extra-services-container');
        const template = $('#extra-service-template').html();
        container.append(template);
    });

    $(document).on('click', '.remove-extra-service', function () {
        const list = $('#extra-services-container');
        if (list.find('.extra-service-item').length > 1) {
            $(this).closest('.extra-service-item').remove();
        } else {
            $(this).closest('.extra-service-item').find('input').val('');
        }
    });

    // --- Save as Draft ---
    $('#save-bus-draft').on('click', function () {
        saveBusData(true);
    });

    // --- Top Save/Publish Button (save without step change) ---
    $('#save-bus-publish').on('click', function () {
        saveBusData(false);
    });

    // --- Media Uploader ---
    $('#set-bus-thumbnail').on('click', function (e) {
        e.preventDefault();

        const frame = wp.media({
            title: 'Select Bus Thumbnail',
            button: { text: 'Use this image' },
            multiple: false
        });

        frame.on('select', function () {
            const attachment = frame.state().get('selection').first().toJSON();
            $('#bus_thumbnail_id').val(attachment.id);

            $('#set-bus-thumbnail').html(`
                <div class="bus-thumbnail-preview">
                    <img src="${attachment.url}" alt="">
                    <p>Click to change image</p>
                </div>
            `);
        });

        frame.open();
    });

    // --- Step 1: Inline Stop Addition ---
    $(document).on('click', '#add-inline-stop-btn', function () {
        const nameInput = $('#new-stop-name');
        const name = nameInput.val().trim();
        const messageDiv = $('#inline-stop-message');
        const btn = $(this);

        if (!name) {
            messageDiv.html('<span style="color:red;">Please enter a stop name</span>');
            return;
        }

        btn.prop('disabled', true).html('<span class="spinner is-active" style="float:none; margin:0 5px 0 0;"></span> Adding...');
        messageDiv.empty();

        $.ajax({
            url: wbbm_bus_edit.ajax_url,
            type: 'POST',
            data: {
                action: 'wbbm_add_inline_stop',
                security: wbbm_bus_edit.nonce,
                term_name: name
            },
            success: function (response) {
                btn.prop('disabled', false).html('<span class="dashicons dashicons-plus"></span> Add New Stop');
                if (response.success) {
                    nameInput.val('');
                    const displayMsg = response.data.message || 'Stop added successfully!';
                    messageDiv.html('<span style="color:green;">✓ ' + displayMsg + '</span>');

                    // Update Step 1 sidebar list
                    const stopsList = $('#bus-stops-list');
                    stopsList.find('.no-stops').remove();
                    stopsList.append(`
                        <li data-id="${response.data.term_id}">
                            <span class="dashicons dashicons-location"></span>
                            <span class="stop-name">${response.data.name}</span>
                        </li>
                    `);

                    // Update Step 2 dropdowns
                    const newOption = new Option(response.data.name, response.data.name, false, false);
                    $('.route-place-select').each(function () {
                        $(this).append($(newOption).clone());
                    });

                    // Update Step 2 template
                    const template = $('#route-item-template');
                    if (template.length) {
                        const templateHtml = template.html();
                        const updatedTemplate = templateHtml.replace('</select>', `<option value="${response.data.name}">${response.data.name}</option></select>`);
                        template.html(updatedTemplate);
                    }

                    // Update Step 3 city selector
                    $('#pickup-city-selector').append(new Option(response.data.name, response.data.name, false, false));
                    if ($.fn.select2) {
                        $('#pickup-city-selector').trigger('change');
                    }

                    setTimeout(() => messageDiv.fadeOut(300, () => messageDiv.empty().show()), 5000);
                } else {
                    messageDiv.html('<span style="color:red;">Error: ' + (response.data || 'Unknown error') + '</span>');
                }
            },
            error: function () {
                btn.prop('disabled', false).html('<span class="dashicons dashicons-plus"></span> Add New Stop');
                messageDiv.html('<span style="color:red;">Server error occurred</span>');
            }
        });
    });
    // --- Step 2: Sidebar Pickup Point Addition ---
    $(document).on('click', '#add-inline-pickpoint-btn', function () {
        const nameInput = $('#new-pickpoint-name');
        const name = nameInput.val().trim();
        const btn = $(this);

        if (!name) {
            window.wbbm_show_toast('Please enter a pickup point name', 'delete');
            return;
        }

        btn.prop('disabled', true).html('<span class="spinner is-active" style="float:none; margin:0 5px 0 0;"></span> Adding...');

        $.ajax({
            url: wbbm_bus_edit.ajax_url,
            type: 'POST',
            data: {
                action: 'wbbm_add_inline_pickpoint',
                security: wbbm_bus_edit.nonce,
                term_name: name
            },
            success: function (response) {
                btn.prop('disabled', false).html('<span class="dashicons dashicons-plus"></span> Add Pickup Point');
                if (response.success) {
                    nameInput.val('');
                    window.wbbm_show_toast(response.data.message || 'Pickup point added successfully!');

                    // Update Sidebar list
                    const pointsList = $('#sidebar-pickpoints-list');
                    pointsList.find('.no-points').remove();
                    pointsList.append(`
                        <li data-id="${response.data.term_id}">
                            <span class="dashicons dashicons-location-alt"></span>
                            <span class="point-name">${response.data.name}</span>
                        </li>
                    `);

                    // Update Route Item dropdowns (across all steps if applicable)
                    const newOption = `<option value="${response.data.name}">${response.data.name}</option>`;
                    
                    // 1. Update all existing selects in route items
                    $('.route-pickup-points-wrap select').append(newOption);

                    // 2. Update the hidden options data in the .bus-card for future-added route items
                    const routeCard = $('.bus-card[data-pickpoints-options]');
                    if (routeCard.length) {
                        let optionsData = routeCard.data('pickpoints-options') || [];
                        optionsData.push({ term_id: response.data.term_id, name: response.data.name });
                        routeCard.data('pickpoints-options', optionsData);
                    }

                } else {
                    window.wbbm_show_toast('Error: ' + (response.data || 'Unknown error'), 'delete');
                }
            },
            error: function () {
                btn.prop('disabled', false).html('<span class="dashicons dashicons-plus"></span> Add Pickup Point');
                window.wbbm_show_toast('Server error occurred', 'delete');
            }
        });
    });

    // Initialize on load
    const initialStep = new URLSearchParams(window.location.search).get('step') || 1;
    goToStep(initialStep);
});
