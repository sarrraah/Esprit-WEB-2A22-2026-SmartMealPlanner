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
        <h1 style="font-family: 'Patrick Hand', cursive; font-size: 3.2rem; font-weight: 400; color: #2d2d2d; line-height: 1.3; letter-spacing: 1px;">
          Découvrez nos <span style="color:#ce1212;">ingrédients</span><br>frais &amp; sains
        </h1>
        <p style="color: #6c757d; font-size: 1rem; margin-top: 1rem;">
          Packs meal prep, produits frais et ingrédients sélectionnés — cuisinez sain sans effort.
        </p>
        <div class="d-flex align-items-center gap-3 mt-4">
          <a href="produits.php" class="btn btn-danger rounded-pill px-4 py-2 fw-semibold" style="background-color:#c0392b;border:none;">
            Booka a Table
          </a>
          <a href="#" class="d-flex align-items-center gap-2 text-dark text-decoration-none fw-semibold">
            <span style="width:40px;height:40px;border:2px solid #c0392b;border-radius:50%;display:flex;align-items:center;justify-content:center;">
              <i class="bi bi-play-fill text-danger"></i>
            </span>
            Watch Video
          </a>
        </div>
      </div>
      <div class="col-lg-5 order-1 order-lg-2 hero-img">
        <img src="../assets/template/img/hero-img.png" class="img-fluid animated" alt="Hero">
      </div>
    </div>
  </div>
</section>

  <link href="https://fonts.googleapis.com/css2?family=Patrick+Hand&display=swap" rel="stylesheet">

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
