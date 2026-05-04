<?php
require_once __DIR__ . '/../../controller/EvenementController.php';
require_once __DIR__ . '/../../controller/ParticipationController.php';
require_once __DIR__ . '/../../config.php';

$id_event  = isset($_GET['id_event']) ? (int)$_GET['id_event'] : 0;
$evCtrl    = new EvenementController();
$evenement = $evCtrl->getEvenementById($id_event);

if (!$evenement || !str_contains(strtolower($evenement->getStatut()), 'actif')) {
    header('Location: ../front/interfaceevent.php');
    exit;
}

$isFree     = ($evenement->getPrix() == 0);
$priceLabel = $isFree ? 'Gratuit' : number_format($evenement->getPrix(), 2) . ' TND';

$errors      = [];
$success     = false;
$promoApplied = null; // will hold promo row if valid

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom    = trim($_POST['nom']    ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email  = trim($_POST['email']  ?? '');
    $places = (int)($_POST['nombre_places_reservees'] ?? 1);
    $mode   = $_POST['mode_paiement'] ?? '';
    $promoCode = strtoupper(trim($_POST['promo_code'] ?? ''));

    if (strlen($nom) < 2)
        $errors[] = "Le nom doit contenir au moins 2 caractères.";
    if (strlen($prenom) < 2)
        $errors[] = "Le prénom doit contenir au moins 2 caractères.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = "L'adresse email est invalide.";
    if ($places < 1 || $places > 10)
        $errors[] = "Le nombre de places doit être entre 1 et 10.";

    if ($isFree) {
        $mode = 'gratuit';
    } elseif (!in_array($mode, ['espèces', 'carte', 'virement'])) {
        $errors[] = "Veuillez choisir un mode de paiement.";
    }

    // Validate promo code if provided
    if (!$isFree && $promoCode !== '') {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare(
                "SELECT * FROM promo_code
                 WHERE code = ?
                   AND active = 1
                   AND (id_event IS NULL OR id_event = ?)
                   AND (expires_at IS NULL OR expires_at > NOW())
                   AND (max_uses IS NULL OR used_count < max_uses)
                 LIMIT 1"
            );
            $stmt->execute([$promoCode, $id_event]);
            $promoRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($promoRow) {
                $promoApplied = $promoRow;
            } else {
                $errors[] = "Code promo invalide ou expiré.";
            }
        } catch (Throwable $e) {
            // promo_code table may not exist yet — ignore silently
        }
    }

    if (empty($errors)) {
        $ctrl = new ParticipationController();
        $p = new Participation(
            null, $id_event, $nom, $prenom, $email,
            $places, $mode, 'en_attente', date('Y-m-d H:i:s')
        );
        $ctrl->addParticipation($p);

        // Increment promo usage counter
        if ($promoApplied) {
            try {
                $pdo = Database::getConnection();
                $pdo->prepare("UPDATE promo_code SET used_count = used_count + 1 WHERE id = ?")
                    ->execute([$promoApplied['id']]);
            } catch (Throwable $e) {}
        }

        // Send confirmation email
        require_once __DIR__ . '/sendMail.php';
        $event_date = date('d/m/Y', strtotime($evenement->getDateDebut()));
        sendConfirmationEmail(
            $email,
            $prenom . ' ' . $nom,
            $evenement->getTitre(),
            $event_date,
            $evenement->getLieu(),
            (float)$evenement->getPrix(),
            $places,
            'en_attente'
        );

        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inscription – <?= htmlspecialchars($evenement->getTitre()) ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root{
  --bg:#ffffff;
  --text:#1a1a1a;
  --muted:#555;
  --border:#e5e5e5;
  --accent:#e63946;
  --surface:#ffffff;
}
body.dark{
  --bg:#0f172a;
  --text:#f1f5f9;
  --muted:rgba(241,245,249,0.75);
  --border:#334155;
  --surface:#1e293b;
}

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    font-size: 16px;
}

