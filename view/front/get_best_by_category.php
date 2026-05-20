<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

try {
    $pdo = config::getConnexion();

    // Build absolute image URL base
    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $docRoot  = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
    $projRoot = str_replace('\\', '/', realpath(__DIR__ . '/../../'));
    $basePath = str_replace($docRoot, '', $projRoot);
    $imgBase  = $scheme . '://' . $_SERVER['HTTP_HOST'] . $basePath . '/view/assets/img/products/';

    // Get best product per category (highest avg rating, then newest)
    $stmt = $pdo->query("
        SELECT p.id, p.nom, p.description, p.prix, p.image,
               p.quantiteStock, p.dateExpiration,
               COALESCE(c.nom, 'Autre') AS categorie_nom,
               c.id_categorie,
               COALESCE(AVG(a.note), 0) AS avg_note,
               COUNT(a.id_avis) AS nb_avis
        FROM produit p
        LEFT JOIN categorieproduit c ON c.id_categorie = p.id_categorie
        LEFT JOIN avis a ON a.id_produit = p.id
        WHERE p.quantiteStock > 0
        GROUP BY p.id
        ORDER BY avg_note DESC, p.id DESC
    ");

    $all = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pick best per category
    $seen   = [];
    $result = [];
    foreach ($all as $p) {
        $cat = $p['categorie_nom'];
        if (isset($seen[$cat])) continue;
        $seen[$cat] = true;

        $img = $p['image'] ?? '';
        if (empty($img))                         $imgSrc = '';
        elseif (str_starts_with($img, 'http'))   $imgSrc = $img;
        else                                     $imgSrc = $imgBase . $img;

        $result[] = [
            'id'        => (int) $p['id'],
            'nom'       => $p['nom'],
            'prix'      => (float) $p['prix'],
            'image'     => $imgSrc,
            'categorie' => $cat,
            'avg_note'  => round((float) $p['avg_note'], 1),
            'nb_avis'   => (int) $p['nb_avis'],
        ];

        if (count($result) >= 6) break;
    }

    echo json_encode($result);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
