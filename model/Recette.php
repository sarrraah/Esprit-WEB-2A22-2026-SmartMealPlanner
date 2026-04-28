<?php
defined('APP_ROOT') || require_once __DIR__ . '/../config.php';

class Recette
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = config::getConnexion();
    }

    // ── CREATE ────────────────────────────────────────────────────────────────

    public function addRecette(
        string $nom, string $etapes = '', ?int $tempsPrep = null,
        ?int $tempsCuisson = null, string $difficulte = 'Facile',
        int $nbPersonnes = 2, ?string $image = null
    ): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO recette_repas
                (nom_recette, etapes, temps_prep, temps_cuisson, difficulte, nb_personnes, image_recette)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nom, $etapes, $tempsPrep, $tempsCuisson, $difficulte, $nbPersonnes, $image]);
        return (int) $this->pdo->lastInsertId();
    }

    // ── READ ALL ──────────────────────────────────────────────────────────────

    public function getAllRecettes(): array
    {
        return $this->pdo->query(
            "SELECT * FROM recette_repas ORDER BY id_recette"
        )->fetchAll();
    }

    // ── READ ALL avec nombre de repas associés (jointure) ─────────────────────

    public function getAllRecettesWithRepasCount(): array
    {
        return $this->pdo->query("
            SELECT
                rr.*,
                COUNT(r.id_repas) AS nb_repas
            FROM recette_repas rr
            LEFT JOIN repas r ON rr.id_recette = r.id_recette
            GROUP BY rr.id_recette
            ORDER BY rr.id_recette
        ")->fetchAll();
    }

    // ── READ ONE ──────────────────────────────────────────────────────────────

    public function getRecetteById(int $id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM recette_repas WHERE id_recette = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // ── READ ONE avec tous ses repas et ingrédients (jointure complète) ───────

    public function getRecetteWithRepas(int $id): array
    {
        // 1. La recette
        $stmt = $this->pdo->prepare(
            "SELECT * FROM recette_repas WHERE id_recette = ?"
        );
        $stmt->execute([$id]);
        $recette = $stmt->fetch();

        if (!$recette) return [];

        // 2. Les repas liés à cette recette (jointure)
        $stmt2 = $this->pdo->prepare("
            SELECT
                r.*,
                rr.nom_recette,
                rr.difficulte,
                rr.temps_prep,
                rr.temps_cuisson
            FROM repas r
            INNER JOIN recette_repas rr ON r.id_recette = rr.id_recette
            WHERE r.id_recette = ?
            ORDER BY r.id_repas
        ");
        $stmt2->execute([$id]);
        $repas = $stmt2->fetchAll();

        // 3. Les ingrédients de chaque repas (via la recette)
        foreach ($repas as &$rep) {
            $stmt3 = $this->pdo->prepare(
                "SELECT * FROM ingredient WHERE id_recette = ? ORDER BY id_ingredient"
            );
            $stmt3->execute([$id]); // id = id_recette
            $rep['ingredients'] = $stmt3->fetchAll();
        }

        $recette['repas'] = $repas;
        return $recette;
    }

    // ── UPDATE ────────────────────────────────────────────────────────────────

    public function updateRecette(
        int $id, string $nom, string $etapes = '', ?int $tempsPrep = null,
        ?int $tempsCuisson = null, string $difficulte = 'Facile',
        int $nbPersonnes = 2, ?string $image = null
    ): bool {
        if ($image !== null && $image !== '') {
            $stmt = $this->pdo->prepare("
                UPDATE recette_repas
                SET nom_recette=?, etapes=?, temps_prep=?, temps_cuisson=?,
                    difficulte=?, nb_personnes=?, image_recette=?
                WHERE id_recette=?
            ");
            return $stmt->execute([
                $nom, $etapes, $tempsPrep, $tempsCuisson,
                $difficulte, $nbPersonnes, $image, $id
            ]);
        }
        $stmt = $this->pdo->prepare("
            UPDATE recette_repas
            SET nom_recette=?, etapes=?, temps_prep=?, temps_cuisson=?,
                difficulte=?, nb_personnes=?
            WHERE id_recette=?
        ");
        return $stmt->execute([
            $nom, $etapes, $tempsPrep, $tempsCuisson,
            $difficulte, $nbPersonnes, $id
        ]);
    }

    // ── DELETE ────────────────────────────────────────────────────────────────

    public function deleteRecette(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM recette_repas WHERE id_recette = ?"
        );
        return $stmt->execute([$id]);
    }

    // ── STATS ─────────────────────────────────────────────────────────────────

    public function countRecettes(): int
    {
        return (int) $this->pdo->query(
            "SELECT COUNT(*) FROM recette_repas"
        )->fetchColumn();
    }
}
