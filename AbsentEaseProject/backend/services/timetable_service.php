<?php
/**
 * The class timetable: 6 days x 9 hours, stored as text like
 *     "monday[2-3-4],tuesday[4-6-7]"
 * (the hours in which the class has lectures - only these can be taken as OD).
 */

const WEEK_DAYS       = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
const HOURS_PER_DAY   = 9;
const LUNCH_HOUR      = 5;

/** "monday[2-3-4],tuesday[4]"  ->  ['monday' => [2, 3, 4], 'tuesday' => [4]] */
function parse_day_hours(string $day_hours): array
{
    $result = [];
    preg_match_all('/(\w+)\[([^\]]+)\]/', $day_hours, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        $result[strtolower($match[1])] = array_map('intval', explode('-', $match[2]));
    }
    return $result;
}

/**
 * Validates what the browser sent and rebuilds it in canonical form:
 * known days only (Mon..Sat order), hours 1-9 without lunch, sorted, no duplicates.
 */
function clean_day_hours(string $day_hours): string
{
    $parsed = parse_day_hours($day_hours);
    $parts  = [];
    foreach (WEEK_DAYS as $day) {
        $hours = array_values(array_unique(array_filter($parsed[$day] ?? [], function ($hour) {
            return $hour >= 1 && $hour <= HOURS_PER_DAY && $hour !== LUNCH_HOUR;
        })));
        sort($hours);
        if ($hours) {
            $parts[] = $day . '[' . implode('-', $hours) . ']';
        }
    }
    return implode(',', $parts);
}

/** Total lecture hours per week in a timetable. */
function timetable_hours_per_week(string $timetable): int
{
    return array_sum(array_map('count', parse_day_hours($timetable)));
}

/** "2026-11-02" -> "Mon, 02 Nov 2026" */
function format_date_label(string $ymd): string
{
    return date('D, d M Y', strtotime($ymd));
}

/**
 * Slots (['od_date','hour'] rows) -> grouped for display:
 *   [['date' => '2026-11-02', 'label' => 'Mon, 02 Nov 2026', 'hours' => [3, 4]], ...]
 */
function group_slots_by_date(array $slots): array
{
    $days = [];
    foreach ($slots as $slot) {
        $date = $slot['od_date'];
        $days[$date] = $days[$date] ?? ['date' => $date, 'label' => format_date_label($date), 'hours' => []];
        $days[$date]['hours'][] = (int)$slot['hour'];
    }
    return array_values($days);
}
