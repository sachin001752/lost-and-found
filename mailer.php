<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

/**
 * Sends an OTP email to the user
 * 
 * @param string $toEmail Recipient email
 * @param string $toName Recipient name
 * @param string $otp The OTP code
 * @return bool|string True on success, error message on failure
 */
function sendOTPMail($toEmail, $toName, $otp) {
    // Always log OTP to file for testing/reference
    $logMessage = "Time: " . date('Y-m-d H:i:s') . " | To: $toEmail ($toName) | OTP: $otp" . PHP_EOL;
    file_put_contents('otp_log.txt', $logMessage, FILE_APPEND);

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = 0; // Disable debug for live use
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = trim(SMTP_USER);
        $mail->Password   = str_replace(' ', '', trim(SMTP_PASS));
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';

        // Bypassing SSL verification for local testing
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);

        // Headers to help avoid spam
        $mail->XMailer = 'Kalri Lost & Found Mailer';
        
        // Subject with Test Prefix if needed
        $subjectPrefix = (MAIL_MODE === 'test') ? '[TEST] ' : '';

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subjectPrefix . 'Verification Code: ' . $otp;


        
        // Beautiful Email Template
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 12px;'>
                <div style='text-align: center; margin-bottom: 20px;'>
                    <h1 style='color: #667eea; margin: 0;'>Welcome to ".SMTP_FROM_NAME."</h1>
                </div>
                <div style='background-color: #f8f9fa; padding: 30px; border-radius: 10px; text-align: center;'>
                    <p style='font-size: 16px; color: #333;'>Hello <strong>$toName</strong>,</p>
                    <p style='font-size: 16px; color: #555;'>Thank you for joining us! Please use the following code to verify your account:</p>
                    <div style='font-size: 36px; font-weight: bold; letter-spacing: 5px; color: #764ba2; margin: 30px 0; padding: 15px; border: 2px dashed #764ba2; display: inline-block;'>
                        $otp
                    </div>
                    <p style='font-size: 14px; color: #888;'>This code will expire in 10 minutes.</p>
                </div>
                <div style='margin-top: 25px; text-align: center; font-size: 12px; color: #aaa;'>
                    <p>If you didn't request this code, you can safely ignore this email.</p>
                    <p>&copy; " . date('Y') . " ".SMTP_FROM_NAME.". All rights reserved.</p>
                </div>
            </div>
        ";
        
        $mail->AltBody = "Hello $toName, your OTP for verification is: $otp";

        $mail->send();
        return true;
    } catch (Exception $e) {
        $error = "Mail Error: {$mail->ErrorInfo}";
        file_put_contents('mail_error.log', $error . PHP_EOL, FILE_APPEND);
        return $error;
    }
}
