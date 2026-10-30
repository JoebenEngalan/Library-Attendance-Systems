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
// Check if form is submitted (either the normal Save, or Save & Mark Ready)
if (isset($_POST['Add']) || isset($_POST['AddReady'])) {

    $remarks = isset($_POST['AddReady']) ? 'Ready for approval' : null;

    $studid = trim($_POST['studid']);

    // Proper-case Lname/Fname: 'JOSE' -> 'Jose', 'DELA CRUZ' -> 'Dela Cruz'
    $lname = ucwords(strtolower(trim($_POST['inputLName'])));
    $fname = ucwords(strtolower(trim($_POST['inputFName'])));

    $courses = $_POST['course'];
    $yearlevel = $_POST['yearlevel'];

    // "Library Visitor" does not use the standard student ID, so it skips the format rules.
    // (Matches any course whose abbreviation contains "visitor", case-insensitive.)
    $isVisitor = stripos((string) $courses, 'visitor') !== false;

    if ($isVisitor) {
        // Visitor ID: letters/numbers only, max 14 characters (studID column size)
        $idFormatOk = preg_match('/^[A-Za-z0-9]{1,14}$/', $studid) === 1;
        $idYearOk   = true;
    } else {
        // Student ID: [2-digit course code][personal number][2-digit enrollment year]
        // Personal number = digits, optionally ending in one letter (e.g. 289949A23)
        // Total 5 to 14 characters; year (last 2 digits) from 09 (2009) up to the current year
        $studid     = strtoupper($studid);
        $idFormatOk = strlen($studid) <= 14 && preg_match('/^[0-9]{2}[0-9]+[A-C]?[0-9]{2}$/', $studid) === 1;
        $idYY       = $idFormatOk ? (int) substr($studid, -2) : 0;
        $idYearOk   = $idFormatOk && $idYY >= 9 && $idYY <= (int) date('y');
    }

    // Validate input fields
    if (empty($studid) || empty($lname) || empty($fname) || empty($courses) || empty($yearlevel)) {
        echo '<script>alert("Empty Fields. Please try again")</script>';
    } elseif (!$idFormatOk) {
        echo $isVisitor
            ? '<script>alert("Invalid Visitor ID. Use letters and numbers only (max 14 characters).")</script>'
            : '<script>alert("Invalid Student ID. Use: 2-digit course code + personal number (numbers, may end in A, B or C) + 2-digit enrollment year.")</script>';
    } elseif (!$idYearOk) {
        echo '<script>alert("Invalid Student ID. The last 2 digits (enrollment year) must be from 09 (2009) up to the current year.")</script>';
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
                $sql = "INSERT INTO studtbl (studID, Lname, Fname, Course, YearLevel, remarks)
                        VALUES (:studid, :inputLName, :inputFName, :course, :yearlevel, :remarks)";
                $query = $dbh->prepare($sql);
                $query->bindParam(':studid', $studid, PDO::PARAM_STR);
                $query->bindParam(':inputLName', $lname, PDO::PARAM_STR);
                $query->bindParam(':inputFName', $fname, PDO::PARAM_STR);
                $query->bindParam(':course', $courses, PDO::PARAM_STR);
                $query->bindParam(':yearlevel', $yearlevel, PDO::PARAM_STR);
                $query->bindParam(':remarks', $remarks, PDO::PARAM_STR);
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
                        <div class="input-group input-group-lg mb-1">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fa-duotone fa-solid fa-hashtag text-muted"></i>
                            </span>
                            <div class="form-floating">
                                <input class="form-control border-start-0" name="studid" id="studid" type="text" 
                                autocomplete="off" placeholder="Student ID" required 
                                inputmode="text" maxlength="14" title="Course code (2 digits) + personal number (may end in a letter) + enrollment year (2 digits)"/>
                                <label for="studid">Student ID</label>
                            </div>
                        </div>
                        <!-- Live ID check feedback -->
                        <div id="studidFeedback" class="small mb-3" style="min-height:1.25rem;" aria-live="polite"></div>

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
                    <div class="modal-footer border-0 px-0 pb-0 flex-nowrap gap-2">
                        <button type="button" class="btn btn-light border text-nowrap" data-bs-dismiss="modal">
                            <i class="fa-duotone fa-solid fa-xmark me-1"></i> Close
                        </button>
                        <button class="btn btn-primary d-flex align-items-center gap-2 text-nowrap" type="submit" name="Add" value="Add" id="btnSave"
                                style="background: linear-gradient(135deg, #22b573 0%, #16a085 100%); border:none;">
                            <i class="fa-duotone fa-solid fa-floppy-disk"></i> Save
                        </button>
                        <button class="btn btn-success d-flex align-items-center gap-2 text-nowrap" type="submit" name="AddReady" value="AddReady" id="btnSaveReady">
                            <i class="fa-duotone fa-solid fa-circle-check"></i> Save & Mark Ready
                        </button>
                    </div>
                </form>
                <!-- End of Form -->
            </div>
        </div>
    </div>
</div>

<script>
// Live check: does this Student ID already exist?
(function () {
    const modal    = document.getElementById('Addstudent');
    const input    = document.getElementById('studid');
    const course   = document.getElementById('course');
    const feedback = document.getElementById('studidFeedback');
    const saveBtns = [document.getElementById('btnSave'), document.getElementById('btnSaveReady')];
    let timer = null;
    let latest = 0; // ignore out-of-date responses

    // "Library Visitor" doesn't use the standard student ID format
    function isVisitor() {
        return course.value.toLowerCase().includes('visitor');
    }

    function applyIdMode() {
        input.inputMode = 'text';
    }

    function setButtons(disabled) {
        saveBtns.forEach(b => { if (b) b.disabled = disabled; });
    }

    function setState(state) {
        input.classList.remove('is-invalid', 'is-valid');
        switch (state) {
            case 'checking':
                feedback.innerHTML = '<span class="text-muted">Checking ID...</span>';
                setButtons(false);
                break;
            case 'exists':
                input.classList.add('is-invalid');
                feedback.innerHTML = '<span class="text-danger"><i class="fa-solid fa-circle-xmark"></i> This Student ID already exists.</span>';
                setButtons(true);
                break;
            case 'available':
                input.classList.add('is-valid');
                feedback.innerHTML = '<span class="text-success"><i class="fa-solid fa-circle-check"></i> ID is available.</span>';
                setButtons(false);
                break;
            case 'incomplete':
                input.classList.add('is-invalid');
                feedback.innerHTML = '<span class="text-danger"><i class="fa-solid fa-circle-xmark"></i> Format: 2-digit course code + personal number + 2-digit year (the personal number may end in A, B or C, e.g. 2890909126 or 289949A23).</span>';
                setButtons(true);
                break;
            case 'badyear':
                input.classList.add('is-invalid');
                feedback.innerHTML = '<span class="text-danger"><i class="fa-solid fa-circle-xmark"></i> The last 2 digits (enrollment year) must be from 09 (2009) up to the current year.</span>';
                setButtons(true);
                break;
            case 'error':
                feedback.innerHTML = '<span class="text-warning">Could not check the ID right now. It will be verified when you save.</span>';
                setButtons(false);
                break;
            default: // reset
                feedback.innerHTML = '';
                setButtons(false);
        }
    }

    async function check(value) {
        const reqId = ++latest;
        try {
            const fd = new FormData();
            fd.append('studid', value);
            const res  = await fetch('pages/checkStudId.php', { method: 'POST', body: fd });
            const text = await res.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (parseErr) {
                console.error('checkStudId.php did not return JSON. HTTP ' + res.status + ' from ' + res.url + '\nResponse starts with:\n' + text.slice(0, 300));
                if (reqId === latest) setState('error');
                return;
            }
            if (reqId !== latest) return;
            if (data.error) {
                console.error('checkStudId.php reported a server error (HTTP ' + res.status + ').');
                return setState('error');
            }
            setState(data.exists ? 'exists' : 'available');
        } catch (e) {
            console.error('ID check request failed:', e);
            if (reqId === latest) setState('error');
        }
    }

    function validate() {
        clearTimeout(timer);
        const visitor = isVisitor();

        // Letters and numbers only; student IDs are stored in capitals
        input.value = input.value.replace(/[^A-Za-z0-9]/g, '');
        if (!visitor) input.value = input.value.toUpperCase();
        const value = input.value;
        if (!value) { latest++; setState('reset'); return; }

        if (!visitor) {
            // Format: 2-digit course + personal number (digits, may end in one letter) + 2-digit year
            if (!/^[0-9]{2}[0-9]+[A-C]?[0-9]{2}$/.test(value)) { latest++; setState('incomplete'); return; }

            // Enrollment year: 2009 (school start) up to the current year
            const yy = parseInt(value.slice(-2), 10);
            const currentYY = new Date().getFullYear() % 100;
            if (yy < 9 || yy > currentYY) { latest++; setState('badyear'); return; }
        }

        setState('checking');
        timer = setTimeout(() => check(value), 400); // wait for typing to pause
    }

    // Block symbols and spaces in the ID (letters and numbers only)
    input.addEventListener('keydown', function (e) {
        if (e.ctrlKey || e.metaKey) return;
        const allowed = ['Backspace','Delete','Tab','ArrowLeft','ArrowRight','Home','End','Enter'];
        if (allowed.includes(e.key) || e.key.length > 1) return;
        const ok = /^[A-Za-z0-9]$/.test(e.key);
        if (!ok) e.preventDefault();
    });

    input.addEventListener('input', validate);
    course.addEventListener('change', function () {
        applyIdMode();
        validate();
    });

    // Clear the check state whenever the modal is closed
    modal.addEventListener('hidden.bs.modal', function () {
        clearTimeout(timer);
        latest++;
        setState('reset');
    });
})();
</script>