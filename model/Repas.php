<?php
defined('APP_ROOT') || require_once __DIR__ . '/../config.php';

class Repas
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = config::getConnexion();
    }

    // ── CREATE ────────────────────────────────────────────────────────────────

    public function addRepas(
        string $nom, $calories, $proteines, $glucides, $lipides,
        string $description, string $typeRepas, int $idRecette,
        ?string $image = null
    ): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO repas
                (nom, calories, proteines, glucides, lipides,
                 description, type_repas, id_recette, image_repas)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $nom, $calories, $proteines, $glucides, $lipides,
            $description, $typeRepas, $idRecette, $image
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    // ── READ ALL (avec jointure recette) ──────────────────────────────────────

    public function getAllRepas(): array
    {
        return $this->pdo->query("
            SELECT
                r.*,
                rr.nom_recette,
                rr.difficulte,
                rr.temps_prep,
                rr.temps_cuisson,
                rr.nb_personnes
            FROM repas r
            LEFT JOIN recette_repas rr ON r.id_recette = rr.id_recette
            ORDER BY r.id_repas DESC
        ")->fetchAll();
    }

    // ── READ ONE (avec jointure recette) ──────────────────────────────────────

    public function getRepasById(int $id)
    {
        $stmt = $this->pdo->prepare("
            SELECT
                r.*,
                rr.nom_recette,
                rr.difficulte,
                rr.temps_prep,
                rr.temps_cuisson,
                rr.nb_personnes,
                rr.etapes
            FROM repas r
            LEFT JOIN recette_repas rr ON r.id_recette = rr.id_recette
            WHERE r.id_repas = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // ── UPDATE ────────────────────────────────────────────────────────────────

    public function updateRepas(
        int $id, string $nom, $calories, $proteines, $glucides, $lipides,
        string $description, string $typeRepas, int $idRecette,
        ?string $image = null
    ): void {
        if ($image !== null && $image !== '') {
            $stmt = $this->pdo->prepare("
                UPDATE repas
                SET nom=?, calories=?, proteines=?, glucides=?, lipides=?,
                    description=?, type_repas=?, id_recette=?, image_repas=?
                WHERE id_repas=?
            ");
            $stmt->execute([
                $nom, $calories, $proteines, $glucides, $lipides,
                $description, $typeRepas, $idRecette, $image, $id
            ]);
        } else {
            $stmt = $this->pdo->prepare("
                UPDATE repas
                SET nom=?, calories=?, proteines=?, glucides=?, lipides=?,
                    description=?, type_repas=?, id_recette=?
                WHERE id_repas=?
            ");
            $stmt->execute([
                $nom, $calories, $proteines, $glucides, $lipides,
                $description, $typeRepas, $idRecette, $id
            ]);
        }
    }

    // ── DELETE ────────────────────────────────────────────────────────────────

    public function deleteRepas(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM repas WHERE id_repas = ?");
        $stmt->execute([$id]);
    }

    // ── Recettes (pour les dropdowns) ─────────────────────────────────────────

    public function getAllRecettes(): array
    {
        return $this->pdo->query(
            "SELECT * FROM recette_repas ORDER BY id_recette"
        )->fetchAll();
    }

    // ── Recherche ─────────────────────────────────────────────────────────────

    public function searchRepas(string $query): array
    {
        $like = '%' . $query . '%';
        return $this->pdo->prepare("
            SELECT
                r.*,
                rr.nom_recette,
                rr.difficulte,
                rr.temps_prep,
                rr.temps_cuisson,
                rr.nb_personnes
            FROM repas r
            LEFT JOIN recette_repas rr ON r.id_recette = rr.id_recette
            WHERE r.nom           LIKE ?
               OR r.description   LIKE ?
               OR r.type_repas    LIKE ?
               OR rr.nom_recette  LIKE ?
            ORDER BY r.id_repas DESC
        ")->execute([$like, $like, $like, $like])
          ? $this->pdo->prepare("
            SELECT
                r.*,
                rr.nom_recette,
                rr.difficulte,
                rr.temps_prep,
                rr.temps_cuisson,
                rr.nb_personnes
            FROM repas r
            LEFT JOIN recette_repas rr ON r.id_recette = rr.id_recette
            WHERE r.nom           LIKE ?
               OR r.description   LIKE ?
               OR r.type_repas    LIKE ?
               OR rr.nom_recette  LIKE ?
            ORDER BY r.id_repas DESC
        ") : [];
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
        return (float) $this->pdo->query(
            "SELECT COALESCE(SUM(calories), 0) FROM repas"
        )->fetchColumn();
    }

    public function avgCalories(): float
    {
        return (float) $this->pdo->query(
            "SELECT COALESCE(AVG(calories), 0) FROM repas"
        )->fetchColumn();
    }

    public function statsByType(): array
    {
        return $this->pdo->query(
            "SELECT type_repas, COUNT(*) AS total FROM repas GROUP BY type_repas"
        )->fetchAll();
    }

    // ── Jointure complète : repas + recette + ingrédients ────────────────────

    public function getRepasWithDetails(int $id): array
    {
        // Repas + recette (jointure complète)
        $stmt = $this->pdo->prepare("
            SELECT
                r.*,
                rr.nom_recette,
                rr.difficulte,
                rr.temps_prep,
                rr.temps_cuisson,
                rr.nb_personnes,
                rr.etapes,
                rr.image_recette
            FROM repas r
            LEFT JOIN recette_repas rr ON r.id_recette = rr.id_recette
            WHERE r.id_repas = ?
        ");
        $stmt->execute([$id]);
        $repas = $stmt->fetch();

        if (!$repas) return [];

        // Ingrédients via la recette (pas via le repas)
        $stmt2 = $this->pdo->prepare("
            SELECT i.*
            FROM ingredient i
            WHERE i.id_recette = ?
            ORDER BY i.id_ingredient
        ");
        $stmt2->execute([$repas['id_recette']]);
        $repas['ingredients'] = $stmt2->fetchAll();

        return $repas;
    }

    // ── Tous les repas avec nom recette (pour les listes) ────────────────────

    public function getAllRepasWithRecette(): array
    {
        return $this->pdo->query("
            SELECT
                r.id_repas,
                r.nom,
                r.calories,
                r.proteines,
                r.glucides,
                r.lipides,
                r.description,
                r.type_repas,
                r.image_repas,
                r.created_at,
                rr.id_recette,
                rr.nom_recette,
                rr.difficulte,
                rr.temps_prep,
                rr.temps_cuisson,
                rr.nb_personnes
            FROM repas r
            LEFT JOIN recette_repas rr ON r.id_recette = rr.id_recette
            ORDER BY r.id_repas DESC
        ")->fetchAll();
    }
}
