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

$total    = count($evenements);
$actifs   = count(array_filter($evenements, fn($e) => str_contains(strtolower($e->getStatut()), 'actif')));
$gratuits = count(array_filter($evenements, fn($e) => (float)$e->getPrix() == 0));
$termines = count(array_filter($evenements, fn($e) => str_contains(strtolower($e->getStatut()), 'termin')));

$participationCounts = [];
foreach ($evenements as $e) {
    $participationCounts[$e->getIdEvent()] = $controller->countParticipationsByEvent($e->getIdEvent());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Événements</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .stat-card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
        .table thead th { background-color: #f2f2f2; font-weight: 600; font-size: 13px; color: #495057; }
        .table tbody tr:hover { background-color: #f8f9fa; }
        .table td { font-size: 13px; vertical-align: middle; }
        .badge-pill { font-size: 12px; padding: 5px 12px; border-radius: 20px; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#">
            <i class="fas fa-calendar-alt me-2"></i>NutriPlanner — Back Office
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active fw-semibold" href="listEvenements.php">
                        <i class="fas fa-list me-1"></i>Événements
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="listParticipations.php">
                        <i class="fas fa-users me-1"></i>Participants
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="afficherProduit.php">
                        <i class="fas fa-box me-1"></i>Produits
                    </a>
                </li>
            </ul>
            <span class="navbar-text text-white">
                <i class="fas fa-user-circle me-1"></i>Admin
            </span>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 pb-5">

    <!-- Titre + boutons -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">
            <i class="fas fa-calendar-alt me-2 text-success"></i>Gestion des Événements
        </h4>
        <div class="d-flex gap-2">
            <a href="addEvenement.php" class="btn btn-success">
                <i class="fas fa-plus me-1"></i>Nouvel Événement
            </a>
            <button class="btn btn-outline-secondary" onclick="exportCSV()">
                <i class="fas fa-download me-1"></i>Export CSV
            </button>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card text-center p-3">
                <div class="fs-2 fw-bold text-dark"><?= $total ?></div>
                <div class="text-muted small text-uppercase fw-semibold">Total</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card text-center p-3">
                <div class="fs-2 fw-bold text-success"><?= $actifs ?></div>
                <div class="text-muted small text-uppercase fw-semibold">Actifs</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card text-center p-3">
                <div class="fs-2 fw-bold text-primary"><?= $gratuits ?></div>
                <div class="text-muted small text-uppercase fw-semibold">Gratuits</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card text-center p-3">
                <div class="fs-2 fw-bold text-secondary"><?= $termines ?></div>
                <div class="text-muted small text-uppercase fw-semibold">Terminés</div>
            </div>
        </div>
    </div>

    <!-- Alertes -->
    <?php if ($msg === 'added'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i>Événement ajouté avec succès.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($msg === 'updated'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i>Événement mis à jour avec succès.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($msg === 'deleted'): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-trash me-1"></i>Événement supprimé.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filtres -->
    <div class="row g-2 mb-3">
        <div class="col-md-3">
            <input type="text" id="search-event" class="form-control" placeholder="🔍 Rechercher un événement...">
        </div>
        <div class="col-md-2">
            <select id="filter-type" class="form-select">
                <option value="">Tous les types</option>
                <?php
                $types = array_unique(array_map(fn($e) => $e->getType(), $evenements));
                sort($types);
                foreach ($types as $t): ?>
                    <option value="<?= htmlspecialchars(strtolower($t)) ?>"><?= htmlspecialchars($t) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select id="filter-statut" class="form-select">
                <option value="">Tous les statuts</option>
                <?php
                $statuts = array_unique(array_map(fn($e) => $e->getStatut(), $evenements));
                sort($statuts);
                foreach ($statuts as $s): ?>
                    <option value="<?= htmlspecialchars(strtolower($s)) ?>"><?= htmlspecialchars($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select id="filter-tri" class="form-select">
                <option value="">— Trier —</option>
                <option value="titre-asc">Titre A → Z</option>
                <option value="titre-desc">Titre Z → A</option>
                <option value="prix-asc">Prix croissant ↑</option>
                <option value="prix-desc">Prix décroissant ↓</option>
                <option value="date-asc">Date proche ↑</option>
                <option value="date-desc">Date lointaine ↓</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100" onclick="annulerFiltres()">
                <i class="fas fa-times me-1"></i>Annuler
            </button>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0" id="table-evenements">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Type</th>
                            <th>Lieu</th>
                            <th>Début</th>
                            <th>Fin</th>
                            <th>Capacité</th>
                            <th>Participations</th>
                            <th>Prix</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-evenements">
                    <?php foreach ($evenements as $e):
                        $s = strtolower($e->getStatut());
                        $badgeClass = match(true) {
                            str_contains($s, 'actif')  => 'bg-success',
                            str_contains($s, 'annul')  => 'bg-warning text-dark',
                            str_contains($s, 'termin') => 'bg-secondary',
                            default => 'bg-secondary',
                        };
                        $isFree     = (float)$e->getPrix() == 0;
                        $priceLabel = $isFree
                            ? '<span class="text-success fw-semibold">Gratuit</span>'
                            : '<span class="text-danger fw-semibold">' . number_format($e->getPrix(), 2) . ' TND</span>';

                        $count  = $participationCounts[$e->getIdEvent()] ?? 0;
                        $cap    = (int)$e->getCapaciteMax();
                        $full   = $cap > 0 && $count >= $cap;
                        $almost = $cap > 0 && !$full && ($count / $cap) >= 0.8;
                        $pillBadge = $full ? 'bg-danger' : ($almost ? 'bg-warning text-dark' : 'bg-success');
                    ?>
                        <tr
                            data-titre="<?= htmlspecialchars(strtolower($e->getTitre()), ENT_QUOTES) ?>"
                            data-type="<?= htmlspecialchars(strtolower($e->getType()), ENT_QUOTES) ?>"
                            data-statut="<?= htmlspecialchars(strtolower($e->getStatut()), ENT_QUOTES) ?>"
                            data-prix="<?= (float)$e->getPrix() ?>"
                            data-date="<?= htmlspecialchars($e->getDateDebut() ?? '', ENT_QUOTES) ?>">
                            <td><?= (int)$e->getIdEvent() ?></td>
                            <td><strong><?= htmlspecialchars($e->getTitre()) ?></strong></td>
                            <td><?= htmlspecialchars($e->getType()) ?></td>
                            <td><?= htmlspecialchars($e->getLieu()) ?></td>
                            <td><?= htmlspecialchars($e->getDateDebut()) ?></td>
                            <td><?= htmlspecialchars($e->getDateFin()) ?></td>
                            <td class="text-center"><?= $cap ?></td>
                            <td class="text-center">
                                <a href="listParticipations.php?id_event=<?= $e->getIdEvent() ?>"
                                   class="badge badge-pill <?= $pillBadge ?> text-decoration-none">
                                    👥 <?= $count ?> / <?= $cap ?>
                                </a>
                            </td>
                            <td><?= $priceLabel ?></td>
                            <td>
                                <span class="badge badge-pill <?= $badgeClass ?>">
                                    <?= htmlspecialchars($e->getStatut()) ?>
                                </span>
                            </td>
                            <td>
                                <a href="updateEvenement.php?id=<?= (int)$e->getIdEvent() ?>"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a href="listParticipations.php?id_event=<?= (int)$e->getIdEvent() ?>"
                                   class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-users"></i>
                                </a>
                                <a href="listEvenements.php?delete=<?= (int)$e->getIdEvent() ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Supprimer cet événement ?')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="no-result" style="display:none;">
                        <td colspan="11" class="text-center text-muted py-4">
                            <i class="fas fa-search me-2"></i>Aucun événement trouvé.
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div><!-- /container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
var tbody = document.getElementById('tbody-evenements');
Array.from(tbody.querySelectorAll('tr:not(#no-result)')).forEach(function(r, i) {
    r.dataset.index = i;
});

function filtrerEtTrier() {
    var q      = document.getElementById('search-event').value.toLowerCase().trim();
    var type   = document.getElementById('filter-type').value.toLowerCase();
    var statut = document.getElementById('filter-statut').value.toLowerCase();
    var tri    = document.getElementById('filter-tri').value;
    var rows   = Array.from(tbody.querySelectorAll('tr:not(#no-result)'));
    var visible = 0;

    rows.forEach(function(row) {
        var titre = row.dataset.titre  || '';
        var rType = row.dataset.type   || '';
        var rStat = row.dataset.statut || '';
        var matchSearch = !q      || titre.includes(q) || rType.includes(q);
        var matchType   = !type   || rType === type;
        var matchStatut = !statut || rStat === statut;
        var show = matchSearch && matchType && matchStatut;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    if (tri) {
        var field = tri.split('-')[0];
        var dir   = tri.split('-')[1];
        var visibleRows = rows.filter(function(r) { return r.style.display !== 'none'; });
        visibleRows.sort(function(a, b) {
            var va, vb;
            if (field === 'titre') {
                va = a.dataset.titre || ''; vb = b.dataset.titre || '';
                return dir === 'asc' ? va.localeCompare(vb) : vb.localeCompare(va);
            }
            if (field === 'prix') {
                va = parseFloat(a.dataset.prix) || 0;
                vb = parseFloat(b.dataset.prix) || 0;
                return dir === 'asc' ? va - vb : vb - va;
            }
            if (field === 'date') {
                va = a.dataset.date || ''; vb = b.dataset.date || '';
                return dir === 'asc' ? va.localeCompare(vb) : vb.localeCompare(va);
            }
            return 0;
        });
        visibleRows.forEach(function(r) {
            tbody.insertBefore(r, document.getElementById('no-result'));
        });
    } else {
        var allRows = rows.slice().sort(function(a, b) {
            return (parseInt(a.dataset.index) || 0) - (parseInt(b.dataset.index) || 0);
        });
        allRows.forEach(function(r) {
            tbody.insertBefore(r, document.getElementById('no-result'));
        });
    }

    document.getElementById('no-result').style.display = visible === 0 ? '' : 'none';
}

function annulerFiltres() {
    document.getElementById('search-event').value   = '';
    document.getElementById('filter-type').value    = '';
    document.getElementById('filter-statut').value  = '';
    document.getElementById('filter-tri').value     = '';
    filtrerEtTrier();
}

function exportCSV() {
    var headers = ['ID','Titre','Type','Lieu','Début','Fin','Capacité','Participations','Prix','Statut'];
    var rows = Array.from(tbody.querySelectorAll('tr:not(#no-result)')).filter(function(r) {
        return r.style.display !== 'none';
    });
    var escape = function(v) { return '"' + v.replace(/"/g, '""') + '"'; };
    var lines  = [headers.map(escape).join(',')];
    rows.forEach(function(row) {
        var cols = [];
        for (var i = 0; i < row.cells.length - 1; i++) {
            cols.push(escape(row.cells[i].textContent.trim()));
        }
        lines.push(cols.join(','));
    });
    var blob = new Blob(['\uFEFF' + lines.join('\r\n')], {type: 'text/csv;charset=utf-8'});
    var a    = document.createElement('a');
    a.href   = URL.createObjectURL(blob);
    a.download = 'evenements_' + new Date().toISOString().slice(0, 10) + '.csv';
    a.click();
}

document.getElementById('search-event').addEventListener('input', filtrerEtTrier);
document.getElementById('filter-type').addEventListener('change', filtrerEtTrier);
document.getElementById('filter-statut').addEventListener('change', filtrerEtTrier);
document.getElementById('filter-tri').addEventListener('change', filtrerEtTrier);
</script>

</body>
</html>