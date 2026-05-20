<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

$id = (int) ($_GET['id_produit'] ?? 0);
if (!$id) { echo json_encode([]); exit; }

try {
    $pdo  = config::getConnexion();

    // Check if avis table exists
    if (!$pdo->query("SHOW TABLES LIKE 'avis'")->fetchColumn()) {
        echo json_encode([]); exit;
    }

    $stmt = $pdo->prepare("
        SELECT a.id_avis, a.note, a.commentaire, a.date_avis,
               COALESCE(u.prenom, '') AS prenom, COALESCE(u.nom, '') AS nom
        FROM avis a
        LEFT JOIN user u ON u.id = a.id_user
        WHERE a.id_produit = :id
        ORDER BY a.date_avis DESC
        LIMIT 20
    ");
    $stmt->execute([':id' => $id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Throwable $e) {
    echo json_encode([]);
}
