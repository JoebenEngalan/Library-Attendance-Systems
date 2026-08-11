<!DOCTYPE html>
<?php
session_start();
error_reporting(0);
ini_set('display_errors', 1);
include "includes/config.php";
require_once "includes/session_check.php";

// Access control: Only Admin and Main Admin
$allowedRoles = ['Admin', 'Main Admin'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles, true)) {
    echo "<script>
        alert('Access denied.');
        window.location.href = 'admin.php';
    </script>";
    exit;
}

$message = "";
$messageType = "";

// Manual Time Out Process
if (isset($_POST['manual_timeout'])){

$id_number = $_POST['id_number'];
$date_out = $_POST['date_out'];
$time_out = $_POST['time_out'];

$sql = "UPDATE attendance 
        SET time_out = :time_out
        WHERE id_number = :id_number
        AND DATE(date_in) = :date_out
        AND (
            time_out = '23:59:59'
            OR time_out IS NULL
        )
        LIMIT 1";

$stmt = $dbh->prepare($sql);
$stmt->execute([
    ':time_out' => $time_out,
    ':id_number' => $id_number,
    ':date_out' => $date_out
]);

if ($stmt->rowCount() > 0) {
    $message = "Manual Time Out recorded successfully.";
    $messageType = "success";
} else {
    $message = "No matching record found or already timed out.";
    $messageType = "danger";
}
}


