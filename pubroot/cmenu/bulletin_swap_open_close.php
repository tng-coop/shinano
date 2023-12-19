<?php

declare(strict_types=1);

include_once(__DIR__ . "/../../lib/common.php");
include_once(__DIR__ . "/../../lib/form_check.php");
include_once(__DIR__ . '/../../lib/transactions.php');



// deny not loggedin request
if(! $login->user()){
    please_login_page();
    exit();
}

// deny not POST request.
if($request_method!="POST"){
    RenderByTemplate("template.html", "invalid request - Shinano -", "invalid request");
    exit();
}

// deny invalid POST parameters
print_r($_POST);

if(!isset($_POST['demand']) || !int_string_p($_POST["oid"])) {
    RenderByTemplate("template.html", "invalid request - Shinano -", "invalid request.");
    exit();
}

// deny CSRF unsafe access
if(!$csrf->checkToken()){
    RenderByTemplate("template.html", "invalid request - Shinano -", "invalid request. token error");
    exit();
}

// set variables

$loggedin_email = $login->user('email');
///$public_uid = $login->user('public_uid');

$user_own_id = intval($_POST['oid']);

$demand = $_POST['demand'];

// UPDATE DB
if ($demand=='let_open') {
    \Tx\with_connection($data_source_name, $sql_rw_user, $sql_rw_pass)(
        function($conn_rw) use ($loggedin_email, $user_own_id) {
            \TxSnn\open_job_thing($conn_rw, $loggedin_email, $user_own_id);
            return true;});

    $message = "opened now";
}
elseif ($demand=='let_close') {
    \Tx\with_connection($data_source_name, $sql_rw_user, $sql_rw_pass)(
        function($conn_rw) use ($loggedin_email, $user_own_id) {
            print_r($user_own_id);
            \TxSnn\close_job_thing($conn_rw, $loggedin_email, $user_own_id);
            return true;});

    $message = "closed now";
}
else {
    RenderByTemplate("template.html", "invalid request - Shinano -", "invalid request. step error");
    exit();
}


// prepare content

$bulletin_url = url_of_bulletin_detail($login->user('public_uid'), $user_own_id);

$content_html = <<<CONTENT
<a href='{$bulletin_url}'>selected bulletin</a> is ${message}.
<br />
or back to <a href='{$pubroot}cmenu/index.php'>cooperator's menu</a>
<br />
or back to <a href='{$pubroot}cmenu/bulletins.php'>your bulletins edit</a>

CONTENT;


// Render to HTML by template.

RenderByTemplate("template.html", "{$title_part} - Shinano -", $content_html);


?>
