<!DOCTYPE html>
<?php
session_start();
error_reporting(0);
include "includes/config.php";
require_once "includes/session_check.php";

$allowedRoles = ['Admin', 'Main Admin'];

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles, true)) {
    echo "<script>
        alert('Access denied.');
        window.location.href = '../admin.php';
    </script>";
    exit;
}


/* ===== USERS TABLE (CREATED vs UPDATED) ===== */
$usersUpdatedSql = "
    SELECT 
        MAX(COALESCE(updated_at, created_at)) AS last_updated
    FROM users
";

$usersUpdatedQuery = $dbh->prepare($usersUpdatedSql);
$usersUpdatedQuery->execute();
$usersUpdatedRow = $usersUpdatedQuery->fetch(PDO::FETCH_OBJ);

$usersLastUpdated = $usersUpdatedRow->last_updated
    ? date("F d, Y h:i A", strtotime($usersUpdatedRow->last_updated))
    : "No records yet";

/* ===== USER ACTIVITY TABLE ===== */
$activityUpdatedSql = "SELECT MAX(activity_time) AS last_updated FROM user_activity";
$activityUpdatedQuery = $dbh->prepare($activityUpdatedSql);
$activityUpdatedQuery->execute();
$activityUpdatedRow = $activityUpdatedQuery->fetch(PDO::FETCH_OBJ);

$activityLastUpdated = $activityUpdatedRow->last_updated
    ? date("F d, Y h:i A", strtotime($activityUpdatedRow->last_updated))
    : "No activity yet";

?>

