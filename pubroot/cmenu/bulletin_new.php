<?php

declare(strict_types=1);

include_once(__DIR__ . "/../../lib/common.php");
include_once(__DIR__ . "/../../lib/form_check.php");
include_once(__DIR__ . '/../../lib/transactions.php');

// deny not loggedin request
if(! $login->user()){
    please_login_page();
    exit();
}


// fill variables by POSTed values

$step_demand = $_POST['step_demand'];

$form_accessors = ["title", "description", "attribute", "open_close"];
$post_data = array_map(fn($accessor) => h($_POST[$accessor]), $form_accessors);
$pvs = array(); // posted values
[$pvs['title'], $pvs['description'], $pvs['attribute'], $pvs['open_close']] = $post_data;

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

$content_actual = "{$debug_tml}\n{$content_html}";

RenderByTemplate("template.html", "{$title_part} - Shinano -",
                 $content_actual);


// process and content preparing.

function content_and_process_by_GET($pvs, $messages){
    // 1. edit page
    $title_part = "edit bulletin";
    $content_html = content_of_edit_bulletin($pvs, $messages);

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
    // variables
    global $login;
    $loggedin_email = $login->user('email');

    // form check

    $post_checks = array();

    [[$post_checks['title'], $messages['title']],
     [$post_checks['description'], $messages['description']],
     [$post_checks['attribute'], $messages['attribute']],
     [$post_checks['open_close'], $messages['open_close']],
     [$post_checks['title_duplicated_in_each_user'], $messages['title_duplicated_in_each_user'], $messages['title_duplicated_url_p']]]
    = [\FormCheck\check_text_safe(trim($pvs['title']), false, (256 - 4)),
       \FormCheck\check_text_safe($pvs['description'], false, (16384 - 4)),
       \FormCheck\check_radio_value_safe($pvs['attribute'], ['L', 'S']),
       \FormCheck\check_radio_value_safe($pvs['open_close'], ['open', 'close']),
       check_title_duplicate_in_each_user($login->user('email'), trim($pvs['title'])) // check duplicated title
    ];

    $safe_form_post_p = array_reduce($post_checks, (fn($carry, $item) => $carry&&($item||$item==="")), true);

    // prepare Content

    // 3. update database page
    global $step_demand;
    if ($safe_form_post_p && $step_demand === 'upload') {

        // register user to DB.
        global $data_source_name, $sql_rw_user, $sql_rw_pass;
        \Tx\with_connection($data_source_name, $sql_rw_user, $sql_rw_pass)(
            function($conn_rw) use($loggedin_email, $post_checks) {
                $open_or_close = $post_checks['open_close'];

                // add job thing
                $entry_id = \TxSnn\add_job_things($post_checks['attribute'])
                ($conn_rw, $loggedin_email, $post_checks['title'], $post_checks['description']);

                // open or close it
                if($open_or_close =='open') {
                    \TxSnn\open_job_thing($conn_rw, $loggedin_email, $entry_id);
                }elseif($open_or_close=='close'){
                    \TxSnn\close_job_thing($conn_rw, $loggedin_email, $entry_id);
                }else {
                    return false;
                }

                return true;});

        [$bottom, $post_a_href, $post_sucessed_p] = check_title_duplicate_in_each_user($loggedin_email, $post_checks['title']);

        if($post_sucessed_p){
            global $pubroot;
            $title_part = "uploaded";
            $content_html = "Your post is uploaded at <a href='{$post_a_href}'>here</a> <br />"
                          . "or back to <a href='{$pubroot}'>index_menu</a> <br />"
                          . "or back to <a href='{$pubroot}cmenu/bulletins.php'>your bulletins edit</a>";
        } else {
            $title_part = "something wrong";
            $content_html = "something wrong";
            error_log("Error of POSTing: failed POST or failed DataBase state");
        }
    }
    // 2. confirm page
    elseif ($safe_form_post_p && $step_demand== 'confirm') {
        $title_part = "please confirm";
        $content_html = content_of_confirm_bulletin($pvs, $messages);
    }
    // 1. edit page
    else {
        $title_part = "new bulletin";
        $content_html = content_of_edit_bulletin($pvs, $messages);
    }

    // return
    return [$title_part, $content_html, $messages];
}

// prepare contents

function content_of_confirm_bulletin($pvs, $messages){

    global $csrf;
    $csrf_html = $csrf->hiddenInputHTML();

    $message_listing_or_seeking
    = $pvs['attribute']==='L' ? "as Listing" :
      ($pvs['attribute']==='S' ? "as Seeking" : "as null");

    $message_open_or_close
    = $pvs['open_close']==='open'  ? "as Opened" :
      ($pvs['open_close']==='close' ? "as Closed" : "as null");

    $content_confirm_form_html = <<<CONTENT
<h3> Confirm your Bulletin </h3>
<hr />
<h3>${pvs['title']}</h3>
<pre>${pvs['description']}</pre>
<hr />
<p>${message_listing_or_seeking}</p>
<p>${message_open_or_close}</p>

<form action="" method="POST">
  ${csrf_html}
  <input type="hidden" name="title"       value="${pvs['title']}" />
  <input type="hidden" name="description" value="${pvs['description']}" />
  <input type="hidden" name="attribute"   value="${pvs['attribute']}" />
  <input type="hidden" name="open_close"  value="${pvs['open_close']}" />

  <input type="submit" name="step_demand" value="edit">
  <input type="submit" name="step_demand" value="upload">
</form>
CONTENT;

    return $content_confirm_form_html;
}

function content_of_edit_bulletin($pvs, $messages){
    global $csrf;
    $csrf_html = $csrf->hiddenInputHTML();

    $title_duplicate_message =
        (($messages['title_duplicated_in_each_user']==="") ? "" :
         ($messages['title_duplicated_url_p']===true)) ?
        "\ntitle you put is duplicated at <a href='{$messages['title_duplicated_in_each_user']}'>here</a> " :
        "\n".$messages['title_duplicated_in_each_user'];

    $messages_for_title = $messages['title'] . $title_duplicate_message;

    $content_edit_bulletin_form_html = <<<CONTENT
{$messages['csrf']}
<h3> New Bulletin </h3>
<form action="" method="POST">
  ${csrf_html}
  <dl>

    <dt> title </dt>
    <dd> <input type="text" name="title" required value="${pvs['title']}"> </input> </dd>
    <dd> <pre>{$messages_for_title}</pre> </dd>

    <dt> description </dt>
    <dd> <textarea name="description" cols="72" rows="13" required>${pvs['description']}</textarea> </dd>
    <dd> <pre>{$messages['description']}</pre> </dd>

    <dt> attribute </dt>
    <dd> <input type="radio" name="attribute" required value="L" {$pvs['checks_L']} />L: Listing
         <input type="radio" name="attribute" required value="S" {$pvs['checks_S']} />S: Seeking
    <dd> <pre>{$messages['attribute']}</pre> </dd>

    <dt> Opened Update / Closed Save </dt>
    <dd> <input type="radio" name="open_close" required value="open"  {$pvs['checks_open']}  /> Open Update
         <input type="radio" name="open_close" required value="close" {$pvs['checks_close']} /> Closed Save
    </dd>
    <dd> <pre>{$messages['open_close']}</pre> </dd>

  </dl>
  <input type="submit" name="step_demand" value="confirm"> </input>
</form>

CONTENT;

    return $content_edit_bulletin_form_html;
}

?>
