<?php
/**
 * Loaded at the very top of every page:
 *     require __DIR__ . '/../../backend/bootstrap.php';
 *
 * Starts the session and loads config + all backend code, so pages never
 * have to repeat connection / session setup again.
 */

define('PROJECT_ROOT', dirname(__DIR__));

require_once __DIR__ . '/config/app_config.php';
date_default_timezone_set(APP_TIMEZONE);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Core
require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/core/http_helpers.php';
require_once __DIR__ . '/core/view_renderer.php';
require_once __DIR__ . '/core/session_auth.php';
require_once __DIR__ . '/core/navigation.php';

// Data access (all SQL lives here)
require_once __DIR__ . '/repositories/account_repository.php';
require_once __DIR__ . '/repositories/student_repository.php';
require_once __DIR__ . '/repositories/teacher_repository.php';
require_once __DIR__ . '/repositories/class_teacher_repository.php';
require_once __DIR__ . '/repositories/od_request_repository.php';

// Business rules
require_once __DIR__ . '/services/timetable_service.php';
require_once __DIR__ . '/services/student_semester_service.php';
require_once __DIR__ . '/services/auth_service.php';
require_once __DIR__ . '/services/student_admin_service.php';
require_once __DIR__ . '/services/teacher_admin_service.php';
require_once __DIR__ . '/services/od_request_service.php';
require_once __DIR__ . '/services/attendance_service.php';
