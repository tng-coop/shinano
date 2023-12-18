<?php

declare(strict_types=1);

// # parameters


// ### sql parameters

/*
// -- to detect user and password, call after login to sql client,
[mysql]> SELECT host,user,password FROM mysql.user;
+--------------+-------------+-----------------------+
| Host         | User        | Password              |
+--------------+-------------+-----------------------+
| localhost    | mariadb.sys |                       | -- or mysql system user maybe.
... omit ...
| localhost    | sdev_ro     | xxxxxxxxxxxxxxxs      | -- Password are maybe hashed!
| localhost    | sdev_rw     | xxxxxxxxxxxxxxxx      | -- Password are maybe hashed!
... omit ...
+--------------+-------------+-----------------------+
*/

$config = parse_ini_file(__DIR__ . "/../config.ini", true);
if ($config === false) {
    // Handle the error - the file may not exist or is not readable
    echo "Error: Unable to read the configuration file.";
    exit;
}

function get_config(string $k1, string $k2) : string {
    global $config;
    if (!key_exists($k1 , $config) || !key_exists($k2 , $config[$k1])) {
        throw new RuntimeException("Error: initialization: cannot read config param \$config['$k1']['$k2']");
    }
    return $config[$k1][$k2];
}

// Get read-only and read-write SQL credentials
[$sql_ro_user, $sql_ro_pass] = [get_config('database','readonly_user'), get_config('database','readonly_password')];
[$sql_rw_user, $sql_rw_pass] = [get_config('database','readwrite_user'), get_config('database','readwrite_password')];

// Abstraction of the last three lines from DATA1
$data_source_name = get_config('database','dsn');


// ### url
$pubroot = get_config('url','url_shinano_pubroot'); # url of shinano's pubroot

// ### request_method

if(is_GET()){
    $request_method = "GET";
}elseif(is_POST()){
    $request_method = "POST";
}

// # utility functions

function h($string){
    return htmlspecialchars(strval($string));
}

function int_string_p(string $integer_string_maybe){
    return (is_numeric($integer_string_maybe) && is_int(intval($integer_string_maybe)));
}

function exit_by_error($error_){
    echo "Server Error";
    error_log("PHP Error: " . $error_->getMessage());
    exit();
}

function get_url(){
    // check if secured
    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        $url = "https://";
    else
        $url = "http://";
    // Append the host(domain name, ip) to the URL.
    $url.= $_SERVER['HTTP_HOST'];

    // Append the requested resource location to the URL
    $url.= $_SERVER['REQUEST_URI'];

    return $url;
}


function text_char_all_1byte_p ($text){
    $len = mb_strlen($text, "UTF-8");
    $wdt = mb_strwidth($text, "UTF-8");
    return $len == $wdt;
}

// check whether request method is GET of POST

function is_GET(){
    if (key_exists('REQUEST_METHOD', $_SERVER) && $_SERVER['REQUEST_METHOD']=='GET'){
        return true;
    } else {
        return false;
    }
}

function is_POST(){
    if (key_exists('REQUEST_METHOD', $_SERVER) && $_SERVER['REQUEST_METHOD']=='POST'){
        return true;
    } else {
        return false;
    }
}

// public_uid

function to_public_uid(string $ustr) : int {
    list ($hi4, $lo4) = sscanf($ustr, "%04u-%04u");
    return $hi4 * 10000 + $lo4;
}

function from_public_uid(int $public_uid) : string {
    $hi4 = intdiv($public_uid, 10000);
    $lo4 = $public_uid % 10000;
    return sprintf("%04u-%04u", $hi4, $lo4);
}

// # wrap Template and Configs

class TemplateAndConfigs{

    // config and config values
    function __construct(){
        global $pubroot;

        $this->_document_root = realpath(__DIR__ . "/../");
        $this->_URL = get_url();
        $this->pub = $pubroot;
    }

    // template
    function eval_template($template_file){
        $v = $this;
        //include($this->_document_root . "/./template/{$template_file}");
        include(__DIR__ . "/./template/{$template_file}");
    }
}

function RenderByTemplate($template_file, $title, $contents,
                                $values = []){

    $tpl = new TemplateAndConfigs();
    $tpl->page_title = $title;
    $tpl->content_actual = $contents;


    /*
    foreach($values as $val){
        // register val to variables of class.
        $name = (varName($val));
        $this->$name = $val;

    }
    */

    // apply and echos template
    $tpl->eval_template($template_file);

}


// URL to specific pages

function url_of_bulletin_detail($job_entry_id){
    // method for detect specific job_entry is going to be changed.
    global $pubroot;
    return "{$pubroot}bulletin.php?eid={$job_entry_id}";
}

function url_of_cooperator_detail($puid){
    global $pubroot;
    return "${pubroot}cooperator.php?puid=${puid}";
}

// specific parts of html

