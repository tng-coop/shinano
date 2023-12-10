<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");


// deny not loggedin request
if(! $login->user()){
    please_login_page();
    exit();
}

// if logged in request, prepare contents.

// parepare and execute DB and SQL
$job_entries
    = db_ask_ro("SELECT attribute,user,title,description,created_at,opened_at,closed_at" .
                "  FROM job_entry;",
                [], \PDO::FETCH_DEFAULT);

// make content_actual of cooperators

function html_text_of_matches_list($job_entries){
    $tml_text = "";
    $tml_text = "<div class='cooperators'>";
    foreach($job_entries as $row){
        [$t_title, $t_attribute, $t_created_at, $t_opened_at, $t_closed_at, $t_description]
        = [htmlspecialchars($row['title']),
           $row['attribute'],
           $row['created_at'],
           $row['opened_at'],
           $row['closed_at'],
           htmlspecialchars($row['description'])];
        $tml_text
            .= 
            ("<div class='cooperator'>" .
             "  <h3> {$t_title} </h3>" .
             "  S/L: <span> {$t_attribute} </span> , " .
             "  created: <span> {$t_created_at} </span> , " .
             "  opened: <span> {$t_opened_at} </span> , " .
             "  closed: <span> {$t_closed_at} </span> ," .
             "  <p> {$t_description} </p>" .
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
