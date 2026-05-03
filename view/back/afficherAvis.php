<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/AvisController.php';

$controller = new AvisController();
$allAvis    = $controller->getAllAvis();

// ── Stats ──────────────────────────────────────────────────────────────────
$totalAvis   = count($allAvis);
$avgNote     = $totalAvis > 0 ? round(array_sum(array_column($allAvis, 'note')) / $totalAvis, 1) : 0;
$count5Stars = count(array_filter($allAvis, fn($a) => (int)$a['note'] === 5));
$count1Star  = count(array_filter($allAvis, fn($a) => (int)$a['note'] === 1));

// ── Filters ────────────────────────────────────────────────────────────────
$filterProduit = (int)($_GET['produit'] ?? 0);
$filterNote    = (int)($_GET['note']    ?? 0);
$filterSearch  = trim($_GET['q']        ?? '');

$filtered = array_values(array_filter($allAvis, function ($a) use ($filterProduit, $filterNote, $filterSearch) {
    if ($filterProduit && (int)$a['id_produit'] !== $filterProduit) return false;
    if ($filterNote    && (int)$a['note']        !== $filterNote)    return false;
    if ($filterSearch  && stripos($a['commentaire'], $filterSearch) === false) return false;
    return true;
}));

// ── Distinct products for dropdown ─────────────────────────────────────────
$produits = [];
foreach ($allAvis as $a) {
    $pid = (int)$a['id_produit'];
    if (!isset($produits[$pid])) {
        $produits[$pid] = $a['produit_nom'] ?? ('Produit #' . $pid);
    }
}

// ── Detect schema: does produit have id_categorie column? ──────────────────
$db = config::getConnexion();
$hasIdCat = $db->query("SHOW COLUMNS FROM produit LIKE 'id_categorie'")->fetch() !== false;

