<?php

declare(strict_types=1);


namespace UTILS; // namespace was called 'NAMES' before in before versions.
use \PDO;


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
| localhost    | sdev_ro     | Kis0Shinan0DevR0      | -- Password are maybe hashed!
| localhost    | sdev_rw     | Kis0Shinan0DevRW      | -- Password are maybe hashed!
... omit ...
+--------------+-------------+-----------------------+
*/

[$sql_ro_user, $sql_ro_pass] = ["sdev_ro", "Kis0Shinan0DevR0"];
[$sql_rw_user, $sql_rw_pass] = ["sdev_rw", "Kis0Shinan0DevRW"];

$dbname = "shinano_dev";
$dbhost = "localhost";
$sqlclient = "mysql";
$data_source_name = "{$sqlclient}:host={$dbhost};dbname={$dbname};charset=UTF8";



// # Wrap SQL connections, WPDO is Wrapped PDO.

class WPDO{
    function __construct($data_source_name, $sql_user, $sql_password){
        try {
            $this->$conn = new PDO($data_source_name, $sql_user, $sql_password);
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

    
    function askdb($sql_sentence){
        try {
            $stmt = ($this->$conn)->prepare($sql_sentence);
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
        include($this->_document_root . "/./ingredients/template/{$template_file}");
    }
}

?>
