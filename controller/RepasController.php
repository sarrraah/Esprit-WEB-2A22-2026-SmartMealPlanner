<?php
session_start();
defined('APP_ROOT') || require_once __DIR__ . '/../config.php';

// ── Helpers ───────────────────────────────────────────────────────────────────

function repasBaseUrl(): string {
    $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $project = str_replace('\\', '/', dirname(__DIR__));
    $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
    return $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);
}

function uploadRepasImage(string $field): ?string {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) return null;
    $dir = __DIR__ . '/../uploads/repas';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $filename = 'repas_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dir . '/' . $filename)) return null;
    return 'uploads/repas/' . $filename;
}

function deleteRepasFile(?string $path): void {
    if (empty($path) || strpos($path, 'uploads/repas/') !== 0) return;
    $abs = __DIR__ . '/../' . $path;
    if (is_file($abs)) @unlink($abs);
}

/**
 * Validation des champs du repas.
 * Retourne un tableau d'erreurs (vide = OK).
 */
function validateRepasFields(array $f): array {
    $errors = [];

    // Nom : obligatoire, lettres/espaces/accents/tirets uniquement, pas de chiffres
    if ($f['nom'] === '') {
        $errors['nom'] = 'Le nom du repas est obligatoire.';
    } elseif (preg_match('/\d/', $f['nom'])) {
        $errors['nom'] = 'Le nom du repas ne doit pas contenir de chiffres.';
    } elseif (!preg_match('/^[\p{L}\s\'\-\.]+$/u', $f['nom'])) {
        $errors['nom'] = 'Le nom du repas ne doit contenir que des lettres.';
    }

    // Calories : nombre positif si renseigné
    if ($f['calories'] !== null && $f['calories'] < 0) {
        $errors['calories'] = 'Les calories doivent être un nombre positif.';
    }

    // Protéines : nombre positif si renseigné
    if ($f['proteines'] !== null && $f['proteines'] < 0) {
        $errors['proteines'] = 'Les protéines doivent être un nombre positif.';
    }

    // Glucides : nombre positif si renseigné
    if ($f['glucides'] !== null && $f['glucides'] < 0) {
        $errors['glucides'] = 'Les glucides doivent être un nombre positif.';
    }

    // Lipides : nombre positif si renseigné
    if ($f['lipides'] !== null && $f['lipides'] < 0) {
        $errors['lipides'] = 'Les lipides doivent être un nombre positif.';
    }

    // Recette obligatoire
    if ($f['id_recette'] <= 0) {
        $errors['id_recette'] = 'Veuillez sélectionner une catégorie.';
    }

    return $errors;
}

function getRepasFields(): array {
    $f = function(string $k): ?float {
        $v = $_POST[$k] ?? '';
        return ($v !== '' && is_numeric($v)) ? (float)$v : null;
    };
    return [
        'nom'         => trim($_POST['nom'] ?? ''),
        'calories'    => $f('calories'),
        'proteines'   => $f('proteines'),
        'glucides'    => $f('glucides'),
        'lipides'     => $f('lipides'),
        'description' => trim($_POST['description'] ?? ''),
        'type_repas'  => $_POST['type_repas'] ?? 'Dejeuner',
        'id_recette'  => (int)($_POST['id_recette'] ?? 0),
    ];
}

// ── Init ──────────────────────────────────────────────────────────────────────

$pdo    = config::getConnexion();
$base   = repasBaseUrl();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$from   = $_POST['from'] ?? ($_GET['from'] ?? 'back');
$back   = $base . '/view/back/repas.php';
$front  = $base . '/view/front/repas.php';
$redir  = $from === 'front' ? $front : $back;

// ── ADD ───────────────────────────────────────────────────────────────────────
if ($method === 'POST' && $action === '') {
    $f      = getRepasFields();
    $errors = validateRepasFields($f);

    if (!empty($errors)) {
        $_SESSION['repas_errors'] = $errors;
        $_SESSION['repas_old']    = $_POST;
        $redirect = $from === 'back'
            ? $base . '/view/back/add_repas.php'
            : $base . '/view/front/add_repas.php';
        header('Location: ' . $redirect . '?error=1');
        exit;
    }

    $image = uploadRepasImage('image_repas');

    // SQL INSERT
    $stmt = $pdo->prepare("
        INSERT INTO repas
            (nom, calories, proteines, glucides, lipides,
             description, type_repas, id_recette, image_repas)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $f['nom'], $f['calories'], $f['proteines'], $f['glucides'],
        $f['lipides'], $f['description'], $f['type_repas'],
        $f['id_recette'], $image
    ]);

    $newId = (int) $pdo->lastInsertId();

    // ── Synchronisation automatique avec la recette ───────────────────────────
    // 1. Vérifier si une recette liée à id_recette existe
    $stmtRec = $pdo->prepare("SELECT * FROM recette_repas WHERE id_recette = ?");
    $stmtRec->execute([$f['id_recette']]);
    $recette = $stmtRec->fetch();

    if ($recette) {
        // 2. Si la recette n'a pas encore de photo et qu'on vient d'uploader une image → copier
        if (!empty($image) && empty($recette['image_recette'])) {
            // Copier l'image dans uploads/recettes/
            $srcPath  = __DIR__ . '/../' . $image;
            $destDir  = __DIR__ . '/../uploads/recettes';
            if (!is_dir($destDir)) mkdir($destDir, 0777, true);
            $ext      = pathinfo($image, PATHINFO_EXTENSION);
            $destFile = 'recette_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destPath = $destDir . '/' . $destFile;
            @copy($srcPath, $destPath);
            $recetteImage = 'uploads/recettes/' . $destFile;

            // Mettre à jour la photo de la recette
            $pdo->prepare("UPDATE recette_repas SET image_recette=? WHERE id_recette=?")
                ->execute([$recetteImage, $f['id_recette']]);
        }
    } else {
        // 3. Créer automatiquement une recette si elle n'existe pas encore
        $etapesAuto = "Recette générée automatiquement pour le repas : " . $f['nom'];
        $recetteImage = null;

        if (!empty($image)) {
            $srcPath  = __DIR__ . '/../' . $image;
            $destDir  = __DIR__ . '/../uploads/recettes';
            if (!is_dir($destDir)) mkdir($destDir, 0777, true);
            $ext      = pathinfo($image, PATHINFO_EXTENSION);
            $destFile = 'recette_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            @copy($srcPath, $destDir . '/' . $destFile);
            $recetteImage = 'uploads/recettes/' . $destFile;
        }

        $pdo->prepare("
            INSERT INTO recette_repas (nom_recette, etapes, difficulte, nb_personnes, image_recette)
            VALUES (?, ?, 'Facile', 2, ?)
        ")->execute([$f['nom'], $etapesAuto, $recetteImage]);
    }

    if ($from === 'back') {
        header('Location: ' . $base . '/view/back/repas.php?success=1');
    } else {
        header('Location: ' . $front . '?success=1');
    }
    exit;
}

