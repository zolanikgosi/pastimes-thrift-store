<?php
session_start(); // Start the session

// Include database connection
include 'DBConn.php';

// Initialize variables for error messages and sticky form values
$error = "";
$username = "";
$email = "";
$password = "";

// Minimum password length requirement
$minPasswordLength = 8;

// Path to your text file containing user data (if applicable)
$filename = "C:\wamp\wamp\www\pastimes\_resources\userData.txt";

function importUsers($dbConnection, $filename)
{
    if (($handle = fopen($filename, 'r')) !== false) {
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            list($name, $username, $plainPassword, $email, $status) = $data;

            // Debug output to check values
            error_log("Importing user: $username with plain password: $plainPassword"); 

            // Hash the password
            $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);
            error_log("Hashed password for user $username: $hashedPassword"); // Debug output

            $stmt = $dbConnection->prepare(
                "INSERT INTO tbluser (name, username, password, email, status) 
                VALUES (?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                    name = VALUES(name), 
                    password = VALUES(password), 
                    email = VALUES(email),  
                    status = VALUES(status)"
            );
            
            // Bind parameters (5 placeholders, 5 values)
            $stmt->bind_param("sssss", $name, $username, $hashedPassword, $email, $status);
            
            // Execute the statement
            if (!$stmt->execute()) {
                error_log("Execution failed: " . $stmt->error); // Log execution error
            }
        }
        fclose($handle);
    } else {
        error_log("Failed to open the file: $filename"); // Log file opening error
    }
}

// Check if the importUsers function should be called
if (file_exists($filename)) {
    importUsers($dbConnection, $filename);
}

// Handle user login if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user input and sanitize
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check if the password meets the minimum length requirement
    if (strlen($password) < $minPasswordLength) {
        $error = "Password must be at least $minPasswordLength characters long.";
    } else {
        // Prepare SQL statement to fetch user data based on username and email
        $stmt = $dbConnection->prepare("SELECT userID, name, password, email, status FROM tbluser WHERE username = ? AND email = ?");
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
                // Check if user is verified
                if ($user['status'] == 'verified') {
                    // Set session variables for verified users
                    $_SESSION['userID'] = $user['userID'];
                    $_SESSION['name'] = $user['name'];
        

                    // Display user's information
                    echo "<p>User " . htmlspecialchars($user['name']) . " is logged in.</p>";

                    // Display user's data in a table format
                    echo "<table>
                            <tr><th>UserID</th><td>" . htmlspecialchars($user['userID']) . "</td></tr>
                            <tr><th>Name</th><td>" . htmlspecialchars($user['name']) . "</td></tr>
                            <tr><th>Email</th><td>" . htmlspecialchars($user['email']) . "</td></tr>
                          </table>";

                    // Redirect to homepage
                    echo "<script>window.location.href='index.php';</script>";
                } else {
                    // Handle non-verified users
                    if ($user['status'] == 'pending') {
                        $error = "Your account is pending verification. Please wait for approval.";
                    } elseif ($user['status'] == 'rejected') {
                        $error = "Your account was rejected.";
                    }
                }
            } else {
                $error = "Incorrect password. Please try again.";
            }
        } else {
            $error = "User does not exist.";
        }

        $stmt->close(); // Close statement
        $dbConnection->close(); // Close connection
    }
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
    <link rel="stylesheet" href="style.css"> <!-- Ensure this file path is correct very important-->
    <title>Pastimes User Login Page</title>
</head>


<body class ="login-page">  <!--   very important-->
   <div class="login-container">   
   <img src="_images/pastimes_logo.jpg" alt="Pastimes Logo" width="150">
   <h1>Log In</h1>
    
        <?php if (!empty($error)): ?>
            <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form action="user_login.php" method="POST">
        
        <input type="text" name="username" placeholder="Username" required value="<?php echo htmlspecialchars($username); ?>">
        <input type="email" name="email" placeholder="Email Address" required value="<?php echo htmlspecialchars($email); ?>">
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" name ="login">Login</button>
    </form>

     <!-- Back to Homepage Button -->
     <form action="index.php" method="get">
            <button class="btn" type="button" onclick="window.location.href='index.php';">Back to Homepage</button>
        </form>
    </div>
</body>

</html>
