<?php
/**
 * Best meal per category endpoint.
 * Returns the top-rated (avg note DESC, nb_avis DESC) available product
 * for each category that has at least one product in stock.
 */
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../config.php';

try {
    $db = config::getConnexion();

    // Detect schema
    $hasIdCat = $db->query("SHOW COLUMNS FROM produit LIKE 'id_categorie'")->fetch() !== false;

    if ($hasIdCat) {
        $sql = "
            SELECT
                c.id_categorie,
                c.nom          AS categorie_nom,
                p.id, p.nom, p.prix, p.image, p.description,
                ROUND(COALESCE(AVG(a.note), 0), 1) AS avg_note,
                COUNT(a.id_avis)                   AS nb_avis
            FROM categorieproduit c
            JOIN produit p ON p.id_categorie = c.id_categorie
                AND p.quantiteStock > 0
            LEFT JOIN avis a ON a.id_produit = p.id
            GROUP BY c.id_categorie, c.nom, p.id, p.nom, p.prix, p.image, p.description
            HAVING p.id = (
                SELECT p2.id
                FROM produit p2
                LEFT JOIN avis a2 ON a2.id_produit = p2.id
                WHERE p2.id_categorie = c.id_categorie
                  AND p2.quantiteStock > 0
                GROUP BY p2.id
                ORDER BY COALESCE(AVG(a2.note), 0) DESC, COUNT(a2.id_avis) DESC, p2.quantiteStock DESC
                LIMIT 1
            )
            ORDER BY c.nom ASC
        ";
    } else {
        $sql = "
            SELECT
                c.id_categorie,
                c.nom          AS categorie_nom,
                p.id, p.nom, p.prix, p.image, p.description,
                ROUND(COALESCE(AVG(a.note), 0), 1) AS avg_note,
                COUNT(a.id_avis)                   AS nb_avis
            FROM categorieproduit c
            JOIN produit p ON CAST(p.categorie AS UNSIGNED) = c.id_categorie
                AND p.quantiteStock > 0
            LEFT JOIN avis a ON a.id_produit = p.id
            GROUP BY c.id_categorie, c.nom, p.id, p.nom, p.prix, p.image, p.description
            HAVING p.id = (
                SELECT p2.id
                FROM produit p2
                LEFT JOIN avis a2 ON a2.id_produit = p2.id
                WHERE CAST(p2.categorie AS UNSIGNED) = c.id_categorie
                  AND p2.quantiteStock > 0
                GROUP BY p2.id
                ORDER BY COALESCE(AVG(a2.note), 0) DESC, COUNT(a2.id_avis) DESC, p2.quantiteStock DESC
                LIMIT 1
            )
            ORDER BY c.nom ASC
        ";
    }

    $rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($rows as $row) {
        $img = $row['image'] ?? '';
        if (empty($img))                       $imgUrl = '';
        elseif (str_starts_with($img, 'http')) $imgUrl = $img;
        else                                   $imgUrl = '../../uploads/' . $img;

        $result[] = [
            'id'           => (int)$row['id'],
            'nom'          => $row['nom'],
            'prix'         => (float)$row['prix'],
            'image'        => $imgUrl,
            'avg_note'     => (float)$row['avg_note'],
            'nb_avis'      => (int)$row['nb_avis'],
            'categorie'    => $row['categorie_nom'],
            'id_categorie' => (int)$row['id_categorie'],
        ];
    }

    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
