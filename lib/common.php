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

// search job entry

function search_job_entries($search_pattern_text, $offset_from, $bulletin_per_page){
    global $data_source_name, $sql_ro_user, $sql_ro_pass;
    
    [$job_entries, $n_entries] 
    = \Tx\with_connection($data_source_name, $sql_ro_user, $sql_ro_pass)(
        function($conn_ro) use ($search_pattern_text, $bulletin_per_page, $offset_from) {
            $sql_common 
                = "  WHERE (   (J.closed_at IS NULL AND J.opened_at IS NOT NULL)" // opened entries
                . "         OR  J.opened_at > J.closed_at)"                       // opened entries
                . "    AND (    J.title       LIKE CONCAT('%', :pattern, '%')"
                . "         OR  J.description LIKE CONCAT('%', :pattern, '%'))"
                ;

            $sql_n_entries // counter of result searched
                = "SELECT COUNT(*) as count"
                . "  FROM job_entry AS J"
                . "  {$sql_common}"
                . ";";
            $stmt = $conn_ro->prepare($sql_n_entries);
            $stmt->execute([':pattern'=>$search_pattern_text]);
            $n_entries = $stmt->fetchAll(\PDO::FETCH_ASSOC)[0]['count'];

            $sql_search // result of entries searched by text which is sorted and lingth limited.
                = "SELECT U.public_uid, U.name, J.id, J.attribute, J.title, J.description, "
                . "    J.created_at, J.updated_at, J.opened_at, J.closed_at"
                . "  FROM user AS U INNER JOIN job_entry AS J"
                . "    ON U.id = J.user"
                . "  {$sql_common}"
                . "  ORDER BY J.opened_at DESC"
                . "  LIMIT {$bulletin_per_page} OFFSET {$offset_from}"
                . ";";
            $stmt = $conn_ro->prepare($sql_search);
            $stmt->execute([':pattern'=>$search_pattern_text]);
            $job_entries = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
            return [$job_entries, $n_entries];
        });
    return [$job_entries, $n_entries];
}

// search cooperators

function search_cooperators($search_pattern_text, $offset_from, $cooperators_per_page){
    global $data_source_name, $sql_ro_user, $sql_ro_pass;
    
    [$cooperators, $n_cooperators]
    = \Tx\with_connection($data_source_name, $sql_ro_user, $sql_ro_pass)(
        function($conn_ro) use ($search_pattern_text, $cooperators_per_page, $offset_from) {
            $sql_common
                = "  WHERE (TRUE)" // opened user
                . "    AND (    U.name  LIKE CONCAT('%', :pattern, '%')"
                . "         OR  U.note  LIKE CONCAT('%', :pattern, '%')"
                . "         OR  U.email LIKE CONCAT('%', :pattern, '%'))"
                ;
            $sql_n_cooperators // counter of result searched
                = "SELECT COUNT(*) as count"
                . "  FROM user AS U"
                . "  {$sql_common}"
                . ";";
            $stmt = $conn_ro->prepare($sql_n_cooperators);
            $stmt->execute([':pattern'=>$search_pattern_text]);
            $n_cooperators = $stmt->fetchAll(\PDO::FETCH_ASSOC)[0]['count'];

            $sql_search // result of searched cooperators
                = "SELECT U.name, U.email, U.public_uid, U.note, U.created_at"
                . "  FROM user as U"
                . "  {$sql_common}"
                . "  ORDER BY U.created_at DESC"
                . "  LIMIT {$cooperators_per_page} OFFSET {$offset_from}"
                . ";";
            $stmt = $conn_ro->prepare($sql_search);
            $stmt->execute([':pattern'=>$search_pattern_text]);
            $cooperators = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return [$cooperators, $n_cooperators];
        });

    return [$cooperators, $n_cooperators];
}

?>

