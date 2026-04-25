<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Evenement.php';

class EvenementController
{
    function listEvenements()
    {
        $db    = config::getConnexion();
        $query = $db->query("SELECT * FROM evenement ORDER BY date_debut DESC");
        $rows  = $query->fetchAll();
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
                $row['type'],
                $row['image'] ?? null
            );
        }
        return $evenements;
    }

    function getEvenementById($id)
    {
        $sql = "SELECT * FROM evenement WHERE id_event = :id";
        $db  = config::getConnexion();
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
                $row['type'],
                $row['image'] ?? null
            );
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    function addEvenement($evenement)
    {
        $sql = "INSERT INTO evenement
                VALUES (NULL, :titre, :description, :date_debut, :date_fin, :lieu, :capacite_max, :prix, :statut, :type, :image)";
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
                'image'        => $evenement->getImage(),
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    function updateEvenement($evenement, $id)
    {
        try {
            $db    = config::getConnexion();
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
                    type         = :type,
                    image        = :image
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
                'image'        => $evenement->getImage(),
            ]);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    function deleteEvenement($ide)
    {
        $sql = "DELETE FROM evenement WHERE id_event = :id";
        $db  = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $ide);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // ── Utilitaire upload image ──────────────────────────────────────────
    function uploadImage($file, $oldImage = null)
{
    $uploadDir = __DIR__ . '/../uploads/evenements/';

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize      = 5 * 1024 * 1024;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => "Erreur lors de l upload."];
    }
    if (!in_array($file['type'], $allowedTypes)) {
        return ['error' => "Format non autorise (jpg, png, gif, webp)."];
    }
    if ($file['size'] > $maxSize) {
        return ['error' => "Image trop lourde (max 5 Mo)."];
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('event_', true) . '.' . $ext;

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        return ['error' => "Impossible de deplacer le fichier."];
    }

    if ($oldImage && file_exists($uploadDir . $oldImage)) {
        unlink($uploadDir . $oldImage);
    }

    return ['success' => $filename];
}
}
?>