<?php
/**
 * export_recette_pdf.php — Export PDF d'une recette individuelle
 *
 * Génère une fiche recette complète au format PDF :
 *   - Informations générales (difficulté, temps, personnes)
 *   - Étapes de préparation
 *   - Liste des repas associés avec leurs macros nutritionnelles
 *   - Ingrédients de chaque repas
 *
 * Paramètre GET requis :
 *   - id : identifiant de la recette (int)
 */

defined('APP_ROOT') || require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Recette.php';

// Récupérer et valider l'identifiant de la recette
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: recette.php');
    exit;
}

$recetteModel = new Recette();

// Charger la recette avec tous ses repas et ingrédients (jointure complète)
$recette = $recetteModel->getRecetteWithRepas($id);

if (!$recette) {
    header('Location: recette.php');
    exit;
}

// Construire l'URL de base pour les images
$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$project = str_replace('\\', '/', dirname(__DIR__, 2));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);

// Calculer les statistiques nutritionnelles globales de la recette
$repas         = $recette['repas'] ?? [];
$totalCalories = array_sum(array_column($repas, 'calories'));
$totalProteines= array_sum(array_column($repas, 'proteines'));
$totalGlucides = array_sum(array_column($repas, 'glucides'));
$totalLipides  = array_sum(array_column($repas, 'lipides'));
$nbRepas       = count($repas);

