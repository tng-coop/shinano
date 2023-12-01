<?php

declare(strict_types=1);

namespace NAMES;


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
+--------------+-------------+-----------------------+
*/

[$sql_ro_user, $sql_ro_pass] = ["sdev_ro", "Kis0Shinan0DevR0"];
[$sql_rw_user, $sql_rw_pass] = ["sdev_rw", "Kis0Shinan0DevRW"];

$dbname = "shinano_dev";
$dbhost = "localhost";
$sqlclient = "mysql";
$data_source_name = "{$sqlclient}:host={$dbhost};dbname={$dbname};charset=UTF8";


// # functions

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
