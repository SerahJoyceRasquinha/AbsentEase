<?php
/**
 * SQL for TEACHERS.
 * Rename / delete need only ONE statement on verifiable_teacher_details:
 * teacher_credentials and class_teacher_details follow by CASCADE.
 */

/** All teachers + whether they signed up + the class they own. */
function teachers_all(): array
{
    return db_fetch_all(
        'SELECT t.username, t.email, (c.username IS NOT NULL) AS has_account, ct.year_course
           FROM verifiable_teacher_details t
           LEFT JOIN teacher_credentials c    ON c.username = t.username
           LEFT JOIN class_teacher_details ct ON ct.teacher_username = t.username
          ORDER BY t.username'
    );
}

/** Is this username or email already used by another teacher? */
function teacher_username_or_email_taken(string $username, string $email, string $ignore_username = ''): bool
{
    return (int)db_fetch_value(
        'SELECT COUNT(*) FROM verifiable_teacher_details WHERE (username = ? OR email = ?) AND username <> ?',
        [$username, $email, $ignore_username]
    ) > 0;
}

function teacher_insert(string $username, string $email): void
{
    db_execute('INSERT INTO verifiable_teacher_details (username, email) VALUES (?, ?)', [$username, $email]);
}

function teacher_rename(string $old_username, string $new_username, string $new_email): void
{
    db_transaction(function () use ($old_username, $new_username, $new_email) {
        db_execute('UPDATE verifiable_teacher_details SET username = ?, email = ? WHERE username = ?', [$new_username, $new_email, $old_username]);
        // History column (no foreign key, so it survives if the teacher is deleted later)
        db_execute('UPDATE od_requests SET reviewed_by = ? WHERE reviewed_by = ?', [$new_username, $old_username]);
    });
}

/**
 * Login + class setup are removed by CASCADE. The class's pending OD requests are
 * NOT lost: they wait for the next teacher who takes over the class.
 */
function teacher_delete(string $username): void
{
    db_execute('DELETE FROM verifiable_teacher_details WHERE username = ?', [$username]);
}
