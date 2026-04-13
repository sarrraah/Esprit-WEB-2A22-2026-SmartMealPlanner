<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../model/Evenement.php';
class EvenementController
{
    function listEvenements()
    {
        $db = config::getConnexion();
        $query = $db->query("SELECT * FROM evenement ORDER BY date_debut DESC");
        $rows = $query->fetchAll();
        $evenements = [];
        foreach ($rows as $row) {
            $evenements[] = new Evenement(
                $row['id_event'],
                $row['titre'],
                $row['description'],
                $row['date_debut'],
                $row['date_fin'],
                $row['lieu'],
                $row['capacite_max'],
                $row['prix'],
                $row['statut'],
                $row['type']
            );
        }
        return $evenements;
    }

    function getEvenementById($id)
    {
        $sql = "SELECT * FROM evenement WHERE id_event = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
            $row = $req->fetch();
            if (!$row) return null;
            return new Evenement(
                $row['id_event'],
                $row['titre'],
                $row['description'],
                $row['date_debut'],
                $row['date_fin'],
                $row['lieu'],
                $row['capacite_max'],
                $row['prix'],
                $row['statut'],
                $row['type']
            );
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    function addEvenement($evenement)
    {
        $sql = "INSERT INTO evenement
                VALUES (NULL, :titre, :description, :date_debut, :date_fin, :lieu, :capacite_max, :prix, :statut, :type)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre'        => $evenement->getTitre(),
                'description'  => $evenement->getDescription(),
                'date_debut'   => $evenement->getDateDebut(),
                'date_fin'     => $evenement->getDateFin(),
                'lieu'         => $evenement->getLieu(),
                'capacite_max' => $evenement->getCapaciteMax(),
                'prix'         => $evenement->getPrix(),
                'statut'       => $evenement->getStatut(),
                'type'         => $evenement->getType(),
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    function updateEvenement($evenement, $id)
    {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE evenement SET
                    titre        = :titre,
                    description  = :description,
                    date_debut   = :date_debut,
                    date_fin     = :date_fin,
                    lieu         = :lieu,
                    capacite_max = :capacite_max,
                    prix         = :prix,
                    statut       = :statut,
                    type         = :type
                WHERE id_event   = :id'
            );

            $query->execute([
                'id'           => $id,
                'titre'        => $evenement->getTitre(),
                'description'  => $evenement->getDescription(),
                'date_debut'   => $evenement->getDateDebut(),
                'date_fin'     => $evenement->getDateFin(),
                'lieu'         => $evenement->getLieu(),
                'capacite_max' => $evenement->getCapaciteMax(),
                'prix'         => $evenement->getPrix(),
                'statut'       => $evenement->getStatut(),
                'type'         => $evenement->getType(),
            ]);

            echo $query->rowCount() . " records UPDATED successfully <br>";
        } catch (PDOException $e) {
            $e->getMessage();
        }
    }

    function deleteEvenement($ide)
    {
        $sql = "DELETE FROM evenement WHERE id_event = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $ide);

        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>