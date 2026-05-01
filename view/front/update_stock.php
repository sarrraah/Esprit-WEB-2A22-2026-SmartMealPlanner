<?php
/**
 * AJAX endpoint — decrement stock after order confirmation.
 * Expects POST: items = JSON array of {id, quantite}
 */
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$raw   = file_get_contents('php://input');
$data  = json_decode($raw, true);
$items = $data['items'] ?? [];

if (empty($items) || !is_array($items)) {
    echo json_encode(['success' => false, 'error' => 'No items provided']);
    exit;
}

$db = config::getConnexion();
$errors = [];

foreach ($items as $item) {
    $id  = (int)($item['id']       ?? 0);
    $qty = (int)($item['quantite'] ?? 0);

    if ($id <= 0 || $qty <= 0) continue;

    // Fetch current stock
    $stmt = $db->prepare("SELECT quantiteStock FROM produit WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        $errors[] = "Product $id not found";
        continue;
    }

    $newStock = max(0, (int)$row['quantiteStock'] - $qty);

    $upd = $db->prepare("UPDATE produit SET quantiteStock = :stock WHERE id = :id");
    $upd->execute(['stock' => $newStock, 'id' => $id]);
}

echo json_encode(['success' => true, 'errors' => $errors]);
