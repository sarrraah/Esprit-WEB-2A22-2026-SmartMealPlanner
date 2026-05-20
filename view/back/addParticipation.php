<?php
require_once __DIR__ . '/../../controller/ParticipationController.php';
require_once __DIR__ . '/../../controller/EvenementController.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$evCtrl    = new EvenementController();
$allEvents = $evCtrl->listEvenements();

$prefillEvent = isset($_GET['id_event']) ? (int)$_GET['id_event'] : 0;
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_event   = (int)   ($_POST['id_event']                ?? 0);
    $nom        = trim(    $_POST['nom']                     ?? '');
    $prenom     = trim(    $_POST['prenom']                  ?? '');
    $email      = trim(    $_POST['email']                   ?? '');
    $places     = max(1, (int)($_POST['nombre_places_reservees'] ?? 1));
    $mode       = trim(    $_POST['mode_paiement']           ?? 'gratuit');
    $statut     = trim(    $_POST['statut']                  ?? 'en_attente');
    $date       = trim(    $_POST['date_participation']      ?? date('Y-m-d'));

    if (!$id_event)          $errors[] = 'Please select an event.';
    if ($nom === '')         $errors[] = 'Last name is required.';
    if ($prenom === '')      $errors[] = 'First name is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL))
                             $errors[] = 'A valid email is required.';

    if (empty($errors)) {
        $ctrl = new ParticipationController();
        $p = new Participation(null, $id_event, $nom, $prenom, $email, $places, $mode, $statut, $date);
        $ctrl->addParticipation($p);
        header('Location: listParticipations.php?msg=added');
        exit;
    }
}

$pageTitle = 'Add Participation';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/sidebar.php';
?>

<div class="admin-main">
  <div class="admin-topbar">
    <h5><i class="bi bi-person-plus me-2" style="color:var(--accent)"></i>Add Participation</h5>
    <a href="listParticipations.php" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i>Back to list
    </a>
  </div>

  <div class="admin-content" style="max-width:680px;margin:0 auto;padding:28px 16px;">

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger mb-4">
        <ul class="mb-0">
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="admin-card card">
      <div class="card-body p-4">
        <form method="POST" action="addParticipation.php<?= $prefillEvent ? '?id_event='.$prefillEvent : '' ?>">

          <div class="row g-3">

            <!-- Event -->
            <div class="col-12">
              <label class="form-label fw-semibold">Event <span class="text-danger">*</span></label>
              <select name="id_event" class="form-select" required>
                <option value="">— Select an event —</option>
                <?php foreach ($allEvents as $ev): ?>
                  <option value="<?= $ev->getIdEvent() ?>"
                    <?= (($prefillEvent ?: (int)($_POST['id_event'] ?? 0)) == $ev->getIdEvent()) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($ev->getTitre()) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Name -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
              <input type="text" name="nom" class="form-control"
                     value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
              <input type="text" name="prenom" class="form-control"
                     value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required>
            </div>

            <!-- Email -->
            <div class="col-12">
              <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control"
                     value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>

            <!-- Places -->
            <div class="col-md-4">
              <label class="form-label fw-semibold">Seats Reserved</label>
              <input type="number" name="nombre_places_reservees" class="form-control"
                     min="1" value="<?= (int)($_POST['nombre_places_reservees'] ?? 1) ?>">
            </div>

            <!-- Payment -->
            <div class="col-md-4">
              <label class="form-label fw-semibold">Payment Method</label>
              <select name="mode_paiement" class="form-select">
                <?php foreach (['gratuit','espèces','carte','virement'] as $m): ?>
                  <option value="<?= $m ?>" <?= (($_POST['mode_paiement'] ?? 'gratuit') === $m) ? 'selected' : '' ?>>
                    <?= ucfirst($m) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Status -->
            <div class="col-md-4">
              <label class="form-label fw-semibold">Status</label>
              <select name="statut" class="form-select">
                <option value="en_attente" <?= (($_POST['statut'] ?? 'en_attente') === 'en_attente') ? 'selected' : '' ?>>Pending</option>
                <option value="confirme"   <?= (($_POST['statut'] ?? '') === 'confirme')   ? 'selected' : '' ?>>Confirmed</option>
                <option value="refuse"     <?= (($_POST['statut'] ?? '') === 'refuse')     ? 'selected' : '' ?>>Refused</option>
              </select>
            </div>

            <!-- Date -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Participation Date</label>
              <input type="date" name="date_participation" class="form-control"
                     value="<?= htmlspecialchars($_POST['date_participation'] ?? date('Y-m-d')) ?>">
            </div>

          </div>

          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-yummy">
              <i class="bi bi-check2-circle me-1"></i>Save Participation
            </button>
            <a href="listParticipations.php" class="btn btn-outline-secondary">Cancel</a>
          </div>

        </form>
      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/partials/foot.php'; ?>
