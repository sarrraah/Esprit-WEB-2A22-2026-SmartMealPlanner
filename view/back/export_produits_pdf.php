<?php
/**
 * Export all products as PDF (pure PHP, no external library needed)
 * Uses HTML → browser print trick with a clean print stylesheet
 */
require_once __DIR__ . '/../../controller/ProduitController.php';

$produitController = new ProduitController();
$produits = $produitController->listProduits();

foreach ($produits as &$p) {
    $p['statut'] = determinerStatut($p['quantiteStock'], $p['dateExpiration']);
}
unset($p);

$totalProduits = count($produits);
$totalStock    = array_sum(array_column($produits, 'quantiteStock'));
$totalDispo    = count(array_filter($produits, fn($p) => $p['statut'] === 'Disponible'));
$totalRupture  = count(array_filter($produits, fn($p) => $p['statut'] === 'Rupture'));
$totalEpuise   = count(array_filter($produits, fn($p) => $p['statut'] === 'Épuisé'));
$date          = date('d/m/Y H:i');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Stock Report — SmartMeal Planner</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: Arial, sans-serif; font-size: 12px; color: #2d2d2d; background: white; }

  /* ── PRINT BUTTON (hidden when printing) ── */
  .no-print { text-align: center; padding: 20px; background: #f5f5f5; border-bottom: 1px solid #ddd; }
  .btn-print {
    background: #ce1212; color: white; border: none; padding: 12px 32px;
    border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer;
    margin-right: 10px;
  }
  .btn-back {
    background: #555; color: white; border: none; padding: 12px 24px;
    border-radius: 8px; font-size: 14px; cursor: pointer; text-decoration: none;
    display: inline-block;
  }

  /* ── REPORT CONTENT ── */
  .report { padding: 30px 40px; max-width: 1100px; margin: 0 auto; }

  /* Header */
  .report-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; border-bottom: 3px solid #ce1212; padding-bottom: 16px; }
  .report-header h1 { font-size: 24px; font-weight: 900; color: #ce1212; letter-spacing: 1px; }
  .report-header .subtitle { font-size: 11px; color: #888; margin-top: 4px; }
  .report-header .meta { text-align: right; font-size: 11px; color: #888; }

  /* KPI cards */
  .kpi-row { display: flex; gap: 12px; margin-bottom: 24px; }
  .kpi { flex: 1; border-radius: 8px; padding: 12px 16px; text-align: center; }
  .kpi .val { font-size: 22px; font-weight: 900; }
  .kpi .lbl { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #666; margin-top: 2px; }
  .kpi-red   { background: #fdecea; border-left: 4px solid #ce1212; }
  .kpi-green { background: #e8f5e9; border-left: 4px solid #28a745; }
  .kpi-orange{ background: #fff8e1; border-left: 4px solid #f57f17; }
  .kpi-grey  { background: #f5f5f5; border-left: 4px solid #6c757d; }
  .kpi-blue  { background: #e8f0fe; border-left: 4px solid #1a73e8; }

  /* Table */
  table { width: 100%; border-collapse: collapse; font-size: 11px; }
  thead tr { background: #2d2d2d; color: white; }
  thead th { padding: 9px 10px; text-align: left; font-size: 10px; letter-spacing: 0.8px; text-transform: uppercase; }
  tbody tr:nth-child(even) { background: #f9f9f9; }
  tbody tr:hover { background: #fff3f3; }
  tbody td { padding: 8px 10px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }

  .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 10px; font-weight: 700; }
  .badge-dispo   { background: #e8f5e9; color: #2e7d32; }
  .badge-rupture { background: #fdecea; color: #c62828; }
  .badge-epuise  { background: #f5f5f5; color: #757575; }

  .stock-bar-wrap { width: 80px; background: #eee; border-radius: 4px; height: 6px; display: inline-block; vertical-align: middle; margin-left: 6px; }
  .stock-bar { height: 6px; border-radius: 4px; background: #28a745; }

  /* Footer */
  .report-footer { margin-top: 24px; padding-top: 12px; border-top: 1px solid #eee; font-size: 10px; color: #aaa; display: flex; justify-content: space-between; }

  /* Print styles */
  @media print {
    .no-print { display: none !important; }
    body { font-size: 11px; }
    .report { padding: 10px 20px; }
    @page { margin: 15mm; size: A4 landscape; }
    thead { display: table-header-group; }
    tr { page-break-inside: avoid; }
  }
</style>
</head>
<body>

<!-- Print / Back buttons -->
<div class="no-print">
  <button class="btn-print" onclick="window.print()">🖨️ Print / Save as PDF</button>
  <a href="afficherProduit.php" class="btn-back">← Back to Dashboard</a>
</div>

<div class="report">

  <!-- Header -->
  <div class="report-header">
    <div>
      <h1>📦 Stock Report</h1>
      <div class="subtitle">SmartMeal Planner — Full Product Inventory</div>
    </div>
    <div class="meta">
      Generated: <?= $date ?><br>
      Total products: <strong><?= $totalProduits ?></strong>
    </div>
  </div>

  <!-- KPIs -->
  <div class="kpi-row">
    <div class="kpi kpi-red">
      <div class="val" style="color:#ce1212;"><?= $totalProduits ?></div>
      <div class="lbl">Total Products</div>
    </div>
    <div class="kpi kpi-blue">
      <div class="val" style="color:#1a73e8;"><?= $totalStock ?></div>
      <div class="lbl">Total Stock Units</div>
    </div>
    <div class="kpi kpi-green">
      <div class="val" style="color:#2e7d32;"><?= $totalDispo ?></div>
      <div class="lbl">Available</div>
    </div>
    <div class="kpi kpi-orange">
      <div class="val" style="color:#f57f17;"><?= $totalRupture ?></div>
      <div class="lbl">Out of Stock</div>
    </div>
    <div class="kpi kpi-grey">
      <div class="val" style="color:#6c757d;"><?= $totalEpuise ?></div>
      <div class="lbl">Expired</div>
    </div>
  </div>

  <!-- Table -->
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Product Name</th>
        <th>Category</th>
        <th>Description</th>
        <th>Price</th>
        <th>Stock Qty</th>
        <th>Expiration</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
    <?php
    $maxStock = max(array_column($produits, 'quantiteStock') ?: [1]);
    foreach ($produits as $i => $p):
      $badgeCls = match($p['statut']) {
        'Disponible' => 'badge-dispo',
        'Rupture'    => 'badge-rupture',
        default      => 'badge-epuise',
      };
      $badgeTxt = match($p['statut']) {
        'Disponible' => 'Available',
        'Rupture'    => 'Out of Stock',
        default      => 'Expired',
      };
      $barWidth = $maxStock > 0 ? round(($p['quantiteStock'] / $maxStock) * 80) : 0;
      $barColor = $p['quantiteStock'] > 10 ? '#28a745' : ($p['quantiteStock'] > 0 ? '#f57f17' : '#dc3545');
      $desc = mb_strimwidth($p['description'] ?? '', 0, 80, '…');
    ?>
      <tr>
        <td style="color:#999;font-size:10px;"><?= $i + 1 ?></td>
        <td><strong><?= htmlspecialchars($p['nom']) ?></strong></td>
        <td style="color:#666;"><?= htmlspecialchars($p['categorie_nom'] ?? '—') ?></td>
        <td style="color:#888;font-size:10px;"><?= htmlspecialchars($desc) ?></td>
        <td style="font-weight:700;color:#ce1212;"><?= number_format((float)$p['prix'], 2, ',', ' ') ?> DT</td>
        <td>
          <strong><?= (int)$p['quantiteStock'] ?></strong>
          <span class="stock-bar-wrap"><span class="stock-bar" style="width:<?= $barWidth ?>px;background:<?= $barColor ?>;"></span></span>
        </td>
        <td style="color:<?= (strtotime($p['dateExpiration'] ?? '') < time()) ? '#c62828' : '#555' ?>;">
          <?= $p['dateExpiration'] ? date('d/m/Y', strtotime($p['dateExpiration'])) : '—' ?>
        </td>
        <td><span class="badge <?= $badgeCls ?>"><?= $badgeTxt ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Footer -->
  <div class="report-footer">
    <span>SmartMeal Planner — Confidential</span>
    <span>Generated on <?= $date ?></span>
  </div>

</div>

</body>
</html>
