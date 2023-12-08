<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");


// parepare and execute DB and SQL

$wconn_ro = new WPDO($data_source_name, $sql_ro_user, $sql_ro_pass);
$sql1 = "SELECT name,email,public_uid,note,created_at FROM user;";
$stmt = $wconn_ro->askdb($sql1);


// make actual content of cooperators

function html_text_of_cooperators($statement){
    
    $tml_text = "";
    $tml_text = "<div class='cooperators'>";
    while($row = $statement->fetch()){
        [$name, $note, $puid, $email, $created_at]
        = [h($row['name']),
           h($row['note']),
           h($row['public_uid']),
           h($row["email"]),
           h($row["created_at"])];
        $tml_text .= 
                  ("<div class='cooperator'>" .
                   "  <h3> {$name} </h3>". 
                   "  <a href='./cooperator.php?puid=${puid}'> c </a>" .
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



// prepare template

$tpl = new TemplateAndConfigs();


if ($login->user()) {
    // if logged in, show cooperators.
    
    $cooperators_tml = html_text_of_cooperators($stmt);    
    
    $tpl->page_title = "cooperators - Shinano";

    $tpl->content_actual = "{$cooperators_tml}";

    // apply and echos template
    $tpl->eval_template("template.html");

}else {
    // if not logged in, ask login.

    $tpl->page_title = "Please login - Shinano";

    $tpl->content_actual = "Please login";

    $tpl->eval_template("template.html");

}

?>
