<?php
// Configuration PDO MySQL pour Smart Meal Planner

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'smart_meal_planner');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Modifier la colonne categorie pour être VARCHAR si elle est encore INT
    try {
        // Vérifier si la colonne id_categorie existe et est une foreign key
        $result = $pdo->query("SHOW COLUMNS FROM produit LIKE 'id_categorie'");
        if ($result->rowCount() > 0) {
            // Supprimer la foreign key si elle existe
            $pdo->exec("ALTER TABLE produit DROP FOREIGN KEY fk_produit_categorie");
            // Changer la colonne
            $pdo->exec("ALTER TABLE produit CHANGE id_categorie categorie VARCHAR(100) DEFAULT 'Autre'");
        }
    } catch (Exception $e) {
        // Ignorer si la colonne est déjà modifiée ou erreur
    }
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Fonction pour déterminer le statut automatiquement
function determinerStatut($quantiteStock, $dateExpiration) {
    $dateActuelle = new DateTime();
    $dateExp = DateTime::createFromFormat('Y-m-d', $dateExpiration);
    
    if ($dateExp && $dateExp < $dateActuelle) {
        return 'Épuisé';
    } else if ($quantiteStock == 0) {
        return 'Rupture';
    } else if ($quantiteStock > 0) {
        return 'Disponible';
    }
    return 'Inconnu';
}

// Direction du dossier d'uploads
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', '/ryhem/view/back/uploads/');

// Créer le dossier s'il n'existe pas
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Extensions autorisées pour les images
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$maxFileSize = 5 * 1024 * 1024; // 5 MB
?>
<?php
class config
{
    private static $pdo = null;

    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            $servername = "localhost";
            $username   = "root";
            $password   = "";
            $dbname     = "smart_meal_planner";
            try {
                self::$pdo = new PDO(
                    "mysql:host=$servername;dbname=$dbname;charset=utf8",
                    $username,
                    $password
                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                die('Erreur : ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
?>