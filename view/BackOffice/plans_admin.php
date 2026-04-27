<?php
$activePage = 'plans';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../model/Plan.php';
require_once __DIR__ . '/../../model/Meal.php';

$plan = Plan::first();
$assetPrefix = '/3rdV/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/assets/';

// Build week days
$today = date('Y-m-d');
$weekStart = date('Y-m-d', strtotime('monday this week'));
$weekDays = [];
for ($i = 0; $i < 7; $i++) {
    $weekDays[] = date('Y-m-d', strtotime($weekStart . " +{$i} days"));
}

// Selected day
$selectedDate = $_GET['date'] ?? $today;

// Load plan_meals for selected day — use same logic as front office (overrides + seed fallback)
$allMeals = Meal::all();
$overrides = [];
$overridePmIds = []; // plan_meals.id keyed by type (for delete)
$queryError = '';

if ($plan) {
    try {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT id, meal_type, meal_id FROM plan_meals WHERE plan_id=:pid AND meal_date=:dt');
        $stmt->execute([':pid' => $plan->id, ':dt' => $selectedDate]);
        foreach ($stmt->fetchAll() as $row) {
            $type = strtolower($row['meal_type']);
            $overrides[$type]     = (int) $row['meal_id'];
            $overridePmIds[$type] = (int) $row['id'];
        }
    } catch (Throwable $e) { $queryError = $e->getMessage(); }
}

// Same pickMeal logic as front office
function pickMealBO(array $meals, string $type, int $seed, array $overrides): ?Meal {
    if (isset($overrides[$type])) {
        foreach ($meals as $m) { if ($m->id === $overrides[$type]) return $m; }
    }
    $filtered = array_values(array_filter($meals, fn($m) => $m->mealType === $type));
    if (!$filtered) return null;
    return $filtered[$seed % count($filtered)];
}

$startTs = $plan ? (strtotime($plan->dateDebut) ?: time()) : time();
$selTs   = strtotime($selectedDate);
$dayNum  = (int) floor(($selTs - $startTs) / 86400) + 1;

$slots = ['breakfast', 'lunch', 'dinner', 'snack'];
$dayMeals = [];
foreach ($slots as $i => $type) {
    $meal = pickMealBO($allMeals, $type, $dayNum + $i, $overrides);
    if ($meal) {
        $isOverride = isset($overrides[$type]);
        $dayMeals[] = [
            'id'         => $overridePmIds[$type] ?? null, // null = seeded, not in DB
            'meal_type'  => $type,
            'meal_id'    => $meal->id,
            'name'       => $meal->name,
            'calories'   => $meal->calories,
            'notes'      => $meal->description,
            'is_override'=> $isOverride,
        ];
    }
}

// Daily calorie target from description
$dailyKcal = 0;
if ($plan && preg_match('/Daily target:\s*(\d+)/i', $plan->description, $m2)) {
    $dailyKcal = (int) $m2[1];
}
$totalKcal = array_sum(array_column($dayMeals, 'calories'));

// All meals for the add form
$allMeals = Meal::all();

