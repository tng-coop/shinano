<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");
include_once(__DIR__ . "/../lib/form_check.php");
include_once(__DIR__ . '/../lib/transactions.php');

// deny not loggedin request
if(! $login->user()){
    please_login_page();
    exit();
}


// fill variables by POSTed values

$form_accessors = ["title", "description", "attribute", "open_close", "step_current"];
$post_data = array_map(fn($accessor) => h($_POST[$accessor]), $form_accessors);
$pvs = array(); // posted values
[$pvs['title'], $pvs['description'], $pvs['attribute'], $pvs['open_close'],
 $pvs['step_previous']]
= $post_data;

// according to POSTing, radio button into checked.
[$pvs['checks_L'], $pvs['checks_S']] = [($pvs['attribute']==='L' ? 'checked' : ''),
                                        ($pvs['attribute']==='S' ? 'checked' : '')];

[$pvs['checks_open'] ,$pvs['checks_close']] = [($pvs['open_close']==='open'  ? 'checked' : ''),
                                               ($pvs['open_close']==='close' ? 'checked' : '')];



// page and process by the case of GEt or POST.

$messages = array(); // messages by check of Form or DB ...

if($request_method == "GET") {
    [$title_part, $content_html, $messages] = content_and_process_by_GET($pvs, $messages);
} elseif($request_method == "POST") {
    [$title_part, $content_html, $messages] = content_and_process_by_POST($pvs, $messages);
}

// render HTML
$debug_tml = "";

$content_actual = "${debug_tml}\n{$content_html}";

RenderByTemplate("template.html", "{$title_part} - Shinano -",
                 $content_actual);


// process and content preparing.

function content_and_process_by_GET($pvs, $messages){
    // 1. edit page
    $title_part = "Edit Seek";
    $content_html = content_of_edit_seek($pvs, $messages);

    // return
    return [$title_part, $content_html, $messages];
}

function content_and_process_by_POST($pvs, $messages){
    global $csrf;
    // check CSRF
    if(!$csrf->checkToken()){
        $messages['csrf'] = "invalid token. use form.\n";
        return null;
    }

    // form check
    $post_checks = array();

    [[$post_checks['title'], $messages['title']],
     [$post_checks['description'], $messages['description']],
     [$post_checks['attribute'], $messages['attribute']],
     [$post_checks['open_close'], $messages['open_close']]]
    = [check_text_safe($pvs['title'], false, (256 - 4)),
       check_text_safe($pvs['description'], false, (16384 - 4)),
       check_radio_value_safe($pvs['attribute'], ['L', 'S']),
       check_radio_value_safe($pvs['open_close'], ['open', 'close'])];

    $safe_form_post_p
        = array_reduce($post_checks,
                       fn($carry, $item) => $carry && ($item || $item===""),
                       true);

    // prepare Content

    // 0. eheck email
    global $login;
    $loggedin_email = $login->user('email');
    if (! $loggedin_email) {
        $title_part = "Please Login";
        $content_html = "Please Login";
    }
    // 3. update database page
    elseif ($safe_form_post_p && $pvs['step_previous'] === 'confirm') {
        global $data_source_name, $sql_rw_user, $sql_rw_pass;
        // check Duplicated POST
        $duplicated_post = select_duplicated_seeks_from_db($loggedin_email, $post_checks['title']);

        if($duplicated_post){
            // when duplicated
            
        }elseif(!$duplicated_post){
            // register user to DB.
            \Tx\with_connection($data_source_name, $sql_rw_user, $sql_rw_pass)(
                function($conn_rw) use($loggedin_email, $post_checks) {
                    \TxSnn\add_job_things($post_checks['attribute'])
                        ($conn_rw, $loggedin_email,
                         $post_checks['title'], $post_checks['description']);});

            $title_part = "Uploaded";
            $content_html = "your Seek is Uploaded";
        }

    }
    // 2. confirm page
    elseif ($safe_form_post_p && $pvs['step_previous'] === 'edit') {
        $title_part = "Please Confirm";
        $content_html = content_of_confirm_seek($pvs, $messages);
    }
    // 1. edit page
    else {
        $title_part = "Edit Seek";
        $content_html = content_of_edit_seek($pvs, $messages);
    }
    
    // return
    return [$title_part, $content_html, $messages];
}

// POSTed parameter's check

