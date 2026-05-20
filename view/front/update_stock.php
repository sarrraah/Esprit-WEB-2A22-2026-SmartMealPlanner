<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

$data  = json_decode(file_get_contents('php://input'), true);
$items = $data['items'] ?? [];

if (empty($items)) {
    echo json_encode(['ok' => false, 'error' => 'No items']); exit;
}

try {
    $pdo = config::getConnexion();
    $pdo->beginTransaction();

    foreach ($items as $item) {
        $id  = (int)   ($item['id']  ?? 0);
        $qty = (int)   ($item['qty'] ?? 0);
        if (!$id || $qty <= 0) continue;

        // Check stock
        $check = $pdo->prepare("SELECT quantiteStock FROM produit WHERE id = :id FOR UPDATE");
        $check->execute([':id' => $id]);
        $stock = (int) $check->fetchColumn();

        if ($stock < $qty) {
            $pdo->rollBack();
            echo json_encode(['ok' => false, 'error' => "Insufficient stock for product #$id"]);
            exit;
        }

        $pdo->prepare("UPDATE produit SET quantiteStock = quantiteStock - :qty WHERE id = :id")
            ->execute([':qty' => $qty, ':id' => $id]);
    }

    $pdo->commit();
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
