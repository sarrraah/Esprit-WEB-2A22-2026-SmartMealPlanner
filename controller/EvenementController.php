<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Evenement.php';

class EvenementController
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = config::getConnexion();
    }

    public function showEvenement($evenement)
    {
        $evenement->show();
    }

    public function listEvenements()
    {
        $stmt = $this->pdo->query("SELECT * FROM evenement ORDER BY date_debut DESC");
        $rows = $stmt->fetchAll();
        $evenements = [];
        foreach ($rows as $row) {
            $evenements[] = new Evenement(
                $row['titre'],
                $row['description'],
                $row['date_debut'],
                $row['date_fin'],
                $row['lieu'],
                $row['capacite_max'],
                $row['prix'],
                $row['statut'],
                $row['type'],
                $row['id_event']
            );
        }
        return $evenements;
    }

    public function getEvenementById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM evenement WHERE id_event = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        return new Evenement(
            $row['titre'],
            $row['description'],
            $row['date_debut'],
            $row['date_fin'],
            $row['lieu'],
            $row['capacite_max'],
            $row['prix'],
            $row['statut'],
            $row['type'],
            $row['id_event']
        );
    }

    public function addEvenement($evenement)
    {
        $sql = "INSERT INTO evenement
                    (titre, description, date_debut, date_fin, lieu, capacite_max, prix, statut, type)
                VALUES
                    (:titre, :description, :date_debut, :date_fin, :lieu, :capacite_max, :prix, :statut, :type)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':titre'        => $evenement->getTitre(),
            ':description'  => $evenement->getDescription(),
            ':date_debut'   => $evenement->getDateDebut(),
            ':date_fin'     => $evenement->getDateFin(),
            ':lieu'         => $evenement->getLieu(),
            ':capacite_max' => $evenement->getCapaciteMax(),
            ':prix'         => $evenement->getPrix(),
            ':statut'       => $evenement->getStatut(),
            ':type'         => $evenement->getType(),
        ]);
        return $this->pdo->lastInsertId();
    }

    public function updateEvenement($evenement)
    {
        $sql = "UPDATE evenement SET
                    titre        = :titre,
                    description  = :description,
                    date_debut   = :date_debut,
                    date_fin     = :date_fin,
                    lieu         = :lieu,
                    capacite_max = :capacite_max,
                    prix         = :prix,
                    statut       = :statut,
                    type         = :type
                WHERE id_event   = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':titre'        => $evenement->getTitre(),
            ':description'  => $evenement->getDescription(),
            ':date_debut'   => $evenement->getDateDebut(),
            ':date_fin'     => $evenement->getDateFin(),
            ':lieu'         => $evenement->getLieu(),
            ':capacite_max' => $evenement->getCapaciteMax(),
            ':prix'         => $evenement->getPrix(),
            ':statut'       => $evenement->getStatut(),
            ':type'         => $evenement->getType(),
            ':id'           => $evenement->getIdEvent(),
        ]);
    }

    public function deleteEvenement($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM evenement WHERE id_event = :id");
        $stmt->execute([':id' => $id]);
    }
}
?>