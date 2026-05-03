<?php
defined('APP_ROOT') || require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Recette.php';

$recetteModel = new Recette();
$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$recette = $id > 0 ? $recetteModel->getRecetteWithRepas($id) : null;

if (!$recette) {
    header('Location: recette.php');
    exit;
}

$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$project = str_replace('\\', '/', dirname(__DIR__, 2));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);

$pageTitle = htmlspecialchars($recette['nom_recette']) . ' - SmartMeal Admin';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/sidebar.php';
?>

<div class="admin-main">
    <div class="admin-topbar">
        <h5>
            <i class="bi bi-journal-richtext me-2" style="color:var(--accent)"></i>
            <?= htmlspecialchars($recette['nom_recette']) ?>
        </h5>
        <div class="d-flex gap-2">
            <a href="export_recette_pdf.php?id=<?= $recette['id_recette'] ?>"
               target="_blank"
               class="btn btn-sm btn-outline-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i>Exporter PDF
            </a>
            <a href="edit_recette.php?id=<?= $recette['id_recette'] ?>" class="btn btn-sm btn-outline-warning">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
            <a href="recette.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Retour
            </a>
        </div>
    </div>

    <div class="admin-content">
        <div class="row g-4">

            <!-- ── Colonne gauche : infos recette ── -->
            <div class="col-lg-4">

                <!-- Photo -->
                <div class="admin-card card mb-4">
                    <?php if (!empty($recette['image_recette'])): ?>
                        <img src="<?= htmlspecialchars($baseUrl.'/'.$recette['image_recette']) ?>"
                             style="height:220px;object-fit:cover;width:100%;" alt="">
                    <?php else: ?>
                        <div style="height:220px;background:linear-gradient(135deg,#e8f0fe,#c7d7fd);display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-journal-richtext" style="font-size:4rem;color:#0d6efd;opacity:.3;"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h4 style="font-family:'Amatic SC',sans-serif;font-size:1.8rem;color:#37373f;">
                            <?= htmlspecialchars($recette['nom_recette']) ?>
                        </h4>

                        <!-- Badges -->
                        <div class="d-flex gap-2 flex-wrap mb-3">
                            <?php
                            $bc = match($recette['difficulte'] ?? 'Facile') {
                                'Facile'    => 'badge-facile',
                                'Moyen'     => 'badge-moyen',
                                'Difficile' => 'badge-difficile',
                                default     => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $bc ?>"><?= htmlspecialchars($recette['difficulte'] ?? 'Facile') ?></span>
                            <span class="badge bg-secondary"><?= count($recette['repas']) ?> repas</span>
                        </div>

                        <!-- Infos -->
                        <div class="d-flex flex-column gap-2 small">
                            <?php if (!empty($recette['temps_prep'])): ?>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-clock text-primary fs-5"></i>
                                <div>
                                    <div class="text-muted" style="font-size:.75rem;">Préparation</div>
                                    <div class="fw-medium"><?= $recette['temps_prep'] ?> min</div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($recette['temps_cuisson'])): ?>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-fire text-danger fs-5"></i>
                                <div>
                                    <div class="text-muted" style="font-size:.75rem;">Cuisson</div>
                                    <div class="fw-medium"><?= $recette['temps_cuisson'] ?> min</div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-people text-success fs-5"></i>
                                <div>
                                    <div class="text-muted" style="font-size:.75rem;">Personnes</div>
                                    <div class="fw-medium"><?= $recette['nb_personnes'] ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Étapes de préparation -->
                <?php if (!empty($recette['etapes'])): ?>
                <div class="admin-card card">
                    <div class="card-header">
                        <i class="bi bi-list-ol me-2" style="color:var(--accent)"></i>Étapes de préparation
                    </div>
                    <div class="card-body">
                        <div style="white-space:pre-line;font-size:.9rem;line-height:1.7;">
                            <?= htmlspecialchars($recette['etapes']) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- ── Colonne droite : repas associés ── -->
            <div class="col-lg-8">
                <div class="admin-card card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>
                            <i class="bi bi-bowl-hot me-2" style="color:var(--accent)"></i>
                            Repas utilisant cette recette
                            <span class="badge ms-1" style="background:var(--accent)"><?= count($recette['repas']) ?></span>
                        </span>
                        <a href="add_repas.php" class="btn btn-yummy btn-sm">
                            <i class="bi bi-plus-lg me-1"></i>Nouveau Repas
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recette['repas'])): ?>
                            <div class="p-5 text-center text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3" style="opacity:.3;"></i>
                                <p>Aucun repas n'utilise encore cette recette.</p>
                                <a href="add_repas.php" class="btn btn-yummy btn-sm">
                                    <i class="bi bi-plus-lg me-1"></i>Créer un repas avec cette recette
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="accordion" id="repasAccordion">
                                <?php foreach ($recette['repas'] as $i => $r): ?>
                                <div class="accordion-item border-0 border-bottom">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#repas<?= $r['id_repas'] ?>">
                                            <div class="d-flex align-items-center gap-3 w-100 me-3">
                                                <!-- Miniature -->
                                                <?php if (!empty($r['image_repas'])): ?>
                                                    <img src="<?= htmlspecialchars($baseUrl.'/'.$r['image_repas']) ?>"
                                                         style="width:44px;height:44px;object-fit:cover;border-radius:8px;flex-shrink:0;">
                                                <?php else: ?>
                                                    <div style="width:44px;height:44px;background:linear-gradient(135deg,#f8d7da,#f5c6cb);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                        <i class="bi bi-egg-fried" style="color:#ce1212;opacity:.5;font-size:.9rem;"></i>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="flex-grow-1">
                                                    <div class="fw-bold" style="font-family:'Amatic SC',sans-serif;font-size:1.2rem;">
                                                        <?= htmlspecialchars($r['nom']) ?>
                                                    </div>
                                                    <div class="d-flex gap-2 small text-muted">
                                                        <span><i class="bi bi-tag me-1"></i><?= htmlspecialchars($r['type_repas'] ?? '-') ?></span>
                                                        <?php if (!empty($r['calories'])): ?>
                                                            <span><i class="bi bi-fire me-1"></i><?= $r['calories'] ?> kcal</span>
                                                        <?php endif; ?>
                                                        <span><i class="bi bi-basket me-1"></i><?= count($r['ingredients']) ?> ingrédient(s)</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="repas<?= $r['id_repas'] ?>"
                                         class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                                         data-bs-parent="#repasAccordion">
                                        <div class="accordion-body pt-2">
                                            <div class="row g-3">

                                                <!-- Macros -->
                                                <?php if (!empty($r['proteines']) || !empty($r['glucides']) || !empty($r['lipides'])): ?>
                                                <div class="col-12">
                                                    <div class="d-flex gap-3 flex-wrap">
                                                        <?php if (!empty($r['proteines'])): ?>
                                                        <div class="text-center px-3 py-2 rounded-3" style="background:#e8f0fe;">
                                                            <div class="fw-bold text-primary"><?= $r['proteines'] ?>g</div>
                                                            <div class="text-muted" style="font-size:.75rem;">Protéines</div>
                                                        </div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($r['glucides'])): ?>
                                                        <div class="text-center px-3 py-2 rounded-3" style="background:#fff3cd;">
                                                            <div class="fw-bold text-warning"><?= $r['glucides'] ?>g</div>
                                                            <div class="text-muted" style="font-size:.75rem;">Glucides</div>
                                                        </div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($r['lipides'])): ?>
                                                        <div class="text-center px-3 py-2 rounded-3" style="background:#fde8e8;">
                                                            <div class="fw-bold text-danger"><?= $r['lipides'] ?>g</div>
                                                            <div class="text-muted" style="font-size:.75rem;">Lipides</div>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php endif; ?>

                                                <!-- Ingrédients -->
                                                <div class="col-md-6">
                                                    <h6 class="fw-semibold mb-2" style="font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:#6c757d;">
                                                        <i class="bi bi-basket me-1" style="color:var(--accent)"></i>Ingrédients
                                                    </h6>
                                                    <?php if (empty($r['ingredients'])): ?>
                                                        <p class="text-muted small">
                                                            Aucun ingrédient.
                                                            <a href="ingredients.php?id_repas=<?= $r['id_repas'] ?>">Ajouter</a>
                                                        </p>
                                                    <?php else: ?>
                                                        <ul class="list-unstyled mb-0">
                                                            <?php foreach ($r['ingredients'] as $ing): ?>
                                                            <li class="d-flex align-items-center gap-2 mb-1 small">
                                                                <i class="bi bi-dot" style="color:var(--accent);font-size:1.2rem;"></i>
                                                                <span class="fw-medium"><?= htmlspecialchars($ing['nom_ingredient']) ?></span>
                                                                <?php if (!empty($ing['quantite'])): ?>
                                                                    <span class="text-muted ms-auto">
                                                                        <?= htmlspecialchars($ing['quantite']) ?>
                                                                        <?= htmlspecialchars($ing['unite'] ?? '') ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                            </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Description -->
                                                <?php if (!empty($r['description'])): ?>
                                                <div class="col-md-6">
                                                    <h6 class="fw-semibold mb-2" style="font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:#6c757d;">
                                                        <i class="bi bi-card-text me-1" style="color:var(--accent)"></i>Notes
                                                    </h6>
                                                    <p class="text-muted small mb-0"><?= htmlspecialchars($r['description']) ?></p>
                                                </div>
                                                <?php endif; ?>

                                                <!-- Actions -->
                                                <div class="col-12 d-flex gap-2 pt-1">
                                                    <a href="ingredients.php?id_repas=<?= $r['id_repas'] ?>"
                                                       class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-list-ul me-1"></i>Ingrédients
                                                    </a>
                                                    <a href="edit_repas.php?id=<?= $r['id_repas'] ?>"
                                                       class="btn btn-sm btn-outline-warning">
                                                        <i class="bi bi-pencil me-1"></i>Modifier
                                                    </a>
                                                    <a href="../../controller/RepasController.php?action=delete&id=<?= $r['id_repas'] ?>"
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Supprimer ce repas ?')">
                                                        <i class="bi bi-trash me-1"></i>Supprimer
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/foot.php'; ?>
