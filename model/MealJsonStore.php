<?php

require_once __DIR__ . '/../config/Database.php';

/**
 * Data access layer for meals — backed by MySQL via PDO.
 * Keeps the same static interface so Meal.php and controllers are unchanged.
 */
class MealJsonStore
{
    public static function exists(): bool
    {
        try {
            Database::pdo()->query('SELECT COUNT(*) FROM meals');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function loadRows(): array
    {
        try {
            $stmt = Database::pdo()->query('SELECT * FROM meals ORDER BY id ASC');
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    public static function saveRows(array $rows): void
    {
        $pdo = Database::pdo();

        $pdo->exec('DELETE FROM meals');

        $stmt = $pdo->prepare('
            INSERT INTO meals (id, name, calories, description, image, recipeUrl, mealType)
            VALUES (:id, :name, :calories, :description, :image, :recipeUrl, :mealType)
        ');

        foreach (array_values($rows) as $i => $row) {
            $stmt->execute([
                ':id'          => $i + 1,
                ':name'        => $row['name']        ?? '',
                ':calories'    => (int) ($row['calories'] ?? 0),
                ':description' => $row['description'] ?? '',
                ':image'       => $row['image']       ?? '',
                ':recipeUrl'   => $row['recipeUrl']   ?? '#',
                ':mealType'    => $row['mealType']    ?? 'lunch',
            ]);
        }
    }
}
