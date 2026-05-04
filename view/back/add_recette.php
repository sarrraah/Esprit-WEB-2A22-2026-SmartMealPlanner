<?php
session_start();
defined('APP_ROOT') || require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Recette.php';
require_once __DIR__ . '/../../model/Ingredient.php';

$formErrors = $_SESSION['recette_errors'] ?? [];
$formOld    = $_SESSION['recette_old'] ?? [];
unset($_SESSION['recette_errors'], $_SESSION['recette_old']);

$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$project = str_replace('\\', '/', dirname(__DIR__, 2));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);

$recetteModel    = new Recette();
$ingredientModel = new Ingredient();

// Step 2: recette already created, manage its ingredients
$idRecette  = isset($_GET['id_recette']) ? (int)$_GET['id_recette'] : 0;
$recette    = $idRecette > 0 ? $recetteModel->getRecetteById($idRecette) : null;

// Ingredients for this recette (stored in repas_ingredient linked to recette)
// We reuse the ingredient table but link to id_recette via a temp repas approach
// Simpler: store ingredients directly on recette using a recette_ingredient table
// For now we show the form and list from session-stored temp ingredients
$ingredients = $idRecette > 0 ? $ingredientModel->getByRecette($idRecette) : [];

$editIngredient = null;
if ($idRecette > 0 && isset($_GET['edit_ing'])) {
    $editIngredient = $ingredientModel->getById((int)$_GET['edit_ing']);
}

