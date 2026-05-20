<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$data = json_decode(file_get_contents('php://input'), true);
$id   = (int)($data['id'] ?? 0);

if (!$id) { echo json_encode(['success' => false, 'error' => 'Missing ID']); exit; }

try {
    $pdo = config::getConnexion();
    $pdo->prepare("DELETE FROM commentaire WHERE id_commentaire = :id")->execute([':id' => $id]);
    echo json_encode(['success' => true, 'ok' => true]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'ok' => false, 'error' => $e->getMessage()]);
}
