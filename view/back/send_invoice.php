<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../model/Database.php';
require_once __DIR__ . '/sendMail.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$id_event   = (int)($data['id_event']   ?? 0);
$prenom     = trim($data['prenom']      ?? '');
$nom        = trim($data['nom']         ?? '');
$email      = trim($data['email']       ?? '');
$telephone  = trim($data['telephone']   ?? '');
$places     = (int)($data['places']     ?? 1);
$mode_paiement = trim($data['mode_paiement'] ?? '');
$promo_code = strtoupper(trim($data['promo_code'] ?? ''));

// ── Server-side validation ────────────────────────────────────────────
$errors = [];
if (!$id_event)                              $errors[] = 'Événement invalide';
if (strlen($prenom) < 2)                     $errors[] = 'Prénom invalide';
if (strlen($nom) < 2)                        $errors[] = 'Nom invalide';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide';
if (!preg_match('/^(\+216)?[2-9]\d{7}$/', preg_replace('/\s/', '', $telephone)))
    $errors[] = 'Téléphone tunisien invalide (ex: 22123456)';
if ($places < 1 || $places > 10)            $errors[] = 'Nombre de places invalide';
if (!in_array($mode_paiement, ['carte', 'livraison', 'espèces', 'virement', 'gratuit']))
    $errors[] = 'Mode de paiement invalide';

if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

