<?php
/**
 * Manager actions on students. Each returns [type, message] for flash().
 */

function add_student(string $rollno): array
{
    $record = student_record_from_rollno($rollno);
    if ($record === null) {
        return ['error', 'Invalid roll number. Use the format YYCCCCNN, e.g. 26csmm01.'];
    }
    if ($record['current_semester'] === 0) {
        return ['error', "{$record['rollno']} is not currently in college (course over or not started)."];
    }
    if (student_exists($record['rollno'])) {
        return ['error', "{$record['rollno']} is already added."];
    }
    student_insert($record);
    return ['success', "Student {$record['rollno']} added."];
}

/** Add every roll number from $start to $end (same class). One summary message. */
function add_student_range(string $start_rollno, string $end_rollno): array
{
    $start = normalize_rollno($start_rollno);
    $end   = normalize_rollno($end_rollno);

    if (!is_valid_rollno($start) || !is_valid_rollno($end)) {
        return ['error', 'Invalid roll number. Use the format YYCCCCNN, e.g. 26csmm01.'];
    }
    if (substr($start, 0, 6) !== substr($end, 0, 6)) {
        return ['error', 'Both roll numbers must belong to the same class (same first 6 characters).'];
    }
    $prefix = substr($start, 0, 6);
    $from   = (int)substr($start, 6, 2);
    $to     = (int)substr($end, 6, 2);
    if ($to < $from) {
        return ['error', 'The end roll number must be after the start roll number.'];
    }

    $added = $skipped = 0;
    $not_in_college = false;
    for ($n = $from; $n <= $to; $n++) {
        $record = student_record_from_rollno($prefix . str_pad((string)$n, 2, '0', STR_PAD_LEFT));
        if ($record['current_semester'] === 0) {
            $not_in_college = true;
            break;                                  // whole class has the same year
        }
        if (student_exists($record['rollno'])) {
            $skipped++;
            continue;
        }
        student_insert($record);
        $added++;
    }

    if ($not_in_college) {
        return ['error', "Class $prefix is not currently in college (course over or not started)."];
    }
    return ['success', "Added $added student(s) to $prefix." . ($skipped ? " $skipped already existed and were skipped." : '')];
}

/** Change a roll number. Login + OD requests follow automatically (foreign key cascade). */
function change_student_rollno(string $old_rollno, string $new_rollno): array
{
    $old_rollno = normalize_rollno($old_rollno);
    $record = student_record_from_rollno($new_rollno);

    if ($record === null) {
        return ['error', 'Invalid roll number. Use the format YYCCCCNN, e.g. 26csmm01.'];
    }
    if ($record['rollno'] === $old_rollno) {
        return ['info', 'The roll number was not changed.'];
    }
    if (student_exists($record['rollno'])) {
        return ['error', "{$record['rollno']} already belongs to another student."];
    }
    if ($record['current_semester'] === 0) {
        return ['error', "{$record['rollno']} would not be in college - not saved."];
    }
    student_replace_rollno($old_rollno, $record);
    return ['success', "Roll number changed from $old_rollno to {$record['rollno']}. Login and OD requests moved with it."];
}

function delete_student(string $rollno): array
{
    students_delete([normalize_rollno($rollno)]);
    return ['success', normalize_rollno($rollno) . ' deleted, together with their login and OD requests.'];
}

/** Recalculate semesters; delete graduated students (their logins + requests go by cascade). */
function remove_graduated_students(): array
{
    $graduated = [];
    foreach (students_all() as $student) {
        $semester = current_semester((int)$student['course_year_start']);
        if ($semester === 0) {
            $graduated[] = $student['student_rollno'];
        } elseif ($semester !== (int)$student['student_current_semester']) {
            student_set_current_semester($student['student_rollno'], $semester);
        }
    }
    students_delete($graduated);
    return $graduated
        ? ['success', 'Semesters updated. Removed ' . count($graduated) . ' graduated student(s).']
        : ['success', 'Semesters updated. No graduated students found.'];
}
