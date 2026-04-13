<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Repas.php';

$recipeModel = new Repas();
$recettes = $recipeModel->getRecettes();
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-white border-bottom">
        <div class="container">
            <a class="navbar-brand fw-bold" href="home.php">Smart Meal Planner</a>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-success" href="home.php">Home</a>
                <a class="btn btn-success" href="repas.php">Repas</a>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Ajouter un Nouveau Repas</h1>
        <form action="<?php echo htmlspecialchars($baseUrl . '/controller/RepasController.php', ENT_QUOTES, 'UTF-8'); ?>" method="POST">
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
                <label for="id_recette" class="form-label">Recette</label>
                <?php if (empty($recettes)): ?>
                    <div class="alert alert-warning">Aucune recette trouvée. Veuillez d'abord ajouter des recettes dans le back office.</div>
                    <select class="form-control" id="id_recette" name="id_recette" disabled>
                        <option value="">Aucune recette disponible</option>
                    </select>
                <?php else: ?>
                    <select class="form-control" id="id_recette" name="id_recette" required>
                        <option value="">Sélectionnez une recette</option>
                        <?php foreach ($recettes as $cat): ?>
                            <option value="<?php echo $cat['id_recette']; ?>"><?php echo htmlspecialchars($cat['nom_recette']); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary" <?php echo empty($recettes) ? 'disabled' : ''; ?>>Ajouter le repas</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>