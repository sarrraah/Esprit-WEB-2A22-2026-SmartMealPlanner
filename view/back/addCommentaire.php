<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Support both JSON body (detailevent.php) and POST form (produits.php)
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) $data = $_POST;

$id_event = (int)  ($data['id_event'] ?? 0);
$contenu  = trim(  $data['contenu']   ?? '');
$auteur   = trim(  $data['auteur']    ?? '');
$userId   = (int)  ($_SESSION['user_id'] ?? 0);

if (!$id_event || $contenu === '') {
    echo json_encode(['ok' => false, 'success' => false, 'error' => 'Missing fields']); exit;
}
if (mb_strlen($contenu) > 500) {
    echo json_encode(['ok' => false, 'success' => false, 'error' => 'Comment too long']); exit;
}

try {
    $pdo = config::getConnexion();

    // Create table if not exists — supports both use cases
    $pdo->exec("CREATE TABLE IF NOT EXISTS commentaire (
        id_commentaire INT AUTO_INCREMENT PRIMARY KEY,
        id_event INT,
        id_produit INT,
        id_user INT,
        auteur VARCHAR(100),
        contenu TEXT NOT NULL,
        date_commentaire DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX(id_event),
        INDEX(id_produit)
    )");

    $stmt = $pdo->prepare("
        INSERT INTO commentaire (id_event, id_user, auteur, contenu, date_commentaire)
        VALUES (:id_event, :uid, :auteur, :contenu, NOW())
    ");
    $stmt->execute([
        ':id_event' => $id_event,
        ':uid'      => $userId ?: null,
        ':auteur'   => $auteur ?: ($_SESSION['prenom'] ?? 'Anonymous'),
        ':contenu'  => $contenu,
    ]);
    $newId = (int) $pdo->lastInsertId();

    echo json_encode([
        'ok'      => true,
        'success' => true,
        'comment' => [
            'id'         => $newId,
            'auteur'     => $auteur ?: ($_SESSION['prenom'] ?? 'Anonymous'),
            'contenu'    => $contenu,
            'created_at' => date('Y-m-d H:i:s'),
        ]
    ]);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'success' => false, 'error' => $e->getMessage()]);
}
