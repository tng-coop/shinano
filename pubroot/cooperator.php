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
$request_public_uid = ((isset($_GET['puid']) && int_string_p($_GET['puid']))
                     ? intval($_GET['puid']) : null);

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

$sql1
    = "SELECT name,email,public_uid,note,created_at"
    . "  FROM user"
    . "  WHERE public_uid = :public_uid";
$cooperator_thing = db_ask_ro($sql1, ['public_uid' => $request_public_uid], \PDO::FETCH_ASSOC)[0];


$ret_stmt_user_jobs
    = \Tx\with_connection($data_source_name, $sql_ro_user, $sql_ro_pass)(
        function($conn_ro) use ($request_public_uid){  
            return \TxSnn\view_job_things_by_public_uid($conn_ro, $request_public_uid);});
$job_entries = $ret_stmt_user_jobs->fetchAll(\PDO::FETCH_ASSOC);


// make actual content of cooperator
function html_text_of_specific_cooperator($cooperator_thing, $job_entries){
    // detect null
    if(is_null($cooperator_thing)) {
        return "not found";
    }

    // content of cooperator and cooperator's job things
    $cooperator_info_tml = html_text_of_cooperator($cooperator_thing);

    $jobs_table_tml = "<h3>".h("{$cooperator_thing['name']}'s bulletins")."</h3>"
                    . html_text_of_bulletins_table($job_entries, false);
    
    $tml_text = $cooperator_info_tml . "<hr />"
              . $jobs_table_tml;

    return $tml_text;
}


// prepare content and render by template

$cooperator_tml = html_text_of_specific_cooperator($cooperator_thing, $job_entries);

RenderByTemplate("template.html", "Cooperator - Shinano -",
                 $cooperator_tml);

?>
