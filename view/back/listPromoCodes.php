<?php
/**
 * Admin page: manage promo codes
 */
require_once '../../model/Database.php';

$pdo = Database::getConnection();

// Auto-create table
$pdo->exec("CREATE TABLE IF NOT EXISTS promo_code (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(50) NOT NULL UNIQUE,
    discount    DECIMAL(10,2) NOT NULL DEFAULT 0,
    type        ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    id_event    INT NULL,
    milestone_id INT NULL,
    max_uses    INT NULL,
    used_count  INT NOT NULL DEFAULT 0,
    expires_at  DATETIME NULL,
    active      TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME DEFAULT NOW()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Add milestone_id column if missing (for existing installs)
try { $pdo->exec("ALTER TABLE promo_code ADD COLUMN milestone_id INT NULL AFTER id_event"); } catch(Throwable $e) {}

$errors  = [];
$success = '';

// ── Milestone definitions ─────────────────────────────────────────────
$milestone_defs = [
    1 => ['emoji' => '🥄', 'label' => 'First Step',       'discount' => 5,  'count' => 1],
    2 => ['emoji' => '🌱', 'label' => 'Event Explorer',    'discount' => 10, 'count' => 3],
    3 => ['emoji' => '🔥', 'label' => 'Event Enthusiast',  'discount' => 15, 'count' => 5],
    4 => ['emoji' => '🥗', 'label' => 'Regular Attendee',  'discount' => 20, 'count' => 10],
    5 => ['emoji' => '💪', 'label' => 'Dedicated Member',  'discount' => 25, 'count' => 15],
    6 => ['emoji' => '⚡', 'label' => 'Event Champion',    'discount' => 30, 'count' => 20],
    7 => ['emoji' => '🏆', 'label' => 'VIP Attendee',      'discount' => 40, 'count' => 30],
];

// Handle save milestone codes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_milestones') {
    foreach ($milestone_defs as $mid => $m) {
        $code = strtoupper(trim($_POST['milestone_code'][$mid] ?? ''));
        if (!$code) continue;
        // Upsert: delete old milestone code for this id, insert new
        $pdo->prepare("DELETE FROM promo_code WHERE milestone_id = ?")->execute([$mid]);
        try {
            $pdo->prepare(
                "INSERT INTO promo_code (code, discount, type, milestone_id, max_uses, expires_at, active)
                 VALUES (?, ?, 'percent', ?, NULL, NULL, 1)"
            )->execute([$code, $m['discount'], $mid]);
        } catch (Throwable $e) {
            $errors[] = "Code « $code » : " . $e->getMessage();
        }
    }
    if (empty($errors)) $success = '✅ Codes milestone sauvegardés.';
}

