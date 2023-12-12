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

$dbname = "shinano_dev";
$dbhost = "localhost";
$sqlclient = "mysql";
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

?>
