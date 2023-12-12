<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");


// deny not loggedin request
if(! $login->user()){
    please_login_page();
    exit();
}

// if logged in request, prepare contents.

// ask DB

$sql1
    = "SELECT U.name, U.email, U.public_uid, "
    . "    J.id, J.attribute, J.user, J.title, J.description, J.created_at, J.opened_at, J.closed_at"
    . "  FROM user as U INNER JOIN job_entry as J"
    . "    ON U.id = J.user"
    . "  ORDER BY J.id"
    . ";";

$job_entries = db_ask_ro($sql1, [], \PDO::FETCH_ASSOC);

// make content_actual of cooperators

function html_text_of_matches_list($job_entries){
    $key_names = array_keys($job_entries[0]);

    // content
    $tml_text = "";
    $tml_text = "<div class='cooperators'>";
    foreach($job_entries as $row){

        // set key_value
        $vals = []; // values
        foreach($key_names as $key){
            $vals[$key] = h($row[$key]);
        }

        // content of row
        $listing_or_seeking = ($vals['attribute'] =='L'  ?  'Listing' :
                               ($vals['attribute']=='S' ?  'Seeking' : 'showing'));
        $detail_url = url_of_match_detail($vals['id']);
        $description_omitted = mb_strimwidth($vals['description'], 0, 74*3, '...', 'UTF-8' );  // limit to 74*3 characters.

        global $pubroot;
        $tml_text
            .= 
            ("<div class='look_for_seek'>" .
             "  <a href='{$detail_url}'> <h3> {$vals['title']} </h3> </a>" .
             "  {$listing_or_seeking} by " .
             "  <a href='{$pubroot}cooperator.php?puid={$vals['public_uid']}'>{$vals['name']}</a> <br />" .
             "  id: <span> {$vals['id']} </span> , " .
             "  S/L: <span> {$vals['attribute']} </span> , " .
             "  created: <span> {$vals['created_at']} </span> , " .
             "  opened: <span> {$vals['opened_at']} </span> , " .
             "  closed: <span> {$vals['closed_at']} </span> ," .
             "  <p><pre style='display: inline;'> {$description_omitted}</pre>" .
             "     <a href='{$detail_url}'>(detail)</a></p>" .
             "</div>" .
             "<hr /> \n");
    }
    $tml_text .= "</div>";
    
    return $tml_text;
}

$matches_list_tml = html_text_of_matches_list($job_entries);


// prepare and render by template

RenderByTemplate("template.html", "Look for match - Shinano -",
                 $matches_list_tml);

?>
