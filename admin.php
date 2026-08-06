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
                                                    WHERE DATE(date_in) = CURDATE()
                                                    ORDER BY 
                                                        (time_out IS NULL OR time_out = '') DESC,
                                                        time_out ASC,
                                                        date_in ASC";
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
                                                            if (empty($row->time_out)) {
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

                        <div class="card mb-4">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <i class="fa-duotone fa-solid fa-trophy me-1"></i>
                                        Top 3 Students by Course (<span id="topStudentsLabel"></span>)
                                    </div>

                                    <div class="d-flex align-items-center gap-2 flex-nowrap">
                                        <!-- Month Filter -->
                                        <select id="topStudentsMonth" class="form-select form-select-sm" style="min-width: 130px;">
                                            <option value="all">All Months</option>
                                            <?php
                                            $currentMonth = date('n');
                                            $monthNames = [
                                                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                                            ];
                                            foreach ($monthNames as $num => $name) {
                                                $selected = ($num == $currentMonth) ? 'selected' : '';
                                                echo "<option value='$num' $selected>$name</option>";
                                            }
                                            ?>
                                        </select>

                                        <!-- Year Filter -->
                                        <select id="topStudentsYear" class="form-select form-select-sm" style="min-width: 90px;">
                                            <?php
                                            $currentYear = date('Y');
                                            for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                                                $selected = ($y == $currentYear) ? 'selected' : '';
                                                echo "<option value='$y' $selected>$y</option>";
                                            }
                                            ?>
                                        </select>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-primary d-flex align-items-center gap-1 text-nowrap"
                                            onclick="loadTopStudentsTable()"
                                        >
                                            <i class="fa-duotone fa-light fa-filter"></i>
                                            <span class="d-none d-sm-inline">Filter</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="topStudentsTable" name="topStudentsTable" class="table table-striped table-hover align-middle w-100">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Course</th>
                                                <th>Rank</th>
                                                <th class="text-end">ID Number</th>
                                                <th class="text-center">Full Name</th>
                                                <th>Year Level</th>
                                                <th>Total Visits</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Populated by DataTables via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="card-footer small text-muted">
                                <i class="fa-regular fa-clock me-1"></i>
                                Updated: <?php echo date("F d, Y h:i A"); ?>
                            </div>
                        </div>

                        <script>
                        const topStudentsMonthNames = {
                            1: 'January', 2: 'February', 3: 'March', 4: 'April',
                            5: 'May', 6: 'June', 7: 'July', 8: 'August',
                            9: 'September', 10: 'October', 11: 'November', 12: 'December'
                        };

                        let topStudentsDataTable = null;

                        function loadTopStudentsTable() {
                            const month = document.getElementById('topStudentsMonth').value;
                            const year = document.getElementById('topStudentsYear').value;

                            const label = (month === 'all')
                                ? `All Months ${year}`
                                : `${topStudentsMonthNames[month]} ${year}`;
                            document.getElementById('topStudentsLabel').textContent = label;

                            if (topStudentsDataTable) {
                                topStudentsDataTable.ajax.url(
                                    `pages/top_students_by_course.php?month=${month}&year=${year}`
                                ).load();
                                return;
                            }

                            topStudentsDataTable = $('#topStudentsTable').DataTable({
                                ajax: {
                                    url: `pages/top_students_by_course.php?month=${month}&year=${year}`,
                                    dataSrc: 'data'
                                },
                                columns: [
                                    { data: 'course' },
                                    { data: 'rank_in_course' },
                                    { data: 'id_number', className: 'text-end' },
                                    { data: 'fullname', className: 'text-center' },
                                    { data: 'yearlevel' },
                                    { data: 'total_visits' }
                                ],
                                order: [[0, 'asc'], [1, 'asc']],
                                pageLength: 25,
                                lengthChange: false
                            });
                        }

                        document.addEventListener('DOMContentLoaded', loadTopStudentsTable);
                        </script>

                        <div class="card mb-4">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <i class="fa-duotone fa-solid fa-users me-1"></i>
                                        Student Attendance Count (<span id="studentCountLabel"></span>)
                                    </div>

                                    <div class="d-flex align-items-center gap-2 flex-nowrap overflow-auto pb-1">
                                        <!-- From Month / Year -->
                                        <select id="studentCountStartMonth" class="form-select form-select-sm" style="min-width: 120px;">
                                            <?php
                                            foreach ($monthNames as $num => $name) {
                                                $selected = ($num == 1) ? 'selected' : '';
                                                echo "<option value='$num' $selected>$name</option>";
                                            }
                                            ?>
                                        </select>

                                        <select id="studentCountStartYear" class="form-select form-select-sm" style="min-width: 90px;">
                                            <?php
                                            for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                                                $selected = ($y == $currentYear) ? 'selected' : '';
                                                echo "<option value='$y' $selected>$y</option>";
                                            }
                                            ?>
                                        </select>

                                        <span>to</span>

                                        <!-- To Month / Year -->
                                        <select id="studentCountEndMonth" class="form-select form-select-sm" style="min-width: 120px;">
                                            <?php
                                            foreach ($monthNames as $num => $name) {
                                                $selected = ($num == $currentMonth) ? 'selected' : '';
                                                echo "<option value='$num' $selected>$name</option>";
                                            }
                                            ?>
                                        </select>

                                        <select id="studentCountEndYear" class="form-select form-select-sm" style="min-width: 90px;">
                                            <?php
                                            for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                                                $selected = ($y == $currentYear) ? 'selected' : '';
                                                echo "<option value='$y' $selected>$y</option>";
                                            }
                                            ?>
                                        </select>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-primary d-flex align-items-center gap-1 text-nowrap"
                                            onclick="loadStudentCountTable()"
                                        >
                                            <i class="fa-duotone fa-light fa-filter"></i>
                                            <span class="d-none d-sm-inline">Filter</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="studentCountTable" name="studentCountTable" class="table table-striped table-hover align-middle w-100">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-end">ID Number</th>
                                                <th class="text-center">Full Name</th>
                                                <th>Course</th>
                                                <th>Year Level</th>
                                                <th>Total Visits</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Populated by DataTables via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="card-footer small text-muted">
                                <i class="fa-regular fa-clock me-1"></i>
                                Excludes students who did not time out
                            </div>
                        </div>

                        <script>
                        let studentCountDataTable = null;

                        function loadStudentCountTable() {
                            const startMonth = document.getElementById('studentCountStartMonth').value;
                            const endMonth = document.getElementById('studentCountEndMonth').value;
                            const startYear = document.getElementById('studentCountStartYear').value;
                            const endYear = document.getElementById('studentCountEndYear').value;

                            const url = `pages/student_attendance_count.php?start_month=${startMonth}&end_month=${endMonth}&start_year=${startYear}&end_year=${endYear}`;
                            const label = `${topStudentsMonthNames[startMonth]} ${startYear} - ${topStudentsMonthNames[endMonth]} ${endYear}`;

                            document.getElementById('studentCountLabel').textContent = label;

                            if (studentCountDataTable) {
                                studentCountDataTable.ajax.url(url).load();
                                return;
                            }

                            studentCountDataTable = $('#studentCountTable').DataTable({
                                ajax: {
                                    url: url,
                                    dataSrc: 'data'
                                },
                                columns: [
                                    { data: 'id_number', className: 'text-end' },
                                    { data: 'fullname', className: 'text-center' },
                                    { data: 'course' },
                                    { data: 'yearlevel' },
                                    { data: 'total_visits' }
                                ],
                                order: [[4, 'desc']],
                                pageLength: 25,
                                lengthChange: false
                            });
                        }

                        document.addEventListener('DOMContentLoaded', loadStudentCountTable);
                        </script>

                    </div>
                </main>
                <?php include('pages/footer.php');?>
            </div>
        </div>
       
        <?php include('pages/scripts.php');?>
    </body>
</html>