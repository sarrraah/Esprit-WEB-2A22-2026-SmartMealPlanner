<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';
$data = json_decode(file_get_contents('php://input'), true);
$code = trim(strtoupper($data['code'] ?? ''));
$eventId = (int)($data['id_event'] ?? 0);
if (!$code) { echo json_encode(['valid' => false, 'error' => 'Code required']); exit; }
try {
    $pdo = config::getConnexion();
    // Check if promo_code table exists
    if (!$pdo->query("SHOW TABLES LIKE 'promo_code'")->fetchColumn()) {
        echo json_encode(['valid' => false, 'error' => 'Invalid promo code']); exit;
    }
    $stmt = $pdo->prepare("SELECT * FROM promo_code WHERE code = :code AND (id_event = :eid OR id_event IS NULL) AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1");
    $stmt->execute([':code' => $code, ':eid' => $eventId]);
    $promo = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($promo) {
        echo json_encode(['valid' => true, 'discount' => (float)($promo['discount'] ?? 0), 'type' => $promo['type'] ?? 'fixed']);
    } else {
        echo json_encode(['valid' => false, 'error' => 'Invalid or expired promo code']);
    }
} catch (Throwable $e) { echo json_encode(['valid' => false, 'error' => 'Invalid promo code']); }
