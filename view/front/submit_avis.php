<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$id_produit  = (int)   ($_POST['id_produit']  ?? 0);
$note        = (int)   ($_POST['note']         ?? 0);
$commentaire = trim(   $_POST['commentaire']   ?? '');
$userId      = (int)   ($_SESSION['user_id']   ?? 0);

if (!$id_produit || $note < 1 || $note > 5) {
    echo json_encode(['ok' => false, 'error' => 'Invalid data']); exit;
}

try {
    $pdo = config::getConnexion();

    // Create table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS avis (
        id_avis INT AUTO_INCREMENT PRIMARY KEY,
        id_produit INT NOT NULL,
        id_user INT,
        note TINYINT NOT NULL,
        commentaire TEXT,
        date_avis DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX(id_produit)
    )");

    $stmt = $pdo->prepare("
        INSERT INTO avis (id_produit, id_user, note, commentaire)
        VALUES (:pid, :uid, :note, :comment)
    ");
    $stmt->execute([
        ':pid'     => $id_produit,
        ':uid'     => $userId ?: null,
        ':note'    => $note,
        ':comment' => $commentaire,
    ]);

    // Return updated avg
    $avg = $pdo->prepare("SELECT AVG(note), COUNT(*) FROM avis WHERE id_produit = :pid");
    $avg->execute([':pid' => $id_produit]);
    [$avgNote, $count] = $avg->fetch(PDO::FETCH_NUM);

    echo json_encode(['ok' => true, 'avg_note' => round((float)$avgNote, 1), 'count' => (int)$count]);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
