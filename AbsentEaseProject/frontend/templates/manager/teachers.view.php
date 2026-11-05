<?php
/**
 * Admin: teachers.  Controller: pages/manager/teachers.php
 * Variables: $teachers (username, email, has_account, year_course)
 */
?>
<?php include_partial('layout_start', ['page_title' => 'Teachers', 'active_nav' => 'teachers']); ?>

<div class="page-header">
    <div><h1>Teachers</h1><p>Only teachers listed here can create an account.</p></div>
</div>

<div class="card" style="margin-bottom:20px">
    <div class="card__header"><div><h2>Add a teacher</h2><p>The teacher signs up with exactly this username and email.</p></div></div>
    <div class="card__body">
        <form method="post" class="row">
            <input class="input" style="flex:1;min-width:180px" name="username" placeholder="Username, e.g. anita.k" pattern="[A-Za-z0-9_.\-]{2,100}" required>
            <input class="input" style="flex:1.4;min-width:220px" type="email" name="email" placeholder="Email" required>
            <button type="submit" name="add_teacher" class="btn btn--primary"><?= icon('plus') ?>Add teacher</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card__header">
        <div><h2>All teachers <span class="faint">(<?= count($teachers) ?>)</span></h2></div>
        <label class="search"><?= icon('search') ?><input class="input" placeholder="Search…" data-table-search="teacherTable"></label>
    </div>
    <?php if (!$teachers): ?>
        <?php include_partial('empty_state', ['icon' => 'user', 'title' => 'No teachers yet', 'text' => 'Add a teacher above.']); ?>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table" id="teacherTable">
            <thead><tr><th>Username</th><th>Email</th><th>Class</th><th>Account</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($teachers as $t): ?>
                <tr>
                    <td><b><?= e($t['username']) ?></b></td>
                    <td class="muted"><?= e($t['email']) ?></td>
                    <td><?= $t['year_course'] ? '<span class="mono">' . e($t['year_course']) . '</span>' : '<span class="faint">No class yet</span>' ?></td>
                    <td><?= $t['has_account'] ? '<span class="badge badge--approved">Signed up</span>' : '<span class="badge">Not yet</span>' ?></td>
                    <td class="actions">
                        <button type="button" class="btn btn--ghost btn--sm" data-dialog-open="editTeacherDialog"
                                data-fill="<?= e(json_encode(['old_username' => $t['username'], 'username' => $t['username'], 'email' => $t['email']])) ?>"><?= icon('edit') ?>Edit</button>
                        <form method="post" data-confirm="Delete <?= e($t['username']) ?>? Their login and class setup are deleted. Their class's pending OD requests will wait for the next class teacher." data-confirm-button="Delete">
                            <input type="hidden" name="username" value="<?= e($t['username']) ?>">
                            <button type="submit" name="delete_teacher" class="btn btn--ghost btn--sm"><?= icon('trash') ?>Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<dialog class="dialog" id="editTeacherDialog">
    <form method="post">
        <div class="dialog__body">
            <h2>Edit teacher</h2>
            <p>Their login, class and review history follow the new username.</p>
            <div class="form">
                <div class="field"><label for="tUser">Username</label><input class="input" id="tUser" name="username" pattern="[A-Za-z0-9_.\-]{2,100}" required></div>
                <div class="field"><label for="tEmail">Email</label><input class="input" id="tEmail" type="email" name="email" required></div>
            </div>
            <input type="hidden" name="old_username">
        </div>
        <div class="dialog__footer">
            <button type="button" class="btn btn--secondary" data-dialog-close>Cancel</button>
            <button type="submit" name="update_teacher" class="btn btn--primary">Save</button>
        </div>
    </form>
</dialog>

<?php include_partial('layout_end'); ?>
