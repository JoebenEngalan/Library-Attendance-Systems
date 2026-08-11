<!DOCTYPE html>
<?php
session_start();
error_reporting(0);
include "includes/config.php";

// Include session check to ensure user is authenticated before accessing records.php
require_once "includes/session_check.php";

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include updateStud.php to handle student record updates
include 'pages/updateStud.php';
?>

<!-- Display success message if exists -->
<?php if (isset($_SESSION['message'])): ?>
<script>
    alert("<?php echo $_SESSION['message']; ?>");
</script>
<?php unset($_SESSION['message']); endif; ?>

<html lang="en"> 
	<?php include('pages/head.php');?>
    <body class="sb-nav-fixed">
		<?php include('pages/nav.php');?>
        <div id="layoutSidenav">
			<?php include('pages/side.php');?>
            <div id="layoutSidenav_content">
               <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Student Records</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="admin.php" style="color: #d63384" >Dashboard</a></li>
                            <li class="breadcrumb-item active">Student Records</li>
                        </ol>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Student Records
                            </div>
                            <?php include('pages/addStud.php');?>
                            <div class="card-body">

                                <!-- Buttons -->
                                <div class="d-flex flex-column flex-md-row align-items-stretch gap-3 mb-3">

                                    <!-- Add Student Button -->
                                    <button type="button"
                                        class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2"
                                        data-bs-toggle="modal"
                                        data-bs-target="#Addstudent"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="bottom"
                                        title="Add a new student profile">
                                        <i class="fa-duotone fa-regular fa-user-plus"></i>
                                        <span class="d-none d-sm-inline">Add Student Profile</span>
                                    </button>

                                </div>

                                <!-- Student Records Table -->
                                <div class="table-responsive">
                                    <table id="recordTable" name="recordTable" class="table table-striped table-hover text-center mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th class="text-center">Student ID</th>
                                                <th class="text-start">Last Name</th>
                                                <th class="text-start">First Name</th>
                                                <th class="text-center">Year Level</th>
                                                <th class="text-center">Course</th>
                                                <th>Status</th>
                                                <th>Updated At</th>
                                                <th>Remarks</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                            <tbody>
                                            <!-- Fetch student records from database and display in table -->
                                            <?php
                                            $sql = "SELECT  id, studID,Lname,Fname, Course,YearLevel, remarks,status,updated_at 
                                                    FROM studtbl";
                                            $query = $dbh->prepare($sql);
                                            $query->execute();
                                            $results = $query->fetchAll(PDO::FETCH_OBJ);

                                            if ($query->rowCount() > 0) {
                                                foreach ($results as $row) {
                                            ?>
                                                <tr>
                                                    <td><?php echo htmlentities($row->studID); ?></td>
                                                    <td class="text-start"><?php echo htmlentities($row->Lname); ?></td>
                                                    <td class="text-start"><?php echo htmlentities($row->Fname); ?></td>
                                                    <td><?php echo htmlentities($row->YearLevel); ?></td>
                                                    <td><?php echo htmlentities($row->Course); ?></td>

                                                    <!-- STATUS (Badge Style) -->
                                                    <td>
                                                        <?php
                                                        $badge = match($row->status) {
                                                            'ACTIVE'     => 'success',
                                                            'SUSPENDED'  => 'danger',
                                                            'INACTIVE'   => 'secondary',
                                                            'GRADUATED'  => 'primary',
                                                            default      => 'dark'
                                                        };
                                                        ?>
                                                        <span class="badge bg-<?php echo $badge; ?>">
                                                            <?php echo htmlentities($row->status); ?>
                                                        </span>
                                                    </td>

                                                    <!-- UPDATED AT -->
                                                    <td>
                                                        <?php echo $row->updated_at 
                                                            ? date("M d, Y h:i A", strtotime($row->updated_at)) 
                                                            : '-'; ?>
                                                    </td>

                                                    <!-- REMARKS -->
                                                    <td><?php echo htmlentities($row->remarks ?? '-'); ?></td>

                                                    <!-- ACTION -->
                                                    <td>
                                                        <a href="#"
                                                        class="edit_data4 btn btn-sm btn-secondary"
                                                        data-id="<?php echo $row->id; ?>"
                                                        data-studid="<?php echo $row->studID; ?>"
                                                        data-lname="<?php echo $row->Lname; ?>"
                                                        data-fname="<?php echo $row->Fname; ?>"
                                                        data-course="<?php echo $row->Course; ?>"
                                                        data-year="<?php echo $row->YearLevel; ?>"
                                                        data-remarks="<?php echo $row->remarks; ?>"
                                                        data-status="<?php echo $row->status; ?>"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#EditSudent">
                                                            <i class="fa-duotone fa-solid fa-pen-to-square"></i>
                                                            <span class="d-none d-sm-inline"> Edit</span>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php
                                                }
                                            } else {
                                                echo '<tr><td colspan="9" class="text-center">No records found</td></tr>';
                                            }
                                            ?>
                                            </tbody>

                                    </table>
                                </div>

                            </div>                                        
                        
                        </div>

                        <!-- Edit Student Modal -->                        
                        <div class="modal fade" id="EditSudent" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="editmodal" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

                                    <!-- Gradient Header -->
                                    <div class="modal-header border-0 text-white"
                                         style="background: linear-gradient(135deg, #5678f5 0%, #7c5cf0 100%); padding: 1.5rem 1.75rem;">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="d-flex align-items-center justify-content-center"
                                                 style="width:48px; height:48px; background:rgba(255,255,255,0.2); border-radius:50%;">
                                                <i class="fa-duotone fa-solid fa-user-pen fs-4"></i>
                                            </div>
                                            <div>
                                                <h1 class="modal-title fs-5 mb-0" id="editmodal">Edit Student Profile</h1>
                                                <div class="small opacity-75">Update this student's information</div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body p-4" style="background:#f9fafc;">
                                        <form class="forms-sample" method="POST" action="">

                                            <!-- Hidden ID -->
                                            <input type="hidden" name="id" id="edit_id">

                                            <!-- Section: Identity -->
                                            <div class="bg-white rounded-3 p-3 mb-3 shadow-sm">
                                                <div class="text-uppercase text-muted small fw-semibold mb-3 d-flex align-items-center gap-2">
                                                    <i class="fa-duotone fa-solid fa-id-card"></i> Identity
                                                </div>

                                                <!-- Student ID -->
                                                <div class="input-group input-group-lg mb-3">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fa-duotone fa-solid fa-hashtag text-muted"></i>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input class="form-control border-start-0" name="edstudid" id="edstudid" type="text" required
                                                            placeholder="Student ID"
                                                            onkeydown="return /[a-zA-Z0-9]/i.test(event.key) || ['Backspace',' ','Delete','ArrowLeft','ArrowRight'].includes(event.key)">
                                                        <label for="edstudid">Student / Visitor ID</label>
                                                    </div>
                                                </div>

                                                <!-- Last Name -->
                                                <div class="input-group input-group-lg mb-3">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fa-duotone fa-solid fa-signature text-muted"></i>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input class="form-control border-start-0" name="LName" id="LName" type="text" required
                                                            placeholder="Last Name"
                                                            onkeydown="return /[a-zA-Z]/i.test(event.key) || ['Backspace',' ','Delete','ArrowLeft','ArrowRight','-'].includes(event.key)">
                                                        <label for="LName">Last Name</label>
                                                    </div>
                                                </div>

                                                <!-- First Name -->
                                                <div class="input-group input-group-lg">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fa-duotone fa-solid fa-signature text-muted"></i>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input class="form-control border-start-0" name="FName" id="FName" type="text" required
                                                            placeholder="First Name"
                                                            onkeydown="return /[a-zA-Z]/i.test(event.key) || ['Backspace',' ','Delete','ArrowLeft','ArrowRight','-'].includes(event.key)">
                                                        <label for="FName">First Name</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section: Academic Info -->
                                            <div class="bg-white rounded-3 p-3 mb-3 shadow-sm">
                                                <div class="text-uppercase text-muted small fw-semibold mb-3 d-flex align-items-center gap-2">
                                                    <i class="fa-duotone fa-solid fa-graduation-cap"></i> Academic Info
                                                </div>

                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label for="editcourse" class="form-label small text-muted mb-1">Course</label>
                                                        <select class="form-select form-select-lg" id="editcourse" name="editcourse" required>
                                                            <option value="" disabled>Course</option>
                                                            <!-- Dynamically populate courses from coursetbl -->
                                                            <?php
                                                            $sql = "SELECT * FROM coursetbl";
                                                            $query = $dbh->prepare($sql);
                                                            $query->execute();
                                                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                            foreach ($results as $row) {
                                                                echo '<option value="'.$row->abv.'">'.$row->abv.'</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label for="edityearlevel" class="form-label small text-muted mb-1">Year Level</label>
                                                        <select class="form-select form-select-lg" id="edityearlevel" name="edityearlevel" required>
                                                            <option value="">Year Level</option>
                                                            <option value="1st Year">1st Year</option>
                                                            <option value="2nd Year">2nd Year</option>
                                                            <option value="3rd Year">3rd Year</option>
                                                            <option value="4th Year">4th Year</option>
                                                            <option value="None">None</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section: Status -->
                                            <div class="bg-white rounded-3 p-3 mb-3 shadow-sm">
                                                <div class="text-uppercase text-muted small fw-semibold mb-3 d-flex align-items-center gap-2">
                                                    <i class="fa-duotone fa-solid fa-circle-check"></i> Status
                                                </div>

                                                <!-- STATUS -->
                                                <div class="form-floating mb-3">
                                                    <select class="form-select" name="status" id="editstatus" required>
                                                        <option value="ACTIVE">🟢 ACTIVE</option>
                                                        <option value="SUSPENDED">🟠 SUSPENDED</option>
                                                        <option value="INACTIVE">⚪ INACTIVE</option>
                                                        <option value="GRADUATED">🎓 GRADUATED</option>
                                                    </select>
                                                    <label for="editstatus">Status</label>
                                                </div>

                                                <!-- REMARKS -->
                                                <div class="form-floating">
                                                    <textarea class="form-control" name="remarks" id="editremarks"
                                                            placeholder="Remarks" style="height: 90px"></textarea>
                                                    <label for="editremarks">Remarks</label>
                                                </div>
                                            </div>

                                            <div class="modal-footer border-0 px-0 pb-0">
                                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
                                                    <i class="fa-duotone fa-solid fa-xmark me-1"></i> Close
                                                </button>
                                                <button type="submit" name="updateStudent" class="btn btn-primary d-flex align-items-center gap-2"
                                                        style="background: linear-gradient(135deg, #5678f5 0%, #7c5cf0 100%); border:none;">
                                                    <i class="fa-duotone fa-solid fa-floppy-disk"></i> Save Changes
                                                </button>
                                            </div>

                                        </form>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </main>
                <?php include('pages/footer.php');?>
            </div>
        <?php include('pages/scripts.php');?>
        <script src="js/records.js"></script>                
    </body>
</html>
