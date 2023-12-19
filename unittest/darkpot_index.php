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


// Ask DB about SQL.
$user_array = db_ask_ro("SELECT name,email,id,public_uid,passwd_hash FROM user;");


// contents
$users_table_tml = "<table>";
$users_table_tml .= "<tr> <th>id</th> <th>name</th> <th>email</th> <th>public_uid</th> <th>passwd_hash</th> </tr>";

$user_trs = array_map(fn($user) =>
    "<tr> <td>{$user['id']}</td> <td>{$user['name']}</td> <td>{$user['email']}</td> <td>{$user['public_uid']}</td> <td>{$user['passwd_hash']}</td> </tr>" ,
                      $user_array);
$users_table_tml .= array_reduce($user_trs, (fn($carry, $tr) => $carry . $tr), "");

$users_table_tml .= "</table>";


// make contents


$debug_area = <<<DEBUGAREA
<hr />
<pre>
Debug Area's text:
ro_user: {$sql_ro_user}, pass: {$sql_ro_pass}
ro_user: {$sql_rw_user}, pass: {$sql_rw_pass}
{$sqlclient}:dbname={$dbname};host={$dbhost}", $sql_ro_user, $sql_ro_pass
</pre>

{$users_table_tml}
<hr />
DEBUGAREA;

// $debug_area=""; // if $debug_area is not needed ,uncomment


// prepare content

$content_index = <<<CONTENT_INDEX

<a href=\"./list_jobs.php\"> jobs </a> , 
   <a href=\"./list_seeker.php\"> seeks </a>
   <br />

   {$debug_area}

CONTENT_INDEX;


// render HTML by template
RenderByTemplate("template.html", "index - Shinano -",
                 $content_index);


?>
