<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/CategorieController.php';

$controller = new CategorieController();
$categories = $controller->getAllCategories();

include("header.php");
?>
<section class="section">
<div class="container py-5">
    <h2 class="mb-4">Catégories</h2>
    <div class="row g-4">
        <?php foreach ($categories as $categorie): ?>
            <?php
            $imgSrc = '';
            if (!empty($categorie['image'])) {
                $imgSrc = str_starts_with($categorie['image'], 'http')
                    ? $categorie['image']
                    : UPLOAD_URL . $categorie['image'];
            }
            ?>
            <div class="col-md-4 col-sm-6">
                <a href="produitsParCategorie.php?id=<?= (int) $categorie['id_categorie'] ?>" class="text-decoration-none">
                    <div class="card h-100 shadow-sm overflow-hidden">
                        <?php if ($imgSrc): ?>
                            <img src="<?= htmlspecialchars($imgSrc) ?>" class="card-img-top" alt="<?= htmlspecialchars($categorie['nom']) ?>" style="height:200px;object-fit:cover;">
                        <?php else: ?>
                            <div style="height:200px;background:#f5f5f5;display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-tag text-muted" style="font-size:3rem;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title text-dark"><?= htmlspecialchars($categorie['nom']) ?></h5>
                            <p class="card-text text-muted small"><?= htmlspecialchars($categorie['description'] ?? '') ?></p>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</section>

<script>
function updateBadge() {
    var total = JSON.parse(localStorage.getItem('panier') || '[]').reduce(function(s, p) { return s + p.quantite; }, 0);
    var badge = document.getElementById('panier-badge');
    if (badge) { badge.textContent = total; badge.style.display = total > 0 ? 'inline-block' : 'none'; }
}
function ouvrirPanier() { window.location.href = 'produits.php'; }
updateBadge();
</script>

<?php include("footer.php"); ?>
