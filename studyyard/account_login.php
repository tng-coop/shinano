<?php

declare(strict_types=1);

include_once(__DIR__ . "/./ingredients/utilities.php");
include_once(__DIR__ . "/./ingredients/form_check.php");
include_once(__DIR__ . '/../lib/transactions.php');


// fill variables by POSTed values

$form_accessors = ["email", "password"];
$post_data = array_map(fn($accessor) => $_POST[$accessor], $form_accessors);
[$post_email, $post_password] = $post_data;


// login if the safe pair of email (as id) and password are match.

function check_for_user_post($email, $password){
    [$bool__passwd, $str__passwd] = \FormCheck\check_if_post_is_safe($password);
    $passwd = ($bool__passwd==true) ? $password : null;
    return [\FormCheck\check_user_email_safe($email),
            [$passwd, $str__passwd]];
}


$doing_login_user = null;

if($request_method == "POST"){
    [[$checked_email, $form_message_email],
     [$checked_password, $form_message_password]]
    = check_for_user_post($post_email, $post_password);

    // select database and check password.
    if($checked_email!=null && $checked_password!=null){

        // ask database
        global $data_source_name, $sql_rw_user, $sql_rw_pass;
        $conn = \UTILS\PDO_connect($data_source_name, $sql_ro_user, $sql_ro_pass);
        $stmt = $conn->prepare("SELECT id,name,email,passwd_hash FROM user" .
                               "  WHERE lower(email)=lower(:email);");
        $stmt->execute(['email' => $checked_email]);
        
        // check user and password
        $users = $stmt->fetchAll();
        if(count($users) == 1 && 
           $users[0] &&
           password_verify($checked_password, $users[0]["passwd_hash"])){
            $doing_login_user = $users[0];
        } else {
            $doing_login_user = null;
        }

        // html by login state
        if($doing_login_user) {
            $db_message_tml = "";
        }else{
            $db_message_tml = "<pre> failed to login. </pre> <br />";
        }   
    }
}




// prepare template

$tpl = new \UTILS\TemplateAndConfigs();

$tpl->page_title = "Account Login - Shinano - ";


// make contents

if($doing_login_user){
    //$doing_login_user_text = $doing_login_user;
    $login_form_html =<<<LOGED_IN_MESSAGE
<h3>Hello, "${doing_login_user['name']}" !!!</h3>
<pre>
name: ${doing_login_user['name']}
id: ${doing_login_user['id']}
email: ${doing_login_user['email']}
passwd_hash: ${doing_login_user['passwd_hash']}
</pre>
LOGED_IN_MESSAGE;

}elseif(! $doing_login_user){
    $login_form_html = <<<LOGIN_FORM
<h3> Login Account </h3>
${db_message_tml}
<form action="" method="post">
  <dl>
    <dt> email or user_id </dt>
    <dd> <input type="text" name="email" required value="${post_email}"> </input> </dd>
    <dd> <pre>{$form_message_email}</pre> </dd>
    <dt> password </dt>
    <dd> <input type="password" name="password" required value=""> </input> </dd>
    <dd> <pre>{$form_message_password}</pre> </dd>
  </dl>
  <input type="submit" value="login"> </input>
</form>
LOGIN_FORM;
}


// apply and echos template

$tpl->content_actual = <<<CONTENTLOGIN
{$login_form_html}
CONTENTLOGIN;

$tpl->eval_template("template.html");

?>
