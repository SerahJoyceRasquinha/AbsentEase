<?php
/**
 * TEACHER -> "OD Requests"  (the ONE review page)
 * The class teacher opens the proof, then approves or rejects each pending request.
 * Approving shows the "attendance updated" dialog (see backend/services/attendance_service.php).
 * Tabs: ?tab=pending (default) | ?tab=reviewed
 * Template: frontend/templates/teacher/od_requests.view.php
 */
require __DIR__ . '/../../backend/bootstrap.php';
$username = require_login('teacher');

if (posted('decision')) {
    [$type, $message, $title, $dialog] = review_od_request($username, (int)post('request_id'), post('decision'), post('remark'));
    flash($message, $type, $title, $dialog,
        $dialog ? 'Note: the attendance module is not connected yet - this confirmation is where it will plug in.' : null);
    reload_page();
}

$class = class_of_teacher($username);
$tab   = query_param('tab') === 'reviewed' ? 'reviewed' : 'pending';

render_page('teacher/od_requests', [
    'class'    => $class,
    'tab'      => $tab,
    'pending'  => $class ? decorate_od_requests(od_requests_of_class($class['year_course'], [OD_PENDING])) : [],
    'reviewed' => $class ? decorate_od_requests(od_requests_of_class($class['year_course'], [OD_APPROVED, OD_REJECTED])) : [],
]);
