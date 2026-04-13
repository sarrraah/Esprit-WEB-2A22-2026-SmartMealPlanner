<?php
require '../../controller/EvenementController.php';
if (isset($_GET['id'])) {
    $controller = new EvenementController();
    $controller->deleteEvenement($_GET['id']);
    header('Location: listEvenements.php');
    exit;
} else {
    echo "ID non fourni.";
}
?>