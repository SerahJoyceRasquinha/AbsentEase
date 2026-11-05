/**
 * Request OD page: draws the "date x hour" grid for the chosen dates and
 * sends the selection as  slots = "2026-11-02:3,2026-11-02:4".
 * Used on: pages/student/request_od.php
 *
 * Data comes from <div id="odGrid" data-...> (filled by the backend):
 *   data-lecture-hours  {"monday":[2,3,4], ...}  hours the class has lectures (selectable)
 *   data-taken          {"2026-11-02":[3]}       hours already in a pending/approved request
 *   data-max-days, data-hours, data-lunch
 */
(function () {
    'use strict';
    const form = document.getElementById('odRequestForm');
    if (!form) return;

    const grid      = document.getElementById('odGrid');
    const fromInput = document.getElementById('fromDate');
    const toInput   = document.getElementById('toDate');
    const summary   = document.getElementById('selectedSummary');
    const output    = document.getElementById('slotsInput');

    const lectures = JSON.parse(grid.dataset.lectureHours || '{}');
    const taken    = JSON.parse(grid.dataset.taken || '{}');
    const maxDays  = parseInt(grid.dataset.maxDays, 10);
    const hours    = parseInt(grid.dataset.hours, 10);
    const lunch    = parseInt(grid.dataset.lunch, 10);
    const DAY_KEYS = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

    const selected = new Set();     // "2026-11-02:3"

    // "2026-11-02" <-> Date (local time, no timezone surprises)
    function parse(ymd) { const p = ymd.split('-').map(Number); return new Date(p[0], p[1] - 1, p[2]); }
    function ymd(d) { return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); }
    function label(d) { return d.toLocaleDateString('en-GB', { weekday: 'short', day: '2-digit', month: 'short' }); }

    function datesInRange() {
        if (!fromInput.value) return [];
        if (!toInput.value || toInput.value < fromInput.value) toInput.value = fromInput.value;
        const dates = [];
        const d = parse(fromInput.value);
        const end = parse(toInput.value);
        while (d <= end && dates.length < maxDays) { dates.push(new Date(d)); d.setDate(d.getDate() + 1); }
        toInput.value = ymd(dates[dates.length - 1]);      // clamp to the max length
        return dates;
    }

    function render() {
        const dates = datesInRange();
        const visibleKeys = new Set();
        grid.innerHTML = '';

        if (!dates.length) {
            grid.innerHTML = '<div class="hour-grid-empty">Choose the event dates to see your lecture hours.</div>';
            update();
            return;
        }

        const table = document.createElement('table');
        table.className = 'hour-grid';
        let head = '<thead><tr><th class="hour-grid__day">Date</th>';
        for (let h = 1; h <= hours; h++) head += h === lunch ? '<th class="hour-grid__lunch">Lunch</th>' : '<th>' + h + '</th>';
        table.innerHTML = head + '</tr></thead>';
        const body = document.createElement('tbody');

        dates.forEach(function (date) {
            const key = ymd(date);
            const weekday = DAY_KEYS[date.getDay()];
            const lectureHours = lectures[weekday] || [];
            const takenHours = taken[key] || [];
            const row = document.createElement('tr');

            const dayCell = document.createElement('th');
            dayCell.className = 'hour-grid__day';
            dayCell.textContent = label(date);
            row.appendChild(dayCell);

            if (weekday === 'sunday') {
                const cell = document.createElement('td');
                cell.colSpan = hours;
                cell.className = 'hour-grid__note';
                cell.textContent = 'Sunday — no lectures';
                row.appendChild(cell);
                body.appendChild(row);
                return;
            }

            const rowButtons = [];
            for (let h = 1; h <= hours; h++) {
                const cell = document.createElement('td');
                if (h === lunch) {
                    cell.className = 'hour-grid__lunch';
                } else if (lectureHours.indexOf(h) === -1) {
                    cell.innerHTML = '<span class="slot slot--off">–</span>';
                } else if (takenHours.indexOf(h) !== -1) {
                    cell.innerHTML = '<span class="slot slot--taken" title="Already in another request">' + h + '</span>';
                } else {
                    const slotKey = key + ':' + h;
                    visibleKeys.add(slotKey);
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'slot slot--free' + (selected.has(slotKey) ? ' is-on' : '');
                    button.textContent = h;
                    button.setAttribute('aria-pressed', selected.has(slotKey));
                    button.addEventListener('click', function () { toggle(slotKey, button); });
                    rowButtons.push({ key: slotKey, button: button });
                    cell.appendChild(button);
                }
                row.appendChild(cell);
            }

            if (rowButtons.length) {
                dayCell.classList.add('is-clickable');
                dayCell.title = 'Select / clear all hours of this day';
                dayCell.addEventListener('click', function () {
                    const allOn = rowButtons.every(function (b) { return selected.has(b.key); });
                    rowButtons.forEach(function (b) { toggle(b.key, b.button, !allOn); });
                });
            }
            body.appendChild(row);
        });

        // forget selections for dates no longer shown
        Array.from(selected).forEach(function (k) { if (!visibleKeys.has(k)) selected.delete(k); });
        table.appendChild(body);
        grid.appendChild(table);
        update();
    }

    function toggle(key, button, force) {
        const on = force === undefined ? !selected.has(key) : force;
        if (on) selected.add(key); else selected.delete(key);
        button.classList.toggle('is-on', on);
        button.setAttribute('aria-pressed', on);
        update();
    }

    function update() {
        const days = new Set(Array.from(selected).map(function (k) { return k.split(':')[0]; }));
        summary.textContent = selected.size
            ? selected.size + ' hour' + (selected.size === 1 ? '' : 's') + ' on ' + days.size + ' day' + (days.size === 1 ? '' : 's')
            : 'No hours selected';
        output.value = Array.from(selected).sort().join(',');
    }

    fromInput.addEventListener('change', function () { if (!toInput.value || toInput.value < fromInput.value) toInput.value = fromInput.value; render(); });
    toInput.addEventListener('change', render);

    form.addEventListener('submit', function (event) {
        if (selected.size === 0) {
            event.preventDefault();
            grid.classList.add('has-error');
            summary.textContent = 'Select at least one hour';
            setTimeout(function () { grid.classList.remove('has-error'); }, 1500);
        }
    });

    render();
})();
