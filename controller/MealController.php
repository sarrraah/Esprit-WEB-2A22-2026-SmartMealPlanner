<?php

require_once __DIR__ . '/../model/Meal.php';

/**
 * Front-office meals: supplies data to the view layer.
 */
class MealController
{
    /**
     * @return Meal[]
     */
    public static function listMeals(): array
    {
        return Meal::all();
    }

    public static function getMeal(int $id): ?Meal
    {
        return Meal::find($id);
    }
}
