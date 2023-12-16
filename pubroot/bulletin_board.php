<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");


// deny not loggedin request
if(! $login->user()){
    please_login_page();
    exit();
}

// if logged in request, prepare bulletins.

// GET's search_text and npage
$search_text
    = (! is_null($_GET['search_text'])) ? $_GET['search_text'] : "";

$request_npage
    = (isset($_GET['npage']) && int_string_p($_GET['npage'])) ? intval($_GET['npage']) : 1;

// set limitter of num entries per page
$bulletins_per_page = 17;
$offset_from = ($request_npage - 1) * $bulletins_per_page; // npage count from 1

// ask DB
[$job_entries, $n_entries] = search_job_entries($search_text, $offset_from, $bulletins_per_page);


// make content_actual of cooperators
function html_text_of_bulletins_list($job_entries){
    // detect null
    if(is_null($job_entries[0])){
        return "";
    }

    // content
    $content_tml = "";
    $content_tml = "<div class='builtin_board'>";
    foreach($job_entries as $row){
        $content_tml .= html_text_of_bulletin($row, true, 74*3) // limit to 74*3 characters.
                     .  "<hr />\n";
    }
    $content_tml .= "</div>";
    
    return $content_tml;
}

// actual contents

$html_bulletins_list = html_text_of_bulletins_list($job_entries);

$search_text_url_query 
    = ($search_text=="") ? "" : http_build_query(['search_text' => $search_text]);
$html_hrefs_npages
    = html_text_of_npages_a_hrefs("bulletin_board.php",
                                  $request_npage, $n_entries, $bulletins_per_page,
                                  $search_text_url_query);

$n_entries_tml = ($search_text=="")
               ? "<p>Thanks for opened {$n_entries} bulletins in Shinano. </p>"
               : "<p>In easy search, there is {$n_entries} matchis found. </p>";

$bulletins_list_tml
    = "<h3>Bulltein Board of Shinano <!-- (==BBS) --> </h3>"
    . $n_entries_tml
    . $html_hrefs_npages . "<hr />" 
    . $html_bulletins_list . "<hr />"
    . $html_hrefs_npages;

// render HTML by template

RenderByTemplate("template.html", "Look for Bulletin Board of - Shinano -",
                 $bulletins_list_tml);


?>
