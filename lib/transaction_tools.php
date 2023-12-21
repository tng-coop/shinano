<?php

// DataBase tools which uses transactions.php

declare(strict_types=1);

include_once(__DIR__ . '/./utilities.php');
include_once(__DIR__ . '/./transactions.php');

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


// ask DB about duplication

function select_duplicated_bulletins_from_db(string $email, string $title, int $id_on_user=-1){
    $sql_sel_dup = "SELECT J.id_on_user AS eid , U.email, U.public_uid, J.title"
                 . "  FROM user as U INNER JOIN job_entry AS J"
                 . "    ON U.id = J.user"
                 . "  WHERE J.title = :title"
                 . "    AND U.email = :email"
                 . "    AND J.id_on_user != :id_on_user"
                 . ";";

    $ret0 = db_ask_ro($sql_sel_dup, [":email"=>$email, ":title"=>$title, "id_on_user"=>$id_on_user],
                      \PDO::FETCH_ASSOC);
    return $ret0;
}

function check_title_duplicate_in_each_user(string $email, string $title, int $id_on_user=-1){
    // returns [success_p, message, duplicated_url_p];
    if(gettype($title)!=='string' || $title==="") {
        return [null, "invalid title.", false];
    }
    $duplicated_post = select_duplicated_bulletins_from_db($email, $title, $id_on_user);

    if($duplicated_post) {
        $dup0 = $duplicated_post[0];
        $duplicated_url = url_of_bulletin_detail($dup0['public_uid'], $dup0['eid']);
        return [null, $duplicated_url, true]; // duplicated
    } else {
        return ['not_duplicated', "", false]; // not duplicated
    }
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
                = "SELECT U.public_uid, U.name, J.id_on_user AS eid, J.attribute, J.title, J.description, "
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
