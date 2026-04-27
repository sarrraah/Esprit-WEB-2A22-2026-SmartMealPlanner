<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Participation.php';

class ParticipationController
{
    function listParticipations()
    {
        $db    = config::getConnexion();
        $query = $db->query("SELECT * FROM participation ORDER BY date_participation DESC");
        $list  = [];
        foreach ($query->fetchAll() as $row) {
            $list[] = new Participation(
                $row['id_participation'],
                $row['id_event'],
                $row['nom'],
                $row['prenom'],
                $row['email'],
                $row['nombre_places_reservees'],
                $row['mode_paiement'],
                $row['statut'],
                $row['date_participation']
            );
        }
        return $list;
    }

    function listParticipationsByEvent($id_event)
    {
        $db  = config::getConnexion();
        $req = $db->prepare("SELECT * FROM participation WHERE id_event = :id ORDER BY date_participation DESC");
        $req->bindValue(':id', $id_event);
        $req->execute();
        $list = [];
        foreach ($req->fetchAll() as $row) {
            $list[] = new Participation(
                $row['id_participation'],
                $row['id_event'],
                $row['nom'],
                $row['prenom'],
                $row['email'],
                $row['nombre_places_reservees'],
                $row['mode_paiement'],
                $row['statut'],
                $row['date_participation']
            );
        }
        return $list;
    }

    function getParticipationById($id)
    {
        $db  = config::getConnexion();
        $req = $db->prepare("SELECT * FROM participation WHERE id_participation = :id");
        $req->bindValue(':id', $id);
        try {
            $req->execute();
            $row = $req->fetch();
            if (!$row) return null;
            return new Participation(
                $row['id_participation'],
                $row['id_event'],
                $row['nom'],
                $row['prenom'],
                $row['email'],
                $row['nombre_places_reservees'],
                $row['mode_paiement'],
                $row['statut'],
                $row['date_participation']
            );
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    function addParticipation($participation)
    {
        $sql = "INSERT INTO participation
                (id_event, nom, prenom, email, nombre_places_reservees, mode_paiement, statut, date_participation)
                VALUES (:id_event, :nom, :prenom, :email, :nombre_places_reservees, :mode_paiement, :statut, :date_participation)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_event'                => $participation->getIdEvent(),
                'nom'                     => $participation->getNom(),
                'prenom'                  => $participation->getPrenom(),
                'email'                   => $participation->getEmail(),
                'nombre_places_reservees' => $participation->getNombrePlacesReservees(),
                'mode_paiement'           => $participation->getModePaiement(),
                'statut'                  => $participation->getStatut(),
                'date_participation'      => $participation->getDateParticipation(),
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    function updateParticipation($participation, $id)
    {
        try {
            $db    = config::getConnexion();
            $query = $db->prepare(
                'UPDATE participation SET
                    id_event                = :id_event,
                    nom                     = :nom,
                    prenom                  = :prenom,
                    email                   = :email,
                    nombre_places_reservees = :nombre_places_reservees,
                    mode_paiement           = :mode_paiement,
                    statut                  = :statut,
                    date_participation      = :date_participation
                WHERE id_participation = :id'
            );
            $query->execute([
                'id'                      => $id,
                'id_event'                => $participation->getIdEvent(),
                'nom'                     => $participation->getNom(),
                'prenom'                  => $participation->getPrenom(),
                'email'                   => $participation->getEmail(),
                'nombre_places_reservees' => $participation->getNombrePlacesReservees(),
                'mode_paiement'           => $participation->getModePaiement(),
                'statut'                  => $participation->getStatut(),
                'date_participation'      => $participation->getDateParticipation(),
            ]);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    function deleteParticipation($id)
    {
        $db  = config::getConnexion();
        $req = $db->prepare("DELETE FROM participation WHERE id_participation = :id");
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    function countParticipationsByEvent($id_event)
    {
        $db  = config::getConnexion();
        $req = $db->prepare("SELECT COUNT(*) FROM participation WHERE id_event = :id");
        $req->bindValue(':id', $id_event);
        $req->execute();
        return (int) $req->fetchColumn();
    }

    function getParticipationsAvecEvenement($id_event)
    {
        $db  = config::getConnexion();
        $req = $db->prepare(
            "SELECT participation.*, evenement.titre, evenement.lieu, evenement.date_debut
             FROM participation
             INNER JOIN evenement ON participation.id_event = evenement.id_event
             WHERE participation.id_event = :id
             ORDER BY participation.date_participation DESC"
        );
        $req->bindValue(':id', $id_event);
        try {
            $req->execute();
            return $req->fetchAll();
        } catch (PDOException $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>