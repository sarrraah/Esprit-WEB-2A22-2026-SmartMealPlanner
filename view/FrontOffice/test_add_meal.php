<?php
// Temporary test script - DELETE after testing
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../model/Plan.php';
require_once __DIR__ . '/../../model/Meal.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== Test Add Meal ===\n\n";

// Simulate adding Continental Breakfast Plate (ID 7, breakfast)
$testMealId = 7;
$testMealType = 'breakfast';
$testDate = date('Y-m-d');

echo "Testing with:\n";
echo "  Meal ID: $testMealId\n";
echo "  Meal Type: $testMealType\n";
echo "  Date: $testDate\n\n";

try {
    $pdo = Database::pdo();
    $plan = Plan::first();
    
    if (!$plan) {
        echo "✗ No plan found\n";
        exit;
    }
    
    echo "Plan ID: {$plan->id}\n\n";
    
    // Check before
    echo "BEFORE:\n";
    $stmt = $pdo->prepare('SELECT * FROM plan_meals WHERE plan_id=:pid AND meal_date=:dt AND meal_type=:mt');
    $stmt->execute([':pid' => $plan->id, ':dt' => $testDate, ':mt' => $testMealType]);
    $before = $stmt->fetch();
    if ($before) {
        echo "  Existing meal ID: {$before['meal_id']}\n";
    } else {
        echo "  No existing meal\n";
    }
    
    // Insert/Update
    echo "\nINSERTING/UPDATING...\n";
    $stmt = $pdo->prepare('
        INSERT INTO plan_meals (plan_id, meal_date, meal_type, meal_id)
        VALUES (:plan_id, :meal_date, :meal_type, :meal_id)
        ON DUPLICATE KEY UPDATE meal_id = :meal_id2
    ');
    $result = $stmt->execute([
        ':plan_id'   => $plan->id,
        ':meal_date' => $testDate,
        ':meal_type' => $testMealType,
        ':meal_id'   => $testMealId,
        ':meal_id2'  => $testMealId,
    ]);
    
    echo "  Result: " . ($result ? "SUCCESS" : "FAILED") . "\n";
    echo "  Rows affected: " . $stmt->rowCount() . "\n";
    
    // Check after
    echo "\nAFTER:\n";
    $stmt = $pdo->prepare('SELECT * FROM plan_meals WHERE plan_id=:pid AND meal_date=:dt AND meal_type=:mt');
    $stmt->execute([':pid' => $plan->id, ':dt' => $testDate, ':mt' => $testMealType]);
    $after = $stmt->fetch();
    if ($after) {
        echo "  ✓ Meal ID: {$after['meal_id']}\n";
        echo "  Record ID: {$after['id']}\n";
    } else {
        echo "  ✗ No record found!\n";
    }
    
    // Show all records
    echo "\nALL RECORDS:\n";
    $all = $pdo->query('SELECT * FROM plan_meals')->fetchAll();
    if (empty($all)) {
        echo "  (empty)\n";
    } else {
        foreach ($all as $rec) {
            echo "  - ID:{$rec['id']}, Plan:{$rec['plan_id']}, Date:{$rec['meal_date']}, Type:{$rec['meal_type']}, Meal:{$rec['meal_id']}\n";
        }
    }
    
} catch (Throwable $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "  " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== Test Complete ===\n";
