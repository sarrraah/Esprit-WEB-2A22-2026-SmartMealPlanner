<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$data = json_decode(file_get_contents('php://input'), true);
$id   = (int)($data['id_event'] ?? 0);
$note = (int)($data['note']     ?? 0);
if (!$id || $note < 1 || $note > 5) { echo json_encode(['ok' => false]); exit; }
try {
    $pdo = config::getConnexion();
    $pdo->exec("CREATE TABLE IF NOT EXISTS event_rating (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_event INT NOT NULL,
        session_id VARCHAR(64),
        note TINYINT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_session_event (id_event, session_id)
    )");
    $sid = $data['session_id'] ?? session_id();
    $pdo->prepare("INSERT INTO event_rating (id_event, session_id, note) VALUES (:id, :sid, :note)
                   ON DUPLICATE KEY UPDATE note = :note2")
        ->execute([':id' => $id, ':sid' => $sid, ':note' => $note, ':note2' => $note]);
    $row = $pdo->query("SELECT AVG(note) AS avg, COUNT(*) AS cnt FROM event_rating WHERE id_event = $id")->fetch();
    echo json_encode(['ok' => true, 'avg' => round((float)$row['avg'], 1), 'count' => (int)$row['cnt']]);
} catch (Throwable $e) { echo json_encode(['ok' => false, 'error' => $e->getMessage()]); }
