<?php

// utilities

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
    exit(1); // Exit with error code 1
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

// ### email
$email_from = get_config('email','email_from');


// ### request_method

if(is_GET()){
    $request_method = "GET";
}elseif(is_POST()){
    $request_method = "POST";
}

// # utility functions

function h($string): string {
    return htmlspecialchars(strval($string), ENT_QUOTES, 'UTF-8', false);
}

function hd($string): string {
    return htmlspecialchars_decode(strval($string), ENT_QUOTES);
}

function int_string_p($integer_string_maybe): bool {
    if (is_null($integer_string_maybe)) {
        return false;
    } else {
        return (is_numeric($integer_string_maybe) && is_int(intval($integer_string_maybe)));
    }
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

// URL to specific pages

function url_of_bulletin_detail($public_uid, $id_on_user){
    // method for detect specific job_entry is going to be changed.
    global $pubroot;
    return "{$pubroot}bulletin.php?puid={$public_uid}&oid={$id_on_user}";
}

function url_of_cooperator_detail($puid){
    global $pubroot;
    return "{$pubroot}cooperator.php?puid={$puid}";
}


?>
