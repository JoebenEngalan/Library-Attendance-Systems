<?php
session_start();
error_reporting(0);
include "../includes/config.php";
date_default_timezone_set('Asia/Manila');

// ✅ Step 1: Get student ID from session
if (!isset($_SESSION['id_number'])) {
    header("Location: ../index.php");
    exit();
}

$id_number = $_SESSION['id_number'];
unset($_SESSION['id_number']); // clear session to prevent repeat

// ✅ Server-side validation (IMPORTANT)
if (!preg_match('/^[a-zA-Z0-9]+$/', $id_number)) {
    $_SESSION['message'] = "<span style='color:red;'>❌ Invalid Student ID format.</span>";
    header("Location: ../index.php");
    exit();
}

// ✅ Current date and time
$today = date("Y-m-d");
$current_time = date("H:i:s");
$current_time_12 = date("h:i A");

// ✅ Step 2: Verify student exists
$sqlStudent = "SELECT * FROM studtbl WHERE studID = :studID";
$queryStudent = $dbh->prepare($sqlStudent);
$queryStudent->bindParam(':studID', $id_number, PDO::PARAM_STR);
$queryStudent->execute();
$student = $queryStudent->fetch(PDO::FETCH_OBJ);

if (!$student) {
    $_SESSION['message'] = "<span style='color:red;'>❌ Student ID not found in the system. Please register to the Library Administrator.</span>";
    header("Location: ../index.php");
    exit();
}

// ❌ Block non-active students
if ($student->status !== "ACTIVE") {

    $statusMessage = [
        "SUSPENDED" => "⛔ Your account is SUSPENDED. Contact the Library administrator.",
        "INACTIVE" => "⚠️ Your account is INACTIVE. Contact the Library administrator.",
        "GRADUATED" => "🎓 You are already GRADUATED."
    ];

    $msg = $statusMessage[$student->status] ?? "❌ Account not allowed.";

    $_SESSION['message'] = "<span style='color:red;'>$msg</span>";
    header("Location: ../index.php");
    exit();
}


// ✅ Student details
$fullname = htmlspecialchars($student->Lname . ", " . $student->Fname);
$course = htmlspecialchars($student->Course);
$yearlevel = htmlspecialchars($student->YearLevel);

// ✅ Step 3: Check for an open attendance record
$sqlOpen = "SELECT * FROM attendance WHERE id_number = :id_number AND time_out IS NULL ORDER BY attendance_id DESC LIMIT 1";
$queryOpen = $dbh->prepare($sqlOpen);
$queryOpen->bindParam(':id_number', $id_number, PDO::PARAM_STR);
$queryOpen->execute();
$openRecord = $queryOpen->fetch(PDO::FETCH_OBJ);

// ✅ Step 4: Process attendance logic
if ($openRecord) {
    if ($openRecord->date_in != $today) {
        // Auto-close previous day's record
        $autoClose = "UPDATE attendance SET time_out = '23:59:59' WHERE attendance_id = :aid";
        $stmtClose = $dbh->prepare($autoClose);
        $stmtClose->bindParam(':aid', $openRecord->attendance_id, PDO::PARAM_INT);
        $stmtClose->execute();

        // New Time IN record (with fullname, course, yearlevel)
        $insert = "INSERT INTO attendance (id_number, fullname, course, yearlevel, date_in, time_in)
                   VALUES (:id_number, :fullname, :course, :yearlevel, :date_in, :time_in)";
        $stmtIns = $dbh->prepare($insert);
        $stmtIns->bindParam(':id_number', $id_number, PDO::PARAM_STR);
        $stmtIns->bindParam(':fullname', $fullname, PDO::PARAM_STR);
        $stmtIns->bindParam(':course', $course, PDO::PARAM_STR);
        $stmtIns->bindParam(':yearlevel', $yearlevel, PDO::PARAM_STR);
        $stmtIns->bindParam(':date_in', $today, PDO::PARAM_STR);
        $stmtIns->bindParam(':time_in', $current_time, PDO::PARAM_STR);
        $stmtIns->execute();

        $_SESSION['message'] = "⚠️ Previous session auto-closed.<br>✅ New Time IN recorded for <b>$fullname</b> at $current_time_12.";

    } else {
        // Time OUT for same day
        $time_in = $openRecord->time_in;
        $datetime1 = strtotime($time_in);
        $datetime2 = strtotime($current_time);
        $duration_seconds = $datetime2 - $datetime1;

        $hours = floor($duration_seconds / 3600);
        $minutes = floor(($duration_seconds % 3600) / 60);
        $seconds = $duration_seconds % 60;
        $duration = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);

        $update = "UPDATE attendance SET time_out = :time_out WHERE attendance_id = :aid";
        $stmtUp = $dbh->prepare($update);
        $stmtUp->bindParam(':time_out', $current_time, PDO::PARAM_STR);
        $stmtUp->bindParam(':aid', $openRecord->attendance_id, PDO::PARAM_INT);
        $stmtUp->execute();

        $_SESSION['message'] = "✅ Time OUT recorded for <b>$fullname</b> at $current_time_12.<br>🕒 Duration inside: $duration.";
    }

} else {
    // No open record — new Time IN (with fullname, course, yearlevel)
    $insert = "INSERT INTO attendance (id_number, fullname, course, yearlevel, date_in, time_in)
               VALUES (:id_number, :fullname, :course, :yearlevel, :date_in, :time_in)";
    $stmtIns = $dbh->prepare($insert);
    $stmtIns->bindParam(':id_number', $id_number, PDO::PARAM_STR);
    $stmtIns->bindParam(':fullname', $fullname, PDO::PARAM_STR);
    $stmtIns->bindParam(':course', $course, PDO::PARAM_STR);
    $stmtIns->bindParam(':yearlevel', $yearlevel, PDO::PARAM_STR);
    $stmtIns->bindParam(':date_in', $today, PDO::PARAM_STR);
    $stmtIns->bindParam(':time_in', $current_time, PDO::PARAM_STR);
    $stmtIns->execute();

    $_SESSION['message'] = "✅ Time IN recorded for <b>$fullname</b> at $current_time_12.";
}

// ✅ Redirect back to index.php (prevents form resubmission)
header("Location: ../index.php"); 
exit();
?>

