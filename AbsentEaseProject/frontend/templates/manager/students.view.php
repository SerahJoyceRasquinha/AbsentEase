<?php
/**
 * Admin: students.  Controller: pages/manager/students.php
 * Variables: $students (rows with year_course, has_account, class_teacher, semester_now)
 */
?>
<?php include_partial('layout_start', ['page_title' => 'Students', 'active_nav' => 'students']); ?>

<div class="page-header">
    <div><h1>Students</h1><p>Only students listed here can create an account.</p></div>
    <div class="page-header__actions">
        <form method="post" data-confirm="Recalculate every student's semester and permanently delete students who have finished the course (with their logins and OD requests)?">
            <button type="submit" name="remove_graduated" class="btn btn--secondary"><?= icon('cap') ?>Update semesters</button>
        </form>
    </div>
</div>

<div class="card" style="margin-bottom:20px">
    <div class="card__header">
        <div><h2>Add students</h2><p>Roll number format <span class="mono">YYCCCCNN</span>, e.g. <span class="mono">26csmm01</span>. Email is created as roll@<?= e(STUDENT_EMAIL_DOMAIN) ?>.</p></div>
    </div>
    <div class="card__body">
        <div class="grid-2">
            <form method="post" class="form">
                <div class="field">
                    <label for="one">One student</label>
                    <div class="row">
                        <input class="input" style="flex:1" id="one" name="student_rollno" placeholder="26csmm01" pattern="\d{2}[A-Za-z]{4}\d{2}" required>
                        <button type="submit" name="add_student" class="btn btn--primary"><?= icon('plus') ?>Add</button>
                    </div>
                </div>
            </form>
            <form method="post" class="form">
                <div class="field">
                    <label for="from">A whole range</label>
                    <div class="row">
                        <input class="input" style="flex:1" id="from" name="start_rollno" placeholder="From 26csmm01" pattern="\d{2}[A-Za-z]{4}\d{2}" required>
                        <input class="input" style="flex:1" name="end_rollno" placeholder="To 26csmm60" pattern="\d{2}[A-Za-z]{4}\d{2}" required>
                        <button type="submit" name="add_range" class="btn btn--primary"><?= icon('plus') ?>Add range</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card">
    <div class="card__header">
        <div><h2>All students <span class="faint">(<?= count($students) ?>)</span></h2></div>
        <label class="search"><?= icon('search') ?><input class="input" placeholder="Search roll no, class, teacher…" data-table-search="studentTable"></label>
    </div>
    <?php if (!$students): ?>
        <?php include_partial('empty_state', ['icon' => 'users', 'title' => 'No students yet', 'text' => 'Add roll numbers above.']); ?>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table" id="studentTable">
            <thead><tr><th>Roll no</th><th>Email</th><th>Class</th><th>Semester</th><th>Class teacher</th><th>Account</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($students as $s): ?>
                <tr>
                    <td><b class="mono"><?= e($s['student_rollno']) ?></b></td>
                    <td class="muted"><?= e($s['student_email']) ?></td>
                    <td class="mono"><?= e($s['year_course']) ?></td>
                    <td><?= $s['semester_now'] ? (int)$s['semester_now'] : '<span class="badge badge--rejected">Graduated</span>' ?></td>
                    <td><?= $s['class_teacher'] ? e($s['class_teacher']) : '<span class="faint">Not assigned</span>' ?></td>
                    <td><?= $s['has_account'] ? '<span class="badge badge--approved">Signed up</span>' : '<span class="badge">Not yet</span>' ?></td>
                    <td class="actions">
                        <button type="button" class="btn btn--ghost btn--sm" data-dialog-open="editStudentDialog"
                                data-fill="<?= e(json_encode(['old_rollno' => $s['student_rollno'], 'new_rollno' => $s['student_rollno']])) ?>"><?= icon('edit') ?>Edit</button>
                        <form method="post" data-confirm="Delete <?= e($s['student_rollno']) ?>? Their login and all their OD requests are deleted too." data-confirm-button="Delete">
                            <input type="hidden" name="rollno" value="<?= e($s['student_rollno']) ?>">
                            <button type="submit" name="delete_student" class="btn btn--ghost btn--sm"><?= icon('trash') ?>Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<dialog class="dialog" id="editStudentDialog">
    <form method="post">
        <div class="dialog__body">
            <h2>Change roll number</h2>
            <p>Email, course, year and semester are recalculated. The student's login and OD requests move to the new roll number.</p>
            <div class="form">
                <div class="field">
                    <label for="newRoll">New roll number</label>
                    <input class="input" id="newRoll" name="new_rollno" pattern="\d{2}[A-Za-z]{4}\d{2}" required>
                </div>
            </div>
            <input type="hidden" name="old_rollno">
        </div>
        <div class="dialog__footer">
            <button type="button" class="btn btn--secondary" data-dialog-close>Cancel</button>
            <button type="submit" name="update_student" class="btn btn--primary">Save</button>
        </div>
    </form>
</dialog>

<?php include_partial('layout_end'); ?>
