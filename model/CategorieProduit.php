<?php
class CategorieProduit
{
    private $id_categorie;
    private $nom;
    private $description;
    private $image;

    public function __construct($id_categorie, $nom, $description, $image = null)
    {
        $this->id_categorie = $id_categorie;
        $this->nom = $nom;
        $this->description = $description;
        $this->image = $image;
    }

    public function getIdCategorie() { return $this->id_categorie; }
    public function getNom() { return $this->nom; }
    public function getDescription() { return $this->description; }
    public function getImage() { return $this->image; }

    public function setIdCategorie($id_categorie) { $this->id_categorie = $id_categorie; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setDescription($description) { $this->description = $description; }
    public function setImage($image) { $this->image = $image; }
}
?>
