<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) session_start();

echo '<pre>';
echo "=== SESSION ===\n";
print_r($_SESSION);

echo "\n=== CONFIG ===\n";
$configPath = __DIR__ . '/../../config.php';
echo "config.php path: $configPath\n";
echo "config.php exists: " . (file_exists($configPath) ? 'YES' : 'NO') . "\n";

require_once $configPath;
echo "config class exists: " . (class_exists('config') ? 'YES' : 'NO') . "\n";

echo "\n=== DB CONNECTION ===\n";
try {
    $pdo = config::getConnexion();
    echo "DB connected: YES\n";
    
    $userId = $_SESSION['user_id'] ?? null;
    echo "user_id in session: " . ($userId ?? 'NOT SET') . "\n";
    
    if ($userId) {
        $stmt = $pdo->prepare("SELECT * FROM user WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "\n=== USER FROM DB ===\n";
        if ($user) {
            // Hide password
            $user['mot_de_passe'] = '[HIDDEN]';
            print_r($user);
        } else {
            echo "USER NOT FOUND for id=$userId\n";
        }
    }
} catch (Exception $e) {
    echo "DB ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== PROFILE.PHP REQUIRE CHAIN ===\n";
$profilePath = __DIR__ . '/profile.php';
echo "profile.php exists: " . (file_exists($profilePath) ? 'YES' : 'NO') . "\n";

// Check for PHP errors in profile.php by reading first 50 lines
$lines = file($profilePath);
echo "First require_once lines:\n";
foreach ($lines as $i => $line) {
    if (str_contains($line, 'require') || str_contains($line, 'include')) {
        echo "  Line " . ($i+1) . ": " . trim($line) . "\n";
    }
    if ($i > 60) break;
}

echo "\n=== CHECKING REQUIRED FILES ===\n";
$requires = [
    'config.php'                    => __DIR__ . '/../../config.php',
    'UserController.php'            => __DIR__ . '/../../controller/UserController.php',
    'header.php'                    => __DIR__ . '/header.php',
    'footer.php'                    => __DIR__ . '/footer.php',
    'auth.php'                      => __DIR__ . '/auth.php',
];
foreach ($requires as $name => $path) {
    echo "$name: " . (file_exists($path) ? 'EXISTS ✓' : 'MISSING ✗') . "\n";
}

echo "\n=== ROLE CHECK ===\n";
$role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? 'NOT SET';
echo "Role: $role\n";
echo "Is admin: " . (strtolower($role) === 'admin' ? 'YES' : 'NO') . "\n";

echo '</pre>';
?>