/*
|--------------------------------------------------------------------------
| MANUAL BACKUP — studtbl & attendance
| On-demand snapshot of both tables, independent of the rollover process.
| Useful before making manual corrections (e.g. fixing typo'd IDs/names).
|--------------------------------------------------------------------------
*/
if (isset($_POST['manual_backup'])) {

    try {

        $timestamp = date("Ymd_His");
        $studBackupTable = "studtbl_backup_" . $timestamp;
        $attendanceBackupTable = "attendance_backup_" . $timestamp;

        $dbh->beginTransaction();

        $dbh->exec("CREATE TABLE `{$studBackupTable}` LIKE studtbl");
        $dbh->exec("INSERT INTO `{$studBackupTable}` SELECT * FROM studtbl");

        $dbh->exec("CREATE TABLE `{$attendanceBackupTable}` LIKE attendance");
        $dbh->exec("INSERT INTO `{$attendanceBackupTable}` SELECT * FROM attendance");

        $dbh->commit();

        $_SESSION['message'] = "
        <div style='
            padding:10px;
            background:#d4edda;
            color:#155724;
            border-radius:5px;
            margin-bottom:10px;'>

            💾 Manual backup created successfully.<br><br>

            📋 studtbl → <code>{$studBackupTable}</code><br>
            📋 attendance → <code>{$attendanceBackupTable}</code>
        </div>";

    } catch (Exception $e) {

        if ($dbh->inTransaction()) {
            $dbh->rollBack();
        }

        $_SESSION['message'] = "
        <div style='
            padding:10px;
            background:#f8d7da;
            color:#721c24;
            border-radius:5px;
            margin-bottom:10px;'>

            ❌ Error while creating manual backup.
        </div>";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


if (isset($_POST['rollover_students'])) {

    try {

        // Start transaction
        $dbh->beginTransaction();

        /*
        |--------------------------------------------------------------------------
        | Backup studtbl BEFORE making any rollover changes
        | Table name is stamped with the rollover date/time so it can be
        | identified later (and used by the Reset button below).
        |--------------------------------------------------------------------------
        */
        $backupTable = "studtbl_backup_" . date("Ymd_His");

        $dbh->exec("CREATE TABLE `{$backupTable}` LIKE studtbl");
        $dbh->exec("INSERT INTO `{$backupTable}` SELECT * FROM studtbl");

        /*
        |--------------------------------------------------------------------------
        | 4th Year → Alumni
        |--------------------------------------------------------------------------
        */
        $sql1 = "UPDATE studtbl
                 SET 
                    YearLevel = 'None',
                    Course = 'Alumni',
                    status = 'GRADUATED',
                    updated_at = CURRENT_TIMESTAMP
                 WHERE YearLevel = '4'
                 AND status = 'ACTIVE'";

        $stmt1 = $dbh->prepare($sql1);
        $stmt1->execute();

        $graduates = $stmt1->rowCount();

        /*
        |--------------------------------------------------------------------------
        | 3rd Year → 4th Year
        |--------------------------------------------------------------------------
        */
        $sql2 = "UPDATE studtbl
                 SET 
                    YearLevel = '4',
                    updated_at = CURRENT_TIMESTAMP
                 WHERE YearLevel = '3'
                 AND status = 'ACTIVE'";

        $stmt2 = $dbh->prepare($sql2);
        $stmt2->execute();

        $third = $stmt2->rowCount();

        /*
        |--------------------------------------------------------------------------
        | 2nd Year → 3rd Year
        |--------------------------------------------------------------------------
        */
        $sql3 = "UPDATE studtbl
                 SET 
                    YearLevel = '3',
                    updated_at = CURRENT_TIMESTAMP
                 WHERE YearLevel = '2'
                 AND status = 'ACTIVE'";

        $stmt3 = $dbh->prepare($sql3);
        $stmt3->execute();

        $second = $stmt3->rowCount();

        /*
        |--------------------------------------------------------------------------
        | 1st Year → 2nd Year
        |--------------------------------------------------------------------------
        */
        $sql4 = "UPDATE studtbl
                 SET 
                    YearLevel = '2',
                    updated_at = CURRENT_TIMESTAMP
                 WHERE YearLevel = '1'
                 AND status = 'ACTIVE'";

        $stmt4 = $dbh->prepare($sql4);
        $stmt4->execute();

        $first = $stmt4->rowCount();

        // Commit all changes
        $dbh->commit();

        $_SESSION['message'] = "
        <div style='
            padding:10px;
            background:#d4edda;
            color:#155724;
            border-radius:5px;
            margin-bottom:10px;'>

            ✅ Student rollover completed successfully.<br><br>

            🎓 Graduated to Alumni: {$graduates}<br>
            📘 3rd → 4th Year: {$third}<br>
            📗 2nd → 3rd Year: {$second}<br>
            📙 1st → 2nd Year: {$first}<br><br>

            💾 Backup created: <code>{$backupTable}</code>
        </div>";

    } catch (Exception $e) {

        $dbh->rollBack();

        $_SESSION['message'] = "
        <div style='
            padding:10px;
            background:#f8d7da;
            color:#721c24;
            border-radius:5px;
            margin-bottom:10px;'>

            ❌ Error during rollover process.
        </div>";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


/*
|--------------------------------------------------------------------------
| RESET ROLLOVER
| Restores studtbl from the most recent studtbl_backup_* table
| (i.e. undoes the last rollover run).
|--------------------------------------------------------------------------
*/
if (isset($_POST['reset_rollover'])) {

    try {

        // Find the most recent rollover backup table
        $findBackup = $dbh->prepare("
            SELECT TABLE_NAME
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME LIKE 'studtbl_backup_%'
            ORDER BY TABLE_NAME DESC
            LIMIT 1
        ");
        $findBackup->execute();
        $latestBackup = $findBackup->fetchColumn();

        if (!$latestBackup) {

            $_SESSION['message'] = "
            <div style='
                padding:10px;
                background:#f8d7da;
                color:#721c24;
                border-radius:5px;
                margin-bottom:10px;'>
                ❌ No rollover backup found to restore from.
            </div>";

        } else {

            $dbh->beginTransaction();

            // Replace studtbl contents with the backup's contents
            $dbh->exec("TRUNCATE TABLE studtbl");
            $dbh->exec("INSERT INTO studtbl SELECT * FROM `{$latestBackup}`");

            $dbh->commit();

            $_SESSION['message'] = "
            <div style='
                padding:10px;
                background:#d4edda;
                color:#155724;
                border-radius:5px;
                margin-bottom:10px;'>
                ↩️ Rollover reset successfully. Restored from <code>{$latestBackup}</code>.
            </div>";
        }

    } catch (Exception $e) {

        if ($dbh->inTransaction()) {
            $dbh->rollBack();
        }

        $_SESSION['message'] = "
        <div style='
            padding:10px;
            background:#f8d7da;
            color:#721c24;
            border-radius:5px;
            margin-bottom:10px;'>
            ❌ Error while resetting rollover.
        </div>";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

?>

<!-- Display Session Message -->
<?php
if (isset($_SESSION['message'])) {
    echo $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<html lang="en"> 
	<?php include('pages/head.php');?>
    <body class="sb-nav-fixed">
		<?php include('pages/nav.php');?>
        <div id="layoutSidenav">
			<?php include('pages/side.php');?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Settings</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="admin.php" style="color: #d63384" >Dashboard</a></li>
                            <li class="breadcrumb-item active">Settings</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fa-solid fa-clock"></i>
                                Manual Time Out (Power Interruption Recovery)
                            </div>
                            <div class="card-body">
                                
                                <?php if (!empty($message)) { ?>
                                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                                <?php echo $message; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php } ?>

                                <form method="POST">

                                    <div class="row">

                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Student ID Number</label>
                                            <input type="text" name="id_number" class="form-control" required>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Date</label>
                                            <input type="date" name="date_out" 
                                            class="form-control"
                                            required>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Time Out</label>
                                            <input type="time" name="time_out" 
                                            class="form-control"
                                            required>
                                        </div>

                                        <div class="col-md-3 d-flex align-items-end mb-3">
                                            <button type="submit" name="manual_timeout" class="btn btn-danger w-100">
                                                <i class="fa-solid fa-right-from-bracket"></i> Record Manual Time Out
                                            </button>
                                        </div>

                                    </div>

                                </form>

                            </div>                    
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fa-solid fa-clock"></i>
                                Manual Time Out (Power Interruption Recovery)
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="AttendTable" class="table table-striped table-hover mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                            <th class="text-left">Time Out</th>
                                            <th class="text-left">Time In</th>
                                            <th class="text-left">Date In</th>
                                            <th class="text-right">ID</th>
                                            <th class="text-center">Full Name</th>
                                            <th class="text-left">Course</th>
                                            <th class="text-left">Year Level</th>
                                            <th class="d-none">MonthYear</th>
                                            </tr>
                                        </thead>

                                        <tbody>

                                            <?php
                                                $sql = "SELECT id_number, fullname, course, yearlevel, date_in, time_in, time_out
                                                        FROM attendance
                                                        WHERE time_out IS NULL 
                                                        OR time_out = '23:59:59'
                                                        OR time_out = ''
                                                        ORDER BY date_in DESC, time_in ASC";

                                                $query = $dbh->prepare($sql);
                                                $query->execute();
                                                $results = $query->fetchAll(PDO::FETCH_OBJ);
                                            ?>
                                        <?php if ($query->rowCount() > 0): ?>
                                        <?php foreach ($results as $row): ?>
                                        <tr class="table-danger">

                                            <td>
                                                <?php 
                                                    $timeout = trim($row->time_out);

                                                    if ($timeout === "23:59:59") {
                                                        echo "Didn't Timeout";
                                                    } elseif ($timeout === "" || $timeout === null) {
                                                        echo "Still Inside";
                                                    } else {
                                                        echo date("h:i A", strtotime($timeout));
                                                    }
                                                ?>
                                            </td>

                                            <td>
                                                <?php echo date("h:i A", strtotime($row->time_in)); ?>
                                            </td>

                                            <td>
                                                <?php echo date("F d, Y", strtotime($row->date_in)); ?>
                                            </td>

                                            <td class="text-right">
                                                <?php echo htmlspecialchars($row->id_number); ?>
                                            </td>

                                            <td class="text-center">
                                                <?php echo htmlspecialchars($row->fullname); ?>
                                            </td>

                                            <td>
                                                <?php echo htmlspecialchars($row->course); ?>
                                            </td>

                                            <td>
                                                <?php echo htmlspecialchars($row->yearlevel); ?>
                                            </td>

                                            <td class="d-none">
                                                <?php echo date('F Y', strtotime($row->date_in)); ?>
                                            </td>

                                        </tr>
                                            
                                        <?php endforeach; ?>
                                            <?php else: ?>

                                        <tr>
                                            <td class="text-center" colspan="6">No "Didn't Timeout" records found</td>
                                        </tr>

                                        <?php endif; ?>

                                        </tbody>

                                    </table>
                                </div>
                            </div>
                            
                        </div>
                         
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fa-duotone fa-regular fa-file-spreadsheet"></i>
                                Import Student Profiles
                            </div>
                            <div class="card-body">

                                <div style="display:flex; gap:10px; flex-wrap:wrap;">

                                    <!-- Import Button -->
                                    <form method="POST" id="importTriggerForm">
                                        <button type="button"
                                                onclick="document.getElementById('importModal').style.display='flex'"
                                                style="padding:10px 20px;background:#22b573;color:white;border:none;border-radius:5px;cursor:pointer;">
                                            <i class="fa-duotone fa-regular fa-file-spreadsheet"></i>
                                            Import from CSV / Excel
                                        </button>
                                    </form>

                                </div>

                                <!-- Import Modal Overlay -->
                                <div id="importModal" style="
                                    display:none;
                                    position:fixed;
                                    top:0; left:0;
                                    width:100%; height:100%;
                                    background:rgba(0,0,0,0.5);
                                    z-index:9999;
                                    justify-content:center;
                                    align-items:center;">

                                    <!-- Modal Box -->
                                    <div style="
                                        background:white;
                                        border-radius:10px;
                                        padding:30px;
                                        max-width:460px;
                                        width:90%;
                                        box-shadow:0 10px 30px rgba(0,0,0,0.3);
                                        text-align:left;">

                                        <!-- Icon + Title -->
                                        <div style="text-align:center; margin-bottom:15px;">
                                            <div style="font-size:44px; margin-bottom:8px;">📥</div>
                                            <h3 style="margin:0; color:#22b573;">Import Student Profiles</h3>
                                        </div>

                                        <!-- Instructions -->
                                        <div style="
                                            background:#f9f9f9;
                                            border:1px solid #ddd;
                                            border-radius:8px;
                                            padding:12px 14px;
                                            font-size:13px;
                                            color:#333;
                                            line-height:1.7;
                                            margin-bottom:18px;">
                                            <strong>File requirements:</strong>
                                            <ul style="margin:6px 0 0; padding-left:18px;">
                                                <li>Format: <code>.csv</code> or <code>.xlsx</code></li>
                                                <li>Column order: <code>studID, Lname, Fname, Course, YearLevel</code></li>
                                                <li>YearLevel must be exactly: 1st Year, 2nd Year, 3rd Year, 4th Year, or None</li>
                                                <li>Course must match an existing course code</li>
                                                <li>Names are auto-corrected to proper case (e.g. <code>JOSE</code> → <code>Jose</code>)</li>
                                                <li>Duplicate Student IDs are automatically skipped</li>
                                            </ul>
                                        </div>

                                        <!-- Upload Form -->
                                        <form method="POST"
                                            action="pages/import.php"
                                            enctype="multipart/form-data"
                                            id="importForm">

                                            <div style="margin-bottom:15px;">
                                                <label for="importFile" style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                                                    Select File
                                                </label>
                                                <input type="file"
                                                    name="file"
                                                    id="importFile"
                                                    required
                                                    accept=".csv,.xls,.xlsx"
                                                    style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
                                            </div>
                                        </form>

                                        <p style="color:#999; font-size:12px; margin-bottom:20px;">
                                            💡 Tip: use the <strong>"Backup studtbl &amp; attendance"</strong> button
                                            below before importing a large file.
                                        </p>

                                        <!-- Buttons -->
                                        <div style="display:flex; gap:10px; justify-content:center;">

                                            <!-- Cancel -->
                                            <button type="button"
                                                    onclick="document.getElementById('importModal').style.display='none'"
                                                    style="
                                                        padding:10px 25px;
                                                        background:#ccc;
                                                        color:#333;
                                                        border:none;
                                                        border-radius:5px;
                                                        cursor:pointer;
                                                        font-size:14px;">
                                                ✖ Cancel
                                            </button>

                                            <!-- Confirm -->
                                            <button type="submit"
                                                    form="importForm"
                                                    name="importBtn"
                                                    style="
                                                        padding:10px 25px;
                                                        background:#22b573;
                                                        color:white;
                                                        border:none;
                                                        border-radius:5px;
                                                        cursor:pointer;
                                                        font-size:14px;">
                                                ✔ Import
                                            </button>

                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="card mb-4"> 
                            <div class="card-header">
                                <i class="fa-solid fa-graduation-cap"></i>
                                Student Rollover (Promote Students to Next Year Level or Graduate to Alumni)
                            </div>
                            <div class="card-body">

                                <div style="display:flex; gap:10px; flex-wrap:wrap;">

                                    <!-- Manual Backup Button -->
                                    <form method="POST" id="manualBackupForm">
                                        <button type="button"
                                                onclick="document.getElementById('manualBackupModal').style.display='flex'"
                                                style="padding:10px 20px;background:#198754;color:white;border:none;border-radius:5px;cursor:pointer;">
                                            <i class="fa-solid fa-database"></i>
                                            Backup studtbl &amp; attendance
                                        </button>
                                    </form>

                                    <!-- Manual Backup Modal Overlay -->
                                    <div id="manualBackupModal" style="
                                        display:none;
                                        position:fixed;
                                        top:0; left:0;
                                        width:100%; height:100%;
                                        background:rgba(0,0,0,0.5);
                                        z-index:9999;
                                        justify-content:center;
                                        align-items:center;">

                                        <!-- Modal Box -->
                                        <div style="
                                            background:white;
                                            border-radius:10px;
                                            padding:30px;
                                            max-width:420px;
                                            width:90%;
                                            box-shadow:0 10px 30px rgba(0,0,0,0.3);
                                            text-align:center;">

                                            <!-- Icon -->
                                            <div style="font-size:48px; margin-bottom:10px;">💾</div>

                                            <!-- Title -->
                                            <h3 style="margin:0 0 10px; color:#198754;">Create Manual Backup</h3>

                                            <!-- Description -->
                                            <p style="color:#555; font-size:14px; margin-bottom:15px;">
                                                This will create a timestamped snapshot of both
                                                <strong>studtbl</strong> and <strong>attendance</strong>
                                                in their current state.
                                            </p>

                                            <!-- Note -->
                                            <p style="color:#999; font-size:12px; margin-bottom:20px;">
                                                ℹ️ No existing data is changed — this only adds two new backup tables.
                                            </p>

                                            <!-- Buttons -->
                                            <div style="display:flex; gap:10px; justify-content:center;">

                                                <!-- Cancel -->
                                                <button type="button"
                                                        onclick="document.getElementById('manualBackupModal').style.display='none'"
                                                        style="
                                                            padding:10px 25px;
                                                            background:#ccc;
                                                            color:#333;
                                                            border:none;
                                                            border-radius:5px;
                                                            cursor:pointer;
                                                            font-size:14px;">
                                                    ✖ Cancel
                                                </button>

                                                <!-- Confirm -->
                                                <button type="submit"
                                                        form="manualBackupForm"
                                                        name="manual_backup"
                                                        style="
                                                            padding:10px 25px;
                                                            background:#198754;
                                                            color:white;
                                                            border:none;
                                                            border-radius:5px;
                                                            cursor:pointer;
                                                            font-size:14px;">
                                                    ✔ Yes, Backup Now
                                                </button>

                                            </div>
                                        </div>
                                    </div>

                                    <!-- Rollover Button -->
                                    <form method="POST" id="rolloverForm">
                                        <button type="button"
                                                onclick="document.getElementById('rolloverModal').style.display='flex'"
                                                style="padding:10px 20px;background:#5678f5;color:white;border:none;border-radius:5px;cursor:pointer;">
                                            Run Student Yearly Rollover
                                        </button>
                                    </form>

                                    <!-- Reset Rollover Button -->
                                    <form method="POST" id="resetRolloverForm">
                                        <button type="button"
                                                onclick="document.getElementById('resetRolloverModal').style.display='flex'"
                                                style="padding:10px 20px;background:#8f0419;color:white;border:none;border-radius:5px;cursor:pointer;">
                                            Reset Rollover
                                        </button>
                                    </form>

                                    <!-- Reset Rollover Modal Overlay -->
                                    <div id="resetRolloverModal" style="
                                        display:none;
                                        position:fixed;
                                        top:0; left:0;
                                        width:100%; height:100%;
                                        background:rgba(0,0,0,0.5);
                                        z-index:9999;
                                        justify-content:center;
                                        align-items:center;">

                                        <!-- Modal Box -->
                                        <div style="
                                            background:white;
                                            border-radius:10px;
                                            padding:30px;
                                            max-width:420px;
                                            width:90%;
                                            box-shadow:0 10px 30px rgba(0,0,0,0.3);
                                            text-align:center;">

                                            <!-- Icon -->
                                            <div style="font-size:48px; margin-bottom:10px;">↩️</div>

                                            <!-- Title -->
                                            <h3 style="margin:0 0 10px; color:#8f0419;">Reset Last Rollover</h3>

                                            <!-- Description -->
                                            <p style="color:#555; font-size:14px; margin-bottom:15px;">
                                                This will <strong>restore studtbl</strong> from the most recent
                                                rollover backup, undoing the last "Run Student Yearly Rollover"
                                                action (year levels, course, and status will revert).
                                            </p>

                                            <!-- Warning -->
                                            <p style="color:#999; font-size:12px; margin-bottom:20px;">
                                                ⚠️ Any student edits made <strong>after</strong> the last rollover
                                                will be lost. Only the most recent backup can be restored.
                                            </p>

                                            <!-- Buttons -->
                                            <div style="display:flex; gap:10px; justify-content:center;">

                                                <!-- Cancel -->
                                                <button type="button"
                                                        onclick="document.getElementById('resetRolloverModal').style.display='none'"
                                                        style="
                                                            padding:10px 25px;
                                                            background:#ccc;
                                                            color:#333;
                                                            border:none;
                                                            border-radius:5px;
                                                            cursor:pointer;
                                                            font-size:14px;">
                                                    ✖ Cancel
                                                </button>

                                                <!-- Confirm -->
                                                <button type="submit"
                                                        form="resetRolloverForm"
                                                        name="reset_rollover"
                                                        style="
                                                            padding:10px 25px;
                                                            background:#8f0419;
                                                            color:white;
                                                            border:none;
                                                            border-radius:5px;
                                                            cursor:pointer;
                                                            font-size:14px;">
                                                    ✔ Yes, Reset
                                                </button>

                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal Overlay -->
                                    <div id="rolloverModal" style="
                                        display:none;
                                        position:fixed;
                                        top:0; left:0;
                                        width:100%; height:100%;
                                        background:rgba(0,0,0,0.5);
                                        z-index:9999;
                                        justify-content:center;
                                        align-items:center;">

                                        <!-- Modal Box -->
                                        <div style="
                                            background:white;
                                            border-radius:10px;
                                            padding:30px;
                                            max-width:420px;
                                            width:90%;
                                            box-shadow:0 10px 30px rgba(0,0,0,0.3);
                                            text-align:center;">

                                            <!-- Icon -->
                                            <div style="font-size:48px; margin-bottom:10px;">⚠️</div>

                                            <!-- Title -->
                                            <h3 style="margin:0 0 10px; color:#5678f5;">Confirm Student Rollover</h3>

                                            <!-- Description -->
                                            <p style="color:#555; font-size:14px; margin-bottom:15px;">
                                                This will update <strong>all active students</strong>:
                                            </p>

                                            <!-- Rollover Summary -->
                                            <div style="
                                                background:#f9f9f9;
                                                border:1px solid #ddd;
                                                border-radius:8px;
                                                padding:12px;
                                                text-align:left;
                                                font-size:14px;
                                                color:#333;
                                                margin-bottom:20px;
                                                line-height:2;">
                                                📙 1st Year → 2nd Year<br>
                                                📗 2nd Year → 3rd Year<br>
                                                📘 3rd Year → 4th Year<br>
                                                🎓 4th Year → Alumni (GRADUATED)
                                            </div>

                                            <!-- Warning -->
                                            <p style="color:#999; font-size:12px; margin-bottom:20px;">
                                                ⚠️ This action <strong>cannot be undone</strong>. Proceed with caution.
                                            </p>

                                            <!-- Buttons -->
                                            <div style="display:flex; gap:10px; justify-content:center;">

                                                <!-- Cancel -->
                                                <button type="button"
                                                        onclick="document.getElementById('rolloverModal').style.display='none'"
                                                        style="
                                                            padding:10px 25px;
                                                            background:#ccc;
                                                            color:#333;
                                                            border:none;
                                                            border-radius:5px;
                                                            cursor:pointer;
                                                            font-size:14px;">
                                                    ✖ Cancel
                                                </button>

                                                <!-- Confirm -->
                                                <button type="submit"
                                                        form="rolloverForm"
                                                        name="rollover_students"
                                                        style="
                                                            padding:10px 25px;
                                                            background:#8f0419;
                                                            color:white;
                                                            border:none;
                                                            border-radius:5px;
                                                            cursor:pointer;
                                                            font-size:14px;">
                                                    ✔ Yes, Proceed
                                                </button>

                                            </div>
                                        </div>
                                    </div>
   
                                </div>
                            </div>                             
                        </div>                 
                    </div>
                </main>
                <?php include('pages/footer.php');?>
            </div>
        </div>
        <?php include('pages/scripts.php');?>
    </body>
</html>