<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

$config = parse_ini_file('config.ini', true);
try {
    // Server settings
    $mail->SMTPDebug = 2;                  // Enable verbose debug output
    if (isset($config['email']['use_smtp']) && $config['email']['use_smtp'] == true) {
        $mail->isSMTP();                       // Set mailer to use SMTP
    }
    // $mail->Host       = 'claudette.mayfirst.org';        // Specify main SMTP server
    $mail->Port       = 1025;                 // TCP port to connect to
    // $mail->Helo = 'mayfirst.org';  
    // Recipients
    $mail->setFrom('shinano@tng.coop', 'shinano');
    $mail->addAddress('yasu@yasuaki.com', 'Yasuaki');     // Add a recipient

    // Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = 'Hello';
    $mail->Body    = 'Hello from PHPMailer';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