// Compter le total d'ingrédients sur tous les repas
$nbIngredients = array_sum(array_map(fn($r) => count($r['ingredients'] ?? []), $repas));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche Recette — <?= htmlspecialchars($recette['nom_recette']) ?></title>
    <style>
        /* ── Reset & base ─────────────────────────────────────────────── */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11pt;
            color: #1a1a1a;
            background: #fff;
            padding: 24px 28px;
            max-width: 900px;
            margin: 0 auto;
        }

        /* ── Boutons d'action (masqués à l'impression) ────────────────── */
        .print-actions {
            position: fixed;
            top: 16px;
            right: 16px;
            display: flex;
            gap: 8px;
            z-index: 999;
        }

        .btn-print {
            background: #ce1212;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 11pt;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 8px rgba(206,18,18,.3);
            text-decoration: none;
        }

        .btn-print:hover { background: #a80f0f; }

        .btn-back {
            background: #f0f0f0;
            color: #333;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 11pt;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-back:hover { background: #e0e0e0; }

        /* ── En-tête du document ──────────────────────────────────────── */
        .pdf-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #ce1212;
            padding-bottom: 12px;
            margin-bottom: 22px;
        }

        .brand {
            font-size: 20pt;
            font-weight: 800;
            color: #ce1212;
        }

        .brand span { color: #1a1a1a; }

        .header-meta {
            text-align: right;
            font-size: 8.5pt;
            color: #888;
            line-height: 1.7;
        }

        /* ── Bloc hero : image + titre + badges ──────────────────────── */
        .hero {
            display: flex;
            gap: 20px;
            margin-bottom: 22px;
            align-items: flex-start;
        }

        .hero-img {
            width: 180px;
            height: 140px;
            object-fit: cover;
            border-radius: 10px;
            flex-shrink: 0;
            border: 1px solid #e8e8e8;
        }

        .hero-placeholder {
            width: 180px;
            height: 140px;
            border-radius: 10px;
            flex-shrink: 0;
            background: linear-gradient(135deg, #e8f0fe, #c7d7fd);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36pt;
            color: #0d6efd;
            opacity: .4;
        }

        .hero-info { flex: 1; }

        .recette-title {
            font-size: 22pt;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 8px;
            line-height: 1.2;
        }

        /* ── Badges ───────────────────────────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 8.5pt;
            font-weight: 600;
            margin-right: 4px;
        }

        .badge-facile    { background: #d1fae5; color: #065f46; }
        .badge-moyen     { background: #fef3c7; color: #92400e; }
        .badge-difficile { background: #fee2e2; color: #991b1b; }
        .badge-gray      { background: #f0f0f0; color: #555; }
        .badge-blue      { background: #dbeafe; color: #1e40af; }
        .badge-red       { background: #fee2e2; color: #991b1b; }

        /* ── Grille d'infos rapides ───────────────────────────────────── */
        .info-grid {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 8px 14px;
            min-width: 80px;
            text-align: center;
        }

        .info-item .val {
            font-size: 14pt;
            font-weight: 800;
            color: #ce1212;
        }

        .info-item .lbl {
            font-size: 7.5pt;
            color: #888;
            margin-top: 2px;
        }

        /* ── Séparateur de section ────────────────────────────────────── */
        .section-title {
            font-size: 11pt;
            font-weight: 700;
            color: #ce1212;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-left: 4px solid #ce1212;
            padding-left: 10px;
            margin: 20px 0 10px;
        }

        /* ── Bloc étapes ──────────────────────────────────────────────── */
        .etapes-box {
            background: #fffbf0;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 14px 16px;
            font-size: 10pt;
            line-height: 1.8;
            white-space: pre-line;
            color: #444;
        }

        /* ── Tableau nutritionnel global ──────────────────────────────── */
        .nutri-summary {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .nutri-box {
            flex: 1;
            min-width: 90px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 8px 10px;
            text-align: center;
        }

        .nutri-box .n-val {
            font-size: 14pt;
            font-weight: 800;
            display: block;
        }

        .nutri-box .n-lbl {
            font-size: 7.5pt;
            color: #888;
        }

        .n-cal   { color: #ce1212; }
        .n-prot  { color: #1d4ed8; }
        .n-gluc  { color: #d97706; }
        .n-lip   { color: #dc2626; }

        /* ── Carte repas ──────────────────────────────────────────────── */
        .repas-card {
            border: 1px solid #e8e8e8;
            border-radius: 10px;
            margin-bottom: 14px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .repas-header {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f8f9fa;
            padding: 10px 14px;
            border-bottom: 1px solid #e8e8e8;
        }

        .repas-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 7px;
            flex-shrink: 0;
        }

        .repas-thumb-placeholder {
            width: 50px;
            height: 50px;
            border-radius: 7px;
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18pt;
            flex-shrink: 0;
        }

        .repas-name {
            font-size: 13pt;
            font-weight: 700;
            color: #1a1a1a;
        }

        .repas-meta {
            font-size: 8.5pt;
            color: #888;
            margin-top: 2px;
        }

        .repas-body {
            display: flex;
            gap: 0;
        }

        /* Colonne macros */
        .repas-macros {
            width: 160px;
            flex-shrink: 0;
            border-right: 1px solid #e8e8e8;
            padding: 12px;
        }

        .macro-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 3px 0;
            font-size: 9pt;
            border-bottom: 1px dashed #f0f0f0;
        }

        .macro-row:last-child { border-bottom: none; }

        .macro-lbl { color: #888; }
        .macro-val { font-weight: 700; }

        /* Colonne ingrédients */
        .repas-ingredients {
            flex: 1;
            padding: 12px 14px;
        }

        .ing-title {
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #888;
            margin-bottom: 6px;
        }

        .ing-list {
            list-style: none;
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }

        .ing-item {
            background: #f0f4ff;
            border-radius: 20px;
            padding: 2px 10px;
            font-size: 8.5pt;
            color: #1e40af;
        }

        .ing-item .qty {
            color: #888;
            font-size: 8pt;
        }

        /* Description repas */
        .repas-desc {
            padding: 0 14px 10px;
            font-size: 9pt;
            color: #666;
            font-style: italic;
        }

        /* ── Pied de page ─────────────────────────────────────────────── */
        .pdf-footer {
            margin-top: 28px;
            padding-top: 10px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            font-size: 8pt;
            color: #aaa;
        }

        /* ── Règles d'impression ──────────────────────────────────────── */
        @media print {
            .print-actions { display: none !important; }

            body {
                padding: 0;
                font-size: 10pt;
                max-width: 100%;
            }

            .repas-card { page-break-inside: avoid; }

            @page {
                size: A4 portrait;
                margin: 14mm 12mm;
            }
        }
    </style>
</head>
<body>

    <!-- ── Boutons d'action ────────────────────────────────────────────── -->
    <div class="print-actions">
        <a href="view_recette.php?id=<?= $id ?>" class="btn-back">← Retour</a>
        <button class="btn-print" onclick="window.print()">🖨️ Télécharger PDF</button>
    </div>

    <!-- ── En-tête du document ────────────────────────────────────────── -->
    <div class="pdf-header">
        <div>
            <div class="brand">Smart<span>Meal</span> Planner</div>
            <div style="font-size:8.5pt;color:#888;margin-top:2px;">Fiche Recette Détaillée</div>
        </div>
        <div class="header-meta">
            <strong>Exporté le :</strong> <?= date('d/m/Y à H:i') ?><br>
            <strong>Réf. recette :</strong> #<?= $recette['id_recette'] ?><br>
            <strong>Créée le :</strong> <?= !empty($recette['created_at']) ? date('d/m/Y', strtotime($recette['created_at'])) : '—' ?>
        </div>
    </div>

    <!-- ── Bloc hero : image + titre ──────────────────────────────────── -->
    <div class="hero">
        <?php if (!empty($recette['image_recette'])): ?>
            <img class="hero-img"
                 src="<?= htmlspecialchars($baseUrl . '/' . $recette['image_recette']) ?>"
                 alt="<?= htmlspecialchars($recette['nom_recette']) ?>">
        <?php else: ?>
            <div class="hero-placeholder">🍽</div>
        <?php endif; ?>

        <div class="hero-info">
            <div class="recette-title"><?= htmlspecialchars($recette['nom_recette']) ?></div>

            <!-- Badges difficulté + stats -->
            <div style="margin-bottom:10px;">
                <?php
                $badgeClass = match($recette['difficulte'] ?? 'Facile') {
                    'Facile'    => 'badge-facile',
                    'Moyen'     => 'badge-moyen',
                    'Difficile' => 'badge-difficile',
                    default     => 'badge-gray',
                };
                ?>
                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($recette['difficulte'] ?? 'Facile') ?></span>
                <span class="badge badge-gray"><?= $nbRepas ?> repas</span>
                <span class="badge badge-blue"><?= $nbIngredients ?> ingrédients</span>
                <?php if ($totalCalories > 0): ?>
                    <span class="badge badge-red"><?= number_format($totalCalories, 0, ',', ' ') ?> kcal total</span>
                <?php endif; ?>
            </div>

            <!-- Infos rapides -->
            <div class="info-grid">
                <?php if (!empty($recette['temps_prep'])): ?>
                <div class="info-item">
                    <span class="val"><?= $recette['temps_prep'] ?></span>
                    <span class="lbl">min prép.</span>
                </div>
                <?php endif; ?>
                <?php if (!empty($recette['temps_cuisson'])): ?>
                <div class="info-item">
                    <span class="val"><?= $recette['temps_cuisson'] ?></span>
                    <span class="lbl">min cuisson</span>
                </div>
                <?php endif; ?>
                <div class="info-item">
                    <span class="val"><?= $recette['nb_personnes'] ?></span>
                    <span class="lbl">personnes</span>
                </div>
                <?php if (!empty($recette['temps_prep']) && !empty($recette['temps_cuisson'])): ?>
                <div class="info-item">
                    <span class="val"><?= $recette['temps_prep'] + $recette['temps_cuisson'] ?></span>
                    <span class="lbl">min total</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Étapes de préparation ──────────────────────────────────────── -->
    <?php if (!empty($recette['etapes'])): ?>
    <div class="section-title">📋 Étapes de préparation</div>
    <div class="etapes-box"><?= htmlspecialchars($recette['etapes']) ?></div>
    <?php endif; ?>

    <!-- ── Bilan nutritionnel global ──────────────────────────────────── -->
    <?php if ($totalCalories > 0 || $totalProteines > 0 || $totalGlucides > 0 || $totalLipides > 0): ?>
    <div class="section-title">📊 Bilan nutritionnel global (tous repas)</div>
    <div class="nutri-summary">
        <?php if ($totalCalories > 0): ?>
        <div class="nutri-box">
            <span class="n-val n-cal"><?= number_format($totalCalories, 0, ',', ' ') ?></span>
            <span class="n-lbl">kcal total</span>
        </div>
        <?php endif; ?>
        <?php if ($totalProteines > 0): ?>
        <div class="nutri-box">
            <span class="n-val n-prot"><?= number_format($totalProteines, 1, ',', ' ') ?>g</span>
            <span class="n-lbl">Protéines</span>
        </div>
        <?php endif; ?>
        <?php if ($totalGlucides > 0): ?>
        <div class="nutri-box">
            <span class="n-val n-gluc"><?= number_format($totalGlucides, 1, ',', ' ') ?>g</span>
            <span class="n-lbl">Glucides</span>
        </div>
        <?php endif; ?>
        <?php if ($totalLipides > 0): ?>
        <div class="nutri-box">
            <span class="n-val n-lip"><?= number_format($totalLipides, 1, ',', ' ') ?>g</span>
            <span class="n-lbl">Lipides</span>
        </div>
        <?php endif; ?>
        <?php if ($nbRepas > 0 && $totalCalories > 0): ?>
        <div class="nutri-box">
            <span class="n-val" style="color:#6b7280;"><?= number_format($totalCalories / $nbRepas, 0, ',', ' ') ?></span>
            <span class="n-lbl">kcal moy./repas</span>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ── Repas associés ─────────────────────────────────────────────── -->
    <div class="section-title">🍽 Repas associés (<?= $nbRepas ?>)</div>

    <?php if (empty($repas)): ?>
        <p style="color:#aaa;font-style:italic;padding:16px 0;">Aucun repas associé à cette recette.</p>
    <?php else: ?>
        <?php foreach ($repas as $r): ?>
        <div class="repas-card">

            <!-- En-tête du repas -->
            <div class="repas-header">
                <?php if (!empty($r['image_repas'])): ?>
                    <img class="repas-thumb"
                         src="<?= htmlspecialchars($baseUrl . '/' . $r['image_repas']) ?>"
                         alt="<?= htmlspecialchars($r['nom']) ?>">
                <?php else: ?>
                    <div class="repas-thumb-placeholder">🥘</div>
                <?php endif; ?>

                <div style="flex:1;">
                    <div class="repas-name"><?= htmlspecialchars($r['nom']) ?></div>
                    <div class="repas-meta">
                        <?php if (!empty($r['type_repas'])): ?>
                            <span class="badge badge-gray" style="font-size:7.5pt;"><?= htmlspecialchars($r['type_repas']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($r['calories'])): ?>
                            <span class="badge badge-red" style="font-size:7.5pt;"><?= htmlspecialchars($r['calories']) ?> kcal</span>
                        <?php endif; ?>
                        <span style="color:#bbb;margin-left:4px;">Repas #<?= $r['id_repas'] ?></span>
                    </div>
                </div>
            </div>

            <!-- Corps : macros + ingrédients -->
            <div class="repas-body">

                <!-- Colonne macros nutritionnelles -->
                <div class="repas-macros">
                    <div style="font-size:7.5pt;font-weight:700;text-transform:uppercase;color:#aaa;margin-bottom:6px;">Nutrition</div>
                    <div class="macro-row">
                        <span class="macro-lbl">Calories</span>
                        <span class="macro-val n-cal"><?= !empty($r['calories'])  ? $r['calories']  . ' kcal' : '—' ?></span>
                    </div>
                    <div class="macro-row">
                        <span class="macro-lbl">Protéines</span>
                        <span class="macro-val n-prot"><?= !empty($r['proteines']) ? $r['proteines'] . ' g'    : '—' ?></span>
                    </div>
                    <div class="macro-row">
                        <span class="macro-lbl">Glucides</span>
                        <span class="macro-val n-gluc"><?= !empty($r['glucides'])  ? $r['glucides']  . ' g'    : '—' ?></span>
                    </div>
                    <div class="macro-row">
                        <span class="macro-lbl">Lipides</span>
                        <span class="macro-val n-lip"><?= !empty($r['lipides'])   ? $r['lipides']   . ' g'    : '—' ?></span>
                    </div>
                </div>

                <!-- Colonne ingrédients -->
                <div class="repas-ingredients">
                    <div class="ing-title">🧺 Ingrédients (<?= count($r['ingredients'] ?? []) ?>)</div>
                    <?php if (empty($r['ingredients'])): ?>
                        <span style="color:#ccc;font-style:italic;font-size:9pt;">Aucun ingrédient renseigné.</span>
                    <?php else: ?>
                        <ul class="ing-list">
                            <?php foreach ($r['ingredients'] as $ing): ?>
                            <li class="ing-item">
                                <?= htmlspecialchars($ing['nom_ingredient']) ?>
                                <?php if (!empty($ing['quantite'])): ?>
                                    <span class="qty">
                                        — <?= htmlspecialchars($ing['quantite']) ?>
                                        <?= htmlspecialchars($ing['unite'] ?? '') ?>
                                    </span>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Description du repas si disponible -->
            <?php if (!empty($r['description'])): ?>
            <div class="repas-desc">
                💬 <?= htmlspecialchars($r['description']) ?>
            </div>
            <?php endif; ?>

        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- ── Pied de page ───────────────────────────────────────────────── -->
    <div class="pdf-footer">
        <span>SmartMeal Planner — Fiche Recette</span>
        <span><?= htmlspecialchars($recette['nom_recette']) ?></span>
        <span>Exporté le <?= date('d/m/Y à H:i:s') ?></span>
    </div>

    <!-- ── Déclenchement automatique de l'impression ─────────────────── -->
    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 700);
        });
    </script>

</body>
</html>
