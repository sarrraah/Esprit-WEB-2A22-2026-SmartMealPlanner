<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/CategorieController.php';
require_once __DIR__ . '/../../controller/ProduitController.php';

$categorieController = new CategorieController();
$produitController   = new ProduitController();
$allCategories = $categorieController->getAllCategories();
$allProduits   = $produitController->listProduits();
$totalCategories = count($allCategories);
$totalProduits   = count($allProduits);
$categories = $allCategories;

include("header.php");
?>

<style>
.dashboard-banner {
  background: linear-gradient(rgba(0,0,0,0.55),rgba(0,0,0,0.55)),
              url('https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=1200') center/cover no-repeat;
  border-radius: 14px; padding: 36px 32px; margin-bottom: 28px; color: white;
}
.dashboard-banner h2 {
  font-family:'Raleway',sans-serif !important;
  font-size:1.8rem; font-weight:300; letter-spacing:5px;
  text-transform:uppercase; margin:0 0 8px;
  color:white !important; text-shadow:none !important;
}
.dashboard-banner h2 span { font-weight:700; color:#e74c3c !important; }
.dashboard-banner p {
  font-size:0.85rem; color:rgba(255,255,255,0.75) !important;
  margin:0; font-weight:300; letter-spacing:1px;
  font-family:'Raleway',sans-serif !important;
}
.stat-card { background:#fff;border-radius:12px;padding:18px 20px;box-shadow:0 2px 10px rgba(0,0,0,0.06);display:flex;align-items:center;gap:16px; }
.stat-icon { width:46px;height:46px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0; }
.stat-label { font-size:0.72rem;color:#999;letter-spacing:1px;text-transform:uppercase;font-weight:300; }
.stat-value { font-size:1.9rem;font-weight:700;color:#2d2d2d;line-height:1; }
.section-card { background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,0.06); }
.section-card-title { font-family:'Raleway',sans-serif;font-size:0.75rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#2d2d2d;display:flex;align-items:center;gap:8px;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #f0f0f0; }
.section-card-title i { color:#e74c3c; }
.btn-action-primary { background:#e74c3c;color:white;border:none;border-radius:8px;padding:10px 16px;font-size:0.8rem;font-weight:600;letter-spacing:0.5px;width:100%;text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:6px;transition:0.2s;margin-bottom:8px; }
.btn-action-primary:hover { background:#c0392b;color:white; }
.btn-action-secondary { background:#fff;color:#333;border:1px solid #e0e0e0;border-radius:8px;padding:9px 16px;font-size:0.8rem;font-weight:400;width:100%;text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:6px;transition:0.2s;margin-bottom:8px; }
.btn-action-secondary:hover { background:#f5f5f5;color:#333; }
.filter-input { border-radius:8px;border:1px solid #e0e0e0;padding:8px 12px;font-size:0.82rem;width:100%;outline:none;font-family:'Raleway',sans-serif; }
.filter-input:focus { border-color:#e74c3c; }
.table th { font-family:'Raleway',sans-serif;font-size:0.7rem;letter-spacing:1.5px;text-transform:uppercase;color:#999;font-weight:600;border-bottom:2px solid #f0f0f0;padding:10px 12px; }
.table td { padding:10px 12px;font-size:0.83rem;vertical-align:middle;border-bottom:1px solid #f8f8f8; }
.table tbody tr:hover { background:#fafafa; }
.btn-edit   { background:#fff8e1;color:#f57f17;border:none;border-radius:6px;padding:5px 10px;font-size:12px; }
.btn-delete { background:#fdecea;color:#c62828;border:none;border-radius:6px;padding:5px 10px;font-size:12px; }
.btn-edit:hover   { background:#fff3cd; }
.btn-delete:hover { background:#ffcdd2; }
</style>

<div class="page-body">

  <!-- BANNER -->
  <div class="dashboard-banner">
    <h2>Welcome to <span>SmartMeal</span></h2>
    <p>Manage your product categories.</p>
  </div>

  <!-- STATS -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="stat-card">
        <div class="stat-icon" style="background:#fdecea;"><i class="bi bi-box-seam text-danger"></i></div>
        <div><div class="stat-label">Total Products</div><div class="stat-value"><?= $totalProduits ?></div></div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card">
        <div class="stat-icon" style="background:#e8f0fe;"><i class="bi bi-tags" style="color:#1a73e8;"></i></div>
        <div><div class="stat-label">Categories</div><div class="stat-value"><?= $totalCategories ?></div></div>
      </div>
    </div>
  </div>

  <!-- MAIN ROW -->
  <div class="row g-3">
    <div class="col-lg-8">
      <div class="section-card">
        <div class="section-card-title">
          <i class="bi bi-tags"></i> All Categories
          <a href="ajouterCategorie.php" class="ms-auto" style="background:#e74c3c;color:white;border-radius:20px;padding:4px 14px;font-size:11px;text-decoration:none;font-weight:600;">+ Add</a>
        </div>
        <div class="mb-3">
          <input type="text" id="search-categorie" class="filter-input" placeholder="Search categories...">
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>Image</th><th>Name</th><th>Description</th><th>Products</th><th>Actions</th></tr></thead>
            <tbody id="tbody-categories">
            <?php foreach ($categories as $cat):
              $imgSrc = '';
              if (!empty($cat['image'])) $imgSrc = str_starts_with($cat['image'],'http') ? $cat['image'] : UPLOAD_URL.$cat['image'];
            ?>
              <tr data-nom="<?= htmlspecialchars(strtolower($cat['nom']),ENT_QUOTES) ?>">
                <td>
                  <?php if ($imgSrc): ?>
                    <img src="<?= htmlspecialchars($imgSrc) ?>" style="width:50px;height:50px;object-fit:cover;border-radius:8px;">
                  <?php else: ?>
                    <div style="width:50px;height:50px;background:#f0f0f0;border-radius:8px;display:flex;align-items:center;justify-content:center;"><i class="bi bi-image text-muted"></i></div>
                  <?php endif; ?>
                </td>
                <td><strong><?= htmlspecialchars($cat['nom']) ?></strong></td>
                <td style="font-size:0.8rem;color:#888;"><?= htmlspecialchars(mb_strimwidth($cat['description'] ?? '', 0, 60, '...')) ?></td>
                <td>
                  <a href="afficherProduit.php?categorie=<?= (int)$cat['id_categorie'] ?>"
                     style="background:#e8f0fe;color:#1a73e8;border:none;border-radius:6px;padding:5px 12px;font-size:12px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                    <i class="bi bi-eye"></i> View Products
                  </a>
                </td>
                <td>
                  <a href="modifierCategorie.php?id=<?= (int)$cat['id_categorie'] ?>" class="btn-edit"><i class="bi bi-pencil"></i></a>
                  <a href="supprimerCategorie.php?id=<?= (int)$cat['id_categorie'] ?>" class="btn-delete ms-1"><i class="bi bi-trash"></i></a>
                </td>
              </tr>
            <?php endforeach; ?>
            <tr id="no-result-cat" style="display:none;">
              <td colspan="5" class="text-center text-muted py-3">No categories found.</td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="section-card">
        <div class="section-card-title"><i class="bi bi-lightning-charge"></i> Quick Actions</div>
        <a href="ajouterCategorie.php" class="btn-action-primary"><i class="bi bi-plus-circle"></i> Add a Category</a>
        <a href="ajouterProduit.php" class="btn-action-secondary"><i class="bi bi-plus-circle"></i> Add a Product</a>
        <a href="afficherProduit.php" class="btn-action-secondary"><i class="bi bi-box-seam"></i> Manage Products</a>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('search-categorie').addEventListener('input', function() {
  var q = this.value.toLowerCase().trim();
  var rows = document.querySelectorAll('#tbody-categories tr:not(#no-result-cat)');
  var visible = 0;
  rows.forEach(function(r){ var match=!q||r.dataset.nom.includes(q); r.style.display=match?'':'none'; if(match)visible++; });
  document.getElementById('no-result-cat').style.display = visible===0?'':'none';
});
</script>

<?php include("footer.php"); ?>
