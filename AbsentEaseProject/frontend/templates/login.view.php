<?php
/**
 * Login / sign-up page (index.php).
 * Variables: $active_role ('student'|'teacher'|'manager'), $active_mode ('login'|'signup')
 * Switching tabs: frontend/js/login_page.js.  Each form posts back to index.php.
 */
$panel_class = function (string $role, string $mode) use ($active_role, $active_mode) {
    return ($role === $active_role && $mode === $active_mode) ? 'auth-form is-active' : 'auth-form';
};
?>
<?php include_partial('html_head', ['page_title' => 'Sign in', 'stylesheets' => ['login.css'], 'body_class' => 'login-page']); ?>

<div class="auth">
    <section class="auth__intro">
        <div class="brand brand--light">
            <span class="brand__mark"><?= icon('shield') ?></span>
            AbsentEase
        </div>
        <div class="auth__pitch">
            <h1>On-Duty attendance,<br>without the paperwork.</h1>
            <p>Students request OD for the lecture hours they miss for college events. Class teachers check the proof and approve it in one click.</p>
            <ul class="auth__points">
                <li><?= icon('calendar') ?>Pick the exact lecture hours from your class timetable</li>
                <li><?= icon('external') ?>Attach proof once — your class teacher reviews it</li>
                <li><?= icon('check') ?>Track every request: pending, approved or rejected</li>
            </ul>
        </div>
        <p class="auth__foot">Kristu Jayanti College · OD Management</p>
    </section>

    <section class="auth__panel">
        <div class="auth__card">
            <h2>Welcome back</h2>
            <p class="muted">Choose who you are to continue.</p>

            <div class="role-tabs" role="tablist">
                <?php foreach (['student' => 'Student', 'teacher' => 'Teacher', 'manager' => 'Admin'] as $role => $label): ?>
                    <button type="button" class="<?= $role === $active_role ? 'is-active' : '' ?>" data-role-tab="<?= $role ?>"><?= e($label) ?></button>
                <?php endforeach; ?>
            </div>

            <!-- ============ STUDENT ============ -->
            <form method="post" class="<?= $panel_class('student', 'login') ?>" data-role="student" data-mode="login">
                <div class="field"><label for="s_roll">Roll number</label>
                    <input class="input" id="s_roll" name="student_login_rollno" placeholder="e.g. 26csmm01" autocomplete="username" required></div>
                <div class="field"><label for="s_pw">Password</label>
                    <input class="input" id="s_pw" type="password" name="student_login_password" autocomplete="current-password" required></div>
                <button type="submit" name="student_login" class="btn btn--primary btn--block">Log in</button>
                <p class="auth-switch">New here? <a href="#" data-mode-switch="signup">Create your student account</a></p>
            </form>

            <form method="post" class="<?= $panel_class('student', 'signup') ?>" data-role="student" data-mode="signup">
                <div class="field"><label for="ss_roll">Roll number</label>
                    <input class="input" id="ss_roll" name="student_rollno" placeholder="e.g. 26csmm01" required></div>
                <div class="field"><label for="ss_email">College email</label>
                    <input class="input" id="ss_email" type="email" name="student_email" placeholder="26csmm01@<?= e(STUDENT_EMAIL_DOMAIN) ?>" required></div>
                <div class="form-row">
                    <div class="field"><label for="ss_pw">Password</label>
                        <input class="input" id="ss_pw" type="password" name="student_password" minlength="6" autocomplete="new-password" required></div>
                    <div class="field"><label for="ss_pw2">Confirm</label>
                        <input class="input" id="ss_pw2" type="password" name="student_confirm_password" minlength="6" autocomplete="new-password" required></div>
                </div>
                <button type="submit" name="student_signup" class="btn btn--primary btn--block">Create account</button>
                <p class="auth-switch">Already registered? <a href="#" data-mode-switch="login">Log in</a></p>
            </form>

            <!-- ============ TEACHER ============ -->
            <form method="post" class="<?= $panel_class('teacher', 'login') ?>" data-role="teacher" data-mode="login">
                <div class="field"><label for="t_user">Username</label>
                    <input class="input" id="t_user" name="login_username" autocomplete="username" required></div>
                <div class="field"><label for="t_pw">Password</label>
                    <input class="input" id="t_pw" type="password" name="login_password" autocomplete="current-password" required></div>
                <button type="submit" name="teacher_login" class="btn btn--primary btn--block">Log in</button>
                <p class="auth-switch">First time? <a href="#" data-mode-switch="signup">Create your teacher account</a></p>
            </form>

            <form method="post" class="<?= $panel_class('teacher', 'signup') ?>" data-role="teacher" data-mode="signup">
                <div class="field"><label for="ts_user">Username</label>
                    <input class="input" id="ts_user" name="signup_username" placeholder="As given by the admin" required></div>
                <div class="field"><label for="ts_email">Email</label>
                    <input class="input" id="ts_email" type="email" name="signup_email" required></div>
                <div class="form-row">
                    <div class="field"><label for="ts_pw">Password</label>
                        <input class="input" id="ts_pw" type="password" name="signup_password" minlength="6" autocomplete="new-password" required></div>
                    <div class="field"><label for="ts_pw2">Confirm</label>
                        <input class="input" id="ts_pw2" type="password" name="signup_confirm_password" minlength="6" autocomplete="new-password" required></div>
                </div>
                <button type="submit" name="teacher_signup" class="btn btn--primary btn--block">Create account</button>
                <p class="auth-switch">Already registered? <a href="#" data-mode-switch="login">Log in</a></p>
            </form>

            <!-- ============ MANAGER (login only) ============ -->
            <form method="post" class="<?= $panel_class('manager', 'login') ?>" data-role="manager" data-mode="login">
                <div class="field"><label for="m_email">Admin email</label>
                    <input class="input" id="m_email" type="email" name="manager_email" autocomplete="username" required></div>
                <div class="field"><label for="m_pw">Password</label>
                    <input class="input" id="m_pw" type="password" name="manager_password" autocomplete="current-password" required></div>
                <button type="submit" name="manager_login" class="btn btn--primary btn--block">Log in</button>
                <p class="auth-switch faint">Admins add teachers and students. There is no admin sign-up.</p>
            </form>
        </div>
    </section>
</div>

<?php include_partial('html_foot', ['scripts' => ['login_page.js']]); ?>
