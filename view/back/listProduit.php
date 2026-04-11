<?php
require_once "../../controller/ProduitController.php";

$controller = new ProduitController();
$produits = $controller->listProduits();
?>

<h2>Liste des Produits</h2>

<a href="addProduit.php">+ Ajouter Produit</a>

<table border="1">
    <tr>
        <th>ID</th>
        <th>Nom</th>
        <th>Prix</th>
        <th>Stock</th>
        <th>Actions</th>
    </tr>

    <?php foreach ($produits as $p) { ?>
        <tr>
            <td><?= $p->getId() ?></td>
            <td><?= $p->getNom() ?></td>
            <td><?= $p->getPrix() ?></td>
            <td><?= $p->getQuantiteStock() ?></td>
            <td>
                <a href="editProduit.php?id=<?= $p->getId() ?>">Edit</a>
                <a href="deleteProduit.php?id=<?= $p->getId() ?>">Delete</a>
            </td>
        </tr>
    <?php } ?>
</table>