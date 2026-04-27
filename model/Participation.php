<?php
class Participation {
    private $id_participation;
    private $id_event;
    private $nom;
    private $prenom;
    private $email;
    private $nombre_places_reservees;
    private $mode_paiement;
    private $statut;
    private $date_participation;

    public function __construct($id_participation, $id_event, $nom, $prenom, $email,
                                $nombre_places_reservees, $mode_paiement, $statut, $date_participation) {
        $this->id_participation        = $id_participation;
        $this->id_event                = $id_event;
        $this->nom                     = $nom;
        $this->prenom                  = $prenom;
        $this->email                   = $email;
        $this->nombre_places_reservees = $nombre_places_reservees;
        $this->mode_paiement           = $mode_paiement;
        $this->statut                  = $statut;
        $this->date_participation      = $date_participation;
    }

    public function getIdParticipation()       { return $this->id_participation; }
    public function getIdEvent()               { return $this->id_event; }
    public function getNom()                   { return $this->nom; }
    public function getPrenom()                { return $this->prenom; }
    public function getEmail()                 { return $this->email; }
    public function getNombrePlacesReservees() { return $this->nombre_places_reservees; }
    public function getModePaiement()          { return $this->mode_paiement; }
    public function getStatut()                { return $this->statut; }
    public function getDateParticipation()     { return $this->date_participation; }
}
?>