// Fetch existing milestone codes
$milestoneCodes = [];
$rows = $pdo->query("SELECT milestone_id, code FROM promo_code WHERE milestone_id IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) $milestoneCodes[(int)$r['milestone_id']] = $r['code'];

// Handle toggle active / delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare("UPDATE promo_code SET active = 1 - active WHERE id = ?")->execute([$id]);
    header('Location: listPromoCodes.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare("DELETE FROM promo_code WHERE id = ?")->execute([$id]);
    header('Location: listPromoCodes.php'); exit;
}

// Fetch all promo codes (non-milestone only)
$promos = $pdo->query("SELECT * FROM promo_code WHERE milestone_id IS NULL ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch events for the dropdown
$events = $pdo->query("SELECT id_event, titre FROM evenement ORDER BY titre")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Codes Promo – Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#fff5f5;color:#1a0505;min-height:100vh}
nav{background:#fff;border-bottom:1.5px solid #f7c1c1;padding:0 32px;display:flex;align-items:center;justify-content:space-between;height:60px;position:sticky;top:0;z-index:100}
.logo{font-size:18px;font-weight:600;color:#1a0505;text-decoration:none}.logo span{color:#b91c1c}
.nav-links a{font-size:14px;color:#9a3535;text-decoration:none;font-weight:500;margin-left:24px}
.nav-links a:hover{color:#b91c1c}
.container{max-width:1000px;margin:0 auto;padding:32px}
h1{font-size:22px;font-weight:600;margin-bottom:24px;color:#1a0505}
.card{background:#fff;border:1px solid #fde8e8;border-radius:14px;padding:24px;margin-bottom:28px}
.card h2{font-size:15px;font-weight:600;margin-bottom:18px;color:#1a0505;border-bottom:1px solid #fce8e8;padding-bottom:12px}
.form-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px}
.form-group{display:flex;flex-direction:column;gap:6px}
label{font-size:13px;font-weight:500;color:#4a1515}
input,select{border:1px solid #fde8e8;border-radius:8px;padding:9px 12px;font-size:13px;font-family:inherit;outline:none;background:#fff;color:#1a0505}
input:focus,select:focus{border-color:#b91c1c;box-shadow:0 0 0 3px rgba(185,28,28,.1)}
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .15s}
.btn-primary{background:#b91c1c;color:#fff}.btn-primary:hover{background:#991b1b}
.btn-sm{padding:5px 12px;font-size:12px}
.btn-outline{background:transparent;border:1px solid #f7c1c1;color:#9a3535}.btn-outline:hover{background:#fce8e8}
.btn-danger-sm{background:#fce8e8;color:#b91c1c;border:1px solid #f7c1c1}.btn-danger-sm:hover{background:#f7c1c1}
.alert{padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:14px}
.alert-success{background:#f0fdf4;color:#166534;border:1px solid #86efac}
.alert-danger{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
table{width:100%;border-collapse:collapse;font-size:13px}
th{text-align:left;padding:10px 12px;background:#fce8e8;color:#7f1d1d;font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:.5px}
td{padding:10px 12px;border-bottom:1px solid #fce8e8;vertical-align:middle}
tr:last-child td{border-bottom:none}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
.badge-active{background:#dcfce7;color:#166534}
.badge-inactive{background:#fce8e8;color:#7f1d1d}
.badge-percent{background:#eff6ff;color:#1d4ed8}
.badge-fixed{background:#fdf4ff;color:#7e22ce}
.actions{display:flex;gap:6px}
@media(max-width:600px){.form-grid{grid-template-columns:1fr}.container{padding:16px}}
</style>
</head>
<body>
<nav>
  <a href="listEvenements.php" class="logo">Event <span>Admin</span></a>
  <div class="nav-links">
    <a href="listEvenements.php">Événements</a>
    <a href="listParticipations.php">Participations</a>
    <a href="listPromoCodes.php">Codes Promo</a>
  </div>
</nav>

<div class="container">
  <h1>🎟️ Gestion des Codes Promo</h1>

  <?php foreach ($errors as $e): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
  <?php endforeach; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <!-- Milestone Codes -->
  <div class="card">
    <h2>🎯 Codes Promo par Palier (Goals & Rewards)</h2>
    <p style="font-size:13px;color:#9a3535;margin-bottom:16px">Définissez le code promo que les utilisateurs recevront à chaque palier d'inscription.</p>
    <form method="POST">
      <input type="hidden" name="action" value="save_milestones">
      <table>
        <thead>
          <tr>
            <th>Palier</th>
            <th>Inscriptions requises</th>
            <th>Réduction</th>
            <th>Code Promo</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($milestone_defs as $mid => $m): ?>
          <tr>
            <td><strong><?= $m['emoji'] ?> <?= htmlspecialchars($m['label']) ?></strong></td>
            <td><?= $m['count'] ?> événement(s)</td>
            <td><span class="badge badge-percent"><?= $m['discount'] ?>%</span></td>
            <td>
              <input type="text" name="milestone_code[<?= $mid ?>]"
                value="<?= htmlspecialchars($milestoneCodes[$mid] ?? '') ?>"
                placeholder="ex: WELCOME5"
                style="text-transform:uppercase;width:160px">
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div style="margin-top:16px">
        <button type="submit" class="btn btn-primary">💾 Sauvegarder les codes</button>
      </div>
    </form>
  </div>


  <!-- List -->
  <div class="card">
    <h2>📋 Codes existants (<?= count($promos) ?>)</h2>
    <?php if (empty($promos)): ?>
      <p style="color:#9a3535;font-size:13px">Aucun code promo pour l'instant.</p>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Code</th>
          <th>Réduction</th>
          <th>Événement</th>
          <th>Utilisations</th>
          <th>Expire</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($promos as $p):
          $eventName = '—';
          if ($p['id_event']) {
            foreach ($events as $ev) {
              if ($ev['id_event'] == $p['id_event']) { $eventName = $ev['titre']; break; }
            }
          }
          $discLabel = ($p['type'] === 'percent')
            ? number_format($p['discount'], 0) . '%'
            : number_format($p['discount'], 2) . ' TND';
          $usesLabel = $p['max_uses']
            ? $p['used_count'] . ' / ' . $p['max_uses']
            : $p['used_count'] . ' / ∞';
          $expLabel = $p['expires_at']
            ? date('d/m/Y H:i', strtotime($p['expires_at']))
            : '—';
        ?>
        <tr>
          <td><strong><?= htmlspecialchars($p['code']) ?></strong></td>
          <td>
            <span class="badge badge-<?= $p['type'] ?>"><?= $discLabel ?></span>
          </td>
          <td><?= htmlspecialchars($eventName) ?></td>
          <td><?= $usesLabel ?></td>
          <td><?= $expLabel ?></td>
          <td>
            <span class="badge <?= $p['active'] ? 'badge-active' : 'badge-inactive' ?>">
              <?= $p['active'] ? 'Actif' : 'Inactif' ?>
            </span>
          </td>
          <td>
            <div class="actions">
              <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline">
                  <?= $p['active'] ? '⏸ Désactiver' : '▶ Activer' ?>
                </button>
              </form>
              <form method="POST" style="display:inline"
                    onsubmit="return confirm('Supprimer ce code promo ?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger-sm">🗑 Supprimer</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
