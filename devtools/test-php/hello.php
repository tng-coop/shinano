<?php
echo "Hello, World!<br>";

$servername = "127.0.0.1";
$username = "root";
$password = "";
$port = "3306";

try {
    $conn = new PDO("mysql:host=$servername;port=$port", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully<br>";

    // Query to show databases
    $stmt = $conn->query("SHOW DATABASES");
    
    while ($row = $stmt->fetch()) {
        echo "Database: " . $row["Database"] . "<br>";
    }
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
