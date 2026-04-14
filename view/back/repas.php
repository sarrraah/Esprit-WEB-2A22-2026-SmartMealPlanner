<?php
defined('APP_ROOT') || require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Repas.php';
require_once __DIR__ . '/../../model/Recette.php';

$repasModel   = new Repas();
$recetteModel = new Recette();
$repas        = $repasModel->getAllRepas();
$recettes     = $recetteModel->getAllRecettes();
$recMap       = array_column($recettes, 'nom_recette', 'id_recette');

$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$project = str_replace('\\', '/', dirname(__DIR__, 2));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);

$pageTitle = 'Gestion des Repas - SmartMeal Admin';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/sidebar.php';
?>
<div class="admin-main">
    <div class="admin-topbar">
        <h5><i class="bi bi-bowl-hot me-2" style="color:var(--accent)"></i>Gestion des Repas</h5>
        <a href="add_repas.php" class="btn btn-yummy btn-sm"><i class="bi bi-plus-lg me-1"></i>Ajouter un Repas</a>
    </div>
    <div class="admin-content">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible alert-auto fade show"><i class="bi bi-check-circle me-2"></i>Opération réalisée.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-info alert-dismissible alert-auto fade show"><i class="bi bi-trash me-2"></i>Repas supprimé.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <?php if (empty($repas)): ?>
            <div class="admin-card card"><div class="card-body p-5 text-center text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-3" style="color:var(--accent);opacity:.4;"></i>
                <p class="mb-3">Aucun repas enregistré.</p>
                <a href="add_repas.php" class="btn btn-yummy"><i class="bi bi-plus-lg me-1"></i>Ajouter le premier repas</a>
            </div></div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($repas as $r): ?>
            <div class="col-lg-4 col-md-6">
                <div class="admin-card card h-100">
                    <?php if (!empty($r['image_repas'])): ?>
                        <img src="<?= htmlspecialchars($baseUrl.'/'.$r['image_repas']) ?>" style="height:200px;object-fit:cover;width:100%;" alt="<?= htmlspecialchars($r['nom']) ?>">
                    <?php else: ?>
                        <div style="height:200px;background:linear-gradient(135deg,#f8d7da,#f5c6cb);display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-egg-fried" style="font-size:3.5rem;color:#ce1212;opacity:.35;"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="fw-bold mb-0" style="font-family:'Amatic SC',sans-serif;font-size:1.4rem;"><?= htmlspecialchars($r['nom']) ?></h5>
                            <?php if (!empty($r['calories'])): ?>
                                <span class="badge" style="background:var(--accent)"><?= $r['calories'] ?> kcal</span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-1 flex-wrap mb-2">
                            <?php if (!empty($r['type_repas'])): ?><span class="badge bg-secondary"><?= htmlspecialchars($r['type_repas']) ?></span><?php endif; ?>
                            <?php if (!empty($recMap[$r['id_recette'] ?? 0])): ?><span class="badge bg-primary"><?= htmlspecialchars($recMap[$r['id_recette']]) ?></span><?php endif; ?>
                        </div>
                        <?php if (!empty($r['proteines']) || !empty($r['glucides']) || !empty($r['lipides'])): ?>
                        <div class="d-flex gap-2 small text-muted mb-2">
                            <?php if (!empty($r['proteines'])): ?><span><i class="bi bi-droplet me-1 text-primary"></i><?= $r['proteines'] ?>g</span><?php endif; ?>
                            <?php if (!empty($r['glucides'])): ?><span><i class="bi bi-lightning me-1 text-warning"></i><?= $r['glucides'] ?>g</span><?php endif; ?>
                            <?php if (!empty($r['lipides'])): ?><span><i class="bi bi-heart me-1 text-danger"></i><?= $r['lipides'] ?>g</span><?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($r['description'])): ?>
                            <p class="text-muted small mb-0"><?= htmlspecialchars(mb_substr($r['description'],0,80)).(mb_strlen($r['description'])>80?'…':'') ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white border-0 d-flex gap-2 pb-3 px-3">
                        <a href="ingredients.php?id_repas=<?= $r['id_repas'] ?>" class="btn btn-sm btn-outline-success flex-fill"><i class="bi bi-list-ul me-1"></i>Ingrédients</a>
                        <a href="edit_repas.php?id=<?= $r['id_repas'] ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                        <a href="../../controller/RepasController.php?action=delete&id=<?= $r['id_repas'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ?')"><i class="bi bi-trash"></i></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/partials/foot.php'; ?>
