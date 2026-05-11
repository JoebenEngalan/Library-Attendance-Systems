<?php
session_start();
error_reporting(0);
// Include DB connection
include "includes/config.php";
// Set timezone
date_default_timezone_set('Asia/Manila');
// AUTO-CLOSE PREVIOUS DAY OPEN ATTENDANCE
$today = date("Y-m-d");
$sqlAutoClose = "UPDATE attendance 
                 SET time_out = '23:59:59'
                 WHERE time_out IS NULL
                 AND date_in < :today";

$stmtAuto = $dbh->prepare($sqlAutoClose);
$stmtAuto->bindParam(':today', $today, PDO::PARAM_STR);
$stmtAuto->execute();

// Step 1: If form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $_SESSION['id_number'] = $_POST['id_number']; // save temporarily
    header("Location: pages/attend.php");
    exit();
}
// Step 2: Display message from attend.php
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="en">
<?php include('pages/indexhead.php');?>

<body>
    <!-- Navigation -->
    <?php include('pages/indexnav.php');?>
    
    <!-- Masthead -->
    <header class="masthead min-vh-50 d-flex align-items-center">
        <div class="container position-relative">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-8 col-xl-6">

                    <div class="text-center text-white">
                                                     
                        <?php if ($message): ?>
                            <div class="alert alert-light mt-4 fw-bold fs-3" role="alert">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>
                        <h1 class="mb-1" id="clock"></h1>
                        <h2 class="mb-4" id="date"></h2>

                        <form method="POST" id="attendanceForm">
                            <div class="row g-2 justify-content-center">

                                <!-- ID Input -->
                                <div class="col-12 col-sm-8">
                                    <input
                                        type="text"
                                        name="id_number"
                                        id="id_number"
                                        class="form-control form-control-lg text-left"
                                        placeholder="Enter or Scan Student ID"
                                        autocomplete="off"
                                        onkeydown="return /[a-zA-Z0-9]+/i.test(event.key)"
                                        required
                                        autofocus>
                                </div>

                                <!-- Submit Button -->
                                <div class="col-12 col-sm-auto d-grid">
                                    <button
                                        type="submit"
                                        name="submit"
                                        class="btn btn-lg text-white"
                                        style="background-color:#d63384;">
                                        Enter
                                    </button>
                                </div>

                            </div>
                        </form>
                        
                    </div>

                </div>
            </div>
        </div>
    </header>

    <div class="modal fade" id="aboutModal" tabindex="-1" aria-labelledby="aboutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm modal-md">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="aboutModalLabel">
                        MonCast Learning Resource Center
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <p class="fw-bold mb-1">About the System</p>
                    <p class="small text-muted">
                        The MonCast Learning Resource Center Attendance System is designed
                        to efficiently monitor and manage student attendance records
                        using a simple and user-friendly interface.
                    </p>

                    <hr>

                    <p class="fw-bold mb-1">Features</p>
                    <ul class="small">
                        <li>Real-time student attendance tracking</li>
                        <li>Monthly and daily attendance reports</li>
                        <li>Mobile-friendly and responsive design</li>
                        <li>Secure admin access</li>
                    </ul>

                    <hr>

                    <p class="fw-bold mb-1">Developed For</p>
                    <p class="small text-muted mb-0">
                        MonCast Learning Resource Center
                    </p>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Contact Modal -->
    <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="contactModalLabel">
                        Contact Information
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center">

                    <p class="fw-bold mb-1">Name</p>
                    <p class="text-muted">Joeben P. Engalan</p>

                    <hr>

                    <p class="fw-bold mb-1">Email</p>
                    <p class="mb-0">
                        <a href="mailto:jpengalan@usep.edu.ph" class="text-decoration-none">
                            jpengalan@usep.edu.ph
                        </a>
                    </p>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>

            </div>
        </div>
    </div>
    <!-- Footer -->                       
    <footer class="py-4 bg-light mt-auto">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center justify-content-between small flex-wrap">

                <div class="text-muted">
                    MonCast Learning Resource Center &copy; All Rights Reserved <span id="year"></span>
                </div>

                <div class="text-nowrap">
                    <a href="#"
                    class="text-decoration-none"
                    style="color:#d63384"
                    data-bs-toggle="modal"
                    data-bs-target="#aboutModal">
                        About
                    </a>
                    <span class="mx-1">&middot;</span>

                    <a href="#"
                    class="text-decoration-none"
                    style="color:#d63384"
                    data-bs-toggle="modal" 
                    data-bs-target="#contactModal"                 
                    >
                        Contact
                    </a>
                    <span class="mx-1">&middot;</span>

                    <a href="login.php"
                    class="text-decoration-none"
                    style="color:#d63384">
                        Admin
                    </a>
                </div>

            </div>
        </div>
    </footer>

</body>

    <script>
        document.getElementById("year").textContent = new Date().getFullYear();
       // Keep input focused and empty after page load
        window.addEventListener('load', function() {
            var input = document.getElementById('id_number');
            if (input) { input.value = ''; input.focus(); }
        });
    // optional: enter key submits naturally since it's a form
    </script>

    <script src="assets/bootstrap.min.js"></script>
    <script src="js/clock.js"></script>    
</html>
