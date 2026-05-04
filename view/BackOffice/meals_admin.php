<?php

/**
 * Back office admin shell — served directly by PHP so that
 * the relative API fetches (meals_list.php, meals_save.php, meals_delete.php)
 * always resolve correctly.
 */

require_once __DIR__ . '/../../model/Meal.php';

$allMeals = Meal::all();
$stats = ['breakfast' => 0, 'lunch' => 0, 'dinner' => 0, 'snack' => 0];
$totalCal = ['breakfast' => 0, 'lunch' => 0, 'dinner' => 0, 'snack' => 0];
foreach ($allMeals as $m) {
    if (isset($stats[$m->mealType])) {
        $stats[$m->mealType]++;
        $totalCal[$m->mealType] += $m->calories;
    }
}
$total = array_sum($stats);
$avgCal = [];
foreach ($stats as $type => $count) {
    $avgCal[$type] = $count > 0 ? (int) round($totalCal[$type] / $count) : 0;
}

?><!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Back office — Meals</title>
  <link href="/3rdV/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/assets/img/favicon.jpg" rel="icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&family=Amatic+SC:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/3rdV/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/assets/css/main.css" rel="stylesheet">
  <link href="/3rdV/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/assets/css/backoffice-meals.css" rel="stylesheet">
</head>

<body class="index-page bo-meals-page">

<div class="bo-layout">
<?php $activePage = 'meals'; include __DIR__ . '/sidebar.php'; ?>

