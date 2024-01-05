<?php

// Path to your .ini file
$iniFilePath = __DIR__ . '/../config.ini';

// Parse the .ini file with sections
$iniArray = parse_ini_file($iniFilePath, true);

// Convert the array to JSON
$json = json_encode($iniArray, JSON_PRETTY_PRINT);

// Output the JSON
echo $json;

?>