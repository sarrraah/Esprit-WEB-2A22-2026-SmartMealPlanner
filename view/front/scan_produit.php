<?php
/**
 * Product barcode scan page.
 * GET ?id=123 → shows full product details
 * No ?id → shows scanner UI
 */
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/ProduitController.php';

$produitController = new ProduitController();
$produit = null;
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $produit = $produitController->getProduitById($id);
    if ($produit) {
        $produit['statut'] = determinerStatut($produit['quantiteStock'], $produit['dateExpiration']);
    }
}

$img = $produit['image'] ?? '';
if (empty($img))                         $imgSrc = '';
elseif (str_starts_with($img,'http'))    $imgSrc = $img;
elseif (str_starts_with($img,'meals/'))  $imgSrc = '../../view/assets/img/'.$img;
else                                      $imgSrc = UPLOAD_URL.$img;

$badgeColor = match($produit['statut'] ?? '') {
    'Disponible' => '#28a745',
    'Rupture'    => '#dc3545',
    default      => '#6c757d',
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Product Scan — SmartMeal Planner</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Inter', sans-serif; background: #f5f5f0; min-height: 100vh; }
.top-bar { background: #ce1212; color: white; padding: 14px 20px; display: flex; align-items: center; gap: 12px; }
.top-bar h1 { font-size: 1.1rem; font-weight: 700; }
.back-btn { color: white; text-decoration: none; font-size: 1.2rem; }
.container { max-width: 600px; margin: 0 auto; padding: 24px 16px; }

/* Product card */
.product-card { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
.product-img { width: 100%; height: 240px; object-fit: cover; }
.product-img-ph { width: 100%; height: 240px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 4rem; color: #ccc; }
.product-body { padding: 20px; }
.product-name { font-size: 1.3rem; font-weight: 700; color: #2d2d2d; margin-bottom: 6px; }
.product-cat { font-size: 0.8rem; color: #999; margin-bottom: 12px; }
.product-desc { font-size: 0.88rem; color: #666; line-height: 1.6; margin-bottom: 16px; }
.info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f5f5f5; font-size: 0.85rem; }
.info-label { color: #999; font-weight: 500; }
.info-val { font-weight: 700; color: #2d2d2d; }
.price-big { font-size: 1.8rem; font-weight: 900; color: #ce1212; margin: 16px 0 8px; }
.badge { display: inline-block; padding: 4px 14px; border-radius: 20px; font-size: 0.78rem; font-weight: 700; color: white; }

/* Barcode section */
.barcode-wrap { text-align: center; padding: 20px; background: #f9f9f9; border-top: 1px solid #f0f0f0; }
.barcode-wrap svg { max-width: 100%; }

/* Scanner UI */
.scanner-card { background: white; border-radius: 16px; padding: 28px 20px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
.scanner-card h2 { font-size: 1.2rem; font-weight: 700; color: #2d2d2d; margin-bottom: 8px; }
.scanner-card p { font-size: 0.85rem; color: #888; margin-bottom: 20px; }
#reader { width: 100%; border-radius: 12px; overflow: hidden; }
.manual-input { display: flex; gap: 8px; margin-top: 16px; }
.manual-input input { flex: 1; border: 1px solid #ddd; border-radius: 10px; padding: 10px 14px; font-size: 0.9rem; outline: none; }
.manual-input input:focus { border-color: #ce1212; }
.manual-input button { background: #ce1212; color: white; border: none; border-radius: 10px; padding: 10px 20px; font-weight: 700; cursor: pointer; }
</style>
</head>
<body>

<div class="top-bar">
  <a href="produits.php" class="back-btn"><i class="bi bi-arrow-left"></i></a>
  <h1><i class="bi bi-upc-scan me-2"></i>Product Scanner</h1>
</div>

<div class="container">

<?php if ($produit): ?>
  <!-- ── PRODUCT DETAILS ── -->
  <div class="product-card">
    <?php if ($imgSrc): ?>
      <img src="<?= htmlspecialchars($imgSrc) ?>" class="product-img" alt="<?= htmlspecialchars($produit['nom']) ?>">
    <?php else: ?>
      <div class="product-img-ph"><i class="bi bi-image"></i></div>
    <?php endif; ?>

    <div class="product-body">
      <div class="product-name"><?= htmlspecialchars($produit['nom']) ?></div>
      <div class="product-cat"><i class="bi bi-tag-fill me-1" style="color:#ce1212;"></i><?= htmlspecialchars($produit['categorie_nom'] ?? 'Uncategorized') ?></div>
      <div class="product-desc"><?= htmlspecialchars($produit['description'] ?? '') ?></div>

      <div class="info-row">
        <span class="info-label">Status</span>
        <span class="badge" style="background:<?= $badgeColor ?>;"><?= htmlspecialchars($produit['statut']) ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Stock</span>
        <span class="info-val"><?= (int)$produit['quantiteStock'] ?> units</span>
      </div>
      <div class="info-row">
        <span class="info-label">Expiration</span>
        <span class="info-val" style="color:<?= (strtotime($produit['dateExpiration'] ?? '') < time()) ? '#dc3545' : '#2d2d2d' ?>;">
          <?= $produit['dateExpiration'] ? date('d/m/Y', strtotime($produit['dateExpiration'])) : '—' ?>
        </span>
      </div>
      <div class="info-row" style="border:none;">
        <span class="info-label">Product ID</span>
        <span class="info-val">#<?= (int)$produit['id'] ?></span>
      </div>

      <div class="price-big"><?= number_format((float)$produit['prix'], 2, ',', ' ') ?> DT</div>
    </div>

    <!-- Barcode -->
    <div class="barcode-wrap">
      <svg id="barcode-detail"></svg>
      <div style="font-size:0.7rem;color:#aaa;margin-top:6px;">Scan to view product details</div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
  <script>
    // Generate EAN-13 from product ID (pad to 12 digits, add check digit)
    function makeEAN13(id) {
      var base = String(id).padStart(12, '0').substring(0, 12);
      var sum = 0;
      for (var i = 0; i < 12; i++) sum += parseInt(base[i]) * (i % 2 === 0 ? 1 : 3);
      var check = (10 - (sum % 10)) % 10;
      return base + check;
    }
    JsBarcode('#barcode-detail', makeEAN13(<?= (int)$produit['id'] ?>), {
      format: 'EAN13', width: 2, height: 60, displayValue: true,
      fontSize: 12, margin: 10, background: '#f9f9f9'
    });
  </script>

<?php else: ?>
  <!-- ── SCANNER UI ── -->
  <div class="scanner-card">
    <h2><i class="bi bi-camera me-2" style="color:#ce1212;"></i>Scan a Barcode</h2>
    <p>Point your camera at a product barcode to view its details instantly.</p>
    <div id="reader"></div>
    <div class="manual-input">
      <input type="text" id="manual-code" placeholder="Or enter barcode manually..." maxlength="13">
      <button onclick="goToProduct()">Go</button>
    </div>
  </div>

  <!-- html5-qrcode for camera scanning -->
  <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
  <script>
    function makeEAN13(id) {
      var base = String(id).padStart(12, '0').substring(0, 12);
      var sum = 0;
      for (var i = 0; i < 12; i++) sum += parseInt(base[i]) * (i % 2 === 0 ? 1 : 3);
      var check = (10 - (sum % 10)) % 10;
      return base + check;
    }

    function ean13ToId(code) {
      // Strip leading zeros and check digit
      return parseInt(code.substring(0, 12)) || 0;
    }

    var html5QrCode = new Html5Qrcode("reader");
    html5QrCode.start(
      { facingMode: "environment" },
      { fps: 10, qrbox: { width: 280, height: 120 } },
      function(decodedText) {
        html5QrCode.stop();
        var id = ean13ToId(decodedText);
        if (id > 0) window.location.href = 'scan_produit.php?id=' + id;
      },
      function(err) {}
    ).catch(function(err) {
      document.getElementById('reader').innerHTML =
        '<p style="color:#888;font-size:0.85rem;padding:20px;">Camera not available. Use manual input below.</p>';
    });

    function goToProduct() {
      var code = document.getElementById('manual-code').value.trim();
      if (!code) return;
      var id = ean13ToId(code);
      if (id > 0) window.location.href = 'scan_produit.php?id=' + id;
    }
    document.getElementById('manual-code').addEventListener('keydown', function(e) {
      if (e.key === 'Enter') goToProduct();
    });
  </script>
<?php endif; ?>

</div>
</body>
</html>
