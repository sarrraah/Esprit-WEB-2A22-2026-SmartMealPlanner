<?php
/**
 * RepasController.php — Contrôleur CRUD pour les repas
 *
 * Gère toutes les opérations sur la table `repas` :
 *   - Ajout d'un repas (POST sans action)
 *   - Modification d'un repas (POST action=update)
 *   - Suppression d'un repas (GET action=delete)
 *   - Recherche par recette (GET action=byRecette)
 *
 * Gère également l'upload d'images et la synchronisation automatique
 * des photos avec la table `recette_repas`.
 */

session_start();
defined('APP_ROOT') || require_once __DIR__ . '/../config.php';

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Construit l'URL de base du projet de manière dynamique.
 * Fonctionne en HTTP et HTTPS, quel que soit l'emplacement du projet.
 *
 * @return string URL de base (ex: http://localhost/SmartMealPlanner)
 */
function repasBaseUrl(): string {
    $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $project = str_replace('\\', '/', dirname(__DIR__));
    $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
    return $scheme . '://' . $_SERVER['HTTP_HOST'] . str_replace($docRoot, '', $project);
}

/**
 * Gère l'upload d'une image pour un repas.
 *
 * Vérifie que le fichier est bien envoyé, valide l'extension,
 * génère un nom unique et déplace le fichier dans uploads/repas/.
 *
 * @param string $field Nom du champ <input type="file"> dans le formulaire
 * @return string|null  Chemin relatif de l'image (ex: uploads/repas/repas_xxx.jpg) ou null si échec
 */
function uploadRepasImage(string $field): ?string {
    // Vérifier que le fichier a bien été envoyé sans erreur
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;

    // Valider l'extension du fichier (formats autorisés)
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) return null;

    // Créer le dossier de destination s'il n'existe pas
    $dir = __DIR__ . '/../uploads/repas';
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    // Générer un nom de fichier unique avec timestamp + bytes aléatoires
    $filename = 'repas_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

    // Déplacer le fichier temporaire vers le dossier de destination
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dir . '/' . $filename)) return null;

    return 'uploads/repas/' . $filename;
}

/**
 * Supprime un fichier image d'un repas du serveur.
 *
 * Vérifie que le chemin est valide et appartient bien au dossier uploads/repas/
 * avant de supprimer pour éviter toute suppression accidentelle.
 *
 * @param string|null $path Chemin relatif de l'image à supprimer
 */
function deleteRepasFile(?string $path): void {
    // Ignorer si le chemin est vide ou ne correspond pas au dossier attendu
    if (empty($path) || strpos($path, 'uploads/repas/') !== 0) return;

    $abs = __DIR__ . '/../' . $path;

    // Supprimer le fichier s'il existe (@ supprime les warnings PHP)
    if (is_file($abs)) @unlink($abs);
}

/**
 * Valide les champs soumis dans le formulaire de repas.
 *
 * Règles de validation :
 *  - nom       : obligatoire, lettres/espaces/accents/tirets uniquement, sans chiffres
 *  - calories  : nombre positif si renseigné
 *  - proteines : nombre positif si renseigné
 *  - glucides  : nombre positif si renseigné
 *  - lipides   : nombre positif si renseigné
 *  - id_recette: doit être sélectionné (> 0)
 *
 * @param array $f Tableau des champs du formulaire (issu de getRepasFields())
 * @return array   Tableau d'erreurs indexé par nom de champ (vide = tout est valide)
 */
function validateRepasFields(array $f): array {
    $errors = [];

    // Validation du nom : obligatoire, sans chiffres, lettres uniquement
    if ($f['nom'] === '') {
        $errors['nom'] = 'Le nom du repas est obligatoire.';
    } elseif (preg_match('/\d/', $f['nom'])) {
        $errors['nom'] = 'Le nom du repas ne doit pas contenir de chiffres.';
    } elseif (!preg_match('/^[\p{L}\s\'\-\.]+$/u', $f['nom'])) {
        $errors['nom'] = 'Le nom du repas ne doit contenir que des lettres.';
    }

    // Validation des valeurs nutritionnelles (doivent être positives si renseignées)
    if ($f['calories'] !== null && $f['calories'] < 0) {
        $errors['calories'] = 'Les calories doivent être un nombre positif.';
    }

    if ($f['proteines'] !== null && $f['proteines'] < 0) {
        $errors['proteines'] = 'Les protéines doivent être un nombre positif.';
    }

    if ($f['glucides'] !== null && $f['glucides'] < 0) {
        $errors['glucides'] = 'Les glucides doivent être un nombre positif.';
    }

    if ($f['lipides'] !== null && $f['lipides'] < 0) {
        $errors['lipides'] = 'Les lipides doivent être un nombre positif.';
    }

    // Validation de la recette associée : sélection obligatoire
    if ($f['id_recette'] <= 0) {
        $errors['id_recette'] = 'Veuillez sélectionner une catégorie.';
    }

    return $errors;
}

