<?php
/**
 * ADMIN (MANAGER) DASHBOARD - totals + classes that still need a class teacher.
 * Template: frontend/templates/manager/manager_dashboard.view.php
 */
require __DIR__ . '/../../backend/bootstrap.php';
require_login('manager');

$students = students_all();
$teachers = teachers_all();
$classes  = classes_overview();

render_page('manager/manager_dashboard', [
    'student_total'     => count($students),
    'student_signed_up' => count(array_filter($students, function ($s) { return $s['has_account']; })),
    'teacher_total'     => count($teachers),
    'teacher_signed_up' => count(array_filter($teachers, function ($t) { return $t['has_account']; })),
    'class_total'       => count($classes),
    'classes_without'   => array_values(array_filter($classes, function ($c) { return $c['teacher_username'] === null; })),
    'pending_total'     => od_pending_count_total(),
]);
