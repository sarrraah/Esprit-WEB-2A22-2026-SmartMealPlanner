<?php
// Test plans endpoint
require_once __DIR__ . '/../../model/Plan.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== Test Plans List ===\n\n";

try {
    $plans = Plan::all();
    echo "Found " . count($plans) . " plan(s)\n\n";
    
    foreach ($plans as $plan) {
        echo "Plan #{$plan->id}:\n";
        echo "  Name: {$plan->nom}\n";
        echo "  Duration: {$plan->duree} days\n";
        echo "  Start: {$plan->dateDebut}\n";
        echo "  End: {$plan->dateFin}\n";
        echo "  Objective: {$plan->objectif}\n";
        echo "  Description: {$plan->description}\n\n";
    }
    
    echo "\nJSON output:\n";
    echo json_encode(array_map(fn(Plan $p) => $p->toArray() + ['mealTypeLabel' => $p->mealTypeLabel()], $plans), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
