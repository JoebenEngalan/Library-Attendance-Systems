<?php
// Lightweight status check used by the kiosk (index.php) to auto-refresh
// itself the moment an admin toggles the attendance form on/off from
// Settings. Returns minimal JSON — no session/auth needed since it
// exposes nothing sensitive.
error_reporting(0);
include "../includes/config.php";

header('Content-Type: application/json');

$disabled = false;
$announcement = '';
try {
    $stmt = $dbh->query("SELECT setting_key, setting_value FROM system_settings
                          WHERE setting_key IN ('attendance_form_disabled', 'attendance_announcement')");
    $settings = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    $disabled     = ($settings['attendance_form_disabled'] ?? '0') === '1';
    $announcement = $settings['attendance_announcement'] ?? '';
} catch (Exception $e) {
    $disabled = false;
    $announcement = '';
}

echo json_encode(['disabled' => $disabled, 'announcement' => $announcement]);