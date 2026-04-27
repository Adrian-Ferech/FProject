<?php
session_start(); // Start session for login, consisntent across pages.
require_once(__DIR__ . '/../PHP/db.php'); // Require DB connection to load all data the page needs.

$errorMsg = ""; // Set empty error message for displaying errors later

if (isset($_POST['submitted'])) { // If Login is pressed on the admin page then...

    if (empty($_POST['username']) || empty($_POST['password'])) { // Check that username OR password are not empty
        $errorMsg = "Please fill all fields!"; // Set error message if wrong
    } else {

        $username = $_POST['username']; // Store  temporary details for authentication
        $password = $_POST['password'];

        // Query to be executed later 
        $stmt = $conn->prepare("
            SELECT Admin_ID, password 
            FROM admins 
            WHERE Name = ?
        ");

        if ($stmt) { // If query is invalid then returns error
            $stmt->bind_param("s", $username);
            $stmt->execute(); // Execute query

            $result = $stmt->get_result(); // Get results of query.

            if ($result->num_rows > 0) { // If 0 results are returned then user does not exist or not found and return error

                $row = $result->fetch_assoc();

                // Comparing db to input, no advanced safety features as explained in report
                if ($password === $row['password']) {

                    // Sets session variables
                    $_SESSION['admin_id'] = $row['Admin_ID'];
                    $_SESSION['user'] = $username;
                    $_SESSION['role'] = "admin";

                    header("Location: adminPage.php"); // Redirect to Admin dashboard if everything is valid
                    exit();

                } else {
                    $errorMsg = "Incorrect password";
                }

            } else {
                $errorMsg = "Admin not found";
            }

            $stmt->close(); // Close query

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
    <!-- Main container for styling and positioning-->
    <div class="signup-container"> 
        <div class="signup-form-container">
          <!-- Form Start with all inputs -->
            <form action="adminLogin.php" method="post">
                <p> Admin Login </p>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" size="15" maxlength="25" required placeholder="Enter your username">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" size="15" maxlength="25" required placeholder="Enter your password">
                <input type="submit" value="Login">
                <input type="hidden" name="submitted" value="TRUE">
            </form>
            <!-- Links to other Login pages -->
            <div class="admin-login">
                <a href="lecturerLogin.php">Lecturer Login</a>
                <a href="login.php">Student Login</a>
            </div>
        </div>
    </div>    
</body>
</html>