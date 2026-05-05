<?php
defined('APP_ROOT') || require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Recette.php';

$recetteModel = new Recette();
$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$recette = $id > 0 ? $recetteModel->getRecetteWithRepas($id) : null;

if (!$recette) {
    header('Location: recette.php');
    exit;
}

$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$project = str_replace('\\', '/', dirname(__DIR__, 2));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);

$pageTitle = htmlspecialchars($recette['nom_recette']) . ' - SmartMeal Admin';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/sidebar.php';
?>

<div class="admin-main">
    <div class="admin-topbar">
        <h5>
            <i class="bi bi-journal-richtext me-2" style="color:var(--accent)"></i>
            <?= htmlspecialchars($recette['nom_recette']) ?>
        </h5>
        <div class="d-flex gap-2">
            <a href="export_recette_pdf.php?id=<?= $recette['id_recette'] ?>"
               target="_blank"
               class="btn btn-sm btn-outline-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i>Exporter PDF
            </a>
            <a href="edit_recette.php?id=<?= $recette['id_recette'] ?>" class="btn btn-sm btn-outline-warning">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
            <a href="recette.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Retour
            </a>
        </div>
    </div>

    <div class="admin-content">
        <div class="row g-4">

            <!-- ── Colonne gauche : infos recette ── -->
            <div class="col-lg-4">

                <!-- Photo -->
                <div class="admin-card card mb-4">
                    <?php if (!empty($recette['image_recette'])): ?>
                        <img src="<?= htmlspecialchars($baseUrl.'/'.$recette['image_recette']) ?>"
                             style="height:220px;object-fit:cover;width:100%;" alt="">
                    <?php else: ?>
                        <div style="height:220px;background:linear-gradient(135deg,#e8f0fe,#c7d7fd);display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-journal-richtext" style="font-size:4rem;color:#0d6efd;opacity:.3;"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h4 style="font-family:'Amatic SC',sans-serif;font-size:1.8rem;color:#37373f;">
                            <?= htmlspecialchars($recette['nom_recette']) ?>
                        </h4>

                        <!-- Badges -->
                        <div class="d-flex gap-2 flex-wrap mb-3">
                            <?php
                            $bc = match($recette['difficulte'] ?? 'Facile') {
                                'Facile'    => 'badge-facile',
                                'Moyen'     => 'badge-moyen',
                                'Difficile' => 'badge-difficile',
                                default     => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $bc ?>"><?= htmlspecialchars($recette['difficulte'] ?? 'Facile') ?></span>
                            <span class="badge bg-secondary"><?= count($recette['repas']) ?> repas</span>
                        </div>

                        <!-- Infos -->
                        <div class="d-flex flex-column gap-2 small">
                            <?php if (!empty($recette['temps_prep'])): ?>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-clock text-primary fs-5"></i>
                                <div>
                                    <div class="text-muted" style="font-size:.75rem;">Préparation</div>
                                    <div class="fw-medium"><?= $recette['temps_prep'] ?> min</div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($recette['temps_cuisson'])): ?>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-fire text-danger fs-5"></i>
                                <div>
                                    <div class="text-muted" style="font-size:.75rem;">Cuisson</div>
                                    <div class="fw-medium"><?= $recette['temps_cuisson'] ?> min</div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-people text-success fs-5"></i>
                                <div>
                                    <div class="text-muted" style="font-size:.75rem;">Personnes</div>
                                    <div class="fw-medium"><?= $recette['nb_personnes'] ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Étapes de préparation -->
                <?php if (!empty($recette['etapes'])): ?>
                <div class="admin-card card">
                    <div class="card-header">
                        <i class="bi bi-list-ol me-2" style="color:var(--accent)"></i>Étapes de préparation
                    </div>
                    <div class="card-body">
                        <div style="white-space:pre-line;font-size:.9rem;line-height:1.7;">
                            <?= htmlspecialchars($recette['etapes']) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- QR Code + Carte image de la recette -->
                <div class="admin-card card mt-4">
                    <div class="card-header">
                        <i class="bi bi-image me-2" style="color:var(--accent)"></i>Carte recette (image téléchargeable)
                    </div>
                    <div class="card-body text-center">
                        <p class="text-muted small mb-3">
                            Génère une image avec la photo, les infos et le QR code — visible sur téléphone sans connexion.
                        </p>

                        <!-- Canvas caché où tout est dessiné -->
                        <canvas id="recetteCanvas" style="display:none;"></canvas>

                        <!-- Prévisualisation de la carte générée -->
                        <div id="cartePreview" class="d-none mb-3">
                            <img id="carteImg"
                                 style="max-width:100%;border-radius:12px;box-shadow:0 4px 16px rgba(0,0,0,.15);"
                                 alt="Carte recette">
                        </div>

                        <!-- Boutons -->
                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                            <button id="btnGenCarte" onclick="genererCarte()" class="btn btn-sm btn-yummy">
                                <i class="bi bi-magic me-1"></i>Générer la carte image
                            </button>
                            <a id="btnDlCarte" class="btn btn-sm btn-outline-primary d-none"
                               download="recette-<?= $recette['id_recette'] ?>-<?= preg_replace('/[^a-z0-9]/i', '-', $recette['nom_recette']) ?>.png">
                                <i class="bi bi-download me-1"></i>Télécharger PNG
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Données PHP injectées pour le canvas JS -->
                <script>
                var RECETTE_DATA = {
                    nom:        <?= json_encode($recette['nom_recette']) ?>,
                    difficulte: <?= json_encode($recette['difficulte'] ?? 'Facile') ?>,
                    tempsPrep:  <?= json_encode($recette['temps_prep'] ?? null) ?>,
                    tempsCuis:  <?= json_encode($recette['temps_cuisson'] ?? null) ?>,
                    nbPers:     <?= json_encode($recette['nb_personnes'] ?? 2) ?>,
                    etapes:     <?= json_encode($recette['etapes'] ?? '') ?>,
                    imageUrl:   <?= json_encode(!empty($recette['image_recette']) ? $baseUrl.'/'.$recette['image_recette'] : null) ?>,
                    repas: <?= json_encode(array_map(function($r) use ($baseUrl) {
                        return [
                            'nom'        => $r['nom'],
                            'calories'   => $r['calories'] ?? null,
                            'type_repas' => $r['type_repas'] ?? '',
                            'imageUrl'   => !empty($r['image_repas']) ? $baseUrl.'/'.$r['image_repas'] : null,
                            'ingredients'=> array_map(fn($i) => $i['nom_ingredient'] . (!empty($i['quantite']) ? ' '.$i['quantite'].($i['unite']??'') : ''), $r['ingredients'] ?? []),
                        ];
                    }, $recette['repas'] ?? [])) ?>
                };
                </script>
            </div>

            <!-- ── Colonne droite : repas associés ── -->
            <div class="col-lg-8">
                <div class="admin-card card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>
                            <i class="bi bi-bowl-hot me-2" style="color:var(--accent)"></i>
                            Repas utilisant cette recette
                            <span class="badge ms-1" style="background:var(--accent)"><?= count($recette['repas']) ?></span>
                        </span>
                        <a href="add_repas.php" class="btn btn-yummy btn-sm">
                            <i class="bi bi-plus-lg me-1"></i>Nouveau Repas
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recette['repas'])): ?>
                            <div class="p-5 text-center text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3" style="opacity:.3;"></i>
                                <p>Aucun repas n'utilise encore cette recette.</p>
                                <a href="add_repas.php" class="btn btn-yummy btn-sm">
                                    <i class="bi bi-plus-lg me-1"></i>Créer un repas avec cette recette
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="accordion" id="repasAccordion">
                                <?php foreach ($recette['repas'] as $i => $r): ?>
                                <div class="accordion-item border-0 border-bottom">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#repas<?= $r['id_repas'] ?>">
                                            <div class="d-flex align-items-center gap-3 w-100 me-3">
                                                <!-- Miniature -->
                                                <?php if (!empty($r['image_repas'])): ?>
                                                    <img src="<?= htmlspecialchars($baseUrl.'/'.$r['image_repas']) ?>"
                                                         style="width:44px;height:44px;object-fit:cover;border-radius:8px;flex-shrink:0;">
                                                <?php else: ?>
                                                    <div style="width:44px;height:44px;background:linear-gradient(135deg,#f8d7da,#f5c6cb);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                        <i class="bi bi-egg-fried" style="color:#ce1212;opacity:.5;font-size:.9rem;"></i>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="flex-grow-1">
                                                    <div class="fw-bold" style="font-family:'Amatic SC',sans-serif;font-size:1.2rem;">
                                                        <?= htmlspecialchars($r['nom']) ?>
                                                    </div>
                                                    <div class="d-flex gap-2 small text-muted">
                                                        <span><i class="bi bi-tag me-1"></i><?= htmlspecialchars($r['type_repas'] ?? '-') ?></span>
                                                        <?php if (!empty($r['calories'])): ?>
                                                            <span><i class="bi bi-fire me-1"></i><?= $r['calories'] ?> kcal</span>
                                                        <?php endif; ?>
                                                        <span><i class="bi bi-basket me-1"></i><?= count($r['ingredients']) ?> ingrédient(s)</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="repas<?= $r['id_repas'] ?>"
                                         class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                                         data-bs-parent="#repasAccordion">
                                        <div class="accordion-body pt-2">
                                            <div class="row g-3">

                                                <!-- Macros -->
                                                <?php if (!empty($r['proteines']) || !empty($r['glucides']) || !empty($r['lipides'])): ?>
                                                <div class="col-12">
                                                    <div class="d-flex gap-3 flex-wrap">
                                                        <?php if (!empty($r['proteines'])): ?>
                                                        <div class="text-center px-3 py-2 rounded-3" style="background:#e8f0fe;">
                                                            <div class="fw-bold text-primary"><?= $r['proteines'] ?>g</div>
                                                            <div class="text-muted" style="font-size:.75rem;">Protéines</div>
                                                        </div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($r['glucides'])): ?>
                                                        <div class="text-center px-3 py-2 rounded-3" style="background:#fff3cd;">
                                                            <div class="fw-bold text-warning"><?= $r['glucides'] ?>g</div>
                                                            <div class="text-muted" style="font-size:.75rem;">Glucides</div>
                                                        </div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($r['lipides'])): ?>
                                                        <div class="text-center px-3 py-2 rounded-3" style="background:#fde8e8;">
                                                            <div class="fw-bold text-danger"><?= $r['lipides'] ?>g</div>
                                                            <div class="text-muted" style="font-size:.75rem;">Lipides</div>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php endif; ?>

                                                <!-- Ingrédients -->
                                                <div class="col-md-6">
                                                    <h6 class="fw-semibold mb-2" style="font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:#6c757d;">
                                                        <i class="bi bi-basket me-1" style="color:var(--accent)"></i>Ingrédients
                                                    </h6>
                                                    <?php if (empty($r['ingredients'])): ?>
                                                        <p class="text-muted small">
                                                            Aucun ingrédient.
                                                            <a href="ingredients.php?id_repas=<?= $r['id_repas'] ?>">Ajouter</a>
                                                        </p>
                                                    <?php else: ?>
                                                        <ul class="list-unstyled mb-0">
                                                            <?php foreach ($r['ingredients'] as $ing): ?>
                                                            <li class="d-flex align-items-center gap-2 mb-1 small">
                                                                <i class="bi bi-dot" style="color:var(--accent);font-size:1.2rem;"></i>
                                                                <span class="fw-medium"><?= htmlspecialchars($ing['nom_ingredient']) ?></span>
                                                                <?php if (!empty($ing['quantite'])): ?>
                                                                    <span class="text-muted ms-auto">
                                                                        <?= htmlspecialchars($ing['quantite']) ?>
                                                                        <?= htmlspecialchars($ing['unite'] ?? '') ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                            </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Description -->
                                                <?php if (!empty($r['description'])): ?>
                                                <div class="col-md-6">
                                                    <h6 class="fw-semibold mb-2" style="font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:#6c757d;">
                                                        <i class="bi bi-card-text me-1" style="color:var(--accent)"></i>Notes
                                                    </h6>
                                                    <p class="text-muted small mb-0"><?= htmlspecialchars($r['description']) ?></p>
                                                </div>
                                                <?php endif; ?>

                                                <!-- Actions -->
                                                <div class="col-12 d-flex gap-2 pt-1">
                                                    <a href="ingredients.php?id_repas=<?= $r['id_repas'] ?>"
                                                       class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-list-ul me-1"></i>Ingrédients
                                                    </a>
                                                    <a href="edit_repas.php?id=<?= $r['id_repas'] ?>"
                                                       class="btn btn-sm btn-outline-warning">
                                                        <i class="bi bi-pencil me-1"></i>Modifier
                                                    </a>
                                                    <a href="../../controller/RepasController.php?action=delete&id=<?= $r['id_repas'] ?>"
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Supprimer ce repas ?')">
                                                        <i class="bi bi-trash me-1"></i>Supprimer
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- QRCode.js — génération côté client -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
// ═══════════════════════════════════════════════════════════════════
// GÉNÉRATEUR DE CARTE RECETTE — Canvas HTML5
// Produit une image PNG autonome : photo + infos + QR code
// ═══════════════════════════════════════════════════════════════════

