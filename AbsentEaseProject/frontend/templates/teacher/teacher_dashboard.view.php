<?php
/**
 * Teacher dashboard.  Controller: pages/teacher/teacher_dashboard.php
 * Variables: $username, $class (null = not set up) and when set up:
 *            $semester, $students, $hours_per_week, $pending_count, $pending, $reviewed_count
 */
?>
<?php include_partial('layout_start', ['page_title' => 'Dashboard', 'active_nav' => 'dashboard']); ?>

<div class="page-header">
    <div>
        <h1>Welcome, <?= e($username) ?></h1>
        <p><?= $class ? 'Class teacher of <b>' . e($class['year_course']) . '</b>' . ($semester ? ' · Semester ' . e($semester) : '') : 'You have not set up a class yet.' ?></p>
    </div>
    <?php if ($class): ?>
        <div class="page-header__actions">
            <a class="btn btn--primary" href="<?= e(app_url('pages/teacher/od_requests.php')) ?>"><?= icon('inbox') ?>Review requests</a>
        </div>
    <?php endif; ?>
</div>

<?php if (!$class): ?>
    <div class="card">
        <?php include_partial('empty_state', ['icon' => 'calendar', 'title' => 'Set up your class',
            'text' => 'Choose the class you are class teacher of and mark its lecture hours. Its students\' OD requests will then come to you.',
            'action_label' => 'Set up class', 'action_url' => 'pages/teacher/class_setup.php']); ?>
    </div>
<?php else: ?>
    <div class="stats">
        <div class="stat stat--pending"><div class="stat__label"><?= icon('clock') ?>Pending requests</div><div class="stat__value"><?= (int)$pending_count ?></div><div class="stat__hint">Waiting for your decision</div></div>
        <div class="stat"><div class="stat__label"><?= icon('check') ?>Reviewed</div><div class="stat__value"><?= (int)$reviewed_count ?></div><div class="stat__hint">Approved or rejected</div></div>
        <div class="stat"><div class="stat__label"><?= icon('users') ?>Students</div><div class="stat__value"><?= (int)$students ?></div><div class="stat__hint">In <?= e($class['year_course']) ?></div></div>
        <div class="stat"><div class="stat__label"><?= icon('calendar') ?>Lecture hours</div><div class="stat__value"><?= (int)$hours_per_week ?></div><div class="stat__hint">Per week in timetable</div></div>
    </div>

    <div class="card">
        <div class="card__header">
            <div><h2>Waiting for you</h2><p>Oldest requests first</p></div>
            <a class="btn btn--secondary btn--sm" href="<?= e(app_url('pages/teacher/od_requests.php')) ?>">Open all</a>
        </div>
        <div class="card__body">
            <?php if (!$pending): ?>
                <?php include_partial('empty_state', ['icon' => 'check', 'title' => 'All caught up', 'text' => 'There are no pending OD requests from your class.']); ?>
            <?php else: ?>
                <div class="request-list">
                    <?php foreach ($pending as $request): ?>
                        <?php include_partial('od_request_card', ['request' => $request, 'viewer' => 'teacher', 'show_actions' => false]); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include_partial('layout_end'); ?>
