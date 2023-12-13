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
if($request_method == "GET") {
    if(isset($_GET['npage']) && int_string_p($_GET['npage'])){
        $request_npage = intval($_GET['npage']);
    } else {
        $request_npage = 1;
    }
}

$cooperators_per_page = 17;
$offset_from = ($request_npage - 1) * $cooperators_per_page; // npage count from 1

// Ask DB.

$sql1 
    = "SELECT U.name,U.email,U.public_uid,U.note,U.created_at"
    . "  FROM user AS U"
    . "  ORDER BY U.id"
    . "  LIMIT {$cooperators_per_page} OFFSET {$offset_from}"
    . ";";

$cooperator_array = db_ask_ro($sql1, [], \PDO::FETCH_ASSOC);


$n_cooperators = db_ask_ro("SELECT COUNT(*) FROM user;")[0][0]; // WHERE opend cooperator(user)

// make actual content of cooperators

function html_text_of_cooperators($cooper_arr){
    if(is_null($cooper_arr[0])){
        return "";
    }

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
            . "  <p style='color:blue;'> links of look for bulletins </p>"
            . "  email: <span> {$email} </span>"
            . "  , created: <span> {$created_at} </span>"
            . "</div>"
            ."<hr /> \n";
        $contents_tml .= $row_tml;
    }
    $contents_tml .= "</div>";
    
    return $contents_tml;
}

// actual contents

$html_cooperators_list = html_text_of_cooperators($cooperator_array);
$html_hrefs_npages 
    = html_text_of_npages_a_hrefs("cooperators.php",
                                  $request_npage, $n_cooperators, $cooperators_per_page);

$cooperators_tml = "<p>Thanks for {$n_cooperators} cooperators in Shinano. </p>"
                 . $html_hrefs_npages . "<hr />" 
                 . $html_cooperators_list . "<hr />"
                 . $html_hrefs_npages;

// render HTML by template

RenderByTemplate("template.html", "Cooperators - Shinano -",
                 $cooperators_tml);

?>
