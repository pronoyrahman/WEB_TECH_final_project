// TravelVista - client-side form validation. A form opts in with data-validate;
// each input declares its rules via data-* attributes, e.g.
//   <input name="email" data-rules="required email" data-label="Email address">
//   <input name="password" data-rules="required" data-min="8">
//   <input name="password_confirm" data-rules="required" data-match="password">
//   <p class="field__error" data-error-for="email"></p>
// This is a convenience only; controllers and API endpoints validate again server-side.

(function () {
    'use strict';

    const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

    // the rules

    const rules = {
        required: function (value, field) {
            if (field.type === 'checkbox') {
                return field.checked ? null : 'Tick this to continue.';
            }
            return value.trim() === '' ? label(field) + ' is required.' : null;
        },

        email: function (value) {
            if (value.trim() === '') {
                return null;                       // "required" covers empty
            }
            return EMAIL_PATTERN.test(value.trim()) ? null : 'That does not look like an email address.';
        },

        number: function (value, field) {
            if (value.trim() === '') {
                return null;
            }
            if (!/^-?\d+(\.\d+)?$/.test(value.trim())) {
                return label(field) + ' must be a number.';
            }
            const n = parseFloat(value);
            if (field.dataset.numMin !== undefined && n < parseFloat(field.dataset.numMin)) {
                return label(field) + ' cannot be below ' + field.dataset.numMin + '.';
            }
            if (field.dataset.numMax !== undefined && n > parseFloat(field.dataset.numMax)) {
                return label(field) + ' cannot be above ' + field.dataset.numMax + '.';
            }
            return null;
        },

        image: function (value, field) {
            const file = field.files && field.files[0];
            if (!file) {
                return null;                       // choosing nothing is fine
            }
            const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (allowed.indexOf(file.type) === -1) {
                return 'Use a JPG, PNG, WEBP or GIF image.';
            }
            const limit = parseInt(field.dataset.maxBytes || '2097152', 10);
            if (file.size > limit) {
                return 'That image is larger than the ' + (limit / 1048576).toFixed(1) + ' MB limit.';
            }
            return null;
        }
    };

    // Display label for a field, used in messages.
    function label(field) {
        return field.dataset.label || 'This field';
    }

    // checking one field

    function checkField(field, form) {
        const value = field.value === null || field.value === undefined ? '' : field.value;
        const declared = (field.dataset.rules || '').split(/\s+/).filter(Boolean);

        for (let i = 0; i < declared.length; i++) {
            const rule = rules[declared[i]];
            if (rule) {
                const message = rule(value, field);
                if (message) {
                    return message;
                }
            }
        }

        // length limits
        const trimmed = value.trim();

        if (field.dataset.min && trimmed.length > 0 && trimmed.length < parseInt(field.dataset.min, 10)) {
            return field.dataset.minMessage
                || (label(field) + ' needs at least ' + field.dataset.min + ' characters.');
        }

        if (field.dataset.max && trimmed.length > parseInt(field.dataset.max, 10)) {
            return label(field) + ' must be under ' + field.dataset.max + ' characters.';
        }

        // match another field, e.g. password confirmation
        if (field.dataset.match) {
            const other = form.elements[field.dataset.match];
            if (other && value !== other.value) {
                return field.dataset.matchMessage || 'The two entries do not match.';
            }
        }

        // oneOf: at least one box in the named group must be ticked
        if (field.dataset.rules && field.dataset.rules.indexOf('oneOf') !== -1) {
            const group = form.querySelectorAll('input[name="' + field.name + '"]');
            const anyChecked = Array.prototype.some.call(group, function (box) { return box.checked; });
            if (!anyChecked) {
                return label(field) + ' - pick at least one.';
            }
        }

        return null;
    }

    // showing and clearing messages

    function errorBox(form, field) {
        return form.querySelector('[data-error-for="' + field.name + '"]');
    }

    function showError(form, field, message) {
        field.classList.add('is-invalid');
        field.setAttribute('aria-invalid', 'true');

        const box = errorBox(form, field);
        if (box) {
            box.textContent = message;
            box.classList.add('is-shown');
        }
    }

    function clearError(form, field) {
        field.classList.remove('is-invalid');
        field.removeAttribute('aria-invalid');

        const box = errorBox(form, field);
        if (box) {
            box.textContent = '';
            box.classList.remove('is-shown');
        }
    }

    // wiring a form up

    function setUp(form) {
        const fields = Array.prototype.slice.call(form.querySelectorAll('[data-rules]'));

        form.setAttribute('novalidate', 'novalidate');

        fields.forEach(function (field) {
            // check on blur, then live once the field has an error
            field.addEventListener('blur', function () {
                const message = checkField(field, form);
                if (message) {
                    showError(form, field, message);
                } else {
                    clearError(form, field);
                }
            });

            field.addEventListener('input', function () {
                if (field.classList.contains('is-invalid')) {
                    const message = checkField(field, form);
                    if (!message) {
                        clearError(form, field);
                    }
                }
                // re-check the confirmation field when the original changes
                const partner = form.querySelector('[data-match="' + field.name + '"]');
                if (partner && partner.classList.contains('is-invalid')) {
                    const partnerMessage = checkField(partner, form);
                    if (!partnerMessage) {
                        clearError(form, partner);
                    }
                }
            });

            if (field.type === 'file' || field.tagName === 'SELECT') {
                field.addEventListener('change', function () {
                    const message = checkField(field, form);
                    if (message) {
                        showError(form, field, message);
                    } else {
                        clearError(form, field);
                    }
                });
            }
        });

        form.addEventListener('submit', function (event) {
            let firstBad = null;

            fields.forEach(function (field) {
                const message = checkField(field, form);
                if (message) {
                    showError(form, field, message);
                    if (!firstBad) {
                        firstBad = field;
                    }
                } else {
                    clearError(form, field);
                }
            });

            if (firstBad) {
                event.preventDefault();
                firstBad.focus();
                firstBad.scrollIntoView({ block: 'center', behavior: 'smooth' });
                return;
            }

            // block a double submit
            const submit = form.querySelector('[type="submit"]');
            if (submit && !form.dataset.keepEnabled) {
                submit.classList.add('is-busy');
                submit.disabled = true;
                setTimeout(function () {
                    submit.classList.remove('is-busy');
                    submit.disabled = false;
                }, 6000);
            }
        });
    }

    // per-field extras

    // Three-segment password strength bar.
    function setUpStrength(field) {
        const bar = document.querySelector('[data-strength-for="' + field.name + '"]');
        if (!bar) {
            return;
        }
        const segs = bar.querySelectorAll('.strength__seg');

        field.addEventListener('input', function () {
            const value = field.value;
            let score = 0;

            if (value.length >= 8) { score++; }
            if (value.length >= 12 && /[^A-Za-z0-9]/.test(value)) { score++; }
            if (/[A-Z]/.test(value) && /[a-z]/.test(value) && /\d/.test(value)) { score++; }
            if (value.length === 0) { score = 0; }

            const state = ['', 'is-weak', 'is-fair', 'is-strong'][Math.min(score, 3)];

            segs.forEach(function (seg, index) {
                seg.className = 'strength__seg' + (index < score && state ? ' ' + state : '');
            });
        });
    }

    // Live character counter for a textarea with a limit.
    function setUpCounter(field) {
        const counter = document.querySelector('[data-counter-for="' + field.name + '"]');
        if (!counter) {
            return;
        }
        const max = parseInt(field.dataset.max || '0', 10);

        const paint = function () {
            const used = field.value.trim().length;
            counter.textContent = used + ' / ' + max;
            counter.classList.toggle('is-over', used > max);
        };

        field.addEventListener('input', paint);
        paint();
    }

    // Ask the server whether an email is free while the user is still typing.
    // The server checks again before writing.
    function setUpEmailCheck(form, field) {
        const box = errorBox(form, field);
        if (!box || !window.TV) {
            return;
        }

        const check = TV.debounce(async function () {
            const value = field.value.trim();
            if (value === '' || !EMAIL_PATTERN.test(value)) {
                return;
            }
            try {
                const result = await TV.api('?page=api/check_email&email=' + encodeURIComponent(value));
                if (result.checked && result.available === false && field.value.trim() === value) {
                    showError(form, field, 'An account already uses this address.');
                }
            } catch (error) {
                // ignore: the server validates again on submit
            }
        }, 450);

        field.addEventListener('input', check);
    }

    // Preview the chosen image next to the file input.
    function setUpPreview(field) {
        const preview = document.querySelector('[data-preview-for="' + field.name + '"]');
        if (!preview) {
            return;
        }

        field.addEventListener('change', function () {
            const file = field.files && field.files[0];
            if (!file || file.type.indexOf('image/') !== 0) {
                return;
            }
            const reader = new FileReader();
            reader.onload = function (event) { preview.src = event.target.result; };
            reader.readAsDataURL(file);
        });
    }

    // start

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form[data-validate]').forEach(function (form) {
            setUp(form);

            form.querySelectorAll('[data-strength]').forEach(setUpStrength);
            form.querySelectorAll('[data-max]').forEach(setUpCounter);
            form.querySelectorAll('[data-preview]').forEach(setUpPreview);
            form.querySelectorAll('[data-check-email]').forEach(function (field) {
                setUpEmailCheck(form, field);
            });
        });
    });
})();

