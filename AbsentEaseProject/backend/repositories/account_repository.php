<?php
/**
 * SQL for LOGIN ACCOUNTS of all three roles.
 * A teacher/student can only sign up if the manager has added them (whitelist).
 * Emails live ONLY in the whitelist tables; credentials tables hold id + password hash.
 */

const ACCOUNT_TABLES = [
    'teacher' => [
        'whitelist_table'   => 'verifiable_teacher_details',
        'whitelist_id'      => 'username',
        'whitelist_email'   => 'email',
        'credentials_table' => 'teacher_credentials',
        'credentials_id'    => 'username',
    ],
    'student' => [
        'whitelist_table'   => 'verifiable_student_details',
        'whitelist_id'      => 'student_rollno',
        'whitelist_email'   => 'student_email',
        'credentials_table' => 'student_credentials',
        'credentials_id'    => 'rollno',
    ],
];

/** Has the manager added this person (id + email must both match)? */
function account_is_whitelisted(string $role, string $id, string $email): bool
{
    $t = ACCOUNT_TABLES[$role];
    return (int)db_fetch_value(
        "SELECT COUNT(*) FROM {$t['whitelist_table']} WHERE {$t['whitelist_id']} = ? AND {$t['whitelist_email']} = ?",
        [$id, $email]
    ) > 0;
}

function account_already_registered(string $role, string $id): bool
{
    $t = ACCOUNT_TABLES[$role];
    return (int)db_fetch_value("SELECT COUNT(*) FROM {$t['credentials_table']} WHERE {$t['credentials_id']} = ?", [$id]) > 0;
}

function account_create(string $role, string $id, string $password_hash): void
{
    $t = ACCOUNT_TABLES[$role];
    db_execute("INSERT INTO {$t['credentials_table']} ({$t['credentials_id']}, password) VALUES (?, ?)", [$id, $password_hash]);
}

/** Stored bcrypt hash, or null if no such account. */
function account_password_hash(string $role, string $id): ?string
{
    $t = ACCOUNT_TABLES[$role];
    $hash = db_fetch_value("SELECT password FROM {$t['credentials_table']} WHERE {$t['credentials_id']} = ?", [$id]);
    return $hash === null ? null : (string)$hash;
}

/** Manager bcrypt hash, or null when the email is not a manager. */
function manager_password_hash(string $email): ?string
{
    $hash = db_fetch_value('SELECT password FROM manager_credentials WHERE email = ?', [$email]);
    return $hash === null ? null : (string)$hash;
}
