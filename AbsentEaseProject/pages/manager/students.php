<?php
/**
 * ADMIN -> "Students"
 * Add students (one roll number or a range), change a roll number, delete,
 * and update semesters / remove graduated students.
 * Deleting or changing a roll number also deletes / moves their login and OD requests (database cascade).
 * Template: frontend/templates/manager/students.view.php
 */
require __DIR__ . '/../../backend/bootstrap.php';
require_login('manager');

if (posted('add_student'))        { flash_result(add_student(post('student_rollno'))); reload_page(); }
if (posted('add_range'))          { flash_result(add_student_range(post('start_rollno'), post('end_rollno'))); reload_page(); }
if (posted('update_student'))     { flash_result(change_student_rollno(post('old_rollno'), post('new_rollno'))); reload_page(); }
if (posted('delete_student'))     { flash_result(delete_student(post('rollno'))); reload_page(); }
if (posted('remove_graduated'))   { flash_result(remove_graduated_students()); reload_page(); }

$students = students_all();
foreach ($students as &$student) {
    $student['semester_now'] = current_semester((int)$student['course_year_start']);
}
unset($student);

render_page('manager/students', ['students' => $students]);
