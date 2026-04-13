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
                <li class="mb-2"><a href="repas.php" class="active">Gestion des Repas</a></li>
                <li class="mb-2"><a href="recette.php">Gestion des Recettes</a></li>
                <li class="mb-2"><a href="utilisateurs.php">Gestion des Utilisateurs</a></li>
                <li class="mb-2"><a href="aliments_durables.php">Aliments Durables</a></li>
                <li class="mb-2"><a href="statistiques.php">Statistiques</a></li>
                <li class="mb-2"><a href="contenu_nutritionnel.php">Contenu Nutritionnel</a></li>
                <li class="mb-2"><a href="../../index.php">Retour au Front Office</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content flex-grow-1 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Gestion des Repas</h1>
                <a href="add_recette.php" class="btn btn-success">Ajouter une recette</a>
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
                                <th>Image</th>
                                <th>Nom</th>
                                <th>Calories</th>
                                <th>Ingrédients</th>
                                <th>Recette</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recipes as $recipe): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($recipe['id_repas']); ?></td>
                                    <td>
                                        <?php if (!empty($recipe['image_repas'])): ?>
                                            <img src="<?php echo htmlspecialchars($baseUrl . '/' . $recipe['image_repas'], ENT_QUOTES, 'UTF-8'); ?>" alt="Image repas" style="width:70px;height:70px;object-fit:cover;border-radius:8px;">
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($recipe['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($recipe['calories'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars(substr($recipe['description'], 0, 50)) . (strlen($recipe['description']) > 50 ? '...' : ''); ?></td>
                                    <td><?php echo htmlspecialchars($recettesMap[$recipe['id_recette']] ?? 'ID '.$recipe['id_recette']); ?></td>
                                    <td class="text-center">
                                        <a href="edit_recette.php?id=<?php echo $recipe['id_repas']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                                        <a href="../../controller/RepasController.php?action=delete&id=<?php echo $recipe['id_repas']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce repas ?');">Supprimer</a>
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