<?php
/**
 * Recette.php — Modèle de la table `recette_repas`
 *
 * Gère toutes les opérations CRUD sur les recettes :
 *   - Création d'une recette
 *   - Lecture (toutes, une seule, avec jointure repas/ingrédients)
 *   - Mise à jour
 *   - Suppression
 *   - Statistiques
 */

// Charger la configuration de la base de données si elle n'est pas déjà incluse
defined('APP_ROOT') || require_once __DIR__ . '/../config.php';

class Recette
{
    /** @var PDO Instance de connexion à la base de données */
    private $pdo;

    /**
     * Constructeur : initialise la connexion PDO via la classe config.
     */
    public function __construct()
    {
        $this->pdo = config::getConnexion();
    }

    // ── CREATE ────────────────────────────────────────────────────────────────

    /**
     * Insère une nouvelle recette en base de données.
     *
     * @param string      $nom          Nom de la recette (obligatoire)
     * @param string      $etapes       Étapes de préparation (texte libre)
     * @param int|null    $tempsPrep    Temps de préparation en minutes
     * @param int|null    $tempsCuisson Temps de cuisson en minutes
     * @param string      $difficulte   Niveau de difficulté ('Facile', 'Moyen', 'Difficile')
     * @param int         $nbPersonnes  Nombre de personnes (par défaut 2)
     * @param string|null $image        Chemin relatif de l'image (ex: uploads/recettes/xxx.jpg)
     * @return int                      Identifiant de la recette nouvellement créée
     */
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

        // Retourner l'id auto-incrémenté de la recette insérée
        return (int) $this->pdo->lastInsertId();
    }

    // ── READ ALL ──────────────────────────────────────────────────────────────

    /**
     * Récupère toutes les recettes, triées par identifiant croissant.
     *
     * @return array Liste de toutes les recettes
     */
    public function getAllRecettes(): array
    {
        return $this->pdo->query(
            "SELECT * FROM recette_repas ORDER BY id_recette"
        )->fetchAll();
    }

    // ── READ ALL avec comptage des repas associés (jointure LEFT JOIN) ────────

    /**
     * Récupère toutes les recettes avec le nombre de repas associés et
     * la somme totale des calories de leurs repas.
     *
     * Utilise un LEFT JOIN pour inclure les recettes sans repas (nb_repas = 0).
     * La colonne `total_calories` contient la somme des calories de tous les repas liés.
     *
     * @param string $orderBy  Colonne de tri : 'id_recette' (défaut) ou 'total_calories'
     * @param string $orderDir Direction du tri : 'ASC' ou 'DESC'
     * @return array Liste des recettes avec `nb_repas` et `total_calories`
     */
    public function getAllRecettesWithRepasCount(
        string $orderBy  = 'id_recette',
        string $orderDir = 'ASC'
    ): array {
        // Sécuriser les paramètres de tri contre les injections SQL
        $allowedCols = ['id_recette', 'total_calories', 'nb_repas', 'nom_recette'];
        $allowedDirs = ['ASC', 'DESC'];

        $col = in_array($orderBy, $allowedCols, true)  ? $orderBy  : 'id_recette';
        $dir = in_array(strtoupper($orderDir), $allowedDirs, true) ? strtoupper($orderDir) : 'ASC';

        return $this->pdo->query("
            SELECT
                rr.*,
                COUNT(r.id_repas)            AS nb_repas,
                COALESCE(SUM(r.calories), 0) AS total_calories
            FROM recette_repas rr
            LEFT JOIN repas r ON rr.id_recette = r.id_recette
            GROUP BY rr.id_recette
            ORDER BY $col $dir
        ")->fetchAll();
    }

    // ── READ ONE ──────────────────────────────────────────────────────────────

    /**
     * Récupère une recette par son identifiant.
     *
     * @param int $id Identifiant de la recette
     * @return array|false Données de la recette ou false si non trouvée
     */
    public function getRecetteById(int $id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM recette_repas WHERE id_recette = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // ── READ ONE avec tous ses repas et ingrédients (jointure complète) ───────

    /**
     * Récupère une recette avec tous ses repas et les ingrédients de chaque repas.
     *
     * Effectue 3 requêtes imbriquées :
     *  1. La recette elle-même
     *  2. Les repas liés à cette recette (INNER JOIN)
     *  3. Les ingrédients de chaque repas (via id_recette)
     *
     * @param int $id Identifiant de la recette
     * @return array  Tableau complet avec clés 'repas' et 'ingredients', ou [] si non trouvée
     */
    public function getRecetteWithRepas(int $id): array
    {
        // 1. Récupérer la recette principale
        $stmt = $this->pdo->prepare(
            "SELECT * FROM recette_repas WHERE id_recette = ?"
        );
        $stmt->execute([$id]);
        $recette = $stmt->fetch();

        // Retourner un tableau vide si la recette n'existe pas
        if (!$recette) return [];

        // 2. Récupérer tous les repas liés à cette recette avec les infos de la recette
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

        // 3. Pour chaque repas, récupérer ses ingrédients (liés via id_recette)
        foreach ($repas as &$rep) {
            $stmt3 = $this->pdo->prepare(
                "SELECT * FROM ingredient WHERE id_recette = ? ORDER BY id_ingredient"
            );
            $stmt3->execute([$id]); // Filtrer par id_recette
            $rep['ingredients'] = $stmt3->fetchAll();
        }

        // Attacher la liste des repas à la recette et retourner le tout
        $recette['repas'] = $repas;
        return $recette;
    }

    // ── UPDATE ────────────────────────────────────────────────────────────────

    /**
     * Met à jour une recette existante.
     *
     * Si une nouvelle image est fournie, elle est incluse dans la mise à jour.
     * Sinon, le champ image_recette n'est pas modifié.
     *
     * @param int         $id           Identifiant de la recette à modifier
     * @param string      $nom          Nouveau nom
     * @param string      $etapes       Nouvelles étapes
     * @param int|null    $tempsPrep    Nouveau temps de préparation
     * @param int|null    $tempsCuisson Nouveau temps de cuisson
     * @param string      $difficulte   Nouveau niveau de difficulté
     * @param int         $nbPersonnes  Nouveau nombre de personnes
     * @param string|null $image        Nouveau chemin d'image (null = pas de changement)
     * @return bool                     true si la mise à jour a réussi
     */
    public function updateRecette(
        int $id, string $nom, string $etapes = '', ?int $tempsPrep = null,
        ?int $tempsCuisson = null, string $difficulte = 'Facile',
        int $nbPersonnes = 2, ?string $image = null
    ): bool {
        if ($image !== null && $image !== '') {
            // Mise à jour avec changement d'image
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

        // Mise à jour sans modifier l'image existante
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

    /**
     * Supprime une recette par son identifiant.
     *
     * Note : les repas liés peuvent être affectés selon les contraintes
     * de clé étrangère définies en base de données.
     *
     * @param int $id Identifiant de la recette à supprimer
     * @return bool   true si la suppression a réussi
     */
    public function deleteRecette(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM recette_repas WHERE id_recette = ?"
        );
        return $stmt->execute([$id]);
    }

    // ── STATS ─────────────────────────────────────────────────────────────────

    /**
     * Retourne le nombre total de recettes en base de données.
     *
     * @return int Nombre de recettes
     */
    public function countRecettes(): int
    {
        return (int) $this->pdo->query(
            "SELECT COUNT(*) FROM recette_repas"
        )->fetchColumn();
    }
}
