<?php
/**
 * ATTENDANCE - placeholder for the attendance module (not built yet).
 *
 * Called once, right after a class teacher APPROVES an OD request.
 * $request has: id, student_rollno, event_name, slots (each: od_date, hour).
 *
 * When the attendance tables exist, mark every slot of the request as
 * "On Duty" here. Until then it only builds the confirmation text that
 * the teacher sees in the "OD approved" dialog.
 */
function mark_attendance_for_approved_od(array $request): string
{
    // TODO (attendance module): for each $request['slots'] -> mark od_date + hour as OD for student_rollno.

    $parts = [];
    foreach (group_slots_by_date($request['slots']) as $day) {
        $parts[] = $day['label'] . ' (hour ' . implode(', ', $day['hours']) . ')';
    }
    return "Attendance of {$request['student_rollno']} has been updated as On-Duty for \"{$request['event_name']}\": "
        . implode('; ', $parts) . '.';
}
