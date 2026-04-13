<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contenu Nutritionnel - Back Office</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h1 class="mb-4">Gestion du contenu nutritionnel</h1>
    <div class="mb-3 d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-dark" href="index.php">Dashboard</a>
        <a class="btn btn-outline-dark" href="recette.php">Recettes</a>
        <a class="btn btn-outline-dark" href="utilisateurs.php">Utilisateurs</a>
        <a class="btn btn-outline-dark" href="aliments_durables.php">Aliments Durables</a>
        <a class="btn btn-outline-dark" href="statistiques.php">Statistiques</a>
    </div>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Paramètres nutritionnels</h5>
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">Protéines (g)</label><input class="form-control" value="20"></div>
                <div class="col-md-3"><label class="form-label">Glucides (g)</label><input class="form-control" value="50"></div>
                <div class="col-md-3"><label class="form-label">Lipides (g)</label><input class="form-control" value="15"></div>
                <div class="col-md-3"><label class="form-label">Fibres (g)</label><input class="form-control" value="8"></div>
            </div>
            <button class="btn btn-success mt-3" type="button">Enregistrer</button>
        </div>
    </div>
</div>
</body>
</html>
