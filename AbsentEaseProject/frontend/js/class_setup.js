/**
 * Teacher "My Class" page: click hours to mark lectures, count them, and save as
 * "monday[2-3-4],tuesday[4-6]" in the hidden input.
 * Used on: pages/teacher/class_setup.php
 */
(function () {
    'use strict';
    const form = document.getElementById('classSetupForm');
    if (!form) return;

    const rows   = form.querySelectorAll('#timetableGrid tr[data-day]');
    const output = document.getElementById('timetableInput');
    const count  = document.getElementById('hoursCount');

    function serialize() {
        const parts = [];
        let total = 0;
        rows.forEach(function (row) {
            const hours = Array.from(row.querySelectorAll('.slot.is-on')).map(function (b) { return b.dataset.hour; });
            total += hours.length;
            if (hours.length) parts.push(row.dataset.day + '[' + hours.join('-') + ']');
        });
        output.value = parts.join(',');
        count.textContent = total;
        return total;
    }

    function set(button, on) {
        button.classList.toggle('is-on', on);
        button.setAttribute('aria-pressed', on);
    }

    rows.forEach(function (row) {
        const buttons = Array.from(row.querySelectorAll('.slot'));
        buttons.forEach(function (button) {
            button.addEventListener('click', function () { set(button, !button.classList.contains('is-on')); serialize(); });
        });
        row.querySelector('.hour-grid__day').addEventListener('click', function () {
            const allOn = buttons.every(function (b) { return b.classList.contains('is-on'); });
            buttons.forEach(function (b) { set(b, !allOn); });
            serialize();
        });
    });

    form.addEventListener('submit', function (event) {
        if (serialize() === 0) {
            event.preventDefault();
            const grid = document.getElementById('timetableGrid');
            grid.classList.add('has-error');
            setTimeout(function () { grid.classList.remove('has-error'); }, 1500);
        }
    });

    serialize();
})();
