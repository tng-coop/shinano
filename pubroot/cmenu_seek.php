<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");


// deny not loggedin request
if(! $login->user()){
    please_login_page();
    exit();
}


// fill variables by POSTed values

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
    [$title_part, $content_html] = content_and_process_by_GET($pvs, $messages);
} elseif($request_method == "POST") {
    [$title_part, $content_html] = content_and_process_by_POST($pvs, $messages);
}

// render HTML
$debug_tml = "";

$content_actual = "${debug_tml}"
                . " <h3> Edit Seek </h3>"
                . "{$content_html}";

RenderByTemplate("template.html", $title_part . "- Shinano -",
                 $content_actual);


// process and content preparing.

function content_and_process_by_GET($pvs, $messages){
    // 1. edit page
    $title_part = "Edit Seek";
    $content_html = content_of_edit_seek($pvs, $messages);

    // return
    return [$title_part, $content_html];
}

function content_and_process_by_POST($pvs, $messages){
    global $csrf;
    // check CSRF
    if(!$csrf->checkToken()){
        $messages['csrf'] = "invalid token. use form.\n";
        return null;
    }

    // form check


    // prepare Content
    
    // 3. update database page
    // if ($pvs['step_previous']='confirm') {

    // 3.n update database


    // 2. confirm page
    // elseif ($pvs['step_previous']='edit') {

    // 1. edit page
    // else {
    $title_part = "Edit Seek";
    $content_html = content_of_edit_seek($pvs, $messages);

    // return
    return [$title_part, $content_html];
}


// prepare contents

function content_of_edit_seek($pvs, $messages){
    global $csrf;

    $csrf_html = $csrf->hiddenInputHTML();
    
    $content_edit_seek_form_html = <<<CONTENT
{$messages['csrf']}
<form action="" method="post">
  ${csrf_html}
  <dl>
    <dt> title </dt>
    <dd> <input type="text" name="title" required value="${pvs['title']}"> </input> </dd>
    <dd> <pre>{$form_message_title}</pre> </dd>
    <dt> description </dt>
    <dd> <textarea name="description" cols="72" rows="13" required>${pvs['description']}</textarea> </dd>
    <dd> <pre>{$form_message_description}</pre> </dd>
    <dt> attribute </dt>
    <dd> <input type="radio" name="attribute" required value="L" {$pvs['checks_L']} />L: Listing
         <input type="radio" name="attribute" required value="S" {$pvs['checks_S']} />S: Seeking
    <dd> <pre>{$form_message_attribute}</pre> </dd>
    </dd>
    <dt> Opened Update / Closed Save </dt>
    <dd> <input type="radio" name="open_close" required value="open"  {$pvs['checks_open']}  /> Open Update
         <input type="radio" name="open_close" required value="close" {$pvs['checks_close']} /> Closed Save
    </dd>
    <dd> <pre>{$form_message_open_close}</pre> </dd>
  </dl>
  <input type="submit" value="Update"> </input>
</form>

CONTENT;
    
    return $content_edit_seek_form_html;
}

?>
