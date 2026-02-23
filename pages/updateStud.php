<?php
session_start();
error_reporting(0);
include "includes/config.php"; // adjust path if needed

if (isset($_POST['updateStudent'])) {

    // REQUIRED FIELDS
    $id        = (int) $_POST['id'];
    $studid    = trim($_POST['edstudid']);
    $lname     = trim($_POST['LName']);
    $fname     = trim($_POST['FName']);
    $course    = $_POST['editcourse'];
    $yearlevel = $_POST['edityearlevel'];

    // NEW FIELDS
    $remarks   = trim($_POST['remarks'] ?? null);
    $status    = $_POST['status'] ?? 'ACTIVE';

    // ALLOWED STATUS VALUES (SECURITY)
    $allowedStatus = ['ACTIVE', 'SUSPENDED', 'INACTIVE', 'GRADUATED'];
    if (!in_array($status, $allowedStatus)) {
        $_SESSION['message'] = "❌ Invalid status value.";
        header("Location: records.php");
        exit();
    }

    // UPDATE QUERY
    $sql = " UPDATE studtbl SET 
                    studID     = :studid,
                    Lname      = :lname,
                    Fname      = :fname,
                    Course     = :course,
                    YearLevel  = :yearlevel,
                    remarks    = :remarks,
                    status     = :status,
                    updated_at = NOW()
                    WHERE id = :id";

    $query = $dbh->prepare($sql);

    $query->bindParam(':studid', $studid, PDO::PARAM_STR);
    $query->bindParam(':lname', $lname, PDO::PARAM_STR);
    $query->bindParam(':fname', $fname, PDO::PARAM_STR);
    $query->bindParam(':course', $course, PDO::PARAM_STR);
    $query->bindParam(':yearlevel', $yearlevel, PDO::PARAM_STR);
    $query->bindParam(':remarks', $remarks, PDO::PARAM_STR);
    $query->bindParam(':status', $status, PDO::PARAM_STR);
    $query->bindParam(':id', $id, PDO::PARAM_INT);

    if ($query->execute()) {
        $_SESSION['message'] = "✅ Student updated successfully!";
    } else {
        $_SESSION['message'] = "❌ Update failed. Please try again.";
    }

    // Redirect to refresh table
    header("Location: records.php");
    exit();
}
?>



