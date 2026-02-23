<?php
session_start();
error_reporting(0);
include "includes/config.php";

$courses = "";
$advs = "";
$color = "#6c757d"; // default color

/* =========================
   ADD COURSE
========================= */
if (isset($_POST['CSubmit'])) {

    $courses = $_POST['CourseN'];
    $advs    = $_POST['Courseabv'];
    $color   = $_POST['CourseColor'] ?? '#6c757d';

    if (empty($courses) || empty($advs)) {
        echo '<script>alert("Empty Fields. Please try again")</script>';
    } else {

        // Check if course already exists
        $check_sql = "SELECT * FROM coursetbl 
                      WHERE Cname = :CourseN OR abv = :Courseabv";
        $check_query = $dbh->prepare($check_sql);
        $check_query->bindParam(':CourseN', $courses, PDO::PARAM_STR);
        $check_query->bindParam(':Courseabv', $advs, PDO::PARAM_STR);
        $check_query->execute();

        if ($check_query->rowCount() > 0) {

            echo '<script>alert("Course already exists. Please try again.")</script>';

        } else {

            // Insert new course WITH COLOR
            $sql = "INSERT INTO coursetbl (Cname, abv, color)
                    VALUES (:CourseN, :Courseabv, :CourseColor)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':CourseN', $courses, PDO::PARAM_STR);
            $query->bindParam(':Courseabv', $advs, PDO::PARAM_STR);
            $query->bindParam(':CourseColor', $color, PDO::PARAM_STR);
            $query->execute();

            $LastInsertId = $dbh->lastInsertId();

            if ($LastInsertId > 0) {
                echo '<script>
                        alert("Course added successfully!");
                        window.location.href = "courses.php";
                      </script>';
                exit;
            } else {
                echo '<script>alert("Something went wrong. Please try again.")</script>';
            }
        }
    }
}

/* =========================
   UPDATE COURSE
========================= */
if (isset($_POST['UpdateCourse'])) {

    $courses = $_POST['CourseN'];
    $advs    = $_POST['Courseabv'];
    $color   = $_POST['CourseColor'] ?? '#6c757d';
    $cid     = $_POST['Cid'] ?? '';

    if (empty($courses) || empty($advs) || empty($cid)) {
        echo '<script>alert("Empty Fields or Invalid Course ID. Please try again")</script>';
        echo "<script>window.location.href ='courses.php'</script>";
    } else {

        // Check existing record
        $checkSql = "SELECT Cname, abv, color FROM coursetbl WHERE id = :Cid";
        $checkQuery = $dbh->prepare($checkSql);
        $checkQuery->bindParam(':Cid', $cid, PDO::PARAM_STR);
        $checkQuery->execute();
        $existing = $checkQuery->fetch(PDO::FETCH_ASSOC);

        if ($existing &&
            $existing['Cname'] === $courses &&
            $existing['abv'] === $advs &&
            $existing['color'] === $color
        ) {

            echo '<script>alert("No changes detected. Record not updated.")</script>';
            echo "<script>window.location.href ='courses.php'</script>";

        } else {

            // Update WITH COLOR
            $sql = "UPDATE coursetbl
                    SET Cname = :CourseN,
                        abv   = :Courseabv,
                        color = :CourseColor
                    WHERE id = :Cid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':CourseN', $courses, PDO::PARAM_STR);
            $query->bindParam(':Courseabv', $advs, PDO::PARAM_STR);
            $query->bindParam(':CourseColor', $color, PDO::PARAM_STR);
            $query->bindParam(':Cid', $cid, PDO::PARAM_STR);
            $query->execute();

            echo '<script>alert("Updated successfully")</script>';

            unset($_SESSION['Cname'], $_SESSION['Cabv'], $_SESSION['Ccolor'], $_SESSION['Cid']);
            echo "<script>window.location.href ='courses.php'</script>";
        }
    }
}

/* =========================
   SEARCH COURSE
========================= */
if (isset($_POST['CSearch'])) {

    $NCourse = $_POST['NCourse'];
    $sql = "SELECT * FROM coursetbl WHERE Cname = :NCourse";
    $query = $dbh->prepare($sql);
    $query->bindParam(':NCourse', $NCourse, PDO::PARAM_STR);
    $query->execute();

    $results = $query->fetchAll(PDO::FETCH_OBJ);

    if ($query->rowCount() > 0) {
        foreach ($results as $result) {

            $_SESSION['Cname']  = $result->Cname;
            $_SESSION['Cabv']   = $result->abv;
            $_SESSION['Ccolor'] = $result->color;
            $_SESSION['Cid']    = $result->id;

            echo "<script>window.location.href='courses.php'</script>";
            exit;
        }
    } else {
        echo '<script>alert("No record found")</script>';
        echo "<script>window.location.href ='courses.php'</script>";
        exit;
    }
}

/* =========================
   RESET SESSION
========================= */
if (isset($_POST['reset_all'])) {

    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }

    session_unset();
    session_destroy();

    echo "<script>alert('Session cleared successfully!');</script>";
    echo "<script>window.location.href='courses.php'</script>";
    exit;
}
?>
