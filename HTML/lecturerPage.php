<?php
session_start(); // Start session for login, consisntent across pages.
require_once(__DIR__ . '/../PHP/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'lecturer') { // Must be a lecturer to access lecturer dashboard
    header("Location: lecturerLogin.php");
    exit();
}

$lecturer_id = $_SESSION['lecturer_id'];

// Stats for lecturers

// Finds total students recorded in the DB
$totalStudents = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM students");
if ($result) {
    $totalStudents = $result->fetch_assoc()['total'];
}

// Students who have a preference recorded in the DB
$studentsWithPreferences = 0;
$result = $conn->query("
    SELECT COUNT(DISTINCT Student_ID) AS total
    FROM preferences
");
if ($result) {
    $studentsWithPreferences = $result->fetch_assoc()['total'];
}

// Total students allocated in the DB
$totalAllocated = 0;
$result = $conn->query("
    SELECT COUNT(DISTINCT Student_ID) AS total
    FROM allocation
");
if ($result) {
    $totalAllocated = $result->fetch_assoc()['total'];
}

// The total number of projects this lecturer has created
$lecturerProjects = 0;
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM projects
    WHERE Lecturer_ID = ?
");
$stmt->bind_param("i", $lecturer_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $lecturerProjects = $result->fetch_assoc()['total'];
}
$stmt->close();

// The total number of students allocated to this lecturers projects
$allocatedToLecturer = 0;
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM allocation
    WHERE Lecturer_ID = ?
");
$stmt->bind_param("i", $lecturer_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $allocatedToLecturer = $result->fetch_assoc()['total'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="../CSS/stylesheet2.css">
  <title>Home page</title>
</head>
<body>
<!-- Navbar code -->
<ul class = "navbar">
  <li><a href="Home.php">Home page</a></li>
  <li><a href="Main.php">Project listing</a></li>
  <li><a href="login.php">Login</a></li>
  <li><a href="lecturerPage.php">Lecturers</a></li>
</ul>

<!-- Splits page into two columns -->
<div class = "Seperator">
    <!-- Links to other pages for lecturers -->
    <div class = "LecturerLinks">
        <div>
        <a class="lecturerLink" href="../HTML/createProject.php"> Create a new project: </a>
        </div>
        <div>
            <a class="lecturerLink" href="../HTML/editProject.php"> Edit existing projects: </a>
        </div>
        <div>
            <a class="lecturerLink" href="../HTML/deleteProject.php"> Remove a project: </a>
        </div>
    </div>
    <!-- Stats for lecturers -->
    <div>
        <!-- Each div section contains one piece of data -->
        <div class="statsWrapper">
            <div class="statCard">
                <h3>Total Students</h3>
                <p><?php echo $totalStudents; ?></p>
            </div>

            <div class="statCard">
                <h3>Students With Preferences</h3>
                <p><?php echo $studentsWithPreferences; ?></p>
            </div>

            <div class="statCard">
                <h3>Total Allocated</h3>
                <p><?php echo $totalAllocated; ?></p>
            </div>

            <div class="statCard">
                <h3>Your Projects</h3>
                <p><?php echo $lecturerProjects; ?></p>
            </div>

            <div class="statCard">
                <h3>Allocated To Your Projects</h3>
                <p><?php echo $allocatedToLecturer; ?></p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
