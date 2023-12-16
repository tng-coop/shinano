<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");


// deny not loggedin request
if(! $login->user()){
    please_login_page();
    exit();
}


// if logged in, prepare specific cooperator page.

// GET's public_uid
if(isset($_GET['puid']) && int_string_p($_GET['puid'])){
    $request_public_uid = intval($_GET['puid']);    
} else {
    $request_public_uid = false;
}

// deny invalid URL
if(! $request_public_uid){
    $invalid_puid_message_tml
        = "your requesting puid is invalid. <br />"
        . "<a href='./cooperators.php'>back to cooperators</a>";
    RenderByTemplate("template.html", "invalid puid - Shinano -",
                     $invalid_puid_message_tml);
    exit();
}

// ask DB

$ret_stmt_user
    = \Tx\with_connection($data_source_name, $sql_ro_user, $sql_ro_pass)(
        function($conn_ro) use ($request_public_uid){  
            $sql1 = "SELECT name,email,public_uid,note,created_at"
                  . "  FROM user"
                  . "  WHERE public_uid = :public_uid";
            $stmt = $conn_ro->prepare($sql1);
            $stmt->execute(['public_uid' => $request_public_uid]);
            return $stmt;
        });

$ret_stmt_user_jobs
    = \Tx\with_connection($data_source_name, $sql_ro_user, $sql_ro_pass)(
        function($conn_ro) use ($request_public_uid){  
            return \TxSnn\view_job_things_by_public_uid($conn_ro, $request_public_uid);
        });


// make actual content of cooperators

function html_text_of_specific_cooperator($stmt_of_user, $stmt_of_jobs){

    $user_thing = array_map('h', $stmt_of_user->fetch());

    $jobs = $stmt_of_jobs->fetchAll(\PDO::FETCH_ASSOC);
    
    // html of user and user's job things
    $cooperator_info_tml = html_text_of_cooperator($user_thing);
    $jobs_table_tml = "<h3>".h("{$user_thing['name']}'s bulletins")."</h3>"
                    . html_text_of_bulletins_table($jobs, false);
    
    $tml_text = $cooperator_info_tml . "<hr />"
              . $jobs_table_tml;

    return $tml_text;
}


// prepare template

$cooperator_tml = html_text_of_specific_cooperator($ret_stmt_user, $ret_stmt_user_jobs);

RenderByTemplate("template.html", "Cooperator - Shinano -",
                 $cooperator_tml);

?>
