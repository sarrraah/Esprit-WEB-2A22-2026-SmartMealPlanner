<?php

declare(strict_types=1);

/**
 * API: JSON list of all meals (for back office table).
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../model/Meal.php';

$meals = Meal::all();

$out = array_map(static function (Meal $m): array {
    return [
        'id' => $m->id,
        'name' => $m->name,
        'calories' => $m->calories,
        'description' => $m->description,
        'image' => $m->image,
        'recipeUrl' => $m->recipeUrl,
        'mealType' => $m->mealType,
        'mealTypeLabel' => $m->mealTypeLabel(),
    ];
}, $meals);

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
