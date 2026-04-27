<?php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../model/Plan.php';
require_once __DIR__ . '/../../model/Meal.php';

$assetPrefix = '/3rdV/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/assets/';

$plan = Plan::first();
if (!$plan) { header('Location: Plans.php'); exit; }

// Date param
$dateStr    = $_GET['date'] ?? date('Y-m-d');
$weekOffset = (int) ($_GET['week'] ?? 0);
$ts         = strtotime($dateStr);
$startTs    = $plan->dateDebut ? strtotime($plan->dateDebut) : time();
$dayNum     = (int) floor(($ts - $startTs) / 86400) + 1;

// Daily calorie target
$dailyKcal = 0;
if (preg_match('/Daily target:\s*(\d+)/i', $plan->description, $m)) {
    $dailyKcal = (int) $m[1];
}

// Objective label
$objectifLabels = [
    'lose_weight'     => 'Lose Weight',
    'maintain_weight' => 'Maintain Weight',
    'gain_muscle'     => 'Gain Muscle',
    'eat_healthy'     => 'Eat Healthy',
];
$objectifLabel = $objectifLabels[$plan->objectif] ?? ucfirst($plan->objectif);

// Pick one meal per type — check DB overrides first, then seed by day number
$allMeals = Meal::all();

// Load overrides for this date from plan_detail table
$overrides = [];
try {
    $pdo = Database::pdo();
    $stmt = $pdo->prepare('SELECT meal_type, meal_id FROM plan_detail WHERE plan_id=:pid AND meal_date=:dt');
    $stmt->execute([':pid' => $plan->id, ':dt' => $dateStr]);
    foreach ($stmt->fetchAll() as $row) {
        $overrides[strtolower($row['meal_type'])] = (int) $row['meal_id'];
    }
} catch (Throwable $e) {}

function pickMeal(array $meals, string $type, int $seed, array $overrides): ?Meal {
    // Use override if set
    if (isset($overrides[$type])) {
        foreach ($meals as $m) {
            if ($m->id === $overrides[$type]) return $m;
        }
    }
    $filtered = array_values(array_filter($meals, fn($m) => $m->mealType === $type));
    if (!$filtered) return null;
    return $filtered[$seed % count($filtered)];
}

$slots = [
    ['type' => 'breakfast', 'label' => 'Breakfast', 'icon' => '☀️',  'tag' => 'High Fiber',    'tagColor' => '#e8f5e9', 'tagText' => '#2e7d32'],
    ['type' => 'lunch',     'label' => 'Lunch',     'icon' => '🥗',  'tag' => 'High Protein',  'tagColor' => '#e8f5e9', 'tagText' => '#2e7d32'],
    ['type' => 'dinner',    'label' => 'Dinner',    'icon' => '🍽️', 'tag' => 'Rich in Omega-3','tagColor' => '#e8f5e9', 'tagText' => '#2e7d32'],
    ['type' => 'snack',     'label' => 'Snack',     'icon' => '🍎',  'tag' => 'Low Calorie',   'tagColor' => '#e8f5e9', 'tagText' => '#2e7d32'],
];

$suggested = [];
foreach ($slots as $i => $slot) {
    $meal = pickMeal($allMeals, $slot['type'], $dayNum + $i, $overrides);
    $suggested[] = array_merge($slot, ['meal' => $meal]);
}

$totalPlanned = array_sum(array_map(fn($s) => $s['meal'] ? $s['meal']->calories : 0, $suggested));
$remaining    = max(0, $dailyKcal - $totalPlanned);

$daysElapsed = $plan->daysElapsed();
$progress    = $plan->progressPercent();

