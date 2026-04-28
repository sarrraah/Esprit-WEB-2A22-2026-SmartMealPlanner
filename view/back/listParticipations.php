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
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Participations</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body { background: #fff; font-family: 'Segoe UI', sans-serif; margin: 0; }

        /* ── Navbar ── */
        .top-navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            height: 64px;
            border-bottom: 1px solid #eee;
            background: #fff;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .top-navbar .brand { font-weight: 800; font-size: 20px; color: #111; text-decoration: none; }
        .top-navbar .brand span { color: #e63946; }
        .top-navbar .nav-links { display: flex; gap: 32px; list-style: none; margin: 0; padding: 0; }
        .top-navbar .nav-links a {
            text-decoration: none; color: #555; font-size: 14px; font-weight: 500;
            padding-bottom: 4px; border-bottom: 2px solid transparent;
        }
        .top-navbar .nav-links a.active,
        .top-navbar .nav-links a:hover { color: #111; border-bottom: 2px solid #e63946; }
        .top-navbar .btn-add {
            background: #e63946; color: #fff; border: none; border-radius: 25px;
            padding: 9px 22px; font-size: 14px; font-weight: 600;
            text-decoration: none; cursor: pointer; transition: background 0.2s;
        }
        .top-navbar .btn-add:hover { background: #c1121f; color: #fff; }

        /* ── Page content ── */
        .page-content { padding: 40px 40px 60px; }

        .page-title { font-size: 38px; font-weight: 400; color: #111; margin-bottom: 8px; }

        /* ── Stats ── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 12px;
            padding: 18px 22px;
        }
        .stat-num { font-size: 28px; font-weight: 700; color: #111; line-height: 1; }
        .stat-lbl { font-size: 11px; color: #888; margin-top: 6px; text-transform: uppercase; letter-spacing: .6px; }
        .stat-card.green .stat-num { color: #198754; }
        .stat-card.orange .stat-num { color: #f59e0b; }
        .stat-card.red .stat-num   { color: #e63946; }

        /* ── Event banner ── */
        .event-banner {
            background: #f8f9fa; border: 1px solid #e5e5e5; border-radius: 10px;
            padding: 12px 18px; margin-bottom: 20px;
            display: flex; align-items: center; gap: 12px; font-size: 14px;
        }
        .event-banner strong { color: #111; }
        .event-banner a {
            margin-left: auto; font-size: 13px; color: #e63946;
            text-decoration: none; font-weight: 500;
            padding: 5px 14px; border: 1px solid #e63946; border-radius: 8px;
        }
        .event-banner a:hover { background: #fce8e8; }

        /* ── Filters ── */
        .filters-row {
            display: grid;
            grid-template-columns: 2fr 1.4fr 1.8fr auto;
            gap: 12px;
            margin-bottom: 24px;
            width: 100%;
            align-items: center;
        }
        .filters-row input,
        .filters-row select {
            width: 100%; border: 1px solid #ddd; border-radius: 8px;
            padding: 11px 16px; font-size: 15px; color: #333;
            background: #fff; outline: none;
        }
        .filters-row input:focus,
        .filters-row select:focus { border-color: #aaa; }
        .filters-row .btn-cancel {
            background: #fff; border: 1px solid #ddd; border-radius: 8px;
            padding: 11px 18px; font-size: 15px; color: #555; cursor: pointer;
            display: flex; align-items: center; gap: 6px; white-space: nowrap;
        }
        .filters-row .btn-cancel:hover { border-color: #aaa; color: #111; }

        /* ── Table ── */
        .table-wrap { border: 1px solid #e5e5e5; border-radius: 10px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; font-size: 16px; }
        thead th {
            background: #fff; font-weight: 700;
            padding: 18px 20px; border-bottom: 2px solid #e5e5e5;
            white-space: nowrap; font-size: 15px;
        }
        thead th:nth-child(1) { color: #6c757d; }
        thead th:nth-child(2) { color: #111; }
        thead th:nth-child(3) { color: #0d6efd; }
        thead th:nth-child(4) { color: #198754; }
        thead th:nth-child(5) { color: #e63946; }
        thead th:nth-child(6) { color: #6c757d; }
        thead th:nth-child(7) { color: #6c757d; }
        thead th:nth-child(8) { color: #6c757d; }

        tbody tr { border-bottom: 1px solid #f0f0f0; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #fafafa; }
        tbody td {
            padding: 16px 20px; color: #333; font-size: 16px;
            vertical-align: middle; white-space: nowrap;
        }
        tbody td:nth-child(2) {
            font-size: 17px; font-weight: 400; color: #111;
        }
        tbody td:nth-child(3) { white-space: normal; min-width: 150px; }

        /* ── Badges statut ── */
        .badge-status {
            display: inline-block; padding: 4px 12px; border-radius: 20px;
            font-size: 12px; font-weight: 600; background: #333; color: #fff;
        }
        .badge-status.confirme { background: #198754; }
        .badge-status.attente  { background: #f59e0b; color: #fff; }
        .badge-status.annule   { background: #6c757d; }

        /* ── Prix ── */
        .prix-gratuit { color: #198754; font-weight: 600; }
        .prix-payant  { color: #e63946; font-weight: 600; }

        /* ── Boutons actions ── */
        .btn-modifier {
            border: 1.5px solid #0d6efd; color: #0d6efd; background: #fff;
            border-radius: 6px; padding: 4px 14px; font-size: 13px; font-weight: 500;
            text-decoration: none; cursor: pointer; transition: all 0.15s;
        }
        .btn-modifier:hover { background: #0d6efd; color: #fff; }
        .btn-supprimer {
            border: 1.5px solid #dc3545; color: #dc3545; background: #fff;
            border-radius: 6px; padding: 4px 14px; font-size: 13px; font-weight: 500;
            text-decoration: none; cursor: pointer; transition: all 0.15s; margin-left: 6px;
        }
        .btn-supprimer:hover { background: #dc3545; color: #fff; }

        /* ── Alert ── */
        .alert { border-radius: 8px; font-size: 14px; margin-bottom: 20px; }

        /* ── No result ── */
        .no-result td { text-align: center; color: #aaa; padding: 40px; font-size: 15px; }

        /* ── Export btn ── */
        .btn-export {
            background: #fff; border: 1px solid #ddd; border-radius: 8px;
            padding: 9px 18px; font-size: 14px; color: #555; cursor: pointer;
            display: flex; align-items: center; gap: 6px;
        }
        .btn-export:hover { border-color: #aaa; color: #111; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="top-navbar">
    <a href="#" class="brand">Smart Meal Planner<span>.</span></a>
    <ul class="nav-links">
        <li><a href="listEvenements.php">Événements</a></li>
        <li><a href="listParticipations.php" class="active">Participants</a></li>
        <li><a href="afficherProduit.php">Produits</a></li>
        <li><a href="afficherCategorie.php">Catégories</a></li>
    </ul>
    <a href="addParticipation.php<?= $id_event_filter ? '?id_event='.$id_event_filter : '' ?>" class="btn-add">
        Ajouter Participation
    </a>
</nav>

<!-- CONTENT -->
<div class="page-content">

    <!-- Titre + export -->
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <h1 class="page-title mb-0">Gestion des Participations</h1>
        <button class="btn-export" onclick="exportCSV()">
            <i class="fas fa-download"></i> Export CSV
        </button>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-num"><?= $total ?></div>
            <div class="stat-lbl">Total</div>
        </div>
        <div class="stat-card green">
            <div class="stat-num"><?= $confirmes ?></div>
            <div class="stat-lbl">Confirmés</div>
        </div>
        <div class="stat-card orange">
            <div class="stat-num"><?= $enAttente ?></div>
            <div class="stat-lbl">En attente</div>
        </div>
        <div class="stat-card red">
            <div class="stat-num"><?= number_format($revenue, 2) ?></div>
            <div class="stat-lbl">Revenue (TND)</div>
        </div>
    </div>

    <!-- Alertes -->
    <?php if ($msg === 'added'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-1"></i>Participation ajoutée avec succès.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($msg === 'updated'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-1"></i>Participation mise à jour.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($msg === 'deleted'): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="fas fa-trash me-1"></i>Participation supprimée.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Bannière événement filtré -->
    <?php if ($id_event_filter && $eventTitle): ?>
    <div class="event-banner">
        <i class="fas fa-calendar-alt text-success"></i>
        <div>
            <strong><?= htmlspecialchars($eventTitle) ?></strong>
            <div style="font-size:12px;color:#888;margin-top:2px;">Participations liées à cet événement</div>
        </div>
        <a href="listParticipations.php">Voir tout</a>
    </div>
    <?php endif; ?>

    <!-- Filtres -->
    <div class="filters-row">
        <input type="text" id="search-input" placeholder="Rechercher un participant...">
        <select id="filter-statut">
            <option value="">Tous les statuts</option>
            <option value="confirmé">Confirmé</option>
            <option value="en attente">En attente</option>
            <option value="annulé">Annulé</option>
        </select>
        <select id="filter-event">
            <option value="">Tous les événements</option>
            <?php foreach ($allEvents as $ev): ?>
                <option value="<?= $ev->getIdEvent() ?>"
                    <?= ($id_event_filter == $ev->getIdEvent()) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($ev->getTitre()) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="btn-cancel" onclick="annulerFiltres()">
            <i class="bi bi-x-circle"></i> Annuler
        </button>
    </div>

    <!-- Tableau -->
    <div class="table-wrap">
        <table id="table-participations">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Participant</th>
                    <th>Événement</th>
                    <th>Date</th>
                    <th>Montant</th>
                    <th>Mode Paiement</th>
                    <th>Statut</th>
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
                        <a href="updateParticipation.php?id=<?= $p->getIdParticipation() ?>" class="btn-modifier">Modifier</a>
                        <a href="listParticipations.php?delete=<?= $p->getIdParticipation() ?><?= $id_event_filter ? '&id_event='.$id_event_filter : '' ?>"
                           class="btn-supprimer"
                           onclick="return confirm('Supprimer cette participation ?')">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr id="no-result" style="display:none;" class="no-result">
                <td colspan="8"><i class="fas fa-search me-2"></i>Aucune participation trouvée.</td>
            </tr>
            </tbody>
        </table>
    </div>

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
    var headers = ['ID','Participant','Événement','Date','Montant','Mode Paiement','Statut'];
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