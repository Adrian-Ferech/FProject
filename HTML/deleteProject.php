<?php
session_start(); // Start session for login, consisntent across pages.
require_once(__DIR__ . '/../PHP/db.php');

if (
    !isset($_SESSION['role']) ||
    !in_array($_SESSION['role'], ['lecturer', 'admin'], true) // Any user not logged in or that is not an admin/lecturer is redirected to login page
) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role']; // set to $role for easier reuse later, shorter keyword
$projects = [];

$search = trim($_GET['search'] ?? ''); // Used for the searchbar to filter projects by name
$filterLecturer = $_GET['lecturer_id'] ?? ''; // If the user is a lecturer then we grab their ID to only list their projects

$lecturers = [];

if ($role === 'admin') { // if Admin then all projects are displayed
    $lecturerResult = $conn->query("
        SELECT Lecturer_ID, Name
        FROM lecturers
        ORDER BY Name ASC
    ");

    if ($lecturerResult) { // If executed then put result into lecturers
        while ($row = $lecturerResult->fetch_assoc()) {
            $lecturers[] = $row;
        }
    }
    // prepare sql statement early as we can modify it depending on the filters
    $sql = "
        SELECT p.Project_ID, p.Title, p.Description, l.Name AS LecturerName
        FROM projects p
        JOIN lecturers l ON p.Lecturer_ID = l.Lecturer_ID
        WHERE 1=1
    ";

    $params = [];
    $types = "";

    if ($filterLecturer !== '') { // Changes the statement if filtering my lecturer.
        $sql .= " AND p.Lecturer_ID = ?";
        $params[] = (int)$filterLecturer;
        $types .= "i";
    }

    if ($search !== '') { // Changes if filtering by searchbar
        $sql .= " AND (p.Title LIKE ? OR p.Description LIKE ?)";
        $searchTerm = "%" . $search . "%";
        $params[] = $searchTerm; // We search the title and description here so supply 2 of the same values here
        $params[] = $searchTerm;
        $types .= "ss";
    }

    $sql .= " ORDER BY p.Title ASC"; // Order alphabetically

    $stmt = $conn->prepare($sql); 

    if (!empty($params)) { // Check if there are filters, if not then no change
        $stmt->bind_param($types, ...$params);
    }

} else {
    if (!isset($_SESSION['lecturer_id'])) { // lecturer section of code
        die("Lecturer session missing");
    }

    $lecturer_id = $_SESSION['lecturer_id'];
    // Select only projects from the logged in lecturer
    $sql = "
        SELECT Project_ID, Title, Description
        FROM projects
        WHERE Lecturer_ID = ?
    ";

    $params = [$lecturer_id];
    $types = "i";

    if ($search !== '') {
        $sql .= " AND (Title LIKE ? OR Description LIKE ?)";
        $searchTerm = "%" . $search . "%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }

    $sql .= " ORDER BY Title ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result(); // execute statement

while ($row = $result->fetch_assoc()) {
    $projects[] = $row; // projects to display
}

$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="../CSS/stylesheet1.css">
  <title>Remove Projects</title>
</head>
<body>
<!-- Navbar code -->
<ul class="navbar">
  <li><a href="Home.php">Home page</a></li>
  <li><a href="Main.php">Project listing</a></li>
  <li><a href="login.php">Login</a></li>
  <li><a href="lecturerPage.php">Lecturers</a></li>
</ul>

<div>
  <!-- Split allows flexbox -->
  <div class="split">
    <h1>Remove Projects</h1>
  </div>

  <!-- Project deletion form -->
  <form method="get" action="deleteProject.php" class="filterForm">
    <label for="search">Search projects:</label>
    <input
      type="text"
      name="search"
      id="search"
      placeholder="Search by title or description"
      value="<?php echo htmlspecialchars($search); ?>"
    >
    <!-- Admin only feature which allows filtering by lecturers as they see all projects -->
    <?php if ($role === 'admin'): ?>
      <label for="lecturer_id">Filter by lecturer:</label>
      <select name="lecturer_id" id="lecturer_id">
        <option value="">All lecturers</option>

        <?php foreach ($lecturers as $lecturer): ?>
          <option
            value="<?php echo $lecturer['Lecturer_ID']; ?>"
            <?php if ((string)$filterLecturer === (string)$lecturer['Lecturer_ID']) echo 'selected'; ?>
          >
            <?php echo htmlspecialchars($lecturer['Name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    <?php endif; ?>
    <!-- Form submit button -->
    <button type="submit">Filter</button>
    <a href="deleteProject.php">Clear</a>
  </form>

  <!-- Display code for projects -->
  <?php if (!empty($projects)): ?>

    <div class="projectsWrapper">

      <?php foreach ($projects as $project): ?>
        <div class="container">

          <div class="projectTitle">
            <?php if ($role === 'admin'): ?>
              Lecturer: <?php echo htmlspecialchars($project['LecturerName']); ?><br>
            <?php endif; ?>

            <?php echo htmlspecialchars($project['Title']); ?>
          </div>

          <div class="projectDescription">
            <?php echo htmlspecialchars($project['Description']); ?>
          </div>

          <div class="projectLink">
            <!-- inline display lets text sit better inside its box -->
            <form action="../PHP/deleteProject.php" method="post" style="display:inline;">
              <input type="hidden" name="project_id" value="<?php echo $project['Project_ID']; ?>">
              <button type="submit" onclick="return confirm('Are you sure you want to delete this project?');">
                Delete Project
              </button>
            </form>
          </div>

        </div>
      <?php endforeach; ?>

    </div>

  <?php else: ?>
    <p>No projects found.</p>
  <?php endif; ?>

</div>

<script src="../JS/script1.js"></script>
</body>
</html>