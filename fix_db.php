<?php
defined('APP_ROOT') || define('APP_ROOT', __DIR__);
require_once __DIR__ . '/config.php';

$pdo = config::getConnexion();
$steps = [];

try {
    // Désactiver les vérifications FK temporairement
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $steps[] = "✅ FK checks désactivés.";

    // Supprimer TOUTES les FK sur la table ingredient
    $fks = $pdo->query("
        SELECT CONSTRAINT_NAME
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'ingredient'
          AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($fks as $fk) {
        $pdo->exec("ALTER TABLE ingredient DROP FOREIGN KEY `$fk`");
        $steps[] = "✅ FK '$fk' supprimée.";
    }

    // Rendre id_repas nullable
    $pdo->exec("ALTER TABLE ingredient MODIFY COLUMN id_repas INT NULL DEFAULT NULL");
    $steps[] = "✅ id_repas rendu nullable.";

    // Ajouter id_recette si manquante
    $cols = $pdo->query("SHOW COLUMNS FROM ingredient")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('id_recette', $cols)) {
        $pdo->exec("ALTER TABLE ingredient ADD COLUMN id_recette INT NULL DEFAULT NULL");
        $steps[] = "✅ Colonne id_recette ajoutée.";
    } else {
        $steps[] = "ℹ️ Colonne id_recette déjà présente.";
    }

    // Ajouter FK sur id_recette (vers recette_repas)
    $existingFks = $pdo->query("
        SELECT CONSTRAINT_NAME
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'ingredient'
          AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('fk_ingredient_recette', $existingFks)) {
        $pdo->exec("
            ALTER TABLE ingredient
            ADD CONSTRAINT fk_ingredient_recette
            FOREIGN KEY (id_recette) REFERENCES recette_repas(id_recette)
            ON DELETE CASCADE ON UPDATE CASCADE
        ");
        $steps[] = "✅ FK fk_ingredient_recette ajoutée.";
    } else {
        $steps[] = "ℹ️ FK fk_ingredient_recette déjà présente.";
    }

    // Réactiver les vérifications FK
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    $steps[] = "✅ FK checks réactivés.";

    // Afficher la structure finale
    $cols = $pdo->query("SHOW COLUMNS FROM ingredient")->fetchAll();
    $colNames = array_column($cols, 'Field');
    $steps[] = "📋 Colonnes : " . implode(', ', $colNames);

    echo "<div style='font-family:sans-serif;max-width:640px;margin:2rem auto;padding:2rem;background:#d1f2eb;border-radius:12px;border:2px solid #198754;'>";
    echo "<h2 style='color:#198754;margin-top:0'>✅ Base de données corrigée !</h2>";
    echo "<ul style='line-height:2;'>";
    foreach ($steps as $s) echo "<li>$s</li>";
    echo "</ul>";
    echo "<div style='margin-top:1.5rem;display:flex;gap:1rem;flex-wrap:wrap;'>";
    echo "<a href='view/back/recette.php' style='background:#ce1212;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:600;'>→ Gérer les Recettes</a>";
    echo "<a href='view/back/index.php' style='background:#1a1f2e;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:600;'>→ Back Office</a>";
    echo "</div></div>";

} catch (PDOException $e) {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "<div style='font-family:sans-serif;max-width:640px;margin:2rem auto;padding:2rem;background:#fde8e8;border-radius:12px;border:2px solid #ce1212;'>";
    echo "<h2 style='color:#ce1212;margin-top:0'>❌ Erreur</h2>";
    echo "<p><strong>" . htmlspecialchars($e->getMessage()) . "</strong></p>";
    echo "<ul>";
    foreach ($steps as $s) echo "<li>$s</li>";
    echo "</ul>";
    echo "</div>";
}
?>
