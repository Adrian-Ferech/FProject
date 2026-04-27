<?php
session_start();

$errorMsg = "";

if (isset($_POST['submitted'])) {

    if (empty($_POST['username']) || empty($_POST['password'])) {
        $errorMsg = "Please fill both the username and password fields!";
    } else {

        // Include database connection
        require_once(__DIR__ . '/../PHP/db.php');  

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Prepare statement
        $stmt = $conn->prepare("SELECT Student_id, password FROM students WHERE Name = ?");
        
        if ($stmt) {
            $stmt->bind_param("s", $_POST['username']); // "s" = string
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows > 0) {

                $row = $result->fetch_assoc();

                if ($_POST['password'] === $row['password']) {

                    $_SESSION['user'] = htmlspecialchars($_POST['username']);
                    $_SESSION['uid'] = $row['Student_id'];  // fixed column name
                    $_SESSION['role'] = 'student';

                    header("Location: Home.php");
                    exit();

                } else {
                    $errorMsg = "Error logging in: Password does not match";
                }

            } else {
                $errorMsg = "Error logging in: Username not found";
            }

            $stmt->close();

        } else {
            $errorMsg = "Failed to prepare statement";
        }

        $conn->close();
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
            <form action="login.php" method="post">
                <p> Student Login </p>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" size="15" maxlength="25" required placeholder="Enter your username">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" size="15" maxlength="25" required placeholder="Enter your password">
                <input type="submit" value="Login">
                <input type="hidden" name="submitted" value="TRUE">
                <p>Not a member? <a href="register.php"><span>Register</span></a></p>
            </form>
            <div class="admin-login">
                <a href="lecturerLogin.php">Lecturer Login</a>
                <a href="adminLogin.php">Admin Login</a>
            </div>
        </div>
    </div>    
</body>
</html>