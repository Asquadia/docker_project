<?php
session_start();

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the submitted username and password
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Database credentials from environment variables or secrets
        $db_user = trim(file_get_contents(getenv('MYSQL_USER_FILE')));
        $db_password = trim(file_get_contents(getenv('MYSQL_PASSWORD_FILE')));
        $db_host = getenv('MYSQL_HOST');
        $db_name = getenv('MYSQL_DATABASE');

        // Create a new MySQLi connection
        $conn = new mysqli($db_host, $db_user, $db_password, $db_name);

        // Check for connection errors
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if username already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $error = "Username already taken!";
        } else {
            // Hash Funtction to the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Add the new user into the database
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hashed_password);
            if ($stmt->execute()) {
                // Registration successful, redirect to login page
                header("Location: index.php");
                exit();
            } else {
                $error = "Error registering user!";
            }
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Page</title>
</head>
<body>
    <h2>Register</h2>
    <?php
    if (isset($error)) {
        echo '<p style="color:red;">'.$error.'</p>';
    }
    ?>
    <form method="POST" action="register.php">
        Username: <input type="text" name="username" required><br><br>
        Password: <input type="password" name="password" required><br><br>
        Confirm Password: <input type="password" name="confirm_password" required><br><br>
        <input type="submit" value="Register">
    </form>
    <p>Already have an account? <a href="index.php">Login here</a></p>
</body>
</html>
