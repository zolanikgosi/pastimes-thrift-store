<?php
session_start();

// Include database connection
include 'DBConn.php';

// Initialize variables for error messages and sticky form values
$error = "";
$username = "";
$password = "";

// Path to your text file containing user data
$filename = "C:\wamp\wamp\www\pastimes\_resources\adminData.txt";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user input and sanitize
    $username = trim($_POST['username'] ?? ''); // Use null coalescing to avoid undefined index
    $password = trim($_POST['password'] ?? '');

    // Check password length
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        // Prepare SQL statement to fetch user data
        $stmt = $dbConnection->prepare("SELECT adminID, name, password, email FROM tblAdmin WHERE username = ?");
        if ($stmt === false) {
            die("Error preparing statement: " . $dbConnection->error);
        }

        // Bind parameters and execute
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) { // Use $user['password'] here
                // Set session variables
                $_SESSION['userID'] = $user['adminID']; // Correct variable name
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = 'Admin';
                
                header('Location: admin_dashboard.php'); // Redirect to management page
                exit;
            } else {
                $error = "Incorrect password. Please try again.";
            }
        } else {
            $error = "Admin user does not exist.";
        }
    }
}

// Check for the user data file and import if it exists
if (file_exists($filename)) {
    importAdminUsers($dbConnection, $filename);
} else {
    // Log this for debugging if needed
    // echo "User data file not found."; // Commented out to prevent premature message
}

function importAdminUsers($dbConnection, $filename) {
    if (($handle = fopen($filename, 'r')) !== false) {
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            if (count($data) < 4) { // Ensure there are enough fields
                continue; // Skip this line if it doesn't have enough data
            }
            list($name, $username, $plainPassword, $email) = $data; // Adjust based on your CSV structure

            // Hash the password
            $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);

            // Prepare insert statement with ON DUPLICATE KEY UPDATE
            $stmt = $dbConnection->prepare(
                "INSERT INTO tblAdmin (name, username, password, email) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                    name = VALUES(name), 
                    password = VALUES(password), 
                    email = VALUES(email)"
            );

            if ($stmt === false) {
                echo "Error preparing statement for user $username: " . $dbConnection->error . "<br>";
                continue; // Skip to the next iteration
            }

            $stmt->bind_param("ssss", $name, $username, $hashedPassword, $email);

            // Execute the statement
            if ($stmt->execute()) {
                // User imported successfully
                // echo "Admin user $username imported successfully.<br>"; // Commented out for debugging
            } else {
                echo "Error importing admin user $username: " . $stmt->error . "<br>";
            }
        }
        fclose($handle);
    } else {
        echo "Error opening the file.";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Pastimes is your go-to online store for high-quality branded used clothing, offering top-notch fashion at affordable prices.">
    <meta name="keywords" content="clothing store, online shopping, used clothing, affordable fashion, Pastimes, fashion">
    <meta name="revisit" content="30 days">
    <meta http-equiv="refresh" content="30">
    <meta name="robots" content="noindex, nofollow">
    <title>Pastimes Admin Login Page</title>
    <link rel="stylesheet" href="style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome for icons -->
</head>

<body class="login-page">
    
    <div class="login-container">
    <img src="_images/pastimes_logo.jpg" alt="Pastimes Logo" width="150">
        <h1>Admin Log In</h1>

        <?php if (isset($error) && $error): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form action="admin_login.php" method="POST">
            <div class="input-group">
                <i class="fas fa-user"></i> <!-- Icon for username -->
                <input type="text" name="username" placeholder="Admin Username" value="<?php echo htmlspecialchars($username); ?>" required> <!-- Sticky username -->
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i> <!-- Icon for password -->
                <input type="password" name="password" placeholder="Admin Password" required> <!-- Changed name to password -->
            </div>
            <button type="submit" name="login">Log In</button> <!-- Changed name to login -->
        </form>

        <!-- Go Back to Homepage Button -->
        <form action="index.php" method="get">
            <button class="btn" type="button" onclick="window.location.href='index.php';">Go Back to Homepage</button>
        </form>

    </div>

</body>
</html>
