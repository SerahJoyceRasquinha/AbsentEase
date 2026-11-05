<?php
/**
 * =====================================================================
 *  AbsentEase - database setup + upgrade
 * =====================================================================
 *  Run automatically by START_ABSENTEASE.bat (using XAMPP's php.exe).
 *  Manual run:   C:\xampp\php\php.exe database\setup_database.php
 *
 *  Safe to run any number of times:
 *   - fresh install  -> creates the database, all tables and the default manager
 *   - older version  -> upgrades the tables IN PLACE and keeps your data
 *   - already current -> changes nothing
 *
 *  Upgrade from version 1 (the first restructure) does:
 *   1. manager passwords: AES (reversible) -> bcrypt hash
 *   2. removes copied data that could go out of sync:
 *        verifiable_student_details.class_teacher_username  (now worked out from the class)
 *        class_teacher_details.semester                      (now worked out from the year)
 *        teacher_credentials.email / student_credentials.email (email lives in the whitelist only)
 *   3. OD requests: one reviewer (the class teacher) instead of two;
 *        "monday[3-4]" + "02-11-2026" text  ->  one od_request_slots row per date+hour
 *        status w/a/r -> pending/approved/rejected
 *   4. deletes orphan rows (logins / requests whose person no longer exists)
 *   5. adds FOREIGN KEYS with CASCADE so MySQL keeps everything consistent from now on
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Run this from the command line (START_ABSENTEASE.bat does it for you).');
}

require __DIR__ . '/../backend/config/app_config.php';
require __DIR__ . '/../backend/services/timetable_service.php';   // parse_day_hours()

$port = isset($argv[1]) && ctype_digit($argv[1]) ? (int)$argv[1] : DB_PORT;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $db = new mysqli('127.0.0.1', DB_USER, DB_PASSWORD, '', $port);
} catch (mysqli_sql_exception $e) {
    fwrite(STDERR, "Cannot connect to MySQL on port $port: " . $e->getMessage() . "\n");
    exit(1);
}
$db->set_charset('utf8mb4');
$db->query('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
$db->select_db(DB_NAME);

$changes = [];

// ---------------------------------------------------------------- helpers
function q(string $sql, array $params = [])
{
    global $db;
    if (!$params) {
        return $db->query($sql);
    }
    $stmt = $db->prepare($sql);
    $stmt->bind_param(str_repeat('s', count($params)), ...array_map('strval', $params));
    $stmt->execute();
    $result = $stmt->get_result();
    return $result === false ? $stmt->affected_rows : $result;
}
function value(string $sql, array $params = [])
{
    $row = q($sql, $params)->fetch_row();
    return $row ? $row[0] : null;
}
function table_exists(string $table): bool
{
    return (int)value('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$table]) > 0;
}
function column_exists(string $table, string $column): bool
{
    return (int)value('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?', [$table, $column]) > 0;
}
function column_type(string $table, string $column): ?string
{
    return value('SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?', [$table, $column]);
}
function constraint_exists(string $table, string $name): bool
{
    return (int)value('SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?', [$table, $name]) > 0;
}
function unique_index_on(string $table, string $column): bool
{
    return (int)value("SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND NON_UNIQUE = 0 AND SEQ_IN_INDEX = 1
                       AND INDEX_NAME IN (SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? GROUP BY INDEX_NAME HAVING COUNT(*) = 1)",
                      [$table, $column, $table]) > 0;
}
function delete_orphans(string $label, string $sql): void
{
    global $changes;
    $n = q($sql);
    if (is_int($n) || $n === true) {
        global $db;
        $n = $db->affected_rows;
    }
    if ($n > 0) {
        $changes[] = "Removed $n orphan $label";
    }
}
function add_fk(string $table, string $name, string $definition): void
{
    global $changes;
    if (!constraint_exists($table, $name)) {
        q("ALTER TABLE `$table` ADD CONSTRAINT `$name` $definition");
        $changes[] = "Added cascade rule $name";
    }
}

// ---------------------------------------------------------------- 0. old od_requests without an id
if (table_exists('od_requests') && !column_exists('od_requests', 'id')) {
    q('ALTER TABLE od_requests ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST');
    $changes[] = 'od_requests: added id column';
}

// ---------------------------------------------------------------- 1. create missing tables
$schema = file_get_contents(__DIR__ . '/absentease_schema.sql');
$schema = preg_replace('/^\s*--.*$/m', '', $schema);
foreach (array_filter(array_map('trim', explode(";\n", $schema))) as $statement) {
    if (preg_match('/^CREATE TABLE IF NOT EXISTS (\w+)/i', $statement, $m)) {
        if (!table_exists($m[1])) {
            q($statement);
            $changes[] = "Created table {$m[1]}";
        }
    }
}

// ---------------------------------------------------------------- 2. manager passwords -> bcrypt
foreach (q('SELECT email, password, AES_DECRYPT(password, ?) AS plain FROM manager_credentials', [MANAGER_AES_KEY])->fetch_all(MYSQLI_ASSOC) as $row) {
    if (strpos((string)$row['password'], '$2y$') !== 0 && $row['plain'] !== null) {
        q('UPDATE manager_credentials SET password = ? WHERE email = ?', [password_hash($row['plain'], PASSWORD_BCRYPT), $row['email']]);
        $changes[] = "Manager {$row['email']}: password re-stored as bcrypt (same password)";
    }
}
if (column_type('manager_credentials', 'password') !== 'varchar') {
    q('ALTER TABLE manager_credentials MODIFY password VARCHAR(255) NOT NULL');
}

// ---------------------------------------------------------------- 3. teachers
delete_orphans('teacher login(s)', 'DELETE FROM teacher_credentials WHERE username NOT IN (SELECT username FROM verifiable_teacher_details)');
if (column_exists('teacher_credentials', 'email')) {
    q('ALTER TABLE teacher_credentials DROP COLUMN email');
    $changes[] = 'teacher_credentials: removed copied email column';
}
add_fk('teacher_credentials', 'fk_teacher_login_whitelist',
    'FOREIGN KEY (username) REFERENCES verifiable_teacher_details (username) ON DELETE CASCADE ON UPDATE CASCADE');

// ---------------------------------------------------------------- 4. students
delete_orphans('student login(s)', 'DELETE FROM student_credentials WHERE rollno NOT IN (SELECT student_rollno FROM verifiable_student_details)');
if (column_exists('student_credentials', 'email')) {
    q('ALTER TABLE student_credentials DROP COLUMN email');
    $changes[] = 'student_credentials: removed copied email column';
}
add_fk('student_credentials', 'fk_student_login_whitelist',
    'FOREIGN KEY (rollno) REFERENCES verifiable_student_details (student_rollno) ON DELETE CASCADE ON UPDATE CASCADE');
if (column_exists('verifiable_student_details', 'class_teacher_username')) {
    q('ALTER TABLE verifiable_student_details DROP COLUMN class_teacher_username');
    $changes[] = 'verifiable_student_details: removed copied class_teacher_username (now worked out from the class)';
}

// ---------------------------------------------------------------- 5. class teachers
delete_orphans('class setup(s)', 'DELETE FROM class_teacher_details WHERE teacher_username NOT IN (SELECT username FROM verifiable_teacher_details)');
if (column_exists('class_teacher_details', 'semester')) {
    q('ALTER TABLE class_teacher_details DROP COLUMN semester');
    $changes[] = 'class_teacher_details: removed stored semester (now worked out from the year)';
}
if (!unique_index_on('class_teacher_details', 'year_course')) {
    // A class may have only one class teacher: keep the most recent setup.
    delete_orphans('duplicate class teacher(s) for the same class',
        'DELETE c1 FROM class_teacher_details c1 JOIN class_teacher_details c2
           ON c1.year_course = c2.year_course AND (c1.submission_date < c2.submission_date
              OR (c1.submission_date = c2.submission_date AND c1.teacher_username > c2.teacher_username))');
    q('ALTER TABLE class_teacher_details ADD UNIQUE KEY uq_one_teacher_per_class (year_course)');
    $changes[] = 'class_teacher_details: one class teacher per class is now enforced';
}
add_fk('class_teacher_details', 'fk_class_teacher_whitelist',
    'FOREIGN KEY (teacher_username) REFERENCES verifiable_teacher_details (username) ON DELETE CASCADE ON UPDATE CASCADE');

// ---------------------------------------------------------------- 6. OD requests v1 -> v2
if (column_exists('od_requests', 'day_and_hours')) {
    if (column_exists('od_requests', 'comment')) {
        q('ALTER TABLE od_requests CHANGE COLUMN comment reason VARCHAR(500) NULL');
    }
    if (column_exists('od_requests', 'link')) {
        q('ALTER TABLE od_requests CHANGE COLUMN link proof_link VARCHAR(500) NULL');
    }
    q('ALTER TABLE od_requests
        ADD COLUMN IF NOT EXISTS event_name    VARCHAR(150) NULL AFTER student_rollno,
        ADD COLUMN IF NOT EXISTS reviewed_by   VARCHAR(100) NULL,
        ADD COLUMN IF NOT EXISTS reviewed_at   DATETIME     NULL,
        ADD COLUMN IF NOT EXISTS review_remark VARCHAR(500) NULL,
        MODIFY status VARCHAR(10) NOT NULL DEFAULT \'pending\'');
    q("UPDATE od_requests SET status = CASE status WHEN 'w' THEN 'pending' WHEN 'a' THEN 'approved' WHEN 'r' THEN 'rejected' ELSE status END");
    q("UPDATE od_requests SET reviewed_by = class_teacher_username WHERE status IN ('approved','rejected') AND reviewed_by IS NULL");
    q("UPDATE od_requests SET event_name = COALESCE(NULLIF(LEFT(reason, 150), ''), 'Event') WHERE event_name IS NULL");
    q("UPDATE od_requests SET proof_link = '' WHERE proof_link IS NULL");

    // text pair -> one slot row per date + hour
    $converted = 0;
    foreach (q('SELECT id, day_and_hours, od_dates FROM od_requests')->fetch_all(MYSQLI_ASSOC) as $row) {
        $dates = array_values(array_filter(explode(',', (string)$row['od_dates'])));
        $i = 0;
        foreach (parse_day_hours((string)$row['day_and_hours']) as $hours) {
            $date = DateTime::createFromFormat('d-m-Y', trim($dates[$i++] ?? ''));
            if (!$date) {
                continue;
            }
            foreach ($hours as $hour) {
                q('INSERT IGNORE INTO od_request_slots (od_request_id, od_date, hour) VALUES (?, ?, ?)', [$row['id'], $date->format('Y-m-d'), $hour]);
            }
        }
        $converted++;
    }
    q('ALTER TABLE od_requests
        DROP COLUMN day_and_hours, DROP COLUMN od_dates,
        DROP COLUMN IF EXISTS faculty_verifier, DROP COLUMN IF EXISTS faculty_verifier_status,
        DROP COLUMN IF EXISTS class_teacher_username,
        MODIFY event_name VARCHAR(150) NOT NULL,
        MODIFY proof_link VARCHAR(500) NOT NULL');
    $changes[] = "od_requests: converted $converted request(s) to the single class-teacher review format";
}
delete_orphans('OD request(s) of students who no longer exist',
    'DELETE FROM od_requests WHERE student_rollno NOT IN (SELECT student_rollno FROM verifiable_student_details)');
delete_orphans('OD request(s) with no dates', 'DELETE FROM od_requests WHERE id NOT IN (SELECT od_request_id FROM od_request_slots)');
add_fk('od_requests', 'fk_od_request_student',
    'FOREIGN KEY (student_rollno) REFERENCES verifiable_student_details (student_rollno) ON DELETE CASCADE ON UPDATE CASCADE');

// ---------------------------------------------------------------- 7. default manager
if ((int)value('SELECT COUNT(*) FROM manager_credentials') === 0) {
    q('INSERT INTO manager_credentials (email, password) VALUES (?, ?)', ['admin@absentease.com', password_hash('admin123', PASSWORD_BCRYPT)]);
    $changes[] = 'Created default manager admin@absentease.com / admin123';
}

// ---------------------------------------------------------------- report
if ($changes) {
    echo "  Database updated:\n";
    foreach ($changes as $change) {
        echo "   - $change\n";
    }
} else {
    echo "  Database is up to date (nothing to change).\n";
}
exit(0);
