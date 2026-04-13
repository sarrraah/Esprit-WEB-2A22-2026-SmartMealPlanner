<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Repas.php';

$recipeModel = new Repas();
$recipes = $recipeModel->getAllRepas();
$recettes = $recipeModel->getRecettes();
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$projectPath = str_replace('\\', '/', dirname(__DIR__, 2));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$relativeProject = str_replace($docRoot, '', $projectPath);
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $relativeProject;
$recettesMap = [];
foreach ($recettes as $cat) {
    $recettesMap[$cat['id_recette']] = $cat['nom_recette'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Recettes - Back Office</title>
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Gestion des Recettes</h1>
            <a href="add_recette.php" class="btn btn-success">Ajouter une recette</a>
        </div>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Operation realisee avec succes.</div>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th><th>Image</th><th>Nom</th><th>Calories</th><th>Ingredients</th><th>Recette</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recipes as $recipe): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($recipe['id_repas']); ?></td>
                        <td>
                            <?php if (!empty($recipe['image_repas'])): ?>
                                <img src="<?php echo htmlspecialchars($baseUrl . '/' . $recipe['image_repas'], ENT_QUOTES, 'UTF-8'); ?>" alt="Image recette" style="width:70px;height:70px;object-fit:cover;border-radius:8px;">
                            <?php else: ?><span class="text-muted">-</span><?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($recipe['nom']); ?></td>
                        <td><?php echo htmlspecialchars($recipe['calories'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars(substr($recipe['description'], 0, 50)) . (strlen($recipe['description']) > 50 ? '...' : ''); ?></td>
                        <td><?php echo htmlspecialchars($recettesMap[$recipe['id_recette']] ?? 'ID '.$recipe['id_recette']); ?></td>
                        <td>
                            <a href="edit_recette.php?id=<?php echo $recipe['id_repas']; ?>" class="btn btn-warning btn-sm">Modifier</a>
                            <a href="../../controller/RepasController.php?action=delete&id=<?php echo $recipe['id_repas']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette recette ?');">Supprimer</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
