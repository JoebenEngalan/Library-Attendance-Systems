<?php
session_start();
require_once "../includes/config.php";

if (isset($_SESSION['user_id'], $_SESSION['username'])) {

    $dbh->prepare("
        INSERT INTO user_activity 
            (user_id, username, activity_type, activity_description)
        VALUES 
            (?, ?, 'LOGOUT', 'User logged out')
    ")->execute([
        $_SESSION['user_id'],
        $_SESSION['username']
    ]);

    $dbh->prepare("
        UPDATE users SET is_logged_in = 0 WHERE user_id = ?
    ")->execute([$_SESSION['user_id']]);
}

$_SESSION = [];
session_destroy();

header("Location: ../index.php");
exit;

?>

