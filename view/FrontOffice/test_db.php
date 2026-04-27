<?php
// Temporary debug script - DELETE after testing
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../model/Plan.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== Database Connection Test ===\n\n";

try {
    $pdo = Database::pdo();
    echo "✓ Database connected successfully\n\n";
    
    // Check if plan_meals table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'plan_meals'");
    if ($stmt->rowCount() > 0) {
        echo "✓ plan_meals table exists\n\n";
        
        // Show table structure
        echo "Table structure:\n";
        $cols = $pdo->query("DESCRIBE plan_meals")->fetchAll();
        foreach ($cols as $col) {
            echo "  - {$col['Field']} ({$col['Type']})\n";
        }
        echo "\n";
        
        // Show all records
        echo "Current records in plan_meals:\n";
        $records = $pdo->query("SELECT * FROM plan_meals ORDER BY meal_date DESC, meal_type")->fetchAll();
        if (empty($records)) {
            echo "  (no records)\n";
        } else {
            foreach ($records as $rec) {
                echo "  - Plan #{$rec['plan_id']}, Date: {$rec['meal_date']}, Type: {$rec['meal_type']}, Meal ID: {$rec['meal_id']}\n";
            }
        }
        echo "\n";
    } else {
        echo "✗ plan_meals table does NOT exist\n";
        echo "  Creating table...\n";
        $pdo->exec('CREATE TABLE IF NOT EXISTS plan_meals (
            id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
            plan_id   INT UNSIGNED NOT NULL,
            meal_date DATE         NOT NULL,
            meal_type VARCHAR(20)  NOT NULL,
            meal_id   INT UNSIGNED NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uq_plan_date_type (plan_id, meal_date, meal_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        echo "  ✓ Table created\n\n";
    }
    
    // Check active plan
    $plan = Plan::first();
    if ($plan) {
        echo "✓ Active plan found:\n";
        echo "  - ID: {$plan->id}\n";
        echo "  - Name: {$plan->nom}\n";
        echo "  - Start: {$plan->dateDebut}\n";
        echo "  - End: {$plan->dateFin}\n";
        echo "  - Today: " . date('Y-m-d') . "\n";
    } else {
        echo "✗ No active plan found\n";
    }
    
} catch (Throwable $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== Test Complete ===\n";
