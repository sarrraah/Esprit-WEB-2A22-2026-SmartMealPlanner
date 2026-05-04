<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config.mail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    $mail->SMTPDebug  = 2; // Show full debug output
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = GMAIL_USER;
    $mail->Password   = GMAIL_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(GMAIL_USER, 'Smart Meal Planner Test');
    $mail->addAddress(GMAIL_USER); // send to self

    $mail->isHTML(true);
    $mail->Subject = '✅ Test Email — Smart Meal Planner';
    $mail->Body    = '<h2>Test réussi !</h2><p>L\'envoi d\'email fonctionne correctement.</p>';
    $mail->AltBody = 'Test email - Smart Meal Planner';

    $mail->send();
    echo '<h2 style="color:green">✅ Email envoyé avec succès !</h2>';
    echo '<p>Vérifie ta boîte Gmail : <strong>' . GMAIL_USER . '</strong></p>';
} catch (Exception $e) {
    echo '<h2 style="color:red">❌ Erreur d\'envoi</h2>';
    echo '<pre>' . htmlspecialchars($mail->ErrorInfo) . '</pre>';
}
