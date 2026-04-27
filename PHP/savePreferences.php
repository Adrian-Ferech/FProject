<?php
session_start();
require_once(__DIR__ . '/db.php');

// Get student id
$student_id = $_SESSION['uid'];

// Get data from JS ranking
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo "No data received";
    exit();
}

$stmt = $conn->prepare("DELETE FROM preferences WHERE Student_ID = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->close();

// Insert preferences in order given to algorithm
$stmt = $conn->prepare("INSERT INTO preferences (Student_ID, Project_ID, preference_order) VALUES (?, ?, ?)");

foreach ($data as $pref) {
    $project_id = $pref['project_id'];
    $order = $pref['order'];

    $stmt->bind_param("iii", $student_id, $project_id, $order);
    $stmt->execute();
}

$stmt->close();
$conn->close();

echo "Preferences saved successfully!";
?>