<?php

declare(strict_types=1);

include_once(__DIR__ . "/./ingredients/utilities.php");



// parepare and execute DB and SQL

$wconn_ro = new \UTILS\WPDO($data_source_name, $sql_ro_user, $sql_ro_pass);

$sql1 = "SELECT attribute,user,title,description,created_at,opened_at,closed_at FROM shinano_dev.job_entry;";


$stmt = $wconn_ro->askdb($sql1);



// make content_actual of cooperators

function html_text_of_matches_list($statement){
    
    $tml_text = "";
    $tml_text = "<div class='cooperators'>";
    while($row = $statement->fetch()){
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

$matches_list_tml = html_text_of_matches_list($stmt);


// prepare template

$tpl = new \UTILS\TemplateAndConfigs();

$tpl->page_title = "look for match";


$tpl->content_actual = <<<CONTENTINDEX

{$matches_list_tml}

CONTENTINDEX;


// apply and echos template

$tpl->eval_template("template.html");

?>
