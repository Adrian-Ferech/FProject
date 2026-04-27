<?php
session_start(); // Start session for login, consisntent across pages.

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { // Any user not logged in or that is not an admin/lecturer is redirected to login page
    header("Location: adminLogin.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="../CSS/stylesheet2.css">
  <title>Admin page</title>
</head>
<body>
    <!-- Navbar code -->
<ul class = "navbar">
  <li><a href="Home.php">Home page</a></li>
  <li><a href="adminPage.php">Admin Page</a></li>
  <li><a href="manageAllocations.php">Allocations</a></li>
</ul>

<!-- Main divisor of the page -->
<div class = "Seperator">
    <!-- Left side of page for links to other pages -->
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
    <!-- Right side of page which used to be used for allocation testing and stats. No use right now -->
    <div>
        <form action="../PHP/allocationAlgorithm.php" method="post">
            <label for="allocation_type">Select Allocation Type:</label>
            <select name="allocation_type" id="allocation_type" required>
                <option value="fcfs">First Come First Serve</option>
                <option value="grade">Grade First</option>
            </select>

            <button type="submit" name="run_allocation">
                Run Allocation
            </button>
        </form>
    </div>
</div>
</body>
</html>
