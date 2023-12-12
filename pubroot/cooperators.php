<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");


// deny not loggedin request
if(! $login->user()){
    please_login_page();
    exit();
}

// if logged in, prepare cooperators page.

// Ask DB about Query.

$cooperator_array = db_ask_ro("SELECT name,email,public_uid,note,created_at FROM user;",
                  [], \PDO::FETCH_DEFAULT);


// make actual content of cooperators

function html_text_of_cooperators($cooper_arr){
    $contents_tml = "";
    $contents_tml = "<div class='cooperators'>";
    foreach($cooper_arr as $row){
        [$name, $note, $puid, $email, $created_at]
        = array_map(fn($key) => h($row[$key]), 
                    ['name', 'note', 'public_uid', 'email', 'created_at']);

        $href_of_cooperator = url_of_cooperator_detail($puid);
        
        $row_tml
            = "<div class='cooperator'>"
            . "  <h3> {$name} </h3>"
            . "  <a href='{$href_of_cooperator}'> detail </a>"
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




// render HTML by template

$cooperators_tml = html_text_of_cooperators($cooperator_array);

RenderByTemplate("template.html", "Cooperators - Shinano -",
                 $cooperators_tml);

?>
