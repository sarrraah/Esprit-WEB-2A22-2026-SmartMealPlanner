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
                    <div class="stat-label">Upcoming</div>
                    <div class="stat-value"><?= $upcomingEvents ?></div>
                </div>
                <div class="stat-card danger">
                    <div class="stat-label">Full capacity</div>
                    <div class="stat-value"><?= $fullEvents ?></div>
                </div>
            </div>

            <div class="dashboard-grid">
                <section class="section-card">
                    <div class="section-card-title">
                        <span><i class="bi bi-calendar3"></i> Event table</span>
                        <a href="addEvenement.php" class="btn-action-primary"><i class="bi bi-plus-circle"></i> New event</a>
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
<script>
var tbody = document.getElementById('tbody-evenements');
Array.from(tbody.querySelectorAll('tr:not(#no-result)')).forEach(function(r,i){ r.dataset.index=i; });

function filtrerEtTrier() {
    var q      = document.getElementById('search-event').value.toLowerCase().trim();
    var type   = document.getElementById('filter-type').value.toLowerCase();
    var statut = document.getElementById('filter-statut').value.toLowerCase();
    var tri    = document.getElementById('filter-tri').value;
    var rows   = Array.from(tbody.querySelectorAll('tr:not(#no-result)'));
    var visible = 0;

    rows.forEach(function(row) {
        var show = (!q || (row.dataset.titre||'').includes(q) || (row.dataset.type||'').includes(q))
                && (!type   || row.dataset.type   === type)
                && (!statut || row.dataset.statut === statut);
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    if (tri) {
        var field=tri.split('-')[0], dir=tri.split('-')[1];
        var vis=rows.filter(function(r){ return r.style.display!=='none'; });
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
    document.getElementById('no-result').style.display = visible===0 ? '' : 'none';
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