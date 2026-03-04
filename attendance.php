<!DOCTYPE html>
<?php
session_start();
error_reporting(0);
include "includes/config.php";
require_once "includes/session_check.php";

if ($action === "filter" && !empty($_POST['month'])) {

    $_SESSION['selected_month'] = $_POST['month'];

    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

if ($action === "all") {

    $_SESSION['selected_month'] = "all";

    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
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
                        <h1 class="mt-4">Attendance</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="admin.php" style="color: #d63384" >Dashboard</a></li>
                            <li class="breadcrumb-item active">Attendance List</li>
                        </ol> 
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Monthly Attendance Overview
                            </div>                            
                            <!-- Attendance Summary Cards -->
                            <div class="card-body">
                                <div class="row">
                                    <!-- Monthly Attendance Summary Cards -->
                                    <div class="col-xl-4 col-md-6">
                                        <div class="card text-white mb-4" style="background-color: #d63384; border-color: #d63384;">
                                            <div class="card-body">
                                                <?php 
                                                    $sql = "SELECT COUNT(attendance_id) AS total_attendance 
                                                            FROM attendance 
                                                            WHERE YEAR(`date_in`) = YEAR(CURDATE()) 
                                                            AND MONTH(`date_in`) = MONTH(CURDATE())";
                                                    $query = $dbh->prepare($sql);
                                                    $query->execute();
                                                    $result = $query->fetch(PDO::FETCH_OBJ);
                                                    $total = $result ? $result->total_attendance : 0;
                                                ?>
                                                <h3><?php echo date('F'); ?></h3>
                                                <i class="fa-duotone fa-solid fa-users"></i>
                                                <p class="h5">Total Attendance: <?php echo $total; ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-md-6">
                                        <div class="card text-white mb-4" style="background-color: #d63384; border-color: #d63384;">
                                            <div class="card-body">
                                                <?php 
                                                    $sql = "SELECT COUNT(attendance_id) AS total_timeout
                                                            FROM attendance
                                                            WHERE time_out IS NOT NULL 
                                                            AND time_out <> ''
                                                            AND YEAR(`date_in`) = YEAR(CURDATE())
                                                            AND MONTH(`date_in`) = MONTH(CURDATE())";

                                                    $query = $dbh->prepare($sql);
                                                    $query->execute();
                                                    $result = $query->fetch(PDO::FETCH_OBJ);
                                                    $total = $result ? $result->total_timeout : 0;
                                                ?>
                                                <h3><?php echo date('F'); ?></h3>
                                                <i class="fa-duotone fa-solid fa-users"></i>
                                                <p class="h5">Total Time Out: <?php echo $total; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xl-4 col-md-6">
                                        <div class="card text-white mb-4" style="background-color: #d63384; border-color: #d63384;">
                                            <div class="card-body">
                                                <?php 
                                                    $sql = "SELECT COUNT(attendance_id) AS total_attendance
                                                            FROM attendance
                                                            WHERE (time_out IS NULL OR time_out = '23:59:59')
                                                            AND YEAR(`date_in`) = YEAR(CURDATE())
                                                            AND MONTH(`date_in`) = MONTH(CURDATE())";

                                                    $query = $dbh->prepare($sql);
                                                    $query->execute();
                                                    $result = $query->fetch(PDO::FETCH_OBJ);
                                                    $total = $result ? $result->total_attendance : 0;
                                                ?>
                                                <h3><?php echo date('F'); ?></h3>
                                                <i class="fa-duotone fa-solid fa-users"></i>
                                                <p class="h5">Did not Time Out: <?php echo $total; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xl-3 col-md-6">
                                        <div class="card text-white mb-4" style="background-color: #FFBF00; border-color: #B38600;">
                                            <div class="card-body">
                                                <?php
                                                $sql = "SELECT COUNT(DISTINCT attendance_id) AS number_of_students 
                                                        FROM attendance WHERE course = 'BSBA MM'";

                                                $query = $dbh->prepare($sql);
                                                $query->execute();
                                                $result = $query->fetch(PDO::FETCH_OBJ);
                                                $total = $result ? $result->number_of_students : 0;
                                                ?>
                                                <h3>BSBA MM</h3>
                                                <p class="h5">Num of Students: <?php echo $total; ?></p>
                                            </div> 
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <div class="card text-white mb-4" style="background-color: #FFBF00; border-color: #B38600;">
                                            <div class="card-body">
                                                <?php
                                                $sql = "SELECT COUNT(DISTINCT attendance_id) AS number_of_students 
                                                        FROM attendance WHERE course = 'BSBA FM'";

                                                $query = $dbh->prepare($sql);
                                                $query->execute();
                                                $result = $query->fetch(PDO::FETCH_OBJ);
                                                $total = $result ? $result->number_of_students : 0;
                                                ?>
                                                <h3>BSBA FM</h3>
                                                <p class="h5">Num of Students: <?php echo $total; ?></p>
                                            </div> 
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <div class="card text-white mb-4" style="background-color: #FFBF00; border-color: #B38600;">
                                            <div class="card-body">
                                                <?php
                                                $sql = "SELECT COUNT(DISTINCT attendance_id) AS number_of_students 
                                                        FROM attendance WHERE course = 'BSBA HRM'";

                                                $query = $dbh->prepare($sql);
                                                $query->execute();
                                                $result = $query->fetch(PDO::FETCH_OBJ);
                                                $total = $result ? $result->number_of_students : 0;
                                                ?>
                                                <h3>BSBA HRM</h3>
                                                <p class="h5">Num of Students: <?php echo $total; ?></p>
                                            </div> 
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <div class="card text-white mb-4" style="background-color: #005033ff; border-color: #013220;">
                                            <div class="card-body">
                                                <?php
                                                $sql = "SELECT COUNT(DISTINCT attendance_id) AS number_of_students
                                                        FROM attendance 
                                                        WHERE course = 'BSA'
                                                        AND YEAR(`date_in`) = YEAR(CURDATE())
                                                        AND MONTH(`date_in`) = MONTH(CURDATE())";

                                                $query = $dbh->prepare($sql);
                                                $query->execute();
                                                $result = $query->fetch(PDO::FETCH_OBJ);
                                                $total = $result ? $result->number_of_students : 0;
                                                ?>
                                                <h3>BSA</h3>
                                                <p class="h5">Num of Students: <?php echo $total; ?></p>
                                            </div> 
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <div class="card bg-primary text-white mb-4">
                                            <div class="card-body">
                                                <?php
                                                $sql = "SELECT COUNT(DISTINCT attendance_id) AS number_of_students 
                                                        FROM attendance WHERE course = 'BSEd SS'";

                                                $query = $dbh->prepare($sql);
                                                $query->execute();
                                                $result = $query->fetch(PDO::FETCH_OBJ);
                                                $total = $result ? $result->number_of_students : 0;
                                                ?>
                                                <h3>BSEd SS</h3>
                                                <p class="h5">Num of Students: <?php echo $total; ?></p>
                                            </div> 
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <div class="card bg-primary text-white mb-4">
                                            <div class="card-body">
                                                <?php
                                                $sql = "SELECT COUNT(DISTINCT attendance_id) AS number_of_students 
                                                        FROM attendance WHERE course = 'BSEd English'";

                                                $query = $dbh->prepare($sql);
                                                $query->execute();
                                                $result = $query->fetch(PDO::FETCH_OBJ);
                                                $total = $result ? $result->number_of_students : 0;
                                                ?>
                                                <h3>BSEd English</h3>
                                                <p class="h5">Num of Students: <?php echo $total; ?></p>
                                            </div> 
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <div class="card bg-primary text-white mb-4">
                                            <div class="card-body">
                                                <?php
                                                $sql = "SELECT COUNT(DISTINCT attendance_id) AS number_of_students
                                                        FROM attendance 
                                                        WHERE course = 'BSEd Math'
                                                        AND YEAR(`date_in`) = YEAR(CURDATE())
                                                        AND MONTH(`date_in`) = MONTH(CURDATE())";

                                                $query = $dbh->prepare($sql);
                                                $query->execute();
                                                $result = $query->fetch(PDO::FETCH_OBJ);
                                                $total = $result ? $result->number_of_students : 0;
                                                ?>
                                                <h3>BSEd Math</h3>
                                                <p class="h5">Num of Students: <?php echo $total; ?></p>
                                            </div> 
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                       <div class="card bg-primary text-white mb-4">
                                            <div class="card-body">
                                                <?php
                                                $sql = "SELECT COUNT(DISTINCT attendance_id) AS number_of_students 
                                                        FROM attendance WHERE course = 'BEEd'";

                                                $query = $dbh->prepare($sql);
                                                $query->execute();
                                                $result = $query->fetch(PDO::FETCH_OBJ);
                                                $total = $result ? $result->number_of_students : 0;
                                                ?>
                                                <h3>BEEd</h3>
                                                <p class="h5">Num of Students: <?php echo $total; ?></p>
                                            </div> 
                                        </div>
                                    </div>

                                </div>   
                            </div>            
                        </div>

                        <!-- Attendance Table Card -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i> Monthly Attendance List
                            </div>

                            <div class="card-body">
                                <?php
                                    $selected_month = $_SESSION['selected_month'] ?? date("Y-m");

                                   

                                    /* ✅ Show ALL only when button clicked */
                                    if ($selected_month === "all") {

                                        $sql = "SELECT id_number, fullname, course, yearlevel,
                                                date_in, time_in, time_out
                                                FROM attendance
                                                ORDER BY date_in ASC, time_in ASC";

                                        $query = $dbh->prepare($sql);
                                        $query->execute();

                                    } else {

                                        list($selected_year, $selected_month_num)
                                            = explode("-", $selected_month);

                                        $sql = "SELECT id_number, fullname, course, yearlevel,
                                                date_in, time_in, time_out
                                                FROM attendance
                                                WHERE YEAR(date_in)=:year
                                                AND MONTH(date_in)=:month
                                                ORDER BY date_in ASC, time_in ASC";

                                        $query = $dbh->prepare($sql);
                                        $query->bindParam(':year', $selected_year, PDO::PARAM_INT);
                                        $query->bindParam(':month', $selected_month_num, PDO::PARAM_INT);
                                        $query->execute();
                                    }

                                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                                ?>

                                <!-- Filter Form -->
                                <form method="POST" class="mb-3">
                                    <div class="d-flex flex-column flex-sm-row gap-2 align-items-start align-items-sm-center">
                                        <input type="month" id="month" name="month" class="form-control w-auto"
                                            style="max-width: 180px;"
                                            value="<?php echo htmlspecialchars($selected_month); ?>">

                                        <button class="btn btn-outline-secondary w-100 w-sm-auto" type="submit" name="action" value="filter">
                                            View
                                        </button>

                                        <button class="btn btn-outline-primary w-100 w-sm-auto" type="submit" name="action" value="all">
                                            Show All Months
                                        </button>
                                    </div>
                                </form>

                                <!-- Display selected month/year or "All Months" -->
                                <?php if ($action === "all"): ?>
                                    <h5 class="mb-3"><strong>Showing All Attendance Records</strong></h5>
                                <?php else: ?>
                                    <h5 class="mb-3">
                                        Showing Attendance for:
                                        <strong><?php echo date("F Y", strtotime($selected_year . '-' . $selected_month_num . '-01')); ?></strong>
                                    </h5>
                                <?php endif; ?>

                                <!-- Attendance Table -->
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
                                            <?php if ($query->rowCount() > 0): ?>
                                                <?php foreach ($results as $row): ?>
                                                    <tr>
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
                                                    <td class="text-center" colspan="7">No Data</td>
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
