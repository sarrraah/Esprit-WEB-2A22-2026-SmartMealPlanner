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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_event          = $_POST['id_event'];
    $nom_participant   = trim($_POST['nom_participant']);
    $statut            = $_POST['statut'];
    $montant           = $_POST['montant'];
    $date_participation= $_POST['date_participation'];

    if (empty($id_event) || !is_numeric($id_event))
        $errors[] = "Veuillez sélectionner un événement.";
    if (strlen($nom_participant) < 2)
        $errors[] = "Le nom du participant doit contenir au moins 2 caractères.";
    if (!in_array($statut, ['confirmé', 'en attente', 'annulé']))
        $errors[] = "Veuillez choisir un statut valide.";
    if (!is_numeric($montant) || (float)$montant < 0)
        $errors[] = "Le montant doit être un nombre positif (0 pour gratuit).";
    if (empty($date_participation))
        $errors[] = "La date de participation est obligatoire.";

    if (empty($errors)) {
        $participation->setIdEvent((int)$id_event);
        $participation->setNomParticipant($nom_participant);
        $participation->setStatut($statut);
        $participation->setMontant((float)$montant);
        $participation->setDateParticipation($date_participation);
        $ctrl->updateParticipation($participation, $id);
        header('Location: listParticipations.php?msg=updated');
        exit;
    }

    // Keep user input on error
    $participation->setIdEvent($id_event);
    $participation->setNomParticipant($nom_participant);
    $participation->setStatut($statut);
    $participation->setMontant($montant);
    $participation->setDateParticipation($date_participation);
}

function formatDateTimeLocal($dateStr) {
    if (empty($dateStr)) return '';
    try { $dt = new DateTime($dateStr); return $dt->format('Y-m-d\TH:i'); }
    catch (Exception $e) { return ''; }
}

$dp = formatDateTimeLocal($participation->getDateParticipation());
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Modifier la Participation #<?= $id ?> – Event Management</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#fff5f5;color:#1a0505;min-height:100vh}

