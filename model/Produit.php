<?php

class Produit
{
    private $id;
    private $nom;
    private $description;
    private $prix;
    private $quantiteStock;
    private $estDurable;
    private $dateExpiration;
    private $image;
    private $statut;
    private $id_categorie;

    public function __construct(
        $nom = '',
        $description = '',
        $prix = 0,
        $quantiteStock = 0,
        $estDurable = 0,
        $dateExpiration = null,
        $image = '',
        $statut = '',
        $id_categorie = null,
        $id = null
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->description = $description;
        $this->prix = $prix;
        $this->quantiteStock = $quantiteStock;
        $this->estDurable = $estDurable;
        $this->dateExpiration = $dateExpiration;
        $this->image = $image;
        $this->statut = $statut;
        $this->id_categorie = $id_categorie;
    }

    // GETTERS
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getDescription() { return $this->description; }
    public function getPrix() { return $this->prix; }
    public function getQuantiteStock() { return $this->quantiteStock; }
    public function getEstDurable() { return $this->estDurable; }
    public function getDateExpiration() { return $this->dateExpiration; }
    public function getImage() { return $this->image; }
    public function getStatut() { return $this->statut; }
    public function getIdCategorie() { return $this->id_categorie; }

    // SETTERS
    public function setId($id) { $this->id = $id; }
}
?>