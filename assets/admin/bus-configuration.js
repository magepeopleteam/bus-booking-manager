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

/* =========================================================================
   Instant tab switching for the Bus Configuration screen.

   Swaps the toolbar + table in place instead of reloading the admin page.
   Progressive enhancement: every tab link keeps its real href, so without
   JS — or if a request fails — navigation still works as before.
   ========================================================================= */
(function () {
    'use strict';

    var cfg = window.wbbmConfig || null;
    if (!cfg || !cfg.ajaxUrl) { return; }

    var panel = document.querySelector('[data-wbbm-cfg-panel]');
    var nav = document.querySelector('.wbbm-config-tabs');
    if (!panel || !nav) { return; }

    var cache = Object.create(null);
    var seq = 0;

    function fetchTab(tab) {
        if (cache[tab]) { return Promise.resolve(cache[tab]); }

        var body = new URLSearchParams();
        body.set('action', 'wbbm_bus_config_tab');
        body.set('nonce', cfg.nonce);
        body.set('tab', tab);

        return fetch(cfg.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        })
            .then(function (r) { return r.json(); })
            .then(function (json) {
                if (!json || !json.success) { throw new Error('failed'); }
                cache[tab] = json.data;
                return json.data;
            });
    }

    /**
     * Run <script> tags that arrived through innerHTML — assigning innerHTML
     * parses them but never executes them, so inline initialisers (the icon
     * picker, date fields) would otherwise stay dead after a swap.
     */
    function runScripts(container) {
        Array.prototype.forEach.call(container.querySelectorAll('script'), function (old) {
            var type = (old.getAttribute('type') || '').toLowerCase();
            if (type && type !== 'text/javascript' && type !== 'application/javascript' && type !== 'module') {
                return;
            }
            var fresh = document.createElement('script');
            if (old.src) {
                if (document.querySelector('script[src="' + old.src + '"]:not([data-wbbm-reinjected])')) {
                    old.parentNode.removeChild(old);
                    return;
                }
                fresh.src = old.src;
                fresh.async = false;
                fresh.setAttribute('data-wbbm-reinjected', '1');
            } else {
                fresh.textContent = old.textContent;
            }
            if (type) { fresh.type = old.type; }
            old.parentNode.replaceChild(fresh, old);
        });
    }

    function paint(data) {
        panel.innerHTML = data.html;
        runScripts(panel);

        Array.prototype.forEach.call(nav.querySelectorAll('[data-wbbm-cfg-tab]'), function (a) {
            if (a.getAttribute('data-wbbm-cfg-tab') === data.tab) {
                a.setAttribute('aria-current', 'page');
            } else {
                a.removeAttribute('aria-current');
            }
        });

        // The header's Add button is per-resource.
        var add = document.querySelector('[data-wbbm-cfg-add]');
        if (add) {
            if (data.canCreate) {
                add.hidden = false;
                add.href = data.addUrl;
                var icon = add.querySelector('.dashicons');
                add.textContent = data.addLabel;
                if (icon) { add.insertBefore(icon, add.firstChild); }
            } else {
                add.hidden = true;
            }
        }

        panel.classList.remove('is-loading');

        // Let module scripts re-bind to the freshly injected markup.
        document.dispatchEvent(new CustomEvent('wbbm-hub:tab-loaded', {
            detail: { tab: data.tab, panel: panel }
        }));
    }

    nav.addEventListener('click', function (e) {
        var a = e.target.closest('[data-wbbm-cfg-tab]');
        if (!a || !nav.contains(a)) { return; }
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) { return; }

        e.preventDefault();
        var tab = a.getAttribute('data-wbbm-cfg-tab');
        var token = ++seq;

        if (cache[tab]) {
            paint(cache[tab]);
        } else {
            panel.classList.add('is-loading');
        }

        fetchTab(tab)
            .then(function (d) { if (token === seq) { paint(d); } })
            .catch(function () { if (token === seq) { window.location.href = a.href; } });

        var url = new URL(window.location.href);
        url.searchParams.set('page', cfg.page);
        url.searchParams.set('tab', tab);
        url.searchParams.delete('s');
        url.searchParams.delete('paged');
        window.history.pushState({ wbbmCfgTab: tab }, '', url.toString());
    });

    // Warm the cache on intent.
    nav.addEventListener('mouseover', function (e) {
        var a = e.target.closest('[data-wbbm-cfg-tab]');
        if (!a) { return; }
        var tab = a.getAttribute('data-wbbm-cfg-tab');
        if (!cache[tab]) { fetchTab(tab).catch(function () {}); }
    });

    window.addEventListener('popstate', function (e) {
        if (e.state && e.state.wbbmCfgTab) {
            fetchTab(e.state.wbbmCfgTab).then(paint).catch(function () { window.location.reload(); });
        }
    });

    var current = nav.querySelector('[aria-current="page"]');
    if (current) {
        window.history.replaceState(
            { wbbmCfgTab: current.getAttribute('data-wbbm-cfg-tab') },
            '',
            window.location.href
        );
    }

    /* ---------------------------------------------------------------------
       Modal save without a reload.

       The form is posted to the very same URL it would post to normally, so
       the server does its usual nonce check, capability check, validation
       and save. Nothing is duplicated here:

         - success  -> the handler redirects to ?notice=created|updated, so a
                       redirected response URL is the success signal. The
                       modal closes and the table is refreshed in place.
         - invalid  -> the handler re-renders the page with $this->errors, so
                       the error list is lifted out of that HTML and shown in
                       the still-open modal.
       ------------------------------------------------------------------- */
    var modalEl = document.getElementById('wbbm-config-modal');

    function activeTab() {
        var a = nav.querySelector('[data-wbbm-cfg-tab][aria-current]');
        return a ? a.getAttribute('data-wbbm-cfg-tab') : null;
    }

    function showErrors(form, html) {
        var slot = form.querySelector('[data-wbbm-cfg-errors]');
        if (!slot) { return; }

        var list = slot.querySelector('ul');
        if (list) { list.innerHTML = ''; }

        var parsed = new DOMParser().parseFromString(html, 'text/html');
        var incoming = parsed.querySelector('[data-wbbm-cfg-errors] ul');

        if (incoming && list) {
            list.innerHTML = incoming.innerHTML;
        } else if (list) {
            var li = document.createElement('li');
            li.textContent = 'Could not save. Please check the form and try again.';
            list.appendChild(li);
        }

        slot.removeAttribute('hidden');
        slot.focus();
    }

    function flashNotice(text) {
        var shell = document.querySelector('.wbbm-config-shell');
        if (!shell || !panel) { return; }
        var box = document.createElement('div');
        box.className = 'wbbm-config-notice';
        box.setAttribute('role', 'status');
        box.textContent = text;
        panel.insertBefore(box, panel.firstChild);
        window.setTimeout(function () {
            box.style.transition = 'opacity .3s';
            box.style.opacity = '0';
            window.setTimeout(function () { box.remove(); }, 320);
        }, 4000);
    }

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement)) { return; }
        if (!form.hasAttribute('data-wbbm-cfg-form')) { return; }

        // Let the browser surface its own validation first.
        if (typeof form.checkValidity === 'function' && !form.checkValidity()) { return; }

        e.preventDefault();

        var tab = activeTab();
        var buttons = form.querySelectorAll('button[type="submit"]');
        Array.prototype.forEach.call(buttons, function (b) { b.disabled = true; });

        var data = new FormData(form);
        if (e.submitter && e.submitter.name) {
            data.append(e.submitter.name, e.submitter.value || '');
        }

        fetch(form.action, {
            method: 'POST',
            credentials: 'same-origin',
            body: data,
            redirect: 'follow'
        })
            .then(function (r) {
                return r.text().then(function (body) {
                    return { url: r.url, body: body, ok: r.ok };
                });
            })
            .then(function (res) {
                Array.prototype.forEach.call(buttons, function (b) { b.disabled = false; });

                var saved = /[?&]notice=(created|updated)/.test(res.url);
                if (!saved) {
                    showErrors(form, res.body);
                    return;
                }

                // Close the modal, then repaint the table from the server.
                if (modalEl) {
                    modalEl.classList.remove('is-open');
                    modalEl.hidden = true;
                    document.body.classList.remove('wbbm-config-modal-open');
                }

                delete cache[tab];
                panel.classList.add('is-loading');

                return fetchTab(tab).then(function (d) {
                    paint(d);
                    flashNotice(/notice=created/.test(res.url)
                        ? 'Item created.'
                        : 'Item updated.');
                });
            })
            .catch(function () {
                Array.prototype.forEach.call(buttons, function (b) { b.disabled = false; });
                panel.classList.remove('is-loading');
                form.submit();   // Fall back to a normal post.
            });
    });
})();