$pageTitle = 'Ajouter une Recette - SmartMeal Admin';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/sidebar.php';
?>
<div class="admin-main">
    <div class="admin-topbar">
        <h5><i class="bi bi-journal-plus me-2" style="color:var(--accent)"></i>
            <?= $recette ? 'Recette : '.htmlspecialchars($recette['nom_recette']) : 'Ajouter une Recette' ?>
        </h5>
        <a href="recette.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Retour</a>
    </div>
    <div class="admin-content">

        <?php if (!$recette): ?>
        <!-- ══ STEP 1: Create recette ══ -->
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="admin-card card">
                    <div class="card-body p-4">
                        <form action="<?= htmlspecialchars($baseUrl.'/controller/RecetteController.php?action=add') ?>"
                              method="POST" enctype="multipart/form-data" id="formRecette" novalidate>
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

                                <div class="col-12">
                                    <h6 class="fw-bold text-muted mb-0" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.08em;">
                                        <i class="bi bi-info-circle me-1" style="color:var(--accent)"></i>Informations générales
                                    </h6>
                                    <hr class="mt-1 mb-2">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-medium">Nom de la recette <span class="text-danger">*</span></label>
                                    <input type="text" name="nom" class="form-control"
                                           placeholder="Ex: Poulet rôti aux herbes de Provence">
                                    <div class="invalid-feedback"></div>
                                    <small class="text-muted">Donnez un nom clair et descriptif.</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Niveau de difficulté</label>
                                    <select name="difficulte" class="form-select">
                                        <option value="Facile">🟢 Facile — moins de 30 min</option>
                                        <option value="Moyen">🟡 Moyen — 30 à 60 min</option>
                                        <option value="Difficile">🔴 Difficile — plus d'1 heure</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium"><i class="bi bi-clock me-1 text-primary"></i>Temps de préparation</label>
                                    <div class="input-group">
                                        <input type="text" name="temps_prep" class="form-control" placeholder="Ex: 15">
                                        <span class="input-group-text">min</span>
                                    </div>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium"><i class="bi bi-fire me-1 text-danger"></i>Temps de cuisson</label>
                                    <div class="input-group">
                                        <input type="text" name="temps_cuisson" class="form-control" placeholder="Ex: 30">
                                        <span class="input-group-text">min</span>
                                    </div>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium"><i class="bi bi-people me-1 text-success"></i>Nombre de personnes</label>
                                    <div class="input-group">
                                        <input type="text" name="nb_personnes" class="form-control" value="2">
                                        <span class="input-group-text">pers.</span>
                                    </div>
                                    <div class="invalid-feedback"></div>
                                </div>

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
                                        <!-- Bouton de génération automatique -->
                                        <button type="button" id="btnGenerate"
                                                class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1"
                                                onclick="genererEtapes()"
                                                title="Génère des étapes selon le nom, la difficulté et les temps saisis">
                                            <span id="genIcon">✨</span>
                                            <span id="genLabel">Générer automatiquement</span>
                                        </button>
                                    </div>
                                    <textarea name="etapes" id="etapes" class="form-control" rows="6"
                                              placeholder="1. Préchauffer le four à 200°C&#10;2. Laver et sécher le poulet&#10;3. Mélanger l'huile, l'ail et les herbes&#10;4. Enfourner 45 min&#10;5. Laisser reposer 5 min avant de servir"></textarea>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <small class="text-muted">Numérotez chaque étape pour faciliter la lecture.</small>
                                        <small id="genStatus" class="text-muted fst-italic"></small>
                                    </div>
                                </div>

                                <div class="col-12 mt-2">
                                    <div class="alert alert-info mb-0" style="font-size:.85rem;">
                                        <i class="bi bi-info-circle me-2"></i>
                                        La photo de la recette sera automatiquement copiée depuis la photo du repas associé.
                                    </div>
                                </div>

                                <!-- ── Vidéo YouTube ──────────────────────────────────────── -->
                                <div class="col-12 mt-2">
                                    <h6 class="fw-bold text-muted mb-0" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.08em;">
                                        <i class="bi bi-youtube me-1" style="color:#ff0000"></i>Vidéo de la recette
                                    </h6>
                                    <hr class="mt-1 mb-3">

                                    <!-- Champ caché qui stocke l'ID vidéo YouTube -->
                                    <input type="hidden" name="video_youtube" id="video_youtube_id" value="">

                                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                                        <button type="button" id="btnSearchVideo"
                                                class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1"
                                                onclick="rechercherVideo()"
                                                title="Recherche automatiquement une vidéo YouTube selon le nom de la recette">
                                            <span id="videoIcon">▶</span>
                                            <span id="videoLabel">Trouver une vidéo YouTube</span>
                                        </button>
                                        <button type="button" id="btnClearVideo"
                                                class="btn btn-sm btn-outline-secondary d-none"
                                                onclick="clearVideo()">
                                            <i class="bi bi-x-circle me-1"></i>Retirer la vidéo
                                        </button>
                                        <small id="videoStatus" class="text-muted fst-italic"></small>
                                    </div>

                                    <!-- Prévisualisation de la vidéo trouvée -->
                                    <div id="videoPreview" class="d-none">
                                        <div class="card border-0 bg-light" style="border-radius:12px;overflow:hidden;">
                                            <div class="row g-0 align-items-center">
                                                <div class="col-md-5">
                                                    <!-- Player YouTube embarqué -->
                                                    <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:12px 0 0 12px;">
                                                        <iframe id="videoFrame"
                                                                src=""
                                                                style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"
                                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                                allowfullscreen
                                                                loading="lazy">
                                                        </iframe>
                                                    </div>
                                                </div>
                                                <div class="col-md-7 p-3">
                                                    <div class="d-flex align-items-start gap-2 mb-1">
                                                        <i class="bi bi-youtube text-danger fs-5 flex-shrink-0"></i>
                                                        <div>
                                                            <div id="videoTitle" class="fw-semibold" style="font-size:.9rem;line-height:1.3;"></div>
                                                            <div id="videoChannel" class="text-muted small mt-1"></div>
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
                                <button type="submit" class="btn btn-yummy">
                                    <i class="bi bi-arrow-right-circle me-1"></i>Créer la Recette et ajouter les ingrédients
                                </button>
                                <a href="recette.php" class="btn btn-outline-secondary">Annuler</a>
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

        <div class="row g-4">
            <!-- Left: ingredient form -->
            <div class="col-lg-4">
                <div class="admin-card card">
                    <div class="card-header">
                        <i class="bi bi-basket me-2" style="color:var(--accent)"></i>
                        <?= $editIngredient ? 'Modifier l\'ingrédient' : 'Ajouter un ingrédient à ce repas' ?>
                    </div>
                    <div class="card-body">
                        <form action="<?= htmlspecialchars($baseUrl.'/controller/IngredientRecetteController.php') ?>" method="POST">
                            <input type="hidden" name="action" value="<?= $editIngredient ? 'update' : 'add' ?>">
                            <input type="hidden" name="id_recette" value="<?= $idRecette ?>">
                            <?php if ($editIngredient): ?>
                                <input type="hidden" name="id_ingredient" value="<?= $editIngredient['id_ingredient'] ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label fw-medium">
                                    <i class="bi bi-dot text-danger"></i>Nom de l'ingrédient <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nom_ingredient" class="form-control"
                                       placeholder="Ex: Poulet, Tomate, Farine, Huile d'olive..."
                                       value="<?= htmlspecialchars($editIngredient['nom_ingredient'] ?? '') ?>">
                                <small class="text-muted">Soyez précis : "Poulet" plutôt que "viande".</small>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-medium">
                                        <i class="bi bi-123 me-1 text-primary"></i>Quantité
                                    </label>
                                    <input type="text" name="quantite" class="form-control"
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
                                    <a href="add_recette.php?id_recette=<?= $idRecette ?>" class="btn btn-outline-secondary">Annuler</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Recette info -->
                <div class="admin-card card mt-4">
                    <div class="card-body p-3">
                        <h6 class="fw-semibold mb-2" style="font-family:'Amatic SC',sans-serif;font-size:1.2rem;">Infos de la recette</h6>
                        <div class="d-flex flex-column gap-1 small text-muted">
                            <span><i class="bi bi-journal-richtext me-2" style="color:var(--accent)"></i><?= htmlspecialchars($recette['nom_recette']) ?></span>
                            <?php if (!empty($recette['temps_prep'])): ?>
                                <span><i class="bi bi-clock me-2 text-primary"></i><?= $recette['temps_prep'] ?> min préparation</span>
                            <?php endif; ?>
                            <?php if (!empty($recette['temps_cuisson'])): ?>
                                <span><i class="bi bi-fire me-2 text-danger"></i><?= $recette['temps_cuisson'] ?> min cuisson</span>
                            <?php endif; ?>
                            <span><i class="bi bi-people me-2 text-success"></i><?= $recette['nb_personnes'] ?> personnes</span>
                            <span><i class="bi bi-list-ul me-2" style="color:var(--accent)"></i><?= count($ingredients) ?> ingrédient(s)</span>
                        </div>
                        <hr>
                        <a href="recette.php" class="btn btn-yummy btn-sm w-100">
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
                                                <a href="add_recette.php?id_recette=<?= $idRecette ?>&edit_ing=<?= $ing['id_ingredient'] ?>"
                                                   class="btn btn-sm btn-outline-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="<?= htmlspecialchars($baseUrl.'/controller/IngredientRecetteController.php?action=delete&id='.$ing['id_ingredient'].'&id_recette='.$idRecette) ?>"
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
$baseUrlJs = json_encode($baseUrl);
$extraJs = <<<JS
<script>
// URL de base du projet injectée depuis PHP
const BASE_URL = $baseUrlJs;

