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

$pageTitle = 'Modifier le Repas - SmartMeal Admin';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/sidebar.php';
?>
<div class="admin-main">
    <div class="admin-topbar">
        <h5><i class="bi bi-pencil me-2" style="color:var(--accent)"></i>Modifier le Repas</h5>
        <a href="repas.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Retour</a>
    </div>
    <div class="admin-content">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="admin-card card">
                    <div class="card-body p-4">
                        <form action="<?= htmlspecialchars($baseUrl.'/controller/RepasController.php') ?>" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= $repas['id_repas'] ?>">
                            <input type="hidden" name="current_image" value="<?= htmlspecialchars($repas['image_repas'] ?? '') ?>">
                            <input type="hidden" name="from" value="back">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Nom du repas <span class="text-danger">*</span></label>
                                    <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($repas['nom']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Type de repas</label>
                                    <select name="type_repas" class="form-select">
                                        <?php foreach (['Petit-dejeuner'=>'Petit-déjeuner','Dejeuner'=>'Déjeuner','Diner'=>'Dîner','Collation'=>'Collation'] as $v=>$l): ?>
                                            <option value="<?= $v ?>" <?= ($repas['type_repas']??'')===$v?'selected':'' ?>><?= $l ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Recette <span class="text-danger">*</span></label>
                                    <select name="id_recette" class="form-select" required>
                                        <?php foreach ($recettes as $rec): ?>
                                            <option value="<?= $rec['id_recette'] ?>" <?= ($repas['id_recette']??0)==$rec['id_recette']?'selected':'' ?>><?= htmlspecialchars($rec['nom_recette']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Calories (kcal)</label>
                                    <input type="number" step="0.1" min="0" name="calories" class="form-control" value="<?= htmlspecialchars($repas['calories']??'') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Protéines (g)</label>
                                    <input type="number" step="0.1" min="0" name="proteines" class="form-control" value="<?= htmlspecialchars($repas['proteines']??'') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Glucides (g)</label>
                                    <input type="number" step="0.1" min="0" name="glucides" class="form-control" value="<?= htmlspecialchars($repas['glucides']??'') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Lipides (g)</label>
                                    <input type="number" step="0.1" min="0" name="lipides" class="form-control" value="<?= htmlspecialchars($repas['lipides']??'') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium">Notes / Description</label>
                                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($repas['description']??'') ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium">Image du repas</label>
                                    <?php if (!empty($repas['image_repas'])): ?>
                                        <div class="mb-2"><img src="<?= htmlspecialchars($baseUrl.'/'.$repas['image_repas']) ?>" style="max-height:140px;border-radius:10px;object-fit:cover;"><p class="text-muted small mt-1">Laissez vide pour conserver.</p></div>
                                    <?php endif; ?>
                                    <div class="drop-zone" id="dropZone" onclick="document.getElementById('image_repas').click()"
                                         ondragover="event.preventDefault();this.style.background='#fde8e8'"
                                         ondragleave="this.style.background='#fff8f8'"
                                         ondrop="handleDrop(event)">
                                        <div id="dropContent">
                                            <i class="bi bi-cloud-arrow-up" style="font-size:2rem;color:var(--accent);"></i>
                                            <p class="mb-1 fw-medium mt-2">Nouvelle image</p>
                                            <p class="text-muted small mb-0">Glissez ou cliquez</p>
                                        </div>
                                        <div id="prev1" class="d-none">
                                            <img id="previewImg1" src="" style="max-height:140px;max-width:100%;border-radius:10px;object-fit:cover;">
                                            <p class="text-muted small mt-2 mb-0" id="fileName1"></p>
                                            <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="event.stopPropagation();clearImage()"><i class="bi bi-x-circle me-1"></i>Supprimer</button>
                                        </div>
                                    </div>
                                    <input type="file" id="image_repas" name="image_repas" accept="image/*" class="d-none" onchange="previewImg(this)">
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-yummy"><i class="bi bi-save me-1"></i>Enregistrer</button>
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
