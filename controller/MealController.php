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
     * Get favourite meal IDs for the current user.
     * @return int[]
     */
    public static function getFavouriteIds(int $userId = 1): array
    {
        try {
            $pdo  = Database::pdo();
            $stmt = $pdo->prepare('SELECT meal_id FROM favourites WHERE user_id = :uid ORDER BY created_at DESC');
            $stmt->execute([':uid' => $userId]);
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Search meals by name, calories or type.
     * @return Meal[]
     */
    public static function searchMeals(string $query, string $searchBy): array
    {
        $all = Meal::all();
        if ($query === '') return $all;

        return array_values(array_filter($all, function(Meal $m) use ($query, $searchBy) {
            switch ($searchBy) {
                case 'calories':
                    return str_contains((string) $m->calories, $query);
                case 'type':
                    return stripos($m->mealType, $query) !== false;
                default: // name
                    return stripos($m->name, $query) !== false;
            }
        }));
    }

    /**
     * Returns meals that are currently assigned to the active plan today,
     * joined via plan_detail. Each row has extra keys: plan_name, objectif.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function listMealsWithPlan(): array
    {
        try {
            $pdo  = Database::pdo();
            $stmt = $pdo->prepare('
                SELECT
                    m.id_meal,
                    m.nom_meal,
                    m.type,
                    m.calories,
                    m.image,
                    m.recipe_url,
                    mp.nom      AS plan_name,
                    mp.objectif AS objectif
                FROM meal m
                INNER JOIN plan_detail pd ON pd.meal_id = m.id_meal
                INNER JOIN mealplan mp    ON mp.id_plan = pd.plan_id
                GROUP BY m.id_meal
            ');
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }
}
