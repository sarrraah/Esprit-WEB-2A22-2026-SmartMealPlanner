<?php
defined('APP_ROOT') || require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Repas.php';
require_once __DIR__ . '/../../model/Recette.php';

$repasModel   = new Repas();
$recetteModel = new Recette();
$totalRepas    = $repasModel->countRepas();
$totalRecettes = $repasModel->countRecettes();
$totalCal      = round($repasModel->totalCalories(), 1);
$avgCal        = round($repasModel->avgCalories(), 1);
$lastRepas     = array_slice($repasModel->getAllRepas(), 0, 6);
$statsByType   = $repasModel->statsByType();

$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$project = str_replace('\\', '/', dirname(__DIR__, 2));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);

$pageTitle = 'Tableau de Bord - SmartMeal Admin';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/sidebar.php';
?>
<div class="admin-main">
    <div class="admin-topbar">
        <h5><i class="bi bi-speedometer2 me-2" style="color:var(--accent)"></i>Tableau de Bord</h5>
        <div class="d-flex gap-2">
            <a href="add_repas.php" class="btn btn-yummy btn-sm"><i class="bi bi-plus-lg me-1"></i>Nouveau Repas</a>
            <a href="add_recette.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-plus-lg me-1"></i>Nouvelle Recette</a>
        </div>
    </div>
    <div class="admin-content">

        <!-- Banner -->
        <div class="rounded-4 mb-4 overflow-hidden position-relative" style="height:160px;">
            <img src="../../view/assets/img/stats-bg.jpg" style="width:100%;height:100%;object-fit:cover;filter:brightness(.45);" alt="">
            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center px-4">
                <div class="text-white">
                    <h2 style="font-family:'Amatic SC',sans-serif;font-size:2.4rem;margin:0;">
                        Bienvenue sur <span style="color:#ce1212;">SmartMeal</span> Admin
                    </h2>
                    <p class="mb-0 opacity-75 small">Gérez vos repas, recettes et statistiques.</p>
                </div>
            </div>
        </div>

        <!-- KPI -->
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3 p-4">
                        <div class="icon-box" style="background:#fde8e8;color:#ce1212;"><i class="bi bi-bowl-hot"></i></div>
                        <div><div class="text-muted small">Total Repas</div><div class="stat-value"><?= $totalRepas ?></div></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3 p-4">
                        <div class="icon-box" style="background:#e8f0fe;color:#0d6efd;"><i class="bi bi-journal-richtext"></i></div>
                        <div><div class="text-muted small">Total Recettes</div><div class="stat-value"><?= $totalRecettes ?></div></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3 p-4">
                        <div class="icon-box" style="background:#fff3cd;color:#fd7e14;"><i class="bi bi-fire"></i></div>
                        <div><div class="text-muted small">Calories Totales</div><div class="stat-value"><?= $totalCal ?></div></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3 p-4">
                        <div class="icon-box" style="background:#d1f2eb;color:#198754;"><i class="bi bi-graph-up"></i></div>
                        <div><div class="text-muted small">Moy. Cal/Repas</div><div class="stat-value"><?= $avgCal ?></div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Recent repas -->
            <div class="col-lg-8">
                <div class="admin-card card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-clock-history me-2" style="color:var(--accent)"></i>Derniers Repas</span>
                        <a href="repas.php" class="btn btn-yummy btn-sm">Voir tout</a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($lastRepas)): ?>
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Aucun repas. <a href="add_repas.php">Ajoutez-en un !</a>
                            </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table admin-table mb-0">
                                <thead><tr><th>Photo</th><th>Nom</th><th>Type</th><th>Calories</th><th>Actions</th></tr></thead>
                                <tbody>
                                    <?php foreach ($lastRepas as $r): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($r['image_repas'])): ?>
                                                <img src="<?= htmlspecialchars($baseUrl.'/'.$r['image_repas']) ?>" style="width:48px;height:48px;object-fit:cover;border-radius:10px;">
                                            <?php else: ?>
                                                <div style="width:48px;height:48px;background:linear-gradient(135deg,#f8d7da,#f5c6cb);border-radius:10px;display:flex;align-items:center;justify-content:center;"><i class="bi bi-egg-fried" style="color:#ce1212;opacity:.5;"></i></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-medium"><?= htmlspecialchars($r['nom']) ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($r['type_repas'] ?? '-') ?></span></td>
                                        <td><?= htmlspecialchars($r['calories'] ?? '-') ?> kcal</td>
                                        <td>
                                            <a href="ingredients.php?id_repas=<?= $r['id_repas'] ?>" class="btn btn-sm btn-outline-success" title="Ingrédients"><i class="bi bi-list-ul"></i></a>
                                            <a href="edit_repas.php?id=<?= $r['id_repas'] ?>" class="btn btn-sm btn-outline-warning" title="Modifier"><i class="bi bi-pencil"></i></a>
                                            <a href="../../controller/RepasController.php?action=delete&id=<?= $r['id_repas'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ?')"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right column -->
            <div class="col-lg-4 d-flex flex-column gap-4">
                <div class="admin-card card">
                    <div class="card-header"><i class="bi bi-pie-chart me-2" style="color:var(--accent)"></i>Repas par Type</div>
                    <div class="card-body">
                        <?php foreach ($statsByType as $s): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small"><?= htmlspecialchars($s['type_repas']) ?></span>
                            <span class="badge" style="background:var(--accent)"><?= $s['total'] ?></span>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($statsByType)): ?><p class="text-muted small">Aucune donnée.</p><?php endif; ?>
                    </div>
                </div>
                <div class="admin-card card">
                    <div class="card-header"><i class="bi bi-lightning me-2" style="color:var(--accent)"></i>Actions Rapides</div>
                    <div class="card-body d-grid gap-2">
                        <a href="add_repas.php" class="btn btn-yummy"><i class="bi bi-plus-circle me-1"></i>Ajouter un Repas</a>
                        <a href="add_recette.php" class="btn btn-outline-secondary"><i class="bi bi-journal-plus me-1"></i>Ajouter une Recette</a>
                        <a href="statistiques.php" class="btn btn-outline-secondary"><i class="bi bi-bar-chart me-1"></i>Statistiques</a>
                    </div>
                </div>
                <div class="admin-card card">
                    <div class="card-header"><i class="bi bi-images me-2" style="color:var(--accent)"></i>Galerie</div>
                    <div class="card-body p-2">
                        <div class="row g-1">
                            <?php for ($i=1;$i<=4;$i++): ?>
                            <div class="col-6"><img src="../../view/assets/img/gallery/gallery-<?= $i ?>.jpg" class="img-fluid rounded-3" style="height:75px;object-fit:cover;width:100%;"></div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/partials/foot.php'; ?>