function select_duplicated_seeks_from_db(string $email, string $title){
    return true;
}

function check_text_safe(string $string_text, bool $enable_spaces_text_p , int $text_length_limit){
    // is safe POST?
    [$check_safe_post_p, $check_safe_post_text] = \FormCheck\check_if_post_is_safe($string_text);
    if(! $check_safe_post_p){
        return [null, $check_safe_post_text];
    }
    // is spaces POST?
    if((! $enable_spaces_text_p) && trim($string_text) === ''){
        return [null, "input some text"];
    }
    // is too Long?
    if(mb_strlen($string_text) > $text_length_limit){
        return [null, "Too long text. Text need to be less than {$text_length_limit} characters."];
    }
    // return safe
    return [$string_text, ""];
}

function check_radio_value_safe($radio_value, array $enabled_values = []){
    // is safe POST?
    [$check_safe_post_p, $check_safe_post_text] = \FormCheck\check_if_post_is_safe($radio_value);
    if(! $check_safe_post_p){
        return [null, $check_safe_post_text];
    }
    // is selected from list ?
    $selected_p
        = array_reduce($enabled_values, fn($carry, $item) => $carry || $item==$radio_value, false);
    if(! $selected_p){
        return [null, "Select Item from radio box."];
    }
    // return safe
    return [$radio_value, ""];
}


// prepare contents

function content_of_confirm_seek($pvs, $messages){

    global $csrf;
    $csrf_html = $csrf->hiddenInputHTML();

    $current_step_html = "<input type='hidden' name='step_current' value='confirm'>";
    
    $message_listing_or_seeking
        = ((($pvs['attribute']==='L')  ? "as Listing" :
            ($pvs['attribute']==='S')) ? "as Seeking" : "as null");
    
    $message_open_or_close
        = ((($pvs['open_close']==='open')   ? "as Opend"  :
            ($pvs['open_close']==='close')) ? "as Closed" : "as null");

    $content_confirm_form_html = <<<CONTENT
<h3> Confirm your Seek </h3>
<hr />
<h3>${pvs['title']}</h3>
<pre>${pvs['description']}</pre>
<hr />
<p>${message_listing_or_seeking}</p>
<p>${message_open_or_close}</p>

<form action="" method="POST">
  ${csrf_html}
  ${current_step_html}
  <input type="hidden" name="title"       value="${pvs['title']}" />
  <input type="hidden" name="description" value="${pvs['description']}" />
  <input type="hidden" name="attribute"   value="${pvs['attribute']}" />
  <input type="hidden" name="open_close"  value="${pvs['open_close']}" />
  back_to_edit, <input type="submit" value="Upload">
</form>
CONTENT;
    
    return $content_confirm_form_html;
}

function content_of_edit_seek($pvs, $messages){
    global $csrf;
    $csrf_html = $csrf->hiddenInputHTML();

    $current_step_html = "<input type='hidden' name='step_current' value='edit'>";
    
    $content_edit_seek_form_html = <<<CONTENT
{$messages['csrf']}
<h3> Edit Seek </h3>
<form action="" method="POST">
  ${csrf_html}
  ${current_step_html}
  <dl>
    <dt> title </dt>
    <dd> <input type="text" name="title" required value="${pvs['title']}"> </input> </dd>
    <dd> <pre>{$messages['title']}</pre> </dd>
    <dt> description </dt>
    <dd> <textarea name="description" cols="72" rows="13" required>${pvs['description']}</textarea> </dd>
    <dd> <pre>{$messages['description']}</pre> </dd>
    <dt> attribute </dt>
    <dd> <input type="radio" name="attribute" required value="L" {$pvs['checks_L']} />L: Listing
         <input type="radio" name="attribute" required value="S" {$pvs['checks_S']} />S: Seeking
    <dd> <pre>{$messages['attribute']}</pre> </dd>
    </dd>
    <dt> Opened Update / Closed Save </dt>
    <dd> <input type="radio" name="open_close" required value="open"  {$pvs['checks_open']}  /> Open Update
         <input type="radio" name="open_close" required value="close" {$pvs['checks_close']} /> Closed Save
    </dd>
    <dd> <pre>{$messages['open_close']}</pre> </dd>
  </dl>
  <input type="submit" value="Confirm"> </input>
</form>

CONTENT;
    
    return $content_edit_seek_form_html;
}

?>
