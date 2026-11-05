<?php
/**
 * SQL for STUDENTS.
 * A student's CLASS = first 6 characters of the roll no (e.g. 26csmm).
 * Their class teacher is whoever owns that class in class_teacher_details
 * (worked out with a JOIN - never copied into the student row).
 *
 * Deleting a student or changing a roll no needs only ONE statement:
 * the foreign keys cascade it to student_credentials and od_requests.
 */

function student_exists(string $rollno): bool
{
    return (int)db_fetch_value('SELECT COUNT(*) FROM verifiable_student_details WHERE student_rollno = ?', [$rollno]) > 0;
}

/** All students + whether they have signed up + their class teacher. */
function students_all(): array
{
    return db_fetch_all(
        'SELECT s.*, LEFT(s.student_rollno, 6) AS year_course,
                (c.rollno IS NOT NULL) AS has_account,
                ct.teacher_username AS class_teacher
           FROM verifiable_student_details s
           LEFT JOIN student_credentials c    ON c.rollno = s.student_rollno
           LEFT JOIN class_teacher_details ct ON ct.year_course = LEFT(s.student_rollno, 6)
          ORDER BY s.student_rollno'
    );
}

/** $record comes from student_record_from_rollno() in student_semester_service.php */
function student_insert(array $record): void
{
    db_execute(
        'INSERT INTO verifiable_student_details
            (student_rollno, student_course, student_email, course_year_start, course_year_end, semester_duration, student_current_semester)
         VALUES (?, ?, ?, ?, ?, ?, ?)',
        [$record['rollno'], $record['course'], $record['email'], $record['year_start'],
         $record['year_end'], $record['semester_duration'], $record['current_semester']]
    );
}

/** New roll no -> login and OD requests follow automatically (ON UPDATE CASCADE). */
function student_replace_rollno(string $old_rollno, array $new_record): void
{
    db_execute(
        'UPDATE verifiable_student_details
            SET student_rollno = ?, student_course = ?, student_email = ?, course_year_start = ?,
                course_year_end = ?, semester_duration = ?, student_current_semester = ?
          WHERE student_rollno = ?',
        [$new_record['rollno'], $new_record['course'], $new_record['email'], $new_record['year_start'],
         $new_record['year_end'], $new_record['semester_duration'], $new_record['current_semester'], $old_rollno]
    );
}

function student_set_current_semester(string $rollno, int $semester): void
{
    db_execute('UPDATE verifiable_student_details SET student_current_semester = ? WHERE student_rollno = ?', [$semester, $rollno]);
}

/** Login + OD requests + their dates are removed automatically (ON DELETE CASCADE). */
function students_delete(array $rollnos): int
{
    if (!$rollnos) {
        return 0;
    }
    return db_execute('DELETE FROM verifiable_student_details WHERE student_rollno IN (' . db_placeholders($rollnos) . ')', $rollnos);
}

/** Every class that has at least one student: [['year_course' => '26csmm', 'students' => 60], ...] */
function student_classes(): array
{
    return db_fetch_all(
        'SELECT LEFT(student_rollno, 6) AS year_course, COUNT(*) AS students
           FROM verifiable_student_details GROUP BY LEFT(student_rollno, 6) ORDER BY year_course'
    );
}

function student_count_in_class(string $year_course): int
{
    return (int)db_fetch_value('SELECT COUNT(*) FROM verifiable_student_details WHERE LEFT(student_rollno, 6) = ?', [$year_course]);
}