// Prev / next day
$prevDate = date('Y-m-d', strtotime('-1 day', $ts));
$nextDate = date('Y-m-d', strtotime('+1 day', $ts));
$planEnd  = $plan->dateFin ? strtotime($plan->dateFin) : strtotime("+{$plan->duree} days", $startTs);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Day <?php echo $dayNum; ?> — Smart Meal Planner</title>
  <link href="<?php echo $assetPrefix; ?>img/favicon.jpg" rel="icon">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?php echo $assetPrefix; ?>css/main.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; font-size: 1rem; }
    .dp-wrap  { max-width: 1100px; margin: 0 auto; padding: 2rem 1rem 4rem; }
    .dp-card  { background: #fff; border-radius: 16px; border: 1px solid #f0f0f0; padding: 1.75rem; }

    /* Sidebar */
    .vp-sidebar h3  { font-size: 1.2rem; font-weight: 700; }
    .vp-meta-label  { font-size: .85rem; color: #999; margin-bottom: .1rem; }
    .vp-meta-val    { font-size: 1rem; font-weight: 600; }
    .vp-badge       { display: inline-block; background: #e8f5e9; color: #2e7d32; font-size: .85rem; font-weight: 600; padding: .2rem .7rem; border-radius: 20px; }
    .progress-track { height: 10px; border-radius: 5px; background: #f0f0f0; overflow: hidden; }
    .progress-fill  { height: 10px; border-radius: 5px; background: #ce1212; }
    .tip-box        { background: #fff8f8; border-radius: 10px; padding: 1rem; font-size: .95rem; }

    /* Day nav */
    .day-nav { display: flex; align-items: center; gap: 1rem; font-size: 1.3rem; font-weight: 700; }
    .day-nav a { color: #555; text-decoration: none; font-size: 1.1rem; }
    .day-nav a:hover { color: #ce1212; }

    /* Stats bar */
    .stats-bar { background: #fff8f8; border-radius: 12px; padding: 1.25rem 1.5rem; display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.75rem; }
    .stat-item { display: flex; align-items: center; gap: .75rem; }
    .stat-icon { font-size: 1.6rem; }
    .stat-label { font-size: .85rem; color: #999; }
    .stat-val   { font-size: 1.15rem; font-weight: 700; color: #212529; }
    .stat-val.green { color: #2e7d32; }

    /* Meal rows */
    .meal-row { display: flex; align-items: center; gap: 1.25rem; padding: 1.25rem 0; border-bottom: 1px solid #f5f5f5; }
    .meal-row:last-child { border-bottom: none; }
    .meal-img { width: 110px; height: 90px; border-radius: 12px; object-fit: cover; flex-shrink: 0; }
    .meal-type-label { font-size: .85rem; color: #888; display: flex; align-items: center; gap: .3rem; margin-bottom: .2rem; }
    .meal-name { font-size: 1.1rem; font-weight: 700; margin-bottom: .25rem; }
    .meal-desc { font-size: .9rem; color: #666; margin-bottom: .4rem; }
    .meal-tag  { display: inline-block; font-size: .8rem; font-weight: 600; padding: .15rem .6rem; border-radius: 20px; }
    .meal-kcal { font-size: 1.4rem; font-weight: 800; color: #ce1212; white-space: nowrap; }
    .meal-kcal span { font-size: .85rem; font-weight: 400; color: #999; display: block; }
    .btn-recipe { border: 1.5px solid #ce1212; color: #ce1212; background: #fff; border-radius: 8px; padding: .4rem 1rem; font-size: .9rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: .4rem; transition: .2s; white-space: nowrap; }
    .btn-recipe:hover { background: #ce1212; color: #fff; }
    .meal-actions { display: flex; flex-direction: column; align-items: flex-end; gap: .75rem; margin-left: auto; flex-shrink: 0; }

    /* Nutrient summary */
    .nutrient-bar { background: #fff8f8; border-radius: 12px; padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; margin-top: 1.5rem; }
    .nutrient-item { flex: 1; min-width: 100px; }
    .nutrient-label { font-size: .85rem; color: #999; margin-bottom: .25rem; }
    .nutrient-val   { font-size: 1rem; font-weight: 700; }
    .nutrient-track { height: 6px; border-radius: 3px; background: #f0f0f0; overflow: hidden; margin-top: .3rem; }
    .nutrient-fill  { height: 6px; border-radius: 3px; background: #ce1212; }

    /* Bottom bar */
    .bottom-bar { background: #fff; border-top: 1px solid #f0f0f0; padding: 1rem 1.5rem; display: flex; align-items: center; justify-content: space-between; border-radius: 0 0 16px 16px; margin-top: 1.5rem; flex-wrap: wrap; gap: 1rem; }
    .bottom-bar p { font-size: .95rem; color: #777; margin: 0; }
  </style>
</head>
<body>

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container position-relative d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center me-auto me-xl-0">
        <img src="<?php echo $assetPrefix; ?>img/logo-smp.jpg" alt="SmartMealPlanner" height="44">
        <h1 class="sitename"><span style="color:#212529;">Smart</span><span style="color:#ce1212;">MealPlanner</span></h1>
      </a>
      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="Meals.php">Meals</a></li>
          <li><a href="Plans.php" class="active">My Plan</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>
    </div>
  </header>

  <main>
    <div class="dp-wrap">
      <div class="dp-card">
        <div class="row g-4">

          <!-- ── Sidebar ──────────────────────────────────────── -->
          <div class="col-lg-3 vp-sidebar">
            <a href="Plans.php" class="text-muted text-decoration-none d-inline-flex align-items-center gap-1 mb-3" style="font-size:.9rem;">
              <i class="bi bi-arrow-left"></i> Back to My Plan
            </a>
            <h3><?php echo htmlspecialchars($plan->nom); ?></h3>
            <span class="vp-badge mb-3 d-inline-block">Active</span>

            <div class="mb-2">
              <p class="vp-meta-label"><i class="bi bi-bullseye text-danger me-1"></i> Objective</p>
              <p class="vp-meta-val"><?php echo htmlspecialchars($objectifLabel); ?></p>
            </div>
            <div class="mb-2">
              <p class="vp-meta-label"><i class="bi bi-calendar3 text-danger me-1"></i> Duration</p>
              <p class="vp-meta-val"><?php echo $plan->duree; ?> Days</p>
            </div>
            <?php if ($dailyKcal > 0) : ?>
            <div class="mb-3">
              <p class="vp-meta-label"><i class="bi bi-fire text-danger me-1"></i> Daily Target</p>
              <p class="vp-meta-val"><?php echo $dailyKcal; ?> kcal</p>
            </div>
            <?php endif; ?>

            <hr>
            <p class="fw-semibold mb-1" style="font-size:.95rem;">Overall Progress</p>
            <p class="text-muted mb-1" style="font-size:.9rem;"><?php echo $daysElapsed; ?> / <?php echo $plan->duree; ?> days</p>
            <div class="progress-track mb-2">
              <div class="progress-fill" style="width:<?php echo $progress; ?>%;"></div>
            </div>
            <p class="text-muted mb-3" style="font-size:.9rem;">
              <?php echo $progress >= 50 ? 'Keep going! You\'re doing great!' : 'You\'re getting started!'; ?>
            </p>

            <div class="tip-box">
              <p class="fw-semibold mb-1" style="color:#ce1212;font-size:.95rem;">🔥 Tip</p>
              <p class="mb-0" style="font-size:.9rem;">Consistency is the key to achieving your goals.</p>
            </div>
          </div>

          <!-- ── Main content ─────────────────────────────────── -->
          <div class="col-lg-9">

            <!-- Day nav -->
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
              <div class="day-nav">
                <?php if ($ts > $startTs) : ?>
                <a href="day_plan.php?date=<?php echo $prevDate; ?>&week=<?php echo $weekOffset; ?>"><i class="bi bi-chevron-left"></i></a>
                <?php else : ?>
                <span style="color:#ddd;font-size:1.1rem;"><i class="bi bi-chevron-left"></i></span>
                <?php endif; ?>

                <span>Day <?php echo $dayNum; ?> — <?php echo date('l, M j, Y', $ts); ?></span>

                <?php if ($ts < $planEnd) : ?>
                <a href="day_plan.php?date=<?php echo $nextDate; ?>&week=<?php echo $weekOffset; ?>"><i class="bi bi-chevron-right"></i></a>
                <?php else : ?>
                <span style="color:#ddd;font-size:1.1rem;"><i class="bi bi-chevron-right"></i></span>
                <?php endif; ?>
              </div>
              <a href="view_plan.php?week=<?php echo $weekOffset; ?>" class="btn btn-outline-danger rounded-pill px-4" style="font-size:.95rem;">
                <i class="bi bi-calendar3 me-1"></i> View Calendar
              </a>
            </div>

            <!-- Stats bar -->
            <div class="stats-bar">
              <div class="stat-item">
                <span class="stat-icon">🎯</span>
                <div>
                  <p class="stat-label">Daily Target</p>
                  <p class="stat-val"><?php echo $dailyKcal ?: '—'; ?> kcal</p>
                </div>
              </div>
              <div class="stat-item">
                <span class="stat-icon">🔥</span>
                <div>
                  <p class="stat-label">Total Planned</p>
                  <p class="stat-val"><?php echo $totalPlanned; ?> kcal</p>
                </div>
              </div>
              <div class="stat-item">
                <span class="stat-icon">🥗</span>
                <div>
                  <p class="stat-label">Remaining</p>
                  <p class="stat-val <?php echo $remaining <= 100 ? 'green' : ''; ?>"><?php echo $remaining; ?> kcal</p>
                </div>
              </div>
            </div>

            <!-- Meal list -->
            <h2 class="fw-bold mb-3" style="font-size:1.25rem;">Suggested Meals for Today</h2>

            <?php foreach ($suggested as $slot) :
              $meal = $slot['meal'];
              if (!$meal) continue;
              $imgSrc = $assetPrefix . ltrim(preg_replace('#^assets/#', '', $meal->image), '/');
            ?>
            <div class="meal-row">
              <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($meal->name); ?>" class="meal-img">
              <div style="flex:1;min-width:0;">
                <p class="meal-type-label"><?php echo $slot['icon']; ?> <?php echo $slot['label']; ?></p>
                <p class="meal-name"><?php echo htmlspecialchars($meal->name); ?></p>
                <p class="meal-desc"><?php echo htmlspecialchars($meal->description); ?></p>
                <span class="meal-tag" style="background:<?php echo $slot['tagColor']; ?>;color:<?php echo $slot['tagText']; ?>;">
                  ✔ <?php echo $slot['tag']; ?>
                </span>
              </div>
              <div class="meal-actions">
                <div class="text-end">
                  <p class="meal-kcal"><?php echo $meal->calories; ?><span>kcal</span></p>
                </div>
                <?php if ($meal->recipeUrl && $meal->recipeUrl !== '#') : ?>
                <a href="<?php echo htmlspecialchars($meal->recipeUrl); ?>" target="_blank" rel="noopener" class="btn-recipe">
                  <i class="bi bi-book"></i> View Recipe
                </a>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>

            <!-- Nutrient summary -->
            <div class="nutrient-bar">
              <span style="font-size:2.5rem;">🥧</span>
              <div style="flex:1;">
                <p class="fw-bold mb-0" style="font-size:1rem;">Nutrient Summary</p>
                <p class="text-muted mb-0" style="font-size:.9rem;">Good balance! You're hitting your daily targets.</p>
              </div>
              <?php
                $protein = (int) round($totalPlanned * 0.30 / 4);
                $carbs   = (int) round($totalPlanned * 0.45 / 4);
                $fats    = (int) round($totalPlanned * 0.25 / 9);
                $pTarget = $dailyKcal ? (int) round($dailyKcal * 0.30 / 4) : 100;
                $cTarget = $dailyKcal ? (int) round($dailyKcal * 0.45 / 4) : 200;
                $fTarget = $dailyKcal ? (int) round($dailyKcal * 0.25 / 9) : 65;
              ?>
              <div class="nutrient-item">
                <p class="nutrient-label">Protein</p>
                <p class="nutrient-val"><?php echo $protein; ?>g / <?php echo $pTarget; ?>g</p>
                <div class="nutrient-track"><div class="nutrient-fill" style="width:<?php echo min(100, (int)(($protein/$pTarget)*100)); ?>%;"></div></div>
              </div>
              <div class="nutrient-item">
                <p class="nutrient-label">Carbs</p>
                <p class="nutrient-val"><?php echo $carbs; ?>g / <?php echo $cTarget; ?>g</p>
                <div class="nutrient-track"><div class="nutrient-fill" style="width:<?php echo min(100, (int)(($carbs/$cTarget)*100)); ?>%;"></div></div>
              </div>
              <div class="nutrient-item">
                <p class="nutrient-label">Fats</p>
                <p class="nutrient-val"><?php echo $fats; ?>g / <?php echo $fTarget; ?>g</p>
                <div class="nutrient-track"><div class="nutrient-fill" style="width:<?php echo min(100, (int)(($fats/$fTarget)*100)); ?>%;"></div></div>
              </div>
            </div>

            <!-- Bottom bar -->
            <div class="bottom-bar">
              <p><i class="bi bi-info-circle text-danger me-1"></i> You can replace any meal with another from our meal gallery.</p>
              <a href="Meals.php" class="btn btn-danger rounded-pill px-4" style="font-size:.95rem;">
                <i class="bi bi-arrow-repeat me-1"></i> Replace Meal
              </a>
            </div>

          </div>
        </div>
      </div>
    </div>
  </main>

  <footer id="footer" class="footer dark-background">
    <div class="container copyright text-center py-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">Smart Meal Planner</strong> <span>All Rights Reserved</span></p>
    </div>
  </footer>

  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  <div id="preloader"></div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?php echo $assetPrefix; ?>js/main.js"></script>
</body>
</html>

