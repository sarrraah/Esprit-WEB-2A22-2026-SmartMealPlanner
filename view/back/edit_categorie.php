<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une Catégorie - Back Office</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Modifier une Catégorie</h1>
        <?php
        require_once '../../controller/CategorieController.php';
        $id = $_GET['id'] ?? '';
        $categorie = $categorieModel->getCategorieById($id);
        if (!$categorie) {
            echo '<p>Catégorie non trouvée.</p>';
            exit;
        }
        ?>
        <form action="../../controller/CategorieController.php?action=edit" method="post">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($categorie['id_categorie']); ?>">
            <div class="mb-3">
                <label for="nom" class="form-label">Nom de la Catégorie</label>
                <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($categorie['nom_categorie']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Modifier</button>
            <a href="categorie.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>