<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Produit.php';

class ProduitController
{
    private function hasIdCategorieColumn()
    {
        $db = config::getConnexion();
        $stmt = $db->query("SHOW COLUMNS FROM produit LIKE 'id_categorie'");
        return $stmt->fetch() !== false;
    }

    public function listProduits()
    {
        $db = config::getConnexion();
        if ($this->hasIdCategorieColumn()) {
            $query = $db->query("
                SELECT p.*, c.nom AS categorie_nom
                FROM produit p
                LEFT JOIN categorieproduit c ON p.id_categorie = c.id_categorie
                ORDER BY p.id DESC
            ");
        } else {
            $query = $db->query("
                SELECT p.*, COALESCE(c.nom, p.categorie) AS categorie_nom
                FROM produit p
                LEFT JOIN categorieproduit c ON c.id_categorie = CAST(p.categorie AS UNSIGNED)
                ORDER BY p.id DESC
            ");
        }
        return $query->fetchAll();
    }

    public function listProduitsByCategorie($idCategorie)
    {
        $db = config::getConnexion();
        if ($this->hasIdCategorieColumn()) {
            $query = $db->prepare("
                SELECT p.*, c.nom AS categorie_nom
                FROM produit p
                LEFT JOIN categorieproduit c ON p.id_categorie = c.id_categorie
                WHERE p.id_categorie = :id_categorie
                ORDER BY p.id DESC
            ");
            $query->execute(['id_categorie' => $idCategorie]);
        } else {
            $query = $db->prepare("
                SELECT p.*, COALESCE(c.nom, p.categorie) AS categorie_nom
                FROM produit p
                LEFT JOIN categorieproduit c ON c.id_categorie = CAST(p.categorie AS UNSIGNED)
                WHERE c.id_categorie = :id_categorie
                ORDER BY p.id DESC
            ");
            $query->execute(['id_categorie' => $idCategorie]);
        }
        return $query->fetchAll();
    }

    public function searchProduits($statutFilter = 'Tous', $searchQuery = '')
    {
        if ($this->hasIdCategorieColumn()) {
            $sql = "
                SELECT p.*, c.nom AS categorie_nom
                FROM produit p
                LEFT JOIN categorieproduit c ON p.id_categorie = c.id_categorie
                WHERE 1 = 1
            ";
        } else {
            $sql = "
                SELECT p.*, COALESCE(c.nom, p.categorie) AS categorie_nom
                FROM produit p
                LEFT JOIN categorieproduit c ON c.id_categorie = CAST(p.categorie AS UNSIGNED)
                WHERE 1 = 1
            ";
        }
        $params = [];

        if ($statutFilter !== 'Tous') {
            $sql .= " AND p.statut = :statut";
            $params['statut'] = $statutFilter;
        }

        if ($searchQuery !== '') {
            $sql .= " AND p.nom LIKE :search";
            $params['search'] = '%' . $searchQuery . '%';
        }

        $sql .= " ORDER BY p.id DESC";

        $db = config::getConnexion();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getProduitById($id)
    {
        $db = config::getConnexion();
        if ($this->hasIdCategorieColumn()) {
            $sql = "SELECT * FROM produit WHERE id = :id";
        } else {
            $sql = "
                SELECT p.*,
                    (SELECT cp.id_categorie FROM categorieproduit cp WHERE cp.nom = p.categorie LIMIT 1) AS id_categorie
                FROM produit p
                WHERE p.id = :id
            ";
        }
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id, PDO::PARAM_INT);
        $req->execute();
        return $req->fetch() ?: null;
    }

    public function addProduit($produit)
    {
        $db = config::getConnexion();
        if ($this->hasIdCategorieColumn()) {
            $sql = "INSERT INTO produit
                    (nom, description, prix, quantiteStock, estDurable, dateExpiration, image, statut, id_categorie)
                    VALUES (:nom, :description, :prix, :quantiteStock, :estDurable, :dateExpiration, :image, :statut, :id_categorie)";
            $query = $db->prepare($sql);
            return $query->execute([
                'nom' => $produit->getNom(),
                'description' => $produit->getDescription(),
                'prix' => $produit->getPrix(),
                'quantiteStock' => $produit->getQuantiteStock(),
                'estDurable' => 0,
                'dateExpiration' => $produit->getDateExpiration(),
                'image' => $produit->getImage(),
                'statut' => $produit->getStatut(),
                'id_categorie' => $produit->getIdCategorie(),
            ]);
        }

        $categorieNom = 'Autre';
        if ($produit->getIdCategorie()) {
            $stmtCat = $db->prepare("SELECT nom FROM categorieproduit WHERE id_categorie = :id_categorie");
            $stmtCat->execute(['id_categorie' => $produit->getIdCategorie()]);
            $categorieNom = $stmtCat->fetchColumn() ?: 'Autre';
        }

        $sql = "INSERT INTO produit
                (nom, description, prix, quantiteStock, estDurable, dateExpiration, image, statut, categorie)
                VALUES (:nom, :description, :prix, :quantiteStock, :estDurable, :dateExpiration, :image, :statut, :categorie)";
        $query = $db->prepare($sql);
        return $query->execute([
            'nom' => $produit->getNom(),
            'description' => $produit->getDescription(),
            'prix' => $produit->getPrix(),
            'quantiteStock' => $produit->getQuantiteStock(),
            'estDurable' => 0,
            'dateExpiration' => $produit->getDateExpiration(),
            'image' => $produit->getImage(),
            'statut' => $produit->getStatut(),
            'categorie' => $produit->getIdCategorie(), // store the numeric ID
        ]);
    }

    public function updateProduit($produit, $id)
    {
        $db = config::getConnexion();
        if ($this->hasIdCategorieColumn()) {
            $query = $db->prepare(
                'UPDATE produit SET
                    nom = :nom,
                    description = :description,
                    quantiteStock = :quantiteStock,
                    dateExpiration = :dateExpiration,
                    id_categorie = :id_categorie,
                    prix = :prix,
                    image = :image,
                    statut = :statut
                WHERE id = :id'
            );

            return $query->execute([
                'id' => $id,
                'nom' => $produit->getNom(),
                'description' => $produit->getDescription(),
                'quantiteStock' => $produit->getQuantiteStock(),
                'dateExpiration' => $produit->getDateExpiration(),
                'id_categorie' => $produit->getIdCategorie(),
                'prix' => $produit->getPrix(),
                'image' => $produit->getImage(),
                'statut' => $produit->getStatut(),
            ]);
        }

        $categorieNom = 'Autre';
        if ($produit->getIdCategorie()) {
            $stmtCat = $db->prepare("SELECT nom FROM categorieproduit WHERE id_categorie = :id_categorie");
            $stmtCat->execute(['id_categorie' => $produit->getIdCategorie()]);
            $categorieNom = $stmtCat->fetchColumn() ?: 'Autre';
        }

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

        return $query->execute([
            'id' => $id,
            'nom' => $produit->getNom(),
            'description' => $produit->getDescription(),
            'quantiteStock' => $produit->getQuantiteStock(),
            'dateExpiration' => $produit->getDateExpiration(),
            'categorie' => $produit->getIdCategorie(), // store the numeric ID
            'prix' => $produit->getPrix(),
            'image' => $produit->getImage(),
            'statut' => $produit->getStatut(),
        ]);
    }

    public function deleteProduit($id)
    {
        $sql = "DELETE FROM produit WHERE id = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        return $req->execute(['id' => $id]);
    }
}
?>