<?php
/**
 * Student: new OD request.  Controller: pages/student/request_od.php   JS: od_request_form.js
 * Variables: $class_teacher (or null), $lecture_hours (['monday' => [2,3]]), $taken_slots (['2026-11-02' => [3]]), $date_window (min/max)
 *
 * The hour grid is drawn by JavaScript for the chosen dates, using the data-* values on #odGrid.
 * The selection is sent as  slots = "2026-11-02:3,2026-11-02:4".
 */
?>
<?php include_partial('layout_start', ['page_title' => 'Request OD', 'active_nav' => 'request', 'stylesheets' => ['timetable.css']]); ?>

<div class="page-header">
    <div><h1>Request On-Duty</h1><p>Select the lecture hours you missed (or will miss) for a college event.</p></div>
</div>

<?php if (!$class_teacher): ?>
    <div class="card"><?php include_partial('empty_state', ['icon' => 'calendar', 'title' => 'Your class has no class teacher yet',
        'text' => 'OD requests go to your class teacher. Once they set up the class timetable you can request OD here.']); ?></div>
<?php else: ?>
<form method="post" id="odRequestForm" class="grid-sidebar">
    <div class="stack">
        <div class="card">
            <div class="card__header"><div><h2>1. Event</h2><p>What is the OD for?</p></div></div>
            <div class="card__body form">
                <div class="field">
                    <label class="required" for="event_name">Event name</label>
                    <input class="input" id="event_name" name="event_name" maxlength="150" placeholder="e.g. Inter-college hackathon at Christ University" required>
                </div>
                <div class="field">
                    <label for="reason">Details <span class="faint">(optional)</span></label>
                    <textarea class="textarea" id="reason" name="reason" maxlength="500" placeholder="Your role in the event, organiser, anything your class teacher should know"></textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card__header">
                <div><h2>2. Dates and hours</h2><p>Only your class's lecture hours can be selected. Click a date to select all its hours.</p></div>
            </div>
            <div class="card__body form">
                <div class="form-row">
                    <div class="field">
                        <label class="required" for="fromDate">From</label>
                        <input class="input" type="date" id="fromDate" min="<?= e($date_window['min']) ?>" max="<?= e($date_window['max']) ?>" required>
                    </div>
                    <div class="field">
                        <label class="required" for="toDate">To</label>
                        <input class="input" type="date" id="toDate" min="<?= e($date_window['min']) ?>" max="<?= e($date_window['max']) ?>" required>
                        <span class="hint">Up to <?= OD_MAX_DAYS_PER_REQUEST ?> days</span>
                    </div>
                </div>
                <div id="odGrid" class="hour-grid-wrap"
                     data-lecture-hours="<?= e(json_encode($lecture_hours)) ?>"
                     data-taken="<?= e(json_encode($taken_slots)) ?>"
                     data-max-days="<?= OD_MAX_DAYS_PER_REQUEST ?>"
                     data-hours="<?= HOURS_PER_DAY ?>" data-lunch="<?= LUNCH_HOUR ?>">
                </div>
                <div class="legend">
                    <span><i class="legend__box legend__box--free"></i>Lecture hour</span>
                    <span><i class="legend__box legend__box--on"></i>Selected</span>
                    <span><i class="legend__box legend__box--taken"></i>Already requested</span>
                    <span><i class="legend__box legend__box--off"></i>No lecture</span>
                </div>
            </div>
        </div>
    </div>

    <aside class="stack sticky-side">
        <div class="card">
            <div class="card__header"><div><h2>3. Proof</h2><p>Your class teacher checks this before approving.</p></div></div>
            <div class="card__body form">
                <div class="field">
                    <label class="required" for="proof_link">Proof link</label>
                    <input class="input" id="proof_link" name="proof_link" maxlength="500" placeholder="https://drive.google.com/..." required>
                    <span class="hint">Google Drive link to your certificate / invitation. Make sure it is shared with “Anyone with the link”.</span>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card__body">
                <dl class="facts">
                    <dt>Goes to</dt><dd><?= e($class_teacher) ?> (class teacher)</dd>
                    <dt>Selected</dt><dd><span id="selectedSummary">No hours selected</span></dd>
                </dl>
            </div>
            <div class="card__footer">
                <input type="hidden" name="slots" id="slotsInput">
                <button type="submit" name="submit_od_request" class="btn btn--primary btn--block"><?= icon('check') ?>Submit request</button>
            </div>
        </div>
    </aside>
</form>
<?php endif; ?>

<?php include_partial('layout_end', ['scripts' => ['od_request_form.js']]); ?>
