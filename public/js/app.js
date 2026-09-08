// TravelVista - shared browser code, loaded on every page. Sets up window.TV,
// the top bar, toasts and the wishlist buttons.

(function () {
    'use strict';

    // TV: shared helpers the page scripts build on

    const baseMeta = document.querySelector('meta[name="base-url"]');
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');

    const TV = {
        baseUrl: baseMeta ? baseMeta.content : '',
        csrf: csrfMeta ? csrfMeta.content : ''
    };

    // Build a project URL from a relative path.
    TV.url = function (path) {
        return TV.baseUrl + '/' + String(path).replace(/^\/+/, '');
    };

    // Escape a value before inserting it into innerHTML.
    TV.escape = function (value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    // Call a JSON API endpoint. Resolves to the parsed body; throws an Error
    // carrying the server's message when the response is not ok.
    TV.api = async function (path, options = {}) {
        const method = (options.method || 'GET').toUpperCase();
        const config = {
            method: method,
            headers: { 'Accept': 'application/json', 'X-CSRF-Token': TV.csrf },
            credentials: 'same-origin'
        };

        if (options.body !== undefined) {
            config.headers['Content-Type'] = 'application/json';
            config.body = JSON.stringify(Object.assign({ csrf_token: TV.csrf }, options.body));
        }

        let response;
        try {
            response = await fetch(TV.url(path), config);
        } catch (networkError) {
            throw new Error('No connection to the server. Check that it is running.');
        }

        let payload = {};
        try {
            payload = await response.json();
        } catch (parseError) {
            throw new Error('The server sent something unreadable (HTTP ' + response.status + ').');
        }

        if (!response.ok || payload.ok === false) {
            const error = new Error(payload.error || 'That did not work. Try again.');
            error.status = response.status;
            error.payload = payload;
            throw error;
        }

        return payload;
    };

    // Turn an object into a query string, skipping empty values.
    TV.query = function (params) {
        const search = new URLSearchParams();

        Object.keys(params).forEach(function (key) {
            const value = params[key];
            if (Array.isArray(value)) {
                value.forEach(function (item) { search.append(key + '[]', item); });
            } else if (value !== '' && value !== null && value !== undefined) {
                search.append(key, value);
            }
        });

        return search.toString();
    };

    // Toasts

    const MARKS = { success: 'Done', error: 'Stop', info: 'Note' };

    TV.toast = function (type, message) {
        const kind = MARKS[type] ? type : 'info';

        let stack = document.querySelector('.toast-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.className = 'toast-stack';
            stack.setAttribute('role', 'status');
            stack.setAttribute('aria-live', 'polite');
            document.body.appendChild(stack);
        }

        const toast = document.createElement('div');
        toast.className = 'toast toast--' + kind;
        toast.innerHTML = '<span class="toast__mark">' + MARKS[kind] + '</span>'
                        + '<span>' + TV.escape(message) + '</span>';
        stack.appendChild(toast);

        const remove = function () {
            toast.classList.add('is-leaving');
            toast.addEventListener('animationend', function () { toast.remove(); }, { once: true });
        };

        setTimeout(remove, kind === 'error' ? 6000 : 3600);
        toast.addEventListener('click', remove);
    };

    // Card markup - must match app/Views/layouts/dispatch_card.php so an AJAX
    // result renders the same as a server-rendered card.

    TV.renderMeter = function (segments) {
        let html = '<span class="meter" role="img" aria-label="Cost level">';
        for (let i = 1; i <= 3; i++) {
            html += '<span class="meter__seg' + (i <= segments ? ' is-on' : '') + '"></span>';
        }
        return html + '</span>';
    };

    TV.renderCard = function (post, index, canSave) {
        // stagger comes from the .reveal:nth-child rules in global.css
        const saveButton = canSave
            ? '<button class="dispatch__save' + (post.saved ? ' is-saved' : '') + '" type="button"'
                + ' data-wishlist-toggle data-post-id="' + post.id + '"'
                + ' aria-pressed="' + (post.saved ? 'true' : 'false') + '"'
                + ' title="' + (post.saved ? 'Remove from wishlist' : 'Save to wishlist') + '">'
                + '<span class="sr-only">' + (post.saved ? 'Remove from wishlist' : 'Save to wishlist') + '</span>'
                + '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"'
                + ' stroke-linejoin="round" aria-hidden="true">'
                + '<path d="M6 3h12a1 1 0 0 1 1 1v17l-7-4-7 4V4a1 1 0 0 1 1-1z"/></svg></button>'
            : '';

        return ''
            + '<article class="dispatch reveal">'
            +   '<a class="dispatch__cover" href="' + TV.escape(post.url) + '" tabindex="-1" aria-hidden="true">'
            +     '<img src="' + TV.escape(post.cover) + '" alt="" loading="lazy" width="800" height="350">'
            +     '<span class="tag dispatch__stamp">' + TV.escape(post.genre) + '</span>'
            +   '</a>'
            +   saveButton
            +   '<div class="dispatch__body">'
            +     '<h3 class="dispatch__title"><a href="' + TV.escape(post.url) + '">'
            +       TV.escape(post.title) + '</a></h3>'
            +     '<p class="dispatch__snippet">' + TV.escape(post.snippet) + '</p>'
            +     '<dl class="ledger">'
            +       '<div class="ledger__row"><dt class="ledger__key">Country</dt>'
            +         '<dd class="ledger__val">' + TV.escape(post.country) + '</dd></div>'
            +       '<div class="ledger__row"><dt class="ledger__key">Medium</dt>'
            +         '<dd class="ledger__val">' + TV.escape(post.medium) + '</dd></div>'
            +       '<div class="ledger__row"><dt class="ledger__key">Cost</dt>'
            +         '<dd class="ledger__val">' + TV.renderMeter(post.segments)
            +           '<span class="ml-text">' + TV.escape(post.cost_label) + '</span></dd></div>'
            +     '</dl>'
            +   '</div>'
            +   '<div class="dispatch__foot">'
            +     '<span class="dispatch__by">Filed by <strong>' + TV.escape(post.scout_name) + '</strong></span>'
            +     '<span class="filenum">' + TV.escape(post.file_number) + '</span>'
            +   '</div>'
            + '</article>';
    };

    // Run fn once after calls stop for `wait` ms.
    TV.debounce = function (fn, wait) {
        let timer = null;
        return function () {
            const args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () { fn.apply(null, args); }, wait);
        };
    };

    TV.confirm = function (message) {
        return window.confirm(message);
    };

    window.TV = TV;

    // Top bar: mobile menu and account menu

    document.addEventListener('DOMContentLoaded', function () {

        const topbar = document.getElementById('topbar');
        const navToggle = document.getElementById('navToggle');

        if (topbar && navToggle) {
            navToggle.addEventListener('click', function () {
                const open = topbar.classList.toggle('is-open');
                navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                navToggle.setAttribute('aria-label', open ? 'Hide menu' : 'Show menu');
            });
        }

        const userMenu = document.getElementById('userMenu');
        const userMenuBtn = document.getElementById('userMenuBtn');

        if (userMenu && userMenuBtn) {
            userMenuBtn.addEventListener('click', function (event) {
                event.stopPropagation();
                const open = userMenu.classList.toggle('is-open');
                userMenuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });

            document.addEventListener('click', function (event) {
                if (!userMenu.contains(event.target)) {
                    userMenu.classList.remove('is-open');
                    userMenuBtn.setAttribute('aria-expanded', 'false');
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    userMenu.classList.remove('is-open');
                    userMenuBtn.setAttribute('aria-expanded', 'false');
                }
            });
        }

        // dismissable flash messages
        document.addEventListener('click', function (event) {
            const close = event.target.closest('.flash__close');
            if (close) {
                close.closest('.flash').remove();
            }
        });
    });

    // Wishlist buttons, delegated from the document so buttons added later by
    // the browse page's AJAX results also work.

    document.addEventListener('click', async function (event) {
        const button = event.target.closest('[data-wishlist-toggle]');
        if (!button || button.classList.contains('is-busy')) {
            return;
        }

        event.preventDefault();

        const postId = parseInt(button.dataset.postId, 10);
        const saved = button.classList.contains('is-saved');
        const endpoint = saved ? '?page=api/wishlist_remove' : '?page=api/wishlist_add';

        button.classList.add('is-busy');

        try {
            const result = await TV.api(endpoint, { method: 'POST', body: { post_id: postId } });

            // sync every button for this post
            document.querySelectorAll('[data-wishlist-toggle][data-post-id="' + postId + '"]')
                .forEach(function (el) {
                    el.classList.toggle('is-saved', result.saved);
                    el.setAttribute('aria-pressed', result.saved ? 'true' : 'false');
                    el.title = result.saved ? 'Remove from wishlist' : 'Save to wishlist';

                    const label = el.querySelector('.sr-only');
                    if (label) {
                        label.textContent = result.saved ? 'Remove from wishlist' : 'Save to wishlist';
                    }
                    const text = el.querySelector('[data-wishlist-label]');
                    if (text) {
                        text.textContent = result.saved ? 'Saved to wishlist' : 'Save to wishlist';
                    }
                });

            updateWishlistCount(result.count);
            TV.toast('success', result.message);

            document.dispatchEvent(new CustomEvent('tv:wishlist', { detail: result }));
        } catch (error) {
            TV.toast('error', error.message);
        } finally {
            button.classList.remove('is-busy');
        }
    });

    // Update the wishlist count shown in the top bar.
    function updateWishlistCount(count) {
        const badge = document.getElementById('navWishlistCount');
        if (!badge) {
            return;
        }
        badge.textContent = count;
        badge.hidden = count === 0;
    }

    TV.updateWishlistCount = updateWishlistCount;
})();

