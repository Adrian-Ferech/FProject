<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once(__DIR__ . '/db.php');
require_once(__DIR__ . '/tagCombinations.php'); // All tag combinations are updated when a project is removed

if (
    !isset($_SESSION['role']) ||
    !in_array($_SESSION['role'], ['lecturer', 'admin'], true)
) {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

$role = $_SESSION['role'];
$project_id = (int)($_POST['project_id'] ?? 0);

if ($project_id <= 0) {
    die("Invalid project");
}

if ($role === 'lecturer') {
    if (!isset($_SESSION['lecturer_id'])) {
        die("Lecturer session missing");
    }

    $lecturer_id = (int)$_SESSION['lecturer_id'];

    $check = $conn->prepare("
        SELECT Project_ID
        FROM projects
        WHERE Project_ID = ? AND Lecturer_ID = ?
    ");
    $check->bind_param("ii", $project_id, $lecturer_id);

} else {
    $check = $conn->prepare("
        SELECT Project_ID
        FROM projects
        WHERE Project_ID = ?
    ");
    $check->bind_param("i", $project_id);
}

$check->execute();

if ($check->get_result()->num_rows === 0) {
    die("Project not found or permission denied");
}

$check->close();

$conn->begin_transaction();
// Execute statement, if error then report message
try {
    $delete = $conn->prepare("
        DELETE FROM projects
        WHERE Project_ID = ?
    ");

    if (!$delete) {
        throw new Exception($conn->error);
    }

    $delete->bind_param("i", $project_id);

    if (!$delete->execute()) {
        throw new Exception($delete->error);
    }

    $delete->close();

    // Create new tag combinations again after project deletion
    syncTagCombinations($conn);

    $conn->commit();

    // Redirect back
    if ($role === 'admin') {
        header("Location: ../HTML/deleteProject.php?deleted=1");
    } else {
        header("Location: ../HTML/deleteProject.php?deleted=1");
    }

    exit();

} catch (Exception $e) {
    $conn->rollback();
    die("Delete failed: " . $e->getMessage());
}
?>