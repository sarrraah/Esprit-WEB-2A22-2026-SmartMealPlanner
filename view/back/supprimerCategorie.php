<?php
require_once __DIR__ . '/../../controller/CategorieController.php';

$controller = new CategorieController();
$id = (int) ($_GET['id'] ?? 0);
$categorie = $controller->getCategorieById($id);
if (!$categorie) {
    header('Location: afficherCategorie.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->deleteCategorie($id);
    header('Location: afficherCategorie.php');
    exit;
}

include("header.php");
?>
<section class="section">
<div class="container py-4">
    <div class="card"><div class="card-body">
        <h4>Supprimer cette catégorie ?</h4>
        <p><strong><?= htmlspecialchars($categorie['nom']) ?></strong></p>
        <form method="POST" class="d-flex gap-2">
            <button class="btn btn-danger">Confirmer</button>
            <a href="afficherCategorie.php" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div></div>
</div>
</section>
<?php include("footer.php"); ?>
