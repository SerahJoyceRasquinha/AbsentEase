<?php
/**
 * Roll number -> course, email, years, semester windows, current semester.
 *
 * Roll number format: YY + 4-letter course + NN   e.g. 23bcam07
 *   - joined in June 20YY, 6 semesters over 3 years
 *   - odd semesters  June -> December
 *   - even semesters January -> May
 *
 * Before: this ~45-line calculation was copy-pasted 4 times (add one student,
 * add range, update student, refresh) and the "Refresh" copy compared exact
 * timestamps, so clicking Refresh any time in December or May (after the 1st)
 * deleted EVERY student as "graduated". Now there is one version that
 * compares by month.
 */

const ROLLNO_PATTERN = '/^(\d{2})([a-z]{4})(\d{2})$/';

function normalize_rollno(string $rollno): string
{
    return strtolower(trim($rollno));
}

function is_valid_rollno(string $rollno): bool
{
    return (bool)preg_match(ROLLNO_PATTERN, normalize_rollno($rollno));
}

/** [ ['number'=>1, 'start'=>'2023-06', 'end'=>'2023-12', 'label'=>'1:june2023-december2023'], ... ] */
function semester_windows(int $year_start): array
{
    $windows = [];
    for ($i = 0; $i < COURSE_DURATION_YEARS * 2; $i++) {
        $number = $i + 1;
        $year   = $year_start + intdiv($i + 1, 2);      // sem 1: start year, sem 2&3: +1, sem 4&5: +2 ...
        if ($i % 2 === 0) {
            $windows[] = ['number' => $number, 'start' => "$year-06", 'end' => "$year-12", 'label' => "{$number}:june{$year}-december{$year}"];
        } else {
            $windows[] = ['number' => $number, 'start' => "$year-01", 'end' => "$year-05", 'label' => "{$number}:january{$year}-may{$year}"];
        }
    }
    return $windows;
}

/** Semester the batch is in today, or 0 if the course is over / hasn't started. */
function current_semester(int $year_start, ?DateTimeInterface $today = null): int
{
    $this_month = ($today ?? new DateTimeImmutable())->format('Y-m');
    foreach (semester_windows($year_start) as $window) {
        if ($this_month >= $window['start'] && $this_month <= $window['end']) {   // 'YYYY-MM' strings compare correctly
            return $window['number'];
        }
    }
    return 0;
}

/**
 * Everything the database stores about a student, derived from the roll no.
 * Returns null for an invalid roll number.
 */
function student_record_from_rollno(string $rollno): ?array
{
    $rollno = normalize_rollno($rollno);
    if (!preg_match(ROLLNO_PATTERN, $rollno, $parts)) {
        return null;
    }

    $year_start = 2000 + (int)$parts[1];
    return [
        'rollno'            => $rollno,
        'course'            => $parts[2],
        'email'             => $rollno . '@' . STUDENT_EMAIL_DOMAIN,
        'year_start'        => $year_start,
        'year_end'          => $year_start + COURSE_DURATION_YEARS,
        'semester_duration' => implode(', ', array_column(semester_windows($year_start), 'label')),
        'current_semester'  => current_semester($year_start),
    ];
}
