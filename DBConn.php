<?php
// Start output buffering
ob_start();

// Define the database connection parameters
$dbHost = "localhost"; // The server address where the database is hosted
$dbUser = "root"; // The username used to connect to the database
$dbPassword = ""; // The password for the database user (leave empty if no password is set)
$dbName = "clothingstore"; // The name of the database to connect to

// Create a new connection to the MySQL database using the provided parameters
$dbConnection = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);

// Check if the connection was successful
if ($dbConnection->connect_error) {
    // If the connection fails, output an error message
    die("<p style='color: red;'>Connection failed: " . $dbConnection->connect_error . "</p>");
} else {
    // If the connection is successful, confirm with a success message (optional for debugging)
    // echo "<p style='color: green;'>Connected to the database successfully!</p>";
}

// Flush output buffering to the browser
ob_end_flush();
?>
