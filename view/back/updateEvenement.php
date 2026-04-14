<?php
require '../../controller/EvenementController.php';
$errors = [];

$id = null;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
} elseif (isset($_POST['id'])) {
    $id = $_POST['id'];
}

if (!$id) {
    header('Location: listEvenements.php');
    exit;
}

$controller = new EvenementController();
$evenement  = $controller->getEvenementById($id);

if (!$evenement) {
    header('Location: listEvenements.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre        = trim($_POST['titre']);
    $description  = trim($_POST['description']);
    $date_debut   = $_POST['date_debut'];
    $date_fin     = $_POST['date_fin'];
    $lieu         = trim($_POST['lieu']);
    $capacite_max = $_POST['capacite_max'];
    $prix         = $_POST['prix'];
    $statut       = $_POST['statut'];
    $type         = trim($_POST['type']);

    if (strlen($titre) < 3)
        $errors[] = "Le titre doit contenir au moins 3 caractères.";
    if (empty($description))
        $errors[] = "La description est obligatoire.";
    if (empty($date_debut))
        $errors[] = "La date de début est obligatoire.";
    if (empty($date_fin))
        $errors[] = "La date de fin est obligatoire.";
    if (!empty($date_debut) && !empty($date_fin) && $date_fin <= $date_debut)
        $errors[] = "La date de fin doit être postérieure à la date de début.";
    if (empty($lieu))
        $errors[] = "Le lieu est obligatoire.";
    if (!is_numeric($capacite_max) || (int)$capacite_max < 1)
        $errors[] = "La capacité maximale doit être un entier positif (≥ 1).";
    if (!is_numeric($prix) || (float)$prix < 0)
        $errors[] = "Le prix doit être un nombre positif.";
    if (!in_array($statut, ['actif', 'annulé', 'terminé']))
        $errors[] = "Veuillez choisir un statut valide.";
    if (empty($type))
        $errors[] = "Le type est obligatoire.";

    if (empty($errors)) {
        $evenement->setTitre($titre);
        $evenement->setDescription($description);
        $evenement->setDateDebut($date_debut);
        $evenement->setDateFin($date_fin);
        $evenement->setLieu($lieu);
        $evenement->setCapaciteMax((int)$capacite_max);
        $evenement->setPrix((float)$prix);
        $evenement->setStatut($statut);
        $evenement->setType($type);

        $controller->updateEvenement($evenement, $id);
        header('Location: listEvenements.php');
        exit;
    }

    $evenement->setTitre($titre);
    $evenement->setDescription($description);
    $evenement->setDateDebut($date_debut);
    $evenement->setDateFin($date_fin);
    $evenement->setLieu($lieu);
    $evenement->setCapaciteMax($capacite_max);
    $evenement->setPrix($prix);
    $evenement->setStatut($statut);
    $evenement->setType($type);
}

function formatDateForInput($dateStr) {
    if (empty($dateStr)) return '';
    try {
        $dt = new DateTime($dateStr);
        return $dt->format('Y-m-d\TH:i');
    } catch (Exception $e) {
        return '';
    }
}

$dd = formatDateForInput($evenement->getDateDebut());
$df = formatDateForInput($evenement->getDateFin());
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Modifier l'Événement #<?= $id ?> – Event Management</title>
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
.form-group textarea,
.form-group select{
  padding:10px 14px;
  border:1px solid #f7c1c1;
  border-radius:10px;
  background:#fff;
  color:#1a0505;
  font-size:14px;
  font-family:'Inter',sans-serif;
  outline:none;
  transition:border-color .2s, box-shadow .2s;
  width:100%
}
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus{
  border-color:#b91c1c;
  box-shadow:0 0 0 3px rgba(185,28,28,0.1)
}
.form-group input::placeholder,
.form-group textarea::placeholder{color:#c9a0a0}
.form-group textarea{resize:vertical;min-height:90px}
.form-group select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%239a3535' d='M6 8L1 3h10z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 14px center;padding-right:36px}

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
    <a href="interfaceevent.php">Events</a>
    <a href="listEvenements.php">Admin</a>
  </div>
</nav>

<div class="page-hero">
  <h1>✏️ Modifier l'Événement #<?= $id ?></h1>
  <p>Modifiez les informations de l'événement ci-dessous</p>
</div>

<div class="container">
  <div class="card">
    <h2>Informations de l'événement</h2>

    <?php if (!empty($errors)): ?>
      <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger">❌ <?= htmlspecialchars($err) ?></div>
      <?php endforeach; ?>
    <?php endif; ?>

    <form method="POST" action="">
      <input type="hidden" name="id" value="<?= $id ?>">

      <div class="form-row">
        <div class="form-group">
          <label>Titre *</label>
          <input type="text" name="titre" id="titre"
                 value="<?= htmlspecialchars($evenement->getTitre()) ?>">
        </div>
        <div class="form-group">
          <label>Type *</label>
          <input type="text" name="type" id="type"
                 value="<?= htmlspecialchars($evenement->getType()) ?>">
        </div>
      </div>

      <div class="form-group">
        <label>Description *</label>
        <textarea name="description" id="description"><?= htmlspecialchars($evenement->getDescription()) ?></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Date de Début *</label>
          <input type="text" name="date_debut" id="date_debut"
                 placeholder="YYYY-MM-DDTHH:MM"
                 value="<?= $dd ?>">
        </div>
        <div class="form-group">
          <label>Date de Fin *</label>
          <input type="text" name="date_fin" id="date_fin"
                 placeholder="YYYY-MM-DDTHH:MM"
                 value="<?= $df ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Lieu *</label>
          <input type="text" name="lieu" id="lieu"
                 value="<?= htmlspecialchars($evenement->getLieu()) ?>">
        </div>
        <div class="form-group">
          <label>Capacité Maximale *</label>
          <input type="text" name="capacite_max" id="capacite_max"
                 value="<?= htmlspecialchars($evenement->getCapaciteMax()) ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Prix (TND) *</label>
          <input type="text" name="prix" id="prix"
                 value="<?= htmlspecialchars($evenement->getPrix()) ?>">
        </div>
        <div class="form-group">
          <label>Statut *</label>
          <select name="statut" id="statut">
            <option value="">-- Choisir --</option>
            <option value="actif"   <?= $evenement->getStatut() === 'actif'   ? 'selected' : '' ?>>Actif</option>
            <option value="annulé"  <?= $evenement->getStatut() === 'annulé'  ? 'selected' : '' ?>>Annulé</option>
            <option value="terminé" <?= $evenement->getStatut() === 'terminé' ? 'selected' : '' ?>>Terminé</option>
          </select>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-save">💾 Enregistrer les modifications</button>
        <a href="listEvenements.php" class="btn btn-back">← Retour</a>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');

    form.addEventListener('submit', function (e) {
        const errors = [];

        const titre       = document.getElementById('titre').value.trim();
        const description = document.getElementById('description').value.trim();
        const date_debut  = document.getElementById('date_debut').value;
        const date_fin    = document.getElementById('date_fin').value;
        const lieu        = document.getElementById('lieu').value.trim();
        const capacite    = document.getElementById('capacite_max').value;
        const prix        = document.getElementById('prix').value;
        const statut      = document.getElementById('statut').value;
        const type        = document.getElementById('type').value.trim();

        if (titre.length < 3)
            errors.push("Le titre doit contenir au moins 3 caractères.");

        if (description === '')
            errors.push("La description est obligatoire.");

        if (date_debut === '')
            errors.push("La date de début est obligatoire.");

        if (date_fin === '')
            errors.push("La date de fin est obligatoire.");

        if (date_debut !== '' && date_fin !== '' && date_fin <= date_debut)
            errors.push("La date de fin doit être postérieure à la date de début.");

        if (lieu === '')
            errors.push("Le lieu est obligatoire.");

        if (capacite === '' || isNaN(capacite) || !Number.isInteger(Number(capacite)) || parseInt(capacite) < 1)
            errors.push("La capacité maximale doit être un entier positif (≥ 1).");

        if (prix === '' || isNaN(prix) || parseFloat(prix) < 0)
            errors.push("Le prix doit être un nombre positif.");

        const statutsValides = ['actif', 'annulé', 'terminé'];
        if (!statutsValides.includes(statut))
            errors.push("Veuillez choisir un statut valide.");

        if (type === '')
            errors.push("Le type est obligatoire.");

        let errorDiv = document.getElementById('js-errors');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.id = 'js-errors';
            errorDiv.style.cssText = 'background:#fce8e8;border:1px solid #f09595;padding:12px 16px;margin-bottom:15px;border-radius:10px;color:#7f1d1d;font-size:14px;';
            form.prepend(errorDiv);
        }

        if (errors.length > 0) {
            e.preventDefault();
            errorDiv.innerHTML = '<strong>Erreurs :</strong><ul style="margin:8px 0 0 16px">'
                + errors.map(err => `<li>${err}</li>`).join('')
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