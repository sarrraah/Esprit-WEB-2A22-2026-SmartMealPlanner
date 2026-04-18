<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Participation.php';
class ParticipationController
{
    function listParticipations()
    {
        $db = config::getConnexion();
        $query = $db->query("SELECT * FROM participation ORDER BY date_participation DESC");
        $rows = $query->fetchAll();
        $participations = [];
        foreach ($rows as $row) {
            $participations[] = new Participation(
                $row['id_participation'],
                $row['id_event'],
                $row['nom_participant'],
                $row['statut'],
                $row['montant'],
                $row['date_participation']
            );
        }
        return $participations;
    }

    function listParticipationsByEvent($id_event)
    {
        $db  = config::getConnexion();
        $req = $db->prepare("SELECT * FROM participation WHERE id_event = :id ORDER BY date_participation DESC");
        $req->bindValue(':id', $id_event);
        $req->execute();
        $rows = $req->fetchAll();
        $participations = [];
        foreach ($rows as $row) {
            $participations[] = new Participation(
                $row['id_participation'],
                $row['id_event'],
                $row['nom_participant'],
                $row['statut'],
                $row['montant'],
                $row['date_participation']
            );
        }
        return $participations;
    }

    function getParticipationById($id)
    {
        $sql = "SELECT * FROM participation WHERE id_participation = :id";
        $db  = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
            $row = $req->fetch();
            if (!$row) return null;
            return new Participation(
                $row['id_participation'],
                $row['id_event'],
                $row['nom_participant'],
                $row['statut'],
                $row['montant'],
                $row['date_participation']
            );
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    function addParticipation($participation)
    {
        $sql = "INSERT INTO participation
                VALUES (NULL, :id_event, :nom_participant, :statut, :montant, :date_participation)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_event'           => $participation->getIdEvent(),
                'nom_participant'    => $participation->getNomParticipant(),
                'statut'             => $participation->getStatut(),
                'montant'            => $participation->getMontant(),
                'date_participation' => $participation->getDateParticipation(),
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    function updateParticipation($participation, $id)
    {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE participation SET
                    id_event           = :id_event,
                    nom_participant    = :nom_participant,
                    statut             = :statut,
                    montant            = :montant,
                    date_participation = :date_participation
                WHERE id_participation = :id'
            );
            $query->execute([
                'id'                 => $id,
                'id_event'           => $participation->getIdEvent(),
                'nom_participant'    => $participation->getNomParticipant(),
                'statut'             => $participation->getStatut(),
                'montant'            => $participation->getMontant(),
                'date_participation' => $participation->getDateParticipation(),
            ]);
            echo $query->rowCount() . " records UPDATED successfully <br>";
        } catch (PDOException $e) {
            $e->getMessage();
        }
    }

    function deleteParticipation($id)
    {
        $sql = "DELETE FROM participation WHERE id_participation = :id";
        $db  = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>