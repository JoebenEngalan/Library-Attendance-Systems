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
        <div class="modal-content" style="border-radius:10px; border:none; box-shadow:0 10px 30px rgba(0,0,0,0.3);">

            <div class="modal-body" style="padding:30px;">

                <!-- Icon + Title -->
                <div style="text-align:center; margin-bottom:15px;">
                    <div style="font-size:44px; margin-bottom:8px;">👤</div>
                    <h3 style="margin:0; color:#0d6efd;" id="AddUserModalLabel">Add User Account</h3>
                </div>

                <p style="color:#555; font-size:14px; margin-bottom:15px; text-align:center;">
                    Create a new admin or user account for the system.
                </p>

                <form method="post" id="addUserForm">

                    <!-- Username -->
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                            Username
                        </label>
                        <input class="form-control"
                               name="username"
                               id="username"
                               type="text"
                               placeholder="Username"
                               autocomplete="off"
                               onkeydown="return /[a-zA-Z0-9@]/.test(event.key) || ['Backspace',' ','Delete','ArrowLeft','ArrowRight'].includes(event.key)"
                               required
                               style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;"/>
                    </div>

                    <!-- First Name -->
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                            First Name
                        </label>
                        <input class="form-control"
                               name="firstname"
                               id="firstname"
                               type="text"
                               placeholder="First Name"
                               autocomplete="off"
                               onkeydown="return /[a-zA-Z]/i.test(event.key) || ['Backspace',' ','Delete','ArrowLeft','ArrowRight'].includes(event.key)"
                               required
                               style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;"/>
                    </div>

                    <!-- Last Name -->
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                            Last Name
                        </label>
                        <input class="form-control"
                               name="lastname"
                               id="lastname"
                               type="text"
                               placeholder="Last Name"
                               autocomplete="off"
                               onkeydown="return /[a-zA-Z]/i.test(event.key) || ['Backspace',' ','Delete','ArrowLeft','ArrowRight'].includes(event.key)"
                               required
                               style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;"/>
                    </div>

                    <!-- Password -->
                    <div style="margin-bottom:12px; position:relative;">
                        <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                            Password
                        </label>
                        <input class="form-control"
                            name="password"
                            id="password"
                            type="password"
                            placeholder="Password"
                            autocomplete="off"
                            onkeydown="return /[a-zA-Z0-9]/.test(event.key)"
                            required
                            style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;"/>

                        <!-- Show / Hide Button -->
                        <button type="button"
                                class="btn btn-sm position-absolute end-0"
                                style="top:38px;"
                                onclick="togglePassword(this)"
                                tabindex="-1">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>

                    <!-- role -->
                    <div style="margin-bottom:5px;">
                        <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                            Role
                        </label>
                        <select class="form-select" name="role" required
                                style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
                            <option value="Admin" selected>Admin</option>
                            <option value="User">User</option>
                        </select>
                    </div>

                </form>

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
                            form="addUserForm"
                            name="AddUser"
                            class="btn"
                            style="
                                padding:10px 25px;
                                background:#0d6efd;
                                color:white;
                                border:none;
                                border-radius:5px;
                                cursor:pointer;
                                font-size:14px;">
                        ✔ Save User
                    </button>

                </div>

            </div>

        </div>
    </div>
</div>