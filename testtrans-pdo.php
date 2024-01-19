<?php

// Database connection variables
$host = '127.0.0.1';
$username = 'sdev_rw';
$password = 'Kis0Shinan0DevRW';
$dbname = 'shinano_dev';
$charset = 'UTF8';

// Create PDO connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Turn off auto-commit
    $conn->beginTransaction();

    // Define the predefined values for the insert
    $attribute = 'L';
    $title = 'John job listing';
    $description = 'example job description 1';

    // Select only the first record from the user table
    $selectQuery = "SELECT id, email FROM user LIMIT 1";
    $stmt = $conn->prepare($selectQuery);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Fetch the first row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // Extract user id and email from the row
        $userId = $row['id'];
        $email = $row['email'];
        $idOnUser="111111111111";
        print "$email";

        // Perform insert operation for the fetched row
        $insertQuery = "INSERT INTO job_entry (attribute, user, id_on_user, title, description, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, current_timestamp, current_timestamp)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->execute([$attribute, $userId, $idOnUser, $title, $description]);
    } else {
        throw new Exception("No user found");
    }

    // Commit transaction
    $conn->commit();
    echo "Transaction completed successfully";

} catch (PDOException $e) {
    // Rollback transaction if any error occurs
    $conn->rollBack();
    echo "Transaction failed: " . $e->getMessage();
} catch (Exception $e) {
    // Handle any other exceptions
    $conn->rollBack();
    echo "Transaction failed: " . $e->getMessage();
}
?>
