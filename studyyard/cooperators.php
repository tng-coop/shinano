<?php

declare(strict_types=1);

include_once(__DIR__ . "/./ingredients/utilities.php");



// parepare and execute DB and SQL

$wconn_ro = new \UTILS\WPDO($data_source_name, $sql_ro_user, $sql_ro_pass);

$sql1 = "SELECT name,email,note,created_at FROM shinano_dev.user;";

$stmt = $wconn_ro->askdb($sql1);


// make content_actual of cooperators

function html_text_of_cooperators($statement){
    $tml_text = "";

    $tml_text = "<div class='cooperators'>";
    while($row = $statement->fetch()){
        $tml_text .= 
            ("<div class='cooperator'>" .
             "  <h3> {$row['name']} </h3>". 
             "  <p> <pre> {$row['note']} </pre> </p> " .
             "  email: <span> {$row["email"]} </span>" . 
             "  , created: <span> {$row["created_at"]} </span>".
             "</div>" .
             "<hr /> \n");
    }

    $tml_text .= "</div>";
    return $tml_text;
}


$cooperators_tml = html_text_of_cooperators($stmt);


// prepare template

$tpl = new \UTILS\TemplateAndConfigs();

$tpl->page_title = "index php here";


$tpl->content_actual = <<<CONTENTINDEX

{$cooperators_tml}

CONTENTINDEX;


// apply and echos template

$tpl->eval_template("template.html");

?>
