<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");

// Include the configuration file
$config = parse_ini_file(__DIR__ . "/../config.ini", true);
if ($config === false) {
    echo "Error: Unable to read the configuration file.";
    exit;
}

// $dsn = "{$sqlclient}:host={$dbhost};dbname={$dbname};charset=...";

$dsn = "mysql:host=localhost;dbname=shinano_dev";

// Connecting to the database using read-only credentials
$dbconn_ro = PDO_connect($dsn, $config['database']['readonly_user'], $config['database']['readonly_password']); 
$stmt_ro = $dbconn_ro->query("SELECT 1");
$row = $stmt_ro->fetch(PDO::FETCH_ASSOC);
print_r($row);

// Connecting to the database using read-write credentials
$dbconn_rw = PDO_connect($dsn, $config['database']['readwrite_user'], $config['database']['readwrite_password']);
$stmt_rw = $dbconn_rw->query("SELECT 1");
$row = $stmt_rw->fetch(PDO::FETCH_ASSOC);
print_r($row);

 ?>
