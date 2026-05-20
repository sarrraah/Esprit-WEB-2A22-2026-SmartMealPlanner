<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$data      = json_decode(file_get_contents('php://input'), true);
$id_event  = (int)  ($data['id_event']  ?? 0);
$type      = trim(  $data['type']       ?? $data['emoji'] ?? '');
$sessionId = trim(  $data['session_id'] ?? session_id());

if (!$id_event || !$type) {
    echo json_encode(['success' => false, 'error' => 'Missing fields']); exit;
}

try {
    $pdo = config::getConnexion();

    // Check existing reaction for this session+event
    $existing = $pdo->prepare("SELECT id, type FROM reaction WHERE id_event = :id AND session_id = :sid");
    $existing->execute([':id' => $id_event, ':sid' => $sessionId]);
    $prev = $existing->fetch(PDO::FETCH_ASSOC);

    if ($prev) {
        if ($prev['type'] === $type) {
            // Same type → toggle OFF
            $pdo->prepare("DELETE FROM reaction WHERE id = :id")->execute([':id' => $prev['id']]);
            $myReaction = null;
        } else {
            // Different type → switch
            $pdo->prepare("UPDATE reaction SET type = :type WHERE id = :id")
                ->execute([':type' => $type, ':id' => $prev['id']]);
            $myReaction = $type;
        }
    } else {
        // New reaction
        $pdo->prepare("INSERT INTO reaction (id_event, session_id, type) VALUES (:id, :sid, :type)")
            ->execute([':id' => $id_event, ':sid' => $sessionId, ':type' => $type]);
        $myReaction = $type;
    }

    // Return updated counts keyed by type
    $stmt = $pdo->prepare("SELECT type, COUNT(*) AS cnt FROM reaction WHERE id_event = :id GROUP BY type");
    $stmt->execute([':id' => $id_event]);
    $counts = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $counts[$r['type']] = (int) $r['cnt'];
    }

    echo json_encode(['success' => true, 'counts' => $counts, 'my_reaction' => $myReaction]);

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
