<?php
class Produit
{
    private $id;
    private $nom;
    private $description;
    private $quantiteStock;
    private $dateExpiration;
    private $id_categorie;
    private $prix;
    private $image;
    private $statut;

    public function __construct($id, $nom, $description, $quantiteStock, $dateExpiration, $id_categorie, $prix, $image, $statut)
    {
        $this->id = $id;
        $this->nom = $nom;
        $this->description = $description;
        $this->quantiteStock = $quantiteStock;
        $this->dateExpiration = $dateExpiration;
        $this->id_categorie = $id_categorie;
        $this->prix = $prix;
        $this->image = $image;
        $this->statut = $statut;
    }

    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getDescription() { return $this->description; }
    public function getQuantiteStock() { return $this->quantiteStock; }
    public function getDateExpiration() { return $this->dateExpiration; }
    public function getIdCategorie() { return $this->id_categorie; }
    public function getPrix() { return $this->prix; }
    public function getImage() { return $this->image; }
    public function getStatut() { return $this->statut; }

    public function setId($id) { $this->id = $id; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setDescription($description) { $this->description = $description; }
    public function setQuantiteStock($quantiteStock) { $this->quantiteStock = $quantiteStock; }
    public function setDateExpiration($dateExpiration) { $this->dateExpiration = $dateExpiration; }
    public function setIdCategorie($id_categorie) { $this->id_categorie = $id_categorie; }
    public function setPrix($prix) { $this->prix = $prix; }
    public function setImage($image) { $this->image = $image; }
    public function setStatut($statut) { $this->statut = $statut; }
}
?>