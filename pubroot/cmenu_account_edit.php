<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");

// 

$session_cooperator = $login->user();


// deny not loggedin request
if(! $login->user()){
    please_login_page();
    exit();
}



// index page for logged in cooperator
if($session_cooperator){
    $content_index_loggedin = <<<CONTENT
<h1>Shinano.</h1>
Hello! {$session_cooperator['name']}! <br />

edit your note.

<form>

<form action="" method="POST">
  ${csrf_html}
  <dl>
    <dt> description </dt>
    <dd> <textarea name="description" cols="72" rows="5" required>${pvs['description']}</textarea> </dd>
    <dd> <pre>{$messages['description']}</pre> </dd>
    </dd>

  </dl>
  <input type="submit" name="step_demand" value="confirm"> </input>
</form>



CONTENT;

    RenderByTemplate("template.html", "Index - Shinano -",
                     $content_index_loggedin);

    exit();
}


?>
