<?php
require_once __DIR__ . '/../model/Recette.php';

$recetteModel = new Recette();
$action = $_GET['action'] ?? 'list';

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    if (!empty($nom)) {
        $recetteModel->addRecette($nom);
        header('Location: ../view/back/recette.php');
        exit;
    }
} elseif ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $nom = $_POST['nom'] ?? '';
    if (!empty($id) && !empty($nom)) {
        $recetteModel->updateRecette($id, $nom);
        header('Location: ../view/back/recette.php');
        exit;
    }
} elseif ($action === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $recetteModel->deleteRecette($id);
    header('Location: ../view/back/recette.php');
    exit;
}

$recettes = $recetteModel->getAllRecettes();
?>
