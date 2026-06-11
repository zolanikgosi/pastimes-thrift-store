<?php

// Include the database connection
include 'DBConn.php'; // Ensure this line is present

$sqlFile = 'MyClothingStore.sql';

// Enable error reporting for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check if the SQL file exists
if (file_exists($sqlFile)) {
    // Get the contents of the SQL file
    $sqlCommands = file_get_contents($sqlFile);
    // Split the SQL commands into an array
    $sqlStatements = explode(';', $sqlCommands);

    // Disable foreign key checks to avoid constraint issues
    $dbConnection->query('SET FOREIGN_KEY_CHECKS = 0;');

    // Drop the existing tables if they exist
    $dropTablesSQL = "DROP TABLE IF EXISTS tblAdmin, tblSeller, tblUser, tblClothes, tblAorder, tbl_item;";
    
    // Execute the drop tables query
    if ($dbConnection->query($dropTablesSQL) === TRUE) {
        echo 'Existing tables dropped successfully.<br>';
        
        // Loop through each SQL statement from the file
        foreach ($sqlStatements as $sql) {
            $sql = trim($sql);
            // Create the table only if the statement is not empty
            if (!empty($sql)) {
                try {
                    // Execute the SQL statement
                    if ($dbConnection->query($sql) === TRUE) {
                        echo 'Table created successfully.<br>';
                    } else {
                        echo 'Error creating table: ' . $dbConnection->error . '<br>';
                    }
                } catch (Exception $e) {
                    echo 'Error executing query: ' . $e->getMessage() . '<br>';
                }
            }
        }
    } else {
        echo 'Error dropping tables: ' . $dbConnection->error . '<br>';
    }

    // Re-enable foreign key checks
    $dbConnection->query('SET FOREIGN_KEY_CHECKS = 1;');
} else {
    echo 'SQL file not found: ' . $sqlFile . '<br>';
}

// Close the connection
$dbConnection->close();  // Close the connection

?>
