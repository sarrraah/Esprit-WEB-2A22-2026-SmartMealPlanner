<?php

require_once __DIR__ . '/../../model/Plan.php';

$assetPrefix = '/3rdV/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/assets/';

$plan = Plan::first();
if (!$plan) {
    header('Location: Plans.php');
    exit;
}

// Parse daily calorie target from description ("Daily target: 1800 kcal")
$dailyKcal = 0;
if (preg_match('/Daily target:\s*(\d+)/i', $plan->description, $m)) {
    $dailyKcal = (int) $m[1];
}

// Week navigation
$weekOffset = (int) ($_GET['week'] ?? 0);
$startTs    = $plan->dateDebut ? strtotime($plan->dateDebut) : strtotime('monday this week');

// Find the Monday of the plan's start week, then offset
$planMonday = strtotime('monday this week', $startTs);
if (date('N', $startTs) == 1) $planMonday = $startTs; // already monday
$weekStart  = strtotime("+{$weekOffset} weeks", $planMonday);
$weekEnd    = strtotime('+6 days', $weekStart);

$today      = strtotime(date('Y-m-d'));
$planEnd    = $plan->dateFin ? strtotime($plan->dateFin) : strtotime("+{$plan->duree} days", $startTs);

// Build 7 days
$days = [];
for ($i = 0; $i < 7; $i++) {
    $ts       = strtotime("+{$i} days", $weekStart);
    $dayNum   = (int) floor(($ts - $startTs) / 86400) + 1;
    $inPlan   = $ts >= $startTs && $ts <= $planEnd && $dayNum >= 1 && $dayNum <= $plan->duree;
    $isPast   = $ts < $today;
    $isToday  = $ts === $today;
    $days[]   = [
        'ts'      => $ts,
        'label'   => date('D', $ts),
        'date'    => date('M j', $ts),
        'dayNum'  => $dayNum,
        'inPlan'  => $inPlan,
        'isPast'  => $isPast && $inPlan,
        'isToday' => $isToday && $inPlan,
        'kcal'    => $inPlan ? $dailyKcal : 0,
    ];
}

$daysElapsed    = $plan->daysElapsed();
$progress       = $plan->progressPercent();
$weeklyTarget   = $dailyKcal * 7;
$plannedSoFar   = $dailyKcal * min($daysElapsed, 7);
$weeklyRemaining = max(0, $weeklyTarget - $plannedSoFar);

