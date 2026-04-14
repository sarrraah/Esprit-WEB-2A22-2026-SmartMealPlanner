<?php
// supprimer.php - Supprimer un produit (Delete)
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

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    header('Location: afficher.php');
    exit;
}

// Récupérer la photo pour la supprimer
$stmt = $pdo->prepare("SELECT photo, nom FROM produits WHERE id = ?");
$stmt->execute([$id]);
$produit = $stmt->fetch(PDO::FETCH_ASSOC);

if ($produit) {
    // Supprimer la photo du disque
    if (!empty($produit['photo']) && file_exists($produit['photo'])) {
        unlink($produit['photo']);
    }
    
    // Supprimer de la base
    $stmt = $pdo->prepare("DELETE FROM produits WHERE id = ?");
    $stmt->execute([$id]);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppression en cours</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #f0f5ec;
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .card {
            background: white;
            border-radius: 48px;
            padding: 2.5rem;
            text-align: center;
            max-width: 450px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
        }
        .card i {
            font-size: 4rem;
            color: #c23d2b;
            margin-bottom: 1rem;
        }
        .card h2 {
            color: #2d6a2d;
            margin-bottom: 0.5rem;
        }
        .card p {
            color: #5f7a52;
            margin-bottom: 1.5rem;
        }
        .btn {
            display: inline-block;
            background: #3c8c40;
            color: white;
            padding: 12px 28px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
        }
        .btn:hover { background: #2b6e2f; }
    </style>
</head>
<body>
<div class="card">
    <i class="fas fa-trash-alt"></i>
    <h2>Produit supprimé</h2>
    <p>Le produit "<?= htmlspecialchars($produit['nom'] ?? '') ?>" a été supprimé avec succès.</p>
    <a href="afficher.php" class="btn"><i class="fas fa-arrow-left"></i> Retour à la liste</a>
</div>
<?php
// Redirection automatique après 3 secondes
header("refresh:3;url=afficher.php");
?>
</body>
</html>