/**
 * Login page: Student / Teacher / Admin tabs and the Log in <-> Sign up switch.
 * Used on: index.php (frontend/templates/login.view.php)
 * Each <form> has data-role and data-mode; exactly one is visible.
 */
(function () {
    let role = document.querySelector('.auth-form.is-active').dataset.role;
    let mode = document.querySelector('.auth-form.is-active').dataset.mode;

    function show() {
        document.querySelectorAll('.auth-form').forEach(function (form) {
            form.classList.toggle('is-active', form.dataset.role === role && form.dataset.mode === mode);
        });
        document.querySelectorAll('[data-role-tab]').forEach(function (tab) {
            tab.classList.toggle('is-active', tab.dataset.roleTab === role);
        });
        const first = document.querySelector('.auth-form.is-active input');
        if (first) first.focus();
    }

    document.querySelectorAll('[data-role-tab]').forEach(function (tab) {
        tab.addEventListener('click', function () {
            role = tab.dataset.roleTab;
            mode = 'login';                     // admin has login only; others start on login
            show();
        });
    });

    document.querySelectorAll('[data-mode-switch]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            mode = link.dataset.modeSwitch;
            show();
        });
    });
})();
