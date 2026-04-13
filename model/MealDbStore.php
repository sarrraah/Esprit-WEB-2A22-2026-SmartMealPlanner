<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/Meal.php';

/**
 * Meals persistence in MySQL.
 *
 * Uses your existing table `meal` (as shown in phpMyAdmin screenshot):
 * - id_meal (PK)
 * - nom_meal (name)
 * - type (mealType)
 * - calories
 * - notes (description)
 *
 * Optional columns (if present) will be used:
 * - image, recipeUrl
 */
final class MealDbStore
{
    public static function tableExists(): bool
    {
        try {
            $pdo = Database::pdo();
            $stmt = $pdo->query("SHOW TABLES LIKE 'meal'");
            return (bool) $stmt->fetchColumn();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Ensure optional columns exist so uploads/recipe URLs can be persisted.
     * Safe to call repeatedly.
     */
    public static function ensureSchema(): void
    {
        $pdo = Database::pdo();
        $cols = self::existingColumns($pdo);

        if (!in_array('image', $cols, true)) {
            $pdo->exec("ALTER TABLE meal ADD COLUMN image VARCHAR(255) NULL");
        }

        // prefer recipe_url snake_case to avoid reserved/casing issues
        if (!in_array('recipe_url', $cols, true) && !in_array('recipeUrl', $cols, true)) {
            $pdo->exec("ALTER TABLE meal ADD COLUMN recipe_url VARCHAR(255) NULL");
        }

        // Convenience view: always-croissant display ids (1..N) without touching the real PK.
        // This is what you want to "see" in SQL/phpMyAdmin, while keeping `id_meal` stable.
        try {
            $pdo->exec(
                "CREATE OR REPLACE VIEW meal_ordered AS
                 SELECT
                   ROW_NUMBER() OVER (ORDER BY id_meal) AS display_id,
                   m.*
                 FROM meal m"
            );
        } catch (Throwable) {
            // If the DB user can't create views, ignore (app will still work).
        }
    }

    /**
     * @return Meal[]
     */
    public static function all(): array
    {
        $pdo = Database::pdo();
        // Make sure optional columns exist before selecting
        self::ensureSchema();
        $cols = self::existingColumns($pdo);

        $select = [
            "id_meal AS id",
            "nom_meal AS name",
            "`type` AS mealType",
            "calories AS calories",
            "notes AS description",
        ];

        if (in_array('image', $cols, true)) {
            $select[] = "image AS image";
        } else {
            $select[] = "'' AS image";
        }

        if (in_array('recipeUrl', $cols, true)) {
            $select[] = "recipeUrl AS recipeUrl";
        } elseif (in_array('recipe_url', $cols, true)) {
            $select[] = "recipe_url AS recipeUrl";
        } else {
            $select[] = "'#' AS recipeUrl";
        }

        $sql = "SELECT " . implode(', ', $select) . " FROM meal ORDER BY id_meal ASC";
        $rows = $pdo->query($sql)->fetchAll();

        $meals = [];
        foreach ($rows as $r) {
            $meals[] = new Meal(
                (int) ($r['id'] ?? 0),
                (string) ($r['name'] ?? ''),
                (int) ($r['calories'] ?? 0),
                (string) ($r['description'] ?? ''),
                (string) ($r['image'] ?? ''),
                (string) ($r['recipeUrl'] ?? '#'),
                (string) ($r['mealType'] ?? 'lunch')
            );
        }
        return $meals;
    }

    public static function countMeals(): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->query("SELECT COUNT(*) FROM meal");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Sync meals from JSON rows into DB (insert missing; update image/recipe if empty).
     *
     * @param array<int, array<string, mixed>> $rows
     */
    public static function syncFromJsonRows(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $pdo = Database::pdo();
        self::ensureSchema();
        $cols = self::existingColumns($pdo);

        $hasImage = in_array('image', $cols, true);
        $recipeCol = in_array('recipeUrl', $cols, true) ? 'recipeUrl' : (in_array('recipe_url', $cols, true) ? 'recipe_url' : null);

        $selectExisting = $pdo->prepare(
            "SELECT id_meal, " .
            ($hasImage ? "image" : "NULL AS image") . ", " .
            ($recipeCol ? "{$recipeCol} AS recipeUrl" : "NULL AS recipeUrl") .
            " FROM meal WHERE nom_meal = :name AND `type` = :type AND calories = :calories AND notes = :notes LIMIT 1"
        );

        $update = null;
        if ($hasImage || $recipeCol) {
            $set = [];
            if ($hasImage) $set[] = "image = :image";
            if ($recipeCol) $set[] = "{$recipeCol} = :recipeUrl";
            $update = $pdo->prepare("UPDATE meal SET " . implode(', ', $set) . " WHERE id_meal = :id");
        }

        foreach ($rows as $r) {
            if (!is_array($r)) continue;

            $name = (string) ($r['name'] ?? '');
            $type = (string) ($r['mealType'] ?? 'lunch');
            $cal = (int) ($r['calories'] ?? 0);
            $notes = (string) ($r['description'] ?? '');
            $image = (string) ($r['image'] ?? '');
            $recipe = (string) ($r['recipeUrl'] ?? '#');

            if ($name === '') continue;

            $selectExisting->execute([
                ':name' => $name,
                ':type' => $type,
                ':calories' => $cal,
                ':notes' => $notes,
            ]);
            $existing = $selectExisting->fetch();

            if ($existing) {
                if ($update) {
                    $needsUpdate = false;
                    $params = [':id' => (int) $existing['id_meal']];
                    if ($hasImage) {
                        $cur = (string) ($existing['image'] ?? '');
                        if ($cur === '' && $image !== '') {
                            $needsUpdate = true;
                        }
                        $params[':image'] = $cur !== '' ? $cur : $image;
                    }
                    if ($recipeCol) {
                        $curR = (string) ($existing['recipeUrl'] ?? '');
                        if (($curR === '' || $curR === '#') && $recipe !== '' && $recipe !== '#') {
                            $needsUpdate = true;
                        }
                        $params[':recipeUrl'] = ($curR !== '' ? $curR : $recipe);
                    }

                    if ($needsUpdate) {
                        $update->execute($params);
                    }
                }
                continue;
            }

            self::insert(new Meal(0, $name, $cal, $notes, $image, $recipe, $type));
        }
    }

    public static function insert(Meal $m): int
    {
        $pdo = Database::pdo();
        self::ensureSchema();
        $cols = self::existingColumns($pdo);

        $fields = ['nom_meal', 'type', 'calories', 'notes'];
        $params = [':name', ':type', ':calories', ':notes'];

        $data = [
            ':name' => $m->name,
            ':type' => $m->mealType,
            ':calories' => $m->calories,
            ':notes' => $m->description,
        ];

        if (in_array('image', $cols, true)) {
            $fields[] = 'image';
            $params[] = ':image';
            $data[':image'] = $m->image;
        }

        if (in_array('recipeUrl', $cols, true)) {
            $fields[] = 'recipeUrl';
            $params[] = ':recipeUrl';
            $data[':recipeUrl'] = $m->recipeUrl;
        } elseif (in_array('recipe_url', $cols, true)) {
            $fields[] = 'recipe_url';
            $params[] = ':recipeUrl';
            $data[':recipeUrl'] = $m->recipeUrl;
        }

        $sql = "INSERT INTO meal (" . implode(',', $fields) . ") VALUES (" . implode(',', $params) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);

        $id = (int) $pdo->lastInsertId();
        self::resequenceIds();
        return $id;
    }

    public static function update(int $id, Meal $m): void
    {
        $pdo = Database::pdo();
        self::ensureSchema();
        $cols = self::existingColumns($pdo);

        $sets = [
            "nom_meal = :name",
            "`type` = :type",
            "calories = :calories",
            "notes = :notes",
        ];

        $data = [
            ':id' => $id,
            ':name' => $m->name,
            ':type' => $m->mealType,
            ':calories' => $m->calories,
            ':notes' => $m->description,
        ];

        if (in_array('image', $cols, true)) {
            $sets[] = "image = :image";
            $data[':image'] = $m->image;
        }

        if (in_array('recipeUrl', $cols, true)) {
            $sets[] = "recipeUrl = :recipeUrl";
            $data[':recipeUrl'] = $m->recipeUrl;
        } elseif (in_array('recipe_url', $cols, true)) {
            $sets[] = "recipe_url = :recipeUrl";
            $data[':recipeUrl'] = $m->recipeUrl;
        }

        $sql = "UPDATE meal SET " . implode(', ', $sets) . " WHERE id_meal = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
    }

    public static function delete(int $id): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("DELETE FROM meal WHERE id_meal = :id");
        $stmt->execute([':id' => $id]);
        self::resequenceIds();
    }

