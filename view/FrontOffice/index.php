<?php

require_once __DIR__ . '/../../controller/MealController.php';

$meals = MealController::listMeals();
$assetPrefix = '/3rdV/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/assets/';

function resolveImageUrl(string $image, string $prefix): string {
    return $prefix . ltrim(preg_replace('#^assets/#', '', $image), '/');
}

$featured = array_slice($meals, 0, 4);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Smart Meal Planner</title>
  <meta name="description" content="Plan your meals with Smart Meal Planner.">

  <link href="<?php echo $assetPrefix; ?>img/favicon.jpg" rel="icon">
  <link href="<?php echo $assetPrefix; ?>img/apple-touch-icon.png" rel="apple-touch-icon">

  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Amatic+SC:wght@400;700&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/glightbox@3.3.0/dist/css/glightbox.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet">
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
          <li><a href="index.php" class="active">Home</a></li>
          <li><a href="Meals.php">Meals</a></li>
          <li><a href="Plans.php">My Plan</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>



    </div>
  </header>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section light-background">
      <div class="container">
        <div class="row gy-4 justify-content-center justify-content-lg-between">
          <div class="col-lg-5 order-2 order-lg-1 d-flex flex-column justify-content-center">
            <h1 data-aos="fade-up">Enjoy Your Healthy<br>Delicious Food</h1>
            <p data-aos="fade-up" data-aos-delay="100">Discover and plan your meals with our smart meal planner.</p>
            <div class="d-flex" data-aos="fade-up" data-aos-delay="200">
              <a href="Meals.php" class="btn-get-started">Browse Meals</a>
            </div>
          </div>
          <div class="col-lg-5 order-1 order-lg-2 hero-img" data-aos="zoom-out">
            <img src="<?php echo $assetPrefix; ?>img/hero-img.png" class="img-fluid animated" alt="">
          </div>
        </div>
      </div>
    </section><!-- /Hero Section -->

    <!-- About Section -->
    <section id="about" class="about section">
      <div class="container section-title" data-aos="fade-up">
        <h2>About Us</h2>
        <p><span>Learn More</span> <span class="description-title">About Us</span></p>
      </div>
      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-7" data-aos="fade-up" data-aos-delay="100">
            <img src="<?php echo $assetPrefix; ?>img/about.jpg" class="img-fluid mb-4" alt="">
          </div>
          <div class="col-lg-5" data-aos="fade-up" data-aos-delay="250">
            <div class="content ps-0 ps-lg-5">
              <p class="fst-italic">
                We help you plan healthy, delicious meals tailored to your lifestyle and nutritional goals.
              </p>
              <ul>
                <li><i class="bi bi-check-circle-fill"></i> <span>Browse a curated gallery of meals with full nutritional info.</span></li>
                <li><i class="bi bi-check-circle-fill"></i> <span>Filter by meal type: breakfast, lunch, dinner, or snacks.</span></li>
                <li><i class="bi bi-check-circle-fill"></i> <span>Build your personal meal plan with a single click.</span></li>
              </ul>
              <div class="position-relative mt-4">
                <img src="<?php echo $assetPrefix; ?>img/about-2.jpg" class="img-fluid" alt="">
              </div>
            </div>
          </div>
        </div>
      </div>
    </section><!-- /About Section -->

    <!-- Featured Meals Section -->
    <?php if (!empty($featured)) : ?>
    <section id="featured-meals" class="menu section light-background">
      <div class="container section-title" data-aos="fade-up">
        <h2>Featured Meals</h2>
        <p><span>Discover</span> <span class="description-title">Our Top Picks</span></p>
      </div>
      <div class="container">
        <div class="row g-4" data-aos="fade-up" data-aos-delay="100">
          <?php foreach ($featured as $meal) :
            $imgSrc = htmlspecialchars(resolveImageUrl($meal->image, $assetPrefix), ENT_QUOTES, 'UTF-8');
            $safeName = htmlspecialchars($meal->name, ENT_QUOTES, 'UTF-8');
            $safeDesc = htmlspecialchars($meal->description, ENT_QUOTES, 'UTF-8');
            $safeTypeLabel = htmlspecialchars($meal->mealTypeLabel(), ENT_QUOTES, 'UTF-8');
          ?>
          <div class="col-lg-3 col-md-6">
            <div class="meal-card">
              <div class="meal-card__media">
                <img src="<?php echo $imgSrc; ?>" alt="<?php echo $safeName; ?>" loading="lazy" class="img-fluid">
              </div>
              <div class="meal-card__body">
                <h3 class="meal-card__name"><?php echo $safeName; ?></h3>
                <p class="meal-card__desc"><?php echo $safeDesc; ?></p>
                <p class="meal-card__calories"><strong><?php echo (int) $meal->calories; ?></strong> kcal &mdash; <?php echo $safeTypeLabel; ?></p>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="text-center mt-4" data-aos="fade-up">
          <a href="Meals.php" class="btn btn-danger">View all meals</a>
        </div>
      </div>
    </section><!-- /Featured Meals Section -->
    <?php endif; ?>

    <!-- Stats Section -->
    <section id="stats" class="stats section dark-background">
      <img src="<?php echo $assetPrefix; ?>img/stats-bg.jpg" alt="" data-aos="fade-in">
      <div class="container position-relative" data-aos="fade-up" data-aos-delay="100">
        <div class="row gy-4">
          <div class="col-lg-3 col-md-6">
            <div class="stats-item text-center w-100 h-100">
              <span data-purecounter-start="0" data-purecounter-end="<?php echo count($meals); ?>" data-purecounter-duration="1" class="purecounter"></span>
              <p>Meals Available</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="stats-item text-center w-100 h-100">
              <span data-purecounter-start="0" data-purecounter-end="4" data-purecounter-duration="1" class="purecounter"></span>
              <p>Meal Types</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="stats-item text-center w-100 h-100">
              <span data-purecounter-start="0" data-purecounter-end="100" data-purecounter-duration="1" class="purecounter"></span>
              <p>Happy Planners</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="stats-item text-center w-100 h-100">
              <span data-purecounter-start="0" data-purecounter-end="24" data-purecounter-duration="1" class="purecounter"></span>
              <p>Hours Available</p>
            </div>
          </div>
        </div>
      </div>
    </section><!-- /Stats Section -->

    <!-- Gallery Section -->
    <section id="gallery" class="gallery section">
      <div class="container section-title" data-aos="fade-up">
        <h2>Gallery</h2>
        <p><span>Check Our</span> <span class="description-title">Food Gallery</span></p>
      </div>
      <div class="container-fluid" data-aos="fade-up" data-aos-delay="100">
        <div class="row g-0">
          <?php for ($i = 1; $i <= 8; $i++) : ?>
          <div class="col-lg-3 col-md-4">
            <div class="gallery-item">
              <a href="<?php echo $assetPrefix; ?>img/gallery/gallery-<?php echo $i; ?>.jpg" class="glightbox" data-gallery="images-gallery">
                <img src="<?php echo $assetPrefix; ?>img/gallery/gallery-<?php echo $i; ?>.jpg" alt="" class="img-fluid">
              </a>
            </div>
          </div>
          <?php endfor; ?>
        </div>
      </div>
    </section><!-- /Gallery Section -->

  </main>

  <footer id="footer" class="footer dark-background">
    <div class="container copyright text-center py-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">Smart Meal Planner</strong> <span>All Rights Reserved</span></p>
    </div>
  </footer>

  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  <div id="preloader"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/glightbox@3.3.0/dist/js/glightbox.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script src="main.js"></script>
</body>

</html>
