<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/CategorieController.php';
require_once __DIR__ . '/../../model/CategorieProduit.php';

$controller = new CategorieController();
$id         = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$categorie  = $controller->getCategorieById($id);

if (!$categorie) {
    header('Location: afficherCategorie.php');
    exit;
}

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom         = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image       = $categorie['image'] ?? '';

    if (!empty($_FILES['image']['name'])) {
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($fileExt, $allowedExtensions, true) && $_FILES['image']['size'] <= $maxFileSize) {
            $image = time() . '_' . uniqid() . '.' . $fileExt;
            move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . $image);
        } else {
            $erreur = 'Format image invalide ou fichier trop lourd (max 5MB).';
        }
    }

    if ($erreur === '' && $nom === '') {
        $erreur = 'Le nom est obligatoire.';
    }

    if ($erreur === '') {
        $controller->updateCategorie(new CategorieProduit($id, $nom, $description, $image), $id);
        header('Location: afficherCategorie.php');
        exit;
    }
}

$imageActuelle = $categorie['image'] ?? '';
$imgSrc = $imageActuelle ? UPLOAD_URL . $imageActuelle : '';

include("header.php");
?>
<section class="section">
<div class="container py-4">
    <h2>Modifier Catégorie</h2>
    <?php if ($erreur): ?><div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="row g-3">
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="col-md-6">
            <label class="form-label">Nom *</label>
            <input type="text" name="nom" id="cat-nom" class="form-control" minlength="2" maxlength="100" value="<?= htmlspecialchars($categorie['nom']) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Image</label>
            <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(this, 'preview')">
            <div class="mt-2">
                <?php if ($imgSrc): ?>
                    <img id="preview" src="<?= htmlspecialchars($imgSrc) ?>" alt="Image actuelle" style="height:120px;object-fit:cover;border-radius:8px;border:1px solid #ddd;">
                <?php else: ?>
                    <img id="preview" src="#" alt="Aperçu" style="display:none;height:120px;object-fit:cover;border-radius:8px;border:1px solid #ddd;">
                <?php endif; ?>
            </div>
        </div>
        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" id="cat-description" class="form-control" rows="4" maxlength="500"><?= htmlspecialchars($categorie['description'] ?? '') ?></textarea>
            <div class="form-text text-muted"><span id="desc-count">0</span>/500 caractères</div>
        </div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-danger">Mettre à jour</button>
            <a href="afficherCategorie.php" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
</section>
<script>
function previewImage(input, previewId) {
    var preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    }
}
var descEl = document.getElementById('cat-description');
var countEl = document.getElementById('desc-count');
if (descEl) {
    countEl.textContent = descEl.value.length;
    descEl.addEventListener('input', function() { countEl.textContent = this.value.length; });
}
</script>
<script src="/ryhem/view/assets/js/categorie-validation.js"></script>
<?php include("footer.php"); ?>
