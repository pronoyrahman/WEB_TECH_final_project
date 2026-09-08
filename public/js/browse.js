// TravelVista - browse page: live search and filtering over AJAX. The search
// box calls ?page=api/posts_search; the filters call ?page=api/posts_filter.
// The grid is redrawn from JSON without a reload, and the filter state is
// mirrored into the address bar so a view can be shared or bookmarked.

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        const page = document.getElementById('browsePage');
        if (!page) {
            return;
        }

        const searchInput = document.getElementById('searchInput');
        const results     = document.getElementById('results');
        const countLabel  = document.getElementById('resultCount');
        const chips       = document.getElementById('activeChips');
        const clearButton = document.getElementById('clearFilters');
        const sortSelect  = document.getElementById('sortSelect');
        const countrySel  = document.getElementById('countryFilter');

        const canSave = page.dataset.canSave === '1';

        // reading the current filter state

        function currentFilters() {
            return {
                q: searchInput ? searchInput.value.trim() : '',
                country: countrySel ? countrySel.value : '',
                genre: checkedValues('genre'),
                cost: checkedValues('cost'),
                sort: sortSelect ? sortSelect.value : 'newest'
            };
        }

        function checkedValues(name) {
            return Array.prototype.map.call(
                page.querySelectorAll('input[name="' + name + '"]:checked'),
                function (box) { return box.value; }
            );
        }

        // drawing

        function draw(payload) {
            if (payload.count === 0) {
                results.innerHTML = emptyState(payload.filters ? payload.filters.q : '');
            } else {
                results.innerHTML = payload.results.map(function (post, index) {
                    return TV.renderCard(post, index, canSave);
                }).join('');
            }

            countLabel.innerHTML = '<b>' + payload.count + '</b> '
                                 + (payload.count === 1 ? 'dispatch' : 'dispatches');

            drawChips();
        }

        function emptyState(term) {
            const what = term
                ? 'Nothing in the archive matches &ldquo;' + TV.escape(term) + '&rdquo;.'
                : 'No dispatch matches those filters yet.';

            return '<div class="empty"><h3>No matches</h3><p>' + what
                 + ' Try a broader search, or clear the filters to see everything.</p>'
                 + '<button class="btn btn--ghost" type="button" data-clear-filters>Clear filters</button></div>';
        }

        // Render the active-filter chips.
        function drawChips() {
            if (!chips) {
                return;
            }
            const state = currentFilters();
            const parts = [];

            if (state.q) {
                parts.push(chip('search', state.q, 'q'));
            }
            if (state.country) {
                parts.push(chip('country', state.country, 'country'));
            }
            state.genre.forEach(function (value) { parts.push(chip('genre', value, 'genre:' + value)); });
            state.cost.forEach(function (value) { parts.push(chip('cost', value, 'cost:' + value)); });

            chips.innerHTML = parts.join('');
            chips.hidden = parts.length === 0;

            if (clearButton) {
                clearButton.hidden = parts.length === 0;
            }
        }

        function chip(kind, value, token) {
            return '<span class="chip">' + TV.escape(kind) + ': ' + TV.escape(value)
                 + '<button type="button" data-drop="' + TV.escape(token) + '"'
                 + ' aria-label="Remove this filter">&times;</button></span>';
        }

        // fetching

        let sequence = 0;          // guards against a slow reply overwriting a fast one

        async function load(useSearchEndpoint) {
            const state = currentFilters();
            const ticket = ++sequence;

            page.classList.add('is-loading');

            const endpoint = useSearchEndpoint
                ? '?page=api/posts_search&' + TV.query({ q: state.q })
                : '?page=api/posts_filter&' + TV.query(state);

            try {
                const payload = await TV.api(endpoint);

                if (ticket !== sequence) {
                    return;                        // a newer request has already started
                }
                draw(payload);
                syncAddressBar(state);
            } catch (error) {
                if (ticket === sequence) {
                    results.innerHTML = '<div class="empty"><h3>Could not load the archive</h3><p>'
                                      + TV.escape(error.message) + '</p>'
                                      + '<button class="btn btn--ghost" type="button" data-retry>Try again</button></div>';
                }
            } finally {
                if (ticket === sequence) {
                    page.classList.remove('is-loading');
                }
            }
        }

        // Mirror the filter state into the URL. page=browse must stay first, or
        // reloading a shared link lands on the home page.
        function syncAddressBar(state) {
            const query = TV.query(state === undefined ? currentFilters() : state);
            const url = window.location.pathname + '?page=browse' + (query ? '&' + query : '');
            window.history.replaceState(null, '', url);
        }

        // events

        // True when only the search box is narrowing results.
        function searchOnly() {
            const state = currentFilters();
            return state.q !== ''
                && state.country === ''
                && state.genre.length === 0
                && state.cost.length === 0;
        }

        if (searchInput) {
            const runSearch = TV.debounce(function () { load(searchOnly()); }, 280);

            searchInput.addEventListener('input', runSearch);

            searchInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    load(searchOnly());
                }
                if (event.key === 'Escape' && searchInput.value !== '') {
                    searchInput.value = '';
                    load(false);
                }
            });
        }

        page.querySelectorAll('input[name="genre"], input[name="cost"]').forEach(function (box) {
            box.addEventListener('change', function () { load(false); });
        });

        if (countrySel) {
            countrySel.addEventListener('change', function () { load(false); });
        }

        if (sortSelect) {
            sortSelect.addEventListener('change', function () { load(false); });
        }

        // chip removal, clear-all, retry
        document.addEventListener('click', function (event) {

            const drop = event.target.closest('[data-drop]');
            if (drop) {
                const token = drop.dataset.drop;

                if (token === 'q' && searchInput) {
                    searchInput.value = '';
                } else if (token === 'country' && countrySel) {
                    countrySel.value = '';
                } else {
                    const pair = token.split(':');
                    const box = page.querySelector('input[name="' + pair[0] + '"][value="' + pair[1] + '"]');
                    if (box) {
                        box.checked = false;
                    }
                }
                load(false);
                return;
            }

            if (event.target.closest('[data-clear-filters]')) {
                clearAll();
                return;
            }

            if (event.target.closest('[data-retry]')) {
                load(false);
            }
        });

        function clearAll() {
            if (searchInput) { searchInput.value = ''; }
            if (countrySel)  { countrySel.value = ''; }
            page.querySelectorAll('input[name="genre"], input[name="cost"]')
                .forEach(function (box) { box.checked = false; });
            load(false);
        }

        // start

        // PHP renders the first page of results, so only the chips need
        // drawing on load - no request on arrival.
        drawChips();
    });
})();

