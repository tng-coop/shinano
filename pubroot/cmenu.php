<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");



// deny not loggedin request
if(! $login->user()){
    please_login_page();
    exit();
}


// index page for logged in cooperator
if($login->user()){
    $session_cooperator = $login->user();

    $content_cmenu = <<<CONTENT_CMENU
<h3>Hello! {$session_cooperator['name']}!.<br /> Here is Cooperator's Menu.</h3>

<ul>
  <li><a href='{$pubroot}cmenu_bulletins.php'>edit your note, edit your bulletins</a></li>
  <li><a href='{$pubroot}cmenu_bulletin_new.php'>new bulletin</a></li>
</ul>

CONTENT_CMENU;

    RenderByTemplate("template.html", "Cooperator's Menu - Shinano -",
                     $content_cmenu);

    exit();
}


?>
