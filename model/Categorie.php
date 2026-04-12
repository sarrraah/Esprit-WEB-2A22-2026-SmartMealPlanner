<?php
require_once __DIR__ . '/../config.php';

class Categorie {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function addCategorie($nom) {
        $stmt = $this->pdo->prepare("INSERT INTO categorie_repas (id_categorie, nom_categorie) VALUES (NULL, ?)");
        return $stmt->execute([$nom]);
    }

    public function getAllCategories() {
        $stmt = $this->pdo->query("SELECT * FROM categorie_repas ORDER BY id_categorie");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategorieById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM categorie_repas WHERE id_categorie = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateCategorie($id, $nom) {
        $stmt = $this->pdo->prepare("UPDATE categorie_repas SET nom_categorie = ? WHERE id_categorie = ?");
        return $stmt->execute([$nom, $id]);
    }

    public function deleteCategorie($id) {
        $stmt = $this->pdo->prepare("DELETE FROM categorie_repas WHERE id_categorie = ?");
        return $stmt->execute([$id]);
    }
}
?>