<?php
/**
 * Admin: classes overview.  Controller: pages/manager/classes.php
 * Variables: $classes (year_course, year, course, semester, students, students_signed_up, teacher_username, pending_requests)
 */
?>
<?php include_partial('layout_start', ['page_title' => 'Classes', 'active_nav' => 'classes']); ?>

<div class="page-header">
    <div><h1>Classes</h1><p>A class is made of all roll numbers with the same first 6 characters. Teachers claim their class under “My Class”.</p></div>
</div>

<div class="card">
    <?php if (!$classes): ?>
        <?php include_partial('empty_state', ['icon' => 'layers', 'title' => 'No classes yet', 'text' => 'Classes appear when you add students.',
            'action_label' => 'Add students', 'action_url' => 'pages/manager/students.php']); ?>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Class</th><th>Course</th><th>Joined</th><th>Semester</th><th>Students</th><th>Class teacher</th><th>Pending OD</th></tr></thead>
            <tbody>
            <?php foreach ($classes as $c): ?>
                <tr>
                    <td><b class="mono"><?= e($c['year_course']) ?></b></td>
                    <td><?= e($c['course']) ?></td>
                    <td><?= e($c['year']) ?></td>
                    <td><?= $c['semester'] ? (int)$c['semester'] : '<span class="badge badge--rejected">Graduated</span>' ?></td>
                    <td><?= (int)$c['students'] ?> <span class="faint">(<?= (int)$c['students_signed_up'] ?> signed up)</span></td>
                    <td><?= $c['teacher_username'] ? e($c['teacher_username']) : '<span class="badge badge--pending">Not assigned</span>' ?></td>
                    <td><?= $c['pending_requests'] ? '<span class="badge badge--pending">' . (int)$c['pending_requests'] . '</span>' : '<span class="faint">0</span>' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php include_partial('layout_end'); ?>
