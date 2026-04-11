<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Produit.php';

class ProduitController
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = config::getConnexion();
    }

    // LIST
    public function listProduits()
    {
        $stmt = $this->pdo->query("SELECT * FROM produit ORDER BY id DESC");
        $rows = $stmt->fetchAll();

        $produits = [];

        foreach ($rows as $row) {
            $produits[] = new Produit(
                $row['nom'],
                $row['description'],
                $row['prix'],
                $row['quantiteStock'],
                $row['estDurable'],
                $row['dateExpiration'],
                $row['image'],
                $row['statut'],
                $row['id_categorie'],
                $row['id']
            );
        }

        return $produits;
    }

    // GET BY ID
    public function getProduitById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM produit WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return new Produit(
            $row['nom'],
            $row['description'],
            $row['prix'],
            $row['quantiteStock'],
            $row['estDurable'],
            $row['dateExpiration'],
            $row['image'],
            $row['statut'],
            $row['id_categorie'],
            $row['id']
        );
    }

    // ADD
    public function addProduit($p)
    {
        $sql = "INSERT INTO produit
        (nom, description, prix, quantiteStock, estDurable, dateExpiration, image, statut, id_categorie)
        VALUES
        (:nom, :description, :prix, :qte, :durable, :dateExp, :image, :statut, :cat)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $p->getNom(),
            ':description' => $p->getDescription(),
            ':prix' => $p->getPrix(),
            ':qte' => $p->getQuantiteStock(),
            ':durable' => $p->getEstDurable(),
            ':dateExp' => $p->getDateExpiration(),
            ':image' => $p->getImage(),
            ':statut' => $p->getStatut(),
            ':cat' => $p->getIdCategorie()
        ]);
    }

    // UPDATE
    public function updateProduit($p)
    {
        $sql = "UPDATE produit SET
        nom = :nom,
        description = :description,
        prix = :prix,
        quantiteStock = :qte,
        estDurable = :durable,
        dateExpiration = :dateExp,
        image = :image,
        statut = :statut,
        id_categorie = :cat
        WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $p->getNom(),
            ':description' => $p->getDescription(),
            ':prix' => $p->getPrix(),
            ':qte' => $p->getQuantiteStock(),
            ':durable' => $p->getEstDurable(),
            ':dateExp' => $p->getDateExpiration(),
            ':image' => $p->getImage(),
            ':statut' => $p->getStatut(),
            ':cat' => $p->getIdCategorie(),
            ':id' => $p->getId()
        ]);
    }

    // DELETE
    public function deleteProduit($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM produit WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}
?>