// TravelVista - trip cost calculator on a destination page. The figure updates
// locally as the steppers move, then ?page=api/cost_estimate confirms it. Both
// sides run the same formula:
//   weeks = days / 7
//   party = 1 + (travellers - 1) * 0.85   (first traveller pays full)
//   total = base * weeks * party

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        const calc = document.getElementById('costCalc');
        if (!calc) {
            return;
        }

        const postId     = parseInt(calc.dataset.postId, 10);
        const baseCost   = parseFloat(calc.dataset.baseCost);
        const symbol     = calc.dataset.symbol || '$';

        const travellers = document.getElementById('calcTravellers');
        const days       = document.getElementById('calcDays');
        const totalOut   = document.getElementById('calcTotal');
        const perPerson  = document.getElementById('calcPerPerson');
        const perDay     = document.getElementById('calcPerDay');
        const noteBox    = document.getElementById('calcNote');

        const LIMITS = {
            calcTravellers: { min: 1, max: 10 },
            calcDays:       { min: 1, max: 60 }
        };

        // formatting

        function money(amount) {
            const rounded = Math.round(amount * 100) / 100;
            const whole = rounded === Math.round(rounded);
            return symbol + rounded.toLocaleString('en-US', {
                minimumFractionDigits: whole ? 0 : 2,
                maximumFractionDigits: whole ? 0 : 2
            });
        }

        // the shared formula

        function estimate(people, nights) {
            const weeks = nights / 7;
            const party = 1 + (people - 1) * 0.85;
            const total = baseCost * weeks * party;

            return { total: total, perPerson: total / people, perDay: total / nights };
        }

        // reading the controls

        function readValue(field) {
            const limit = LIMITS[field.id];
            let value = parseInt(field.value, 10);

            if (isNaN(value)) {
                value = limit.min;
            }
            return Math.max(limit.min, Math.min(limit.max, value));
        }

        function paint() {
            const people = readValue(travellers);
            const nights = readValue(days);
            const figures = estimate(people, nights);

            totalOut.textContent = money(figures.total);
            perPerson.textContent = money(figures.perPerson);
            perDay.textContent = money(figures.perDay);

            updateSteppers();
        }

        // Disable a stepper arrow at the end of its range.
        function updateSteppers() {
            calc.querySelectorAll('.stepper').forEach(function (stepper) {
                const field = stepper.querySelector('input');
                const limit = LIMITS[field.id];
                const value = readValue(field);

                stepper.querySelector('[data-step="-1"]').disabled = value <= limit.min;
                stepper.querySelector('[data-step="1"]').disabled = value >= limit.max;
            });
        }

        // confirming with the server

        const confirmWithServer = TV.debounce(async function () {
            const people = readValue(travellers);
            const nights = readValue(days);

            try {
                const result = await TV.api('?page=api/cost_estimate&' + TV.query({
                    post_id: postId,
                    travellers: people,
                    days: nights
                }));

                // only overwrite if the controls have not moved on since
                if (readValue(travellers) === people && readValue(days) === nights) {
                    totalOut.textContent = result.display.total;
                    perPerson.textContent = result.display.per_person;
                    perDay.textContent = result.display.per_day;
                }
                setNote('Confirmed against the archive on ' + result.display.base + ' base cost.');
            } catch (error) {
                setNote('Estimated in your browser - the server could not be reached.');
            }
        }, 400);

        function setNote(text) {
            if (noteBox) {
                noteBox.textContent = text;
            }
        }

        // events

        calc.addEventListener('click', function (event) {
            const button = event.target.closest('[data-step]');
            if (!button) {
                return;
            }

            const field = button.closest('.stepper').querySelector('input');
            const limit = LIMITS[field.id];
            const next = readValue(field) + parseInt(button.dataset.step, 10);

            field.value = Math.max(limit.min, Math.min(limit.max, next));
            paint();
            confirmWithServer();
        });

        [travellers, days].forEach(function (field) {
            field.addEventListener('input', function () {
                // allow the field to be briefly empty while retyping
                if (field.value.trim() !== '') {
                    paint();
                    confirmWithServer();
                }
            });

            field.addEventListener('blur', function () {
                field.value = readValue(field);
                paint();
                confirmWithServer();
            });
        });

        paint();
        confirmWithServer();
    });
})();