// ── Most reviewed products (proxy for most purchased) ──────────────────────
if ($hasIdCat) {
    $stmtTop = $db->query("
        SELECT p.id, p.nom, p.image,
               COUNT(a.id_avis)           AS nb_avis,
               ROUND(AVG(a.note), 1)      AS avg_note,
               c.nom                      AS categorie_nom
        FROM avis a
        JOIN produit p  ON a.id_produit = p.id
        LEFT JOIN categorieproduit c ON p.id_categorie = c.id_categorie
        GROUP BY p.id, p.nom, p.image, c.nom
        ORDER BY nb_avis DESC, avg_note DESC
        LIMIT 5
    ");
} else {
    $stmtTop = $db->query("
        SELECT p.id, p.nom, p.image,
               COUNT(a.id_avis)           AS nb_avis,
               ROUND(AVG(a.note), 1)      AS avg_note,
               COALESCE(c.nom, p.categorie) AS categorie_nom
        FROM avis a
        JOIN produit p  ON a.id_produit = p.id
        LEFT JOIN categorieproduit c ON c.id_categorie = CAST(p.categorie AS UNSIGNED)
        GROUP BY p.id, p.nom, p.image, categorie_nom
        ORDER BY nb_avis DESC, avg_note DESC
        LIMIT 5
    ");
}
$topProduits = $stmtTop->fetchAll();

// ── Most reviewed categories ───────────────────────────────────────────────
if ($hasIdCat) {
    $stmtCat = $db->query("
        SELECT c.nom AS categorie_nom,
               COUNT(a.id_avis)       AS nb_avis,
               ROUND(AVG(a.note), 1)  AS avg_note,
               COUNT(DISTINCT p.id)   AS nb_produits
        FROM avis a
        JOIN produit p ON a.id_produit = p.id
        LEFT JOIN categorieproduit c ON p.id_categorie = c.id_categorie
        GROUP BY c.id_categorie, c.nom
        ORDER BY nb_avis DESC
        LIMIT 6
    ");
} else {
    $stmtCat = $db->query("
        SELECT COALESCE(c.nom, p.categorie) AS categorie_nom,
               COUNT(a.id_avis)             AS nb_avis,
               ROUND(AVG(a.note), 1)        AS avg_note,
               COUNT(DISTINCT p.id)         AS nb_produits
        FROM avis a
        JOIN produit p ON a.id_produit = p.id
        LEFT JOIN categorieproduit c ON c.id_categorie = CAST(p.categorie AS UNSIGNED)
        GROUP BY categorie_nom
        ORDER BY nb_avis DESC
        LIMIT 6
    ");
}
$topCategories = $stmtCat->fetchAll();

// ── Reviews per note (for bar chart) ──────────────────────────────────────
$noteDistrib = [1=>0, 2=>0, 3=>0, 4=>0, 5=>0];
foreach ($allAvis as $a) {
    $n = (int)$a['note'];
    if ($n >= 1 && $n <= 5) $noteDistrib[$n]++;
}

// ── Overall satisfaction rate (kept for stat cards) ────────────────────────
$satisfaits   = count(array_filter($allAvis, fn($a) => (int)$a['note'] >= 4));
$tauxGlobal   = $totalAvis > 0 ? round($satisfaits / $totalAvis * 100, 1) : 0;
$insatisfaits = count(array_filter($allAvis, fn($a) => (int)$a['note'] <= 2));
$tauxInsatisf = $totalAvis > 0 ? round($insatisfaits / $totalAvis * 100, 1) : 0;

// ── Star helper ────────────────────────────────────────────────────────────
function renderStars(int $note): string {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $note
            ? '<i class="bi bi-star-fill" style="color:#f57f17;font-size:0.8rem;"></i>'
            : '<i class="bi bi-star"      style="color:#ddd;font-size:0.8rem;"></i>';
    }
    return $html;
}

include('header.php');
?>

<style>
.dashboard-banner {
  background: linear-gradient(rgba(0,0,0,0.55),rgba(0,0,0,0.55)),
              url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1200') center/cover no-repeat;
  border-radius:14px; padding:36px 32px; margin-bottom:28px; color:white;
}
.dashboard-banner h2 {
  font-family:'Raleway',sans-serif !important;
  font-size:1.8rem; font-weight:300; letter-spacing:5px;
  text-transform:uppercase; margin:0 0 8px; color:white !important;
}
.dashboard-banner h2 span { font-weight:700; color:#e74c3c !important; }
.dashboard-banner p {
  font-size:0.85rem; color:rgba(255,255,255,0.75) !important;
  margin:0; font-weight:300; letter-spacing:1px;
}
.stat-card {
  background:#fff; border-radius:12px; padding:18px 20px;
  box-shadow:0 2px 10px rgba(0,0,0,0.06); display:flex; align-items:center; gap:16px;
}
.stat-icon {
  width:46px; height:46px; border-radius:10px;
  display:flex; align-items:center; justify-content:center;
  font-size:1.3rem; flex-shrink:0;
}
.stat-label { font-size:0.72rem; color:#999; letter-spacing:1px; text-transform:uppercase; font-weight:300; }
.stat-value { font-size:1.9rem; font-weight:700; color:#2d2d2d; line-height:1; }
.section-card { background:#fff; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); }
.section-card-title {
  font-size:0.75rem; font-weight:700; letter-spacing:2px; text-transform:uppercase;
  color:#2d2d2d; display:flex; align-items:center; gap:8px;
  margin-bottom:16px; padding-bottom:12px; border-bottom:1px solid #f0f0f0;
}
.section-card-title i { color:#e74c3c; }
.filter-input {
  border-radius:8px; border:1px solid #e0e0e0; padding:8px 12px;
  font-size:0.82rem; width:100%; outline:none;
}
.filter-input:focus { border-color:#e74c3c; }
.table th {
  font-size:0.7rem; letter-spacing:1.5px; text-transform:uppercase;
  color:#999; font-weight:600; border-bottom:2px solid #f0f0f0; padding:10px 12px;
}
.table td { padding:10px 12px; font-size:0.83rem; vertical-align:middle; border-bottom:1px solid #f8f8f8; }
.table tbody tr:hover { background:#fafafa; }
.btn-delete {
  background:#fdecea; color:#c62828; border:none;
  border-radius:6px; padding:5px 10px; font-size:12px; cursor:pointer;
}
.btn-delete:hover { background:#ffcdd2; }
</style>

<div class="page-body">

  <!-- BANNER -->
  <div class="dashboard-banner">
    <h2>Customer <span>Reviews</span></h2>
    <p>Monitor and manage all product reviews submitted by customers.</p>
  </div>

  <!-- STATS -->
  <div class="row g-3 mb-4">
    <div class="col-md-2">
      <div class="stat-card" style="border-left:4px solid #e74c3c;">
        <div class="stat-icon" style="background:#fdecea;">
          <i class="bi bi-chat-square-text" style="color:#e74c3c;"></i>
        </div>
        <div>
          <div class="stat-label">Total Reviews</div>
          <div class="stat-value"><?= $totalAvis ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="stat-card" style="border-left:4px solid #1a73e8;">
        <div class="stat-icon" style="background:#e8f0fe;">
          <i class="bi bi-star-half" style="color:#1a73e8;"></i>
        </div>
        <div>
          <div class="stat-label">Avg Note</div>
          <div class="stat-value"><?= $avgNote ?></div>
          <div style="font-size:0.7rem;color:#999;margin-top:2px;">out of 5</div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="stat-card" style="border-left:4px solid #f57f17;">
        <div class="stat-icon" style="background:#fff8e1;">
          <i class="bi bi-star-fill" style="color:#f57f17;"></i>
        </div>
        <div>
          <div class="stat-label">5-Star</div>
          <div class="stat-value"><?= $count5Stars ?></div>
          <div style="font-size:0.7rem;color:#f57f17;margin-top:2px;">
            <?= $totalAvis > 0 ? round($count5Stars / $totalAvis * 100) : 0 ?>%
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="stat-card" style="border-left:4px solid #c62828;">
        <div class="stat-icon" style="background:#fdecea;">
          <i class="bi bi-star" style="color:#c62828;"></i>
        </div>
        <div>
          <div class="stat-label">1-Star</div>
          <div class="stat-value"><?= $count1Star ?></div>
          <div style="font-size:0.7rem;color:#c62828;margin-top:2px;">
            <?= $totalAvis > 0 ? round($count1Star / $totalAvis * 100) : 0 ?>%
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="stat-card" style="border-left:4px solid #2e7d32;">
        <div class="stat-icon" style="background:#e8f5e9;">
          <i class="bi bi-emoji-smile" style="color:#2e7d32;"></i>
        </div>
        <div>
          <div class="stat-label">Satisfaction</div>
          <div class="stat-value" style="color:#2e7d32;"><?= $tauxGlobal ?>%</div>
          <div style="font-size:0.7rem;color:#2e7d32;margin-top:2px;">notes ≥ 4★</div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="stat-card" style="border-left:4px solid #f57f17;">
        <div class="stat-icon" style="background:#fff8e1;">
          <i class="bi bi-emoji-frown" style="color:#f57f17;"></i>
        </div>
        <div>
          <div class="stat-label">Dissatisfied</div>
          <div class="stat-value" style="color:#f57f17;"><?= $tauxInsatisf ?>%</div>
          <div style="font-size:0.7rem;color:#f57f17;margin-top:2px;">notes ≤ 2★</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ANALYTICS SECTION -->
  <div style="background:#fff;border-radius:14px;padding:20px 24px;box-shadow:0 2px 10px rgba(0,0,0,0.06);margin-bottom:28px;">
    <div style="font-size:0.75rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#2d2d2d;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;gap:8px;">
      <i class="bi bi-graph-up" style="color:#e74c3c;"></i> Analytics Overview
    </div>
    <div class="row g-3">

      <!-- Most reviewed products -->
      <div class="col-lg-4">
        <div style="background:#f9f9f9;border-radius:10px;padding:18px;height:100%;">
          <div style="font-size:0.68rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#999;margin-bottom:14px;display:flex;align-items:center;gap:6px;">
            <i class="bi bi-fire" style="color:#e74c3c;"></i> Most Reviewed Meals
            <span style="font-size:0.6rem;color:#bbb;font-weight:400;text-transform:none;letter-spacing:0;margin-left:2px;">by review count</span>
          </div>
          <?php if (empty($topProduits)): ?>
            <p style="font-size:0.8rem;color:#bbb;text-align:center;padding:20px 0;">No data yet.</p>
          <?php else: ?>
            <?php
            $maxAvis = max(array_column($topProduits, 'nb_avis')) ?: 1;
            $rankColors = ['#e74c3c','#f57f17','#1a73e8','#2e7d32','#8e44ad'];
            foreach ($topProduits as $rank => $meal):
              $img = $meal['image'] ?? '';
              if (empty($img))                        $imgSrc = '';
              elseif (str_starts_with($img,'http'))   $imgSrc = $img;
              else                                     $imgSrc = UPLOAD_URL . $img;
              $pct   = round($meal['nb_avis'] / $maxAvis * 100);
              $stars = round((float)$meal['avg_note']);
              $rc    = $rankColors[$rank] ?? '#999';
            ?>
            <div style="display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #f0f0f0;">
              <div style="width:22px;height:22px;border-radius:50%;background:<?= $rc ?>;color:white;font-size:0.65rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><?= $rank+1 ?></div>
              <?php if ($imgSrc): ?>
                <img src="<?= htmlspecialchars($imgSrc) ?>" style="width:36px;height:36px;object-fit:cover;border-radius:6px;flex-shrink:0;">
              <?php else: ?>
                <div style="width:36px;height:36px;background:#e9e9e9;border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-image" style="color:#ccc;"></i></div>
              <?php endif; ?>
              <div style="flex:1;min-width:0;">
                <div style="font-size:0.8rem;font-weight:600;color:#2d2d2d;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($meal['nom']) ?></div>
                <div style="display:flex;align-items:center;gap:6px;margin-top:3px;">
                  <div style="flex:1;background:#e9e9e9;border-radius:4px;height:5px;">
                    <div style="width:<?= $pct ?>%;background:<?= $rc ?>;height:5px;border-radius:4px;"></div>
                  </div>
                  <span style="font-size:0.65rem;color:#999;white-space:nowrap;"><?= $meal['nb_avis'] ?> review<?= $meal['nb_avis'] > 1 ? 's' : '' ?></span>
                </div>
                <div style="font-size:0.65rem;color:#f57f17;margin-top:1px;">
                  <?= str_repeat('★', $stars) ?><span style="color:#ddd;"><?= str_repeat('★', 5-$stars) ?></span>
                  <span style="color:#bbb;margin-left:3px;"><?= $meal['avg_note'] ?>/5</span>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Most reviewed categories -->
      <div class="col-lg-4">
        <div style="background:#f9f9f9;border-radius:10px;padding:18px;height:100%;">
          <div style="font-size:0.68rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#999;margin-bottom:14px;display:flex;align-items:center;gap:6px;">
            <i class="bi bi-tags-fill" style="color:#1a73e8;"></i> Most Reviewed Categories
          </div>
          <?php if (empty($topCategories)): ?>
            <p style="font-size:0.8rem;color:#bbb;text-align:center;padding:20px 0;">No data yet.</p>
          <?php else: ?>
            <?php
            $maxCatAvis = max(array_column($topCategories, 'nb_avis')) ?: 1;
            $catColors  = ['#e74c3c','#1a73e8','#f57f17','#2e7d32','#8e44ad','#16a085'];
            foreach ($topCategories as $ci => $cat):
              $pct   = round($cat['nb_avis'] / $maxCatAvis * 100);
              $stars = round((float)$cat['avg_note']);
              $cc    = $catColors[$ci % count($catColors)];
            ?>
            <div style="padding:9px 0;border-bottom:1px solid #f0f0f0;">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
                <div style="display:flex;align-items:center;gap:8px;">
                  <div style="width:10px;height:10px;border-radius:50%;background:<?= $cc ?>;flex-shrink:0;"></div>
                  <span style="font-size:0.82rem;font-weight:600;color:#2d2d2d;"><?= htmlspecialchars($cat['categorie_nom'] ?? 'Uncategorized') ?></span>
                </div>
                <span style="font-size:0.72rem;color:#fff;background:<?= $cc ?>;border-radius:12px;padding:2px 9px;font-weight:700;"><?= $cat['nb_avis'] ?></span>
              </div>
              <div style="background:#e9e9e9;border-radius:4px;height:6px;margin-bottom:4px;">
                <div style="width:<?= $pct ?>%;background:<?= $cc ?>;height:6px;border-radius:4px;"></div>
              </div>
              <div style="font-size:0.65rem;color:#999;display:flex;gap:10px;">
                <span><i class="bi bi-box-seam" style="margin-right:2px;"></i><?= $cat['nb_produits'] ?> product<?= $cat['nb_produits'] > 1 ? 's' : '' ?></span>
                <span style="color:#f57f17;"><?= str_repeat('★', $stars) ?><span style="color:#ddd;"><?= str_repeat('★', 5-$stars) ?></span> <?= $cat['avg_note'] ?>/5</span>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Rating distribution chart -->
      <div class="col-lg-4">
        <div style="background:#f9f9f9;border-radius:10px;padding:18px;height:100%;">
          <div style="font-size:0.68rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#999;margin-bottom:14px;display:flex;align-items:center;gap:6px;">
            <i class="bi bi-bar-chart-fill" style="color:#f57f17;"></i> Rating Distribution
          </div>
          <canvas id="chartNoteDistrib" height="200"></canvas>
          <div style="margin-top:12px;">
            <?php foreach ([5,4,3,2,1] as $n): ?>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;">
              <span style="font-size:0.7rem;color:#f57f17;width:40px;text-align:right;flex-shrink:0;"><?= str_repeat('★',$n) ?></span>
              <div style="flex:1;background:#e9e9e9;border-radius:4px;height:8px;">
                <div style="width:<?= $totalAvis > 0 ? round($noteDistrib[$n]/$totalAvis*100) : 0 ?>%;background:<?= $n>=4?'#2e7d32':($n==3?'#f57f17':'#e74c3c') ?>;height:8px;border-radius:4px;"></div>
              </div>
              <span style="font-size:0.7rem;color:#999;width:28px;flex-shrink:0;"><?= $noteDistrib[$n] ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script>
  Chart.defaults.font.family = "'Raleway', sans-serif";
  Chart.defaults.font.size   = 10;

  // ── Satisfaction curve ────────────────────────────────────────────────────
  <?php if (!empty($satLabels)): ?>
  new Chart(document.getElementById('chartSatisfaction'), {
    type: 'line',
    data: {
      labels: <?= json_encode($satLabels) ?>,
      datasets: [
        {
          label: 'Satisfaction % (≥4★)',
          data: <?= json_encode($satTaux) ?>,
          borderColor: '#2e7d32',
          backgroundColor: 'rgba(46,125,50,0.08)',
          borderWidth: 2.5,
          pointBackgroundColor: '#2e7d32',
          pointRadius: 4,
          pointHoverRadius: 6,
          tension: 0.4,
          fill: true,
          yAxisID: 'yPct'
        },
        {
          label: 'Avg Note /5',
          data: <?= json_encode($satAvg) ?>,
          borderColor: '#1a73e8',
          backgroundColor: 'transparent',
          borderWidth: 2,
          pointBackgroundColor: '#1a73e8',
          pointRadius: 3,
          pointHoverRadius: 5,
          tension: 0.4,
          fill: false,
          yAxisID: 'yNote'
        },
        {
          label: 'Reviews',
          data: <?= json_encode($satCounts) ?>,
          borderColor: '#e74c3c',
          backgroundColor: 'transparent',
          borderWidth: 1.5,
          borderDash: [5, 4],
          pointBackgroundColor: '#e74c3c',
          pointRadius: 3,
          pointHoverRadius: 5,
          tension: 0.3,
          fill: false,
          yAxisID: 'yCount'
        }
      ]
    },
    options: {
      responsive: true,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#fff',
          borderColor: '#e0e0e0',
          borderWidth: 1,
          titleColor: '#2d2d2d',
          bodyColor: '#555',
          padding: 10,
          callbacks: {
            label: function(ctx) {
              if (ctx.datasetIndex === 0) return ' Satisfaction: ' + ctx.parsed.y + '%';
              if (ctx.datasetIndex === 1) return ' Avg note: ' + ctx.parsed.y + '/5';
              return ' Reviews: ' + ctx.parsed.y;
            }
          }
        }
      },
      scales: {
        x: { grid: { display: false }, ticks: { maxRotation: 45 } },
        yPct: {
          type: 'linear', position: 'left',
          min: 0, max: 100,
          grid: { color: '#f0f0f0' },
          ticks: { callback: v => v + '%', stepSize: 20 },
          title: { display: true, text: 'Satisfaction %', color: '#2e7d32', font: { size: 9 } }
        },
        yNote: {
          type: 'linear', position: 'right',
          min: 0, max: 5,
          grid: { display: false },
          ticks: { callback: v => v + '★', stepSize: 1 },
          title: { display: true, text: 'Avg Note', color: '#1a73e8', font: { size: 9 } }
        },
        yCount: { display: false }
      }
    }
  });
  <?php endif; ?>

  // ── Rating distribution bar chart ─────────────────────────────────────────
  new Chart(document.getElementById('chartNoteDistrib'), {
    type: 'bar',
    data: {
      labels: ['1★','2★','3★','4★','5★'],
      datasets: [{
        data: [<?= implode(',', array_values($noteDistrib)) ?>],
        backgroundColor: ['#e74c3c','#f57f17','#f39c12','#27ae60','#2e7d32'],
        borderRadius: 5, borderSkipped: false
      }]
    },
    options: {
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, grid: { color: '#eee' }, ticks: { stepSize: 1 } },
        x: { grid: { display: false } }
      }
    }
  });
  </script>

  <!-- TABLE CARD -->
  <div class="section-card" id="table-section">
    <div class="section-card-title">
      <i class="bi bi-chat-square-text-fill"></i> All Reviews
      <span class="ms-auto" style="font-size:0.72rem;color:#999;font-weight:300;letter-spacing:0;">
        <?= count($filtered) ?> result<?= count($filtered) !== 1 ? 's' : '' ?>
      </span>
    </div>

    <!-- Filters -->
    <form method="GET" action="afficherAvis.php#table-section">
      <div class="row g-2 mb-3">
        <div class="col-md-4">
          <select name="produit" class="filter-input" onchange="this.form.submit()">
            <option value="0">All products</option>
            <?php foreach ($produits as $pid => $pnom): ?>
              <option value="<?= $pid ?>" <?= $filterProduit === $pid ? 'selected' : '' ?>>
                <?= htmlspecialchars($pnom) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <select name="note" class="filter-input" onchange="this.form.submit()">
            <option value="0">All ratings</option>
            <?php for ($n = 5; $n >= 1; $n--): ?>
              <option value="<?= $n ?>" <?= $filterNote === $n ? 'selected' : '' ?>>
                <?= $n ?> star<?= $n > 1 ? 's' : '' ?>
              </option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="col-md-4">
          <input type="text" name="q" value="<?= htmlspecialchars($filterSearch) ?>"
            placeholder="Search in comments…" class="filter-input">
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button type="submit" style="background:#e74c3c;color:#fff;border:none;border-radius:8px;padding:8px 14px;font-size:0.8rem;font-weight:600;cursor:pointer;flex:1;">
            <i class="bi bi-search"></i>
          </button>
          <?php if ($filterProduit || $filterNote || $filterSearch): ?>
            <a href="afficherAvis.php" style="background:#f5f5f5;color:#333;border:1px solid #e0e0e0;border-radius:8px;padding:8px 12px;font-size:0.8rem;text-decoration:none;display:flex;align-items:center;">
              <i class="bi bi-x-lg"></i>
            </a>
          <?php endif; ?>
        </div>
      </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
      <table class="table" style="font-family:'Raleway',sans-serif;">
        <thead>
          <tr>
            <th>#</th>
            <th>Product</th>
            <th>Note</th>
            <th>Comment</th>
            <th>Date</th>
            <th>Sentiment</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($filtered) === 0): ?>
            <tr>
              <td colspan="7" class="text-center text-muted py-4">
                <i class="bi bi-inbox" style="font-size:1.5rem;display:block;margin-bottom:6px;"></i>
                No reviews found.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($filtered as $i => $avis): ?>
              <tr>
                <td style="color:#999;"><?= $i + 1 ?></td>
                <td>
                  <?php if (!empty($avis['produit_nom'])): ?>
                    <strong><?= htmlspecialchars($avis['produit_nom']) ?></strong>
                  <?php else: ?>
                    <span style="color:#bbb;font-style:italic;">Produit supprimé</span>
                  <?php endif; ?>
                </td>
                <td style="white-space:nowrap;">
                  <?= renderStars((int)$avis['note']) ?>
                </td>
                <td style="color:#555;max-width:260px;">
                  <?= htmlspecialchars(mb_strimwidth($avis['commentaire'], 0, 80, '…')) ?>
                </td>
                <td style="color:#999;white-space:nowrap;font-size:0.78rem;">
                  <?= htmlspecialchars($avis['date_avis']) ?>
                </td>
                <td>
                  <?php if (isset($avis['sentiment']) && $avis['sentiment'] !== ''): ?>
                    <span style="font-size:1.1rem;" title="<?= htmlspecialchars($avis['sentiment']) ?>">
                      <?= htmlspecialchars($avis['sentiment']) ?>
                    </span>
                  <?php else: ?>
                    <span style="color:#ccc;">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="supprimerAvis.php?id=<?= (int)$avis['id_avis'] ?>" class="btn-delete">
                    <i class="bi bi-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php include('footer.php'); ?>
