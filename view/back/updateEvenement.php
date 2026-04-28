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

    if (strlen($titre) < 3)         $errors[] = "Title must be at least 3 characters.";
    if (empty($description))        $errors[] = "Description is required.";
    if (empty($date_debut))         $errors[] = "Start date is required.";
    if (empty($date_fin))           $errors[] = "End date is required.";
    if (!empty($date_debut) && !empty($date_fin) && $date_fin <= $date_debut)
                                    $errors[] = "End date must be after start date.";
    if (empty($lieu))               $errors[] = "Location is required.";
    if (!is_numeric($capacite_max) || (int)$capacite_max < 1)
                                    $errors[] = "Max capacity must be a positive integer (>= 1).";
    if (!is_numeric($prix) || (float)$prix < 0)
                                    $errors[] = "Price must be a positive number.";
    if (!in_array($statut, ['actif', 'annulé', 'terminé']))
                                    $errors[] = "Please choose a valid status.";
    if (empty($type))               $errors[] = "Type is required.";

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
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Event #<?= $id ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="css/admin.css">
</head>
<body>

<div class="admin-shell">
  <aside class="sidebar">
    <div class="brand">
      <div class="brand-mark">S</div>
      <div class="brand-name">SmartMeal</div>
    </div>
    <div class="section-label">Dashboard</div>
    <nav>
      <a href="listEvenements.php" class="active"><i class="bi bi-calendar-event-fill"></i> Events</a>
      <a href="listParticipations.php"><i class="bi bi-people-fill"></i> Participants</a>
      <a href="afficherProduit.php"><i class="bi bi-bag-fill"></i> Products</a>
      <a href="afficherCategorie.php"><i class="bi bi-tags-fill"></i> Categories</a>
    </nav>
    <div class="section-label">System</div>
    <nav>
      <a href="#"><i class="bi bi-bar-chart-fill"></i> Analytics</a>
      <a href="#"><i class="bi bi-gear-fill"></i> Settings</a>
    </nav>
  </aside>
  <main class="main-area">
    <div class="topbar">
      <div class="topbar-title">
        <span class="label">Event Management</span>
        <h1>Edit Event</h1>
        <p>Update event details, schedule, and capacity from the admin dashboard.</p>
      </div>
      <div class="topbar-action">
        <a href="listEvenements.php" class="btn-primary"><i class="bi bi-arrow-left"></i> Back to events</a>
      </div>
    </div>
    <div class="content-wrap">
      <div class="dashboard-grid">
        <section class="section-card">
          <div class="section-card-title">
            <span><i class="bi bi-pencil-square"></i> Event details</span>
          </div>
          <form method="POST" action="" enctype="multipart/form-data" class="row g-3" id="evenementForm">
            <input type="hidden" name="id" value="<?= $id ?>">

            <div class="col-md-6">
              <label class="form-label">Title *</label>
              <input type="text" name="titre" class="form-control" value="<?= htmlspecialchars($evenement->getTitre()) ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Type *</label>
              <input type="text" name="type" class="form-control" value="<?= htmlspecialchars($evenement->getType()) ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Start Date *</label>
              <input type="datetime-local" name="date_debut" class="form-control" value="<?= $dd ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">End Date *</label>
              <input type="datetime-local" name="date_fin" class="form-control" value="<?= $df ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Location *</label>
              <input type="text" name="lieu" class="form-control" value="<?= htmlspecialchars($evenement->getLieu()) ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Max Capacity *</label>
              <input type="number" name="capacite_max" class="form-control" value="<?= htmlspecialchars($evenement->getCapaciteMax()) ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Price (TND) *</label>
              <input type="number" name="prix" step="0.01" min="0" class="form-control" value="<?= htmlspecialchars($evenement->getPrix()) ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Status *</label>
              <select name="statut" class="form-select">
                <option value="">-- Select --</option>
                <option value="actif"   <?= $evenement->getStatut()==='actif'   ? 'selected':'' ?>>Active</option>
                <option value="annulé"  <?= $evenement->getStatut()==='annulé'  ? 'selected':'' ?>>Canceled</option>
                <option value="terminé" <?= $evenement->getStatut()==='terminé' ? 'selected':'' ?>>Finished</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Image</label>
              <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(this, 'previewImg')">
              <div class="image-preview-box">
                <?php if ($imageUrl): ?>
                  <img id="previewImg" src="<?= htmlspecialchars($imageUrl) ?>" alt="Current image">
                  <label class="delete-img-label">
                    <input type="checkbox" name="delete_image" value="1" onchange="toggleUpload(this)">
                    Remove current image
                  </label>
                <?php else: ?>
                  <img id="previewImg" src="#" alt="Preview" style="display:none;">
                <?php endif; ?>
              </div>
            </div>

            <div class="col-12">
              <label class="form-label">Description *</label>
              <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($evenement->getDescription()) ?></textarea>
            </div>

            <div class="col-12 d-flex gap-2">
              <button type="submit" class="btn btn-danger">Update</button>
              <a href="listEvenements.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
          </form>
        </section>
        <aside class="side-panel">
          <div class="small-card">
            <h3>Event preview</h3>
            <?php if ($imageUrl): ?>
              <img src="<?= htmlspecialchars($imageUrl) ?>" alt="Event image" style="width:100%;border-radius:12px;object-fit:cover;max-height:220px;margin-bottom:14px;">
            <?php endif; ?>
            <ul>
              <li><span>Title</span><span><?= htmlspecialchars($evenement->getTitre()) ?></span></li>
              <li><span>Location</span><span><?= htmlspecialchars($evenement->getLieu()) ?></span></li>
              <li><span>Type</span><span><?= htmlspecialchars($evenement->getType()) ?></span></li>
              <li><span>Status</span><span><?= htmlspecialchars($evenement->getStatut()) ?></span></li>
            </ul>
          </div>
          <div class="small-card">
            <h3>Quick actions</h3>
            <a href="addEvenement.php" class="btn-action-secondary"><i class="bi bi-plus-circle"></i> Add new event</a>
            <a href="listParticipations.php" class="btn-action-secondary"><i class="bi bi-people"></i> View participants</a>
          </div>
        </aside>
      </div>
    </div>
  </main>
</div>

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