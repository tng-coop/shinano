<?php

declare(strict_types=1);

namespace AccountCreate;

include_once(__DIR__ . "/./ingredients/utilities.php");
include_once(__DIR__ . '/../lib/transactions.php');


// check request method whether POST or GET

if(\UTILS\is_GET()){
    $request_method = "GET";
}elseif(\UTILS\is_POST()){
    $request_method = "POST";
}


// fill variables by POSTed values

$form_accessors = ["name", "email", "password_first", "password_check"];
$post_data = array_map(fn($accessor) => $_POST[$accessor], $form_accessors);
[$post_name, $post_email, $post_password_first, $post_password_check] = $post_data;


// check for post value

$preg_str_of_marks = preg_quote(' !"#$%&\'()*+,-./:;<=>?@[\\]^_`{|}~', '/'); // Ascii code

function check_for_user_name($name){
    // check value for user
    $form_check_message = "";
    if(!isset($name)) {
        $form_check_message .= "please use form. \n";
    } elseif ($name === false) {
        $form_check_message .= "unuseable character is set.\n";
    } elseif ($name === "") {
        $form_check_message .= "please input name in form. \n";
    } else {
        /*
          // for string type user_id
          if(preg_match('/[^\w\ \_\-]/', $name)){
          $form_check_message 
          .= "Allowed characters for user is 0-9, A-Z, a-z, space ' ', underscore '_' and hyphen '-'.\n";
          }
        */
        global $preg_str_of_marks;
        $str1 = "/^[${preg_str_of_marks}0-9]+$/";
        if(preg_match($str1 , $name)){ // mark and number only
            $form_check_message .= "name constructed only by marks and numbers is not allowed. \n";
        }
        if(strlen($name) < 5 || strlen($name) > 64) {
            $form_check_message .= "name need to be more than 5 chracters and less than 64 characters. \n";
        }
        if(preg_match('/^\d+$/D', $name)){ // number only
            $form_check_message .= "number only user name is not allowed.\n";
        }
    }

    // return
    if($form_check_message !== ""){
        return [null, $form_check_message];
    } else {
        return [$name, ""];
    }
}

function check_for_user_email($email){
    // form check
    if(!isset($email)) {
        return [null, "please use form. \n"];
    } elseif ($email === false) {
        return [null, "unuseable character is set.\n"];
    } elseif ($email === "") {
        return [null, "please input name in form. \n"];
    }

    // email format check
    if(strlen($email) > 254) {
        return [null, "email must be less than 255 characters. \n"];
    }
    if(!filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE)){
        return [null, "it unable to recoginize as email.\n"];
    }
    list($local_part, $domain_part) = explode('@', $email, 2);
    if(!checkdnsrr($domain_part, 'MX') &&
       !checkdnsrr($domain_part, 'A') &&
       !checkdnsrr($domain_part, 'AAAA')){
        return [null, "Please use exists email.\n"];
    }

    // check database
    global $data_source_name, $sql_ro_user, $sql_ro_pass;
    $id_exists = \Tx\with_connection($data_source_name, $sql_ro_user, $sql_ro_pass)(
        function($conn_rw) use ($email){
            $emails_user_id = \TxSnn\user_id_lock_by_email($conn_rw, $email);
            return $emails_user_id;
        }
    );
    if($id_exists) {
        return [null, "email's user is already exists."];
    }
    
    // return
    if(false){
        return [null, ""];
    } elseif(true){
        return [$email, ""];
    }
}

