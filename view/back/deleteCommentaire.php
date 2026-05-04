<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../model/Database.php';

$data = json_decode(file_get_contents('php://input'), true);

$id       = (int)($data['id']       ?? 0);
$id_event = (int)($data['id_event'] ?? 0);

if (!$id || !$id_event) {
    echo json_encode(['success' => false, 'error' => 'Données manquantes']);
    exit;
}

try {
    $pdo  = Database::getConnection();
    $stmt = $pdo->prepare(
        "DELETE FROM commentaire_event WHERE id = ? AND id_event = ?"
    );
    $stmt->execute([$id, $id_event]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Commentaire introuvable']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
