<?php
/**
 * SQL for OD REQUESTS.
 *
 *   od_requests       one row per request (event, reason, proof link, status, who reviewed)
 *   od_request_slots  one row per date + hour the request covers
 *
 * Status: pending -> approved | rejected. Only the class teacher of the
 * student's class (first 6 chars of the roll no) can decide.
 */

const OD_PENDING  = 'pending';
const OD_APPROVED = 'approved';
const OD_REJECTED = 'rejected';

/** Saves a request and its slots together (all or nothing). Returns the new id. */
function od_request_create(string $rollno, string $event_name, string $reason, string $proof_link, array $slots): int
{
    return db_transaction(function () use ($rollno, $event_name, $reason, $proof_link, $slots) {
        db_execute(
            'INSERT INTO od_requests (student_rollno, event_name, reason, proof_link, status) VALUES (?, ?, ?, ?, ?)',
            [$rollno, $event_name, $reason === '' ? null : $reason, $proof_link, OD_PENDING]
        );
        $id = (int)db()->insert_id;
        foreach ($slots as $slot) {
            db_execute('INSERT INTO od_request_slots (od_request_id, od_date, hour) VALUES (?, ?, ?)', [$id, $slot['date'], $slot['hour']]);
        }
        return $id;
    });
}

/**
 * Date+hours the student already has in a pending or approved request
 * (so the same hour can't be requested twice).  Returns ['2026-11-02' => [3, 4], ...]
 */
function od_slots_already_requested(string $rollno): array
{
    $rows = db_fetch_all(
        "SELECT s.od_date, s.hour FROM od_request_slots s JOIN od_requests r ON r.id = s.od_request_id
          WHERE r.student_rollno = ? AND r.status IN ('pending', 'approved') AND s.od_date >= ?",
        [$rollno, date('Y-m-d', strtotime('-' . OD_PAST_DAYS_ALLOWED . ' days'))]
    );
    $taken = [];
    foreach ($rows as $row) {
        $taken[$row['od_date']][] = (int)$row['hour'];
    }
    return $taken;
}

/** Adds 'slots' (list of ['od_date','hour']) to each request row. One query for all requests. */
function od_requests_with_slots(array $requests): array
{
    if (!$requests) {
        return [];
    }
    $ids = array_column($requests, 'id');
    $by_request = [];
    foreach (db_fetch_all('SELECT od_request_id, od_date, hour FROM od_request_slots WHERE od_request_id IN (' . db_placeholders($ids) . ') ORDER BY od_date, hour', $ids) as $slot) {
        $by_request[$slot['od_request_id']][] = $slot;
    }
    foreach ($requests as &$request) {
        $request['slots'] = $by_request[$request['id']] ?? [];
    }
    return $requests;
}

// ---------------------------------------------------------------- student side

function od_requests_of_student(string $rollno): array
{
    return od_requests_with_slots(db_fetch_all(
        'SELECT * FROM od_requests WHERE student_rollno = ? ORDER BY submission_date DESC, id DESC',
        [$rollno]
    ));
}

/** Student cancels their own request - only while it is still pending. Slots go by CASCADE. */
function od_request_withdraw(int $id, string $rollno): bool
{
    return db_execute('DELETE FROM od_requests WHERE id = ? AND student_rollno = ? AND status = ?', [$id, $rollno, OD_PENDING]) > 0;
}

function od_status_counts_of_student(string $rollno): array
{
    $counts = [OD_PENDING => 0, OD_APPROVED => 0, OD_REJECTED => 0];
    foreach (db_fetch_all('SELECT status, COUNT(*) AS n FROM od_requests WHERE student_rollno = ? GROUP BY status', [$rollno]) as $row) {
        $counts[$row['status']] = (int)$row['n'];
    }
    return $counts;
}

function od_approved_hours_of_student(string $rollno): int
{
    return (int)db_fetch_value(
        'SELECT COUNT(*) FROM od_request_slots s JOIN od_requests r ON r.id = s.od_request_id WHERE r.student_rollno = ? AND r.status = ?',
        [$rollno, OD_APPROVED]
    );
}

// ---------------------------------------------------------------- class teacher side

/** Requests of one class with the given status(es). */
function od_requests_of_class(string $year_course, array $statuses, int $limit = 200): array
{
    $order = in_array(OD_PENDING, $statuses, true) ? 'r.submission_date ASC' : 'r.reviewed_at DESC, r.id DESC';
    return od_requests_with_slots(db_fetch_all(
        'SELECT r.* FROM od_requests r
          WHERE LEFT(r.student_rollno, 6) = ? AND r.status IN (' . db_placeholders($statuses) . ")
          ORDER BY $order LIMIT $limit",
        array_merge([$year_course], $statuses)
    ));
}

function od_pending_count_of_class(string $year_course): int
{
    return (int)db_fetch_value("SELECT COUNT(*) FROM od_requests WHERE status = 'pending' AND LEFT(student_rollno, 6) = ?", [$year_course]);
}

/** One request, only if it belongs to this class. */
function od_request_of_class(int $id, string $year_course): ?array
{
    $row = db_fetch_one('SELECT * FROM od_requests WHERE id = ? AND LEFT(student_rollno, 6) = ?', [$id, $year_course]);
    return $row ? od_requests_with_slots([$row])[0] : null;
}

/** Approve / reject. Only touches requests that are still pending (no double decisions). */
function od_request_decide(int $id, string $new_status, string $teacher_username, string $remark): bool
{
    return db_execute(
        'UPDATE od_requests SET status = ?, reviewed_by = ?, reviewed_at = NOW(), review_remark = ?
          WHERE id = ? AND status = ?',
        [$new_status, $teacher_username, $remark === '' ? null : $remark, $id, OD_PENDING]
    ) > 0;
}

// ---------------------------------------------------------------- manager side

function od_pending_count_total(): int
{
    return (int)db_fetch_value("SELECT COUNT(*) FROM od_requests WHERE status = 'pending'");
}
