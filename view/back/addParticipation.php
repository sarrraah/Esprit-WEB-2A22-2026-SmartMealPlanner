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
            null,
            $id_event,
            $nom,
            $prenom,
            $email,
            $places,
            $mode,
            'en_attente',
            date('Y-m-d H:i:s')
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
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#fff5f5;color:#1a0505;min-height:100vh}
nav{background:#fff;border-bottom:1.5px solid #f7c1c1;padding:0 32px;height:60px;
    display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100}
.logo{font-size:18px;font-weight:600;color:#1a0505;text-decoration:none}
.logo span{color:#b91c1c}
.back{font-size:13px;color:#9a3535;text-decoration:none;font-weight:500}
.back:hover{color:#b91c1c}
.hero{background:linear-gradient(135deg,#7f1d1d,#b91c1c);padding:40px 32px;text-align:center;color:#fff}
.hero h1{font-size:22px;font-weight:600;margin-bottom:6px}
.hero p{font-size:14px;opacity:.7}
.container{max-width:580px;margin:0 auto;padding:32px 16px 60px}
.recap{background:#fff;border:1px solid #fde8e8;border-radius:14px;padding:18px 20px;
       margin-bottom:24px;display:flex;gap:14px;align-items:flex-start}
.recap-icon{font-size:28px}
.recap-title{font-size:15px;font-weight:600;color:#1a0505;margin-bottom:4px}
.recap-meta{font-size:13px;color:#9a3535;display:flex;flex-direction:column;gap:3px}
.price-tag{display:inline-block;margin-top:8px;background:#fce8e8;color:#b91c1c;
           font-weight:700;font-size:13px;padding:4px 12px;border-radius:20px}
.card{background:#fff;border:1px solid #fde8e8;border-radius:16px;padding:28px;
      box-shadow:0 2px 12px rgba(185,28,28,0.06)}
.card h2{font-size:16px;font-weight:600;color:#1a0505;margin-bottom:20px;
         padding-bottom:14px;border-bottom:1.5px solid #fce8e8}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.form-group{display:flex;flex-direction:column;gap:6px;margin-bottom:16px}
.form-group label{font-size:13px;font-weight:500;color:#7f1d1d}
.form-group input,.form-group select{
    padding:10px 14px;border:1px solid #f7c1c1;border-radius:10px;
    background:#fff;color:#1a0505;font-size:14px;font-family:'Inter',sans-serif;
    outline:none;transition:border-color .2s,box-shadow .2s;width:100%}
.form-group input:focus,.form-group select:focus{border-color:#b91c1c;box-shadow:0 0 0 3px rgba(185,28,28,0.1)}
.form-group input::placeholder{color:#c9a0a0}
.form-group input.err,.form-group select.err{border-color:#b91c1c}
.field-error{font-size:12px;color:#b91c1c;margin-top:2px;display:none}
.field-error.show{display:block}
.pay-options{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px}
.pay-card input{display:none}
.pay-card label{display:flex;flex-direction:column;align-items:center;gap:6px;
                padding:14px 8px;border:1.5px solid #f7c1c1;border-radius:12px;
                cursor:pointer;font-size:12px;font-weight:500;color:#9a3535;
                transition:all .15s;text-align:center}
.pay-card label:hover{border-color:#b91c1c;background:#fce8e8;color:#7f1d1d}
.pay-card input:checked + label{border-color:#b91c1c;background:#fce8e8;color:#b91c1c;font-weight:600}
.pay-icon{font-size:22px}
.alert-danger{background:#fce8e8;color:#7f1d1d;border:1px solid #f09595;
              padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:14px}
.btn-submit{width:100%;background:#b91c1c;color:#fff;border:none;border-radius:10px;
            padding:13px;font-size:15px;font-weight:600;cursor:pointer;
            font-family:'Inter',sans-serif;transition:background .15s;margin-top:4px}
.btn-submit:hover{background:#991b1b}
.note{font-size:12px;color:#9a3535;text-align:center;margin-top:14px;line-height:1.6}
.success-box{background:#fff;border:1px solid #fde8e8;border-radius:16px;padding:44px 28px;
             text-align:center;box-shadow:0 2px 12px rgba(185,28,28,0.06)}
.success-icon{font-size:60px;margin-bottom:16px}
.success-title{font-size:22px;font-weight:600;color:#1a0505;margin-bottom:10px}
.success-sub{font-size:14px;color:#9a3535;line-height:1.8;margin-bottom:28px}
.success-actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
.btn-red{display:inline-block;background:#b91c1c;color:#fff;padding:12px 28px;
         border-radius:10px;text-decoration:none;font-weight:500;font-size:14px;transition:background .15s}
.btn-red:hover{background:#991b1b}
.btn-outline{display:inline-block;background:#fff;color:#b91c1c;padding:11px 24px;
             border-radius:10px;text-decoration:none;font-weight:500;font-size:14px;
             border:1.5px solid #f7c1c1;transition:all .15s}
.btn-outline:hover{background:#fce8e8}
@media(max-width:500px){.form-row{grid-template-columns:1fr}.pay-options{grid-template-columns:1fr 1fr}.hero{padding:28px 16px}}
</style>
</head>
<body>

<nav>
  <a href="../front/interfaceevent.php" class="logo">Event <span>Management</span></a>
  <a href="../front/detailEvent.php?id=<?= $id_event ?>" class="back">← Retour à l'événement</a>
</nav>

<div class="hero">
  <h1>📝 Inscription à l'événement</h1>
  <p>Complétez le formulaire pour réserver votre place</p>
</div>

<div class="container">

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

  <div class="success-box">
    <div class="success-icon">🎉</div>
    <div class="success-title">Inscription enregistrée !</div>
    <div class="success-sub">
      Votre demande de participation à<br>
      <strong><?= htmlspecialchars($evenement->getTitre()) ?></strong><br>
      a bien été reçue. Votre statut est <strong>« en attente »</strong> —
      vous serez notifié(e) par email dès confirmation.
    </div>
    <div class="success-actions">
      <a href="../front/interfaceevent.php" class="btn-red">
        🗓️ Voir tous les événements
      </a>
      <a href="../front/detailEvent.php?id=<?= $id_event ?>" class="btn-outline">
        ↩ Retour à l'événement
      </a>
    </div>
  </div>

  <?php else: ?>

  <div class="card">
    <h2>Vos informations</h2>

    <?php foreach ($errors as $err): ?>
      <div class="alert-danger">❌ <?= htmlspecialchars($err) ?></div>
    <?php endforeach; ?>

    <form method="POST" action="" id="regForm" novalidate>

      <div class="form-row">
        <div class="form-group">
          <label>Nom *</label>
          <input type="text" name="nom" id="nom"
                 placeholder="Ben Ali"
                 value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
          <span class="field-error" id="err-nom"></span>
        </div>
        <div class="form-group">
          <label>Prénom *</label>
          <input type="text" name="prenom" id="prenom"
                 placeholder="Ahmed"
                 value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
          <span class="field-error" id="err-prenom"></span>
        </div>
      </div>

      <div class="form-group">
        <label>Adresse email *</label>
        <input type="email" name="email" id="email"
               placeholder="ahmed.benali@email.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <span class="field-error" id="err-email"></span>
      </div>

      <div class="form-group">
        <label>Nombre de places *</label>
        <input type="number" name="nombre_places_reservees" id="places"
               min="1" max="10"
               value="<?= htmlspecialchars($_POST['nombre_places_reservees'] ?? '1') ?>">
        <span class="field-error" id="err-places"></span>
      </div>

      <?php if ($isFree): ?>
        <input type="hidden" name="mode_paiement" value="gratuit">
        <div class="form-group">
          <label>Mode de paiement</label>
          <div style="padding:10px 14px;background:#f0fdf4;border:1px solid #86efac;
                      border-radius:10px;font-size:13px;color:#15803d;font-weight:500">
            ✅ Cet événement est gratuit — aucun paiement requis
          </div>
        </div>
      <?php else: ?>
        <div class="form-group">
          <label>Mode de paiement *</label>
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
        </div>
      <?php endif; ?>

      <button type="submit" class="btn-submit">🎟️ Confirmer mon inscription</button>

      <p class="note">
        Votre inscription sera marquée <strong>« en attente »</strong> jusqu'à confirmation.<br>
        <?= $isFree ? 'Aucun frais pour cet événement.' : 'Montant total calculé selon le nombre de places.' ?>
      </p>
    </form>
  </div>

  <?php endif; ?>
</div>

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