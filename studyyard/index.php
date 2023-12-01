<?php

namespace NAMES;
use PDO;
include_once(__DIR__ . "/./ingredients/utilities.php");


$tpl = new TemplateAndConfigs();

$tpl->page_title = "index php here";


$dbname = "shinano_dev";
$dbhost = "localhost";
$sqlclient = "mysql";

$data_source_name = "{$sqlclient}:host={$dbhost};dbname={$dbname};charset=UTF8";


//$dbconn_ro = new PDO("{$sqlclient}:dbname={$dbname};host={$dbhost}", $sql_ro_user, $sql_ro_pass);


try {
    $dbconn_ro = new PDO($data_source_name, $sql_ro_user, $sql_ro_pass);
    //echo "aaa";

} catch (Exception $e){
    echo $e->getMessage();
}


$show_conn_ro = print_r($dbconn_ro, true);

$debug_area = <<<DEBUGAREA
<hr />
<pre>
Debug Area's text:
ro_user: {$sql_ro_user}, pass: {$sql_ro_pass}
ro_user: {$sql_rw_user}, pass: {$sql_rw_pass}
conn: {$show_conn_ro}
</pre>
"{$sqlclient}:dbname={$dbname};host={$dbhost}", $sql_ro_user, $sql_ro_pass
<hr />
DEBUGAREA;


// $debug_area=""; //

// Todo: SQL contents selecting and show them
$tpl->content_actual = <<<CONTENTINDEX

<a href=\"./list_jobs.php\"> jobs </a>
<br />
<a href=\"./list_seeker.php\"> seeks </a>
<br />

{$debug_area}

CONTENTINDEX;




// hoge

$tpl->eval_template("template.html");


?>
