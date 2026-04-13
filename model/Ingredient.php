<?php
defined('APP_ROOT') || require_once __DIR__ . '/../config.php';

class Ingredient
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = config::getConnexion();
    }

    /** Get all ingredients for a repas */
    public function getByRepas(int $idRepas): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM ingredient WHERE id_repas = ? ORDER BY id_ingredient"
        );
        $stmt->execute([$idRepas]);
        return $stmt->fetchAll();
    }

    /** Get one ingredient */
    public function getById(int $id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM ingredient WHERE id_ingredient = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /** Add ingredient to a repas */
    public function addIngredient(string $nom, ?float $quantite, string $unite, int $idRepas): bool
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO ingredient (nom_ingredient, quantite, unite, id_repas) VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$nom, $quantite, $unite, $idRepas]);
    }

    /** Update ingredient */
    public function updateIngredient(int $id, string $nom, ?float $quantite, string $unite): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE ingredient SET nom_ingredient=?, quantite=?, unite=? WHERE id_ingredient=?"
        );
        return $stmt->execute([$nom, $quantite, $unite, $id]);
    }

    /** Delete ingredient */
    public function deleteIngredient(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM ingredient WHERE id_ingredient = ?");
        return $stmt->execute([$id]);
    }

    /** Delete all ingredients of a repas */
    public function deleteByRepas(int $idRepas): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM ingredient WHERE id_repas = ?");
        return $stmt->execute([$idRepas]);
    }

    /** Count ingredients for a repas */
    public function countByRepas(int $idRepas): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM ingredient WHERE id_repas = ?");
        $stmt->execute([$idRepas]);
        return (int) $stmt->fetchColumn();
    }
}
