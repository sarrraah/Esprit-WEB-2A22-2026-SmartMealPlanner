<?php
session_start();
defined('APP_ROOT') || require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Repas.php';
require_once __DIR__ . '/../../model/Recette.php';
require_once __DIR__ . '/../../model/Ingredient.php';

$formErrors = $_SESSION['repas_errors'] ?? [];
$formOld    = $_SESSION['repas_old'] ?? [];
unset($_SESSION['repas_errors'], $_SESSION['repas_old']);

$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$project = str_replace('\\', '/', dirname(__DIR__, 2));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);

$repasModel      = new Repas();
$recetteModel    = new Recette();
$ingredientModel = new Ingredient();
$recettes        = $recetteModel->getAllRecettes();

// Step 2: repas already created, manage its ingredients
$idRepas = isset($_GET['id_repas']) ? (int)$_GET['id_repas'] : 0;
$repas   = $idRepas > 0 ? $repasModel->getRepasById($idRepas) : null;
$ingredients = $idRepas > 0 ? $ingredientModel->getByRepas($idRepas) : [];

$editIngredient = null;
if ($idRepas > 0 && isset($_GET['edit_ing'])) {
    $editIngredient = $ingredientModel->getById((int)$_GET['edit_ing']);
}

$pageTitle = 'Ajouter un Repas - SmartMeal Admin';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/sidebar.php';
?>
<div class="admin-main">
    <div class="admin-topbar">
        <h5><i class="bi bi-plus-circle me-2" style="color:var(--accent)"></i>
            <?= $repas ? 'Repas : '.htmlspecialchars($repas['nom']) : 'Ajouter un Repas' ?>
        </h5>
        <a href="repas.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Retour</a>
    </div>
    <div class="admin-content">

        <?php if (!$repas): ?>
        <!-- ══ STEP 1: Create repas ══ -->
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="admin-card card">
                    <div class="card-body p-4">
                        <?php if (empty($recettes)): ?>
                            <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Aucune catégorie. <a href="add_recette.php">Créez-en une d'abord.</a></div>
                        <?php endif; ?>
                        <form action="<?= htmlspecialchars($baseUrl.'/controller/RepasController.php') ?>"
                              method="POST" enctype="multipart/form-data" id="formRepas" novalidate>
                            <input type="hidden" name="from" value="back">
                            <?php if (!empty($formErrors)): ?>
                            <div class="alert alert-danger mb-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Erreurs de saisie :</strong>
                                <ul class="mb-0 mt-1">
                                    <?php foreach ($formErrors as $err): ?>
                                        <li><?= htmlspecialchars($err) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                            <div class="row g-3">

                                <!-- Infos générales -->
                                <div class="col-12">
                                    <h6 class="fw-bold text-muted mb-0" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.08em;">
                                        <i class="bi bi-info-circle me-1" style="color:var(--accent)"></i>Informations générales
                                    </h6>
                                    <hr class="mt-1 mb-2">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Nom du repas <span class="text-danger">*</span></label>
                                    <input type="text" name="nom" class="form-control" placeholder="Ex: Salade César" required autofocus>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Type de repas</label>
                                    <select name="type_repas" class="form-select">
                                        <option value="Petit-dejeuner">🌅 Petit-déjeuner</option>
                                        <option value="Dejeuner" selected>☀️ Déjeuner</option>
                                        <option value="Diner">🌙 Dîner</option>
                                        <option value="Collation">🍎 Collation</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Catégorie <span class="text-danger">*</span></label>
                                    <select name="id_recette" class="form-select" required <?= empty($recettes)?'disabled':'' ?>>
                                        <option value="">-- Sélectionnez une catégorie --</option>
                                        <?php foreach ($recettes as $rec): ?>
                                            <option value="<?= $rec['id_recette'] ?>"><?= htmlspecialchars($rec['nom_recette']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium"><i class="bi bi-fire me-1 text-danger"></i>Calories (kcal)</label>
                                    <input type="number" step="0.1" min="0" name="calories" class="form-control" placeholder="Ex: 450">
                                </div>

                                <!-- Valeurs nutritionnelles -->
                                <div class="col-12 mt-2">
                                    <h6 class="fw-bold text-muted mb-0" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.08em;">
                                        <i class="bi bi-heart-pulse me-1" style="color:var(--accent)"></i>Valeurs nutritionnelles
                                    </h6>
                                    <hr class="mt-1 mb-2">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium"><i class="bi bi-droplet me-1 text-primary"></i>Protéines (g)</label>
                                    <input type="number" step="0.1" min="0" name="proteines" class="form-control" placeholder="Ex: 25">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium"><i class="bi bi-lightning me-1 text-warning"></i>Glucides (g)</label>
                                    <input type="number" step="0.1" min="0" name="glucides" class="form-control" placeholder="Ex: 50">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium"><i class="bi bi-heart me-1 text-danger"></i>Lipides (g)</label>
                                    <input type="number" step="0.1" min="0" name="lipides" class="form-control" placeholder="Ex: 15">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium">Notes / Description</label>
                                    <textarea name="description" class="form-control" rows="2" placeholder="Notes sur ce repas..."></textarea>
                                </div>

                                <!-- Photo -->
                                <div class="col-12 mt-2">
                                    <h6 class="fw-bold text-muted mb-0" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.08em;">
                                        <i class="bi bi-camera me-1" style="color:var(--accent)"></i>Photo du repas
                                    </h6>
                                    <hr class="mt-1 mb-2">
                                </div>
                                <div class="col-12">
                                    <div class="drop-zone" id="dropZone" onclick="document.getElementById('image_repas').click()"
                                         ondragover="event.preventDefault();this.style.background='#fde8e8'"
                                         ondragleave="this.style.background='#fff8f8'"
                                         ondrop="handleDrop(event)">
                                        <div id="dropContent">
                                            <i class="bi bi-cloud-arrow-up" style="font-size:2rem;color:var(--accent);"></i>
                                            <p class="mb-1 fw-medium mt-2">Glissez une image ici</p>
                                            <p class="text-muted small mb-0">ou cliquez — JPG, PNG, WEBP</p>
                                        </div>
                                        <div id="prev1" class="d-none">
                                            <img id="previewImg1" src="" style="max-height:160px;max-width:100%;border-radius:10px;object-fit:cover;">
                                            <p class="text-muted small mt-2 mb-0" id="fileName1"></p>
                                            <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="event.stopPropagation();clearImage()"><i class="bi bi-x-circle me-1"></i>Supprimer</button>
                                        </div>
                                    </div>
                                    <input type="file" id="image_repas" name="image_repas" accept="image/*" class="d-none" onchange="previewImg(this)">
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-yummy" <?= empty($recettes)?'disabled':'' ?>>
                                    <i class="bi bi-arrow-right-circle me-1"></i>Créer le Repas et ajouter les ingrédients
                                </button>
                                <a href="repas.php" class="btn btn-outline-secondary">Annuler</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- ══ STEP 2: Add ingredients ══ -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible alert-auto fade show"><i class="bi bi-check-circle me-2"></i>Ingrédient ajouté.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-info alert-dismissible alert-auto fade show"><i class="bi bi-trash me-2"></i>Ingrédient supprimé.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left: ingredient form + repas info -->
            <div class="col-lg-4">
                <div class="admin-card card">
                    <div class="card-header">
                        <i class="bi bi-basket me-2" style="color:var(--accent)"></i>
                        <?= $editIngredient ? 'Modifier l\'ingrédient' : 'Ajouter un ingrédient à ce repas' ?>
                    </div>
                    <div class="card-body">
                        <form action="<?= htmlspecialchars($baseUrl.'/controller/IngredientController.php') ?>" method="POST" id="formIngredient" novalidate>
                            <input type="hidden" name="action" value="<?= $editIngredient ? 'update' : 'add' ?>">
                            <input type="hidden" name="id_repas" value="<?= $idRepas ?>">
                            <input type="hidden" name="redirect" value="add_repas">
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
                                        $unites = ['g'=>'g — grammes','kg'=>'kg — kilogrammes','ml'=>'ml — millilitres','L'=>'L — litres','pièce'=>'pièce(s)','cuillère à soupe'=>'c. à soupe','cuillère à café'=>'c. à café','tasse'=>'tasse','pincée'=>'pincée','tranche'=>'tranche(s)'];
                                        $cur = $editIngredient['unite'] ?? 'g';
                                        foreach ($unites as $v => $l):
                                        ?>
                                            <option value="<?= $v ?>" <?= $cur===$v?'selected':'' ?>><?= $l ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-yummy flex-fill">
                                    <i class="bi bi-<?= $editIngredient ? 'save' : 'plus-circle' ?> me-1"></i>
                                    <?= $editIngredient ? 'Enregistrer' : 'Ajouter l\'ingrédient' ?>
                                </button>
                                <?php if ($editIngredient): ?>
                                    <a href="add_repas.php?id_repas=<?= $idRepas ?>" class="btn btn-outline-secondary">Annuler</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Repas info -->
                <div class="admin-card card mt-4">
                    <div class="card-body p-3">
                        <h6 class="fw-semibold mb-2" style="font-family:'Amatic SC',sans-serif;font-size:1.2rem;">Infos du repas</h6>
                        <div class="d-flex flex-column gap-1 small text-muted">
                            <span><i class="bi bi-bowl-hot me-2" style="color:var(--accent)"></i><?= htmlspecialchars($repas['nom']) ?></span>
                            <?php if (!empty($repas['calories'])): ?>
                                <span><i class="bi bi-fire me-2 text-warning"></i><?= $repas['calories'] ?> kcal</span>
                            <?php endif; ?>
                            <span><i class="bi bi-tag me-2 text-primary"></i><?= htmlspecialchars($repas['type_repas'] ?? '-') ?></span>
                            <span><i class="bi bi-list-ul me-2" style="color:var(--accent)"></i><?= count($ingredients) ?> ingrédient(s)</span>
                        </div>
                        <hr>
                        <a href="repas.php" class="btn btn-yummy btn-sm w-100">
                            <i class="bi bi-check-circle me-1"></i>Terminer
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right: ingredients list -->
            <div class="col-lg-8">
                <div class="admin-card card">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-list-ul" style="color:var(--accent)"></i>
                        Liste des ingrédients
                        <span class="badge ms-1" style="background:var(--accent)"><?= count($ingredients) ?></span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($ingredients)): ?>
                            <div class="p-5 text-center text-muted">
                                <i class="bi bi-basket fs-1 d-block mb-3" style="opacity:.3;"></i>
                                <p class="mb-1">Aucun ingrédient ajouté.</p>
                                <p class="small">Utilisez le formulaire à gauche pour ajouter des ingrédients.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table admin-table mb-0">
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
                                        <tr <?= (isset($_GET['edit_ing']) && $_GET['edit_ing'] == $ing['id_ingredient']) ? 'class="table-warning"' : '' ?>>
                                            <td class="text-muted small"><?= $ing['id_ingredient'] ?></td>
                                            <td class="fw-medium">
                                                <i class="bi bi-dot" style="color:var(--accent)"></i>
                                                <?= htmlspecialchars($ing['nom_ingredient']) ?>
                                            </td>
                                            <td><?= htmlspecialchars($ing['quantite'] ?? '-') ?></td>
                                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($ing['unite'] ?? '-') ?></span></td>
                                            <td class="text-center">
                                                <a href="add_repas.php?id_repas=<?= $idRepas ?>&edit_ing=<?= $ing['id_ingredient'] ?>"
                                                   class="btn btn-sm btn-outline-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="<?= htmlspecialchars($baseUrl.'/controller/IngredientController.php?action=delete&id='.$ing['id_ingredient'].'&id_repas='.$idRepas.'&redirect=add_repas') ?>"
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
        <?php endif; ?>

    </div>
