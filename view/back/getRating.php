<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';
$id = (int)($_GET['id_event'] ?? 0);
if (!$id) { echo json_encode(['avg' => 0, 'count' => 0]); exit; }
try {
    $pdo = config::getConnexion();
    if (!$pdo->query("SHOW TABLES LIKE 'event_rating'")->fetchColumn()) {
        echo json_encode(['avg' => 0, 'count' => 0]); exit;
    }
    $stmt = $pdo->prepare("SELECT AVG(note) AS avg, COUNT(*) AS cnt FROM event_rating WHERE id_event = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['avg' => round((float)($row['avg'] ?? 0), 1), 'count' => (int)($row['cnt'] ?? 0)]);
} catch (Throwable $e) { echo json_encode(['avg' => 0, 'count' => 0]); }
