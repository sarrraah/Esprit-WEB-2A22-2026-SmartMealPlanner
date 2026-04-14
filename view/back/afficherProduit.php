<?php
// afficher.php - Afficher tous les produits (Read)
$db_file = 'produits.db';
$pdo = null;

try {
    $pdo = new PDO("sqlite:" . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS produits (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nom TEXT NOT NULL,
        quantite INTEGER DEFAULT 0,
        prix REAL DEFAULT 0,
        date_expiration TEXT,
        photo TEXT,
        statut TEXT
    )");
} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}

// Récupérer tous les produits
$produits = $pdo->query("SELECT * FROM produits ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Meal Planner - Nos Produits</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #f0f5ec;
            font-family: 'Inter', sans-serif;
            padding: 2rem;
            color: #1a3a1a;
        }
        .container { max-width: 1300px; margin: 0 auto; }
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .header h1 {
            font-size: 2.5rem;
            background: linear-gradient(135deg, #2d6a2d, #6ba34e);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
        }
        .header p { color: #4a6b3a; margin-top: 8px; }
        .btn-ajout {
            display: inline-block;
            background: #3c8c40;
            color: white;
            padding: 12px 28px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 2rem;
            transition: 0.2s;
        }
        .btn-ajout:hover { background: #2b6e2f; }
        .grid-produits {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.8rem;
        }
        .card {
            background: white;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2f0d6;
            transition: 0.2s;
        }
        .card:hover { transform: translateY(-5px); }
        .card-img {
            height: 180px;
            background: #eef3e6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: #8bbf74;
        }
        .card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .card-body { padding: 1.2rem; }
        .card-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2b572b;
            margin-bottom: 8px;
        }
        .card-desc {
            font-size: 0.85rem;
            color: #5f7a52;
            margin-bottom: 12px;
        }
        .prix {
            font-size: 1.2rem;
            font-weight: 700;
            color: #3c8c40;
            margin: 8px 0;
        }
        .statut {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 8px;
        }
        .statut.Disponible { background: #c8e6b5; color: #2b6e2f; }
        .statut.Rupture { background: #ffe0cf; color: #c23d2b; }
        .statut.Stock-faible { background: #fff0c0; color: #b97f10; }
        .card-actions {
            display: flex;
            gap: 12px;
            margin-top: 1rem;
            padding-top: 12px;
            border-top: 1px solid #eef3e8;
        }
        .btn-modif, .btn-suppr {
            padding: 6px 16px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-modif { background: #e4efdc; color: #3a6b2f; }
        .btn-modif:hover { background: #d0e4c4; }
        .btn-suppr { background: #f9e0db; color: #c23d2b; }
        .btn-suppr:hover { background: #f2c9c0; }
        .empty {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 32px;
            color: #8aae78;
        }
        @media (max-width: 700px) {
            body { padding: 1rem; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1><i class="fas fa-apple-alt"></i> Smart Meal Planner</h1>
        <p>Qualité et fraîcheur garanties</p>
    </div>
    
    <div style="text-align: center;">
        <a href="ajouter.php" class="btn-ajout"><i class="fas fa-plus"></i> Ajouter un produit</a>
    </div>

    <?php if(empty($produits)): ?>
        <div class="empty">
            <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
            <p>Aucun produit pour le moment. Cliquez sur "Ajouter" pour commencer.</p>
        </div>
    <?php else: ?>
        <div class="grid-produits">
            <?php foreach($produits as $p): ?>
                <div class="card">
                    <div class="card-img">
                        <?php if(!empty($p['photo']) && file_exists($p['photo'])): ?>
                            <img src="<?= htmlspecialchars($p['photo']) ?>" alt="<?= htmlspecialchars($p['nom']) ?>">
                        <?php else: ?>
                            <i class="fas fa-apple-alt"></i>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="card-title"><?= htmlspecialchars($p['nom']) ?></div>
                        <div class="card-desc">
                            <i class="fas fa-boxes"></i> Stock: <?= $p['quantite'] ?> unités<br>
                            <i class="fas fa-calendar-alt"></i> Exp: <?= $p['date_expiration'] ?: 'Non définie' ?>
                        </div>
                        <div class="prix"><?= number_format($p['prix'], 2) ?> DT</div>
                        <span class="statut <?= htmlspecialchars($p['statut'] ?? 'Disponible') ?>">
                            <?= htmlspecialchars($p['statut'] ?? 'Disponible') ?>
                        </span>
                        <div class="card-actions">
                            <a href="modifier.php?id=<?= $p['id'] ?>" class="btn-modif"><i class="fas fa-edit"></i> Modifier</a>
                            <a href="supprimer.php?id=<?= $p['id'] ?>" class="btn-suppr" onclick="return confirm('Supprimer ce produit ?')"><i class="fas fa-trash-alt"></i> Supprimer</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>