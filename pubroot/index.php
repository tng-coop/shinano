<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");

// 

$session_cooperator = $login->user();


$content_index_common = <<<CONTENT
<h1>Shinano is the something.</h1>

<p>here is Shinano document.
text or text_file is rendered.</p>

<p>once register to cooperator and login,
you can edit and search Look bulletin Board of Shinano, search Cooperator, ......</p>

<p><u>some</u> <u>document</u> <u>links</u></p>
CONTENT;
/*
   // not finished to write
   You can access cooperator page and seeking page,
   You can write and require new you project which is looking for another cooperators,
   show you or your to 
   project, cooperator to
 */


// index page for unlogged in cooperator
if(! $session_cooperator){
    $content_index_unloggedin=<<<CONTENT
{$content_index_common}

<h3>account login or create</h3>
<p>
  account
  <a href="{$pubroot}account_login.php">login</a>
  <a href="{$pubroot}account_create.php">create</a>
</p>

CONTENT;


    RenderByTemplate("template.html", "Index - Shinano -",
                     $content_index_unloggedin);

    exit();
}


// index page for logged in cooperator
if($session_cooperator){
    $content_index_loggedin = <<<CONTENT
{$content_index_common}

<h3>Hello! {$session_cooperator['name']}!</h3>
<p>
  access <a href='{$pubroot}cmenu/index.php'>cooperator menu</a> to edit your bulletin or your information.
</p>

CONTENT;

    RenderByTemplate("template.html", "Index - Shinano -",
                     $content_index_loggedin);

    exit();
}

?>
