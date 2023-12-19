<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");


// deny not loggedin request
if(! $login->user()){
    please_login_page();
    exit();
}

// if logged in, prepare cooperators page.

// GET's npage
$search_text
    = (isset($_GET['search_text'])) ? $_GET['search_text'] : "";

$request_npage
    = (isset($_GET['npage']) && int_string_p($_GET['npage'])) ? intval($_GET['npage']) : 1;

// set limitter of num cooperators per page
$cooperators_per_page = 17;
$offset_from = ($request_npage - 1) * $cooperators_per_page; // npage count from 1

// Ask DB.

[$cooperators, $n_cooperators] = search_cooperators($search_text, $offset_from, $cooperators_per_page);


// make actual content of cooperators
function html_text_of_cooperator_list($cooper_arr){
    // detect null
    if(is_null($cooper_arr[0])){
        return "";
    }

    // content
    $contents_tml = "";
    $contents_tml = "<div class='cooperators'>";
    foreach($cooper_arr as $row){
        $contents_tml .= html_text_of_cooperator($row, true, 74*3) // limit to 74*3 characters.
                      .  "<hr />\n";
    }
    $contents_tml .= "</div>";
    
    return $contents_tml;
}

// actual contents

$html_cooperators_list = html_text_of_cooperator_list($cooperators);

$search_text_url_query 
    = ($search_text=="") ? "" : http_build_query(['search_text' => $search_text]);
$html_hrefs_npages 
    = html_text_of_npages_a_hrefs("cooperators.php",
                                  $request_npage, $n_cooperators, $cooperators_per_page,
                                  $search_text_url_query);

$n_entries_tml = ($search_text=="")
               ? "<p>Thanks for {$n_cooperators} cooperators in Shinano. </p>"
               : "<p>In easy search, there is {$n_cooperators} matches found. </p>";

$cooperators_tml
    = "<h3>Cooperators of Shinano </h3>"
    . $n_entries_tml
    . $html_hrefs_npages . "<hr />" 
    . $html_cooperators_list . "<hr />"
    . $html_hrefs_npages;

// render HTML by template

RenderByTemplate("template.html", "Cooperators of - Shinano -",
                 $cooperators_tml);


?>
