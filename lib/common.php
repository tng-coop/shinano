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


// db_ask_ro
function db_ask_ro(string $query, ?array $params=null, int $mode = \PDO::FETCH_DEFAULT){
    global $data_source_name, $sql_ro_user, $sql_ro_pass;
    return \Tx\with_connection($data_source_name, $sql_ro_user, $sql_ro_pass)(
        function($conn_ro) use($query, $params, $mode){
            $stmt = $conn_ro->prepare($query);
            $stmt->execute($params);
            $ret =  $stmt->fetchAll($mode);
            return $ret;
        });
}

// please login page
function please_login_page(string $message="Please Login.",
                           string $title="Please Login - Shinano - "){
    RenderByTemplate("template.html", $title, $message);
}


?>

