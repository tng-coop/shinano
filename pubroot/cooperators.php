<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");


if(! $login->user()){
    // if not logged in, Page to ask login.
    $tpl = new TemplateAndConfigs();
    $tpl->page_title = "Please login - Shinano";
    $tpl->content_actual = "to show cooperators, Please login.";
    $tpl->eval_template("template.html");

    exit();
}

// if logged in, prepare cooperators page.

// parepare and execute DB and SQL
$wconn_ro = new WPDO($data_source_name, $sql_ro_user, $sql_ro_pass);
$sql1 = "SELECT name,email,public_uid,note,created_at FROM user;";
$stmt = $wconn_ro->askdb($sql1);

// make actual content of cooperators

function html_text_of_cooperators($statement){
    $contents_tml = "";
    $contents_tml = "<div class='cooperators'>";
    while($row = $statement->fetch()){
        [$name, $note, $puid, $email, $created_at]
        = array_map(fn($key) => h($row[$key]), 
                    ['name', 'note', 'public_uid', 'email', 'created_at']);
        
        $row_tml
            = "<div class='cooperator'>"
            . "  <h3> {$name} </h3>"
            . "  <a href='./cooperator.php?puid=${puid}'> c </a>"
            . "  <pre> {$note} </pre>"
            . "  <p style='color:blue;'> links of look for match </p>"
            . "  email: <span> {$email} </span>"
            . "  , created: <span> {$created_at} </span>"
            . "</div>"
            ."<hr /> \n";
        $contents_tml .= $row_tml;
    }
    $contents_tml .= "</div>";
    
    return $contents_tml;
}


// prepare template

$tpl = new TemplateAndConfigs();
    
$cooperators_tml = html_text_of_cooperators($stmt);    

$tpl->page_title = "cooperators - Shinano";

$tpl->content_actual = "{$cooperators_tml}";

// apply and echos template
$tpl->eval_template("template.html");


?>
