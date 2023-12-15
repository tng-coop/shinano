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
    ($script_relative_url, $npage_current, $n_entries, $entries_per_page){

    $n_npages = ceil($n_entries / $entries_per_page);
    global $pubroot;
    $script_link = "{$pubroot}{$script_relative_url}";

    $a_s_tml = "";
    for($iter=1; $iter<=$n_npages; $iter++){
        $a_s_tml .= " <a href='{$script_link}?npage={$iter}'>{$iter}</a>";
    }
    return $a_s_tml;
}


// cooperator's html

function html_text_of_cooperator(array $user_info){
    $user_things = array_map('h', $user_info);

    $tml_text
        = "<h3>{$user_things['name']}</h3>"
        . "<ul>"
        . "  <li>created: {$user_things['created_at']}</li>"
        . "  <li>email: {$user_things['email']}</li>"
        . "  <li>public_uid: {$user_things['public_uid']}</li>"
        . "</ul>"
        . "<p>{$user_things['note']}</p>";

    return $tml_text;
}

// bulletin table

function tml_bulletin_delete_button(int $job_entry_id){
    global $csrf;
    $token = $csrf->hiddenInputHTML();

    $form_tml = "<form action='cmenu_bulletin_close.php' method='POST'>"
              . "  " . $token
              . "  <input type='hidden' name='entry_id' value='{$job_entry_id}'>"
              . "  <input type='submit' value='delete' />"
              . "</form>";
    return $form_tml;
}

function tml_bulletin_edit_button(int $job_entry_id){
    global $csrf;
    $token = $csrf->hiddenInputHTML();

    $form_tml = "<form action='cmenu_bulletin_edit.php' method='POST'>"
              . "  " . $token
              . "  <input type='hidden' name='step_demand' value='ask_db_edit_post'>"
              . "  <input type='hidden' name='job_entry_id' value='{$job_entry_id}'>"
              . "  <input type='submit' value='edit' />"
              . "</form>";
    return $form_tml;
}

function html_text_of_bulletins_table (array $bulletin_array, $edit_menu_p=false){
    // accessor for array
    $col_keys = ['eid', 'attribute', 'title', 'a_href', 'description', 'created_at', 'updated_at', 'opened_at', 'closed_at'];

    // table
    $tml_text  = "";
    $tml_text .= "<table>";

    // table head
    $tr_keys  = array_merge
              (['id', 'L/S', 'title', 'A', 'detail', 'created', 'updated', 'opened', 'closed'],
               ($edit_menu_p ? ['edit', 'delete'] : []));

    $tml_text .= "<tr>"
              . array_reduce($tr_keys, fn($carry, $key) => $carry . " <th>$key</th> ", "")
              . "</ tr>";

    // table rows
    foreach($bulletin_array as $row){
        // each row into html injection safe
        $row_tml_formed = [];
        foreach($col_keys as $key) {
            $row_tml_formed[$key] = h((gettype($row[$key])=='string') ?
                                      mb_strimwidth($row[$key], 0, 50, '...', 'UTF-8') :
                                      $row[$key]);
        }
        $row_tml_formed['a_href'] = "<a href='".url_of_bulletin_detail($row['eid'])."'>A</a>";

        // edit menu buttons if edit_menu_p
        if($edit_menu_p){
            $tml_delete_button = "<td>".tml_bulletin_delete_button($row['eid'])."</td>";
            $tml_edit_button = "<td>".tml_bulletin_edit_button($row['eid'])."</td>";
        }

        // tml of each row
        $row_tml = "<tr>"
                 . array_reduce($col_keys,
                                fn($carry, $key) =>
                                $carry . "<td>${row_tml_formed[$key]}</td>",
                                "")
                 . (($edit_menu_p) ? $tml_edit_button : "")
                 . (($edit_menu_p) ? $tml_delete_button : "")
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
