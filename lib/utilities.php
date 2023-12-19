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
