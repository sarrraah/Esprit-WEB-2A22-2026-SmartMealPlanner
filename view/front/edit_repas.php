<?php
defined('APP_ROOT') || require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Repas.php';
require_once __DIR__ . '/../../model/Recette.php';

$repasModel   = new Repas();
$recetteModel = new Recette();
$id    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$repas = $id > 0 ? $repasModel->getRepasById($id) : null;
if (!$repas) { header('Location: repas.php'); exit; }
$recettes = $recetteModel->getAllRecettes();

$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$project = str_replace('\\', '/', dirname(__DIR__, 2));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);

$pageTitle  = 'Modifier le Repas - Smart Meal Planner';
$activePage = 'repas';
require_once __DIR__ . '/partials/header.php';
?>

<!-- Page Title -->
<div class="page-title dark-background" data-aos="fade">
    <div class="container position-relative">
        <h1>Modifier le Repas</h1>
        <nav class="breadcrumbs">
            <ol>
                <li><a href="home.php">Accueil</a></li>
                <li><a href="repas.php">Repas</a></li>
                <li class="current">Modifier</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Form Section -->
<section class="section">
    <div class="container" data-aos="fade-up">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm" style="border-radius:16px;">
                    <div class="card-body p-4 p-md-5">
                        <form action="<?= htmlspecialchars($baseUrl.'/controller/RepasController.php') ?>"
                              method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= $repas['id_repas'] ?>">
                            <input type="hidden" name="current_image" value="<?= htmlspecialchars($repas['image_repas'] ?? '') ?>">
                            <input type="hidden" name="from" value="front">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Nom du repas <span class="text-danger">*</span></label>
                                    <input type="text" name="nom" class="form-control form-control-lg"
                                           value="<?= htmlspecialchars($repas['nom']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Type de repas</label>
                                    <select name="type_repas" class="form-select form-select-lg">
                                        <?php foreach (['Petit-dejeuner'=>'Petit-déjeuner','Dejeuner'=>'Déjeuner','Diner'=>'Dîner','Collation'=>'Collation'] as $val => $label): ?>
                                            <option value="<?= $val ?>" <?= ($repas['type_repas'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Recette <span class="text-danger">*</span></label>
                                    <select name="id_recette" class="form-select form-select-lg" required>
                                        <option value="">-- Sélectionnez --</option>
                                        <?php foreach ($recettes as $rec): ?>
                                            <option value="<?= $rec['id_recette'] ?>"
                                                <?= ($repas['id_recette'] ?? 0) == $rec['id_recette'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($rec['nom_recette']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Calories (kcal)</label>
                                    <input type="number" step="0.1" min="0" name="calories" class="form-control"
                                           value="<?= htmlspecialchars($repas['calories'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Protéines (g)</label>
                                    <input type="number" step="0.1" min="0" name="proteines" class="form-control"
                                           value="<?= htmlspecialchars($repas['proteines'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Glucides (g)</label>
                                    <input type="number" step="0.1" min="0" name="glucides" class="form-control"
                                           value="<?= htmlspecialchars($repas['glucides'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Lipides (g)</label>
                                    <input type="number" step="0.1" min="0" name="lipides" class="form-control"
                                           value="<?= htmlspecialchars($repas['lipides'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium">Notes / Description</label>
                                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($repas['description'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium">Image du repas</label>
                                    <?php if (!empty($repas['image_repas'])): ?>
                                        <div class="mb-3">
                                            <img src="<?= htmlspecialchars($baseUrl.'/'.$repas['image_repas']) ?>"
                                                 alt="Image actuelle"
                                                 style="max-height:180px;border-radius:12px;object-fit:cover;">
                                            <p class="text-muted small mt-1">Image actuelle — laissez vide pour la conserver.</p>
                                        </div>
                                    <?php endif; ?>
                                    <div id="dropZone" onclick="document.getElementById('image_repas').click()"
                                         style="border:2px dashed #ce1212;border-radius:14px;padding:1.5rem;text-align:center;cursor:pointer;background:#fff8f8;transition:background .2s;"
                                         ondragover="event.preventDefault();this.style.background='#fde8e8'"
                                         ondragleave="this.style.background='#fff8f8'"
                                         ondrop="handleDrop(event)">
                                        <div id="dropContent">
                                            <i class="bi bi-cloud-arrow-up" style="font-size:2rem;color:#ce1212;"></i>
                                            <p class="mb-1 fw-medium mt-2">Nouvelle image</p>
                                            <p class="text-muted small mb-0">Glissez ou cliquez pour choisir</p>
                                        </div>
                                        <div id="prev1" class="d-none">
                                            <img id="previewImg1" src="" alt="Aperçu"
                                                 style="max-height:180px;max-width:100%;border-radius:10px;object-fit:cover;">
                                            <p class="text-muted small mt-2 mb-0" id="fileName1"></p>
                                            <button type="button" class="btn btn-sm btn-outline-danger mt-2"
                                                    onclick="event.stopPropagation();clearImage()">
                                                <i class="bi bi-x-circle me-1"></i>Supprimer
                                            </button>
                                        </div>
                                    </div>
                                    <input type="file" id="image_repas" name="image_repas"
                                           accept="image/*" class="d-none" onchange="previewImg(this)">
                                </div>
                            </div>
                            <div class="d-flex gap-3 mt-4">
                                <button type="submit" class="btn btn-warning btn-lg flex-fill">
                                    <i class="bi bi-save me-1"></i>Enregistrer les modifications
                                </button>
                                <a href="repas.php" class="btn btn-outline-secondary btn-lg">Annuler</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$extraJs = <<<JS
<script>
function previewImg(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('dropContent').classList.add('d-none');
        document.getElementById('prev1').classList.remove('d-none');
        document.getElementById('previewImg1').src = e.target.result;
        document.getElementById('fileName1').textContent = file.name + ' (' + (file.size/1024).toFixed(1) + ' KB)';
    };
    reader.readAsDataURL(file);
}
function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropZone').style.background = '#fff8f8';
    const file = e.dataTransfer.files[0];
    if (!file || !file.type.startsWith('image/')) return;
    const dt = new DataTransfer();
    dt.items.add(file);
    document.getElementById('image_repas').files = dt.files;
    previewImg(document.getElementById('image_repas'));
}
function clearImage() {
    document.getElementById('image_repas').value = '';
    document.getElementById('prev1').classList.add('d-none');
    document.getElementById('dropContent').classList.remove('d-none');
}
</script>
JS;
require_once __DIR__ . '/partials/footer.php';
?>
