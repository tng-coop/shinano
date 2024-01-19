<?php

// Database connection variables
$host = '127.0.0.1';
$username = 'sdev_rw';
$password = 'Kis0Shinan0DevRW';
$dbname = 'shinano_dev';
$charset = 'UTF8';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
if (!$conn->set_charset($charset)) {
    printf("Error loading character set %s: %s\n", $charset, $conn->error);
    exit();
}

// Turn off auto-commit
$conn->autocommit(FALSE);

try {
    // Start transaction
    $conn->begin_transaction();

    // Define the predefined values for the insert
    $attribute = 'L';
    $title = 'John job listing';
    $description = 'example job description 1';

    // Select only the first record from the user table
    $selectQuery = "SELECT id, email FROM user LIMIT 1";
    $result = $conn->query($selectQuery);
    if (!$result) {
        throw new Exception("Error in SELECT query: " . $conn->error);
    }

    // Fetch the first row
    $row = $result->fetch_assoc();
    if ($row) {
        // Extract user id and email from the row
        $userId = $row['id'];
        $email = $row['email'];
        $idOnUser="111111111111";
        print "$email";
        
        // Perform insert operation for the fetched row
        $insertQuery = "INSERT INTO job_entry (attribute, user, id_on_user, title, description, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, current_timestamp, current_timestamp)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("siiss", $attribute, $userId, $idOnUser, $title, $description);

        if (!$stmt->execute()) {
            throw new Exception("Error in INSERT query: " . $stmt->error);
        }
    } else {
        throw new Exception("No user found");
    }

    // Commit transaction
    $conn->commit();
    echo "Transaction completed successfully";

} catch (Exception $e) {
    // Rollback transaction if any error occurs
    $conn->rollback();
    echo "Transaction failed: " . $e->getMessage();
} finally {
    // Close statement and connection
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}

?>
