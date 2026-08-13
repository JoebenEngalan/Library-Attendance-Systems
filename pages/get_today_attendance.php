<?php
/**
 * pages/get_today_attendance.php
 *
 * Returns today's attendance rows as JSON, for the "Todays Attendance
 * Records" table on admin.php to poll and refresh itself without a
 * full page reload.
 *
 * Same query/formatting as the table's original PHP render in
 * admin.php, just returned as JSON instead of printed as HTML.
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

echo json_encode($rows);
