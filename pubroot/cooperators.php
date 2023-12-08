<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");


// parepare and execute DB and SQL

$wconn_ro = new WPDO($data_source_name, $sql_ro_user, $sql_ro_pass);

$sql1 = "SELECT name,email,note,created_at FROM shinano_dev.user;";

$stmt = $wconn_ro->askdb($sql1);


// make content_actual of cooperators

function html_text_of_cooperators($statement){
    
    $tml_text = "";
    $tml_text = "<div class='cooperators'>";
    while($row = $statement->fetch()){
        [$name, $note, $email, $created_at]
        = [htmlspecialchars($row['name']),
           htmlspecialchars($row['note']),
           htmlspecialchars($row["email"]),
           $row["created_at"]];    
        $tml_text .= 
            ("<div class='cooperator'>" .
             "  <h3> {$name} </h3>". 
             "  <p> <pre> {$note} </pre> </p> " .
             "  <p style='color:blue;'> links of look for match </p>" .
             "  email: <span> {$email} </span>" . 
             "  , created: <span> {$created_at} </span>".
             "</div>" .
             "<hr /> \n");
    }
    $tml_text .= "</div>";
    
    return $tml_text;
}


$cooperators_tml = html_text_of_cooperators($stmt);


// prepare template

$tpl = new TemplateAndConfigs();

$tpl->page_title = "index php here";


$tpl->content_actual = <<<CONTENTINDEX

{$cooperators_tml}

CONTENTINDEX;


// apply and echos template

$tpl->eval_template("template.html");

?>
