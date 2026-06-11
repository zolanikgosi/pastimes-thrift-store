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
        $checkStmt = $dbConnection->prepare("SELECT * FROM tblSeller WHERE email = ? OR username = ?");
        $checkStmt->bind_param("ss", $email, $username);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email or username already exists. Please choose a different one.";
        } else {
            // Hash the password before storing it
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert new seller into the database with a 'pending' status
            $stmt = $dbConnection->prepare("INSERT INTO tblSeller (name, username, email, password, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->bind_param("ssss", $name, $username, $email, $hashedPassword);

            if ($stmt->execute()) {
                echo "<script>alert('Registration successful! Please wait for admin approval.'); window.location.href='seller_login.php';</script>";
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
    <title>Pastimes Seller Registration Page</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="signup-container">
    <img src="_images/pastimes_logo.jpg" alt="Pastimes Logo" width="150">
        <h1>Seller Registration</h1>
        <p>To sell an item, please create your account. Admin approval is required before you can list items.</p>

        <form action="" method="POST"> <!-- Form submission handled in the same PHP file -->
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            <div class="form-group">
                <label for="username">Create Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Create Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Register</button>
        </form>

        <p>Already have an account? <a href="seller_login.php">Click here to login</a></p> 
        
        <button class="btn" onclick="window.location.href='index.php';">Back to Homepage</button>
    </div>
</body>

</html>


