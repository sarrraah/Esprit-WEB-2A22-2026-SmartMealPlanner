<?php
/**
 * search_repas.php — Recherche des repas par recette
 *
 * Equivalent exact du workshop PDF : searchAlbums.php
 *   - Formulaire select des recettes (comme select des genres)
 *   - Affichage des repas correspondants (comme affichage des albums)
 */

defined('APP_ROOT') || require_once __DIR__ . '/../../config.php';

// a. Faire appel au Controller — equivalent : require_once "../controller/genreC.php"
require_once __DIR__ . '/../../controller/RepasRecetteController.php';

$repasRecetteC = new RepasRecetteController();

// Traitement du formulaire — equivalent workshop
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_recette']) && isset($_POST['search'])) {
        $idRecette = (int)$_POST['id_recette'];
        $list      = $repasRecetteC->afficherRepasByRecette($idRecette);
    }
}

// Toutes les recettes pour le select — equivalent afficherGenres()
$recettes = $repasRecetteC->afficherToutesRecettes();

$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$project = str_replace('\\', '/', dirname(__DIR__, 2));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);

$pageTitle = 'Repas par Recette - SmartMeal Admin';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/sidebar.php';
?>

<div class="admin-main">
    <div class="admin-topbar">
        <h5><i class="bi bi-search me-2" style="color:var(--accent)"></i>Recherche de Repas par Recette</h5>
        <a href="repas.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    <div class="admin-content">
        <div class="row justify-content-center">
            <div class="col-lg-9">

                <!-- b. Formulaire pour sélectionner une recette -->
                <!-- Equivalent : formulaire select genre du workshop -->
                <div class="admin-card card mb-4">
                    <div class="card-header">
                        <i class="bi bi-funnel me-2" style="color:var(--accent)"></i>
                        Recherche d'albums par recette
                    </div>
                    <div class="card-body p-4">
                        <form action="" method="POST" class="d-flex align-items-end gap-3 flex-wrap">

                            <div class="flex-grow-1">
                                <label for="id_recette" class="form-label fw-medium">
                                    Sélectionnez une recette :
                                </label>
                                <!-- Select des recettes — equivalent select des genres -->
                                <select name="id_recette" id="id_recette" class="form-select">
                                    <?php foreach ($recettes as $recette): ?>
                                        <option value="<?= $recette['id_recette'] ?>"
                                            <?= isset($idRecette) && $idRecette == $recette['id_recette'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($recette['nom_recette']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Bouton submit — equivalent input type="submit" name="search" -->
                            <div>
                                <input type="submit" name="search" value="Rechercher"
                                       class="btn btn-yummy">
                            </div>

                        </form>
                    </div>
                </div>

                <!-- c. Afficher les repas correspondants à la recette sélectionnée -->
                <!-- Equivalent : affichage albums correspondants au genre sélectionné -->
                <?php if (isset($list)): ?>
                <div class="admin-card card">
                    <div class="card-header">
                        <i class="bi bi-bowl-hot me-2" style="color:var(--accent)"></i>
                        Repas correspondants à la recette sélectionnée
                        <span class="badge ms-2" style="background:var(--accent)">
                            <?= count($list) ?>
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($list)): ?>
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2" style="opacity:.3;"></i>
                                Aucun repas trouvé pour cette recette.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table admin-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Nom du Repas</th>
                                            <th>Recette</th>
                                            <th>Type</th>
                                            <th>Calories</th>
                                            <th>Difficulté</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($list as $repas): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($repas['image_repas'])): ?>
                                                    <img src="<?= htmlspecialchars($baseUrl.'/'.$repas['image_repas']) ?>"
                                                         style="width:50px;height:50px;object-fit:cover;border-radius:8px;">
                                                <?php else: ?>
                                                    <div style="width:50px;height:50px;background:linear-gradient(135deg,#f8d7da,#f5c6cb);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                                        <i class="bi bi-egg-fried" style="color:#ce1212;opacity:.5;"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="fw-medium"><?= htmlspecialchars($repas['nom']) ?></td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?= htmlspecialchars($repas['nom_recette']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?= htmlspecialchars($repas['type_repas'] ?? '-') ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($repas['calories'] ?? '-') ?> kcal</td>
                                            <td>
                                                <?php
                                                $bc = match($repas['difficulte'] ?? 'Facile') {
                                                    'Facile'    => 'badge-facile',
                                                    'Moyen'     => 'badge-moyen',
                                                    'Difficile' => 'badge-difficile',
                                                    default     => 'bg-secondary'
                                                };
                                                ?>
                                                <span class="badge <?= $bc ?>">
                                                    <?= htmlspecialchars($repas['difficulte'] ?? 'Facile') ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="edit_repas.php?id=<?= $repas['id_repas'] ?>"
                                                   class="btn btn-sm btn-outline-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="../../controller/RepasController.php?action=delete&id=<?= $repas['id_repas'] ?>"
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Supprimer ce repas ?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/foot.php'; ?>
