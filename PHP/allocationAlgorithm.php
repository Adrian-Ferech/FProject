<?php
session_start();
require_once 'db.php';

// only lecturers or admins can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

$allocationType = $_POST['allocation_type'] ?? ''; // Check allocation type before running allocation

if (!in_array($allocationType, ['fcfs', 'grade'], true)) {
    die("Invalid allocation type");
}

$conn->begin_transaction();

// Load all active projects and project capacities
$projectCapacities = [];

$capacityResult = $conn->query("
    SELECT Project_ID, Lecturer_ID, capacity
    FROM projects
    WHERE Status = 1
");

if (!$capacityResult) {
    throw new Exception("Failed to load project capacities: " . $conn->error);
}

while ($row = $capacityResult->fetch_assoc()) {
    $projectCapacities[$row['Project_ID']] = [
        'capacity' => (int)$row['capacity'],
        'filled' => 0,
        'lecturer_id' => (int)$row['Lecturer_ID']
    ];
}

// Load existing allocations so we do not overwrite old ones
$allocatedStudents = [];

$existingAllocations = $conn->query("
    SELECT Student_ID, Project_ID
    FROM allocation
");

if (!$existingAllocations) {
    throw new Exception("Failed to load existing allocations: " . $conn->error);
}

while ($row = $existingAllocations->fetch_assoc()) {
    $studentID = (int)$row['Student_ID'];
    $projectID = (int)$row['Project_ID'];

    $allocatedStudents[$studentID] = true;

    if (isset($projectCapacities[$projectID])) {
        $projectCapacities[$projectID]['filled']++;
    }
}

// Allocation algorithm which runs three times for each preference that students have. Can be scaled with more methods or rounds if needed
for ($round = 1; $round <= 3; $round++) {

    if ($allocationType === 'fcfs') {
        $sql = "
            SELECT 
                p.Preference_ID,
                p.Student_ID,
                p.Project_ID,
                pr.Lecturer_ID
            FROM preferences p
            JOIN projects pr ON p.Project_ID = pr.Project_ID
            WHERE p.preference_order = ?
                AND pr.Status = 1
            ORDER BY p.Preference_ID ASC
        ";
    } else {
        $sql = "
            SELECT 
                p.Preference_ID,
                p.Student_ID,
                p.Project_ID,
                pr.Lecturer_ID,
                s.grade
            FROM preferences p
            JOIN projects pr ON p.Project_ID = pr.Project_ID
            JOIN students s ON p.Student_ID = s.Student_ID
            WHERE p.preference_order = ?
                AND pr.Status = 1
            ORDER BY s.grade DESC, p.Preference_ID ASC
        ";
    }

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $round);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($pref = $result->fetch_assoc()) {
        $studentID = (int)$pref['Student_ID'];
        $projectID = (int)$pref['Project_ID'];

        // Skip students already allocated
        if (isset($allocatedStudents[$studentID])) {
            continue;
        }

        // Skip inactive projects
        if (!isset($projectCapacities[$projectID])) {
            continue;
        }

        // Skip full projects
        if ($projectCapacities[$projectID]['filled'] >= $projectCapacities[$projectID]['capacity']) {
            continue;
        }

        $lecturerID = $projectCapacities[$projectID]['lecturer_id'];
        // Insert allocation
        $insert = $conn->prepare("
            INSERT INTO allocation (Student_ID, Project_ID, Lecturer_ID)
            VALUES (?, ?, ?)
        ");

        if (!$insert) {
            throw new Exception("Insert prepare failed: " . $conn->error);
        }

        $insert->bind_param("iii", $studentID, $projectID, $lecturerID);

        if (!$insert->execute()) {
            throw new Exception("Insert failed: " . $insert->error);
        }

        $allocatedStudents[$studentID] = true;
        $projectCapacities[$projectID]['filled']++;
    }

    $stmt->close();
}

$conn->commit();

header("Location: ../HTML/adminPage.php?allocated=1&type=" . urlencode($allocationType));
exit();
?>