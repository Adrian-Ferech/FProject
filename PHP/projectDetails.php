<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // If project ID is found then get details for project
    $stmt = $conn->prepare("SELECT title, description FROM projects WHERE Project_ID = ?");
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
    <title><?php echo htmlspecialchars($project['title']); ?></title>
</head>
<body>

<h1><?php echo htmlspecialchars($project['title']); ?></h1>
<p><?php echo htmlspecialchars($project['description']); ?></p>

<form action="addPreference.php" method="post">
    <input type="hidden" name="project_id" value="<?php echo $id; ?>">
    <button type="submit">Add to Preferences</button>
</form>

<a href="../HTML/Main.php">Back to Projects</a>

</body>
</html>