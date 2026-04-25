<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/CategorieController.php';
require_once __DIR__ . '/../../controller/ProduitController.php';
require_once __DIR__ . '/../../model/Produit.php';

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$produitController = new ProduitController();
$categorieController = new CategorieController();
$produit = $produitController->getProduitById($id);
$categories = $categorieController->getAllCategories();

if (!$produit) {
    header('Location: afficherProduit.php');
    exit;
}

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = (float) ($_POST['prix'] ?? 0);
    $quantiteStock = (int) ($_POST['quantiteStock'] ?? 0);
    $dateExpiration = trim($_POST['dateExpiration'] ?? '');
    $idCategorie = !empty($_POST['id_categorie']) ? (int) $_POST['id_categorie'] : null;
    $image = $produit['image'] ?? '';

    if (!empty($_FILES['image']['name'])) {
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($fileExt, $allowedExtensions, true) && $_FILES['image']['size'] <= $maxFileSize) {
            $image = time() . '_' . uniqid() . '.' . $fileExt;
            move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . $image);
        }
    }

    if ($nom === '' || strlen($nom) < 2 || $prix <= 0 || $prix > 99999 || $quantiteStock < 0 || $quantiteStock > 99999 || $dateExpiration === '') {
        $erreur = "Veuillez corriger les champs obligatoires.";
    } elseif (strtotime($dateExpiration) < strtotime(date('Y-m-d'))) {
        $erreur = "La date d'expiration ne peut pas être dans le passé.";
    } else {
        $updated = new Produit($id, $nom, $description, $quantiteStock, $dateExpiration, $idCategorie, $prix, $image, determinerStatut($quantiteStock, $dateExpiration));
        $produitController->updateProduit($updated, $id);
        header('Location: afficherProduit.php');
        exit;
    }
}

include("header.php");
?>
<section class="section">
<div class="container py-4">
    <h2>Modifier le Produit</h2>
    <?php if ($erreur): ?><div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="row g-3" id="produitForm">
        <input type="hidden" name="id" value="<?= (int) $produit['id'] ?>">
        <div class="col-md-6"><label class="form-label">Nom *</label><input id="nom" type="text" name="nom" class="form-control" minlength="2" maxlength="100" value="<?= htmlspecialchars($produit['nom']) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Prix *</label><input id="prix" type="number" step="0.01" min="0.01" max="99999" name="prix" class="form-control" value="<?= htmlspecialchars($produit['prix']) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Quantité *</label><input id="quantiteStock" type="number" min="0" max="99999" name="quantiteStock" class="form-control" value="<?= (int) $produit['quantiteStock'] ?>" required></div>
        <div class="col-md-6"><label class="form-label">Date d'expiration *</label><input id="dateExpiration" type="date" name="dateExpiration" class="form-control" value="<?= htmlspecialchars($produit['dateExpiration']) ?>" required></div>
        <div class="col-md-6">
            <label class="form-label">Catégorie</label>
            <select name="id_categorie" class="form-select">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($categories as $categorie): ?>
                    <option value="<?= (int) $categorie['id_categorie'] ?>" <?= ((int) $produit['id_categorie'] === (int) $categorie['id_categorie']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($categorie['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Image</label>
            <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(this, 'preview')">
            <div class="mt-2">
                <?php
                $imgActuelle = $produit['image'] ?? '';
                $imgSrc = $imgActuelle ? (str_starts_with($imgActuelle, 'http') ? $imgActuelle : UPLOAD_URL . $imgActuelle) : '';
                ?>
                <?php if ($imgSrc): ?>
                    <img id="preview" src="<?= htmlspecialchars($imgSrc) ?>" alt="Image actuelle" style="height:120px;object-fit:cover;border-radius:8px;border:1px solid #ddd;">
                <?php else: ?>
                    <img id="preview" src="#" alt="Aperçu" style="display:none;height:120px;object-fit:cover;border-radius:8px;border:1px solid #ddd;">
                <?php endif; ?>
            </div>
        </div>
        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($produit['description'] ?? '') ?></textarea></div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-danger">Mettre à jour</button>
            <a class="btn btn-outline-secondary" href="afficherProduit.php">Annuler</a>
        </div>
    </form>
</div>
</section>
<script>
function previewImage(input, previewId) {
    var preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
<script src="/ryhem/view/assets/js/produit-validation.js"></script>
<?php include("footer.php"); ?>
