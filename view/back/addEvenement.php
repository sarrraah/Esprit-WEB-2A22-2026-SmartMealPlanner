<?php
require '../../controller/EvenementController.php';
$errors = [];

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
    $imageName    = null;

    // Validations
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

    // Upload image (optionnel)
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $controller = new EvenementController();
        $result     = $controller->uploadImage($_FILES['image']);
        if (isset($result['error'])) {
            $errors[] = $result['error'];
        } else {
            $imageName = $result['success'];
        }
    }

    if (empty($errors)) {
        $controller = new EvenementController();
        $evenement  = new Evenement(
            null,
            $titre,
            $description,
            $date_debut,
            $date_fin,
            $lieu,
            (int)$capacite_max,
            (float)$prix,
            $statut,
            $type,
            $imageName
        );
        $controller->addEvenement($evenement);
        header('Location: listEvenements.php?msg=added');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ajouter un Événement – Event Management</title>
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

.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:0}
.form-group{display:flex;flex-direction:column;gap:6px;margin-bottom:18px}
.form-group label{font-size:13px;font-weight:500;color:#7f1d1d}
.form-group input,
.form-group textarea,
.form-group select{
  padding:10px 14px;border:1px solid #f7c1c1;border-radius:10px;background:#fff;
  color:#1a0505;font-size:14px;font-family:'Inter',sans-serif;outline:none;
  transition:border-color .2s,box-shadow .2s;width:100%
}
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus{border-color:#b91c1c;box-shadow:0 0 0 3px rgba(185,28,28,0.1)}
.form-group input::placeholder,
.form-group textarea::placeholder{color:#c9a0a0}
.form-group textarea{resize:vertical;min-height:90px}
.form-group select{
  appearance:none;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%239a3535' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
  background-repeat:no-repeat;background-position:right 14px center;padding-right:36px
}
.form-group input.is-invalid,
.form-group textarea.is-invalid,
.form-group select.is-invalid{border-color:#b91c1c;box-shadow:0 0 0 3px rgba(185,28,28,0.15)}
.field-error{font-size:12px;color:#b91c1c;margin-top:2px;display:none}
.field-error.visible{display:block}

/* ── Upload image ── */
.upload-area{
  border:2px dashed #f7c1c1;border-radius:12px;padding:24px;text-align:center;
  cursor:pointer;transition:all .2s;position:relative;background:#fff5f5
}
.upload-area:hover{border-color:#b91c1c;background:#fce8e8}
.upload-area input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.upload-icon{font-size:32px;margin-bottom:8px}
.upload-txt{font-size:13px;color:#9a3535;font-weight:500}
.upload-sub{font-size:12px;color:#c9a0a0;margin-top:4px}
.preview-wrap{margin-top:14px;display:none}
.preview-wrap img{max-height:180px;border-radius:10px;border:1px solid #fde8e8;object-fit:cover}
.preview-name{font-size:12px;color:#9a3535;margin-top:6px}

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
  <h1>➕ Ajouter un Événement</h1>
  <p>Remplissez les informations pour créer un nouvel événement</p>
</div>

<div class="container">
  <div class="card">
    <h2>Informations de l'événement</h2>

    <?php foreach ($errors as $err): ?>
      <div class="alert alert-danger">❌ <?= htmlspecialchars($err) ?></div>
    <?php endforeach; ?>
    <div id="js-errors"></div>

    <form method="POST" action="" id="eventForm" novalidate enctype="multipart/form-data">

      <div class="form-row">
        <div class="form-group">
          <label>Titre *</label>
          <input type="text" name="titre" id="titre"
                 placeholder="Titre de l'événement"
                 value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>">
          <span class="field-error" id="err-titre"></span>
        </div>
        <div class="form-group">
          <label>Type *</label>
          <input type="text" name="type" id="type"
                 placeholder="Conférence, Atelier, Forum..."
                 value="<?= htmlspecialchars($_POST['type'] ?? '') ?>">
          <span class="field-error" id="err-type"></span>
        </div>
      </div>

      <div class="form-group">
        <label>Description *</label>
        <textarea name="description" id="description"
                  placeholder="Décrivez l'événement..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        <span class="field-error" id="err-description"></span>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Date de début *</label>
          <input type="datetime-local" name="date_debut" id="date_debut"
                 value="<?= htmlspecialchars($_POST['date_debut'] ?? '') ?>">
          <span class="field-error" id="err-date_debut"></span>
        </div>
        <div class="form-group">
          <label>Date de fin *</label>
          <input type="datetime-local" name="date_fin" id="date_fin"
                 value="<?= htmlspecialchars($_POST['date_fin'] ?? '') ?>">
          <span class="field-error" id="err-date_fin"></span>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Lieu *</label>
          <input type="text" name="lieu" id="lieu"
                 placeholder="Ex: Tunis, Salle Horizon..."
                 value="<?= htmlspecialchars($_POST['lieu'] ?? '') ?>">
          <span class="field-error" id="err-lieu"></span>
        </div>
        <div class="form-group">
          <label>Capacité maximale *</label>
          <input type="number" name="capacite_max" id="capacite_max"
                 placeholder="Ex: 200"
                 value="<?= htmlspecialchars($_POST['capacite_max'] ?? '') ?>">
          <span class="field-error" id="err-capacite_max"></span>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Prix (TND) *</label>
          <input type="number" name="prix" id="prix"
                 placeholder="0 pour gratuit"
                 step="0.01" min="0"
                 value="<?= htmlspecialchars($_POST['prix'] ?? '') ?>">
          <span class="field-error" id="err-prix"></span>
        </div>
        <div class="form-group">
          <label>Statut *</label>
          <select name="statut" id="statut">
            <option value="">-- Choisir un statut --</option>
            <option value="actif"   <?= (($_POST['statut'] ?? '') === 'actif')   ? 'selected' : '' ?>>Actif</option>
            <option value="annulé"  <?= (($_POST['statut'] ?? '') === 'annulé')  ? 'selected' : '' ?>>Annulé</option>
            <option value="terminé" <?= (($_POST['statut'] ?? '') === 'terminé') ? 'selected' : '' ?>>Terminé</option>
          </select>
          <span class="field-error" id="err-statut"></span>
        </div>
      </div>

      <!-- IMAGE UPLOAD -->
      <div class="form-group">
        <label>Image de l'événement (optionnel)</label>
        <div class="upload-area" id="uploadArea">
          <input type="file" name="image" id="imageInput" accept="image/*" onchange="previewImage(this)">
          <div class="upload-icon">🖼️</div>
          <div class="upload-txt">Cliquez ou glissez une image ici</div>
          <div class="upload-sub">JPG, PNG, GIF, WEBP — max 5 Mo</div>
        </div>
        <div class="preview-wrap" id="previewWrap">
          <img id="previewImg" src="" alt="Aperçu">
          <div class="preview-name" id="previewName"></div>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-save">💾 Enregistrer</button>
        <a href="listEvenements.php" class="btn btn-back">← Retour</a>
      </div>
    </form>
  </div>
</div>

<script>
function previewImage(input) {
  const wrap = document.getElementById('previewWrap');
  const img  = document.getElementById('previewImg');
  const name = document.getElementById('previewName');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      img.src = e.target.result;
      name.textContent = input.files[0].name;
      wrap.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

document.getElementById('eventForm').addEventListener('submit', function(e) {
  var titre       = document.getElementById('titre').value.trim();
  var description = document.getElementById('description').value.trim();
  var date_debut  = document.getElementById('date_debut').value;
  var date_fin    = document.getElementById('date_fin').value;
  var lieu        = document.getElementById('lieu').value.trim();
  var capacite    = document.getElementById('capacite_max').value;
  var prix        = document.getElementById('prix').value;
  var statut      = document.getElementById('statut').value;
  var type        = document.getElementById('type').value.trim();

  document.querySelectorAll('.field-error').forEach(el => { el.textContent=''; el.classList.remove('visible'); });
  document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
  document.getElementById('js-errors').innerHTML = '';

  var errors = [];
  function addError(fieldId, msg) {
    errors.push(msg);
    var f = document.getElementById(fieldId);
    var s = document.getElementById('err-' + fieldId);
    if (f) f.classList.add('is-invalid');
    if (s) { s.textContent = msg; s.classList.add('visible'); }
  }

  if (titre.length < 3)         addError('titre',       'Le titre doit contenir au moins 3 caractères.');
  if (!description)             addError('description', 'La description est obligatoire.');
  if (!date_debut)              addError('date_debut',  'La date de début est obligatoire.');
  if (!date_fin)                addError('date_fin',    'La date de fin est obligatoire.');
  if (date_debut && date_fin && date_fin <= date_debut)
                                addError('date_fin',    'La date de fin doit être postérieure à la date de début.');
  if (!lieu)                    addError('lieu',        'Le lieu est obligatoire.');
  if (!capacite || isNaN(capacite) || parseInt(capacite) < 1)
                                addError('capacite_max','La capacité doit être un entier positif (≥ 1).');
  if (prix === '' || isNaN(prix) || parseFloat(prix) < 0)
                                addError('prix',        'Le prix doit être un nombre positif.');
  if (!statut)                  addError('statut',      'Veuillez choisir un statut.');
  if (!type)                    addError('type',        'Le type est obligatoire.');

  if (errors.length > 0) {
    e.preventDefault();
    var c = document.getElementById('js-errors');
    c.innerHTML = errors.map(err => '<div class="alert alert-danger">❌ ' + err + '</div>').join('');
    c.scrollIntoView({ behavior:'smooth', block:'start' });
  }
});
</script>
</body>
</html>