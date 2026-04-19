<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../model/Produit.php';

class ProduitController
{
    function listProduits()
    {
        $db = config::getConnexion();
        $query = $db->query("SELECT * FROM produit ORDER BY id DESC");
        $rows = $query->fetchAll();
        $produits = [];
        foreach ($rows as $row) {
            $produits[] = new Produit(
                $row['id'],
                $row['nom'],
                $row['description'],
                $row['quantiteStock'],
                $row['dateExpiration'],
                $row['categorie'],
                $row['prix'],
                $row['image'],
                $row['statut']
            );
        }
        return $produits;
    }

    function getProduitById($id)
    {
        $sql = "SELECT * FROM produit WHERE id = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
            $row = $req->fetch();
            if (!$row) return null;
            return new Produit(
                $row['id'],
                $row['nom'],
                $row['description'],
                $row['quantiteStock'],
                $row['dateExpiration'],
                $row['categorie'],
                $row['prix'],
                $row['image'],
                $row['statut']
            );
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    function addProduit($produit)
    {
        $sql = "INSERT INTO produit
                VALUES (NULL, :nom, :description, :quantiteStock, :dateExpiration, :categorie, :prix, :image, :statut)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom' => $produit->getNom(),
                'description' => $produit->getDescription(),
                'quantiteStock' => $produit->getQuantiteStock(),
                'dateExpiration' => $produit->getDateExpiration(),
                'categorie' => $produit->getCategorie(),
                'prix' => $produit->getPrix(),
                'image' => $produit->getImage(),
                'statut' => $produit->getStatut(),
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    function updateProduit($produit, $id)
    {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE produit SET
                    nom = :nom,
                    description = :description,
                    quantiteStock = :quantiteStock,
                    dateExpiration = :dateExpiration,
                    categorie = :categorie,
                    prix = :prix,
                    image = :image,
                    statut = :statut
                WHERE id = :id'
            );

            $query->execute([
                'id' => $id,
                'nom' => $produit->getNom(),
                'description' => $produit->getDescription(),
                'quantiteStock' => $produit->getQuantiteStock(),
                'dateExpiration' => $produit->getDateExpiration(),
                'categorie' => $produit->getCategorie(),
                'prix' => $produit->getPrix(),
                'image' => $produit->getImage(),
                'statut' => $produit->getStatut(),
            ]);

            echo $query->rowCount() . " records UPDATED successfully <br>";
        } catch (PDOException $e) {
            $e->getMessage();
        }
    }

    function deleteProduit($id)
    {
        $sql = "DELETE FROM produit WHERE id = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);

        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>