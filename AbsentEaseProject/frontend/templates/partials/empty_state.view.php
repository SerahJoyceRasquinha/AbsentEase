<?php
/**
 * "Nothing here yet" box.  Variables: $icon, $title, $text, optional $action_label + $action_url
 */
?>
<div class="empty">
    <div class="empty__icon"><?= icon($icon ?? 'inbox') ?></div>
    <h3><?= e($title) ?></h3>
    <p><?= e($text) ?></p>
    <?php if (!empty($action_label)): ?>
        <a class="btn btn--primary" href="<?= e(app_url($action_url)) ?>"><?= icon('plus') ?><?= e($action_label) ?></a>
    <?php endif; ?>
</div>
