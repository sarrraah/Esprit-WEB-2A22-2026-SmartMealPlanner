<?php
session_start();
defined('APP_ROOT') || require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Repas.php';
require_once __DIR__ . '/../../model/Recette.php';

$formErrors = $_SESSION['repas_errors'] ?? [];
$formOld    = $_SESSION['repas_old'] ?? [];
unset($_SESSION['repas_errors'], $_SESSION['repas_old']);

$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$project = str_replace('\\', '/', dirname(__DIR__, 2));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);

$repasModel   = new Repas();
$recetteModel = new Recette();
$recettes     = $recetteModel->getAllRecettes();

$pageTitle = 'Ajouter un Repas - SmartMeal Admin';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/sidebar.php';
?>
<div class="admin-main">
    <div class="admin-topbar">
        <h5><i class="bi bi-plus-circle me-2" style="color:var(--accent)"></i>Ajouter un Repas</h5>
        <a href="repas.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Retour</a>
    </div>
    <div class="admin-content">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="admin-card card">
                    <div class="card-body p-4">
                        <?php if (empty($recettes)): ?>
                            <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Aucune recette disponible. <a href="add_recette.php">Créez-en une d'abord.</a></div>
                        <?php endif; ?>
                        <?php if (!empty($formErrors)): ?>
                            <div class="alert alert-danger mb-3">
                                <i class="bi bi-exclamation-triangle me-2"></i><strong>Erreurs :</strong>
                                <ul class="mb-0 mt-1">
                                    <?php foreach ($formErrors as $err): ?>
                                        <li><?= htmlspecialchars($err) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <form action="<?= htmlspecialchars($baseUrl.'/controller/RepasController.php') ?>"
                              method="POST" enctype="multipart/form-data" id="formRepas">
                            <input type="hidden" name="from" value="back">
                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="fw-bold text-muted mb-0" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.08em;">
                                        <i class="bi bi-info-circle me-1" style="color:var(--accent)"></i>Informations générales
                                    </h6>
                                    <hr class="mt-1 mb-2">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Nom du repas <span class="text-danger">*</span></label>
                                    <input type="text" name="nom" class="form-control"
                                           placeholder="Ex: Salade César"
                                           value="<?= htmlspecialchars($formOld['nom'] ?? '') ?>">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Type de repas</label>
                                    <select name="type_repas" class="form-select">
                                        <?php foreach (['Petit-dejeuner'=>'🌅 Petit-déjeuner','Dejeuner'=>'☀️ Déjeuner','Diner'=>'🌙 Dîner','Collation'=>'🍎 Collation'] as $v=>$l): ?>
                                            <option value="<?= $v ?>" <?= ($formOld['type_repas'] ?? 'Dejeuner') === $v ? 'selected' : '' ?>><?= $l ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Recette <span class="text-danger">*</span></label>
                                    <select name="id_recette" class="form-select" <?= empty($recettes)?'disabled':'' ?>>
                                        <option value="">-- Sélectionnez une recette --</option>
                                        <?php foreach ($recettes as $rec): ?>
                                            <option value="<?= $rec['id_recette'] ?>"
                                                <?= ($formOld['id_recette'] ?? '') == $rec['id_recette'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($rec['nom_recette']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Calories (kcal)</label>
                                    <input type="text" name="calories" class="form-control"
                                           placeholder="Ex: 450"
                                           value="<?= htmlspecialchars($formOld['calories'] ?? '') ?>">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-12 mt-2">
                                    <h6 class="fw-bold text-muted mb-0" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.08em;">
                                        <i class="bi bi-heart-pulse me-1" style="color:var(--accent)"></i>Valeurs nutritionnelles
                                    </h6>
                                    <hr class="mt-1 mb-2">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Protéines (g)</label>
                                    <input type="text" name="proteines" class="form-control"
                                           placeholder="Ex: 25"
                                           value="<?= htmlspecialchars($formOld['proteines'] ?? '') ?>">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Glucides (g)</label>
                                    <input type="text" name="glucides" class="form-control"
                                           placeholder="Ex: 50"
                                           value="<?= htmlspecialchars($formOld['glucides'] ?? '') ?>">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Lipides (g)</label>
                                    <input type="text" name="lipides" class="form-control"
                                           placeholder="Ex: 15"
                                           value="<?= htmlspecialchars($formOld['lipides'] ?? '') ?>">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium">Notes / Description</label>
                                    <textarea name="description" class="form-control" rows="2"
                                              placeholder="Notes sur ce repas..."><?= htmlspecialchars($formOld['description'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12 mt-2">
                                    <h6 class="fw-bold text-muted mb-0" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.08em;">
                                        <i class="bi bi-camera me-1" style="color:var(--accent)"></i>Photo du repas
                                    </h6>
                                    <hr class="mt-1 mb-2">
                                    <div class="alert alert-info mb-2" style="font-size:.82rem;">
                                        <i class="bi bi-info-circle me-1"></i>
                                        La photo sera automatiquement copiée dans la recette associée.
                                    </div>
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
                                            <button type="button" class="btn btn-sm btn-outline-danger mt-2"
                                                    onclick="event.stopPropagation();clearImage()">
                                                <i class="bi bi-x-circle me-1"></i>Supprimer
                                            </button>
                                        </div>
                                    </div>
                                    <input type="file" id="image_repas" name="image_repas"
                                           class="d-none" onchange="previewImg(this)">
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-yummy" <?= empty($recettes)?'disabled':'' ?>>
                                    <i class="bi bi-plus-circle me-1"></i>Ajouter le Repas
                                </button>
                                <a href="repas.php" class="btn btn-outline-secondary">Annuler</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$extraJs = <<<JS
<script>
smAttachRealtime('formRepas', ['nom'], ['calories','proteines','glucides','lipides']);
smAttachSubmit('formRepas', [
    { name: 'nom',        type: 'nom',    label: 'Le nom du repas' },
    { name: 'id_recette', type: 'select', label: 'La recette' },
    { name: 'calories',   type: 'number', label: 'Les calories',  min: 0 },
    { name: 'proteines',  type: 'number', label: 'Les protéines', min: 0 },
    { name: 'glucides',   type: 'number', label: 'Les glucides',  min: 0 },
    { name: 'lipides',    type: 'number', label: 'Les lipides',   min: 0 },
]);

// ── Remplir automatiquement le nom du repas avec le nom de la recette ─────────
document.addEventListener('DOMContentLoaded', function () {
    const selectRecette = document.querySelector('[name="id_recette"]');
    const inputNom      = document.querySelector('[name="nom"]');

    if (!selectRecette || !inputNom) return;

    selectRecette.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const nomRecette     = selectedOption.text.trim();

        // Remplir seulement si le champ nom est vide ou correspond à l'ancienne recette
        if (nomRecette && this.value !== '') {
            inputNom.value = nomRecette;
            smMarkValid(inputNom);
        } else if (this.value === '') {
            inputNom.value = '';
            smClearFieldError(inputNom);
        }
    });
});
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
