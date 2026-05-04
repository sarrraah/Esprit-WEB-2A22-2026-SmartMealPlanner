<?php
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ── Gmail SMTP config ─────────────────────────────────────────────────
if (!defined('GMAIL_USER')) {
    require_once __DIR__ . '/../../config.mail.php';
}

function sendMailSMTP($to_email, $to_name, $subject, $html, $text) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = GMAIL_USER;
        $mail->Password   = GMAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(GMAIL_USER, 'Smart Meal Planner Events');
        $mail->addAddress($to_email, $to_name);
        $mail->addReplyTo(GMAIL_USER, 'Smart Meal Planner Events');

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html;
        $mail->AltBody = $text;

        $mail->send();
        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}

function buildEmailHtml($emoji, $header_gradient, $title, $to_name, $message,
                        $event_title, $event_lieu, $event_date, $places, $prix_label,
                        $status_color, $status_label) {
    return '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
body{font-family:Inter,Arial,sans-serif;background:#f5f5f5;margin:0;padding:0}
.wrap{max-width:600px;margin:30px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08)}
.header{background:'.$header_gradient.';padding:36px 32px;text-align:center}
.header h1{color:#fff;margin:0;font-size:24px;font-weight:700}
.header p{color:rgba(255,255,255,0.85);margin:8px 0 0;font-size:14px}
.body{padding:32px}
.event-card{background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:20px;margin:20px 0}
.event-title{font-size:18px;font-weight:700;color:#111;margin-bottom:14px}
.status-badge{display:inline-block;padding:8px 20px;border-radius:20px;font-size:14px;font-weight:700;color:#fff;background:'.$status_color.'}
.footer{background:#f3f4f6;padding:20px 32px;text-align:center;font-size:12px;color:#6b7280}
</style></head><body>
<div class="wrap">
  <div class="header">
    <div style="font-size:48px;margin-bottom:12px">'.$emoji.'</div>
    <h1>'.$title.'</h1>
    <p>Smart Meal Planner Events</p>
  </div>
  <div class="body">
    <p style="font-size:16px;color:#111;margin-bottom:16px">Bonjour <strong>'.htmlspecialchars($to_name).'</strong>,</p>
    <p style="font-size:14px;color:#444;line-height:1.7;margin-bottom:20px">'.$message.'</p>
    <div class="event-card">
      <div class="event-title">📅 '.htmlspecialchars($event_title).'</div>
      <table style="width:100%;border-collapse:collapse">
        <tr><td style="padding:7px 0;color:#6b7280;font-size:13px;border-bottom:1px solid #e5e7eb">📍 Lieu</td><td style="padding:7px 0;font-weight:600;font-size:13px;text-align:right;border-bottom:1px solid #e5e7eb">'.htmlspecialchars($event_lieu).'</td></tr>
        <tr><td style="padding:7px 0;color:#6b7280;font-size:13px;border-bottom:1px solid #e5e7eb">🗓️ Date</td><td style="padding:7px 0;font-weight:600;font-size:13px;text-align:right;border-bottom:1px solid #e5e7eb">'.htmlspecialchars($event_date).'</td></tr>
        <tr><td style="padding:7px 0;color:#6b7280;font-size:13px;border-bottom:1px solid #e5e7eb">🎟️ Places</td><td style="padding:7px 0;font-weight:600;font-size:13px;text-align:right;border-bottom:1px solid #e5e7eb">'.$places.' place(s)</td></tr>
        <tr><td style="padding:7px 0;color:#6b7280;font-size:13px">💰 Montant</td><td style="padding:7px 0;font-weight:700;font-size:14px;text-align:right">'.$prix_label.'</td></tr>
      </table>
    </div>
    <div style="text-align:center;margin:24px 0"><span class="status-badge">'.$status_label.'</span></div>
  </div>
  <div class="footer">
    <p>© 2026 Smart Meal Planner · Tous droits réservés</p>
    <p style="margin-top:4px">Cet email a été envoyé automatiquement.</p>
  </div>
</div></body></html>';
}

function sendConfirmationEmail($to_email, $to_name, $event_title, $event_date,
                               $event_lieu, $event_prix, $places, $statut) {
    $prix_label = ($event_prix == 0) ? 'Gratuit' : number_format($event_prix * $places, 2) . ' TND';
    $html = buildEmailHtml(
        '🎉', 'linear-gradient(135deg,#b91c1c,#e63946)',
        'Inscription enregistrée !', $to_name,
        'Votre inscription a bien été enregistrée. Voici le récapitulatif :',
        $event_title, $event_lieu, $event_date, $places, $prix_label,
        '#f59e0b', '⏳ En attente de confirmation'
    );
    $text = "Bonjour $to_name,\nVotre inscription à \"$event_title\" a été enregistrée.\nLieu: $event_lieu\nDate: $event_date\nPlaces: $places\nMontant: $prix_label\n\nMerci,\nSmart Meal Planner";
    return sendMailSMTP($to_email, $to_name, '🎉 Inscription enregistrée — ' . $event_title, $html, $text);
}

function sendStatusEmail($to_email, $to_name, $event_title, $event_date,
                         $event_lieu, $event_prix, $places, $statut) {
    $is_confirmed = ($statut === 'confirmé');
    $prix_label   = ($event_prix == 0) ? 'Gratuit' : number_format($event_prix * $places, 2) . ' TND';
    $emoji        = $is_confirmed ? '✅' : '❌';
    $title        = $is_confirmed ? 'Inscription confirmée !' : 'Inscription annulée';
    $gradient     = $is_confirmed ? 'linear-gradient(135deg,#16a34a,#22c55e)' : 'linear-gradient(135deg,#b91c1c,#e63946)';
    $status_color = $is_confirmed ? '#16a34a' : '#dc2626';
    $status_label = $is_confirmed ? '✅ Confirmée' : '❌ Annulée';
    $message      = $is_confirmed
        ? 'Bonne nouvelle ! Votre inscription a été <strong>confirmée</strong>. Nous vous attendons !'
        : 'Votre inscription a été <strong>annulée</strong>. Contactez-nous pour plus d\'informations.';

    $html = buildEmailHtml($emoji, $gradient, $title, $to_name, $message,
        $event_title, $event_lieu, $event_date, $places, $prix_label,
        $status_color, $status_label);
    $text = "Bonjour $to_name,\nVotre inscription à \"$event_title\" est : $status_label\n\nMerci,\nSmart Meal Planner";
    return sendMailSMTP($to_email, $to_name, "$emoji $title — $event_title", $html, $text);
}