// ── UPDATE ────────────────────────────────────────────────────────────────────
if ($method === 'POST' && $action === 'update') {
    $id     = (int)($_POST['id'] ?? 0);
    $f      = getRepasFields();
    $errors = validateRepasFields($f);

    if (!empty($errors)) {
        $_SESSION['repas_errors'] = $errors;
        $_SESSION['repas_old']    = $_POST;
        $redirect = $from === 'back'
            ? $base . '/view/back/edit_repas.php?id=' . $id
            : $base . '/view/front/edit_repas.php?id=' . $id;
        header('Location: ' . $redirect . '?error=1');
        exit;
    }

    $current = $_POST['current_image'] ?? null;
    $new     = uploadRepasImage('image_repas');
    $image   = $new ?: $current;

    if ($image !== null && $image !== '') {
        // SQL UPDATE avec image
        $stmt = $pdo->prepare("
            UPDATE repas
            SET nom=?, calories=?, proteines=?, glucides=?, lipides=?,
                description=?, type_repas=?, id_recette=?, image_repas=?
            WHERE id_repas=?
        ");
        $stmt->execute([
            $f['nom'], $f['calories'], $f['proteines'], $f['glucides'],
            $f['lipides'], $f['description'], $f['type_repas'],
            $f['id_recette'], $image, $id
        ]);
    } else {
        // SQL UPDATE sans image
        $stmt = $pdo->prepare("
            UPDATE repas
            SET nom=?, calories=?, proteines=?, glucides=?, lipides=?,
                description=?, type_repas=?, id_recette=?
            WHERE id_repas=?
        ");
        $stmt->execute([
            $f['nom'], $f['calories'], $f['proteines'], $f['glucides'],
            $f['lipides'], $f['description'], $f['type_repas'],
            $f['id_recette'], $id
        ]);
    }

    if ($new && $current && $new !== $current) deleteRepasFile($current);

    // ── Synchroniser la photo avec la recette ─────────────────────────────────
    if ($new && $f['id_recette'] > 0) {
        $srcPath = __DIR__ . '/../' . $new;
        $destDir = __DIR__ . '/../uploads/recettes';
        if (!is_dir($destDir)) mkdir($destDir, 0777, true);
        $ext      = pathinfo($new, PATHINFO_EXTENSION);
        $destFile = 'recette_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        @copy($srcPath, $destDir . '/' . $destFile);
        $recetteImage = 'uploads/recettes/' . $destFile;
        $pdo->prepare("UPDATE recette_repas SET image_recette=? WHERE id_recette=?")
            ->execute([$recetteImage, $f['id_recette']]);
    }

    header('Location: ' . $redir . '?success=1');
    exit;
}

// ── DELETE ────────────────────────────────────────────────────────────────────
if ($method === 'GET' && $action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // SQL SELECT pour récupérer l'image avant suppression
    $stmt = $pdo->prepare("SELECT image_repas FROM repas WHERE id_repas = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();

    // SQL DELETE
    $stmt = $pdo->prepare("DELETE FROM repas WHERE id_repas = ?");
    $stmt->execute([$id]);

    if ($item && !empty($item['image_repas'])) {
        deleteRepasFile($item['image_repas']);
    }

    header('Location: ' . $redir . '?deleted=1');
    exit;
}

// ── SEARCH BY RECETTE ─────────────────────────────────────────────────────────
// Equivalent workshop : afficherAlbums($idGenre)
if ($method === 'GET' && $action === 'byRecette' && isset($_GET['id_recette'])) {
    $idRecette = (int)$_GET['id_recette'];
    $repasParRecette = $model->afficherRepasByRecette($idRecette);
    $recettes        = $model->afficherToutesRecettes();

    // Passer les données à la vue
    $_SESSION['repas_par_recette'] = $repasParRecette;
    $_SESSION['recettes_list']     = $recettes;
    $_SESSION['recette_selectee']  = $idRecette;

    header('Location: ' . $base . '/view/back/search_repas.php');
    exit;
}

header('Location: ' . $back);
exit;
