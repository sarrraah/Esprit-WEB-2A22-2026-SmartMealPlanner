<?php
/**
 * recette_mobile.php — Page mobile optimisée pour le scan QR code
 *
 * Affiche la fiche complète d'une recette dans une interface
 * pensée pour les petits écrans (téléphone).
 *
 * Paramètre GET requis :
 *   - id : identifiant de la recette (int)
 */
defined('APP_ROOT') || require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Recette.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: repas.php');
    exit;
}

$recetteModel = new Recette();
$recette      = $recetteModel->getRecetteWithRepas($id);

if (!$recette) {
    header('Location: repas.php');
    exit;
}

// URL de base pour les images
$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$project = str_replace('\\', '/', dirname(__DIR__, 2));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);

// Calculs nutritionnels globaux
$repas         = $recette['repas'] ?? [];
$totalCalories = array_sum(array_column($repas, 'calories'));
$totalProteines= array_sum(array_column($repas, 'proteines'));
$totalGlucides = array_sum(array_column($repas, 'glucides'));
$totalLipides  = array_sum(array_column($repas, 'lipides'));

// Badge difficulté
$diffBadge = match($recette['difficulte'] ?? 'Facile') {
    'Facile'    => ['color' => '#065f46', 'bg' => '#d1fae5', 'icon' => '🟢'],
    'Moyen'     => ['color' => '#92400e', 'bg' => '#fef3c7', 'icon' => '🟡'],
    'Difficile' => ['color' => '#991b1b', 'bg' => '#fee2e2', 'icon' => '🔴'],
    default     => ['color' => '#555',    'bg' => '#f0f0f0', 'icon' => '⚪'],
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <!-- Viewport mobile-first : empêche le zoom automatique -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#ce1212">
    <title><?= htmlspecialchars($recette['nom_recette']) ?> — SmartMeal</title>

    <!-- Bootstrap Icons uniquement (léger) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Amatic+SC:wght@700&display=swap" rel="stylesheet">

    <style>
        /* ── Reset mobile ─────────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { font-size: 16px; -webkit-text-size-adjust: 100%; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f5f5;
            color: #1a1a1a;
            min-height: 100vh;
            padding-bottom: 80px; /* espace pour la bottom bar */
        }

        /* ── Header sticky ────────────────────────────────────────────── */
        .mob-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #ce1212;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,.2);
        }

        .mob-header .back-btn {
            background: rgba(255,255,255,.2);
            border: none;
            color: #fff;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            cursor: pointer;
            text-decoration: none;
            flex-shrink: 0;
        }

        .mob-header .back-btn:hover { background: rgba(255,255,255,.3); }

        .mob-header .title {
            font-family: 'Amatic SC', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mob-header .brand {
            font-size: .75rem;
            opacity: .8;
            flex-shrink: 0;
        }

        /* ── Hero image ───────────────────────────────────────────────── */
        .hero-img {
            width: 100%;
            height: 240px;
            object-fit: cover;
            display: block;
        }

        .hero-placeholder {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
        }

        /* ── Sections ─────────────────────────────────────────────────── */
        .section {
            background: #fff;
            border-radius: 16px;
            margin: 12px;
            padding: 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }

        .section-title {
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #ce1212;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ── Badges ───────────────────────────────────────────────────── */
        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
        }

        /* ── Grille infos rapides ─────────────────────────────────────── */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-top: 12px;
        }

        .info-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 10px 6px;
            text-align: center;
        }

        .info-box .val {
            font-size: 1.2rem;
            font-weight: 800;
            color: #ce1212;
            display: block;
        }

        .info-box .lbl {
            font-size: .65rem;
            color: #888;
            margin-top: 2px;
        }

        /* ── Bilan nutritionnel ───────────────────────────────────────── */
        .nutri-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .nutri-box {
            border-radius: 10px;
            padding: 10px;
            text-align: center;
        }

        .nutri-box .n-val {
            font-size: 1.1rem;
            font-weight: 800;
            display: block;
        }

        .nutri-box .n-lbl {
            font-size: .65rem;
            color: #888;
        }

        /* ── Étapes ───────────────────────────────────────────────────── */
        .step-item {
            display: flex;
            gap: 12px;
            margin-bottom: 14px;
            align-items: flex-start;
        }

        .step-num {
            width: 28px;
            height: 28px;
            min-width: 28px;
            background: #ce1212;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            font-weight: 700;
            margin-top: 1px;
        }

        .step-text {
            font-size: .9rem;
            line-height: 1.5;
            color: #333;
            padding-top: 4px;
        }

        /* ── Ingrédients ──────────────────────────────────────────────── */
        .ing-list {
            list-style: none;
        }

        .ing-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: .9rem;
        }

        .ing-item:last-child { border-bottom: none; }

        .ing-name {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        .ing-dot {
            width: 8px;
            height: 8px;
            background: #ce1212;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .ing-qty {
            font-size: .8rem;
            color: #888;
            background: #f5f5f5;
            padding: 2px 8px;
            border-radius: 20px;
        }

        /* ── Repas cards ──────────────────────────────────────────────── */
        .repas-card {
            border: 1px solid #f0f0f0;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .repas-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            background: #fafafa;
            cursor: pointer;
            user-select: none;
        }

        .repas-thumb {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .repas-thumb-ph {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .repas-name { font-weight: 600; font-size: .9rem; }
        .repas-meta { font-size: .75rem; color: #888; margin-top: 2px; }

        .repas-body {
            padding: 12px;
            display: none;
            border-top: 1px solid #f0f0f0;
        }

        .repas-body.open { display: block; }

        .chevron {
            margin-left: auto;
            transition: transform .2s;
            color: #aaa;
        }

        .chevron.open { transform: rotate(180deg); }

        /* ── Macro pills ──────────────────────────────────────────────── */
        .macro-pills {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .macro-pill {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
        }

        /* ── Vidéo YouTube ────────────────────────────────────────────── */
        .video-wrapper {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 12px;
        }

        .video-wrapper iframe {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            border: 0;
        }

        /* ── Bottom action bar ────────────────────────────────────────── */
        .bottom-bar {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: #fff;
            border-top: 1px solid #e8e8e8;
            padding: 10px 16px;
            display: flex;
            gap: 8px;
            z-index: 100;
            box-shadow: 0 -2px 12px rgba(0,0,0,.08);
        }

        .btn-mob {
            flex: 1;
            padding: 10px;
            border-radius: 10px;
            border: none;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-mob-primary { background: #ce1212; color: #fff; }
        .btn-mob-primary:hover { background: #a80f0f; color: #fff; }
        .btn-mob-outline { background: #f5f5f5; color: #333; }
        .btn-mob-outline:hover { background: #e8e8e8; }

        /* ── Toast notification ───────────────────────────────────────── */
        .toast-mob {
            position: fixed;
            bottom: 80px;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: #1a1a1a;
            color: #fff;
            padding: 10px 20px;
            border-radius: 20px;
            font-size: .85rem;
            opacity: 0;
            transition: all .3s;
            pointer-events: none;
            white-space: nowrap;
            z-index: 200;
        }

        .toast-mob.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    </style>
</head>
<body>

    <!-- ── Header sticky ──────────────────────────────────────────────── -->
    <div class="mob-header">
        <a href="javascript:history.back()" class="back-btn" aria-label="Retour">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="title"><?= htmlspecialchars($recette['nom_recette']) ?></div>
        <div class="brand">SmartMeal</div>
    </div>

    <!-- ── Photo hero ─────────────────────────────────────────────────── -->
    <?php if (!empty($recette['image_recette'])): ?>
        <img class="hero-img"
             src="<?= htmlspecialchars($baseUrl . '/' . $recette['image_recette']) ?>"
             alt="<?= htmlspecialchars($recette['nom_recette']) ?>">
    <?php else: ?>
        <div class="hero-placeholder">🍽</div>
    <?php endif; ?>

    <!-- ── Infos générales ────────────────────────────────────────────── -->
    <div class="section">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
            <h1 style="font-family:'Amatic SC',sans-serif;font-size:1.8rem;line-height:1.1;color:#1a1a1a;flex:1;">
                <?= htmlspecialchars($recette['nom_recette']) ?>
            </h1>
            <span class="badge-pill ms-2"
                  style="background:<?= $diffBadge['bg'] ?>;color:<?= $diffBadge['color'] ?>;flex-shrink:0;">
                <?= $diffBadge['icon'] ?> <?= htmlspecialchars($recette['difficulte'] ?? 'Facile') ?>
            </span>
        </div>

        <!-- Infos rapides : temps + personnes -->
        <div class="info-grid">
            <?php if (!empty($recette['temps_prep'])): ?>
            <div class="info-box">
                <span class="val"><?= $recette['temps_prep'] ?></span>
                <span class="lbl">⏱ min prép.</span>
            </div>
            <?php endif; ?>
            <?php if (!empty($recette['temps_cuisson'])): ?>
            <div class="info-box">
                <span class="val"><?= $recette['temps_cuisson'] ?></span>
                <span class="lbl">🔥 min cuisson</span>
            </div>
            <?php endif; ?>
            <div class="info-box">
                <span class="val"><?= $recette['nb_personnes'] ?></span>
                <span class="lbl">👥 personnes</span>
            </div>
            <?php if (count($repas) > 0): ?>
            <div class="info-box">
                <span class="val"><?= count($repas) ?></span>
                <span class="lbl">🍽 repas</span>
            </div>
            <?php endif; ?>
            <?php if ($totalCalories > 0): ?>
            <div class="info-box">
                <span class="val"><?= number_format($totalCalories, 0) ?></span>
                <span class="lbl">🔥 kcal total</span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Bilan nutritionnel ──────────────────────────────────────────── -->
    <?php if ($totalCalories > 0 || $totalProteines > 0 || $totalGlucides > 0 || $totalLipides > 0): ?>
    <div class="section">
        <div class="section-title"><i class="bi bi-bar-chart-fill"></i> Bilan nutritionnel</div>
        <div class="nutri-grid">
            <?php if ($totalCalories > 0): ?>
            <div class="nutri-box" style="background:#fff5f5;">
                <span class="n-val" style="color:#ce1212;"><?= number_format($totalCalories, 0) ?> kcal</span>
                <span class="n-lbl">Calories totales</span>
            </div>
            <?php endif; ?>
            <?php if ($totalProteines > 0): ?>
            <div class="nutri-box" style="background:#eff6ff;">
                <span class="n-val" style="color:#1d4ed8;"><?= number_format($totalProteines, 1) ?>g</span>
                <span class="n-lbl">Protéines</span>
            </div>
            <?php endif; ?>
            <?php if ($totalGlucides > 0): ?>
            <div class="nutri-box" style="background:#fffbeb;">
                <span class="n-val" style="color:#d97706;"><?= number_format($totalGlucides, 1) ?>g</span>
                <span class="n-lbl">Glucides</span>
            </div>
            <?php endif; ?>
            <?php if ($totalLipides > 0): ?>
            <div class="nutri-box" style="background:#fef2f2;">
                <span class="n-val" style="color:#dc2626;"><?= number_format($totalLipides, 1) ?>g</span>
                <span class="n-lbl">Lipides</span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Étapes de préparation ──────────────────────────────────────── -->
    <?php if (!empty($recette['etapes'])): ?>
    <div class="section">
        <div class="section-title"><i class="bi bi-list-ol"></i> Étapes de préparation</div>
        <?php
        $etapesArr = array_values(array_filter(
            array_map('trim', explode("\n", $recette['etapes']))
        ));
        foreach ($etapesArr as $i => $etape):
            $text = preg_replace('/^\d+[\.\)]\s*/', '', $etape);
        ?>
        <div class="step-item">
            <div class="step-num"><?= $i + 1 ?></div>
            <div class="step-text"><?= htmlspecialchars($text) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ── Repas associés ─────────────────────────────────────────────── -->
    <?php if (!empty($repas)): ?>
    <div class="section">
        <div class="section-title"><i class="bi bi-bowl-hot"></i> Repas (<?= count($repas) ?>)</div>

        <?php foreach ($repas as $i => $r): ?>
        <div class="repas-card">
            <!-- En-tête cliquable -->
            <div class="repas-card-header" onclick="toggleRepas(<?= $r['id_repas'] ?>)">
                <?php if (!empty($r['image_repas'])): ?>
                    <img class="repas-thumb"
                         src="<?= htmlspecialchars($baseUrl . '/' . $r['image_repas']) ?>"
                         alt="<?= htmlspecialchars($r['nom']) ?>">
                <?php else: ?>
                    <div class="repas-thumb-ph">🥘</div>
                <?php endif; ?>
                <div style="flex:1;min-width:0;">
                    <div class="repas-name"><?= htmlspecialchars($r['nom']) ?></div>
                    <div class="repas-meta">
                        <?= htmlspecialchars($r['type_repas'] ?? '') ?>
                        <?php if (!empty($r['calories'])): ?>
                            · <?= $r['calories'] ?> kcal
                        <?php endif; ?>
                        · <?= count($r['ingredients'] ?? []) ?> ingr.
                    </div>
                </div>
                <i class="bi bi-chevron-down chevron" id="chev-<?= $r['id_repas'] ?>"></i>
            </div>

            <!-- Corps dépliable -->
            <div class="repas-body <?= $i === 0 ? 'open' : '' ?>" id="body-<?= $r['id_repas'] ?>">

                <!-- Macros -->
                <?php if (!empty($r['calories']) || !empty($r['proteines']) || !empty($r['glucides']) || !empty($r['lipides'])): ?>
                <div class="macro-pills">
                    <?php if (!empty($r['calories'])): ?>
                    <span class="macro-pill" style="background:#fff5f5;color:#ce1212;">🔥 <?= $r['calories'] ?> kcal</span>
                    <?php endif; ?>
                    <?php if (!empty($r['proteines'])): ?>
                    <span class="macro-pill" style="background:#eff6ff;color:#1d4ed8;">💪 <?= $r['proteines'] ?>g prot.</span>
                    <?php endif; ?>
                    <?php if (!empty($r['glucides'])): ?>
                    <span class="macro-pill" style="background:#fffbeb;color:#d97706;">⚡ <?= $r['glucides'] ?>g gluc.</span>
                    <?php endif; ?>
                    <?php if (!empty($r['lipides'])): ?>
                    <span class="macro-pill" style="background:#fef2f2;color:#dc2626;">🫧 <?= $r['lipides'] ?>g lip.</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Ingrédients -->
                <?php if (!empty($r['ingredients'])): ?>
                <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;color:#aaa;margin-bottom:6px;">
                    🧺 Ingrédients
                </div>
                <ul class="ing-list">
                    <?php foreach ($r['ingredients'] as $ing): ?>
                    <li class="ing-item">
                        <span class="ing-name">
                            <span class="ing-dot"></span>
                            <?= htmlspecialchars($ing['nom_ingredient']) ?>
                        </span>
                        <?php if (!empty($ing['quantite'])): ?>
                        <span class="ing-qty">
                            <?= htmlspecialchars($ing['quantite']) ?>
                            <?= htmlspecialchars($ing['unite'] ?? '') ?>
                        </span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <!-- Description -->
                <?php if (!empty($r['description'])): ?>
                <p style="font-size:.85rem;color:#666;margin-top:8px;font-style:italic;">
                    <?= htmlspecialchars($r['description']) ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ── Vidéo YouTube ──────────────────────────────────────────────── -->
    <?php
    // Récupérer video_youtube depuis la recette (colonne directe)
    $videoId = $recette['video_youtube'] ?? null;
    // Fallback : chercher dans le premier repas si disponible
    if (empty($videoId) && !empty($repas)) {
        $videoId = $repas[0]['video_youtube'] ?? null;
    }
    ?>
    <?php if (!empty($videoId)): ?>
    <div class="section">
        <div class="section-title"><i class="bi bi-youtube" style="color:#ff0000;"></i> Vidéo de la recette</div>
        <div class="video-wrapper">
            <iframe
                src="https://www.youtube.com/embed/<?= htmlspecialchars($videoId) ?>?rel=0&modestbranding=1"
                title="Vidéo : <?= htmlspecialchars($recette['nom_recette']) ?>"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
                loading="lazy">
            </iframe>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Bottom action bar ──────────────────────────────────────────── -->
    <div class="bottom-bar">
        <button class="btn-mob btn-mob-outline" onclick="shareRecette()">
            <i class="bi bi-share"></i> Partager
        </button>
        <a href="repas.php" class="btn-mob btn-mob-primary">
            <i class="bi bi-grid"></i> Tous les repas
        </a>
    </div>

    <!-- Toast notification -->
    <div class="toast-mob" id="toast">✅ Lien copié !</div>

    <script>
        // ── Accordion repas ───────────────────────────────────────────────
        function toggleRepas(id) {
            var body  = document.getElementById('body-' + id);
            var chev  = document.getElementById('chev-' + id);
            var isOpen = body.classList.contains('open');
            body.classList.toggle('open', !isOpen);
            chev.classList.toggle('open', !isOpen);
        }

        // Ouvrir le premier repas par défaut
        (function() {
            var first = document.querySelector('.repas-body');
            var firstChev = document.querySelector('.chevron');
            if (first) { first.classList.add('open'); }
            if (firstChev) { firstChev.classList.add('open'); }
        })();

        // ── Partage natif ou copie du lien ────────────────────────────────
        function shareRecette() {
            var url   = window.location.href;
            var title = <?= json_encode($recette['nom_recette']) ?>;

            // Web Share API (disponible sur mobile Chrome/Safari)
            if (navigator.share) {
                navigator.share({
                    title: title + ' — SmartMeal Planner',
                    text:  'Découvrez cette recette : ' + title,
                    url:   url
                }).catch(function() {});
            } else {
                // Fallback : copier dans le presse-papier
                navigator.clipboard.writeText(url).then(function() {
                    showToast('✅ Lien copié !');
                }).catch(function() {
                    showToast('📋 ' + url);
                });
            }
        }

        // ── Toast ─────────────────────────────────────────────────────────
        function showToast(msg) {
            var t = document.getElementById('toast');
            t.textContent = msg;
            t.classList.add('show');
            setTimeout(function() { t.classList.remove('show'); }, 2500);
        }
    </script>
</body>
</html>
