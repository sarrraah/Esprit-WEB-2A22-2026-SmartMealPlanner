<?php
// admin/create.php - Ajouter un nouveau produit
require_once 'config.php';

$error = '';
$success = '';

// Fonction pour uploader l'image
function uploadImage($file) {
    $target_dir = "uploads/";
    
    // Créer le dossier s'il n'existe pas
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowed_extensions = array("jpg", "jpeg", "png", "gif", "webp");
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return false;
    }
    
    // Générer un nom unique
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    }
    
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_produit = trim($_POST['nom_produit'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = floatval($_POST['prix'] ?? 0);
    $quantiteStock = intval($_POST['quantiteStock'] ?? 0);
    $dateExpiration = $_POST['dateExpiration'] ?? '';
    $categorie = $_POST['categorie'] ?? 'fruits';
    $image = '';
    $statut = $_POST['statut'] ?? 'disponible';
    
    // Gestion de l'upload d'image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploaded_image = uploadImage($_FILES['image']);
        if ($uploaded_image) {
            $image = $uploaded_image;
        } else {
            $error = "Format d'image non supporté. Utilisez JPG, PNG, GIF ou WEBP.";
        }
    }
    
    // Validation
    if (empty($nom_produit) || empty($description) || $prix <= 0) {
        $error = "Veuillez remplir tous les champs obligatoires";
    } elseif (empty($error)) {
        try {
            // Déterminer le statut en fonction du stock
            if ($quantiteStock <= 0) {
                $statut = 'epuise';
            } elseif ($quantiteStock < 5) {
                $statut = 'rupture';
            } else {
                $statut = 'disponible';
            }
            
            $sql = "INSERT INTO produits (nom_produit, description, prix, quantiteStock, dateExpiration, categorie, image, statut) 
                    VALUES (:nom_produit, :description, :prix, :quantiteStock, :dateExpiration, :categorie, :image, :statut)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nom_produit' => $nom_produit,
                ':description' => $description,
                ':prix' => $prix,
                ':quantiteStock' => $quantiteStock,
                ':dateExpiration' => $dateExpiration,
                ':categorie' => $categorie,
                ':image' => $image,
                ':statut' => $statut
            ]);
            
            $_SESSION['success_message'] = "Produit ajouté avec succès !";
            header("Location: read.php");
            exit();
            
        } catch(PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un produit - Smart Meal Planner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #e0e0e0 0%, #e0e0e0 100%);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            background: white;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #ff4444 !important;
        }

        .navbar-brand i {
            margin-right: 10px;
        }

        .nav-link {
            color: #333 !important;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 25px;
            padding: 8px 20px !important;
        }

        .nav-link:hover, .nav-link.active {
            background: #ff4444;
            color: white !important;
        }

        /* Container principal */
        .main-container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header */
        .page-header {
            background: white;
            border-radius: 20px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .page-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        .page-header h1 i {
            color: #ff4444;
            margin-right: 10px;
        }

        .btn-back {
            background: #6c757d;
            color: white;
            border-radius: 25px;
            padding: 10px 25px;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background: #5a6268;
            color: white;
            transform: translateX(-5px);
        }

        /* Formulaire */
        .form-wrapper {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .form-label i {
            color: #ff4444;
            margin-right: 8px;
        }

        .required::after {
            content: " *";
            color: #ff4444;
        }

        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #ff4444;
            box-shadow: 0 0 0 0.2rem rgba(255,68,68,0.25);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        /* Upload d'image */
        .upload-container {
            border: 2px dashed #ff4444;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            background: #fff5f5;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-container:hover {
            background: #ffe0e0;
            border-color: #cc0000;
        }

        .upload-icon {
            font-size: 48px;
            color: #ff4444;
            margin-bottom: 10px;
        }

        .upload-text {
            color: #666;
            margin-bottom: 10px;
        }

        .upload-button {
            background: #ff4444;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 8px 20px;
            font-size: 14px;
            cursor: pointer;
            display: inline-block;
            margin-top: 10px;
        }

        .upload-button:hover {
            background: #cc0000;
        }

        .image-preview {
            margin-top: 15px;
            text-align: center;
            display: none;
        }

        .image-preview img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 10px;
            border: 2px solid #ff4444;
            padding: 5px;
        }

        .remove-image {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 5px 15px;
            margin-top: 10px;
            cursor: pointer;
            font-size: 12px;
        }

        .remove-image:hover {
            background: #c82333;
        }

        /* Bouton submit */
        .btn-submit {
            background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 12px 40px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,68,68,0.3);
        }

        /* Alertes */
        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 20px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }
            
            .form-wrapper {
                padding: 1.5rem;
            }
            
            .page-header {
                padding: 1rem 1.5rem;
            }
            
            .page-header h1 {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="read.php">
                <i class="fas fa-utensils"></i>
                Smart Meal Planner
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="read.php">
                            <i class="fas fa-box me-1"></i> Produits
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="create.php">
                            <i class="fas fa-plus me-1"></i> Ajouter
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center">
            <h1>
                <i class="fas fa-plus-circle"></i>
                Ajouter un nouveau produit
            </h1>
            <a href="read.php" class="btn-back btn">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>

        <!-- Messages -->
        <?php if($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Formulaire -->
        <div class="form-wrapper">
            <form method="POST" action="" enctype="multipart/form-data" id="productForm">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label required">
                            <i class="fas fa-tag"></i>Nom du produit
                        </label>
                        <input type="text" name="nom_produit" class="form-control" 
                               placeholder="Ex: Pommes Bio" required>
                    </div>

                    <div class="col-md-3 mb-4">
                        <label class="form-label required">
                            <i class="fas fa-money-bill-wave"></i>Prix (DT)
                        </label>
                        <input type="number" step="0.01" name="prix" class="form-control" 
                               placeholder="0.00" required>
                    </div>

                    <div class="col-md-3 mb-4">
                        <label class="form-label">
                            <i class="fas fa-layer-group"></i>Catégorie
                        </label>
                        <select name="categorie" class="form-select">
                            <option value="fruits">🍎 Fruits</option>
                            <option value="legumes">🥕 Légumes</option>
                            <option value="viandes">🥩 Viandes</option>
                            <option value="poissons">🐟 Poissons</option>
                            <option value="produits_laitiers">🥛 Produits laitiers</option>
                            <option value="epicerie">🥫 Épicerie</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">
                            <i class="fas fa-boxes"></i>Quantité en stock
                        </label>
                        <input type="number" name="quantiteStock" class="form-control" 
                               placeholder="0" value="0">
                        <small class="text-muted">Le statut sera mis à jour automatiquement</small>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">
                            <i class="fas fa-calendar-alt"></i>Date d'expiration
                        </label>
                        <input type="date" name="dateExpiration" class="form-control">
                    </div>

                    <div class="col-12 mb-4">
                        <label class="form-label">
                            <i class="fas fa-image"></i>Photo du produit
                        </label>
                        <div class="upload-container" id="uploadContainer">
                            <div class="upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="upload-text">
                                Cliquez ou glissez-déposez une image ici
                            </div>
                            <div class="upload-text small text-muted">
                                Formats supportés: JPG, PNG, GIF, WEBP (Max 5MB)
                            </div>
                            <button type="button" class="upload-button" onclick="document.getElementById('imageFile').click()">
                                <i class="fas fa-folder-open"></i> Parcourir
                            </button>
                            <input type="file" name="image" id="imageFile" accept="image/*" style="display: none;">
                        </div>
                        <div class="image-preview" id="imagePreview">
                            <img id="previewImg" alt="Aperçu">
                            <br>
                            <button type="button" class="remove-image" id="removeImage">
                                <i class="fas fa-trash-alt"></i> Supprimer
                            </button>
                        </div>
                    </div>

                    <div class="col-12 mb-4">
                        <label class="form-label required">
                            <i class="fas fa-align-left"></i>Description
                        </label>
                        <textarea name="description" class="form-control" 
                                  placeholder="Décrivez votre produit..." required></textarea>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save me-2"></i>Enregistrer le produit
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Upload d'image depuis le PC
        const uploadContainer = document.getElementById('uploadContainer');
        const imageFile = document.getElementById('imageFile');
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        const removeImageBtn = document.getElementById('removeImage');

        // Click sur le container pour ouvrir le dialogue
        uploadContainer.addEventListener('click', function(e) {
            if (e.target !== removeImageBtn && !e.target.classList.contains('remove-image')) {
                imageFile.click();
            }
        });

        // Glisser-déposer
        uploadContainer.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadContainer.style.background = '#ffe0e0';
            uploadContainer.style.borderColor = '#cc0000';
        });

        uploadContainer.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadContainer.style.background = '#fff5f5';
            uploadContainer.style.borderColor = '#ff4444';
        });

        uploadContainer.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadContainer.style.background = '#fff5f5';
            uploadContainer.style.borderColor = '#ff4444';
            
            const file = e.dataTransfer.files[0];
            if (file && file.type.match('image.*')) {
                handleImageFile(file);
            } else {
                alert('Veuillez déposer une image valide');
            }
        });

        // Quand un fichier est sélectionné
        imageFile.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                handleImageFile(e.target.files[0]);
            }
        });

        // Gérer le fichier image
        function handleImageFile(file) {
            // Vérifier le type de fichier
            if (!file.type.match('image.*')) {
                alert('Veuillez sélectionner une image valide');
                return;
            }
            
            // Vérifier la taille (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('L\'image ne doit pas dépasser 5MB');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
                uploadContainer.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }

        // Supprimer l'image
        removeImageBtn.addEventListener('click', function() {
            imagePreview.style.display = 'none';
            uploadContainer.style.display = 'block';
            imageFile.value = '';
            previewImg.src = '';
        });

        // Validation du formulaire
        document.getElementById('productForm').addEventListener('submit', function(e) {
            const nom = document.querySelector('[name="nom_produit"]').value.trim();
            const description = document.querySelector('[name="description"]').value.trim();
            const prix = parseFloat(document.querySelector('[name="prix"]').value);
            
            if (!nom || !description || isNaN(prix) || prix <= 0) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires correctement');
            }
        });
    </script>
</body>
</html>