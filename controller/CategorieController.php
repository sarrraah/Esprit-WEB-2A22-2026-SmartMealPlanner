<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/CategorieProduit.php';

class CategorieController
{
    public function addCategorie($categorie)
    {
        $db = config::getConnexion();
        $query = $db->prepare("INSERT INTO categorieproduit (nom, description, image) VALUES (:nom, :description, :image)");
        return $query->execute([
            'nom'         => $categorie->getNom(),
            'description' => $categorie->getDescription(),
            'image'       => $categorie->getImage(),
        ]);
    }

    public function getAllCategories()
    {
        $db = config::getConnexion();
        $query = $db->query("SELECT * FROM categorieproduit ORDER BY nom ASC");
        return $query->fetchAll();
    }

    public function getCategorieById($id)
    {
        $db = config::getConnexion();
        $query = $db->prepare("SELECT * FROM categorieproduit WHERE id_categorie = :id_categorie");
        $query->execute(['id_categorie' => $id]);
        return $query->fetch() ?: null;
    }

    public function updateCategorie($categorie, $id)
    {
        $db = config::getConnexion();
        $query = $db->prepare("
            UPDATE categorieproduit
            SET nom = :nom, description = :description, image = :image
            WHERE id_categorie = :id_categorie
        ");
        return $query->execute([
            'id_categorie' => $id,
            'nom'          => $categorie->getNom(),
            'description'  => $categorie->getDescription(),
            'image'        => $categorie->getImage(),
        ]);
    }

    public function deleteCategorie($id)
    {
        $db = config::getConnexion();
        $query = $db->prepare("DELETE FROM categorieproduit WHERE id_categorie = :id_categorie");
        return $query->execute(['id_categorie' => $id]);
    }
}
?>
