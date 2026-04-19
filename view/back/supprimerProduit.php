<?php
require_once __DIR__ . '/../../config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$erreur = '';
$succes = '';

if ($id <= 0) {
    header('Location: afficherProduit.php');
    exit;
}

// Récupérer le produit
$stmt = $pdo->prepare("SELECT * FROM produit WHERE id = ?");
$stmt->execute([$id]);
$produit = $stmt->fetch();

if (!$produit) {
    // Vérifier s'il y a des produits dans la table
    $stmt_check = $pdo->query("SELECT COUNT(*) as total FROM produit");
    $total = $stmt_check->fetch()['total'];
    die("<div class='alert alert-danger' style='margin-top: 20px;'>Produit non trouvé (ID: $id). Total produits dans la base: $total</div>");
}

// Traiter la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Supprimer la photo si elle existe
        if (!empty($produit['image'])) {
            $imagePath = UPLOAD_DIR . $produit['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Supprimer le produit de la base de données
        $stmt = $pdo->prepare("DELETE FROM produit WHERE id = ?");
        $stmt->execute([$id]);

        $succes = true;
    } catch (Exception $e) {
        $erreur = "Erreur lors de la suppression: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer Produit - Administration</title>
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
        <h1 class="mb-4">Confirmer la suppression</h1>
        
        <?php if ($erreur): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?= $erreur ?>
            </div>
        <?php endif; ?>
        
        <?php if ($succes): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Produit supprimé avec succès. Redirection...
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = 'afficherProduit.php';
                }, 2000);
            </script>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Êtes-vous sûr de vouloir supprimer ce produit ?</h5>
                    <p class="card-text">
                        <strong>Nom:</strong> <?= htmlspecialchars($produit['nom']) ?><br>
                        <strong>Description:</strong> <?= htmlspecialchars($produit['description'] ?? 'Aucune') ?><br>
                        <strong>Prix:</strong> <?= number_format($produit['prix'], 2, ',', ' ') ?> DT<br>
                        <strong>Quantité:</strong> <?= $produit['quantiteStock'] ?><br>
                        <strong>Catégorie:</strong> <?= htmlspecialchars($produit['categorie'] ?? 'Autre') ?>
                    </p>
                    <form method="POST">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Oui, supprimer
                        </button>
                        <a href="afficherProduit.php" class="btn btn-secondary ms-2">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
