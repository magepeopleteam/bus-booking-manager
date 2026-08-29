/**
 * Progressive enhancement for hub screens.
 *
 * Derives the mobile `data-label` values used by the responsive card layout
 * from each table's own headers. Presentation only: every form and action on
 * a hub page works with this script absent.
 */
(function () {
    'use strict';

    function labelTable(table) {
        if (table.getAttribute('data-wbbm-labelled') === '1') {
            return;
        }

        var headRow = table.tHead && table.tHead.rows.length ? table.tHead.rows[0] : null;
        if (!headRow) {
            return;
        }

        var headings = [];
        Array.prototype.forEach.call(headRow.cells, function (cell) {
            headings.push((cell.textContent || '').replace(/\s+/g, ' ').trim());
        });

        Array.prototype.forEach.call(table.tBodies, function (body) {
            Array.prototype.forEach.call(body.rows, function (row) {
                // Detail/expander rows span the grid and carry no per-column meaning.
                if (row.cells.length !== headings.length) {
                    return;
                }
                Array.prototype.forEach.call(row.cells, function (cell, i) {
                    if (!cell.hasAttribute('data-label') && headings[i]) {
                        cell.setAttribute('data-label', headings[i]);
                    }
                });
            });
        });

        table.setAttribute('data-wbbm-labelled', '1');
    }

    function init() {
        var panels = document.querySelectorAll('.wbbm-hub-panel');
        Array.prototype.forEach.call(panels, function (panel) {
            var tables = panel.querySelectorAll('table');
            Array.prototype.forEach.call(tables, function (table) {
                // Opt a table out with class="wbbm-hub-table-plain".
                if (table.classList.contains('wbbm-hub-table-plain')) {
                    return;
                }
                table.classList.add('wbbm-hub-table');
                labelTable(table);

                // Keep wide tables scrolling inside their own container.
                var parent = table.parentNode;
                if (parent && !parent.classList.contains('wbbm-hub-tablewrap')) {
                    var wrap = document.createElement('div');
                    wrap.className = 'wbbm-hub-tablewrap';
                    parent.insertBefore(wrap, table);
                    wrap.appendChild(table);
                }
            });
        });
    }

    /**
     * Instant client-side filtering for a hub table.
     *
     * Attaches to any [data-wbbm-filter] input, whose value names the table to
     * filter (or the nearest one). Rows are matched on their full text, so it
     * works for every module without per-module wiring. Filtering is purely a
     * view concern: nothing is removed from the DOM or the server.
     */
    function initFilters() {
        var inputs = document.querySelectorAll('[data-wbbm-filter]');

        Array.prototype.forEach.call(inputs, function (input) {
            var selector = input.getAttribute('data-wbbm-filter');
            var scope = input.closest('.wbbm-hub-panel') || document;
            var table = selector ? scope.querySelector(selector) : null;

            if (!table) {
                var wrap = input.closest('.wbbm-hub-panel');
                table = wrap ? wrap.querySelector('table') : null;
            }
            if (!table || !table.tBodies.length) {
                return;
            }

            var counter = scope.querySelector('[data-wbbm-filter-count]');
            // Placeholder rows are not data and must not be counted or filtered.
            var rows = Array.prototype.slice.call(table.tBodies[0].rows)
                .filter(function (row) { return !row.classList.contains('wbbm-hub-empty-row'); });
            var emptyRow = table.tBodies[0].querySelector('tr.wbbm-hub-empty-row');

            function apply() {
                var q = input.value.trim().toLowerCase();
                var shown = 0;

                rows.forEach(function (row) {
                    var hit = !q || (row.textContent || '').toLowerCase().indexOf(q) !== -1;
                    row.setAttribute('data-wbbm-filtered', hit ? '0' : '1');
                    if (hit) { shown++; }
                });

                if (counter) {
                    counter.textContent = counter.getAttribute('data-wbbm-filter-count')
                        .replace('%1$s', String(shown))
                        .replace('%2$s', String(rows.length));
                }

                // Show the placeholder only when a filter hid everything.
                if (emptyRow && rows.length) {
                    emptyRow.setAttribute('data-wbbm-filtered', shown === 0 ? '0' : '1');
                }
            }

            input.addEventListener('input', apply);
            input.addEventListener('search', apply);
            apply();
        });
    }

    /* =====================================================================
       Instant tab switching.

       Tabs swap the panel in place over AJAX instead of reloading the whole
       admin page. Results are cached per tab+query for the life of the page
       and prefetched on hover, so a revisit is immediate.

       This is progressive enhancement: every tab link keeps its real href,
       so without JS (or if a request fails) navigation still works.
       ===================================================================== */
    var cfg = window.wbbmHub || null;
    var cache = Object.create(null);   // tab+query -> rendered panel
    var inflight = Object.create(null);
    var seq = 0;

    function panelEl() { return document.querySelector('[data-wbbm-panel]'); }

    function keyFor(tab, query) { return tab + '|' + (query || ''); }

    function fetchTab(tab, query) {
        var key = keyFor(tab, query);
        if (cache[key]) {
            return Promise.resolve(cache[key]);
        }
        if (inflight[key]) {
            return inflight[key];
        }

        var body = new URLSearchParams();
        body.set('action', cfg.action);
        body.set('nonce', cfg.nonce);
        body.set('tab', tab);
        body.set('query', query || '');

        var p = fetch(cfg.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        })
            .then(function (r) { return r.json(); })
            .then(function (json) {
                if (!json || !json.success) {
                    throw new Error('failed');
                }
                cache[key] = json.data;
                delete inflight[key];
                return json.data;
            })
            .catch(function (e) {
                delete inflight[key];
                throw e;
            });

        inflight[key] = p;
        return p;
    }

    function setActive(tab) {
        var links = document.querySelectorAll('[data-wbbm-tab]');
        Array.prototype.forEach.call(links, function (a) {
            if (a.getAttribute('data-wbbm-tab') === tab) {
                a.setAttribute('aria-current', 'page');
            } else {
                a.removeAttribute('aria-current');
            }
        });
    }

    function paint(data) {
        var panel = panelEl();
        if (!panel) { return; }

        panel.innerHTML = data.html;
        panel.className = 'wbbm-hub-panel wbbm-hub-panel--' + data.tab;

        var sub = document.querySelector('[data-wbbm-subtitle]');
        if (sub) {
            sub.textContent = data.description || '';
            if (data.description) {
                sub.removeAttribute('hidden');
            } else {
                sub.setAttribute('hidden', '');
            }
        }

        setActive(data.tab);

        // Re-run the enhancements that ran on first paint.
        init();
        initFilters();

        // Let module scripts re-bind to freshly injected markup.
        document.dispatchEvent(new CustomEvent('wbbm-hub:tab-loaded', {
            detail: { tab: data.tab, panel: panel }
        }));

        panel.setAttribute('aria-busy', 'false');
    }

    function showError() {
        var panel = panelEl();
        if (!panel) { return; }
        panel.setAttribute('aria-busy', 'false');
        panel.innerHTML = '';
        var box = document.createElement('div');
        box.className = 'wbbm-hub-notice is-error';
        box.textContent = (cfg.i18n && cfg.i18n.failed) || 'Could not load this section.';
        panel.appendChild(box);
    }

    function go(tab, query, push) {
        var panel = panelEl();
        if (!panel || !cfg) { return; }

        var token = ++seq;
        var key = keyFor(tab, query);

        // A cached tab paints synchronously; no spinner flash.
        if (cache[key]) {
            paint(cache[key]);
        } else {
            panel.setAttribute('aria-busy', 'true');
            panel.classList.add('is-loading');
        }

        fetchTab(tab, query)
            .then(function (data) {
                if (token !== seq) { return; }   // superseded by a later click
                panel.classList.remove('is-loading');
                paint(data);
            })
            .catch(function () {
                if (token !== seq) { return; }
                panel.classList.remove('is-loading');
                showError();
            });

        if (push) {
            var url = new URL(window.location.href);
            url.searchParams.set('page', cfg.page);
            url.searchParams.set('tab', tab);
            window.history.pushState({ wbbmTab: tab, wbbmQuery: query || '' }, '', url.toString());
        }
    }

    function initTabs() {
        if (!cfg || !cfg.action || !panelEl()) {
            return;
        }

        var nav = document.querySelector('[data-wbbm-tabs]');
        if (!nav) { return; }

        nav.addEventListener('click', function (e) {
            var a = e.target.closest('[data-wbbm-tab]');
            if (!a || !nav.contains(a)) { return; }

            // Let the browser handle modified clicks (new tab, download, etc).
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) { return; }

            e.preventDefault();
            go(a.getAttribute('data-wbbm-tab'), '', true);
        });

        // Warm the cache on intent, so the click itself is instant.
        nav.addEventListener('mouseover', function (e) {
            var a = e.target.closest('[data-wbbm-tab]');
            if (!a) { return; }
            var tab = a.getAttribute('data-wbbm-tab');
            if (!cache[keyFor(tab, '')]) {
                fetchTab(tab, '').catch(function () {});
            }
        });

        window.addEventListener('popstate', function (e) {
            var st = e.state;
            if (st && st.wbbmTab) {
                go(st.wbbmTab, st.wbbmQuery || '', false);
            }
        });

        // Seed history so Back from the first swap returns here.
        var current = nav.querySelector('[aria-current="page"]');
        if (current) {
            window.history.replaceState(
                { wbbmTab: current.getAttribute('data-wbbm-tab'), wbbmQuery: '' },
                '',
                window.location.href
            );
        }
    }

    /* =====================================================================
       Fast saves.

       Forms inside a hub panel post over fetch and repaint the panel in
       place, so saving does not cost a full admin page load. The server-side
       handlers, nonces and redirects are untouched — the redirect target is
       simply read back and used to refresh the panel.

       Excluded, and left to submit natively:
         - anything marked data-wbbm-native (file downloads)
         - GET forms that only change filters (the tab reload covers those)
       ===================================================================== */
    function panelQuery() {
        var u = new URL(window.location.href);
        u.searchParams.delete('page');
        u.searchParams.delete('post_type');
        u.searchParams.delete('tab');
        return u.searchParams.toString();
    }

    function currentTab() {
        var a = document.querySelector('[data-wbbm-tab][aria-current]');
        return a ? a.getAttribute('data-wbbm-tab') : null;
    }

    function flash(kind, text) {
        var panel = panelEl();
        if (!panel) { return; }
        var box = document.createElement('div');
        box.className = 'wbbm-hub-notice is-' + kind;
        box.setAttribute('role', 'status');
        box.textContent = text;
        panel.insertBefore(box, panel.firstChild);
        window.setTimeout(function () {
            box.style.transition = 'opacity .3s';
            box.style.opacity = '0';
            window.setTimeout(function () { box.remove(); }, 320);
        }, 4000);
    }

    function initForms() {
        if (!cfg || !cfg.action) { return; }
        var panel = panelEl();
        if (!panel) { return; }

        panel.addEventListener('submit', function (e) {
            var form = e.target;
            if (!(form instanceof HTMLFormElement)) { return; }
            if (form.hasAttribute('data-wbbm-native')) { return; }

            var method = (form.getAttribute('method') || 'get').toLowerCase();
            var action = form.getAttribute('action') || '';

            // Only take over POSTs to admin-post.php; everything else keeps
            // its normal behaviour so nothing subtle breaks.
            if (method !== 'post' || action.indexOf('admin-post.php') === -1) { return; }

            e.preventDefault();

            var tab = currentTab();
            var submitters = form.querySelectorAll('button[type="submit"], input[type="submit"]');
            Array.prototype.forEach.call(submitters, function (b) { b.disabled = true; });
            panel.classList.add('is-loading');

            var data = new FormData(form);
            // FormData omits the button that was clicked; add it back.
            if (e.submitter && e.submitter.name) {
                data.append(e.submitter.name, e.submitter.value || '');
            }

            fetch(action, {
                method: 'POST',
                credentials: 'same-origin',
                body: data,
                redirect: 'follow'
            })
                .then(function (r) {
                    // Handlers redirect back to the hub; carry any flags the
                    // redirect added (wbbm_saved, wbbm_test, …) into the reload.
                    var q = '';
                    try {
                        var u = new URL(r.url);
                        u.searchParams.delete('page');
                        u.searchParams.delete('post_type');
                        u.searchParams.delete('tab');
                        q = u.searchParams.toString();
                    } catch (err) { q = panelQuery(); }

                    // The saved state invalidates every cached panel.
                    cache = Object.create(null);
                    return fetchTab(tab, q).then(function (d) {
                        panel.classList.remove('is-loading');
                        paint(d);
                        var url = new URL(window.location.href);
                        url.search = '';
                        url.searchParams.set('post_type', new URLSearchParams(window.location.search).get('post_type') || '');
                        url.searchParams.set('page', cfg.page);
                        url.searchParams.set('tab', tab);
                        new URLSearchParams(q).forEach(function (v, k) { url.searchParams.set(k, v); });
                        window.history.replaceState({ wbbmTab: tab, wbbmQuery: q }, '', url.toString());
                    });
                })
                .catch(function () {
                    panel.classList.remove('is-loading');
                    Array.prototype.forEach.call(submitters, function (b) { b.disabled = false; });
                    flash('error', (cfg.i18n && cfg.i18n.failed) || 'Could not save.');
                });
        });
    }

    function boot() {
        init();
        initFilters();
        initTabs();
        initForms();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
