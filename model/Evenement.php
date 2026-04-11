<?php
class Evenement
{
    private $id_event;
    private $titre;
    private $description;
    private $date_debut;
    private $date_fin;
    private $lieu;
    private $capacite_max;
    private $prix;
    private $statut;
    private $type;

    // ── Constructeur paramétré ──────────────────────────────────────────────
    public function __construct(
        $titre        = '',
        $description  = '',
        $date_debut   = '',
        $date_fin     = '',
        $lieu         = '',
        $capacite_max = 0,
        $prix         = 0.0,
        $statut       = '',
        $type         = '',
        $id_event     = null
    ) {
        $this->id_event     = $id_event;
        $this->titre        = $titre;
        $this->description  = $description;
        $this->date_debut   = $date_debut;
        $this->date_fin     = $date_fin;
        $this->lieu         = $lieu;
        $this->capacite_max = $capacite_max;
        $this->prix         = $prix;
        $this->statut       = $statut;
        $this->type         = $type;
    }

    // ── Getters ─────────────────────────────────────────────────────────────
    public function getIdEvent()     { return $this->id_event; }
    public function getTitre()       { return $this->titre; }
    public function getDescription() { return $this->description; }
    public function getDateDebut()   { return $this->date_debut; }
    public function getDateFin()     { return $this->date_fin; }
    public function getLieu()        { return $this->lieu; }
    public function getCapaciteMax() { return $this->capacite_max; }
    public function getPrix()        { return $this->prix; }
    public function getStatut()      { return $this->statut; }
    public function getType()        { return $this->type; }

    // ── Setters ─────────────────────────────────────────────────────────────
    public function setIdEvent($id_event)         { $this->id_event     = $id_event; }
    public function setTitre($titre)              { $this->titre        = $titre; }
    public function setDescription($description)  { $this->description  = $description; }
    public function setDateDebut($date_debut)     { $this->date_debut   = $date_debut; }
    public function setDateFin($date_fin)         { $this->date_fin     = $date_fin; }
    public function setLieu($lieu)                { $this->lieu         = $lieu; }
    public function setCapaciteMax($capacite_max) { $this->capacite_max = $capacite_max; }
    public function setPrix($prix)                { $this->prix         = $prix; }
    public function setStatut($statut)            { $this->statut       = $statut; }
    public function setType($type)                { $this->type         = $type; }

    // ── Méthode show() ──────────────────────────────────────────────────────
    public function show()
    {
        echo '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse;width:100%;">
                <tr style="background:#4A90D9;color:#fff;">
                    <th>Champ</th><th>Valeur</th>
                </tr>
                <tr><td><b>ID</b></td><td>' . htmlspecialchars($this->id_event ?? 'N/A') . '</td></tr>
                <tr><td><b>Titre</b></td><td>' . htmlspecialchars($this->titre) . '</td></tr>
                <tr><td><b>Description</b></td><td>' . htmlspecialchars($this->description) . '</td></tr>
                <tr><td><b>Date Début</b></td><td>' . htmlspecialchars($this->date_debut) . '</td></tr>
                <tr><td><b>Date Fin</b></td><td>' . htmlspecialchars($this->date_fin) . '</td></tr>
                <tr><td><b>Lieu</b></td><td>' . htmlspecialchars($this->lieu) . '</td></tr>
                <tr><td><b>Capacité Max</b></td><td>' . htmlspecialchars($this->capacite_max) . '</td></tr>
                <tr><td><b>Prix</b></td><td>' . htmlspecialchars($this->prix) . ' TND</td></tr>
                <tr><td><b>Statut</b></td><td>' . htmlspecialchars($this->statut) . '</td></tr>
                <tr><td><b>Type</b></td><td>' . htmlspecialchars($this->type) . '</td></tr>
              </table>';
    }
}
?>
