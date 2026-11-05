<?php
/**
 * SQL for CLASS TEACHER SETUP (class_teacher_details).
 * One teacher per class and one class per teacher (enforced by the table's keys).
 */

/** ['teacher_username', 'year_course', 'timetable', 'submission_date'] or null. */
function class_of_teacher(string $teacher_username): ?array
{
    return db_fetch_one('SELECT * FROM class_teacher_details WHERE teacher_username = ?', [$teacher_username]);
}

/** Class-teacher row that owns a class (e.g. '26csmm'), or null. */
function class_teacher_of_class(string $year_course): ?array
{
    return db_fetch_one('SELECT * FROM class_teacher_details WHERE year_course = ?', [$year_course]);
}

function class_teacher_save(string $teacher_username, string $year_course, string $timetable): void
{
    if (class_of_teacher($teacher_username)) {
        db_execute(
            'UPDATE class_teacher_details SET year_course = ?, timetable = ?, submission_date = CURRENT_TIMESTAMP WHERE teacher_username = ?',
            [$year_course, $timetable, $teacher_username]
        );
    } else {
        db_execute(
            'INSERT INTO class_teacher_details (teacher_username, year_course, timetable) VALUES (?, ?, ?)',
            [$teacher_username, $year_course, $timetable]
        );
    }
}

/** Every class that has students, with its teacher (if any) and OD numbers. For the manager. */
function classes_overview(): array
{
    return db_fetch_all(
        "SELECT LEFT(s.student_rollno, 6) AS year_course,
                COUNT(*) AS students,
                SUM(c.rollno IS NOT NULL) AS students_signed_up,
                MIN(s.course_year_start) AS year_start,
                ct.teacher_username,
                (SELECT COUNT(*) FROM od_requests r
                  WHERE r.status = 'pending' AND LEFT(r.student_rollno, 6) = LEFT(s.student_rollno, 6)) AS pending_requests
           FROM verifiable_student_details s
           LEFT JOIN student_credentials c    ON c.rollno = s.student_rollno
           LEFT JOIN class_teacher_details ct ON ct.year_course = LEFT(s.student_rollno, 6)
          GROUP BY LEFT(s.student_rollno, 6), ct.teacher_username
          ORDER BY year_course"
    );
}
