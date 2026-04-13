<?php
require '../../controller/EvenementController.php';
$controller = new EvenementController();

if (isset($_GET['delete'])) {
    $controller->deleteEvenement($_GET['delete']);
    header('Location: listEvenements.php?msg=deleted');
    exit;
}

$evenements = $controller->listEvenements();
$msg = $_GET['msg'] ?? '';

$total    = count($evenements);
$actifs   = count(array_filter($evenements, fn($e) => str_contains(strtolower($e->getStatut()), 'actif')));
$gratuits = count(array_filter($evenements, fn($e) => (float)$e->getPrix() == 0));
$termines = count(array_filter($evenements, fn($e) => str_contains(strtolower($e->getStatut()), 'termin')));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Liste des Événements</title>
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

.page-hero{background:linear-gradient(135deg,#7f1d1d 0%,#b91c1c 100%);padding:36px 32px 28px;text-align:center;color:#fff}
.page-hero h1{font-size:24px;font-weight:600;margin-bottom:6px}
.page-hero p{font-size:14px;color:rgba(255,255,255,0.6);margin-bottom:28px}

.stats-grid{display:flex;gap:16px;justify-content:center;flex-wrap:wrap}
.stat-card{background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);border-radius:14px;padding:14px 28px;min-width:130px;text-align:center;backdrop-filter:blur(4px)}
.stat-card .stat-num{font-size:28px;font-weight:700;color:#fff;line-height:1}
.stat-card .stat-label{font-size:11px;color:rgba(255,255,255,0.65);margin-top:5px;text-transform:uppercase;letter-spacing:.6px}
.stat-card.s-actif .stat-num{color:#fca5a5}
.stat-card.s-gratuit .stat-num{color:#86efac}
.stat-card.s-termine .stat-num{color:#d1d5db}

.container{max-width:1200px;margin:0 auto;padding:32px 24px 60px}

.alert{padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:14px;font-weight:500}
.alert-success{background:#fce8e8;color:#7f1d1d;border:1px solid #f09595}
.alert-danger{background:#fff0e0;color:#7a4000;border:1px solid #fad99a}

.toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px}
.filters{display:flex;gap:10px;flex-wrap:wrap;align-items:center}

.search-wrap{position:relative}
.search-wrap input{padding:9px 14px 9px 36px;border:1px solid #f7c1c1;border-radius:10px;background:#fff;color:#1a0505;font-size:14px;width:220px;outline:none;font-family:'Inter',sans-serif;transition:border-color .2s}
.search-wrap input:focus{border-color:#b91c1c;box-shadow:0 0 0 3px rgba(185,28,28,0.1)}
.search-wrap input::placeholder{color:#c9a0a0}
.search-wrap::before{content:'🔍';font-size:13px;position:absolute;left:11px;top:50%;transform:translateY(-50%);pointer-events:none}

.filter-select{padding:9px 14px;border:1px solid #f7c1c1;border-radius:10px;background:#fff;color:#1a0505;font-size:14px;outline:none;font-family:'Inter',sans-serif;cursor:pointer;transition:border-color .2s}
.filter-select:focus{border-color:#b91c1c;box-shadow:0 0 0 3px rgba(185,28,28,0.1)}

.btn-actions{display:flex;gap:10px;flex-wrap:wrap}
.btn-new{display:inline-flex;align-items:center;gap:6px;background:#b91c1c;color:#fff;border:none;border-radius:10px;padding:10px 20px;font-size:14px;font-weight:500;font-family:'Inter',sans-serif;cursor:pointer;text-decoration:none;transition:background .15s}
.btn-new:hover{background:#991b1b}
.btn-csv{display:inline-flex;align-items:center;gap:6px;background:#fff;color:#7f1d1d;border:1.5px solid #f7c1c1;border-radius:10px;padding:9px 18px;font-size:14px;font-weight:500;font-family:'Inter',sans-serif;cursor:pointer;transition:all .15s}
.btn-csv:hover{background:#fce8e8;border-color:#f09595}

.table-wrap{background:#fff;border:1px solid #fde8e8;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(185,28,28,0.06)}

table{width:100%;border-collapse:collapse}
thead{background:linear-gradient(135deg,#7f1d1d 0%,#b91c1c 100%)}
thead th{padding:14px 12px;text-align:center;font-size:13px;font-weight:500;color:#fff;white-space:nowrap;user-select:none}
thead th.sortable{cursor:pointer;transition:background .15s}
thead th.sortable:hover{background:rgba(255,255,255,0.12)}
thead th .sort-arrow{display:inline-block;margin-left:5px;font-size:10px;opacity:.5;transition:opacity .15s}
thead th.asc .sort-arrow::after{content:'▲';opacity:1}
thead th.desc .sort-arrow::after{content:'▼';opacity:1}
thead th:not(.asc):not(.desc) .sort-arrow::after{content:'⇅';opacity:.4}

tbody tr{border-bottom:1px solid #fce8e8;transition:background .15s}
tbody tr:last-child{border-bottom:none}
tbody tr:hover{background:#fff5f5}
td{padding:13px 12px;text-align:center;font-size:13px;color:#1a0505}
td b{font-weight:600;color:#7f1d1d}

.badge{padding:4px 12px;border-radius:20px;font-size:11px;font-weight:600}
.badge-active{background:#fce8e8;color:#7f1d1d;border:1px solid #f09595}
.badge-annule{background:#fff0e0;color:#7a4000;border:1px solid #fad99a}
.badge-termine{background:#f0f0f0;color:#555;border:1px solid #ccc}

.price-free{color:#15803d;font-weight:600}
.price-paid{color:#b91c1c;font-weight:600}

.actions{display:flex;justify-content:center;align-items:center;gap:8px}
.action-btn{width:36px;height:36px;border:none;border-radius:10px;cursor:pointer;font-size:15px;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;transition:all .15s;box-shadow:0 2px 6px rgba(0,0,0,0.1)}
.action-btn:hover{transform:translateY(-2px);opacity:.9}
.btn-edit{background:#f7c1c1;color:#7f1d1d}
.btn-edit:hover{background:#f09595}
.btn-view{background:#fce8e8;color:#b91c1c}
.btn-view:hover{background:#f7c1c1}
.btn-delete{background:#b91c1c;color:#fff}
.btn-delete:hover{background:#991b1b}

.empty{text-align:center;padding:50px 20px;color:#9a3535;font-size:14px}
.empty-icon{font-size:36px;margin-bottom:10px}

.pagination{display:flex;align-items:center;justify-content:space-between;margin-top:20px;flex-wrap:wrap;gap:12px}
.pagination-info{font-size:13px;color:#9a3535}
.pagination-btns{display:flex;gap:6px;flex-wrap:wrap;align-items:center}
.pg-btn{width:34px;height:34px;border:1.5px solid #f7c1c1;border-radius:8px;background:#fff;color:#7f1d1d;font-size:13px;font-weight:500;cursor:pointer;font-family:'Inter',sans-serif;transition:all .15s;display:inline-flex;align-items:center;justify-content:center}
.pg-btn:hover{background:#fce8e8;border-color:#f09595}
.pg-btn.active{background:#b91c1c;color:#fff;border-color:#b91c1c}
.pg-btn:disabled{opacity:.35;cursor:default}

.pg-size-wrap{display:flex;align-items:center;gap:8px;font-size:13px;color:#9a3535}
.pg-size-wrap select{padding:5px 10px;border:1.5px solid #f7c1c1;border-radius:8px;background:#fff;color:#1a0505;font-size:13px;outline:none;font-family:'Inter',sans-serif;cursor:pointer}

@media(max-width:768px){
  nav{padding:0 16px}
  .page-hero{padding:24px 16px 20px}
  .container{padding:20px 16px 40px}
  .toolbar{flex-direction:column;align-items:flex-start}
  .search-wrap input{width:100%}
  .table-wrap{overflow-x:auto}
  .stats-grid{gap:10px}
  .stat-card{padding:12px 18px;min-width:110px}
  .stat-card .stat-num{font-size:22px}
}
</style>
</head>
<body>

<nav>
  <a href="interfaceevent.php" class="logo">Event <span>Management</span></a>
  <div class="nav-links">
    <a href="interfaceevent.php">Événements</a>
    <a href="#about">À propos</a>
    <a href="#contact">Contact</a>
  </div>
</nav>

<div class="page-hero">
  <h1>📋 Liste des Événements</h1>
  <p>Gérez tous vos événements depuis un seul endroit</p>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-num"><?= $total ?></div>
      <div class="stat-label">Total</div>
    </div>
    <div class="stat-card s-actif">
      <div class="stat-num"><?= $actifs ?></div>
      <div class="stat-label">Actifs</div>
    </div>
    <div class="stat-card s-gratuit">
      <div class="stat-num"><?= $gratuits ?></div>
      <div class="stat-label">Gratuits</div>
    </div>
    <div class="stat-card s-termine">
      <div class="stat-num"><?= $termines ?></div>
      <div class="stat-label">Terminés</div>
    </div>
  </div>
</div>

<div class="container">

  <?php if ($msg === 'added'):   ?><div class="alert alert-success">✅ Événement ajouté avec succès.</div><?php endif; ?>
  <?php if ($msg === 'updated'): ?><div class="alert alert-success">✅ Événement mis à jour avec succès.</div><?php endif; ?>
  <?php if ($msg === 'deleted'): ?><div class="alert alert-danger">🗑️ Événement supprimé.</div><?php endif; ?>

  <div class="toolbar">
    <div class="filters">
      <div class="search-wrap">
        <input type="text" id="searchInput" placeholder="Rechercher..." oninput="applyFilters()">
      </div>
      <select class="filter-select" id="filterType" onchange="applyFilters()">
        <option value="">— Tous les types —</option>
        <?php
          $types = array_unique(array_map(fn($e) => $e->getType(), $evenements));
          sort($types);
          foreach ($types as $t): ?>
            <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
        <?php endforeach; ?>
      </select>
      <select class="filter-select" id="filterStatut" onchange="applyFilters()">
        <option value="">— Tous les statuts —</option>
        <?php
          $statuts = array_unique(array_map(fn($e) => $e->getStatut(), $evenements));
          sort($statuts);
          foreach ($statuts as $s): ?>
            <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="btn-actions">
      <button class="btn-csv" onclick="exportCSV()">⬇ Export CSV</button>
      <a class="btn-new" href="addEvenement.php">+ Nouvel Événement</a>
    </div>
  </div>

  <div class="table-wrap">
    <?php if (empty($evenements)): ?>
      <div class="empty">
        <div class="empty-icon">📅</div>
        Aucun événement enregistré.
      </div>
    <?php else: ?>
    <table id="eventsTable">
      <thead>
        <tr>
          <th>#</th>
          <th class="sortable" data-col="1" onclick="sortTable(1)">Titre <span class="sort-arrow"></span></th>
          <th>Type</th>
          <th>Lieu</th>
          <th class="sortable" data-col="4" onclick="sortTable(4)">Début <span class="sort-arrow"></span></th>
          <th>Fin</th>
          <th>Capacité</th>
          <th class="sortable" data-col="7" onclick="sortTable(7)">Prix <span class="sort-arrow"></span></th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($evenements as $e):
          $s = strtolower($e->getStatut());
          $badge = match(true) {
              str_contains($s, 'actif')  => 'badge-active',
              str_contains($s, 'annul')  => 'badge-annule',
              str_contains($s, 'termin') => 'badge-termine',
              default => 'badge-active',
          };
          $isFree = (float)$e->getPrix() == 0;
          $priceClass = $isFree ? 'price-free' : 'price-paid';
          $priceLabel = $isFree ? 'Gratuit' : number_format($e->getPrix(), 2) . ' TND';
      ?>
        <tr
          data-type="<?= htmlspecialchars($e->getType()) ?>"
          data-statut="<?= htmlspecialchars($e->getStatut()) ?>"
          data-prix="<?= (float)$e->getPrix() ?>">
          <td><?= htmlspecialchars($e->getIdEvent()) ?></td>
          <td><b><?= htmlspecialchars($e->getTitre()) ?></b></td>
          <td><?= htmlspecialchars($e->getType()) ?></td>
          <td><?= htmlspecialchars($e->getLieu()) ?></td>
          <td><?= htmlspecialchars($e->getDateDebut()) ?></td>
          <td><?= htmlspecialchars($e->getDateFin()) ?></td>
          <td><?= htmlspecialchars($e->getCapaciteMax()) ?></td>
          <td><span class="<?= $priceClass ?>"><?= $priceLabel ?></span></td>
          <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($e->getStatut()) ?></span></td>
          <td>
            <div class="actions">
              <a class="action-btn btn-edit"
                 href="updateEvenement.php?id=<?= $e->getIdEvent() ?>"
                 title="Modifier">✏️</a>
              <a class="action-btn btn-view"
                 href="listParticipations.php?id_event=<?= $e->getIdEvent() ?>"
                 title="Participants">👥</a>
              <a class="action-btn btn-delete"
                 href="listEvenements.php?delete=<?= $e->getIdEvent() ?>"
                 onclick="return confirm('Supprimer cet événement ?')"
                 title="Supprimer">🗑️</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

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
const allRows = Array.from(document.querySelectorAll('#eventsTable tbody tr'));
let sortCol = -1, sortDir = 1;
let currentPage = 1;

function getCellText(row, col) {
  return row.cells[col]?.textContent.trim() ?? '';
}

function sortTable(col) {
  const ths = document.querySelectorAll('#eventsTable thead th');
  if (sortCol === col) { sortDir *= -1; } else { sortCol = col; sortDir = 1; }
  ths.forEach((th, i) => {
    th.classList.remove('asc','desc');
    if (i === col) th.classList.add(sortDir === 1 ? 'asc' : 'desc');
  });
  applyFilters();
}

function applyFilters() {
  const q = document.getElementById('searchInput').value.toLowerCase();
  const type = document.getElementById('filterType').value.toLowerCase();
  const statut = document.getElementById('filterStatut').value.toLowerCase();

  let visible = allRows.filter(row => {
    const text = row.textContent.toLowerCase();
    const rowType = (row.dataset.type || '').toLowerCase();
    const rowStatut = (row.dataset.statut || '').toLowerCase();
    return (!q || text.includes(q))
      && (!type || rowType === type)
      && (!statut || rowStatut === statut);
  });

  if (sortCol >= 0) {
    visible.sort((a, b) => {
      let va = getCellText(a, sortCol);
      let vb = getCellText(b, sortCol);
      const na = parseFloat(va.replace(/[^\d.]/g, ''));
      const nb = parseFloat(vb.replace(/[^\d.]/g, ''));
      if (!isNaN(na) && !isNaN(nb)) return (na - nb) * sortDir;
      return va.localeCompare(vb, 'fr') * sortDir;
    });
  }

  renderPage(visible, currentPage);
}

function renderPage(rows, page) {
  const pgSize = parseInt(document.getElementById('pgSize').value);
  const total = rows.length;
  const totalPages = Math.max(1, Math.ceil(total / pgSize));
  if (page > totalPages) page = totalPages;
  currentPage = page;

  const start = (page - 1) * pgSize;
  const end = Math.min(start + pgSize, total);

  allRows.forEach(r => r.style.display = 'none');
  rows.slice(start, end).forEach(r => r.style.display = '');

  document.getElementById('paginationInfo').textContent =
    total === 0 ? 'Aucun résultat'
    : `Affichage ${start + 1}–${end} sur ${total} événement${total > 1 ? 's' : ''}`;

  const container = document.getElementById('pgButtons');
  container.innerHTML = '';

  const mkBtn = (label, p, disabled, active) => {
    const b = document.createElement('button');
    b.className = 'pg-btn' + (active ? ' active' : '');
    b.textContent = label;
    b.disabled = disabled;
    b.onclick = () => goPage(p);
    return b;
  };

  container.appendChild(mkBtn('‹', page - 1, page <= 1, false));

  let pages = [];
  if (totalPages <= 7) {
    for (let i = 1; i <= totalPages; i++) pages.push(i);
  } else {
    pages = [1];
    if (page > 3) pages.push('…');
    for (let i = Math.max(2, page-1); i <= Math.min(totalPages-1, page+1); i++) pages.push(i);
    if (page < totalPages - 2) pages.push('…');
    pages.push(totalPages);
  }

  pages.forEach(p => {
    if (p === '…') {
      const span = document.createElement('span');
      span.textContent = '…';
      span.style.cssText = 'padding:0 4px;color:#9a3535;font-size:13px;line-height:34px';
      container.appendChild(span);
    } else {
      container.appendChild(mkBtn(p, p, false, p === page));
    }
  });

  container.appendChild(mkBtn('›', page + 1, page >= totalPages, false));
}

function goPage(p) {
  currentPage = p;
  applyFilters();
}

function exportCSV() {
  const headers = ['#','Titre','Type','Lieu','Début','Fin','Capacité','Prix','Statut'];
  const visibleRows = allRows.filter(r => r.style.display !== 'none' || true);

  const q = document.getElementById('searchInput').value.toLowerCase();
  const type = document.getElementById('filterType').value.toLowerCase();
  const statut = document.getElementById('filterStatut').value.toLowerCase();

  const rows = allRows.filter(row => {
    const text = row.textContent.toLowerCase();
    const rowType = (row.dataset.type || '').toLowerCase();
    const rowStatut = (row.dataset.statut || '').toLowerCase();
    return (!q || text.includes(q))
      && (!type || rowType === type)
      && (!statut || rowStatut === statut);
  });

  const escape = v => '"' + v.replace(/"/g, '""') + '"';
  const lines = [headers.map(escape).join(',')];

  rows.forEach(row => {
    const cols = [];
    for (let i = 0; i < row.cells.length - 1; i++) {
      cols.push(escape(row.cells[i].textContent.trim()));
    }
    lines.push(cols.join(','));
  });

  const blob = new Blob(['\uFEFF' + lines.join('\r\n')], {type:'text/csv;charset=utf-8'});
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'evenements_' + new Date().toISOString().slice(0,10) + '.csv';
  a.click();
}

applyFilters();
</script>

</body>
</html>