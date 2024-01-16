<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

function mb_send_mail_compat($to, $subject, $message, $headers, $continuation = null) {
    $mail = new PHPMailer(true);
    $config = parse_ini_file(__DIR__ . '/../config.ini', true);

    try {
        // SMTP Configuration
        if (isset($config['email']['use_smtp']) && $config['email']['use_smtp']) {
            $mail->isSMTP();
            $mail->Port = 1025; // TCP port to connect to
            // ... [rest of your SMTP settings]
        }

        // Basic email setup
        $mail->setFrom('shinano@tng.coop', 'shinano'); 
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $message;

        // Handling additional headers
        if (!empty($headers) && is_array($headers)) {
            foreach ($headers as $headerName => $headerValue) {
                // Add custom headers
                $mail->addCustomHeader($headerName, $headerValue);
            }
        }

        // If a continuation is provided, call it with the PHPMailer instance
        if (is_callable($continuation)) {
            $continuation($mail);
        }

        // Send the email
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log error or handle as needed
        return false;
    }
}
