<?php
require_once('../controller/EvenementController.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $controller = new EvenementController();
    $controller->deleteEvenement($id);

    // Redirection après suppression
    header('Location: listEvenements.php?msg=deleted');
    exit;
} else {
    echo "❌ ID non fourni.";
}