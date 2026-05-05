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
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-medium mb-0">
                                            <i class="bi bi-123 me-1"></i>Étapes de préparation
                                        </label>
                                        <!-- Bouton génération IA — même logique que add_recette.php -->
                                        <button type="button" id="btnGenerate"
                                                class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1"
                                                onclick="genererEtapes()"
                                                title="Régénère les étapes selon le nom, la difficulté et les temps saisis">
                                            <span id="genIcon">✨</span>
                                            <span id="genLabel">Régénérer automatiquement</span>
                                        </button>
                                    </div>
                                    <textarea name="etapes" id="etapes" class="form-control" rows="6"
                                              placeholder="1. Préchauffer le four à 200°C&#10;2. ..."><?= htmlspecialchars($recette['etapes']??'') ?></textarea>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <small class="text-muted">Numérotez chaque étape pour faciliter la lecture.</small>
                                        <small id="genStatus" class="text-muted fst-italic"></small>
                                    </div>
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

                                <!-- ── Vidéo YouTube ──────────────────────────────────────── -->
                                <div class="col-12 mt-2">
                                    <h6 class="fw-bold text-muted mb-0" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.08em;">
                                        <i class="bi bi-youtube me-1" style="color:#ff0000"></i>Vidéo de la recette
                                    </h6>
                                    <hr class="mt-1 mb-3">

                                    <!-- Champ caché stockant l'ID vidéo YouTube -->
                                    <input type="hidden" name="video_youtube" id="video_youtube_id"
                                           value="<?= htmlspecialchars($recette['video_youtube'] ?? '') ?>">

                                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                                        <button type="button" id="btnSearchVideo"
                                                class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1"
                                                onclick="rechercherVideo()"
                                                title="Recherche une nouvelle vidéo YouTube selon le nom de la recette">
                                            <span id="videoIcon">▶</span>
                                            <span id="videoLabel">
                                                <?= !empty($recette['video_youtube']) ? 'Changer la vidéo' : 'Trouver une vidéo YouTube' ?>
                                            </span>
                                        </button>
                                        <button type="button" id="btnClearVideo"
                                                class="btn btn-sm btn-outline-secondary <?= empty($recette['video_youtube']) ? 'd-none' : '' ?>"
                                                onclick="clearVideo()">
                                            <i class="bi bi-x-circle me-1"></i>Retirer la vidéo
                                        </button>
                                        <small id="videoStatus" class="text-muted fst-italic"></small>
                                    </div>

                                    <!-- Prévisualisation : vidéo actuelle ou nouvelle vidéo trouvée -->
                                    <div id="videoPreview" class="<?= empty($recette['video_youtube']) ? 'd-none' : '' ?>">
                                        <div class="card border-0 bg-light" style="border-radius:12px;overflow:hidden;">
                                            <div class="row g-0 align-items-center">
                                                <div class="col-md-5">
                                                    <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:12px 0 0 12px;">
                                                        <iframe id="videoFrame"
                                                                src="<?= !empty($recette['video_youtube']) ? 'https://www.youtube.com/embed/' . htmlspecialchars($recette['video_youtube']) . '?rel=0&modestbranding=1' : '' ?>"
                                                                style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"
                                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                                allowfullscreen loading="lazy">
                                                        </iframe>
                                                    </div>
                                                </div>
                                                <div class="col-md-7 p-3">
                                                    <div class="d-flex align-items-start gap-2 mb-1">
                                                        <i class="bi bi-youtube text-danger fs-5 flex-shrink-0"></i>
                                                        <div>
                                                            <div id="videoTitle" class="fw-semibold" style="font-size:.9rem;line-height:1.3;">
                                                                <?= !empty($recette['video_youtube']) ? 'Vidéo associée à cette recette' : '' ?>
                                                            </div>
                                                            <div id="videoChannel" class="text-muted small mt-1">
                                                                <?= !empty($recette['video_youtube']) ? '📺 ID : ' . htmlspecialchars($recette['video_youtube']) : '' ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2">
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                            <i class="bi bi-check-circle me-1"></i>Vidéo sélectionnée
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
$baseUrlJs = json_encode($baseUrl);
$extraJs = <<<JS
<script>
const BASE_URL = $baseUrlJs;

