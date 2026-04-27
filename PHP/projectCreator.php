<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once(__DIR__ . '/db.php');
require_once(__DIR__ . '/tagCombinations.php'); // Updates tag combinations whenever a new project is created

if (
    !isset($_SESSION['role']) ||
    !in_array($_SESSION['role'], ['lecturer', 'admin'], true) // need to be admin or lecturer to create a project
) {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

// Get all project details to insert into DB
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$prerequisite = $_POST['prerequisite'] ?? 'NONE';
$capacity = (int)($_POST['capacity'] ?? 1);
$status = 1;
$tags = $_POST['tags'] ?? [];

// Verify details are there
if ($title === '' || $description === '' || $capacity < 1) {
    die("All required fields must be completed.");
}

if ($_SESSION['role'] === 'lecturer') {
    if (!isset($_SESSION['lecturer_id'])) {
        die("Lecturer session missing.");
    }

    $lecturer_id = $_SESSION['lecturer_id'];
} else {
    $lecturer_id = (int)($_POST['lecturer_id'] ?? 0);

    if ($lecturer_id <= 0) {
        die("Admin must select a lecturer.");
    }

    $check = $conn->prepare("SELECT Lecturer_ID FROM lecturers WHERE Lecturer_ID = ?");
    $check->bind_param("i", $lecturer_id);
    $check->execute();

    if ($check->get_result()->num_rows === 0) {
        die("Selected lecturer does not exist.");
    }

    $check->close();
}

$conn->begin_transaction();

// Prepare sql statement for project creation in DB
try {
    $stmt = $conn->prepare("
        INSERT INTO projects (Lecturer_ID, Title, Description, Prerequisite, Status, capacity)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    // bind parameters to sql statement
    $stmt->bind_param(
        "isssii",
        $lecturer_id,
        $title,
        $description,
        $prerequisite,
        $status,
        $capacity
    );

    if (!$stmt->execute()) {
        throw new Exception("Project insert failed: " . $stmt->error);
    }

    $project_id = $conn->insert_id;
    $stmt->close();

    if (!empty($tags)) {
        $tagStmt = $conn->prepare("
            INSERT INTO project_tags (Project_ID, Tag_ID)
            VALUES (?, ?)
        ");
        // Insert new tags into project_tags to also create new tag combinations
        if (!$tagStmt) {
            throw new Exception("Tag prepare failed: " . $conn->error);
        }

        foreach ($tags as $tag_id) {
            $tag_id = (int)$tag_id;

            $tagStmt->bind_param("ii", $project_id, $tag_id);

            if (!$tagStmt->execute()) {
                throw new Exception("Tag insert failed: " . $tagStmt->error);
            }
        }

        $tagStmt->close();
    }
    // Create new tag combinations
    syncTagCombinations($conn);
    $conn->commit();

    if ($_SESSION['role'] === 'admin') {
        header("Location: ../HTML/adminPage.php?created=1");
    } else {
        header("Location: ../HTML/lecturerPage.php?created=1");
    }

    exit();

} catch (Exception $e) {
    $conn->rollback();
    die("Error: " . $e->getMessage());
}