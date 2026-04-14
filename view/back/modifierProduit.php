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
    <title>Modifier un produit - Smart Meal Planner</title>
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

        .form-container {
            max-width: 980px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 30px;
            padding: 45px;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.08);
        }

        .form-title {
            color: #e63946;
            font-weight: 800;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 2rem;
        }

        .form-title i {
            font-size: 2rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            transition: 0.3s;
            font-size: 0.95rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #e63946;
            box-shadow: 0 0 0 3px rgba(230, 57, 70, 0.1);
            outline: none;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .checkbox-section {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        .checkbox-section input {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .checkbox-section label {
            margin: 0;
            cursor: pointer;
            font-weight: 500;
        }

        /* Image Preview */
        .image-section {
            background: #f8f4f7;
            padding: 25px;
            border-radius: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(230, 57, 70, 0.12);
            display: grid;
            gap: 20px;
            align-items: center;
            justify-items: center;
        }

        .image-preview {
            position: relative;
            width: 100%;
            max-width: 420px;
            height: 360px;
            margin-bottom: 15px;
            border-radius: 20px;
            overflow: hidden;
            background: #f1f3f8;
            box-shadow: inset 0 0 0 1px rgba(229, 57, 70, 0.1);
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 20px;
            transition: transform 0.4s ease;
        }

        .image-preview img:hover {
            transform: scale(1.03);
        }

        .image-input-wrapper input[type="file"] {
            display: none;
        }

        .btn-upload {
            background: linear-gradient(135deg, #e63946, #f15b6c);
            color: white;
            padding: 12px 24px;
            border-radius: 14px;
            cursor: pointer;
            font-weight: 700;
            display: inline-block;
            transition: 0.3s;
            border: none;
            box-shadow: 0 10px 30px rgba(230, 57, 70, 0.18);
        }

        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(230, 57, 70, 0.22);
        }

        .file-name {
            font-size: 0.9rem;
            color: #999;
            margin-top: 8px;
        }

        /* Buttons */
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-custom {
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-ajouter {
            background: linear-gradient(135deg, #e63946, #f15b6c);
            color: white;
            flex: 1;
            box-shadow: 0 10px 25px rgba(230, 57, 70, 0.2);
        }

        .btn-ajouter:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(230, 57, 70, 0.28);
            color: white;
        }

        .btn-annuler {
            background: #e0e0e0;
            color: #333;
            flex: 1;
        }

        .btn-annuler:hover {
            background: #d0d0d0;
            color: #333;
        }

        /* Alerts */
        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <span class="navbar-brand">
                <i class="fas fa-utensils"></i> Smart Meal Planner
            </span>
        </div>
    </nav>

    <!-- Form -->
    <div class="container-lg">
        <div class="form-container">
            <h1 class="form-title">
                <i class="fas fa-edit"></i>
                Modifier un Produit
            </h1>

            <!-- Messages -->
            <?php if (!empty($succes)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($succes) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($erreur)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($erreur) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="modifierProduit.php?id=<?= $produit['id'] ?>" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $produit['id'] ?>">
                
                <!-- Image Section -->
                <div class="image-section">
                    <p style="font-weight: 600; margin-bottom: 15px;">Photo du produit</p>
                    
                    <div class="image-preview">
                        <img id="imagePreview" 
                             src="<?php if (!empty($produit['image']) && file_exists(UPLOAD_DIR . $produit['image'])): ?>
                                   <?= UPLOAD_URL . htmlspecialchars($produit['image']) ?>
                                 <?php else: ?>
                                   data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22150%22 height=%22150%22 viewBox=%220 0 150 150%22%3E%3Crect fill=%22%23f0f0f0%22 width=%22150%22 height=%22150%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 font-family=%22sans-serif%22 font-size=%2214%22 fill=%22%23999%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22%3EPas d%27image%3C/text%3E%3C/svg%3E
                                 <?php endif; ?>" 
                             alt="Aperçu">
                    </div>

                    <div class="image-input-wrapper">
                        <label for="imageInput" class="btn-upload">
                            <i class="fas fa-image"></i> Modifier l'image
                        </label>
                        <input type="file" id="imageInput" name="image" accept="image/*">
                    </div>
                    <p class="file-name">Format: JPG, PNG, GIF, WEBP - Max 5 MB</p>
                </div>

                <!-- Form Fields -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom du produit *</label>
                        <input type="text" class="form-control" id="nom" name="nom" required value="<?= htmlspecialchars($produit['nom']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="prix">Prix (DT) *</label>
                        <input type="number" class="form-control" id="prix" name="prix" step="0.01" min="0" required value="<?= htmlspecialchars($produit['prix']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="quantiteStock">Quantité en stock</label>
                        <input type="number" class="form-control" id="quantiteStock" name="quantiteStock" min="0" value="<?= htmlspecialchars($produit['quantiteStock']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="dateExpiration">Date d'expiration</label>
                        <input type="date" class="form-control" id="dateExpiration" name="dateExpiration" value="<?= htmlspecialchars($produit['dateExpiration'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="categorie">Catégorie</label>
                        <input type="text" class="form-control" id="categorie" name="categorie" 
                               value="<?= htmlspecialchars($produit['categorie'] ?? $produit['id_categorie'] ?? 'Autre') ?>" 
                               placeholder="Ex: Fruits, Légumes, Protéines">
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($produit['description'] ?? '') ?></textarea>
                </div>

                <div class="checkbox-section">
                    <input type="checkbox" id="estDurable" name="estDurable" <?= !empty($produit['estDurable']) ? 'checked' : '' ?>>
                    <label for="estDurable">Produit durable</label>
                </div>

                <!-- Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn-custom btn-ajouter">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>
                    <a href="afficherProduit.php" class="btn-custom btn-annuler">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Prévisualiser l'image avant upload
        document.getElementById('imageInput')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('imagePreview').src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</html>
