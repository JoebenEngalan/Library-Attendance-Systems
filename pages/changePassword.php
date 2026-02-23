<?php
session_start();
require_once '../includes/config.php';

if (!isset($_POST['changePassword'])) {
    header("Location: ../activitylog.php");
    exit;
}

$user_id     = $_POST['user_id'] ?? '';
$newPassword = $_POST['change_new_password'] ?? '';

if (empty($user_id) || empty($newPassword)) {
    $_SESSION['error'] = "Password cannot be empty.";
    header("Location: ../activitylog.php");
    exit;
}

/* 🔒 STEP 1: Fetch target user's role */
$stmt = $dbh->prepare("SELECT role FROM users WHERE user_id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$targetUser = $stmt->fetch(PDO::FETCH_OBJ);

if (!$targetUser) {
    $_SESSION['error'] = "User not found.";
    header("Location: ../activitylog.php");
    exit;
}

/* 🔒 STEP 2: Protect Main Admin */
if ($targetUser->role === 'Main Admin' && $_SESSION['role'] !== 'Main Admin') {
    $_SESSION['error'] = "You are not allowed to change the Main Admin password.";
    header("Location: ../activitylog.php");
    exit;
}

/* 🔐 STEP 3: Hash password */
$hashed = password_hash($newPassword, PASSWORD_DEFAULT);

/* ✅ STEP 4: Update password */
$sql = "UPDATE users
        SET password = :password,
            updated_at = NOW()
        WHERE user_id = :user_id";

$stmt = $dbh->prepare($sql);
$success = $stmt->execute([
    ':password' => $hashed,
    ':user_id'  => $user_id
]);

/* ✅ STEP 5: Result */
if ($success && $stmt->rowCount() > 0) {
    $_SESSION['success'] = "Password updated successfully.";
} else {
    $_SESSION['error'] = "Failed to update password.";
}

header("Location: ../activitylog.php");
exit;
   
?>