nav{background:#fff;border-bottom:1.5px solid #f7c1c1;padding:0 32px;display:flex;align-items:center;justify-content:space-between;height:60px;position:sticky;top:0;z-index:100}
.logo{font-size:18px;font-weight:600;color:#1a0505;text-decoration:none}
.logo span{color:#b91c1c}
.nav-links{display:flex;gap:28px}
.nav-links a{font-size:14px;color:#9a3535;text-decoration:none;font-weight:500;transition:color .2s}
.nav-links a:hover{color:#b91c1c}

.page-hero{background:linear-gradient(135deg,#7f1d1d 0%,#b91c1c 100%);padding:36px 32px;text-align:center;color:#fff}
.page-hero h1{font-size:24px;font-weight:600;margin-bottom:6px}
.page-hero p{font-size:14px;color:rgba(255,255,255,0.6)}

.container{max-width:780px;margin:0 auto;padding:32px 24px 60px}
.card{background:#fff;border:1px solid #fde8e8;border-radius:16px;padding:32px;box-shadow:0 2px 12px rgba(185,28,28,0.06)}
.card h2{font-size:18px;font-weight:600;color:#1a0505;margin-bottom:24px;padding-bottom:16px;border-bottom:1.5px solid #fce8e8}

.alert{padding:12px 16px;border-radius:10px;margin-bottom:12px;font-size:14px}
.alert-danger{background:#fce8e8;color:#7f1d1d;border:1px solid #f09595}

.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{display:flex;flex-direction:column;gap:6px;margin-bottom:18px}
.form-group label{font-size:13px;font-weight:500;color:#7f1d1d}
.form-group input,
.form-group select{
  padding:10px 14px;
  border:1px solid #f7c1c1;
  border-radius:10px;
  background:#fff;
  color:#1a0505;
  font-size:14px;
  font-family:'Inter',sans-serif;
  outline:none;
  transition:border-color .2s,box-shadow .2s;
  width:100%
}
.form-group input:focus,
.form-group select:focus{
  border-color:#b91c1c;
  box-shadow:0 0 0 3px rgba(185,28,28,0.1)
}
.form-group select{
  appearance:none;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%239a3535' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
  background-repeat:no-repeat;
  background-position:right 14px center;
  padding-right:36px
}
.form-actions{display:flex;gap:12px;margin-top:8px;flex-wrap:wrap}
.btn{display:inline-flex;align-items:center;gap:6px;padding:10px 24px;border-radius:10px;font-size:14px;font-weight:500;font-family:'Inter',sans-serif;cursor:pointer;border:none;text-decoration:none;transition:all .15s}
.btn-save{background:#b91c1c;color:#fff}
.btn-save:hover{background:#991b1b}
.btn-back{background:#fff;color:#9a3535;border:1px solid #f7c1c1}
.btn-back:hover{background:#fce8e8;border-color:#f09595;color:#7f1d1d}

@media(max-width:600px){
  .form-row{grid-template-columns:1fr}
  .card{padding:20px}
  nav{padding:0 16px}
  .page-hero{padding:24px 16px}
}
</style>
</head>
<body>

<nav>
  <a href="interfaceevent.php" class="logo">Event <span>Management</span></a>
  <div class="nav-links">
    <a href="listEvenements.php">Événements</a>
    <a href="listParticipations.php">Participants</a>
  </div>
</nav>

<div class="page-hero">
  <h1>✏️ Modifier la Participation #<?= $id ?></h1>
  <p>Modifiez les informations de la participation</p>
</div>

<div class="container">
  <div class="card">
    <h2>Informations de la participation</h2>

    <?php foreach ($errors as $err): ?>
      <div class="alert alert-danger">❌ <?= htmlspecialchars($err) ?></div>
    <?php endforeach; ?>

    <form method="POST" action="" id="partForm">
      <input type="hidden" name="id" value="<?= $id ?>">

      <div class="form-group">
        <label>Événement *</label>
        <select name="id_event" id="id_event">
          <option value="">-- Choisir un événement --</option>
          <?php foreach ($allEvents as $ev): ?>
            <option value="<?= $ev->getIdEvent() ?>"
              <?= $participation->getIdEvent() == $ev->getIdEvent() ? 'selected' : '' ?>>
              <?= htmlspecialchars($ev->getTitre()) ?> (<?= htmlspecialchars($ev->getLieu()) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Nom du participant *</label>
          <input type="text" name="nom_participant" id="nom_participant"
                 value="<?= htmlspecialchars($participation->getNomParticipant()) ?>">
        </div>
        <div class="form-group">
          <label>Statut *</label>
          <select name="statut" id="statut">
            <option value="">-- Choisir --</option>
            <option value="confirmé"   <?= $participation->getStatut() === 'confirmé'   ? 'selected' : '' ?>>Confirmé</option>
            <option value="en attente" <?= $participation->getStatut() === 'en attente' ? 'selected' : '' ?>>En attente</option>
            <option value="annulé"     <?= $participation->getStatut() === 'annulé'     ? 'selected' : '' ?>>Annulé</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Montant (TND) *</label>
          <input type="number" name="montant" id="montant"
                 step="0.01" min="0"
                 value="<?= htmlspecialchars($participation->getMontant()) ?>">
        </div>
        <div class="form-group">
          <label>Date de participation *</label>
          <input type="datetime-local" name="date_participation" id="date_participation"
                 value="<?= $dp ?>">
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-save">💾 Enregistrer les modifications</button>
        <a href="listParticipations.php" class="btn btn-back">← Retour</a>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  document.getElementById('partForm').addEventListener('submit', function (e) {
    var errors = [];

    var id_event          = document.getElementById('id_event').value;
    var nom_participant   = document.getElementById('nom_participant').value.trim();
    var statut            = document.getElementById('statut').value;
    var montant           = document.getElementById('montant').value;
    var date_participation= document.getElementById('date_participation').value;

    if (!id_event)
      errors.push("Veuillez sélectionner un événement.");
    if (nom_participant.length < 2)
      errors.push("Le nom doit contenir au moins 2 caractères.");
    if (!statut)
      errors.push("Veuillez choisir un statut.");
    if (montant === '' || isNaN(montant) || parseFloat(montant) < 0)
      errors.push("Le montant doit être un nombre positif (0 pour gratuit).");
    if (!date_participation)
      errors.push("La date de participation est obligatoire.");

    var errorDiv = document.getElementById('js-errors');
    if (!errorDiv) {
      errorDiv = document.createElement('div');
      errorDiv.id = 'js-errors';
      errorDiv.style.cssText='background:#fce8e8;border:1px solid #f09595;padding:12px 16px;margin-bottom:15px;border-radius:10px;color:#7f1d1d;font-size:14px;';
      document.getElementById('partForm').prepend(errorDiv);
    }

    if (errors.length > 0) {
      e.preventDefault();
      errorDiv.innerHTML = '<strong>Erreurs :</strong><ul style="margin:8px 0 0 16px">'
        + errors.map(function(err){ return '<li>'+err+'</li>'; }).join('')
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