<?php

declare(strict_types=1);

include_once(__DIR__ . '/./utilities.php');
include_once(__DIR__ . '/./transactions.php');
include_once(__DIR__ . '/./csrf.php');
include_once(__DIR__ . '/./user_login.php');

// session start if not started.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// instances
$csrf = new \CSRF\CSRF();
$login = new \USER_LOGIN\LOGIN();

?>

