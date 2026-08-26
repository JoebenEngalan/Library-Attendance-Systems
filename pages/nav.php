<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">

    <!-- Brand -->
    <a class="navbar-brand ps-3" href="admin.php">
        <span class="d-none d-md-inline">MonCast Learning Resource Center</span>
        <span class="d-inline d-md-none">MonCast LRC</span>
    </a>

    <!-- Push right content -->
    <div class="ms-auto d-flex align-items-center pe-3">

        <ul class="navbar-nav d-flex flex-row align-items-center">

            <!-- Sidebar Toggle -->
            <li class="nav-item me-2">
                <button class="btn btn-link btn-sm p-2" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </li>

            <!-- User Dropdown -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" 
                   id="navbarDropdown" href="#" role="button" 
                   data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                </a>

                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] !== 'User'): ?>
                    <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                    <li><a class="dropdown-item" href="activitylog.php">Activity Log</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <?php endif; ?>
                    <li>
                        <a class="dropdown-item text-primary" href="index.php" target="_blank" rel="noopener noreferrer">
                            <i class="fa-solid fa-house me-1"></i> Landing Page
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item text-danger" href="pages/logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    </li>
                    
                </ul>
            </li>

        </ul>

    </div>

</nav>
