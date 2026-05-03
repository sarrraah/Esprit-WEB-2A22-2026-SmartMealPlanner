<?php
/**
 * export_recettes_pdf.php — Export PDF de la liste des recettes
 *
 * Génère une page HTML optimisée pour l'impression/export PDF.
 * Le navigateur déclenche automatiquement la boîte de dialogue
 * "Enregistrer en PDF" via window.print().
 *
 * Paramètres GET supportés (hérités de recette.php) :
 *   - orderBy  : colonne de tri (id_recette | total_calories | nb_repas | nom_recette)
 *   - orderDir : direction du tri (ASC | DESC)
 */

defined('APP_ROOT') || require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Recette.php';

// Récupérer les paramètres de tri transmis depuis recette.php
$orderBy  = $_GET['orderBy']  ?? 'id_recette';
$orderDir = $_GET['orderDir'] ?? 'ASC';

$recetteModel = new Recette();
$recettes     = $recetteModel->getAllRecettesWithRepasCount($orderBy, $orderDir);

// Construire l'URL de base pour les images
$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$project = str_replace('\\', '/', dirname(__DIR__, 2));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);

// Libellé du tri actif pour l'en-tête du PDF
$triLabel = match(true) {
    $orderBy === 'total_calories' && $orderDir === 'ASC'  => 'Triées par calories ↑ croissant',
    $orderBy === 'total_calories' && $orderDir === 'DESC' => 'Triées par calories ↓ décroissant',
    $orderBy === 'nb_repas'       && $orderDir === 'ASC'  => 'Triées par nombre de repas ↑',
    $orderBy === 'nb_repas'       && $orderDir === 'DESC' => 'Triées par nombre de repas ↓',
    $orderBy === 'nom_recette'                            => 'Triées par nom',
    default                                               => 'Ordre par défaut',
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Recettes — SmartMeal Planner</title>
    <style>
        /* ── Reset & base ─────────────────────────────────────────────── */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11pt;
            color: #1a1a1a;
            background: #fff;
            padding: 20px;
        }

        /* ── En-tête du document ──────────────────────────────────────── */
        .pdf-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #ce1212;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }

        .pdf-header .brand {
            font-size: 22pt;
            font-weight: 800;
            color: #ce1212;
            letter-spacing: -0.5px;
        }

        .pdf-header .brand span {
            color: #1a1a1a;
        }

        .pdf-header .meta {
            text-align: right;
            font-size: 9pt;
            color: #666;
            line-height: 1.6;
        }

        .pdf-header .meta strong {
            color: #1a1a1a;
        }

        /* ── Titre de section ─────────────────────────────────────────── */
        .pdf-title {
            font-size: 16pt;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 4px;
        }

        .pdf-subtitle {
            font-size: 9pt;
            color: #888;
            margin-bottom: 18px;
        }

        /* ── Tableau principal ────────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }

        thead tr {
            background-color: #ce1212;
            color: #fff;
        }

        thead th {
            padding: 9px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 9.5pt;
            letter-spacing: 0.3px;
        }

        tbody tr {
            border-bottom: 1px solid #e8e8e8;
        }

        /* Alternance de couleur des lignes */
        tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        tbody tr:hover {
            background-color: #fff5f5;
        }

        tbody td {
            padding: 8px 10px;
            vertical-align: middle;
        }

        /* ── Badges de difficulté ─────────────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 8.5pt;
            font-weight: 600;
        }

        .badge-facile    { background: #d1fae5; color: #065f46; }
        .badge-moyen     { background: #fef3c7; color: #92400e; }
        .badge-difficile { background: #fee2e2; color: #991b1b; }

        /* ── Colonne calories ─────────────────────────────────────────── */
        .cal-value {
            font-weight: 700;
            color: #ce1212;
        }

        .cal-zero {
            color: #bbb;
            font-style: italic;
        }

        /* ── Pied de page ─────────────────────────────────────────────── */
        .pdf-footer {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            font-size: 8.5pt;
            color: #999;
        }

        /* ── Résumé statistiques ──────────────────────────────────────── */
        .stats-row {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .stat-box {
            flex: 1;
            min-width: 120px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 14px;
            text-align: center;
        }

        .stat-box .stat-num {
            font-size: 18pt;
            font-weight: 800;
            color: #ce1212;
            display: block;
        }

        .stat-box .stat-lbl {
            font-size: 8.5pt;
            color: #888;
        }

        /* ── Bouton d'impression (masqué à l'impression) ──────────────── */
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

        /* ── Règles d'impression ──────────────────────────────────────── */
        @media print {
            /* Masquer les boutons à l'impression */
            .print-actions { display: none !important; }

            body { padding: 0; font-size: 10pt; }

            /* Éviter les coupures de page dans les lignes du tableau */
            tbody tr { page-break-inside: avoid; }

            /* Forcer un saut de page avant le pied de page si nécessaire */
            .pdf-footer { page-break-before: auto; }

            /* Répéter l'en-tête du tableau sur chaque page */
            thead { display: table-header-group; }

            /* Taille de page A4 paysage pour plus de colonnes */
            @page {
                size: A4 landscape;
                margin: 15mm 12mm;
            }
        }
    </style>
</head>
<body>

    <!-- ── Boutons d'action (masqués à l'impression) ──────────────────── -->
    <div class="print-actions">
        <a href="recette.php?orderBy=<?= htmlspecialchars($orderBy) ?>&orderDir=<?= htmlspecialchars($orderDir) ?>"
           class="btn-back">
            ← Retour
        </a>
        <button class="btn-print" onclick="window.print()">
            🖨️ Télécharger / Imprimer PDF
        </button>
    </div>

    <!-- ── En-tête du document PDF ────────────────────────────────────── -->
    <div class="pdf-header">
        <div>
            <div class="brand">Smart<span>Meal</span> Planner</div>
            <div style="font-size:9pt;color:#888;margin-top:2px;">Rapport — Liste des Recettes</div>
        </div>
        <div class="meta">
            <strong>Date d'export :</strong> <?= date('d/m/Y à H:i') ?><br>
            <strong>Tri appliqué :</strong> <?= htmlspecialchars($triLabel) ?><br>
            <strong>Total recettes :</strong> <?= count($recettes) ?>
        </div>
    </div>

    <!-- ── Titre ──────────────────────────────────────────────────────── -->
    <div class="pdf-title">📋 Liste des Recettes</div>
    <div class="pdf-subtitle"><?= htmlspecialchars($triLabel) ?> — Généré le <?= date('d/m/Y') ?></div>

    <!-- ── Statistiques résumées ──────────────────────────────────────── -->
    <?php
    // Calculer les statistiques globales pour le résumé
    $totalRecettes  = count($recettes);
    $totalRepas     = array_sum(array_column($recettes, 'nb_repas'));
    $totalCalories  = array_sum(array_column($recettes, 'total_calories'));
    $maxCal         = $totalRecettes > 0 ? max(array_column($recettes, 'total_calories')) : 0;
    ?>
    <div class="stats-row">
        <div class="stat-box">
            <span class="stat-num"><?= $totalRecettes ?></span>
            <span class="stat-lbl">Recettes</span>
        </div>
        <div class="stat-box">
            <span class="stat-num"><?= $totalRepas ?></span>
            <span class="stat-lbl">Repas associés</span>
        </div>
        <div class="stat-box">
            <span class="stat-num"><?= number_format($totalCalories, 0, ',', ' ') ?></span>
            <span class="stat-lbl">kcal total cumulé</span>
        </div>
        <div class="stat-box">
            <span class="stat-num"><?= $totalRecettes > 0 ? number_format($totalCalories / $totalRecettes, 0, ',', ' ') : 0 ?></span>
            <span class="stat-lbl">kcal moy. / recette</span>
        </div>
        <div class="stat-box">
            <span class="stat-num"><?= number_format($maxCal, 0, ',', ' ') ?></span>
            <span class="stat-lbl">kcal max (1 recette)</span>
        </div>
    </div>

    <!-- ── Tableau des recettes ───────────────────────────────────────── -->
    <?php if (empty($recettes)): ?>
        <p style="text-align:center;color:#999;padding:40px 0;">Aucune recette à exporter.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th style="width:30px;">#</th>
                <th style="width:200px;">Nom de la recette</th>
                <th style="width:80px;">Difficulté</th>
                <th style="width:70px;">Prép.</th>
                <th style="width:70px;">Cuisson</th>
                <th style="width:60px;">Pers.</th>
                <th style="width:60px;">Repas</th>
                <th style="width:110px;">🔥 Calories totales</th>
                <th>Étapes (aperçu)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recettes as $i => $rec): ?>
            <tr>
                <!-- Numéro de ligne -->
                <td style="color:#aaa;font-size:9pt;"><?= $i + 1 ?></td>

                <!-- Nom de la recette -->
                <td style="font-weight:600;"><?= htmlspecialchars($rec['nom_recette']) ?></td>

                <!-- Badge de difficulté -->
                <td>
                    <?php
                    $badgeClass = match($rec['difficulte'] ?? 'Facile') {
                        'Facile'    => 'badge-facile',
                        'Moyen'     => 'badge-moyen',
                        'Difficile' => 'badge-difficile',
                        default     => 'badge-facile',
                    };
                    ?>
                    <span class="badge <?= $badgeClass ?>">
                        <?= htmlspecialchars($rec['difficulte'] ?? 'Facile') ?>
                    </span>
                </td>

                <!-- Temps de préparation -->
                <td>
                    <?= !empty($rec['temps_prep'])
                        ? htmlspecialchars($rec['temps_prep']) . ' min'
                        : '<span style="color:#ccc">—</span>' ?>
                </td>

                <!-- Temps de cuisson -->
                <td>
                    <?= !empty($rec['temps_cuisson'])
                        ? htmlspecialchars($rec['temps_cuisson']) . ' min'
                        : '<span style="color:#ccc">—</span>' ?>
                </td>

                <!-- Nombre de personnes -->
                <td style="text-align:center;"><?= htmlspecialchars($rec['nb_personnes']) ?></td>

                <!-- Nombre de repas liés -->
                <td style="text-align:center;font-weight:600;"><?= (int)$rec['nb_repas'] ?></td>

                <!-- Total calories (mis en valeur) -->
                <td style="text-align:right;">
                    <?php if (($rec['total_calories'] ?? 0) > 0): ?>
                        <span class="cal-value">
                            <?= number_format($rec['total_calories'], 0, ',', ' ') ?> kcal
                        </span>
                    <?php else: ?>
                        <span class="cal-zero">—</span>
                    <?php endif; ?>
                </td>

                <!-- Aperçu des étapes (tronqué à 80 caractères) -->
                <td style="color:#555;font-size:9pt;">
                    <?php if (!empty($rec['etapes'])): ?>
                        <?= htmlspecialchars(mb_substr($rec['etapes'], 0, 80)) ?>
                        <?= mb_strlen($rec['etapes']) > 80 ? '…' : '' ?>
                    <?php else: ?>
                        <span style="color:#ccc;font-style:italic;">Aucune étape</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- ── Pied de page ───────────────────────────────────────────────── -->
    <div class="pdf-footer">
        <span>SmartMeal Planner — Export automatique</span>
        <span>Généré le <?= date('d/m/Y à H:i:s') ?></span>
        <span><?= $totalRecettes ?> recette<?= $totalRecettes > 1 ? 's' : '' ?> exportée<?= $totalRecettes > 1 ? 's' : '' ?></span>
    </div>

    <!-- ── Déclenchement automatique de l'impression ─────────────────── -->
    <script>
        // Ouvrir automatiquement la boîte de dialogue d'impression/PDF
        // après un court délai pour laisser le temps aux images de charger
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 600);
        });
    </script>

</body>
</html>
