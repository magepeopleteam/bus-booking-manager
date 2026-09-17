/**
 * Settings screen enhancements.
 *
 * The settings framework renders one flat form-table per section. This layer
 * turns that into the designed screen without changing how anything is
 * registered or saved:
 *
 *   1. Rows are redistributed into titled cards (GROUPS below). Anything a
 *      map does not mention lands in a trailing card, so a new option added
 *      in PHP still appears rather than vanishing.
 *   2. On/Off <select>s become switches. The select stays in the form and
 *      stays the value that submits.
 *   3. Checkbox lists become pills.
 *   4. Long sections get a search box; Translations also gets a
 *      "changed only" filter and a marker on strings that differ from the
 *      English source.
 *   5. The save bar reports saved / unsaved and offers Discard.
 *
 * Runs on the settings hub only. Each section is its own <form>.
 */
(function () {
    'use strict';

    var SEARCH_THRESHOLD = 20;
    var ON = 'on';

    /*
     * Rows are matched on the English label in the <th>, which is fixed in
     * PHP -- the editable value lives in the field, not the label.
     */
    var GROUPS = {
        wbbm_general_setting_sec: {
            cards: [
                {
                    title: 'Booking behaviour',
                    desc: 'How seats are held, priced and released.',
                    match: ['Buffer time', 'Entire Bus Booking', 'Discount Price', 'Seat booked on status']
                },
                {
                    title: 'Search results list',
                    desc: 'Which columns the minimal results theme shows.',
                    match: ['Type Column', 'Seat Column']
                },
                {
                    title: 'Post type',
                    desc: 'How buses are stored and addressed.',
                    match: ['Gutenburg', 'CPT Name', 'Slug']
                },
                {
                    title: 'Pages',
                    desc: 'Where the search form sends the customer.',
                    match: ['Search Result Page']
                }
            ]
        },
        wbbm_global_offday_sec: {
            cards: [
                {
                    title: 'Days the fleet does not run',
                    desc: 'Applied to every bus unless a bus overrides it.',
                    match: ['Global Off-Dates', 'Global Off-Days']
                }
            ]
        },
        wbbm_label_setting_sec: {
            collapsible: true,
            changedFilter: true,
            cards: [
                {
                    title: 'Search form',
                    desc: 'Shown on the booking widget.',
                    match: ['Buy Ticket', 'From', 'To', 'Date of Journey', 'Return Date', 'One Way', 'Return', 'Search Buses', 'Search', 'Select Journey Date']
                },
                {
                    title: 'Results list',
                    desc: 'Columns and row labels.',
                    match: ['Route', 'Date', 'Bus Name', 'Departing', 'Coach No', 'Starting Time', 'End Time', 'Fare', 'Type', 'Arrival', 'Seats Available', 'View', 'View Seats', 'Schedule', 'Bus Image', 'Start & Arrival Time', 'Bus No', 'Total Seat']
                },
                {
                    title: 'Booking and fares',
                    desc: 'Seat picker, quantities and totals.',
                    match: ['Book Now', 'Total', 'Sub Total', 'Seat No', 'Remove', 'Adult', 'Child', 'Infant', 'Student', 'Seat List', 'Total Passenger', 'Ticket', 'Seat', 'No Seat Available', 'Entire Bus', 'Extra Bag', 'Extra Services', 'Item added to the cart']
                },
                {
                    title: 'Stops and pickup',
                    desc: 'Boarding, dropping and pickup points.',
                    match: ['Boarding Points', 'Dropping Points', 'Boarding', 'Dropping', 'Pickup Point', 'Select Pickup Area', 'Start From', 'End To']
                },
                {
                    title: 'Ticket',
                    desc: 'Printed and emailed ticket.',
                    match: ['QR Code', 'PIN Number', 'Journey Information', 'Issue Date', 'Journey Date', 'Coach Name', 'Ticket Printing', 'Check In', 'Passenger Checked In', 'login your account', 'Time']
                },
                {
                    title: 'Passenger details',
                    desc: 'Fields collected from the traveller.',
                    match: ['Passenger Information', 'Name', 'Address', 'Gender', 'Email', 'Phone', 'Nationality', 'Date of Birth', 'Flight Arrival', 'Flight Departure']
                }
            ]
        }
    };

    function text(node) {
        return (node.textContent || '').replace(/\s+/g, ' ').trim();
    }

    /** The row's label without the description sentence that shares its cell. */
    function rowLabel(row) {
        var th = row.querySelector('th');
        if (!th) {
            return '';
        }
        var clone = th.cloneNode(true);
        var description = clone.querySelector('.description');
        if (description) {
            description.remove();
        }
        return text(clone);
    }

    function sectionId(form) {
        var group = form.closest('.group');
        return group ? group.id : '';
    }

    /* ------------------------------------------------------------ switches */

    function isOnOffSelect(select) {
        if (select.options.length !== 2) {
            return false;
        }
        return [select.options[0].value, select.options[1].value].sort().join('|') === 'off|on';
    }

    function buildSwitch(select) {
        var row = select.closest('tr');
        var wrap = document.createElement('span');
        wrap.className = 'wbbm-switch-field';

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'wbbm-switch';
        button.setAttribute('role', 'switch');
        button.setAttribute('aria-label', row ? rowLabel(row) : 'Toggle');

        var state = document.createElement('span');
        state.className = 'wbbm-switch-state';

        function sync() {
            var on = select.value === ON;
            button.setAttribute('aria-checked', on ? 'true' : 'false');
            state.textContent = on ? 'On' : 'Off';
        }

        button.addEventListener('click', function () {
            select.value = select.value === ON ? 'off' : ON;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            sync();
        });
        select.addEventListener('change', sync);

        select.parentNode.insertBefore(wrap, select);
        wrap.appendChild(button);
        wrap.appendChild(state);
        wrap.appendChild(select);
        sync();
    }

    function enhanceSwitches(root) {
        Array.prototype.forEach.call(root.querySelectorAll('table.wbbm-hub-table td > select'), function (select) {
            if (!select.closest('.wbbm-switch-field') && isOnOffSelect(select)) {
                buildSwitch(select);
            }
        });
    }

    /* -------------------------------------------------------------- pills */

    function enhanceCheckboxes(root) {
        Array.prototype.forEach.call(root.querySelectorAll('table.wbbm-hub-table td'), function (cell) {
            var boxes = cell.querySelectorAll('input[type="checkbox"]');
            if (boxes.length < 2 || cell.querySelector('.wbbm-pill-set')) {
                return;
            }

            var set = document.createElement('div');
            set.className = 'wbbm-pill-set';

            Array.prototype.forEach.call(boxes, function (box) {
                var label = box.closest('label') || cell.querySelector('label[for="' + box.id + '"]');
                if (!label) {
                    return;
                }
                var pill = document.createElement('label');
                pill.className = 'wbbm-pill';
                pill.setAttribute('for', box.id);

                var mark = document.createElement('span');
                mark.className = 'wbbm-pill-box';

                var caption = document.createElement('span');
                caption.textContent = text(label);

                function sync() {
                    pill.classList.toggle('is-on', box.checked);
                }
                box.addEventListener('change', sync);

                label.parentNode.insertBefore(pill, label);
                pill.appendChild(box);
                pill.appendChild(mark);
                pill.appendChild(caption);
                label.remove();
                set.appendChild(pill);
                sync();
            });

            if (set.children.length) {
                // strip the <br>s and the repeated help sentence the list left behind
                Array.prototype.forEach.call(cell.querySelectorAll('br'), function (br) { br.remove(); });
                cell.insertBefore(set, cell.firstChild);
                Array.prototype.forEach.call(cell.childNodes, function (node) {
                    if (node.nodeType === 3 && node.textContent.trim()) {
                        node.textContent = '';
                    }
                });
            }
        });
    }

    /* --------------------------------------------------------------- cards */

    function matches(label, needles) {
        var haystack = label.toLowerCase();
        for (var i = 0; i < needles.length; i++) {
            if (haystack.indexOf(needles[i].toLowerCase()) > -1) {
                return true;
            }
        }
        return false;
    }

    function makeCard(spec, collapsible) {
        var card = document.createElement('section');
        card.className = 'wbbm-fieldset';

        var head = document.createElement(collapsible ? 'button' : 'div');
        head.className = 'wbbm-fieldset-head';
        if (collapsible) {
            head.type = 'button';
            head.setAttribute('aria-expanded', 'true');
        }

        var heading = document.createElement('span');
        heading.className = 'wbbm-fieldset-title';
        heading.textContent = spec.title;

        var desc = document.createElement('span');
        desc.className = 'wbbm-fieldset-desc';
        desc.textContent = spec.desc || '';

        var count = document.createElement('span');
        count.className = 'wbbm-fieldset-count';

        head.appendChild(heading);
        head.appendChild(desc);
        head.appendChild(count);

        var body = document.createElement('div');
        body.className = 'wbbm-fieldset-body';

        var table = document.createElement('table');
        table.className = 'form-table wbbm-hub-table';
        table.setAttribute('role', 'presentation');
        var tbody = document.createElement('tbody');
        table.appendChild(tbody);
        body.appendChild(table);

        card.appendChild(head);
        card.appendChild(body);

        if (collapsible) {
            head.addEventListener('click', function () {
                var open = head.getAttribute('aria-expanded') === 'true';
                head.setAttribute('aria-expanded', open ? 'false' : 'true');
                body.hidden = open;
            });
        }

        card.wbbmBody = tbody;
        card.wbbmCount = count;
        card.wbbmHead = head;
        card.wbbmBodyWrap = body;
        return card;
    }

    function buildCards(form, config) {
        var source = form.querySelector('table.wbbm-hub-table');
        if (!source || form.querySelector('.wbbm-fieldset')) {
            return null;
        }

        var rows = Array.prototype.slice.call(source.querySelectorAll('tbody > tr'));
        if (!rows.length) {
            return null;
        }

        var wrap = source.closest('.wbbm-hub-tablewrap') || source;
        var holder = document.createElement('div');
        holder.className = 'wbbm-fieldset-stack';
        wrap.parentNode.insertBefore(holder, wrap);

        var cards = config.cards.map(function (spec) {
            var card = makeCard(spec, config.collapsible);
            holder.appendChild(card);
            return card;
        });

        var other = makeCard({ title: 'Other settings', desc: 'Not yet sorted into a group.' }, config.collapsible);
        holder.appendChild(other);

        rows.forEach(function (row) {
            var label = rowLabel(row);
            var target = other;
            for (var i = 0; i < cards.length; i++) {
                if (matches(label, config.cards[i].match)) {
                    target = cards[i];
                    break;
                }
            }
            target.wbbmBody.appendChild(row);
        });

        cards.concat([other]).forEach(function (card) {
            if (!card.wbbmBody.children.length) {
                card.remove();
            }
        });

        wrap.remove();
        return holder;
    }

    /* ------------------------------------------------ search + filters */

    function addFilters(form, config) {
        var rows = Array.prototype.slice.call(form.querySelectorAll('.wbbm-fieldset tbody > tr, table.wbbm-hub-table tbody > tr'));
        if (rows.length < SEARCH_THRESHOLD || form.querySelector('.wbbm-section-filter')) {
            return;
        }

        var meta = rows.map(function (row) {
            var label = rowLabel(row);
            var field = row.querySelector('input[type="text"]');
            return {
                row: row,
                haystack: (label + ' ' + (field ? field.value : '')).toLowerCase(),
                field: field,
                source: label
            };
        });

        // a translation that no longer reads like its English source
        if (config.changedFilter) {
            meta.forEach(function (m) {
                if (m.field && m.field.value.trim() && m.field.value.trim() !== m.source) {
                    m.row.classList.add('wbbm-row-changed');
                    m.changed = true;
                }
            });
        }

        var bar = document.createElement('div');
        bar.className = 'wbbm-section-filter';

        var id = 'wbbm-filter-' + Math.random().toString(36).slice(2, 8);
        var label = document.createElement('label');
        label.setAttribute('for', id);
        label.textContent = 'Find a setting';

        var input = document.createElement('input');
        input.type = 'search';
        input.id = id;
        input.placeholder = 'Search ' + rows.length + ' settings in this section';

        var changedBtn = null;
        if (config.changedFilter) {
            changedBtn = document.createElement('button');
            changedBtn.type = 'button';
            changedBtn.className = 'wbbm-filter-toggle';
            changedBtn.setAttribute('role', 'switch');
            changedBtn.setAttribute('aria-checked', 'false');
            changedBtn.textContent = 'Only strings I have changed';
            changedBtn.addEventListener('click', function () {
                var on = changedBtn.getAttribute('aria-checked') === 'true';
                changedBtn.setAttribute('aria-checked', on ? 'false' : 'true');
                apply();
            });
        }

        var count = document.createElement('span');
        count.className = 'wbbm-section-filter-count';

        var empty = document.createElement('p');
        empty.className = 'wbbm-section-empty';
        empty.hidden = true;

        function apply() {
            var query = input.value.trim().toLowerCase();
            var changedOnly = changedBtn && changedBtn.getAttribute('aria-checked') === 'true';
            var shown = 0;

            meta.forEach(function (m) {
                var match = (!query || m.haystack.indexOf(query) > -1) && (!changedOnly || m.changed);
                m.row.hidden = !match;
                if (match) {
                    shown += 1;
                }
            });

            Array.prototype.forEach.call(form.querySelectorAll('.wbbm-fieldset'), function (card) {
                var visible = card.querySelectorAll('tbody > tr:not([hidden])').length;
                card.hidden = visible === 0;
                if (card.wbbmCount) {
                    card.wbbmCount.textContent = visible + (query || changedOnly ? ' shown' : '');
                }
                // a search should reveal what it matched
                if ((query || changedOnly) && card.wbbmHead && card.wbbmHead.tagName === 'BUTTON') {
                    card.wbbmHead.setAttribute('aria-expanded', 'true');
                    card.wbbmBodyWrap.hidden = false;
                }
            });

            count.textContent = query || changedOnly ? shown + ' of ' + rows.length + ' shown' : rows.length + ' settings';
            empty.hidden = shown > 0;
            empty.textContent = 'Nothing in this section matches that.';
        }

        input.addEventListener('input', apply);

        bar.appendChild(label);
        bar.appendChild(input);
        if (changedBtn) {
            bar.appendChild(changedBtn);
        }
        bar.appendChild(count);

        var stack = form.querySelector('.wbbm-fieldset-stack') || form.querySelector('.wbbm-hub-tablewrap');
        stack.parentNode.insertBefore(bar, stack);
        stack.parentNode.insertBefore(empty, stack.nextSibling);
        apply();
    }

    /* ------------------------------------------------------------ save bar */

    function enhanceSaveBar(form) {
        var submit = form.querySelector('p.submit');
        if (!submit || submit.querySelector('.wbbm-save-state')) {
            return;
        }

        var state = document.createElement('span');
        state.className = 'wbbm-save-state';

        var dot = document.createElement('span');
        dot.className = 'wbbm-save-dot';
        var word = document.createElement('span');
        word.textContent = 'All changes saved';

        state.appendChild(dot);
        state.appendChild(word);

        var discard = document.createElement('button');
        discard.type = 'reset';
        discard.className = 'button wbbm-discard';
        discard.textContent = 'Discard changes';
        discard.hidden = true;

        submit.insertBefore(state, submit.firstChild);
        submit.insertBefore(discard, submit.querySelector('input[type="submit"]'));

        function dirty() {
            submit.classList.add('is-dirty');
            word.textContent = 'Unsaved changes';
            discard.hidden = false;
        }
        function clean() {
            submit.classList.remove('is-dirty');
            word.textContent = 'All changes saved';
            discard.hidden = true;
        }

        form.addEventListener('input', dirty);
        form.addEventListener('change', dirty);
        form.addEventListener('reset', function () { window.setTimeout(clean, 0); });
    }

    /* ----------------------------------------------------------------- run */

    /** Row count beside each section in the left rail. */
    function railCounts(root) {
        Array.prototype.forEach.call(root.querySelectorAll('.nav-tab-wrapper .nav-tab'), function (tab) {
            if (tab.querySelector('.wbbm-tab-count')) {
                return;
            }
            /*
             * Only the section being viewed is in the DOM, so the count comes
             * from data-count, which show_navigation() writes for every
             * section. The fallback reads data-section rather than the href:
             * the href is a full URL now, and feeding that to querySelector
             * throws a SyntaxError that takes the whole enhancement with it.
             */
            var count = parseInt(tab.getAttribute('data-count'), 10);
            if (!count) {
                var id = tab.getAttribute('data-section') || '';
                var section = /^[A-Za-z][\w-]*$/.test(id) ? root.querySelector('#' + id) : null;
                count = section ? section.querySelectorAll('table.wbbm-hub-table tbody > tr').length : 0;
            }
            if (!count) {
                return;
            }
            var badge = document.createElement('span');
            badge.className = 'wbbm-tab-count';
            badge.textContent = count;
            tab.appendChild(badge);
        });
    }

    /*
     * Each step is isolated: a thrown error in one must not leave the rest of
     * the screen un-enhanced, which is exactly what happened when a bad
     * selector in the rail counts stopped the switches from ever being built.
     */
    function step(name, fn) {
        try {
            fn();
        } catch (error) {
            if (window.console && window.console.warn) {
                window.console.warn('wbbm settings: ' + name + ' failed', error);
            }
        }
    }

    function enhance(root) {
        if (!root) {
            return;
        }

        step('sectionNav', function () { sectionNav(root); });
        step('railCounts', function () { railCounts(root); });
        step('switches', function () { enhanceSwitches(root); });
        step('checkboxes', function () { enhanceCheckboxes(root); });

        Array.prototype.forEach.call(root.querySelectorAll('.group form'), function (form) {
            var config = GROUPS[sectionId(form)] || { cards: [] };
            step('cards', function () {
                if (config.cards.length) {
                    buildCards(form, config);
                }
            });
            step('filters', function () { addFilters(form, config); });
            step('saveBar', function () { enhanceSaveBar(form); });
        });
    }

    /* --------------------------------------------------- section loading */
    /*
     * Each section is its own request (see class-mage-settings.php), which
     * keeps the other sections' fields out of the page. These are real links,
     * so they work without JS -- here they are upgraded to fetch just the new
     * section and swap it in, so switching does not reload the whole screen.
     */

    function reinitSection(section) {
        // the inline init in class-mage-settings.php only runs on a full load
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.wpColorPicker) {
            window.jQuery(section).find('.wp-color-picker-field').wpColorPicker();
        }
    }

    function loadSection(href) {
        var panel = document.querySelector('.wbbm-hub-panel');
        var current = panel ? panel.querySelector('.wbbm_settings_panel') : null;
        if (!current) {
            window.location.href = href;
            return;
        }

        panel.classList.add('wbbm-section-loading');

        window.fetch(href, { credentials: 'same-origin' })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.text();
            })
            .then(function (html) {
                var doc = new window.DOMParser().parseFromString(html, 'text/html');
                var next = doc.querySelector('.wbbm_settings_panel');
                if (!next) {
                    throw new Error('panel missing');
                }

                current.parentNode.replaceChild(next, current);
                window.history.pushState({ wbbmSection: true }, '', href);

                panel.classList.remove('wbbm-section-loading');

                /*
                 * wbbm-admin-shell.js adds .wbbm-hub-table and the scroll
                 * wrapper on first paint only. Without re-running it the
                 * injected table keeps plain form-table markup, every rule
                 * keyed to that class stops matching, and the switches, pills
                 * and cards below find nothing to work on.
                 */
                if (typeof window.wbbmHubDecorate === 'function') {
                    window.wbbmHubDecorate();
                }

                enhance(panel);
                reinitSection(next);
                window.scrollTo(0, 0);
            })
            .catch(function () {
                // anything unexpected falls back to following the link
                window.location.href = href;
            });
    }

    function sectionNav(root) {
        var wrapper = root.querySelector('.nav-tab-wrapper');
        if (!wrapper || wrapper.getAttribute('data-wbbm-section-nav')) {
            return;
        }
        wrapper.setAttribute('data-wbbm-section-nav', '1');

        wrapper.addEventListener('click', function (event) {
            var link = event.target.closest ? event.target.closest('a.nav-tab') : null;
            // let the browser handle modified clicks and middle-click
            if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }
            if (!window.fetch || !window.DOMParser || !window.history.pushState) {
                return;
            }

            event.preventDefault();

            wrapper.querySelectorAll('a.nav-tab').forEach(function (a) {
                a.classList.remove('nav-tab-active');
                a.removeAttribute('aria-current');
            });
            link.classList.add('nav-tab-active');
            link.setAttribute('aria-current', 'page');

            loadSection(link.href);
        });
    }

    window.addEventListener('popstate', function (event) {
        if (event.state && event.state.wbbmSection) {
            window.location.reload();
        }
    });

    function init() {
        enhance(document.querySelector('.wbbm-hub-panel'));
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // the shell fires this after its own tab swap has been decorated
    document.addEventListener('wbbm-hub:tab-loaded', function () {
        enhance(document.querySelector('.wbbm-hub-panel'));
    });

    // the hub swaps panels over AJAX without a reload
    var host = document.querySelector('[data-wbbm-panel]');
    if (host && window.MutationObserver) {
        new window.MutationObserver(function () {
            enhance(document.querySelector('.wbbm-hub-panel'));
        }).observe(host, { childList: true, subtree: true });
    }
})();
