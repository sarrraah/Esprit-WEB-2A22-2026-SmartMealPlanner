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

} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Function to automatically determine product status
function determinerStatut($quantiteStock, $dateExpiration) {
    $dateActuelle = new DateTime();
    $dateExp = DateTime::createFromFormat('Y-m-d', $dateExpiration);
    
    if ($dateExp && $dateExp < $dateActuelle) {
        return 'Expired';
    } else if ($quantiteStock == 0) {
        return 'Out of Stock';
    } else if ($quantiteStock > 0) {
        return 'Available';
    }
    return 'Unknown';
}

// Direction du dossier d'uploads
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', '/ryhem/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/uploads/');

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
                    "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
                    $username,
                    $password
                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$pdo->exec("SET NAMES utf8mb4");
            } catch (Exception $e) {
                die('Erreur : ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
?>