try {
    $pdo = Database::getConnection();

    // Get event details
    $stmt = $pdo->prepare("SELECT * FROM evenement WHERE id_event = ?");
    $stmt->execute([$id_event]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$event) { echo json_encode(['success' => false, 'error' => 'Événement introuvable']); exit; }

    $prix_unitaire = (float)$event['prix'];
    $montant_total = $prix_unitaire * $places;

    // Apply promo code if provided
    $discount_amount = 0;
    $promo_label     = '';
    if ($promo_code && $montant_total > 0) {
        $stmtP = $pdo->prepare(
            "SELECT * FROM promo_code WHERE code = ? AND active = 1
             AND (id_event IS NULL OR id_event = ?)
             AND (expires_at IS NULL OR expires_at > NOW())
             AND (max_uses IS NULL OR used_count < max_uses) LIMIT 1"
        );
        $stmtP->execute([$promo_code, $id_event]);
        $promo = $stmtP->fetch(PDO::FETCH_ASSOC);
        if ($promo) {
            if ($promo['type'] === 'percent') {
                $discount_amount = $montant_total * ($promo['discount'] / 100);
            } else {
                $discount_amount = min((float)$promo['discount'], $montant_total);
            }
            $montant_total -= $discount_amount;
            $promo_label = $promo_code . ' (-' . number_format($discount_amount, 2) . ' TND)';
            $pdo->prepare("UPDATE promo_code SET used_count = used_count + 1 WHERE id = ?")
                ->execute([$promo['id']]);
        }
    }

    // Generate unique reservation number
    $reservation_num = 'RES-' . strtoupper(substr(md5(uniqid()), 0, 8));

    // Save participation
    $stmtIns = $pdo->prepare(
        "INSERT INTO participation (id_event, nom, prenom, email, nombre_places_reservees, mode_paiement, statut, date_participation)
         VALUES (?, ?, ?, ?, ?, ?, 'en_attente', NOW())"
    );
    $stmtIns->execute([$id_event, $nom, $prenom, $email, $places, $mode_paiement]);

    // ── Check milestone & generate promo code ─────────────────────────
    $milestones = [
        1  => ['discount' => 5,  'label' => '🥄 First Step',      'prefix' => 'FIRST5'],
        3  => ['discount' => 10, 'label' => '🌱 Event Explorer',   'prefix' => 'EXPLR10'],
        5  => ['discount' => 15, 'label' => '🔥 Event Enthusiast', 'prefix' => 'ENTHU15'],
        10 => ['discount' => 20, 'label' => '🥗 Regular Attendee', 'prefix' => 'REGUL20'],
        15 => ['discount' => 25, 'label' => '💪 Dedicated Member', 'prefix' => 'DEDIC25'],
        20 => ['discount' => 30, 'label' => '⚡ Event Champion',   'prefix' => 'CHAMP30'],
        30 => ['discount' => 40, 'label' => '🏆 VIP Attendee',     'prefix' => 'VIP40'],
    ];

    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM participation WHERE email = ?");
    $stmtCount->execute([$email]);
    $participationCount = (int)$stmtCount->fetchColumn();

    $milestone_code  = null;
    $milestone_label = null;
    $milestone_disc  = null;

    if (isset($milestones[$participationCount])) {
        $m    = $milestones[$participationCount];
        // Deterministic code: same email+milestone always gives same code
        $code = $m['prefix'] . '-' . strtoupper(substr(md5($email . $m['prefix']), 0, 6));

        // Create promo code in DB (ignore if already exists)
        try {
            $pdo->prepare(
                "INSERT IGNORE INTO promo_code (code, discount, type, id_event, max_uses, expires_at, active)
                 VALUES (?, ?, 'percent', NULL, 1, DATE_ADD(NOW(), INTERVAL 30 DAY), 1)"
            )->execute([$code, $m['discount']]);
        } catch (Throwable $ignored) {}

        $milestone_code  = $code;
        $milestone_label = $m['label'];
        $milestone_disc  = $m['discount'];
    }

    // Build invoice HTML
    $event_date = date('d/m/Y', strtotime($event['date_debut']));
    $now        = date('d/m/Y H:i');
    $mode_label = match($mode_paiement) {
        'carte'     => '💳 Carte bancaire',
        'livraison' => '🚪 Paiement à la livraison',
        'espèces'   => '💵 Espèces',
        'virement'  => '🏦 Virement',
        default     => '✅ Gratuit',
    };

    $invoice_html = '<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>
body{font-family:Inter,Arial,sans-serif;background:#f5f5f5;margin:0;padding:0}
.wrap{max-width:620px;margin:30px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08)}
.header{background:linear-gradient(135deg,#b91c1c,#e63946);padding:32px;text-align:center;color:#fff}
.header h1{margin:0;font-size:22px;font-weight:700}
.header p{margin:6px 0 0;opacity:.85;font-size:13px}
.body{padding:32px}
.invoice-num{background:#fff5f5;border:1px solid #fde8e8;border-radius:8px;padding:12px 16px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center}
.invoice-num .num{font-size:18px;font-weight:800;color:#b91c1c;letter-spacing:1px}
.invoice-num .date{font-size:12px;color:#9a3535}
.section-title{font-size:13px;font-weight:700;color:#9a3535;text-transform:uppercase;letter-spacing:.8px;margin:20px 0 10px;border-bottom:1px solid #fce8e8;padding-bottom:6px}
table.info{width:100%;border-collapse:collapse;font-size:14px}
table.info td{padding:7px 0;border-bottom:1px solid #f5f5f5}
table.info td:first-child{color:#666;width:45%}
table.info td:last-child{font-weight:600;color:#111;text-align:right}
.total-row{background:#fff5f5;border-radius:8px;padding:14px 16px;display:flex;justify-content:space-between;align-items:center;margin-top:16px}
.total-row .label{font-size:14px;color:#9a3535;font-weight:600}
.total-row .amount{font-size:22px;font-weight:800;color:#b91c1c}
.status-badge{display:inline-block;background:#fef9c3;color:#854d0e;border:1px solid #fde68a;padding:6px 16px;border-radius:20px;font-size:13px;font-weight:700}
.reward-box{background:linear-gradient(135deg,#fff7ed,#fef3c7);border:2px solid #fbbf24;border-radius:12px;padding:20px;margin-top:24px;text-align:center}
.reward-box h3{color:#92400e;font-size:16px;margin:0 0 8px}
.reward-code{font-size:24px;font-weight:900;color:#b91c1c;letter-spacing:3px;background:#fff;border:2px dashed #fbbf24;border-radius:8px;padding:10px 20px;display:inline-block;margin:10px 0}
.reward-box p{color:#78350f;font-size:13px;margin:6px 0 0}
.footer{background:#fce8e8;padding:20px 32px;text-align:center;font-size:12px;color:#9a3535}
</style></head><body>
<div class="wrap">
  <div class="header">
    <div style="font-size:40px;margin-bottom:10px">🧾</div>
    <h1>Facture de Réservation</h1>
    <p>Smart Meal Planner Events</p>
  </div>
  <div class="body">
    <div class="invoice-num">
      <span class="num">' . $reservation_num . '</span>
      <span class="date">Émise le ' . $now . '</span>
    </div>

    <div class="section-title">👤 Informations client</div>
    <table class="info">
      <tr><td>Nom complet</td><td>' . htmlspecialchars($prenom . ' ' . $nom) . '</td></tr>
      <tr><td>Email</td><td>' . htmlspecialchars($email) . '</td></tr>
      <tr><td>Téléphone</td><td>' . htmlspecialchars($telephone) . '</td></tr>
    </table>

    <div class="section-title">📅 Détails de l\'événement</div>
    <table class="info">
      <tr><td>Événement</td><td>' . htmlspecialchars($event['titre']) . '</td></tr>
      <tr><td>Date</td><td>' . $event_date . '</td></tr>
      <tr><td>Lieu</td><td>' . htmlspecialchars($event['lieu']) . '</td></tr>
      <tr><td>Places réservées</td><td>' . $places . '</td></tr>
      <tr><td>Prix unitaire</td><td>' . ($prix_unitaire == 0 ? 'Gratuit' : number_format($prix_unitaire, 2) . ' TND') . '</td></tr>
      ' . ($promo_label ? '<tr><td>Code promo</td><td style="color:#16a34a">' . htmlspecialchars($promo_label) . '</td></tr>' : '') . '
      <tr><td>Mode de paiement</td><td>' . $mode_label . '</td></tr>
    </table>

    <div class="total-row">
      <span class="label">Total à payer</span>
      <span class="amount">' . ($montant_total == 0 ? 'Gratuit' : number_format($montant_total, 2) . ' TND') . '</span>
    </div>

    <div style="text-align:center;margin-top:20px">
      <span class="status-badge">⏳ En attente de confirmation</span>
    </div>
    ' . ($milestone_code ? '
    <div class="reward-box">
      <h3>🎉 Félicitations ! Vous avez débloqué ' . htmlspecialchars($milestone_label) . ' !</h3>
      <div class="reward-code">' . htmlspecialchars($milestone_code) . '</div>
      <p>' . $milestone_disc . '% de réduction sur votre prochaine inscription · Valable 30 jours</p>
    </div>' : '') . '
  </div>
  <div class="footer">
    <p>Merci pour votre inscription !</p>
    <p style="margin-top:4px">© 2026 Smart Meal Planner · Tous droits réservés</p>
  </div>
</div></body></html>';

    $invoice_text = "Facture $reservation_num\n$prenom $nom\n$email\n$telephone\n\nÉvénement: {$event['titre']}\nDate: $event_date\nLieu: {$event['lieu']}\nPlaces: $places\nTotal: " . ($montant_total == 0 ? 'Gratuit' : number_format($montant_total, 2) . ' TND') . "\n\nMerci,\nSmart Meal Planner";

    // Send invoice email
    $mailResult = sendMailSMTP(
        $email,
        $prenom . ' ' . $nom,
        '🧾 Facture ' . $reservation_num . ' — ' . $event['titre'],
        $invoice_html,
        $invoice_text
    );

    echo json_encode([
        'success'          => true,
        'reservation_num'  => $reservation_num,
        'montant'          => $montant_total,
        'mail_sent'        => $mailResult['success'] ?? false,
        'milestone_code'   => $milestone_code,
        'milestone_label'  => $milestone_label,
        'milestone_disc'   => $milestone_disc,
        'participation_count' => $participationCount,
    ]);

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
