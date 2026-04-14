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
    <title>Confirmer la suppression</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(180deg, #fff2f5 0%, #f6f3f8 35%, #ffffff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .navbar-custom {
            background: white !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .navbar-brand {
            color: #ff4444 !important;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .navbar-brand i {
            color: #ff4444;
            margin-right: 8px;
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            border-bottom: 2px solid #f0f0f0;
            padding: 30px;
        }

        .modal-title {
            color: #ef4444;
            font-weight: 700;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .modal-title i {
            font-size: 1.6rem;
        }

        .modal-body {
            padding: 30px;
        }

        .produit-info {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #e63946;
        }

        .produit-info h5 {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .produit-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            font-size: 0.9rem;
        }

        .detail-item {
            color: #666;
        }

        .detail-label {
            font-weight: 600;
            color: #333;
        }

        .warning-text {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            color: #92400e;
            font-size: 0.9rem;
        }

        .modal-footer {
            border-top: 2px solid #f0f0f0;
            padding: 20px 30px;
            display: flex;
            gap: 12px;
        }

        .btn-confirm, .btn-cancel {
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-confirm {
            background: #ef4444;
            color: white;
            flex: 1;
        }

        .btn-confirm:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
            color: white;
        }

        .btn-cancel {
            background: #e0e0e0;
            color: #333;
            flex: 1;
        }

        .btn-cancel:hover {
            background: #d0d0d0;
            color: #333;
        }

        .success-message {
            text-align: center;
            padding: 40px;
        }

        .success-icon {
            font-size: 4rem;
            color: #10b981;
            margin-bottom: 20px;
        }

        .success-text {
            font-size: 1.2rem;
            color: #059669;
            margin-bottom: 30px;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .produit-details {
                grid-template-columns: 1fr;
            }

            .modal-footer {
                flex-direction: column;
            }

            .btn-confirm, .btn-cancel {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <span class="navbar-brand">
                <i class="fas fa-utensils"></i> Smart Meal Planner
            </span>
        </div>
    </nav>

    <!-- Modal -->
    <div class="modal" style="display: block; background: transparent;">
        <div class="modal-dialog modal-dialog-centered" style="margin-top: 60px;">
            <div class="modal-content">
                <?php if ($succes): ?>
                    <!-- Success Message -->
                    <div class="modal-body success-message">
                        <div class="success-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <p class="success-text">Produit supprimé avec succès</p>
                        <a href="afficherProduit.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                <?php elseif ($erreur): ?>
                    <!-- Error Message -->
                    <div class="modal-body">
                        <div style="background: #fee2e2; border: 2px solid #ef4444; border-radius: 10px; padding: 20px; color: #991b1b; margin-bottom: 20px;">
                            <i class="fas fa-exclamation-circle"></i> <?= $erreur ?>
                        </div>
                        <a href="afficherProduit.php" class="btn btn-secondary" style="width: 100%;">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Confirmation Form -->
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            Confirmer la suppression
                        </h5>
                    </div>

                    <div class="modal-body">
                        <p style="color: #666; margin-bottom: 20px;">
                            Êtes-vous sûr de vouloir supprimer ce produit ? Cette action est <strong>irréversible</strong>.
                        </p>

                        <!-- Produit Info -->
                        <div class="produit-info">
                            <h5>
                                <i class="fas fa-box"></i> <?= htmlspecialchars($produit['nom']) ?>
                            </h5>
                            <div class="produit-details">
                                <div class="detail-item">
                                    <span class="detail-label">Prix:</span> <?= number_format($produit['prix'], 2, ',', ' ') ?> DT
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Stock:</span> <?= $produit['quantiteStock'] ?>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Expiration:</span> <?= date('d/m/Y', strtotime($produit['dateExpiration'])) ?>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Image:</span> <?= !empty($produit['image']) ? 'Oui' : 'Non' ?>
                                </div>
                            </div>
                        </div>

                        <div class="warning-text">
                            <i class="fas fa-warning"></i>
                            <strong>Attention:</strong> La photo du produit (si elle existe) sera également supprimée du serveur.
                        </div>
                    </div>

                    <div class="modal-footer">
                        <form method="POST" style="display: flex; gap: 12px; width: 100%;">
                            <button type="submit" class="btn-confirm">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                            <a href="afficherProduit.php" class="btn-cancel" style="text-decoration: none;">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>