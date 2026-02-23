<!DOCTYPE html>
<?php
session_start();
error_reporting(0);
include "includes/config.php";
require_once "includes/session_check.php";
// Access control: Only Admin and Main Admin can access this page
$allowedRoles = ['Admin', 'Main Admin'];
// Access control: Only Admin and Main Admin can access this page
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles, true)) {
    echo "<script>
        alert('Access denied.');
        window.location.href = '../admin.php';
    </script>";
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
                        <h1 class="mt-4">Settings</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="admin.php" style="color: #d63384" >Dashboard</a></li>
                            <li class="breadcrumb-item active">Settings</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fa-solid fa-file-spreadsheet"></i>
                                Settings
                            </div>
                            <div class="card-body">
                               
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
