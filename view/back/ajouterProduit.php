<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/CategorieController.php';
require_once __DIR__ . '/../../controller/ProduitController.php';
require_once __DIR__ . '/../../model/Produit.php';

$categorieController = new CategorieController();
$produitController   = new ProduitController();
$categories = $categorieController->getAllCategories();
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = (float)($_POST['prix'] ?? 0);
    $quantiteStock = (int)($_POST['quantiteStock'] ?? 0);
    $dateExpiration = trim($_POST['dateExpiration'] ?? '');
    $idCategorie = !empty($_POST['id_categorie']) ? (int)$_POST['id_categorie'] : null;
    $image = '';

    if (!empty($_FILES['image']['name'])) {
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($fileExt, $allowedExtensions, true) && $_FILES['image']['size'] <= $maxFileSize) {
            $image = time().'_'.uniqid().'.'.$fileExt;
            move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR.$image);
        }
    }

    if ($nom===''||strlen($nom)<2||$prix<=0||$prix>99999||$quantiteStock<0||$quantiteStock>99999||$dateExpiration==='') {
        $erreur = "Please fill in all required fields correctly.";
    } elseif (strtotime($dateExpiration) < strtotime(date('Y-m-d'))) {
        $erreur = "Expiration date cannot be in the past.";
    } else {
        $produit = new Produit(null,$nom,$description,$quantiteStock,$dateExpiration,$idCategorie,$prix,$image,'Disponible');
        $produitController->addProduit($produit);
        header('Location: afficherProduit.php');
        exit;
    }
}

include("header.php");
?>

<style>
.form-card { background:#fff;border-radius:12px;padding:28px;box-shadow:0 2px 10px rgba(0,0,0,0.06); }
.form-card h3 { font-family:'Raleway',sans-serif;font-size:0.85rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#2d2d2d;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #f0f0f0; }
.form-label { font-size:0.78rem;font-weight:600;letter-spacing:0.5px;color:#555;text-transform:uppercase; }
.form-control, .form-select { border-radius:8px;border:1px solid #e0e0e0;font-size:0.85rem;padding:9px 12px; }
.form-control:focus, .form-select:focus { border-color:#e74c3c;box-shadow:0 0 0 3px rgba(231,76,60,0.1); }
.btn-save { background:#e74c3c;color:white;border:none;border-radius:8px;padding:10px 24px;font-size:0.85rem;font-weight:600;letter-spacing:0.5px; }
.btn-save:hover { background:#c0392b;color:white; }
.btn-cancel { background:#fff;color:#333;border:1px solid #e0e0e0;border-radius:8px;padding:10px 24px;font-size:0.85rem;text-decoration:none; }
.btn-cancel:hover { background:#f5f5f5;color:#333; }
</style>

<div class="page-body">
  <div class="form-card">
    <h3><i class="bi bi-plus-circle me-2" style="color:#e74c3c;"></i>Add New Product</h3>
    <?php if ($erreur): ?><div class="alert alert-danger mb-3"><?= htmlspecialchars($erreur) ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Name *</label>
        <input type="text" name="nom" class="form-control" minlength="2" maxlength="100" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Price (DT) *</label>
        <input type="number" step="0.01" min="0.01" max="99999" name="prix" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Stock Quantity *</label>
        <input type="number" min="0" max="99999" name="quantiteStock" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Expiration Date *</label>
        <input type="date" name="dateExpiration" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Category</label>
        <select name="id_categorie" class="form-select">
          <option value="">— Select —</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= (int)$cat['id_categorie'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Image</label>
        <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(this,'preview')">
        <div class="mt-2">
          <img id="preview" src="#" alt="Preview" style="display:none;height:110px;object-fit:cover;border-radius:8px;border:1px solid #ddd;">
        </div>
      </div>
      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3"></textarea>
      </div>
      <div class="col-12 d-flex gap-2 mt-2">
        <button class="btn-save">Save Product</button>
        <a href="afficherProduit.php" class="btn-cancel">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script>
function previewImage(input,id){var p=document.getElementById(id);if(input.files&&input.files[0]){var r=new FileReader();r.onload=function(e){p.src=e.target.result;p.style.display='block';};r.readAsDataURL(input.files[0]);}}
</script>
<?php include("footer.php"); ?>
