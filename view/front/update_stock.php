<?php
/**
 * AJAX endpoint — validate stock then decrement after order confirmation.
 * Expects POST: items = JSON array of {id, quantite}
 *
 * Response on success:  { "success": true, "errors": [] }
 * Response on stock err: { "success": false, "stockErrors": [{ "id", "nom", "stockDisponible", "quantiteDemandee" }] }
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
$stockErrors = [];
$errors      = [];

// ── Phase 1 : validate all items before touching any stock ────────────────
foreach ($items as $item) {
    $id  = (int)($item['id']       ?? 0);
    $qty = (int)($item['quantite'] ?? 0);

    if ($id <= 0 || $qty <= 0) continue;

    $stmt = $db->prepare("SELECT id, nom, quantiteStock FROM produit WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        $errors[] = "Product $id not found";
        continue;
    }

    $stockDisponible = (int)$row['quantiteStock'];

    // Bug condition: qty > stockDisponible
    if ($qty > $stockDisponible) {
        $stockErrors[] = [
            'id'               => $id,
            'nom'              => $row['nom'],
            'stockDisponible'  => $stockDisponible,
            'quantiteDemandee' => $qty,
        ];
    }
}

// If any item exceeds available stock, reject the entire order without modifying anything
if (!empty($stockErrors)) {
    echo json_encode([
        'success'     => false,
        'stockErrors' => $stockErrors,
    ]);
    exit;
}

// ── Phase 2 : all items are valid — decrement stock ───────────────────────
foreach ($items as $item) {
    $id  = (int)($item['id']       ?? 0);
    $qty = (int)($item['quantite'] ?? 0);

    if ($id <= 0 || $qty <= 0) continue;

    // Re-fetch current stock (guard against concurrent requests)
    $stmt = $db->prepare("SELECT quantiteStock FROM produit WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        $errors[] = "Product $id not found";
        continue;
    }

    $newStock = (int)$row['quantiteStock'] - $qty;
    if ($newStock < 0) $newStock = 0; // safety net for race conditions

    $upd = $db->prepare("UPDATE produit SET quantiteStock = :stock WHERE id = :id");
    $upd->execute(['stock' => $newStock, 'id' => $id]);
}

echo json_encode(['success' => true, 'errors' => $errors]);
