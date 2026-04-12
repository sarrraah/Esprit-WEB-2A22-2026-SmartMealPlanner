<?php
require_once '../../config.php';
require_once '../../model/Recette.php';

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
    <title>Gestion des Repas - Back Office</title>
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
                <li class="mb-2"><a href="recette.php" class="active">Gestion des Repas</a></li>
                <li class="mb-2"><a href="#">Gestion des Utilisateurs</a></li>
                <li class="mb-2"><a href="#">Aliments Durables</a></li>
                <li class="mb-2"><a href="#">Statistiques</a></li>
                <li class="mb-2"><a href="../index.php">Retour au Front Office</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content flex-grow-1 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Gestion des Repas</h1>
                <a href="add_recette.php" class="btn btn-success">Ajouter un repas</a>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Opération réalisée avec succès.</div>
            <?php endif; ?>

            <?php if (count($recipes) === 0): ?>
                <div class="alert alert-info">Aucun repas trouvé.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Calories</th>
                                <th>Ingrédients</th>
                                <th>Catégorie</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recipes as $recipe): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($recipe['id_repas']); ?></td>
                                    <td><?php echo htmlspecialchars($recipe['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($recipe['calories'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars(substr($recipe['description'], 0, 50)) . (strlen($recipe['description']) > 50 ? '...' : ''); ?></td>
                                    <td><?php echo htmlspecialchars($categoriesMap[$recipe['id_categorie']] ?? 'ID '.$recipe['id_categorie']); ?></td>
                                    <td>
                                        <a href="edit_recette.php?id=<?php echo $recipe['id_repas']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                                        <a href="../../controller/RecetteController.php?action=delete&id=<?php echo $recipe['id_repas']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce repas ?');">Supprimer</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>