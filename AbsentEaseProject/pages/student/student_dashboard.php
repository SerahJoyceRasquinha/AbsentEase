<?php
/**
 * STUDENT DASHBOARD - counts, class teacher, latest requests.
 * Template: frontend/templates/student/student_dashboard.view.php
 */
require __DIR__ . '/../../backend/bootstrap.php';
$rollno = require_login('student');

$class = class_teacher_of_class(class_of_student($rollno));

render_page('student/student_dashboard', [
    'rollno'         => $rollno,
    'year_course'    => class_of_student($rollno),
    'semester'       => class_semester(class_of_student($rollno)),
    'class_teacher'  => $class['teacher_username'] ?? null,
    'counts'         => od_status_counts_of_student($rollno),
    'approved_hours' => od_approved_hours_of_student($rollno),
    'recent'         => decorate_od_requests(array_slice(od_requests_of_student($rollno), 0, 3)),
]);
