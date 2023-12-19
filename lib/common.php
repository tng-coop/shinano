<?php

declare(strict_types=1);

include_once(__DIR__ . '/./utilities.php');
include_once(__DIR__ . '/./transactions.php');
include_once(__DIR__ . '/./transaction_tools.php');
include_once(__DIR__ . '/./csrf.php');
include_once(__DIR__ . '/./user_login.php');
include_once(__DIR__ . '/./tml_templates.php');


// session start if not started.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// instances
$csrf = new \CSRF\CSRF();
$login = new \USER_LOGIN\LOGIN();

// please login page
function please_login_page(string $message="Please Login.",
                           string $title="Please Login - Shinano - "){
    RenderByTemplate("template.html", $title, $message);
}

// redirect page
function redirect_page(string $url_redirect){
    $meta_redirect = "<meta http-equiv='refresh' content='5;url=${url_redirect}' />";
    RenderByTemplate("template.html", "Redirect - Shinano -",
                     $meta_redirect .
                     "Invalid URL. redirect in 5 second." .
                     "to <a href='${url_redirect}'>here</a>");
}


?>

