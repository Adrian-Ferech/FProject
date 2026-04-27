<?php
session_start();
require_once(__DIR__ . '/../PHP/db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: adminLogin.php");
    exit();
}

// Admin Stats

// Total students without an allocation
$students = [];
$result = $conn->query("
    SELECT s.Student_ID, s.Name
    FROM students s
    LEFT JOIN allocation a ON s.Student_ID = a.Student_ID
    WHERE a.Student_ID IS NULL
    ORDER BY s.Name ASC
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Active projects with remaining capacity
$projects = [];
$result = $conn->query("
    SELECT 
        p.Project_ID,
        p.Title,
        l.Name AS LecturerName,
        p.capacity,
        COUNT(a.Allocation_ID) AS allocated_count
    FROM projects p
    JOIN lecturers l ON p.Lecturer_ID = l.Lecturer_ID
    LEFT JOIN allocation a ON p.Project_ID = a.Project_ID
    WHERE p.Status = 1
    GROUP BY p.Project_ID, p.Title, l.Name, p.capacity
    HAVING allocated_count < p.capacity
    ORDER BY p.Title ASC
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
}

// Existing allocations
$allocations = [];
$result = $conn->query("
    SELECT 
        a.Allocation_ID,
        s.Name AS StudentName,
        p.Title AS ProjectTitle,
        l.Name AS LecturerName
    FROM allocation a
    JOIN students s ON a.Student_ID = s.Student_ID
    JOIN projects p ON a.Project_ID = p.Project_ID
    JOIN lecturers l ON a.Lecturer_ID = l.Lecturer_ID
    ORDER BY s.Name ASC
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $allocations[] = $row;
    }
}

// Stats
$totalLecturers = 0;

// The amount of lecturers recorded in the DB
$result = $conn->query("SELECT COUNT(*) AS total FROM lecturers");
if ($result) {
    $totalLecturers = (int)$result->fetch_assoc()['total'];
}

// The amount of students recorded in the DB
$totalStudents = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM students");
if ($result) {
    $totalStudents = (int)$result->fetch_assoc()['total'];
}

// The amount of students that have preferences
$studentsWithPreferences = 0;
$result = $conn->query("SELECT COUNT(DISTINCT Student_ID) AS total FROM preferences");
if ($result) {
    $studentsWithPreferences = (int)$result->fetch_assoc()['total'];
}

// The amount of students that are allocated to a project
$totalAllocated = 0;
$result = $conn->query("SELECT COUNT(DISTINCT Student_ID) AS total FROM allocation");
if ($result) {
    $totalAllocated = (int)$result->fetch_assoc()['total'];
}

// The amount of students that are unallocated with a simple calculation
$unallocatedStudents = $totalStudents - $totalAllocated;

// Amount of ACTIVE projects
$totalProjects = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM projects WHERE Status = 1");
if ($result) {
    $totalProjects = (int)$result->fetch_assoc()['total'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../CSS/stylesheet2.css">
    <link rel="stylesheet" href="../CSS/adminstylesheet.css">
    <title>Manage Allocations</title>
</head>
<body>
<!-- Navbar Code -->
<ul class="navbar">
  <li><a href="Home.php">Home page</a></li>
  <li><a href="adminPage.php">Admin Page</a></li>
  <li><a href="manageAllocations.php">Allocations</a></li>
</ul>

<h1 class="pageTitle">Manage Allocations</h1>
<!-- Main page div -->
<div class="pageSplit">
    <!-- left side with flex direction column -->
    <div class="leftHalf">
        <!-- Box for student allocations -->
        <div class="panel">
            <h2>Manually Add Allocation</h2>
            <!-- Form to submit for assigning students -->
            <form class="allocationForm" action="../PHP/addAllocation.php" method="post">
                <!-- Selects student to assign -->
                <label for="student_id">Student:</label>
                <select name="student_id" id="student_id" required>
                    <option value="">Select student</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['Student_ID']; ?>">
                            <?php echo htmlspecialchars($student['Name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="project_id">Project:</label>
                <select name="project_id" id="project_id" required>
                    <!-- Selects project to assign -->
                    <option value="">Select project</option>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?php echo $project['Project_ID']; ?>">
                            <?php
                            echo htmlspecialchars($project['Title']) . ' - ' .
                                 htmlspecialchars($project['LecturerName']) .
                                 ' (Remaining: ' . ($project['capacity'] - $project['allocated_count']) . ')';
                            ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Add Allocation</button>
            </form>
        </div>
        <!-- Form to remove students from allocation -->
        <div class="panel">
            <h2>Current Allocations</h2>

            <?php if (!empty($allocations)): ?>
                <table class="allocTable">
                    <tr>
                        <th>Student</th>
                        <th>Project</th>
                        <th>Lecturer</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($allocations as $allocation): // loops through each allocation and displays the student / lecturer / project ?>
                        <tr>
                            <td><?php echo htmlspecialchars($allocation['StudentName']); ?></td>
                            <td><?php echo htmlspecialchars($allocation['ProjectTitle']); ?></td>
                            <td><?php echo htmlspecialchars($allocation['LecturerName']); ?></td>
                            <td>
                                <form action="../PHP/removeAllocation.php" method="post">
                                    <input type="hidden" name="allocation_id" value="<?php echo $allocation['Allocation_ID']; ?>">
                                    <button type="submit" onclick="return confirm('Remove this allocation?');">
                                        Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No allocations found.</p>
            <?php endif; ?>
        </div>
    </div>
    <!-- Displays Admin stats -->
    <div class="rightHalf">
        <div class="panel">
            <h2>Admin Stats</h2>

            <div class="statsColumn">
                <div class="statCard">
                    <span>Total Students</span>
                    <strong><?php echo $totalStudents; ?></strong>
                </div>

                <div class="statCard">
                    <span>Students With Preferences</span>
                    <strong><?php echo $studentsWithPreferences; ?></strong>
                </div>

                <div class="statCard">
                    <span>Total Allocated</span>
                    <strong><?php echo $totalAllocated; ?></strong>
                </div>

                <div class="statCard">
                    <span>Unallocated Students</span>
                    <strong><?php echo $unallocatedStudents; ?></strong>
                </div>

                <div class="statCard">
                    <span>Total Lecturers</span>
                    <strong><?php echo $totalLecturers; ?></strong>
                </div>

                <div class="statCard">
                    <span>Active Projects</span>
                    <strong><?php echo $totalProjects; ?></strong>
                </div>
            </div>
        </div>
    </div>

</div>

</body>
</html>