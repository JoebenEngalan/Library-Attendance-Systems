<!DOCTYPE html>
<?php 
session_start();
error_reporting(0);
include('includes/config.php');
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

// Get course details for editing (if applicable)
$id = isset($_GET['editid']) ? $_GET['editid'] : '';
$cname = isset($_GET['edit']) ? $_GET['edit'] : '';
$abv = isset($_GET['abv']) ? $_GET['abv'] : '';

// Default color
$color = '#6c757d';
// If editing, get the color from URL
if (isset($_GET['color'])) {
    $color = $_GET['color'];
}

?>

<html lang="en"> 
	<?php include('pages/head.php');?>

	<!-- Page content above -->
	<script src="js/course.js"></script>

    <body class="sb-nav-fixed">
		<?php include('pages/nav.php');?>
        <div id="layoutSidenav">
			<?php include('pages/side.php');?>
            <div id="layoutSidenav_content">
                <main>
					
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Courses</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="admin.php" style="color: #d63384" >Dashboard</a></li>
                            <li class="breadcrumb-item active">Courses</li>
                        </ol>
				
						<div class="row">
							<!-- Courses Form -->
							<div class="col-12 col-md-4">
								<div class="card mb-3">

									<div class="card-header d-flex align-items-center justify-content-between">
										<span>
											<i class="fa-solid fa-file-circle-plus me-1"></i>
											Courses Form
										</span>

										<!-- Collapse Toggle (Right Side) -->
										<button class="btn btn-sm btn-outline-secondary"
												type="button"
												data-bs-toggle="collapse"
												data-bs-target="#courseFormCollapse"
												aria-expanded="true"
												aria-controls="courseFormCollapse">
											<i class="fa-solid fa-chevron-down"></i>
										</button>
									</div>

									<!-- Collapsible Body -->
									<div id="courseFormCollapse" class="collapse show">
										<div class="card-body">

											<form class="forms-sample form-horizontal" method="post">

												<input type="hidden" name="Cid" value="<?php echo htmlspecialchars($id); ?>">

												<div class="form-floating mb-3">
													<input class="form-control"
														name="CourseN"
														id="CourseN"
														type="text"
														autocomplete="off"
														required
														onkeydown="return /[a-zA-Z]/i.test(event.key)"
														value="<?php echo htmlspecialchars($cname); ?>"/>
													<label for="CourseN">Course Fullname</label>
												</div>

												<div class="form-floating mb-3">
													<input class="form-control"
														name="Courseabv"
														id="Courseabv"
														type="text"
														autocomplete="off"
														required
														onkeydown="return /[a-zA-Z]/i.test(event.key)"
														value="<?php echo htmlspecialchars($abv); ?>"/>
													<label for="Courseabv">Course Abbreviation</label>
												</div>

												<div class="mb-3">
													<label class="form-label">Course Color</label>

													<div class="d-flex align-items-center gap-3 flex-wrap">
														<input type="color"
															class="form-control form-control-color"
															name="CourseColor"
															value="<?php echo htmlspecialchars($color); ?>"
															style="width:60px;height:45px;"
															required>

														<input type="text"
															class="form-control"
															value="<?php echo htmlspecialchars($color); ?>"
															readonly
															style="max-width:120px;">

														<span style="width:45px;height:45px;
																	background-color:<?php echo htmlspecialchars($color); ?>;
																	border-radius:6px;
																	border:1px solid #ccc;">
														</span>
													</div>
												</div>

												<div class="d-flex flex-wrap gap-2 justify-content-end">
													<button class="btn btn-danger btn-sm"
															name="reset_all"
															type="reset">
														<i class="fa-regular fa-arrow-rotate-right"></i>
														<span class="d-none d-sm-inline">Clear</span>
													</button>

													<button class="btn btn-primary btn-sm"
															name="CSubmit"
															value="CSubmit"
															type="submit">
														<i class="fa-solid fa-arrow-down-from-arc"></i>
														<span class="d-none d-sm-inline">Save</span>
													</button>

													<button class="btn btn-success btn-sm"
															name="UpdateCourse"
															value="Edit"
															type="submit">
														<i class="fa-solid fa-pen-to-square"></i>
														<span class="d-none d-sm-inline">Edit</span>
													</button>
												</div>
											</form>
										</div>
									</div>
								</div>
							</div>

							<!-- Courses List -->
							<div class="col-12 col-md-8">
								<div class="card mb-3">
									<div class="card-header">
										<i class="fa-solid fa-table-list"></i> Courses List
									</div>
									<div class="card-body">
										<div class="table-responsive">
											<table id="coursetable" class="table table-bordered table-striped mb-0">
												<thead class="table-dark">
													<tr>
														<th class="text-start">#</th>
														<th class="text-center">Course</th>
														<th class="text-center">Abbreviation</th>
														<th class="text-center">Color</th>
														<th class="text-center">Action</th>
													</tr>
												</thead>
												<tbody>
													<?php
													$sql = "SELECT id, Cname, abv, color FROM coursetbl ORDER BY id DESC";
													$query = $dbh->prepare($sql);
													$query->execute();
													$results=$query->fetchAll(PDO::FETCH_OBJ);
													$cnt=1;
													if($query->rowCount() > 0) {
														foreach($results as $row) { 
													?>  
														<tr>
															<td class="text-start"><?php echo htmlentities($cnt);?></td>
															<td><?php echo htmlentities($row->Cname);?></td>
															<td class="text-center"><?php echo htmlentities($row->abv);?></td>
															<td class="text-center">
																<span
																	style="
																		display:inline-block;
																		width:24px;
																		height:24px;
																		background-color: <?php echo htmlentities($row->color); ?>;
																		border-radius: 4px;
																		border: 1px solid #ccc;
																	"
																	title="<?php echo htmlentities($row->color); ?>">
																</span>
															</td>
															<td class="text-center">
																<a href="?editid=<?php echo urlencode($row->id); ?>
																	&edit=<?php echo urlencode($row->Cname); ?>
																	&abv=<?php echo urlencode($row->abv); ?>
																	&color=<?php echo urlencode($row->color); ?>"
																	class="btn btn-sm text-white"
																	style="background-color: <?php echo htmlspecialchars($row->color); ?>;"
																	title="Edit course">

																	<i class="fa-solid fa-pen-to-square"></i>
																</a>
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
							</div>              
						</div>
					
					</div>															
					<?php include('pages/course.php');?>

                </main>
				<?php include('pages/footer.php');?>
            </div>
        </div>
        <?php include('pages/scripts.php');?>	

	</body>
</html>
