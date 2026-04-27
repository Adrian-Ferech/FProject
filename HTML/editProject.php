<?php
session_start(); // Start session for login, consisntent across pages.
require_once(__DIR__ . '/../PHP/db.php');

if (
    !isset($_SESSION['role']) ||
    !in_array($_SESSION['role'], ['lecturer', 'admin'], true) // User can be admin or lecturer as they both need access to this page with different permissions
) {
    header("Location: login.php"); // Redirect to login if invalid permissions
    exit();
}

$lecturer_id = $_SESSION['lecturer_id'];

$stmt = $conn->prepare("SELECT title, description, Project_ID FROM projects WHERE Lecturer_ID = ?"); // Grab projects from logged in lecturer
$stmt->bind_param("i", $lecturer_id);
$stmt->execute();

$result = $stmt->get_result();

$projects = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="../CSS/stylesheet1.css">
  <title>Project Navigation</title>
</head>
<body>
<!-- Navbar code -->
<ul class = "navbar">
  <li><a href="Home.php">Home page</a></li>
  <li><a href="Main.php">Project listing</a></li>
  <li><a href="login.php">Login</a></li>
  <li><a href="lecturerPage.php">Lecturers</a></li>
</ul>

<div>
  <!-- Title -->
  <div class="split">
    <h1> Final Year Project Listing</h1>
  </div>
  <?php if (!empty($projects)): ?>

  <div class="projectsWrapper">

    <!-- Loop through each project and display them with flexbox -->
    <?php foreach ($projects as $project): ?>
      <div class="container">
        <div class="projectTitle">
          <?php echo htmlspecialchars($project['title']); ?>
        </div>

        <div class="projectDescription">
          <?php echo htmlspecialchars($project['description']); ?>
        </div>

        <div class="projectLink">
          <a href="../PHP/projectEditor.php?id=<?php echo $project['Project_ID']; ?>">
            Edit Project
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php else: ?>
    <p>No projects found.</p>
  <?php endif; ?>
</div>

</body>
</html>
