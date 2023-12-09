<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");



// GET's public_uid
if(isset($_GET['puid']) && 
   is_numeric($_GET['puid']) &&
   is_int(intval($_GET['puid']))){
    //print_r($_GET['puid']);
    $request_public_uid = intval($_GET['puid']);    
}

// deny invalid URL
if(! $request_public_uid){
    // if not logged in, Page to ask login.
    $invalid_puid_message_tml
        = "your requesting puid is invalid. <br />"
        . "<a href='./cooperators.php'>back to cooperators</a>";
    RenderByTemplate("template.html", "invalid puid - Shinano -",
                     $invalid_puid_message_tml);
    exit();
}

// deny none logged in user.
if(! $login->user()){
    // if not logged in, Page to ask login.
    RenderByTemplate("template.html", "Please Login - Shinano -",
                     "to show cooperators, Please login.");
    exit();
}


// if logged in, prepare specific cooperator page.


// parepare and execute DB and SQL

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

function html_text_of_cooperators($stmt_of_user, $stmt_of_jobs){

    $user_thing = array_map('h', $stmt_of_user->fetch());

    $jobs = $stmt_of_jobs->fetchAll(PDO::FETCH_ASSOC);
    
    // html of user and user's job things
    $jobs_text = array_reduce($jobs,
                              fn($carry, $job) => 
                              $carry . "<pre>" .  h(implode(", ", $job)) . "</pre>\n",
                              "");
    
    $tml_text 
        = "<h3>{$user_thing['name']}</h3>"
        . "<ul>"
        . "  <li>created: {$user_thing['created_at']}</li>"
        . "  <li>email: {$user_thing['email']}</li>"
        . "  <li>public_uid: {$user_thing['public_uid']}</li>"
        . "</ul>"
        . "<p>{$user_thing['note']}</p>"
        . "<hr />"
        . "<p>" . $jobs_text . "</p>";


    return $tml_text;
}


// prepare template

$cooperator_tml = html_text_of_cooperators($ret_stmt_user, $ret_stmt_user_jobs);

RenderByTemplate("template.html", "Cooperator - Shinano -",
                 $cooperator_tml);

?>
