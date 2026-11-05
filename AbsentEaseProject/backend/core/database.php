<?php
/**
 * Database helpers.
 *
 * Replaces the "new mysqli(...) / prepare / bind_param / execute / close"
 * boilerplate that was repeated in every file. Usage:
 *
 *     db_fetch_all('SELECT * FROM t WHERE a = ?', [$a]);
 *     db_fetch_one('SELECT * FROM t WHERE a = ?', [$a]);     // one row or null
 *     db_fetch_value('SELECT COUNT(*) FROM t');               // first column of first row
 *     db_execute('UPDATE t SET a = ? WHERE b = ?', [$a, $b]); // returns affected rows
 *     db_transaction(function () { ... });                    // all-or-nothing
 *
 * Parameter types (s / i / d) are detected automatically.
 */

/** One shared connection per request (it used to be one per file). */
function db(): mysqli
{
    static $connection = null;

    if ($connection === null) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
            $connection->set_charset('utf8mb4');
        } catch (mysqli_sql_exception $e) {
            http_response_code(500);
            exit(
                '<h2 style="font-family:Segoe UI,sans-serif">Cannot connect to the database</h2>'
                . '<p style="font-family:Segoe UI,sans-serif">Make sure <b>MySQL is running</b> in the XAMPP Control Panel '
                . 'and that the database <b>' . DB_NAME . '</b> exists (START_ABSENTEASE.bat creates it automatically).</p>'
                . '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>'
            );
        }
    }
    return $connection;
}

/** Prepare + bind + execute. Used by all the helpers below. */
function db_run(string $sql, array $params = []): mysqli_stmt
{
    $statement = db()->prepare($sql);

    if ($params) {
        $types = '';
        foreach ($params as $value) {
            $types .= is_int($value) ? 'i' : (is_float($value) ? 'd' : 's');
        }
        $statement->bind_param($types, ...$params);
    }

    $statement->execute();
    return $statement;
}

function db_fetch_all(string $sql, array $params = []): array
{
    $statement = db_run($sql, $params);
    $rows = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    $statement->close();
    return $rows;
}

function db_fetch_one(string $sql, array $params = []): ?array
{
    $statement = db_run($sql, $params);
    $row = $statement->get_result()->fetch_assoc();
    $statement->close();
    return $row ?: null;
}

function db_fetch_value(string $sql, array $params = [])
{
    $row = db_fetch_one($sql, $params);
    return $row === null ? null : reset($row);
}

function db_execute(string $sql, array $params = []): int
{
    $statement = db_run($sql, $params);
    $affected = $statement->affected_rows;
    $statement->close();
    return $affected;
}

/** Runs $work inside a transaction: either every query succeeds or none are saved. */
function db_transaction(callable $work)
{
    db()->begin_transaction();
    try {
        $result = $work();
        db()->commit();
        return $result;
    } catch (Throwable $e) {
        db()->rollback();
        throw $e;
    }
}

/** "?, ?, ?" for an IN (...) list. */
function db_placeholders(array $values): string
{
    return implode(', ', array_fill(0, count($values), '?'));
}
