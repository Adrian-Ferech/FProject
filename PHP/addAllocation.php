<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

$student_id = $_POST['student_id'] ?? null;
$project_id = $_POST['project_id'] ?? null;

if (!$student_id || !$project_id) {
    header("Location: ../HTML/manageAllocations.php?error=Missing required fields");
    exit();
}

// Check student not already allocated
$stmt = $conn->prepare("
    SELECT Allocation_ID
    FROM allocation
    WHERE Student_ID = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    header("Location: ../HTML/manageAllocations.php?error=Student already allocated");
    exit();
}
$stmt->close();

// Check project and capacity
$stmt = $conn->prepare("
    SELECT 
        p.Lecturer_ID,
        p.capacity,
        COUNT(a.Allocation_ID) AS allocated_count
    FROM projects p
    LEFT JOIN allocation a ON p.Project_ID = a.Project_ID
    WHERE p.Project_ID = ? AND p.Status = 1
    GROUP BY p.Project_ID, p.Lecturer_ID, p.capacity
");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../HTML/manageAllocations.php?error=Project not found");
    exit();
}

$project = $result->fetch_assoc();
$stmt->close();

if ((int)$project['allocated_count'] >= (int)$project['capacity']) {
    header("Location: ../HTML/manageAllocations.php?error=Project is full");
    exit();
}

$lecturer_id = $project['Lecturer_ID'];

// Insert allocation
$stmt = $conn->prepare("
    INSERT INTO allocation (Student_ID, Project_ID, Lecturer_ID)
    VALUES (?, ?, ?)
");
$stmt->bind_param("iii", $student_id, $project_id, $lecturer_id);

if ($stmt->execute()) {
    header("Location: ../HTML/manageAllocations.php?success=1");
    exit();
} else {
    header("Location: ../HTML/manageAllocations.php?error=Failed to add allocation");
    exit();
}