<?php
require_once __DIR__ . '/../../controller/ProduitController.php';

$id = (int) ($_GET['id'] ?? 0);
$produitController = new ProduitController();
$produit = $produitController->getProduitById($id);

if (!$produit) {
    header('Location: afficherProduit.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($produit['image']) && file_exists(UPLOAD_DIR . $produit['image'])) {
        unlink(UPLOAD_DIR . $produit['image']);
    }
    $produitController->deleteProduit($id);
    header('Location: afficherProduit.php');
    exit;
}

include("header.php");
?>
<section class="section">
<div class="container py-4">
    <div class="card">
        <div class="card-body">
            <h4>Confirmer la suppression</h4>
            <p>Produit : <strong><?= htmlspecialchars($produit['nom']) ?></strong></p>
            <form method="POST" class="d-flex gap-2">
                <button class="btn btn-danger">Oui, supprimer</button>
                <a href="afficherProduit.php" class="btn btn-outline-secondary">Annuler</a>
            </form>
        </div>
    </div>
</div>
</section>
<?php include("footer.php"); ?>
