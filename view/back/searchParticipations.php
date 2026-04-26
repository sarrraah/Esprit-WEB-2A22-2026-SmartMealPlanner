<?php
require '../../controller/ParticipationController.php';
require '../../controller/EvenementController.php';

$partCtrl = new ParticipationController();
$evCtrl   = new EvenementController();

// ── Traitement du formulaire ─────────────────────────────────────────────
$list    = null;
$idEvent = null;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['id_event']) && isset($_POST['search'])) {
        $idEvent = $_POST['id_event'];
        $list    = $partCtrl->getParticipationsAvecEvenement($idEvent);
    }
}

// ── Liste de tous les événements pour le <select> ────────────────────────
$evenements = $evCtrl->listEvenements();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Recherche de participants par événement</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#fff5f5;color:#1a0505;min-height:100vh}

/* ── NAVBAR ── */
.navbar{background:#7f1d1d;padding:0 28px;height:58px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100}
.nav-logo{font-size:16px;font-weight:600;color:#fff;text-decoration:none}
.nav-logo span{color:#fca5a5}
.nav-links{display:flex;gap:6px;align-items:center}
.nav-link{font-size:13px;color:rgba(255,255,255,0.7);text-decoration:none;font-weight:500;padding:7px 14px;border-radius:8px;transition:all .15s}
.nav-link:hover{background:rgba(255,255,255,0.1);color:#fff}
.nav-link.active{background:rgba(255,255,255,0.18);color:#fff;font-weight:600}

/* ── CONTENT ── */
.content{max-width:860px;margin:40px auto;padding:0 20px}

/* ── CARD ── */
.card{background:#fff;border:1px solid #fde8e8;border-radius:16px;padding:28px 32px;box-shadow:0 2px 12px rgba(185,28,28,0.06);margin-bottom:24px}
.page-title{font-size:20px;font-weight:600;color:#7f1d1d;margin-bottom:6px}
.page-sub{font-size:13px;color:#9a3535;margin-bottom:24px}

/* ── FORM ── */
.form-row{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.form-label{font-size:13px;font-weight:500;color:#1a0505;white-space:nowrap}
.form-select{padding:10px 16px;border:1.5px solid #f7c1c1;border-radius:10px;background:#fff;color:#1a0505;font-size:13px;font-family:'Inter',sans-serif;outline:none;cursor:pointer;transition:border-color .2s;min-width:220px}
.form-select:focus{border-color:#b91c1c;box-shadow:0 0 0 3px rgba(185,28,28,0.1)}
.btn-search{display:inline-flex;align-items:center;gap:7px;background:#b91c1c;color:#fff;border:none;border-radius:10px;padding:10px 22px;font-size:13px;font-weight:500;font-family:'Inter',sans-serif;cursor:pointer;transition:background .15s}
.btn-search:hover{background:#991b1b}

/* ── RESULTS ── */
.results-title{font-size:16px;font-weight:600;color:#7f1d1d;margin-bottom:16px;padding-bottom:10px;border-bottom:1.5px solid #fde8e8;display:flex;align-items:center;gap:10px}
.count-badge{display:inline-block;background:#fce8e8;color:#7f1d1d;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;border:1px solid #f09595}

/* ── TABLE ── */
.table-wrap{border-radius:12px;overflow:hidden;border:1px solid #fde8e8}
table{width:100%;border-collapse:collapse}
thead{background:linear-gradient(135deg,#7f1d1d 0%,#b91c1c 100%)}
thead th{padding:12px 14px;text-align:left;font-size:12px;font-weight:500;color:#fff;white-space:nowrap}
tbody tr{border-bottom:1px solid #fce8e8;transition:background .15s}
tbody tr:last-child{border-bottom:none}
tbody tr:hover{background:#fff5f5}
td{padding:12px 14px;font-size:13px;color:#1a0505}
td b{font-weight:600;color:#7f1d1d}

.badge{padding:4px 12px;border-radius:20px;font-size:11px;font-weight:600}
.badge-confirme{background:#dcfce7;color:#166534;border:1px solid #86efac}
.badge-attente{background:#fff7ed;color:#b45309;border:1px solid #fed7aa}
.badge-annule{background:#fce8e8;color:#7f1d1d;border:1px solid #f09595}

.montant-paid{font-weight:600;color:#b91c1c}
.montant-free{font-weight:600;color:#15803d}

/* ── EMPTY ── */
.empty{text-align:center;padding:40px 20px;color:#9a3535;font-size:14px}
.empty-icon{font-size:36px;margin-bottom:10px}

/* ── EVENT INFO BANNER ── */
.event-info{background:#fce8e8;border:1px solid #f09595;border-radius:10px;padding:12px 16px;margin-bottom:18px;font-size:13px;color:#7f1d1d}
.event-info b{font-weight:600}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <a href="listEvenements.php" class="nav-logo">Event <span>Manager</span></a>
  <div class="nav-links">
    <a class="nav-link" href="listEvenements.php">📋 Événements</a>
    <a class="nav-link" href="addEvenement.php">➕ Nouvel événement</a>
    <a class="nav-link" href="listParticipations.php">👥 Participants</a>
    <a class="nav-link active" href="searchParticipations.php">🔍 Recherche</a>
  </div>
</nav>

<div class="content">

  <!-- FORMULAIRE -->
  <div class="card">
    <div class="page-title">🔍 Recherche de participants par événement</div>
    <div class="page-sub">Sélectionnez un événement pour afficher tous ses participants</div>

    <form action="" method="POST">
      <div class="form-row">
        <label class="form-label" for="id_event">Sélectionnez un événement :</label>
        <select class="form-select" name="id_event" id="id_event">
          <?php foreach ($evenements as $e): ?>
            <option value="<?= $e->getIdEvent() ?>"
              <?= ($idEvent == $e->getIdEvent()) ? 'selected' : '' ?>>
              <?= htmlspecialchars($e->getTitre()) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button class="btn-search" type="submit" name="search">
          🔍 Rechercher
        </button>
      </div>
    </form>
  </div>

  <!-- RÉSULTATS -->
  <?php if (isset($list)): ?>
  <div class="card">

    <?php if (!empty($list)): ?>
      <!-- Infos de l'événement (grâce à la jointure) -->
      <div class="event-info">
        📋 <b><?= htmlspecialchars($list[0]['titre']) ?></b>
        &nbsp;|&nbsp; 📍 <?= htmlspecialchars($list[0]['lieu']) ?>
        &nbsp;|&nbsp; 📅 <?= htmlspecialchars($list[0]['date_debut']) ?>
      </div>
    <?php endif; ?>

    <div class="results-title">
      Participants correspondants à l'événement sélectionné
      <span class="count-badge">
        <?= count($list) ?> participant<?= count($list) > 1 ? 's' : '' ?>
      </span>
    </div>

    <?php if (empty($list)): ?>
      <div class="empty">
        <div class="empty-icon">👥</div>
        Aucun participant trouvé pour cet événement.
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Participant</th>
              <th>Événement (JOIN)</th>
              <th>Date participation</th>
              <th>Montant</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($list as $p):
              $s = strtolower($p['statut']);
              $badgeClass = match(true) {
                  str_contains($s, 'confirm') => 'badge-confirme',
                  str_contains($s, 'attente') => 'badge-attente',
                  default                     => 'badge-annule',
              };
              $isFree = (float)$p['montant'] == 0;
              $mClass = $isFree ? 'montant-free' : 'montant-paid';
              $mLabel = $isFree ? 'Gratuit' : number_format($p['montant'], 2) . ' TND';
          ?>
            <tr>
              <td><?= $p['id_participation'] ?></td>
              <td><b><?= htmlspecialchars($p['nom_participant']) ?></b></td>
              <td><?= htmlspecialchars($p['titre']) ?></td>
              <td><?= htmlspecialchars($p['date_participation']) ?></td>
              <td><span class="<?= $mClass ?>"><?= $mLabel ?></span></td>
              <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($p['statut']) ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

</div>
</body>
</html>