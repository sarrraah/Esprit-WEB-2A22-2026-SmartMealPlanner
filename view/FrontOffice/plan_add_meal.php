<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../model/Plan.php';
require_once __DIR__ . '/../../model/Meal.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'POST required']);
    exit;
}

$mealId   = (int) ($_POST['meal_id']   ?? 0);
$mealType = trim($_POST['meal_type']   ?? '');
$mealDate = trim($_POST['meal_date']   ?? date('Y-m-d')); // accept chosen date from front office

$plan = Plan::first();
if (!$plan) {
    echo json_encode(['ok' => false, 'message' => 'No active plan found. Please create a plan first.']);
    exit;
}

if ($mealId <= 0) {
    echo json_encode(['ok' => false, 'message' => 'Invalid meal ID: ' . $mealId]);
    exit;
}

if ($mealType === '') {
    echo json_encode(['ok' => false, 'message' => 'Meal type is required']);
    exit;
}

// Verify meal exists
$allMeals = Meal::all();
$mealExists = false;
$selectedMeal = null;
foreach ($allMeals as $meal) {
    if ($meal->id === $mealId) {
        $mealExists = true;
        $selectedMeal = $meal;
        break;
    }
}

if (!$mealExists) {
    echo json_encode(['ok' => false, 'message' => 'Meal not found with ID: ' . $mealId]);
    exit;
}

// Verify date is within the plan's active range
$planStart = $plan->dateDebut ? strtotime($plan->dateDebut) : 0;
$planEnd   = $plan->dateFin   ? strtotime($plan->dateFin)   : PHP_INT_MAX;
$chosenTs  = strtotime($mealDate);
if ($chosenTs < $planStart || $chosenTs > $planEnd) {
    echo json_encode(['ok' => false, 'message' => 'Date (' . $mealDate . ') is outside your plan dates (' . $plan->dateDebut . ' to ' . $plan->dateFin . ').']);
    exit;
}

try {
    $pdo = Database::pdo();

    // Ensure table exists
    $pdo->exec('CREATE TABLE IF NOT EXISTS plan_detail (
        id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
        plan_id   INT UNSIGNED NOT NULL,
        meal_date DATE         NOT NULL,
        meal_type VARCHAR(20)  NOT NULL,
        meal_id   INT UNSIGNED NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uq_plan_date_type (plan_id, meal_date, meal_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    // Check if a meal already exists for this type today
    $checkStmt = $pdo->prepare('SELECT meal_id FROM plan_detail WHERE plan_id=:pid AND meal_date=:dt AND meal_type=:mt');
    $checkStmt->execute([':pid' => $plan->id, ':dt' => $mealDate, ':mt' => $mealType]);
    $existing = $checkStmt->fetch();

    $stmt = $pdo->prepare('
        INSERT INTO plan_detail (plan_id, meal_date, meal_type, meal_id)
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

    $action = $existing ? 'replaced' : 'added';
    echo json_encode([
        'ok' => true, 
        'message' => ucfirst($mealType) . ' meal ' . $action . ' successfully!',
        'meal_name' => $selectedMeal->name,
        'action' => $action
    ]);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}

