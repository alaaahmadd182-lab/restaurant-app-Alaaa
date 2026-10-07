<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/../vendor/autoload.php";

function sendOtpEmail($email, $otp)
{
    $mail = new PHPMailer(true);

    try {
        $env = parse_ini_file(__DIR__ . "/../.env");

        $mail->isSMTP();
        $mail->Host = $env["MAIL_HOST"];
        $mail->SMTPAuth = true;
        $mail->Username = $env["MAIL_USERNAME"];
        $mail->Password = $env["MAIL_PASSWORD"];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int) $env["MAIL_PORT"];

        $mail->setFrom(
            $env["MAIL_FROM_ADDRESS"],
            $env["MAIL_FROM_NAME"]
        );

        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "Your Restaurant App verification code";

        $mail->Body = "
            <h2>Restaurant App</h2>
            <p>Your verification code is:</p>
            <h1>{$otp}</h1>
            <p>This code expires in 5 minutes.</p>
        ";

        $mail->AltBody =
            "Your Restaurant App verification code is {$otp}. " .
            "This code expires in 5 minutes.";

        $mail->send();

        return true;

    } catch (Throwable $e) {
        error_log("MAIL ERROR: " . $e->getMessage());
        return false;
    }
}