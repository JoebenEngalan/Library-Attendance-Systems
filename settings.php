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
if (isset($_POST['manual_timeout'])) {

$id_number = $_POST['id_number'];
$date_out = $_POST['date_out'];
$time_out = $_POST['time_out'];

$sql = "UPDATE attendance 
        SET time_out = :time_out
        WHERE id_number = :id_number
        AND DATE(date_in) = :date_out
        AND time_out = '23:59:59'";

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
                                                        WHERE time_out = '23:59:59'
                                                        ORDER BY date_in DESC, time_in ASC";

                                                $query = $dbh->prepare($sql);
                                                $query->execute();
                                                $results = $query->fetchAll(PDO::FETCH_OBJ);
                                            ?>
                                        <?php if ($query->rowCount() > 0): ?>
                                        <?php foreach ($results as $row): ?>
                                        <tr class="table-danger">

                                            <td>Didn't Timeout</td>

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
                              
                    </div>
                </main>
                <?php include('pages/footer.php');?>
            </div>
        </div>

        
        <?php include('pages/scripts.php');?>
    </body>
</html>
