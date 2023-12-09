<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");


if(! $login->user()){
    // if not logged in, Page to ask login.
    RenderByTemplate("template.html", "Please Login - Shinano -",
                     "to show cooperators, Please login.");
    exit();
}

// if logged in, prepare cooperators page.

// parepare and execute DB and SQL
$wconn_ro = new PDO($data_source_name, $sql_ro_user, $sql_ro_pass);
$sql1 = "SELECT name,email,public_uid,note,created_at FROM user;";

$stmt = $wconn_ro->prepare($sql1);
$stmt->execute();

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


// render HTML by template.

$cooperators_tml = html_text_of_cooperators($stmt);

RenderByTemplate("template.html", "Cooperators - Shinano -",
                 $cooperators_tml);

?>
