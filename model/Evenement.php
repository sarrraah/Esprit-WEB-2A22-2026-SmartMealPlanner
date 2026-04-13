<?php
class Evenement
{
    private ?int    $id_event     = null;
    private ?string $titre        = null;
    private ?string $description  = null;
    private ?string $date_debut   = null;
    private ?string $date_fin     = null;
    private ?string $lieu         = null;
    private ?int    $capacite_max = null;
    private ?float  $prix         = null;
    private ?string $statut       = null;
    private ?string $type         = null;

    public function __construct($id = null, $t, $d, $dd, $df, $l, $cm, $p, $s, $ty)
    {
        $this->id_event     = $id;
        $this->titre        = $t;
        $this->description  = $d;
        $this->date_debut   = $dd;
        $this->date_fin     = $df;
        $this->lieu         = $l;
        $this->capacite_max = $cm;
        $this->prix         = $p;
        $this->statut       = $s;
        $this->type         = $ty;
    }

    public function getIdEvent()
    {
        return $this->id_event;
    }

    public function getTitre()
    {
        return $this->titre;
    }

    public function setTitre($t)
    {
        $this->titre = $t;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($d)
    {
        $this->description = $d;

        return $this;
    }

    public function getDateDebut()
    {
        return $this->date_debut;
    }

    public function setDateDebut($dd)
    {
        $this->date_debut = $dd;

        return $this;
    }

    public function getDateFin()
    {
        return $this->date_fin;
    }

    public function setDateFin($df)
    {
        $this->date_fin = $df;

        return $this;
    }

    public function getLieu()
    {
        return $this->lieu;
    }

    public function setLieu($l)
    {
        $this->lieu = $l;

        return $this;
    }

    public function getCapaciteMax()
    {
        return $this->capacite_max;
    }

    public function setCapaciteMax($cm)
    {
        $this->capacite_max = $cm;

        return $this;
    }

    public function getPrix()
    {
        return $this->prix;
    }

    public function setPrix($p)
    {
        $this->prix = $p;

        return $this;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($s)
    {
        $this->statut = $s;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($ty)
    {
        $this->type = $ty;

        return $this;
    }
}
?>