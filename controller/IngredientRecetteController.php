<?php
defined('APP_ROOT') || require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Ingredient.php';

function ingRecetteBaseUrl(): string {
    $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $project = str_replace('\\', '/', dirname(__DIR__));
    $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
    return $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);
}

$model     = new Ingredient();
$base      = ingRecetteBaseUrl();
$action    = $_GET['action'] ?? ($_POST['action'] ?? '');
$idRecette = (int)($_GET['id_recette'] ?? $_POST['id_recette'] ?? 0);
$back      = $base . '/view/back/add_recette.php?id_recette=' . $idRecette;

// ADD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
    $nom      = trim($_POST['nom_ingredient'] ?? '');
    $quantite = (isset($_POST['quantite']) && $_POST['quantite'] !== '') ? (float)$_POST['quantite'] : null;
    $unite    = trim($_POST['unite'] ?? 'g');
    if ($nom !== '' && $idRecette > 0) {
        $model->addIngredientToRecette($nom, $quantite, $unite, $idRecette);
    }
    header('Location: ' . $back . '&success=1'); exit;
}

// UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    $id       = (int)($_POST['id_ingredient'] ?? 0);
    $nom      = trim($_POST['nom_ingredient'] ?? '');
    $quantite = (isset($_POST['quantite']) && $_POST['quantite'] !== '') ? (float)$_POST['quantite'] : null;
    $unite    = trim($_POST['unite'] ?? 'g');
    if ($id > 0 && $nom !== '') {
        $model->updateIngredient($id, $nom, $quantite, $unite);
    }
    header('Location: ' . $back . '&success=1'); exit;
}

// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id > 0) $model->deleteIngredient($id);
    header('Location: ' . $back . '&deleted=1'); exit;
}

header('Location: ' . $back); exit;