<html lang="en"> 
	<?php include('pages/head.php');?>

    <script>
        document.addEventListener("DOMContentLoaded", function () {

            const modal = document.getElementById("changePasswordModal");
            const userIdInput = document.getElementById("passwordUserId");

            modal.addEventListener("show.bs.modal", function (event) {
                const button = event.relatedTarget; // the button that opened modal

                if (!button) {
                    console.error("No relatedTarget (button not found)");
                    return;
                }

                const userId = button.getAttribute("data-user-id");
                console.log("User ID received:", userId);

                userIdInput.value = userId;
            });

        });
    </script>

    <body class="sb-nav-fixed">
		<?php include('pages/nav.php');?>
        <div id="layoutSidenav">
			<?php include('pages/side.php');?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Activity Log</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="admin.php" style="color: #d63384" >Dashboard</a></li>
                            <li class="breadcrumb-item active">Activity log</li>
                        </ol>
                        
                        <div class="card mb-4">
                            <!-- Card Header -->
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <span>
                                    <i class="fa-solid fa-file-spreadsheet me-1"></i>
                                    <strong>Activity Log</strong>
                                </span>

                                <!-- Collapse Toggle (Right Side) -->
                                <button class="btn btn-sm btn-outline-secondary"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#activityLogCollapse"
                                        aria-expanded="true"
                                        aria-controls="activityLogCollapse">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                            </div>
                            <!-- Collapsible Body -->
                            <div id="activityLogCollapse" class="collapse show">
                                <div class="card-body p-0">

                                    <!-- Responsive Table -->
                                    <div class="table-responsive">
                                        <table id="activitytables" class="table table-striped table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>User ID</th>
                                                    <th>User Name</th>
                                                    <th>Activity Type</th>
                                                    <th>Description</th>
                                                    <th>Date &amp; Time</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    $sql = "SELECT * FROM user_activity ORDER BY activity_time DESC";
                                                    $query = $dbh->prepare($sql);
                                                    $query->execute();
                                                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                    $cnt = 1;

                                                    if ($query->rowCount() > 0) {
                                                        foreach ($results as $row) {
                                                ?>
                                                    <tr>
                                                        <td><?php echo htmlentities($cnt); ?></td>
                                                        <td><?php echo htmlentities($row->user_id); ?></td>
                                                        <td><?php echo htmlentities($row->username); ?></td>
                                                        <td><?php echo htmlentities($row->activity_type); ?></td>
                                                        <td><?php echo htmlentities($row->activity_description); ?></td>
                                                        <td><?php echo htmlentities($row->activity_time); ?></td>
                                                    </tr>
                                                <?php
                                                            $cnt++;
                                                        }
                                                    }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                            <!-- Optional Footer -->
                            <div class="card-footer text-muted small">
                                <i class="fa-regular fa-clock me-1"></i>
                                Updated: <?php echo htmlentities($activityLastUpdated); ?>
                            </div>
                        </div>


                        <div class="card mb-4">
                            <?php include('pages/addUser.php');?>                                                        
                            <!-- Card Header -->
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <span>
                                    <i class="fa-solid fa-file-spreadsheet me-1"></i>
                                    <strong>User Log</strong>
                                </span>

                                <!-- Collapse Toggle -->
                                <button class="btn btn-sm btn-outline-secondary"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#userLogCollapse"
                                        aria-expanded="false"
                                        aria-controls="userLogCollapse">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                            </div>
                            <!-- Collapsible Area (CLOSED BY DEFAULT) -->
                            <div id="userLogCollapse" class="collapse">

                                <!-- Add User Button (Now Collapsible) -->
                                <div class="px-3 pt-3">
                                    <button type="button"
                                            class="btn btn-primary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#AddUserModal"
                                            title="Add a new user">
                                        <i class="fa-solid fa-user-plus me-1"></i>
                                        Add User
                                    </button>
                                </div>

                               <div id="usersCollapse" class="collapse show">
                                    <div class="card-body p-2">

                                        <!-- Responsive Table -->
                                        <div class="table-responsive">
                                            <table id="usertables" class="table table-striped table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Username</th>
                                                        <th>Full Name</th> 
                                                        <th>Status</th>
                                                        <th>Created At</th>
                                                        <th>Updated At</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    <?php
                                                    $sql = "SELECT user_id, username, fullname, role, created_at, updated_at
                                                            FROM users
                                                            ORDER BY COALESCE(updated_at, created_at) DESC;";

                                                    $query = $dbh->prepare($sql);
                                                    $query->execute();
                                                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                    $cnt = 1;

                                                    if ($query->rowCount() > 0) {
                                                        foreach ($results as $row) {
                                                    ?>
                                                    <tr>
                                                        <td><?= $cnt; ?></td>
                                                        <td><?= htmlentities($row->username); ?></td>
                                                        <td><?= htmlentities($row->fullname); ?></td>
                                                        <td>
                                                            <?php
                                                                $badgeClass = match ($row->role) {
                                                                    'Main Admin' => 'danger',  // 🔴 Red
                                                                    'Admin'      => 'primary', // 🔵 Blue
                                                                    'User'       => 'success', // 🟢 Green
                                                                     default     => 'secondary'
                                                                };
                                                                ?>
                                                            <span class="badge bg-<?= $badgeClass ?>">
                                                                <?= htmlentities($row->role) ?>
                                                            </span>
                                                        </td>
                                                        <td><?= htmlentities($row->created_at); ?></td>
                                                        <td>
                                                            <?php
                                                                echo $row->updated_at ? date("F d, Y h:i A", strtotime($row->updated_at)) : '—';           
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                                $isMainAdminRow = ($row->role === 'Main Admin');
                                                                $isMainAdminSession = ($_SESSION['role'] === 'Main Admin');

                                                                $disabled = ($isMainAdminRow && !$isMainAdminSession);
                                                            ?>
                                                            <!-- CHANGE PASSWORD -->
                                                            <button
                                                                class="btn btn-sm btn-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#changePasswordModal"
                                                                data-user-id="<?= $row->user_id ?>"
                                                                <?= $disabled ? 'disabled title="Only Main Admin can modify this account"' : '' ?>
                                                            >
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <!-- DELETE USER -->
                                                            <!-- Prevent self-deletion -->
                                                            <?php if ($row->user_id != $_SESSION['user_id']) : ?>
                                                                
                                                                <button 
                                                                    class="btn btn-sm btn-danger"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#deleteUserModal"
                                                                    data-user-id="<?= $row->user_id ?>"
                                                                    data-fullname="<?= htmlspecialchars($row->fullname) ?>"
                                                                    data-username="<?= htmlspecialchars($row->username) ?>"
                                                                    <?= $disabled ? 'disabled title="Only Main Admin can delete this account"' : '' ?>
                                                                >
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            <?php endif; ?>

                                                        </td>
                                                    </tr>
                                                    <?php
                                                            $cnt++;
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-footer text-muted small">
                                    <i class="fa-regular fa-clock me-1"></i>
                                    Last updated: <?= htmlentities($usersLastUpdated) ?>
                                </div>

                            </div> 

                            <!-- Change Password Modal -->
                            <div class="modal fade" id="changePasswordModal" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <form method="POST" action="pages/changePassword.php" class="modal-content"
                                        style="border-radius:10px; border:none; box-shadow:0 10px 30px rgba(0,0,0,0.3);">

                                        <div class="modal-body" style="padding:30px;">

                                            <!-- Icon + Title -->
                                            <div style="text-align:center; margin-bottom:15px;">
                                                <div style="font-size:44px; margin-bottom:8px;">🔑</div>
                                                <h3 style="margin:0; color:#0d6efd;">Change Password</h3>
                                            </div>

                                            <p style="color:#555; font-size:14px; margin-bottom:15px; text-align:center;">
                                                Set a new password for this account.
                                            </p>

                                            <input type="hidden" name="user_id" id="passwordUserId">

                                            <div style="margin-bottom:5px; position:relative;">
                                                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                                                    New Password
                                                </label>

                                                <input
                                                    type="password"
                                                    name="change_new_password"
                                                    id="change_new_password"
                                                    onkeydown="return /[a-zA-Z0-9]+/i.test(event.key)"
                                                    required
                                                    style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;"/>

                                                <button
                                                    type="button"
                                                    class="btn btn-sm position-absolute end-0"
                                                    style="top:38px;"
                                                    onclick="toggleChangePassword(this)"
                                                    tabindex="-1">
                                                    <i class="fa-solid fa-eye"></i>
                                                </button>
                                            </div>

                                            <!-- Buttons -->
                                            <div style="display:flex; gap:10px; justify-content:center; margin-top:20px;">

                                                <!-- Cancel -->
                                                <button type="button"
                                                        class="btn"
                                                        data-bs-dismiss="modal"
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
                                                        name="changePassword"
                                                        class="btn"
                                                        style="
                                                            padding:10px 25px;
                                                            background:#0d6efd;
                                                            color:white;
                                                            border:none;
                                                            border-radius:5px;
                                                            cursor:pointer;
                                                            font-size:14px;">
                                                    ✔ Update Password
                                                </button>

                                            </div>

                                        </div>

                                    </form>
                                </div>
                            </div>

                            <!-- Delete User Modal -->
                            <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">

                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">Confirm Delete</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>

                                        <form method="POST" action="pages/deleteUser.php">
                                            <div class="modal-body">
                                                <input type="hidden" name="user_id" id="deleteUserId">

                                                <p>
                                                    ⚠️ You are about to delete the following user:
                                                </p>

                                                <ul class="list-group mb-3">
                                                    <li class="list-group-item">
                                                        <strong>Full Name:</strong>
                                                        <span id="deleteFullname"></span>
                                                    </li>
                                                    <li class="list-group-item">
                                                        <strong>Username:</strong>
                                                        <span id="deleteUsername"></span>
                                                    </li>
                                                </ul>

                                                <p class="text-danger mb-0">
                                                    <strong>This action cannot be undone.</strong>
                                                </p>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    Cancel
                                                </button>
                                                <button type="submit" name="deleteUser" class="btn btn-danger">
                                                    Yes, Delete
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
        </div>

        <?php include('pages/scripts.php');?>

        <script>

            document.getElementById('deleteUserModal').addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;

                document.getElementById('deleteUserId').value =
                    button.getAttribute('data-user-id');

                document.getElementById('deleteFullname').textContent =
                    button.getAttribute('data-fullname');

                document.getElementById('deleteUsername').textContent =
                    button.getAttribute('data-username');
            });

            function setPasswordUserId(userId) {
                document.getElementById('passwordUserId').value = userId;
            }

            function toggleChangePassword(btn) {
                const wrapper = btn.closest(".position-relative");
                const passwordInput = wrapper.querySelector("input");
                const icon = btn.querySelector("i");

                if (passwordInput.type === "password") {
                    passwordInput.type = "text";
                    icon.classList.replace("fa-eye", "fa-eye-slash");
                } else {
                    passwordInput.type = "password";
                    icon.classList.replace("fa-eye-slash", "fa-eye");
                }
            }

            document.getElementById('changePasswordModal')
            .addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-user-id');

                document.getElementById('passwordUserId').value = userId;
            });
       
            function togglePassword(btn) {
                const password = document.getElementById("password");
                const icon = btn.querySelector("i");

                if (password.type === "password") {
                    password.type = "text";
                    icon.classList.replace("fa-eye", "fa-eye-slash");
                } else {
                    password.type = "password";
                    icon.classList.replace("fa-eye-slash", "fa-eye");
                }
            }

            document.getElementById('changePasswordModal')
                .addEventListener('show.bs.modal', function (event) {

                    const button = event.relatedTarget;
                    const userId = button.getAttribute('data-user-id');

                    document.getElementById('passwordUserId').value = userId;
            });

            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById('AddUserModal');
                const form = document.getElementById('addUserForm');

                modal.addEventListener('hidden.bs.modal', function () {
                    form.reset();
                });
            });

            document.addEventListener("DOMContentLoaded", function() {
                // When the modal closes → reset all fields inside the form
                const editModal = document.getElementById('AddUserModal');
                editModal.addEventListener('hidden.bs.modal', function () {
                    // reset all inputs inside the modal
                    editModal.querySelector('form').reset();
                });
            });

        </script>

        <script>
            /* Prevent form resubmission on refresh */
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }

            document.addEventListener("DOMContentLoaded", function () {

                const modal = document.getElementById("AddUserModal");   // modal ID
                const form  = modal.querySelector("form");              // form inside modal

                /* RESET FORM WHEN MODAL CLOSES */
                modal.addEventListener("hidden.bs.modal", function () {
                    form.reset();

                    // Clear validation styles if any
                    form.querySelectorAll(".is-valid, .is-invalid").forEach(el => {
                        el.classList.remove("is-valid", "is-invalid");
                    });
                });

                /* OPTIONAL: Manual Reset Button */
                const resetBtn = document.getElementById("reset_all");

                if (resetBtn) {
                    resetBtn.addEventListener("click", function (e) {
                        e.preventDefault();

                        if (confirm("Are you sure you want to clear all fields?")) {
                            form.reset();
                        }
                    });
                }

            });
        </script>


    </body>
</html>
