<?php
require '../../controller/EvenementController.php';
$errors = [];

$id = null;
if (isset($_GET['id']))  $id = $_GET['id'];
if (isset($_POST['id'])) $id = $_POST['id'];
if (!$id) { header('Location: listEvenements.php'); exit; }

$controller = new EvenementController();
$evenement  = $controller->getEvenementById($id);
if (!$evenement) { header('Location: listEvenements.php'); exit; }

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
    $imageName    = $evenement->getImage();

    if (strlen($titre) < 3)         $errors[] = "Le titre doit contenir au moins 3 caractères.";
    if (empty($description))        $errors[] = "La description est obligatoire.";
    if (empty($date_debut))         $errors[] = "La date de début est obligatoire.";
    if (empty($date_fin))           $errors[] = "La date de fin est obligatoire.";
    if (!empty($date_debut) && !empty($date_fin) && $date_fin <= $date_debut)
                                    $errors[] = "La date de fin doit être postérieure à la date de début.";
    if (empty($lieu))               $errors[] = "Le lieu est obligatoire.";
    if (!is_numeric($capacite_max) || (int)$capacite_max < 1)
                                    $errors[] = "La capacité maximale doit être un entier positif (≥ 1).";
    if (!is_numeric($prix) || (float)$prix < 0)
                                    $errors[] = "Le prix doit être un nombre positif.";
    if (!in_array($statut, ['actif', 'annulé', 'terminé']))
                                    $errors[] = "Veuillez choisir un statut valide.";
    if (empty($type))               $errors[] = "Le type est obligatoire.";

    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $result = $controller->uploadImage($_FILES['image'], $evenement->getImage());
        if (isset($result['error'])) {
            $errors[] = $result['error'];
        } else {
            $imageName = $result['success'];
        }
    }

    if (isset($_POST['delete_image']) && $_POST['delete_image'] === '1') {
        $uploadDir = __DIR__ . '/../../uploads/evenements/';
        if ($evenement->getImage() && file_exists($uploadDir . $evenement->getImage())) {
            unlink($uploadDir . $evenement->getImage());
        }
        $imageName = null;
    }

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
        $evenement->setImage($imageName);
        $controller->updateEvenement($evenement, $id);
        header('Location: listEvenements.php?msg=updated');
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
    try { $dt = new DateTime($dateStr); return $dt->format('Y-m-d\TH:i'); }
    catch (Exception $e) { return ''; }
}
$dd = formatDateForInput($evenement->getDateDebut());
$df = formatDateForInput($evenement->getDateFin());
$imageUrl = $evenement->getImage() ? '../../../uploads/evenements/' . $evenement->getImage() : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Modifier l'Événement #<?= $id ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'DM Sans', sans-serif;
    background: #fff;
    color: #1a1a1a;
    min-height: 100vh;
}

/* ── NAV (même style que produit) ── */
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
.section { padding: 60px 0 100px; }
.container { max-width: 100%; margin: 0; padding: 0 60px; }

h2 {
    font-size: 28px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 28px;
}

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
    padding: 10px 14px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: #fff;
    color: #1a1a1a;
    font-size: 14px;
    font-family: 'DM Sans', sans-serif;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
}
.form-control:focus,
.form-select:focus {
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}
textarea.form-control { resize: vertical; min-height: 100px; }
.form-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23555' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    padding-right: 36px;
}

