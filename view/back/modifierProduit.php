<?php
require_once __DIR__ . '/../../config.php';

// R�cup�rer l'ID soit depuis GET soit depuis le champ POST cach�
$id = 0;
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
} elseif (isset($_POST['id'])) {
    $id = intval($_POST['id']);
}

$produit = null;
$erreur = '';
$succes = '';

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM produit WHERE id = ?");
    $stmt->execute([$id]);
    $produit = $stmt->fetch();
}

if (!$produit) {
    header('Location: afficherProduit.php');
    exit;
}

$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$maxFileSize = 5 * 1024 * 1024;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = floatval($_POST['prix'] ?? 0);
    $quantiteStock = intval($_POST['quantiteStock'] ?? 0);
    $dateExpiration = trim($_POST['dateExpiration'] ?? '');
    $categorie = trim($_POST['categorie'] ?? ($produit['categorie'] ?? 'Autre'));
    $estDurable = isset($_POST['estDurable']) ? 1 : 0;
    $image = $produit['image'];

    if ($categorie === '') {
        $categorie = 'Autre';
    }

    if (empty($nom)) {
        $erreur = 'Le nom du produit est requis.';
    } elseif ($prix <= 0) {
        $erreur = 'Le prix doit �tre sup�rieur � 0.';
    } elseif (empty($dateExpiration)) {
        $erreur = 'La date d\'expiration est requise.';
    }

    if (empty($erreur) && isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $erreur = 'Erreur lors du t�l�chargement du fichier.';
        } else {
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($fileExt, $allowedExtensions)) {
                $erreur = 'Format de fichier non autoris�. Formats accept�s: ' . implode(', ', $allowedExtensions);
            } elseif ($file['size'] > $maxFileSize) {
                $erreur = 'La taille du fichier d�passe 5 Mo.';
            } else {
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0755, true);
                }
                $newFileName = time() . '_' . uniqid() . '.' . $fileExt;
                if (move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $newFileName)) {
                    if (!empty($produit['image']) && file_exists(UPLOAD_DIR . $produit['image'])) {
                        unlink(UPLOAD_DIR . $produit['image']);
                    }
                    $image = $newFileName;
                } else {
                    $erreur = 'Impossible de d�placer le fichier t�l�charg�.';
                }
            }
        }
    }

    if (empty($erreur)) {
        try {
            $sql = "UPDATE produit SET nom = ?, description = ?, prix = ?, quantiteStock = ?, dateExpiration = ?, estDurable = ?, image = ?, categorie = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $description, $prix, $quantiteStock, $dateExpiration, $estDurable, $image, $categorie, $id]);
            $succes = 'Produit modifi� avec succ�s.';
            $stmt = $pdo->prepare("SELECT * FROM produit WHERE id = ?");
            $stmt->execute([$id]);
            $produit = $stmt->fetch();
        } catch (Exception $e) {
            $erreur = 'Erreur lors de la mise � jour : ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Produit - Administration</title>
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
        <h1 class="mb-4">Modifier un Produit</h1>
        
        <?php if ($erreur): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?= $erreur ?>
            </div>
        <?php endif; ?>
        
        <?php if ($succes): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $succes ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="row g-3">
            <input type="hidden" name="id" value="<?= $produit['id'] ?>">
            
            <div class="col-md-6">
                <label for="nom" class="form-label">Nom du produit *</label>
                <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($produit['nom']) ?>" required>
            </div>
            
            <div class="col-md-6">
                <label for="prix" class="form-label">Prix (DT) *</label>
                <input type="number" class="form-control" id="prix" name="prix" step="0.01" min="0" value="<?= $produit['prix'] ?>" required>
            </div>
            
            <div class="col-md-6">
                <label for="quantiteStock" class="form-label">Quantité en stock *</label>
                <input type="number" class="form-control" id="quantiteStock" name="quantiteStock" min="0" value="<?= $produit['quantiteStock'] ?>" required>
            </div>
            
            <div class="col-md-6">
                <label for="dateExpiration" class="form-label">Date d'expiration *</label>
                <input type="date" class="form-control" id="dateExpiration" name="dateExpiration" value="<?= $produit['dateExpiration'] ?>" required>
            </div>
            
            <div class="col-md-6">
                <label for="categorie" class="form-label">Catégorie</label>
                <input type="text" class="form-control" id="categorie" name="categorie" value="<?= htmlspecialchars($produit['categorie']) ?>" placeholder="Ex: Fruits, Légumes, Protéines">
            </div>
            
            <div class="col-md-6">
                <label for="image" class="form-label">Image (laisser vide pour garder l'actuelle)</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                <?php if (!empty($produit['image'])): ?>
                    <small class="text-muted">Image actuelle: <?= htmlspecialchars($produit['image']) ?></small>
                <?php endif; ?>
            </div>
            
            <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($produit['description']) ?></textarea>
            </div>
            
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="estDurable" name="estDurable" <?= $produit['estDurable'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="estDurable">
                        Produit durable
                    </label>
                </div>
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Modifier le produit
                </button>
                <a href="afficherProduit.php" class="btn btn-secondary ms-2">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
