<?php
require_once "../../controller/ProduitController.php";

$controller = new ProduitController();
$produits = $controller->listProduits();
?>

<h1>Nos Produits</h1>

<div style="display:flex; gap:20px; flex-wrap:wrap;">

<?php foreach ($produits as $p) { ?>
    <div style="border:1px solid black; padding:10px; width:200px;">
        <h3><?= $p->getNom() ?></h3>
        <p><?= $p->getPrix() ?> TND</p>
        <p><?= $p->getDescription() ?></p>

        <a href="produitDetails.php?id=<?= $p->getId() ?>">
            Voir détails
        </a>
    </div>
<?php } ?>

</div>