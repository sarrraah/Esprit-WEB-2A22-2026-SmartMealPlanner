<?php
require '../../controller/ParticipationController.php';
require '../../controller/EvenementController.php';

$errors    = [];
$evCtrl    = new EvenementController();
$allEvents = $evCtrl->listEvenements();

$id = null;
if (isset($_GET['id']))  $id = $_GET['id'];
if (isset($_POST['id'])) $id = $_POST['id'];

if (!$id) { header('Location: listParticipations.php'); exit; }

$ctrl          = new ParticipationController();
$participation = $ctrl->getParticipationById($id);

if (!$participation) { header('Location: listParticipations.php'); exit; }

$eventMap = [];
foreach ($allEvents as $ev) {
    $eventMap[$ev->getIdEvent()] = $ev;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_event           = $_POST['id_event']           ?? '';
    $nom                = trim($_POST['nom']            ?? '');
    $prenom             = trim($_POST['prenom']         ?? '');
    $email              = trim($_POST['email']          ?? '');
    $places             = (int)($_POST['nombre_places_reservees'] ?? 1);
    $mode_paiement      = $_POST['mode_paiement']       ?? '';
    $statut             = $_POST['statut']              ?? '';
    $date_participation = $_POST['date_participation']  ?? '';

    if (empty($id_event) || !is_numeric($id_event))
        $errors[] = "Veuillez sélectionner un événement.";
    if (strlen($nom) < 2)
        $errors[] = "Le nom doit contenir au moins 2 caractères.";
    if (strlen($prenom) < 2)
        $errors[] = "Le prénom doit contenir au moins 2 caractères.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = "L'adresse email est invalide.";
    if ($places < 1 || $places > 10)
        $errors[] = "Le nombre de places doit être entre 1 et 10.";
    if (!in_array($mode_paiement, ['espèces', 'carte', 'virement', 'gratuit']))
        $errors[] = "Veuillez choisir un mode de paiement valide.";
    if (!in_array($statut, ['confirmé', 'en attente', 'annulé', 'en_attente']))
        $errors[] = "Veuillez choisir un statut valide.";
    if (empty($date_participation))
        $errors[] = "La date de participation est obligatoire.";

    if (empty($errors)) {
        $updated = new Participation(
            $id,
            (int)$id_event,
            $nom,
            $prenom,
            $email,
            $places,
            $mode_paiement,
            $statut,
            $date_participation
        );
        $ctrl->updateParticipation($updated, $id);
        header('Location: listParticipations.php?msg=updated');
        exit;
    }
}

function formatDateTimeLocal($dateStr) {
    if (empty($dateStr)) return '';
    try { $dt = new DateTime($dateStr); return $dt->format('Y-m-d\TH:i'); }
    catch (Exception $e) { return ''; }
}

$dp = formatDateTimeLocal($participation->getDateParticipation());

