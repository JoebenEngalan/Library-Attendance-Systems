<?php
session_start();
error_reporting(0);
include '../includes/config.php';

/* Monthly Attendance Year (independent filter) */
$monthlyYear = isset($_GET['year_monthly']) && is_numeric($_GET['year_monthly'])
    ? (int)$_GET['year_monthly']
    : (int)date('Y');

$months = [
    "January","February","March","April","May","June",
    "July","August","September","October","November","December"
];

/* Initialize all months with zero */
$data = array_fill(0, 12, 0);

$sql = "SELECT MONTH(date_in) AS month, COUNT(*) AS total
        FROM attendance
        WHERE YEAR(date_in) = :year
        GROUP BY MONTH(date_in)";
$query = $dbh->prepare($sql);
$query->bindParam(':year', $monthlyYear, PDO::PARAM_INT);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

foreach ($results as $row) {
    $data[$row->month - 1] = (int)$row->total;
}

/* Always return valid JSON */
echo json_encode([
    'labels' => $months,
    'data'   => $data
]);
exit;
?>

