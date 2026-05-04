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
            null, $titre, $description, $date_debut, $date_fin,
            $lieu, (int)$capacite_max, (float)$prix, $statut, $type, $imageName
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
<title>Ajouter un Événement</title>
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
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 60px;
    position: sticky;
    top: 0;
    z-index: 100;
}
.logo { font-size: 20px; font-weight: 700; color: var(--text); text-decoration: none; }
.logo span { color: var(--accent); }
.nav-links { display: flex; align-items: center; gap: 32px; }
.nav-links a {
    font-size: 14px; color: var(--muted); text-decoration: none; font-weight: 500;
    padding-bottom: 2px; border-bottom: 2px solid transparent;
    transition: color .2s, border-color .2s;
}
.nav-links a:hover, .nav-links a.active { color: var(--text); border-bottom-color: var(--accent); }
.btn-nav {
    background: var(--accent); color: #fff; border: none; padding: 9px 20px;
    border-radius: 999px; font-size: 14px; font-weight: 600; cursor: pointer;
    text-decoration: none; font-family: inherit;
}

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

h2 { font-size: 28px; font-weight: 700; color: #1a1a1a; margin-bottom: 28px; }

/* ── ALERTS ── */
.alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 10px; font-size: 14px; }
.alert-danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

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
textarea.form-control { resize: vertical; min-height: 100px; }
.form-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23555' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px;
}
.form-control.is-invalid, .form-select.is-invalid {
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220,38,38,0.1);
}
.field-error { font-size: 12px; color: #dc2626; margin-top: 4px; display: none; }
.field-error.visible { display: block; }

/* ── IMAGE UPLOAD ── */
.upload-area {
    border: 2px dashed #d1d5db; border-radius: 8px; padding: 28px;
    text-align: center; cursor: pointer; transition: all .2s;
    position: relative; background: #f9fafb;
}
.upload-area:hover { border-color: #dc2626; background: #fef2f2; }
.upload-area input[type=file] {
    position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
}
.upload-icon { font-size: 30px; margin-bottom: 8px; }
.upload-txt { font-size: 14px; color: #555; font-weight: 500; }
.upload-sub { font-size: 12px; color: #aaa; margin-top: 4px; }
.preview-wrap { margin-top: 14px; display: none; }
.preview-wrap img { max-height: 180px; border-radius: 8px; border: 1px solid #e5e5e5; object-fit: cover; }
.preview-name { font-size: 12px; color: #888; margin-top: 6px; }

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
.btn-outline-secondary { background: #fff; color: #555; border: 1px solid #d1d5db; }
.btn-outline-secondary:hover { background: #f9fafb; }

@media (max-width: 600px) {
    nav { padding: 0 16px; }
    .col-md-6 { flex: 0 0 100%; }
}
</style>
</head>
<body>

<nav>
  <a href="listEvenements.php" class="logo">Smart Meal Planner<span>.</span></a>
  <div class="nav-links">
    <a href="listEvenements.php" class="active">Événements</a>
    <a href="listParticipations.php">Participants</a>
  </div>
  <div class="nav-right">
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
    <a href="listEvenements.php" class="btn-nav">← Retour à la liste</a>
  </div>
</nav>

<section class="section">
<div class="container">

  <h2>Ajouter un Événement</h2>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
  <?php endforeach; ?>
  <div id="js-errors"></div>

  <form method="POST" action="" id="eventForm" novalidate enctype="multipart/form-data" class="row">

    <!-- Titre -->
    <div class="col-md-6">
      <label class="form-label">Titre *</label>
      <input type="text" name="titre" id="titre" class="form-control"
             placeholder="Titre de l'événement"
             value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>">
      <span class="field-error" id="err-titre"></span>
    </div>

    <!-- Type -->
    <div class="col-md-6">
      <label class="form-label">Type *</label>
      <input type="text" name="type" id="type" class="form-control"
             placeholder="Conférence, Atelier, Forum..."
             value="<?= htmlspecialchars($_POST['type'] ?? '') ?>">
      <span class="field-error" id="err-type"></span>
    </div>

    <!-- Date début -->
    <div class="col-md-6">
      <label class="form-label">Date de début *</label>
      <input type="datetime-local" name="date_debut" id="date_debut" class="form-control"
             value="<?= htmlspecialchars($_POST['date_debut'] ?? '') ?>">
      <span class="field-error" id="err-date_debut"></span>
    </div>

    <!-- Date fin -->
    <div class="col-md-6">
      <label class="form-label">Date de fin *</label>
      <input type="datetime-local" name="date_fin" id="date_fin" class="form-control"
             value="<?= htmlspecialchars($_POST['date_fin'] ?? '') ?>">
      <span class="field-error" id="err-date_fin"></span>
    </div>

    <!-- Lieu -->
    <div class="col-md-6">
      <label class="form-label">Lieu *</label>
      <input type="text" name="lieu" id="lieu" class="form-control"
             placeholder="Ex: Tunis, Salle Horizon..."
             value="<?= htmlspecialchars($_POST['lieu'] ?? '') ?>">
      <span class="field-error" id="err-lieu"></span>
    </div>

    <!-- Capacité -->
    <div class="col-md-6">
      <label class="form-label">Capacité maximale *</label>
      <input type="number" name="capacite_max" id="capacite_max" class="form-control"
             placeholder="Ex: 200"
             value="<?= htmlspecialchars($_POST['capacite_max'] ?? '') ?>">
      <span class="field-error" id="err-capacite_max"></span>
    </div>

    <!-- Prix -->
    <div class="col-md-6">
      <label class="form-label">Prix (TND) *</label>
      <input type="number" name="prix" id="prix" class="form-control"
             placeholder="0 pour gratuit" step="0.01" min="0"
             value="<?= htmlspecialchars($_POST['prix'] ?? '') ?>">
      <span class="field-error" id="err-prix"></span>
    </div>

    <!-- Statut -->
    <div class="col-md-6">
      <label class="form-label">Statut *</label>
      <select name="statut" id="statut" class="form-select">
        <option value="">-- Choisir un statut --</option>
        <option value="actif"   <?= (($_POST['statut'] ?? '') === 'actif')   ? 'selected' : '' ?>>Actif</option>
        <option value="annulé"  <?= (($_POST['statut'] ?? '') === 'annulé')  ? 'selected' : '' ?>>Annulé</option>
        <option value="terminé" <?= (($_POST['statut'] ?? '') === 'terminé') ? 'selected' : '' ?>>Terminé</option>
      </select>
      <span class="field-error" id="err-statut"></span>
    </div>

    <!-- Description -->
    <div class="col-12">
      <label class="form-label">Description *</label>
      <textarea name="description" id="description" class="form-control"
                placeholder="Décrivez l'événement..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
      <span class="field-error" id="err-description"></span>
    </div>

    <!-- Image -->
    <div class="col-12">
      <label class="form-label">Image de l'événement (optionnel)</label>
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

    <!-- Actions -->
    <div class="col-12 d-flex gap-2">
      <button type="submit" class="btn btn-danger">Enregistrer</button>
      <a href="listEvenements.php" class="btn btn-outline-secondary">Annuler</a>
    </div>

  </form>
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

  if (titre.length < 3)       addError('titre',       'Le titre doit contenir au moins 3 caractères.');
  if (!description)           addError('description', 'La description est obligatoire.');
  if (!date_debut)            addError('date_debut',  'La date de début est obligatoire.');
  if (!date_fin)              addError('date_fin',    'La date de fin est obligatoire.');
  if (date_debut && date_fin && date_fin <= date_debut)
                              addError('date_fin',    'La date de fin doit être postérieure à la date de début.');
  if (!lieu)                  addError('lieu',        'Le lieu est obligatoire.');
  if (!capacite || isNaN(capacite) || parseInt(capacite) < 1)
                              addError('capacite_max','La capacité doit être un entier positif (≥ 1).');
  if (prix === '' || isNaN(prix) || parseFloat(prix) < 0)
                              addError('prix',        'Le prix doit être un nombre positif.');
  if (!statut)                addError('statut',      'Veuillez choisir un statut.');
  if (!type)                  addError('type',        'Le type est obligatoire.');

  if (errors.length > 0) {
    e.preventDefault();
    var c = document.getElementById('js-errors');
    c.innerHTML = errors.map(err => '<div class="alert alert-danger">' + err + '</div>').join('');
    c.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
});
</script>
</body>
</html>