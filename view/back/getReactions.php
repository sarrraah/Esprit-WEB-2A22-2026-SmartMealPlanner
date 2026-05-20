<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$id        = (int) ($_GET['id_event']  ?? 0);
$sessionId = trim( $_GET['session_id'] ?? session_id());

if (!$id) { echo json_encode(['counts' => [], 'my_reaction' => null]); exit; }

try {
    $pdo = config::getConnexion();

    if (!$pdo->query("SHOW TABLES LIKE 'reaction'")->fetchColumn()) {
        echo json_encode(['counts' => [], 'my_reaction' => null]); exit;
    }

    // Counts per type
    $stmt = $pdo->prepare("SELECT type, COUNT(*) AS cnt FROM reaction WHERE id_event = :id GROUP BY type");
    $stmt->execute([':id' => $id]);
    $counts = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $counts[$r['type']] = (int) $r['cnt'];
    }

    // This session's reaction
    $myStmt = $pdo->prepare("SELECT type FROM reaction WHERE id_event = :id AND session_id = :sid LIMIT 1");
    $myStmt->execute([':id' => $id, ':sid' => $sessionId]);
    $myReaction = $myStmt->fetchColumn() ?: null;

    echo json_encode(['counts' => $counts, 'my_reaction' => $myReaction]);

} catch (Throwable $e) {
    echo json_encode(['counts' => [], 'my_reaction' => null]);
}
