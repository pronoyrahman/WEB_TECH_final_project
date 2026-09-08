// TravelVista - comments on a destination page. Posting and deleting go over
// AJAX, so a new comment appears immediately and a deleted one fades out.

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        const form = document.getElementById('commentForm');
        const list = document.getElementById('commentList');
        const countLabel = document.getElementById('commentCount');

        if (!list) {
            return;
        }

        // posting a comment

        if (form) {
            const textarea = form.querySelector('[name="content"]');
            const submit = form.querySelector('[type="submit"]');
            const errorBox = form.querySelector('[data-error-for="content"]');
            const postId = parseInt(form.dataset.postId, 10);
            const maxLength = parseInt(textarea.dataset.max || '800', 10);

            form.addEventListener('submit', async function (event) {
                event.preventDefault();

                const content = textarea.value.trim();

                // the same checks the endpoint makes, run here first
                if (content === '') {
                    return fail('Write something before posting.');
                }
                if (content.length < 3) {
                    return fail('That is a little short - add a few more words.');
                }
                if (content.length > maxLength) {
                    return fail('Keep it under ' + maxLength + ' characters.');
                }

                clearFail();
                submit.classList.add('is-busy');
                submit.disabled = true;

                try {
                    const result = await TV.api('?page=api/comments_add', {
                        method: 'POST',
                        body: { post_id: postId, body: content }
                    });

                    removeEmptyState();
                    list.insertAdjacentHTML('beforeend', renderComment(result.comment));
                    setCount(result.count);

                    textarea.value = '';
                    textarea.dispatchEvent(new Event('input'));    // resets the counter

                    const added = list.lastElementChild;
                    added.classList.add('is-new');
                    added.scrollIntoView({ block: 'nearest', behavior: 'smooth' });

                    TV.toast('success', result.message);
                } catch (error) {
                    fail(error.message);
                } finally {
                    submit.classList.remove('is-busy');
                    submit.disabled = false;
                }
            });

            function fail(message) {
                if (errorBox) {
                    errorBox.textContent = message;
                    errorBox.classList.add('is-shown');
                }
                textarea.classList.add('is-invalid');
                textarea.focus();
            }

            function clearFail() {
                if (errorBox) {
                    errorBox.textContent = '';
                    errorBox.classList.remove('is-shown');
                }
                textarea.classList.remove('is-invalid');
            }

            // this form is not wired to validation.js, so the counter lives here
            const counter = form.querySelector('[data-counter-for="content"]');

            function paintCounter() {
                if (!counter) {
                    return;
                }
                const used = textarea.value.trim().length;
                counter.textContent = used + ' / ' + maxLength;
                counter.classList.toggle('is-over', used > maxLength);
            }

            textarea.addEventListener('input', function () {
                if (textarea.classList.contains('is-invalid') && textarea.value.trim() !== '') {
                    clearFail();
                }
                paintCounter();
            });

            paintCounter();
        }

        // deleting your own comment

        list.addEventListener('click', async function (event) {
            const button = event.target.closest('[data-delete-comment]');
            if (!button) {
                return;
            }

            const item = button.closest('.comment');
            const commentId = parseInt(button.dataset.commentId, 10);

            if (!TV.confirm('Delete this comment? It cannot be brought back.')) {
                return;
            }

            button.classList.add('is-busy');

            try {
                const result = await TV.api('?page=api/comments_delete', {
                    method: 'POST',
                    body: { comment_id: commentId }
                });

                item.classList.add('is-leaving');
                fadeOut(item, function () {
                    if (list.children.length === 0) {
                        showEmptyState();
                    }
                });

                setCount(result.count);
                TV.toast('success', result.message);
            } catch (error) {
                button.classList.remove('is-busy');
                TV.toast('error', error.message);
            }
        });

        // small helpers

        function renderComment(comment) {
            const face = comment.avatar
                ? '<img class="avatar" src="' + TV.escape(comment.avatar) + '" alt="">'
                : '<span class="avatar" aria-hidden="true">' + TV.escape(comment.initial) + '</span>';

            const remove = comment.own
                ? '<button class="btn btn--link comment__del" type="button"'
                    + ' data-delete-comment data-comment-id="' + comment.id + '">Delete</button>'
                : '';

            return '<article class="comment" data-comment="' + comment.id + '">'
                 +   face
                 +   '<div>'
                 +     '<div class="comment__head">'
                 +       '<span class="comment__author">' + TV.escape(comment.author) + '</span>'
                 +       '<span class="comment__when">' + TV.escape(comment.when) + '</span>'
                 +       remove
                 +     '</div>'
                 +     '<p class="comment__body">' + TV.escape(comment.content) + '</p>'
                 +   '</div>'
                 + '</article>';
        }

        // Remove an element after it fades, or after a timeout. A background tab
        // never fires transitionend, so the timer is the fallback.
        function fadeOut(element, done) {
            var finished = false;

            function finish() {
                if (finished) {
                    return;
                }
                finished = true;
                element.remove();
                done();
            }

            element.addEventListener('transitionend', finish, { once: true });
            setTimeout(finish, 400);
        }

        function setCount(count) {
            if (countLabel) {
                countLabel.textContent = count;
            }
        }

        function removeEmptyState() {
            const empty = document.getElementById('commentsEmpty');
            if (empty) {
                empty.remove();
            }
        }

        function showEmptyState() {
            if (document.getElementById('commentsEmpty')) {
                return;
            }
            list.insertAdjacentHTML('afterend',
                '<div class="empty" id="commentsEmpty"><h3>No notes yet</h3>'
                + '<p>Been here? Tell the next traveller what to expect.</p></div>');
        }
    });
})();