    /**
     * Force `meal.id_meal` to be sequential (1..N) and reset AUTO_INCREMENT.
     * Also updates referencing FK columns if they exist.
     *
     * WARNING: This is not recommended for production, but matches your requirement.
     */
    public static function resequenceIds(): void
    {
        $pdo = Database::pdo();
        self::ensureSchema();

        $ids = $pdo->query("SELECT id_meal FROM meal ORDER BY id_meal ASC")->fetchAll(PDO::FETCH_COLUMN);
        if (!$ids) {
            // keep auto_increment at 1
            try { $pdo->exec("ALTER TABLE meal AUTO_INCREMENT = 1"); } catch (Throwable) {}
            return;
        }

        $map = [];
        $n = 0;
        foreach ($ids as $old) {
            $n++;
            $oldId = (int) $old;
            if ($oldId !== $n) {
                $map[$oldId] = $n;
            }
        }
        if ($map === []) {
            // already sequential; just ensure auto_increment
            try { $pdo->exec("ALTER TABLE meal AUTO_INCREMENT = " . ((int) count($ids) + 1)); } catch (Throwable) {}
            return;
        }

        $pdo->beginTransaction();
        try {
            $dbName = (string) $pdo->query("SELECT DATABASE()")->fetchColumn();

            // Find FK references to meal.id_meal and update them too
            $fkStmt = $pdo->prepare(
                "SELECT TABLE_NAME, COLUMN_NAME
                 FROM information_schema.KEY_COLUMN_USAGE
                 WHERE REFERENCED_TABLE_SCHEMA = :db
                   AND REFERENCED_TABLE_NAME = 'meal'
                   AND REFERENCED_COLUMN_NAME = 'id_meal'"
            );
            $fkStmt->execute([':db' => $dbName]);
            $refs = $fkStmt->fetchAll();

            // Use positive temporary ids (works even if id_meal is UNSIGNED)
            $maxId = (int) $pdo->query("SELECT MAX(id_meal) FROM meal")->fetchColumn();
            $offset = $maxId + 100000;

            // 1) Move meal ids to temporary high positive values to avoid collisions
            $updTmpMeal = $pdo->prepare("UPDATE meal SET id_meal = :tmp WHERE id_meal = :old");
            foreach ($map as $oldId => $newId) {
                $updTmpMeal->execute([':tmp' => $offset + $oldId, ':old' => $oldId]);
            }

            // 2) Update referencing tables to those temporary ids too
            foreach ($refs as $ref) {
                $table = (string) ($ref['TABLE_NAME'] ?? '');
                $col = (string) ($ref['COLUMN_NAME'] ?? '');
                if ($table === '' || $col === '') continue;

                $sql = "UPDATE `{$table}` SET `{$col}` = :tmp WHERE `{$col}` = :old";
                $upd = $pdo->prepare($sql);
                foreach ($map as $oldId => $newId) {
                    $upd->execute([':tmp' => $offset + $oldId, ':old' => $oldId]);
                }
            }

            // 3) Assign final new ids
            $updFinalMeal = $pdo->prepare("UPDATE meal SET id_meal = :new WHERE id_meal = :tmp");
            foreach ($map as $oldId => $newId) {
                $updFinalMeal->execute([':new' => $newId, ':tmp' => $offset + $oldId]);
            }

            foreach ($refs as $ref) {
                $table = (string) ($ref['TABLE_NAME'] ?? '');
                $col = (string) ($ref['COLUMN_NAME'] ?? '');
                if ($table === '' || $col === '') continue;

                $sql = "UPDATE `{$table}` SET `{$col}` = :new WHERE `{$col}` = :tmp";
                $upd = $pdo->prepare($sql);
                foreach ($map as $oldId => $newId) {
                    $upd->execute([':new' => $newId, ':tmp' => $offset + $oldId]);
                }
            }

            $pdo->commit();
            
            // 4) Reset AUTO_INCREMENT (must be outside transaction since ALTER TABLE auto-commits in MySQL)
            try {
                $pdo->exec("ALTER TABLE meal AUTO_INCREMENT = " . ((int) count($ids) + 1));
            } catch (Throwable) {
                // Ignore AUTO_INCREMENT errors; they're not critical
            }
        } catch (Throwable $e) {
            try {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
            } catch (Throwable) {
                // Already rolled back or no transaction
            }
            throw $e;
        }
    }

    /**
     * @return string[]
     */
    private static function existingColumns(PDO $pdo): array
    {
        $stmt = $pdo->query("SHOW COLUMNS FROM meal");
        $cols = [];
        foreach ($stmt->fetchAll() as $row) {
            $cols[] = (string) ($row['Field'] ?? '');
        }
        return $cols;
    }
}