// Dimensions de la carte (format portrait téléphone)
var W = 540, H = 960;
var ACCENT = '#ce1212';

// ── Utilitaires canvas ────────────────────────────────────────────
function roundRect(ctx, x, y, w, h, r) {
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.lineTo(x + w - r, y);
    ctx.quadraticCurveTo(x + w, y, x + w, y + r);
    ctx.lineTo(x + w, y + h - r);
    ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
    ctx.lineTo(x + r, y + h);
    ctx.quadraticCurveTo(x, y + h, x, y + h - r);
    ctx.lineTo(x, y + r);
    ctx.quadraticCurveTo(x, y, x + r, y);
    ctx.closePath();
}

// Découpe le texte en lignes selon une largeur max
function wrapText(ctx, text, maxWidth) {
    var words = text.split(' '), lines = [], line = '';
    words.forEach(function(w) {
        var test = line ? line + ' ' + w : w;
        if (ctx.measureText(test).width > maxWidth && line) {
            lines.push(line);
            line = w;
        } else {
            line = test;
        }
    });
    if (line) lines.push(line);
    return lines;
}

// ── Construit le texte encodé dans le QR code ────────────────────
// Contient uniquement les infos essentielles — max ~300 chars pour QR niveau M
function buildQRText() {
    var lines = [];

    lines.push(RECETTE_DATA.nom.toUpperCase());

    var meta = [];
    if (RECETTE_DATA.difficulte) meta.push(RECETTE_DATA.difficulte);
    if (RECETTE_DATA.tempsPrep)  meta.push('Prep: ' + RECETTE_DATA.tempsPrep + 'min');
    if (RECETTE_DATA.tempsCuis)  meta.push('Cuisson: ' + RECETTE_DATA.tempsCuis + 'min');
    meta.push(RECETTE_DATA.nbPers + ' pers.');
    lines.push(meta.join(' | '));

    // Calories totales
    var totalCal = RECETTE_DATA.repas.reduce(function(s, r) {
        return s + (parseFloat(r.calories) || 0);
    }, 0);
    if (totalCal > 0) lines.push('Calories: ' + Math.round(totalCal) + ' kcal');

    // Repas (noms seulement)
    if (RECETTE_DATA.repas.length > 0) {
        lines.push('Repas: ' + RECETTE_DATA.repas.slice(0, 3).map(function(r) {
            return r.nom;
        }).join(', '));
    }

    // 2 premières étapes seulement
    if (RECETTE_DATA.etapes) {
        var etapes = RECETTE_DATA.etapes.split('\n')
            .map(function(e) { return e.trim().replace(/^\d+[\.\)]\s*/, ''); })
            .filter(Boolean)
            .slice(0, 2);
        if (etapes.length > 0) lines.push('Etapes: ' + etapes.join(' / '));
    }

    lines.push('SmartMeal Planner');

    // Tronquer à 250 chars max pour rester dans la capacité QR niveau M
    var text = lines.join('\n');
    if (text.length > 250) text = text.substring(0, 247) + '...';
    return text;
}

