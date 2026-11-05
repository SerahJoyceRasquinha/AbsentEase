<?php
/**
 * ============================================================
 *  AbsentEase - THE ONLY PLACE TO CHANGE SETTINGS
 * ============================================================
 */

// ---- MySQL (XAMPP defaults: user "root", empty password) ----
const DB_HOST     = 'localhost';
const DB_USER     = 'root';
const DB_PASSWORD = '';
const DB_NAME     = 'absenteasev02complex';
const DB_PORT     = 3306;

// ---- College specific values ----
const STUDENT_EMAIL_DOMAIN  = 'kristujayanti.com';   // student email = <rollno>@<domain>
const COURSE_DURATION_YEARS = 3;                     // 3 years = 6 semesters
const APP_TIMEZONE          = 'Asia/Kolkata';

// ---- OD request rules ----
const OD_PAST_DAYS_ALLOWED    = 15;   // a student may apply up to 15 days AFTER the event (proof is ready by then)
const OD_FUTURE_DAYS_ALLOWED  = 30;   // ...or up to 30 days BEFORE it
const OD_MAX_DAYS_PER_REQUEST = 7;    // one request covers at most 7 consecutive days

// ---- Only used by database/setup_database.php to convert old AES manager passwords to bcrypt ----
const MANAGER_AES_KEY = 'my_secret_key';
