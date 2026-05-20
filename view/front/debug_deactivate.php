<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>=== SESSION ===</h2><pre>"; print_r($_SESSION); echo "</pre>";

// Config
$configPath = __DIR__ . '/../../config.php';
echo "<h2>=== CONFIG ===</h2>";
echo "config.php path: $configPath<br>";
echo "config.php exists: " . (file_exists($configPath) ? '<span style="color:green">YES</span>' : '<span style="color:red">NO</span>') . "<br>";

if (file_exists($configPath)) {
    require_once $configPath;
    echo "config class exists: " . (class_exists('config') ? '<span style="color:green">YES</span>' : '<span style="color:red">NO</span>') . "<br>";
}

// DB + user
echo "<h2>=== DB + USER ===</h2>";
if (!isset($_SESSION['user_id'])) {
    echo "<span style='color:red'>No user_id in session — not logged in</span><br>";
} else {
    $userId = $_SESSION['user_id'];
    echo "user_id in session: $userId<br>";
    try {
        $pdo = config::getConnexion();
        echo "DB connected: <span style='color:green'>YES</span><br>";
        $stmt = $pdo->prepare("SELECT id, nom, prenom, email, role, statut FROM user WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<pre>"; print_r($user); echo "</pre>";
    } catch (Exception $e) {
        echo "<span style='color:red'>DB Error: " . $e->getMessage() . "</span><br>";
    }
}

// File checks
echo "<h2>=== FILE CHECKS ===</h2>";
$files = [
    'deactivate_account.php' => __DIR__ . '/deactivate_account.php',
    'auth.php'               => __DIR__ . '/auth.php',
    'header.php'             => __DIR__ . '/header.php',
    'footer.php'             => __DIR__ . '/footer.php',
    'main.css'               => __DIR__ . '/../assets/css/main.css',
];
foreach ($files as $label => $path) {
    $ok = file_exists($path);
    echo "$label: " . ($ok ? '<span style="color:green">EXISTS ✓</span>' : '<span style="color:red">MISSING ✗ — ' . $path . '</span>') . "<br>";
}

// Vendor scripts (should NOT be loaded locally — all via CDN)
echo "<h2>=== VENDOR SCRIPTS (should be CDN, not local) ===</h2>";
$vendorFiles = [
    'vendor/bootstrap/js' => __DIR__ . '/../assets/vendor/bootstrap/js/bootstrap.bundle.min.js',
    'vendor/aos/aos.js'   => __DIR__ . '/../assets/vendor/aos/aos.js',
    'vendor/aos/aos.css'  => __DIR__ . '/../assets/vendor/aos/aos.css',
];
foreach ($vendorFiles as $label => $path) {
    $ok = file_exists($path);
    echo "$label: " . ($ok ? '<span style="color:orange">EXISTS locally (ok)</span>' : '<span style="color:blue">NOT local — must use CDN ✓</span>') . "<br>";
}

// auth.php content check
echo "<h2>=== AUTH.PHP REQUIRE CHECK ===</h2>";
$authPath = __DIR__ . '/auth.php';
if (file_exists($authPath)) {
    $authContent = file_get_contents($authPath);
    echo "<pre>" . htmlspecialchars(substr($authContent, 0, 500)) . "</pre>";
} else {
    echo "<span style='color:red'>auth.php not found</span><br>";
}
?>