$objectifLabels = [
    'lose_weight'     => 'Lose Weight',
    'maintain_weight' => 'Maintain Weight',
    'gain_muscle'     => 'Gain Muscle',
    'eat_healthy'     => 'Eat Healthy',
];
$objectifLabel = $objectifLabels[$plan->objectif] ?? ucfirst($plan->objectif);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>View Plan — Smart Meal Planner</title>
  <link href="<?php echo $assetPrefix; ?>img/favicon.jpg" rel="icon">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?php echo $assetPrefix; ?>css/main.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; font-size: 1rem; }
    .vp-wrap   { max-width: 1100px; margin: 0 auto; padding: 2rem 1rem 4rem; }
    .vp-card   { background: #fff; border-radius: 16px; border: 1px solid #f0f0f0; padding: 1.75rem; }

    /* Sidebar */
    .vp-sidebar h3  { font-size: 1.2rem; font-weight: 700; }
    .vp-meta-label  { font-size: .85rem; color: #999; margin-bottom: .1rem; }
    .vp-meta-val    { font-size: 1rem; font-weight: 600; color: #212529; }
    .vp-badge       { display: inline-block; background: #e8f5e9; color: #2e7d32; font-size: .85rem; font-weight: 600; padding: .2rem .7rem; border-radius: 20px; }
    .progress-track { height: 10px; border-radius: 5px; background: #f0f0f0; overflow: hidden; }
    .progress-fill  { height: 10px; border-radius: 5px; background: #ce1212; }
    .tip-box        { background: #f0faf0; border-radius: 10px; padding: 1rem; font-size: .95rem; color: #2e7d32; }

    /* Calendar */
    .cal-nav    { display: flex; align-items: center; gap: .75rem; font-size: 1rem; font-weight: 600; }
    .cal-nav button { background: none; border: 1px solid #e0e0e0; border-radius: 8px; width: 34px; height: 34px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .cal-nav button:hover { border-color: #ce1212; color: #ce1212; }

    .day-grid   { display: grid; grid-template-columns: repeat(7, 1fr); gap: .75rem; margin-top: 1.25rem; }
    .day-cell   { border: 1.5px solid #e8e8e8; border-radius: 12px; padding: 1rem .5rem; text-align: center; background: #fafafa; transition: .2s; }
    .day-cell.today   { border-color: #ce1212; background: #fff; }
    .day-cell.past    { background: #fff; }
    .day-cell.outside { opacity: .35; }
    .day-cell:hover:not(.outside) { border-color: #ce1212; cursor: pointer; }

    .day-name   { font-size: .85rem; font-weight: 700; color: #555; }
    .day-date   { font-size: .8rem; color: #999; margin-bottom: .5rem; }
    .day-cell.today .day-name { color: #ce1212; }
    .day-cell.today .day-date { color: #ce1212; }

    .day-kcal   { font-size: 1.5rem; font-weight: 700; color: #212529; line-height: 1.1; }
    .day-cell.today .day-kcal { color: #ce1212; }
    .day-unit   { font-size: .8rem; color: #999; }

    .day-status { font-size: .78rem; font-weight: 600; margin: .5rem 0 .4rem; }
    .status-completed { color: #2e7d32; }
    .status-today     { color: #ce1212; background: #fff0f0; border-radius: 20px; padding: .1rem .5rem; }
    .status-upcoming  { color: #999; }

    .day-icon   { font-size: 1.2rem; }
    .day-icon.check  { color: #2e7d32; }
    .day-icon.arrow  { color: #ce1212; }
    .day-icon.lock   { color: #ccc; }

    /* Weekly summary */
    .weekly-bar { background: #fff8f8; border-radius: 12px; padding: 1rem 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-top: 1.25rem; }
    .weekly-bar .label { font-size: .95rem; color: #555; }
    .weekly-bar strong { color: #212529; }

    .hint-bar { display: flex; align-items: center; gap: .6rem; font-size: .95rem; color: #777; margin-top: 1rem; }
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
    <div class="vp-wrap">

      <!-- Page header -->
      <div class="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2">
        <div>
          <a href="Plans.php" class="text-muted text-decoration-none d-inline-flex align-items-center gap-1 mb-2" style="font-size:.95rem;">
            <i class="bi bi-arrow-left"></i> Back to My Plan
          </a>
          <h1 class="fw-bold mb-1" style="font-size:1.8rem;">My Plan</h1>
          <p class="text-muted mb-0" style="font-size:1rem;">Here's your plan overview. Click on a day to see the suggested meals.</p>
        </div>
        <a href="create_plan.php" class="btn btn-outline-danger rounded-pill px-4" style="font-size:1rem;">
          <i class="bi bi-pencil me-1"></i> Edit Plan
        </a>
        <form method="POST" action="create_plan.php" id="delete-plan-form">
          <input type="hidden" name="action" value="delete">
          <button type="button" class="btn btn-danger rounded-pill px-4" style="font-size:1rem;" id="btn-delete-plan">
            <i class="bi bi-trash me-1"></i> Delete Plan
          </button>
        </form>
      </div>

      <div class="vp-card">
        <div class="row g-4">

          <!-- ── Sidebar ──────────────────────────────────────── -->
          <div class="col-lg-3 vp-sidebar">
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
              <?php echo $progress >= 100 ? '🎉 Plan complete!' : ($progress >= 50 ? 'Keep going! You\'re doing great!' : 'You\'re getting started!'); ?>
            </p>

            <div class="tip-box">
              <p class="fw-semibold mb-1" style="font-size:.95rem;">🌿 Tip</p>
              <p class="mb-0" style="font-size:.9rem;">Staying consistent with your plan brings the best results. You've got this!</p>
            </div>
          </div>

          <!-- ── Calendar ─────────────────────────────────────── -->
          <div class="col-lg-9">

            <!-- Week nav -->
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
              <div class="cal-nav">
                <a href="?week=<?php echo $weekOffset - 1; ?>" class="cal-nav-btn">
                  <button><i class="bi bi-chevron-left"></i></button>
                </a>
                <span>
                  <i class="bi bi-calendar3 text-danger me-1"></i>
                  <?php echo date('M j', $weekStart); ?> – <?php echo date('M j, Y', $weekEnd); ?>
                </span>
                <a href="?week=<?php echo $weekOffset + 1; ?>" class="cal-nav-btn">
                  <button><i class="bi bi-chevron-right"></i></button>
                </a>
              </div>
            </div>

            <!-- Day grid -->
            <div class="day-grid">
              <?php foreach ($days as $day) :
                $cls = 'day-cell';
                if (!$day['inPlan'])    $cls .= ' outside';
                elseif ($day['isToday']) $cls .= ' today';
                elseif ($day['isPast'])  $cls .= ' past';
                $dateStr = date('Y-m-d', $day['ts']);
              ?>
              <a href="day_plan.php?date=<?php echo $dateStr; ?>&week=<?php echo $weekOffset; ?>"
                 class="<?php echo $cls; ?>" style="text-decoration:none;color:inherit;"
                 <?php echo !$day['inPlan'] ? 'style="pointer-events:none;text-decoration:none;color:inherit;"' : ''; ?>>
                <div class="day-name"><?php echo $day['label']; ?></div>
                <div class="day-date"><?php echo $day['date']; ?></div>

                <?php if ($day['inPlan'] && $day['kcal'] > 0) : ?>
                  <div class="day-kcal"><?php echo $day['kcal']; ?></div>
                  <div class="day-unit">kcal</div>
                <?php elseif ($day['inPlan']) : ?>
                  <div class="day-kcal" style="font-size:1rem;color:#ccc;">—</div>
                <?php else : ?>
                  <div class="day-kcal" style="font-size:1rem;color:#ddd;">—</div>
                <?php endif; ?>

                <?php if ($day['inPlan']) : ?>
                  <?php if ($day['isPast']) : ?>
                    <div class="day-status status-completed">Completed</div>
                    <div class="day-icon check"><i class="bi bi-check-circle-fill"></i></div>
                  <?php elseif ($day['isToday']) : ?>
                    <div class="day-status status-today">Today</div>
                    <div class="day-icon arrow"><i class="bi bi-arrow-right-circle"></i></div>
                  <?php else : ?>
                    <div class="day-status status-upcoming">Upcoming</div>
                    <div class="day-icon lock"><i class="bi bi-lock-fill"></i></div>
                  <?php endif; ?>
                <?php endif; ?>
              </a>
              <?php endforeach; ?>
            </div>

            <!-- Weekly summary — doughnut chart -->
            <div class="weekly-bar" style="align-items:center;gap:1.5rem;">
              <canvas id="weeklyChart" width="40" height="40" style="flex-shrink:0;width:40px;height:40px;"></canvas>
              <div style="flex:1;">
                <p class="fw-bold mb-1" style="color:#ce1212;font-size:1rem;">Weekly Summary</p>
                <p class="label mb-2">
                  Target: <strong><?php echo number_format($weeklyTarget); ?> kcal</strong>
                  &nbsp;·&nbsp; Planned: <strong><?php echo number_format($plannedSoFar); ?> kcal</strong>
                  &nbsp;·&nbsp; Remaining: <strong><?php echo number_format($weeklyRemaining); ?> kcal</strong>
                </p>
                <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                  <span style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;">
                    <span style="width:12px;height:12px;background:#ce1212;border-radius:50%;display:inline-block;"></span> Planned
                  </span>
                  <span style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;">
                    <span style="width:12px;height:12px;background:#f0f0f0;border-radius:50%;display:inline-block;"></span> Remaining
                  </span>
                </div>
              </div>
              <a href="Plans.php" class="btn btn-danger rounded-pill px-4" style="font-size:.95rem;flex-shrink:0;">
                <i class="bi bi-graph-up-arrow me-1"></i> View Progress
              </a>
            </div>

            <!-- Hint -->
            <div class="hint-bar">
              <i class="bi bi-info-circle text-danger"></i>
              Click on any day to view the suggested meals and recipes for that day.
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
  <!-- Delete Plan Modal -->
  <div class="modal fade" id="deletePlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold">Delete Plan</h5>
        </div>
        <div class="modal-body pt-2">
          Delete your plan? This cannot be undone.
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger"
            onclick="document.getElementById('delete-plan-form').submit();">Delete</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script src="<?php echo $assetPrefix; ?>js/main.js"></script>
  <script>
    document.getElementById('btn-delete-plan').addEventListener('click', function() {
      new bootstrap.Modal(document.getElementById('deletePlanModal')).show();
    });

    // Weekly summary doughnut chart
    new Chart(document.getElementById('weeklyChart'), {
      type: 'doughnut',
      data: {
        labels: ['Planned', 'Remaining'],
        datasets: [{
          data: [<?php echo $plannedSoFar; ?>, <?php echo max(0, $weeklyRemaining); ?>],
          backgroundColor: ['#ce1212', '#f0f0f0'],
          borderWidth: 0,
          hoverOffset: 4
        }]
      },
      options: {
        cutout: '72%',
        responsive: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function(ctx) {
                return ' ' + ctx.parsed.toLocaleString() + ' kcal';
              }
            }
          }
        }
      }
    });
  </script>
</body>
</html>

