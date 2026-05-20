<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

$id    = (int) ($_GET['id_event'] ?? 0);
$page  = max(1, (int) ($_GET['page']  ?? 1));
$limit = min(20, max(1, (int) ($_GET['limit'] ?? 6)));

if (!$id) { echo json_encode(['comments' => [], 'total' => 0, 'page' => 1, 'pages' => 1]); exit; }

try {
    $pdo = config::getConnexion();

    // Create table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS commentaire (
        id_commentaire INT AUTO_INCREMENT PRIMARY KEY,
        id_event INT, id_produit INT, id_user INT,
        auteur VARCHAR(100), contenu TEXT NOT NULL,
        date_commentaire DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX(id_event)
    )");

    // Total count
    $cntStmt = $pdo->prepare("SELECT COUNT(*) FROM commentaire WHERE id_event = :id");
    $cntStmt->execute([':id' => $id]);
    $total  = (int) $cntStmt->fetchColumn();
    $pages  = max(1, (int) ceil($total / $limit));
    $offset = ($page - 1) * $limit;

    $stmt = $pdo->prepare("
        SELECT id_commentaire AS id,
               COALESCE(auteur, 'Anonymous') AS auteur,
               contenu,
               id_user,
               date_commentaire AS created_at
        FROM commentaire
        WHERE id_event = :id
        ORDER BY date_commentaire DESC
        LIMIT :lim OFFSET :off
    ");
    $stmt->bindValue(':id',  $id,     PDO::PARAM_INT);
    $stmt->bindValue(':lim', $limit,  PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'comments' => $rows,
        'total'    => $total,
        'page'     => $page,
        'pages'    => $pages,
    ]);
} catch (Throwable $e) {
    echo json_encode(['comments' => [], 'total' => 0, 'page' => 1, 'pages' => 1]);
}
