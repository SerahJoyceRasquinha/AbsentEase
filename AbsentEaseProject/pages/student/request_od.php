<?php
/**
 * STUDENT -> "Request OD"
 * Pick the event dates, tick the lecture hours missed, add the proof link.
 * The request goes to the class teacher of the student's class.
 * Template: frontend/templates/student/request_od.view.php   JS: frontend/js/od_request_form.js
 */
require __DIR__ . '/../../backend/bootstrap.php';
$rollno = require_login('student');

if (posted('submit_od_request')) {
    $result = submit_od_request($rollno, post('event_name'), post('reason'), post('proof_link'), post('slots'));
    flash_result($result);
    if ($result[0] === 'success') {
        redirect_to('pages/student/my_od_requests.php');
    }
    reload_page();
}

$class = class_teacher_of_class(class_of_student($rollno));

render_page('student/request_od', [
    'class_teacher'  => $class['teacher_username'] ?? null,
    'lecture_hours'  => $class ? parse_day_hours($class['timetable']) : [],
    'taken_slots'    => od_slots_already_requested($rollno),
    'date_window'    => od_date_window(),
]);
