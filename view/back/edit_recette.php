<?php
defined('APP_ROOT') || require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Recette.php';

$recetteModel = new Recette();
$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$recette = $id > 0 ? $recetteModel->getRecetteById($id) : null;
if (!$recette) { header('Location: recette.php'); exit; }

$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$project = str_replace('\\', '/', dirname(__DIR__, 2));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);

$pageTitle = 'Modifier la Recette - SmartMeal Admin';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/sidebar.php';
?>
<div class="admin-main">
    <div class="admin-topbar">
        <h5><i class="bi bi-pencil me-2" style="color:var(--accent)"></i>Modifier la Recette</h5>
        <a href="recette.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Retour</a>
    </div>
    <div class="admin-content">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="admin-card card">
                    <div class="card-body p-4">
                        <form action="<?= htmlspecialchars($baseUrl.'/controller/RecetteController.php?action=update') ?>" method="POST" id="formEditRecette" novalidate>
                            <input type="hidden" name="id" value="<?= $recette['id_recette'] ?>">
                            <div class="row g-3">

                                <!-- Infos générales -->
                                <div class="col-12">
                                    <h6 class="fw-bold text-muted mb-0" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.08em;">
                                        <i class="bi bi-info-circle me-1" style="color:var(--accent)"></i>Informations générales
                                    </h6>
                                    <hr class="mt-1 mb-2">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-medium">Nom de la recette <span class="text-danger">*</span></label>
                                    <input type="text" name="nom" class="form-control"
                                           value="<?= htmlspecialchars($recette['nom_recette']) ?>">
                                    <div class="invalid-feedback"></div>
                                    <small class="text-muted">Donnez un nom clair et descriptif.</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Niveau de difficulté</label>
                                    <select name="difficulte" class="form-select">
                                        <?php foreach (['Facile'=>'🟢 Facile — moins de 30 min','Moyen'=>'🟡 Moyen — 30 à 60 min','Difficile'=>'🔴 Difficile — plus d\'1 heure'] as $v=>$l): ?>
                                            <option value="<?= $v ?>" <?= ($recette['difficulte']??'')===$v?'selected':'' ?>><?= $l ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">
                                        <i class="bi bi-clock me-1 text-primary"></i>Temps de préparation
                                    </label>
                                    <div class="input-group">
                                        <input type="text" name="temps_prep" class="form-control"
                                               value="<?= htmlspecialchars($recette['temps_prep']??'') ?>">
                                        <span class="input-group-text">min</span>
                                    </div>
                                    <div class="invalid-feedback"></div>
                                    <small class="text-muted">Découpe, marinade, mélange...</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">
                                        <i class="bi bi-fire me-1 text-danger"></i>Temps de cuisson
                                    </label>
                                    <div class="input-group">
                                        <input type="text" name="temps_cuisson" class="form-control"
                                               value="<?= htmlspecialchars($recette['temps_cuisson']??'') ?>">
                                        <span class="input-group-text">min</span>
                                    </div>
                                    <div class="invalid-feedback"></div>
                                    <small class="text-muted">Four, poêle, vapeur...</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">
                                        <i class="bi bi-people me-1 text-success"></i>Nombre de personnes
                                    </label>
                                    <div class="input-group">
                                        <input type="text" name="nb_personnes" class="form-control"
                                               value="<?= htmlspecialchars($recette['nb_personnes']??2) ?>">
                                        <span class="input-group-text">pers.</span>
                                    </div>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Étapes -->
                                <div class="col-12 mt-2">
                                    <h6 class="fw-bold text-muted mb-0" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.08em;">
                                        <i class="bi bi-list-ol me-1" style="color:var(--accent)"></i>Comment préparer ce repas
                                    </h6>
                                    <hr class="mt-1 mb-2">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium">
                                        <i class="bi bi-123 me-1"></i>Étapes de préparation
                                    </label>
                                    <textarea name="etapes" class="form-control" rows="6"
                                              placeholder="1. Préchauffer le four à 200°C&#10;2. ..."><?= htmlspecialchars($recette['etapes']??'') ?></textarea>
                                    <small class="text-muted">Numérotez chaque étape pour faciliter la lecture.</small>
                                </div>

                                <!-- Photo automatique -->
                                <div class="col-12 mt-2">
                                    <?php if (!empty($recette['image_recette'])): ?>
                                        <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:#f8f9fc;">
                                            <img src="<?= htmlspecialchars($baseUrl.'/'.$recette['image_recette']) ?>"
                                                 style="width:80px;height:80px;object-fit:cover;border-radius:10px;">
                                            <div>
                                                <div class="fw-medium">Photo actuelle</div>
                                                <div class="text-muted small">
                                                    <i class="bi bi-info-circle me-1"></i>
                                                    La photo est synchronisée automatiquement depuis le repas associé.
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info mb-0" style="font-size:.85rem;">
                                            <i class="bi bi-info-circle me-2"></i>
                                            La photo sera automatiquement copiée depuis la photo du repas associé.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-yummy"><i class="bi bi-save me-1"></i>Enregistrer</button>
                                <a href="recette.php" class="btn btn-outline-secondary">Annuler</a>
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
smAttachRealtime('formEditRecette',
    ['nom'],
    ['temps_prep','temps_cuisson','nb_personnes']
);
smAttachSubmit('formEditRecette', [
    { name: 'nom',           type: 'nom',    label: 'Le nom de la recette' },
    { name: 'temps_prep',    type: 'number', label: 'Le temps de préparation', min: 0 },
    { name: 'temps_cuisson', type: 'number', label: 'Le temps de cuisson',     min: 0 },
    { name: 'nb_personnes',  type: 'number', label: 'Le nombre de personnes',  min: 1, required: true },
]);
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
    document.getElementById('image_recette').files=dt.files;
    previewImg(document.getElementById('image_recette'));
}
function clearImage(){
    document.getElementById('image_recette').value='';
    document.getElementById('prev1').classList.add('d-none');
    document.getElementById('dropContent').classList.remove('d-none');
}
</script>
JS;
require_once __DIR__ . '/partials/foot.php';
?>
