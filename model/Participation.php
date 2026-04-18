<?php
class Participation
{
    private ?int    $id_participation  = null;
    private ?int    $id_event          = null;
    private ?string $nom_participant   = null;
    private ?string $statut            = null;
    private ?float  $montant           = null;
    private ?string $date_participation = null;

    public function __construct(
        $id = null,
        $id_event,
        $nom_participant,
        $statut,
        $montant,
        $date_participation
    ) {
        $this->id_participation   = $id;
        $this->id_event           = $id_event;
        $this->nom_participant    = $nom_participant;
        $this->statut             = $statut;
        $this->montant            = $montant;
        $this->date_participation = $date_participation;
    }

    public function getIdParticipation()
    {
        return $this->id_participation;
    }

    public function getIdEvent()
    {
        return $this->id_event;
    }

    public function setIdEvent($id_event)
    {
        $this->id_event = $id_event;
        return $this;
    }

    public function getNomParticipant()
    {
        return $this->nom_participant;
    }

    public function setNomParticipant($nom)
    {
        $this->nom_participant = $nom;
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

    public function getMontant()
    {
        return $this->montant;
    }

    public function setMontant($m)
    {
        $this->montant = $m;
        return $this;
    }

    public function getDateParticipation()
    {
        return $this->date_participation;
    }

    public function setDateParticipation($d)
    {
        $this->date_participation = $d;
        return $this;
    }
}
?>