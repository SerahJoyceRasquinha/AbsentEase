<?php
/**
 * The links in the top bar for each role. Used by frontend/templates/partials/layout_start.view.php
 * 'key' must match the $active_nav a page passes to the layout.
 */
function navigation_for(string $role, string $user): array
{
    switch ($role) {
        case 'student':
            return [
                ['key' => 'dashboard', 'label' => 'Dashboard',   'icon' => 'home', 'page' => 'pages/student/student_dashboard.php'],
                ['key' => 'request',   'label' => 'Request OD',  'icon' => 'plus', 'page' => 'pages/student/request_od.php'],
                ['key' => 'requests',  'label' => 'My Requests', 'icon' => 'list', 'page' => 'pages/student/my_od_requests.php'],
            ];
        case 'teacher':
            $class = class_of_teacher($user);
            return [
                ['key' => 'dashboard', 'label' => 'Dashboard',   'icon' => 'home',  'page' => 'pages/teacher/teacher_dashboard.php'],
                ['key' => 'requests',  'label' => 'OD Requests', 'icon' => 'inbox', 'page' => 'pages/teacher/od_requests.php',
                 'count' => $class ? od_pending_count_of_class($class['year_course']) : 0],
                ['key' => 'class',     'label' => 'My Class',    'icon' => 'calendar', 'page' => 'pages/teacher/class_setup.php'],
            ];
        case 'manager':
            return [
                ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home',   'page' => 'pages/manager/manager_dashboard.php'],
                ['key' => 'students',  'label' => 'Students',  'icon' => 'users',  'page' => 'pages/manager/students.php'],
                ['key' => 'teachers',  'label' => 'Teachers',  'icon' => 'user',   'page' => 'pages/manager/teachers.php'],
                ['key' => 'classes',   'label' => 'Classes',   'icon' => 'layers', 'page' => 'pages/manager/classes.php'],
            ];
    }
    return [];
}
