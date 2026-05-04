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

// Load consumed meals from session
session_start();
$sessionKey = 'consumed_' . $plan->id . '_' . $dateStr;
$consumed   = $_SESSION[$sessionKey] ?? [];

// Calculate consumed nutrients (only from consumed meals)
$consumedCalories = 0;
$consumedProtein  = 0;
$consumedCarbs    = 0;
$consumedFats     = 0;
$skippedMeals     = []; // meals in plan but not consumed

foreach ($suggested as $slot) {
    $meal = $slot['meal'];
    if (!$meal) continue;
    if (isset($consumed[$slot['type']])) {
        $consumedCalories += $meal->calories;
        $consumedProtein  += (int) round($meal->calories * 0.30 / 4);
        $consumedCarbs    += (int) round($meal->calories * 0.45 / 4);
        $consumedFats     += (int) round($meal->calories * 0.25 / 9);
    } else {
        $skippedMeals[] = ['type' => $slot['type'], 'meal' => $meal];
    }
}

// Build chatbot context
$chatContext = [
    'plan_name'        => $plan->nom,
    'objective'        => $plan->objectif,
    'daily_target'     => $dailyKcal,
    'total_planned'    => $totalPlanned,
    'consumed_cal'     => $consumedCalories,
    'remaining_cal'    => max(0, $dailyKcal - $consumedCalories),
    'consumed_protein' => $consumedProtein,
    'consumed_carbs'   => $consumedCarbs,
    'consumed_fats'    => $consumedFats,
    'skipped_meals'    => array_map(fn($s) => $s['type'] . ': ' . $s['meal']->name, $skippedMeals),
    'date'             => $dateStr,
    'day_num'          => $dayNum,
];

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

    /* Chatbot */
    .chat-msg { display:flex; }
    .chat-bubble { max-width:85%; padding:.6rem .9rem; border-radius:12px; font-size:.88rem; line-height:1.5; }
    .bot-bubble { background:#f8f9fa; color:#212529; border-radius:4px 12px 12px 12px; }
    .user-bubble { background:#ce1212; color:#fff; border-radius:12px 4px 12px 12px; margin-left:auto; }
    .chat-msg.user { justify-content:flex-end; }
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
            <!-- Stats bar — one doughnut with all 3 values -->
            <div class="stats-bar" style="display:flex;align-items:center;gap:2rem;flex-wrap:wrap;">
              <div style="position:relative;width:110px;height:110px;flex-shrink:0;">
                <canvas id="statsChart" width="110" height="110" style="width:110px;height:110px;"></canvas>
                <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;line-height:1.2;">
                  <span style="font-size:.7rem;color:#999;">Consumed</span><br>
                  <span id="stat-center-val" style="font-size:.95rem;font-weight:700;color:#ce1212;"><?php echo $consumedCalories; ?></span>
                </div>
              </div>
              <div style="display:flex;flex-direction:column;gap:.75rem;">
                <div style="display:flex;align-items:center;gap:.5rem;">
                  <span style="width:12px;height:12px;background:#ce1212;border-radius:50%;flex-shrink:0;"></span>
                  <span class="stat-label" style="margin:0;">Daily Target</span>
                  <span class="stat-val" style="margin:0;margin-left:.5rem;"><?php echo $dailyKcal ?: '—'; ?> kcal</span>
                </div>
                <div style="display:flex;align-items:center;gap:.5rem;">
                  <span style="width:12px;height:12px;background:#f59e0b;border-radius:50%;flex-shrink:0;"></span>
                  <span class="stat-label" style="margin:0;">Consumed</span>
                  <span class="stat-val" id="stat-consumed" style="margin:0;margin-left:.5rem;"><?php echo $consumedCalories; ?> kcal</span>
                </div>
                <div style="display:flex;align-items:center;gap:.5rem;">
                  <span style="width:12px;height:12px;background:#10b981;border-radius:50%;flex-shrink:0;"></span>
                  <span class="stat-label" style="margin:0;">Remaining</span>
                  <span class="stat-val green" id="stat-remaining" style="margin:0;margin-left:.5rem;"><?php echo max(0, $dailyKcal - $consumedCalories); ?> kcal</span>
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
                <a href="Meals.php?filter=<?php echo urlencode($slot['type']); ?>&date=<?php echo urlencode($dateStr); ?>"
                   class="btn-recipe" style="border-color:#ce1212;color:#ce1212;">
                  <i class="bi bi-arrow-repeat"></i> Replace
                </a>
                <button class="btn-consumed <?php echo isset($consumed[$slot['type']]) ? 'consumed' : ''; ?>"
                        onclick="toggleConsumed('<?php echo $slot['type']; ?>', '<?php echo $dateStr; ?>', this)"
                        style="border:none;border-radius:8px;padding:.4rem 1rem;font-size:.9rem;font-weight:600;cursor:pointer;transition:.2s;background:<?php echo isset($consumed[$slot['type']]) ? '#2e7d32' : '#e8f5e9'; ?>;color:<?php echo isset($consumed[$slot['type']]) ? '#fff' : '#2e7d32'; ?>;">
                  ✔ <?php echo isset($consumed[$slot['type']]) ? 'Consumed' : 'Mark Consumed'; ?>
                </button>
              </div>
            </div>
            <?php endforeach; ?>

            <!-- AI Chatbot -->
            <div style="background:#fff;border-radius:16px;border:1px solid #f0f0f0;margin-top:1.5rem;overflow:hidden;">
              <div style="background:linear-gradient(135deg,#ce1212,#ff6b6b);padding:1rem 1.25rem;display:flex;align-items:center;gap:.75rem;">
                <span style="font-size:1.5rem;">🤖</span>
                <div>
                  <p style="color:#fff;font-weight:700;margin:0;font-size:1rem;">Smart Meal Assistant</p>
                  <p style="color:rgba(255,255,255,.8);margin:0;font-size:.8rem;">Powered by your meal data</p>
                </div>
              </div>
              <div id="chat-messages" style="height:220px;overflow-y:auto;padding:1rem;display:flex;flex-direction:column;gap:.75rem;">
                <div class="chat-msg bot">
                  <div class="chat-bubble bot-bubble">
                    👋 Hi! I'm your Smart Meal Assistant. I can help you track your nutrition, suggest meals, and guide you toward your <strong><?php echo htmlspecialchars($objectifLabel); ?></strong> goal. Ask me anything!
                  </div>
                </div>
              </div>
              <div style="padding:.75rem 1rem;border-top:1px solid #f0f0f0;display:flex;gap:.5rem;">
                <input type="text" id="chat-input" placeholder="Ask about your meals, nutrition, goals..." 
                  style="flex:1;border:1.5px solid #eee;border-radius:20px;padding:.5rem 1rem;font-size:.9rem;outline:none;"
                  onkeydown="if(event.key==='Enter') sendChat()">
                <button onclick="sendChat()" style="background:#ce1212;color:#fff;border:none;border-radius:20px;padding:.5rem 1.25rem;font-weight:600;cursor:pointer;">Send</button>
              </div>
              <!-- Quick prompts -->
              <div style="padding:.5rem 1rem .75rem;display:flex;flex-wrap:wrap;gap:.4rem;">
                <?php
                  $quickPrompts = [
                    'How many calories have I consumed?',
                    'What meals did I skip?',
                    'Suggest a replacement meal',
                    'How am I doing today?',
                    'Explain my meal plan',
                  ];
                  foreach ($quickPrompts as $qp):
                ?>
                <button onclick="sendQuick('<?php echo htmlspecialchars($qp, ENT_QUOTES); ?>')"
                  style="background:#fff8f8;color:#ce1212;border:1px solid #fde8e8;border-radius:20px;padding:.25rem .75rem;font-size:.8rem;cursor:pointer;">
                  <?php echo htmlspecialchars($qp); ?>
                </button>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Nutrient summary — consumed only -->
            <div class="nutrient-bar">
              <div style="flex:1;min-width:140px;">
                <p class="fw-bold mb-0" style="font-size:1rem;">Nutrient Summary</p>
                <p class="text-muted mb-0" style="font-size:.9rem;">Based on <strong>consumed</strong> meals only.</p>
              </div>
              <?php
                $pTarget = $dailyKcal ? (int) round($dailyKcal * 0.30 / 4) : 100;
                $cTarget = $dailyKcal ? (int) round($dailyKcal * 0.45 / 4) : 200;
                $fTarget = $dailyKcal ? (int) round($dailyKcal * 0.25 / 9) : 65;
              ?>
              <div class="nutrient-item text-center">
                <canvas id="proteinChart" width="70" height="70" style="width:70px;height:70px;"></canvas>
                <p class="nutrient-label mt-1">Protein</p>
                <p class="nutrient-val" id="protein-val"><?php echo $consumedProtein; ?>g / <?php echo $pTarget; ?>g</p>
              </div>
              <div class="nutrient-item text-center">
                <canvas id="carbsChart" width="70" height="70" style="width:70px;height:70px;"></canvas>
                <p class="nutrient-label mt-1">Carbs</p>
                <p class="nutrient-val" id="carbs-val"><?php echo $consumedCarbs; ?>g / <?php echo $cTarget; ?>g</p>
              </div>
              <div class="nutrient-item text-center">
                <canvas id="fatsChart" width="70" height="70" style="width:70px;height:70px;"></canvas>
                <p class="nutrient-label mt-1">Fats</p>
                <p class="nutrient-val" id="fats-val"><?php echo $consumedFats; ?>g / <?php echo $fTarget; ?>g</p>
              </div>
            </div>

            <!-- Bottom bar removed — Replace button is now per meal row -->

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
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script src="<?php echo $assetPrefix; ?>js/main.js"></script>
  <script>
  // Stats doughnut — 3 segments: target, consumed, remaining
  var statsChartObj = new Chart(document.getElementById('statsChart'), {
    type: 'doughnut',
    data: {
      labels: ['Daily Target', 'Consumed', 'Remaining'],
      datasets: [{
        data: [
          <?php echo $dailyKcal; ?>,
          <?php echo $consumedCalories; ?>,
          <?php echo max(0, $dailyKcal - $consumedCalories); ?>
        ],
        backgroundColor: ['#ce1212', '#f59e0b', '#10b981'],
        borderWidth: 2,
        borderColor: '#fff'
      }]
    },
    options: {
      cutout: '65%',
      responsive: false,
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.parsed + ' kcal' } }
      }
    }
  });

  // Nutrient doughnut charts
  function nutrientChart(id, val, target, color) {
    return new Chart(document.getElementById(id), {
      type: 'doughnut',
      data: {
        datasets: [{ 
          data: [Math.min(val, target), Math.max(0, target - val)],
          backgroundColor: [color, '#f0f0f0'],
          borderWidth: 0
        }]
      },
      options: { cutout: '68%', responsive: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' ' + ctx.parsed + 'g' } } } }
    });
  }
  var proteinChartObj = nutrientChart('proteinChart', <?php echo $consumedProtein; ?>, <?php echo $pTarget; ?>, '#3b82f6');
  var carbsChartObj   = nutrientChart('carbsChart',   <?php echo $consumedCarbs; ?>,   <?php echo $cTarget; ?>, '#f59e0b');
  var fatsChartObj    = nutrientChart('fatsChart',    <?php echo $consumedFats; ?>,    <?php echo $fTarget; ?>, '#10b981');

  // ── Chatbot engine ──────────────────────────────────────────
  var CTX = <?php echo json_encode($chatContext); ?>;
  var ALL_MEALS = <?php echo json_encode(array_map(fn($m) => ['id'=>$m->id,'name'=>$m->name,'type'=>$m->mealType,'calories'=>$m->calories], $allMeals)); ?>;

  // Meal calories per type for live chart updates
  var MEAL_CALORIES = <?php
    $mealCalByType = [];
    foreach ($suggested as $slot) {
        if ($slot['meal']) $mealCalByType[$slot['type']] = $slot['meal']->calories;
    }
    echo json_encode($mealCalByType);
  ?>;

  var DAILY_TARGET = <?php echo $dailyKcal ?: 2000; ?>;

  // Targets
  var P_TARGET = Math.round(DAILY_TARGET * 0.30 / 4);
  var C_TARGET = Math.round(DAILY_TARGET * 0.45 / 4);
  var F_TARGET = Math.round(DAILY_TARGET * 0.25 / 9);

  // Track consumed state in JS (mirrors session)
  var consumedState = <?php echo json_encode(array_keys($consumed)); ?>;

  function getConsumedNutrients() {
    var cal = 0, prot = 0, carbs = 0, fats = 0;
    consumedState.forEach(function(type) {
      var c = MEAL_CALORIES[type] || 0;
      cal   += c;
      prot  += Math.round(c * 0.30 / 4);
      carbs += Math.round(c * 0.45 / 4);
      fats  += Math.round(c * 0.25 / 9);
    });
    return { cal: cal, prot: prot, carbs: carbs, fats: fats };
  }

  function updateCharts() {
    var n = getConsumedNutrients();

    // Update nutrient chart data
    proteinChartObj.data.datasets[0].data = [Math.min(n.prot, P_TARGET), Math.max(0, P_TARGET - n.prot)];
    carbsChartObj.data.datasets[0].data   = [Math.min(n.carbs, C_TARGET), Math.max(0, C_TARGET - n.carbs)];
    fatsChartObj.data.datasets[0].data    = [Math.min(n.fats, F_TARGET), Math.max(0, F_TARGET - n.fats)];
    proteinChartObj.update();
    carbsChartObj.update();
    fatsChartObj.update();

    // Update labels
    document.getElementById('protein-val').textContent = n.prot + 'g / ' + P_TARGET + 'g';
    document.getElementById('carbs-val').textContent   = n.carbs + 'g / ' + C_TARGET + 'g';
    document.getElementById('fats-val').textContent    = n.fats + 'g / ' + F_TARGET + 'g';

    // Update stats chart
    var rem = Math.max(0, DAILY_TARGET - n.cal);
    statsChartObj.data.datasets[0].data = [DAILY_TARGET, n.cal, rem];
    statsChartObj.update();

    // Update stat labels
    document.getElementById('stat-consumed').textContent  = n.cal + ' kcal';
    document.getElementById('stat-remaining').textContent = rem + ' kcal';
    document.getElementById('stat-center-val').textContent = n.cal;

    // Update CTX for chatbot
    CTX.consumed_cal     = n.cal;
    CTX.consumed_protein = n.prot;
    CTX.consumed_carbs   = n.carbs;
    CTX.consumed_fats    = n.fats;
    CTX.remaining_cal    = rem;
  }

  var OBJECTIVE_TIPS = {
    'lose_weight':     'Focus on low-calorie, high-fiber meals. Aim for a 300-500 kcal daily deficit.',
    'maintain_weight': 'Balance your intake with your daily target. Consistency is key.',
    'gain_muscle':     'Prioritize high-protein meals. Aim for 1.6-2.2g of protein per kg of body weight.',
    'eat_healthy':     'Choose whole foods, plenty of vegetables, and balanced macros.'
  };

  function getBotReply(msg) {
    var m = msg.toLowerCase();
    var consumed = CTX.consumed_cal;
    var target   = CTX.daily_target;
    var remaining = CTX.remaining_cal;
    var skipped  = CTX.skipped_meals;
    var obj      = CTX.objective;

    // Calories consumed
    if (m.includes('calori') || m.includes('how much') || m.includes('consumed')) {
      var pct = target > 0 ? Math.round((consumed / target) * 100) : 0;
      return '🔥 You\'ve consumed <strong>' + consumed + ' kcal</strong> today (' + pct + '% of your ' + target + ' kcal target). ' +
        (remaining > 0 ? 'You have <strong>' + remaining + ' kcal</strong> remaining.' : '✅ You\'ve hit your daily target!');
    }

    // Protein
    if (m.includes('protein')) {
      return '💪 You\'ve consumed <strong>' + CTX.consumed_protein + 'g of protein</strong> out of your ' + Math.round(target * 0.30 / 4) + 'g target. ' +
        (obj === 'gain_muscle' ? 'Keep it up — protein is essential for muscle growth!' : 'Good progress!');
    }

    // Carbs
    if (m.includes('carb')) {
      return '🌾 You\'ve consumed <strong>' + CTX.consumed_carbs + 'g of carbs</strong> out of your ' + Math.round(target * 0.45 / 4) + 'g target.';
    }

    // Fats
    if (m.includes('fat')) {
      return '🥑 You\'ve consumed <strong>' + CTX.consumed_fats + 'g of fats</strong> out of your ' + Math.round(target * 0.25 / 9) + 'g target.';
    }

    // Skipped meals
    if (m.includes('skip') || m.includes('miss') || m.includes('avoid')) {
      if (skipped.length === 0) return '✅ Great job! You\'ve consumed all your planned meals today!';
      var skippedList = skipped.join(', ');
      var suggestions = ALL_MEALS.filter(function(meal) {
        return skipped.some(function(s) { return s.startsWith(meal.type); });
      }).slice(0, 3).map(function(m) { return '• ' + m.name + ' (' + m.calories + ' kcal)'; }).join('<br>');
      return '⚠️ You\'ve skipped: <strong>' + skippedList + '</strong>.<br><br>Here are some alternatives you might enjoy:<br>' + suggestions;
    }

    // Suggest meal
    if (m.includes('suggest') || m.includes('recommend') || m.includes('alternative') || m.includes('replacement')) {
      var typeMatch = ['breakfast','lunch','dinner','snack'].find(t => m.includes(t));
      var pool = ALL_MEALS.filter(function(meal) { return !typeMatch || meal.type === typeMatch; });
      var picks = pool.sort(() => Math.random() - 0.5).slice(0, 3);
      return '🍽️ Here are some meal suggestions' + (typeMatch ? ' for ' + typeMatch : '') + ':<br>' +
        picks.map(function(p) { return '• <strong>' + p.name + '</strong> (' + p.calories + ' kcal)'; }).join('<br>');
    }

    // How am I doing
    if (m.includes('doing') || m.includes('progress') || m.includes('status')) {
      var pct = target > 0 ? Math.round((consumed / target) * 100) : 0;
      var msg2 = pct >= 100 ? '🎉 Excellent! You\'ve hit your calorie target!' :
                 pct >= 75  ? '👍 Great progress! Almost there.' :
                 pct >= 50  ? '💪 Halfway through your daily goal.' :
                              '🌱 Just getting started. Keep going!';
      return msg2 + '<br>Consumed: <strong>' + consumed + ' kcal</strong> / ' + target + ' kcal (' + pct + '%)' +
        (skipped.length > 0 ? '<br>⚠️ ' + skipped.length + ' meal(s) not yet consumed.' : '<br>✅ All meals consumed!');
    }

    // Explain meal plan
    if (m.includes('explain') || m.includes('how') || m.includes('work') || m.includes('routine') || m.includes('plan')) {
      return '📋 <strong>How your meal plan works:</strong><br>' +
        '1. Your plan <strong>' + CTX.plan_name + '</strong> runs for ' + <?php echo $plan->duree; ?> + ' days.<br>' +
        '2. Each day has 4 meal slots: Breakfast, Lunch, Dinner & Snack.<br>' +
        '3. Your daily calorie target is <strong>' + target + ' kcal</strong>.<br>' +
        '4. Click <strong>Replace</strong> to swap any meal with one from the gallery.<br>' +
        '5. Click <strong>Mark Consumed</strong> when you\'ve eaten a meal.<br>' +
        '6. The charts update based on what you\'ve actually consumed.';
    }

    // Goal tips
    if (m.includes('goal') || m.includes('tip') || m.includes('advice') || m.includes('achieve')) {
      var tip = OBJECTIVE_TIPS[obj] || 'Stay consistent with your meal plan for best results.';
      return '🎯 <strong>Goal: ' + obj.replace('_',' ') + '</strong><br>' + tip;
    }

    // Nutrients overview
    if (m.includes('nutrient') || m.includes('macro') || m.includes('breakdown')) {
      return '📊 <strong>Today\'s consumed nutrients:</strong><br>' +
        '🔴 Calories: <strong>' + consumed + ' kcal</strong><br>' +
        '💪 Protein: <strong>' + CTX.consumed_protein + 'g</strong><br>' +
        '🌾 Carbs: <strong>' + CTX.consumed_carbs + 'g</strong><br>' +
        '🥑 Fats: <strong>' + CTX.consumed_fats + 'g</strong>';
    }

    // Default
    return '🤔 I can help you with:<br>• Calories & nutrients consumed<br>• Skipped meals & suggestions<br>• Your goal tips<br>• How your meal plan works<br><br>Try asking: <em>"How many calories have I consumed?"</em> or <em>"Suggest a meal"</em>';
  }

  function addMessage(text, isUser) {
    var box = document.getElementById('chat-messages');
    var div = document.createElement('div');
    div.className = 'chat-msg ' + (isUser ? 'user' : '');
    var bubble = document.createElement('div');
    bubble.className = 'chat-bubble ' + (isUser ? 'user-bubble' : 'bot-bubble');
    bubble.innerHTML = text;
    div.appendChild(bubble);
    box.appendChild(div);
    box.scrollTop = box.scrollHeight;
  }

  function sendChat() {
    var input = document.getElementById('chat-input');
    var msg = input.value.trim();
    if (!msg) return;
    addMessage(msg, true);
    input.value = '';
    setTimeout(function() { addMessage(getBotReply(msg), false); }, 300);
  }

  function sendQuick(msg) {
    addMessage(msg, true);
    setTimeout(function() { addMessage(getBotReply(msg), false); }, 300);
  }
  // ── End chatbot ──────────────────────────────────────────────

  function toggleConsumed(mealType, date, btn) {
    var fd = new FormData();
    fd.append('meal_type', mealType);
    fd.append('date', date);
    fetch('toggle_consumed.php', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(d => {
        if (d.consumed) {
          btn.textContent = '✔ Consumed';
          btn.style.background = '#2e7d32';
          btn.style.color = '#fff';
          if (!consumedState.includes(mealType)) consumedState.push(mealType);
        } else {
          btn.textContent = '✔ Mark Consumed';
          btn.style.background = '#e8f5e9';
          btn.style.color = '#2e7d32';
          consumedState = consumedState.filter(t => t !== mealType);
        }
        updateCharts();
      });
  }
  </script>
  <script src="meal_notifications.js"></script>
</body>
</html>

