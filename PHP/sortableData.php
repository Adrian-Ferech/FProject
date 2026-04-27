<?php
session_start();
require_once 'db.php';

$student_id = $_SESSION['uid'];

$stmt = $conn->prepare("
    SELECT 
        p.Project_ID, 
        p.title, 
        pref.preference_order
    FROM preferences pref
    INNER JOIN projects p 
        ON pref.Project_ID = p.Project_ID
    WHERE pref.Student_ID = ?
    ORDER BY pref.preference_order IS NULL, pref.preference_order ASC
");

$stmt->bind_param("i", $student_id);
$stmt->execute();


$result = $stmt->get_result();
$projects = $result->fetch_all(MYSQLI_ASSOC);


$conn->close();
?>