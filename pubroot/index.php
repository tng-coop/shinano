<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");

// parese config .ini file

$config = parse_ini_file(__DIR__ . "/../config.ini", true);
if ($config === false) {
    // Handle the error - the file may not exist or is not readable
    echo "Error: Unable to read the configuration file.";
    exit;
}

// Accessing values from the 'database' section
echo "Read-Only User: " . $config['database']['readonly_user'] . "<br>";
echo "Read-Only Password: " . $config['database']['readonly_password'] . "<br>";
echo "Read-Write User: " . $config['database']['readwrite_user'] . "<br>";
echo "Read-Write Password: " . $config['database']['readwrite_password'] . "<br>";


// prepare template

$tpl = new TemplateAndConfigs();

$tpl->page_title = "index php here";


// parepare and execute DB and SQL

$wconn_ro = new WPDO($data_source_name, $sql_ro_user, $sql_ro_pass);
//$wconn_ro = new WPDO($data_source_name, $sql_ro_user, "fial_pssaowrd"); // the case of password failure

$sql1 = "SELECT name,email,id,public_uid,passwd_hash FROM user;";

$stmt = $wconn_ro->askdb($sql1);


// contents

$user_array = $stmt->fetchAll();

$users_table_tml = "<table>";
$users_table_tml .= "<tr> <th>id</th> <th>name</th> <th>email</th> <th>public_uid</th> <th>passwd_hash</th> </tr>";

$user_trs = array_map(fn($user) =>
    "<tr> <td>{$user['id']}</td> <td>{$user['name']}</td> <td>{$user['email']}</td> <td>{$user['public_uid']}</td> <td>{$user['passwd_hash']}</td> </tr>" ,
                      $user_array);
$users_table_tml .= array_reduce($user_trs, (fn($carry, $tr) => $carry . $tr), "");

$users_table_tml .= "</table>";


// make contents
$show_conn_ro = get_class($wconn_ro);

$debug_area = <<<DEBUGAREA
<hr />
<pre>
Debug Area's text:
ro_user: {$sql_ro_user}, pass: {$sql_ro_pass}
ro_user: {$sql_rw_user}, pass: {$sql_rw_pass}
conn: {$show_conn_ro}
{$sqlclient}:dbname={$dbname};host={$dbhost}", $sql_ro_user, $sql_ro_pass
</pre>

{$users_table_tml}
<hr />
DEBUGAREA;

// $debug_area=""; // if $debug_area is not needed ,uncomment


$tpl->content_actual = <<<CONTENTINDEX

<a href=\"./list_jobs.php\"> jobs </a> , 
<a href=\"./list_seeker.php\"> seeks </a>
<br />

{$debug_area}


CONTENTINDEX;


// apply and echos template

$tpl->eval_template("template.html");

?>
