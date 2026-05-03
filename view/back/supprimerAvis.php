<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/AvisController.php';

$controller = new AvisController();
$id         = (int)($_GET['id'] ?? 0);
$avis       = $controller->getAvisById($id);

if (!$avis) {
    header('Location: afficherAvis.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->deleteAvis($id);
    header('Location: afficherAvis.php');
    exit;
}

// ── Star helper (same as afficherAvis.php) ─────────────────────────────────
function renderStars(int $note): string {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $note
            ? '<i class="bi bi-star-fill" style="color:#f57f17;font-size:0.9rem;"></i>'
            : '<i class="bi bi-star"      style="color:#ddd;font-size:0.9rem;"></i>';
    }
    return $html;
}

include('header.php');
?>

<style>
.confirm-card {
  background:#fff; border-radius:12px; padding:36px 32px;
  box-shadow:0 2px 10px rgba(0,0,0,0.06); max-width:520px;
  margin:40px auto; text-align:center;
}
.confirm-card .icon { font-size:3rem; color:#e74c3c; margin-bottom:16px; }
.confirm-card h3 {
  font-family:'Raleway',sans-serif; font-size:0.9rem; font-weight:700;
  letter-spacing:2px; text-transform:uppercase; color:#2d2d2d; margin-bottom:8px;
}
.confirm-card .meta {
  background:#f9f9f9; border-radius:10px; padding:14px 18px;
  margin:16px 0 24px; text-align:left; font-size:0.83rem; color:#555;
}
.confirm-card .meta strong { color:#2d2d2d; }
.btn-confirm {
  background:#e74c3c; color:white; border:none;
  border-radius:8px; padding:10px 28px; font-size:0.85rem; font-weight:600; cursor:pointer;
}
.btn-confirm:hover { background:#c0392b; color:white; }
.btn-cancel {
  background:#fff; color:#333; border:1px solid #e0e0e0;
  border-radius:8px; padding:10px 28px; font-size:0.85rem; text-decoration:none;
}
.btn-cancel:hover { background:#f5f5f5; color:#333; }
</style>

<div class="page-body">
  <div class="confirm-card">
    <div class="icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
    <h3>Delete Review</h3>
    <p style="font-size:0.88rem;color:#666;margin-bottom:0;">
      Are you sure you want to permanently delete this review? This action cannot be undone.
    </p>

    <div class="meta">
      <div style="margin-bottom:8px;">
        <strong>Product:</strong>
        <?php if (!empty($avis['produit_nom'])): ?>
          <?= htmlspecialchars($avis['produit_nom']) ?>
        <?php else: ?>
          <span style="color:#bbb;font-style:italic;">Produit supprimé</span>
        <?php endif; ?>
      </div>
      <div style="margin-bottom:8px;">
        <strong>Note:</strong> <?= renderStars((int)$avis['note']) ?>
      </div>
      <div>
        <strong>Comment:</strong>
        <?= htmlspecialchars(mb_strimwidth($avis['commentaire'], 0, 100, '…')) ?>
      </div>
    </div>

    <form method="POST" class="d-flex gap-2 justify-content-center">
      <button type="submit" class="btn-confirm">
        <i class="bi bi-trash me-1"></i> Yes, Delete
      </button>
      <a href="afficherAvis.php" class="btn-cancel">Cancel</a>
    </form>
  </div>
</div>

<?php include('footer.php'); ?>
