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


// ### request_method

if(is_GET()){
    $request_method = "GET";
}elseif(is_POST()){
    $request_method = "POST";
}


// # database connection

function db_connect_ro(){
    global $data_source_name, $sql_ro_user, $sql_ro_pass;
    return new PDO ($data_source_name,
                    $sql_ro_user, $sql_ro_pass,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
}


// # Wrap SQL connections, WPDO is Wrapped PDO.

function PDO_connect($data_source_name, $sql_user, $sql_password) {
     try {
         return new PDO($data_source_name, $sql_user, $sql_password);
     } catch (\PDOException $e) {
         echo "Server Error";
         $err_s = "WPDO Error: PDOException was caused when tried to connect DB Server via PDO.";
         error_log($err_s);
         error_log("WPDO Error: " . $e->getMessage());
         exit();
     } catch (\Error $e) {
         exit_by_error($e);
     }
}

class WPDO{
    public $conn;

    function __construct($data_source_name, $sql_user, $sql_password){
        $this->conn = PDO_connect($data_source_name, $sql_user, $sql_password);
    }

    function askdb($sql_sentence){
        try {
            $stmt = ($this->conn)->prepare($sql_sentence);
            $execute = $stmt->execute();
            if(!$execute){
                throw new Exception("blah");
            }
            return $stmt;
        } catch (\Exception $e) {
            exit_by_error($e);
        }
    }
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
    if ($_SERVER['REQUEST_METHOD']=='GET'){
        return true;
    } else {
        return false;
    }
}

function is_POST(){
    if ($_SERVER['REQUEST_METHOD']=='POST'){
        return true;
    } else {
        return false;
    }
}


// # wrap Template and Configs

class TemplateAndConfigs{

    // config and config values
    function __construct(){
        $this->_document_root = realpath(__DIR__ . "/../");
        $this->_URL = get_url();
    }

    // template
    function eval_template($template_file){
        $v = $this;
        //include($this->_document_root . "/./template/{$template_file}");
        include(__DIR__ . "/./template/{$template_file}");
    }
}

?>
