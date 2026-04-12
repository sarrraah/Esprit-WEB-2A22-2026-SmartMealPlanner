<?php
require_once '../config.php';
require_once '../model/Recette.php';

$recipeModel = new Recette();
$recipes = $recipeModel->getAllRecettes();
$categories = $recipeModel->getCategories();
$categoriesMap = [];
foreach ($categories as $cat) {
    $categoriesMap[$cat['id_categorie']] = $cat['nom_categorie'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Repas - Smart Meal Planner</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3">Gestion des Repas</h1>
                <p class="text-muted">Liste des repas enregistrés dans votre base de données.</p>
            </div>
            <div>
                <a href="add_recette.php" class="btn btn-success">Ajouter un repas</a>
                <a href="index.php" class="btn btn-secondary ms-2">Retour à l’accueil</a>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">L’opération a été réalisée avec succès.</div>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Le repas a été supprimé avec succès.</div>
        <?php endif; ?>

        <?php if (count($recipes) === 0): ?>
            <div class="alert alert-info">Aucun repas trouvé. Ajoutez un repas pour commencer.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Calories</th>
                            <th>Ingrédients</th>
                            <th>Catégorie</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recipes as $recipe): ?>
                            <tr>
                                <td><?= htmlspecialchars($recipe['id_repas']) ?></td>
                                <td><?= htmlspecialchars($recipe['nom']) ?></td>
                                <td><?= htmlspecialchars($recipe['calories'] ?? '-') ?></td>
                                <td><?= nl2br(htmlspecialchars($recipe['description'])) ?></td>
                                <td><?= htmlspecialchars($categoriesMap[$recipe['id_categorie']] ?? 'ID '.$recipe['id_categorie']) ?></td>
                                <td class="text-center">
                                    <a href="../controller/RecetteController.php?action=delete&id=<?= urlencode($recipe['id_repas']) ?>&from=front" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce repas ?');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>