/* ── NAV ── */
nav {
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 0 40px;
    display: flex; align-items: center; justify-content: space-between;
    height: 60px; position: sticky; top: 0; z-index: 100;
}
.logo { font-size: 20px; font-weight: 700; color: var(--text); text-decoration: none; }
.logo span { color: var(--accent); }
.back {
    font-size: 14px; color: var(--muted); text-decoration: none; font-weight: 500;
    padding-bottom: 2px; border-bottom: 2px solid transparent;
    transition: color .2s, border-color .2s;
}
.back:hover { color: var(--accent); border-bottom-color: var(--accent); }

/* navbar tools */
.nav-right{display:flex;align-items:center;gap:12px;}
.topbar-tools{position:relative;display:inline-flex;align-items:center;gap:10px;}
.icon-btn{
  position:relative;width:40px;height:40px;border-radius:999px;
  border:1px solid var(--border);background:var(--surface);color:var(--text);
  display:grid;place-items:center;cursor:pointer;
  transition:transform .15s ease, background .15s ease, border-color .15s ease;
}
.icon-btn:hover{transform:translateY(-1px);background:rgba(230,57,70,0.08);border-color:rgba(230,57,70,0.35);}
.notif-badge{
  position:absolute;top:-6px;right:-6px;min-width:20px;height:20px;padding:0 6px;border-radius:999px;
  background:var(--accent);color:#fff;font-size:11px;font-weight:800;line-height:20px;text-align:center;
  border:2px solid var(--surface);display:none;
}
.notif-dropdown{
  position:absolute;right:0;top:48px;width:min(380px,82vw);
  background:var(--surface);border:1px solid var(--border);border-radius:16px;
  box-shadow:0 28px 60px rgba(15,23,42,0.14);overflow:hidden;display:none;z-index:2000;
}
.notif-dropdown.open{display:block;}
.notif-head{padding:14px 16px;background:rgba(230,57,70,0.12);border-bottom:1px solid rgba(230,57,70,0.18);display:flex;justify-content:space-between;gap:10px;}
.notif-head strong{font-size:13px;letter-spacing:0.08em;text-transform:uppercase;}
.notif-list{max-height:360px;overflow:auto;}
.notif-item{padding:12px 16px;border-bottom:1px solid rgba(15,23,42,0.08);display:grid;gap:4px;}
.notif-item:last-child{border-bottom:none;}
.notif-item .title{font-weight:800;font-size:13px;}
.notif-item .desc{color:var(--muted);font-size:13px;line-height:1.4;}
.notif-item a{text-decoration:none;color:inherit;}
.notif-empty{padding:18px 16px;color:var(--muted);font-size:13px;}

/* ── LAYOUT ── */
.section { padding: 16px 0 24px; }
.container { max-width: 100%; margin: 0; padding: 0 20px; }

