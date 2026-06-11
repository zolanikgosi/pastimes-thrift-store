<?php
session_start(); // Start the session

// Include database connection
include 'DBConn.php';

// Initialize variables for error messages and sticky form values
$error = "";
$username = "";
$email = "";
$password = "";

// Minimum password length requirements
$minPasswordLength = 8;

// Path to your text file containing seller data
$filename = "C:\wamp\wamp\www\pastimes\_resources\sellerData.txt";

// Function to import sellers from the text file
function importSellers($dbConnection, $filename)
{
    if (($handle = fopen($filename, 'r')) !== false) {
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            // Ensure there are exactly 5 elements (name, username, password, email, status)
            if (count($data) === 5) {
                list($name, $username, $plainPassword, $email, $status) = $data;

                // Hash the password
                $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);

                // Prepare insert statement with ON DUPLICATE KEY UPDATE
                $stmt = $dbConnection->prepare(
                    "INSERT INTO tblseller (name, username, password, email, status) 
                    VALUES (?, ?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                        name = VALUES(name), 
                        password = VALUES(password), 
                        email = VALUES(email), 
                        status = VALUES(status)"
                );

                if ($stmt === false) {
                    echo "Error preparing statement: " . $dbConnection->error;
                    continue; // Skip to the next row if there's an error
                }

                $stmt->bind_param("sssss", $name, $username, $hashedPassword, $email, $status);

                // Execute the statement and check for errors
                if (!$stmt->execute()) {
                    echo "Error executing statement: " . $stmt->error;
                }
                $stmt->close(); // Close statement
            
            }
        }
        fclose($handle);
    } else {
        echo "Could not open the file: $filename";
    }
}

// Check if the importSellers function should be called
if (file_exists($filename)) {
    importSellers($dbConnection, $filename);
}

// Handle seller login if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user input and sanitize
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check if the password meets the minimum length requirement
    if (strlen($password) < $minPasswordLength) {
        $error = "Password must be at least $minPasswordLength characters long.";
    } else {
        // Prepare SQL statement to fetch seller data based on username and email
        $stmt = $dbConnection->prepare("SELECT sellerID, name, password, email, status FROM tblseller WHERE username = ? AND email = ?");
        
        if ($stmt === false) {
            die("Error preparing statement: " . $dbConnection->error);
        }

        // Bind parameters and execute
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Check if seller is verified
                if ($user['status'] == 'verified') {
                    // Set session variables for verified sellers
                    $_SESSION['userID'] = $user['sellerID'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['role'] = 'Seller';

                    // Redirect to the seller dashboard
                    echo "<script>window.location.href='seller_dashboard.php';</script>";
                } else {
                    // Handle non-verified sellers
                    if ($user['status'] == 'pending') {
                        $error = "Your account is pending verification. Please wait for admin approval.";
                    } elseif ($user['status'] == 'rejected') {
                        $error = "Your account was rejected. Contact support for more information.";
                    }
                }
            } else {
                $error = "Incorrect password. Please try again.";
            }
        } else {
            $error = "Seller does not exist.";
        }

        $stmt->close(); // Close statement
    }
}

// Display any error messages
if (!empty($error)) {
    echo "<script>alert('$error');</script>";
}

// Close database connection at the end
$dbConnection->close();
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
    <title>Pastimes Seller Login Page</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="login-page">
    <div class="login-container">
    <img src="_images/pastimes_logo.jpg" alt="Pastimes Logo" width="150">
        <h1>Seller Login</h1>

        <?php if (!empty($error)): ?>
            <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form action="seller_login.php" method="POST">
            <input type="text" name="username" placeholder="Username" required value="<?php echo htmlspecialchars($username); ?>">
            <input type="email" name="email" placeholder="Email Address" required value="<?php echo htmlspecialchars($email); ?>">
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit" name="login">Log In</button>
        </form>

        <!-- Registration Link -->
        <a href="seller_registration.php" class="link">Don't have a seller account? Click here to register</a>

        <!-- Back to Homepage Button -->
        <form action="index.php" method="get">
            <button class="btn" type="button" onclick="window.location.href='index.php';">Back to Homepage</button>
        </form>
    </div>
</body>

</html>