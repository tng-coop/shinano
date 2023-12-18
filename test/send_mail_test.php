<?php

echo "email送信試験";

mb_language("Japanese");
mb_internal_encoding("UTF-8");
 
$send_to = "maau3p@gmail.com"; // email or Makinori Ikegami
$title = "やあ hello.";
$message = "こんにちは、りょうしは如何？\nhellow how are you?";
$headers = "From: from@example.com";

$result_send_mail = mb_send_mail($send_to, $title, $message, $headers);
 
if($result_send_mail){
    echo "メール送信成功！";
} else {
    echo "メール送信失敗。";
}


?>

    
