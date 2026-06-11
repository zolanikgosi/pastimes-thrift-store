<?php
include 'DBConn.php'; // Database connection file
$error = "";

// Initialize variables to avoid undefined variable warnings
$name = $username = $email = $password = "";

// Handle form submission for registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check if the password length is at least 8 characters
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        // Check if email or username already exists
        $checkStmt = $dbConnection->prepare("SELECT * FROM tblUser WHERE email = ? OR username = ?");
        $checkStmt->bind_param("ss", $email, $username);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email or username already exists. Please choose a different one.";
        } else {
            // Hash the password before storing it
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert new user into the database with a 'pending' status
            $stmt = $dbConnection->prepare("INSERT INTO tblUser (name, username, email, password, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->bind_param("ssss", $name, $username, $email, $hashedPassword);

            if ($stmt->execute()) {
                echo "<script>alert('Registration successful! Please wait for admin approval.'); window.location.href='user_login.php';</script>";
            } else {
                $error = "Registration failed. Please try again.";
            }
            $stmt->close();
        }
        $checkStmt->close();
    }
    $dbConnection->close();
}

// Display any error messages
if (!empty($error)) {
    echo "<script>alert('$error');</script>";
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
    <title>Pastimes Sign Up</title>
    <link rel="stylesheet" href="style.css">

</head>

<body>
    <div class="signup-page-container">
        <div class="signup-header">
            <img src="_images/pastimes_logo.jpg" alt="Pastimes Logo" class="signup-logo" width="150">
            <h1 class="signup-title">Create Your Account</h1>
            <p class="signup-description">To like, purchase an item, or chat with sellers, please create an account.</p>
        </div>

        <form action="" method="POST" class="signup-form">
            <div class="input-group">
                <label for="name" class="input-label">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required class="input-field">
            </div>

            <div class="input-group">
                <label for="username" class="input-label">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required class="input-field">
            </div>

            <div class="input-group">
                <label for="email" class="input-label">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required class="input-field">
            </div>

            <div class="input-group">
                <label for="password" class="input-label">Password</label>
                <input type="password" id="password" name="password" required class="input-field">
            </div>

            <button type="submit" class="btn-primary">Create Account</button>
        </form>

        <p class="existing-account">Already have an account? <a href="user_login.php" class="login-link">Log in here</a></p>

        <div class="account-type-selection">
            <p>Sign Up as:</p>
            <div class="account-buttons">
                <button class="btn-secondary" onclick="window.location.href='admin_registration.php';">Admin</button>
                <button class="btn-secondary" onclick="window.location.href='seller_registration.php';">Seller</button>
            </div>
            <button class="btn-secondary return-home" onclick="window.location.href='index.php';">Back to Homepage</button>
        </div>
    </div>
</body>

</html>