// ── Recherche vidéo YouTube (identique à add_recette.php) ─────────────────────
function rechercherVideo() {
    const nom = document.querySelector('[name="nom"]')?.value.trim() ?? '';

    if (!nom) {
        document.querySelector('[name="nom"]').focus();
        document.getElementById('videoStatus').textContent = '⚠ Saisissez d\'abord le nom de la recette.';
        document.getElementById('videoStatus').className = 'text-warning fst-italic small';
        return;
    }

    const btn    = document.getElementById('btnSearchVideo');
    const icon   = document.getElementById('videoIcon');
    const label  = document.getElementById('videoLabel');
    const status = document.getElementById('videoStatus');

    btn.disabled = true;
    icon.textContent  = '⏳';
    label.textContent = 'Recherche en cours…';
    status.textContent = '';

    fetch(BASE_URL + '/controller/SearchYoutubeController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nom })
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) {
            if (data.setup_required) {
                status.innerHTML = '⚙️ <strong>Clé API YouTube requise</strong> — Configurez <code>YOUTUBE_API_KEY</code> dans <code>SearchYoutubeController.php</code>';
                status.className = 'text-warning fst-italic small';
            } else {
                status.textContent = '❌ ' + data.error;
                status.className = 'text-danger fst-italic small';
            }
            return;
        }

        // Mettre à jour le champ caché
        document.getElementById('video_youtube_id').value = data.video_id;

        // Mettre à jour le player
        document.getElementById('videoFrame').src   = data.embed_url + '?rel=0&modestbranding=1';
        document.getElementById('videoTitle').textContent   = data.title;
        document.getElementById('videoChannel').textContent = '📺 ' + data.channel;

        // Afficher le bloc
        document.getElementById('videoPreview').classList.remove('d-none');
        document.getElementById('btnClearVideo').classList.remove('d-none');

        status.textContent = '✅ Nouvelle vidéo trouvée et associée.';
        status.className = 'text-success fst-italic small';
    })
    .catch(() => {
        status.textContent = '❌ Erreur de connexion au serveur.';
        status.className = 'text-danger fst-italic small';
    })
    .finally(() => {
        btn.disabled = false;
        icon.textContent  = '▶';
        label.textContent = 'Changer la vidéo';
    });
}

// Effacer la vidéo
function clearVideo() {
    document.getElementById('video_youtube_id').value = '';
    document.getElementById('videoFrame').src = '';
    document.getElementById('videoPreview').classList.add('d-none');
    document.getElementById('btnClearVideo').classList.add('d-none');
    document.getElementById('videoStatus').textContent = '';
    document.getElementById('videoLabel').textContent = 'Trouver une vidéo YouTube';
}

// ── Génération automatique des étapes (identique à add_recette.php) ───────────
function genererEtapes() {
    const nom        = document.querySelector('[name="nom"]')?.value.trim() ?? '';
    const difficulte = document.querySelector('[name="difficulte"]')?.value ?? 'Facile';
    const tempsPrep  = document.querySelector('[name="temps_prep"]')?.value.trim() ?? '';
    const tempsCuis  = document.querySelector('[name="temps_cuisson"]')?.value.trim() ?? '';
    const nbPers     = document.querySelector('[name="nb_personnes"]')?.value.trim() ?? '2';

    if (!nom) {
        document.querySelector('[name="nom"]').focus();
        document.getElementById('genStatus').textContent = '⚠ Saisissez d\'abord le nom de la recette.';
        document.getElementById('genStatus').className = 'text-warning fst-italic small';
        return;
    }

    const btn    = document.getElementById('btnGenerate');
    const icon   = document.getElementById('genIcon');
    const label  = document.getElementById('genLabel');
    const status = document.getElementById('genStatus');

    btn.disabled = true;
    icon.textContent  = '⏳';
    label.textContent = 'Génération…';
    status.textContent = '';

    fetch(BASE_URL + '/controller/GenerateRecetteController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nom, difficulte, temps_prep: tempsPrep, temps_cuisson: tempsCuis, nb_personnes: nbPers })
    })
    .then(r => r.json())
    .then(data => {
        if (data.etapes) {
            document.getElementById('etapes').value = data.etapes;
            status.textContent = '✅ Étapes régénérées avec succès !';
            status.className = 'text-success fst-italic small';
        } else {
            status.textContent = '❌ ' + (data.error ?? 'Erreur inconnue');
            status.className = 'text-danger fst-italic small';
        }
    })
    .catch(() => {
        status.textContent = '❌ Erreur de connexion au serveur.';
        status.className = 'text-danger fst-italic small';
    })
    .finally(() => {
        btn.disabled = false;
        icon.textContent  = '✨';
        label.textContent = 'Régénérer automatiquement';
    });
}

// ── Validation formulaire ─────────────────────────────────────────────────────
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
