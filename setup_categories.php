<?php
require_once 'config.php';

try {
    $pdo = config::getConnexion();

    // Check if categories exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM categorie_repas");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        // Insert default categories
        $categories = [
            ['id_categorie' => 1, 'nom_categorie' => 'Entrée'],
            ['id_categorie' => 2, 'nom_categorie' => 'Plat principal'],
            ['id_categorie' => 3, 'nom_categorie' => 'Dessert'],
            ['id_categorie' => 4, 'nom_categorie' => 'Boisson'],
        ];

        $stmt = $pdo->prepare("INSERT INTO categorie_repas (id_categorie, nom_categorie) VALUES (?, ?)");
        foreach ($categories as $cat) {
            $stmt->execute([$cat['id_categorie'], $cat['nom_categorie']]);
        }

        echo "Catégories insérées avec succès.";
    } else {
        echo "Les catégories existent déjà.";
    }
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>