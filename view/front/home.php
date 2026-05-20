<?php
$pageTitle  = 'Smart Meal Planner - Home';
require_once __DIR__ . '/../../controller/MealController.php';
require_once __DIR__ . '/../../controller/ProduitController.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Recette.php';
require_once __DIR__ . '/header.php';

// Pull up to 6 featured meals from the real meals module
$featuredMeals = array_slice(MealController::listMeals(), 0, 6);

// Pull up to 6 featured products from the real shop module
$_prodCtrl    = new ProduitController();
$featuredProds = array_slice($_prodCtrl->listProduits(), 0, 6);

$assetPrefix = '/integration/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/assets/';

function homeResolveImage(string $image, string $prefix): string {
    if ($image === '') return $prefix . 'img/meals/meal-07.png';
    if (str_starts_with($image, 'http')) return $image;
    return $prefix . ltrim(preg_replace('#^assets/#', '', $image), '/');
}

function homeResolveProduitImage(string $img): string {
    if ($img === '') return '';
    if (str_starts_with($img, 'http')) return $img;
    if (str_starts_with($img, 'meals/')) return '/integration/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/assets/img/' . $img;
    return UPLOAD_URL . $img;
}

$tpl = '../assets/img/';   // template images base
$own = '../assets/img/';            // our own images base
?>

<!-- -- HERO -- -->
<section id="hero" class="hero section light-background">
  <div class="container position-relative">
    <div class="row gy-4 justify-content-center justify-content-lg-between align-items-center">

      <!-- Left: title + description + buttons -->
      <div class="col-lg-4 order-2 order-lg-1 d-flex flex-column justify-content-center">
        <h1>Enjoy Your Healthy<br>Delicious Food</h1>
        <p>Plan your meals intelligently. Discover balanced recipes, manage your daily nutrition and reach your health goals - all in one place.</p>
        <div class="d-flex">
          <a href="Meals.php" class="btn-get-started">Browse Meals</a>
          <a href="interfaceevent.php" class="glightbox btn-watch-video d-flex align-items-center ms-4">
            <i class="bi bi-play-circle"></i><span class="ms-2">View Events</span>
          </a>
        </div>
      </div>

      <!-- Centre: feature icons grid -->
      <div class="col-lg-3 order-3 order-lg-2 d-flex align-items-center justify-content-center">
        <div class="hero-features-grid">
          <a href="Meals.php" class="hero-feat-item">
            <div class="hero-feat-icon" style="background:#f0faf0;"><i class="bi bi-flower1" style="color:#4caf50;"></i></div>
            <span>Healthy<br>Meals</span>
          </a>
          <a href="repas.php" class="hero-feat-item">
            <div class="hero-feat-icon" style="background:#fff8e8;"><i class="bi bi-journal-richtext" style="color:#f59e0b;"></i></div>
            <span>Delicious<br>Recipes</span>
          </a>
          <a href="Plans.php" class="hero-feat-item">
            <div class="hero-feat-icon" style="background:#fff0f0;"><i class="bi bi-calendar3" style="color:#ce1212;"></i></div>
            <span>Meal<br>Planning</span>
          </a>
          <a href="interfaceevent.php" class="hero-feat-item">
            <div class="hero-feat-icon" style="background:#f5f0ff;"><i class="bi bi-people" style="color:#8b5cf6;"></i></div>
            <span>Events &amp;<br>Workshops</span>
          </a>
          <a href="#" class="hero-feat-item">
            <div class="hero-feat-icon" style="background:#fff4ed;"><i class="bi bi-graph-up-arrow" style="color:#f97316;"></i></div>
            <span>Nutrition<br>Tracking</span>
          </a>
          <a href="produits.php" class="hero-feat-item">
            <div class="hero-feat-icon" style="background:#eff6ff;"><i class="bi bi-basket2" style="color:#3b82f6;"></i></div>
            <span>Shop<br>Products</span>
          </a>
        </div>
      </div>

      <!-- Right: hero image -->
      <div class="col-lg-4 order-1 order-lg-3 hero-img">
        <img src="<?= $tpl ?>hero-img.png" class="img-fluid animated" alt="Smart Meal Planner">
      </div>

    </div>
  </div>
