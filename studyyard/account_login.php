<?php

declare(strict_types=1);

include_once(__DIR__ . "/./ingredients/utilities.php");

// prepare template

$tpl = new \UTILS\TemplateAndConfigs();

$tpl->page_title = "Account Login - Shinano - ";

// parepare and execute DB and SQL

$wconn_ro = new \UTILS\WPDO($data_source_name, $sql_ro_user, $sql_ro_pass);
//$wconn_ro = new \UTILS\WPDO($data_source_name, $sql_ro_user, "fial_pssaowrd"); // the case of password failure

$sql1 = "SELECT name,email,passwd_hash FROM shinano_dev.user;";

$stmt = $wconn_ro->askdb($sql1);


while($row=$stmt->fetch()){
    $sql1_db_text .= "{$row["name"]} :       {$row["email"]}      : {$row["passwd_hash"]} \n";
}


// make contents

$account_put_email_or_id = "hogehoge@foobarmail.com";
$account_put_password = "password_xxxxx";

$login_form_html = <<<LOGINFORM1
<form action="" method="post">
  <dl>
    <dt> email or user_id </dt>
    <dd> <input type="text" name="email_or_id" required value="${account_put_email_or_id}"> </input> </dd>
    <dt> password </dt>
    <dd> <input type="password" name="password" required value=""> </input> </dd>
  </dl>
  <input type="submit" value="login"> </input>
</form>

LOGINFORM1;


// $debug_area=""; // if $debug_area is not needed ,uncomment



$tpl->content_actual = <<<CONTENTLOGIN

{$login_form_html}

CONTENTLOGIN;


// apply and echos template

$tpl->eval_template("template.html");

?>
