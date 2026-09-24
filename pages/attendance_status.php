<?php
// Lightweight status check used by the kiosk (index.php) to auto-refresh
// itself the moment an admin toggles the attendance form on/off from
// Settings. Returns minimal JSON — no session/auth needed since it
// exposes nothing sensitive.
error_reporting(0);
include "../includes/config.php";

header('Content-Type: application/json');

$disabled = false;
try {
    $stmt = $dbh->query("SELECT setting_value FROM system_settings WHERE setting_key = 'attendance_form_disabled'");
    $disabled = ($stmt->fetchColumn() === '1');
} catch (Exception $e) {
    $disabled = false;
}

echo json_encode(['disabled' => $disabled]);
