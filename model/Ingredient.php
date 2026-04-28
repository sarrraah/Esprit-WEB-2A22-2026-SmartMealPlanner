<?php
defined('APP_ROOT') || require_once __DIR__ . '/../config.php';

class Ingredient
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = config::getConnexion();
    }

    // ── Ingrédients d'une RECETTE ─────────────────────────────────────────────

    /** Récupérer tous les ingrédients d'une recette */
    public function getByRecette(int $idRecette): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM ingredient WHERE id_recette = ? ORDER BY id_ingredient"
        );
        $stmt->execute([$idRecette]);
        return $stmt->fetchAll();
    }

    /** Ajouter un ingrédient à une recette */
    public function addIngredientToRecette(string $nom, ?float $quantite, string $unite, int $idRecette): bool
    {
        // Désactiver temporairement les FK pour contourner la contrainte legacy
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

        $stmt = $this->pdo->prepare(
            "INSERT INTO ingredient (nom_ingredient, quantite, unite, id_recette, id_repas)
             VALUES (?, ?, ?, ?, NULL)"
        );
        $result = $stmt->execute([$nom, $quantite, $unite, $idRecette]);

        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        return $result;
    }

    // ── Ingrédients d'un REPAS (via sa recette) ───────────────────────────────

    /** Récupérer les ingrédients d'un repas via sa recette */
    public function getByRepas(int $idRepas): array
    {
        $stmt = $this->pdo->prepare("
            SELECT i.*
            FROM ingredient i
            INNER JOIN repas r ON i.id_recette = r.id_recette
            WHERE r.id_repas = ?
            ORDER BY i.id_ingredient
        ");
        $stmt->execute([$idRepas]);
        return $stmt->fetchAll();
    }

    // ── CRUD commun ───────────────────────────────────────────────────────────

    /** Récupérer un ingrédient par son ID */
    public function getById(int $id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM ingredient WHERE id_ingredient = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /** Modifier un ingrédient */
    public function updateIngredient(int $id, string $nom, ?float $quantite, string $unite): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE ingredient SET nom_ingredient=?, quantite=?, unite=? WHERE id_ingredient=?"
        );
        return $stmt->execute([$nom, $quantite, $unite, $id]);
    }

    /** Supprimer un ingrédient */
    public function deleteIngredient(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM ingredient WHERE id_ingredient = ?");
        return $stmt->execute([$id]);
    }

    /** Supprimer tous les ingrédients d'une recette */
    public function deleteByRecette(int $idRecette): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM ingredient WHERE id_recette = ?");
        return $stmt->execute([$idRecette]);
    }

    /** Compter les ingrédients d'une recette */
    public function countByRecette(int $idRecette): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM ingredient WHERE id_recette = ?");
        $stmt->execute([$idRecette]);
        return (int) $stmt->fetchColumn();
    }
}
