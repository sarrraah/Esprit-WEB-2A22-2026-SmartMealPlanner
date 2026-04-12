<?php
require_once '../config.php';
require_once '../model/Recette.php';

$recipe = new Recette();
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$projectPath = str_replace('\\', '/', dirname(__DIR__));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$relativeProject = str_replace($docRoot, '', $projectPath);
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $relativeProject;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] !== 'update')) {
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $calories = isset($_POST['calories']) ? floatval($_POST['calories']) : null;
    $idCategorie = isset($_POST['id_categorie']) ? intval($_POST['id_categorie']) : null;
    $from = isset($_POST['from']) ? $_POST['from'] : 'back';

    $recipe->addRecette($nom, $calories, $description, $idCategorie);

    $redirect = $from === 'front' ? $baseUrl . '/view/recette.php?success=1' : $baseUrl . '/view/back/recette.php?success=1';
    header('Location: ' . $redirect);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $recipe->deleteRecette($id);
    $redirect = isset($_GET['from']) && $_GET['from'] === 'front' ? $baseUrl . '/view/recette.php?deleted=1' : $baseUrl . '/view/back/recette.php?success=1';
    header('Location: ' . $redirect);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = intval($_POST['id']);
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $calories = isset($_POST['calories']) ? floatval($_POST['calories']) : null;
    $idCategorie = isset($_POST['id_categorie']) ? intval($_POST['id_categorie']) : null;
    $from = isset($_POST['from']) ? $_POST['from'] : 'back';

    $recipe->updateRecette($id, $nom, $calories, $description, $idCategorie);
    $redirect = $from === 'front' ? $baseUrl . '/view/recette.php?success=1' : $baseUrl . '/view/back/recette.php?success=1';
    header('Location: ' . $redirect);
    exit();
}
?>