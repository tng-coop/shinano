<?php

declare(strict_types=1);

//include_once(__DIR__ . "/../../lib/form_check.php");
include_once(__DIR__ . '/../../lib/common.php');

// if safe form-post and alleady user-login, logout user.

if(! $request_method == "POST" || ! $csrf->checkToken()){
    $message_of_logouting = "To do logout, please use form.";
} elseif(! $login->user()) {
    $message_of_logouting = "You have not logged in.";
} else {
    $login->logout();
    $message_of_logouting = "You have loggedout.";
}


// prepare template

RenderByTemplate("template.html", "Account Logout - Shinano -",
                 $message_of_logouting);

?>
