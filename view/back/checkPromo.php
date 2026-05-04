<?php
require '../../controller/ParticipationController.php';
require '../../controller/EvenementController.php';

$partCtrl = new ParticipationController();
$evCtrl   = new EvenementController();

$list    = null;
$idEvent = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['id_event']) && isset($_POST['search'])) {
        $idEvent = $_POST['id_event'];
        $list    = $partCtrl->getParticipationsAvecEvenement($idEvent);
    }
}

$evenements = $evCtrl->listEvenements();

// ── Helper : récupère une valeur depuis plusieurs clés possibles ──────────
function val($row, array $keys, $default = '') {
    foreach ($keys as $k) {
        if (isset($row[$k]) && $row[$k] !== null) return $row[$k];
    }
    return $default;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recherche de participants</title>
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
    display: flex; align-items: center; justify-content: space-between;
    height: 60px; position: sticky; top: 0; z-index: 100;
}
.logo { font-size: 20px; font-weight: 700; color: #1a1a1a; text-decoration: none; }
.logo span { color: #dc2626; }
.nav-links { display: flex; align-items: center; gap: 32px; }
.nav-links a {
    font-size: 14px; color: #555; text-decoration: none; font-weight: 500;
    padding-bottom: 2px; border-bottom: 2px solid transparent;
    transition: color .2s, border-color .2s;
}
.nav-links a:hover { color: #1a1a1a; border-bottom-color: #dc2626; }
.nav-links a.active { color: #1a1a1a; border-bottom-color: #dc2626; }
.btn-nav {
    background: #dc2626; color: #fff; border: none; padding: 9px 20px;
    border-radius: 999px; font-size: 14px; font-weight: 600;
    cursor: pointer; text-decoration: none; font-family: inherit;
}

/* ── LAYOUT ── */
.section { padding: 16px 0 24px; }
.container { max-width: 100%; margin: 0; padding: 0 20px; }

h2 { font-size: 28px; font-weight: 700; color: #1a1a1a; margin-bottom: 6px; }
.sub { font-size: 14px; color: #888; margin-bottom: 24px; }

/* ── SEARCH BAR ── */
.search-bar {
    display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
    padding: 20px; background: #f9fafb;
    border: 1px solid #e5e5e5; border-radius: 10px;
    margin-bottom: 28px;
}
.search-bar label { font-size: 14px; font-weight: 500; color: #333; white-space: nowrap; }
.form-select {
    flex: 1; min-width: 240px;
    padding: 13px 16px; border: 1px solid #d1d5db; border-radius: 8px;
    background: #fff; color: #1a1a1a;
    font-size: 15px; font-family: 'DM Sans', sans-serif;
    outline: none; transition: border-color .2s, box-shadow .2s;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23555' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px;
}
.form-select:focus { border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,0.1); }
.btn-search {
    display: inline-flex; align-items: center; gap: 7px;
    background: #dc2626; color: #fff; border: none;
    border-radius: 8px; padding: 13px 24px;
    font-size: 15px; font-weight: 600; font-family: 'DM Sans', sans-serif;
    cursor: pointer; transition: background .15s; white-space: nowrap;
}
.btn-search:hover { background: #b91c1c; }

/* ── EVENT BANNER ── */
.event-banner {
    background: #fef2f2; border: 1px solid #fecaca;
    border-radius: 8px; padding: 13px 16px;
    margin-bottom: 20px; font-size: 14px; color: #991b1b;
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
}
.event-banner b { font-weight: 600; color: #dc2626; }

/* ── RESULTS HEADER ── */
.results-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px; flex-wrap: wrap; gap: 10px;
}
.results-header h3 { font-size: 17px; font-weight: 600; color: #1a1a1a; }
.count-badge {
    background: #fef2f2; color: #dc2626;
    font-size: 12px; font-weight: 600;
    padding: 4px 12px; border-radius: 999px;
    border: 1px solid #fecaca;
}

/* ── TABLE ── */
.table-wrap { border-radius: 10px; overflow: hidden; border: 1px solid #e5e5e5; }
table { width: 100%; border-collapse: collapse; }
thead { background: #dc2626; }
thead th {
    padding: 13px 16px; text-align: left;
    font-size: 13px; font-weight: 600; color: #fff; white-space: nowrap;
}
tbody tr { border-bottom: 1px solid #f3f4f6; transition: background .15s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #fef2f2; }
td { padding: 13px 16px; font-size: 14px; color: #1a1a1a; }
td b { font-weight: 600; }

/* ── BADGES ── */
.badge { padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; }
.badge-confirme  { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.badge-attente   { background: #fff7ed; color: #b45309; border: 1px solid #fed7aa; }
.badge-annule    { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

.montant-paid { font-weight: 600; color: #dc2626; }
.montant-free { font-weight: 600; color: #16a34a; }

/* ── EMPTY ── */
.empty { text-align: center; padding: 48px 20px; color: #aaa; font-size: 15px; }
.empty-icon { font-size: 40px; margin-bottom: 12px; }

/* ── DEBUG (désactiver en prod) ── */
.debug-box {
    background: #f1f5f9; border: 1px solid #cbd5e1;
    border-radius: 8px; padding: 12px 16px;
    font-size: 12px; color: #475569; margin-bottom: 20px;
    font-family: monospace; white-space: pre-wrap; word-break: break-all;
}

@media (max-width: 600px) {
    nav { padding: 0 16px; }
    .search-bar { flex-direction: column; align-items: stretch; }
    .btn-search { width: 100%; justify-content: center; }
}
</style>
</head>
<body>

<nav>
  <a href="listEvenements.php" class="logo">Smart Event<span>.</span></a>
  <div class="nav-links">
    <a href="listEvenements.php">Événements</a>
    <a href="listParticipations.php">Participants</a>
    <a href="searchParticipations.php" class="active">Recherche</a>
  </div>
  <a href="addEvenement.php" class="btn-nav">Ajouter Événement</a>
</nav>

<section class="section">
<div class="container">

  <h2>Recherche de participants</h2>
  <p class="sub">Sélectionnez un événement pour afficher tous ses participants</p>

  <!-- FORMULAIRE -->
  <form action="" method="POST">
    <div class="search-bar">
      <label for="id_event">Événement :</label>
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

  <!-- RÉSULTATS -->
  <?php if (isset($list)): ?>

    <?php
    // ── Debug : affiche les clés disponibles (à supprimer en prod) ──
    if (!empty($list)) {
        echo '<div class="debug-box"><strong>Clés disponibles dans $list[0] :</strong> ' . implode(', ', array_keys($list[0])) . '</div>';
    }
    ?>

    <?php if (!empty($list)): ?>
      <div class="event-banner">
        📋 <b><?= htmlspecialchars(val($list[0], ['titre', 'event_titre', 'nom_event', 'evenement_titre'])) ?></b>
        &nbsp;|&nbsp; 📍 <?= htmlspecialchars(val($list[0], ['lieu', 'event_lieu', 'lieu_event'])) ?>
        &nbsp;|&nbsp; 📅 <?= htmlspecialchars(val($list[0], ['date_debut', 'event_date', 'date_evenement'])) ?>
      </div>
    <?php endif; ?>

    <div class="results-header">
      <h3>Participants de l'événement sélectionné</h3>
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
              <th>Email</th>
              <th>Places</th>
              <th>Date participation</th>
              <th>Montant</th>
              <th>Paiement</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($list as $p):
              // ── Clés flexibles ──────────────────────────────────────────
              $nom    = val($p, ['nom_participant', 'nom', 'participant_nom', 'prenom_nom']);
              $prenom = val($p, ['prenom_participant', 'prenom', 'participant_prenom']);
              $nomAff = $prenom ? $nom . ' ' . $prenom : $nom;

              $email  = val($p, ['email', 'participant_email', 'email_participant']);
              $places = val($p, ['nombre_places_reservees', 'places', 'nb_places'], 1);
              $date   = val($p, ['date_participation', 'date', 'created_at']);
              $mode   = val($p, ['mode_paiement', 'paiement', 'payment_mode']);

              // Montant : calculé ou fourni
              $montantRaw = val($p, ['montant', 'total', 'montant_total', 'prix_total'], null);
              if ($montantRaw === null) {
                  // Calcul depuis prix × places si disponible
                  $prixUnit = val($p, ['prix', 'prix_evenement', 'price'], 0);
                  $montantRaw = (float)$prixUnit * (int)$places;
              }
              $isFree = ((float)$montantRaw == 0);
              $mClass = $isFree ? 'montant-free' : 'montant-paid';
              $mLabel = $isFree ? 'Gratuit' : number_format((float)$montantRaw, 2) . ' TND';

              $statut = val($p, ['statut', 'status', 'etat'], '—');
              $s = strtolower($statut);
              $badgeClass = match(true) {
                  str_contains($s, 'confirm') => 'badge-confirme',
                  str_contains($s, 'attente') => 'badge-attente',
                  default                     => 'badge-annule',
              };
          ?>
            <tr>
              <td><?= htmlspecialchars(val($p, ['id_participation', 'id', 'participation_id'])) ?></td>
              <td><b><?= htmlspecialchars($nomAff ?: '—') ?></b></td>
              <td><?= htmlspecialchars($email ?: '—') ?></td>
              <td><?= htmlspecialchars($places) ?></td>
              <td><?= htmlspecialchars($date ?: '—') ?></td>
              <td><span class="<?= $mClass ?>"><?= $mLabel ?></span></td>
              <td><?= htmlspecialchars($mode ?: '—') ?></td>
              <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($statut) ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

  <?php endif; ?>

</div>
</section>
</body>
</html>