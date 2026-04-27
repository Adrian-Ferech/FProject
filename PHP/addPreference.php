<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['uid'])) {
    die("You must be logged in as a student");
}

$student_id = $_SESSION['uid'];
$project_id = $_POST['project_id'] ?? null;

if (!$project_id) {
    die("Invalid project");
}

// Check if preference is already added
$check = $conn->prepare("
    SELECT * FROM preferences 
    WHERE Student_ID = ? AND Project_ID = ?
");
$check->bind_param("ii", $student_id, $project_id);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    die("You already selected this project");
}

// choose next preference number if there is already a preference in list
$result = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM preferences 
    WHERE Student_ID = ?
");
$result->bind_param("i", $student_id);
$result->execute();
$count = $result->get_result()->fetch_assoc()['total'];

if ($count >= 3) {
    die("You can only select up to 3 preferences");
}

$preference_order = $count + 1;

// Insert preference
$stmt = $conn->prepare("
    INSERT INTO preferences (Student_ID, Project_ID, preference_order)
    VALUES (?, ?, ?)
");

$stmt->bind_param("iii", $student_id, $project_id, $preference_order);

if ($stmt->execute()) {
    header("Location: ../HTML/Main.php?added=1");
    exit();
} else {
    die("Error: " . $stmt->error);
}
?>