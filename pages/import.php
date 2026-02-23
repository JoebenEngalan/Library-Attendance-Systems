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
$spreadsheet = $reader->load($fileTmp);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray();

/* ---------------------------------------
      IMPORT TO DATABASE
-----------------------------------------*/

$success = 0;
$failed  = 0;

unset($data[0]); // remove header

foreach ($data as $row) {

    $studID     = trim($row[0]);
    $lname      = trim($row[1]);
    $fname      = trim($row[2]);
    $course     = trim($row[3]);
    $yearlevel  = trim($row[4]);

    if ($studID == "") continue;

    // Duplicate check
    $check = $dbh->prepare("SELECT studID FROM studtbl WHERE studID = :sid LIMIT 1");
    $check->bindParam(':sid', $studID, PDO::PARAM_STR);
    $check->execute();

    if ($check->rowCount() > 0) {
        $failed++;
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

    if ($query->execute()) {
        $success++;
    } else {
        $failed++;
    }
}

/* ---------------------------------------
      REDIRECT BACK TO records.php
-----------------------------------------*/
$_SESSION['message'] = "Imported: $success | Skipped: $failed";
header("Location: ../records.php");
exit();

?>

