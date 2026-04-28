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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Événements</title>
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
        .top-navbar .brand {
            font-weight: 800;
            font-size: 20px;
            color: #111;
            text-decoration: none;
        }
        .top-navbar .brand span { color: #e63946; }
        .top-navbar .nav-links { display: flex; gap: 32px; list-style: none; margin: 0; padding: 0; }
        .top-navbar .nav-links a {
            text-decoration: none;
            color: #555;
            font-size: 14px;
            font-weight: 500;
            padding-bottom: 4px;
            border-bottom: 2px solid transparent;
        }
        .top-navbar .nav-links a.active,
        .top-navbar .nav-links a:hover {
            color: #111;
            border-bottom: 2px solid #e63946;
        }
        .top-navbar .btn-add {
            background: #e63946;
            color: #fff;
            border: none;
            border-radius: 25px;
            padding: 9px 22px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        .top-navbar .btn-add:hover { background: #c1121f; color: #fff; }

        /* ── Page content ── */
        .page-content { padding: 40px 40px 60px; }

        .page-title {
            font-size: 38px;
            font-weight: 400;
            color: #111;
            margin-bottom: 24px;
        }

        /* ── Filters ── */
        .filters-row {
            display: grid;
            grid-template-columns: 2fr 1.2fr 1.2fr 1.5fr auto;
            gap: 12px;
            margin-bottom: 24px;
            align-items: center;
            width: 100%;
        }
        .filters-row input,
        .filters-row select {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 11px 16px;
            font-size: 15px;
            color: #333;
            background: #fff;
            outline: none;
        }
        .filters-row input:focus,
        .filters-row select:focus { border-color: #aaa; }
        .filters-row .btn-cancel {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 11px 18px;
            font-size: 15px;
            color: #555;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }
        .filters-row .btn-cancel:hover { border-color: #aaa; color: #111; }

        /* ── Table ── */
        .table-wrap { border: 1px solid #e5e5e5; border-radius: 10px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; font-size: 16px; }
        thead th {
            background: #fff;
            font-weight: 700;
            padding: 18px 20px;
            border-bottom: 2px solid #e5e5e5;
            white-space: nowrap;
            font-size: 15px;
        }
        thead th:nth-child(1)  { color: #6c757d; }
        thead th:nth-child(2)  { color: #111; }
        thead th:nth-child(3)  { color: #0d6efd; }
        thead th:nth-child(4)  { color: #6c757d; }
        thead th:nth-child(5)  { color: #198754; }
        thead th:nth-child(6)  { color: #198754; }
        thead th:nth-child(7)  { color: #6c757d; }
        thead th:nth-child(8)  { color: #0d6efd; }
        thead th:nth-child(9)  { color: #dc3545; }
        thead th:nth-child(10) { color: #6c757d; }
        thead th:nth-child(11) { color: #6c757d; }

        tbody tr { border-bottom: 1px solid #f0f0f0; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #fafafa; }
        tbody td {
            padding: 16px 20px;
            color: #333;
            font-size: 16px;
            vertical-align: middle;
            white-space: nowrap;
        }
        tbody td:nth-child(2) {
            white-space: normal;
            min-width: 160px;
            font-size: 17px;
            font-weight: 400;
            color: #111;
        }
        tbody td:nth-child(4) { white-space: normal; min-width: 130px; }

        /* ── Badges ── */
        .badge-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: #333;
            color: #fff;
        }
        .badge-status.actif   { background: #198754; }
        .badge-status.annule  { background: #ffc107; color: #333; }
        .badge-status.termine { background: #6c757d; }

        .badge-inscrit {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #fff;
            text-decoration: none;
        }
        .badge-inscrit.ok     { background: #198754; }
        .badge-inscrit.almost { background: #ffc107; color: #333; }
        .badge-inscrit.full   { background: #dc3545; }

        /* ── Action buttons ── */
        .btn-modifier {
            border: 1.5px solid #0d6efd;
            color: #0d6efd;
            background: #fff;
            border-radius: 6px;
            padding: 4px 14px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s;
        }
        .btn-modifier:hover { background: #0d6efd; color: #fff; }

        .btn-supprimer {
            border: 1.5px solid #dc3545;
            color: #dc3545;
            background: #fff;
            border-radius: 6px;
            padding: 4px 14px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s;
            margin-left: 6px;
        }
        .btn-supprimer:hover { background: #dc3545; color: #fff; }

        .btn-participants {
            border: 1.5px solid #0dcaf0;
            color: #0dcaf0;
            background: #fff;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 13px;
            text-decoration: none;
            margin-left: 6px;
            transition: all 0.15s;
        }
        .btn-participants:hover { background: #0dcaf0; color: #fff; }

        /* Prix */
        .prix-gratuit { color: #198754; font-weight: 600; }
        .prix-payant  { color: #dc3545; font-weight: 600; }

        /* No result */
        .no-result td { text-align: center; color: #aaa; padding: 30px; font-size: 14px; }

        /* Alert */
        .alert { border-radius: 8px; font-size: 14px; margin-bottom: 20px; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="top-navbar">
    <a href="#" class="brand">Smart Meal Planner<span>.</span></a>
    <ul class="nav-links">
        <li><a href="listEvenements.php" class="active">Événements</a></li>
        <li><a href="listParticipations.php">Participants</a></li>
        <li><a href="afficherProduit.php">Produits</a></li>
        <li><a href="afficherCategorie.php">Catégories</a></li>
    </ul>
    <a href="addEvenement.php" class="btn-add">Ajouter Événement</a>
</nav>

<!-- CONTENT -->
<div class="page-content">

    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
        <h1 class="page-title mb-0">Gestion des Événements</h1>
        <button class="btn-cancel" onclick="exportCSV()" style="border-radius:8px;">
            <i class="fas fa-download"></i> Export CSV
        </button>
    </div>

    <!-- Alertes -->
    <?php if ($msg === 'added'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-1"></i>Événement ajouté avec succès.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($msg === 'updated'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-1"></i>Événement mis à jour avec succès.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($msg === 'deleted'): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="fas fa-trash me-1"></i>Événement supprimé.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filtres -->
    <div class="filters-row">
        <input type="text" id="search-event" placeholder="Rechercher un événement...">
        <select id="filter-type">
            <option value="">Tous les types</option>
            <?php
            $types = array_unique(array_map(fn($e) => $e->getType(), $evenements));
            sort($types);
            foreach ($types as $t): ?>
                <option value="<?= htmlspecialchars(strtolower($t)) ?>"><?= htmlspecialchars($t) ?></option>
            <?php endforeach; ?>
        </select>
        <select id="filter-statut">
            <option value="">Tous les statuts</option>
            <?php
            $statuts = array_unique(array_map(fn($e) => $e->getStatut(), $evenements));
            sort($statuts);
            foreach ($statuts as $s): ?>
                <option value="<?= htmlspecialchars(strtolower($s)) ?>"><?= htmlspecialchars($s) ?></option>
            <?php endforeach; ?>
        </select>
        <select id="filter-tri">
            <option value="">— Trier —</option>
            <option value="titre-asc">Titre A → Z</option>
            <option value="titre-desc">Titre Z → A</option>
            <option value="prix-asc">Prix croissant ↑</option>
            <option value="prix-desc">Prix décroissant ↓</option>
            <option value="date-asc">Date proche ↑</option>
            <option value="date-desc">Date lointaine ↓</option>
        </select>
        <button class="btn-cancel" onclick="annulerFiltres()">
            <i class="bi bi-x-circle"></i> Annuler
        </button>
    </div>

    <!-- Tableau -->
    <div class="table-wrap">
        <table id="table-evenements">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Lieu</th>
                    <th>Début</th>
                    <th>Fin</th>
                    <th>Cap.</th>
                    <th>Inscrits</th>
                    <th>Prix</th>
                    <th>Statut</th>
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
                        <a href="updateEvenement.php?id=<?= (int)$e->getIdEvent() ?>" class="btn-modifier">Modifier</a>
                        <a href="listParticipations.php?id_event=<?= (int)$e->getIdEvent() ?>" class="btn-participants" title="Participants"><i class="fas fa-users"></i></a>
                        <a href="listEvenements.php?delete=<?= (int)$e->getIdEvent() ?>" class="btn-supprimer"
                           onclick="return confirm('Supprimer cet événement ?')">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr id="no-result" class="no-result" style="display:none;">
                <td colspan="11"><i class="fas fa-search me-2"></i>Aucun événement trouvé.</td>
            </tr>
            </tbody>
        </table>
    </div>

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
            if (field==='titre') return dir==='asc'?(a.dataset.titre||'').localeCompare(b.dataset.titre||''):(b.dataset.titre||'').localeCompare(a.dataset.titre||'');
            if (field==='prix')  { var va=parseFloat(a.dataset.prix)||0,vb=parseFloat(b.dataset.prix)||0; return dir==='asc'?va-vb:vb-va; }
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
    var headers=['ID','Titre','Type','Lieu','Début','Fin','Capacité','Inscrits','Prix','Statut'];
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