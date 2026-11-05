<?php
/**
 * Teacher: review OD requests of their class.  Controller: pages/teacher/od_requests.php
 * Variables: $class (or null), $tab ('pending'|'reviewed'), $pending, $reviewed
 * Approve / Reject buttons (in partials/od_request_card) open the two dialogs below.
 */
?>
<?php include_partial('layout_start', ['page_title' => 'OD Requests', 'active_nav' => 'requests']); ?>

<div class="page-header">
    <div><h1>OD requests</h1><p>Open each proof, then approve or reject. Approved hours are marked as On-Duty.</p></div>
</div>

<?php if (!$class): ?>
    <div class="card"><?php include_partial('empty_state', ['icon' => 'calendar', 'title' => 'Set up your class first',
        'text' => 'Requests come from the students of your class.', 'action_label' => 'Set up class', 'action_url' => 'pages/teacher/class_setup.php']); ?></div>
<?php else: ?>
    <nav class="tabs">
        <a class="tab <?= $tab === 'pending' ? 'is-active' : '' ?>" href="?tab=pending">Pending <span class="tab__count"><?= count($pending) ?></span></a>
        <a class="tab <?= $tab === 'reviewed' ? 'is-active' : '' ?>" href="?tab=reviewed">Reviewed <span class="tab__count"><?= count($reviewed) ?></span></a>
    </nav>

    <?php $list = $tab === 'pending' ? $pending : $reviewed; ?>
    <?php if (!$list): ?>
        <div class="card"><?php include_partial('empty_state', $tab === 'pending'
            ? ['icon' => 'check', 'title' => 'All caught up', 'text' => 'No pending OD requests from ' . $class['year_course'] . '.']
            : ['icon' => 'inbox', 'title' => 'Nothing reviewed yet', 'text' => 'Approved and rejected requests will be listed here.']); ?></div>
    <?php else: ?>
        <div class="request-list">
            <?php foreach ($list as $request): ?>
                <?php include_partial('od_request_card', ['request' => $request, 'viewer' => 'teacher', 'show_actions' => true]); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Approve -->
<dialog class="dialog" id="approveDialog">
    <form method="post">
        <div class="dialog__body">
            <div class="dialog__icon dialog__icon--success"><?= icon('check') ?></div>
            <h2>Approve OD for <span data-text="student"></span>?</h2>
            <p data-text="summary"></p>
            <div class="form">
                <div class="field">
                    <label for="approveRemark">Remark <span class="faint">(optional)</span></label>
                    <textarea class="textarea" id="approveRemark" name="remark" maxlength="500" data-reset placeholder="e.g. Certificate verified"></textarea>
                </div>
            </div>
            <input type="hidden" name="request_id">
        </div>
        <div class="dialog__footer">
            <button type="button" class="btn btn--secondary" data-dialog-close>Cancel</button>
            <button type="submit" name="decision" value="approve" class="btn btn--success"><?= icon('check') ?>Approve</button>
        </div>
    </form>
</dialog>

<!-- Reject -->
<dialog class="dialog" id="rejectDialog">
    <form method="post">
        <div class="dialog__body">
            <div class="dialog__icon dialog__icon--error"><?= icon('x') ?></div>
            <h2>Reject OD for <span data-text="student"></span>?</h2>
            <p data-text="summary"></p>
            <div class="form">
                <div class="field">
                    <label class="required" for="rejectRemark">Reason</label>
                    <textarea class="textarea" id="rejectRemark" name="remark" maxlength="500" data-reset required placeholder="Tell the student why, e.g. the proof link does not open"></textarea>
                </div>
            </div>
            <input type="hidden" name="request_id">
        </div>
        <div class="dialog__footer">
            <button type="button" class="btn btn--secondary" data-dialog-close>Cancel</button>
            <button type="submit" name="decision" value="reject" class="btn btn--danger"><?= icon('x') ?>Reject</button>
        </div>
    </form>
</dialog>

<?php include_partial('layout_end'); ?>
