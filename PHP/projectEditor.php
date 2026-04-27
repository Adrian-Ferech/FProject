<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once(__DIR__ . '/db.php');
require_once(__DIR__ . '/tagCombinations.php'); // all tag combinations are updated when a project is edited

if (
    !isset($_SESSION['role']) ||
    !in_array($_SESSION['role'], ['lecturer', 'admin'], true)
) {
    die("Unauthorized");
}

$role = $_SESSION['role'];

$project_id = $_GET['id'] ?? null;

if (!$project_id) {
    die("No project selected");
}

$project_id = (int)$project_id;

// Select lecturers project details
if ($role === 'lecturer') {
    if (!isset($_SESSION['lecturer_id'])) {
        die("Lecturer session missing");
    }

    $lecturer_id = $_SESSION['lecturer_id'];

    $stmt = $conn->prepare("
        SELECT Project_ID, Lecturer_ID, Title, Description, Prerequisite, capacity
        FROM projects
        WHERE Project_ID = ? AND Lecturer_ID = ?
    ");

    $stmt->bind_param("ii", $project_id, $lecturer_id);
// If admin then just find project
} else {
    $stmt = $conn->prepare("
        SELECT Project_ID, Lecturer_ID, Title, Description, Prerequisite, capacity
        FROM projects
        WHERE Project_ID = ?
    ");

    $stmt->bind_param("i", $project_id);
}

$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();
$stmt->close();

if (!$project) {
    die("Project not found or you do not have permission to edit it");
}

// Get tags to reassign them to projects
$tags = [];
$result = $conn->query("SELECT Tag_ID, Tag_Name FROM tags ORDER BY Tag_Name ASC");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tags[] = $row;
    }
}

// Code to find tags that the project already has, if any
$selectedTags = [];

$stmt = $conn->prepare("
    SELECT Tag_ID
    FROM project_tags
    WHERE Project_ID = ?
");

$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $selectedTags[] = (int)$row['Tag_ID'];
}

$stmt->close();

// Admin can find and edit ownership of lecturers projects
$lecturers = [];

if ($role === 'admin') {
    $result = $conn->query("
        SELECT Lecturer_ID, Name
        FROM lecturers
        ORDER BY Name ASC
    ");

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $lecturers[] = $row;
        }
    }
}


// Code for updating project once page is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['project_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prerequisite = $_POST['prerequisite'] ?? 'NONE';
    $capacity = (int)($_POST['capacity'] ?? 1);
    $newTags = $_POST['tags'] ?? [];

    if ($title === '' || $description === '' || $capacity < 1) {
        die("All required fields are required!");
    }

    if ($role === 'admin') {
        $newLecturerId = (int)($_POST['lecturer_id'] ?? 0);

        if ($newLecturerId <= 0) {
            die("Please select a lecturer.");
        }

        $check = $conn->prepare("
            SELECT Lecturer_ID
            FROM lecturers
            WHERE Lecturer_ID = ?
        ");
        $check->bind_param("i", $newLecturerId);
        $check->execute();

        if ($check->get_result()->num_rows === 0) {
            die("Selected lecturer does not exist.");
        }

        $check->close();
    } else {
        $newLecturerId = $_SESSION['lecturer_id'];
    }

    $conn->begin_transaction();

    try {
        if ($role === 'lecturer') {
            $update = $conn->prepare("
                UPDATE projects
                SET Title = ?, Description = ?, Prerequisite = ?, capacity = ?
                WHERE Project_ID = ? AND Lecturer_ID = ?
            ");

            $update->bind_param(
                "sssiii",
                $title,
                $description,
                $prerequisite,
                $capacity,
                $project_id,
                $newLecturerId
            );
        } else {
            $update = $conn->prepare("
                UPDATE projects
                SET Lecturer_ID = ?, Title = ?, Description = ?, Prerequisite = ?, capacity = ?
                WHERE Project_ID = ?
            ");

            $update->bind_param(
                "isssii",
                $newLecturerId,
                $title,
                $description,
                $prerequisite,
                $capacity,
                $project_id
            );
        }

        if (!$update) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        if (!$update->execute()) {
            throw new Exception("Update failed: " . $update->error);
        }

        $update->close();

        $deleteTags = $conn->prepare("
            DELETE FROM project_tags
            WHERE Project_ID = ?
        ");

        $deleteTags->bind_param("i", $project_id);
        $deleteTags->execute();
        $deleteTags->close();

        if (!empty($newTags)) {
            $insertTag = $conn->prepare("
                INSERT INTO project_tags (Project_ID, Tag_ID)
                VALUES (?, ?)
            ");

            foreach ($newTags as $tag_id) {
                $tag_id = (int)$tag_id;
                $insertTag->bind_param("ii", $project_id, $tag_id);
                $insertTag->execute();
            }

            $insertTag->close();
        }

        syncTagCombinations($conn);

        $conn->commit();

        if ($role === 'admin') {
            header("Location: ../HTML/adminPage.php?updated=1");
        } else {
            header("Location: ../HTML/lecturerPage.php?updated=1");
        }

        exit();

    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Project</title>
    <link rel="stylesheet" href="../CSS/stylesheet2.css">
</head>
<body>

<h1>Edit Project</h1>

<form method="post">

    <?php if ($role === 'admin'): ?>
        <label>Lecturer:</label>
        <select name="lecturer_id" required>
            <?php foreach ($lecturers as $lecturer): ?>
                <option value="<?php echo $lecturer['Lecturer_ID']; ?>"
                    <?php if ((int)$project['Lecturer_ID'] === (int)$lecturer['Lecturer_ID']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($lecturer['Name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <label>Project Name:</label>
    <input type="text" name="project_name"
        value="<?php echo htmlspecialchars($project['Title'] ?? ''); ?>" required>

    <label>Description:</label>
    <textarea name="description" required><?php echo htmlspecialchars($project['Description'] ?? ''); ?></textarea>

    <label>Prerequisite:</label>
    <select name="prerequisite">
        <option value="NONE" <?php if (($project['Prerequisite'] ?? '') === 'NONE') echo 'selected'; ?>>NONE</option>
        <option value="CS3MAS" <?php if (($project['Prerequisite'] ?? '') === 'CS3MAS') echo 'selected'; ?>>CS3MAS</option>
        <option value="CS3OS" <?php if (($project['Prerequisite'] ?? '') === 'CS3OS') echo 'selected'; ?>>CS3OS</option>
        <option value="CS3SA" <?php if (($project['Prerequisite'] ?? '') === 'CS3SA') echo 'selected'; ?>>CS3SA</option>
    </select>

    <label>Capacity:</label>
    <input type="number" name="capacity" min="1"
        value="<?php echo htmlspecialchars($project['capacity'] ?? 1); ?>" required>

    <label>Tags:</label>

    <div class="multiSelect">
        <div class="selectBox" onclick="toggleDropdown()">
            <span id="selectedTagsText">Select tags</span>
        </div>

        <div id="checkboxes" class="checkboxes">
            <?php foreach ($tags as $tag): ?>
                <?php
                    $tagId = (int)$tag['Tag_ID'];
                    $isSelected = in_array($tagId, $selectedTags, true);
                ?>
                <label>
                    <input 
                        type="checkbox" 
                        name="tags[]" 
                        value="<?php echo $tagId; ?>"
                        <?php echo $isSelected ? 'checked' : ''; ?>
                    >
                    <?php echo htmlspecialchars($tag['Tag_Name']); ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    
    <button type="submit">Update Project</button>
</form>

<script src="../JS/tagselector.js"></script>
</body>
</html>