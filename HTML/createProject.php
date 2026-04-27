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

$tags = []; // Load available tags from DB for lecturers to select from
$result = $conn->query("SELECT Tag_ID, Tag_Name FROM tags ORDER BY Tag_Name ASC");

if ($result) { // If valid query then it executes and stores result in tags
    while ($row = $result->fetch_assoc()) {
        $tags[] = $row;
    }
}

$lecturers = [];
if ($_SESSION['role'] === 'admin') { // Admin only feature, if query is valid then all lecturers will be displayed on page.
    $result = $conn->query("SELECT Lecturer_ID, Name FROM lecturers ORDER BY Name ASC");

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $lecturers[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="../CSS/stylesheet2.css">
  <title>Create Project</title>
</head>
<body>
<!-- Navbar code -->
<ul class="navbar">
  <li><a href="Home.php">Home page</a></li>
  <li><a href="Main.php">Project listing</a></li>
  <li><a href="login.php">Login</a></li>
  <li><a href="lecturerPage.php">Lecturers</a></li>
</ul>
<!-- Form to submit for project creation -->
<form class="createProject" action="../PHP/projectCreator.php" method="post">
    <h1>Project creation form</h1>

    <?php if ($_SESSION['role'] === 'admin'): // Admin only, they can choose lecturers or a base account in the DB to create a project for?>
        <label>Lecturer:</label>
        <select name="lecturer_id" required>
            <option value="">Select lecturer</option>
            <?php foreach ($lecturers as $lecturer): ?>
                <option value="<?php echo $lecturer['Lecturer_ID']; ?>">
                    <?php echo htmlspecialchars($lecturer['Name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <!-- Main inputs with some basic authentication -->
    <label>Project Name:</label>
    <input type="text" name="title" placeholder="Enter Project Name" required>

    <label>Description:</label>
    <textarea name="description" placeholder="Enter the project description" required></textarea>

    <!-- Currently not pulled from DB but could be added if modules are expanded and seen as a key feature -->
    <label>Prerequisite:</label>
    <select name="prerequisite">
        <option value="NONE">NONE</option>
        <option value="CS3MAS">CS3MAS</option>
        <option value="CS3OS">CS3OS</option>
        <option value="CS3SA">CS3SA</option>
    </select>

    <label>Capacity:</label>
    <input type="number" name="capacity" min="1" value="1" required>

    <label>Tags:</label>
    <!-- Lecturers can select multiple tags to add from DB to help describe their project -->
    <div class="multiSelect">
        <div class="selectBox" onclick="toggleDropdown()">
            <span id="selectedTags">Select tags</span>
        </div>
        <!-- Main selection code for the tags, lets you select and deselect multiple tags -->
        <div id="checkboxes" class="checkboxes">
            <?php foreach ($tags as $tag): ?>
                <label>
                    <input type="checkbox" name="tags[]" value="<?php echo $tag['Tag_ID']; ?>">
                    <?php echo htmlspecialchars($tag['Tag_Name']); ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>

    <button type="submit" name="submit">Create Project</button>
</form>
<!-- Updates on each change to track and then submit which tags are being used -->
<script src="../JS/tagselector.js"></script>
</body>
</html>