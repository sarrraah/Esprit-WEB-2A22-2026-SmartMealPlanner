<?php
session_start();
defined('APP_ROOT') || require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Ingredient.php';

function ingredientBaseUrl(): string {
    $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $project = str_replace('\\', '/', dirname(__DIR__));
    $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
    return $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);
}

/**
 * Validation des champs d'un ingrédient.
 * Règles :
 *  - nom_ingredient : obligatoire, lettres uniquement, pas de chiffres
 *  - quantite       : nombre positif si renseigné, pas de lettres
 */
function validateIngredientFields(string $nom, $quantite): array {
    $errors = [];

    // Nom : obligatoire + lettres uniquement
    if ($nom === '') {
        $errors['nom_ingredient'] = "Le nom de l'ingrédient est obligatoire.";
    } elseif (preg_match('/\d/', $nom)) {
        $errors['nom_ingredient'] = "Le nom de l'ingrédient ne doit pas contenir de chiffres.";
    } elseif (!preg_match('/^[\p{L}\s\'\-\.]+$/u', $nom)) {
        $errors['nom_ingredient'] = "Le nom de l'ingrédient ne doit contenir que des lettres.";
    }

    // Quantité : nombre positif si renseigné
    if ($quantite !== null && $quantite < 0) {
        $errors['quantite'] = "La quantité doit être un nombre positif.";
    }

    return $errors;
}

$model    = new Ingredient();
$base     = ingredientBaseUrl();
$action   = $_GET['action'] ?? ($_POST['action'] ?? '');
$idRepas  = (int)($_GET['id_repas'] ?? $_POST['id_repas'] ?? 0);
$redirect = $_GET['redirect'] ?? ($_POST['redirect'] ?? '');
$back     = $redirect === 'add_repas'
    ? $base . '/view/back/add_repas.php?id_repas=' . $idRepas
    : $base . '/view/back/ingredients.php?id_repas=' . $idRepas;

// ── ADD ───────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
    $nom      = trim($_POST['nom_ingredient'] ?? '');
    $quantite = (isset($_POST['quantite']) && $_POST['quantite'] !== '') ? (float)$_POST['quantite'] : null;
    $unite    = trim($_POST['unite'] ?? 'g');
    $idRepas  = (int)($_POST['id_repas'] ?? 0);

    $errors = validateIngredientFields($nom, $quantite);

    if (!empty($errors)) {
        $_SESSION['ing_errors'] = $errors;
        $_SESSION['ing_old']    = $_POST;
        header('Location: ' . $back . '&error=1');
        exit;
    }

    if ($nom !== '' && $idRepas > 0) {
        // SQL INSERT ingrédient
        $pdo  = config::getConnexion();
        $stmt = $pdo->prepare(
            "INSERT INTO ingredient (nom_ingredient, quantite, unite, id_repas) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$nom, $quantite, $unite, $idRepas]);
    }

    header('Location: ' . $back . '&success=1');
    exit;
}

// ── UPDATE ────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    $id       = (int)($_POST['id_ingredient'] ?? 0);
    $nom      = trim($_POST['nom_ingredient'] ?? '');
    $quantite = (isset($_POST['quantite']) && $_POST['quantite'] !== '') ? (float)$_POST['quantite'] : null;
    $unite    = trim($_POST['unite'] ?? 'g');
    $idRepas  = (int)($_POST['id_repas'] ?? 0);

    $errors = validateIngredientFields($nom, $quantite);

    if (!empty($errors)) {
        $_SESSION['ing_errors'] = $errors;
        $_SESSION['ing_old']    = $_POST;
        header('Location: ' . $back . '&error=1&edit=' . $id);
        exit;
    }

    if ($id > 0 && $nom !== '') {
        // SQL UPDATE ingrédient
        $pdo  = config::getConnexion();
        $stmt = $pdo->prepare(
            "UPDATE ingredient SET nom_ingredient=?, quantite=?, unite=? WHERE id_ingredient=?"
        );
        $stmt->execute([$nom, $quantite, $unite, $id]);
    }

    header('Location: ' . $back . '&success=1');
    exit;
}

// ── DELETE ────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete' && isset($_GET['id'])) {
    $id      = (int)$_GET['id'];
    $idRepas = (int)($_GET['id_repas'] ?? 0);

    if ($id > 0) {
        // SQL DELETE ingrédient
        $pdo  = config::getConnexion();
        $stmt = $pdo->prepare("DELETE FROM ingredient WHERE id_ingredient = ?");
        $stmt->execute([$id]);
    }

    $back2 = $redirect === 'add_repas'
        ? $base . '/view/back/add_repas.php?id_repas=' . $idRepas
        : $base . '/view/back/ingredients.php?id_repas=' . $idRepas;

    header('Location: ' . $back2 . '&deleted=1');
    exit;
}

header('Location: ' . $back);
exit;
