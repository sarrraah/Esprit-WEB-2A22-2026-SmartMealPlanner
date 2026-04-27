<?php

require_once __DIR__ . '/../model/Meal.php';
require_once __DIR__ . '/../config/Database.php';

class MealController
{
    /** @return Meal[] */
    public static function listMeals(): array
    {
        return Meal::all();
    }

    public static function getMeal(int $id): ?Meal
    {
        return Meal::find($id);
    }

    /**
     * Returns meals joined with their plan (INNER JOIN — only meals with a valid id_plan).
     * Each row has extra keys: plan_name, objectif.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function listMealsWithPlan(): array
    {
        try {
            $pdo  = Database::pdo();
            $stmt = $pdo->prepare('
                SELECT
                    meal.id_meal,
                    meal.nom_meal,
                    meal.type,
                    meal.calories,
                    meal.image,
                    meal.recipe_url,
                    mealplan.nom      AS plan_name,
                    mealplan.objectif AS objectif
                FROM meal
                INNER JOIN mealplan ON meal.id_plan = mealplan.id_plan
            ');
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }
}
