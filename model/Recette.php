<?php
require_once __DIR__ . '/../config.php';

class Recette {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function addRecette($nom) {
        $stmt = $this->pdo->prepare("INSERT INTO recette_repas (id_recette, nom_recette) VALUES (NULL, ?)");
        return $stmt->execute([$nom]);
    }

    public function getAllRecettes() {
        $stmt = $this->pdo->query("SELECT * FROM recette_repas ORDER BY id_recette");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecetteById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM recette_repas WHERE id_recette = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateRecette($id, $nom) {
        $stmt = $this->pdo->prepare("UPDATE recette_repas SET nom_recette = ? WHERE id_recette = ?");
        return $stmt->execute([$nom, $id]);
    }

    public function deleteRecette($id) {
        $stmt = $this->pdo->prepare("DELETE FROM recette_repas WHERE id_recette = ?");
        return $stmt->execute([$id]);
    }
}
?>
