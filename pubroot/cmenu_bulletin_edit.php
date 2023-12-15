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

// deny not POST request.
if($request_method!="POST"){
    RenderByTemplate("template.html", "invalid request - Shinano -", "invalid request");
    exit();
}



// fill variables by DataBase or POSTed values

$step_demand = $_POST['step_demand'];
$job_entry_id = intval($_POST['job_entry_id']);

$pvs = array(); // posted values for entry update.


if(in_array($step_demand, ['ask_db_edit_post'])) {
    // ask DB about editable job_entry
    $db_entry_current = select_job_endty_by_entry_id_if_user($job_entry_id, $login->user('id'))[0];

    // exit if entry is not allowed or not found
    if(is_null($db_entry_current)) {
        RenderByTemplate("template.html", "invalid request - Shinano -",
                         "invalid request. there is no entry which you can edit");
        exit();
    }

    // set $pvs by DB data
    [$pvs['title'], $pvs['description'], $pvs['attribute'], $pvs['open_close']]
    = [h($db_entry_current['title']),
       h($db_entry_current['description']),
       h($db_entry_current['attribute']),
       (job_entry_opened_p($db_entry_current['opened_at'], $db_entry_current['closed_at']))
        ? 'open' : 'close'
    ];

} elseif(in_array($step_demand, ['confirm', 'reedit', 'update'])) {
    //set $pvs by POSTed values
    $form_accessors = ["title", "description", "attribute", "open_close"];
    $post_data = array_map(fn($accessor) => h($_POST[$accessor]), $form_accessors);
    [$pvs['title'], $pvs['description'], $pvs['attribute'], $pvs['open_close']] = $post_data;
} else {
    RenderByTemplate("template.html", "invalid request - Shinano -", "invalid request. step error");
    exit();
}

// according to POSTing, radio button into checked.
[$pvs['checks_L'], $pvs['checks_S']] = [($pvs['attribute']==='L' ? 'checked' : ''),
                                        ($pvs['attribute']==='S' ? 'checked' : '')];

[$pvs['checks_open'] ,$pvs['checks_close']] = [($pvs['open_close']==='open'  ? 'checked' : ''),
                                               ($pvs['open_close']==='close' ? 'checked' : '')];


// check safety of POSTed value in specific step

$messages = array();
$post_checks = array();

if (in_array($step_demand, ['confirm', 'update', 'reedit'])) {
    // check CSRF
    if(!$csrf->checkToken()){
        $messages['csrf'] = "invalid token. use form.\n";
        return null;
    }

    [[$post_checks['title'], $messages['title']],
     [$post_checks['description'], $messages['description']],
     [$post_checks['attribute'], $messages['attribute']],
     [$post_checks['open_close'], $messages['open_close']],
     [$post_checks['title_duplicated_in_each_user'], $messages['title_duplicated_in_each_user'], $messages['title_duplicated_url_p']]]
    = [\FormCheck\check_text_safe(trim($pvs['title']), false, (256 - 4)),
       \FormCheck\check_text_safe($pvs['description'], false, (16384 - 4)),
       \FormCheck\check_radio_value_safe($pvs['attribute'], ['L', 'S']),
       \FormCheck\check_radio_value_safe($pvs['open_close'], ['open', 'close']),
       check_title_duplicate_in_each_user($login->user('email'), trim($pvs['title']), $job_entry_id) // check duplicated title
    ];

    $safe_form_post_p = array_reduce($post_checks, (fn($carry, $item) => $carry&&($item||$item==="")), true);
}