// ── Génération principale ─────────────────────────────────────────
function genererCarte() {
    var btn = document.getElementById('btnGenCarte');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Génération…';

    var canvas = document.getElementById('recetteCanvas');
    canvas.width  = W;
    canvas.height = H;
    var ctx = canvas.getContext('2d');

    // ── 1. Fond blanc ─────────────────────────────────────────────
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, W, H);

    // ── 2. Charger la photo du repas (ou recette) ─────────────────
    // On prend la première image disponible : repas[0] ou recette
    var imgSrc = null;
    if (RECETTE_DATA.repas.length > 0 && RECETTE_DATA.repas[0].imageUrl) {
        imgSrc = RECETTE_DATA.repas[0].imageUrl;
    } else if (RECETTE_DATA.imageUrl) {
        imgSrc = RECETTE_DATA.imageUrl;
    }

    function dessinerCarte(photoImg) {
        // ── Photo hero (haut de carte) ────────────────────────────
        var photoH = 280;
        if (photoImg) {
            // Recadrage centré (cover)
            var ratio  = photoImg.naturalWidth / photoImg.naturalHeight;
            var dw = W, dh = W / ratio;
            var dy = 0;
            if (dh < photoH) { dh = photoH; dw = dh * ratio; }
            var dx = (W - dw) / 2;
            ctx.save();
            ctx.beginPath();
            ctx.rect(0, 0, W, photoH);
            ctx.clip();
            ctx.drawImage(photoImg, dx, dy, dw, dh);
            ctx.restore();
        } else {
            // Placeholder dégradé
            var grad = ctx.createLinearGradient(0, 0, W, photoH);
            grad.addColorStop(0, '#f8d7da');
            grad.addColorStop(1, '#f5c6cb');
            ctx.fillStyle = grad;
            ctx.fillRect(0, 0, W, photoH);
            ctx.fillStyle = 'rgba(206,18,18,.2)';
            ctx.font = 'bold 80px serif';
            ctx.textAlign = 'center';
            ctx.fillText('🍽', W/2, photoH/2 + 28);
        }

        // Dégradé sombre sur le bas de la photo pour lisibilité du titre
        var overlay = ctx.createLinearGradient(0, photoH - 100, 0, photoH);
        overlay.addColorStop(0, 'rgba(0,0,0,0)');
        overlay.addColorStop(1, 'rgba(0,0,0,.65)');
        ctx.fillStyle = overlay;
        ctx.fillRect(0, photoH - 100, W, 100);

        // ── Titre sur la photo ────────────────────────────────────
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 32px "Arial", sans-serif';
        ctx.textAlign = 'left';
        var titleLines = wrapText(ctx, RECETTE_DATA.nom, W - 40);
        var titleY = photoH - 16;
        for (var ti = titleLines.length - 1; ti >= 0; ti--) {
            ctx.fillText(titleLines[ti], 20, titleY);
            titleY -= 38;
        }

        // ── Bandeau rouge SmartMeal ───────────────────────────────
        ctx.fillStyle = ACCENT;
        ctx.fillRect(0, photoH, W, 44);
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 15px Arial';
        ctx.textAlign = 'left';
        ctx.fillText('SmartMeal Planner', 20, photoH + 28);
        ctx.textAlign = 'right';
        ctx.font = '13px Arial';
        ctx.fillText('Fiche Recette', W - 20, photoH + 28);

        var y = photoH + 44 + 20; // curseur vertical

        // ── Infos rapides (grille 3 colonnes) ────────────────────
        var infos = [];
        if (RECETTE_DATA.tempsPrep)  infos.push({ icon: '⏱', val: RECETTE_DATA.tempsPrep + ' min', lbl: 'Préparation' });
        if (RECETTE_DATA.tempsCuis)  infos.push({ icon: '🔥', val: RECETTE_DATA.tempsCuis + ' min', lbl: 'Cuisson' });
        infos.push({ icon: '👥', val: RECETTE_DATA.nbPers + ' pers.', lbl: 'Personnes' });
        if (RECETTE_DATA.repas.length > 0) {
            var totalCal = RECETTE_DATA.repas.reduce(function(s, r) { return s + (parseFloat(r.calories) || 0); }, 0);
            if (totalCal > 0) infos.push({ icon: '🔥', val: Math.round(totalCal) + ' kcal', lbl: 'Calories' });
        }

        var colW = W / Math.min(infos.length, 3);
        infos.slice(0, 3).forEach(function(info, i) {
            var cx = i * colW + colW / 2;
            // Fond gris clair
            roundRect(ctx, i * colW + 8, y, colW - 16, 64, 10);
            ctx.fillStyle = '#f8f9fa';
            ctx.fill();
            // Icône
            ctx.font = '20px serif';
            ctx.textAlign = 'center';
            ctx.fillText(info.icon, cx, y + 24);
            // Valeur
            ctx.fillStyle = ACCENT;
            ctx.font = 'bold 14px Arial';
            ctx.fillText(info.val, cx, y + 42);
            // Label
            ctx.fillStyle = '#888';
            ctx.font = '11px Arial';
            ctx.fillText(info.lbl, cx, y + 56);
        });
        y += 80;

        // ── Badge difficulté ──────────────────────────────────────
        var diffColors = { 'Facile': ['#d1fae5','#065f46'], 'Moyen': ['#fef3c7','#92400e'], 'Difficile': ['#fee2e2','#991b1b'] };
        var dc = diffColors[RECETTE_DATA.difficulte] || ['#f0f0f0','#555'];
        roundRect(ctx, 20, y, 120, 26, 13);
        ctx.fillStyle = dc[0]; ctx.fill();
        ctx.fillStyle = dc[1];
        ctx.font = 'bold 12px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(RECETTE_DATA.difficulte, 80, y + 17);
        y += 40;

        // ── Étapes (max 5) ────────────────────────────────────────
        if (RECETTE_DATA.etapes) {
            ctx.fillStyle = '#333';
            ctx.font = 'bold 13px Arial';
            ctx.textAlign = 'left';
            ctx.fillText('ÉTAPES DE PRÉPARATION', 20, y);
            y += 18;
            // Ligne décorative
            ctx.strokeStyle = ACCENT;
            ctx.lineWidth = 2;
            ctx.beginPath(); ctx.moveTo(20, y); ctx.lineTo(W - 20, y); ctx.stroke();
            y += 12;

            var etapesArr = RECETTE_DATA.etapes.split('\n')
                .map(function(e) { return e.trim().replace(/^\d+[\.\)]\s*/, ''); })
                .filter(Boolean)
                .slice(0, 5);

            etapesArr.forEach(function(etape, i) {
                // Cercle numéroté
                ctx.beginPath();
                ctx.arc(32, y + 8, 10, 0, Math.PI * 2);
                ctx.fillStyle = ACCENT;
                ctx.fill();
                ctx.fillStyle = '#fff';
                ctx.font = 'bold 10px Arial';
                ctx.textAlign = 'center';
                ctx.fillText(i + 1, 32, y + 12);

                // Texte de l'étape
                ctx.fillStyle = '#333';
                ctx.font = '12px Arial';
                ctx.textAlign = 'left';
                var lines = wrapText(ctx, etape, W - 70);
                lines.slice(0, 2).forEach(function(line, li) {
                    ctx.fillText(line, 50, y + 12 + li * 15);
                });
                y += Math.max(28, lines.slice(0, 2).length * 15 + 10);
            });
            y += 8;
        }

        // ── Repas associés (noms + calories) ─────────────────────
        if (RECETTE_DATA.repas.length > 0) {
            ctx.fillStyle = '#333';
            ctx.font = 'bold 13px Arial';
            ctx.textAlign = 'left';
            ctx.fillText('REPAS ASSOCIÉS', 20, y);
            y += 18;
            ctx.strokeStyle = ACCENT;
            ctx.lineWidth = 2;
            ctx.beginPath(); ctx.moveTo(20, y); ctx.lineTo(W - 20, y); ctx.stroke();
            y += 12;

            RECETTE_DATA.repas.slice(0, 4).forEach(function(r) {
                ctx.fillStyle = '#555';
                ctx.font = '12px Arial';
                ctx.textAlign = 'left';
                var label = '• ' + r.nom;
                if (r.calories) label += '  —  ' + r.calories + ' kcal';
                ctx.fillText(label, 20, y);
                y += 20;
            });
            y += 8;
        }

        // ── Zone QR code (bas de carte) ───────────────────────────
        var qrSize = 120;
        var qrX    = W - qrSize - 20;
        var qrY    = H - qrSize - 50;

        // Fond blanc arrondi pour le QR
        roundRect(ctx, qrX - 8, qrY - 8, qrSize + 16, qrSize + 16, 10);
        ctx.fillStyle = '#ffffff';
        ctx.fill();
        ctx.strokeStyle = '#e0e0e0';
        ctx.lineWidth = 1;
        ctx.stroke();

        // Texte à gauche du QR
        ctx.fillStyle = '#555';
        ctx.font = 'bold 12px Arial';
        ctx.textAlign = 'left';
        ctx.fillText('Scanner pour', 20, qrY + 20);
        ctx.fillText('voir la recette', 20, qrY + 36);
        ctx.fillText('sur mobile', 20, qrY + 52);
        ctx.fillStyle = ACCENT;
        ctx.font = '10px Arial';
        ctx.fillText('SmartMeal Planner', 20, qrY + 70);

        // ── Générer le QR code dans un canvas temporaire ──────────
        var qrCanvas = document.createElement('canvas');
        qrCanvas.width  = qrSize;
        qrCanvas.height = qrSize;
        document.body.appendChild(qrCanvas); // doit être dans le DOM

        // Générer le QR dans le canvas temporaire
        var qrTmp = document.createElement('div');
        qrTmp.style.display = 'none';
        document.body.appendChild(qrTmp);

        new QRCode(qrTmp, {
            text:         buildQRText(),
            width:        qrSize,
            height:       qrSize,
            colorDark:    '#1a1a1a',
            colorLight:   '#ffffff',
            correctLevel: QRCode.CorrectLevel.M  // Niveau M : supporte plus de données que H
        });

        // Attendre que QRCode.js ait fini de dessiner
        setTimeout(function() {
            var qrImgEl = qrTmp.querySelector('canvas') || qrTmp.querySelector('img');
            if (qrImgEl) {
                if (qrImgEl.tagName === 'CANVAS') {
                    ctx.drawImage(qrImgEl, qrX, qrY, qrSize, qrSize);
                } else {
                    var qi = new Image();
                    qi.onload = function() { ctx.drawImage(qi, qrX, qrY, qrSize, qrSize); finaliser(); };
                    qi.src = qrImgEl.src;
                    document.body.removeChild(qrTmp);
                    return;
                }
            }
            document.body.removeChild(qrTmp);
            finaliser();
        }, 300);

        // ── Pied de carte ─────────────────────────────────────────
        function finaliser() {
            ctx.fillStyle = ACCENT;
            ctx.fillRect(0, H - 36, W, 36);
            ctx.fillStyle = '#ffffff';
            ctx.font = '11px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('SmartMeal Planner — Fiche Recette — ' + new Date().toLocaleDateString('fr-FR'), W/2, H - 14);

            // ── Afficher la prévisualisation et activer le téléchargement ──
            var dataUrl = canvas.toDataURL('image/png');
            var img     = document.getElementById('carteImg');
            var dlBtn   = document.getElementById('btnDlCarte');

            img.src      = dataUrl;
            dlBtn.href   = dataUrl;

            document.getElementById('cartePreview').classList.remove('d-none');
            dlBtn.classList.remove('d-none');

            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Régénérer';
        }
    }

    // Charger la photo avant de dessiner
    if (imgSrc) {
        var photo = new Image();
        photo.crossOrigin = 'anonymous';
        photo.onload  = function() { dessinerCarte(photo); };
        photo.onerror = function() { dessinerCarte(null); };
        // Ajouter un timestamp pour contourner le cache CORS
        photo.src = imgSrc + (imgSrc.includes('?') ? '&' : '?') + 't=' + Date.now();
    } else {
        dessinerCarte(null);
    }
}
</script>
<?php require_once __DIR__ . '/partials/foot.php'; ?>
