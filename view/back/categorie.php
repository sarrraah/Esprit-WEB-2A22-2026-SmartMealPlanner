<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Catégories - Back Office</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../../controller/CategorieController.php'; ?>
    <div class="container mt-5">
        <h1>Gestion des Catégories</h1>
        <a href="add_categorie.php" class="btn btn-primary mb-3">Ajouter une Catégorie</a>
        <a href="index.php" class="btn btn-secondary mb-3">Retour au Dashboard</a>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $categorie): ?>
                <tr>
                    <td><?php echo htmlspecialchars($categorie['id_categorie']); ?></td>
                    <td><?php echo htmlspecialchars($categorie['nom_categorie']); ?></td>
                    <td>
                        <a href="edit_categorie.php?id=<?php echo $categorie['id_categorie']; ?>" class="btn btn-warning btn-sm">Modifier</a>
                        <a href="../../controller/CategorieController.php?action=delete&id=<?php echo $categorie['id_categorie']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>