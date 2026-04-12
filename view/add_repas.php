<?php
require_once '../config.php';
require_once '../model/Recette.php';

$recipeModel = new Recette();
$categories = $recipeModel->getCategories();
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$projectPath = str_replace('\\', '/', dirname(__DIR__));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$relativeProject = str_replace($docRoot, '', $projectPath);
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $relativeProject;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Repas - Smart Meal Planner</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Ajouter un Nouveau Repas</h1>
        <form action="<?php echo htmlspecialchars($baseUrl . '/controller/RecetteController.php', ENT_QUOTES, 'UTF-8'); ?>" method="POST">
            <input type="hidden" name="from" value="front">
            <div class="mb-3">
                <label for="nom" class="form-label">Nom du repas</label>
                <input type="text" class="form-control" id="nom" name="nom" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Ingrédients</label>
                <textarea class="form-control" id="description" name="description" rows="4" placeholder="Listez les ingrédients séparés par des virgules ou des sauts de ligne"></textarea>
            </div>
            <div class="mb-3">
                <label for="calories" class="form-label">Calories</label>
                <input type="number" step="0.1" class="form-control" id="calories" name="calories">
            </div>
            <div class="mb-3">
                <label for="id_categorie" class="form-label">Catégorie</label>
                <?php if (empty($categories)): ?>
                    <div class="alert alert-warning">Aucune catégorie trouvée. Veuillez d'abord ajouter des catégories dans le back office.</div>
                    <select class="form-control" id="id_categorie" name="id_categorie" disabled>
                        <option value="">Aucune catégorie disponible</option>
                    </select>
                <?php else: ?>
                    <select class="form-control" id="id_categorie" name="id_categorie" required>
                        <option value="">Sélectionnez une catégorie</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id_categorie']; ?>"><?php echo htmlspecialchars($cat['nom_categorie']); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary" <?php echo empty($categories) ? 'disabled' : ''; ?>>Ajouter le repas</button>
        </form>
    </div>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>