<?php
session_start();

// Display the login form if the user hasn't submitted it yet
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    ?>

    <!DOCTYPE html>
    <html>
    <head>
        <title>Login Page</title>
    </head>
    <body>
        <h2>Login</h2>
        <form method="POST" action="index.php">
            Username: <input type="text" name="username" required><br><br>
            Password: <input type="password" name="password" required><br><br>
            <input type="submit" value="Login">
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </body>
    </html>

    <?php
    exit();
}

// Get the submitted username and password
$username = $_POST['username'];
$password = $_POST['password'];

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

// Prepare and execute the SQL statement securely to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Check if a user with the provided username exists
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    // Verify the password
    if (password_verify($password, $user['password'])) {
        echo "success";
    } else {
        echo "who the fuck are you?";
    }
} else {
    echo "who the fuck are you?";
}

$stmt->close();
$conn->close();
?>
