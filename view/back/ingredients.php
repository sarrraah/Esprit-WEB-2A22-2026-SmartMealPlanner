<?php
session_start();
defined('APP_ROOT') || require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Repas.php';
require_once __DIR__ . '/../../model/Ingredient.php';

$repasModel      = new Repas();
$ingredientModel = new Ingredient();

$idRepas = isset($_GET['id_repas']) ? (int)$_GET['id_repas'] : 0;
$repas   = $idRepas > 0 ? $repasModel->getRepasById($idRepas) : null;

if (!$repas) {
    header('Location: repas.php');
    exit;
}

// Ingrédients via la recette du repas
$idRecette   = (int)($repas['id_recette'] ?? 0);
$ingredients = $idRecette > 0 ? $ingredientModel->getByRecette($idRecette) : [];

// Edit mode
$editIngredient = null;
if (isset($_GET['edit'])) {
    $editIngredient = $ingredientModel->getById((int)$_GET['edit']);
}

$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$project = str_replace('\\', '/', dirname(__DIR__, 2));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);

$pageTitle = 'Ingrédients — ' . htmlspecialchars($repas['nom']) . ' - SmartMeal Admin';
require_once __DIR__ . '/partials/head.php';
?>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<div class="admin-main">
    <div class="admin-topbar">
        <div>
            <h5 style="margin:0;">
                <i class="bi bi-list-ul me-2" style="color:var(--accent)"></i>
                Ingrédients — <span style="color:var(--accent)"><?= htmlspecialchars($repas['nom']) ?></span>
            </h5>
        </div>
        <a href="repas.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour aux Repas
        </a>
    </div>

    <div class="admin-content">

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible alert-auto fade show">
            <i class="bi bi-check-circle me-2"></i>Opération réalisée avec succès.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-info alert-dismissible alert-auto fade show">
            <i class="bi bi-trash me-2"></i>Ingrédient supprimé.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- ── Formulaire Ajout / Modification ── -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-semibold">
                        <?= $editIngredient
                            ? '<i class="bi bi-pencil me-2 text-warning"></i>Modifier l\'ingrédient'
                            : '<i class="bi bi-basket me-2" style="color:#ce1212"></i>Ajouter un ingrédient à ce repas' ?>
                    </h6>
                </div>
                <div class="card-body pt-0">
                    <form action="<?= htmlspecialchars($baseUrl.'/controller/IngredientController.php') ?>"
                          method="POST" id="formIngredient" novalidate>
                        <?php
                        $ingErrors = $_SESSION['ing_errors'] ?? [];
                        unset($_SESSION['ing_errors'], $_SESSION['ing_old']);
                        if (!empty($ingErrors)):
                        ?>
                        <div class="alert alert-danger mb-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <ul class="mb-0">
                                <?php foreach ($ingErrors as $err): ?>
                                    <li><?= htmlspecialchars($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        <input type="hidden" name="action" value="<?= $editIngredient ? 'update' : 'add' ?>">
                        <input type="hidden" name="id_repas" value="<?= $idRepas ?>">
                        <?php if ($editIngredient): ?>
                            <input type="hidden" name="id_ingredient" value="<?= $editIngredient['id_ingredient'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label fw-medium">
                                <i class="bi bi-dot text-danger"></i>Nom de l'ingrédient <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nom_ingredient" class="form-control"
                                   placeholder="Ex: Poulet, Tomate, Farine, Huile d'olive..."
                                   value="<?= htmlspecialchars($editIngredient['nom_ingredient'] ?? '') ?>"
                                   required autofocus>
                            <small class="text-muted">Soyez précis : "Poulet" plutôt que "viande".</small>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-medium">
                                    <i class="bi bi-123 me-1 text-primary"></i>Quantité
                                </label>
                                <input type="number" step="0.01" min="0" name="quantite" class="form-control"
                                       placeholder="Ex: 200"
                                       value="<?= htmlspecialchars($editIngredient['quantite'] ?? '') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-medium">
                                    <i class="bi bi-rulers me-1 text-success"></i>Unité de mesure
                                </label>
                                <select name="unite" class="form-select">
                                    <?php
                                    $unites = ['g'=>'g — grammes','kg'=>'kg — kilogrammes','ml'=>'ml — millilitres','L'=>'L — litres','pièce'=>'pièce(s)','cuillère à soupe'=>'cuillère à soupe','cuillère à café'=>'cuillère à café','tasse'=>'tasse','pincée'=>'pincée','tranche'=>'tranche(s)'];
                                    $currentUnite = $editIngredient['unite'] ?? 'g';
                                    foreach ($unites as $v => $l):
                                    ?>
                                        <option value="<?= $v ?>" <?= $currentUnite === $v ? 'selected' : '' ?>><?= $l ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-yummy <?= $editIngredient ? '' : 'flex-fill' ?>">
                                <i class="bi bi-<?= $editIngredient ? 'save' : 'plus-circle' ?> me-1"></i>
                                <?= $editIngredient ? 'Enregistrer les modifications' : 'Ajouter l\'ingrédient' ?>
                            </button>
                            <?php if ($editIngredient): ?>
                                <a href="ingredients.php?id_repas=<?= $idRepas ?>"
                                   class="btn btn-outline-secondary">Annuler</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Repas info card -->
            <div class="card border-0 shadow-sm mt-4" style="border-radius:14px;">
                <div class="card-body p-3">
                    <h6 class="fw-semibold mb-2">Infos du repas</h6>
                    <div class="d-flex flex-column gap-1 small text-muted">
                        <span><i class="bi bi-bowl-hot me-2 text-danger"></i><?= htmlspecialchars($repas['nom']) ?></span>
                        <?php if (!empty($repas['calories'])): ?>
                            <span><i class="bi bi-fire me-2 text-warning"></i><?= $repas['calories'] ?> kcal</span>
                        <?php endif; ?>
                        <span><i class="bi bi-tag me-2 text-primary"></i><?= htmlspecialchars($repas['type_repas']) ?></span>
                        <span><i class="bi bi-list-ul me-2 text-success"></i><?= count($ingredients) ?> ingrédient(s)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Liste des ingrédients ── -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">
                        Liste des ingrédients
                        <span class="badge bg-danger ms-2"><?= count($ingredients) ?></span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($ingredients)): ?>
                        <div class="p-5 text-center text-muted">
                            <i class="bi bi-basket fs-1 d-block mb-3"></i>
                            <p>Aucun ingrédient ajouté.</p>
                            <p class="small">Utilisez le formulaire à gauche pour ajouter des ingrédients.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Ingrédient</th>
                                        <th>Quantité</th>
                                        <th>Unité</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ingredients as $ing): ?>
                                    <tr <?= (isset($_GET['edit']) && $_GET['edit'] == $ing['id_ingredient']) ? 'class="table-warning"' : '' ?>>
                                        <td class="text-muted small"><?= $ing['id_ingredient'] ?></td>
                                        <td class="fw-medium">
                                            <i class="bi bi-dot text-danger"></i>
                                            <?= htmlspecialchars($ing['nom_ingredient']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($ing['quantite'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?= htmlspecialchars($ing['unite'] ?? '-') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="ingredients.php?id_repas=<?= $idRepas ?>&edit=<?= $ing['id_ingredient'] ?>"
                                               class="btn btn-sm btn-outline-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?= htmlspecialchars($baseUrl.'/controller/IngredientController.php?action=delete&id='.$ing['id_ingredient'].'&id_repas='.$idRepas) ?>"
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Supprimer cet ingrédient ?')">
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
        </div>

    </div>
</div>

<?php
$extraJs = <<<JS
<script>
smAttachRealtime('formIngredient',
    ['nom_ingredient'],   // texte : pas de chiffres
    ['quantite']          // numérique : pas de lettres
);
smAttachSubmit('formIngredient', [
    { name: 'nom_ingredient', type: 'nom',    label: "Le nom de l'ingrédient" },
    { name: 'quantite',       type: 'number', label: 'La quantité', min: 0 },
]);
</script>
JS;
require_once __DIR__ . '/partials/foot.php';