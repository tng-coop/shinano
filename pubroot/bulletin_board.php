<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");


// deny not loggedin request
if(! $login->user()){
    please_login_page();
    exit();
}

// if logged in request, prepare contents.

// GET's npage
if($request_method == "GET") {
    if(isset($_GET['npage']) && int_string_p($_GET['npage'])){
        $request_npage = intval($_GET['npage']);
    } else {
        $request_npage = 1;
    }
}

$bulletins_per_page = 17;
$offset_from = ($request_npage - 1)  * $bulletins_per_page; // npage count from 1

// ask DB

$sql1
    = "SELECT U.name, U.email, U.public_uid, "
    . "    J.id, J.attribute, J.user, J.title, J.description, J.created_at, J.opened_at, J.closed_at"
    . "  FROM user as U INNER JOIN job_entry as J"
    . "    ON U.id = J.user"
    . "  ORDER BY J.id"
    . "  LIMIT {$bulletins_per_page} OFFSET {$offset_from}"
    . ";";

$job_entries = db_ask_ro($sql1, [], \PDO::FETCH_ASSOC);


$n_entries = db_ask_ro("SELECT COUNT(*) FROM job_entry;")[0][0]; // WHERE opend cooperator(user) and opend bulletins

// make content_actual of cooperators

function html_text_of_bulletins_list($job_entries){
    if(is_null($job_entries[0])){
        return "";
    }

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
        $detail_url = url_of_bulletin_detail($vals['id']);
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

// actual contents

$html_bulletins_list = html_text_of_bulletins_list($job_entries);
$html_hrefs_npages
    = html_text_of_npages_a_hrefs("bulletin_board.php",
                                  $request_npage, $n_entries, $bulletins_per_page);

$bulletins_list_tml = "<h3>Bulltein Board of Shinano <!-- (==BBS) --> </h3>"
                  . "<p>Thanks for {$n_entries} bulletins in Shinano. </p>"
                  . $html_hrefs_npages . "<hr />" 
                  . $html_bulletins_list . "<hr />"
                  . $html_hrefs_npages;

// render HTML by template

RenderByTemplate("template.html", "Look for Bulletin Board of - Shinano -",
                 $bulletins_list_tml);

?>
