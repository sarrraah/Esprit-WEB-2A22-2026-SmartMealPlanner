<?php
// modifier.php - Modifier un produit (Update)
$db_file = 'produits.db';
$pdo = null;
$error = '';
$success = '';

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

function getStatut($quantite) {
    if ($quantite <= 0) return 'Rupture';
    if ($quantite < 10) return 'Stock faible';
    return 'Disponible';
}

// Récupérer l'ID du produit à modifier
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);
if (!$id) {
    header('Location: afficher.php');
    exit;
}

// Récupérer les infos du produit
$stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
$stmt->execute([$id]);
$produit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produit) {
    header('Location: afficher.php');
    exit;
}

// Traitement de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $quantite = intval($_POST['quantite'] ?? 0);
    $prix = floatval($_POST['prix'] ?? 0);
    $date_expiration = $_POST['date_expiration'] ?? null;
    $statut = getStatut($quantite);
    $photo = $produit['photo'];
    
    if (empty($nom)) {
        $error = "Le nom du produit est requis.";
    } elseif ($prix < 0) {
        $error = "Le prix doit être positif.";
    } else {
        // Nouvelle photo éventuelle
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed)) {
                $filename = uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $filename);
                // Supprimer l'ancienne photo
                if ($produit['photo'] && file_exists($produit['photo'])) {
                    unlink($produit['photo']);
                }
                $photo = $upload_dir . $filename;
            } else {
                $error = "Format d'image non supporté.";
            }
        }
        
        if (empty($error)) {
            $stmt = $pdo->prepare("UPDATE produits SET nom = ?, quantite = ?, prix = ?, date_expiration = ?, statut = ?, photo = ? WHERE id = ?");
            $stmt->execute([$nom, $quantite, $prix, $date_expiration, $statut, $photo, $id]);
            $success = "Produit modifié avec succès !";
            // Recharger les nouvelles données
            $stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
            $stmt->execute([$id]);
            $produit = $stmt->fetch(PDO::FETCH_ASSOC);
            header("refresh:2;url=modifier.php?id=" . $id);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Meal Planner - Modifier un produit</title>
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
        .container { max-width: 700px; margin: 0 auto; }
        .card {
            background: white;
            border-radius: 48px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            border: 1px solid #e2f0d6;
        }
        h1 {
            font-size: 2rem;
            color: #2d6a2d;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 1.5rem;
            color: #5f7e4e;
            text-decoration: none;
        }
        .form-group {
            margin-bottom: 1.3rem;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #2d4a2a;
        }
        input, input[type="number"], input[type="date"] {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e2eccf;
            border-radius: 28px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
        }
        input:focus {
            outline: none;
            border-color: #6ba34e;
        }
        .small-note {
            font-size: 0.7rem;
            color: #8aae78;
            margin-top: 5px;
        }
        .upload-area {
            border: 2px dashed #cce0bd;
            border-radius: 28px;
            padding: 1.2rem;
            text-align: center;
            background: #fafef5;
            cursor: pointer;
        }
        .upload-area i { font-size: 2rem; color: #7dab64; }
        .current-photo {
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .current-photo img {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            object-fit: cover;
        }
        .btn-submit {
            background: #3c8c40;
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 40px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
            margin-top: 1rem;
        }
        .btn-submit:hover { background: #2b6e2f; }
        .error { background: #ffe0cf; color: #c23d2b; padding: 12px; border-radius: 28px; margin-bottom: 1rem; }
        .success { background: #c8e6b5; color: #2b6e2f; padding: 12px; border-radius: 28px; margin-bottom: 1rem; }
    </style>
</head>
<body>
<div class="container">
    <a href="afficher.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour à la liste</a>
    
    <div class="card">
        <h1><i class="fas fa-edit"></i> Modifier le produit</h1>
        <p style="margin-bottom: 1.5rem; color: #5f7a52;">Modifiez les informations ci-dessous</p>
        
        <?php if($error): ?>
            <div class="error"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?> Redirection...</div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $produit['id'] ?>">
            
            <div class="form-group">
                <label>Nom du produit *</label>
                <input type="text" name="nom" required value="<?= htmlspecialchars($produit['nom']) ?>">
            </div>
            
            <div class="form-group">
                <label>Quantité en stock</label>
                <input type="number" name="quantite" value="<?= $produit['quantite'] ?>" min="0">
                <div class="small-note">Le statut sera mis à jour automatiquement</div>
            </div>
            
            <div class="form-group">
                <label>Prix (DT) *</label>
                <input type="number" step="0.01" name="prix" value="<?= $produit['prix'] ?>" required>
            </div>
            
            <div class="form-group">
                <label>Date d'expiration</label>
                <input type="date" name="date_expiration" value="<?= htmlspecialchars($produit['date_expiration'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label>Photo du produit</label>
                <div class="upload-area" onclick="document.getElementById('photo').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Cliquez pour changer la photo<br>Formats: JPG, PNG, GIF, WEBP</p>
                    <input type="file" id="photo" name="photo" accept="image/*" style="display:none">
                </div>
                <?php if(!empty($produit['photo']) && file_exists($produit['photo'])): ?>
                    <div class="current-photo">
                        <img src="<?= htmlspecialchars($produit['photo']) ?>" alt="Photo actuelle">
                        <span style="font-size:0.75rem; color:#7d9a6e;">Photo actuelle</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Enregistrer les modifications</button>
        </form>
    </div>
</div>
<script>
    document.getElementById('photo')?.addEventListener('change', function(e) {
        if(e.target.files.length > 0) {
            alert('Nouvelle image sélectionnée : ' + e.target.files[0].name);
        }
    });
</script>
</body>
</html>
