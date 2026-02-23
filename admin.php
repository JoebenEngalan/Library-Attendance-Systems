<!DOCTYPE html>
<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "includes/config.php";
require_once "includes/session_check.php";

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
                        <h1 class="mt-4">Dashboard</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>

                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center"
                                data-bs-toggle="collapse"
                                data-bs-target="#attendanceOverview"
                                role="button"
                                aria-expanded="true"
                                aria-controls="attendanceOverview">

                                <div>
                                    <i class="fa-duotone fa-regular fa-house"></i>
                                    Todays Attendance Overview
                                </div>

                                <i class="fa-solid fa-chevron-down"></i>
                            </div>                            
                            <div id="attendanceOverview" class="collapse show">
                                <div class="card-body">
                                    
                                    <div class="row">

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
                                                    $sql = "SELECT COUNT(DISTINCT id_number) AS timed_in_students 
                                                                FROM attendance 
                                                                WHERE course = 'BSBA MM' 
                                                                AND time_in IS NOT NULL 
                                                                AND time_in <> '' 
                                                                AND DATE(date_in) = CURDATE()";

                                                        $query = $dbh->prepare($sql);
                                                        $query->execute();
                                                        $result = $query->fetch(PDO::FETCH_OBJ);
                                                        $total = $result ? $result->timed_in_students : 0;
                                                    ?>
                                                    <h3>BSBA MM</h3>
                                                    <i class="fa-duotone fa-solid fa-users"></i>
                                                    <p class="h5">Log-In Students: <?php echo $total; ?></p>
                                                </div> 
                                            </div>
                                        </div>

                                        <div class="col-xl-3 col-md-6">
                                            <div class="card text-white mb-4" style="background-color: #FFBF00; border-color: #B38600;">
                                                <div class="card-body">
                                                    <?php
                                                    $sql = "SELECT COUNT(DISTINCT id_number) AS timed_in_students 
                                                                FROM attendance 
                                                                WHERE course = 'BSBA FM' 
                                                                AND time_in IS NOT NULL 
                                                                AND time_in <> '' 
                                                                AND DATE(date_in) = CURDATE()";

                                                        $query = $dbh->prepare($sql);
                                                        $query->execute();
                                                        $result = $query->fetch(PDO::FETCH_OBJ);
                                                        $total = $result ? $result->timed_in_students : 0;
                                                    ?>
                                                    <h3>BSBA FM</h3>
                                                    <i class="fa-duotone fa-solid fa-users"></i>
                                                    <p class="h5">Log-In Students: <?php echo $total; ?></p>
                                                </div>                                    
                                            </div>
                                        </div>

                                        <div class="col-xl-3 col-md-6">
                                            <div class="card text-white mb-4" style="background-color: #FFBF00; border-color: #B38600;">
                                                <div class="card-body">
                                                    <?php
                                                    $sql = "SELECT COUNT(DISTINCT id_number) AS timed_in_students 
                                                                FROM attendance 
                                                                WHERE course = 'BSBA HRM' 
                                                                AND time_in IS NOT NULL 
                                                                AND time_in <> '' 
                                                                AND DATE(date_in) = CURDATE()";

                                                        $query = $dbh->prepare($sql);
                                                        $query->execute();
                                                        $result = $query->fetch(PDO::FETCH_OBJ);
                                                        $total = $result ? $result->timed_in_students : 0;
                                                    ?>
                                                    <h3>BSBA HRM</h3>
                                                    <i class="fa-duotone fa-solid fa-users"></i>
                                                    <p class="h5">Log-In Students: <?php echo $total; ?></p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xl-3 col-md-6">
                                            <div class="card text-white mb-4" style="background-color: #005033ff; border-color: #013220;">
                                                <div class="card-body">
                                                    <?php
                                                    $sql = "SELECT COUNT(DISTINCT id_number) AS timed_in_students 
                                                                FROM attendance 
                                                                WHERE course = 'BSA' 
                                                                AND time_in IS NOT NULL 
                                                                AND time_in <> '' 
                                                                AND DATE(date_in) = CURDATE()";

                                                        $query = $dbh->prepare($sql);
                                                        $query->execute();
                                                        $result = $query->fetch(PDO::FETCH_OBJ);
                                                        $total = $result ? $result->timed_in_students : 0;
                                                    ?>
                                                    <h3>BSA</h3>
                                                    <i class="fa-duotone fa-solid fa-users"></i>
                                                    <p class="h5">Log-In Students: <?php echo $total; ?></p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xl-3 col-md-6">
                                            <div class="card bg-primary text-white mb-4">
                                                <div class="card-body">
                                                    <?php
                                                    $sql = "SELECT COUNT(DISTINCT id_number) AS timed_in_students 
                                                                FROM attendance 
                                                                WHERE course = 'BSEd SS' 
                                                                AND time_in IS NOT NULL 
                                                                AND time_in <> '' 
                                                                AND DATE(date_in) = CURDATE()";

                                                        $query = $dbh->prepare($sql);
                                                        $query->execute();
                                                        $result = $query->fetch(PDO::FETCH_OBJ);
                                                        $total = $result ? $result->timed_in_students : 0;
                                                    ?>
                                                    <h3>BSEd SS</h3>
                                                    <i class="fa-duotone fa-solid fa-users"></i>
                                                    <p class="h5">Log-In Students: <?php echo $total; ?></p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xl-3 col-md-6">
                                            <div class="card bg-primary text-white mb-4">
                                                <div class="card-body">
                                                    <?php
                                                    $sql = "SELECT COUNT(DISTINCT id_number) AS timed_in_students 
                                                                FROM attendance 
                                                                WHERE course = 'BSEd English' 
                                                                AND time_in IS NOT NULL 
                                                                AND time_in <> '' 
                                                                AND DATE(date_in) = CURDATE()";

                                                        $query = $dbh->prepare($sql);
                                                        $query->execute();
                                                        $result = $query->fetch(PDO::FETCH_OBJ);
                                                        $total = $result ? $result->timed_in_students : 0;
                                                    ?>
                                                    <h3>BSEd English</h3>
                                                    <i class="fa-duotone fa-solid fa-users"></i>
                                                    <p class="h5">Log-In Students: <?php echo $total; ?></p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xl-3 col-md-6">
                                            <div class="card bg-primary text-white mb-4">
                                                <div class="card-body">
                                                    <?php
                                                    $sql = "SELECT COUNT(DISTINCT id_number) AS timed_in_students 
                                                                FROM attendance 
                                                                WHERE course = 'BSEd Math' 
                                                                AND time_in IS NOT NULL 
                                                                AND time_in <> '' 
                                                                AND DATE(date_in) = CURDATE()";

                                                        $query = $dbh->prepare($sql);
                                                        $query->execute();
                                                        $result = $query->fetch(PDO::FETCH_OBJ);
                                                        $total = $result ? $result->timed_in_students : 0;
                                                    ?>
                                                    <h3>BSEd Math</h3>
                                                    <i class="fa-duotone fa-solid fa-users"></i>
                                                    <p class="h5">Log-In Students: <?php echo $total; ?></p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xl-3 col-md-6">
                                            <div class="card bg-primary text-white mb-4">
                                                <div class="card-body">
                                                    <?php
                                                    $sql = "SELECT COUNT(DISTINCT id_number) AS timed_in_students 
                                                                FROM attendance 
                                                                WHERE course = 'BEEd' 
                                                                AND time_in IS NOT NULL 
                                                                AND time_in <> '' 
                                                                AND DATE(date_in) = CURDATE()";

                                                        $query = $dbh->prepare($sql);
                                                        $query->execute();
                                                        $result = $query->fetch(PDO::FETCH_OBJ);
                                                        $total = $result ? $result->timed_in_students : 0;
                                                    ?>
                                                    <h3>BEEd</h3>
                                                    <i class="fa-duotone fa-solid fa-users"></i>
                                                    <p class="h5"> Log-In Students: <?php echo $total; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                
                                </div>
                            </div>
                        
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Todays Attendance Records
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="maindattables" name="maindattables" class="table table-striped table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th >Time Out</th>
                                                <th >Date In</th>
                                                <th>Time In</th>
                                                <th class="text-end">ID</th>
                                                <th class="text-center">Full Name</th>
                                                <th >Course</th>
                                                <th >Year Level</th>  
                                            </tr>
                                        </thead>

                                        <?php
                                            $sql = "SELECT id_number, fullname, course, yearlevel, date_in, time_in, time_out
                                                    FROM attendance
                                                    WHERE DATE(`date_in`) = CURDATE()
                                                    ORDER BY time_out ASC, date_in ASC";
                                            $query = $dbh->prepare($sql);
                                            $query->execute();
                                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                                        ?>

                                        <tbody>
                                            <?php if ($query->rowCount() > 0): ?>
                                                <?php foreach ($results as $row): ?>
                                                    <tr>
                                                        <!-- Time Out -->
                                                        <td>
                                                            <?php
                                                            if ($row->time_out === '23:59:59') {
                                                                echo "Didn't Timeout";
                                                            } elseif (empty($row->time_out)) {
                                                                echo "Still Inside";
                                                            } else {
                                                                echo date("h:i A", strtotime($row->time_out));
                                                            }
                                                            ?>
                                                        </td>

                                                        <!-- Date In -->
                                                        <td>
                                                            <?php echo date("F d, Y", strtotime($row->date_in)); ?>
                                                        </td>

                                                        <!-- Time In -->
                                                        <td>
                                                            <?php echo date("h:i A", strtotime($row->time_in)); ?>
                                                        </td>

                                                        <!-- ID -->
                                                        <td class="text-end">
                                                            <?php echo htmlspecialchars($row->id_number); ?>
                                                        </td>

                                                        <!-- Full Name -->
                                                        <td class="text-center">
                                                            <?php echo htmlspecialchars($row->fullname); ?>
                                                        </td>

                                                        <!-- Course -->
                                                        <td>
                                                            <?php echo htmlspecialchars($row->course); ?>
                                                        </td>

                                                        <!-- Year Level -->
                                                        <td>
                                                            <?php echo htmlspecialchars($row->yearlevel); ?>
                                                        </td>
                                                    </tr>

                                                <?php endforeach; ?>
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
