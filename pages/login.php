<?php
session_start();
require_once "../includes/config.php";

if (!isset($_POST['login'])) {
    header("Location: ../index.php");
    exit;
}

$username = trim($_POST['username']);
$password = $_POST['password'];

if ($username === '' || $password === '') {
    $_SESSION['error'] = "Username and password required.";
    header("Location: ../index.php");
    exit;
}

// Fetch user
$stmt = $dbh->prepare("
    SELECT user_id, username, password, fullname, role, is_logged_in
    FROM users 
    WHERE username = :username 
    LIMIT 1
");
$stmt->execute([':username' => $username]);
$user = $stmt->fetch(PDO::FETCH_OBJ);

// ❌ Invalid login
if (!$user || !password_verify($password, $user->password)) {

    $dbh->prepare("
        INSERT INTO user_activity (username, activity_type, activity_description)
        VALUES (?, 'LOGIN_FAILED', 'Invalid login attempt')
    ")->execute([$username]);

    $_SESSION['error'] = "Invalid username or password.";
    header("Location: ../index.php");
    exit;
}

/* 🔁 AUTO-LOGOUT PREVIOUS SESSION */
if ($user->is_logged_in) {

    $dbh->prepare("
        INSERT INTO user_activity 
            (user_id, username, activity_type, activity_description)
        VALUES 
            (?, ?, 'FORCE_LOGOUT', 'Previous session auto-logged out')
    ")->execute([$user->user_id, $user->username]);

    $dbh->prepare("
        UPDATE users SET is_logged_in = 0 WHERE user_id = ?
    ")->execute([$user->user_id]);
}

/* ✅ LOGIN SUCCESS */
$_SESSION['user_id']  = $user->user_id;
$_SESSION['username'] = $user->username;
$_SESSION['fullname'] = $user->fullname;
$_SESSION['role']     = $user->role;

$dbh->prepare("
    UPDATE users 
    SET is_logged_in = 1, last_activity = NOW()
    WHERE user_id = ?
")->execute([$user->user_id]);

$dbh->prepare("
    INSERT INTO user_activity 
        (user_id, username, activity_type, activity_description)
    VALUES 
        (?, ?, 'LOGIN', 'User logged in')
")->execute([$user->user_id, $user->username]);

header("Location: ../admin.php");
exit;

?>

