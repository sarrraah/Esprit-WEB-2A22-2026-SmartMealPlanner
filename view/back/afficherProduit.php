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
    <title>Gestion des Produits - Smart Meal Planner</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(180deg, #fff2f5 0%, #f6f3f8 35%, #ffffff 100%);
            min-height: 100vh;
            padding: 30px 0;
        }

        .navbar-custom {
            background: white !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
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

        .container-main {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            margin-top: 30px;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header-section h1 {
            color: #e63946;
            font-weight: 700;
            margin: 0;
        }

        .btn-ajouter {
            background: linear-gradient(135deg, #e63946, #f15b6c);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-ajouter:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(230, 57, 70, 0.4);
            color: white;
        }

        /* Filtres et recherche */
        .filters-section {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 0.95rem;
            transition: 0.3s;
        }

        .search-box input:focus {
            border-color: #e63946;
            outline: none;
            box-shadow: 0 0 0 3px rgba(230, 57, 70, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #e63946;
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 10px 20px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 25px;
            cursor: pointer;
            transition: 0.3s;
            font-weight: 600;
            color: #333;
        }

        .filter-btn.active {
            background: linear-gradient(135deg, #e63946, #f15b6c);
            border-color: #e63946;
            color: white;
        }

        .filter-btn:hover {
            border-color: #e63946;
            color: #e63946;
        }

        /* Grille produits */
        .produits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .card-product {
            background: white;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: 0.3s ease;
            position: relative;
        }

        .card-product:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.2);
        }

        .product-img-container {
            position: relative;
            height: 200px;
            overflow: hidden;
            background: #f5f5f5;
        }

        .product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: 0.3s;
        }

        .card-product:hover .product-img {
            transform: scale(1.05);
        }

        .badge-statut {
            position: absolute;
            top: 12px;
            right: 12px;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .badge-disponible {
            background: #10b981;
            color: white;
        }

        .badge-rupture {
            background: #f59e0b;
            color: white;
        }

        .badge-epuise {
            background: #ef4444;
            color: white;
        }

        .card-body {
            padding: 20px;
        }

        .product-name {
            font-weight: 700;
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 8px;
        }

        .product-description {
            font-size: 0.9rem;
            color: #999;
            margin-bottom: 12px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 15px 0;
            padding: 12px 0;
            border-top: 1px solid #f0f0f0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.9rem;
        }

        .info-item {
            color: #666;
        }

        .info-label {
            font-weight: 600;
            color: #333;
        }

        .price {
            font-size: 1.4rem;
            font-weight: 700;
            color: #e63946;
            margin: 12px 0;
        }

        .expiration {
            font-size: 0.85rem;
            color: #999;
            margin-bottom: 15px;
        }

        .product-actions {
            display: flex;
            gap: 8px;
            margin-top: 18px;
        }

        .btn-action {
            flex: 1;
            padding: 10px 12px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-modifier {
            background: linear-gradient(135deg, #e63946, #f15b6c);
            color: white;
        }

        .btn-modifier:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .btn-supprimer {
            background: #fed7d7;
            color: #e53e3e;
        }

        .btn-supprimer:hover {
            background: #fc8181;
            color: white;
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #ddd;
        }

        .empty-state p {
            font-size: 1.1rem;
            margin: 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container-main {
                padding: 20px;
            }

            .header-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .filters-section {
                flex-direction: column;
            }

            .filter-buttons {
                justify-content: flex-start;
            }

            .produits-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 15px;
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

    <!-- Main Content -->
    <div class="container-lg">
        <div class="container-main">
            <!-- Header -->
            <div class="header-section">
                <h1><i class="fas fa-box-open"></i> Gestion des Produits</h1>
                <a href="ajouterProduit.php" class="btn-ajouter">
                    <i class="fas fa-plus"></i> Ajouter un produit
                </a>
            </div>

            <!-- Filters & Search -->
            <div class="filters-section">
                <form method="GET" class="search-box" style="margin: 0;">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Rechercher un produit..." 
                           value="<?= htmlspecialchars($searchQuery) ?>">
                </form>

                <div class="filter-buttons">
                    <a href="?statut=Tous" class="filter-btn <?= $statutFilter === 'Tous' ? 'active' : '' ?>">
                        Tous
                    </a>
                    <a href="?statut=Disponible" class="filter-btn <?= $statutFilter === 'Disponible' ? 'active' : '' ?>">
                        <i class="fas fa-check-circle"></i> Disponible
                    </a>
                    <a href="?statut=Rupture" class="filter-btn <?= $statutFilter === 'Rupture' ? 'active' : '' ?>">
                        <i class="fas fa-exclamation-circle"></i> Rupture
                    </a>
                    <a href="?statut=Épuisé" class="filter-btn <?= $statutFilter === 'Épuisé' ? 'active' : '' ?>">
                        <i class="fas fa-times-circle"></i> Épuisé
                    </a>
                </div>
            </div>

            <!-- Products Grid -->
            <?php if (count($produits) > 0): ?>
                <div class="produits-grid">
                    <?php foreach ($produits as $p): 
                        $statut = $p['statut'];
                        $badgeClass = match($statut) {
                            'Disponible' => 'badge-disponible',
                            'Rupture' => 'badge-rupture',
                            'Épuisé' => 'badge-epuise',
                            default => 'badge-disponible'
                        };
                    ?>
                        <div class="card-product">
                            <!-- Image & Badge -->
                            <div class="product-img-container">
                                <?php
                                $imageSrc = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22300%22 height=%22200%22 viewBox=%220 0 300 200%22%3E%3Crect fill=%22%23f0f0f0%22 width=%22300%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 font-family=%22sans-serif%22 font-size=%2224%22 fill=%22%23999%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22%3EPas d%27image%3C/text%3E%3C/svg%3E';
                                if (!empty($p['image'])) {
                                    if (filter_var($p['image'], FILTER_VALIDATE_URL)) {
                                        $imageSrc = htmlspecialchars($p['image']);
                                    } elseif (file_exists(UPLOAD_DIR . $p['image'])) {
                                        $imageSrc = UPLOAD_URL . htmlspecialchars($p['image']);
                                    }
                                }
                            ?>
                            <img src="<?= $imageSrc ?>" 
                                     class="product-img" alt="<?= htmlspecialchars($p['nom']) ?>">
                                <span class="badge-statut <?= $badgeClass ?>">
                                    <?= $statut ?>
                                </span>
                            </div>

                            <!-- Content -->
                            <div class="card-body">
                                <h5 class="product-name"><?= htmlspecialchars($p['nom']) ?></h5>
                                <p class="product-description"><?= htmlspecialchars($p['description'] ?? '') ?></p>

                                <div class="product-info">
                                    <div class="info-item">
                                        <span class="info-label">Stock:</span> <?= $p['quantiteStock'] ?>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Catégorie:</span> <?= htmlspecialchars($p['categorie'] ?? ($p['id_categorie'] ?? 'Autre')) ?>
                                    </div>
                                </div>

                                <div class="price"><?= number_format($p['prix'], 2, ',', ' ') ?> DT</div>

                                <div class="expiration">
                                    <i class="fas fa-calendar-alt"></i> 
                                    Exp: <?= date('d/m/Y', strtotime($p['dateExpiration'])) ?>
                                </div>

                                <div class="product-actions">
                                    <a href="modifierProduit.php?id=<?= $p['id'] ?>" class="btn-action btn-modifier">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <a href="supprimerProduit.php?id=<?= $p['id'] ?>" class="btn-action btn-supprimer"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Aucun produit trouvé</p>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>