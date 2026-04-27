<?php // Simple connetion to the database for any page that needs it
$host = "localhost";
$user = "root";
$password = "";
$database = "projectallocation";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>