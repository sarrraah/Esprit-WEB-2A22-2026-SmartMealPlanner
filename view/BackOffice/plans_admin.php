<?php $activePage = 'plans'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Back Office — Plans</title>
  <link href="/3rdV/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/assets/img/favicon.jpg" rel="icon">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&family=Amatic+SC:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="/3rdV/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/assets/css/main.css" rel="stylesheet">
  <link href="/3rdV/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/assets/css/backoffice-meals.css" rel="stylesheet">
</head>
<body class="index-page bo-meals-page">

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="bo-main-content">

  <header class="bo-header sticky-top">
    <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap gap-2 px-4">
      <h1 class="sitename mb-0 fs-5">Plans Management</h1>
      <nav class="d-flex gap-3 align-items-center">
        <a href="../FrontOffice/Plans.php">View front office</a>
      </nav>
    </div>
  </header>

  <main class="container-fluid px-4">
    <div class="bo-meals-layout">

      <section class="bo-panel bo-form" aria-labelledby="bo-form-title">
        <h2 id="bo-form-title">Form</h2>
        <div id="bo-feedback" class="bo-feedback" aria-live="polite"></div>

        <form id="bo-plan-form" method="post" action="#" novalidate>
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="editing_id" id="editing_id" value="">

          <div class="mb-3">
            <label class="form-label" for="name">Name</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Plan name">
          </div>

          <div class="mb-3">
            <label class="form-label" for="meal_type">Plan type</label>
            <select class="form-select" id="meal_type" name="meal_type">
              <option value="">Choose…</option>
              <option value="daily">Daily</option>
              <option value="weekly">Weekly</option>
              <option value="diet">Diet</option>
              <option value="sport">Sport</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label" for="objective">Objective</label>
            <input type="text" class="form-control" id="objective" name="objective" placeholder="e.g. Lose weight">
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label" for="duration">Duration (days)</label>
              <input type="text" class="form-control" id="duration" name="duration" placeholder="7">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label" for="days_completed">Days Completed</label>
              <input type="text" class="form-control" id="days_completed" name="days_completed" placeholder="0">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label" for="total_calories">Daily Calories</label>
              <input type="text" class="form-control" id="total_calories" name="total_calories" placeholder="e.g. 2000">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label" for="meals_planned">Meals Planned</label>
              <input type="text" class="form-control" id="meals_planned" name="meals_planned" placeholder="3">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label" for="meals_completed">Meals Completed</label>
              <input type="text" class="form-control" id="meals_completed" name="meals_completed" placeholder="0">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="4" placeholder="Describe the plan…"></textarea>
          </div>

          <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary" id="bo-btn-submit">Save plan</button>
            <button type="button" class="btn btn-outline-secondary" id="bo-btn-clear-form">Clear form</button>
          </div>
        </form>
      </section>

      <section class="bo-panel" aria-labelledby="bo-list-title">
        <h2 id="bo-list-title">List of plans</h2>

        <div class="bo-toolbar">
          <input type="search" class="form-control bo-search" id="bo-search" placeholder="Search" autocomplete="off">
          <select class="form-select" id="bo-filter-type" aria-label="Filter by plan type">
            <option value="">All types</option>
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="diet">Diet</option>
            <option value="sport">Sport</option>
          </select>
          <button type="button" class="btn btn-outline-primary" id="bo-btn-filter">Filter</button>
        </div>

        <div class="bo-table-wrap">
          <table class="table table-hover bo-table align-middle" id="bo-plans-table">
            <thead>
              <tr>
                <th></th>
                <th>ID</th>
                <th>Name</th>
                <th>Type</th>
                <th>Calories</th>
                <th>Description</th>
              </tr>
            </thead>
            <tbody id="bo-plans-tbody"></tbody>
          </table>
        </div>
        <p class="small text-muted mb-2 d-none" id="bo-load-error">Could not load plans. Make sure PHP is running.</p>

        <div class="bo-actions-row">
          <button type="button" class="btn btn-outline-secondary" id="bo-btn-modify">Modify</button>
          <button type="button" class="btn btn-outline-danger" id="bo-btn-delete">Delete</button>
        </div>
      </section>

    </div>
  </main>

  <script>
    window.BO_PLANS_API = {
      list: 'plans_list.php',
      save: 'plans_save.php',
      del:  'plans_delete.php'
    };
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/3rdV/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/assets/js/backoffice-plans.js"></script>
</div>
</body>
</html>
