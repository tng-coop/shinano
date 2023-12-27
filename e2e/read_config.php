<?php
// Check if the correct number of arguments is passed
if ($argc !== 3) {
    echo "Usage: php read_config.php [section] [key]\n";
    exit(1);
}

$section = $argv[1];
$key = $argv[2];

// Read the config.ini file
$config = parse_ini_file('../config.ini', true);

// Check if the section and key exist
if (!isset($config[$section][$key])) {
    echo "Error: Specified section or key not found in config.ini\n";
    exit(1);
}

// Output the value
echo $config[$section][$key] . "\n";
