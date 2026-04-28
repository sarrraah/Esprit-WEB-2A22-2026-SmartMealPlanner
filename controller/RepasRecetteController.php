<?php
/**
 * RepasRecetteController — Jointure repas + recette
 *
 * Equivalent exact du workshop PDF :
 *   GenreC::afficherAlbums($idGenre)  →  RepasRecetteController::afficherRepasByRecette($idRecette)
 *   GenreC::afficherGenres()          →  RepasRecetteController::afficherToutesRecettes()
 */

defined('APP_ROOT') || require_once __DIR__ . '/../config.php';

class RepasRecetteController
{
    /**
     * Afficher les repas d'une recette donnée
     * Equivalent : afficherAlbums($idGenre)
     *
     * SQL : SELECT r.*, rr.nom_recette
     *       FROM repas r
     *       INNER JOIN recette_repas rr ON r.id_recette = rr.id_recette
     *       WHERE r.id_recette = :id
     */
    public function afficherRepasByRecette(int $idRecette): array
    {
        try {
            $pdo   = config::getConnexion();
            $query = $pdo->prepare("
                SELECT r.*, rr.nom_recette, rr.difficulte, rr.temps_prep, rr.temps_cuisson
                FROM repas r
                INNER JOIN recette_repas rr ON r.id_recette = rr.id_recette
                WHERE r.id_recette = :id
            ");
            $query->execute(['id' => $idRecette]);
            return $query->fetchAll();
        } catch (PDOException $e) {
            echo $e->getMessage();
            return [];
        }
    }

    /**
     * Afficher toutes les recettes (pour le select du formulaire)
     * Equivalent : afficherGenres()
     *
     * SQL : SELECT * FROM recette_repas
     */
    public function afficherToutesRecettes(): array
    {
        try {
            $pdo   = config::getConnexion();
            $query = $pdo->prepare("SELECT * FROM recette_repas ORDER BY id_recette");
            $query->execute();
            return $query->fetchAll();
        } catch (PDOException $e) {
            echo $e->getMessage();
            return [];
        }
    }
}
