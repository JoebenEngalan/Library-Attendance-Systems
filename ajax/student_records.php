<?php
// JSON feed for the Student Records DataTable (records.php)
session_start();
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

// Same login rule as records.php
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['data' => [], 'error' => 'Unauthorized']);
    exit;
}

include __DIR__ . '/../includes/config.php';   // provides $dbh

$stmt = $dbh->prepare(
    "SELECT id, studID, Lname, Fname, Course, YearLevel, remarks, status, updated_at,
            DATE_FORMAT(updated_at, '%b %d, %Y %h:%i %p') AS updated_fmt
     FROM studtbl"
);
$stmt->execute();

echo json_encode(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)], JSON_INVALID_UTF8_SUBSTITUTE);
