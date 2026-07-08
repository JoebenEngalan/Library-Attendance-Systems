<?php
session_start();
error_reporting(0);
include "../includes/config.php";

header('Content-Type: application/json');

$month = $_GET['month'] ?? date('n');
$year  = $_GET['year'] ?? date('Y');

if ($month === 'all') {
    $sql = "
        SELECT 
            HOUR(time_in) AS hour_of_day,
            COUNT(*) AS total
        FROM attendance 
        WHERE time_in IS NOT NULL
          AND YEAR(date_in) = :year
        GROUP BY HOUR(time_in)
        ORDER BY hour_of_day
    ";

    $query = $dbh->prepare($sql);
    $query->bindParam(':year', $year, PDO::PARAM_INT);
} else {
    $sql = "
        SELECT 
            HOUR(time_in) AS hour_of_day,
            COUNT(*) AS total
        FROM attendance 
        WHERE time_in IS NOT NULL
          AND MONTH(date_in) = :month
          AND YEAR(date_in) = :year
        GROUP BY HOUR(time_in)
        ORDER BY hour_of_day
    ";

    $query = $dbh->prepare($sql);
    $query->bindParam(':month', $month, PDO::PARAM_INT);
    $query->bindParam(':year', $year, PDO::PARAM_INT);
}

$query->execute();
$results = $query->fetchAll(PDO::FETCH_ASSOC);

/* Build a full 0-23 hour map so missing hours show as 0 */
$hourlyData = array_fill(0, 24, 0);
foreach ($results as $row) {
    $hourlyData[(int)$row['hour_of_day']] = (int)$row['total'];
}

$labels = [];
$data   = [];

foreach ($hourlyData as $hour => $total) {
    $labels[] = date('g A', strtotime("$hour:00"));
    $data[]   = $total;
}

/* Always return valid JSON */
echo json_encode([
    'labels' => $labels,
    'data'   => $data
], JSON_NUMERIC_CHECK);
exit;
?>