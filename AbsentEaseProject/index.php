<?php
/**
 * LOGIN / SIGN-UP PAGE  -  http://localhost/AbsentEase/
 * Handles: admin (manager) login, teacher sign-up/login, student sign-up/login.
 * Template: frontend/templates/login.view.php
 * Already logged in -> goes straight to that role's dashboard.
 */
require __DIR__ . '/backend/bootstrap.php';

/** Back to this page, re-opening the same tab (e.g. ?role=teacher&mode=signup). */
function back_to_tab(string $role, string $mode): void
{
    redirect_to('index.php?role=' . $role . '&mode=' . $mode);
}

if (posted('manager_login')) {
    if (log_in_manager(post('manager_email'), $_POST['manager_password'] ?? '')) {
        redirect_to(ROLE_HOME_PAGES['manager']);
    }
    flash('Incorrect admin email or password.', 'error');
    back_to_tab('manager', 'login');
}

if (posted('teacher_signup')) {
    [$ok, $message] = sign_up('teacher', post('signup_username'), post('signup_email'),
        $_POST['signup_password'] ?? '', $_POST['signup_confirm_password'] ?? '');
    flash($message, $ok ? 'success' : 'error');
    back_to_tab('teacher', $ok ? 'login' : 'signup');
}

if (posted('teacher_login')) {
    if (log_in('teacher', post('login_username'), $_POST['login_password'] ?? '')) {
        redirect_to(ROLE_HOME_PAGES['teacher']);
    }
    flash('Incorrect username or password.', 'error');
    back_to_tab('teacher', 'login');
}

if (posted('student_signup')) {
    [$ok, $message] = sign_up('student', normalize_rollno(post('student_rollno')), strtolower(post('student_email')),
        $_POST['student_password'] ?? '', $_POST['student_confirm_password'] ?? '');
    flash($message, $ok ? 'success' : 'error');
    back_to_tab('student', $ok ? 'login' : 'signup');
}

if (posted('student_login')) {
    if (log_in('student', normalize_rollno(post('student_login_rollno')), $_POST['student_login_password'] ?? '')) {
        redirect_to(ROLE_HOME_PAGES['student']);
    }
    flash('Incorrect roll number or password.', 'error');
    back_to_tab('student', 'login');
}

$role = current_role();
if ($role !== null) {
    redirect_to(ROLE_HOME_PAGES[$role]);
}

$active_role = in_array(query_param('role'), ['student', 'teacher', 'manager'], true) ? query_param('role') : 'student';
$active_mode = (query_param('mode') === 'signup' && $active_role !== 'manager') ? 'signup' : 'login';

render_page('login', ['active_role' => $active_role, 'active_mode' => $active_mode]);
