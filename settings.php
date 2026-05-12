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
| 4th Year → Alumni
|--------------------------------------------------------------------------
*/
if (isset($_POST['graduate_students'])) {

    $sql = "UPDATE studtbl
            SET 
                YearLevel = 'None',
                Course = 'Alumni',
                status = 'GRADUATED',
                updated_at = CURRENT_TIMESTAMP
            WHERE YearLevel = '4th Year'
            AND status = 'ACTIVE'";

    $stmt = $dbh->prepare($sql);
    $stmt->execute();

    $_SESSION['message'] = "
    <div style='padding:10px;background:#d4edda;color:#155724;border-radius:5px;margin-bottom:10px;'>
        ✅ " . $stmt->rowCount() . " student(s) graduated and moved to Alumni.
    </div>";

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

/*
|--------------------------------------------------------------------------
| 3rd Year → 4th Year
|--------------------------------------------------------------------------
*/
if (isset($_POST['promote_third_year'])) {

    $sql = "UPDATE studtbl
            SET 
                YearLevel = '4th Year',
                updated_at = CURRENT_TIMESTAMP
            WHERE YearLevel = '3rd Year'
            AND status = 'ACTIVE'";

    $stmt = $dbh->prepare($sql);
    $stmt->execute();

    $_SESSION['message'] = "
    <div style='padding:10px;background:#d4edda;color:#155724;border-radius:5px;margin-bottom:10px;'>
        ✅ " . $stmt->rowCount() . " student(s) promoted from 3rd Year to 4th Year.
    </div>";

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

/*
|--------------------------------------------------------------------------
| 2nd Year → 3rd Year
|--------------------------------------------------------------------------
*/
if (isset($_POST['promote_second_year'])) {

    $sql = "UPDATE studtbl
            SET 
                YearLevel = '3rd Year',
                updated_at = CURRENT_TIMESTAMP
            WHERE YearLevel = '2nd Year'
            AND status = 'ACTIVE'";

    $stmt = $dbh->prepare($sql);
    $stmt->execute();

    $_SESSION['message'] = "
    <div style='padding:10px;background:#d4edda;color:#155724;border-radius:5px;margin-bottom:10px;'>
        ✅ " . $stmt->rowCount() . " student(s) promoted from 2nd Year to 3rd Year.
    </div>";

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

/*
|--------------------------------------------------------------------------
| 1st Year → 2nd Year
|--------------------------------------------------------------------------
*/
if (isset($_POST['promote_first_year'])) {

    $sql = "UPDATE studtbl
            SET 
                YearLevel = '2nd Year',
                updated_at = CURRENT_TIMESTAMP
            WHERE YearLevel = '1st Year'
            AND status = 'ACTIVE'";

    $stmt = $dbh->prepare($sql);
    $stmt->execute();

    $_SESSION['message'] = "
    <div style='padding:10px;background:#d4edda;color:#155724;border-radius:5px;margin-bottom:10px;'>
        ✅ " . $stmt->rowCount() . " student(s) promoted from 1st Year to 2nd Year.
    </div>";

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


if (isset($_POST['rollover_students'])) {

    try {

        // Start transaction
        $dbh->beginTransaction();

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
            📙 1st → 2nd Year: {$first}
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
                                <i class="fa-solid fa-graduation-cap"></i>
                                Stutend Rollover (Promote Students to Next Year Level or Graduate to Alumni)
                            </div>
                            <div class="card-body">

                                <div style="display:flex; gap:10px; flex-wrap:wrap;">

                                    <!-- 4th → Alumni -->
                                    <form method="POST">
                                        <button type="submit"
                                                name="graduate_students"
                                                onclick="return confirm('Graduate all 4th Year students to Alumni?')"
                                                style="padding:10px 20px;background:#28a745;color:white;border:none;border-radius:5px;cursor:pointer;">
                                            4th Year → Alumni
                                        </button>
                                    </form>

                                    <!-- 3rd → 4th -->
                                    <form method="POST">
                                        <button type="submit"
                                                name="promote_third_year"
                                                onclick="return confirm('Promote all 3rd Year students to 4th Year?')"
                                                style="padding:10px 20px;background:#ffc107;color:black;border:none;border-radius:5px;cursor:pointer;">
                                            3rd Year → 4th Year
                                        </button>
                                    </form>

                                    <!-- 2nd → 3rd -->
                                    <form method="POST">
                                        <button type="submit"
                                                name="promote_second_year"
                                                onclick="return confirm('Promote all 2nd Year students to 3rd Year?')"
                                                style="padding:10px 20px;background:#17a2b8;color:white;border:none;border-radius:5px;cursor:pointer;">
                                            2nd Year → 3rd Year
                                        </button>
                                    </form>

                                    <!-- 1st → 2nd -->
                                    <form method="POST">
                                        <button type="submit"
                                                name="promote_first_year"
                                                onclick="return confirm('Promote all 1st Year students to 2nd Year?')"
                                                style="padding:10px 20px;background:#007bff;color:white;border:none;border-radius:5px;cursor:pointer;">
                                            1st Year → 2nd Year
                                        </button>
                                    </form>

                                    <!-- Single Button -->
                                    <form method="POST">
                                        <button type="submit"
                                                name="rollover_students"
                                                onclick="return confirm('Run yearly student rollover process?')"
                                                style="padding:10px 20px;background:#8f0419;color:white;border:none;border-radius:5px;cursor:pointer;">
                                            Run Student Yearly Rollover
                                        </button>
                                    </form>           
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
