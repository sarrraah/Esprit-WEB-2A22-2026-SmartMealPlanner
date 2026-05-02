<?php

require_once __DIR__ . '/../../controller/MealController.php';
require_once __DIR__ . '/../../model/Plan.php';

$meals       = MealController::listMeals();
$assetPrefix = '/3rdV/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/assets/';

// Search handling
$searchQuery  = trim($_GET['q']        ?? '');
$searchBy     = trim($_GET['searchBy'] ?? 'name');
if ($searchQuery !== '') {
    $meals = MealController::searchMeals($searchQuery, $searchBy);
}

// Get active plan dates for day picker
$plan = Plan::first();
$planStart = $plan ? $plan->dateDebut : date('Y-m-d');
$planEnd   = $plan ? $plan->dateFin   : date('Y-m-d', strtotime('+7 days'));

// JOIN data: keyed by id_meal for O(1) lookup in the card loop
$planData = [];
foreach (MealController::listMealsWithPlan() as $row) {
    $planData[(int)$row['id_meal']] = [
        'plan_name' => (string)($row['plan_name'] ?? ''),
        'objectif'  => (string)($row['objectif']  ?? ''),
    ];
}

function resolveImageUrl(string $image, string $prefix): string {
    return $prefix . ltrim(preg_replace('#^assets/#', '', $image), '/');
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Meals — Smart Meal Planner</title>
  <meta name="description" content="Browse meals for your smart meal plan.">

  <link href="<?php echo $assetPrefix; ?>img/favicon.jpg" rel="icon">

  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Amatic+SC:wght@400;700&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?php echo $assetPrefix; ?>css/main.css" rel="stylesheet">
  <link href="<?php echo $assetPrefix; ?>css/meals.css" rel="stylesheet">
</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container position-relative d-flex align-items-center justify-content-between">

      <a href="index.php" class="logo d-flex align-items-center me-auto me-xl-0">
        <img src="<?php echo $assetPrefix; ?>img/logo-smp.jpg" alt="SmartMealPlanner" height="44">
        <h1 class="sitename"><span style="color:#212529;">Smart</span><span style="color:#ce1212;">MealPlanner</span></h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="Meals.php" class="active">Meals</a></li>
          <li><a href="Plans.php">My Plan</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>



    </div>
  </header>

  <main class="main">

    <section id="meals" class="meals-module section light-background">
      <div class="container section-title">
        <h2>Meals</h2>
        <p><span>Browse</span> <span class="description-title">Your meal gallery</span></p>
      </div>

      <div class="container">

        <!-- Search bar -->
        <form method="GET" action="Meals.php" class="mb-4">
          <div class="d-flex flex-wrap gap-2 justify-content-center align-items-center">
            <div class="input-group" style="max-width:480px;">
              <input
                type="text"
                class="form-control rounded-pill-start"
                name="q"
                placeholder="Search meals..."
                value="<?php echo htmlspecialchars($searchQuery); ?>"
                style="border-radius:50px 0 0 50px;border-color:#ce1212;"
              >
              <select name="searchBy" class="form-select" style="max-width:130px;border-color:#ce1212;border-left:0;">
                <option value="name"     <?php echo $searchBy==='name'    ?'selected':''; ?>>Name</option>
                <option value="calories" <?php echo $searchBy==='calories'?'selected':''; ?>>Calories</option>
              </select>
              <button class="btn btn-danger" type="submit" style="border-radius:0 50px 50px 0;">
                Search
              </button>
            </div>
            <?php if ($searchQuery !== ''): ?>
            <a href="Meals.php" class="btn btn-outline-secondary rounded-pill">Clear</a>
            <?php endif; ?>
          </div>
          <?php if ($searchQuery !== ''): ?>
          <p class="text-center text-muted mt-2" style="font-size:.9rem;">
            <?php echo count($meals); ?> result(s) for "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>" by <?php echo htmlspecialchars($searchBy); ?>
          </p>
          <?php endif; ?>
        </form>

        <!-- Filter bar -->
        <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
          <button class="btn btn-danger rounded-pill meal-filter active" data-filter="all">All</button>
          <button class="btn btn-outline-danger rounded-pill meal-filter" data-filter="breakfast">Breakfast</button>
          <button class="btn btn-outline-danger rounded-pill meal-filter" data-filter="lunch">Lunch</button>
          <button class="btn btn-outline-danger rounded-pill meal-filter" data-filter="dinner">Dinner</button>
          <button class="btn btn-outline-danger rounded-pill meal-filter" data-filter="snack">Snack</button>
          <button class="btn btn-outline-success rounded-pill meal-filter" data-filter="low-cal">Low Calories</button>
        </div>

        <div class="row g-4" id="meal-grid">
          <?php foreach ($meals as $meal) :
            $imgSrc       = htmlspecialchars(resolveImageUrl($meal->image, $assetPrefix), ENT_QUOTES, 'UTF-8');
            $safeName     = htmlspecialchars($meal->name, ENT_QUOTES, 'UTF-8');
            $safeDesc     = htmlspecialchars($meal->description, ENT_QUOTES, 'UTF-8');
            $safeRecipe   = htmlspecialchars($meal->recipeUrl, ENT_QUOTES, 'UTF-8');
            $safeType     = htmlspecialchars($meal->mealType, ENT_QUOTES, 'UTF-8');
            $safeTypeLabel = htmlspecialchars($meal->mealTypeLabel(), ENT_QUOTES, 'UTF-8');
            // JOIN data — gracefully empty if meal has no id_plan
            $pd           = $planData[$meal->id] ?? ['plan_name' => '', 'objectif' => ''];
            $safePlanName = htmlspecialchars($pd['plan_name'], ENT_QUOTES, 'UTF-8');
            $safeObjectif = htmlspecialchars($pd['objectif'],  ENT_QUOTES, 'UTF-8');
          ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
              <article
                class="meal-card"
                data-meal-id="<?php echo (int) $meal->id; ?>"
                data-meal-name="<?php echo $safeName; ?>"
                data-meal-calories="<?php echo (int) $meal->calories; ?>"
                data-meal-description="<?php echo $safeDesc; ?>"
                data-meal-image="<?php echo $imgSrc; ?>"
                data-meal-recipe="<?php echo $safeRecipe; ?>"
                data-meal-type="<?php echo $safeType; ?>"
                data-meal-type-label="<?php echo $safeTypeLabel; ?>"
              >
                <div class="meal-card__media">
                  <img src="<?php echo $imgSrc; ?>" alt="<?php echo $safeName; ?>" loading="lazy">
                </div>
                <div class="meal-card__body">
                  <h3 class="meal-card__name"><?php echo $safeName; ?></h3>
                  <p class="meal-card__calories"><strong><?php echo (int) $meal->calories; ?></strong> kcal</p>
                  <?php if ($safePlanName !== '') : ?>
                  <p class="meal-card__plan" style="font-size:.8rem;color:#888;margin:0;">
                    Plan: <?php echo $safePlanName; ?>
                    <?php if ($safeObjectif !== '') : ?>
                    &nbsp;· Goal: <?php echo $safeObjectif; ?>
                    <?php endif; ?>
                  </p>
                  <?php endif; ?>
                </div>
              </article>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

  </main>

  <footer id="footer" class="footer dark-background">
    <div class="container copyright text-center py-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">Smart Meal Planner</strong> <span>All Rights Reserved</span></p>
    </div>
  </footer>

  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <div id="preloader"></div>

  <div class="modal fade" id="mealDetailModal" tabindex="-1" aria-labelledby="mealDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h2 class="modal-title fs-5" id="mealDetailModalLabel" style="font-size:1.15rem !important;font-weight:700;letter-spacing:.04em;text-transform:uppercase;">Meal details</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="meal-detail__top">
            <div class="meal-detail__image-wrap">
              <img src="" alt="" data-meal-detail="image">
            </div>
            <div class="meal-detail__info-top">
              <h3 class="meal-detail__title" data-meal-detail="name"></h3>
              <p class="meal-detail__calories" data-meal-detail="calories"></p>
            </div>
          </div>
          <div class="meal-detail__bottom">
            <div class="meal-detail__copy">
              <p class="meal-detail__type" data-meal-detail="type"></p>
              <p class="meal-detail__description" data-meal-detail="description"></p>
            </div>
            <div class="meal-detail__actions">
              <a class="btn btn-meal-recipe" data-meal-detail="recipe" href="#" target="_blank" rel="noopener noreferrer">Recipe</a>
              <button type="button" class="btn btn-meal-add" data-meal-detail="add" title="Add to plan" aria-label="Add meal to plan">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Day picker modal -->
  <div class="modal fade" id="dayPickerModal" tabindex="-1" aria-labelledby="dayPickerLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
      <div class="modal-content" style="border-radius:16px;">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold" id="dayPickerLabel">Choose a day</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body pt-2">
          <p class="text-muted mb-3" style="font-size:.9rem;">Which day should <strong id="dp-meal-name"></strong> be added to?</p>
          <div id="dp-days" class="d-flex flex-column gap-2"></div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    window.PLAN_START = '<?php echo $planStart; ?>';
    window.PLAN_END   = '<?php echo $planEnd; ?>';
    console.log('Meals.php inline script loaded');
  </script>
  <script src="/3rdV/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/assets/js/meals.js?v=<?php echo time(); ?>"></script>

  <script>
    document.querySelectorAll('.meal-filter').forEach(function(btn) {
      btn.addEventListener('click', function() {
        document.querySelectorAll('.meal-filter').forEach(function(b) {
          b.classList.remove('active', 'btn-danger', 'btn-success');
          b.classList.add(b.dataset.filter === 'low-cal' ? 'btn-outline-success' : 'btn-outline-danger');
        });
        this.classList.add('active');
        this.classList.remove('btn-outline-danger', 'btn-outline-success');
        this.classList.add(this.dataset.filter === 'low-cal' ? 'btn-success' : 'btn-danger');

        var filter = this.dataset.filter;
        document.querySelectorAll('#meal-grid .col-lg-3').forEach(function(col) {
          var card = col.querySelector('.meal-card');
          var type = card.dataset.mealType;
          var cal  = parseInt(card.dataset.mealCalories, 10);
          var show = filter === 'all'
                  || filter === type
                  || (filter === 'low-cal' && cal < 400);
          col.style.display = show ? '' : 'none';
        });
      });
    });
  </script>
</body>

</html>

