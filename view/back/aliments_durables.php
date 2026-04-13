<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aliments Durables - Back Office</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h1 class="mb-4">Gestion des Aliments Durables</h1>
    <div class="mb-3 d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-dark" href="index.php">Dashboard</a>
        <a class="btn btn-outline-dark" href="recette.php">Recettes</a>
        <a class="btn btn-outline-dark" href="utilisateurs.php">Utilisateurs</a>
        <a class="btn btn-outline-dark" href="statistiques.php">Statistiques</a>
        <a class="btn btn-outline-dark" href="contenu_nutritionnel.php">Contenu Nutritionnel</a>
    </div>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Ajouter un aliment durable</h5>
            <form>
                <div class="row g-3">
                    <div class="col-md-4"><input class="form-control" placeholder="Nom de l'aliment"></div>
                    <div class="col-md-4"><input class="form-control" placeholder="Origine"></div>
                    <div class="col-md-4"><input class="form-control" placeholder="Score écologique"></div>
                </div>
                <button class="btn btn-success mt-3" type="button">Ajouter</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
