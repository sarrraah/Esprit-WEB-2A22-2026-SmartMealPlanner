<?php
require_once __DIR__ . '/../../controller/CategorieController.php';

$controller = new CategorieController();
$id = (int)($_GET['id'] ?? 0);
$categorie = $controller->getCategorieById($id);
if (!$categorie) { header('Location: afficherCategorie.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->deleteCategorie($id);
    header('Location: afficherCategorie.php'); exit;
}

include("header.php");
?>

<style>
.confirm-card { background:#fff;border-radius:12px;padding:32px;box-shadow:0 2px 10px rgba(0,0,0,0.06);max-width:500px;margin:0 auto;text-align:center; }
.confirm-card .icon { font-size:3rem;color:#e74c3c;margin-bottom:16px; }
.confirm-card h3 { font-family:'Raleway',sans-serif;font-size:0.9rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#2d2d2d;margin-bottom:8px; }
.confirm-card p { font-size:0.9rem;color:#666;margin-bottom:24px; }
.btn-confirm { background:#e74c3c;color:white;border:none;border-radius:8px;padding:10px 28px;font-size:0.85rem;font-weight:600; }
.btn-confirm:hover { background:#c0392b;color:white; }
.btn-cancel { background:#fff;color:#333;border:1px solid #e0e0e0;border-radius:8px;padding:10px 28px;font-size:0.85rem;text-decoration:none; }
.btn-cancel:hover { background:#f5f5f5;color:#333; }
</style>

<div class="page-body">
  <div class="confirm-card">
    <div class="icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
    <h3>Delete Category</h3>
    <p>Are you sure you want to delete <strong><?= htmlspecialchars($categorie['nom']) ?></strong>? This action cannot be undone.</p>
    <form method="POST" class="d-flex gap-2 justify-content-center">
      <button class="btn-confirm">Yes, Delete</button>
      <a href="afficherCategorie.php" class="btn-cancel">Cancel</a>
    </form>
  </div>
</div>

<?php include("footer.php"); ?>
