<?php
require '../../controller/ParticipationController.php';
require '../../controller/EvenementController.php';
$ctrl     = new ParticipationController();
$evCtrl   = new EvenementController();

// DELETE
if (isset($_GET['delete'])) {
    $ctrl->deleteParticipation($_GET['delete']);
    $redir = isset($_GET['id_event']) ? 'listParticipations.php?id_event=' . (int)$_GET['id_event'] . '&msg=deleted'
                                      : 'listParticipations.php?msg=deleted';
    header('Location: ' . $redir);
    exit;
}

$id_event_filter = isset($_GET['id_event']) ? (int)$_GET['id_event'] : null;
$eventTitle      = '';

if ($id_event_filter) {
    $participations = $ctrl->listParticipationsByEvent($id_event_filter);
    $ev             = $evCtrl->getEvenementById($id_event_filter);
    $eventTitle     = $ev ? $ev->getTitre() : '';
} else {
    $participations = $ctrl->listParticipations();
}

$allEvents = $evCtrl->listEvenements();

// Construire map événements pour prix
$eventMap = [];
foreach ($allEvents as $ev) {
    $eventMap[$ev->getIdEvent()] = $ev;
}

$msg   = $_GET['msg'] ?? '';
$total = count($participations);

$confirmes = count(array_filter($participations, fn($p) => strtolower($p->getStatut()) === 'confirmé'));
$enAttente = count(array_filter($participations, fn($p) => strtolower($p->getStatut()) === 'en attente'));
$annules   = count(array_filter($participations, fn($p) => strtolower($p->getStatut()) === 'annulé'));

