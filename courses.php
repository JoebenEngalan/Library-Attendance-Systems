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
							<!-- Courses List -->
							<div class="col-12">
								<div class="card mb-3">
									<div class="card-header d-flex justify-content-between align-items-center">
										<span>
											<i class="fa-solid fa-table-list"></i> Courses List
										</span>

										<button type="button"
												class="btn btn-primary btn-sm"
												data-bs-toggle="modal"
												data-bs-target="#courseModal"
												onclick="resetCourseModal()">
											<i class="fa-solid fa-plus"></i>
											<span class="d-none d-sm-inline">Add New Course</span>
										</button>
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
																<button type="button"
																	class="btn btn-sm text-white edit_course"
																	style="background-color: <?php echo htmlspecialchars($row->color); ?>;"
																	title="Edit course"
																	data-bs-toggle="modal"
																	data-bs-target="#courseModal"
																	data-id="<?php echo htmlspecialchars($row->id); ?>"
																	data-name="<?php echo htmlspecialchars($row->Cname); ?>"
																	data-abv="<?php echo htmlspecialchars($row->abv); ?>"
																	data-color="<?php echo htmlspecialchars($row->color); ?>"
																	onclick="openCourseModal(this)">

																	<i class="fa-solid fa-pen-to-square"></i>
																</button>
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

						<!-- Course Modal -->
						<div class="modal fade" id="courseModal" data-bs-backdrop="static" data-bs-keyboard="false"
							 tabindex="-1" aria-labelledby="courseModalLabel" aria-hidden="true">

							<div class="modal-dialog modal-dialog-centered">
								<form class="modal-content" method="post"
									  style="border-radius:10px; border:none; box-shadow:0 10px 30px rgba(0,0,0,0.3);">

									<div class="modal-body" style="padding:30px;">

										<!-- Icon + Title -->
										<div style="text-align:center; margin-bottom:15px;">
											<div style="font-size:44px; margin-bottom:8px;">📚</div>
											<h3 style="margin:0; color:#0d6efd;" id="courseModalLabel">Add New Course</h3>
										</div>

										<p style="color:#555; font-size:14px; margin-bottom:15px; text-align:center;" id="courseModalDesc">
											Add a new course to the system.
										</p>

										<input type="hidden" name="Cid" id="Cid" value="<?php echo htmlspecialchars($id); ?>">

										<div style="margin-bottom:12px;">
											<label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
												Course Fullname
											</label>
											<input class="form-control"
												name="CourseN"
												id="CourseN"
												type="text"
												autocomplete="off"
												required
												onkeydown="return /[a-zA-Z]/i.test(event.key)"
												value="<?php echo htmlspecialchars($cname); ?>"
												style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;"/>
										</div>

										<div style="margin-bottom:12px;">
											<label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
												Course Abbreviation
											</label>
											<input class="form-control"
												name="Courseabv"
												id="Courseabv"
												type="text"
												autocomplete="off"
												required
												onkeydown="return /[a-zA-Z]/i.test(event.key)"
												value="<?php echo htmlspecialchars($abv); ?>"
												style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;"/>
										</div>

										<div style="margin-bottom:5px;">
											<label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
												Course Color
											</label>

											<div class="d-flex align-items-center gap-3">
												<input type="color"
													id="CourseColor"
													name="CourseColor"
													value="<?php echo htmlspecialchars($color); ?>"
													style="width:50px;height:42px; flex-shrink:0; border:1px solid #ccc; border-radius:6px; padding:2px;"
													required>

												<input type="text"
													id="CourseColorText"
													value="<?php echo htmlspecialchars($color); ?>"
													readonly
													style="flex:1; min-width:0; padding:8px; border:1px solid #ccc; border-radius:6px;">

												<span id="CourseColorPreview" style="width:42px;height:42px; flex-shrink:0;
															background-color:<?php echo htmlspecialchars($color); ?>;
															border-radius:6px;
															border:1px solid #ccc;">
												</span>
											</div>
										</div>

									</div>

									<!-- Buttons -->
									<div style="display:flex; gap:10px; justify-content:center; padding:0 30px 30px;">

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

										<!-- Clear (reset session, matches existing behavior) -->
										<button type="submit"
												name="reset_all"
												class="btn"
												style="
													padding:10px 25px;
													background:#dc3545;
													color:white;
													border:none;
													border-radius:5px;
													cursor:pointer;
													font-size:14px;">
											↺ Clear
										</button>

										<!-- Save (Add mode) -->
										<button type="submit"
												name="CSubmit"
												value="CSubmit"
												id="courseSaveBtn"
												class="btn"
												style="
													padding:10px 25px;
													background:#0d6efd;
													color:white;
													border:none;
													border-radius:5px;
													cursor:pointer;
													font-size:14px;">
											✔ Save Course
										</button>

										<!-- Update (Edit mode) -->
										<button type="submit"
												name="UpdateCourse"
												value="Edit"
												id="courseUpdateBtn"
												class="btn"
												style="
													padding:10px 25px;
													background:#198754;
													color:white;
													border:none;
													border-radius:5px;
													cursor:pointer;
													font-size:14px;
													display:none;">
											✔ Update Course
										</button>

									</div>

								</form>
							</div>
						</div>

						<script>
							// Toggle Save vs Update button, and modal title/description,
							// depending on whether we're adding or editing a course.
							function setCourseModalMode(isEdit) {
								document.getElementById('courseSaveBtn').style.display = isEdit ? 'none' : 'inline-block';
								document.getElementById('courseUpdateBtn').style.display = isEdit ? 'inline-block' : 'none';
								document.getElementById('courseModalLabel').textContent = isEdit ? 'Edit Course' : 'Add New Course';
								document.getElementById('courseModalDesc').textContent = isEdit
									? 'Update this course\'s details.'
									: 'Add a new course to the system.';
							}

							function resetCourseModal() {
								document.getElementById('Cid').value = '';
								document.getElementById('CourseN').value = '';
								document.getElementById('Courseabv').value = '';
								document.getElementById('CourseColor').value = '#6c757d';
								document.getElementById('CourseColorText').value = '#6c757d';
								document.getElementById('CourseColorPreview').style.backgroundColor = '#6c757d';
								setCourseModalMode(false);
							}

							function openCourseModal(el) {
								document.getElementById('Cid').value = el.dataset.id;
								document.getElementById('CourseN').value = el.dataset.name;
								document.getElementById('Courseabv').value = el.dataset.abv;
								document.getElementById('CourseColor').value = el.dataset.color;
								document.getElementById('CourseColorText').value = el.dataset.color;
								document.getElementById('CourseColorPreview').style.backgroundColor = el.dataset.color;
								setCourseModalMode(true);
							}

							// Keep the color swatch/text in sync when the color picker changes
							document.getElementById('CourseColor').addEventListener('input', function () {
								document.getElementById('CourseColorText').value = this.value;
								document.getElementById('CourseColorPreview').style.backgroundColor = this.value;
							});

							<?php if (!empty($id)): ?>
							// Page was loaded in edit mode via ?editid=... — open the modal pre-filled
							document.addEventListener('DOMContentLoaded', function () {
								setCourseModalMode(true);
								var modal = new bootstrap.Modal(document.getElementById('courseModal'));
								modal.show();
							});
							<?php endif; ?>
						</script>
					
					</div>															
					<?php include('pages/course.php');?>

                </main>
				<?php include('pages/footer.php');?>
            </div>
        </div>
        <?php include('pages/scripts.php');?>	

	</body>
</html>