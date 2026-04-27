<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'errors' => ['Use POST.'], 'message' => '']);
    exit;
}
require_once __DIR__ . '/../../controller/PlanAdminController.php';
echo json_encode(PlanAdminController::handlePost($_POST), JSON_UNESCAPED_UNICODE);

