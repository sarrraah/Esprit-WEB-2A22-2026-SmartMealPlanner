<?php
require_once __DIR__ . '/../../config.php';

// Récupérer le filtre de statut
$statutFilter = isset($_GET['statut']) ? $_GET['statut'] : 'Tous';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Construire la requête SQL
$sql = "SELECT * FROM produit WHERE 1=1";
$params = [];

if ($statutFilter !== 'Tous') {
    $sql .= " AND statut = ?";
    $params[] = $statutFilter;
}

if ($searchQuery) {
    $sql .= " AND nom LIKE ?";
    $params[] = "%$searchQuery%";
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produits = $stmt->fetchAll();

// Recalculer les statuts automatiquement
foreach ($produits as &$p) {
    $p['statut'] = determinerStatut($p['quantiteStock'], $p['dateExpiration']);
}
unset($p);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Produits - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar a {
            color: #ffffff;
            text-decoration: none;
            display: block;
            padding: 10px 20px;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h4 class="text-white text-center mb-4">Administration</h4>
        <a href="#"><i class="fas fa-calendar-alt"></i> Événements</a>
        <a href="#"><i class="fas fa-utensils"></i> Meal Planner</a>
        <a href="#"><i class="fas fa-book"></i> Recettes</a>
        <a href="#"><i class="fas fa-users"></i> Utilisateurs</a>
        <a href="afficherProduit.php" class="bg-primary"><i class="fas fa-shopping-cart"></i> Boutique</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="mb-4">Gestion des Produits - Boutique</h1>
        
        <!-- Filters & Search -->
        <div class="row mb-3">
            <div class="col-md-6">
                <form method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher par nom..." value="<?= htmlspecialchars($searchQuery) ?>">
                    <button type="submit" class="btn btn-primary ms-2"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <a href="?statut=Tous" class="btn btn-outline-secondary">Tous</a>
                <a href="?statut=Disponible" class="btn btn-outline-success">Disponible</a>
                <a href="?statut=Rupture" class="btn btn-outline-warning">Rupture</a>
                <a href="?statut=Épuisé" class="btn btn-outline-danger">Épuisé</a>
            </div>
        </div>

        <!-- Add Product Button -->
        <div class="mb-3">
            <a href="ajouterProduit.php" class="btn btn-success"><i class="fas fa-plus"></i> Ajouter un produit</a>
        </div>

        <!-- Products Table -->
        <?php if (count($produits) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Description</th>
                            <th>Quantité Stock</th>
                            <th>Date Expiration</th>
                            <th>Catégorie</th>
                            <th>Prix (DT)</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produits as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td><?= htmlspecialchars($p['nom']) ?></td>
                                <td><?= htmlspecialchars($p['description'] ?? '') ?></td>
                                <td><?= $p['quantiteStock'] ?></td>
                                <td><?= date('d/m/Y', strtotime($p['dateExpiration'])) ?></td>
                                <td><?= htmlspecialchars($p['categorie'] ?? ($p['id_categorie'] ?? 'Autre')) ?></td>
                                <td><?= number_format($p['prix'], 2, ',', ' ') ?></td>
                                <td>
                                    <span class="badge 
                                        <?php 
                                        switch($p['statut']) {
                                            case 'Disponible': echo 'bg-success'; break;
                                            case 'Rupture': echo 'bg-warning'; break;
                                            case 'Épuisé': echo 'bg-danger'; break;
                                            default: echo 'bg-secondary';
                                        }
                                        ?>">
                                        <?= $p['statut'] ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="modifierProduit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i> Modifier</a>
                                    <a href="supprimerProduit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');"><i class="fas fa-trash"></i> Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Aucun produit trouvé.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>