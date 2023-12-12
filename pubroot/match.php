<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");


// deny not loggedin request
if(! $login->user()){
    please_login_page();
    exit();
}

// if logged in request, prepare contents.


// utils. there is same pattern at the cooperator.php .
function int_string_p(string $integer_string_maybe){
    return (is_numeric($integer_string_maybe) && is_int(intval($integer_string_maybe)));
}

// Note:
// addressing method for specific job_entry 
// is going to be changed from jobentry.id into user each own jobentry's id.

// parse GET request

if($request_method == "GET") {
    if(isset($_GET['eid']) && int_string_p($_GET['eid'])){
        $request_entry_id = intval($_GET['eid']);
    } else {
        $request_entry_id = false;
    }
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

// make content_actual of cooperators

function html_text_of_matches_list($job_entry){
    $key_names = array_keys($job_entry);

    // content
    if(!(($job_entry)===[])){
        $tml_text = "";
        $tml_text = "<div class='job_entry'>";

        // set key_value
        $vals = []; // values
        foreach($key_names as $key){
            $vals[$key] = h($job_entry[$key]);
        }

        // content of row
        $listing_or_seeking = ($vals['attribute'] =='L'  ?  'Listing' :
                               ($vals['attribute']=='S' ?  'Seeking' : 'showing'));

        global $pubroot;
        $tml_text
            .= 
            ("<div class='look_for_seek'>" .
             "  <a href=''> <h3> {$vals['title']} </h3> </a>" .
             "  {$listing_or_seeking} by " .
             "  <a href='{$pubroot}cooperator.php?puid={$vals['public_uid']}'>{$vals['name']}</a> <br />" .
             "  id: <span> {$vals['id']} </span> , " .
             "  S/L: <span> {$vals['attribute']} </span> , " .
             "  created: <span> {$vals['created_at']} </span> , " .
             "  opened: <span> {$vals['opened_at']} </span> , " .
             "  closed: <span> {$vals['closed_at']} </span> ," .
             "  <p><pre style='display: inline;'> {$vals['description']}</pre>" .
             "</div>" .
             "<hr /> \n");
        $tml_text .= "</div>";
    }

    return $tml_text;
}

$matches_list_tml = html_text_of_matches_list($job_entry);


// prepare and render by template

RenderByTemplate("template.html", "Look for match - Shinano -",
                 $matches_list_tml);

?>
