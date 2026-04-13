<?php
$totalRecipes = 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office - Smart Meal Planner</title>
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
                <li class="mb-2"><a href="index.php" class="active">Tableau de Bord</a></li>
                <li class="mb-2"><a href="repas.php">Gestion des Repas</a></li>
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
            <h1 class="mb-4">Tableau de Bord Administrateur</h1>

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Repas</h5>
                            <p class="card-text display-4"><?php echo $totalRecipes; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Utilisateurs</h5>
                            <p class="card-text display-4">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Aliments Durables</h5>
                            <p class="card-text display-4">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <h3>Actions Rapides</h3>
                <a href="recette.php" class="btn btn-primary me-2">Gérer les Recettes</a>
                <a href="add_recette.php" class="btn btn-success me-2">Ajouter une recette</a>
                <a href="statistiques.php" class="btn btn-outline-dark">Voir les statistiques</a>
            </div>
        </div>
    </div>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>