<?php
require_once __DIR__ . '/../../controller/ProduitController.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $controller = new ProduitController();
    $controller->deleteProduit($id);
}

header('Location: afficherProduit.php?msg=deleted');
exit;