$typeIcons = ['breakfast' => '🌅', 'lunch' => '🥗', 'dinner' => '🍽️', 'snack' => '🍎'];
$objectifLabels = [
    'lose_weight'     => 'Lose Weight',
    'maintain_weight' => 'Maintain Weight',
    'gain_muscle'     => 'Gain Muscle',
    'eat_healthy'     => 'Eat Healthy',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Back Office — My Plan</title>
  <link href="<?php echo $assetPrefix; ?>img/favicon.jpg" rel="icon">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?php echo $assetPrefix; ?>css/main.css" rel="stylesheet">
  <link href="<?php echo $assetPrefix; ?>css/backoffice-meals.css" rel="stylesheet">
  <style>
    body { background: #f5f6fa; }
    .plan-header-bar { background:#fff; border-radius:12px; padding:1.25rem 1.75rem; display:flex; align-items:center; gap:2.5rem; flex-wrap:wrap; margin-bottom:1.5rem; box-shadow:0 1px 6px rgba(0,0,0,.06); }
    .plan-header-bar .ph-label { font-size:.8rem; color:#999; margin-bottom:.15rem; }
    .plan-header-bar .ph-val { font-size:1.1rem; font-weight:700; }
    .plan-header-bar .ph-badge { background:#fff0f0; color:#ce1212; border-radius:20px; padding:.2rem .75rem; font-size:.85rem; font-weight:600; }

    .week-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:.75rem; margin-bottom:1.5rem; }
    .week-card { background:#fff; border-radius:12px; border:1.5px solid #eee; padding:.85rem .5rem; text-align:center; cursor:pointer; transition:.2s; text-decoration:none; color:inherit; }
    .week-card:hover { border-color:#ce1212; }
    .week-card.active { border-color:#ce1212; background:#fff8f8; }
    .week-card .wc-day { font-size:.8rem; font-weight:700; color:#ce1212; }
    .week-card .wc-date { font-size:.75rem; color:#999; margin-bottom:.3rem; }
    .week-card .wc-kcal { font-size:1.3rem; font-weight:800; color:#212529; }
    .week-card .wc-unit { font-size:.7rem; color:#999; }
    .week-card .wc-badge { font-size:.7rem; margin-top:.3rem; color:#aaa; }
    .week-card.today-card .wc-badge { background:#fff0f0; color:#ce1212; border-radius:10px; padding:.1rem .4rem; font-weight:600; }

    .bo-two-col { display:grid; grid-template-columns:1fr 380px; gap:1.25rem; align-items:start; }
    @media(max-width:900px){ .bo-two-col { grid-template-columns:1fr; } }

    .bo-card { background:#fff; border-radius:12px; padding:1.5rem; box-shadow:0 1px 6px rgba(0,0,0,.06); }
    .bo-card h2 { font-size:1.05rem; font-weight:700; margin-bottom:1rem; }

    .meals-table { width:100%; border-collapse:collapse; }
    .meals-table th { font-size:.8rem; color:#999; font-weight:600; padding:.5rem .75rem; border-bottom:1.5px solid #f0f0f0; text-align:left; }
    .meals-table td { padding:.85rem .75rem; border-bottom:1px solid #f8f8f8; vertical-align:middle; font-size:.9rem; }
    .meals-table tr:last-child td { border-bottom:none; }
    .type-icon { font-size:1.2rem; margin-right:.4rem; }
    .type-label { font-weight:600; font-size:.9rem; }
    .kcal-val { font-weight:700; color:#212529; }
    .notes-cell { color:#888; font-size:.85rem; max-width:200px; }
    .btn-icon { border:1px solid #ddd; background:#fff; border-radius:8px; padding:.3rem .55rem; cursor:pointer; transition:.15s; }
    .btn-icon:hover { background:#f5f5f5; }
    .btn-icon.del:hover { background:#fff0f0; border-color:#ce1212; color:#ce1212; }
    .btn-add-day { width:100%; margin-top:1rem; border:1.5px dashed #ce1212; background:#fff; color:#ce1212; border-radius:10px; padding:.65rem; font-weight:600; cursor:pointer; transition:.2s; }
    .btn-add-day:hover { background:#fff8f8; }

    .form-section label { font-size:.85rem; font-weight:600; color:#444; margin-bottom:.3rem; display:block; }
    .form-section .form-control, .form-section .form-select { font-size:.9rem; border-radius:8px; }
    .input-group-icon { display:flex; align-items:center; gap:.75rem; }
    .input-group-icon input, .input-group-icon select { flex:1; }
    .field-icon { width:38px; height:38px; background:#fff0f0; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#ce1212; font-size:1.1rem; flex-shrink:0; }
    .btn-save { background:#ce1212; color:#fff; border:none; border-radius:10px; padding:.75rem; font-weight:700; width:100%; font-size:.95rem; cursor:pointer; transition:.2s; }
    .btn-save:hover { background:#b00f0f; }
    .btn-cancel { background:#fff; color:#555; border:1.5px solid #ddd; border-radius:10px; padding:.75rem; font-weight:600; width:100%; font-size:.95rem; cursor:pointer; }
    .total-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; }
    .total-bar .total-label { font-size:.9rem; color:#666; }
    .total-bar .total-val { font-size:1rem; font-weight:700; color:#ce1212; }
  </style>
</head>
<body class="index-page bo-meals-page">

<div class="bo-layout">
<?php include __DIR__ . '/sidebar.php'; ?>

<div class="bo-main-content">
  <header class="bo-header sticky-top">
    <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap gap-2 px-4">
      <h1 class="sitename mb-0 fs-5">My Plan</h1>
      <nav class="d-flex gap-3 align-items-center">
        <a href="../FrontOffice/Plans.php">View front office</a>
      </nav>
    </div>
  </header>

  <main class="container-fluid px-4 py-4">

    <?php if (!$plan): ?>
      <div class="alert alert-warning">No active plan found. <a href="../FrontOffice/create_plan.php">Create one here</a>.</div>
    <?php else: ?>

    <!-- ── Top: Plan Details (left) + Current Plan card (right) ── -->
    <div class="row g-4 mb-4">

      <!-- Left: Edit form -->
      <div class="col-lg-4">
        <div class="bo-card h-100">
          <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid #ce1212;">
            <span style="color:#ce1212;font-size:1.1rem;">📋</span>
            <h2 class="mb-0" style="font-size:1.05rem;font-weight:700;">Plan Details</h2>
          </div>
          <div id="plan-form-feedback" style="margin-bottom:.75rem;"></div>
          <form id="plan-edit-form">
            <input type="hidden" id="pe-id" value="<?php echo $plan->id; ?>">

            <div class="mb-3">
              <label class="form-label fw-semibold" style="font-size:.85rem;">Plan Name</label>
              <div class="input-group">
                <span class="input-group-text" style="background:#fff8f8;border-color:#eee;">📋</span>
                <input type="text" class="form-control" id="pe-name" value="<?php echo htmlspecialchars($plan->nom); ?>" style="border-left:0;">
              </div>
              <small class="text-muted">Name of your plan.</small>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold" style="font-size:.85rem;">Objective</label>
              <div class="input-group">
                <span class="input-group-text" style="background:#fff8f8;border-color:#eee;">🔥</span>
                <select class="form-select" id="pe-objectif" style="border-left:0;">
                  <?php foreach (['lose_weight'=>'Lose Weight','maintain_weight'=>'Maintain Weight','gain_muscle'=>'Gain Muscle','eat_healthy'=>'Eat Healthy'] as $val=>$lbl): ?>
                  <option value="<?php echo $val; ?>" <?php echo $plan->objectif===$val?'selected':''; ?>><?php echo $lbl; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <small class="text-muted">Main goal of your plan.</small>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold" style="font-size:.85rem;">Duration (days)</label>
              <div class="input-group">
                <span class="input-group-text" style="background:#fff8f8;border-color:#eee;">📅</span>
                <select class="form-select" id="pe-duree" style="border-left:0;">
                  <?php foreach ([7,14,21,30,60,90] as $d): ?>
                  <option value="<?php echo $d; ?>" <?php echo $plan->duree===$d?'selected':''; ?>><?php echo $d; ?> days</option>
                  <?php endforeach; ?>
                </select>
              </div>
              <small class="text-muted">How many days your plan will last.</small>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold" style="font-size:.85rem;">Daily Calorie Target</label>
              <div class="input-group-icon">
                <div class="field-icon" style="width:38px;height:38px;background:#fff0f0;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#ce1212;font-size:1.1rem;flex-shrink:0;">🔥</div>
                <div class="kcal-wrap flex-fill" style="position:relative;">
                  <input type="number" class="form-control" id="pe-calories" value="<?php echo $dailyKcal; ?>" min="0" placeholder="e.g. 2000" style="padding-right:3.5rem;">
                  <span style="position:absolute;right:.9rem;top:50%;transform:translateY(-50%);color:#999;font-size:.95rem;pointer-events:none;">kcal</span>
                </div>
              </div>
              <div id="cal-hint" class="form-text mt-1"></div>
              <div id="cal-range-bar" class="mt-2" style="display:none;">
                <div id="cal-slider-track" style="position:relative;height:8px;background:#d4edda;border-radius:4px;cursor:pointer;margin:8px 4px;">
                  <div id="cal-slider-fill" style="position:absolute;left:0;top:0;height:8px;background:#2e7d32;border-radius:4px;width:0%;"></div>
                  <div id="cal-slider-thumb" style="position:absolute;top:50%;transform:translate(-50%,-50%);width:20px;height:20px;background:#2e7d32;border-radius:50%;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.2);cursor:grab;left:0%;"></div>
                </div>
              </div>
              <small class="text-muted">Target calories per day.</small>
            </div>

            <div class="mb-4">
              <label class="form-label fw-semibold" style="font-size:.85rem;">Description</label>
              <div class="input-group align-items-start">
                <span class="input-group-text" style="background:#fff8f8;border-color:#eee;align-self:flex-start;padding-top:.6rem;">📋</span>
                <textarea class="form-control" id="pe-desc" rows="3" style="border-left:0;"><?php echo htmlspecialchars($plan->description); ?></textarea>
              </div>
              <small class="text-muted">Additional notes about your plan.</small>
            </div>

            <button type="submit" class="btn btn-danger w-100 py-2 fw-bold">
              💾 Save Changes
            </button>
          </form>
        </div>
      </div>

      <!-- Right: Current Plan overview -->
      <div class="col-lg-8">
        <div class="bo-card h-100">
          <div class="d-flex align-items-center justify-content-between mb-3 pb-2" style="border-bottom:2px solid #ce1212;">
            <div class="d-flex align-items-center gap-2">
              <span style="color:#ce1212;font-size:1.1rem;">📅</span>
              <h2 class="mb-0" style="font-size:1.05rem;font-weight:700;">Your Current Plan</h2>
            </div>
            <span style="background:#e8f5e9;color:#2e7d32;border-radius:20px;padding:.2rem .75rem;font-size:.8rem;font-weight:600;">Active</span>
          </div>

          <!-- Plan identity -->
          <div class="d-flex align-items-center gap-4 mb-4 p-3" style="background:#fff8f8;border-radius:12px;">
            <div style="width:72px;height:72px;background:#ffe0e0;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2rem;flex-shrink:0;">📅</div>
            <div>
              <h3 style="font-size:1.4rem;font-weight:800;margin-bottom:.25rem;"><?php echo htmlspecialchars($plan->nom); ?></h3>
              <span style="background:#fff0f0;color:#ce1212;border-radius:20px;padding:.2rem .75rem;font-size:.85rem;font-weight:600;">
                🔥 <?php echo htmlspecialchars($objectifLabels[$plan->objectif] ?? ucfirst($plan->objectif)); ?>
              </span>
              <p class="text-muted mb-0 mt-1" style="font-size:.9rem;">Daily target: <?php echo $dailyKcal; ?> kcal</p>
            </div>
          </div>

          <!-- Stats row -->
          <?php
            $daysElapsed = $plan->daysElapsed();
            $progress    = $plan->progressPercent();
            $mealsPlanned = 0;
            try {
                $pdo = Database::pdo();
                $ms = $pdo->prepare('SELECT COUNT(*) FROM plan_meals WHERE plan_id=:pid');
                $ms->execute([':pid' => $plan->id]);
                $mealsPlanned = (int) $ms->fetchColumn();
            } catch (Throwable $e) {}
          ?>
          <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
              <div class="p-3 rounded-3" style="background:#f8f9fa;">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <span>📅</span><small class="text-muted fw-semibold">Duration</small>
                </div>
                <p class="fw-bold mb-0" style="font-size:1.1rem;"><?php echo $plan->duree; ?> days</p>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="p-3 rounded-3" style="background:#f8f9fa;">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <span>🔥</span><small class="text-muted fw-semibold">Daily Calories</small>
                </div>
                <p class="fw-bold mb-0" style="font-size:1.1rem;"><?php echo $dailyKcal; ?> kcal</p>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="p-3 rounded-3" style="background:#f8f9fa;">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <span>📝</span><small class="text-muted fw-semibold">Days Completed</small>
                </div>
                <p class="fw-bold mb-0" style="font-size:1.1rem;"><?php echo $daysElapsed; ?> / <?php echo $plan->duree; ?> days</p>
                <small class="text-muted"><?php echo $progress; ?>%</small>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="p-3 rounded-3" style="background:#f8f9fa;">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <span>🍴</span><small class="text-muted fw-semibold">Meals Planned</small>
                </div>
                <p class="fw-bold mb-0" style="font-size:1.1rem;"><?php echo $mealsPlanned; ?></p>
                <small class="text-muted">Meals</small>
              </div>
            </div>
          </div>

          <!-- Progress bar -->
          <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span class="fw-semibold" style="font-size:.9rem;">Your Progress</span>
              <span style="color:#ce1212;font-weight:700;font-size:.9rem;"><?php echo $progress; ?>%</span>
            </div>
            <div style="height:8px;background:#f0f0f0;border-radius:4px;overflow:hidden;">
              <div style="height:8px;background:#ce1212;border-radius:4px;width:<?php echo $progress; ?>%;transition:.3s;"></div>
            </div>
          </div>

          <!-- Tip -->
          <div class="p-3 rounded-3" style="background:#fffbf0;border:1px solid #fde68a;">
            <p class="mb-0" style="font-size:.9rem;">⭐ <strong>Tip:</strong> Plan your meals, track your progress, and stay consistent. Small steps every day lead to big results!</p>
          </div>
        </div>
      </div>
    </div><!-- /top row -->

    <!-- Week calendar -->
    <div class="week-grid">
      <?php foreach ($weekDays as $day):
        $isToday = $day === $today;
        $isActive = $day === $selectedDate;
        $isPast = $day < $today;
        $dayLabel = date('D', strtotime($day));
        $dayDate = date('M j', strtotime($day));
        // Get kcal for this day
        $dayKcal = 0;
        try {
            $pdo = Database::pdo();
            $ks = $pdo->prepare('SELECT SUM(m.calories) FROM plan_meals pm JOIN meal m ON m.id_meal=pm.meal_id WHERE pm.plan_id=:pid AND pm.meal_date=:dt');
            $ks->execute([':pid' => $plan->id, ':dt' => $day]);
            $dayKcal = (int) $ks->fetchColumn() ?: $dailyKcal;
        } catch (Throwable $e) { $dayKcal = $dailyKcal; }
      ?>
      <a href="?date=<?php echo $day; ?>" class="week-card <?php echo $isActive ? 'active' : ''; ?> <?php echo $isToday ? 'today-card' : ''; ?>">
        <div class="wc-day"><?php echo $dayLabel; ?></div>
        <div class="wc-date"><?php echo $dayDate; ?></div>
        <div class="wc-kcal"><?php echo $dayKcal ?: $dailyKcal; ?></div>
        <div class="wc-unit">kcal</div>
        <div class="wc-badge">
          <?php if ($isToday): ?>Today
          <?php elseif ($isPast): ?>Done
          <?php else: ?>Upcoming<?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Two column: meals table + form -->
    <div class="bo-two-col">

      <!-- Meals for selected day -->
      <div class="bo-card">
        <div class="total-bar">
          <h2 class="mb-0">Meals for <?php echo date('l, M j', strtotime($selectedDate)); ?></h2>
          <span class="total-val">Total: <?php echo $totalKcal; ?> kcal</span>
        </div>

        <?php if (empty($dayMeals)): ?>
          <?php if ($queryError): ?>
            <p class="text-danger small">DB Error: <?php echo htmlspecialchars($queryError); ?></p>
          <?php endif; ?>
          <p class="text-muted text-center py-4">No meals added for this day yet.</p>
        <?php else: ?>
        <table class="meals-table">
          <thead>
            <tr>
              <th>Meal Type</th>
              <th>Meal Name</th>
              <th>Calories</th>
              <th>Notes</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($dayMeals as $dm): ?>
            <tr>
              <td>
                <span class="type-icon"><?php echo $typeIcons[$dm['meal_type']] ?? '🍴'; ?></span>
                <span class="type-label"><?php echo ucfirst($dm['meal_type']); ?></span>
              </td>
              <td>
                <?php echo htmlspecialchars($dm['name']); ?>
                <?php if (!$dm['is_override']): ?>
                  <span style="font-size:.75rem;color:#aaa;margin-left:.4rem;">(auto)</span>
                <?php endif; ?>
              </td>
              <td class="kcal-val"><?php echo $dm['calories']; ?> kcal</td>
              <td class="notes-cell"><?php echo htmlspecialchars(mb_strimwidth($dm['notes'] ?? '', 0, 60, '…')); ?></td>
              <td>
                <button class="btn-icon me-1" onclick="editMeal('<?php echo $dm['meal_type']; ?>', <?php echo $dm['meal_id']; ?>)" title="Replace">✏️</button>
                <?php if ($dm['is_override']): ?>
                <button class="btn-icon del" onclick="deleteMeal(<?php echo $dm['id']; ?>, '<?php echo $selectedDate; ?>')" title="Remove override">🗑️</button>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>

        <button class="btn-add-day" onclick="showAddForm()">+ Add Meal to This Day</button>
      </div>

      <!-- Add / Edit Meal form -->
      <div class="bo-card form-section" id="meal-form-card">
        <h2>Add / Edit Meal</h2>
        <div id="form-feedback" style="margin-bottom:.75rem;"></div>

        <form id="meal-form">
          <input type="hidden" id="form-date" value="<?php echo $selectedDate; ?>">
          <input type="hidden" id="form-pm-id" value="">

          <div class="mb-3">
            <label>Meal Type <span style="color:#ce1212">*</span></label>
            <select class="form-select" id="form-meal-type">
              <option value="">Choose...</option>
              <option value="breakfast">🌅 Breakfast</option>
              <option value="lunch">🥗 Lunch</option>
              <option value="dinner">🍽️ Dinner</option>
              <option value="snack">🍎 Snack</option>
            </select>
          </div>

          <div class="mb-3">
            <label>Meal Name <span style="color:#ce1212">*</span></label>
            <select class="form-select" id="form-meal-id">
              <option value="">Select a meal...</option>
              <?php foreach ($allMeals as $m): ?>
              <option value="<?php echo $m->id; ?>" data-type="<?php echo $m->mealType; ?>" data-cal="<?php echo $m->calories; ?>">
                <?php echo htmlspecialchars($m->name); ?> (<?php echo $m->calories; ?> kcal)
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn-save">Save Meal</button>
            <button type="button" class="btn-cancel" onclick="resetForm()">Cancel</button>
          </div>
        </form>
      </div>

    </div>
    <?php endif; ?>

  </main>
</div><!-- /bo-main-content -->
</div><!-- /bo-layout -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const BASE = '/3rdV/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/FrontOffice/';

// Save plan details
document.getElementById('plan-edit-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const fd = new FormData();
  fd.append('action',      'save');
  fd.append('editing_id',  document.getElementById('pe-id').value);
  fd.append('name',        document.getElementById('pe-name').value);
  fd.append('objective',   document.getElementById('pe-objectif').value);
  fd.append('duration',    document.getElementById('pe-duree').value);
  const cal = document.getElementById('pe-calories').value;
  fd.append('total_calories', cal);
  fd.append('description', document.getElementById('pe-desc').value || ('Daily target: ' + cal + ' kcal'));
  fd.append('meal_type',   'daily');

  fetch('plans_save.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      const el = document.getElementById('plan-form-feedback');
      el.innerHTML = '<div style="padding:.4rem .75rem;border-radius:8px;font-size:.85rem;background:' +
        (d.ok ? '#e8f5e9;color:#1b5e20' : '#fde8e8;color:#9b1c1c') + '">' +
        (d.message || (d.ok ? 'Saved!' : 'Error')) + '</div>';
      if (d.ok) setTimeout(() => window.location.reload(), 800);
      else setTimeout(() => el.innerHTML = '', 3000);
    });
});

function showFeedback(msg, ok) {
  const el = document.getElementById('form-feedback');
  el.innerHTML = '<div style="padding:.5rem .75rem;border-radius:8px;font-size:.875rem;background:' +
    (ok ? '#e8f5e9;color:#1b5e20' : '#fde8e8;color:#9b1c1c') + '">' + msg + '</div>';
  setTimeout(() => el.innerHTML = '', 3000);
}

function editMeal(type, mealId) {
  document.getElementById('form-meal-type').value = type;
  document.getElementById('form-meal-id').value = mealId;
  document.getElementById('meal-form-card').scrollIntoView({ behavior: 'smooth' });
}

function deleteMeal(pmId, date) {
  if (!confirm('Remove this meal from the plan?')) return;
  const fd = new FormData();
  fd.append('pm_id', pmId);
  fd.append('date', date);
  fetch('plans_delete_meal.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (d.ok) window.location.reload();
      else alert(d.message || 'Error deleting meal');
    });
}

function showAddForm() {
  resetForm();
  document.getElementById('meal-form-card').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
  document.getElementById('form-meal-type').value = '';
  document.getElementById('form-meal-id').value = '';
  document.getElementById('form-pm-id').value = '';
  document.getElementById('form-feedback').innerHTML = '';
}

// Calorie slider (same as front office)
var RANGES = {
  'lose_weight':     { min: 1400, max: 1800, def: 1600, label: 'Weight loss: 1,400 – 1,800 kcal/day' },
  'maintain_weight': { min: 1800, max: 2200, def: 2000, label: 'Maintain weight: 1,800 – 2,200 kcal/day' },
  'gain_muscle':     { min: 2200, max: 2800, def: 2500, label: 'Gain muscle: 2,200 – 2,800 kcal/day' },
  'eat_healthy':     { min: 1600, max: 2200, def: 1800, label: 'Eat healthy: 1,600 – 2,200 kcal/day' },
};
var calInput = document.getElementById('pe-calories');
var calHint  = document.getElementById('cal-hint');
var calBar   = document.getElementById('cal-range-bar');
var track    = document.getElementById('cal-slider-track');
var fill     = document.getElementById('cal-slider-fill');
var thumb    = document.getElementById('cal-slider-thumb');
var objSel   = document.getElementById('pe-objectif');
var currentRange = null;

function pct(val, r) { return Math.min(100, Math.max(0, ((val - r.min) / (r.max - r.min)) * 100)); }
function updateSliderUI(val) { if (!currentRange) return; var p = pct(val, currentRange); fill.style.width = p + '%'; thumb.style.left = p + '%'; }
function updateHint(val, r) { calHint.innerHTML = '👉 ' + r.label + ' &nbsp;<span style="color:#2e7d32;font-weight:700;">' + val + ' kcal/day</span>'; }

function applyRange(obj) {
  var r = RANGES[obj];
  if (!r) { calHint.textContent = ''; calBar.style.display = 'none'; currentRange = null; return; }
  currentRange = r;
  calBar.style.display = '';
  var current = parseInt(calInput.value, 10);
  var val = (!current || current < r.min || current > r.max) ? r.def : current;
  calInput.value = val;
  updateSliderUI(val);
  updateHint(val, r);
}

var dragging = false;
function sliderSetFromEvent(e) {
  if (!currentRange) return;
  var rect = track.getBoundingClientRect();
  var clientX = e.touches ? e.touches[0].clientX : e.clientX;
  var ratio = Math.min(1, Math.max(0, (clientX - rect.left) / rect.width));
  var val = Math.round((currentRange.min + ratio * (currentRange.max - currentRange.min)) / 50) * 50;
  val = Math.min(currentRange.max, Math.max(currentRange.min, val));
  calInput.value = val; updateSliderUI(val); updateHint(val, currentRange);
}
track.addEventListener('mousedown', function(e) { dragging = true; sliderSetFromEvent(e); });
document.addEventListener('mousemove', function(e) { if (dragging) sliderSetFromEvent(e); });
document.addEventListener('mouseup', function() { dragging = false; });
track.addEventListener('touchstart', function(e) { dragging = true; sliderSetFromEvent(e); }, { passive: true });
document.addEventListener('touchmove', function(e) { if (dragging) sliderSetFromEvent(e); }, { passive: true });
document.addEventListener('touchend', function() { dragging = false; });

calInput.addEventListener('input', function() {
  if (!currentRange) return;
  var v = parseInt(this.value, 10);
  if (!isNaN(v)) { updateSliderUI(v); updateHint(v, currentRange); }
});
calInput.addEventListener('blur', function() {
  if (!currentRange) return;
  var v = parseInt(this.value, 10);
  if (isNaN(v) || v < currentRange.min) v = currentRange.min;
  else if (v > currentRange.max) v = currentRange.max;
  this.value = v; updateSliderUI(v); updateHint(v, currentRange);
});

objSel.addEventListener('change', function() { applyRange(this.value); });
applyRange(objSel.value);
document.getElementById('form-meal-type').addEventListener('change', function() {
  const type = this.value;
  const select = document.getElementById('form-meal-id');
  select.value = '';
  Array.from(select.options).forEach(function(opt) {
    if (!opt.value) return; // keep placeholder
    opt.hidden = type ? opt.dataset.type !== type : false;
  });
});

document.getElementById('meal-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const mealType = document.getElementById('form-meal-type').value;
  const mealId   = document.getElementById('form-meal-id').value;
  const date     = document.getElementById('form-date').value;

  if (!mealType || !mealId) {
    showFeedback('Please select a meal type and a meal.', false);
    return;
  }

  const fd = new FormData();
  fd.append('meal_id',   mealId);
  fd.append('meal_type', mealType);
  fd.append('meal_date', date);

  fetch('plans_save_meal.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (d.ok) {
        showFeedback(d.message || 'Meal saved!', true);
        setTimeout(() => window.location.reload(), 800);
      } else {
        showFeedback(d.message || 'Error saving meal.', false);
      }
    })
    .catch(() => showFeedback('Network error.', false));
});
</script>
</body>
</html>
