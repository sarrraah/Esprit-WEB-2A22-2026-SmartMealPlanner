<?php
require_once __DIR__ . '/../model/Categorie.php';

$categorieModel = new Categorie();
$action = $_GET['action'] ?? 'list';

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    if (!empty($nom)) {
        $categorieModel->addCategorie($nom);
        header('Location: ../view/back/categorie.php');
        exit;
    }
} elseif ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $nom = $_POST['nom'] ?? '';
    if (!empty($id) && !empty($nom)) {
        $categorieModel->updateCategorie($id, $nom);
        header('Location: ../view/back/categorie.php');
        exit;
    }
} elseif ($action === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $categorieModel->deleteCategorie($id);
    header('Location: ../view/back/categorie.php');
    exit;
}

$categories = $categorieModel->getAllCategories();
?>