h2 { font-size: 28px; font-weight: 700; color: #1a1a1a; margin-bottom: 24px; }

/* ── RECAP CARD ── */
.recap {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 24px;
    display: flex; gap: 14px; align-items: flex-start;
}
.recap-icon { font-size: 26px; }
.recap-title { font-size: 15px; font-weight: 600; color: #1a1a1a; margin-bottom: 4px; }
.recap-meta { font-size: 13px; color: #888; display: flex; flex-direction: column; gap: 3px; }
.price-tag {
    display: inline-block; margin-top: 8px;
    background: var(--accent); color: #fff;
    font-weight: 700; font-size: 13px;
    padding: 4px 12px; border-radius: 999px;
}

/* ── ALERTS ── */
.alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 10px; font-size: 14px; }
.alert-danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.alert-success { background: #f0fdf4; color: #166534; border: 1px solid #86efac; }

/* ── FORM GRID ── */
.row { display: flex; flex-wrap: wrap; gap: 20px; }
.col-md-6 { flex: 1 1 calc(50% - 10px); min-width: 260px; }
.col-12 { flex: 0 0 100%; }

/* ── LABELS & INPUTS ── */
.form-label { display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px; }
.form-control,
.form-select {
    width: 100%; padding: 13px 16px;
    border: 1px solid #d1d5db; border-radius: 8px;
    background: #fff; color: #1a1a1a;
    font-size: 15px; font-family: 'DM Sans', sans-serif;
    outline: none; transition: border-color .2s, box-shadow .2s;
}
.form-control:focus, .form-select:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(230,57,70,0.12);
}
.form-control::placeholder { color: #aaa; }
.form-control.err { border-color: #dc2626; }
.field-error { font-size: 12px; color: #dc2626; margin-top: 4px; display: none; }
.field-error.show { display: block; }

/* ── PAYMENT OPTIONS ── */
.pay-options { display: grid; grid-template-columns: repeat(3,1fr); gap: 10px; }
.pay-card input { display: none; }
.pay-card label {
    display: flex; flex-direction: column; align-items: center; gap: 6px;
    padding: 14px 8px; border: 1.5px solid #d1d5db; border-radius: 8px;
    cursor: pointer; font-size: 13px; font-weight: 500; color: #555;
    transition: all .15s; text-align: center;
}
.pay-card label:hover { border-color: #dc2626; background: #fef2f2; color: #dc2626; }
.pay-card input:checked + label { border-color: #dc2626; background: #fef2f2; color: #dc2626; font-weight: 600; }
.pay-icon { font-size: 22px; }

/* ── FREE BADGE ── */
.free-badge {
    padding: 13px 16px; background: #f0fdf4;
    border: 1px solid #86efac; border-radius: 8px;
    font-size: 14px; color: #15803d; font-weight: 500;
}

/* ── BUTTONS ── */
.d-flex { display: flex; }
.gap-2 { gap: 10px; }
.btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 22px; border-radius: 8px;
    font-size: 14px; font-weight: 600; font-family: 'DM Sans', sans-serif;
    cursor: pointer; border: none; text-decoration: none; transition: all .15s;
}
.btn-danger { background: #dc2626; color: #fff; }
.btn-danger:hover { background: #b91c1c; }
.btn-full { width: 100%; justify-content: center; padding: 13px; font-size: 15px; }
.btn-outline-secondary { background: #fff; color: #555; border: 1px solid #d1d5db; }
.btn-outline-secondary:hover { background: #f9fafb; }

/* ── SUCCESS BOX ── */
.success-box {
    background: #fff; border: 1px solid #e5e5e5;
    border-radius: 12px; padding: 48px 32px;
    text-align: center; max-width: 560px;
}
.success-icon { font-size: 60px; margin-bottom: 16px; }
.success-title { font-size: 24px; font-weight: 700; color: #1a1a1a; margin-bottom: 10px; }
.success-sub { font-size: 15px; color: #555; line-height: 1.8; margin-bottom: 28px; }

/* ── NOTE ── */
.note { font-size: 12px; color: #aaa; text-align: center; margin-top: 14px; line-height: 1.6; }

@media (max-width: 500px) {
    nav { padding: 0 16px; }
    .col-md-6 { flex: 0 0 100%; }
    .pay-options { grid-template-columns: 1fr 1fr; }
}
</style>
</head>
<body>

<nav>
  <a href="listEvenements.php" class="logo">Smart Meal Planner<span>.</span></a>
  <div class="nav-right">
    <a href="../front/detailEvent.php?id=<?= $id_event ?>" class="back">← Retour à l'événement</a>
    <div class="topbar-tools">
      <button class="icon-btn" id="notif-btn" type="button" aria-label="Notifications">
        <i class="bi bi-bell"></i>
        <span class="notif-badge" id="notif-badge">0</span>
      </button>
      <div class="notif-dropdown" id="notif-dropdown">
        <div class="notif-head">
          <strong>Notifications</strong>
          <span style="color:var(--muted);font-size:12px">Auto refresh</span>
        </div>
        <div class="notif-list" id="notif-list"></div>
      </div>
      <button class="icon-btn" id="theme-toggle-btn" type="button" aria-label="Theme toggle">
        <i class="bi bi-moon-stars-fill"></i>
      </button>
    </div>
  </div>
</nav>

<section class="section">
<div class="container">

  <h2>Inscription à l'événement</h2>

  <!-- Récap événement -->
  <div class="recap">
    <div class="recap-icon">📅</div>
    <div>
      <div class="recap-title"><?= htmlspecialchars($evenement->getTitre()) ?></div>
      <div class="recap-meta">
        <span>📍 <?= htmlspecialchars($evenement->getLieu()) ?></span>
        <span>🗓️ <?= date('d/m/Y', strtotime($evenement->getDateDebut())) ?></span>
      </div>
      <span class="price-tag"><?= $priceLabel ?> / place</span>
      <?php if ($promoApplied): ?>
        <?php
          $disc = (float)$promoApplied['discount'];
          $basePrice = (float)$evenement->getPrix();
          $places_used = (int)($_POST['nombre_places_reservees'] ?? 1);
          $total = $basePrice * $places_used;
          if ($promoApplied['type'] === 'percent') {
              $saved = $total * ($disc / 100);
          } else {
              $saved = min($disc, $total);
          }
          $finalPrice = max(0, $total - $saved);
        ?>
        <span class="price-tag" style="background:#166534;margin-left:6px">
          🎟️ <?= htmlspecialchars($promoApplied['code']) ?> appliqué — économie de <?= number_format($saved, 2) ?> TND
        </span>
        <span class="price-tag" style="background:#1d4ed8;margin-left:6px">
          Total : <?= number_format($finalPrice, 2) ?> TND
        </span>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($success): ?>

  <!-- Success -->
  <div class="success-box">
    <div class="success-icon">🎉</div>
    <div class="success-title">Registration saved!</div>
    <div class="success-sub">
      Your participation request for<br>
      <strong><?= htmlspecialchars($evenement->getTitre()) ?></strong><br>
      has been received. Your status is <strong>“pending”</strong> —
      you will be notified by email once confirmed.
    </div>
    <div class="d-flex gap-2" style="justify-content:center;flex-wrap:wrap">
      <a href="../front/interfaceevent.php" class="btn btn-danger">🗓️ View all events</a>
      <a href="../front/detailEvent.php?id=<?= $id_event ?>" class="btn btn-outline-secondary">↩ Back to event</a>
    </div>
  </div>

  <?php else: ?>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
  <?php endforeach; ?>

  <form method="POST" action="" id="regForm" novalidate class="row">

    <!-- Nom -->
    <div class="col-md-6">
      <label class="form-label">Nom *</label>
      <input type="text" name="nom" id="nom" class="form-control"
             placeholder="Ben Ali"
             value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
      <span class="field-error" id="err-nom"></span>
    </div>

    <!-- Prénom -->
    <div class="col-md-6">
      <label class="form-label">Prénom *</label>
      <input type="text" name="prenom" id="prenom" class="form-control"
             placeholder="Ahmed"
             value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
      <span class="field-error" id="err-prenom"></span>
    </div>

    <!-- Email -->
    <div class="col-md-6">
      <label class="form-label">Adresse email *</label>
      <input type="email" name="email" id="email" class="form-control"
             placeholder="ahmed.benali@email.com"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      <span class="field-error" id="err-email"></span>
    </div>

    <!-- Places -->
    <div class="col-md-6">
      <label class="form-label">Nombre de places *</label>
      <input type="number" name="nombre_places_reservees" id="places" class="form-control"
             min="1" max="10"
             value="<?= htmlspecialchars($_POST['nombre_places_reservees'] ?? '1') ?>">
      <span class="field-error" id="err-places"></span>
    </div>

    <!-- Mode de paiement -->
    <div class="col-12">
      <label class="form-label">Mode de paiement<?= $isFree ? '' : ' *' ?></label>
      <?php if ($isFree): ?>
        <input type="hidden" name="mode_paiement" value="gratuit">
        <div class="free-badge">✅ Cet événement est gratuit — aucun paiement requis</div>
      <?php else: ?>
        <div class="pay-options">
          <div class="pay-card">
            <input type="radio" name="mode_paiement" id="pay-especes" value="espèces"
                   <?= (($_POST['mode_paiement'] ?? '') === 'espèces') ? 'checked' : '' ?>>
            <label for="pay-especes"><span class="pay-icon">💵</span>Espèces</label>
          </div>
          <div class="pay-card">
            <input type="radio" name="mode_paiement" id="pay-carte" value="carte"
                   <?= (($_POST['mode_paiement'] ?? '') === 'carte') ? 'checked' : '' ?>>
            <label for="pay-carte"><span class="pay-icon">💳</span>Carte</label>
          </div>
          <div class="pay-card">
            <input type="radio" name="mode_paiement" id="pay-virement" value="virement"
                   <?= (($_POST['mode_paiement'] ?? '') === 'virement') ? 'checked' : '' ?>>
            <label for="pay-virement"><span class="pay-icon">🏦</span>Virement</label>
          </div>
        </div>
        <span class="field-error" id="err-mode"></span>
      <?php endif; ?>
    </div>

    <!-- Code Promo -->
    <?php if (!$isFree): ?>
    <div class="col-12">
      <label class="form-label">🎟️ Code promo (optionnel)</label>
      <div style="display:flex;gap:10px;align-items:flex-start">
        <div style="flex:1">
          <input type="text" name="promo_code" id="promo_code" class="form-control"
                 placeholder="Ex: SUMMER20"
                 value="<?= htmlspecialchars($_POST['promo_code'] ?? '') ?>"
                 style="text-transform:uppercase;letter-spacing:1px">
          <span class="field-error" id="promo-feedback" style="display:none"></span>
          <span id="promo-success" style="display:none;font-size:12px;color:#166534;margin-top:4px;display:none"></span>
        </div>
        <button type="button" id="check-promo-btn" class="btn btn-outline-secondary"
                style="white-space:nowrap;margin-top:0">Vérifier</button>
      </div>
    </div>
    <?php endif; ?>

    <!-- Submit -->
    <div class="col-12">
      <button type="submit" class="btn btn-danger btn-full">🎟️ Confirmer mon inscription</button>
      <p class="note">
        Votre inscription sera marquée <strong>« en attente »</strong> jusqu'à confirmation.<br>
        <?= $isFree ? 'Aucun frais pour cet événement.' : 'Montant total calculé selon le nombre de places.' ?>
      </p>
    </div>

  </form>

  <?php endif; ?>
</div>
</section>

<script>
// Theme + notifications (back-office)
(function () {
  function setTheme(isDark) {
    document.body.classList.toggle('dark', !!isDark);
    try { localStorage.setItem('bo_theme', isDark ? 'dark' : 'light'); } catch (e) {}
    var themeBtn = document.getElementById('theme-toggle-btn');
    if (themeBtn) themeBtn.innerHTML = isDark ? '<i class="bi bi-sun-fill"></i>' : '<i class="bi bi-moon-stars-fill"></i>';
  }
  function initTheme() {
    var saved = null;
    try { saved = localStorage.getItem('bo_theme'); } catch (e) {}
    setTheme(saved === 'dark');
    var themeBtn = document.getElementById('theme-toggle-btn');
    if (themeBtn) themeBtn.addEventListener('click', function (e) {
      e.preventDefault();
      setTheme(!document.body.classList.contains('dark'));
    });
  }
  function escapeHtml(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }
  function timeAgo(d) {
    var dt = new Date(d);
    if (isNaN(dt.getTime())) return '';
    var diff = Date.now() - dt.getTime();
    var m = Math.floor(diff / 60000);
    if (m < 1) return 'just now';
    if (m < 60) return m + ' min ago';
    var h = Math.floor(m / 60);
    if (h < 24) return h + ' h ago';
    return Math.floor(h / 24) + ' d ago';
  }
  function renderNotifs(list) {
    var badge = document.getElementById('notif-badge');
    var wrap  = document.getElementById('notif-list');
    if (!badge || !wrap) return;
    var c = Array.isArray(list) ? list.length : 0;
    badge.textContent = String(c);
    badge.style.display = c > 0 ? 'inline-grid' : 'none';
    if (!list || list.length === 0) { wrap.innerHTML = '<div class="notif-empty">No alerts for now.</div>'; return; }
    wrap.innerHTML = list.map(function (n) {
      var t = escapeHtml(n.title);
      var d = escapeHtml(n.description);
      var href = n.href ? String(n.href) : '#';
      var when = n.created_at ? timeAgo(n.created_at) : '';
      return '<div class="notif-item"><a href="'+href+'"><div class="title">'+t+'</div><div class="desc">'+d+'</div>'+(when?'<div class="desc" style="font-size:12px;opacity:.8">'+when+'</div>':'')+'</a></div>';
    }).join('');
  }
  async function fetchNotifs() {
    try {
      var res = await fetch('getNotifications.php', { headers: { 'Accept': 'application/json' } });
      var data = await res.json();
      if (data && Array.isArray(data.notifications)) renderNotifs(data.notifications);
    } catch (e) {}
  }
  function initNotifs() {
    var btn = document.getElementById('notif-btn');
    var dd  = document.getElementById('notif-dropdown');
    if (!btn || !dd) return;
    btn.addEventListener('click', function (e) {
      e.preventDefault(); e.stopPropagation();
      dd.classList.toggle('open');
      if (dd.classList.contains('open')) fetchNotifs();
    });
    document.addEventListener('click', function (e) {
      if (!dd.classList.contains('open')) return;
      if (!dd.contains(e.target) && !btn.contains(e.target)) dd.classList.remove('open');
    });
    fetchNotifs();
    setInterval(fetchNotifs, 60000);
  }
  document.addEventListener('DOMContentLoaded', function () { initTheme(); initNotifs(); });
})();

document.getElementById('regForm')?.addEventListener('submit', function(e) {
  var ok = true;
  function clear(id) {
    var f = document.getElementById(id); if(f) f.classList.remove('err');
    var s = document.getElementById('err-'+id); if(s){s.textContent='';s.classList.remove('show');}
  }
  function err(id, msg) {
    var f = document.getElementById(id); if(f) f.classList.add('err');
    var s = document.getElementById('err-'+id); if(s){s.textContent=msg;s.classList.add('show');}
    ok = false;
  }
  ['nom','prenom','email','places'].forEach(clear);

  var nom    = document.getElementById('nom').value.trim();
  var prenom = document.getElementById('prenom').value.trim();
  var email  = document.getElementById('email').value.trim();
  var places = parseInt(document.getElementById('places').value);

  if (nom.length < 2)    err('nom',    'Le nom doit contenir au moins 2 caractères.');
  if (prenom.length < 2) err('prenom', 'Le prénom doit contenir au moins 2 caractères.');
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) err('email', 'Email invalide.');
  if (isNaN(places)||places<1||places>10) err('places', 'Entre 1 et 10 places.');

  var modeInputs = document.querySelectorAll('input[name="mode_paiement"][type="radio"]');
  if (modeInputs.length > 0) {
    var modeChecked = Array.from(modeInputs).some(function(r){ return r.checked; });
    if (!modeChecked) {
      var s = document.getElementById('err-mode');
      if(s){s.textContent='Veuillez choisir un mode de paiement.';s.classList.add('show');}
      ok = false;
    }
  }

  if (!ok) e.preventDefault();
});

// ── Promo code live check ──────────────────────────────────────────────
(function () {
  var btn      = document.getElementById('check-promo-btn');
  var input    = document.getElementById('promo_code');
  var feedback = document.getElementById('promo-feedback');
  var success  = document.getElementById('promo-success');
  if (!btn || !input) return;

  var ID_EVENT = <?= (int)$id_event ?>;

  function showFeedback(msg, isOk) {
    if (feedback) {
      feedback.textContent = msg;
      feedback.style.display = msg ? 'block' : 'none';
      feedback.style.color = isOk ? '#166534' : '#dc2626';
    }
  }

  btn.addEventListener('click', async function () {
    var code = input.value.trim().toUpperCase();
    if (!code) { showFeedback('Veuillez saisir un code.', false); return; }

    btn.disabled = true;
    btn.textContent = '…';
    showFeedback('', false);

    try {
      var res  = await fetch('checkPromo.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ code: code, id_event: ID_EVENT })
      });
      var data = await res.json();
      if (data.valid) {
        showFeedback('✅ ' + data.label, true);
        input.style.borderColor = '#16a34a';
      } else {
        showFeedback('❌ ' + (data.error || 'Code invalide'), false);
        input.style.borderColor = '#dc2626';
      }
    } catch (e) {
      showFeedback('Erreur de connexion.', false);
    } finally {
      btn.disabled = false;
      btn.textContent = 'Vérifier';
    }
  });

  // Reset border on typing
  if (input) input.addEventListener('input', function () {
    input.style.borderColor = '';
    showFeedback('', false);
  });
})();
</script>
</body>
</html>