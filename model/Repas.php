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

    // ── READ ALL — JOIN repas + recette ──────────────────────────────────────

    public function getAllRepas(): array
    {
        return $this->pdo->query("
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
            ORDER BY r.id_repas DESC
        ")->fetchAll();
    }

    public function getAllRepasWithRecette(): array
    {
        return $this->getAllRepas();
    }

    // ── READ ONE — JOIN repas + recette ───────────────────────────────────────

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
                rr.etapes,
                rr.image_recette
            FROM repas r
            LEFT JOIN recette_repas rr ON r.id_recette = rr.id_recette
            WHERE r.id_repas = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // ── READ ONE — JOIN complet : repas + recette + ingrédients ──────────────

    public function getRepasWithDetails(int $id): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                r.*,
                rr.nom_recette,
                rr.difficulte,
                rr.temps_prep,
                rr.temps_cuisson,
                rr.nb_personnes,
                rr.etapes,
                rr.image_recette,
                rr.video_youtube
            FROM repas r
            LEFT JOIN recette_repas rr ON r.id_recette = rr.id_recette
            WHERE r.id_repas = ?
        ");
        $stmt->execute([$id]);
        $repas = $stmt->fetch();

        if (!$repas) return [];

        $stmt2 = $this->pdo->prepare("
            SELECT * FROM ingredient
            WHERE id_recette = ?
            ORDER BY id_ingredient
        ");
        $stmt2->execute([$repas['id_recette']]);
        $repas['ingredients'] = $stmt2->fetchAll();

        return $repas;
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

    // ── Recettes pour les dropdowns ───────────────────────────────────────────

    public function getAllRecettes(): array
    {
        return $this->pdo->query(
            "SELECT * FROM recette_repas ORDER BY id_recette"
        )->fetchAll();
    }

    // ── RECHERCHE — JOIN repas + recette ─────────────────────────────────────

    public function searchRepas(string $query): array
    {
        $like = '%' . $query . '%';
        $stmt = $this->pdo->prepare("
            SELECT
                r.*,
                rr.nom_recette,
                rr.difficulte,
                rr.temps_prep,
                rr.temps_cuisson,
                rr.nb_personnes
            FROM repas r
            LEFT JOIN recette_repas rr ON r.id_recette = rr.id_recette
            WHERE r.nom          LIKE ?
               OR r.description  LIKE ?
               OR r.type_repas   LIKE ?
               OR rr.nom_recette LIKE ?
            ORDER BY r.id_repas DESC
        ");
        $stmt->execute([$like, $like, $like, $like]);
        return $stmt->fetchAll();
    }

    // ── JOINTURE repas + recette ──────────────────────────────────────────────
    // Equivalent workshop : SELECT * FROM album WHERE genre = :id
    // Ici : SELECT * FROM repas WHERE id_recette = :id

    public function afficherRepasByRecette(int $idRecette): array
    {
        $stmt = $this->pdo->prepare("
            SELECT r.*, rr.nom_recette, rr.difficulte, rr.temps_prep, rr.temps_cuisson
            FROM repas r
            INNER JOIN recette_repas rr ON r.id_recette = rr.id_recette
            WHERE r.id_recette = :id
        ");
        $stmt->execute(['id' => $idRecette]);
        return $stmt->fetchAll();
    }

    public function afficherToutesRecettes(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM recette_repas ORDER BY id_recette");
        $stmt->execute();
        return $stmt->fetchAll();
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
}
