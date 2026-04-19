<?php
class Produit
{
    private $id;
    private $nom;
    private $description;
    private $quantiteStock;
    private $dateExpiration;
    private $categorie;
    private $prix;
    private $image;
    private $statut;

    public function __construct($id, $nom, $description, $quantiteStock, $dateExpiration, $categorie, $prix, $image, $statut)
    {
        $this->id = $id;
        $this->nom = $nom;
        $this->description = $description;
        $this->quantiteStock = $quantiteStock;
        $this->dateExpiration = $dateExpiration;
        $this->categorie = $categorie;
        $this->prix = $prix;
        $this->image = $image;
        $this->statut = $statut;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getDescription() { return $this->description; }
    public function getQuantiteStock() { return $this->quantiteStock; }
    public function getDateExpiration() { return $this->dateExpiration; }
    public function getCategorie() { return $this->categorie; }
    public function getPrix() { return $this->prix; }
    public function getImage() { return $this->image; }
    public function getStatut() { return $this->statut; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setDescription($description) { $this->description = $description; }
    public function setQuantiteStock($quantiteStock) { $this->quantiteStock = $quantiteStock; }
    public function setDateExpiration($dateExpiration) { $this->dateExpiration = $dateExpiration; }
    public function setCategorie($categorie) { $this->categorie = $categorie; }
    public function setPrix($prix) { $this->prix = $prix; }
    public function setImage($image) { $this->image = $image; }
    public function setStatut($statut) { $this->statut = $statut; }
}
?>