<?php
/**
 * RepasRecetteController.php — Contrôleur de jointure Repas ↔ Recette
 *
 * Ce contrôleur gère les requêtes SQL impliquant une jointure entre
 * la table `repas` et la table `recette_repas`.
 *
 * Équivalent exact du workshop PDF :
 *   GenreC::afficherAlbums($idGenre)  →  RepasRecetteController::afficherRepasByRecette($idRecette)
 *   GenreC::afficherGenres()          →  RepasRecetteController::afficherToutesRecettes()
 */

// Charger la configuration de la base de données si elle n'est pas déjà incluse
defined('APP_ROOT') || require_once __DIR__ . '/../config.php';

class RepasRecetteController
{
    /**
     * Récupère tous les repas appartenant à une recette donnée.
     *
     * Effectue une jointure INNER JOIN entre `repas` et `recette_repas`
     * pour enrichir chaque repas avec les informations de sa recette.
     *
     * Équivalent workshop : afficherAlbums($idGenre)
     *
     * SQL :
     *   SELECT r.*, rr.nom_recette, rr.difficulte, rr.temps_prep, rr.temps_cuisson
     *   FROM repas r
     *   INNER JOIN recette_repas rr ON r.id_recette = rr.id_recette
     *   WHERE r.id_recette = :id
     *
     * @param int $idRecette Identifiant de la recette à filtrer
     * @return array         Liste des repas avec les détails de la recette associée
     */
    public function afficherRepasByRecette(int $idRecette): array
    {
        try {
            $pdo = config::getConnexion();

            // Préparer la requête avec jointure sur l'id de la recette
            $query = $pdo->prepare("
                SELECT r.*, rr.nom_recette, rr.difficulte, rr.temps_prep, rr.temps_cuisson
                FROM repas r
                INNER JOIN recette_repas rr ON r.id_recette = rr.id_recette
                WHERE r.id_recette = :id
            ");

            // Exécuter avec l'identifiant passé en paramètre
            $query->execute(['id' => $idRecette]);

            // Retourner tous les résultats sous forme de tableau associatif
            return $query->fetchAll();

        } catch (PDOException $e) {
            // Afficher l'erreur SQL et retourner un tableau vide
            echo $e->getMessage();
            return [];
        }
    }

    /**
     * Récupère toutes les recettes disponibles.
     *
     * Utilisée principalement pour alimenter les listes déroulantes (select)
     * dans les formulaires d'ajout et de modification de repas.
     *
     * Équivalent workshop : afficherGenres()
     *
     * SQL : SELECT * FROM recette_repas ORDER BY id_recette
     *
     * @return array Liste complète des recettes triées par identifiant
     */
    public function afficherToutesRecettes(): array
    {
        try {
            $pdo = config::getConnexion();

            // Récupérer toutes les recettes triées par id croissant
            $query = $pdo->prepare("SELECT * FROM recette_repas ORDER BY id_recette");
            $query->execute();

            return $query->fetchAll();

        } catch (PDOException $e) {
            // Afficher l'erreur SQL et retourner un tableau vide
            echo $e->getMessage();
            return [];
        }
    }
}
