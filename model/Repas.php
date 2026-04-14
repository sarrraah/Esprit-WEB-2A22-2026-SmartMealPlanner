<?php
defined('APP_ROOT') || require_once __DIR__ . '/../config.php';

class Repas
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = config::getConnexion();
    }

    public function addRepas(string $nom, $calories, $proteines, $glucides, $lipides, string $description, string $typeRepas, int $idRecette, ?string $image = null): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO repas (nom, calories, proteines, glucides, lipides, description, type_repas, id_recette, image_repas)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$nom, $calories, $proteines, $glucides, $lipides, $description, $typeRepas, $idRecette, $image]);
        return (int) $this->pdo->lastInsertId();
    }

    public function getAllRepas(): array
    {
        return $this->pdo->query("SELECT * FROM repas ORDER BY id_repas DESC")->fetchAll();
    }

    public function getRepasById(int $id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM repas WHERE id_repas = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateRepas(int $id, string $nom, $calories, $proteines, $glucides, $lipides, string $description, string $typeRepas, int $idRecette, ?string $image = null): void
    {
        if ($image !== null && $image !== '') {
            $stmt = $this->pdo->prepare(
                "UPDATE repas SET nom=?, calories=?, proteines=?, glucides=?, lipides=?, description=?, type_repas=?, id_recette=?, image_repas=? WHERE id_repas=?"
            );
            $stmt->execute([$nom, $calories, $proteines, $glucides, $lipides, $description, $typeRepas, $idRecette, $image, $id]);
        } else {
            $stmt = $this->pdo->prepare(
                "UPDATE repas SET nom=?, calories=?, proteines=?, glucides=?, lipides=?, description=?, type_repas=?, id_recette=? WHERE id_repas=?"
            );
            $stmt->execute([$nom, $calories, $proteines, $glucides, $lipides, $description, $typeRepas, $idRecette, $id]);
        }
    }

    public function deleteRepas(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM repas WHERE id_repas = ?");
        $stmt->execute([$id]);
    }

    public function getAllRecettes(): array
    {
        return $this->pdo->query("SELECT * FROM recette_repas ORDER BY id_recette")->fetchAll();
    }

    public function countRepas(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM repas")->fetchColumn();
    }

    public function countRecettes(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM recette_repas")->fetchColumn();
    }

    public function totalCalories(): float
    {
        return (float) $this->pdo->query("SELECT COALESCE(SUM(calories),0) FROM repas")->fetchColumn();
    }

    public function avgCalories(): float
    {
        return (float) $this->pdo->query("SELECT COALESCE(AVG(calories),0) FROM repas")->fetchColumn();
    }

    public function statsByType(): array
    {
        return $this->pdo->query(
            "SELECT type_repas, COUNT(*) as total FROM repas GROUP BY type_repas"
        )->fetchAll();
    }
}
