<?php
require_once '../../config.php';
require_once '../../model/Recette.php';

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$projectPath = str_replace('\\', '/', dirname(__DIR__, 2));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$relativeProject = str_replace($docRoot, '', $projectPath);
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $relativeProject;

$recipeModel = new Recette();
$recipe = null;
$categories = $recipeModel->getCategories();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $recipe = $recipeModel->getRecetteById($id);
}

if (!$recipe) {
    header('Location: recette.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Repas - Back Office</title>
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
        }
        .sidebar a:hover {
            color: #ffc107;
        }
        .main-content {
            margin-left: 250px;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar p-3" style="width: 250px;">
            <h4 class="mb-4">Back Office</h4>
            <ul class="list-unstyled">
                <li class="mb-2"><a href="index.php">Tableau de Bord</a></li>
                <li class="mb-2"><a href="recette.php">Gestion des Repas</a></li>
                <li class="mb-2"><a href="#">Gestion des Utilisateurs</a></li>
                <li class="mb-2"><a href="#">Aliments Durables</a></li>
                <li class="mb-2"><a href="#">Statistiques</a></li>
                <li class="mb-2"><a href="../index.php">Retour au Front Office</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content flex-grow-1 p-4">
            <h1 class="mb-4">Modifier le Repas</h1>
<?php
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $relativeProject;
?>
            <form action="<?php echo htmlspecialchars($baseUrl . '/controller/RecetteController.php', ENT_QUOTES, 'UTF-8'); ?>" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?php echo $recipe['id_repas']; ?>">
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom du repas</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($recipe['nom']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Ingrédients</label>
                    <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($recipe['description']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="calories" class="form-label">Calories</label>
                    <input type="number" step="0.1" class="form-control" id="calories" name="calories" value="<?php echo htmlspecialchars($recipe['calories']); ?>">
                </div>
                <div class="mb-3">
                    <label for="id_categorie" class="form-label">Catégorie</label>
                    <select class="form-control" id="id_categorie" name="id_categorie" required>
                        <option value="">Sélectionnez une catégorie</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id_categorie']; ?>" <?php echo $recipe['id_categorie'] == $cat['id_categorie'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['nom_categorie']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Mettre à Jour</button>
                <a href="recette.php" class="btn btn-secondary ms-2">Annuler</a>
            </form>
        </div>
    </div>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>