/* ── IMAGE SECTION ── */
.image-preview-box {
    margin-top: 8px;
}
.image-preview-box img {
    height: 120px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e5e5e5;
    display: block;
    margin-bottom: 10px;
}
.delete-img-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #dc2626;
    cursor: pointer;
    margin-bottom: 12px;
}
.delete-img-label input {
    width: 15px;
    height: 15px;
    cursor: pointer;
    accent-color: #dc2626;
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

<!-- NAV -->
<nav>
  <a href="interfaceevent.php" class="logo">Smart Event<span>.</span></a>
  <div class="nav-links">
    <a href="listEvenements.php" class="active">Événements</a>
    <a href="listParticipations.php">Participants</a>
  </div>
  <a href="addEvenement.php" class="btn-nav">Ajouter Événement</a>
</nav>

<!-- CONTENT -->
<section class="section">
<div class="container">

  <h2>Modifier l'Événement</h2>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
  <?php endforeach; ?>

  <form method="POST" action="" enctype="multipart/form-data" class="row" id="evenementForm">
    <input type="hidden" name="id" value="<?= $id ?>">

    <!-- Titre -->
    <div class="col-md-6">
      <label class="form-label">Titre *</label>
      <input type="text" name="titre" class="form-control"
             value="<?= htmlspecialchars($evenement->getTitre()) ?>">
    </div>

    <!-- Type -->
    <div class="col-md-6">
      <label class="form-label">Type *</label>
      <input type="text" name="type" class="form-control"
             value="<?= htmlspecialchars($evenement->getType()) ?>">
    </div>

    <!-- Date début -->
    <div class="col-md-6">
      <label class="form-label">Date de début *</label>
      <input type="datetime-local" name="date_debut" class="form-control" value="<?= $dd ?>">
    </div>

    <!-- Date fin -->
    <div class="col-md-6">
      <label class="form-label">Date de fin *</label>
      <input type="datetime-local" name="date_fin" class="form-control" value="<?= $df ?>">
    </div>

    <!-- Lieu -->
    <div class="col-md-6">
      <label class="form-label">Lieu *</label>
      <input type="text" name="lieu" class="form-control"
             value="<?= htmlspecialchars($evenement->getLieu()) ?>">
    </div>

    <!-- Capacité -->
    <div class="col-md-6">
      <label class="form-label">Capacité maximale *</label>
      <input type="number" name="capacite_max" class="form-control"
             value="<?= htmlspecialchars($evenement->getCapaciteMax()) ?>">
    </div>

    <!-- Prix -->
    <div class="col-md-6">
      <label class="form-label">Prix (TND) *</label>
      <input type="number" name="prix" step="0.01" min="0" class="form-control"
             value="<?= htmlspecialchars($evenement->getPrix()) ?>">
    </div>

    <!-- Statut -->
    <div class="col-md-6">
      <label class="form-label">Statut *</label>
      <select name="statut" class="form-select">
        <option value="">-- Sélectionner --</option>
        <option value="actif"   <?= $evenement->getStatut()==='actif'   ? 'selected':'' ?>>Actif</option>
        <option value="annulé"  <?= $evenement->getStatut()==='annulé'  ? 'selected':'' ?>>Annulé</option>
        <option value="terminé" <?= $evenement->getStatut()==='terminé' ? 'selected':'' ?>>Terminé</option>
      </select>
    </div>

    <!-- Image -->
    <div class="col-md-6">
      <label class="form-label">Image</label>
      <input type="file" name="image" class="form-control" accept="image/*"
             onchange="previewImage(this, 'previewImg')">
      <div class="image-preview-box">
        <?php if ($imageUrl): ?>
          <img id="previewImg" src="<?= htmlspecialchars($imageUrl) ?>" alt="Image actuelle">
          <label class="delete-img-label">
            <input type="checkbox" name="delete_image" value="1"
                   onchange="toggleUpload(this)">
            Supprimer cette image
          </label>
        <?php else: ?>
          <img id="previewImg" src="#" alt="Aperçu" style="display:none;">
        <?php endif; ?>
      </div>
    </div>

    <!-- Description -->
    <div class="col-12">
      <label class="form-label">Description *</label>
      <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($evenement->getDescription()) ?></textarea>
    </div>

    <!-- Actions -->
    <div class="col-12 d-flex gap-2">
      <button type="submit" class="btn btn-danger">Mettre à jour</button>
      <a href="listEvenements.php" class="btn btn-outline-secondary">Annuler</a>
    </div>

  </form>
</div>
</section>

<script>
function previewImage(input, previewId) {
    var preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function toggleUpload(cb) {
    var fileInput = document.querySelector('input[name="image"]');
    if (fileInput) {
        fileInput.disabled = cb.checked;
        fileInput.style.opacity = cb.checked ? '0.4' : '1';
    }
}
</script>
</body>
</html>