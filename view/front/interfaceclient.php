<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/CategorieController.php';

$categorieController = new CategorieController();
$categories = $categorieController->getAllCategories();

include("header.php");
?>

<section id="hero" class="hero section light-background">
  <div class="container">
    <div class="row gy-4 justify-content-center justify-content-lg-between">
      <div class="col-lg-5 order-2 order-lg-1 d-flex flex-column justify-content-center">
        <h1>Enjoy Your Healthy<br>Delicious Food</h1>
        <p>Explore notre boutique Smart Meal Planner avec des produits frais et bien classés.</p>
        <div class="d-flex gap-3 mt-3">
        </div>
      </div>
      <div class="col-lg-5 order-1 order-lg-2 hero-img">
        <img src="../assets/template/img/hero-img.png" class="img-fluid animated" alt="Hero">
      </div>
    </div>
  </div>
</section>

<script>
// Badge panier sur la home aussi
function updateBadge() {
    var total = JSON.parse(localStorage.getItem('panier') || '[]').reduce(function(s, p) { return s + p.quantite; }, 0);
    var badge = document.getElementById('panier-badge');
    if (badge) { badge.textContent = total; badge.style.display = total > 0 ? 'inline-block' : 'none'; }
}
function ouvrirPanier() { window.location.href = 'produits.php'; }
updateBadge();
</script>

<?php include("footer.php"); ?>
