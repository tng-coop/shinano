<?php

declare(strict_types=1);

include_once(__DIR__ . "/./ingredients/utilities.php");

// prepare template

$tpl = new \UTILS\TemplateAndConfigs();

$tpl->page_title = "index php here";

// parepare and execute DB and SQL

$wconn_ro = new \UTILS\WPDO($data_source_name, $sql_ro_user, $sql_ro_pass);
//$wconn_ro = new \UTILS\WPDO($data_source_name, $sql_ro_user, "fial_pssaowrd"); // the case of password failure

$sql1 = "SELECT name,email,passwd_hash FROM shinano_dev.user;";

$stmt = $wconn_ro->askdb($sql1);


while($row=$stmt->fetch()){
    $sql1_db_text .= "{$row["name"]} :       {$row["email"]}      : {$row["passwd_hash"]} \n";
}


// make contents

$debug_area = <<<DEBUGAREA
<hr />
<pre>
Debug Area's text:
ro_user: {$sql_ro_user}, pass: {$sql_ro_pass}
ro_user: {$sql_rw_user}, pass: {$sql_rw_pass}
conn: {$show_conn_ro}
result: {$sql1_db_text}
</pre>
"{$sqlclient}:dbname={$dbname};host={$dbhost}", $sql_ro_user, $sql_ro_pass
<hr />
DEBUGAREA;


// $debug_area=""; // if $debug_area is not needed ,uncomment



$tpl->content_actual = <<<CONTENTINDEX

<a href=\"./list_jobs.php\"> jobs </a>
<br />
<a href=\"./list_seeker.php\"> seeks </a>
<br />

{$debug_area}

CONTENTINDEX;


// apply and echos template

$tpl->eval_template("template.html");

?>