function check_for_user_password($password1, $password2){
    $form_check_message = "";
    
    // form check
    if(!isset($password1) || !isset($password2)) {
        return [null, "please use form. \n"];
    } elseif ($password1 === false || $password2 === false) {
        return [null, "unuseable character is set.\n"];
    } elseif ($password1 === "" || $password2 === "") {
        return [null, "please input name in form. \n"];
    }

    // check if password is equal
    if($password1 !== $password2){
        $form_check_message .= "password is not match.\n";
    }

    // password format
    if(strlen($password1) < 8 || strlen($password1) > 128){
        $form_check_message .= "password needs characters of more than 8 and less than 128\n";
    }
    
    global $preg_str_of_marks;
    if(!(preg_match('/[a-zA-Z]/', $password1) &&
         preg_match('/[0-9]/', $password1) &&
         //preg_match("/[${preg_str_of_marks}]/", $password1) 
         true)){
        $form_check_message .= "password needs more than 1 each of [a-z] or [A-Z], [0-9], marks\n";
    }
    
    if(! \UTILS\text_char_all_1byte_p($password1)){
        $form_check_message .= "password need to be half-width characters only.\n";
    }
    
    // return
    if($form_check_message !== ""){
        return [null, $form_check_message];
    } else {
        return [$password1, ""];
    }

}


// Insert DataBase of new user. If POST is safe.

function check_for_user_post($name, $email, $password1, $password2){
    return  [check_for_user_name($name), 
             check_for_user_email($email),
             check_for_user_password($password1, $password2)];
}



$state_create_account = "creating";

if($request_method == "POST"){
    // check POSTed form's values
    [[$checked_name, $form_message_name],
     [$checked_email, $form_message_email],
     [$checked_password, $form_message_password]]
    = check_for_user_post($post_name, $post_email, $post_password_check, $post_password_first);

    // reigster to user table if good POST.
    if($checked_name!=null && $checked_email!=null && $checked_password!=null){        
        global $data_source_name, $sql_rw_user, $sql_rw_pass;
        $new_user_id = \Tx\with_connection($data_source_name, $sql_rw_user, $sql_rw_pass)(
            function($conn_rw) use($checked_name, $checked_email, $checked_password) {
                \TxSnn\add_user($conn_rw, $checked_name, $checked_email, $checked_password, "");
                return \TxSnn\user_id_lock_by_email($conn_rw, $checked_email);
            }
        );

        if($new_user_id){
            $state_create_account="just_created";
        }else{
            $db_message_tml = "<pre> somewhy failed to regist you.<pre> <br />\n";
        }
    }
}

/*
$debug_tml=<<<DEBUG_TML
name  : ${checked_name} , ${form_message_name} <br />
email : ${checked_email} , ${form_message_email} <br />
password: ${checked_password} , ${form_message_password} <br />
DEBUG_TML;
*/

// prepare template

$tpl = new \UTILS\TemplateAndConfigs();

$tpl->page_title = "Account Create - Shinano -";

// parepare and execute DB and SQL

// make contents

if($state_create_account=="just_created"){
    $login_form_html = "you have registered.\n";
}elseif($state_create_account=="creating"){
    $login_form_html = <<<ACCOUNT_CREATE_FORM
${db_message_tml}
To create account, name, email and password are needed. <br />
<form action="" method="post">
  <dl>
    <dt> name </dt>
    <dd> <input type="text" name="name" required value="${post_name}"> </input> </dd>
    <dd> <pre>{$form_message_name}</pre> </dd>
    <dt> email </dt>
    <dd> <input type="text" name="email" required value="${post_email}"> </input> </dd>
    <dd> <pre>{$form_message_email}</pre> </dd>
    <dt> password </dt>
    <dd> <input type="password" name="password_first" required value=""> </input> </dd>
    <dt> password for check </dt>
    <dd> <input type="password" name="password_check" required value=""> </input> </dd>
    <dd> <pre>{$form_message_password}</pre> </dd>
  </dl>
  <input type="submit" value="Check for Create Account"> </input>
</form>
ACCOUNT_CREATE_FORM;
}


$tpl->content_actual = <<<CONTENT_CREATE_ACCOUNT

${debug_tml}
<h3> Create Account </h3>
{$login_form_html}

CONTENT_CREATE_ACCOUNT;


// apply and echos template

$tpl->eval_template("template.html");

?>
