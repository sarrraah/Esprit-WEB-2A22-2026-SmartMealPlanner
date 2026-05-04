<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../model/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$data     = json_decode(file_get_contents('php://input'), true);
$id_event = (int)($data['id_event'] ?? 0);
$places   = (int)($data['places']   ?? 1);

if (!$id_event || $places < 1) {
    echo json_encode(['success' => false, 'error' => 'Données invalides']);
    exit;
}

try {
    $pdo = Database::getConnection();

    // Check availability
    $stmt = $pdo->prepare(
        "SELECT capacite_max,
                (SELECT COUNT(*) FROM participation WHERE id_event = e.id_event) as reserved
         FROM evenement e WHERE id_event = ?"
    );
    $stmt->execute([$id_event]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['success' => false, 'error' => 'Événement introuvable']);
        exit;
    }

    $available = (int)$row['capacite_max'] - (int)$row['reserved'];

    if ($available < $places) {
        echo json_encode([
            'success'   => false,
            'error'     => "Seulement $available place(s) disponible(s).",
            'available' => $available
        ]);
        exit;
    }

    echo json_encode([
        'success'   => true,
        'available' => $available,
        'remaining' => $available - $places,
    ]);

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
