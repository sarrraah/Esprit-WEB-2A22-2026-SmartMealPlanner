<?php
require_once __DIR__ . '/../../controller/ProduitController.php';
require_once __DIR__ . '/../../controller/CategorieController.php';

$produitController   = new ProduitController();
$categorieController = new CategorieController();
$allProduits   = $produitController->listProduits();
$allCategories = $categorieController->getAllCategories();

// Filter by category if provided
$filterCategorie = (int)($_GET['categorie'] ?? 0);
$filterCatNom    = '';

foreach ($allProduits as &$p) { $p['statut'] = determinerStatut($p['quantiteStock'], $p['dateExpiration']); }
unset($p);

if ($filterCategorie > 0) {
    $catObj = $categorieController->getCategorieById($filterCategorie);
    $filterCatNom = $catObj['nom'] ?? '';
    $produits = array_filter($allProduits, function($p) use ($filterCategorie) {
        return (int)($p['categorie'] ?? 0) === $filterCategorie
            || (int)($p['id_categorie'] ?? 0) === $filterCategorie;
    });
    $produits = array_values($produits);
} else {
    $produits = $allProduits;
}

$totalProduits   = count($allProduits);
$totalCategories = count($allCategories);
$totalStock      = array_sum(array_column($allProduits, 'quantiteStock'));
$totalDispo      = count(array_filter($allProduits, fn($p) => $p['statut'] === 'Disponible'));

include("header.php");
?>

<style>
.dashboard-banner {
  background: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)),
              url('https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=1200') center/cover no-repeat;
  border-radius: 14px;
  padding: 36px 32px;
  margin-bottom: 28px;
  color: white;
}
.dashboard-banner h2 {
  font-family: 'Raleway', sans-serif !important;
  font-size: 1.8rem;
  font-weight: 300;
  letter-spacing: 5px;
  text-transform: uppercase;
  margin: 0 0 8px;
  color: white !important;
  text-shadow: none !important;
}
.dashboard-banner h2 span {
  font-weight: 700;
  color: #e74c3c !important;
}
.dashboard-banner p {
  font-size: 0.85rem;
  color: rgba(255,255,255,0.75) !important;
  margin: 0;
  font-weight: 300;
  letter-spacing: 1px;
  font-family: 'Raleway', sans-serif !important;
}

