<?php
defined('APP_ROOT') || require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Recette.php';

$recetteModel = new Recette();

// Récupérer les paramètres de tri depuis l'URL (GET)
// orderBy : colonne de tri — 'id_recette' par défaut, 'total_calories' pour trier par calories
// orderDir : direction — 'ASC' (croissant) ou 'DESC' (décroissant)
$orderBy  = $_GET['orderBy']  ?? 'id_recette';
$orderDir = $_GET['orderDir'] ?? 'ASC';

// Récupérer les recettes avec le nombre de repas et le total de calories, triées selon le choix
$recettes = $recetteModel->getAllRecettesWithRepasCount($orderBy, $orderDir);

$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$project = str_replace('\\', '/', dirname(__DIR__, 2));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);

$pageTitle = 'Gestion des Recettes - SmartMeal Admin';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/sidebar.php';
?>
<div class="admin-main">
    <div class="admin-topbar">
        <h5><i class="bi bi-journal-richtext me-2" style="color:var(--accent)"></i>Gestion des Recettes</h5>
        <div class="d-flex gap-2">
            <!-- Bouton export PDF : transmet le tri actif à la page d'export -->
            <a href="export_recettes_pdf.php?orderBy=<?= htmlspecialchars($orderBy) ?>&orderDir=<?= htmlspecialchars($orderDir) ?>"
               target="_blank"
               class="btn btn-outline-danger btn-sm">
                <i class="bi bi-file-earmark-pdf me-1"></i>Exporter PDF
            </a>
            <a href="add_recette.php" class="btn btn-yummy btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Ajouter une Recette
            </a>
        </div>
    </div>
    <div class="admin-content">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible alert-auto fade show"><i class="bi bi-check-circle me-2"></i>Opération réalisée.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-info alert-dismissible alert-auto fade show"><i class="bi bi-trash me-2"></i>Recette supprimée.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <!-- ── Barre de tri par calories ─────────────────────────────────── -->
        <div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
            <span class="text-muted small fw-semibold"><i class="bi bi-sort-down me-1"></i>Trier par calories :</span>

            <?php
            // Construire les URLs de tri en conservant les autres paramètres GET
            $urlAsc  = '?' . http_build_query(array_merge($_GET, ['orderBy' => 'total_calories', 'orderDir' => 'ASC']));
            $urlDesc = '?' . http_build_query(array_merge($_GET, ['orderBy' => 'total_calories', 'orderDir' => 'DESC']));
            $urlDef  = '?' . http_build_query(array_merge($_GET, ['orderBy' => 'id_recette',     'orderDir' => 'ASC']));

            $isCalAsc  = ($orderBy === 'total_calories' && $orderDir === 'ASC');
            $isCalDesc = ($orderBy === 'total_calories' && $orderDir === 'DESC');
            $isDef     = ($orderBy === 'id_recette');
            ?>

            <!-- Tri croissant : moins caloriques en premier -->
            <a href="<?= $urlAsc ?>"
               class="btn btn-sm <?= $isCalAsc ? 'btn-yummy' : 'btn-outline-secondary' ?>">
                <i class="bi bi-sort-numeric-up me-1"></i>Moins caloriques
            </a>

            <!-- Tri décroissant : plus caloriques en premier -->
            <a href="<?= $urlDesc ?>"
               class="btn btn-sm <?= $isCalDesc ? 'btn-yummy' : 'btn-outline-secondary' ?>">
                <i class="bi bi-sort-numeric-down me-1"></i>Plus caloriques
            </a>

            <!-- Réinitialiser le tri -->
            <a href="<?= $urlDef ?>"
               class="btn btn-sm <?= $isDef ? 'btn-secondary' : 'btn-outline-secondary' ?>">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Par défaut
            </a>

            <!-- Indicateur du tri actif -->
            <?php if ($orderBy === 'total_calories'): ?>
                <span class="badge bg-warning text-dark ms-1">
                    <i class="bi bi-fire me-1"></i>
                    Trié par calories (<?= $orderDir === 'ASC' ? '↑ croissant' : '↓ décroissant' ?>)
                </span>
            <?php endif; ?>
        </div>

        <?php if (empty($recettes)): ?>
            <div class="admin-card card"><div class="card-body p-5 text-center text-muted">
                <i class="bi bi-journal-x fs-1 d-block mb-3" style="color:var(--accent);opacity:.4;"></i>
                <p class="mb-3">Aucune recette.</p>
                <a href="add_recette.php" class="btn btn-yummy"><i class="bi bi-plus-lg me-1"></i>Créer une Recette</a>
            </div></div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($recettes as $rec): ?>
            <div class="col-lg-4 col-md-6">
                <div class="admin-card card h-100">
                    <?php if (!empty($rec['image_recette'])): ?>
                        <img src="<?= htmlspecialchars($baseUrl.'/'.$rec['image_recette']) ?>" style="height:190px;object-fit:cover;width:100%;" alt="">
                    <?php else: ?>
                        <div style="height:190px;background:linear-gradient(135deg,#e8f0fe,#c7d7fd);display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-journal-richtext" style="font-size:3.5rem;color:#0d6efd;opacity:.3;"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="fw-bold mb-0" style="font-family:'Amatic SC',sans-serif;font-size:1.4rem;"><?= htmlspecialchars($rec['nom_recette']) ?></h5>
                            <?php
                            $bc = match($rec['difficulte'] ?? 'Facile') { 'Facile'=>'badge-facile','Moyen'=>'badge-moyen','Difficile'=>'badge-difficile',default=>'bg-secondary' };
                            ?>
                            <span class="badge <?= $bc ?>"><?= htmlspecialchars($rec['difficulte'] ?? 'Facile') ?></span>
                        </div>
                        <div class="d-flex gap-2 mb-2">
                            <span class="badge bg-secondary">
                                <i class="bi bi-bowl-hot me-1"></i><?= $rec['nb_repas'] ?? 0 ?> repas
                            </span>
                            <?php if (($rec['total_calories'] ?? 0) > 0): ?>
                            <span class="badge bg-danger">
                                <i class="bi bi-fire me-1"></i><?= number_format($rec['total_calories'], 0, ',', ' ') ?> kcal total
                            </span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-3 text-muted small mb-2">
                            <?php if (!empty($rec['temps_prep'])): ?><span><i class="bi bi-clock me-1"></i><?= $rec['temps_prep'] ?> min</span><?php endif; ?>
                            <?php if (!empty($rec['temps_cuisson'])): ?><span><i class="bi bi-fire me-1"></i><?= $rec['temps_cuisson'] ?> min</span><?php endif; ?>
                            <span><i class="bi bi-people me-1"></i><?= $rec['nb_personnes'] ?> pers.</span>
                        </div>
                        <?php if (!empty($rec['etapes'])): ?>
                            <p class="text-muted small mb-0"><?= htmlspecialchars(mb_substr($rec['etapes'],0,80)).(mb_strlen($rec['etapes'])>80?'…':'') ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white border-0 d-flex gap-2 pb-3 px-3">
                        <a href="view_recette.php?id=<?= $rec['id_recette'] ?>" class="btn btn-sm btn-outline-primary flex-fill"><i class="bi bi-eye me-1"></i>Voir</a>
                        <a href="edit_recette.php?id=<?= $rec['id_recette'] ?>" class="btn btn-sm btn-outline-warning flex-fill"><i class="bi bi-pencil me-1"></i>Modifier</a>
                        <a href="export_recette_pdf.php?id=<?= $rec['id_recette'] ?>" target="_blank" class="btn btn-sm btn-outline-danger flex-fill"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
                        <a href="../../controller/RecetteController.php?action=delete&id=<?= $rec['id_recette'] ?>" class="btn btn-sm btn-outline-danger flex-fill" onclick="return confirm('Supprimer ?')"><i class="bi bi-trash me-1"></i>Supprimer</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/partials/foot.php'; ?>
