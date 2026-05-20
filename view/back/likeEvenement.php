<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

$data = json_decode(file_get_contents('php://input'), true);
$id     = (int) ($data['id_event'] ?? 0);
$action = trim($data['action'] ?? 'like'); // 'like' or 'unlike'

if (!$id) { echo json_encode(['ok' => false]); exit; }

try {
    $pdo = config::getConnexion();
    if ($action === 'unlike') {
        $pdo->prepare("UPDATE evenement SET likes = GREATEST(0, COALESCE(likes,0) - 1) WHERE id_event = :id")->execute([':id' => $id]);
    } else {
        $pdo->prepare("UPDATE evenement SET likes = COALESCE(likes,0) + 1 WHERE id_event = :id")->execute([':id' => $id]);
    }
    $likes = (int) $pdo->query("SELECT likes FROM evenement WHERE id_event = $id")->fetchColumn();
    echo json_encode(['ok' => true, 'likes' => $likes]);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
