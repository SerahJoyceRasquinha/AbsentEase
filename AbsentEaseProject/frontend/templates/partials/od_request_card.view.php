<?php
/**
 * One OD request as a card. Used on student and teacher pages.
 * Variables:
 *   $request       row from decorate_od_requests() (event_name, days, hour_count, proof_url, status, ...)
 *   $viewer        'student' | 'teacher'
 *   $show_actions  true -> student: "Withdraw" (pending only) / teacher: "Approve" + "Reject" (pending only)
 */
$r = $request;
$days_text = implode('; ', array_map(function ($d) { return $d['label'] . ' (hour ' . implode(', ', $d['hours']) . ')'; }, $r['days']));
?>
<article class="request">
    <div class="request__head">
        <div>
            <div class="request__title"><?= e($r['event_name']) ?></div>
            <div class="request__meta">
                <?php if ($viewer === 'teacher'): ?><span><b class="mono"><?= e($r['student_rollno']) ?></b></span><?php endif; ?>
                <span><?= e($r['hour_count']) ?> hour<?= $r['hour_count'] == 1 ? '' : 's' ?></span>
                <span>Submitted <?= e(format_datetime($r['submission_date'])) ?></span>
            </div>
        </div>
        <div class="spacer"></div>
        <?= status_badge($r['status']) ?>
    </div>

    <div class="request__body">
        <div class="chips">
            <?php foreach ($r['days'] as $day): ?>
                <span class="chip"><?= icon('calendar') ?><?= e($day['label']) ?> <span class="chip__hours">Hr <?= e(implode(', ', $day['hours'])) ?></span></span>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($r['reason']) && $r['reason'] !== $r['event_name']): ?>
            <p class="request__reason"><?= e($r['reason']) ?></p>
        <?php endif; ?>

        <?php if ($r['status'] !== OD_PENDING): ?>
            <div class="request__remark <?= $r['status'] === OD_REJECTED ? 'request__remark--rejected' : '' ?>">
                <b><?= e(ucfirst($r['status'])) ?></b>
                <?php if ($r['reviewed_by']): ?> by <?= e($r['reviewed_by']) ?><?php endif; ?>
                <?php if ($r['reviewed_at']): ?> on <?= e(format_datetime($r['reviewed_at'])) ?><?php endif; ?>
                <?php if ($r['review_remark']): ?><br><span class="muted">“<?= e($r['review_remark']) ?>”</span><?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="request__actions">
        <?php if ($r['proof_url']): ?>
            <a class="btn btn--secondary btn--sm" href="<?= e($r['proof_url']) ?>" target="_blank" rel="noopener"><?= icon('external') ?>View proof</a>
        <?php else: ?>
            <span class="faint">No proof link</span>
        <?php endif; ?>
        <div class="spacer"></div>

        <?php if (!empty($show_actions) && $r['status'] === OD_PENDING): ?>
            <?php if ($viewer === 'teacher'): ?>
                <button type="button" class="btn btn--danger-outline btn--sm" data-dialog-open="rejectDialog"
                        data-fill="<?= e(json_encode(['request_id' => $r['id'], 'student' => $r['student_rollno'], 'summary' => $r['event_name'] . ' — ' . $days_text])) ?>">
                    <?= icon('x') ?>Reject
                </button>
                <button type="button" class="btn btn--success btn--sm" data-dialog-open="approveDialog"
                        data-fill="<?= e(json_encode(['request_id' => $r['id'], 'student' => $r['student_rollno'], 'summary' => $r['event_name'] . ' — ' . $days_text])) ?>">
                    <?= icon('check') ?>Approve
                </button>
            <?php else: ?>
                <form method="post" data-confirm="Withdraw your OD request for “<?= e($r['event_name']) ?>”? Your class teacher will no longer see it.">
                    <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                    <button type="submit" name="withdraw_request" class="btn btn--ghost btn--sm"><?= icon('trash') ?>Withdraw</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</article>
