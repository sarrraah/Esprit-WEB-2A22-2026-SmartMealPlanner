<?php
/**
 * SQL (run once):
 * ALTER TABLE evenement ADD COLUMN IF NOT EXISTS likes INT DEFAULT 0;
 */
header('Content-Type: application/json');
require_once '../../model/Database.php'; // use existing DB connection
$data = json_decode(file_get_contents('php://input'), true);
$id_event = (int)($data['id_event'] ?? 0);
$action   = $data['action'] ?? 'like';
if (!$id_event) { echo json_encode(['success'=>false]); exit; }
try {
    $pdo = Database::getConnection();
    if ($action === 'like') {
        $stmt = $pdo->prepare("UPDATE evenement SET likes = likes + 1 WHERE id_event = ?");
    } else {
        $stmt = $pdo->prepare("UPDATE evenement SET likes = GREATEST(likes - 1, 0) WHERE id_event = ?");
    }
    $stmt->execute([$id_event]);
    $stmt2 = $pdo->prepare("SELECT likes FROM evenement WHERE id_event = ?");
    $stmt2->execute([$id_event]);
    $row = $stmt2->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'likes' => (int)$row['likes']]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

