<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';
$data   = json_decode(file_get_contents('php://input'), true);
$id     = (int)($data['id_event'] ?? 0);
$places = (int)($data['places']   ?? 1);
if (!$id || $places < 1) { echo json_encode(['success' => false, 'error' => 'Invalid data']); exit; }
try {
    $pdo = config::getConnexion();
    $stmt = $pdo->prepare("SELECT capacite_max FROM evenement WHERE id_event = :id");
    $stmt->execute([':id' => $id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$event) { echo json_encode(['success' => false, 'error' => 'Event not found']); exit; }

    // Count existing participations
    $taken = 0;
    if ($pdo->query("SHOW TABLES LIKE 'participation'")->fetchColumn()) {
        $cnt = $pdo->prepare("SELECT COUNT(*) FROM participation WHERE id_event = :id");
        $cnt->execute([':id' => $id]);
        $taken = (int)$cnt->fetchColumn();
    }
    $available = (int)$event['capacite_max'] - $taken;
    if ($available < $places) {
        echo json_encode(['success' => false, 'error' => "Only $available seat(s) available"]);
    } else {
        echo json_encode(['success' => true, 'available' => $available]);
    }
} catch (Throwable $e) { echo json_encode(['success' => false, 'error' => $e->getMessage()]); }
