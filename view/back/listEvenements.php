<?php
require_once __DIR__ . '/../../controller/EvenementController.php';

$controller = new EvenementController();

if (isset($_GET['delete'])) {
    $controller->deleteEvenement($_GET['delete']);
    header('Location: listEvenements.php?msg=deleted');
    exit;
}

$evenements = $controller->listEvenements();
$msg = $_GET['msg'] ?? '';

$participationCounts = [];
foreach ($evenements as $e) {
    $participationCounts[$e->getIdEvent()] = $controller->countParticipationsByEvent($e->getIdEvent());
}

$totalEvents      = count($evenements);
$activeEvents     = count(array_filter($evenements, function($e) {
    return strpos(strtolower($e->getStatut()), 'actif') !== false;
}));
$cancelledEvents  = count(array_filter($evenements, function($e) {
    return strpos(strtolower($e->getStatut()), 'annul') !== false;
}));
$finishedEvents   = count(array_filter($evenements, function($e) {
    return strpos(strtolower($e->getStatut()), 'termin') !== false;
}));
$fullEvents       = count(array_filter($evenements, function($e) use ($participationCounts) {
    $cap = (int)$e->getCapaciteMax();
    $cnt = $participationCounts[$e->getIdEvent()] ?? 0;
    return $cap > 0 && $cnt >= $cap;
}));
$upcomingEvents   = count(array_filter($evenements, function($e) {
    return strtotime($e->getDateDebut()) > time();
}));
$totalCapacity    = array_sum(array_map(function($e) {
    return (int)$e->getCapaciteMax();
}, $evenements));
$withParticipants = count(array_filter($evenements, function($e) use ($participationCounts) {
    return ($participationCounts[$e->getIdEvent()] ?? 0) > 0;
}));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
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
      /* Advanced stats panel (hidden by default) */
      .adv-stats-wrap {
        display: block;
        overflow: hidden;
        max-height: 0;
        opacity: 0;
        transform: translateY(-8px);
        transition: max-height 650ms ease, opacity 450ms ease, transform 450ms ease;
        margin-bottom: 0;
      }
      .adv-stats-wrap.open {
        max-height: 1000px;
        opacity: 1;
        transform: translateY(0);
        margin-bottom: 28px;
      }
      .adv-stats-panel {
        position: relative;
        border-radius: 20px;
        padding: 32px;
        background: #fff;
        color: #111827;
        border: 1px solid #e5e5e5;
        overflow: hidden;
        box-shadow: 0 8px 40px rgba(0,0,0,0.08);
      }
      .adv-stats-panel::before {
        content: "";
        position: absolute;
        inset: -120px;
        background:
          radial-gradient(circle at 20% 30%, rgba(230,57,70,0.16), rgba(230,57,70,0) 55%),
          radial-gradient(circle at 80% 60%, rgba(13,110,253,0.12), rgba(13,110,253,0) 52%),
          radial-gradient(circle at 40% 90%, rgba(245,158,11,0.12), rgba(245,158,11,0) 60%);
        filter: blur(0px);
        animation: advGlow 6s ease-in-out infinite alternate;
        pointer-events: none;
      }
      @keyframes advGlow {
        0% { transform: translate3d(-10px, -12px, 0) scale(1); opacity: .9; }
        100% { transform: translate3d(14px, 10px, 0) scale(1.06); opacity: 1; }
      }
      .adv-stats-head {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
      }
      .adv-stats-title {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
        letter-spacing: .02em;
      }
      .adv-stats-sub {
        color: #6b7280;
        font-size: 13px;
      }
      .adv-grid {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 16px;
      }
      .adv-chart {
        background: #fff;
        border: 1px solid #f0f0f0;
        border-radius: 14px;
        padding: 16px;
        min-height: 280px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
      }
      .adv-chart canvas { width: 100% !important; height: 240px !important; }
      .col-4 { grid-column: span 4; }
      .adv-counters {
        position: relative;
        z-index: 1;
        margin-top: 18px;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
      }
      .counter-card {
        background: #fff;
        border: 1px solid #f0f0f0;
        border-radius: 14px;
        padding: 20px;
        display: grid;
        gap: 10px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
      }
      .counter-ico{
        width: 34px;height:34px;border-radius:10px;display:grid;place-items:center;
        font-size: 14px;
      }
      .counter-val {
        font-size: 36px;
        font-weight: 700;
        letter-spacing: -0.02em;
      }
      .counter-lbl {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.10em;
        text-transform: uppercase;
        color: #888;
      }
      .counter-red .counter-ico{ background: rgba(230,57,70,0.12); color:#e63946; }
      .counter-red .counter-val{ color:#e63946; }
      .counter-blue .counter-ico{ background: rgba(13,110,253,0.12); color:#0d6efd; }
      .counter-blue .counter-val{ color:#0d6efd; }
      .counter-green .counter-ico{ background: rgba(25,135,84,0.12); color:#198754; }
      .counter-green .counter-val{ color:#198754; }
      .counter-orange .counter-ico{ background: rgba(245,158,11,0.16); color:#f59e0b; }
      .counter-orange .counter-val{ color:#f59e0b; }
      @media (max-width: 980px) {
        .col-4 { grid-column: span 12; }
        .adv-counters { grid-template-columns: 1fr 1fr; }
      }
      @media (max-width: 560px) {
        .adv-counters { grid-template-columns: 1fr; }
      }

      /* FAB */
      .stats-fab {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 60px;
        height: 60px;
        border-radius: 999px;
        background: #e63946;
        color: #fff;
        border: none;
        display: grid;
        place-items: center;
        cursor: pointer;
        box-shadow: 0 4px 20px rgba(230,57,70,0.5);
        transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
        z-index: 3000;
      }
      .stats-fab:hover {
        transform: scale(1.12);
        box-shadow: 0 8px 26px rgba(230,57,70,0.75);
      }
      @keyframes pulse-ring {
        0% { box-shadow: 0 0 0 0 rgba(230,57,70,0.6); }
        70% { box-shadow: 0 0 0 18px rgba(230,57,70,0); }
        100% { box-shadow: 0 0 0 0 rgba(230,57,70,0); }
      }
      .stats-fab.pulse { animation: pulse-ring 2s infinite; }
      .stats-fab[data-tip]::after {
        content: attr(data-tip);
        position: absolute;
        right: 72px;
        bottom: 50%;
        transform: translateY(50%);
        background: rgba(15, 23, 42, 0.92);
        color: #fff;
        padding: 8px 10px;
        border-radius: 10px;
        font-size: 12px;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity .15s ease, transform .15s ease;
      }
      .stats-fab:hover::after {
        opacity: 1;
        transform: translateY(50%) translateX(-2px);
      }
    </style>
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
            <a href="listPromoCodes.php"><i class="bi bi-ticket-perforated-fill"></i> Codes Promo</a>
            <a href="listCommentaires.php"><i class="bi bi-chat-dots-fill"></i> Commentaires</a>
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
                <span class="label">Event Dashboard</span>
                <h1>Events overview</h1>
                <p>Manage your events, check capacity and jump into participant lists instantly.</p>
            </div>
            <div class="topbar-action">
                <a href="addEvenement.php" class="btn-primary"><i class="bi bi-plus-lg"></i> Add Event</a>
            </div>
        </div>
        <div class="content-wrap">
            <div class="dashboard-banner">
                <h2>Welcome back to <span>SmartMeal</span></h2>
                <p>Track upcoming events, capacity status and participant engagement from a single dashboard.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-label">Total events</div>
                    <div class="stat-value"><?= $totalEvents ?></div>
                </div>
                <div class="stat-card success">
                    <div class="stat-label">Active events</div>
                    <div class="stat-value"><?= $activeEvents ?></div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-label">Cancelled events</div>
                    <div class="stat-value"><?= $cancelledEvents ?></div>
                </div>
                <div class="stat-card danger">
                    <div class="stat-label">Finished events</div>
                    <div class="stat-value"><?= $finishedEvents ?></div>
                </div>
            </div>

            <div class="adv-stats-wrap" id="advStatsWrap" style="display:none;">
              <div class="adv-stats-panel" id="advStatsPanel">
                <div class="adv-stats-head">
                  <div>
                    <h2 class="adv-stats-title">📊 Statistiques</h2>
                    <div class="adv-stats-sub">Répartition par type • Participations • Évolution mensuelle</div>
                  </div>
                  <div class="adv-stats-sub">Mise à jour automatique</div>
                </div>

                <div class="adv-grid">
                  <div class="adv-chart col-4">
                    <canvas id="chart-repartition-type"></canvas>
                  </div>
                  <div class="adv-chart col-4">
                    <canvas id="chart-participations-event"></canvas>
                  </div>
                  <div class="adv-chart col-4">
                    <canvas id="chart-participations-month"></canvas>
                  </div>
                </div>

                <div class="adv-counters">
                  <div class="counter-card counter-red">
                    <div class="counter-ico"><i class="fas fa-calendar"></i></div>
                    <div class="counter-val" data-counter="totalEvents">0</div>
                    <div class="counter-lbl">Total Events</div>
                  </div>
                  <div class="counter-card counter-blue">
                    <div class="counter-ico"><i class="fas fa-users"></i></div>
                    <div class="counter-val" data-counter="totalParticipations">0</div>
                    <div class="counter-lbl">Total Participations</div>
                  </div>
                  <div class="counter-card counter-green">
                    <div class="counter-ico"><i class="fas fa-coins"></i></div>
                    <div class="counter-val" data-counter="totalRevenue">0</div>
                    <div class="counter-lbl">Total Revenue TND</div>
                  </div>
                  <div class="counter-card counter-orange">
                    <div class="counter-ico"><i class="fas fa-percent"></i></div>
                    <div class="counter-val" data-counter="avgOccupancy">0</div>
                    <div class="counter-lbl">Average Occupancy %</div>
                  </div>
                </div>
              </div>
            </div>

            <button class="stats-fab pulse" id="statsFab" type="button" aria-label="Statistiques avancées" data-tip="Statistiques avancées">
              <i class="fas fa-chart-bar" id="statsFabIcon"></i>
            </button>

            <div class="dashboard-grid">
                <section class="section-card">
                    <div class="section-card-title">
                        <span><i class="bi bi-calendar3"></i> Event table</span>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <button type="button" class="btn-soft" onclick="exportCSV()"><i class="bi bi-download"></i> Export CSV</button>
                            <button type="button" class="btn-soft" onclick="exportPDFEvenements()"><i class="bi bi-filetype-pdf"></i> Export PDF</button>
                            <a href="addEvenement.php" class="btn-action-primary"><i class="bi bi-plus-circle"></i> New event</a>
                        </div>
                    </div>

                    <div class="filters-row">
                        <input type="text" id="search-event" class="filter-input" placeholder="Search event...">
                        <select id="filter-type" class="filter-input">
                            <option value="">All types</option>
                            <?php
                            $types = array_unique(array_map(fn($e) => $e->getType(), $evenements));
                            sort($types);
                            foreach ($types as $t): ?>
                                <option value="<?= htmlspecialchars(strtolower($t)) ?>"><?= htmlspecialchars($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="filter-statut" class="filter-input">
                            <option value="">All statuses</option>
                            <?php
                            $statuts = array_unique(array_map(fn($e) => $e->getStatut(), $evenements));
                            sort($statuts);
                            foreach ($statuts as $s): ?>
                                <option value="<?= htmlspecialchars(strtolower($s)) ?>"><?= htmlspecialchars($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="filter-tri" class="filter-input">
                            <option value="">— Sort —</option>
                            <option value="title-asc">Title A → Z</option>
                            <option value="title-desc">Title Z → A</option>
                            <option value="price-asc">Price low ↑</option>
                            <option value="price-desc">Price high ↓</option>
                            <option value="date-asc">Date soon ↑</option>
                            <option value="date-desc">Date later ↓</option>
                        </select>
                        <button class="btn-soft" onclick="annulerFiltres()">
                            <i class="bi bi-x-circle"></i> Reset
                        </button>
                    </div>

                    <div class="table-wrap">
                        <table id="table-evenements">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Location</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Capacity</th>
                                    <th>Participants</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-evenements">
                            <?php foreach ($evenements as $e):
                                $s = strtolower($e->getStatut());
                                $badgeStatus = str_contains($s,'actif') ? 'actif' : (str_contains($s,'annul') ? 'annule' : 'termine');

                                $isFree = (float)$e->getPrix() == 0;
                                $priceLabel = $isFree
                                    ? '<span class="prix-gratuit">Gratuit</span>'
                                    : '<span class="prix-payant">'.number_format($e->getPrix(),2).' TND</span>';

                                $count = $participationCounts[$e->getIdEvent()] ?? 0;
                                $cap   = (int)$e->getCapaciteMax();
                                $full  = $cap > 0 && $count >= $cap;
                                $almost = $cap > 0 && !$full && ($count / $cap) >= 0.8;
                                $inscritClass = $full ? 'full' : ($almost ? 'almost' : 'ok');

                                $debut = substr($e->getDateDebut(), 0, 16);
                                $fin   = substr($e->getDateFin(),   0, 16);
                            ?>
                                <tr
                                    data-titre="<?= htmlspecialchars(strtolower($e->getTitre()), ENT_QUOTES) ?>"
                                    data-type="<?= htmlspecialchars(strtolower($e->getType()), ENT_QUOTES) ?>"
                                    data-statut="<?= htmlspecialchars(strtolower($e->getStatut()), ENT_QUOTES) ?>"
                                    data-prix="<?= (float)$e->getPrix() ?>"
                                    data-date="<?= htmlspecialchars($e->getDateDebut() ?? '', ENT_QUOTES) ?>">
                                    <td><?= (int)$e->getIdEvent() ?></td>
                                    <td><?= htmlspecialchars($e->getTitre()) ?></td>
                                    <td><?= htmlspecialchars($e->getType()) ?></td>
                                    <td><?= htmlspecialchars($e->getLieu()) ?></td>
                                    <td><?= htmlspecialchars($debut) ?></td>
                                    <td><?= htmlspecialchars($fin) ?></td>
                                    <td><?= $cap ?></td>
                                    <td>
                                        <a href="listParticipations.php?id_event=<?= $e->getIdEvent() ?>" class="badge-inscrit <?= $inscritClass ?>">
                                            <?= $count ?> / <?= $cap ?>
                                        </a>
                                    </td>
                                    <td><?= $priceLabel ?></td>
                                    <td><span class="badge-status <?= $badgeStatus ?>"><?= htmlspecialchars($e->getStatut()) ?></span></td>
                                    <td>
                                        <a href="updateEvenement.php?id=<?= (int)$e->getIdEvent() ?>" class="btn-table btn-edit"><i class="bi bi-pencil"></i></a>
                                        <a href="listParticipations.php?id_event=<?= (int)$e->getIdEvent() ?>" class="btn-table btn-participants" title="View all participants"><i class="fas fa-users"></i></a>
                                        <a href="listEvenements.php?delete=<?= (int)$e->getIdEvent() ?>" class="btn-table btn-delete" onclick="return confirm('Delete this event?')"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr id="no-result" class="no-result" style="display:none;">
                                <td colspan="11"><i class="fas fa-search me-2"></i>No events found.</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination événements -->
                    <div id="pagination-evenements" style="display:flex;align-items:center;justify-content:center;gap:6px;padding:16px 0;flex-wrap:wrap"></div>
                </section>
                <aside class="side-panel">
                    <div class="small-card">
                        <h3>Quick summary</h3>
                        <ul>
                            <li><span>Total events</span><span><?= $totalEvents ?></span></li>
                            <li><span>Total capacity</span><span><?= $totalCapacity ?></span></li>
                            <li><span>Events with bookings</span><span><?= $withParticipants ?></span></li>
                            <li><span>Full events</span><span><?= $fullEvents ?></span></li>
                        </ul>
                    </div>
                    <div class="small-card">
                        <h3>Quick actions</h3>
                        <div class="quick-action">
                            <a href="addEvenement.php"><i class="bi bi-plus-lg"></i> Add event</a>
                            <a href="listParticipations.php"><i class="bi bi-people"></i> View participants</a>
                            <a href="listEvenements.php"><i class="bi bi-arrow-clockwise"></i> Refresh</a>
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

var tbody = document.getElementById('tbody-evenements');
Array.from(tbody.querySelectorAll('tr:not(#no-result)')).forEach(function(r,i){ r.dataset.index=i; });

// ── Pagination événements (8 par page) ──────────────────────────────
var ROWS_PER_PAGE_EV = 8;
var currentPageEv    = 1;

function renderPaginationEv() {
  var allRows = Array.from(tbody.querySelectorAll('tr:not(#no-result)'));
  // Use data-filtered if set, otherwise all rows are visible
  var visible = allRows.filter(function(r){ return r.dataset.filtered !== '0'; });
  var totalPages = Math.max(1, Math.ceil(visible.length / ROWS_PER_PAGE_EV));
  if (currentPageEv > totalPages) currentPageEv = totalPages;

  // Hide all, then show only current page
  allRows.forEach(function(r){ r.style.display = 'none'; });
  visible.forEach(function(r, i) {
    if (i >= (currentPageEv-1)*ROWS_PER_PAGE_EV && i < currentPageEv*ROWS_PER_PAGE_EV) {
      r.style.display = '';
    }
  });

  // no-result row
  var noResult = document.getElementById('no-result');
  if (noResult) noResult.style.display = visible.length === 0 ? '' : 'none';

  // Render pagination controls
  var pag = document.getElementById('pagination-evenements');
  if (!pag) return;
  if (totalPages <= 1) {
    pag.innerHTML = '<span style="font-size:12px;color:var(--muted)">'+visible.length+' événement(s)</span>';
    return;
  }
  var html = '<button class="pag-btn" '+(currentPageEv<=1?'disabled':'')+' onclick="goPageEv('+(currentPageEv-1)+')">‹</button>';
  for (var i = 1; i <= totalPages; i++) {
    if (i===1||i===totalPages||(i>=currentPageEv-1&&i<=currentPageEv+1)) {
      html += '<button class="pag-btn'+(i===currentPageEv?' pag-active':'')+'" onclick="goPageEv('+i+')">'+i+'</button>';
    } else if (i===currentPageEv-2||i===currentPageEv+2) {
      html += '<span style="color:var(--muted);padding:0 4px">…</span>';
    }
  }
  html += '<button class="pag-btn" '+(currentPageEv>=totalPages?'disabled':'')+' onclick="goPageEv('+(currentPageEv+1)+')">›</button>';
  html += '<span style="font-size:12px;color:var(--muted);margin-left:6px">Page '+currentPageEv+'/'+totalPages+' · '+visible.length+' résultats</span>';
  pag.innerHTML = html;
}

function goPageEv(p) { currentPageEv = p; renderPaginationEv(); }

// Hook into existing filter function
var _origFiltrer = typeof filtrerEtTrier === 'function' ? filtrerEtTrier : null;
function filtrerEtTrierWithPag() {
  if (_origFiltrer) _origFiltrer();
  currentPageEv = 1;
  renderPaginationEv();
}
// Override filter calls
document.addEventListener('DOMContentLoaded', function() {
  renderPaginationEv();
  // Re-hook filter inputs
  ['filter-search','filter-type','filter-statut','filter-tri'].forEach(function(id){
    var el = document.getElementById(id);
    if (el) el.addEventListener('input', function(){ currentPageEv=1; renderPaginationEv(); });
    if (el) el.addEventListener('change', function(){ currentPageEv=1; renderPaginationEv(); });
  });
});

function exportPDFEvenements() {
    var rows = Array.from(tbody.querySelectorAll('tr:not(#no-result)')).filter(function(r){ return r.style.display !== 'none'; });
    var headers = ['ID','Title','Type','Location','Start','End','Capacity','Participants','Price','Status'];
    var body = rows.map(function(row){
        var cols = [];
        for (var i = 0; i < row.cells.length - 1; i++) cols.push(row.cells[i].textContent.trim());
        return cols;
    });

    var jspdf = window.jspdf && window.jspdf.jsPDF ? window.jspdf.jsPDF : null;
    if (!jspdf) return;
    var doc = new jspdf({ orientation: 'landscape', unit: 'pt', format: 'a4' });
    var title = 'Events';
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

    doc.save('evenements_' + new Date().toISOString().slice(0,10) + '.pdf');
}

function chartDefaults() {
  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { labels: { color: '#333' } }
    },
    scales: {
      x: { ticks: { color: '#666' }, grid: { color: 'rgba(0,0,0,0.05)' } },
      y: { ticks: { color: '#666' }, grid: { color: 'rgba(0,0,0,0.05)' }, beginAtZero: true }
    }
  };
}

var charts = { bar: null, doughnut: null, line: null };
var statsLoaded = false;
var statsTotals = null;

function countUp(el, to, suffix) {
  var start = 0;
  var dur = 1200;
  var t0 = performance.now();
  function step(t) {
    var p = Math.min(1, (t - t0) / dur);
    var v = Math.floor(start + (to - start) * (p < 1 ? (1 - Math.pow(1 - p, 3)) : 1));
    el.textContent = String(v) + (suffix || '');
    if (p < 1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}

function animateCounters(totals) {
  if (!totals) return;
  document.querySelectorAll('[data-counter]').forEach(function (el) {
    var key = el.getAttribute('data-counter');
    var val = totals[key] ?? 0;
    if (key === 'totalRevenue') {
      // revenue with 2 decimals
      var target = Math.round((parseFloat(val) || 0) * 100) / 100;
      var t0 = performance.now();
      var from = 0;
      var dur = 1200;
      function step(t) {
        var p = Math.min(1, (t - t0) / dur);
        var cur = from + (target - from) * (p < 1 ? (1 - Math.pow(1 - p, 3)) : 1);
        el.textContent = cur.toFixed(2);
        if (p < 1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
    } else if (key === 'avgOccupancy') {
      var targetPct = parseFloat(val) || 0;
      var t0b = performance.now();
      var fromb = 0;
      var durb = 1200;
      function stepb(t) {
        var p = Math.min(1, (t - t0b) / durb);
        var cur = fromb + (targetPct - fromb) * (p < 1 ? (1 - Math.pow(1 - p, 3)) : 1);
        el.textContent = cur.toFixed(1) + '%';
        if (p < 1) requestAnimationFrame(stepb);
      }
      requestAnimationFrame(stepb);
    } else {
      countUp(el, parseInt(val || 0, 10), '');
    }
  });
}

async function loadStatsCharts() {
    try {
        var res = await fetch('getStatsEvenements.php', { headers: { 'Accept': 'application/json' } });
        var data = await res.json();
        if (!data) return;

        var colors = ['#e63946', '#0d6efd', '#198754', '#f59e0b'];

        // Chart 1 — Doughnut: "Répartition par type"
        var labelsD = (data.revenueByType || []).map(function(x){ return x.type; });
        var valuesD = (data.revenueByType || []).map(function(x){ return x.revenue; });
        var ctxD = document.getElementById('chart-repartition-type');
        if (ctxD) {
            if (charts.doughnut) charts.doughnut.destroy();
            charts.doughnut = new Chart(ctxD, {
                type: 'doughnut',
                data: { labels: labelsD, datasets: [{ data: valuesD, backgroundColor: colors, borderColor: '#fff', borderWidth: 2, hoverOffset: 10 }] },
                options: Object.assign({}, chartDefaults(), {
                  plugins: { legend: { position: 'bottom', labels: { color: '#333' } } }
                })
            });
        }

        // Chart 2 — Bar: "Participations par événement"
        var labelsBar = (data.participationsPerEvent || []).map(function(x){
          var t = String(x.titre || '');
          return t.length > 15 ? (t.slice(0, 15) + '…') : t;
        });
        var valuesBar = (data.participationsPerEvent || []).map(function(x){ return x.participations; });
        var ctxBar = document.getElementById('chart-participations-event');
        if (ctxBar) {
            if (charts.bar) charts.bar.destroy();
            var g = ctxBar.getContext('2d');
            var grad = g.createLinearGradient(0, 0, 0, 240);
            grad.addColorStop(0, 'rgba(230,57,70,0.65)');
            grad.addColorStop(1, 'rgba(230,57,70,0.05)');
            charts.bar = new Chart(ctxBar, {
                type: 'bar',
                data: {
                  labels: labelsBar,
                  datasets: [{
                    label: 'Participations',
                    data: valuesBar,
                    backgroundColor: grad,
                    borderColor: 'rgba(230,57,70,0.9)',
                    borderWidth: 1,
                    borderRadius: 10
                  }]
                },
                options: Object.assign({}, chartDefaults(), {
                  plugins: { legend: { display: false } }
                })
            });
        }

        // Chart 3 — Line: "Évolution mensuelle"
        var labelsL = (data.participationsByMonth || []).map(function(x){ return x.month; });
        var valuesL = (data.participationsByMonth || []).map(function(x){ return x.participations; });
        var ctxL = document.getElementById('chart-participations-month');
        if (ctxL) {
            if (charts.line) charts.line.destroy();
            charts.line = new Chart(ctxL, {
                type: 'line',
                data: {
                  labels: labelsL,
                  datasets: [{
                    label: 'Participations',
                    data: valuesL,
                    borderColor: '#e63946',
                    backgroundColor: 'rgba(230,57,70,0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#e63946',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                  }]
                },
                options: Object.assign({}, chartDefaults(), {
                  plugins: { legend: { display: false } }
                })
            });
        }

        statsTotals = data.totals || null;
        statsLoaded = true;
    } catch (e) {}
}

document.addEventListener('DOMContentLoaded', function () {
    var wrap = document.getElementById('advStatsWrap');
    var fab = document.getElementById('statsFab');
    var icon = document.getElementById('statsFabIcon');
    if (wrap) wrap.style.display = 'block';

    function setOpen(open) {
      wrap.classList.toggle('open', open);
      fab.classList.toggle('pulse', !open);
      icon.className = open ? 'fas fa-times' : 'fas fa-chart-bar';
      if (open) {
        if (!statsLoaded) {
          loadStatsCharts().then(function () {
            if (statsTotals) animateCounters(statsTotals);
          });
        } else if (statsTotals) {
          animateCounters(statsTotals);
        }
      }
    }

    setOpen(false);

    if (fab) fab.addEventListener('click', function () {
      var open = wrap.classList.contains('open');
      setOpen(!open);
    });
});

function filtrerEtTrier() {
    var q      = document.getElementById('search-event').value.toLowerCase().trim();
    var type   = document.getElementById('filter-type').value.toLowerCase();
    var statut = document.getElementById('filter-statut').value.toLowerCase();
    var tri    = document.getElementById('filter-tri').value;
    var rows   = Array.from(tbody.querySelectorAll('tr:not(#no-result)'));

    rows.forEach(function(row) {
        var show = (!q || (row.dataset.titre||'').includes(q) || (row.dataset.type||'').includes(q))
                && (!type   || row.dataset.type   === type)
                && (!statut || row.dataset.statut === statut);
        row.dataset.filtered = show ? '1' : '0';
    });

    if (tri) {
        var field=tri.split('-')[0], dir=tri.split('-')[1];
        var vis=rows.filter(function(r){ return r.dataset.filtered==='1'; });
        vis.sort(function(a,b){
            if (field==='title') return dir==='asc'?(a.dataset.titre||'').localeCompare(b.dataset.titre||''):(b.dataset.titre||'').localeCompare(a.dataset.titre||'');
            if (field==='price')  { var va=parseFloat(a.dataset.prix)||0,vb=parseFloat(b.dataset.prix)||0; return dir==='asc'?va-vb:vb-va; }
            if (field==='date')  return dir==='asc'?(a.dataset.date||'').localeCompare(b.dataset.date||''):(b.dataset.date||'').localeCompare(a.dataset.date||'');
            return 0;
        });
        vis.forEach(function(r){ tbody.insertBefore(r,document.getElementById('no-result')); });
    } else {
        rows.slice().sort(function(a,b){ return (parseInt(a.dataset.index)||0)-(parseInt(b.dataset.index)||0); })
            .forEach(function(r){ tbody.insertBefore(r,document.getElementById('no-result')); });
    }

    currentPageEv = 1;
    renderPaginationEv();
}

function annulerFiltres() {
    ['search-event','filter-type','filter-statut','filter-tri'].forEach(function(id){ document.getElementById(id).value=''; });
    filtrerEtTrier();
}

function exportCSV() {
    var headers=['ID','Title','Type','Location','Start','End','Capacity','Participants','Price','Status'];
    var rows=Array.from(tbody.querySelectorAll('tr:not(#no-result)')).filter(function(r){ return r.style.display!=='none'; });
    var esc=function(v){ return '"'+v.replace(/"/g,'""')+'"'; };
    var lines=[headers.map(esc).join(',')];
    rows.forEach(function(row){
        var cols=[];
        for(var i=0;i<row.cells.length-1;i++) cols.push(esc(row.cells[i].textContent.trim()));
        lines.push(cols.join(','));
    });
    var a=document.createElement('a');
    a.href=URL.createObjectURL(new Blob(['\uFEFF'+lines.join('\r\n')],{type:'text/csv;charset=utf-8'}));
    a.download='evenements_'+new Date().toISOString().slice(0,10)+'.csv';
    a.click();
}

document.getElementById('search-event').addEventListener('input', filtrerEtTrier);
document.getElementById('filter-type').addEventListener('change', filtrerEtTrier);
document.getElementById('filter-statut').addEventListener('change', filtrerEtTrier);
document.getElementById('filter-tri').addEventListener('change', filtrerEtTrier);
</script>
</body>
</html>