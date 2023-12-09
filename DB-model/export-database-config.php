<?php
$configFilePath = '../config.ini';
$config = parse_ini_file($configFilePath, true);

if ($config === false) {
    echo "Error parsing the INI file.";
    exit;
}

// Replace 'database' with the actual section name in your INI file
if (isset($config['database'])) {
    foreach ($config['database'] as $key => $value) {
        // Escaping values to ensure they are safe to be sourced
        $safeValue = escapeshellarg($value);
        echo "export $key=$safeValue\n";
    }
} else {
    echo "Section 'database' not found in the INI file.";
}
?>