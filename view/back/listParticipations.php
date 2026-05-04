<?php
require '../../controller/ParticipationController.php';
require '../../controller/EvenementController.php';
$ctrl   = new ParticipationController();
$evCtrl = new EvenementController();

// DELETE
if (isset($_GET['delete'])) {
    $ctrl->deleteParticipation($_GET['delete']);
    $redir = isset($_GET['id_event'])
        ? 'listParticipations.php?id_event=' . (int)$_GET['id_event'] . '&msg=deleted'
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
$eventMap  = [];
foreach ($allEvents as $ev) {
    $eventMap[$ev->getIdEvent()] = $ev;
}

$msg       = $_GET['msg'] ?? '';
$total     = count($participations);
$confirmes = count(array_filter($participations, fn($p) => strtolower($p->getStatut()) === 'confirmé'));
$enAttente = count(array_filter($participations, fn($p) => strtolower($p->getStatut()) === 'en attente'));
$annules   = count(array_filter($participations, fn($p) => strtolower($p->getStatut()) === 'annulé'));

$revenue = 0;
foreach ($participations as $p) {
    $evObj = $eventMap[$p->getIdEvent()] ?? null;
    if ($evObj) $revenue += (float)$evObj->getPrix() * $p->getNombrePlacesReservees();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participation List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
      .bo-logo{
        display:inline-flex;align-items:baseline;gap:2px;
        text-decoration:none;color:#111827;
        font-weight:900;font-size:18px;letter-spacing:-0.02em;
        margin-bottom:4px;
      }
      .bo-logo span{ color:#e63946; font-weight:900; }
      body.dark .bo-logo{ color:#f1f5f9; }
    </style>
</head>
<body>
<div class="admin-shell">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-name">SmartMeal</div>
        </div>
        <div class="section-label">Dashboard</div>
        <nav>
            <a href="listEvenements.php"><i class="bi bi-calendar-event-fill"></i> Events</a>
            <a href="listParticipations.php" class="active"><i class="bi bi-people-fill"></i> Participants</a>
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
                <a class="bo-logo" href="listEvenements.php">Smart Meal Planner<span>.</span></a>
                <span class="label">Participation Dashboard</span>
                <h1>Participant Management</h1>
                <p>Track registrations and manage statuses clearly and quickly.</p>
            </div>
            <div class="topbar-action">
                <a href="addParticipation.php<?= $id_event_filter ? '?id_event='.$id_event_filter : '' ?>" class="btn-primary">
                    <i class="bi bi-plus-lg"></i> New Participation
                </a>
            </div>
        </div>
        <div class="content-wrap">
            <div class="dashboard-banner">
                <h2>Welcome to <span>SmartMeal</span></h2>
                <p>Manage registrations, track revenues and keep an eye on every event from a modern dashboard.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fdecea;"><i class="bi bi-people-fill text-danger"></i></div>
                    <div>
                        <div class="stat-label">Total participations</div>
                        <div class="stat-value"><?= $total ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#e8f5e9;"><i class="bi bi-check-circle text-success"></i></div>
                    <div>
                        <div class="stat-label">Confirmed</div>
                        <div class="stat-value"><?= $confirmes ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fff8e1;"><i class="bi bi-hourglass-split text-warning"></i></div>
                    <div>
                        <div class="stat-label">Pending</div>
                        <div class="stat-value"><?= $enAttente ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#e8f5e9;"><i class="bi bi-currency-dollar text-success"></i></div>
                    <div>
                        <div class="stat-label">Revenue (TND)</div>
                        <div class="stat-value"><?= number_format($revenue, 2) ?></div>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <section class="section-card">
                    <div class="section-card-title">
                        <span><i class="bi bi-people-fill"></i> Recent Participations</span>
                        <a href="addParticipation.php<?= $id_event_filter ? '?id_event='.$id_event_filter : '' ?>" class="btn-action-primary"><i class="bi bi-plus-circle"></i> Add</a>
                    </div>

                    <?php if ($msg === 'added'): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-1"></i> Participation added successfully.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($msg === 'updated'): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-1"></i> Participation updated successfully.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($msg === 'deleted'): ?>
                        <div class="alert alert-warning alert-dismissible fade show">
                            <i class="fas fa-trash me-1"></i> Participation deleted.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($id_event_filter && $eventTitle): ?>
                        <div class="section-card mb-4" style="padding:18px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                                <div>
                                    <strong><?= htmlspecialchars($eventTitle) ?></strong>
                                    <p style="margin:6px 0 0;color:#6b7280;font-size:13px;">Participations linked to this event.</p>
                                </div>
                                <a href="listParticipations.php" class="btn-action-secondary">View all</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row g-2 mb-3">
                        <div class="col-md-5">
                            <input type="text" id="search-input" class="filter-input" placeholder="Search participant...">
                        </div>
                        <div class="col-md-4">
                            <select id="filter-statut" class="filter-input">
                                <option value="">All statuses</option>
                                <option value="confirmé">Confirmed</option>
                                <option value="en attente">Pending</option>
                                <option value="annulé">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="filter-event" class="filter-input">
                                <option value="">All events</option>
                                <?php foreach ($allEvents as $ev): ?>
                                    <option value="<?= $ev->getIdEvent() ?>" <?= ($id_event_filter == $ev->getIdEvent()) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ev->getTitre()) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover" id="table-participations">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Participant</th>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-participations">
                            <?php foreach ($participations as $p):
                                $s = strtolower($p->getStatut());
                                $badgeClass = str_contains($s,'confirm') ? 'confirme' : (str_contains($s,'attente') ? 'attente' : 'annule');

                                $evObj   = $eventMap[$p->getIdEvent()] ?? null;
                                $prix    = $evObj ? (float)$evObj->getPrix() : 0;
                                $montant = $prix * $p->getNombrePlacesReservees();
                                $isFree  = ($montant == 0);
                                $mLabel  = $isFree ? '<span class="prix-gratuit">Gratuit</span>' : '<span class="prix-payant">'.number_format($montant,2).' TND</span>';
                                $evName  = $evObj ? $evObj->getTitre() : '—';
                            ?>
                                <tr
                                    data-nom="<?= htmlspecialchars(strtolower($p->getNom().' '.$p->getPrenom()), ENT_QUOTES) ?>"
                                    data-statut="<?= htmlspecialchars(strtolower($p->getStatut()), ENT_QUOTES) ?>"
                                    data-event="<?= $p->getIdEvent() ?>">
                                    <td><?= $p->getIdParticipation() ?></td>
                                    <td><?= htmlspecialchars($p->getNom().' '.$p->getPrenom()) ?></td>
                                    <td><?= htmlspecialchars($evName) ?></td>
                                    <td><?= htmlspecialchars($p->getDateParticipation()) ?></td>
                                    <td><?= $mLabel ?></td>
                                    <td><?= htmlspecialchars($p->getModePaiement()) ?></td>
                                    <td><span class="badge-status <?= $badgeClass ?>"><?= htmlspecialchars($p->getStatut()) ?></span></td>
                                    <td>
                                        <a href="updateParticipation.php?id=<?= $p->getIdParticipation() ?>" class="btn-edit"><i class="bi bi-pencil"></i></a>
                                        <a href="listParticipations.php?delete=<?= $p->getIdParticipation() ?><?= $id_event_filter ? '&id_event='.$id_event_filter : '' ?>" class="btn-delete ms-2" onclick="return confirm('Delete this participation?')"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr id="no-result" style="display:none;">
                                <td colspan="8" class="text-center text-muted py-3">No participations found.</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination participations -->
                    <div id="pagination-participations" style="display:flex;align-items:center;justify-content:center;gap:6px;padding:16px 0;flex-wrap:wrap"></div>
                </section>
                <aside class="side-panel">
                    <div class="section-card">
                        <div class="section-card-title"><i class="bi bi-bar-chart-line"></i> Summary</div>
                        <ul>
                            <li><span>Total participations</span><span><?= $total ?></span></li>
                            <li><span>Confirmed</span><span><?= $confirmes ?></span></li>
                            <li><span>Pending</span><span><?= $enAttente ?></span></li>
                            <li><span>Revenue</span><span><?= number_format($revenue,2) ?> TND</span></li>
                        </ul>
                    </div>
                    <div class="section-card">
                        <div class="section-card-title"><i class="bi bi-lightning-charge"></i> Quick Actions</div>
                        <a href="addParticipation.php<?= $id_event_filter ? '?id_event='.$id_event_filter : '' ?>" class="btn-action-primary"><i class="bi bi-plus-circle"></i> Add Participation</a>
                        <a href="listEvenements.php" class="btn-action-secondary"><i class="bi bi-calendar-event"></i> View Events</a>
                        <a href="listParticipations.php" class="btn-action-secondary"><i class="bi bi-arrow-clockwise"></i> Reset</a>
                        <div style="display:grid;gap:10px;">
                            <button type="button" class="btn-action-secondary" onclick="exportCSV()"><i class="bi bi-download"></i> Export CSV</button>
                            <button type="button" class="btn-action-secondary" onclick="exportPDFParticipations()"><i class="bi bi-filetype-pdf"></i> Export PDF</button>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
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

var tbody = document.getElementById('tbody-participations');
Array.from(tbody.querySelectorAll('tr:not(#no-result)')).forEach(function(r,i){ r.dataset.index=i; });

// ── Pagination participations (8 par page) ───────────────────────────
var ROWS_PER_PAGE_P = 8;
var currentPageP    = 1;

function renderPaginationP() {
  var allRows = Array.from(tbody.querySelectorAll('tr:not(#no-result)'));
  var visible = allRows.filter(function(r){ return r.dataset.filtered !== '0'; });
  var totalPages = Math.max(1, Math.ceil(visible.length / ROWS_PER_PAGE_P));
  if (currentPageP > totalPages) currentPageP = totalPages;

  // Hide all, show only current page
  allRows.forEach(function(r){ r.style.display = 'none'; });
  visible.forEach(function(r, i) {
    if (i >= (currentPageP-1)*ROWS_PER_PAGE_P && i < currentPageP*ROWS_PER_PAGE_P) {
      r.style.display = '';
    }
  });

  var noResult = document.getElementById('no-result');
  if (noResult) noResult.style.display = visible.length === 0 ? '' : 'none';

  var pag = document.getElementById('pagination-participations');
  if (!pag) return;
  if (totalPages <= 1) {
    pag.innerHTML = '<span style="font-size:12px;color:var(--muted)">'+visible.length+' participation(s)</span>';
    return;
  }
  var html = '<button class="pag-btn" '+(currentPageP<=1?'disabled':'')+' onclick="goPageP('+(currentPageP-1)+')">‹</button>';
  for (var i = 1; i <= totalPages; i++) {
    if (i===1||i===totalPages||(i>=currentPageP-1&&i<=currentPageP+1)) {
      html += '<button class="pag-btn'+(i===currentPageP?' pag-active':'')+'" onclick="goPageP('+i+')">'+i+'</button>';
    } else if (i===currentPageP-2||i===currentPageP+2) {
      html += '<span style="color:var(--muted);padding:0 4px">…</span>';
    }
  }
  html += '<button class="pag-btn" '+(currentPageP>=totalPages?'disabled':'')+' onclick="goPageP('+(currentPageP+1)+')">›</button>';
  html += '<span style="font-size:12px;color:var(--muted);margin-left:6px">Page '+currentPageP+'/'+totalPages+' · '+visible.length+' résultats</span>';
  pag.innerHTML = html;
}

function goPageP(p) { currentPageP = p; renderPaginationP(); }

document.addEventListener('DOMContentLoaded', function() {
  renderPaginationP();
  ['filter-search','filter-statut','filter-event'].forEach(function(id){
    var el = document.getElementById(id);
    if (el) el.addEventListener('input',  function(){ currentPageP=1; renderPaginationP(); });
    if (el) el.addEventListener('change', function(){ currentPageP=1; renderPaginationP(); });
  });
});

function exportPDFParticipations() {
    var rows = Array.from(tbody.querySelectorAll('tr:not(#no-result)')).filter(function(r){ return r.style.display !== 'none'; });
    var headers = ['ID','Participant','Event','Date','Amount','Payment Method','Status'];
    var body = rows.map(function(row){
        var cols = [];
        for (var i = 0; i < row.cells.length - 1; i++) cols.push(row.cells[i].textContent.trim());
        return cols;
    });

    var jspdf = window.jspdf && window.jspdf.jsPDF ? window.jspdf.jsPDF : null;
    if (!jspdf) return;
    var doc = new jspdf({ orientation: 'landscape', unit: 'pt', format: 'a4' });
    var title = 'Participations';
    var dateStr = new Date().toLocaleString();

    doc.setFillColor(230, 57, 70);
    doc.rect(0, 0, doc.internal.pageSize.getWidth(), 64, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(18);
    doc.text(title, 40, 38);
    doc.setFontSize(11);
    doc.text('Export date: ' + dateStr, doc.internal.pageSize.getWidth() - 40, 38, { align: 'right' });

    doc.autoTable({
        head: [headers],
        body: body,
        startY: 84,
        styles: { fontSize: 10, cellPadding: 6, lineColor: [51, 65, 85], lineWidth: 0.2 },
        headStyles: { fillColor: [230, 57, 70], textColor: 255, fontStyle: 'bold' },
        alternateRowStyles: { fillColor: [248, 250, 252] }
    });

    doc.save('participations_' + new Date().toISOString().slice(0,10) + '.pdf');
}

function applyFilters() {
    var q      = document.getElementById('search-input').value.toLowerCase().trim();
    var statut = document.getElementById('filter-statut').value.toLowerCase();
    var evId   = document.getElementById('filter-event').value;
    var rows   = Array.from(tbody.querySelectorAll('tr:not(#no-result)'));

    rows.forEach(function(row) {
        var nom  = row.dataset.nom    || '';
        var st   = row.dataset.statut || '';
        var ev   = row.dataset.event  || '';
        var show = (!q      || nom.includes(q))
                && (!statut || st === statut)
                && (!evId   || ev === evId);
        row.dataset.filtered = show ? '1' : '0';
    });

    currentPageP = 1;
    renderPaginationP();
}

function annulerFiltres() {
    document.getElementById('search-input').value   = '';
    document.getElementById('filter-statut').value  = '';
    document.getElementById('filter-event').value   = '';
    applyFilters();
}

function exportCSV() {
    var headers = ['ID','Participant','Event','Date','Amount','Payment Method','Status'];
    var rows = Array.from(tbody.querySelectorAll('tr:not(#no-result)')).filter(function(r){ return r.style.display !== 'none'; });
    var esc  = function(v){ return '"'+v.replace(/"/g,'""')+'"'; };
    var lines = [headers.map(esc).join(',')];
    rows.forEach(function(row){
        var cols = [];
        for (var i = 0; i < row.cells.length - 1; i++) cols.push(esc(row.cells[i].textContent.trim()));
        lines.push(cols.join(','));
    });
    var a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob(['\uFEFF'+lines.join('\r\n')],{type:'text/csv;charset=utf-8'}));
    a.download = 'participations_'+new Date().toISOString().slice(0,10)+'.csv';
    a.click();
}

document.getElementById('search-input').addEventListener('input', applyFilters);
document.getElementById('filter-statut').addEventListener('change', applyFilters);
document.getElementById('filter-event').addEventListener('change', applyFilters);
</script>
</body>
</html>