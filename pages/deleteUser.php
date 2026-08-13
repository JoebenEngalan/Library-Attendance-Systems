<?php
session_start();
require_once '../includes/config.php';

if (!isset($_POST['deleteUser'])) {
    header("Location: ../activitylog.php");
    exit;
}

$user_id = $_POST['user_id'] ?? '';

if (empty($user_id)) {
    $_SESSION['error'] = "Invalid user.";
    header("Location: ../activitylog.php");
    exit;
}

/* 🔒 STEP 1: Prevent self-delete */
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['error'] = "You cannot delete your own account while logged in.";
    header("Location: ../activitylog.php");
    exit;
}

/* 🔒 STEP 2: Fetch target user's role */
$stmt = $dbh->prepare("SELECT role FROM users WHERE user_id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$targetUser = $stmt->fetch(PDO::FETCH_OBJ);

if (!$targetUser) {
    $_SESSION['error'] = "User not found.";
    header("Location: ../activitylog.php");
    exit;
}

/* 🔒 STEP 3: Protect Main Admin */
if ($targetUser->role === 'Main Admin' && $_SESSION['role'] !== 'Main Admin') {
    $_SESSION['error'] = "You are not allowed to delete a Main Admin account.";
    header("Location: ../activitylog.php");
    exit;
}

/* 🗑 STEP 4: Delete user */
try {
    $stmt = $dbh->prepare("DELETE FROM users WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);

    $_SESSION['success'] = "User deleted successfully.";

} catch (PDOException $e) {

    // SQLSTATE 23000 = integrity constraint violation (e.g. foreign key)
    if ($e->getCode() == '23000') {
        $_SESSION['error'] = "This user cannot be deleted because they have related records (e.g. activity logs) still linked to their account.";
    } else {
        $_SESSION['error'] = "Failed to delete user due to a database error.";
    }
}

header("Location: ../activitylog.php");
exit;
?>