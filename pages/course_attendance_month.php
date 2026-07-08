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
            c.abv AS course,
            c.color,
            COUNT(a.attendance_id) AS total
        FROM attendance a
        INNER JOIN coursetbl c ON a.course = c.abv
        WHERE YEAR(a.date_in) = :year
        GROUP BY c.abv, c.color
        ORDER BY c.abv
    ";

    $query = $dbh->prepare($sql);
    $query->bindParam(':year', $year, PDO::PARAM_INT);
} else {
    $sql = "
        SELECT 
            c.abv AS course,
            c.color,
            COUNT(a.attendance_id) AS total
        FROM attendance a
        INNER JOIN coursetbl c ON a.course = c.abv
        WHERE MONTH(a.date_in) = :month
          AND YEAR(a.date_in) = :year
        GROUP BY c.abv, c.color
        ORDER BY c.abv
    ";

    $query = $dbh->prepare($sql);
    $query->bindParam(':month', $month, PDO::PARAM_INT);
    $query->bindParam(':year', $year, PDO::PARAM_INT);
}

$query->execute();

$labels = [];
$data   = [];
$colors = [];

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $labels[] = $row['course'];
    $data[]   = (int)$row['total'];
    $colors[] = $row['color'] ?: '#6c757d';
}

/* Always return valid JSON */
echo json_encode([
    'labels' => $labels,
    'data'   => $data,
    'colors' => $colors
], JSON_NUMERIC_CHECK);
exit;
?>