<?php
session_start();
error_reporting(0);
include __DIR__ . "/../includes/config.php";  // Correct path from /pages/import.php

/* ---------------------------------------
    AUTOLOADER (NO COMPOSER)
-----------------------------------------*/
spl_autoload_register(function ($class) {

    if (strpos($class, 'PhpOffice\\PhpSpreadsheet') !== 0) {
        return;
    }

    // Convert namespace to filepath
    $relativePath = str_replace('PhpOffice\\PhpSpreadsheet', '', $class);
    $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativePath);

    // Correct path to /assets/phpspreadsheet/
    $file = __DIR__ . '/../assets/phpspreadsheet/src/PhpSpreadsheet' . $relativePath . '.php';

    if (file_exists($file)) {
        require $file;
    } else {
        die("Autoload error: Cannot load: $file");
    }
});

use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

/* ---------------------------------------
     CHECK IF FILE WAS UPLOADED
-----------------------------------------*/
if (!isset($_FILES['file']) || $_FILES['file']['error'] != 0) {
    $_SESSION['message'] = "No file uploaded!";
    header("Location: ../records.php");
    exit();
}

$fileName = $_FILES['file']['name'];
$fileTmp  = $_FILES['file']['tmp_name'];

// Get extension
$extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

/* ---------------------------------------
     SELECT READER
-----------------------------------------*/
if ($extension == 'csv') {
    $reader = new Csv();
    $reader->setDelimiter(',');
    $reader->setEnclosure('"');

} elseif ($extension == 'xlsx') {
    $reader = new Xlsx();

} else {
    $_SESSION['message'] = "Invalid file! Only CSV or XLSX allowed.";
    header("Location: ../records.php");
    exit();
}

/* ---------------------------------------
      LOAD FILE
-----------------------------------------*/
try {
    $spreadsheet = $reader->load($fileTmp);
} catch (Exception $e) {
    $_SESSION['message'] = "❌ Could not read file. Make sure it's a valid CSV/XLSX.";
    header("Location: ../records.php");
    exit();
}

$sheet = $spreadsheet->getActiveSheet();
$data  = $sheet->toArray();

if (count($data) < 2) {
    $_SESSION['message'] = "❌ File appears to be empty (no data rows found).";
    header("Location: ../records.php");
    exit();
}

/* ---------------------------------------
      VALIDATE HEADER ROW
      Expected columns (in order): studID, Lname, Fname, Course, YearLevel
-----------------------------------------*/
$expectedHeaders = ['studid', 'lname', 'fname', 'course', 'yearlevel'];
$actualHeaders   = array_map(function ($h) {
    return strtolower(trim((string) $h));
}, $data[0]);

$headerMismatch = false;
foreach ($expectedHeaders as $i => $expected) {
    if (!isset($actualHeaders[$i]) || $actualHeaders[$i] !== $expected) {
        $headerMismatch = true;
        break;
    }
}

if ($headerMismatch) {
    $_SESSION['message'] = "❌ Column headers don't match the expected format. "
        . "Expected order: studID, Lname, Fname, Course, YearLevel. "
        . "Found: " . implode(', ', $data[0]);
    header("Location: ../records.php");
    exit();
}

unset($data[0]); // remove header row now that it's validated

/* ---------------------------------------
      ALLOWED VALUES (for validation)
-----------------------------------------*/
$allowedYearLevels = ['1st Year', '2nd Year', '3rd Year', '4th Year', 'None'];

$allowedCourses = [];
$courseStmt = $dbh->query("SELECT abv FROM coursetbl");
foreach ($courseStmt->fetchAll(PDO::FETCH_COLUMN) as $abv) {
    $allowedCourses[] = $abv;
}

/* ---------------------------------------
      IMPORT TO DATABASE
-----------------------------------------*/
$success = 0;
$failed  = 0;
$errors  = []; // detailed per-row failure log, e.g. "Row 5 (28958823): Duplicate studID"

$rowNum = 1; // header was row 1, data starts at row 2

foreach ($data as $row) {
    $rowNum++;

    // Raw values
    $studID    = trim((string) ($row[0] ?? ''));
    $lname     = trim((string) ($row[1] ?? ''));
    $fname     = trim((string) ($row[2] ?? ''));
    $course    = trim((string) ($row[3] ?? ''));
    $yearlevel = trim((string) ($row[4] ?? ''));

    if ($studID === "") {
        continue; // silently skip fully blank rows
    }

    // Normalize studID: strip dashes so formatting stays consistent
    // (e.g. '28-9588-23' -> '289588 23' avoided; dashes removed entirely)
    $studID = str_replace('-', '', $studID);

    // Proper-case names, matching addStud.php / updateStud.php behavior
    $lname = ucwords(strtolower($lname));
    $fname = ucwords(strtolower($fname));

    // Validate required fields
    if ($lname === '' || $fname === '' || $course === '' || $yearlevel === '') {
        $failed++;
        $errors[] = "Row {$rowNum} ({$studID}): Missing required field(s).";
        continue;
    }

    // Validate YearLevel against allowed list
    if (!in_array($yearlevel, $allowedYearLevels, true)) {
        $failed++;
        $errors[] = "Row {$rowNum} ({$studID}): Invalid YearLevel '{$yearlevel}'.";
        continue;
    }

    // Validate Course against coursetbl (skip check if coursetbl is empty/unavailable)
    if (!empty($allowedCourses) && !in_array($course, $allowedCourses, true) && $course !== 'Alumni') {
        $failed++;
        $errors[] = "Row {$rowNum} ({$studID}): Unknown Course '{$course}'.";
        continue;
    }

    try {
        // Duplicate check
        $check = $dbh->prepare("SELECT studID FROM studtbl WHERE studID = :sid LIMIT 1");
        $check->bindParam(':sid', $studID, PDO::PARAM_STR);
        $check->execute();

        if ($check->rowCount() > 0) {
            $failed++;
            $errors[] = "Row {$rowNum} ({$studID}): Duplicate studID (already exists).";
            continue;
        }

        // Insert
        $sql = "INSERT INTO studtbl (studID, Lname, Fname, Course, YearLevel)
                VALUES (:studID, :lname, :fname, :course, :yearlevel)";
        $query = $dbh->prepare($sql);

        $query->bindParam(':studID', $studID);
        $query->bindParam(':lname', $lname);
        $query->bindParam(':fname', $fname);
        $query->bindParam(':course', $course);
        $query->bindParam(':yearlevel', $yearlevel);
        $query->execute();

        $success++;

    } catch (PDOException $e) {
        $failed++;
        $errors[] = "Row {$rowNum} ({$studID}): Database error — " . $e->getMessage();
    }
}

/* ---------------------------------------
      BUILD RESULT MESSAGE
-----------------------------------------*/
$message = "✅ Imported: {$success} | ❌ Skipped: {$failed}";

if (!empty($errors)) {
    // Cap the number of detailed lines shown to avoid a huge session message
    $maxShown = 20;
    $shown = array_slice($errors, 0, $maxShown);

    $message .= "<br><br><strong>Details:</strong><br>" . implode('<br>', $shown);

    if (count($errors) > $maxShown) {
        $remaining = count($errors) - $maxShown;
        $message .= "<br>...and {$remaining} more.";
    }
}

/* ---------------------------------------
      REDIRECT BACK TO records.php
-----------------------------------------*/
$_SESSION['message'] = $message;
header("Location: ../records.php");
exit();

?>