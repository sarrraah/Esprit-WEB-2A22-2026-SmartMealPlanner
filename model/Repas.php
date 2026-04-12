<?php
class Recette {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    public function addRecette($nom, $calories, $description, $idCategorie) {
        $sql = "INSERT INTO repas (nom, calories, description, id_categorie) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nom, $calories, $description, $idCategorie]);
    }

    public function getAllRecettes() {
        $sql = "SELECT * FROM repas ORDER BY id_repas DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function deleteRecette($id) {
        $sql = "DELETE FROM repas WHERE id_repas = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
    }

    public function getRecetteById($id) {
        $sql = "SELECT * FROM repas WHERE id_repas = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateRecette($id, $nom, $calories, $description, $idCategorie) {
        $sql = "UPDATE repas SET nom = ?, calories = ?, description = ?, id_categorie = ? WHERE id_repas = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nom, $calories, $description, $idCategorie, $id]);
    }

    public function getCategories() {
        try {
            $sql = "SELECT * FROM categorie_repas ORDER BY id_categorie";
            $stmt = $this->pdo->query($sql);
        } catch (PDOException $e) {
            $sql = "SELECT * FROM categorie_repas ORDER BY id";
            $stmt = $this->pdo->query($sql);
        }

        $categories = $stmt->fetchAll();
        foreach ($categories as &$cat) {
            if (!isset($cat['id_categorie']) && isset($cat['id'])) {
                $cat['id_categorie'] = $cat['id'];
            }
            if (!isset($cat['nom_categorie']) && isset($cat['nom'])) {
                $cat['nom_categorie'] = $cat['nom'];
            }
        }
        return $categories;
    }
}
?>