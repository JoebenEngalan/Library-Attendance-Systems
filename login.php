<!DOCTYPE html>
<?php
session_start();
error_reporting(0);
include "includes/config.php";
?>

<!-- Display error message if exists --> 
<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?= $_SESSION['error']; ?>
    </div>
<?php unset($_SESSION['error']); endif; ?>

<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>MonCast Learning Resource Center</title>
		<link rel="icon" type="image/x-icon" href="assets/img/favicon.ico"/>
        <link href="css/styles.css" rel="stylesheet"/>
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="masthead" 
            style=" 
             background-image: url('assets/img/bg-masthead.jpg'); 
             background-size: cover; 
             background-position: center; 
             background-repeat: no-repeat;"
             >
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-5">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                   <div class="card-header text-center">
                                        <img src="assets/img/android-chrome-512x512.png"
                                            alt="MonCast Emblem"
                                            class="img-fluid my-3"
                                            style="max-width: 100px;">
                                        <div class="fw-light">
                                            MonCast Learning Resource Center
                                        </div>
                                        
                                    </div>

                                    <div class="card-body">
                                        <form method="POST" action="pages/login.php">

                                            <div class="form-floating mb-3">
                                                <input class="form-control"
                                                    id="inputUsername"
                                                    name="username"
                                                    type="text"
                                                    placeholder="Username"
                                                    required />
                                                <label for="inputUsername">Username</label>
                                            </div>

                                            <div class="form-floating mb-3 position-relative">
                                                <input class="form-control"
                                                    id="inputPassword"
                                                    name="password"
                                                    type="password"
                                                    placeholder="Password"
                                                    required
                                                />
                                                <label for="inputPassword">Password</label>

                                                <!-- 👁 Show / Hide Button -->
                                                <span 
                                                    class="position-absolute top-50 end-0 translate-middle-y me-3"
                                                    style="cursor:pointer;"
                                                    onclick="togglePassword()"
                                                >
                                                    <i id="toggleIcon" class="fas fa-eye"></i>
                                                </span>          
                                            </div>
                                            
                                            <!-- Caps Lock Warning -->
                                            <div id="capsWarning" class="alert alert-warning mt-1 py-1 px-2 d-none" role="alert" style="font-size:0.875rem;">
                                                ⚠️ Caps Lock is ON
                                            </div>

                                            <div class="d-flex justify-content-end gap-2 mt-4 mb-0">
                                                <a href="index.php" class="btn btn-secondary">Cancel</a>

                                                <button type="submit"
                                                        name="login"
                                                        class="btn btn-primary"
                                                        style="background-color:#d63384;border-color:#d63384;">
                                                    Login
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="card-footer text-center py-3">
                                        <div class="small">Need an account? Contact the Librarian!</div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
            <div id="layoutAuthentication_footer">
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">MonCast Learning Resource Center &copy; Your Website <span id="year"></span></div>
                            <div>
                                <a href="index.php" style="color: #d63384">Main Page</a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('inputPassword');
            const icon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        // Ensure password is hidden when input loses focus
        document.getElementById('inputPassword').addEventListener('blur', function () {
            this.type = 'password';
            document.getElementById('toggleIcon').className = 'fas fa-eye';
        });

        // Toggle Show/Hide Password
        function togglePassword() {
            const passwordInput = document.getElementById('inputPassword');
            const icon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Caps Lock detection
        function togglePassword() {
            const passwordInput = document.getElementById('inputPassword');
            const icon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Caps Lock detection
        const passwordInput = document.getElementById('inputPassword');
        const capsWarning = document.getElementById('capsWarning');

        function checkCapsLock(e) {
            if (e.getModifierState && e.getModifierState('CapsLock')) {
                capsWarning.classList.remove('d-none');
            } else {
                capsWarning.classList.add('d-none');
            }
        }

        passwordInput.addEventListener('keydown', checkCapsLock);
        passwordInput.addEventListener('keyup', checkCapsLock);
        
        // Set current year in footer
        document.getElementById("year").textContent = new Date().getFullYear();                                
        </script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>
