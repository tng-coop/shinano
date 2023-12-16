<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");


// deny not loggedin request
if(! $login->user()){
    please_login_page();
    exit();
}

// if logged in, prepare cooperators page.

// GET's search_text
$search_text = (! is_null($_GET['search_text'])) ? $_GET['search_text'] : "";


// GET's npage
if($request_method == "GET") {
    if(isset($_GET['npage']) && int_string_p($_GET['npage'])){
        $request_npage = intval($_GET['npage']);
    } else {
        $request_npage = 1;
    }
}

$bulletins_per_page = 17;
$offset_from = ($request_npage - 1) * $bulletins_per_page; // npage count from 1


// Ask DB.

function search_job_entries($search_pattern_text, $offset_from, $bulletin_per_page){
    global $data_source_name, $sql_ro_user, $sql_ro_pass;
    
    [$job_entries, $n_entries] 
    = \Tx\with_connection($data_source_name, $sql_ro_user, $sql_ro_pass)(
        function($conn_ro) use ($search_pattern_text, $bulletin_per_page, $offset_from) {
            $sql_common 
                = "  WHERE (   (J.closed_at IS NULL AND J.opened_at IS NOT NULL)" // opened entries
                . "         OR  J.opened_at > J.closed_at)"                       // opened entries
                . "    AND (    J.title       LIKE CONCAT('%', :pattern, '%')"
                . "         OR  J.description LIKE CONCAT('%', :pattern, '%'))"
                ;

            $sql_n_entries // counter of result searched
                = "SELECT COUNT(*) as count"
                . "  FROM job_entry AS J"
                . "  {$sql_common}"
                . ";";
            $stmt = $conn_ro->prepare($sql_n_entries);
            $stmt->execute([':pattern'=>$search_pattern_text]);
            $n_entries = $stmt->fetchAll(\PDO::FETCH_ASSOC)[0]['count'];

            $sql_search 
                = "SELECT U.public_uid, U.name, J.id, J.attribute, J.title, J.description, "
                . "    J.created_at, J.updated_at, J.opened_at, J.closed_at"
                . "  FROM user AS U INNER JOIN job_entry AS J"
                . "    ON U.id = J.user"
                . "  {$sql_common}"
                . "  ORDER BY J.opened_at DESC"
                . "  LIMIT {$bulletin_per_page} OFFSET {$offset_from}"
                . ";";
            $stmt = $conn_ro->prepare($sql_search);
            $stmt->execute([':pattern'=>$search_pattern_text]);
            $job_entries = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
            return [$job_entries, $n_entries];
        });
    return [$job_entries, $n_entries];
}

[$job_entries, $n_entries] = search_job_entries($search_text, $offset_from, $bulletins_per_page);

// make content_actual of cooperators

function html_text_of_bulletins_list($job_entries){
    if(is_null($job_entries[0])){
        return "";
    }

    $key_names = array_keys($job_entries[0]);

    // content
    $tml_text = "";
    $tml_text = "<div class='cooperators'>";
    foreach($job_entries as $row){

        // set key_value
        $vals = []; // values
        foreach($key_names as $key){
            $vals[$key] = h($row[$key]);
        }

        // content of row
        $listing_or_seeking = ($vals['attribute'] =='L'  ?  'Listing' :
                               ($vals['attribute']=='S' ?  'Seeking' : 'showing'));
        $detail_url = url_of_bulletin_detail($vals['id']);
        $description_omitted = mb_strimwidth($vals['description'], 0, 74*3, '...', 'UTF-8' );  // limit to 74*3 characters.

        global $pubroot;
        $tml_text
            .= 
            ("<div class='look_for_seek'>" .
             "  <a href='{$detail_url}'> <h3> {$vals['title']} </h3> </a>" .
             "  {$listing_or_seeking} by " .
             "  <a href='{$pubroot}cooperator.php?puid={$vals['public_uid']}'>{$vals['name']}</a> <br />" .
             "  id: <span> {$vals['id']} </span> , " .
             "  S/L: <span> {$vals['attribute']} </span> , " .
             "  created: <span> {$vals['created_at']} </span> , " .
             "  opened: <span> {$vals['opened_at']} </span> , " .
             "  closed: <span> {$vals['closed_at']} </span> ," .
             "  <p><pre style='display: inline;'> {$description_omitted}</pre>" .
             "     <a href='{$detail_url}'>(detail)</a></p>" .
             "</div>" .
             "<hr /> \n");
    }
    $tml_text .= "</div>";
    
    return $tml_text;
}

// actual contents

$html_bulletins_list = html_text_of_bulletins_list($job_entries);


$html_hrefs_npages
    = html_text_of_npages_a_hrefs("search.php", $request_npage, $n_entries, $bulletins_per_page,
                                  http_build_query(['search_text' => $_GET['search_text']]));

$bulletins_list_tml = "<h3>Bulltein Board of Shinano <!-- (==BBS) --> </h3>"
                  . "<p>Thanks for {$n_entries} bulletins in Shinano. </p>"
                  . $html_hrefs_npages . "<hr />" 
                  . $html_bulletins_list . "<hr />"
                  . $html_hrefs_npages;

// render HTML by template

RenderByTemplate("template.html", "Look for Bulletin Board of - Shinano -",
                 $bulletins_list_tml);


?>
