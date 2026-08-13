<?php
/**
 * pages/get_today_attendance.php
 *
 * Returns JSON with:
 *   - overview: the "Todays Attendance Overview" stat card numbers
 *   - rows: today's attendance table rows
 *
 * Polled from admin.php to refresh both sections without a full
 * page reload. Same queries/formatting as the original PHP render
 * in admin.php, just returned as JSON instead of printed as HTML.
 */

session_start();
include "../includes/config.php";
require_once "../includes/session_check.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

/* ---------------- Overview stats ---------------- */

function scalarQuery(PDO $dbh, string $sql, string $column) {
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);
    return $result ? (int) $result->$column : 0;
}

$overview = [];

$overview['total_attendance'] = scalarQuery($dbh,
    "SELECT COUNT(attendance_id) AS total_attendance
     FROM attendance
     WHERE YEAR(`date_in`) = YEAR(CURDATE())
     AND MONTH(`date_in`) = MONTH(CURDATE())",
    'total_attendance'
);

$overview['total_timeout'] = scalarQuery($dbh,
    "SELECT COUNT(attendance_id) AS total_timeout
     FROM attendance
     WHERE time_out IS NOT NULL
     AND time_out <> ''
     AND YEAR(`date_in`) = YEAR(CURDATE())
     AND MONTH(`date_in`) = MONTH(CURDATE())",
    'total_timeout'
);

$overview['no_timeout'] = scalarQuery($dbh,
    "SELECT COUNT(attendance_id) AS total_attendance
     FROM attendance
     WHERE (time_out IS NULL OR time_out = '23:59:59')
     AND YEAR(`date_in`) = YEAR(CURDATE())
     AND MONTH(`date_in`) = MONTH(CURDATE())",
    'total_attendance'
);

$courseStatKeys = [
    'bsba_mm'      => 'BSBA MM',
    'bsba_fm'      => 'BSBA FM',
    'bsba_hrm'     => 'BSBA HRM',
    'bsa'          => 'BSA',
    'bsed_ss'      => 'BSEd SS',
    'bsed_english' => 'BSEd English',
    'bsed_math'    => 'BSEd Math',
    'beed'         => 'BEEd',
];

foreach ($courseStatKeys as $key => $courseName) {
    $stmt = $dbh->prepare(
        "SELECT COUNT(DISTINCT id_number) AS timed_in_students
         FROM attendance
         WHERE course = :course
         AND time_in IS NOT NULL
         AND time_in <> ''
         AND DATE(date_in) = CURDATE()"
    );
    $stmt->execute([':course' => $courseName]);
    $result = $stmt->fetch(PDO::FETCH_OBJ);
    $overview[$key] = $result ? (int) $result->timed_in_students : 0;
}

/* ---------------- Attendance table rows ---------------- */

$sql = "SELECT id_number, fullname, course, yearlevel, date_in, time_in, time_out
        FROM attendance
        WHERE DATE(date_in) = CURDATE()
        ORDER BY
            (time_out IS NULL OR time_out = '') DESC,
            time_out ASC,
            date_in ASC";

$query = $dbh->prepare($sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

$rows = [];

foreach ($results as $row) {

    $rows[] = [
        'time_out'  => empty($row->time_out) ? "Still Inside" : date("h:i A", strtotime($row->time_out)),
        'date_in'   => date("F d, Y", strtotime($row->date_in)),
        'time_in'   => date("h:i A", strtotime($row->time_in)),
        'id'        => htmlspecialchars($row->id_number),
        'fullname'  => htmlspecialchars($row->fullname),
        'course'    => htmlspecialchars($row->course),
        'yearlevel' => htmlspecialchars($row->yearlevel),
    ];

}

echo json_encode([
    'overview' => $overview,
    'rows'     => $rows,
]);