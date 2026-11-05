<?php
/**
 * STUDENT -> "My Requests" - every OD request with its status; pending ones can be withdrawn.
 * Template: frontend/templates/student/my_od_requests.view.php
 */
require __DIR__ . '/../../backend/bootstrap.php';
$rollno = require_login('student');

if (posted('withdraw_request')) {
    flash_result(withdraw_od_request($rollno, (int)post('request_id')));
    reload_page();
}

$filter   = in_array(query_param('status'), [OD_PENDING, OD_APPROVED, OD_REJECTED], true) ? query_param('status') : 'all';
$requests = decorate_od_requests(od_requests_of_student($rollno));

render_page('student/my_od_requests', [
    'filter'   => $filter,
    'counts'   => od_status_counts_of_student($rollno),
    'requests' => $filter === 'all' ? $requests : array_values(array_filter($requests, function ($r) use ($filter) {
        return $r['status'] === $filter;
    })),
]);
