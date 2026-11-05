<?php
/**
 * Student: all OD requests.  Controller: pages/student/my_od_requests.php
 * Variables: $filter ('all'|'pending'|'approved'|'rejected'), $counts, $requests
 */
$tabs = ['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'];
$total = array_sum($counts);
?>
<?php include_partial('layout_start', ['page_title' => 'My Requests', 'active_nav' => 'requests']); ?>

<div class="page-header">
    <div><h1>My OD requests</h1><p>Pending requests can be withdrawn until your class teacher decides.</p></div>
    <div class="page-header__actions">
        <a class="btn btn--primary" href="<?= e(app_url('pages/student/request_od.php')) ?>"><?= icon('plus') ?>Request OD</a>
    </div>
</div>

<nav class="tabs">
    <?php foreach ($tabs as $key => $label): ?>
        <a class="tab <?= $filter === $key ? 'is-active' : '' ?>" href="?status=<?= e($key) ?>">
            <?= e($label) ?> <span class="tab__count"><?= $key === 'all' ? $total : (int)$counts[$key] ?></span>
        </a>
    <?php endforeach; ?>
</nav>

<?php if (!$requests): ?>
    <div class="card"><?php include_partial('empty_state', ['icon' => 'inbox', 'title' => 'Nothing here',
        'text' => $filter === 'all' ? 'You have not requested OD yet.' : 'No ' . $filter . ' requests.']); ?></div>
<?php else: ?>
    <div class="request-list">
        <?php foreach ($requests as $request): ?>
            <?php include_partial('od_request_card', ['request' => $request, 'viewer' => 'student', 'show_actions' => true]); ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include_partial('layout_end'); ?>
