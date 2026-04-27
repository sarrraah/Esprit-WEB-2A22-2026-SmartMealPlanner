<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../model/Plan.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'message' => 'POST required']); exit;
}

$mealId   = (int) ($_POST['meal_id']   ?? 0);
$mealType = trim($_POST['meal_type']   ?? '');
$mealDate = trim($_POST['meal_date']   ?? date('Y-m-d'));

$plan = Plan::first();
if (!$plan || $mealId <= 0 || $mealType === '') {
    echo json_encode(['ok' => false, 'message' => 'Invalid data.']); exit;
}

try {
    $pdo = Database::pdo();
    $pdo->exec('CREATE TABLE IF NOT EXISTS plan_meals (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        plan_id INT UNSIGNED NOT NULL,
        meal_date DATE NOT NULL,
        meal_type VARCHAR(20) NOT NULL,
        meal_id INT UNSIGNED NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uq_plan_date_type (plan_id, meal_date, meal_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    $stmt = $pdo->prepare('
        INSERT INTO plan_meals (plan_id, meal_date, meal_type, meal_id)
        VALUES (:plan_id, :meal_date, :meal_type, :meal_id)
        ON DUPLICATE KEY UPDATE meal_id = :meal_id2
    ');
    $stmt->execute([
        ':plan_id'   => $plan->id,
        ':meal_date' => $mealDate,
        ':meal_type' => $mealType,
        ':meal_id'   => $mealId,
        ':meal_id2'  => $mealId,
    ]);

    echo json_encode(['ok' => true, 'message' => ucfirst($mealType) . ' meal saved!']);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}
