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
        $errors[] = "Please select an event.";
    if (strlen($nom) < 2)
        $errors[] = "Last name must be at least 2 characters.";
    if (strlen($prenom) < 2)
        $errors[] = "First name must be at least 2 characters.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = "Email address is invalid.";
    if ($places < 1 || $places > 10)
        $errors[] = "Reserved seats must be between 1 and 10.";
    if (!in_array($mode_paiement, ['espèces', 'carte', 'virement', 'gratuit']))
        $errors[] = "Please choose a valid payment method.";
    if (!in_array($statut, ['confirmé', 'en attente', 'annulé', 'en_attente']))
        $errors[] = "Please choose a valid status.";
    if (empty($date_participation))
        $errors[] = "Participation date is required.";

    if (empty($errors)) {
        $oldStatut = $participation->getStatut();

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

        // ── Send email if status changed to confirmé or annulé ────────
        $newStatut = $statut;
        if ($oldStatut !== $newStatut && in_array($newStatut, ['confirmé', 'annulé'])) {
            require_once __DIR__ . '/sendMail.php';
            $evObj     = $eventMap[(int)$id_event] ?? null;
            $eventDate = $evObj ? date('d/m/Y', strtotime($evObj->getDateDebut())) : '';
            $eventLieu = $evObj ? $evObj->getLieu() : '';
            $eventPrix = $evObj ? (float)$evObj->getPrix() : 0;
            sendStatusEmail(
                $email,
                $prenom . ' ' . $nom,
                $evObj ? $evObj->getTitre() : '',
                $eventDate,
                $eventLieu,
                $eventPrix,
                $places,
                $newStatut
            );
        }

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
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Participation #<?= $id ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
      <a href="listEvenements.php"><i class="bi bi-calendar-event-fill"></i> Events</a>
      <a href="listParticipations.php" class="active"><i class="bi bi-people-fill"></i> Participants</a>
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
        <span class="label">Registration Management</span>
        <h1>Edit Participation</h1>
        <p>Update participant details, event selection, and payment status from the admin dashboard.</p>
      </div>
      <div class="topbar-action">
        <a href="listParticipations.php" class="btn-primary"><i class="bi bi-arrow-left"></i> Back to participants</a>
      </div>
    </div>

    <div class="content-wrap">
      <div class="dashboard-grid">
        <section class="section-card">
          <div class="section-card-title">
            <span><i class="bi bi-pencil-square"></i> Registration details</span>
          </div>

          <div class="montant-info">
            💰 Total amount calculated:
            <strong><?= $montant == 0 ? 'Free' : number_format($montant, 2) . ' TND' ?></strong>
            &nbsp;(<?= $participation->getNombrePlacesReservees() ?> seat(s) × <?= number_format($prix, 2) ?> TND)
          </div>

          <?php foreach ($errors as $err): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
          <?php endforeach; ?>

          <form method="POST" action="" id="partForm" class="row g-3">
            <input type="hidden" name="id" value="<?= $id ?>">

            <div class="col-12">
              <label class="form-label">Event *</label>
              <select name="id_event" id="id_event" class="form-select">
                <option value="">-- Choose an event --</option>
                <?php foreach ($allEvents as $ev): ?>
                  <option value="<?= $ev->getIdEvent() ?>" <?= $participation->getIdEvent() == $ev->getIdEvent() ? 'selected' : '' ?>>
                    <?= htmlspecialchars($ev->getTitre()) ?> (<?= htmlspecialchars($ev->getLieu()) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">First name *</label>
              <input type="text" name="prenom" id="prenom" class="form-control" placeholder="Ahmed" value="<?= htmlspecialchars($_POST['prenom'] ?? $participation->getPrenom()) ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Last name *</label>
              <input type="text" name="nom" id="nom" class="form-control" placeholder="Ben Ali" value="<?= htmlspecialchars($_POST['nom'] ?? $participation->getNom()) ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Email *</label>
              <input type="email" name="email" id="email" class="form-control" placeholder="ahmed@email.com" value="<?= htmlspecialchars($_POST['email'] ?? $participation->getEmail()) ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Reserved seats *</label>
              <input type="number" name="nombre_places_reservees" id="places" class="form-control" min="1" max="10" value="<?= htmlspecialchars($_POST['nombre_places_reservees'] ?? $participation->getNombrePlacesReservees()) ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Payment method *</label>
              <select name="mode_paiement" id="mode_paiement" class="form-select">
                <option value="">-- Choose --</option>
                <option value="gratuit"  <?= ($participation->getModePaiement() === 'gratuit')  ? 'selected' : '' ?>>Free</option>
                <option value="espèces"  <?= ($participation->getModePaiement() === 'espèces')  ? 'selected' : '' ?>>Cash 💵</option>
                <option value="carte"    <?= ($participation->getModePaiement() === 'carte')    ? 'selected' : '' ?>>Card 💳</option>
                <option value="virement" <?= ($participation->getModePaiement() === 'virement') ? 'selected' : '' ?>>Bank transfer 🏦</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Status *</label>
              <select name="statut" id="statut" class="form-select">
                <option value="">-- Choose --</option>
                <option value="confirmé"   <?= $participation->getStatut() === 'confirmé'   ? 'selected' : '' ?>>✅ Confirmed</option>
                <option value="en attente" <?= ($participation->getStatut() === 'en attente' || $participation->getStatut() === 'en_attente') ? 'selected' : '' ?>>⏳ Pending</option>
                <option value="annulé"     <?= $participation->getStatut() === 'annulé'     ? 'selected' : '' ?>>❌ Canceled</option>
              </select>
            </div>

            <div class="col-12">
              <label class="form-label">Participation date *</label>
              <input type="datetime-local" name="date_participation" id="date_participation" class="form-control" value="<?= $dp ?>">
            </div>

            <div class="col-12 d-flex gap-2">
              <button type="submit" class="btn btn-danger">Update participation</button>
              <a href="listParticipations.php" class="btn btn-outline-secondary">Cancel</a>
            </div>

          </form>
        </section>

        <aside class="side-panel">
          <div class="small-card">
            <h3>Participation summary</h3>
            <ul>
              <li><span>Participant</span><span><?= htmlspecialchars($participation->getPrenom() . ' ' . $participation->getNom()) ?></span></li>
              <li><span>Event</span><span><?= htmlspecialchars($evObj ? $evObj->getTitre() : 'Unknown') ?></span></li>
              <li><span>Seats reserved</span><span><?= htmlspecialchars($participation->getNombrePlacesReservees()) ?></span></li>
              <li><span>Payment method</span><span><?= htmlspecialchars($participation->getModePaiement()) ?></span></li>
              <li><span>Total amount</span><span><?= $montant == 0 ? 'Free' : number_format($montant, 2) . ' TND' ?></span></li>
              <li><span>Status</span><span><?= htmlspecialchars($participation->getStatut()) ?></span></li>
            </ul>
          </div>
          <div class="small-card">
            <h3>Quick actions</h3>
            <a href="listParticipations.php" class="btn-action-secondary"><i class="bi bi-list"></i> View registrations</a>
            <a href="addParticipation.php" class="btn-action-secondary"><i class="bi bi-plus-circle"></i> Add registration</a>
          </div>
        </aside>
      </div>
    </div>
  </main>
</div>

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

    if (!id_event)                 errors.push("Please select an event.");
    if (nom.length < 2)             errors.push("Last name must be at least 2 characters.");
    if (prenom.length < 2)          errors.push("First name must be at least 2 characters.");
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.push("Please enter a valid email.");
    if (isNaN(places) || places < 1 || places > 10) errors.push("Reserved seats must be between 1 and 10.");
    if (!mode)                     errors.push("Please select a payment method.");
    if (!statut)                   errors.push("Please select a status.");
    if (!date)                     errors.push("Participation date is required.");

    var errorDiv = document.getElementById('js-errors');
    if (!errorDiv) {
      errorDiv = document.createElement('div');
      errorDiv.id = 'js-errors';
      errorDiv.className = 'alert alert-danger';
      document.getElementById('partForm').prepend(errorDiv);
    }

    if (errors.length > 0) {
      e.preventDefault();
      errorDiv.innerHTML = '<strong>Errors:</strong><ul style="margin:8px 0 0 16px">'
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