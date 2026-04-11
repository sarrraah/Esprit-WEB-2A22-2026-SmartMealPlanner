<?php

/**
 * Optional JSON detail endpoint for a single meal (e.g. future AJAX).
 * Usage: meal_detail.php?id=1
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../controller/MealController.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$meal = $id > 0 ? MealController::getMeal($id) : null;

if ($meal === null) {
    http_response_code(404);
    echo json_encode(['error' => 'Meal not found']);
    exit;
}

echo json_encode([
    'id' => $meal->id,
    'name' => $meal->name,
    'calories' => $meal->calories,
    'description' => $meal->description,
    'image' => '../' . $meal->image,
    'recipeUrl' => $meal->recipeUrl,
    'mealType' => $meal->mealType,
    'mealTypeLabel' => $meal->mealTypeLabel(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
