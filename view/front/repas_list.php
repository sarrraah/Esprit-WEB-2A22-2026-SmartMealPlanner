<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Repas.php';

$recipeModel = new Repas();
$recipes = $recipeModel->getAllRepas();
$recettes = $recipeModel->getRecettes();
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
    <title>Gestion des Repas - Smart Meal Planner</title>
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
    <div class="container mt-5 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3">Gestion des Repas</h1>
                <p class="text-muted">Liste des repas enregistrés dans votre base de données.</p>
            </div>
            <div>
                <a href="add_repas.php" class="btn btn-success">Ajouter un repas</a>
                <a href="home.php" class="btn btn-secondary ms-2">Retour à l’accueil</a>
            </div>
        </div>

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
                            <th>Recette</th>
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
                                <td><?= htmlspecialchars($recettesMap[$recipe['id_recette']] ?? 'ID '.$recipe['id_recette']) ?></td>
                                <td class="text-center">
                                    <a href="../../controller/RepasController.php?action=delete&id=<?= urlencode($recipe['id_repas']) ?>&from=front" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce repas ?');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>