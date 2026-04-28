<?php
defined('APP_ROOT') || define('APP_ROOT', __DIR__);
require_once __DIR__ . '/config.php';

$pdo = config::getConnexion();

// Lister toutes les tables
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

// Tables utilisées par le projet
$tablesUtilisees = ['repas', 'recette_repas', 'ingredient'];

// Tables inutilisées
$tablesInutilisees = array_diff($tables, $tablesUtilisees);

echo "<div style='font-family:sans-serif;max-width:700px;margin:2rem auto;padding:2rem;'>";
echo "<h2>Tables en base de données</h2>";

echo "<h3 style='color:green'>✅ Tables utilisées (" . count($tablesUtilisees) . ")</h3>";
echo "<ul>";
foreach ($tablesUtilisees as $t) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo "<li><strong>$t</strong> — $count enregistrement(s)</li>";
}
echo "</ul>";

if (!empty($tablesInutilisees)) {
    echo "<h3 style='color:red'>❌ Tables inutilisées (" . count($tablesInutilisees) . ")</h3>";
    echo "<ul>";
    foreach ($tablesInutilisees as $t) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "<li><strong>$t</strong> — $count enregistrement(s)</li>";
    }
    echo "</ul>";

    echo "<form method='POST'>";
    echo "<input type='hidden' name='confirm' value='1'>";
    echo "<button type='submit' style='background:#ce1212;color:#fff;padding:10px 20px;border:none;border-radius:8px;cursor:pointer;font-size:1rem;'>";
    echo "🗑️ Supprimer les tables inutilisées";
    echo "</button>";
    echo "</form>";
} else {
    echo "<p style='color:green'>Aucune table inutilisée.</p>";
}

// Suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    foreach ($tablesInutilisees as $t) {
        $pdo->exec("DROP TABLE IF EXISTS `$t`");
        echo "<p style='color:green'>✅ Table <strong>$t</strong> supprimée.</p>";
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "<p><a href='view/back/index.php' style='background:#1a1f2e;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;'>→ Back Office</a></p>";
}

echo "</div>";
?>
