<?php

declare(strict_types=1);

namespace FormCheck;

include_once(__DIR__ . "/./utilities.php");
include_once(__DIR__ . '/../../lib/transactions.php');


// # check for post values

$preg_str_of_marks = preg_quote(' !"#$%&\'()*+,-./:;<=>?@[\\]^_`{|}~', '/'); // Ascii code


function check_if_post_is_safe($formed_string){
    $form_check_message = "";
    if(!isset($formed_string)) {
        return [null, "please use form. \n"];
    } elseif ($formed_string === false) {
        return [null, "unuseable character is set.\n"];
    } elseif ($formed_string === "") {
        return [null, "please input into form. \n"];
    } else {
        return [true, ""];
    }
}

function check_user_id_safe($string_user_id){
    /*
    // format cheker for string typed user_id
    if(preg_match('/[^\w\ \_\-]/', $name)){
    $form_check_message 
    .= "Allowed characters for user is 0-9, A-Z, a-z, space ' ', underscore '_' and hyphen '-'.\n";
    }
    */
    return [null, "post_format_checker for strring_user_id is not implemented"];
}

function check_user_name_safe($name){
    // check value for user
    $form_check_message = "";
    
    // post check
    [$check_safe_post_p, $check_safe_post_text] = check_if_post_is_safe($name);
    if(! $check_safe_post_p){
        return [null, $check_safe_post_text];
    }
    
    // name's format check.
    global $preg_str_of_marks;
    if(preg_match("/^[${preg_str_of_marks}0-9]+$/" , $name)){ // mark and number only
        $form_check_message .= "name constructed only by marks and numbers is not allowed. \n";
    }
    if(strlen($name) < 5 || strlen($name) > 64) {
        $form_check_message .= "name need to be more than 5 chracters and less than 64 characters. \n";
    }
    if(preg_match('/^\d+$/D', $name)){ // number only
        $form_check_message .= "number only user name is not allowed.\n";
    }    

    // return
    if($form_check_message !== ""){
        return [null, $form_check_message];
    } else {
        return [$name, ""];
    }
}


function check_user_email_safe(){
    return null;
}

function check_user_email_safe_and_unique($email){
    // post check
    [$check_safe_post_p, $check_safe_post_text] = check_if_post_is_safe($email);
    if(! $check_safe_post_p){
        return [null, $check_safe_post_text];
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

function check_user_password_safe($password1, $password2){
    $form_check_message = "";
    
    // post check
    [$check_safe_post_p_1, $check_safe_post_text_1] = check_if_post_is_safe($password1);
    [$check_safe_post_p_2, $check_safe_post_text_2] = check_if_post_is_safe($password2);
    if(! $check_safe_post_p_1 || ! $check_safe_post_p_2){
        return [null, $check_safe_post_text_1 . $check_safe_post_text_2];
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


?>