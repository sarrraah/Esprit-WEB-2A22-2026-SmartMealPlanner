<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Smart Meal Planner</title>
  <meta name="description" content="Smart Meal Planner — browse meals or manage the back office.">

  <link href="/3rdV/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/assets/img/favicon.jpg" rel="icon">
  <link href="/3rdV/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Inter:wght@400;500;600;700&family=Amatic+SC:wght@400;700&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="/3rdV/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/assets/css/main.css" rel="stylesheet">

  <style>
    body { min-height: 100vh; display: flex; flex-direction: column; }
    main { flex: 1; display: flex; align-items: center; }
    .entry-card {
      border: none;
      border-radius: 1rem;
      box-shadow: 0 4px 24px rgba(0,0,0,.08);
      transition: transform .2s, box-shadow .2s;
    }
    .entry-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 32px rgba(0,0,0,.14);
    }
    .entry-card .icon { font-size: 3rem; }
  </style>
</head>

<body>

  <header class="bo-header sticky-top" style="background:var(--color-primary,#ce1212);padding:.75rem 0;">
    <div class="container d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center text-decoration-none">
        <img src="assets/img/logo-smp.jpg" alt="SmartMealPlanner logo" style="height:44px;width:auto;margin-right:10px;">
        <h1 class="sitename mb-0 text-white" style="font-size:1.6rem;">SmartMealPlanner</h1>
      </a>
    </div>
  </header>

  <main>
    <div class="container py-5">

      <div class="text-center mb-5">
        <h2 class="display-5 fw-bold">Welcome to Smart Meal Planner</h2>
        <p class="text-muted fs-5">Where would you like to go?</p>
      </div>

      <div class="row g-4 justify-content-center">

        <div class="col-md-5">
          <div class="card entry-card h-100 p-4">
            <div class="card-body d-flex flex-column align-items-start gap-3">
              <i class="bi bi-grid icon text-primary"></i>
              <h3 class="card-title">Front Office</h3>
              <p class="card-text text-muted">Browse the meal gallery, view details, and build your meal plan.</p>
              <div class="d-flex gap-2 mt-auto flex-wrap">
                <a href="FrontOffice/index.php" class="btn btn-primary">Open (PHP)</a>
                <a href="FrontOffice/index.html" class="btn btn-outline-primary">Open (HTML)</a>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-5">
          <div class="card entry-card h-100 p-4">
            <div class="card-body d-flex flex-column align-items-start gap-3">
              <i class="bi bi-shield-lock icon text-danger"></i>
              <h3 class="card-title">Back Office</h3>
              <p class="card-text text-muted">Add, edit, and delete meals. Manage images and nutritional data.</p>
              <div class="d-flex gap-2 mt-auto flex-wrap">
                <a href="BackOffice/index.php" class="btn btn-danger">Open (PHP)</a>
                <a href="BackOffice/index.html" class="btn btn-outline-danger">Open (HTML)</a>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </main>

  <footer class="footer dark-background">
    <div class="container copyright text-center py-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">Smart Meal Planner</strong> <span>All Rights Reserved</span></p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
