<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");


// deny not loggedin request
if(! $login->user()){
    please_login_page();
    exit();
}


// if logged in request, prepare contents.

// Note:
// addressing method for specific job_entry 
// is going to be changed from jobentry.id into user each own jobentry's id.

// GET's eid
$request_entry_id = ((isset($_GET['eid']) && int_string_p($_GET['eid']))
                     ? intval($_GET['eid']) : null);

// deny invalid URL
if (! $request_entry_id){
    $invalid_eid_message_tml
        = "your requesting eid is invalid. <br />"
        . "<a href='./bulletin_board.php'>back to BBS</a>";
    RenderByTemplate("template.html", "invalid eid - Shinano -",
                     $invalid_eid_message_tml);
    exit();
}

// ask DB

$sql1
    = "SELECT U.name, U.email, U.public_uid, "
    . "    J.id, J.attribute, J.user, J.title, J.description, J.created_at, J.opened_at, J.closed_at"
    . "  FROM user as U INNER JOIN job_entry as J"
    . "    ON U.id = J.user"
    . "  WHERE J.id = :entry_id"
    . ";";
$job_entry = db_ask_ro($sql1, ['entry_id' => $request_entry_id], \PDO::FETCH_ASSOC)[0];


// make actual content of bulletin
function html_text_of_bulletins_list($job_entry){
    // detect null
    if(is_null($job_entry)) {
        return "not found";
    }
            
    // content
    $tml_text = html_text_of_bulletin($job_entry);;

    return $tml_text;
}


// prepare content and render by template

$bulletins_list_tml = html_text_of_bulletins_list($job_entry);

RenderByTemplate("template.html", "Look for bulletin - Shinano -",
                 $bulletins_list_tml);

?>