<div class="bo-main-content">

  <header class="bo-header sticky-top">
    <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap gap-2 px-4">
      <h1 class="sitename mb-0 fs-5">Meals Management</h1>
      <nav class="d-flex gap-3 align-items-center">
        <a href="../FrontOffice/Meals.php">View front office</a>
      </nav>
    </div>
  </header>

  <main class="container-fluid px-4">
    <div class="bo-meals-layout">

      <section class="bo-panel bo-form" aria-labelledby="bo-form-title">
        <h2 id="bo-form-title">Form</h2>

        <div id="bo-feedback" class="bo-feedback" aria-live="polite"></div>

        <form id="bo-meal-form" method="post" enctype="multipart/form-data" action="#" novalidate>
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="editing_id" id="editing_id" value="">
          <input type="hidden" name="existing_image" id="existing_image" value="">

          <div class="mb-3">
            <label class="form-label" for="name">Name</label>
            <input type="text" class="form-control" id="name" name="name" maxlength="2000" placeholder="Meal name">
            <div class="form-text">Maximum 60 words.</div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="meal_type">Meal type</label>
            <select class="form-select" id="meal_type" name="meal_type">
              <option value="">Choose…</option>
              <option value="breakfast">Breakfast</option>
              <option value="lunch">Lunch</option>
              <option value="dinner">Dinner</option>
              <option value="snack">Snacks</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label" for="calories">Calories</label>
            <input type="text" class="form-control" id="calories" name="calories" placeholder="e.g. 450">
            <div class="form-text">Whole numbers only, 0–3000.</div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="5" placeholder="Describe the meal…"></textarea>
            <div class="form-text">Maximum 1200 words.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Image</label>
            <div class="bo-insert-row">
              <input type="file" class="d-none" id="meal_image" name="meal_image" accept="image/jpeg,image/png,image/webp,image/gif">
              <button type="button" class="btn btn-outline-secondary" id="bo-btn-insert-image">Insert</button>
              <span class="text-muted small">Choose a file from your computer (JPEG, PNG, WebP, or GIF, max 5 MB).</span>
            </div>
            <img src="" alt="" class="bo-image-preview mt-2" id="bo-image-preview" width="120" height="120">
          </div>

          <div class="mb-3">
            <label class="form-label" for="recipe_url">Recipe URL <span class="text-muted fw-normal">(optional)</span></label>
            <input type="text" class="form-control" id="recipe_url" name="recipe_url" placeholder="https://…">
          </div>

          <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary" id="bo-btn-submit">Save meal</button>
            <button type="button" class="btn btn-outline-secondary" id="bo-btn-clear-form">Clear form</button>
          </div>
        </form>
      </section>

      <section class="bo-panel" aria-labelledby="bo-list-title">
        <h2 id="bo-list-title">List of meals</h2>

        <div class="bo-toolbar">
          <input type="search" class="form-control bo-search" id="bo-search" placeholder="Search" autocomplete="off">
          <select class="form-select" id="bo-filter-type" aria-label="Filter by meal type">
            <option value="">All types</option>
            <option value="breakfast">Breakfast</option>
            <option value="lunch">Lunch</option>
            <option value="dinner">Dinner</option>
            <option value="snack">Snacks</option>
          </select>
          <button type="button" class="btn btn-outline-primary" id="bo-btn-filter">Filter</button>
        </div>

        <div class="bo-table-wrap">
          <table class="table table-hover bo-table align-middle" id="bo-meals-table">
            <thead>
              <tr>
                <th></th>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Type</th>
                <th>Calories</th>
                <th>Description</th>
              </tr>
            </thead>
            <tbody id="bo-meals-tbody">
            </tbody>
          </table>
        </div>
        <p class="small text-muted mb-2 d-none" id="bo-load-error">Could not load meals. Make sure PHP is running.</p>

        <div class="bo-actions-row">
          <button type="button" class="btn btn-outline-secondary" id="bo-btn-modify">Modify</button>
          <button type="button" class="btn btn-outline-danger" id="bo-btn-delete">Delete</button>
        </div>

        <p class="bo-muted mb-0">UI: <code>meals_admin.php</code> · Styles: <code>backoffice-meals.css</code> · Logic: <code>backoffice-meals.js</code> · Data: <code>meals_list.php</code>, <code>meals_save.php</code>, <code>meals_delete.php</code></p>
      </section>

    </div>

    <!-- Charts row -->
    <div class="row g-4 mt-2">
      <div class="col-md-4">
        <div class="bo-panel text-center">
          <h2 style="font-size:1rem;">Meal Type Distribution</h2>
          <canvas id="typeChart" height="220"></canvas>
          <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
            <?php
              $colors = ['breakfast'=>'#f59e0b','lunch'=>'#10b981','dinner'=>'#ce1212','snack'=>'#6366f1'];
              $icons  = ['breakfast'=>'☀️','lunch'=>'🥗','dinner'=>'🍽️','snack'=>'🍎'];
              foreach ($stats as $type => $count):
                $pct = $total > 0 ? round(($count/$total)*100) : 0;
            ?>
            <span style="font-size:.8rem;display:flex;align-items:center;gap:.3rem;">
              <span style="width:10px;height:10px;background:<?php echo $colors[$type]; ?>;border-radius:50%;display:inline-block;"></span>
              <?php echo $icons[$type]; ?> <?php echo ucfirst($type); ?> (<?php echo $count; ?>)
            </span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="bo-panel">
          <h2 style="font-size:1rem;">Avg Calories per Type</h2>
          <canvas id="calChart" height="220"></canvas>
        </div>
      </div>
      <div class="col-md-4">
        <div class="bo-panel">
          <h2 style="font-size:1rem;">Meals Count</h2>
          <canvas id="countChart" height="220"></canvas>
        </div>
      </div>
    </div>

  </main>

  <script>
    window.BO_MEALS_API = {
      list: 'meals_list.php',
      save: 'meals_save.php',
      del:  'meals_delete.php'
    };
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script src="backoffice-meals.js"></script>
  <script>
    var TYPES  = ['Breakfast','Lunch','Dinner','Snack'];
    var COLORS = ['#f59e0b','#10b981','#ce1212','#6366f1'];
    var COUNTS = [<?php echo implode(',', array_values($stats)); ?>];
    var AVGCAL = [<?php echo implode(',', array_values($avgCal)); ?>];

    // Doughnut — type distribution
    new Chart(document.getElementById('typeChart'), {
      type: 'doughnut',
      data: {
        labels: TYPES,
        datasets: [{ data: COUNTS, backgroundColor: COLORS, borderWidth: 3, borderColor: '#fff', hoverOffset: 6 }]
      },
      options: {
        cutout: '60%',
        plugins: {
          legend: { display: false },
          tooltip: { callbacks: { label: ctx => ' ' + ctx.parsed + ' meals (' + Math.round(ctx.parsed / COUNTS.reduce((a,b)=>a+b,0) * 100) + '%)' } }
        }
      }
    });

    // Bar — avg calories
    new Chart(document.getElementById('calChart'), {
      type: 'bar',
      data: {
        labels: TYPES,
        datasets: [{ label: 'Avg kcal', data: AVGCAL, backgroundColor: COLORS, borderRadius: 8, borderSkipped: false }]
      },
      options: {
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, grid: { color: '#f0f0f0' }, ticks: { font: { size: 11 } } },
          x: { grid: { display: false }, ticks: { font: { size: 11 } } }
        }
      }
    });

    // Horizontal bar — count
    new Chart(document.getElementById('countChart'), {
      type: 'bar',
      data: {
        labels: TYPES,
        datasets: [{ label: 'Meals', data: COUNTS, backgroundColor: COLORS, borderRadius: 8, borderSkipped: false }]
      },
      options: {
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: {
          x: { beginAtZero: true, grid: { color: '#f0f0f0' }, ticks: { font: { size: 11 } } },
          y: { grid: { display: false }, ticks: { font: { size: 11 } } }
        }
      }
    });
  </script>
</div><!-- /bo-main-content -->
</div><!-- /bo-layout -->
</body>
</html>

