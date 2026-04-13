<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Repas.php';
$repasModel = new Repas();
$repas = $repasModel->getAllRepas();
$totalRepas = count($repas);
$totalCalories = 0;
foreach ($repas as $item) {
    $totalCalories += (float)($item['calories'] ?? 0);
}
$moyenneCalories = $totalRepas > 0 ? round($totalCalories / $totalRepas, 1) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - Back Office</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h1 class="mb-4">Tableau de bord avec statistiques</h1>
    <div class="mb-3 d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-dark" href="index.php">Dashboard</a>
        <a class="btn btn-outline-dark" href="recette.php">Recettes</a>
        <a class="btn btn-outline-dark" href="utilisateurs.php">Utilisateurs</a>
        <a class="btn btn-outline-dark" href="aliments_durables.php">Aliments Durables</a>
        <a class="btn btn-outline-dark" href="contenu_nutritionnel.php">Contenu Nutritionnel</a>
    </div>
    <div class="row g-3">
        <div class="col-md-4"><div class="card"><div class="card-body"><h6>Total Repas</h6><p class="display-6"><?php echo $totalRepas; ?></p></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><h6>Calories cumulées</h6><p class="display-6"><?php echo $totalCalories; ?></p></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><h6>Moyenne calories/repas</h6><p class="display-6"><?php echo $moyenneCalories; ?></p></div></div></div>
    </div>
</div>
</body>
</html>
