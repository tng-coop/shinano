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

// Get read-only and read-write SQL credentials
[$sql_ro_user, $sql_ro_pass] = [$config['database']['readonly_user'], $config['database']['readonly_password']];
[$sql_rw_user, $sql_rw_pass] = [$config['database']['readwrite_user'], $config['database']['readwrite_password']];

// Abstraction of the last three lines from DATA1
$dbname = $config['database']['dbname'];
$dbhost = $config['database']['dbhost'];
$sqlclient = $config['database']['sqlclient'];
$data_source_name = "{$sqlclient}:host={$dbhost};dbname={$dbname};charset=UTF8";


// ### url
$pubroot = $config['url']['url_shinano_pubroot']; # url of shinano's pubroot

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

function url_of_match_detail($job_entry_id){
    // method for detect specific job_entry is going to be changed.
    global $pubroot;
    return "{$pubroot}match.php?eid={$job_entry_id}";
}

// specific parts of html

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


function html_text_of_job_entry_table (array $job_entries_array, $edit_menu_p=false){
    // accessor for array
    $col_keys = ['eid', 'attribute', 'title', 'description', 'created_at', 'updated_at', 'opened_at', 'closed_at'];

    // table
    $tml_text  = "";
    $tml_text .= "<table>";

    // table head
    $tr_keys  = array_merge
              (['id', 'L/S', 'title', 'detail', 'created', 'updated', 'opened', 'closed'],
               ($edit_menu_p ? ['edit', 'delete'] : []));

    $tml_text .= "<tr>"
              . array_reduce($tr_keys, fn($carry, $key) => $carry . " <th>$key</th> ", "")
              . "</ tr>";

    // table rows
    foreach($job_entries_array as $row){
        // each row into html injection safe
        $row_tml_formed = [];
        foreach($col_keys as $key) {
            $row_tml_formed[$key] = h((gettype($row[$key])=='string') ?
                                      mb_strimwidth($row[$key], 0, 50, '...', 'UTF-8') :
                                      $row[$key]);
        }

        // edit menu buttons if edit_menu_p
        if($edit_menu_p){
            $tml_delete_button = "<td>".tml_entry_delete_button($row['eid'])."</td>";
            $tml_edit_button = "<td>".tml_entry_edit_button($row['eid'])."</td>";
        }

        // tml of each row
        $row_tml = "<tr>"
                 . array_reduce($col_keys,
                                fn($carry, $key) => 
                                $carry . "<td>".h($row_tml_formed[$key])."</td>",
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


?>
