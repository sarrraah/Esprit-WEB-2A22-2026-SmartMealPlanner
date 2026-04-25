<?php

require_once __DIR__ . '/../config/Database.php';

class PlanDbStore
{
    // Maps to the existing `mealplan` table
    private const TABLE = 'mealplan';

    public static function tableExists(): bool
    {
        try {
            $stmt = Database::pdo()->query("SHOW TABLES LIKE '" . self::TABLE . "'");
            return (bool) $stmt->fetchColumn();
        } catch (Throwable $e) {
            return false;
        }
    }

    /** @return Plan[] */
    public static function all(): array
    {
        if (!self::tableExists()) return [];
        try {
            $rows = Database::pdo()->query('SELECT * FROM ' . self::TABLE . ' ORDER BY id_plan ASC')->fetchAll();
            return array_map([Plan::class, 'fromRow'], $rows);
        } catch (Throwable $e) {
            return [];
        }
    }

    public static function first(): ?Plan
    {
        $all = self::all();
        return $all[0] ?? null;
    }

    public static function insert(array $data): void
    {
        $duree = max(1, (int) ($data['duree'] ?? 7));
        $stmt = Database::pdo()->prepare('
            INSERT INTO mealplan (nom, duree, date_debut, date_fin, objectif, description, user_id)
            VALUES (:nom, :duree, :date_debut, :date_fin, :objectif, :description, :user_id)
        ');
        $stmt->execute([
            ':nom'         => $data['nom']         ?? '',
            ':duree'       => $duree,
            ':date_debut'  => $data['date_debut']  ?? date('Y-m-d'),
            ':date_fin'    => $data['date_fin']    ?? date('Y-m-d', strtotime("+{$duree} days")),
            ':objectif'    => $data['objectif']    ?? '',
            ':description' => $data['description'] ?? '',
            ':user_id'     => $data['user_id']     ?? 1,
        ]);

        try {
            Database::pdo()->exec("ALTER TABLE mealplan ALTER COLUMN duree DROP DEFAULT");
        } catch (Throwable $e) {}
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::pdo()->prepare('
            UPDATE mealplan SET nom=:nom, duree=:duree, date_debut=:date_debut,
            date_fin=:date_fin, objectif=:objectif, description=:description
            WHERE id_plan=:id
        ');
        $stmt->execute([
            ':id'          => $id,
            ':nom'         => $data['nom']         ?? '',
            ':duree'       => (int) ($data['duree'] ?? 7),
            ':date_debut'  => $data['date_debut']  ?? date('Y-m-d'),
            ':date_fin'    => $data['date_fin']    ?? '',
            ':objectif'    => $data['objectif']    ?? '',
            ':description' => $data['description'] ?? '',
        ]);
    }

    public static function delete(int $id): void
    {
        Database::pdo()->prepare('DELETE FROM mealplan WHERE id_plan=:id')->execute([':id' => $id]);
    }
}
