<?php
/**
 * TEACHER -> "My Class"
 * Choose the class you are class teacher of and mark its weekly lecture hours.
 * Students of that class can only request OD for these hours, and their requests come to you.
 * Template: frontend/templates/teacher/class_setup.view.php   JS: frontend/js/class_setup.js
 */
require __DIR__ . '/../../backend/bootstrap.php';
$username = require_login('teacher');

if (posted('save_class_setup')) {
    $result = save_class_setup($username, post('year_course'), post('timetable'));
    flash_result($result);
    reload_page();
}

$class = class_of_teacher($username);

render_page('teacher/class_setup', [
    'class'      => $class,
    'choices'    => choosable_classes($username),
    'selected'   => $class ? parse_day_hours($class['timetable']) : [],
]);
