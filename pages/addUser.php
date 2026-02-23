<?php
session_start();
error_reporting(0);
include "includes/config.php";

if (isset($_POST['AddUser'])) {

    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $fname     = trim($_POST['firstname'] ?? '');
    $lname     = trim($_POST['lastname'] ?? '');
    $role      = $_POST['role'] ?? '';

    // Merge fullname
    $fullname = $fname . ' ' . $lname;

    if (empty($username) || empty($password) || empty($fname) || empty($lname)) {
        header("Location: activitylog.php?error=1");
        exit;
    } else {

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, password, fullname, role, created_at)
                VALUES (:username, :password, :fullname, :role, NOW())";

        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed);
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            /* ✅ SUCCESS */
            $_SESSION['success'] = "User added successfully.";
            /* ✅ CLEAR SESSION FORM DATA */
            unset($_SESSION['form_data']);
            /* ✅ PREVENT RESUBMIT */
            header("Location: activitylog.php?success=1");
            echo "<script>alert('User added successfully'); window.location.href ='activitylog.php'</script>";
            exit;

        } else {
            $_SESSION['error'] = "Failed to add user.";
            unset($_SESSION['form_data']);
            header("Location: activitylog.php?error=1");
            echo "<script>alert('Failed to add user successfully'); window.location.href ='activitylog.php'</script>";
            exit;
        }
    }
}
?>


<div class="modal fade" id="AddUserModal" data-bs-backdrop="static" data-bs-keyboard="false"
     tabindex="-1" aria-labelledby="AddUserModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="AddUserModalLabel">
                    Add User Account
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div id="addUserForm" class="modal-body">
                <form method="post">

                    <!-- Username -->
                    <div class="form-floating mb-3">
                        <input class="form-control"
                               name="username"
                               id="username"
                               type="text"
                               placeholder="Username"
                               autocomplete="off"
                               onkeydown="return /[a-zA-Z0-9@]/.test(event.key) || ['Backspace',' ','Delete','ArrowLeft','ArrowRight'].includes(event.key)"
                               required/>
                        <label for="username">Username</label>
                    </div>

                    <!-- First Name -->
                    <div class="form-floating mb-3">
                        <input class="form-control"
                               name="firstname"
                               id="firstname"
                               type="text"
                               placeholder="First Name"
                               autocomplete="off"
                               onkeydown="return /[a-zA-Z]/i.test(event.key) || ['Backspace',' ','Delete','ArrowLeft','ArrowRight'].includes(event.key)"
                               required/>
                        <label for="firstname">First Name</label>
                    </div>

                    <!-- Last Name -->
                    <div class="form-floating mb-3">
                        <input class="form-control"
                               name="lastname"
                               id="lastname"
                               type="text"
                               placeholder="Last Name"
                               autocomplete="off"
                               onkeydown="return /[a-zA-Z]/i.test(event.key) || ['Backspace',' ','Delete','ArrowLeft','ArrowRight'].includes(event.key)"
                               required/>
                        <label for="lastname">Last Name</label>
                    </div>

                    <!-- Password -->
                    <div class="form-floating mb-3 position-relative">
                        <input class="form-control"
                            name="password"
                            id="password"
                            type="password"
                            placeholder="Password"
                            autocomplete="off"
                            onkeydown="return /[a-zA-Z0-9]/.test(event.key)"
                            required/>

                        <label for="password">Password</label>

                        <!-- Show / Hide Button -->
                        <button type="button"
                                class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-2"
                                onclick="togglePassword(this)"
                                tabindex="-1">
                            <i class="fa-solid fa-eye"></i>
                        </button>

                    </div>

                    <!-- role -->
                    <div class="mb-3">
                        <select class="form-select form-select-lg" name="role" required>
                            <option value="Admin" selected>Admin</option>
                            <option value="User">User</option>
                        </select>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer px-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" name="AddUser" class="btn btn-primary">
                            Save User
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>
