<?php
session_start();
error_reporting(0);
include "includes/config.php";

/* ✅ Timezone */
date_default_timezone_set('Asia/Manila');

/* ✅ Current date and time */
$today = date("Y-m-d");
$current_time = date("H:i:s");
$current_time_12 = date("h:i A");
//echo "Current Date: $today, Current Time: $current_time_12";
// Check if form is submitted
if (isset($_POST['Add'])) {

    $studid = $_POST['studid'];

    // Proper-case Lname/Fname: 'JOSE' -> 'Jose', 'DELA CRUZ' -> 'Dela Cruz'
    $lname = ucwords(strtolower(trim($_POST['inputLName'])));
    $fname = ucwords(strtolower(trim($_POST['inputFName'])));

    $courses = $_POST['course'];
    $yearlevel = $_POST['yearlevel'];

    // Validate input fields
    if (empty($studid) || empty($lname) || empty($fname) || empty($courses) || empty($yearlevel)) {
        echo '<script>alert("Empty Fields. Please try again")</script>';
    } else {
        try {

            // Check duplicate student ID
            $checkSql = "SELECT COUNT(*) FROM studtbl WHERE studID = :studid";
            $checkQuery = $dbh->prepare($checkSql);
            $checkQuery->bindParam(':studid', $studid, PDO::PARAM_STR);
            $checkQuery->execute();
            $count = $checkQuery->fetchColumn();

            if ($count > 0) {

                echo '<script>alert("Student ID already exists! Please use a different ID.")</script>';

            } else {

                // Insert student
                $sql = "INSERT INTO studtbl (studID, Lname, Fname, Course, YearLevel)
                        VALUES (:studid, :inputLName, :inputFName, :course, :yearlevel)";
                $query = $dbh->prepare($sql);
                $query->bindParam(':studid', $studid, PDO::PARAM_STR);
                $query->bindParam(':inputLName', $lname, PDO::PARAM_STR);
                $query->bindParam(':inputFName', $fname, PDO::PARAM_STR);
                $query->bindParam(':course', $courses, PDO::PARAM_STR);
                $query->bindParam(':yearlevel', $yearlevel, PDO::PARAM_STR);
                $query->execute();

                /* ✅ Attendance merge using defined time */
                $fullname = $lname . ', ' . $fname;

                $checkAttend = "
                    SELECT attendance_id
                    FROM attendance
                    WHERE id_number = :id
                    AND DATE(date_in) = :today
                    AND time_out IS NULL
                ";

                $stmt = $dbh->prepare($checkAttend);
                $stmt->execute([
                    ':id' => $studid,
                    ':today' => $today
                ]);

                if ($stmt->rowCount() == 0) {

                    $attendanceSQL = "
                        INSERT INTO attendance
                        (id_number, fullname, course, yearlevel, date_in, time_in)
                        VALUES
                        (:id, :fullname, :course, :year, :today, :time)
                    ";

                    $stmt = $dbh->prepare($attendanceSQL);
                    $stmt->execute([
                        ':id' => $studid,
                        ':fullname' => $fullname,
                        ':course' => $courses,
                        ':year' => $yearlevel,
                        ':today' => $today,
                        ':time' => $current_time
                    ]);
                }
                echo "<script>alert('✅ $fullname, $studid of $courses, $yearlevel is now registered and Time in: $today, $current_time_12')</script>";
                echo "<script>window.location.href ='records.php'</script>";
            }

        } catch (PDOException $e) {
            echo '<script>alert("Error: '.$e->getMessage().'")</script>';
        }
    }
} 
?>

<!-- Add Student Modal -->
<div class="modal fade" id="Addstudent" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="Addmodal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <!-- Gradient Header -->
            <div class="modal-header border-0 text-white"
                 style="background: linear-gradient(135deg, #22b573 0%, #16a085 100%); padding: 1.5rem 1.75rem;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center"
                         style="width:48px; height:48px; background:rgba(255,255,255,0.2); border-radius:50%;">
                        <i class="fa-duotone fa-solid fa-user-plus fs-4"></i>
                    </div>
                    <div>
                        <h1 class="modal-title fs-5 mb-0" id="Addmodal">Add Student Profile</h1>
                        <div class="small opacity-75">Register a new student to the system</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4" style="background:#f9fafc;">
                <!-- Start of Form -->
                <form class="forms-sample" method="post">

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
                                <input class="form-control border-start-0" name="studid" id="studid" type="text" 
                                autocomplete="off" placeholder="Student ID" required 
                                onkeydown="return /[a-zA-Z0-9]+/i.test(event.key) || ['Delete','ArrowLeft','ArrowRight'].includes(event.key)"/>
                                <label for="studid">Student ID</label>
                            </div>
                        </div>

                        <!-- Last Name -->
                        <div class="input-group input-group-lg mb-3">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fa-duotone fa-solid fa-signature text-muted"></i>
                            </span>
                            <div class="form-floating">
                                <input class="form-control border-start-0" name="inputLName" id="inputLName" type="text" 
                                autocomplete="off" placeholder="Enter your last name" required 
                                onkeydown="return /[a-zA-Z]/i.test(event.key) || ['Backspace',' ','Delete','ArrowLeft','ArrowRight', '-'].includes(event.key)"/>
                                <label for="inputLName">Last Name</label>
                            </div>
                        </div>

                        <!-- First Name -->
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fa-duotone fa-solid fa-signature text-muted"></i>
                            </span>
                            <div class="form-floating">
                                <input class="form-control border-start-0" name="inputFName" id="inputFName" type="text" 
                                        autocomplete="off" placeholder="Enter your first name" required 
                                        onkeydown="return /[a-zA-Z]/i.test(event.key) || ['Backspace',' ','Delete','ArrowLeft','ArrowRight', '-'].includes(event.key)"/>
                                <label for="inputFName">First Name</label>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Academic Info -->
                    <div class="bg-white rounded-3 p-3 mb-3 shadow-sm">
                        <div class="text-uppercase text-muted small fw-semibold mb-3 d-flex align-items-center gap-2">
                            <i class="fa-duotone fa-solid fa-graduation-cap"></i> Academic Info
                        </div>

                        <!-- Course Selection -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="course" class="form-label small text-muted mb-1">Course</label>
                                <select class="form-select form-select-lg" id="course" name="course" required>
                                    <option value="" disabled selected>Course</option>
                                    <?php
                                    $sql = "SELECT * FROM coursetbl";
                                    $query = $dbh->prepare($sql);
                                    $query->execute();
                                    foreach ($query->fetchAll(PDO::FETCH_OBJ) as $row) {
                                        echo '<option value="'.$row->abv.'">'.$row->abv.'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <!-- Year Level Selection -->    
                            <div class="col-md-6">
                                <label for="yearlevel" class="form-label small text-muted mb-1">Year Level</label>
                                <select class="form-select form-select-lg" id="yearlevel" name="yearlevel" required>
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

                    <!-- Submit Button -->
                    <div class="modal-footer border-0 px-0 pb-0">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
                            <i class="fa-duotone fa-solid fa-xmark me-1"></i> Close
                        </button>
                        <button class="btn btn-primary d-flex align-items-center gap-2" type="submit" name="Add" value="Add"
                                style="background: linear-gradient(135deg, #22b573 0%, #16a085 100%); border:none;">
                            <i class="fa-duotone fa-solid fa-floppy-disk"></i> Save Profile
                        </button>
                    </div>
                </form>
                <!-- End of Form -->
            </div>
        </div>
    </div>
</div>