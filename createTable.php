<?php
// Include the database connection script
include 'DBConn.php';

// Check if the database connection is successful
if (!$dbConnection) {
    die("<p>Database connection failed: " . mysqli_connect_error() . "</p>");
}

// Function to check if the table exists and drop it if it does
function checkAndRecreateTable($dbConnection) {
    // Check if the 'tbluser' table exists
    $checkTable = "SHOW TABLES LIKE 'tbluser'";
    $result = $dbConnection->query($checkTable);
    
    // If the table exists, drop it
    if ($result && $result->num_rows > 0) {
        $dropTable = "DROP TABLE IF EXISTS tbluser";
        if ($dbConnection->query($dropTable) === TRUE) {
            echo "<p>Existing 'tbluser' table dropped successfully.</p>";
        } else {
            die("<p>Error dropping 'tbluser' table: " . $dbConnection->error . "</p>");
        }
    }
    
    // Create the 'tbluser' table with the specified columns
    $createTable = "
    CREATE TABLE tbluser (
        userID INT AUTO_INCREMENT PRIMARY KEY,               -- Unique identifier for each user, auto-incremented
        name VARCHAR(100) NOT NULL,                          -- User's full name (max 100 characters)
        username VARCHAR(50) NOT NULL UNIQUE,                -- Unique username for the user (max 50 characters)
        password VARCHAR(255) NOT NULL,                      -- User's hashed password
        email VARCHAR(100) NOT NULL UNIQUE,                  -- User's email (max 100 characters)
        status ENUM('pending', 'verified', 'rejected') NOT NULL DEFAULT 'pending' -- Status, default to 'pending'
    )";

    // Execute the table creation query
    if ($dbConnection->query($createTable) === TRUE) {
        echo "<p>'tbluser' table created successfully.</p>";
    } else {
        die("<p>Error creating 'tbluser' table: " . $dbConnection->error . "</p>");
    }
}

// Function to load data from userData.txt and insert it into the 'tbluser' table
function loadUserData($dbConnection) {
    // Open the userData.txt file with the absolute path
    $filePath = "C:\wamp\wamp\www\pastimes\_resources\userData.txt"; // Adjusted path for Windows
    
    // Check if the file exists before trying to open it
    if (!file_exists($filePath)) {
        die("<p>File not found: $filePath</p>");
    }

    $file = fopen($filePath, "r");

    if (!$file) {
        die("<p>Could not open userData.txt at $filePath</p>");
    }

    // Prepare the SQL INSERT statement
    $insertStmt = $dbConnection->prepare("INSERT INTO tbluser (name, username, password, email, status) VALUES (?, ?, ?, ?, ?)");

    if (!$insertStmt) {
        die("<p>Error preparing the INSERT statement: " . $dbConnection->error . "</p>");
    }

    // Initialize variables for binding
    $name = $username = $password = $email  = $status = '';

    // Bind the variables to the statement
    $insertStmt->bind_param("sssss", $name, $username, $password, $email, $status);

    // Loop through each line in the file and insert it into the database
    while (($line = fgets($file)) !== false) {
        // Parse the line into an array
        $data = explode(",", trim($line));

        // Check if the data contains at least 4 required columns (name, username, password, email)
        if (count($data) < 4) {
            echo "<p>Insufficient data on line: $line</p>";
            continue; // Skip this iteration if data is not valid
        }

        // Assign values to the variables (in the same order as the table columns)
        $name = $data[0];
        $username = $data[1];
        
        // Hash the password before inserting it into the database
        $password = password_hash($data[2], PASSWORD_DEFAULT); // Secure hashing with default algorithm
        
        $email = $data[3];
        
        // Handle optional columns (role and status) with defaults
        $status = isset($data[5]) ? $data[5] : 'pending'; // Default to 'pending'

        // Execute the INSERT query
        if (!$insertStmt->execute()) {
            echo "<p>Error inserting data: " . $dbConnection->error . "</p>";
        }
    }

    // Close the file and the statement
    fclose($file);
    $insertStmt->close();

    echo "<p>Data loaded successfully from userData.txt.</p>";
}

// Execute the steps
checkAndRecreateTable($dbConnection); // Check and recreate the table
loadUserData($dbConnection);          // Load data from the text file into the table

// Close the database connection
$dbConnection->close();
?>
