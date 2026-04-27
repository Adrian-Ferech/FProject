<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['uid'])) {
    die("You must be logged in");
}

$student_id = $_SESSION['uid'];
$project_id = $_POST['project_id'] ?? null;

if (!$project_id) {
    die("Invalid request");
}

// Delete student preference
$stmt = $conn->prepare("
    DELETE FROM preferences 
    WHERE Student_ID = ? AND Project_ID = ?
");

$stmt->bind_param("ii", $student_id, $project_id);

if ($stmt->execute()) {

    $reorder = $conn->prepare("
        SELECT Preference_ID 
        FROM preferences 
        WHERE Student_ID = ?
        ORDER BY preference_order ASC
    ");

    $reorder->bind_param("i", $student_id);
    $reorder->execute();
    $result = $reorder->get_result();

    $order = 1;

    while ($row = $result->fetch_assoc()) {
        $update = $conn->prepare("
            UPDATE preferences 
            SET preference_order = ? 
            WHERE Preference_ID = ?
        ");
        $update->bind_param("ii", $order, $row['Preference_ID']);
        $update->execute();
        $order++;
    }

    header("Location: ../HTML/Home.php?removed=1");
    exit();

} else {
    die("Error removing preference");
}