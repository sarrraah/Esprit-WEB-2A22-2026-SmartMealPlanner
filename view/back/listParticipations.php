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
                        <button type="button" class="btn-action-secondary" onclick="exportCSV()"><i class="bi bi-download"></i> Export CSV</button>
                    </div>
                </aside>
            </div>
        </div>
    </main>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
var tbody = document.getElementById('tbody-participations');
Array.from(tbody.querySelectorAll('tr:not(#no-result)')).forEach(function(r,i){ r.dataset.index=i; });

function applyFilters() {
    var q      = document.getElementById('search-input').value.toLowerCase().trim();
    var statut = document.getElementById('filter-statut').value.toLowerCase();
    var evId   = document.getElementById('filter-event').value;
    var rows   = Array.from(tbody.querySelectorAll('tr:not(#no-result)'));
    var visible = 0;

    rows.forEach(function(row) {
        var nom  = row.dataset.nom    || '';
        var st   = row.dataset.statut || '';
        var ev   = row.dataset.event  || '';
        var show = (!q      || nom.includes(q))
                && (!statut || st === statut)
                && (!evId   || ev === evId);
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    document.getElementById('no-result').style.display = visible === 0 ? '' : 'none';
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