<?php
require_once __DIR__ . '/../../config.php';

$erreur = '';
$succes = '';

$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$maxFileSize = 5 * 1024 * 1024; // 5 MB

// Traiter le formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = floatval($_POST['prix'] ?? 0);
    $quantiteStock = intval($_POST['quantiteStock'] ?? 0);
    $dateExpiration = trim($_POST['dateExpiration'] ?? '');
    $categorie = trim($_POST['categorie'] ?? '');
    $estDurable = isset($_POST['estDurable']) ? 1 : 0;
    $image = '';

    if (empty($categorie)) {
        $categorie = 'Autre';
    }

    // Validation
    if (empty($nom)) {
        $erreur = "Le nom du produit est requis";
    } elseif ($prix <= 0) {
        $erreur = "Le prix doit être supérieur à 0";
    } elseif (empty($dateExpiration)) {
        $erreur = "La date d'expiration est requise";
    }

    // Traiter l'upload image
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE && empty($erreur)) {
        $file = $_FILES['image'];
        $fileName = basename($file['name']);
        $fileSize = $file['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validation fichier
        if ($fileSize > $maxFileSize) {
            $erreur = "La taille du fichier dépasse 5 MB";
        } elseif (!in_array($fileExt, $allowedExtensions)) {
            $erreur = "Format de fichier non autorisé. Formats acceptés: " . implode(', ', $allowedExtensions);
        } else {
            $newFileName = time() . '_' . uniqid() . '.' . $fileExt;
            if (move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $newFileName)) {
                $image = $newFileName;
            } else {
                $erreur = "Erreur lors du téléchargement du fichier";
            }
        }
    }

    // Insérer dans la base de données
    if (empty($erreur)) {
        try {
            $sql = "INSERT INTO produit (nom, description, prix, quantiteStock, dateExpiration, estDurable, image, categorie, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Disponible')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $description, $prix, $quantiteStock, $dateExpiration, $estDurable, $image, $categorie]);

            $succes = "Produit ajouté avec succès ! Redirection...";
            header("refresh:2;url=afficherProduit.php");
        } catch (Exception $e) {
            $erreur = "Erreur lors de l'ajout: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Produit - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #f4f6f9;
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar h4 {
            color: #ffffff;
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 700;
        }
        .sidebar a {
            color: #ffffff;
            display: block;
            padding: 12px 20px;
            text-decoration: none;
            transition: background 0.2s ease;
        }
        .sidebar a:hover,
        .sidebar a.active {
            background-color: #495057;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px 35px;
        }
        .form-container {
            background: #ffffff;
            border-radius: 20px;
            padding: 30px 35px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
        }
        .form-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .form-group label {
            font-weight: 600;
            color: #333;
        }
        .form-control,
        .form-select {
            border-radius: 12px;
            border: 1px solid #d1d5db;
            padding: 12px 14px;
            font-size: 0.95rem;
        }
        .form-control:focus,
        .form-select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
            outline: none;
        }
        .image-section {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 22px;
            margin-bottom: 25px;
        }
        .image-preview {
            width: 100%;
            max-width: 360px;
            height: 260px;
            background: #edf2f7;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 16px;
        }
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .btn-upload {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #2563eb;
            color: white;
            padding: 10px 18px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-upload:hover {
            background: #1d4ed8;
        }
        .btn-custom {
            min-width: 150px;
            border-radius: 12px;
            font-weight: 600;
        }
        .btn-submit {
            background: #10b981;
            color: white;
            border: none;
        }
        .btn-submit:hover {
            background: #059669;
        }
        .btn-cancel {
            background: #e5e7eb;
            color: #111827;
            border: none;
        }
        .btn-cancel:hover {
            background: #d1d5db;
        }
        .alert {
            border-radius: 14px;
            padding: 16px 18px;
        }
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h4>Administration</h4>
        <a href="#"><i class="fas fa-calendar-alt"></i> Événements</a>
        <a href="#"><i class="fas fa-utensils"></i> Meal Planner</a>
        <a href="#"><i class="fas fa-book"></i> Recettes</a>
        <a href="#"><i class="fas fa-users"></i> Utilisateurs</a>
        <a href="afficherProduit.php" class="active"><i class="fas fa-shopping-cart"></i> Boutique</a>
    </div>

    <div class="main-content">
        <div class="form-container">
            <h1 class="form-title"><i class="fas fa-plus-circle"></i> Ajouter un Produit</h1>

            <?php if ($succes): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($succes) ?>
                </div>
            <?php endif; ?>

            <?php if ($erreur): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($erreur) ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="row g-4">
                <div class="col-12 image-section">
                    <p style="font-weight: 600; margin-bottom: 16px;">Photo du produit</p>
                    <div class="image-preview">
                        <img id="imagePreview" src="data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22150%22 height=%22150%22 viewBox=%220 0 150 150%22%3E%3Crect fill=%22%23edf2f7%22 width=%22150%22 height=%22150%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 font-family=%22sans-serif%22 font-size=%2214%22 fill=%22%23999%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22%3EPas d%27image%3C/text%3E%3C/svg%3E" alt="Aperçu">
                    </div>
                    <label class="btn-upload" for="imageInput"><i class="fas fa-image"></i> Choisir une image</label>
                    <input type="file" id="imageInput" name="image" accept="image/*" style="display:none;">
                    <p class="text-muted mt-2">Format: JPG, PNG, GIF, WEBP - Max 5 MB</p>
                </div>

                <div class="col-md-6">
                    <label for="nom" class="form-label">Nom du produit *</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="prix" class="form-label">Prix (DT) *</label>
                    <input type="number" class="form-control" id="prix" name="prix" step="0.01" min="0" value="<?= htmlspecialchars($_POST['prix'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="quantiteStock" class="form-label">Quantité en stock *</label>
                    <input type="number" class="form-control" id="quantiteStock" name="quantiteStock" min="0" value="<?= htmlspecialchars($_POST['quantiteStock'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="dateExpiration" class="form-label">Date d'expiration *</label>
                    <input type="date" class="form-control" id="dateExpiration" name="dateExpiration" value="<?= htmlspecialchars($_POST['dateExpiration'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="categorie" class="form-label">Catégorie</label>
                    <input type="text" class="form-control" id="categorie" name="categorie" value="<?= htmlspecialchars($_POST['categorie'] ?? '') ?>" placeholder="Ex: Fruits, Légumes, Protéines">
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="estDurable" name="estDurable" <?= isset($_POST['estDurable']) ? 'checked' : '' ?> >
                        <label class="form-check-label" for="estDurable">Produit durable</label>
                    </div>
                </div>
                <div class="col-12 d-flex flex-wrap gap-3">
                    <button type="submit" class="btn btn-submit btn-custom">
                        <i class="fas fa-save"></i> Ajouter le produit
                    </button>
                    <a href="afficherProduit.php" class="btn btn-cancel btn-custom">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('imageInput').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
