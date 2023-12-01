<?php

declare(strict_types=1);

include_once(__DIR__ . "/./ingredients/utilities.php");

// prepare template

$tpl = new \NAMES\TemplateAndConfigs();

$tpl->page_title = "index php here";

// parepare and execute DB and SQL


try {
    //$dbconn_ro = new PDO("{$sqlclient}:dbname={$dbname};host={$dbhost}", $sql_ro_user, $sql_ro_pass);
    $dbconn_ro = new PDO($data_source_name, $sql_ro_user, $sql_ro_pass);
} catch (Exception $e){
    echo $e->getMessage();
}

$show_conn_ro = print_r($dbconn_ro, true);

/*
   // example sql
   $sql1 = "SELECT U.name, J.title, J.description, J.created_at, J.updated_at, J.opened_at, J.closed_at
   FROM shinano_dev.user as U INNER JOIN job_entry AS J
   ON U.id = J.user
   WHERE attribute = 'L' AND U.email = @email
   ORDER BY opened_at IS NULL ASC, created_at ASC;";
   
 */

$sql1 = "SELECT name,email FROM shinano_dev.user;";

$stmt=$dbconn_ro->prepare($sql1);
$stmt->execute();

$sql1_db_text="";
while($row=$stmt->fetch()){
    $sql1_db_text .= "{$row["name"]} :       {$row["email"]} \n";
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
