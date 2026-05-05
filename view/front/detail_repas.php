<?php
defined('APP_ROOT') || require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Repas.php';

$repasModel = new Repas();
$id    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$repas = $id > 0 ? $repasModel->getRepasWithDetails($id) : null;

if (!$repas) {
    header('Location: repas.php');
    exit;
}

$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$project = str_replace('\\', '/', dirname(__DIR__, 2));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);

$pageTitle  = htmlspecialchars($repas['nom']) . ' - Smart Meal Planner';
$activePage = 'repas';
require_once __DIR__ . '/partials/header.php';
?>

<!-- Page Title -->
<div class="page-title dark-background" data-aos="fade">
    <div class="container position-relative">
        <h1><?= htmlspecialchars($repas['nom']) ?></h1>
        <nav class="breadcrumbs">
            <ol>
                <li><a href="home.php">Accueil</a></li>
                <li><a href="repas.php">Repas</a></li>
                <li class="current"><?= htmlspecialchars($repas['nom']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<section class="section">
    <div class="container" data-aos="fade-up">
        <div class="row g-4">

            <!-- ── Colonne gauche : photo + infos ── -->
            <div class="col-lg-5">

                <!-- Photo du repas -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;overflow:hidden;">
                    <?php if (!empty($repas['image_repas'])): ?>
                        <img src="<?= htmlspecialchars($baseUrl.'/'.$repas['image_repas']) ?>"
                             alt="<?= htmlspecialchars($repas['nom']) ?>"
                             style="width:100%;height:300px;object-fit:cover;">
                    <?php else: ?>
                        <div style="height:300px;background:linear-gradient(135deg,#f8d7da,#f5c6cb);display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-egg-fried" style="font-size:5rem;color:#ce1212;opacity:.3;"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body p-4">
                        <h2 style="font-family:'Amatic SC',sans-serif;font-size:2rem;color:#37373f;">
                            <?= htmlspecialchars($repas['nom']) ?>
                        </h2>

                        <!-- Badges -->
                        <div class="d-flex gap-2 flex-wrap mb-3">
                            <?php if (!empty($repas['type_repas'])): ?>
                                <span class="badge bg-secondary"><?= htmlspecialchars($repas['type_repas']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($repas['nom_recette'])): ?>
                                <span class="badge bg-primary"><?= htmlspecialchars($repas['nom_recette']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($repas['difficulte'])): ?>
                                <?php
                                $bc = match($repas['difficulte']) {
                                    'Facile'    => 'bg-success',
                                    'Moyen'     => 'bg-warning text-dark',
                                    'Difficile' => 'bg-danger',
                                    default     => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $bc ?>"><?= htmlspecialchars($repas['difficulte']) ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Calories -->
                        <?php if (!empty($repas['calories'])): ?>
                        <div class="d-flex align-items-center gap-2 mb-3 p-3 rounded-3" style="background:#fff8f8;">
                            <i class="bi bi-fire" style="font-size:1.5rem;color:#ce1212;"></i>
                            <div>
                                <div class="fw-bold fs-4" style="color:#ce1212;"><?= htmlspecialchars($repas['calories']) ?> kcal</div>
                                <div class="text-muted small">Valeur calorique</div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Macros -->
                        <?php if (!empty($repas['proteines']) || !empty($repas['glucides']) || !empty($repas['lipides'])): ?>
                        <div class="row g-2 mb-3">
                            <?php if (!empty($repas['proteines'])): ?>
                            <div class="col-4">
                                <div class="text-center p-2 rounded-3" style="background:#e8f0fe;">
                                    <div class="fw-bold text-primary"><?= $repas['proteines'] ?>g</div>
                                    <div class="text-muted" style="font-size:.75rem;">Protéines</div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($repas['glucides'])): ?>
                            <div class="col-4">
                                <div class="text-center p-2 rounded-3" style="background:#fff3cd;">
                                    <div class="fw-bold text-warning"><?= $repas['glucides'] ?>g</div>
                                    <div class="text-muted" style="font-size:.75rem;">Glucides</div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($repas['lipides'])): ?>
                            <div class="col-4">
                                <div class="text-center p-2 rounded-3" style="background:#fde8e8;">
                                    <div class="fw-bold text-danger"><?= $repas['lipides'] ?>g</div>
                                    <div class="text-muted" style="font-size:.75rem;">Lipides</div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Temps (depuis la recette) -->
                        <?php if (!empty($repas['temps_prep']) || !empty($repas['temps_cuisson'])): ?>
                        <div class="d-flex gap-3 text-muted small">
                            <?php if (!empty($repas['temps_prep'])): ?>
                            <span><i class="bi bi-clock me-1 text-primary"></i><?= $repas['temps_prep'] ?> min préparation</span>
                            <?php endif; ?>
                            <?php if (!empty($repas['temps_cuisson'])): ?>
                            <span><i class="bi bi-fire me-1 text-danger"></i><?= $repas['temps_cuisson'] ?> min cuisson</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($repas['description'])): ?>
                        <p class="text-muted mt-3 mb-0"><?= htmlspecialchars($repas['description']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Ingrédients -->
                <?php if (!empty($repas['ingredients'])): ?>
                <div class="card border-0 shadow-sm" style="border-radius:16px;">
                    <div class="card-body p-4">
                        <h5 style="font-family:'Amatic SC',sans-serif;font-size:1.5rem;color:#37373f;">
                            <i class="bi bi-basket me-2" style="color:#ce1212;"></i>Ingrédients
                            <span class="badge ms-1" style="background:#ce1212;font-size:.7rem;"><?= count($repas['ingredients']) ?></span>
                        </h5>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($repas['ingredients'] as $ing): ?>
                            <li class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <span class="d-flex align-items-center gap-2">
                                    <i class="bi bi-dot" style="color:#ce1212;font-size:1.4rem;"></i>
                                    <span class="fw-medium"><?= htmlspecialchars($ing['nom_ingredient']) ?></span>
                                </span>
                                <?php if (!empty($ing['quantite'])): ?>
                                <span class="badge bg-light text-dark border">
                                    <?= htmlspecialchars($ing['quantite']) ?> <?= htmlspecialchars($ing['unite'] ?? '') ?>
                                </span>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- ── Colonne droite : recette (étapes) ── -->
            <div class="col-lg-7">

                <?php if (!empty($repas['nom_recette'])): ?>
                <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                    <div class="card-body p-4">
                        <h4 style="font-family:'Amatic SC',sans-serif;font-size:1.8rem;color:#37373f;">
                            <i class="bi bi-journal-richtext me-2" style="color:#ce1212;"></i>
                            Recette : <?= htmlspecialchars($repas['nom_recette']) ?>
                        </h4>

                        <!-- Infos recette -->
                        <div class="d-flex gap-3 flex-wrap mb-4 text-muted small">
                            <?php if (!empty($repas['nb_personnes'])): ?>
                            <span><i class="bi bi-people me-1 text-success"></i><?= $repas['nb_personnes'] ?> personnes</span>
                            <?php endif; ?>
                            <?php if (!empty($repas['temps_prep'])): ?>
                            <span><i class="bi bi-clock me-1 text-primary"></i><?= $repas['temps_prep'] ?> min préparation</span>
                            <?php endif; ?>
                            <?php if (!empty($repas['temps_cuisson'])): ?>
                            <span><i class="bi bi-fire me-1 text-danger"></i><?= $repas['temps_cuisson'] ?> min cuisson</span>
                            <?php endif; ?>
                        </div>

                        <!-- Étapes -->
                        <?php if (!empty($repas['etapes'])): ?>
                        <h6 class="fw-bold mb-3" style="font-size:.85rem;text-transform:uppercase;letter-spacing:.08em;color:#6c757d;">
                            <i class="bi bi-list-ol me-1" style="color:#ce1212;"></i>Étapes de préparation
                        </h6>
                        <div class="steps-container">
                            <?php
                            $etapes = array_filter(explode("\n", trim($repas['etapes'])));
                            $stepNum = 0;
                            foreach ($etapes as $etape):
                                $etape = trim($etape);
                                if (empty($etape)) continue;
                                $stepNum++;
                                // Remove leading number if present (e.g. "1. " or "1) ")
                                $text = preg_replace('/^\d+[\.\)]\s*/', '', $etape);
                            ?>
                            <div class="d-flex gap-3 mb-3">
                                <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle fw-bold text-white"
                                     style="width:32px;height:32px;background:#ce1212;font-size:.85rem;min-width:32px;">
                                    <?= $stepNum ?>
                                </div>
                                <div class="pt-1"><?= htmlspecialchars($text) ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                            <p class="text-muted">Aucune étape de préparation renseignée.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Photo de la recette si différente -->
                <?php if (!empty($repas['image_recette'])): ?>
                <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
                    <img src="<?= htmlspecialchars($baseUrl.'/'.$repas['image_recette']) ?>"
                         alt="Photo recette"
                         style="width:100%;height:250px;object-fit:cover;">
                    <div class="card-body p-3">
                        <p class="text-muted small mb-0">
                            <i class="bi bi-camera me-1"></i>Photo de la recette
                        </p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ── Vidéo YouTube de la recette ── -->
                <?php if (!empty($repas['video_youtube'])): ?>
                <div class="card border-0 shadow-sm mt-4" style="border-radius:16px;overflow:hidden;">
                    <div class="card-body p-4 pb-2">
                        <h5 style="font-family:'Amatic SC',sans-serif;font-size:1.5rem;color:#37373f;">
                            <i class="bi bi-youtube me-2" style="color:#ff0000;"></i>Vidéo de la recette
                        </h5>
                        <p class="text-muted small mb-3">Regardez comment préparer cette recette en vidéo.</p>
                    </div>
                    <!-- Player YouTube responsive (ratio 16:9) -->
                    <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;">
                        <iframe
                            src="https://www.youtube.com/embed/<?= htmlspecialchars($repas['video_youtube']) ?>?rel=0&modestbranding=1&color=red"
                            title="Vidéo recette : <?= htmlspecialchars($repas['nom_recette'] ?? $repas['nom']) ?>"
                            style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            loading="lazy">
                        </iframe>
                    </div>
                    <div class="card-body p-3 pt-2">
                        <a href="https://www.youtube.com/watch?v=<?= htmlspecialchars($repas['video_youtube']) ?>"
                           target="_blank" rel="noopener noreferrer"
                           class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Voir sur YouTube
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ── QR Code de partage ── -->
                <?php
                $currentUrl = ($scheme ?? 'http') . '://' . $_SERVER['HTTP_HOST']
                    . str_replace(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '',
                        str_replace('\\', '/', dirname(__DIR__, 2)))
                    . '/view/front/detail_repas.php?id=' . $id;
                ?>
                <div class="card border-0 shadow-sm mt-4" style="border-radius:16px;">
                    <div class="card-body p-4 text-center">
                        <h5 style="font-family:'Amatic SC',sans-serif;font-size:1.5rem;color:#37373f;">
                            <i class="bi bi-qr-code me-2" style="color:#ce1212;"></i>Partager cette recette
                        </h5>
                        <p class="text-muted small mb-3">
                            Scannez ce QR code pour partager cette recette sur mobile.
                        </p>
                        <!-- QR code généré par QRCode.js -->
                        <div id="qrcode-front"
                             class="d-inline-block p-3 bg-white border rounded-3 shadow-sm mb-3"
                             data-url="<?= htmlspecialchars($currentUrl) ?>">
                        </div>
                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                            <button id="btnCopyFront"
                                    onclick="copyFrontUrl('<?= htmlspecialchars($currentUrl) ?>')"
                                    class="btn btn-sm btn-danger">
                                <i class="bi bi-share me-1"></i>Partager
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Retour -->
        <div class="text-center mt-4">
            <a href="repas.php" class="btn-get-started">
                <i class="bi bi-arrow-left me-2"></i>Retour aux repas
            </a>
        </div>
    </div>
</section>

<!-- QRCode.js — génération côté client -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function() {
    var el = document.getElementById('qrcode-front');
    if (!el) return;
    new QRCode(el, {
        text:         el.dataset.url,
        width:        120,
        height:       120,
        colorDark:    '#ce1212',
        colorLight:   '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });
})();

function copyFrontUrl(url) {
    navigator.clipboard.writeText(url).then(function() {
        var btn = document.getElementById('btnCopyFront');
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Copié !';
        setTimeout(function() {
            btn.innerHTML = '<i class="bi bi-share me-1"></i>Partager';
        }, 2000);
    });
}
</script>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
