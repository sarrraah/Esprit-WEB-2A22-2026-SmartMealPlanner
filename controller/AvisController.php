<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Avis.php';

class AvisController
{
    /**
     * Get all reviews, joined with product name, ordered by most recent.
     */
    public function getAllAvis()
    {
        $db = config::getConnexion();
        $stmt = $db->query("
            SELECT a.*, p.nom AS produit_nom
            FROM avis a
            LEFT JOIN produit p ON a.id_produit = p.id
            ORDER BY a.date_avis DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get all reviews for a specific product.
     */
    public function getAvisByProduit($id_produit)
    {
        $db   = config::getConnexion();
        $stmt = $db->prepare("
            SELECT * FROM avis
            WHERE id_produit = :id_produit
            ORDER BY date_avis DESC
        ");
        $stmt->execute(['id_produit' => $id_produit]);
        return $stmt->fetchAll();
    }

    /**
     * Get a single review by ID.
     */
    public function getAvisById($id_avis)
    {
        $db   = config::getConnexion();
        $stmt = $db->prepare("SELECT * FROM avis WHERE id_avis = :id_avis");
        $stmt->bindValue(':id_avis', $id_avis, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch() ?: null;
    }

    /**
     * Get average note for a product.
     */
    public function getAverageNote($id_produit)
    {
        $db   = config::getConnexion();
        $stmt = $db->prepare("SELECT AVG(note) AS avg_note, COUNT(*) AS total FROM avis WHERE id_produit = :id_produit");
        $stmt->execute(['id_produit' => $id_produit]);
        return $stmt->fetch();
    }

    /**
     * Add a new review.
     */
    public function addAvis($avis)
    {
        $db   = config::getConnexion();
        $stmt = $db->prepare("
            INSERT INTO avis (note, commentaire, date_avis, id_produit)
            VALUES (:note, :commentaire, :date_avis, :id_produit)
        ");
        return $stmt->execute([
            'note'        => $avis->getNote(),
            'commentaire' => $avis->getCommentaire(),
            'date_avis'   => $avis->getDateAvis(),
            'id_produit'  => $avis->getIdProduit(),
        ]);
    }

    /**
     * Update an existing review.
     */
    public function updateAvis($avis)
    {
        $db   = config::getConnexion();
        $stmt = $db->prepare("
            UPDATE avis SET
                note        = :note,
                commentaire = :commentaire,
                date_avis   = :date_avis,
                id_produit  = :id_produit
            WHERE id_avis = :id_avis
        ");
        return $stmt->execute([
            'id_avis'     => $avis->getIdAvis(),
            'note'        => $avis->getNote(),
            'commentaire' => $avis->getCommentaire(),
            'date_avis'   => $avis->getDateAvis(),
            'id_produit'  => $avis->getIdProduit(),
        ]);
    }

    /**
     * Delete a review by ID.
     */
    public function deleteAvis($id_avis)
    {
        $db   = config::getConnexion();
        $stmt = $db->prepare("DELETE FROM avis WHERE id_avis = :id_avis");
        return $stmt->execute(['id_avis' => $id_avis]);
    }
}
?>
