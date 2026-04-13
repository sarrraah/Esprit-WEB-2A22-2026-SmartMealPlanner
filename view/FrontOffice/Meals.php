<?php

require_once __DIR__ . '/../../controller/MealController.php';

$meals = MealController::listMeals();
$assetPrefix = '../assets/';

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

  <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="<?php echo $assetPrefix; ?>css/main.css" rel="stylesheet">
  <link href="<?php echo $assetPrefix; ?>css/meals.css" rel="stylesheet">
</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container position-relative d-flex align-items-center justify-content-between">

      <a href="../index.php" class="logo d-flex align-items-center me-auto me-xl-0">
        <img src="<?php echo $assetPrefix; ?>img/logo-smp.jpg" alt="SmartMealPlanner" height="44">
        <h1 class="sitename">SmartMealPlanner</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="../index.php">Home</a></li>
          <li><a href="Meals.php" class="active">Meals</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <a class="btn-getstarted" href="Meals.php">Plan meals</a>

    </div>
  </header>

  <main class="main">

    <section id="meals" class="meals-module section light-background">
      <div class="container section-title">
        <h2>Meals</h2>
        <p><span>Browse</span> <span class="description-title">Your meal gallery</span></p>
      </div>

      <div class="container">
        <div class="row g-4">
          <?php foreach ($meals as $meal) :
            $imgSrc = htmlspecialchars(resolveImageUrl($meal->image, $assetPrefix), ENT_QUOTES, 'UTF-8');
            $safeName = htmlspecialchars($meal->name, ENT_QUOTES, 'UTF-8');
            $safeDesc = htmlspecialchars($meal->description, ENT_QUOTES, 'UTF-8');
            $safeRecipe = htmlspecialchars($meal->recipeUrl, ENT_QUOTES, 'UTF-8');
            $safeType = htmlspecialchars($meal->mealType, ENT_QUOTES, 'UTF-8');
            $safeTypeLabel = htmlspecialchars($meal->mealTypeLabel(), ENT_QUOTES, 'UTF-8');
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
          <h2 class="modal-title fs-5" id="mealDetailModalLabel">Meal details</h2>
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

  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="meals.js"></script>
</body>

</html>
