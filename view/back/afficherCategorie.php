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

// Compute stats
foreach ($allProduits as &$p) { $p['statut'] = determinerStatut($p['quantiteStock'], $p['dateExpiration']); }
unset($p);
$totalDispo    = count(array_filter($allProduits, fn($p) => $p['statut'] === 'Disponible'));
$totalRupture  = count(array_filter($allProduits, fn($p) => $p['statut'] === 'Rupture'));
$totalEpuise   = count(array_filter($allProduits, fn($p) => $p['statut'] === 'Épuisé'));
$totalStock    = array_sum(array_column($allProduits, 'quantiteStock'));

// Products per category (for chart + table)
$catData      = [];
$catProdCount = [];
$catDispoRate = []; // % disponible par catégorie
$catStockData = []; // stock par catégorie

foreach ($allCategories as $cat) {
  $id  = (int)$cat['id_categorie'];
  $nom = $cat['nom'];

  $prodsCat = array_filter($allProduits, function($p) use ($id) {
    return (int)($p['categorie'] ?? $p['id_categorie'] ?? 0) === $id;
  });
  $prodsCat = array_values($prodsCat);

  $cnt   = count($prodsCat);
  $dispo = count(array_filter($prodsCat, fn($p) => $p['statut'] === 'Disponible'));
  $stock = array_sum(array_column($prodsCat, 'quantiteStock'));

  $catData[$nom]      = $cnt;
  $catProdCount[$id]  = $cnt;
  $catDispoRate[$nom] = $cnt > 0 ? round($dispo / $cnt * 100) : 0;
  $catStockData[$nom] = $stock;
}

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
      <div class="stat-card" style="border-left:4px solid #e74c3c;">
        <div class="stat-icon" style="background:#fdecea;"><i class="bi bi-box-seam text-danger"></i></div>
        <div>
          <div class="stat-label">Total Products</div>
          <div class="stat-value"><?= $totalProduits ?></div>
          <div style="font-size:0.7rem;color:#28a745;margin-top:2px;"><i class="bi bi-arrow-up-short"></i><?= $totalDispo ?> available</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card" style="border-left:4px solid #1a73e8;">
        <div class="stat-icon" style="background:#e8f0fe;"><i class="bi bi-tags" style="color:#1a73e8;"></i></div>
        <div>
          <div class="stat-label">Categories</div>
          <div class="stat-value"><?= $totalCategories ?></div>
          <div style="font-size:0.7rem;color:#999;margin-top:2px;">Active categories</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card" style="border-left:4px solid #f57f17;">
        <div class="stat-icon" style="background:#fff8e1;"><i class="bi bi-stack" style="color:#f57f17;"></i></div>
        <div>
          <div class="stat-label">Total Stock</div>
          <div class="stat-value"><?= $totalStock ?></div>
          <div style="font-size:0.7rem;color:#f57f17;margin-top:2px;"><i class="bi bi-exclamation-circle"></i> <?= $totalRupture ?> out of stock</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card" style="border-left:4px solid #2e7d32;">
        <div class="stat-icon" style="background:#e8f5e9;"><i class="bi bi-check-circle" style="color:#2e7d32;"></i></div>
        <div>
          <div class="stat-label">Available</div>
          <div class="stat-value"><?= $totalDispo ?></div>
          <div style="font-size:0.7rem;color:#2e7d32;margin-top:2px;"><?= $totalProduits > 0 ? round($totalDispo/$totalProduits*100) : 0 ?>% of total</div>
        </div>
      </div>
    </div>
  </div>

  <!-- CHARTS -->
  <div style="background:#fff;border-radius:14px;padding:20px 24px;box-shadow:0 2px 10px rgba(0,0,0,0.06);margin-bottom:28px;">
    <div style="font-family:'Raleway',sans-serif;font-size:0.75rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#2d2d2d;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;gap:8px;">
      <i class="bi bi-graph-up" style="color:#e74c3c;"></i> Analytics Overview
    </div>
    <div class="row g-3">
      <!-- Bar: products per category -->
      <div class="col-lg-4">
        <div style="background:#f9f9f9;border-radius:10px;padding:18px;">
          <div style="font-size:0.68rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#999;margin-bottom:14px;display:flex;align-items:center;gap:6px;">
            <i class="bi bi-bar-chart-fill" style="color:#1a73e8;"></i> Products per Category
          </div>
          <canvas id="chartCatProducts" height="220"></canvas>
        </div>
      </div>
      <!-- Horizontal bar: % disponibilité par catégorie -->
      <div class="col-lg-4">
        <div style="background:#f9f9f9;border-radius:10px;padding:18px;">
          <div style="font-size:0.68rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#999;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
            <i class="bi bi-check2-circle" style="color:#28a745;"></i> Availability Rate (%)
          </div>
          <canvas id="chartCatDispo" height="220"></canvas>
        </div>
      </div>
      <!-- Donut: répartition des catégories -->
      <div class="col-lg-4">
        <div style="background:#f9f9f9;border-radius:10px;padding:18px;text-align:center;">
          <div style="font-size:0.68rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#999;margin-bottom:12px;display:flex;align-items:center;justify-content:center;gap:6px;">
            <i class="bi bi-pie-chart-fill" style="color:#e74c3c;"></i> Category Share
          </div>
          <canvas id="chartCatShare" height="220" style="max-height:220px;"></canvas>
          <div style="margin-top:10px;font-size:0.7rem;display:flex;justify-content:center;gap:10px;flex-wrap:wrap;">
            <?php
            $palette = ['#e74c3c','#1a73e8','#f57f17','#2e7d32','#8e44ad','#16a085'];
            $i = 0;
            foreach ($catData as $nom => $cnt):
              $color = $palette[$i % count($palette)]; $i++;
            ?>
            <span><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= $color ?>;margin-right:4px;"></span><?= htmlspecialchars($nom) ?> (<?= $cnt ?>)</span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script>
  Chart.defaults.font.family = "'Raleway', sans-serif";
  Chart.defaults.font.size   = 10;

  new Chart(document.getElementById('chartCatProducts'), {
    type: 'bar',
    data: {
      labels: <?= json_encode(array_keys($catData)) ?>,
      datasets: [{ data: <?= json_encode(array_values($catData)) ?>, backgroundColor: ['#e74c3c','#1a73e8','#f57f17','#2e7d32','#8e44ad','#16a085'], borderRadius: 5, borderSkipped: false }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#eee' }, ticks: { stepSize: 1 } }, x: { grid: { display: false } } } }
  });

  // Availability rate per category (horizontal bar)
  new Chart(document.getElementById('chartCatDispo'), {
    type: 'bar',
    data: {
      labels: <?= json_encode(array_keys($catDispoRate)) ?>,
      datasets: [{
        data: <?= json_encode(array_values($catDispoRate)) ?>,
        backgroundColor: ['rgba(40,167,69,0.75)','rgba(26,115,232,0.75)','rgba(245,127,23,0.75)','rgba(46,125,50,0.75)','rgba(142,68,173,0.75)','rgba(22,160,133,0.75)'],
        borderRadius: 5, borderSkipped: false
      }]
    },
    options: {
      indexAxis: 'y',
      plugins: { legend: { display: false } },
      scales: {
        x: { beginAtZero: true, max: 100, grid: { color: '#eee' }, ticks: { callback: function(v){ return v+'%'; } } },
        y: { grid: { display: false } }
      }
    }
  });

  // Category share donut
  new Chart(document.getElementById('chartCatShare'), {
    type: 'doughnut',
    data: {
      labels: <?= json_encode(array_keys($catData)) ?>,
      datasets: [{
        data: <?= json_encode(array_values($catData)) ?>,
        backgroundColor: ['#e74c3c','#1a73e8','#f57f17','#2e7d32','#8e44ad','#16a085'],
        borderWidth: 2, borderColor: '#f9f9f9'
      }]
    },
    options: { cutout: '60%', maintainAspectRatio: false, plugins: { legend: { display: false } } }
  });

  new Chart(document.getElementById('chartCatStatus'), {
    type: 'doughnut',
    data: {
      labels: ['Available', 'Out of Stock', 'Expired'],
      datasets: [{ data: [<?= $totalDispo ?>, <?= $totalRupture ?>, <?= $totalEpuise ?>], backgroundColor: ['#28a745','#e74c3c','#adb5bd'], borderWidth: 2, borderColor: '#f9f9f9' }]
    },
    options: { cutout: '68%', maintainAspectRatio: false, plugins: { legend: { display: false } } }
  });

  <?php
  $catStockData = [];
  foreach ($allCategories as $cat) {
    $stock = array_sum(array_column(array_filter($allProduits, function($p) use ($cat) {
      return (int)($p['categorie'] ?? $p['id_categorie'] ?? 0) === (int)$cat['id_categorie'];
    }), 'quantiteStock'));
    $catStockData[$cat['nom']] = $stock;
  }
  ?>
  new Chart(document.getElementById('chartCatStock'), {
    type: 'bar',
    data: {
      labels: <?= json_encode(array_keys($catStockData)) ?>,
      datasets: [{ data: <?= json_encode(array_values($catStockData)) ?>, backgroundColor: ['rgba(231,76,60,0.7)','rgba(26,115,232,0.7)','rgba(245,127,23,0.7)','rgba(46,125,50,0.7)','rgba(142,68,173,0.7)','rgba(22,160,133,0.7)'], borderRadius: 5, borderSkipped: false }]
    },
    options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid: { color: '#eee' } }, y: { grid: { display: false } } } }
  });
  </script>

  <!-- MAIN ROW -->
  <div class="row g-3">
    <div class="col-lg-8">
      <div class="section-card">
        <div class="section-card-title">
          <i class="bi bi-tags"></i> All Categories
          <a href="ajouterCategorie.php" class="ms-auto" style="background:#e74c3c;color:white;border-radius:20px;padding:4px 14px;font-size:11px;text-decoration:none;font-weight:600;">+ Add</a>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-md-7">
            <input type="text" id="search-categorie" class="filter-input" placeholder="Search categories...">
          </div>
          <div class="col-md-5">
            <select id="sort-categorie" class="filter-input">
              <option value="">— Sort —</option>
              <option value="nom-asc">Name A → Z</option>
              <option value="nom-desc">Name Z → A</option>
            </select>
          </div>
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
                    <i class="bi bi-eye"></i> <?= $catProdCount[(int)$cat['id_categorie']] ?? 0 ?> product<?= ($catProdCount[(int)$cat['id_categorie']] ?? 0) !== 1 ? 's' : '' ?>
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
function filtrerCategories() {
  var q   = document.getElementById('search-categorie').value.toLowerCase().trim();
  var tri = document.getElementById('sort-categorie').value;
  var rows = Array.from(document.querySelectorAll('#tbody-categories tr:not(#no-result-cat)'));
  var visible = 0;
  rows.forEach(function(r) {
    var match = !q || r.dataset.nom.includes(q);
    r.style.display = match ? '' : 'none';
    if (match) visible++;
  });
  if (tri) {
    var dir = tri.split('-')[1];
    var vis = rows.filter(function(r){ return r.style.display !== 'none'; });
    vis.sort(function(a, b) {
      return dir === 'asc'
        ? (a.dataset.nom||'').localeCompare(b.dataset.nom||'')
        : (b.dataset.nom||'').localeCompare(a.dataset.nom||'');
    });
    var tbody = document.getElementById('tbody-categories');
    vis.forEach(function(r){ tbody.insertBefore(r, document.getElementById('no-result-cat')); });
  }
  document.getElementById('no-result-cat').style.display = visible === 0 ? '' : 'none';
}
document.getElementById('search-categorie').addEventListener('input', filtrerCategories);
document.getElementById('sort-categorie').addEventListener('change', filtrerCategories);
</script>

<?php include("footer.php"); ?>
