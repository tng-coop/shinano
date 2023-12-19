<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");


// deny not loggedin request
if(! $login->user()){
    please_login_page();
    exit();
}


// if logged in request, prepare contents.



// GET's values

// oid: each user Own jobentry ID
$request_oid = ((isset($_GET['oid']) && int_string_p($_GET['oid']))
                     ? intval($_GET['oid']) : null);

// puid: Public_UID (public user id)
$request_puid = ((isset($_GET['puid']) && int_string_p($_GET['puid']))
                 ? intval($_GET['puid']) : null);


// deny invalid URL
if (! $request_oid || ! $request_puid){
    $invalid_eid_message_tml
        = "your requesting ids are invalid. <br />"
        . "<a href='./bulletin_board.php'>back to BBS</a>";
    RenderByTemplate("template.html", "invalid eid - Shinano -",
                     $invalid_eid_message_tml);
    exit();
}

// ask DB
$job_entry = \Tx\with_connection($data_source_name, $sql_ro_user, $sql_ro_pass)(
    function($conn_ro) use ($request_puid, $request_oid){
        $stmt = \TxSnn\view_job_thing_by_public_uid_and_id_on_user
              ($conn_ro, $request_puid, $request_oid);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    });


// make actual content of bulletin
function html_text_of_bulletin_of_page($job_entry){
    // detect null
    if(is_null($job_entry)) {
        return "not found";
    }

    // detect not opened bulletin
    global $login;
    if(! job_entry_opened_p($job_entry['opened_at'], $job_entry['closed_at'])) {
        global $request_oid;
        if($login->user('public_uid') == $job_entry['public_uid'] && 
           $job_entry['eid'] == $request_oid) {
            return "bulletin is not opened";
        } else {
            return "bulletin is not found";
        }
    }

    // content
    $tml_text = html_text_of_bulletin($job_entry);;

    return $tml_text;
}


// prepare content and render by template

$bulletins_list_tml = html_text_of_bulletin_of_page($job_entry);

RenderByTemplate("template.html", "Look for bulletin - Shinano -",
                 $bulletins_list_tml);

?>
