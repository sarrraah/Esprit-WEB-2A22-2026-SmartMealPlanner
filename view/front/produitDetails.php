<?php
require_once "../../controller/ProduitController.php";

$controller = new ProduitController();

$id = $_GET['id'];
$p = $controller->getProduitById($id);
?>

<h1><?= $p->getNom() ?></h1>

<p>Description: <?= $p->getDescription() ?></p>
<p>Prix: <?= $p->getPrix() ?> TND</p>
<p>Stock: <?= $p->getQuantiteStock() ?></p>

<img src="<?= $p->getImage() ?>" width="200">