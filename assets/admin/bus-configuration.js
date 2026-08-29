(function () {
    'use strict';

    var modal = document.getElementById('wbbm-config-modal');
    if (!modal) {
        return;
    }

    var dialog = modal.querySelector('[role="dialog"]');
    var form = modal.querySelector('form');
    var title = document.getElementById('wbbm-config-modal-title');
    var itemId = document.getElementById('wbbm-config-item-id');
    var itemToken = document.getElementById('wbbm-config-item-token');
    var nameField = document.getElementById('wbbm-config-name');
    var slugField = document.getElementById('wbbm-config-slug');
    var descriptionField = document.getElementById('wbbm-config-description');
    var iconField = document.getElementById('wbbm-config-feature-icon');
    var statusField = document.getElementById('wbbm-config-status');
    var metaRows = document.getElementById('wbbm-config-meta-rows');
    var metaTemplate = document.getElementById('wbbm-config-meta-template');
    var addField = document.getElementById('wbbm-config-add-field');
    var initialFocus = document.activeElement;
    var baseTitle = title ? title.textContent.replace(/^Edit /, '').replace(/^Add /, '') : '';

    function focusable() {
        return Array.prototype.slice.call(dialog.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])')).filter(function (element) {
            return element.offsetWidth > 0 || element.offsetHeight > 0;
        });
    }

    function setIcon(icon) {
        if (!iconField) {
            return;
        }
        iconField.value = icon || '';
        var preview = iconField.parentNode.querySelector('[data-empty-text]');
        if (preview) {
            preview.className = icon || 'fas fa-forward';
        }
    }

    function updateEmptyFieldsMessage() {
        var empty = form.querySelector('.wbbm-config-no-fields');
        if (empty && metaRows) {
            empty.hidden = metaRows.children.length > 0;
        }
    }

    function addMetaRow(field) {
        if (!metaRows || !metaTemplate) {
            return;
        }
        var fragment = metaTemplate.content.cloneNode(true);
        var row = fragment.querySelector('.wbbm-config-meta-row');
        row.querySelector('[name="meta_id[]"]').value = field && field.meta_id ? field.meta_id : 0;
        row.querySelector('[name="meta_key[]"]').value = field && field.key ? field.key : '';
        row.querySelector('[name="meta_value[]"]').value = field && field.value ? field.value : '';
        metaRows.appendChild(fragment);
        updateEmptyFieldsMessage();
        return row;
    }

    function fillMetaRows(fields) {
        if (!metaRows) {
            return;
        }
        metaRows.innerHTML = '';
        Array.prototype.slice.call(form.querySelectorAll('.js-wbbm-deleted-meta')).forEach(function (field) {
            field.remove();
        });
        (fields || []).forEach(function (field) {
            addMetaRow(field);
        });
        updateEmptyFieldsMessage();
    }

    function fill(data, mode) {
        data = data || {};
        itemId.value = mode === 'edit' ? (data.id || '') : '';
        itemToken.value = mode === 'edit' ? (data.token || '') : '';
        nameField.value = mode === 'edit' ? (data.name || '') : '';
        if (slugField) {
            slugField.value = mode === 'edit' ? (data.slug || '') : '';
        }
        if (descriptionField) {
            descriptionField.value = mode === 'edit' ? (data.description || '') : '';
        }
        if (iconField) {
            setIcon(mode === 'edit' ? (data.icon || '') : 'fas fa-forward');
        }
        if (statusField) {
            statusField.value = mode === 'edit' && data.status ? data.status : 'draft';
        }
        fillMetaRows(mode === 'edit' ? data.custom_fields : []);
        if (title) {
            title.textContent = (mode === 'edit' ? 'Edit ' : 'Add ') + baseTitle;
        }
    }

    function openModal(trigger) {
        var data = {};
        if (trigger.dataset.item) {
            try {
                data = JSON.parse(trigger.dataset.item);
            } catch (error) {
                window.location.href = trigger.href;
                return;
            }
        }
        initialFocus = trigger;
        fill(data, trigger.dataset.mode || 'add');
        modal.hidden = false;
        modal.classList.add('is-open');
        document.body.classList.add('wbbm-config-modal-open');
        window.setTimeout(function () {
            nameField.focus();
        }, 30);
    }

    function closeModal(event) {
        if (event) {
            event.preventDefault();
        }
        modal.classList.remove('is-open');
        modal.hidden = true;
        document.body.classList.remove('wbbm-config-modal-open');
        if (initialFocus && typeof initialFocus.focus === 'function') {
            initialFocus.focus();
        }
    }

    document.addEventListener('click', function (event) {
        var openLink = event.target.closest('.js-wbbm-modal-link');
        if (openLink) {
            event.preventDefault();
            openModal(openLink);
            return;
        }
        var closeLink = event.target.closest('.js-wbbm-modal-close');
        if (closeLink && modal.classList.contains('is-open')) {
            closeModal(event);
            return;
        }
        var deleteLink = event.target.closest('.wbbm-config-delete');
        if (deleteLink && !window.confirm(deleteLink.dataset.confirm || 'Delete this item?')) {
            event.preventDefault();
            return;
        }
        var removeField = event.target.closest('.js-wbbm-remove-new-field');
        if (removeField) {
            event.preventDefault();
            var row = removeField.closest('.wbbm-config-meta-row');
            var metaId = row.querySelector('[name="meta_id[]"]').value;
            if (metaId && metaId !== '0') {
                var deleted = document.createElement('input');
                deleted.type = 'hidden';
                deleted.name = 'deleted_meta_ids[]';
                deleted.value = metaId;
                deleted.className = 'js-wbbm-deleted-meta';
                form.appendChild(deleted);
            }
            row.remove();
            updateEmptyFieldsMessage();
        }
    });

    if (addField) {
        addField.addEventListener('click', function () {
            var row = addMetaRow({});
            if (row) {
                row.querySelector('[name="meta_key[]"]').focus();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (!modal.classList.contains('is-open')) {
            return;
        }
        if (event.key === 'Escape') {
            closeModal(event);
            return;
        }
        if (event.key !== 'Tab') {
            return;
        }
        var controls = focusable();
        if (!controls.length) {
            event.preventDefault();
            return;
        }
        var first = controls[0];
        var last = controls[controls.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    if (modal.classList.contains('is-open')) {
        document.body.classList.add('wbbm-config-modal-open');
        var error = modal.querySelector('[role="alert"]');
        window.setTimeout(function () {
            (error || nameField).focus();
        }, 30);
    }

    form.addEventListener('submit', function (event) {
        if (!nameField.value.trim()) {
            event.preventDefault();
            nameField.setAttribute('aria-invalid', 'true');
            nameField.focus();
        }
    });
}());