// ── Recherche de vidéo YouTube ────────────────────────────────────────────────
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
            // Cas spécial : clé API non configurée
            if (data.setup_required) {
                status.innerHTML = '⚙️ <strong>Clé API YouTube requise</strong> — Configurez <code>YOUTUBE_API_KEY</code> dans <code>SearchYoutubeController.php</code>';
                status.className = 'text-warning fst-italic small';
            } else {
                status.textContent = '❌ ' + data.error;
                status.className = 'text-danger fst-italic small';
            }
            return;
        }

        // Remplir le champ caché avec l'ID vidéo
        document.getElementById('video_youtube_id').value = data.video_id;

        // Afficher le player YouTube
        document.getElementById('videoFrame').src   = data.embed_url + '?rel=0&modestbranding=1';
        document.getElementById('videoTitle').textContent   = data.title;
        document.getElementById('videoChannel').textContent = '📺 ' + data.channel;

        // Afficher le bloc de prévisualisation
        document.getElementById('videoPreview').classList.remove('d-none');
        document.getElementById('btnClearVideo').classList.remove('d-none');

        status.textContent = '✅ Vidéo trouvée et associée à la recette.';
        status.className = 'text-success fst-italic small';
    })
    .catch(() => {
        status.textContent = '❌ Erreur de connexion au serveur.';
        status.className = 'text-danger fst-italic small';
    })
    .finally(() => {
        btn.disabled = false;
        icon.textContent  = '▶';
        label.textContent = 'Trouver une vidéo YouTube';
    });
}

// Effacer la vidéo sélectionnée
function clearVideo() {
    document.getElementById('video_youtube_id').value = '';
    document.getElementById('videoFrame').src = '';
    document.getElementById('videoPreview').classList.add('d-none');
    document.getElementById('btnClearVideo').classList.add('d-none');
    document.getElementById('videoStatus').textContent = '';
}

// ── Génération automatique des étapes ─────────────────────────────────────────
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

    // État de chargement
    const btn   = document.getElementById('btnGenerate');
    const icon  = document.getElementById('genIcon');
    const label = document.getElementById('genLabel');
    const status = document.getElementById('genStatus');

    btn.disabled = true;
    icon.textContent  = '⏳';
    label.textContent = 'Génération…';
    status.textContent = '';

    // Appel AJAX vers le générateur PHP
    fetch(BASE_URL + '/controller/GenerateRecetteController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nom, difficulte, temps_prep: tempsPrep, temps_cuisson: tempsCuis, nb_personnes: nbPers })
    })
    .then(r => r.json())
    .then(data => {
        if (data.etapes) {
            document.getElementById('etapes').value = data.etapes;
            status.textContent = '✅ Étapes générées avec succès !';
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
        label.textContent = 'Générer automatiquement';
    });
}

// ── Validation formulaire recette ─────────────────────────────────────────────
smAttachRealtime('formRecette',
    ['nom'],
    ['temps_prep','temps_cuisson','nb_personnes']
);
smAttachSubmit('formRecette', [
    { name: 'nom',           type: 'nom',    label: 'Le nom de la recette' },
    { name: 'temps_prep',    type: 'number', label: 'Le temps de préparation', min: 0 },
    { name: 'temps_cuisson', type: 'number', label: 'Le temps de cuisson',     min: 0 },
    { name: 'nb_personnes',  type: 'number', label: 'Le nombre de personnes',  min: 1, required: true },
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
