<?php
/**
 * Send order invoice by email via PHPMailer + Gmail SMTP.
 * POST JSON: { prenom, nom, email, method, items: [{nom, quantite, prix}], total }
 */

// Suppress any PHP warnings/notices that would corrupt JSON output
error_reporting(0);
ini_set('display_errors', 0);

$autoload = __DIR__ . '/../../vendor/autoload.php';
if (!file_exists($autoload)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'autoload not found at: ' . $autoload]);
    exit;
}
require_once $autoload;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$data    = json_decode(file_get_contents('php://input'), true);
$prenom  = htmlspecialchars($data['prenom']  ?? '');
$nom     = htmlspecialchars($data['nom']     ?? '');
$email   = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
$method  = htmlspecialchars($data['method'] ?? '');
$phone   = htmlspecialchars($data['phone']  ?? '');
$items   = $data['items']  ?? [];
$total   = number_format((float)($data['total'] ?? 0), 2, ',', ' ');
$date    = date('d/m/Y H:i');
$orderNo = strtoupper(substr(md5(uniqid()), 0, 8));

if (!$email) {
    echo json_encode(['success' => false, 'error' => 'Invalid email address']);
    exit;
}

// ── Phone validation ──────────────────────────────────────────────────────
$rawPhone = $data['phone'] ?? '';
$normalizedPhone = preg_replace('/[\s\-]/', '', $rawPhone);
if (empty($normalizedPhone)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Numéro de téléphone obligatoire.']);
    exit;
}
if (!preg_match('/^(\+216|00216)?[2-9]\d{7}$/', $normalizedPhone)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Numéro de téléphone invalide.']);
    exit;
}

// ── Build HTML invoice ──────────────────────────────────────────────────────
$rows = '';
foreach ($items as $item) {
    $sous = number_format((float)$item['prix'] * (int)$item['quantite'], 2, ',', ' ');
    $rows .= '
    <tr>
      <td style="padding:10px 14px;border-bottom:1px solid #f0f0f0;">' . htmlspecialchars($item['nom']) . '</td>
      <td style="padding:10px 14px;border-bottom:1px solid #f0f0f0;text-align:center;">' . (int)$item['quantite'] . '</td>
      <td style="padding:10px 14px;border-bottom:1px solid #f0f0f0;text-align:right;">' . number_format((float)$item['prix'], 2, ',', ' ') . ' DT</td>
      <td style="padding:10px 14px;border-bottom:1px solid #f0f0f0;text-align:right;font-weight:600;color:#ce1212;">' . $sous . ' DT</td>
    </tr>';
}

$htmlBody = '
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f5f5f0;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f0;padding:30px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">

      <!-- Header -->
      <tr>
        <td style="background:#ce1212;padding:28px 32px;text-align:center;">
          <h1 style="color:white;margin:0;font-size:24px;letter-spacing:1px;">🍽️ SmartMeal Planner</h1>
          <p style="color:rgba(255,255,255,0.85);margin:6px 0 0;font-size:13px;">Order Confirmation & Invoice</p>
        </td>
      </tr>

      <!-- Thank you -->
      <tr>
        <td style="padding:28px 32px 16px;">
          <h2 style="color:#2d2d2d;margin:0 0 8px;font-size:20px;">Thank you, ' . $prenom . ' ' . $nom . '! ✅</h2>
          <p style="color:#666;margin:0;font-size:14px;line-height:1.6;">
            Your order has been confirmed. Here is your invoice summary.
          </p>
        </td>
      </tr>

      <!-- Order info -->
      <tr>
        <td style="padding:0 32px 20px;">
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#f9f9f9;border-radius:10px;padding:16px;">
            <tr>
              <td style="font-size:12px;color:#999;text-transform:uppercase;letter-spacing:1px;padding:4px 0;">Order Number</td>
              <td style="font-size:13px;font-weight:700;color:#2d2d2d;text-align:right;padding:4px 0;">#' . $orderNo . '</td>
            </tr>
            <tr>
              <td style="font-size:12px;color:#999;text-transform:uppercase;letter-spacing:1px;padding:4px 0;">Date</td>
              <td style="font-size:13px;color:#2d2d2d;text-align:right;padding:4px 0;">' . $date . '</td>
            </tr>
            <tr>
              <td style="font-size:12px;color:#999;text-transform:uppercase;letter-spacing:1px;padding:4px 0;">Payment</td>
              <td style="font-size:13px;color:#2d2d2d;text-align:right;padding:4px 0;">' . $method . '</td>
            </tr>' . ($phone ? '
            <tr>
              <td style="font-size:12px;color:#999;text-transform:uppercase;letter-spacing:1px;padding:4px 0;">Phone</td>
              <td style="font-size:13px;color:#2d2d2d;text-align:right;padding:4px 0;">📞 ' . $phone . '</td>
            </tr>' : '') . '
          </table>
        </td>
      </tr>

      <!-- Items table -->
      <tr>
        <td style="padding:0 32px 20px;">
          <table width="100%" cellpadding="0" cellspacing="0" style="border-radius:10px;overflow:hidden;border:1px solid #f0f0f0;">
            <thead>
              <tr style="background:#2d2d2d;">
                <th style="padding:10px 14px;text-align:left;color:white;font-size:11px;letter-spacing:1px;text-transform:uppercase;">Product</th>
                <th style="padding:10px 14px;text-align:center;color:white;font-size:11px;letter-spacing:1px;text-transform:uppercase;">Qty</th>
                <th style="padding:10px 14px;text-align:right;color:white;font-size:11px;letter-spacing:1px;text-transform:uppercase;">Unit Price</th>
                <th style="padding:10px 14px;text-align:right;color:white;font-size:11px;letter-spacing:1px;text-transform:uppercase;">Subtotal</th>
              </tr>
            </thead>
            <tbody>' . $rows . '</tbody>
          </table>
        </td>
      </tr>

      <!-- Total -->
      <tr>
        <td style="padding:0 32px 28px;">
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td style="font-size:16px;font-weight:700;color:#2d2d2d;">Total</td>
              <td style="font-size:22px;font-weight:900;color:#ce1212;text-align:right;">' . $total . ' DT</td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- Footer -->
      <tr>
        <td style="background:#f9f9f9;padding:20px 32px;text-align:center;border-top:1px solid #f0f0f0;">
          <p style="color:#999;font-size:12px;margin:0;">
            Thank you for shopping with SmartMeal Planner 🥗<br>
            This is an automated email — please do not reply.
          </p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>';

// ── Send via PHPMailer ───────────────────────────────────────────────────────
$mail = new PHPMailer(true);

try {
  
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'smartmealplanner22@gmail.com';
    $mail->Password   = 'hkyd hyqz mmnt qfjh';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('smartmealplanner22@gmail.com', 'SmartMeal Planner');
    $mail->addAddress($email, $prenom . ' ' . $nom);
    $mail->addReplyTo('smartmealplanner22@gmail.com', 'SmartMeal Planner');

    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = '✅ Order Confirmed #' . $orderNo . ' — SmartMeal Planner';
    $mail->Body    = $htmlBody;
    $mail->AltBody = 'Thank you ' . $prenom . ' ' . $nom . '! Your order #' . $orderNo . ' has been confirmed. Total: ' . $total . ' DT.';

    $mail->send();
    echo json_encode(['success' => true, 'order' => $orderNo]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $mail->ErrorInfo]);
}
