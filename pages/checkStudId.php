<?php
session_start();
error_reporting(0);
header('Content-Type: application/json');
// Works from the pages/ folder (includes/ one level up) or from the root
$configPath = file_exists(__DIR__ . '/includes/config.php')
    ? __DIR__ . '/includes/config.php'
    : __DIR__ . '/../includes/config.php';
include $configPath;

// TODO: add your login/session check here (e.g. reject if not logged in)

$studid = isset($_POST['studid']) ? trim($_POST['studid']) : '';

if ($studid === '') {
    echo json_encode(['exists' => false]);
    exit;
}

try {
    $q = $dbh->prepare("SELECT COUNT(*) FROM studtbl WHERE studID = :studid");
    $q->bindParam(':studid', $studid, PDO::PARAM_STR);
    $q->execute();

    // Only report whether it exists, never return the student's details
    echo json_encode(['exists' => $q->fetchColumn() > 0]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => true]);
}