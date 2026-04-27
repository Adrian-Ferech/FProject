<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // If there is a project ID found then display details
    $stmt = $conn->prepare("
    SELECT 
        p.title, 
        p.description, 
        p.prerequisite, 
        p.capacity,
        l.Name AS lecturer_name
    FROM projects p
    JOIN lecturers l ON p.Lecturer_ID = l.Lecturer_ID
    WHERE p.Project_ID = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $project = $result->fetch_assoc();
    } else {
        echo "Project not found.";
        exit;
    }
} else {
    echo "No project selected.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../CSS/stylesheet2.css">
    <title><?php echo htmlspecialchars($project['title']); ?></title>
</head>
<body>
<!-- Display project details -->
<h1><?php echo htmlspecialchars($project['title']); ?></h1>

<p><strong>Description:</strong><br>
<?php echo htmlspecialchars($project['description']); ?></p>

<p><strong>Lecturer:</strong>
<?php echo htmlspecialchars($project['lecturer_name']); ?></p>

<p><strong>Prerequisite:</strong>
<?php echo htmlspecialchars($project['prerequisite']); ?></p>

<p><strong>Capacity:</strong>
<?php echo htmlspecialchars($project['capacity']); ?></p>

<form action="addPreference.php" method="post">
    <input type="hidden" name="project_id" value="<?php echo $id; ?>">
    <button type="submit">Add to Preferences</button>
</form>

<a href="../HTML/Main.php">Back to Projects</a>

</body>
</html>
