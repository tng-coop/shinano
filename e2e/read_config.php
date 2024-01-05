<?php
// Check if the correct number of arguments is passed
if ($argc !== 3) {
    echo "Usage: php read_config.php [section] [key]\n";
    exit(1);
}

$section = $argv[1];
$key = $argv[2];

// Define the path to the config.ini file
// Assuming the config.ini file is in a directory one level above the current script directory
$ini_file_path = __DIR__ . '/../config.ini';

// Check if the config file exists
if (!file_exists($ini_file_path)) {
    echo "Error: config.ini file not found at {$ini_file_path}\n";
    exit(1);
}

// Read the config.ini file
$config = parse_ini_file($ini_file_path, true);


// Check if the section and key exist
if (!isset($config[$section][$key])) {
    echo "Error: Specified section or key not found in config.ini\n";
    exit(1);
}

// Output the value
echo $config[$section][$key] . "\n";