/**
 * Extrait et nettoie les champs du formulaire repas depuis $_POST.
 *
 * Les valeurs numériques vides sont converties en null.
 * Les chaînes sont nettoyées avec trim().
 *
 * @return array Tableau associatif des champs du repas
 */
function getRepasFields(): array {
    // Fonction locale : convertit une valeur POST en float ou null si vide
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

// ── Initialisation ────────────────────────────────────────────────────────────

$pdo    = config::getConnexion();          // Connexion PDO
$base   = repasBaseUrl();                  // URL de base du projet
$method = $_SERVER['REQUEST_METHOD'];      // Méthode HTTP (GET ou POST)
$action = $_GET['action'] ?? ($_POST['action'] ?? ''); // Action demandée
$from   = $_POST['from'] ?? ($_GET['from'] ?? 'back'); // Origine : 'back' ou 'front'

// URLs de redirection selon l'interface (back-office ou front-office)
$back  = $base . '/view/back/repas.php';
$front = $base . '/view/front/repas.php';
$redir = $from === 'front' ? $front : $back;

// ── ADD : Ajout d'un repas ────────────────────────────────────────────────────
if ($method === 'POST' && $action === '') {

    // Récupérer et valider les champs du formulaire
    $f      = getRepasFields();
    $errors = validateRepasFields($f);

    // En cas d'erreur, stocker les messages en session et rediriger vers le formulaire
    if (!empty($errors)) {
        $_SESSION['repas_errors'] = $errors;
        $_SESSION['repas_old']    = $_POST; // Conserver les valeurs saisies pour pré-remplir le formulaire
        $redirect = $from === 'back'
            ? $base . '/view/back/add_repas.php'
            : $base . '/view/front/add_repas.php';
        header('Location: ' . $redirect . '?error=1');
        exit;
    }

    // Traiter l'upload de l'image du repas
    $image = uploadRepasImage('image_repas');

    // Insérer le nouveau repas en base de données
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

    // Récupérer l'id du repas nouvellement créé
    $newId = (int) $pdo->lastInsertId();

    // ── Synchronisation automatique avec la recette associée ─────────────────
    // Vérifier si une recette liée à id_recette existe déjà
    $stmtRec = $pdo->prepare("SELECT * FROM recette_repas WHERE id_recette = ?");
    $stmtRec->execute([$f['id_recette']]);
    $recette = $stmtRec->fetch();

    if ($recette) {
        // La recette existe : si elle n'a pas encore de photo et qu'une image vient d'être uploadée
        if (!empty($image) && empty($recette['image_recette'])) {
            // Copier l'image du repas dans le dossier uploads/recettes/
            $srcPath  = __DIR__ . '/../' . $image;
            $destDir  = __DIR__ . '/../uploads/recettes';
            if (!is_dir($destDir)) mkdir($destDir, 0777, true);

            $ext      = pathinfo($image, PATHINFO_EXTENSION);
            $destFile = 'recette_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destPath = $destDir . '/' . $destFile;
            @copy($srcPath, $destPath);
            $recetteImage = 'uploads/recettes/' . $destFile;

            // Mettre à jour la photo de la recette avec la copie
            $pdo->prepare("UPDATE recette_repas SET image_recette=? WHERE id_recette=?")
                ->execute([$recetteImage, $f['id_recette']]);
        }
    } else {
        // La recette n'existe pas encore : la créer automatiquement
        $etapesAuto   = "Recette générée automatiquement pour le repas : " . $f['nom'];
        $recetteImage = null;

        // Si une image a été uploadée, la copier pour la recette aussi
        if (!empty($image)) {
            $srcPath  = __DIR__ . '/../' . $image;
            $destDir  = __DIR__ . '/../uploads/recettes';
            if (!is_dir($destDir)) mkdir($destDir, 0777, true);

            $ext      = pathinfo($image, PATHINFO_EXTENSION);
            $destFile = 'recette_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            @copy($srcPath, $destDir . '/' . $destFile);
            $recetteImage = 'uploads/recettes/' . $destFile;
        }

        // Insérer la nouvelle recette avec des valeurs par défaut
        $pdo->prepare("
            INSERT INTO recette_repas (nom_recette, etapes, difficulte, nb_personnes, image_recette)
            VALUES (?, ?, 'Facile', 2, ?)
        ")->execute([$f['nom'], $etapesAuto, $recetteImage]);
    }

    // Rediriger vers la liste avec un message de succès
    if ($from === 'back') {
        header('Location: ' . $base . '/view/back/repas.php?success=1');
    } else {
        header('Location: ' . $front . '?success=1');
    }
    exit;
}

// ── UPDATE : Modification d'un repas ─────────────────────────────────────────
if ($method === 'POST' && $action === 'update') {
    $id     = (int)($_POST['id'] ?? 0);
    $f      = getRepasFields();
    $errors = validateRepasFields($f);

    // En cas d'erreur de validation, rediriger vers le formulaire d'édition
    if (!empty($errors)) {
        $_SESSION['repas_errors'] = $errors;
        $_SESSION['repas_old']    = $_POST;
        $redirect = $from === 'back'
            ? $base . '/view/back/edit_repas.php?id=' . $id
            : $base . '/view/front/edit_repas.php?id=' . $id;
        header('Location: ' . $redirect . '?error=1');
        exit;
    }

    // Gérer l'image : utiliser la nouvelle si uploadée, sinon conserver l'ancienne
    $current = $_POST['current_image'] ?? null; // Image actuelle en base
    $new     = uploadRepasImage('image_repas'); // Nouvelle image uploadée (ou null)
    $image   = $new ?: $current;               // Priorité à la nouvelle image

    if ($image !== null && $image !== '') {
        // Mise à jour avec image
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
        // Mise à jour sans modifier l'image
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

    // Supprimer l'ancienne image du serveur si une nouvelle a été uploadée
    if ($new && $current && $new !== $current) deleteRepasFile($current);

    // ── Synchroniser la nouvelle photo avec la recette associée ──────────────
    if ($new && $f['id_recette'] > 0) {
        // Copier la nouvelle image dans le dossier des recettes
        $srcPath = __DIR__ . '/../' . $new;
        $destDir = __DIR__ . '/../uploads/recettes';
        if (!is_dir($destDir)) mkdir($destDir, 0777, true);

        $ext      = pathinfo($new, PATHINFO_EXTENSION);
        $destFile = 'recette_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        @copy($srcPath, $destDir . '/' . $destFile);
        $recetteImage = 'uploads/recettes/' . $destFile;

        // Mettre à jour la photo de la recette correspondante
        $pdo->prepare("UPDATE recette_repas SET image_recette=? WHERE id_recette=?")
            ->execute([$recetteImage, $f['id_recette']]);
    }

    header('Location: ' . $redir . '?success=1');
    exit;
}

// ── DELETE : Suppression d'un repas ──────────────────────────────────────────
if ($method === 'GET' && $action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Récupérer le chemin de l'image avant suppression pour pouvoir la supprimer du serveur
    $stmt = $pdo->prepare("SELECT image_repas FROM repas WHERE id_repas = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();

    // Supprimer le repas de la base de données
    $stmt = $pdo->prepare("DELETE FROM repas WHERE id_repas = ?");
    $stmt->execute([$id]);

    // Supprimer le fichier image associé s'il existe
    if ($item && !empty($item['image_repas'])) {
        deleteRepasFile($item['image_repas']);
    }

    header('Location: ' . $redir . '?deleted=1');
    exit;
}

// ── SEARCH BY RECETTE : Recherche des repas par recette ──────────────────────
// Équivalent workshop : afficherAlbums($idGenre)
if ($method === 'GET' && $action === 'byRecette' && isset($_GET['id_recette'])) {
    $idRecette = (int)$_GET['id_recette'];

    // Récupérer les repas filtrés par recette et la liste complète des recettes
    $repasParRecette = $model->afficherRepasByRecette($idRecette);
    $recettes        = $model->afficherToutesRecettes();

    // Stocker les résultats en session pour les transmettre à la vue
    $_SESSION['repas_par_recette'] = $repasParRecette;
    $_SESSION['recettes_list']     = $recettes;
    $_SESSION['recette_selectee']  = $idRecette; // Mémoriser la recette sélectionnée

    header('Location: ' . $base . '/view/back/search_repas.php');
    exit;
}

// Redirection par défaut vers la liste des repas (back-office)
header('Location: ' . $back);
exit;
