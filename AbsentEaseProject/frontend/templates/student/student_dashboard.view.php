<?php
/**
 * Student dashboard.  Controller: pages/student/student_dashboard.php
 * Variables: $rollno, $year_course, $semester, $class_teacher (or null), $counts, $approved_hours, $recent
 */
?>
<?php include_partial('layout_start', ['page_title' => 'Dashboard', 'active_nav' => 'dashboard']); ?>

<div class="page-header">
    <div>
        <h1>Hello, <?= e($rollno) ?></h1>
        <p>Class <b><?= e($year_course) ?></b><?= $semester ? ' · Semester ' . e($semester) : '' ?>
           · Class teacher: <b><?= $class_teacher ? e($class_teacher) : 'not assigned yet' ?></b></p>
    </div>
    <div class="page-header__actions">
        <a class="btn btn--primary" href="<?= e(app_url('pages/student/request_od.php')) ?>"><?= icon('plus') ?>Request OD</a>
    </div>
</div>

<?php if (!$class_teacher): ?>
    <div class="callout callout--warning" style="margin-bottom:22px">
        <span class="callout__icon"><?= icon('alert') ?></span>
        <div><h3>No class teacher yet</h3><p>OD can be requested once your class teacher sets up the class timetable in AbsentEase.</p></div>
    </div>
<?php endif; ?>

<div class="stats">
    <div class="stat stat--pending"><div class="stat__label"><?= icon('clock') ?>Pending</div><div class="stat__value"><?= (int)$counts['pending'] ?></div><div class="stat__hint">Waiting for your class teacher</div></div>
    <div class="stat stat--approved"><div class="stat__label"><?= icon('check') ?>Approved</div><div class="stat__value"><?= (int)$counts['approved'] ?></div><div class="stat__hint">Requests accepted</div></div>
    <div class="stat stat--rejected"><div class="stat__label"><?= icon('x') ?>Rejected</div><div class="stat__value"><?= (int)$counts['rejected'] ?></div><div class="stat__hint">See the teacher's reason</div></div>
    <div class="stat"><div class="stat__label"><?= icon('calendar') ?>OD hours</div><div class="stat__value"><?= (int)$approved_hours ?></div><div class="stat__hint">Lecture hours approved</div></div>
</div>

<div class="card">
    <div class="card__header">
        <div><h2>Recent requests</h2><p>Your latest OD requests and their status</p></div>
        <a class="btn btn--secondary btn--sm" href="<?= e(app_url('pages/student/my_od_requests.php')) ?>">View all</a>
    </div>
    <div class="card__body">
        <?php if (!$recent): ?>
            <?php include_partial('empty_state', ['icon' => 'inbox', 'title' => 'No requests yet',
                'text' => 'When you miss lectures for a college event, request OD here.',
                'action_label' => 'Request OD', 'action_url' => 'pages/student/request_od.php']); ?>
        <?php else: ?>
            <div class="request-list">
                <?php foreach ($recent as $request): ?>
                    <?php include_partial('od_request_card', ['request' => $request, 'viewer' => 'student', 'show_actions' => false]); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_partial('layout_end'); ?>
