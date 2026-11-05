<?php
/**
 * End of every page: notifications, the shared confirm dialog, scripts.
 * Variables: $scripts (extra files in frontend/js)
 * Messages queued with flash() in the backend reach frontend/js/app.js through data-messages.
 */
?>
<div class="toasts" id="toasts" data-messages="<?= e(json_encode(take_flash_messages())) ?>"></div>

<!-- Shown by app.js for any form/button with data-confirm="..." -->
<dialog class="dialog" id="confirmDialog">
    <div class="dialog__body">
        <div class="dialog__icon dialog__icon--error"><?= icon('alert') ?></div>
        <h2 data-confirm-title>Are you sure?</h2>
        <p data-confirm-text></p>
    </div>
    <div class="dialog__footer">
        <button type="button" class="btn btn--secondary" data-confirm-cancel>Cancel</button>
        <button type="button" class="btn btn--danger" data-confirm-ok>Yes, continue</button>
    </div>
</dialog>

<!-- Shown by app.js for flash(..., dialog: true) messages, e.g. "OD approved" -->
<dialog class="dialog" id="messageDialog">
    <div class="dialog__body">
        <div class="dialog__icon dialog__icon--success" data-message-icon><?= icon('check') ?></div>
        <h2 data-message-title></h2>
        <p data-message-text></p>
        <p class="dialog__note" data-message-note></p>
    </div>
    <div class="dialog__footer">
        <button type="button" class="btn btn--primary" data-message-close>OK</button>
    </div>
</dialog>

<script src="<?= e(js_url('app.js')) ?>"></script>
<?php foreach ($scripts ?? [] as $script): ?>
<script src="<?= e(js_url($script)) ?>"></script>
<?php endforeach; ?>
</body>
</html>
