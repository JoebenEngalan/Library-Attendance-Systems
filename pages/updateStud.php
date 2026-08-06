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

    try {
        $dbh->beginTransaction();

        /* ---- 1. Get the CURRENT studID before overwriting it ----
           We need this to find the matching rows in attendance,
           since attendance.id_number has no foreign key link to studtbl. */
        $lookup = $dbh->prepare("SELECT studID FROM studtbl WHERE id = :id");
        $lookup->bindParam(':id', $id, PDO::PARAM_INT);
        $lookup->execute();
        $oldStudID = $lookup->fetchColumn();

        /* ---- 2. Update studtbl (same as before) ---- */
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
        $query->execute();

        /* ---- 3. Sync attendance.id_number and attendance.fullname
                   for every record tied to this student's OLD studID ---- */
        if ($oldStudID !== false && $oldStudID !== null) {

            $newFullname = $lname . ', ' . $fname;

            $syncSql = "
                UPDATE attendance 
                SET id_number = :new_studid,
                    fullname  = :new_fullname
                WHERE id_number = :old_studid
            ";

            $syncQuery = $dbh->prepare($syncSql);
            $syncQuery->bindParam(':new_studid', $studid, PDO::PARAM_STR);
            $syncQuery->bindParam(':new_fullname', $newFullname, PDO::PARAM_STR);
            $syncQuery->bindParam(':old_studid', $oldStudID, PDO::PARAM_STR);
            $syncQuery->execute();
        }

        $dbh->commit();
        $_SESSION['message'] = "✅ Student updated successfully! (Attendance records synced)";

    } catch (Exception $e) {
        $dbh->rollBack();
        $_SESSION['message'] = "❌ Update failed. Please try again.";
    }

    // Redirect to refresh table
    header("Location: records.php");
    exit();
}
?>