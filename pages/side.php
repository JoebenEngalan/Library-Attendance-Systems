<div id="layoutSidenav_nav">
	<nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
		<div class="sb-sidenav-menu">
			<div class="nav">
				<div class="sb-sidenav-menu-heading">Main Page</div>
				<a class="nav-link" href="admin.php">
					<div class="sb-nav-link-icon"><i class="fa-duotone fa-regular fa-house"></i></i></div>								
					Dashboard
				</a>		
				<a class="nav-link" href="attendance.php">
					<div class="sb-nav-link-icon"><i class="fa-duotone fa-clipboard-list"></i></div>
					Attendance List
				</a>
				<a class="nav-link" href="records.php">
					<div class="sb-nav-link-icon"><i class="fa-duotone fa-address-book"></i></div>
					Student Records
				</a>
				
				<div class="sb-sidenav-menu-heading">Analytics</div>
				<a class="nav-link" href="charts.php">
					<div class="sb-nav-link-icon"><i class="fa-duotone fa-regular fa-file-chart-column"></i></div>
					Reports & Charts
				</a>

				<?php if (isset($_SESSION['role']) && $_SESSION['role'] !== 'User'): ?>
				
				<div class="sb-sidenav-menu-heading">Admin Controls</div>
				

				<a class="nav-link" href="activitylog.php">
					<div class="sb-nav-link-icon">
						<i class="fa-duotone fa-solid fa-table-tree"></i>						
					</div>
					Activity Log
				</a>
				
				<a class="nav-link" href="settings.php">
					<div class="sb-nav-link-icon">
						<i class="fa-duotone fa-gears"></i>
					</div>
					Settings 
				</a>

				<?php endif; ?>		
			</div>
		</div>
		<div class="sb-sidenav-footer">
			<div class="small">Logged in as:</div>
			<?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Guest'); ?>
		</div>
	</nav>
</div>