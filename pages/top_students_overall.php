<?php
session_start();
error_reporting(0);
include "../includes/config.php";

header('Content-Type: application/json');

$month = $_GET['month'] ?? date('n');
$year  = $_GET['year'] ?? date('Y');

if ($month === 'all') {
    $sql = "
        SELECT * FROM (
            SELECT 
                id_number,
                fullname,
                course,
                yearlevel,
                COUNT(*) AS total_visits,
                ROW_NUMBER() OVER (
                    ORDER BY COUNT(*) DESC
                ) AS overall_rank
            FROM attendance
            WHERE YEAR(date_in) = :year
              AND time_out != '23:59:59'
            GROUP BY id_number, fullname, course, yearlevel
        ) ranked
        WHERE overall_rank <= 3
        ORDER BY overall_rank
    ";

    $query = $dbh->prepare($sql);
    $query->bindParam(':year', $year, PDO::PARAM_INT);
} else {
    $sql = "
        SELECT * FROM (
            SELECT 
                id_number,
                fullname,
                course,
                yearlevel,
                COUNT(*) AS total_visits,
                ROW_NUMBER() OVER (
                    ORDER BY COUNT(*) DESC
                ) AS overall_rank
            FROM attendance
            WHERE MONTH(date_in) = :month
              AND YEAR(date_in) = :year
              AND time_out != '23:59:59'
            GROUP BY id_number, fullname, course, yearlevel
        ) ranked
        WHERE overall_rank <= 3
        ORDER BY overall_rank
    ";

    $query = $dbh->prepare($sql);
    $query->bindParam(':month', $month, PDO::PARAM_INT);
    $query->bindParam(':year', $year, PDO::PARAM_INT);
}

$query->execute();
$rows = $query->fetchAll(PDO::FETCH_ASSOC);

/* DataTables expects a "data" array of row arrays/objects */
echo json_encode([
    'data' => $rows
], JSON_NUMERIC_CHECK);
exit;
?>