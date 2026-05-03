<?php
/**
 * config.php — Classe de connexion à la base de données
 *
 * Utilise le pattern Singleton pour garantir une seule instance PDO
 * partagée dans toute l'application.
 */
class config
{
    /** @var PDO|null Instance unique de la connexion PDO (Singleton) */
    private static $pdo = null;

    /**
     * Retourne la connexion PDO unique à la base de données.
     * Si la connexion n'existe pas encore, elle est créée (lazy initialization).
     *
     * @return PDO Instance PDO prête à l'emploi
     */
    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            // Paramètres de connexion à la base de données
            $servername = "127.0.0.1";
            $username   = "root";
            $password   = "";
            $dbname     = "smart_meal_planner";

            try {
                // Création de la connexion PDO avec charset UTF-8 et timeout de 5s
                self::$pdo = new PDO(
                    "mysql:host=$servername;dbname=$dbname;charset=utf8",
                    $username,
                    $password,
                    [
                        PDO::ATTR_TIMEOUT => 5, // Timeout de connexion en secondes
                    ]
                );

                // Activer le mode exception pour les erreurs SQL
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Retourner les résultats sous forme de tableaux associatifs par défaut
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            } catch (Exception $e) {
                // Arrêt de l'application en cas d'échec de connexion
                die('Erreur : ' . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}
?>
