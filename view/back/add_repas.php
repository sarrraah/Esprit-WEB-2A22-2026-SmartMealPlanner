<?php
require_once '../../config.php';
require_once '../../model/Recette.php';

$recipeModel = new Recette();
$categories = $recipeModel->getCategories();
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$projectPath = str_replace('\\', '/', dirname(__DIR__, 2));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$relativeProject = str_replace($docRoot, '', $projectPath);
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $relativeProject;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Repas - Back Office</title>
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
            <h1 class="mb-4">Ajouter un Nouveau Repas</h1>
            <form action="<?php echo htmlspecialchars($baseUrl . '/controller/RecetteController.php', ENT_QUOTES, 'UTF-8'); ?>" method="POST">
                <input type="hidden" name="from" value="back">
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
                        <div class="alert alert-warning">Aucune catégorie trouvée. Ajoutez d'abord des catégories via le back office.</div>
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
                <button type="submit" class="btn btn-primary" <?php echo empty($categories) ? 'disabled' : ''; ?>>Ajouter le Repas</button>
                <a href="recette.php" class="btn btn-secondary ms-2">Annuler</a>
            </form>
        </div>
    </div>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>