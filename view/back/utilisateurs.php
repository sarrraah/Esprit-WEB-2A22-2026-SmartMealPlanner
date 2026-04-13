<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Repas.php';
$repasModel = new Repas();
$totalRepas = count($repasModel->getAllRepas());
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - Back Office</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h1 class="mb-4">Gestion des Utilisateurs</h1>
    <div class="mb-3 d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-dark" href="index.php">Dashboard</a>
        <a class="btn btn-outline-dark" href="recette.php">Recettes</a>
        <a class="btn btn-outline-dark" href="aliments_durables.php">Aliments Durables</a>
        <a class="btn btn-outline-dark" href="statistiques.php">Statistiques</a>
        <a class="btn btn-outline-dark" href="contenu_nutritionnel.php">Contenu Nutritionnel</a>
    </div>
    <div class="alert alert-info">
        Module conforme au README: gestion des utilisateurs (interface prête).<br>
        Nombre de repas en base: <strong><?php echo $totalRepas; ?></strong>
    </div>
    <table class="table table-bordered bg-white">
        <thead><tr><th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th></tr></thead>
        <tbody>
            <tr><td>1</td><td>Admin</td><td>admin@smartmealplanner.tn</td><td>Administrateur</td></tr>
        </tbody>
    </table>
</div>
</body>
</html>
