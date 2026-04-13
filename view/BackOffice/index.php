<?php

require_once __DIR__ . '/../../model/Meal.php';

$meals = Meal::all();
$totalMeals = count($meals);

$byType = ['breakfast' => 0, 'lunch' => 0, 'dinner' => 0, 'snack' => 0];
foreach ($meals as $meal) {
    if (isset($byType[$meal->mealType])) {
        $byType[$meal->mealType]++;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Back Office — Smart Meal Planner</title>
  <link href="../assets/img/favicon.jpg" rel="icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&family=Amatic+SC:wght@400;700&display=swap" rel="stylesheet">
  <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/main.css" rel="stylesheet">
  <link href="../assets/css/backoffice-meals.css" rel="stylesheet">
</head>

<body class="index-page bo-meals-page">

  <header class="bo-header sticky-top">
    <div class="container d-flex align-items-center justify-content-between flex-wrap gap-2">
      <h1 class="sitename mb-0 d-flex align-items-center gap-2">
        <img src="../assets/img/logo-smp.jpg" alt="SmartMealPlanner" height="40">
        SmartMealPlanner Admin
      </h1>
      <nav class="d-flex gap-3 align-items-center">
        <a href="../FrontOffice/index.php">View front office</a>
      </nav>
    </div>
  </header>

  <main class="container py-5">

    <div class="row justify-content-center">
      <div class="col-lg-9">

        <div class="text-center mb-5">
          <h2>Back Office Dashboard</h2>
          <p class="text-muted">Manage your Smart Meal Planner content from here.</p>
        </div>

        <!-- Stats row -->
        <div class="row g-3 mb-5">
          <div class="col-6 col-md">
            <div class="card text-center shadow-sm h-100">
              <div class="card-body py-4">
                <p class="display-6 fw-bold mb-1"><?php echo $totalMeals; ?></p>
                <p class="text-muted mb-0 small">Total Meals</p>
              </div>
            </div>
          </div>
          <div class="col-6 col-md">
            <div class="card text-center shadow-sm h-100">
              <div class="card-body py-4">
                <p class="display-6 fw-bold mb-1"><?php echo $byType['breakfast']; ?></p>
                <p class="text-muted mb-0 small">Breakfasts</p>
              </div>
            </div>
          </div>
          <div class="col-6 col-md">
            <div class="card text-center shadow-sm h-100">
              <div class="card-body py-4">
                <p class="display-6 fw-bold mb-1"><?php echo $byType['lunch']; ?></p>
                <p class="text-muted mb-0 small">Lunches</p>
              </div>
            </div>
          </div>
          <div class="col-6 col-md">
            <div class="card text-center shadow-sm h-100">
              <div class="card-body py-4">
                <p class="display-6 fw-bold mb-1"><?php echo $byType['dinner']; ?></p>
                <p class="text-muted mb-0 small">Dinners</p>
              </div>
            </div>
          </div>
          <div class="col-6 col-md">
            <div class="card text-center shadow-sm h-100">
              <div class="card-body py-4">
                <p class="display-6 fw-bold mb-1"><?php echo $byType['snack']; ?></p>
                <p class="text-muted mb-0 small">Snacks</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Action cards -->
        <div class="row g-4">

          <div class="col-md-6">
            <div class="card h-100 shadow-sm">
              <div class="card-body d-flex flex-column align-items-start gap-3 p-4">
                <div class="d-flex align-items-center gap-3">
                  <i class="bi bi-egg-fried fs-2 text-primary"></i>
                  <h3 class="card-title mb-0">Meals</h3>
                </div>
                <p class="card-text text-muted">Add, edit, and delete meals. Upload images and manage nutritional information.</p>
                <a href="meals_admin.php" class="btn btn-primary mt-auto">Manage Meals</a>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card h-100 shadow-sm">
              <div class="card-body d-flex flex-column align-items-start gap-3 p-4">
                <div class="d-flex align-items-center gap-3">
                  <i class="bi bi-eye fs-2 text-secondary"></i>
                  <h3 class="card-title mb-0">Front Office</h3>
                </div>
                <p class="card-text text-muted">Preview the public-facing meal gallery as visitors see it.</p>
                <a href="../FrontOffice/Meals.php" class="btn btn-outline-secondary mt-auto">View Front Office</a>
              </div>
            </div>
          </div>

        </div>

      </div>
    </div>

  </main>

  <footer class="footer dark-background mt-auto">
    <div class="container copyright text-center py-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">Smart Meal Planner</strong> <span>All Rights Reserved</span></p>
    </div>
  </footer>

  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
