<?php
/**
 * Teacher: choose class + mark weekly lecture hours.  Controller: pages/teacher/class_setup.php
 * Variables: $class (current setup or null), $choices (['26csmm' => ['students'=>60,'semester'=>1]]),
 *            $selected (['monday' => [2,3]] currently saved hours)
 * Grid clicks + saving format: frontend/js/class_setup.js
 */
$current = $class['year_course'] ?? '';
?>
<?php include_partial('layout_start', ['page_title' => 'My Class', 'active_nav' => 'class', 'stylesheets' => ['timetable.css']]); ?>

<div class="page-header">
    <div><h1>My class</h1><p>Students of this class send their OD requests to you, only for the lecture hours you mark here.</p></div>
</div>

<?php if (!$choices): ?>
    <div class="card"><?php include_partial('empty_state', ['icon' => 'users', 'title' => 'No class available',
        'text' => 'Every class already has a class teacher, or the admin has not added students yet.']); ?></div>
<?php else: ?>
<form method="post" id="classSetupForm" class="stack">
    <div class="card">
        <div class="card__header"><div><h2>Class</h2><p>Only classes without a class teacher (and your own) are listed.</p></div></div>
        <div class="card__body form">
            <div class="form-row">
                <div class="field">
                    <label class="required" for="year_course">Year + course</label>
                    <select class="select" id="year_course" name="year_course" required>
                        <option value="">Choose a class…</option>
                        <?php foreach ($choices as $year_course => $info): ?>
                            <option value="<?= e($year_course) ?>" <?= $year_course === $current ? 'selected' : '' ?>>
                                <?= e($year_course) ?> — <?= (int)$info['students'] ?> students<?= $info['semester'] ? ', semester ' . (int)$info['semester'] : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($current): ?><span class="hint">Switching to another class hands <?= e($current) ?> and its pending requests to its next class teacher.</span><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card__header">
            <div><h2>Weekly lecture hours</h2><p>Click the hours in which this class has lectures. Click a day to select or clear the whole day.</p></div>
            <span class="badge badge--info badge--plain"><span id="hoursCount">0</span>&nbsp;hours / week</span>
        </div>
        <div class="card__body">
            <div class="hour-grid-wrap" id="timetableGrid">
                <table class="hour-grid">
                    <thead>
                        <tr>
                            <th class="hour-grid__day">Day</th>
                            <?php for ($hour = 1; $hour <= HOURS_PER_DAY; $hour++): ?>
                                <?= $hour === LUNCH_HOUR ? '<th class="hour-grid__lunch">Lunch</th>' : '<th>' . $hour . '</th>' ?>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (WEEK_DAYS as $day): ?>
                            <tr data-day="<?= e($day) ?>">
                                <th class="hour-grid__day is-clickable"><?= e(ucfirst($day)) ?></th>
                                <?php for ($hour = 1; $hour <= HOURS_PER_DAY; $hour++): ?>
                                    <?php if ($hour === LUNCH_HOUR): ?>
                                        <td class="hour-grid__lunch"></td>
                                    <?php else: ?>
                                        <?php $on = in_array($hour, $selected[$day] ?? [], true); ?>
                                        <td><button type="button" class="slot slot--free <?= $on ? 'is-on' : '' ?>" data-hour="<?= $hour ?>" aria-pressed="<?= $on ? 'true' : 'false' ?>"><?= $hour ?></button></td>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card__footer row">
            <span class="faint"><?= $class ? 'Last saved ' . e(format_datetime($class['submission_date'])) : 'Not saved yet' ?></span>
            <div class="spacer"></div>
            <input type="hidden" name="timetable" id="timetableInput">
            <button type="submit" name="save_class_setup" class="btn btn--primary"><?= icon('check') ?>Save class</button>
        </div>
    </div>
</form>
<?php endif; ?>

<?php include_partial('layout_end', ['scripts' => ['class_setup.js']]); ?>
