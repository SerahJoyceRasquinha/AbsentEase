<?php
/**
 * Sign-up and login rules for all roles.
 */

const ROLE_LABELS = ['teacher' => 'Teacher', 'student' => 'Student', 'manager' => 'Admin'];

/**
 * Teacher / student sign-up. Returns [bool $success, string $message].
 *   1. passwords match and are at least 6 characters
 *   2. the manager has added this id + email
 *   3. no account exists yet
 */
function sign_up(string $role, string $id, string $email, string $password, string $confirm_password): array
{
    $label = ROLE_LABELS[$role];

    if ($password !== $confirm_password) {
        return [false, 'Passwords do not match.'];
    }
    if (strlen($password) < 6) {
        return [false, 'Password must be at least 6 characters.'];
    }
    if (!account_is_whitelisted($role, $id, $email)) {
        return [false, "$label verification failed: these details were not added by the admin. Please contact the admin."];
    }
    if (account_already_registered($role, $id)) {
        return [false, "An account for $id already exists. Please log in instead."];
    }

    account_create($role, $id, password_hash($password, PASSWORD_BCRYPT));
    return [true, "$label account created. You can log in now."];
}

/** Teacher / student login. Starts the session on success. */
function log_in(string $role, string $id, string $password): bool
{
    $hash = account_password_hash($role, $id);
    if ($hash === null || !password_verify($password, $hash)) {
        return false;
    }
    login_as($role, $id);
    return true;
}

/** Manager login. Starts the session on success. */
function log_in_manager(string $email, string $password): bool
{
    $hash = manager_password_hash($email);
    if ($hash === null || !password_verify($password, $hash)) {
        return false;
    }
    login_as('manager', $email);
    return true;
}
