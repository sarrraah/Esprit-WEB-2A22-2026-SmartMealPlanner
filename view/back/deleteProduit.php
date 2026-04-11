<?php
require_once "../../controller/ProduitController.php";

$controller = new ProduitController();

if (isset($_GET['id'])) {
    $controller->deleteProduit($_GET['id']);
}

header("Location: listProduit.php");