.stat-card {
  background: #fff;
  border-radius: 12px;
  padding: 18px 20px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.06);
  display: flex;
  align-items: center;
  gap: 16px;
}
.stat-icon {
  width: 46px; height: 46px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.3rem; flex-shrink: 0;
}
.stat-label { font-size: 0.72rem; color: #999; letter-spacing: 1px; text-transform: uppercase; font-weight: 300; font-family: 'Raleway', sans-serif !important; }
.stat-value { font-size: 1.9rem; font-weight: 700; color: #2d2d2d; line-height: 1; font-family: 'Raleway', sans-serif !important; }

.section-card {
  background: #fff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.06);
}
.section-card-title {
  font-family: 'Raleway', sans-serif;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #2d2d2d;
  display: flex; align-items: center; gap: 8px;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid #f0f0f0;
}
.section-card-title i { color: #e74c3c; }

.btn-action-primary {
  background: #e74c3c; color: white; border: none;
  border-radius: 8px; padding: 10px 16px;
  font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px;
  width: 100%; text-align: center; text-decoration: none;
  display: flex; align-items: center; justify-content: center; gap: 6px;
  transition: 0.2s; margin-bottom: 8px;
}
.btn-action-primary:hover { background: #c0392b; color: white; }
.btn-action-secondary {
  background: #fff; color: #333; border: 1px solid #e0e0e0;
  border-radius: 8px; padding: 9px 16px;
  font-size: 0.8rem; font-weight: 400;
  width: 100%; text-align: center; text-decoration: none;
  display: flex; align-items: center; justify-content: center; gap: 6px;
  transition: 0.2s; margin-bottom: 8px;
}
.btn-action-secondary:hover { background: #f5f5f5; color: #333; }

.filter-input {
  border-radius: 8px; border: 1px solid #e0e0e0;
  padding: 8px 12px; font-size: 0.82rem; width: 100%;
  outline: none; font-family: 'Raleway', sans-serif;
}
.filter-input:focus { border-color: #e74c3c; }

.table th {
  font-family: 'Raleway', sans-serif;
  font-size: 0.7rem; letter-spacing: 1.5px;
  text-transform: uppercase; color: #999;
  font-weight: 600; border-bottom: 2px solid #f0f0f0;
  padding: 10px 12px;
}
.table td { padding: 10px 12px; font-size: 0.83rem; vertical-align: middle; border-bottom: 1px solid #f8f8f8; }
.table tbody tr:hover { background: #fafafa; }

.badge-dispo   { background: #e8f5e9; color: #2e7d32; border-radius: 20px; padding: 3px 10px; font-size: 11px; font-weight: 600; }
.badge-rupture { background: #fdecea; color: #c62828; border-radius: 20px; padding: 3px 10px; font-size: 11px; font-weight: 600; }
.badge-epuise  { background: #f5f5f5; color: #757575; border-radius: 20px; padding: 3px 10px; font-size: 11px; font-weight: 600; }

.btn-edit   { background: #fff8e1; color: #f57f17; border: none; border-radius: 6px; padding: 5px 10px; font-size: 12px; }
.btn-delete { background: #fdecea; color: #c62828; border: none; border-radius: 6px; padding: 5px 10px; font-size: 12px; }
.btn-edit:hover   { background: #fff3cd; }
.btn-delete:hover { background: #ffcdd2; }
</style>

<div class="page-body">

  <!-- BANNER -->
  <div class="dashboard-banner">
    <h2>Welcome to <span>SmartMeal</span></h2>
    <p>Manage your products, categories and inventory.</p>
  </div>

  <?php if ($filterCategorie > 0): ?>
  <!-- Category filter indicator -->
  <div style="background:#e8f0fe;border-radius:10px;padding:12px 18px;margin-bottom:20px;display:flex;align-items:center;justify-content:between;gap:12px;">
    <div style="flex:1;display:flex;align-items:center;gap:10px;">
      <i class="bi bi-funnel-fill" style="color:#1a73e8;"></i>
      <span style="font-size:0.85rem;color:#1a73e8;font-weight:600;">
        Showing products from: <strong><?= htmlspecialchars($filterCatNom) ?></strong>
        (<?= count($produits) ?> product<?= count($produits) !== 1 ? 's' : '' ?>)
      </span>
    </div>
    <a href="afficherProduit.php" style="background:#1a73e8;color:white;border-radius:6px;padding:5px 14px;font-size:12px;font-weight:600;text-decoration:none;">
      <i class="bi bi-x me-1"></i> Clear filter
    </a>
  </div>
  <?php endif; ?>

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
    <div class="col-md-3">
      <div class="stat-card">
        <div class="stat-icon" style="background:#fff8e1;"><i class="bi bi-stack" style="color:#f57f17;"></i></div>
        <div><div class="stat-label">Total Stock</div><div class="stat-value"><?= $totalStock ?></div></div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card">
        <div class="stat-icon" style="background:#e8f5e9;"><i class="bi bi-check-circle" style="color:#2e7d32;"></i></div>
        <div><div class="stat-label">Available</div><div class="stat-value"><?= $totalDispo ?></div></div>
      </div>
    </div>
  </div>

  <!-- MAIN ROW -->
  <div class="row g-3">

    <!-- LEFT: Products Table -->
    <div class="col-lg-8">
      <div class="section-card">
        <div class="section-card-title">
          <i class="bi bi-clock-history"></i> Recent Products
          <a href="ajouterProduit.php" class="ms-auto" style="background:#e74c3c;color:white;border-radius:20px;padding:4px 14px;font-size:11px;text-decoration:none;font-weight:600;">+ Add</a>
        </div>

        <!-- Filters -->
        <div class="row g-2 mb-3">
          <div class="col-md-5">
            <input type="text" id="search-produit" class="filter-input" placeholder="Search products...">
          </div>
          <div class="col-md-4">
            <select id="filter-statut" class="filter-input">
              <option value="">All statuses</option>
              <option value="Disponible">Available</option>
              <option value="Rupture">Out of stock</option>
              <option value="Épuisé">Expired</option>
            </select>
          </div>
          <div class="col-md-3">
            <select id="filter-tri" class="filter-input">
              <option value="">— Sort —</option>
              <option value="nom-asc">Name A → Z</option>
              <option value="nom-desc">Name Z → A</option>
              <option value="prix-asc">Price ↑</option>
              <option value="prix-desc">Price ↓</option>
              <option value="stock-asc">Stock ↑</option>
              <option value="stock-desc">Stock ↓</option>
            </select>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table" id="table-produits">
            <thead>
              <tr>
                <th>Photo</th><th>Name</th><th>Category</th>
                <th>Price</th><th>Stock</th><th>Status</th><th>Actions</th>
              </tr>
            </thead>
            <tbody id="tbody-produits">
            <?php foreach ($produits as $p):
              $img = $p['image'] ?? '';
              if (empty($img))                         $imgSrc = '';
              elseif (str_starts_with($img,'http'))    $imgSrc = $img;
              elseif (str_starts_with($img,'meals/'))  $imgSrc = '../../view/assets/img/'.$img;
              else                                      $imgSrc = UPLOAD_URL.$img;
              $badgeCls = match($p['statut']) { 'Disponible'=>'badge-dispo','Rupture'=>'badge-rupture',default=>'badge-epuise' };
              $badgeTxt = match($p['statut']) { 'Disponible'=>'Available','Rupture'=>'Out of stock',default=>'Expired' };
            ?>
              <tr data-nom="<?= htmlspecialchars(strtolower($p['nom']),ENT_QUOTES) ?>"
                  data-statut="<?= htmlspecialchars($p['statut'],ENT_QUOTES) ?>"
                  data-prix="<?= (float)$p['prix'] ?>"
                  data-stock="<?= (int)$p['quantiteStock'] ?>">
                <td>
                  <?php if ($imgSrc): ?>
                    <img src="<?= htmlspecialchars($imgSrc) ?>" style="width:44px;height:44px;object-fit:cover;border-radius:8px;">
                  <?php else: ?>
                    <div style="width:44px;height:44px;background:#f0f0f0;border-radius:8px;display:flex;align-items:center;justify-content:center;"><i class="bi bi-image text-muted"></i></div>
                  <?php endif; ?>
                </td>
                <td><strong><?= htmlspecialchars($p['nom']) ?></strong></td>
                <td><?= htmlspecialchars($p['categorie_nom'] ?? '—') ?></td>
                <td><?= number_format((float)$p['prix'],2,',',' ') ?> DT</td>
                <td><?= (int)$p['quantiteStock'] ?></td>
                <td><span class="<?= $badgeCls ?>"><?= $badgeTxt ?></span></td>
                <td>
                  <a href="modifierProduit.php?id=<?= (int)$p['id'] ?>" class="btn-edit"><i class="bi bi-pencil"></i></a>
                  <a href="supprimerProduit.php?id=<?= (int)$p['id'] ?>" class="btn-delete ms-1"><i class="bi bi-trash"></i></a>
                </td>
              </tr>
            <?php endforeach; ?>
            <tr id="no-result" style="display:none;">
              <td colspan="7" class="text-center text-muted py-3">No products found.</td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- RIGHT: Categories + Quick Actions -->
    <div class="col-lg-4">

      <!-- Categories by count -->
      <div class="section-card mb-3">
        <div class="section-card-title"><i class="bi bi-tags"></i> Categories</div>
        <?php
        $catCounts = [];
        foreach ($allProduits as $p) {
          $cat = $p['categorie_nom'] ?? 'Uncategorized';
          $catCounts[$cat] = ($catCounts[$cat] ?? 0) + 1;
        }
        arsort($catCounts);
        foreach ($catCounts as $cat => $cnt): ?>
          <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid #f5f5f5;">
            <span style="font-size:0.83rem;"><?= htmlspecialchars($cat) ?></span>
            <span style="background:#e74c3c;color:white;border-radius:12px;padding:2px 10px;font-size:11px;font-weight:700;"><?= $cnt ?></span>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Quick Actions -->
      <div class="section-card">
        <div class="section-card-title"><i class="bi bi-lightning-charge"></i> Quick Actions</div>
        <a href="ajouterProduit.php" class="btn-action-primary"><i class="bi bi-plus-circle"></i> Add a Product</a>
        <a href="ajouterCategorie.php" class="btn-action-secondary"><i class="bi bi-plus-circle"></i> Add a Category</a>
        <a href="afficherCategorie.php" class="btn-action-secondary"><i class="bi bi-tags"></i> Manage Categories</a>
      </div>

    </div>
  </div>
</div>

<script>
var tbody = document.getElementById('tbody-produits');
Array.from(tbody.querySelectorAll('tr:not(#no-result)')).forEach(function(r,i){ r.dataset.index=i; });

function filtrerEtTrier() {
  var q=document.getElementById('search-produit').value.toLowerCase().trim();
  var st=document.getElementById('filter-statut').value;
  var tri=document.getElementById('filter-tri').value;
  var rows=Array.from(tbody.querySelectorAll('tr:not(#no-result)'));
  var visible=0;
  rows.forEach(function(r){
    var matchQ=!q||r.dataset.nom.includes(q);
    var matchS=!st||r.dataset.statut===st;
    r.style.display=(matchQ&&matchS)?'':'none';
    if(matchQ&&matchS) visible++;
  });
  if(tri){
    var field=tri.split('-')[0],dir=tri.split('-')[1];
    var vis=rows.filter(function(r){return r.style.display!=='none';});
    vis.sort(function(a,b){
      if(field==='nom') return dir==='asc'?(a.dataset.nom||'').localeCompare(b.dataset.nom||''):(b.dataset.nom||'').localeCompare(a.dataset.nom||'');
      if(field==='prix'){var va=parseFloat(a.dataset.prix)||0,vb=parseFloat(b.dataset.prix)||0;return dir==='asc'?va-vb:vb-va;}
      if(field==='stock'){var va=parseInt(a.dataset.stock)||0,vb=parseInt(b.dataset.stock)||0;return dir==='asc'?va-vb:vb-va;}
    });
    vis.forEach(function(r){tbody.insertBefore(r,document.getElementById('no-result'));});
  }
  document.getElementById('no-result').style.display=visible===0?'':'none';
}
document.getElementById('search-produit').addEventListener('input',filtrerEtTrier);
document.getElementById('filter-statut').addEventListener('change',filtrerEtTrier);
document.getElementById('filter-tri').addEventListener('change',filtrerEtTrier);
</script>

<?php include("footer.php"); ?>
