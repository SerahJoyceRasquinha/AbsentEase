<?php
/**
 * ADMIN -> "Classes" - every class, its class teacher, sign-ups and pending OD requests (read-only).
 * Template: frontend/templates/manager/classes.view.php
 */
require __DIR__ . '/../../backend/bootstrap.php';
require_login('manager');

$classes = classes_overview();
foreach ($classes as &$class) {
    $class['semester'] = class_semester($class['year_course']);
    $class['year']     = 2000 + (int)substr($class['year_course'], 0, 2);
    $class['course']   = strtoupper(substr($class['year_course'], 2, 4));
}
unset($class);

render_page('manager/classes', ['classes' => $classes]);
