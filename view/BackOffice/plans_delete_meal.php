<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'message' => 'POST required']); exit;
}

$pmId = (int) ($_POST['pm_id'] ?? 0);
if ($pmId <= 0) {
    echo json_encode(['ok' => false, 'message' => 'Invalid ID.']); exit;
}

try {
    $pdo = Database::pdo();
    $stmt = $pdo->prepare('DELETE FROM plan_detail WHERE id = :id');
    $stmt->execute([':id' => $pmId]);
    echo json_encode(['ok' => true, 'message' => 'Meal removed.']);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}

