<?php
defined('APP_ROOT') || require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Repas.php';

$repasModel = new Repas();
// Jointure repas + recette_repas directement dans le model
$repas      = $repasModel->getAllRepasWithRecette();

$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$project = str_replace('\\', '/', dirname(__DIR__, 2));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);

$pageTitle  = 'Nos Repas - Smart Meal Planner';
$activePage = 'repas';
require_once __DIR__ . '/partials/header.php';
?>

<div class="page-title dark-background" data-aos="fade">
    <div class="container position-relative">
        <h1>Nos Repas</h1>
        <p>Découvrez tous les repas enregistrés dans notre planificateur.</p>
        <nav class="breadcrumbs">
            <ol>
                <li><a href="home.php">Accueil</a></li>
                <li class="current">Repas</li>
            </ol>
        </nav>
    </div>
</div>

<section class="section">
    <div class="container" data-aos="fade-up">

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <i class="bi bi-check-circle me-2"></i>Opération réalisée avec succès.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-info alert-dismissible fade show mb-4">
                <i class="bi bi-trash me-2"></i>Repas supprimé avec succès.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0"><?= count($repas) ?> repas trouvé<?= count($repas) > 1 ? 's' : '' ?></h4>
        </div>

        <?php if (empty($repas)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                <p>Aucun repas enregistré.</p>
            </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($repas as $r): ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up">
                <div class="card h-100 border-0 shadow-sm" style="border-radius:14px;overflow:hidden;">
                    <?php if (!empty($r['image_repas'])): ?>
                        <img src="<?= htmlspecialchars($baseUrl.'/'.$r['image_repas']) ?>"
                             alt="<?= htmlspecialchars($r['nom']) ?>"
                             style="height:200px;object-fit:cover;width:100%;">
                    <?php else: ?>
                        <div style="height:200px;background:linear-gradient(135deg,#f8d7da,#f5c6cb);display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-egg-fried" style="font-size:3rem;color:#ce1212;opacity:.4;"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0 fw-bold"><?= htmlspecialchars($r['nom']) ?></h5>
                            <?php if (!empty($r['calories'])): ?>
                                <span class="badge bg-danger"><?= htmlspecialchars($r['calories']) ?> kcal</span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-1 mb-2 flex-wrap">
                            <?php if (!empty($r['type_repas'])): ?>
                                <span class="badge bg-secondary"><?= htmlspecialchars($r['type_repas']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($r['nom_recette'])): ?>
                                <span class="badge bg-primary"><?= htmlspecialchars($r['nom_recette']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($r['proteines']) || !empty($r['glucides']) || !empty($r['lipides'])): ?>
                        <div class="d-flex gap-2 small text-muted mb-2">
                            <?php if (!empty($r['proteines'])): ?><span><i class="bi bi-droplet me-1"></i><?= $r['proteines'] ?>g</span><?php endif; ?>
                            <?php if (!empty($r['glucides'])): ?><span><i class="bi bi-lightning me-1"></i><?= $r['glucides'] ?>g</span><?php endif; ?>
                            <?php if (!empty($r['lipides'])): ?><span><i class="bi bi-heart me-1"></i><?= $r['lipides'] ?>g</span><?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($r['description'])): ?>
                            <p class="card-text text-muted small">
                                <?= htmlspecialchars(mb_substr($r['description'], 0, 90)) . (mb_strlen($r['description']) > 90 ? '…' : '') ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white border-0 d-flex gap-2 pb-3 px-3">
                        <a href="detail_repas.php?id=<?= $r['id_repas'] ?>"
                           class="btn btn-sm btn-danger flex-fill">
                            <i class="bi bi-eye me-1"></i>Voir la recette
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
