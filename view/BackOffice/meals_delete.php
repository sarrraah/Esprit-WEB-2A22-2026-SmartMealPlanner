<?php

declare(strict_types=1);

/**
 * API: delete a meal by id (POST id or selected_id).
 */
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'errors' => ['Use POST.'], 'message' => ''], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/../../controller/MealAdminController.php';

$id = $_POST['id'] ?? $_POST['selected_id'] ?? '';
$result = MealAdminController::handlePost([
    'action' => 'delete',
    'selected_id' => $id,
], []);

echo json_encode($result, JSON_UNESCAPED_UNICODE);
