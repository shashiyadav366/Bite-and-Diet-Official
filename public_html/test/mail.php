<?php
// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Required PHPMailer files
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Create an instance; passing `true` enables exceptions
if (isset($_POST["send"])) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();                                      // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                 // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                             // Enable SMTP authentication
        $mail->Username   = 'yadavshashi@gmail.com';          // Your Gmail email
        $mail->Password   =
        'bovuzhjkvfwixjsd';              // App password generated from Google
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;      // Enable SSL encryption
        $mail->Port       = 465;                              // TCP port to connect to

        // Recipients
        $mail->setFrom($_POST["email"], $_POST["name"]); // Sender Email and Name
        $mail->addAddress('yadavshashi366@gmail.com');          // Add recipient email
        $mail->addReplyTo($_POST["email"], $_POST["name"]); // Reply-to sender email

        // Content
        $mail->isHTML(true);                               // Set email format to HTML
        $mail->Subject = $_POST["subject"];                // Email subject
        $mail->Body    = $_POST["message"];                // Email body content

        // Send email
        $mail->send();
        echo "<script>alert('Message was sent successfully!');</script>";
    } catch (Exception $e) {
        echo "<script>alert('Message could not be sent. Mailer Error: {$mail->ErrorInfo}');</script>";
    }
}
?>
