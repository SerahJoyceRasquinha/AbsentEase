<?php
/**
 * TEACHER DASHBOARD - class summary + oldest pending requests.
 * Template: frontend/templates/teacher/teacher_dashboard.view.php
 */
require __DIR__ . '/../../backend/bootstrap.php';
$username = require_login('teacher');

$class = class_of_teacher($username);
$data  = ['username' => $username, 'class' => $class];

if ($class) {
    $year_course = $class['year_course'];
    $data += [
        'semester'       => class_semester($year_course),
        'students'       => student_count_in_class($year_course),
        'hours_per_week' => timetable_hours_per_week($class['timetable']),
        'pending_count'  => od_pending_count_of_class($year_course),
        'pending'        => decorate_od_requests(od_requests_of_class($year_course, [OD_PENDING], 3)),
        'reviewed_count' => count(od_requests_of_class($year_course, [OD_APPROVED, OD_REJECTED])),
    ];
}

render_page('teacher/teacher_dashboard', $data);
