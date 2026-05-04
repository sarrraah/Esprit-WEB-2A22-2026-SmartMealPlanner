<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../model/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$data       = json_decode(file_get_contents('php://input'), true);
$id_event   = (int)($data['id_event']   ?? 0);
$type       = (string)($data['type']      ?? '');
$session_id = trim((string)($data['session_id'] ?? ''));

$types = ['❤️','😂','😮','😢','👏','🔥'];
if ($id_event <= 0 || $session_id === '' || !in_array($type, $types, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid payload'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = Database::getConnection();

    // Auto-create table if it doesn't exist yet
    $pdo->exec("CREATE TABLE IF NOT EXISTS reaction (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        id_event   INT NOT NULL,
        type       VARCHAR(20) NOT NULL,
        session_id VARCHAR(100) NOT NULL,
        UNIQUE KEY unique_reaction (id_event, session_id, type(20)),
        FOREIGN KEY (id_event) REFERENCES evenement(id_event) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Toggle: insert if not exists, delete if already exists
    $ins = $pdo->prepare(
        "INSERT IGNORE INTO reaction (id_event, type, session_id) VALUES (?, ?, ?)"
    );
    $ins->execute([$id_event, $type, $session_id]);
    if ($ins->rowCount() === 0) {
        $del = $pdo->prepare(
            "DELETE FROM reaction WHERE id_event = ? AND type = ? AND session_id = ?"
        );
        $del->execute([$id_event, $type, $session_id]);
    }

    $stmt = $pdo->prepare(
        "SELECT type, COUNT(*) AS c FROM reaction WHERE id_event = ? GROUP BY type"
    );
    $stmt->execute([$id_event]);
    $counts = array_fill_keys($types, 0);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $t = (string)$r['type'];
        if (isset($counts[$t])) $counts[$t] = (int)$r['c'];
    }

    echo json_encode(['success' => true, 'counts' => $counts], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