</section>
<style>
.hero-features-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.hero-feat-item {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  color: #2d2d2d;
  transition: transform .2s;
}
.hero-feat-item:hover { transform: translateY(-2px); color: #ce1212; }
.hero-feat-icon {
  width: 46px;
  height: 46px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  flex-shrink: 0;
}
.hero-feat-item span {
  font-size: .8rem;
  font-weight: 600;
  line-height: 1.3;
}
</style><!-- /Hero -->

<!-- -- MENU / FEATURED MEALS -- -->
<section id="menu" class="menu section">
  <div class="container section-title">
    <h2>Our Menu</h2>
    <p><span>Check Our </span><span class="description-title">Featured Meals</span></p>
    <p style="font-size:.92rem;color:#888;font-family:'Inter',sans-serif;font-weight:400;margin-top:-10px;">
      Delicious, balanced meals crafted by nutrition experts to fuel your body and fit your goals.
    </p>
  </div>

  <div class="container">
    <div class="row g-0 align-items-start">

      <!-- Left sidebar: selling points -->
      <div class="col-lg-2 d-none d-lg-flex flex-column gap-3 pe-3" style="padding-top:4px;">
        <div class="hm-feat">
          <div class="hm-feat-icon" style="background:#f0faf0;"><i class="bi bi-flower1" style="color:#4caf50;"></i></div>
          <div><strong>Healthy &amp; Balanced</strong><br>Each meal is balanced with the right nutrients.</div>
        </div>
        <div class="hm-feat">
          <div class="hm-feat-icon" style="background:#fff8e8;"><i class="bi bi-fire" style="color:#f59e0b;"></i></div>
          <div><strong>Calorie Smart</strong><br>Clearly displayed calories help you stay on track.</div>
        </div>
        <div class="hm-feat">
          <div class="hm-feat-icon" style="background:#fff0f0;"><i class="bi bi-journal-richtext" style="color:#ce1212;"></i></div>
          <div><strong>Chef-Crafted Recipes</strong><br>Tasty meals made with real, wholesome ingredients.</div>
        </div>
        <div class="hm-feat">
          <div class="hm-feat-icon" style="background:#f5f0ff;"><i class="bi bi-heart" style="color:#8b5cf6;"></i></div>
          <div><strong>For Every Goal</strong><br>Meals for weight loss, muscle gain, and healthy living.</div>
        </div>
        <a href="Meals.php" class="hm-explore-link">Explore All Meals <i class="bi bi-arrow-right"></i></a>
      </div>

      <!-- Meal cards: horizontal scroll on mobile, grid on desktop -->
      <div class="col-lg-10">
        <div class="hm-cards-row">
          <?php foreach ($featuredMeals as $meal):
            $img = homeResolveImage($meal->image, $assetPrefix);
            // Badge based on meal type
            $badge = match($meal->mealType) {
              'breakfast' => ['label' => 'BREAKFAST',   'color' => '#8b5cf6'],
              'lunch'     => ['label' => 'LOW CARB',    'color' => '#10b981'],
              'dinner'    => ['label' => 'HIGH PROTEIN','color' => '#f59e0b'],
              'snack'     => ['label' => 'LOW CALORIE', 'color' => '#3b82f6'],
              default     => ['label' => 'VEGAN',       'color' => '#4caf50'],
            };
            if ($meal->calories < 400) $badge = ['label' => 'LOW CALORIE', 'color' => '#3b82f6'];
            if ($meal->calories > 600) $badge = ['label' => 'HIGH PROTEIN','color' => '#f59e0b'];
          ?>
          <div class="hm-card">
            <div class="hm-card-img-wrap">
              <img src="<?= htmlspecialchars($img) ?>"
                   alt="<?= htmlspecialchars($meal->name) ?>"
                   onerror="this.src='<?= $assetPrefix ?>img/meals/meal-07.png'">
              <span class="hm-badge" style="background:<?= $badge['color'] ?>;"><?= $badge['label'] ?></span>
            </div>
            <div class="hm-card-body">
              <p class="hm-card-name"><?= htmlspecialchars($meal->name) ?></p>
              <p class="hm-card-desc"><?= htmlspecialchars(mb_substr($meal->description, 0, 55)) ?>.</p>
              <div class="hm-card-footer">
                <span class="hm-card-kcal"><?= (int)$meal->calories ?> kcal</span>
                <a href="Meals.php" class="hm-card-fav" title="View in Meals"><i class="bi bi-heart"></i></a>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>

    <!-- Bottom personalise banner -->
    <div class="hm-personalize-banner mt-4">
      <div class="d-flex align-items-center gap-3">
        <div class="hm-personalize-icon"><i class="bi bi-stars"></i></div>
        <div>
          <div style="font-size:.68rem;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#ce1212;">MAKE IT YOURS</div>
          <div style="font-family:'Amatic SC',cursive;font-size:1.5rem;font-weight:700;color:#2d2d2d;line-height:1.2;">
            Personalize Your <span style="color:#ce1212;">Meals Your Way</span>
          </div>
        </div>
      </div>
      <div class="hm-personalize-mid">
        Filter by calories, meal type, diet preference and more. Find the perfect meal for you!
        <br><a href="Plans.php" style="color:#ce1212;font-weight:700;font-size:.85rem;text-decoration:none;">Customize Now <i class="bi bi-arrow-right"></i></a>
      </div>
      <div class="hm-personalize-img">
        <img src="<?= $assetPrefix ?>img/meals/meal-16.png" alt="Personalize meals"
             onerror="this.style.display='none'">
      </div>
    </div>

  </div>
</section>
<style>
/* -- Sidebar features -- */
.hm-feat { display:flex; align-items:flex-start; gap:10px; font-size:.75rem; color:#555; line-height:1.35; }
.hm-feat strong { display:block; font-size:.78rem; color:#2d2d2d; margin-bottom:2px; }
.hm-feat-icon { width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
.hm-explore-link { font-size:.8rem; font-weight:700; color:#ce1212; text-decoration:none; display:flex; align-items:center; gap:4px; margin-top:4px; }
.hm-explore-link:hover { text-decoration:underline; }

/* -- Cards row -- */
.hm-cards-row {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 12px;
}
@media (max-width:1199px) { .hm-cards-row { grid-template-columns: repeat(3,1fr); } }
@media (max-width:767px)  { .hm-cards-row { grid-template-columns: repeat(2,1fr); } }

/* -- Single card -- */
.hm-card {
  background: #fff;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0,0,0,.08);
  transition: transform .2s, box-shadow .2s;
  cursor: pointer;
}
.hm-card:hover { transform: translateY(-5px); box-shadow: 0 8px 24px rgba(0,0,0,.13); }
.hm-card-img-wrap { position:relative; }
.hm-card-img-wrap img { width:100%; height:130px; object-fit:cover; display:block; }
.hm-badge {
  position:absolute; top:8px; left:8px;
  font-size:.58rem; font-weight:800; letter-spacing:.6px; text-transform:uppercase;
  color:#fff; padding:3px 8px; border-radius:20px;
}
.hm-card-body { padding:10px 10px 12px; }
.hm-card-name {
  font-size:.75rem; font-weight:700; color:#2d2d2d; margin:0 0 4px;
  line-height:1.3;
  display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
}
.hm-card-desc {
  font-size:.68rem; color:#999; margin:0 0 8px; line-height:1.3;
  display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
}
.hm-card-footer { display:flex; align-items:center; justify-content:space-between; }
.hm-card-kcal { font-size:.75rem; font-weight:700; color:#2d2d2d; }
.hm-card-fav {
  width:28px; height:28px; border-radius:50%; border:1.5px solid #e0e0e0;
  display:flex; align-items:center; justify-content:center;
  color:#ccc; font-size:.85rem; text-decoration:none; transition:.2s;
}
.hm-card-fav:hover { border-color:#ce1212; color:#ce1212; }

/* -- Personalise banner -- */
.hm-personalize-banner {
  background: linear-gradient(135deg,#fff8f8 0%,#fff0f0 100%);
  border: 1px solid #fde0e0;
  border-radius: 16px;
  padding: 20px 28px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  flex-wrap: wrap;
  overflow: hidden;
}
.hm-personalize-icon {
  width:52px; height:52px; border-radius:50%;
  background:linear-gradient(135deg,#fff0f0,#ffd6d6);
  display:flex; align-items:center; justify-content:center;
  font-size:1.4rem; color:#ce1212; flex-shrink:0;
}
.hm-personalize-mid { font-size:.82rem; color:#666; line-height:1.5; flex:1; min-width:200px; }
.hm-personalize-img { width:120px; height:80px; border-radius:12px; overflow:hidden; flex-shrink:0; }
.hm-personalize-img img { width:100%; height:100%; object-fit:cover; }
@media (max-width:767px) { .hm-personalize-img { display:none; } }
</style><!-- /Menu -->

<!-- -- RECIPES TEASER -- -->
<?php
$_recetteModel  = new Recette();
$_allRecettes   = $_recetteModel->getAllRecettes();
$featuredRecipes = array_slice($_allRecettes, 0, 6);

// Count by difficulte for category bar
$_diffCounts = ['Facile' => 0, 'Moyen' => 0, 'Difficile' => 0];
foreach ($_allRecettes as $_r) {
    $d = $_r['difficulte'] ?? 'Facile';
    if (isset($_diffCounts[$d])) $_diffCounts[$d]++;
}
$_totalRecettes = count($_allRecettes);

function homeResolveRecetteImage(string $img, string $baseUrl): string {
    if ($img === '') return '';
    if (str_starts_with($img, 'http')) return $img;
    return $baseUrl . '/' . ltrim($img, '/');
}
$_recetteBaseUrl = '/integration/Esprit-WEB-2A22-2025-2026-SmartMealPlanner';
?>
<section id="recipes-teaser" class="section">
  <div class="container section-title">
    <h2>Recipes</h2>
    <p><span>Discover </span><span class="description-title">Delicious</span><span> &amp; Healthy </span><span class="description-title">Recipes</span></p>
    <p style="font-size:.92rem;color:#888;font-family:'Inter',sans-serif;font-weight:400;margin-top:-10px;">
      Explore a variety of easy-to-make, nutritious recipes perfect for any meal of the day.
    </p>
  </div>

  <div class="container">
    <div class="row g-0 align-items-start">

      <!-- Left sidebar -->
      <div class="col-lg-2 d-none d-lg-flex flex-column gap-3 pe-3" style="padding-top:4px;">
        <div class="hm-feat">
          <div class="hm-feat-icon" style="background:#f0faf0;"><i class="bi bi-flower1" style="color:#4caf50;"></i></div>
          <div><strong>Healthy Ingredients</strong><br>Wholesome and natural ingredients for better living.</div>
        </div>
        <div class="hm-feat">
          <div class="hm-feat-icon" style="background:#fff8e8;"><i class="bi bi-list-ol" style="color:#f59e0b;"></i></div>
          <div><strong>Step-by-Step Guides</strong><br>Simple instructions to make cooking fun and easy.</div>
        </div>
        <div class="hm-feat">
          <div class="hm-feat-icon" style="background:#fff0f0;"><i class="bi bi-heart" style="color:#ce1212;"></i></div>
          <div><strong>Nutritious &amp; Delicious</strong><br>Perfect balance of taste and nutrition in every recipe.</div>
        </div>
        <div class="hm-feat">
          <div class="hm-feat-icon" style="background:#f5f0ff;"><i class="bi bi-bookmark" style="color:#8b5cf6;"></i></div>
          <div><strong>Save &amp; Cook Later</strong><br>Save your favorite recipes and cook anytime.</div>
        </div>
        <a href="repas.php" class="hm-explore-link">Browse All Recipes <i class="bi bi-arrow-right"></i></a>
      </div>

      <!-- Recipe cards -->
      <div class="col-lg-10">
        <div class="hm-cards-row">
          <?php if (!empty($featuredRecipes)):
            foreach ($featuredRecipes as $rec):
              $rImg   = homeResolveRecetteImage((string)($rec['image_recette'] ?? ''), $_recetteBaseUrl);
              $rName  = htmlspecialchars($rec['nom_recette'] ?? 'Recipe');
              $rDiff  = $rec['difficulte'] ?? 'Facile';
              $rPrep  = (int)($rec['temps_prep'] ?? 0);
              $rCook  = (int)($rec['temps_cuisson'] ?? 0);
              $rTotal = $rPrep + $rCook;
              $rPers  = (int)($rec['nb_personnes'] ?? 2);
              // Badge by difficulty
              $rBadge = match($rDiff) {
                'Facile'    => ['label' => 'EASY',        'color' => '#10b981'],
                'Moyen'     => ['label' => 'MEDIUM',      'color' => '#f59e0b'],
                'Difficile' => ['label' => 'HIGH PROTEIN','color' => '#ce1212'],
                default     => ['label' => 'NEW',         'color' => '#4caf50'],
              };
              // Short description from etapes
              $rDesc = htmlspecialchars(mb_substr(strip_tags($rec['etapes'] ?? ''), 0, 60));
          ?>
          <div class="hm-card">
            <div class="hm-card-img-wrap">
              <?php if ($rImg): ?>
                <img src="<?= $rImg ?>" alt="<?= $rName ?>"
                     onerror="this.parentElement.innerHTML='<div style=\'height:130px;background:linear-gradient(135deg,#fff0f0,#ffd6d6);display:flex;align-items:center;justify-content:center;font-size:2.5rem;\'>???</div>'">
              <?php else: ?>
                <div style="height:130px;background:linear-gradient(135deg,#fff0f0,#ffd6d6);display:flex;align-items:center;justify-content:center;font-size:2.5rem;">???</div>
              <?php endif; ?>
              <span class="hm-badge" style="background:<?= $rBadge['color'] ?>;"><?= $rBadge['label'] ?></span>
              <a href="repas.php" class="hm-card-fav-abs" title="Save recipe"><i class="bi bi-heart"></i></a>
            </div>
            <div class="hm-card-body">
              <p class="hm-card-name"><?= $rName ?></p>
              <?php if ($rDesc): ?>
                <p class="hm-card-desc"><?= $rDesc ?>...</p>
              <?php endif; ?>
              <div class="hm-recipe-meta">
                <?php if ($rTotal > 0): ?>
                  <span><i class="bi bi-clock"></i> <?= $rTotal ?> min</span>
                <?php endif; ?>
                <?php if ($rPers > 0): ?>
                  <span><i class="bi bi-people"></i> <?= $rPers ?></span>
                <?php endif; ?>
              </div>
              <a href="repas.php" class="hm-view-recipe">View Recipe <i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
          <?php endforeach;
          else: ?>
          <div class="col-12 text-center text-muted py-4" style="font-size:.9rem;">
            No recipes yet. <a href="repas.php" class="text-danger">Visit the recipes page</a>
          </div>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <!-- Bottom banner -->
    <div class="hr-bottom-banner mt-4">
      <div class="hr-banner-left">
        <img src="<?= $assetPrefix ?>img/meals/meal-19.png" alt=""
             onerror="this.style.display='none'">
      </div>
      <div class="hr-banner-mid">
        <div style="font-size:.68rem;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#888;">COOK. ENJOY. SHARE.</div>
        <div style="font-family:'Amatic SC',cursive;font-size:1.6rem;font-weight:700;color:#2d2d2d;line-height:1.2;">
          Your Kitchen, <span style="color:#ce1212;">Your Creations</span>
        </div>
        <p style="font-size:.8rem;color:#888;margin-top:6px;">Find inspiration, try new flavors, and bring healthy recipes to life every day.</p>
      </div>
      <div class="hr-banner-features">
        <div class="hr-banner-feat"><i class="bi bi-bookmark-heart" style="color:#ce1212;"></i><div><strong>Save Favorites</strong><br><small>Keep your favorite recipes in one place.</small></div></div>
        <div class="hr-banner-feat"><i class="bi bi-share" style="color:#ce1212;"></i><div><strong>Share Recipes</strong><br><small>Share your creations with the community.</small></div></div>
        <div class="hr-banner-feat"><i class="bi bi-pencil-square" style="color:#ce1212;"></i><div><strong>Create Your Own</strong><br><small>Add your own recipes and inspire others.</small></div></div>
      </div>
      <a href="repas.php" class="btn-get-started" style="white-space:nowrap;flex-shrink:0;">Start Cooking <i class="bi bi-arrow-right ms-1"></i></a>
    </div>

    <!-- Category pills -->
    <div class="hr-cats mt-4">
      <a href="repas.php" class="hr-cat-pill"><span class="hr-cat-icon" style="background:#f0faf0;">??</span><div><strong>Vegetarian</strong><small><?= max(1, intval($_totalRecettes * 0.3)) ?>+ Recipes</small></div></a>
      <a href="repas.php" class="hr-cat-pill"><span class="hr-cat-icon" style="background:#fff8e8;">??</span><div><strong>High Protein</strong><small><?= max(1, intval($_totalRecettes * 0.25)) ?>+ Recipes</small></div></a>
      <a href="repas.php" class="hr-cat-pill"><span class="hr-cat-icon" style="background:#fff0f0;">??</span><div><strong>Low Calorie</strong><small><?= max(1, intval($_totalRecettes * 0.2)) ?>+ Recipes</small></div></a>
      <a href="repas.php" class="hr-cat-pill"><span class="hr-cat-icon" style="background:#f5f5f5;">??</span><div><strong>Gluten Free</strong><small><?= max(1, intval($_totalRecettes * 0.15)) ?>+ Recipes</small></div></a>
      <a href="repas.php" class="hr-cat-pill"><span class="hr-cat-icon" style="background:#fff8e8;">??</span><div><strong>Breakfast</strong><small><?= max(1, intval($_totalRecettes * 0.2)) ?>+ Recipes</small></div></a>
      <a href="repas.php" class="hr-cat-pill"><span class="hr-cat-icon" style="background:#eff6ff;">??</span><div><strong>Lunch</strong><small><?= max(1, intval($_totalRecettes * 0.35)) ?>+ Recipes</small></div></a>
      <a href="repas.php" class="hr-cat-pill"><span class="hr-cat-icon" style="background:#fff0f0;">???</span><div><strong>Dinner</strong><small><?= max(1, intval($_totalRecettes * 0.3)) ?>+ Recipes</small></div></a>
      <a href="repas.php" class="hr-cat-pill"><span class="hr-cat-icon" style="background:#f5f0ff;">??</span><div><strong>Desserts</strong><small><?= max(1, intval($_totalRecettes * 0.1)) ?>+ Recipes</small></div></a>
    </div>

  </div>
</section>
<style>
/* -- Recipe card extras -- */
.hm-card-fav-abs {
  position:absolute; top:8px; right:8px;
  width:28px; height:28px; border-radius:50%;
  background:rgba(255,255,255,.9);
  display:flex; align-items:center; justify-content:center;
  color:#ccc; font-size:.85rem; text-decoration:none; transition:.2s;
  box-shadow:0 1px 4px rgba(0,0,0,.12);
}
.hm-card-fav-abs:hover { color:#ce1212; }
.hm-recipe-meta {
  display:flex; gap:10px; font-size:.68rem; color:#999; margin-bottom:6px;
}
.hm-recipe-meta i { color:#ce1212; }
.hm-view-recipe {
  font-size:.72rem; font-weight:700; color:#ce1212; text-decoration:none;
  display:inline-flex; align-items:center; gap:3px;
}
.hm-view-recipe:hover { text-decoration:underline; }

/* -- Bottom banner -- */
.hr-bottom-banner {
  background:#fff;
  border:1px solid #f0f0f0;
  border-radius:16px;
  padding:20px 24px;
  display:flex; align-items:center; gap:20px; flex-wrap:wrap;
  box-shadow:0 2px 10px rgba(0,0,0,.05);
}
.hr-banner-left { width:90px; height:80px; border-radius:12px; overflow:hidden; flex-shrink:0; }
.hr-banner-left img { width:100%; height:100%; object-fit:cover; }
.hr-banner-mid { flex:1; min-width:180px; }
.hr-banner-features { display:flex; gap:20px; flex-wrap:wrap; }
.hr-banner-feat { display:flex; align-items:flex-start; gap:8px; font-size:.75rem; color:#555; min-width:120px; }
.hr-banner-feat i { font-size:1.1rem; flex-shrink:0; margin-top:2px; }
.hr-banner-feat strong { display:block; font-size:.78rem; color:#2d2d2d; }
.hr-banner-feat small { color:#999; }

/* -- Category pills -- */
.hr-cats {
  display:flex; flex-wrap:wrap; gap:10px; justify-content:center;
}
.hr-cat-pill {
  display:flex; align-items:center; gap:8px;
  background:#fff; border:1px solid #f0f0f0; border-radius:40px;
  padding:6px 14px 6px 8px;
  text-decoration:none; color:#2d2d2d;
  box-shadow:0 1px 4px rgba(0,0,0,.06);
  transition:.2s;
}
.hr-cat-pill:hover { border-color:#ce1212; color:#ce1212; transform:translateY(-2px); }
.hr-cat-icon {
  width:32px; height:32px; border-radius:50%;
  display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0;
}
.hr-cat-pill strong { display:block; font-size:.75rem; font-weight:700; line-height:1.2; }
.hr-cat-pill small { display:block; font-size:.65rem; color:#999; }
@media (max-width:767px) { .hr-banner-left,.hr-banner-features { display:none; } }
</style><!-- /Recipes Teaser -->

<!-- -- SHOP TEASER -- -->
<section id="shop-teaser" class="section light-background">
  <div class="container section-title">
    <h2>Shop</h2>
    <p><span>Shop </span><span class="description-title">Healthy</span><span>, Eat Better</span></p>
    <p style="font-size:.95rem;color:#888;font-family:'Inter',sans-serif;font-weight:400;margin-top:-10px;">
      Find high-quality, healthy ingredients and products handpicked for your meal plans.
    </p>
  </div>
  <div class="container">
    <div class="row align-items-start g-4">

      <!-- Left: selling points -->
      <div class="col-lg-2 col-md-3 d-none d-md-flex flex-column gap-3 justify-content-center" style="padding-top:8px;">
        <div class="d-flex align-items-start gap-2">
          <span style="width:34px;height:34px;border-radius:50%;background:#f0faf0;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1rem;">??</span>
          <div>
            <div style="font-size:.82rem;font-weight:700;color:#2d2d2d;">Carefully Selected</div>
            <div style="font-size:.73rem;color:#999;line-height:1.3;">We choose only the best for your health.</div>
          </div>
        </div>
        <div class="d-flex align-items-start gap-2">
          <span style="width:34px;height:34px;border-radius:50%;background:#fff8f0;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1rem;">??</span>
          <div>
            <div style="font-size:.82rem;font-weight:700;color:#2d2d2d;">Wide Variety</div>
            <div style="font-size:.73rem;color:#999;line-height:1.3;">From fresh produce to superfoods &amp; more.</div>
          </div>
        </div>
        <div class="d-flex align-items-start gap-2">
          <span style="width:34px;height:34px;border-radius:50%;background:#fff0f0;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1rem;">???</span>
          <div>
            <div style="font-size:.82rem;font-weight:700;color:#2d2d2d;">Trusted Quality</div>
            <div style="font-size:.73rem;color:#999;line-height:1.3;">Safe, reliable and nutrient-packed.</div>
          </div>
        </div>
        <a href="produits.php" style="font-size:.82rem;font-weight:700;color:#ce1212;text-decoration:none;display:flex;align-items:center;gap:4px;margin-top:4px;">
          Go to Shop <i class="bi bi-arrow-right"></i>
        </a>
      </div>

      <!-- Right: product cards -->
      <div class="col-lg-10 col-md-9">
        <div class="row g-3">
          <?php foreach ($featuredProds as $prod):
            $pImg = homeResolveProduitImage((string)($prod['image'] ?? ''));
            $pNom = htmlspecialchars($prod['nom'] ?? '', ENT_QUOTES);
            $pDesc = htmlspecialchars(mb_substr($prod['description'] ?? '', 0, 40), ENT_QUOTES);
            $pPrix = number_format((float)($prod['prix'] ?? 0), 2);
            $pStock = (int)($prod['quantiteStock'] ?? 0);
          ?>
          <div class="col-lg-2 col-md-4 col-sm-4 col-6">
            <a href="produits.php" class="text-decoration-none text-dark">
              <div class="home-prod-card">
                <?php if ($pImg): ?>
                  <img src="<?= $pImg ?>" alt="<?= $pNom ?>"
                       onerror="this.parentElement.innerHTML='<div class=\'home-prod-placeholder\'><i class=\'bi bi-image\'></i></div>'">
                <?php else: ?>
                  <div class="home-prod-placeholder"><i class="bi bi-image"></i></div>
                <?php endif; ?>
                <div class="home-prod-info">
                  <p class="home-prod-name"><?= $pNom ?></p>
                  <?php if ($pDesc): ?>
                    <p class="home-prod-desc"><?= $pDesc ?>...</p>
                  <?php endif; ?>
                  <div class="d-flex align-items-center justify-content-between mt-1">
                    <span class="home-prod-price"><?= $pPrix ?> DT</span>
                    <span class="home-prod-cart"><i class="bi bi-cart-plus"></i></span>
                  </div>
                </div>
              </div>
            </a>
          </div>
          <?php endforeach; ?>
          <?php if (empty($featuredProds)): ?>
          <div class="col-12 text-center text-muted py-4" style="font-size:.9rem;">
            No products available yet. <a href="produits.php" class="text-danger">Visit the shop</a>
          </div>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <!-- Bottom banner -->
    <div class="home-shop-banner mt-4">
      <div class="d-flex align-items-center gap-3">
        <span style="font-size:2rem;">??</span>
        <div>
          <div style="font-size:.7rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#888;">EAT WELL. LIVE WELL.</div>
          <div style="font-size:1rem;font-weight:700;color:#2d2d2d;line-height:1.3;">Everything You Need for a <span style="color:#ce1212;">Healthier Lifestyle</span></div>
        </div>
      </div>
      <div class="d-flex flex-wrap gap-4 align-items-center">
        <div class="home-shop-feat"><i class="bi bi-tag-fill"></i><div><strong>Exclusive Offers</strong><br><small>Enjoy special deals every week.</small></div></div>
        <div class="home-shop-feat"><i class="bi bi-truck"></i><div><strong>Fast Delivery</strong><br><small>Quick and reliable to your door.</small></div></div>
        <div class="home-shop-feat"><i class="bi bi-recycle"></i><div><strong>Sustainable Choices</strong><br><small>Good for you, good for the planet.</small></div></div>
      </div>
    </div>

    <div class="text-center mt-4">
      <a href="produits.php" class="btn-get-started">View All Products</a>
    </div>
  </div>
</section>
<style>
.home-prod-card {
  border-radius: 12px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 2px 8px rgba(0,0,0,.09);
  transition: transform .2s, box-shadow .2s;
}
.home-prod-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 18px rgba(0,0,0,.14);
}
.home-prod-card img {
  width: 100%;
  height: 110px;
  object-fit: cover;
  display: block;
}
.home-prod-placeholder {
  width: 100%;
  height: 110px;
  background: #f5f5f5;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: #ccc;
}
.home-prod-info { padding: 8px 10px 10px; }
.home-prod-name {
  font-size: .75rem;
  font-weight: 600;
  color: #2d2d2d;
  margin: 0 0 2px;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.home-prod-desc {
  font-size: .68rem;
  color: #aaa;
  margin: 0 0 4px;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.home-prod-price { font-size: .78rem; font-weight: 700; color: #ce1212; }
.home-prod-cart {
  width: 24px; height: 24px; border-radius: 50%;
  background: #ce1212; color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-size: .75rem; flex-shrink: 0;
}
.home-shop-banner {
  background: linear-gradient(135deg, #f9fdf5 0%, #f0faf0 100%);
  border: 1px solid #d4edda;
  border-radius: 16px;
  padding: 20px 28px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}
.home-shop-feat {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: .78rem;
  color: #555;
}
.home-shop-feat i { font-size: 1.2rem; color: #2e7d32; }
.home-shop-feat strong { font-size: .8rem; color: #2d2d2d; }
.home-shop-feat small { color: #888; }
</style><!-- /Shop Teaser -->

<!-- -- EVENTS TEASER -- -->
<?php
require_once __DIR__ . '/../../controller/EvenementController.php';
$_evCtrl      = new EvenementController();
$featuredEvts = array_slice($_evCtrl->listEvenements(), 0, 3);
?>
<section id="events" class="events section">
  <div class="container section-title">
    <h2>Events</h2>
    <p><span>Organize</span> <span class="description-title">Your Events</span></p>
  </div>
  <div class="container">
    <div class="row gy-4">
      <?php if (!empty($featuredEvts)):
        foreach ($featuredEvts as $evt):
          $evImg   = ($evt->getImage() && trim($evt->getImage()) !== '') ? '../assets/img/events/' . htmlspecialchars($evt->getImage()) : null;
          $evTitle = htmlspecialchars($evt->getTitre() ?? 'Event');
          $evDesc  = htmlspecialchars(mb_substr($evt->getDescription() ?? '', 0, 90));
          $evDate  = $evt->getDateDebut() ? date('M j, Y', strtotime($evt->getDateDebut())) : '';
          $evLieu  = htmlspecialchars($evt->getLieu() ?? '');
          $evType  = htmlspecialchars($evt->getType() ?? '');
          $evPrix  = (float)($evt->getPrix() ?? 0);
      ?>
      <div class="col-lg-4 col-md-6">
        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
          <?php if ($evImg): ?>
            <img src="<?= $evImg ?>" class="card-img-top"
                 alt="<?= $evTitle ?>"
                 style="height:200px;object-fit:cover;"
                 onerror="this.style.display='none'">
          <?php else: ?>
            <div style="height:200px;background:linear-gradient(135deg,#fff0f0,#ffe8e8);display:flex;align-items:center;justify-content:center;font-size:3rem;">📅</div>
          <?php endif; ?>
          <div class="card-body d-flex flex-column">
            <div class="d-flex gap-2 mb-2 flex-wrap">
              <?php if ($evType): ?>
                <span style="font-size:.7rem;font-weight:700;background:#fff0f0;color:#ce1212;border-radius:20px;padding:2px 10px;"><?= $evType ?></span>
              <?php endif; ?>
              <?php if ($evPrix == 0): ?>
                <span style="font-size:.7rem;font-weight:700;background:#f0faf0;color:#2e7d32;border-radius:20px;padding:2px 10px;">Free</span>
              <?php else: ?>
                <span style="font-size:.7rem;font-weight:700;background:#f5f5f5;color:#555;border-radius:20px;padding:2px 10px;"><?= number_format($evPrix, 2) ?> TND</span>
              <?php endif; ?>
            </div>
            <h5 class="card-title fw-bold mb-1"><?= $evTitle ?></h5>
            <?php if ($evDate || $evLieu): ?>
            <p class="text-muted mb-1" style="font-size:.78rem;">
              <?php if ($evDate): ?><i class="bi bi-calendar3 me-1"></i><?= $evDate ?><?php endif; ?>
              <?php if ($evLieu): ?> &nbsp;-&nbsp; <i class="bi bi-geo-alt me-1"></i><?= $evLieu ?><?php endif; ?>
            </p>
            <?php endif; ?>
            <p class="card-text text-muted small flex-grow-1"><?= $evDesc ?>...</p>
            <a href="interfaceevent.php" class="btn btn-sm btn-danger rounded-pill mt-2 align-self-start">View Event</a>
          </div>
        </div>
      </div>
      <?php endforeach;
      else: ?>
      <div class="col-12 text-center text-muted py-4" style="font-size:.9rem;">
        No events available yet. <a href="interfaceevent.php" class="text-danger">Visit the events page</a>
      </div>
      <?php endif; ?>
    </div>
    <div class="text-center mt-4">
      <a href="interfaceevent.php" class="btn-get-started">View All Events</a>
    </div>
  </div>
</section><!-- /Events -->

<!-- -- ABOUT US -- -->
<section id="about-us" class="section light-background">

  <!-- Hero banner -->
  <div class="about-hero">
    <div class="about-hero-img about-hero-img--left">
      <img src="https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400" alt="Healthy food">
    </div>
    <div class="about-hero-content">
      <div class="about-label">ABOUT US</div>
      <h2 class="about-title">About <span>SmartMealPlanner</span></h2>
      <p class="about-subtitle">
        SmartMealPlanner is your all-in-one platform for healthy living.<br>
        We make meal planning simple, nutritious, and enjoyable<br>
        so you can focus on what matters most - your well-being.
      </p>
      <div class="about-pillars">
        <div class="about-pillar">
          <div class="about-pillar-icon" style="background:#f0faf0;"><i class="bi bi-flower1" style="color:#4caf50;"></i></div>
          <div><strong>Healthy Living</strong><p>We promote balanced nutrition and sustainable habits.</p></div>
        </div>
        <div class="about-pillar">
          <div class="about-pillar-icon" style="background:#fff0f0;"><i class="bi bi-heart" style="color:#ce1212;"></i></div>
          <div><strong>Smart Planning</strong><p>Plan your meals intelligently and save time every day.</p></div>
        </div>
        <div class="about-pillar">
          <div class="about-pillar-icon" style="background:#fff8e8;"><i class="bi bi-patch-check" style="color:#f59e0b;"></i></div>
          <div><strong>Quality First</strong><p>We handpick the best ingredients and partners for you.</p></div>
        </div>
        <div class="about-pillar">
          <div class="about-pillar-icon" style="background:#f5f0ff;"><i class="bi bi-people" style="color:#8b5cf6;"></i></div>
          <div><strong>Community</strong><p>We build a supportive community that inspires each other.</p></div>
        </div>
      </div>
    </div>
    <div class="about-hero-img about-hero-img--right">
      <img src="https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=400" alt="Meal prep">
    </div>
  </div>

  <!-- Mission banner -->
  <div class="about-mission">
    <div class="about-mission-left">
      <div style="font-size:.7rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#2e7d32;margin-bottom:8px;">OUR MISSION</div>
      <h3 style="font-family:'Amatic SC',cursive;font-size:2rem;font-weight:700;color:#2d2d2d;line-height:1.2;margin-bottom:12px;">
        Good For You,<br>Good for the Planet
      </h3>
      <p style="font-size:.88rem;color:#555;line-height:1.6;">
        We are committed to helping people live healthier lives while contributing to a better, more sustainable world.
      </p>
    </div>
    <div class="about-mission-mid">
      <div style="font-size:.65rem;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#2e7d32;margin-bottom:12px;">WE SUPPORT THE UN SUSTAINABLE DEVELOPMENT GOALS</div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <div class="sdg-badge" style="background:#4c9f38;">3<br><small>GOOD HEALTH<br>& WELL-BEING</small></div>
        <div class="sdg-badge" style="background:#dda63a;">2<br><small>ZERO<br>HUNGER</small></div>
        <div class="sdg-badge" style="background:#bf8b2e;">12<br><small>RESPONSIBLE<br>CONSUMPTION</small></div>
        <div class="sdg-badge" style="background:#3f7e44;">13<br><small>CLIMATE<br>ACTION</small></div>
        <div class="sdg-badge" style="background:#19486a;">17<br><small>PARTNERSHIPS<br>FOR THE GOALS</small></div>
      </div>
    </div>
    <div class="about-mission-right">
      <img src="https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?w=300" alt="Sustainable planet" style="width:160px;height:120px;object-fit:cover;border-radius:12px;">
    </div>
  </div>

  <!-- Team -->
  <div class="container" style="padding-top:48px;padding-bottom:16px;">
    <div class="section-title text-center">
      <h2>Team</h2>
      <p><span>Meet </span><span class="description-title">Our Team</span></p>
      <div style="width:40px;height:3px;background:#ce1212;border-radius:2px;margin:0 auto 32px;"></div>
    </div>
    <div class="about-team-grid">

      <div class="about-team-card">
        <div class="about-team-avatar">
          <img src="../assets/img/team/balkis.jpeg" alt="Balkis Harrabi"
               onerror="this.src='https://ui-avatars.com/api/?name=Balkis+Harrabi&background=fde8e8&color=ce1212&size=80&bold=true'">
        </div>
        <h5>Balkis Harrabi</h5>
        <span class="about-team-role" style="color:#ce1212;">Project Manager</span>
        <p>Tech enthusiast and leader passionate about creating impactful solutions.</p>
        <div class="about-team-socials">
          <a href="#"><i class="bi bi-linkedin"></i></a>
          <a href="#"><i class="bi bi-github"></i></a>
          <a href="#"><i class="bi bi-envelope"></i></a>
        </div>
      </div>

      <div class="about-team-card">
        <div class="about-team-avatar">
          <img src="../assets/img/team/sarah.jpeg" alt="Sarah Skioui"
               onerror="this.src='https://ui-avatars.com/api/?name=Sarah+Skioui&background=fde8e8&color=ce1212&size=80&bold=true'">
        </div>
        <h5>Sarah Skioui</h5>
        <span class="about-team-role" style="color:#f59e0b;">Developer</span>
        <p>Full-stack developer who loves turning ideas into useful digital experiences.</p>
        <div class="about-team-socials">
          <a href="#"><i class="bi bi-linkedin"></i></a>
          <a href="#"><i class="bi bi-github"></i></a>
          <a href="#"><i class="bi bi-envelope"></i></a>
        </div>
      </div>

      <div class="about-team-card">
        <div class="about-team-avatar">
          <img src="../assets/img/team/ryhem.jpeg" alt="Ryhem Hajii"
               onerror="this.src='https://ui-avatars.com/api/?name=Ryhem+Hajii&background=fde8e8&color=ce1212&size=80&bold=true'">
        </div>
        <h5>Ryhem Hajii</h5>
        <span class="about-team-role" style="color:#f59e0b;">UI/UX Designer</span>
        <p>Designs beautiful and intuitive interfaces that users love.</p>
        <div class="about-team-socials">
          <a href="#"><i class="bi bi-linkedin"></i></a>
          <a href="#"><i class="bi bi-github"></i></a>
          <a href="#"><i class="bi bi-envelope"></i></a>
        </div>
      </div>

      <div class="about-team-card">
        <div class="about-team-avatar">
          <img src="../assets/img/team/rana.jpeg" alt="Rana Ben Abid"
               onerror="this.src='https://ui-avatars.com/api/?name=Rana+Ben+Abid&background=fde8e8&color=ce1212&size=80&bold=true'">
        </div>
        <h5>Rana Ben Abid</h5>
        <span class="about-team-role" style="color:#3b82f6;">Backend Developer</span>
        <p>Loves databases, logic and building reliable backend systems.</p>
        <div class="about-team-socials">
          <a href="#"><i class="bi bi-linkedin"></i></a>
          <a href="#"><i class="bi bi-github"></i></a>
          <a href="#"><i class="bi bi-envelope"></i></a>
        </div>
      </div>

      <div class="about-team-card">
        <div class="about-team-avatar">
          <img src="../assets/img/team/motaz.jpeg" alt="Motaz Ibn Hadj Mouldi"
               onerror="this.src='https://ui-avatars.com/api/?name=Motaz+Ibn+Hadj&background=fde8e8&color=ce1212&size=80&bold=true'">
        </div>
        <h5>Motaz Ibn Hadj Mouldi</h5>
        <span class="about-team-role" style="color:#4caf50;">Content & Nutrition</span>
        <p>Nutrition student who ensures our content is accurate and helpful.</p>
        <div class="about-team-socials">
          <a href="#"><i class="bi bi-linkedin"></i></a>
          <a href="#"><i class="bi bi-github"></i></a>
          <a href="#"><i class="bi bi-envelope"></i></a>
        </div>
      </div>

    </div>
  </div>

  <!-- Community banner -->
  <div class="about-community">
    <div class="about-community-left">
      <div style="font-size:.65rem;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#ce1212;">JOIN OUR GROWING COMMUNITY</div>
      <h3 style="font-family:'Amatic SC',cursive;font-size:2rem;font-weight:700;color:#2d2d2d;line-height:1.2;margin-top:8px;">
        Together, We Can Build<br>a <span style="color:#ce1212;">Healthier Future</span>
      </h3>
      <p style="font-size:.85rem;color:#666;margin-top:10px;">Thousands of users trust SmartMealPlanner to reach their goals and live better every day.</p>
    </div>
    <div class="about-community-stats">
      <div class="about-stat"><i class="bi bi-people-fill"></i><strong>5,000+</strong><span>Happy Users</span></div>
      <div class="about-stat"><i class="bi bi-calendar-check"></i><strong>10,000+</strong><span>Meals Planned</span></div>
      <div class="about-stat"><i class="bi bi-journal-richtext"></i><strong>1,500+</strong><span>Recipes Shared</span></div>
      <div class="about-stat"><i class="bi bi-star-fill"></i><strong>4.8/5</strong><span>User Rating</span></div>
    </div>
    <div class="about-community-img">
      <img src="https://images.unsplash.com/photo-1484723091739-30a097e8f929?w=300" alt="Healthy breakfast">
    </div>
  </div>

</section>
<style>
/* -- About hero -- */
.about-hero {
  display: flex; align-items: center; gap: 0;
  background: #fff; padding: 48px 0 32px;
  overflow: hidden;
}
.about-hero-img { width: 220px; flex-shrink: 0; }
.about-hero-img img { width: 100%; height: 260px; object-fit: cover; }
.about-hero-img--left img { border-radius: 0 16px 16px 0; }
.about-hero-img--right img { border-radius: 16px 0 0 16px; }
.about-hero-content { flex: 1; padding: 0 40px; text-align: center; }
.about-label { font-size:.65rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#999;margin-bottom:8px; }
.about-title { font-family:'Amatic SC',cursive;font-size:2.4rem;font-weight:700;color:#2d2d2d;margin-bottom:12px; }
.about-title span { color:#ce1212; }
.about-subtitle { font-size:.88rem;color:#666;line-height:1.7;margin-bottom:24px; }
.about-pillars { display:flex;flex-wrap:wrap;gap:16px;justify-content:center; }
.about-pillar { display:flex;align-items:flex-start;gap:10px;text-align:left;max-width:200px; }
.about-pillar-icon { width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0; }
.about-pillar strong { display:block;font-size:.8rem;font-weight:700;color:#2d2d2d;margin-bottom:2px; }
.about-pillar p { font-size:.72rem;color:#888;margin:0;line-height:1.4; }

/* -- Mission -- */
.about-mission {
  display: flex; align-items: center; gap: 32px; flex-wrap: wrap;
  background: linear-gradient(135deg,#f0faf0,#e8f5e9);
  padding: 36px 60px; margin: 0;
}
.about-mission-left { flex: 1; min-width: 220px; }
.about-mission-mid  { flex: 1; min-width: 260px; }
.about-mission-right { flex-shrink: 0; }
.sdg-badge {
  width: 64px; height: 64px; border-radius: 10px;
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  color: #fff; font-size: .9rem; font-weight: 800; text-align: center; line-height: 1.1;
}
.sdg-badge small { font-size: .45rem; font-weight: 600; line-height: 1.2; margin-top: 2px; }

/* -- Team -- */
.about-team-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 20px;
}
@media (max-width:1199px) { .about-team-grid { grid-template-columns: repeat(3,1fr); } }
@media (max-width:767px)  { .about-team-grid { grid-template-columns: repeat(2,1fr); } }
.about-team-card {
  text-align: center; padding: 20px 12px;
  background: #fff; border-radius: 16px;
  box-shadow: 0 2px 10px rgba(0,0,0,.07);
  transition: transform .2s, box-shadow .2s;
}
.about-team-card:hover { transform: translateY(-5px); box-shadow: 0 8px 24px rgba(0,0,0,.12); }
.about-team-avatar {
  width: 72px; height: 72px; border-radius: 50%;
  overflow: hidden; margin: 0 auto 12px;
  border: 3px solid #fde8e8;
}
.about-team-avatar img { width: 100%; height: 100%; object-fit: cover; }
.about-team-card h5 { font-size: .88rem; font-weight: 700; color: #2d2d2d; margin: 0 0 4px; }
.about-team-role { font-size: .75rem; font-weight: 700; display: block; margin-bottom: 8px; }
.about-team-card p { font-size: .72rem; color: #888; line-height: 1.4; margin: 0 0 12px; }
.about-team-socials { display: flex; justify-content: center; gap: 10px; }
.about-team-socials a { color: #bbb; font-size: .95rem; text-decoration: none; transition: .2s; }
.about-team-socials a:hover { color: #ce1212; }

/* -- Community -- */
.about-community {
  display: flex; align-items: center; gap: 32px; flex-wrap: wrap;
  background: #fff8f8; border-top: 1px solid #fde8e8;
  padding: 36px 60px; margin-top: 32px;
}
.about-community-left { flex: 1; min-width: 220px; }
.about-community-stats { display: flex; gap: 28px; flex-wrap: wrap; }
.about-stat { display: flex; flex-direction: column; align-items: center; gap: 4px; min-width: 80px; }
.about-stat i { font-size: 1.4rem; color: #ce1212; }
.about-stat strong { font-size: 1.1rem; font-weight: 800; color: #2d2d2d; }
.about-stat span { font-size: .7rem; color: #888; }
.about-community-img { flex-shrink: 0; }
.about-community-img img { width: 140px; height: 100px; object-fit: cover; border-radius: 12px; }
@media (max-width:767px) {
  .about-hero-img { display: none; }
  .about-mission, .about-community { padding: 24px 20px; }
  .about-community-img { display: none; }
}
</style><!-- /About Us -->



<?php require_once __DIR__ . '/footer.php'; ?>