// npages <a href> s

function html_text_of_npages_a_hrefs
    ($script_relative_url, $npage_current, $n_entries, $entries_per_page, 
     $additional_query=null){

    $n_npages = ceil($n_entries / $entries_per_page);
    global $pubroot;
    $script_link = "{$pubroot}{$script_relative_url}";

    $a_s_tml = "";
    for($iter=1; $iter<=$n_npages; $iter++){
        $additional_query_string = $additional_query ? "{$additional_query}&" : "";
        $a_s_tml .= " <a href='{$script_link}?{$additional_query_string}npage={$iter}'>{$iter}</a>";
    }
    return $a_s_tml;
}


// cooperator's html

function html_text_of_cooperator(array $user_info, $omit_p=false, $omitted_length=0){
    // $bulletin_info need to be included both of user info
    $vals = array_map('h', $user_info);

    // content
    $cooperator_url = url_of_cooperator_detail($vals['public_uid']);

    $h_n_tml
        = ((! $omit_p)
           ? "<h3>{$vals['name']}</h3>"
           : "<a href='{$cooperator_url}'><h3>{$vals['name']}</h3></a>");
    $note_omitted 
        = ((! $omit_p)
           ? $vals['note']
           : mb_strimwidth($vals['note'], 0, $omitted_length, '...', 'UTF-8'));
    $detail_a_href
        = ((! $omit_p) ? "" : "  <a href='{$cooperator_url}'>(detail)</a>");

    $tml_text
        = "<div class='cooperator'>"
        . $h_n_tml
        . "  <ul class='posten_meta'>"
        . "    <li>created: {$vals['created_at']}</li>"
        . "    <li>email: {$vals['email']}</li>"
        . "    <li>public_uid: {$vals['public_uid']}</li>"
        . "  </ul>"
        . "  <p class='posten_content'>{$note_omitted}"
        . $detail_a_href
        . "  </p>"
        . "</div>";

    return $tml_text;
}

// bulletin's html

function html_text_of_bulletin(array $bulletin_info, $omit_p=false, $omitted_length=0){
    // $bulletin_info need to be included both of user and job_entry informations
    $vals = array_map('h', $bulletin_info);

    // content
    $listing_or_seeking = ($vals['attribute'] =='L'  ?  'Listing' :
                           ($vals['attribute']=='S' ?  'Seeking' : 'showing'));
    $detail_url = url_of_bulletin_detail($vals['id']); // id of job_entry.id
    $cooperator_url = url_of_cooperator_detail($vals['public_uid']);

    $h_n_tml
        = ((! $omit_p)
           ? "<h3>{$vals['title']}</h3>"
           : "<a href='{$detail_url}'><h3>{$vals['title']}</h3></a>");
    $description_omitted 
        = ((! $omit_p)
           ? $vals['description']
           : mb_strimwidth($vals['description'], 0, $omitted_length, '...', 'UTF-8'));
    $detail_a_href
        = ((! $omit_p) ? "" : "  <a href='{$detail_url}'>(detail)</a>");

    
    $tml_text 
           = "<div class='bulletin'>"
           . $h_n_tml
           . "  {$listing_or_seeking} by "
           . "  <a href='{$cooperator_url}'>{$vals['name']}</a> <br />"
           . "  <ul class='posten_meta'>"
           . "    <li>eid: {$vals['id']}</li>"
           . "    <li>S/L: {$vals['attribute']} </li>"
           . "    <li>created: {$vals['created_at']}</li>"
           . "    <li>updated: {$vals['updated_at']}</li>"
           . "  </ul>"
           . "  <p class='posten_content'> {$description_omitted}"
           . $detail_a_href 
           . "  </p>"
           . "</div>";
    return $tml_text;
}


// bulletin table

function job_entry_opened_p($opened_at, $closed_at){
    if(is_null($opened_at))
    { return false; } 
    elseif(is_null($closed_at))
    { return true; }
    if (strtotime($opened_at) > strtotime($closed_at))
    { return true; }
    else
    { return false; }
}

function tml_bulletin_edit_button(int $job_entry_id){
    global $csrf;
    $token = $csrf->hiddenInputHTML();

    global $pubroot;
    $form_tml = "<form action='{$pubroot}cmenu/bulletin_edit.php' method='POST'>"
              . "  " . $token
              . "  <input type='hidden' name='step_demand' value='ask_db_edit_post'>"
              . "  <input type='hidden' name='job_entry_id' value='{$job_entry_id}'>"
              . "  <input type='submit' value='edit' />"
              . "</form>";
    return $form_tml;
}

