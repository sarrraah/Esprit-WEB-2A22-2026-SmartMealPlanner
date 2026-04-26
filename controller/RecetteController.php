<?php
session_start();
defined('APP_ROOT') || require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Recette.php';

function recetteBaseUrl(): string {
    $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $project = str_replace('\\', '/', dirname(__DIR__));
    $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
    return $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);
}

function uploadRecetteImage(string $field): ?string {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) return null;
    $dir = __DIR__ . '/../uploads/recettes';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $filename = 'recette_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dir . '/' . $filename)) return null;
    return 'uploads/recettes/' . $filename;
}

function getRecetteFields(): array {
    return [
        'nom'          => trim($_POST['nom'] ?? ''),
        'etapes'       => trim($_POST['etapes'] ?? ''),
        'temps_prep'   => (isset($_POST['temps_prep']) && $_POST['temps_prep'] !== '') ? (int)$_POST['temps_prep'] : null,
        'temps_cuisson'=> (isset($_POST['temps_cuisson']) && $_POST['temps_cuisson'] !== '') ? (int)$_POST['temps_cuisson'] : null,
        'difficulte'   => $_POST['difficulte'] ?? 'Facile',
        'nb_personnes' => (int)($_POST['nb_personnes'] ?? 2),
    ];
}
/**
 * Validation des champs de la recette.
 */
function validateRecetteFields(array $f): array {
    $errors = [];

    // Nom : obligatoire, lettres uniquement, pas de chiffres
    if ($f['nom'] === '') {
        $errors['nom'] = 'Le nom de la recette est obligatoire.';
    } elseif (preg_match('/\d/', $f['nom'])) {
        $errors['nom'] = 'Le nom de la recette ne doit pas contenir de chiffres.';
    } elseif (!preg_match('/^[\p{L}\s\'\-\.]+$/u', $f['nom'])) {
        $errors['nom'] = 'Le nom de la recette ne doit contenir que des lettres.';
    }

    // Temps de préparation : entier positif si renseigné
    if ($f['temps_prep'] !== null && $f['temps_prep'] < 0) {
        $errors['temps_prep'] = 'Le temps de préparation doit être un nombre positif.';
    }

    // Temps de cuisson : entier positif si renseigné
    if ($f['temps_cuisson'] !== null && $f['temps_cuisson'] < 0) {
        $errors['temps_cuisson'] = 'Le temps de cuisson doit être un nombre positif.';
    }

    // Nombre de personnes : entier >= 1
    if ($f['nb_personnes'] < 1) {
        $errors['nb_personnes'] = 'Le nombre de personnes doit être au moins 1.';
    }

    return $errors;
}

$base   = recetteBaseUrl();
$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');
$list   = $base . '/view/back/recette.php';
$model  = new Recette();

// ADD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
    $f      = getRecetteFields();
    $errors = validateRecetteFields($f);

    if (!empty($errors)) {
        $_SESSION['recette_errors'] = $errors;
        $_SESSION['recette_old']    = $_POST;
        header('Location: ' . $base . '/view/back/add_recette.php?error=1');
        exit;
    }

    $image = uploadRecetteImage('image_recette');
    if ($f['nom'] !== '') {
        $newId = $model->addRecette($f['nom'], $f['etapes'], $f['temps_prep'], $f['temps_cuisson'], $f['difficulte'], $f['nb_personnes'], $image);
        header('Location: ' . $base . '/view/back/add_recette.php?id_recette=' . $newId);
    } else {
        header('Location: ' . $list . '?success=1');
    }
    exit;
}

// UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    $id     = (int)($_POST['id'] ?? 0);
    $f      = getRecetteFields();
    $errors = validateRecetteFields($f);

    if (!empty($errors)) {
        $_SESSION['recette_errors'] = $errors;
        $_SESSION['recette_old']    = $_POST;
        header('Location: ' . $base . '/view/back/edit_recette.php?id=' . $id . '&error=1');
        exit;
    }

    $current = $_POST['current_image'] ?? null;
    $new     = uploadRecetteImage('image_recette');
    $image   = $new ?: $current;
    if ($id > 0 && $f['nom'] !== '')
        $model->updateRecette($id, $f['nom'], $f['etapes'], $f['temps_prep'], $f['temps_cuisson'], $f['difficulte'], $f['nb_personnes'], $image);
    header('Location: ' . $list . '?success=1'); exit;
}

// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id > 0) $model->deleteRecette($id);
    header('Location: ' . $list . '?deleted=1'); exit;
}

header('Location: ' . $list); exit;