$evObj   = $eventMap[$participation->getIdEvent()] ?? null;
$prix    = $evObj ? (float)$evObj->getPrix() : 0;
$montant = $prix * $participation->getNombrePlacesReservees();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Modifier la Participation #<?= $id ?></title>
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
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 60px;
    position: sticky;
    top: 0;
    z-index: 100;
}
.logo {
    font-size: 20px;
    font-weight: 700;
    color: #1a1a1a;
    text-decoration: none;
}
.logo span { color: #dc2626; }
.nav-links { display: flex; align-items: center; gap: 32px; }
.nav-links a {
    font-size: 14px;
    color: #555;
    text-decoration: none;
    font-weight: 500;
    padding-bottom: 2px;
    border-bottom: 2px solid transparent;
    transition: color .2s, border-color .2s;
}
.nav-links a:hover,
.nav-links a.active { color: #1a1a1a; border-bottom-color: #dc2626; }
.btn-nav {
    background: #dc2626;
    color: #fff;
    border: none;
    padding: 9px 20px;
    border-radius: 999px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    font-family: inherit;
}

/* ── PAGE LAYOUT ── */
.section { padding: 16px 0 24px; }
.container { max-width: 100%; margin: 0; padding: 0 20px; }

h2 {
    font-size: 28px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 28px;
}

/* ── MONTANT INFO ── */
.montant-info {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    padding: 13px 16px;
    font-size: 15px;
    color: #991b1b;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.montant-info strong { color: #dc2626; font-size: 17px; }

/* ── ALERTS ── */
.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 10px;
    font-size: 14px;
}
.alert-danger {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

/* ── FORM GRID ── */
.row { display: flex; flex-wrap: wrap; gap: 20px; }
.col-md-6 { flex: 1 1 calc(50% - 10px); min-width: 260px; }
.col-12 { flex: 0 0 100%; }

/* ── FORM CONTROLS ── */
.form-label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #333;
    margin-bottom: 6px;
}
.form-control,
.form-select {
    width: 100%;
    padding: 13px 16px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: #fff;
    color: #1a1a1a;
    font-size: 15px;
    font-family: 'DM Sans', sans-serif;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
}
.form-control:focus,
.form-select:focus {
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}
.form-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23555' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    padding-right: 36px;
}

/* ── BUTTONS ── */
.d-flex { display: flex; }
.gap-2 { gap: 10px; }
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 22px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    border: none;
    text-decoration: none;
    transition: all .15s;
}
.btn-danger { background: #dc2626; color: #fff; }
.btn-danger:hover { background: #b91c1c; }
.btn-outline-secondary {
    background: #fff;
    color: #555;
    border: 1px solid #d1d5db;
}
.btn-outline-secondary:hover { background: #f9fafb; }

/* ── RESPONSIVE ── */
@media (max-width: 600px) {
    nav { padding: 0 16px; }
    .col-md-6 { flex: 0 0 100%; }
}
</style>
</head>
<body>

<nav>
  <a href="../front/interfaceevent.php" class="logo">Smart Event<span>.</span></a>
  <div class="nav-links">
    <a href="listEvenements.php">Événements</a>
    <a href="listParticipations.php" class="active">Participants</a>
  </div>
  <a href="addParticipation.php" class="btn-nav">Ajouter Participation</a>
</nav>

<section class="section">
<div class="container">

  <h2>Modifier la Participation</h2>

  <!-- Montant calculé -->
  <div class="montant-info">
    💰 Montant total calculé :
    <strong>
      <?= $montant == 0 ? 'Gratuit' : number_format($montant, 2) . ' TND' ?>
    </strong>
    &nbsp;(<?= $participation->getNombrePlacesReservees() ?> place(s) × <?= number_format($prix, 2) ?> TND)
  </div>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
  <?php endforeach; ?>

  <form method="POST" action="" id="partForm" class="row">
    <input type="hidden" name="id" value="<?= $id ?>">

    <!-- Événement (pleine largeur) -->
    <div class="col-12">
      <label class="form-label">Événement *</label>
      <select name="id_event" id="id_event" class="form-select">
        <option value="">-- Choisir un événement --</option>
        <?php foreach ($allEvents as $ev): ?>
          <option value="<?= $ev->getIdEvent() ?>"
            <?= $participation->getIdEvent() == $ev->getIdEvent() ? 'selected' : '' ?>>
            <?= htmlspecialchars($ev->getTitre()) ?> (<?= htmlspecialchars($ev->getLieu()) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Nom -->
    <div class="col-md-6">
      <label class="form-label">Nom *</label>
      <input type="text" name="nom" id="nom" class="form-control"
             placeholder="Ben Ali"
             value="<?= htmlspecialchars($_POST['nom'] ?? $participation->getNom()) ?>">
    </div>

    <!-- Prénom -->
    <div class="col-md-6">
      <label class="form-label">Prénom *</label>
      <input type="text" name="prenom" id="prenom" class="form-control"
             placeholder="Ahmed"
             value="<?= htmlspecialchars($_POST['prenom'] ?? $participation->getPrenom()) ?>">
    </div>

    <!-- Email -->
    <div class="col-md-6">
      <label class="form-label">Email *</label>
      <input type="email" name="email" id="email" class="form-control"
             placeholder="ahmed@email.com"
             value="<?= htmlspecialchars($_POST['email'] ?? $participation->getEmail()) ?>">
    </div>

    <!-- Nombre de places -->
    <div class="col-md-6">
      <label class="form-label">Nombre de places *</label>
      <input type="number" name="nombre_places_reservees" id="places" class="form-control"
             min="1" max="10"
             value="<?= htmlspecialchars($_POST['nombre_places_reservees'] ?? $participation->getNombrePlacesReservees()) ?>">
    </div>

    <!-- Mode de paiement -->
    <div class="col-md-6">
      <label class="form-label">Mode de paiement *</label>
      <select name="mode_paiement" id="mode_paiement" class="form-select">
        <option value="">-- Choisir --</option>
        <option value="gratuit"  <?= ($participation->getModePaiement() === 'gratuit')  ? 'selected' : '' ?>>Gratuit</option>
        <option value="espèces"  <?= ($participation->getModePaiement() === 'espèces')  ? 'selected' : '' ?>>Espèces 💵</option>
        <option value="carte"    <?= ($participation->getModePaiement() === 'carte')    ? 'selected' : '' ?>>Carte 💳</option>
        <option value="virement" <?= ($participation->getModePaiement() === 'virement') ? 'selected' : '' ?>>Virement 🏦</option>
      </select>
    </div>

    <!-- Statut -->
    <div class="col-md-6">
      <label class="form-label">Statut *</label>
      <select name="statut" id="statut" class="form-select">
        <option value="">-- Choisir --</option>
        <option value="confirmé"   <?= $participation->getStatut() === 'confirmé'   ? 'selected' : '' ?>>✅ Confirmé</option>
        <option value="en attente" <?= ($participation->getStatut() === 'en attente' || $participation->getStatut() === 'en_attente') ? 'selected' : '' ?>>⏳ En attente</option>
        <option value="annulé"     <?= $participation->getStatut() === 'annulé'     ? 'selected' : '' ?>>❌ Annulé</option>
      </select>
    </div>

    <!-- Date de participation -->
    <div class="col-12">
      <label class="form-label">Date de participation *</label>
      <input type="datetime-local" name="date_participation" id="date_participation"
             class="form-control" value="<?= $dp ?>">
    </div>

    <!-- Actions -->
    <div class="col-12 d-flex gap-2">
      <button type="submit" class="btn btn-danger">Mettre à jour</button>
      <a href="listParticipations.php" class="btn btn-outline-secondary">Annuler</a>
    </div>

  </form>
</div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  document.getElementById('partForm').addEventListener('submit', function (e) {
    var errors = [];

    var id_event = document.getElementById('id_event').value;
    var nom      = document.getElementById('nom').value.trim();
    var prenom   = document.getElementById('prenom').value.trim();
    var email    = document.getElementById('email').value.trim();
    var places   = parseInt(document.getElementById('places').value);
    var mode     = document.getElementById('mode_paiement').value;
    var statut   = document.getElementById('statut').value;
    var date     = document.getElementById('date_participation').value;

    if (!id_event)                                          errors.push("Veuillez sélectionner un événement.");
    if (nom.length < 2)                                     errors.push("Le nom doit contenir au moins 2 caractères.");
    if (prenom.length < 2)                                  errors.push("Le prénom doit contenir au moins 2 caractères.");
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email))         errors.push("Email invalide.");
    if (isNaN(places) || places < 1 || places > 10)        errors.push("Le nombre de places doit être entre 1 et 10.");
    if (!mode)                                              errors.push("Veuillez choisir un mode de paiement.");
    if (!statut)                                            errors.push("Veuillez choisir un statut.");
    if (!date)                                              errors.push("La date de participation est obligatoire.");

    var errorDiv = document.getElementById('js-errors');
    if (!errorDiv) {
      errorDiv = document.createElement('div');
      errorDiv.id = 'js-errors';
      errorDiv.className = 'alert alert-danger';
      document.getElementById('partForm').prepend(errorDiv);
    }

    if (errors.length > 0) {
      e.preventDefault();
      errorDiv.innerHTML = '<strong>Erreurs :</strong><ul style="margin:8px 0 0 16px">'
        + errors.map(function(err){ return '<li>' + err + '</li>'; }).join('')
        + '</ul>';
      errorDiv.style.display = 'block';
      window.scrollTo(0, 0);
    } else {
      errorDiv.style.display = 'none';
    }
  });
});
</script>

</body>
</html>