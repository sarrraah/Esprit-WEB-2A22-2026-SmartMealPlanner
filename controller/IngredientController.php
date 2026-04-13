<?php
defined('APP_ROOT') || require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Ingredient.php';

function ingredientBaseUrl(): string {
    $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $project = str_replace('\\', '/', dirname(__DIR__));
    $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
    return $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);
}

$model  = new Ingredient();
$base   = ingredientBaseUrl();
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$idRepas = (int)($_GET['id_repas'] ?? $_POST['id_repas'] ?? 0);
$back   = $base . '/view/back/ingredients.php?id_repas=' . $idRepas;

// ADD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
    $nom      = trim($_POST['nom_ingredient'] ?? '');
    $quantite = (isset($_POST['quantite']) && $_POST['quantite'] !== '') ? (float)$_POST['quantite'] : null;
    $unite    = trim($_POST['unite'] ?? '');
    $idRepas  = (int)($_POST['id_repas'] ?? 0);
    if ($nom !== '' && $idRepas > 0) {
        $model->addIngredient($nom, $quantite, $unite, $idRepas);
    }
    header('Location: ' . $base . '/view/back/ingredients.php?id_repas=' . $idRepas . '&success=1');
    exit;
}

// UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    $id       = (int)($_POST['id_ingredient'] ?? 0);
    $nom      = trim($_POST['nom_ingredient'] ?? '');
    $quantite = (isset($_POST['quantite']) && $_POST['quantite'] !== '') ? (float)$_POST['quantite'] : null;
    $unite    = trim($_POST['unite'] ?? '');
    $idRepas  = (int)($_POST['id_repas'] ?? 0);
    if ($id > 0 && $nom !== '') {
        $model->updateIngredient($id, $nom, $quantite, $unite);
    }
    header('Location: ' . $base . '/view/back/ingredients.php?id_repas=' . $idRepas . '&success=1');
    exit;
}

// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete' && isset($_GET['id'])) {
    $id      = (int)$_GET['id'];
    $idRepas = (int)($_GET['id_repas'] ?? 0);
    if ($id > 0) $model->deleteIngredient($id);
    header('Location: ' . $base . '/view/back/ingredients.php?id_repas=' . $idRepas . '&deleted=1');
    exit;
}

header('Location: ' . $back); exit;
