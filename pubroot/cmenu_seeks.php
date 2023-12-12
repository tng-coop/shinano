<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");


// deny not loggedin request
if(! $login->user()){
    please_login_page();
    exit();
}


// DB update of user's note if POST of user_note edit.

include_once(__DIR__ . "/../lib/form_check.php");
include_once(__DIR__ . '/../lib/transactions.php');

function update_user_note_if_note_editted_post(){
    global $request_method, $_POST;
    global $csrf;

    if($request_method != "POST" ||
       $_POST['user_note_edit_area'] != 'submitted' ||
       $_POST['user_note_edit_text'] == null
    ){
        return [null, ""];
    }

    if(!$csrf->checkToken()){
        return [null, "invalid token. please user form.\n"];
    }

    [$checked_note_editted, $message_note_editted] 
    = \FormCheck\check_text_safe($_POST['user_note_edit_text'] ,false ,(16384 - 4));

    global $login;
    $user_id = intval($login->user('id'));

    if($checked_note_editted && $user_id) {
        // register updated note to DB.
        global $data_source_name, $sql_rw_user, $sql_rw_pass;
        \Tx\with_connection($data_source_name, $sql_rw_user, $sql_rw_pass)(
            function($conn_rw) use($user_id, $checked_note_editted) {
                update_user_note($conn_rw, $user_id, $checked_note_editted);
            });
        return [$checked_note_editted, ""];
    }
    return [null, $message_note_editted];
}

function update_user_note($conn_rw, int $user_id, string $note_new){
    \Tx\block($conn_rw, "update_user_note: of ${user_id}")(
        function() use($conn_rw, $user_id, $note_new){
            $stmt = $conn_rw->prepare("UPDATE user SET note = :note WHERE id = :id;");
            $stmt->execute([':note' => $note_new, ':id' => $user_id]);
        });
}

[$note_edit_posted, $note_edit_message] = update_user_note_if_note_editted_post();


// Ask DB about user
$sql_user_info = <<<SQL
SELECT id AS uid,name,email,public_uid,note,created_at
  FROM user
  WHERE id = :id;
SQL;

$user_info_array = db_ask_ro($sql_user_info, [":id" => $login->user('id')]);

if (!isset($user_info_array[0])){
    please_login_page();
    exit();
}

$user_info = $user_info_array[0];

// Ask DB about user's job_entry

$sql_job_entries = <<<SQL
SELECT id AS eid, attribute, user, title, description, created_at, updated_at, opened_at, closed_at 
  FROM job_entry
  WHERE user = :user
SQL;

$job_entries_array = db_ask_ro($sql_job_entries, [":user" => $login->user('id')]);


// prepare contents

function cooperator_note_edit_form(string $note){
    global $csrf;
    $csrf_html = $csrf->hiddenInputHTML();

    global $note_edit_message;
    $form_tml = "edit your note . "
              . "<form action='' method='POST'>"
              . "  <input type='submit' name='note_edit_submit' value='renew note'>"
              .    $csrf_html
              . "  <input type='hidden' name='user_note_edit_area' value='submitted'>"
              . "  <textarea name='user_note_edit_text' cols='72' rows='5'>${note}</textarea>"
              . "  <pre>{$note_edit_message}</pre>"
              . "</form>";
    return  $form_tml;
}

function tml_entry_delete_button(int $job_entry_id){
    global $csrf;
    $token = $csrf->hiddenInputHTML();

    $form_tml = "<form action='cmenu_delete_job_entry.php' method='POST'>"
              . "  " . $token
              . "  <input type='hidden' name='entry_id' value='{$job_entry_id}'>"
              . "  <input type='submit' value='delete' />"
              . "</form>";
    return $form_tml;
}

function tml_entry_edit_button(int $job_entry_id){
    global $csrf;
    $token = $csrf->hiddenInputHTML();

    $form_tml = "<form action='cmenu_seek_edit.php' method='POST'>"
              . "  " . $token
              . "  <input type='hidden' name='mode' value='edit_exist_post'>"
              . "  <input type='hidden' name='entry_id' value='{$job_entry_id}'>"
              . "  <input type='submit' value='edit' />"
              . "</form>";
    return $form_tml;
}


// render HTML

$job_entry_table_html = 
    html_text_of_cooperator($user_info) .
    cooperator_note_edit_form($user_info['note']) .
    html_text_of_job_entry_table($job_entries_array, true);


$content_job_entries = <<<CONTENT_JOB_ENTRIES
{$job_entry_table_html}
CONTENT_JOB_ENTRIES;

// render HTML by template
RenderByTemplate("template.html", "job_entries - Shinano -",
                 $content_job_entries);


?>
