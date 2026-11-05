<?php
/**
 * Login sessions for the three roles: 'manager', 'teacher', 'student'.
 */

const ROLE_SESSION_KEYS = [
    'manager' => 'manager_email',
    'teacher' => 'teacher_username',
    'student' => 'student_rollno',
];

const ROLE_HOME_PAGES = [
    'manager' => 'pages/manager/manager_dashboard.php',
    'teacher' => 'pages/teacher/teacher_dashboard.php',
    'student' => 'pages/student/student_dashboard.php',
];

/** Call after a successful password check. */
function login_as(string $role, string $identifier): void
{
    session_regenerate_id(true);           // prevents session-fixation attacks
    $_SESSION = [];                         // one role at a time
    $_SESSION[ROLE_SESSION_KEYS[$role]] = $identifier;
}

/** Username / roll no / email of the logged-in user for that role, or null. */
function current_user(string $role): ?string
{
    return $_SESSION[ROLE_SESSION_KEYS[$role]] ?? null;
}

/** 'manager' | 'teacher' | 'student' | null */
function current_role(): ?string
{
    foreach (ROLE_SESSION_KEYS as $role => $key) {
        if (isset($_SESSION[$key])) {
            return $role;
        }
    }
    return null;
}

/**
 * Put at the top of every protected page.
 * Not logged in (or session expired) -> back to the login page with a message.
 */
function require_login(string $role): string
{
    $user = current_user($role);
    if ($user === null) {
        flash('Please log in to continue.', 'info');
        redirect_to('index.php');
    }
    return $user;
}

/** Clears the login and starts a fresh session id (so a message can still be shown after logout). */
function logout_everyone(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}
