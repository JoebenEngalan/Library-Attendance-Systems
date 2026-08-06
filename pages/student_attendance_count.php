<?php
session_start();
error_reporting(0);
include "../includes/config.php";
 
header('Content-Type: application/json');
 
$startMonth = $_GET['start_month'] ?? 1;
$endMonth   = $_GET['end_month'] ?? date('n');
$startYear  = $_GET['start_year'] ?? date('Y');
$endYear    = $_GET['end_year'] ?? date('Y');
 
/* Build proper date boundaries so the range works correctly
   even when it crosses a year (e.g. Nov 2025 -> Feb 2026) */
$startDate = sprintf('%04d-%02d-01', $startYear, $startMonth);
$endDate   = date('Y-m-d', strtotime(sprintf('%04d-%02d-01 +1 month', $endYear, $endMonth)));
 
$sql = "
    SELECT 
        id_number,
        fullname,
        course,
        yearlevel,
        COUNT(*) AS total_visits
    FROM attendance
    WHERE time_out IS NOT NULL
      AND time_out != '23:59:59'
      AND date_in >= :start_date
      AND date_in < :end_date
    GROUP BY id_number, fullname, course, yearlevel
    ORDER BY total_visits DESC
";
 
$query = $dbh->prepare($sql);
$query->bindParam(':start_date', $startDate, PDO::PARAM_STR);
$query->bindParam(':end_date', $endDate, PDO::PARAM_STR);
$query->execute();
 
$rows = $query->fetchAll(PDO::FETCH_ASSOC);
 
/* DataTables expects a "data" array */
echo json_encode([
    'data' => $rows
], JSON_NUMERIC_CHECK);
exit;
?>