</div>
<?php
$extraJs = <<<JS
<script>
// ── Validation formulaire repas (step 1) ──────────────────────────────────────
smAttachRealtime('formRepas',
    ['nom'],
    ['calories','proteines','glucides','lipides']
);
smAttachSubmit('formRepas', [
    { name: 'nom',        type: 'nom',    label: 'Le nom du repas' },
    { name: 'id_recette', type: 'select', label: 'La catégorie' },
    { name: 'calories',   type: 'number', label: 'Les calories',  min: 0 },
    { name: 'proteines',  type: 'number', label: 'Les protéines', min: 0 },
    { name: 'glucides',   type: 'number', label: 'Les glucides',  min: 0 },
    { name: 'lipides',    type: 'number', label: 'Les lipides',   min: 0 },
]);

// ── Validation formulaire ingrédients (step 2) ────────────────────────────────
smAttachRealtime('formIngredient',
    ['nom_ingredient'],
    ['quantite']
);
smAttachSubmit('formIngredient', [
    { name: 'nom_ingredient', type: 'nom',    label: "Le nom de l'ingrédient" },
    { name: 'quantite',       type: 'number', label: 'La quantité', min: 0 },
]);

// ── Image preview ─────────────────────────────────────────────────────────────
function previewImg(input) {
    if (!input.files||!input.files[0]) return;
    const file=input.files[0], reader=new FileReader();
    reader.onload=e=>{
        document.getElementById('dropContent').classList.add('d-none');
        document.getElementById('prev1').classList.remove('d-none');
        document.getElementById('previewImg1').src=e.target.result;
        document.getElementById('fileName1').textContent=file.name+' ('+(file.size/1024).toFixed(1)+' KB)';
    };
    reader.readAsDataURL(file);
}
function handleDrop(e){
    e.preventDefault();
    document.getElementById('dropZone').style.background='#fff8f8';
    const file=e.dataTransfer.files[0];
    if(!file||!file.type.startsWith('image/')) return;
    const dt=new DataTransfer(); dt.items.add(file);
    document.getElementById('image_repas').files=dt.files;
    previewImg(document.getElementById('image_repas'));
}
function clearImage(){
    document.getElementById('image_repas').value='';
    document.getElementById('prev1').classList.add('d-none');
    document.getElementById('dropContent').classList.remove('d-none');
}
</script>
JS;
require_once __DIR__ . '/partials/foot.php';
?>
