<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 🔧 10 minutes (testing)
$timeoutSeconds = 10 * 60;

// Fetch session state
$stmt = $dbh->prepare("
    SELECT is_logged_in, last_activity 
    FROM users 
    WHERE user_id = :user_id
");
$stmt->execute([':user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_OBJ);

// ❌ Invalid or force-logged-out
if (!$user || !$user->is_logged_in) {
    session_destroy();
    header("Location: ../index.php?message=Session expired");
    exit;
}

// ⏱ AUTO LOGOUT CHECK
if ($user->last_activity !== null) {
    $inactiveSeconds = time() - strtotime($user->last_activity);

    if ($inactiveSeconds >= $timeoutSeconds) {

        // Log auto logout
        $logSql = "INSERT INTO user_activity
                   (user_id, activity_type, activity_description)
                   VALUES (:user_id, 'AUTO_LOGOUT', 'Auto logout after inactivity')";
        $dbh->prepare($logSql)->execute([':user_id' => $user_id]);

        // Reset login flag
        $dbh->prepare("UPDATE users SET is_logged_in = 0 WHERE user_id = ?")
            ->execute([$user_id]);

        session_destroy();
        header("Location: ../index.php?message=Logged out due to inactivity");
        exit;
    }
}

// ✅ UPDATE ACTIVITY ONLY AFTER CHECK
$dbh->prepare("UPDATE users SET last_activity = NOW() WHERE user_id = ?")
    ->execute([$user_id]);
?>