// prepare html content
// 3. update database if OK POSTing
if ($safe_form_post_p && in_array($step_demand, ['update'])) {
    // UPDATE DB
    $loggedin_email = $login->user('email');
    
    global $data_source_name, $sql_rw_user, $sql_rw_pass;
    $post_successed_p 
    = \Tx\with_connection($data_source_name, $sql_rw_user, $sql_rw_pass)(
        function($conn_rw) use($job_entry_id, $loggedin_email, $post_checks) {
            \TxSnn\update_job_things($conn_rw, $job_entry_id,
                                     $loggedin_email, $post_checks['attribute'],
                                     $post_checks['title'], $post_checks['description']);

            $func_open_close = ( $post_checks['open_close']=='open') ?  '\TxSnn\open_job_things' :
                               (($post_checks['open_close']=='close') ? '\TxSnn\close_job_things' :
                                null);            
            $func_open_close($post_checks['attribute'])($conn_rw, $loggedin_email, $job_entry_id);
            
            return true;
    });

    [$bottom, $post_a_href, $post_sucessed_p] = check_title_duplicate_in_each_user($loggedin_email, $post_checks['title']);

    if ($post_successed_p){
        $title_part = "updated";
        $content_html = "Your post is updated at <a href='{$post_a_href}'>here</a> <br />"
                      . "or back to <a href='{$pubroot}'>index_menu</a> <br />"
                      . "or back to <a href='{$pubroot}cmenu_bulletins.php'>your bulletins edit</a>";
    } else {
        $title_part = "something wrong";
        $content_html = "something wrong";
        error_log("Error of POSTing: failed POST or failed DataBase state");
    }
}
// 2. confirm page
elseif ($safe_form_post_p && in_array($step_demand, ['confirm'])) {
    $title_part = 'please confirm';
    $content_html = content_of_confirm_bulletin($pvs, $messages, $job_entry_id);
}
// 1 or 0. edit page
elseif ((in_array($step_demand, ['ask_db_edit_post', 'reedit'])) ||
        ((! $safe_form_post_p) && in_array($step_demand, ['confirm']))) {
    $title_part = 'edit bulletin';
    $content_html = content_of_edit_bulletin($pvs, $messages, $job_entry_id);
}
// 0. wrong request
else {
    RenderByTemplate("template.html", "invalid request - Shinano -", "invalid request. step error");
    exit();
} 



// DB Data of current job_entry's id

function select_job_endty_by_entry_id_if_user(int $job_entry_id, int $user_id){
    $sql_sel_entry = "SELECT J.id AS eid, U.id AS uid, U.email,"
                   . "    J.title, J.description, J.attribute, J.opened_at, J.closed_at"
                   . "  FROM user as U INNER JOIN job_entry AS J"
                   . "  WHERE J.id = :job_id"
                   . "    AND U.id = :user_id"
                   . ";";
    
    $ret0 = db_ask_ro($sql_sel_entry, [':job_id'=>$job_entry_id, ':user_id'=>$user_id],
                      \PDO::FETCH_ASSOC);
    return $ret0;
}



// Render to HTML by template.

$content_html = <<<CONTENT
${content_html}
CONTENT;

RenderByTemplate("template.html", "{$title_part} - Shinano -", $content_html);


// actual content

function content_of_confirm_bulletin($pvs, $messages, $entry_id){

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
<p> job_entry id is {$entry_id} </p>
<p>${pvs['description']}</p>
<hr />
<p>${message_listing_or_seeking}</p>
<p>${message_open_or_close}</p>

<form action="" method="POST">
  ${csrf_html}  
  <input type='hidden' name='job_entry_id' value="${entry_id}" />

  <input type="hidden" name="title"        value="${pvs['title']}" />
  <input type="hidden" name="description"  value="${pvs['description']}" />
  <input type="hidden" name="attribute"    value="${pvs['attribute']}" />
  <input type="hidden" name="open_close"   value="${pvs['open_close']}" />

  <input type="submit" name="step_demand" value="reedit">
  <input type="submit" name="step_demand" value="update">
</form>
CONTENT;
    
    return $content_confirm_form_html;
}


function content_of_edit_bulletin($pvs, $messages, $entry_id){
    global $csrf;
    $csrf_html = $csrf->hiddenInputHTML();

    $title_duplicate_message = 
        (($messages['title_duplicated_in_each_user']==="") ? "" :
         ($messages['title_duplicated_url_p']===true)) ? 
        "\ntitle you put is duplicated at <a href='{$messages['title_duplicated_in_each_user']}'>here</a> " :
        "\n".$messages['title_duplicated_in_each_user'];

    $messages_for_title = $messages['title'] . $title_duplicate_message;

    $content_html_form = <<<CONTENT_HTML_FORM
<h3> Edit Bulletin </h3>
<p> job_entry id is {$entry_id} </p>
<form action="" method="post">
  ${csrf_html}
  <input type='hidden' name='job_entry_id' value="${entry_id}" />
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

CONTENT_HTML_FORM;
    return $content_html_form;

}


?>