// Calcul revenue basé sur prix événement × nombre de places
$revenue = 0;
foreach ($participations as $p) {
    $evObj = $eventMap[$p->getIdEvent()] ?? null;
    if ($evObj) {
        $revenue += (float)$evObj->getPrix() * $p->getNombrePlacesReservees();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Liste des Participations</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#fff5f5;color:#1a0505;min-height:100vh}

/* ── NAVBAR ── */
.navbar{background:#7f1d1d;padding:0 28px;height:58px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100}
.nav-logo{font-size:16px;font-weight:600;color:#fff;text-decoration:none}
.nav-logo span{color:#fca5a5}
.nav-logo sub{font-size:11px;color:rgba(255,255,255,0.4);margin-left:8px}
.nav-links{display:flex;gap:6px;align-items:center}
.nav-link{font-size:13px;color:rgba(255,255,255,0.7);text-decoration:none;font-weight:500;padding:7px 14px;border-radius:8px;transition:all .15s;display:flex;align-items:center;gap:6px}
.nav-link:hover{background:rgba(255,255,255,0.1);color:#fff}
.nav-link.active{background:rgba(255,255,255,0.18);color:#fff;font-weight:600}
.nav-user{display:flex;align-items:center;gap:8px;background:rgba(255,255,255,0.08);padding:6px 12px;border-radius:10px}
.nav-avatar{width:30px;height:30px;border-radius:50%;background:#b91c1c;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:#fff}
.nav-user-name{font-size:12px;color:#fff;font-weight:500}
.nav-user-role{font-size:10px;color:rgba(255,255,255,0.4)}

/* ── TOPBAR ── */
.topbar{background:#fff;border-bottom:1.5px solid #f7c1c1;padding:0 28px;height:58px;display:flex;align-items:center;justify-content:space-between}
.topbar-title{font-size:15px;font-weight:600;color:#1a0505}
.topbar-sub{font-size:12px;color:#9a3535}
.topbar-right{display:flex;align-items:center;gap:10px}
.btn-new{display:inline-flex;align-items:center;gap:6px;background:#b91c1c;color:#fff;border:none;border-radius:10px;padding:9px 18px;font-size:13px;font-weight:500;font-family:'Inter',sans-serif;cursor:pointer;text-decoration:none;transition:background .15s}
.btn-new:hover{background:#991b1b}
.btn-csv{display:inline-flex;align-items:center;gap:6px;background:#fff;color:#7f1d1d;border:1.5px solid #f7c1c1;border-radius:10px;padding:8px 16px;font-size:13px;font-weight:500;font-family:'Inter',sans-serif;cursor:pointer;transition:all .15s}
.btn-csv:hover{background:#fce8e8;border-color:#f09595}

.content{padding:28px 28px 60px}

/* ── STATS ── */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:26px}
.stat-card{background:#fff;border:1px solid #fde8e8;border-radius:14px;padding:16px 20px}
.stat-num{font-size:26px;font-weight:700;color:#1a0505;line-height:1}
.stat-lbl{font-size:11px;color:#9a3535;margin-top:5px;text-transform:uppercase;letter-spacing:.6px}
.stat-card.s-confirme .stat-num{color:#15803d}
.stat-card.s-attente  .stat-num{color:#b45309}
.stat-card.s-revenue  .stat-num{color:#b91c1c}

/* ── ALERTS ── */
.alert{padding:12px 16px;border-radius:10px;margin-bottom:18px;font-size:14px;font-weight:500}
.alert-success{background:#fce8e8;color:#7f1d1d;border:1px solid #f09595}
.alert-danger{background:#fff0e0;color:#7a4000;border:1px solid #fad99a}

/* ── TOOLBAR ── */
.toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:12px}
.filters{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.search-wrap{position:relative}
.search-wrap input{padding:9px 14px 9px 36px;border:1px solid #f7c1c1;border-radius:10px;background:#fff;color:#1a0505;font-size:13px;width:220px;outline:none;font-family:'Inter',sans-serif;transition:border-color .2s}
.search-wrap input:focus{border-color:#b91c1c;box-shadow:0 0 0 3px rgba(185,28,28,0.1)}
.search-wrap input::placeholder{color:#c9a0a0}
.search-wrap::before{content:'🔍';font-size:13px;position:absolute;left:11px;top:50%;transform:translateY(-50%);pointer-events:none}
.filter-select{padding:9px 14px;border:1px solid #f7c1c1;border-radius:10px;background:#fff;color:#1a0505;font-size:13px;outline:none;font-family:'Inter',sans-serif;cursor:pointer;transition:border-color .2s}
.filter-select:focus{border-color:#b91c1c;box-shadow:0 0 0 3px rgba(185,28,28,0.1)}

/* ── TABLE ── */
.table-wrap{background:#fff;border:1px solid #fde8e8;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(185,28,28,0.06)}
table{width:100%;border-collapse:collapse}
thead{background:linear-gradient(135deg,#7f1d1d 0%,#b91c1c 100%)}
thead th{padding:14px 12px;text-align:center;font-size:13px;font-weight:500;color:#fff;white-space:nowrap;user-select:none}
thead th.sortable{cursor:pointer;transition:background .15s}
thead th.sortable:hover{background:rgba(255,255,255,0.12)}
thead th .sort-arrow{display:inline-block;margin-left:5px;font-size:10px;opacity:.5}
thead th.asc .sort-arrow::after{content:'▲';opacity:1}
thead th.desc .sort-arrow::after{content:'▼';opacity:1}
thead th:not(.asc):not(.desc) .sort-arrow::after{content:'⇅';opacity:.4}
tbody tr{border-bottom:1px solid #fce8e8;transition:background .15s}
tbody tr:last-child{border-bottom:none}
tbody tr:hover{background:#fff5f5}
td{padding:13px 12px;text-align:center;font-size:13px;color:#1a0505}
td b{font-weight:600;color:#7f1d1d}

.badge{padding:4px 12px;border-radius:20px;font-size:11px;font-weight:600}
.badge-confirme{background:#dcfce7;color:#166534;border:1px solid #86efac}
.badge-attente{background:#fff0e0;color:#7a4000;border:1px solid #fad99a}
.badge-annule{background:#f5e8e8;color:#7f1d1d;border:1px solid #f09595}

.montant-cell{font-weight:600;color:#b91c1c}
.montant-free{font-weight:600;color:#15803d}

.actions{display:flex;justify-content:center;align-items:center;gap:8px}
.action-btn{width:36px;height:36px;border:none;border-radius:10px;cursor:pointer;font-size:15px;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;transition:all .15s;box-shadow:0 2px 6px rgba(0,0,0,0.08)}
.action-btn:hover{transform:translateY(-2px)}
.btn-edit{background:#f7c1c1;color:#7f1d1d}
.btn-edit:hover{background:#f09595}
.btn-delete{background:#b91c1c;color:#fff}
.btn-delete:hover{background:#991b1b}

.empty{text-align:center;padding:50px 20px;color:#9a3535;font-size:14px}
.empty-icon{font-size:36px;margin-bottom:10px}

/* ── EVENT BANNER ── */
.event-banner{background:#fff;border:1px solid #fde8e8;border-radius:14px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;gap:12px;font-size:13px}
.event-banner-icon{font-size:22px}
.event-banner-title{font-weight:600;color:#1a0505}
.event-banner-sub{color:#9a3535;font-size:12px;margin-top:2px}
.event-banner a{margin-left:auto;font-size:12px;color:#b91c1c;text-decoration:none;font-weight:500;padding:6px 14px;border:1px solid #f7c1c1;border-radius:8px;transition:all .15s}
.event-banner a:hover{background:#fce8e8}

/* ── PAGINATION ── */
.pagination{display:flex;align-items:center;justify-content:space-between;margin-top:20px;flex-wrap:wrap;gap:12px}
.pagination-info{font-size:13px;color:#9a3535}
.pagination-btns{display:flex;gap:6px;flex-wrap:wrap;align-items:center}
.pg-btn{width:34px;height:34px;border:1.5px solid #f7c1c1;border-radius:8px;background:#fff;color:#7f1d1d;font-size:13px;font-weight:500;cursor:pointer;font-family:'Inter',sans-serif;transition:all .15s;display:inline-flex;align-items:center;justify-content:center}
.pg-btn:hover{background:#fce8e8;border-color:#f09595}
.pg-btn.active{background:#b91c1c;color:#fff;border-color:#b91c1c}
.pg-btn:disabled{opacity:.35;cursor:default}
.pg-size-wrap{display:flex;align-items:center;gap:8px;font-size:13px;color:#9a3535}
.pg-size-wrap select{padding:5px 10px;border:1.5px solid #f7c1c1;border-radius:8px;background:#fff;color:#1a0505;font-size:13px;outline:none;font-family:'Inter',sans-serif;cursor:pointer}

@media(max-width:900px){
  .stats-grid{grid-template-columns:repeat(2,1fr)}
  .content{padding:16px}
  .topbar{padding:0 16px}
  .table-wrap{overflow-x:auto}
  .nav-links{gap:2px}
  .nav-link{padding:6px 10px;font-size:12px}
  .nav-user{display:none}
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <a href="listEvenements.php" class="nav-logo">
    Event <span>Manager</span><sub>Back Office</sub>
  </a>
  <div class="nav-links">
    <a class="nav-link" href="listEvenements.php">📋 Événements</a>
    <a class="nav-link" href="addEvenement.php">➕ Nouvel événement</a>
    <a class="nav-link active" href="listParticipations.php">👥 Participants</a>
    <a class="nav-link" href="../front/interfaceevent.php">🌐 Vue front</a>
  </div>
  <div class="nav-user">
    <div class="nav-avatar">AD</div>
    <div>
      <div class="nav-user-name">Admin</div>
      <div class="nav-user-role">Administrateur</div>
    </div>
  </div>
</nav>

<!-- TOPBAR -->
<div class="topbar">
  <div>
    <div class="topbar-title">👥 Liste des Participations</div>
    <div class="topbar-sub">
      <?= $id_event_filter ? 'Filtré par : ' . htmlspecialchars($eventTitle) : 'Toutes les participations' ?>
    </div>
  </div>
  <div class="topbar-right">
    <button class="btn-csv" onclick="exportCSV()">⬇ Export CSV</button>
    <a class="btn-new" href="addParticipation.php<?= $id_event_filter ? '?id_event=' . $id_event_filter : '' ?>">+ Nouvelle Participation</a>
  </div>
</div>

<div class="content">

  <!-- STATS -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-num"><?= $total ?></div>
      <div class="stat-lbl">Total</div>
    </div>
    <div class="stat-card s-confirme">
      <div class="stat-num"><?= $confirmes ?></div>
      <div class="stat-lbl">Confirmés</div>
    </div>
    <div class="stat-card s-attente">
      <div class="stat-num"><?= $enAttente ?></div>
      <div class="stat-lbl">En attente</div>
    </div>
    <div class="stat-card s-revenue">
      <div class="stat-num"><?= number_format($revenue, 2) ?></div>
      <div class="stat-lbl">Revenue (TND)</div>
    </div>
  </div>

  <!-- ALERTS -->
  <?php if ($msg === 'added'):   ?><div class="alert alert-success">✅ Participation ajoutée avec succès.</div><?php endif; ?>
  <?php if ($msg === 'updated'): ?><div class="alert alert-success">✅ Participation mise à jour.</div><?php endif; ?>
  <?php if ($msg === 'deleted'): ?><div class="alert alert-danger">🗑️ Participation supprimée.</div><?php endif; ?>

  <!-- EVENT BANNER -->
  <?php if ($id_event_filter && $eventTitle): ?>
  <div class="event-banner">
    <span class="event-banner-icon">📋</span>
    <div>
      <div class="event-banner-title"><?= htmlspecialchars($eventTitle) ?></div>
      <div class="event-banner-sub">Affichage des participations liées à cet événement</div>
    </div>
    <a href="listParticipations.php">Voir tout</a>
  </div>
  <?php endif; ?>

  <!-- TOOLBAR -->
  <div class="toolbar">
    <div class="filters">
      <div class="search-wrap">
        <input type="text" id="searchInput" placeholder="Rechercher..." oninput="applyFilters()">
      </div>
      <select class="filter-select" id="filterStatut" onchange="applyFilters()">
        <option value="">— Tous les statuts —</option>
        <option value="confirmé">Confirmé</option>
        <option value="en attente">En attente</option>
        <option value="annulé">Annulé</option>
      </select>
      <select class="filter-select" id="filterEvent" onchange="applyFilters()">
        <option value="">— Tous les événements —</option>
        <?php foreach ($allEvents as $ev): ?>
          <option value="<?= $ev->getIdEvent() ?>"
            <?= ($id_event_filter == $ev->getIdEvent()) ? 'selected' : '' ?>>
            <?= htmlspecialchars($ev->getTitre()) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <!-- TABLE -->
  <div class="table-wrap">
    <?php if (empty($participations)): ?>
      <div class="empty">
        <div class="empty-icon">👥</div>
        Aucune participation enregistrée.
      </div>
    <?php else: ?>
    <table id="partTable">
      <thead>
        <tr>
          <th>#</th>
          <th class="sortable" data-col="1" onclick="sortTable(1)">Participant <span class="sort-arrow"></span></th>
          <th>Événement</th>
          <th class="sortable" data-col="3" onclick="sortTable(3)">Date <span class="sort-arrow"></span></th>
          <th class="sortable" data-col="4" onclick="sortTable(4)">Montant <span class="sort-arrow"></span></th>
          <th>Mode Paiement</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($participations as $p):
          $s = strtolower($p->getStatut());
          $badgeClass = match(true) {
              str_contains($s, 'confirm') => 'badge-confirme',
              str_contains($s, 'attente') => 'badge-attente',
              default                     => 'badge-annule',
          };

          // Calcul montant depuis le prix de l'événement
          $evObj   = $eventMap[$p->getIdEvent()] ?? null;
          $prix    = $evObj ? (float)$evObj->getPrix() : 0;
          $montant = $prix * $p->getNombrePlacesReservees();
          $isFree  = ($montant == 0);
          $mClass  = $isFree ? 'montant-free' : 'montant-cell';
          $mLabel  = $isFree ? 'Gratuit' : number_format($montant, 2) . ' TND';

          $evName  = $evObj ? $evObj->getTitre() : '—';
      ?>
        <tr data-statut="<?= htmlspecialchars($p->getStatut()) ?>"
            data-event="<?= $p->getIdEvent() ?>">
          <td><?= $p->getIdParticipation() ?></td>
          <td><b><?= htmlspecialchars($p->getNom() . ' ' . $p->getPrenom()) ?></b></td>
          <td><?= htmlspecialchars($evName) ?></td>
          <td><?= htmlspecialchars($p->getDateParticipation()) ?></td>
          <td><span class="<?= $mClass ?>"><?= $mLabel ?></span></td>
          <td><?= htmlspecialchars($p->getModePaiement()) ?></td>
          <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($p->getStatut()) ?></span></td>
          <td>
            <div class="actions">
              <a class="action-btn btn-edit"
                 href="updateParticipation.php?id=<?= $p->getIdParticipation() ?>"
                 title="Modifier">✏️</a>
              <a class="action-btn btn-delete"
                 href="listParticipations.php?delete=<?= $p->getIdParticipation() ?><?= $id_event_filter ? '&id_event=' . $id_event_filter : '' ?>"
                 onclick="return confirm('Supprimer cette participation ?')"
                 title="Supprimer">🗑️</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <!-- PAGINATION -->
  <div class="pagination" id="paginationBar">
    <div class="pagination-info" id="paginationInfo"></div>
    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
      <div class="pg-size-wrap">
        Lignes&nbsp;:
        <select id="pgSize" onchange="goPage(1)">
          <option value="10" selected>10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="9999">Tout</option>
        </select>
      </div>
      <div class="pagination-btns" id="pgButtons"></div>
    </div>
  </div>

</div>

<script>
const allRows = Array.from(document.querySelectorAll('#partTable tbody tr'));
let sortCol = -1, sortDir = 1, currentPage = 1;

function getCellText(row, col) {
  return row.cells[col]?.textContent.trim() ?? '';
}

function sortTable(col) {
  const ths = document.querySelectorAll('#partTable thead th');
  if (sortCol === col) { sortDir *= -1; } else { sortCol = col; sortDir = 1; }
  ths.forEach((th, i) => {
    th.classList.remove('asc','desc');
    if (i === col) th.classList.add(sortDir === 1 ? 'asc' : 'desc');
  });
  applyFilters();
}

function applyFilters() {
  const q      = document.getElementById('searchInput').value.toLowerCase();
  const statut = document.getElementById('filterStatut').value.toLowerCase();
  const evId   = document.getElementById('filterEvent').value;

  let visible = allRows.filter(row => {
    const text