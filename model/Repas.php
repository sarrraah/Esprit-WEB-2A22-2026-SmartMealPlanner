<?php
class Repas {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
        $this->ensureImageColumnExists();
    }

    private function ensureImageColumnExists() {
        try {
            $stmt = $this->pdo->query("SHOW COLUMNS FROM repas LIKE 'image_repas'");
            $column = $stmt->fetch();
            if (!$column) {
                $this->pdo->exec("ALTER TABLE repas ADD COLUMN image_repas VARCHAR(255) NULL");
            }
        } catch (PDOException $e) {
            // Do not block application if migration fails.
        }
    }

    public function addRepas($nom, $calories, $description, $idRecette, $imageRepas = null) {
        try {
            $sql = "INSERT INTO repas (nom, calories, description, id_categorie, image_repas) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$nom, $calories, $description, $idRecette, $imageRepas]);
        } catch (PDOException $e) {
            $sql = "INSERT INTO repas (nom, calories, description, id_categorie) VALUES (?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$nom, $calories, $description, $idRecette]);
        }
    }

    public function getAllRepas() {
        $sql = "SELECT * FROM repas ORDER BY id_repas DESC";
        $stmt = $this->pdo->query($sql);
        $repas = $stmt->fetchAll();
        foreach ($repas as &$item) {
            if (!isset($item['id_recette']) && isset($item['id_categorie'])) {
                $item['id_recette'] = $item['id_categorie'];
            }
        }
        return $repas;
    }

    public function deleteRepas($id) {
        $sql = "DELETE FROM repas WHERE id_repas = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
    }

    public function getRepasById($id) {
        $sql = "SELECT * FROM repas WHERE id_repas = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateRepas($id, $nom, $calories, $description, $idRecette, $imageRepas = null) {
        if ($imageRepas !== null && $imageRepas !== '') {
            try {
                $sql = "UPDATE repas SET nom = ?, calories = ?, description = ?, id_categorie = ?, image_repas = ? WHERE id_repas = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$nom, $calories, $description, $idRecette, $imageRepas, $id]);
                return;
            } catch (PDOException $e) {
                // Fallback if image column does not exist.
            }
        }

        $sql = "UPDATE repas SET nom = ?, calories = ?, description = ?, id_categorie = ? WHERE id_repas = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nom, $calories, $description, $idRecette, $id]);
    }

    public function getRecettes() {
        try {
            $sql = "SELECT * FROM categorie_repas ORDER BY id_categorie";
            $stmt = $this->pdo->query($sql);
        } catch (PDOException $e) {
            $sql = "SELECT * FROM categorie_repas ORDER BY id";
            $stmt = $this->pdo->query($sql);
        }

        $recettes = $stmt->fetchAll();
        foreach ($recettes as &$cat) {
            if (!isset($cat['id_categorie']) && isset($cat['id'])) {
                $cat['id_categorie'] = $cat['id'];
            }
            if (!isset($cat['nom_categorie']) && isset($cat['nom'])) {
                $cat['nom_categorie'] = $cat['nom'];
            }
            if (!isset($cat['id_recette']) && isset($cat['id_categorie'])) {
                $cat['id_recette'] = $cat['id_categorie'];
            }
            if (!isset($cat['nom_recette']) && isset($cat['nom_categorie'])) {
                $cat['nom_recette'] = $cat['nom_categorie'];
            }
        }
        return $recettes;
    }
}
?>