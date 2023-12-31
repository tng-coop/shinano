<?php

declare(strict_types=1);

include_once(__DIR__ . "/../../lib/common.php");
include_once(__DIR__ . "/../../lib/form_check.php");
include_once(__DIR__ . '/../../lib/transactions.php');
include_once(__DIR__ . '/../../lib/mb_send_mail_compat.php');

// initialize variables
$db_message_tml = "";
$csrf_message = "";
[$post_email, $form_message_email, $email_not_registered_message] = ["", "", ""];

// fill variables by POSTed values
if($request_method == 'POST'){
    $post_email = $_POST["email"];
}

// check if email is already registered as (non pre) user

function check_if_email_is_not_registered($email){
    $user_exists = db_ask_ro("SELECT id,email FROM user WHERE email = :email",
                             [':email'=>$email], \PDO::FETCH_ASSOC);
    if(count($user_exists)!=0){
        return [null, "email's user is already registered"];
    } else {
        return [true, ""];
    }
}

function send_email_for_email_varification_of_account_create($email_to, $url){

    mb_internal_encoding("UTF-8");

    $title = "[Shinano] Account Create step, this email had sent for user varification.";
    $message
           = "<h3>This is Email varification for Account Creation of Shinano</h3>"
           . "<p>to finish Account Creation, please access below link<br />"
           . "Create Account : <a href='{$url}'>{$url}</a></p>"
           . "<hr />"
           . "<p> this e-mail is sent by shinano. this email is sending only</p>";
    $headers  = "";
    $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
    global $email_from;
    $headers .= "From: Shinano <{$email_from}>" . "\r\n" ; // config email_from
 
        // Log the arguments passed to mb_send_mail
        $logMessage = "Sending email to: $email_to\n";
        $logMessage .= "Title: $title\n";
        $logMessage .= "Message: $message\n";
        $logMessage .= "Headers: $headers\n";
        error_log($logMessage, 0);

        
    $cps = function($mail) {
        $mail->isHTML(true); // Set the email format to HTML
    }; 
    $result_send_email = mb_send_mail_compat($email_to, $title, $message, $headers, $cps);

    return $result_send_email;
    // for testing
    // print_r($message);
    // return true;
}

// Insert pre_user of DB if email-sending is no problem for system.

$state_create_account = "pre_creating";

if($request_method == "POST"){
    // check CSRF
    if(!$csrf->checkToken()){
        $csrf_message = "invalid token. use form.\n";
        
    } else {
        // check POSTed form's values
        [[$checked_email, $form_message_email],
         [$email_not_registered_check, $email_not_registered_message]]
        = [\FormCheck\check_user_email_safe($post_email),
           check_if_email_is_not_registered($post_email)];

        $safe_form_post_p = ($checked_email!=null && $email_not_registered_check==true);
        
        // reigster to user table if good POST.
        if($safe_form_post_p){
            //$checked_hashed_password = password_hash($checked_password, PASSWORD_DEFAULT);
            global $pubroot;
            $urltoken = uniqid(bin2hex(random_bytes(32)));
            $url = "{$pubroot}account/create.php?urltoken={$urltoken}";

            // register pre_user to DB.
            global $data_source_name, $sql_rw_user, $sql_rw_pass;
            $inserted_to_pre_user
                = \Tx\with_connection($data_source_name, $sql_rw_user, $sql_rw_pass)(
                function($conn_rw) use($checked_email, $urltoken) {
                    $sql = "INSERT INTO pre_user (urltoken, email, date, flag)"
                         . "  VALUES (:urltoken, :email, current_timestamp, '0');";
                    $stm = $conn_rw->prepare($sql);
                    $stm->execute([':email'=>$checked_email, ':urltoken'=>$urltoken]);
                    return true;
                });
            if(!$inserted_to_pre_user) {
                $db_message_tml = "somewhy failed to regist you.";
                exit(); // return null;
            }
            
            // send email

            $email_sent = send_email_for_email_varification_of_account_create($checked_email, $url);

            /*
            // for test (test for enviroment where email is not sendable such as localhost.)
            print_r("email: {$checked_email} <br />");
            print_r("urltoken: {$urltoken} <br />");
            print_r("url (temporary): <a href={$url}>{$url}</a>");
            */

            if(! $email_sent) {
                $db_message_tml = "somewhy email sending is failed.";
            }

            if($inserted_to_pre_user && $email_sent) {
                $state_create_account="pre_just_created";
            }
        }
    }
}

// parepare and execute DB and SQL

// Make contents
if ($state_create_account == "pre_just_created") {
    $pre_account_create_form_html = "Shinano sent you E-Mail.<br />Please check it.";
} elseif ($state_create_account == "pre_creating") {
    // CSRF inserting html
    $csrf_html = $csrf->hiddenInputHTML(); // Ensure this is a hidden input

    // Actual content
    $pre_account_create_form_html = <<<ACCOUNT_CREATE_FORM
    $db_message_tml
    To create an account, Shinano checks your e-mail first. <br />
    <pre> {$csrf_message} </pre>
    <form action="" method="post">
        $csrf_html
        <dl>
            <dt>Email</dt>
            <dd><input type="email" name="email" required value="$post_email"></dd>
            <dd><pre>{$form_message_email}{$email_not_registered_message}</pre></dd>
        </dl>
        <input type="submit" value="Check for Email">
    </form>
ACCOUNT_CREATE_FORM;
}

// Prepare template
$content_actual = <<<CONTENT_CREATE_ACCOUNT
<h3>E-Mail check for Create Account</h3>
$pre_account_create_form_html
CONTENT_CREATE_ACCOUNT;

RenderByTemplate("template.html", "Account Create - Shinano -", $content_actual);


?>
