<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

$userId = (int) ($_GET['user_id'] ?? 0);

try {
    $pdo = config::getConnexion();

    // Build absolute image URL base
    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $docRoot  = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
    $projRoot = str_replace('\\', '/', realpath(__DIR__ . '/../../'));
    $basePath = str_replace($docRoot, '', $projRoot);
    $imgBase  = $scheme . '://' . $_SERVER['HTTP_HOST'] . $basePath . '/view/assets/img/products/';

    // Get top 3 products by rating/stock — personalized if user exists
    $stmt = $pdo->query("
        SELECT p.id, p.nom, p.description, p.prix, p.image,
               p.quantiteStock, p.dateExpiration,
               COALESCE(c.nom, '') AS categorie_nom,
               COALESCE(AVG(a.note), 0) AS avg_note,
               COUNT(a.id_avis) AS nb_avis
        FROM produit p
        LEFT JOIN categorieproduit c ON c.id_categorie = p.id_categorie
        LEFT JOIN avis a ON a.id_produit = p.id
        WHERE p.quantiteStock > 0
        GROUP BY p.id
        ORDER BY avg_note DESC, nb_avis DESC, p.id DESC
        LIMIT 3
    ");

    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $medals  = ['🥇', '🥈', '🥉'];
    $reasons = [
        'Top rated by our customers.',
        'Highly popular this week.',
        'Great nutritional value.',
    ];

    $result = [];
    foreach ($produits as $i => $p) {
        $img = $p['image'] ?? '';
        if (empty($img))                         $imgSrc = '';
        elseif (str_starts_with($img, 'http'))   $imgSrc = $img;
        else                                     $imgSrc = $imgBase . $img;

        $result[] = [
            'id'          => (int) $p['id'],
            'nom'         => $p['nom'],
            'description' => mb_substr($p['description'] ?? '', 0, 100) . '...',
            'prix'        => (float) $p['prix'],
            'image'       => $imgSrc,
            'categorie'   => $p['categorie_nom'],
            'avg_note'    => round((float) $p['avg_note'], 1),
            'nb_avis'     => (int) $p['nb_avis'],
            'medal'       => $medals[$i] ?? '🏅',
            'reason'      => $reasons[$i] ?? 'Recommended for you.',
        ];
    }

    echo json_encode($result);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