function tml_bulletin_swap_open_close_button(int $job_entry_id, $opened_at, $closed_at){
    global $csrf;
    $token = $csrf->hiddenInputHTML();

    global $pubroot;
    [$demand, $demand_text] = (job_entry_opened_p($opened_at, $closed_at) 
                               ? ['let_close', 'Close it']
                               : ['let_open',  'Open it']);

    $form_tml = "<form action='{$pubroot}cmenu/bulletin_swap_open_close.php' method='POST'>"
              . "  " . $token
              . "  <input type='hidden' name='entry_id' value='{$job_entry_id}'>"
              . "  <button type='submit' name='demand' value='{$demand}'>{$demand_text}</button>"
              . "</form>";
    return $form_tml;
}

function html_text_of_bulletins_table (array $bulletin_array, $edit_menu_p=false){
    // accessor for array
    $col_keys = ['eid', 'attribute', 'title', 'description', 'created_at', 'updated_at', 'opened_at', 'closed_at'];

    // table
    $tml_text  = "";
    $tml_text .= "L/S means Listing or Seeking. O/C means Opened or Closed.";
    $tml_text .= "<table>";

    // table head
    $tr_keys  = array_merge
              (['id', 'L/S', 'title', 'A', 'detail', 'created', 'updated'],
               ($edit_menu_p ? ['O/C', 'swap O/C', 'edit'] : [])
              ); // opened_at or closed_at is equally to updated.
    $tml_text .= "<tr>"
              .  array_reduce($tr_keys, fn($carry, $key) => $carry . " <th>$key</th> ", "")
              .  "</ tr>";

    // hide not-opened entries if-not editmenu.
    if(! $edit_menu_p){
        $bulletin_array
            = array_filter($bulletin_array,
                           fn($row) => job_entry_opened_p($row['opened_at'], $row['closed_at']));
    }
    

    // table rows
    foreach($bulletin_array as $row){
        // each row into html injection safe
        $row_tml_formed = [];
        foreach($col_keys as $key) {
            $row_tml_formed[$key] = h(mb_strimwidth((string)$row[$key], 0, 50, '...', 'UTF-8'));
        }

        //
        $row_tml_formed['a_href'] = "<a href='".url_of_bulletin_detail($row['eid'])."'>A</a>";
        $row_tml_formed['open_close'] = job_entry_opened_p($row['opened_at'], $row['closed_at']) ? 'O' : 'C';

        // edit menu buttons if edit_menu_p
        if($edit_menu_p){
            $tml_swap_open_close_button
                = "<td>"
                . tml_bulletin_swap_open_close_button($row['eid'],$row['opened_at'],$row['closed_at'])
                . "</td>";
            $tml_edit_button = "<td>".tml_bulletin_edit_button($row['eid'])."</td>";
        }

        // tml of each row
        $row_tml = "<tr>"
                 // show table
                 . "<td>" . $row_tml_formed['eid'] . "</td>"
                 . "<td>" . $row_tml_formed['attribute'] . "</td>"
                 . "<td>" . $row_tml_formed['title'] . "</td>"
                 . "<td>" . $row_tml_formed['a_href'] . "</td>"
                 . "<td>" . $row_tml_formed['description'] . "</td>"
                 . "<td>" . $row_tml_formed['created_at'] . "</td>"
                 . "<td>" . $row_tml_formed['updated_at'] . "</td>"
                 // for edit menu
                 . ($edit_menu_p
                    ? (""
                       . "<td>" . $row_tml_formed['open_close'] . "</td>"
                       . $tml_swap_open_close_button
                       . $tml_edit_button)
                    : "")
                 . "</tr>";

        $tml_text .= $row_tml;
    }

    // end of table
    $tml_text .= "</table>";

    // return
    return $tml_text;
}


// ask DB about duplication


function select_duplicated_bulletins_from_db(string $email, string $title, string $eid_old='-1'){
    $sql_sel_dup = "SELECT J.id AS eid , U.email, J.title"
                 . "  FROM user as U INNER JOIN job_entry AS J"
                 . "    ON U.id = J.user"
                 . "  WHERE J.title = :title"
                 . "    AND U.email = :email"
                 . "    AND J.id != :eid_old"
                 . ";";

    $ret0 = db_ask_ro($sql_sel_dup, [":email"=>$email, ":title"=>$title, "eid_old"=>$eid_old],
                      \PDO::FETCH_ASSOC);
    return $ret0;
}

function check_title_duplicate_in_each_user(string $email, $title, $eid_old=-1){
    // returns [success_p, message, duplicated_url_p];
    if(gettype($title)!=='string' || $title==="") {
        return [null, "invalid title.", false];
    }
    $duplicated_post = select_duplicated_bulletins_from_db($email, $title, (string)$eid_old);
    if($duplicated_post) {
        $dup0 = $duplicated_post[0];
        $duplicated_url = url_of_bulletin_detail($dup0['eid']);
        return [null, $duplicated_url, true]; // duplicated
    } else {
        return ['not_duplicated', "", false]; // not duplicated
    }
}



?>
