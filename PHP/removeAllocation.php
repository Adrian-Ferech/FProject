<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

$allocation_id = $_POST['allocation_id'] ?? null;

if (!$allocation_id) {
    header("Location: ../HTML/manageAllocations.php?error=Invalid allocation");
    exit();
}

$stmt = $conn->prepare("
    DELETE FROM allocation
    WHERE Allocation_ID = ?
");
$stmt->bind_param("i", $allocation_id);

if ($stmt->execute()) {
    header("Location: ../HTML/manageAllocations.php?success=1");
    exit();
} else {
    header("Location: ../HTML/manageAllocations.php?error=Failed to remove allocation");
    exit();
}