<?php
/**
 * OD request rules.
 *
 * Flow:  student requests OD for lecture hours of an event (with a proof link)
 *        -> the CLASS TEACHER of the student's class checks the proof
 *        -> approves (attendance gets marked) or rejects (with a reason).
 */

/** Class (e.g. '26csmm') of a roll number. */
function class_of_student(string $rollno): string
{
    return substr($rollno, 0, 6);
}

/** Earliest / latest date a student may pick, as Y-m-d. */
function od_date_window(): array
{
    return [
        'min' => date('Y-m-d', strtotime('-' . OD_PAST_DAYS_ALLOWED . ' days')),
        'max' => date('Y-m-d', strtotime('+' . OD_FUTURE_DAYS_ALLOWED . ' days')),
    ];
}

/** "drive.google.com/x" -> "https://drive.google.com/x"; null if not a usable web link. */
function normalize_proof_link(string $link): ?string
{
    $link = trim($link);
    if ($link === '') {
        return null;
    }
    if (!preg_match('#^[a-z][a-z0-9+.-]*://#i', $link)) {
        $link = 'https://' . $link;
    }
    return (preg_match('#^https?://#i', $link) && filter_var($link, FILTER_VALIDATE_URL)) ? $link : null;
}

/** "2026-11-02:3,2026-11-02:4" (from the form) -> [['date' => '2026-11-02', 'hour' => 3], ...] or null if malformed. */
function parse_slot_list(string $text): ?array
{
    $slots = [];
    foreach (array_filter(explode(',', $text)) as $part) {
        if (!preg_match('/^(\d{4}-\d{2}-\d{2}):(\d)$/', trim($part), $m)) {
            return null;
        }
        $date = DateTime::createFromFormat('!Y-m-d', $m[1]);
        if (!$date || $date->format('Y-m-d') !== $m[1]) {
            return null;
        }
        $slots[$m[1] . ':' . $m[2]] = ['date' => $m[1], 'hour' => (int)$m[2]];
    }
    ksort($slots);
    return array_values($slots);
}

/** Student submits the form. Returns [type, message]. */
function submit_od_request(string $rollno, string $event_name, string $reason, string $proof_link, string $slot_text): array
{
    $class = class_teacher_of_class(class_of_student($rollno));
    if ($class === null) {
        return ['error', 'Your class does not have a class teacher yet, so OD cannot be requested. Please inform your class teacher.'];
    }
    if ($event_name === '' || mb_strlen($event_name) > 150) {
        return ['error', 'Enter the event name (max 150 characters).'];
    }
    if (mb_strlen($reason) > 500) {
        return ['error', 'Details can be at most 500 characters.'];
    }
    $proof = normalize_proof_link($proof_link);
    if ($proof === null || mb_strlen($proof) > 500) {
        return ['error', 'Enter a valid link to your proof (e.g. a Google Drive link).'];
    }

    $slots = parse_slot_list($slot_text);
    if (!$slots) {
        return ['error', 'Select at least one lecture hour.'];
    }

    $window   = od_date_window();
    $lectures = parse_day_hours($class['timetable']);
    $taken    = od_slots_already_requested($rollno);
    $dates    = array_unique(array_column($slots, 'date'));

    if ((strtotime(max($dates)) - strtotime(min($dates))) / 86400 >= OD_MAX_DAYS_PER_REQUEST) {
        return ['error', 'One request can cover at most ' . OD_MAX_DAYS_PER_REQUEST . ' consecutive days. Make separate requests for separate events.'];
    }
    foreach ($slots as $slot) {
        $label = format_date_label($slot['date']) . ', hour ' . $slot['hour'];
        if ($slot['date'] < $window['min'] || $slot['date'] > $window['max']) {
            return ['error', "$label is outside the allowed dates (" . OD_PAST_DAYS_ALLOWED . ' days back to ' . OD_FUTURE_DAYS_ALLOWED . ' days ahead).'];
        }
        $weekday = strtolower(date('l', strtotime($slot['date'])));
        if (!in_array($slot['hour'], $lectures[$weekday] ?? [], true)) {
            return ['error', "$label is not a lecture hour in your class timetable."];
        }
        if (in_array($slot['hour'], $taken[$slot['date']] ?? [], true)) {
            return ['error', "$label is already in another pending or approved OD request."];
        }
    }

    od_request_create($rollno, $event_name, $reason, $proof, $slots);
    return ['success', 'OD request sent to your class teacher (' . $class['teacher_username'] . ').'];
}

/** Student withdraws a pending request. */
function withdraw_od_request(string $rollno, int $id): array
{
    return od_request_withdraw($id, $rollno)
        ? ['success', 'Request withdrawn.']
        : ['error', 'Only pending requests can be withdrawn.'];
}

/**
 * Class teacher approves / rejects.
 * Returns ['type', 'message', 'title', 'dialog'] for flash().
 */
function review_od_request(string $teacher_username, int $id, string $decision, string $remark): array
{
    $class = class_of_teacher($teacher_username);
    if ($class === null) {
        return ['error', 'Set up your class first.', null, false];
    }
    $request = od_request_of_class($id, $class['year_course']);
    if ($request === null) {
        return ['error', 'That request was not found in your class.', null, false];
    }
    if ($request['status'] !== OD_PENDING) {
        return ['info', 'This request was already ' . $request['status'] . '.', null, false];
    }
    if (mb_strlen($remark) > 500) {
        return ['error', 'The remark can be at most 500 characters.', null, false];
    }

    if ($decision === 'approve') {
        if (!od_request_decide($id, OD_APPROVED, $teacher_username, $remark)) {
            return ['info', 'This request was already decided.', null, false];
        }
        $result = mark_attendance_for_approved_od($request);
        return ['success', $result, 'OD approved', true];
    }

    if ($decision === 'reject') {
        if ($remark === '') {
            return ['error', 'Please give a reason for rejecting, so the student knows what to fix.', null, false];
        }
        od_request_decide($id, OD_REJECTED, $teacher_username, $remark);
        return ['success', "OD request of {$request['student_rollno']} rejected. The student can see your reason.", null, false];
    }
    return ['error', 'Unknown action.', null, false];
}

/** Adds display fields: 'days' (grouped by date), 'hour_count', 'proof_url'. */
function decorate_od_requests(array $requests): array
{
    foreach ($requests as &$request) {
        $request['days']       = group_slots_by_date($request['slots']);
        $request['hour_count'] = count($request['slots']);
        $request['proof_url']  = normalize_proof_link((string)$request['proof_link']);
    }
    return $requests;
}
