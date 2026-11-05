/**
 * app.js - shared behaviour, loaded on EVERY page.
 *
 *   1. Notifications   messages from the backend's flash() -> corner toasts or a dialog
 *   2. Confirm         <form data-confirm="..."> or <button data-confirm="...">  -> styled "Are you sure?"
 *   3. Dialogs         <button data-dialog-open="dialogId" data-fill='{"field":"value"}'>
 *                      opens <dialog id="dialogId">, copies each value into [name=field] inputs
 *                      and [data-text=field] elements inside it.  [data-dialog-close] closes it.
 *   4. Table search    <input data-table-search="tableId">  hides rows that don't contain the text
 *   5. Mobile menu     <button data-nav-toggle>
 */
(function () {
    'use strict';

    // ---------------------------------------------------------------- 1. notifications
    const toasts = document.getElementById('toasts');

    function showToast(item) {
        const toast = document.createElement('div');
        toast.className = 'toast toast--' + (item.type || 'info');
        toast.setAttribute('role', 'status');
        const text = document.createElement('div');
        text.className = 'toast__text';
        if (item.title) {
            const b = document.createElement('b');
            b.textContent = item.title;
            text.appendChild(b);
            text.appendChild(document.createElement('br'));
        }
        text.appendChild(document.createTextNode(item.message));
        const close = document.createElement('button');
        close.className = 'toast__close';
        close.type = 'button';
        close.setAttribute('aria-label', 'Close');
        close.textContent = '×';
        close.addEventListener('click', function () { toast.remove(); });
        toast.append(text, close);
        toasts.appendChild(toast);

        const lifetime = item.type === 'error' ? 9000 : 5000;
        setTimeout(function () {
            toast.classList.add('is-leaving');
            setTimeout(function () { toast.remove(); }, 300);
        }, lifetime);
    }

    function showMessageDialog(item) {
        const dialog = document.getElementById('messageDialog');
        dialog.querySelector('[data-message-title]').textContent = item.title || 'Done';
        dialog.querySelector('[data-message-text]').textContent = item.message;
        dialog.querySelector('[data-message-note]').textContent = item.note || '';
        dialog.querySelector('[data-message-icon]').className =
            'dialog__icon dialog__icon--' + (item.type === 'error' ? 'error' : 'success');
        dialog.showModal();
    }

    document.querySelector('[data-message-close]').addEventListener('click', function () {
        document.getElementById('messageDialog').close();
    });

    let messages = [];
    try { messages = JSON.parse(toasts.dataset.messages || '[]'); } catch (e) { messages = []; }
    messages.forEach(function (item) {
        if (item.dialog) showMessageDialog(item); else showToast(item);
    });

    // ---------------------------------------------------------------- 2. confirm
    const confirmDialog = document.getElementById('confirmDialog');
    let pending = null;     // { form, submitter }

    document.addEventListener('submit', function (event) {
        const form = event.target;
        const submitter = event.submitter;
        const message = (submitter && submitter.dataset.confirm) || form.dataset.confirm;
        if (!message || form.dataset.confirmed === 'yes') return;

        event.preventDefault();
        pending = { form: form, submitter: submitter };
        confirmDialog.querySelector('[data-confirm-text]').textContent = message;
        confirmDialog.querySelector('[data-confirm-ok]').textContent = form.dataset.confirmButton || 'Yes, continue';
        confirmDialog.showModal();
    }, true);

    confirmDialog.querySelector('[data-confirm-cancel]').addEventListener('click', function () {
        confirmDialog.close();
        pending = null;
    });

    confirmDialog.querySelector('[data-confirm-ok]').addEventListener('click', function () {
        confirmDialog.close();
        if (!pending) return;
        pending.form.dataset.confirmed = 'yes';
        pending.form.requestSubmit(pending.submitter || undefined);   // keeps the clicked button's name
        pending = null;
    });

    // ---------------------------------------------------------------- 3. dialogs
    document.querySelectorAll('[data-dialog-open]').forEach(function (button) {
        button.addEventListener('click', function () {
            const dialog = document.getElementById(button.dataset.dialogOpen);
            const values = button.dataset.fill ? JSON.parse(button.dataset.fill) : {};
            Object.keys(values).forEach(function (key) {
                dialog.querySelectorAll('[name="' + key + '"]').forEach(function (input) { input.value = values[key]; });
                dialog.querySelectorAll('[data-text="' + key + '"]').forEach(function (el) { el.textContent = values[key]; });
            });
            dialog.querySelectorAll('textarea[data-reset]').forEach(function (t) { t.value = ''; });
            dialog.showModal();
            const first = dialog.querySelector('input:not([type=hidden]), textarea, select');
            if (first) first.focus();
        });
    });

    document.querySelectorAll('[data-dialog-close]').forEach(function (button) {
        button.addEventListener('click', function () { button.closest('dialog').close(); });
    });

    // ---------------------------------------------------------------- 4. table search
    document.querySelectorAll('[data-table-search]').forEach(function (input) {
        input.addEventListener('input', function () {
            const text = input.value.trim().toLowerCase();
            document.querySelectorAll('#' + input.dataset.tableSearch + ' tbody tr').forEach(function (row) {
                row.hidden = text !== '' && !row.textContent.toLowerCase().includes(text);
            });
        });
    });

    // ---------------------------------------------------------------- 5. mobile menu
    const toggle = document.querySelector('[data-nav-toggle]');
    if (toggle) {
        toggle.addEventListener('click', function () {
            document.getElementById('mainNav').classList.toggle('is-open');
        });
    }
})();
