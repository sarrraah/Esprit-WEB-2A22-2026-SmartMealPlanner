<?php
class Avis
{
    private $id_avis;
    private $note;
    private $commentaire;
    private $date_avis;
    private $id_produit;

    public function __construct($id_avis, $note, $commentaire, $date_avis, $id_produit)
    {
        $this->id_avis     = $id_avis;
        $this->note        = $note;
        $this->commentaire = $commentaire;
        $this->date_avis   = $date_avis;
        $this->id_produit  = $id_produit;
    }

    public function getIdAvis()     { return $this->id_avis; }
    public function getNote()       { return $this->note; }
    public function getCommentaire(){ return $this->commentaire; }
    public function getDateAvis()   { return $this->date_avis; }
    public function getIdProduit()  { return $this->id_produit; }

    public function setIdAvis($id_avis)         { $this->id_avis     = $id_avis; }
    public function setNote($note)              { $this->note        = $note; }
    public function setCommentaire($commentaire){ $this->commentaire = $commentaire; }
    public function setDateAvis($date_avis)     { $this->date_avis   = $date_avis; }
    public function setIdProduit($id_produit)   { $this->id_produit  = $id_produit; }
}
?>
