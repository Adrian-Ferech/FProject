<?php
session_start();
require_once(__DIR__ . '/../PHP/db.php');

$errorMsg = "";

if (isset($_POST['submitted'])) {

    if (empty($_POST['username']) || empty($_POST['password'])) {
        $errorMsg = "Please fill all fields!";
    } else {

        $username = $_POST['username'];
        $password = $_POST['password'];

        // Pull user name and password from DB
        $stmt = $conn->prepare("
            SELECT Lecturer_ID, password 
            FROM lecturers 
            WHERE Name = ?
        ");

        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows > 0) {

                $row = $result->fetch_assoc();
                // Compare passwords
                if ($password === $row['password']) {

                    // Store lecturer variables
                    $_SESSION['lecturer_id'] = $row['Lecturer_ID'];
                    $_SESSION['user'] = $username;
                    $_SESSION['role'] = "lecturer";

                    header("Location: lecturerPage.php");
                    exit();

                } else {
                    $errorMsg = "Incorrect password";
                }

            } else {
                $errorMsg = "Lecturer not found";
            }

            $stmt->close();

        } else {
            $errorMsg = "Query failed: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/loginstylesheet.css">
    <title>Login</title>
</head>
<body>
    <div class="signup-container">
        <div class="signup-form-container">
            <form action="lecturerLogin.php" method="post">
                <p> Lecturer Login</p>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" size="15" maxlength="25" required placeholder="Enter your username">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" size="15" maxlength="25" required placeholder="Enter your password">
                <input type="submit" value="Login">
                <input type="hidden" name="submitted" value="TRUE">
            </form>
            <div class="admin-login">
                <a href="login.php">Student Login</a>
                <a href="adminLogin.php">Admin Login</a>
            </div>
        </div>
    </div>       
</body>
</html>