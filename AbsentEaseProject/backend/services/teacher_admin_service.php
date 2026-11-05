<?php
/**
 * Manager actions on teachers + a teacher setting up their class.
 * Each manager action returns [type, message] for flash().
 */

function add_teacher(string $username, string $email): array
{
    if (!preg_match('/^[A-Za-z0-9_.-]{2,100}$/', $username) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['error', 'Enter a username (letters, numbers, . _ -) and a valid email.'];
    }
    if (teacher_username_or_email_taken($username, $email)) {
        return ['error', 'That username or email is already used by another teacher.'];
    }
    teacher_insert($username, $email);
    return ['success', "Teacher $username added. They can now sign up."];
}

/** Login, class setup and review history follow the new username automatically. */
function update_teacher(string $old_username, string $new_username, string $new_email): array
{
    if (!preg_match('/^[A-Za-z0-9_.-]{2,100}$/', $new_username) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        return ['error', 'Enter a username (letters, numbers, . _ -) and a valid email.'];
    }
    if (teacher_username_or_email_taken($new_username, $new_email, $old_username)) {
        return ['error', 'That username or email is already used by another teacher.'];
    }
    teacher_rename($old_username, $new_username, $new_email);
    return ['success', "Teacher $old_username updated."];
}

function remove_teacher(string $username): array
{
    teacher_delete($username);
    return ['success', "$username deleted. Their class is free for another teacher; its pending OD requests will go to that teacher."];
}

// -------------------------------------------------------------------- class setup (teacher)

/**
 * Classes this teacher may choose: classes with students that have no class teacher,
 * plus the teacher's own class.  ['26csmm' => ['students' => 60, 'semester' => 1], ...]
 */
function choosable_classes(string $teacher_username): array
{
    $result = [];
    foreach (student_classes() as $class) {
        $owner = class_teacher_of_class($class['year_course']);
        if ($owner === null || $owner['teacher_username'] === $teacher_username) {
            $result[$class['year_course']] = [
                'students' => (int)$class['students'],
                'semester' => class_semester($class['year_course']),
            ];
        }
    }
    return $result;
}

/** Semester of a class, worked out from its year (26csmm -> joined 2026). 0 = not in college. */
function class_semester(string $year_course): int
{
    return current_semester(2000 + (int)substr($year_course, 0, 2));
}

function save_class_setup(string $teacher_username, string $year_course, string $timetable): array
{
    if (!isset(choosable_classes($teacher_username)[$year_course])) {
        return ['error', 'Please choose a class from the list.'];
    }
    $timetable = clean_day_hours($timetable);
    if ($timetable === '') {
        return ['error', 'Select at least one lecture hour in the timetable.'];
    }
    class_teacher_save($teacher_username, $year_course, $timetable);
    return ['success', "Saved. You are the class teacher of $year_course; its students' OD requests come to you."];
}
