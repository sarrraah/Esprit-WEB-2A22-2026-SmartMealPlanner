<?php
require_once __DIR__ . '/../../controller/EvenementController.php';
require_once __DIR__ . '/../../controller/ParticipationController.php';

$id_event  = isset($_GET['id_event']) ? (int)$_GET['id_event'] : 0;
$evCtrl    = new EvenementController();
$evenement = $evCtrl->getEvenementById($id_event);

if (!$evenement || !str_contains(strtolower($evenement->getStatut()), 'actif')) {
    header('Location: ../front/interfaceevent.php');
    exit;
}

$isFree     = ($evenement->getPrix() == 0);
$priceLabel = $isFree ? 'Gratuit' : number_format($evenement->getPrix(), 2) . ' TND';

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom    = trim($_POST['nom']    ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email  = trim($_POST['email']  ?? '');
    $places = (int)($_POST['nombre_places_reservees'] ?? 1);
    $mode   = $_POST['mode_paiement'] ?? '';

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

    if (empty($errors)) {
        $ctrl = new ParticipationController();
        $p = new Participation(
            null, $id_event, $nom, $prenom, $email,
            $places, $mode, 'en_attente', date('Y-m-d H:i:s')
        );
        $ctrl->addParticipation($p);
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
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'DM Sans', sans-serif;
    background: #fff;
    color: #1a1a1a;
    min-height: 100vh;
    font-size: 16px;
}

/* ── NAV ── */
nav {
    background: #fff;
    border-bottom: 1px solid #e5e5e5;
    padding: 0 40px;
    display: flex; align-items: center; justify-content: space-between;
    height: 60px; position: sticky; top: 0; z-index: 100;
}
.logo { font-size: 20px; font-weight: 700; color: #1a1a1a; text-decoration: none; }
.logo span { color: #dc2626; }
.back {
    font-size: 14px; color: #555; text-decoration: none; font-weight: 500;
    padding-bottom: 2px; border-bottom: 2px solid transparent;
    transition: color .2s, border-color .2s;
}
.back:hover { color: #dc2626; border-bottom-color: #dc2626; }

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
    background: #dc2626; color: #fff;
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
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220,38,38,0.1);
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
  <a href="../front/interfaceevent.php" class="logo">Smart Event<span>.</span></a>
  <a href="../front/detailEvent.php?id=<?= $id_event ?>" class="back">← Retour à l'événement</a>
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
</script>
</body>
</html>