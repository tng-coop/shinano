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
    $tpl = new TemplateAndConfigs();
    $tpl->page_title = "Please login - Shinano";
    $tpl->content_actual = "to show cooperator, Please login.";
    $tpl->eval_template("template.html");

    exit();
}

// deny none logged in user.
if(! $login->user()){
    $tpl = new TemplateAndConfigs();
    $tpl->page_title = "Please login - Shinano";
    $tpl->content_actual = "to show cooperator, Please login.";
    $tpl->eval_template("template.html");

    exit();
}




// if logged in, prepare specific cooperator page.


//$request_public_uid = 100;




// parepare and execute DB and SQL
//$wconn_ro = \PDO($data_source_name, $sql_ro_user, $sql_ro_pass);


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

$tpl = new TemplateAndConfigs();
    
$cooperators_tml = html_text_of_cooperators($ret_stmt_user, $ret_stmt_user_jobs);

$tpl->page_title = "cooperators - Shinano";

$tpl->content_actual = "{$cooperators_tml}";

// apply and echos template
$tpl->eval_template("template.html");


?>
