<?php
/**
 * Admin dashboard.  Controller: pages/manager/manager_dashboard.php
 * Variables: $student_total, $student_signed_up, $teacher_total, $teacher_signed_up,
 *            $class_total, $classes_without, $pending_total
 */
?>
<?php include_partial('layout_start', ['page_title' => 'Dashboard', 'active_nav' => 'dashboard']); ?>

<div class="page-header">
    <div><h1>Admin dashboard</h1><p>Add teachers and students so they can sign up. Teachers then claim their class.</p></div>
    <div class="page-header__actions">
        <a class="btn btn--secondary" href="<?= e(app_url('pages/manager/teachers.php')) ?>"><?= icon('user') ?>Add teacher</a>
        <a class="btn btn--primary" href="<?= e(app_url('pages/manager/students.php')) ?>"><?= icon('plus') ?>Add students</a>
    </div>
</div>

<div class="stats">
    <div class="stat"><div class="stat__label"><?= icon('users') ?>Students</div><div class="stat__value"><?= (int)$student_total ?></div><div class="stat__hint"><?= (int)$student_signed_up ?> signed up</div></div>
    <div class="stat"><div class="stat__label"><?= icon('user') ?>Teachers</div><div class="stat__value"><?= (int)$teacher_total ?></div><div class="stat__hint"><?= (int)$teacher_signed_up ?> signed up</div></div>
    <div class="stat"><div class="stat__label"><?= icon('layers') ?>Classes</div><div class="stat__value"><?= (int)$class_total ?></div><div class="stat__hint"><?= count($classes_without) ?> without a class teacher</div></div>
    <div class="stat stat--pending"><div class="stat__label"><?= icon('clock') ?>Pending OD</div><div class="stat__value"><?= (int)$pending_total ?></div><div class="stat__hint">Across all classes</div></div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card__header"><div><h2>Classes without a class teacher</h2><p>Students here cannot request OD yet</p></div></div>
        <div class="card__body">
            <?php if (!$classes_without): ?>
                <?php include_partial('empty_state', ['icon' => 'check', 'title' => 'Every class has a class teacher', 'text' => 'Nothing to do here.']); ?>
            <?php else: ?>
                <ul class="list-plain">
                    <?php foreach ($classes_without as $class): ?>
                        <li><span class="badge badge--pending">No teacher</span><b class="mono"><?= e($class['year_course']) ?></b><span class="spacer"></span><span class="muted"><?= (int)$class['students'] ?> students</span></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card__header"><div><h2>How it works</h2></div></div>
        <div class="card__body">
            <ol class="steps">
                <li><b>Add teachers</b> (username + email) and <b>students</b> (roll numbers).</li>
                <li>They <b>sign up</b> on the login page with exactly those details.</li>
                <li>Each teacher opens <b>My Class</b>, picks their class and marks its lecture hours.</li>
                <li>Students <b>request OD</b> for those hours with a proof link.</li>
                <li>The class teacher <b>approves or rejects</b>; approved hours count as On-Duty.</li>
            </ol>
        </div>
    </div>
</div>

<?php include_partial('layout_end'); ?>
