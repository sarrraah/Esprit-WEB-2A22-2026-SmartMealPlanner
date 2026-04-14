<?php
defined('APP_ROOT') || require_once __DIR__ . '/../config.php';

class Recette
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = config::getConnexion();
    }

    public function addRecette(string $nom, string $etapes = '', ?int $tempsPrep = null, ?int $tempsCuisson = null, string $difficulte = 'Facile', int $nbPersonnes = 2, ?string $image = null): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO recette_repas (nom_recette, etapes, temps_prep, temps_cuisson, difficulte, nb_personnes, image_recette)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$nom, $etapes, $tempsPrep, $tempsCuisson, $difficulte, $nbPersonnes, $image]);
        return (int) $this->pdo->lastInsertId();
    }

    public function getAllRecettes(): array
    {
        return $this->pdo->query("SELECT * FROM recette_repas ORDER BY id_recette")->fetchAll();
    }

    public function getRecetteById(int $id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM recette_repas WHERE id_recette = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateRecette(int $id, string $nom, string $etapes = '', ?int $tempsPrep = null, ?int $tempsCuisson = null, string $difficulte = 'Facile', int $nbPersonnes = 2, ?string $image = null): bool
    {
        if ($image !== null && $image !== '') {
            $stmt = $this->pdo->prepare(
                "UPDATE recette_repas SET nom_recette=?, etapes=?, temps_prep=?, temps_cuisson=?, difficulte=?, nb_personnes=?, image_recette=? WHERE id_recette=?"
            );
            return $stmt->execute([$nom, $etapes, $tempsPrep, $tempsCuisson, $difficulte, $nbPersonnes, $image, $id]);
        }
        $stmt = $this->pdo->prepare(
            "UPDATE recette_repas SET nom_recette=?, etapes=?, temps_prep=?, temps_cuisson=?, difficulte=?, nb_personnes=? WHERE id_recette=?"
        );
        return $stmt->execute([$nom, $etapes, $tempsPrep, $tempsCuisson, $difficulte, $nbPersonnes, $id]);
    }

    public function deleteRecette(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM recette_repas WHERE id_recette = ?");
        return $stmt->execute([$id]);
    }

    public function countRecettes(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM recette_repas")->fetchColumn();
    }
}
