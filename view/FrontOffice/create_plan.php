<?php

require_once __DIR__ . '/../../controller/PlanController.php';

$assetPrefix = '/3rdV/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/assets/';
$errors  = [];
$existing = Plan::first();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    PlanController::deletePlan();
    header('Location: Plans.php');
    exit;
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $result = PlanController::createPlan($_POST);
    if ($result['ok']) {
        header('Location: Plans.php');
        exit;
    }
    $errors = $result['errors'];
}

// Pre-fill from existing plan
$prefill = [
    'nom'      => $_POST['nom']      ?? ($existing->nom      ?? ''),
    'objectif' => $_POST['objectif'] ?? ($existing->objectif ?? ''),
    'duree'    => $_POST['duree']    ?? ($existing->duree    ?? 7),
    'calories' => $_POST['calories'] ?? '',
];

// Extract calories from description if editing
if ($existing && empty($_POST['calories'])) {
    if (preg_match('/Daily target:\s*(\d+)/i', $existing->description, $m)) {
        $prefill['calories'] = $m[1];
    }
}

$isEditing = $existing !== null;

$objectives = [
    'lose_weight'     => ['label' => 'Lose Weight',     'icon' => '🔥'],
    'maintain_weight' => ['label' => 'Maintain Weight', 'icon' => '⚖️'],
    'gain_muscle'     => ['label' => 'Gain Muscle',     'icon' => '💪'],
    'eat_healthy'     => ['label' => 'Eat Healthy',     'icon' => '🥗'],
];
$selectedObj = $prefill['objectif'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Create Your Plan — Smart Meal Planner</title>
  <link href="<?php echo $assetPrefix; ?>img/favicon.jpg" rel="icon">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?php echo $assetPrefix; ?>css/main.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; }
    .create-wrap { max-width: 960px; margin: 0 auto; padding: 2rem 1rem 4rem; }
    .back-link { font-size: 1rem; color: #555; text-decoration: none; display: inline-flex; align-items: center; gap: .4rem; margin-bottom: 1.25rem; }
    .back-link:hover { color: #ce1212; }
    .form-card { background: #fff; border-radius: 16px; padding: 2rem; border: 1px solid #f0f0f0; }
    .side-card { background: #fff8f8; border-radius: 16px; padding: 2rem; border: 1px solid #fde8e8; height: 100%; }
    .field-icon { width: 38px; height: 38px; background: #fff0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #ce1212; font-size: 1.1rem; flex-shrink: 0; }
    .input-group-icon { display: flex; align-items: center; gap: .75rem; }
    .input-group-icon input, .input-group-icon select { flex: 1; }
    .obj-btn { border: 2px solid #e0e0e0; border-radius: 10px; padding: .5rem 1rem; background: #fff; cursor: pointer; font-size: .95rem; font-weight: 500; transition: .2s; display: inline-flex; align-items: center; gap: .4rem; }
    .obj-btn:hover, .obj-btn.selected { border-color: #ce1212; background: #fff0f0; color: #ce1212; }
    .side-feature { display: flex; align-items: flex-start; gap: .75rem; margin-bottom: 1.25rem; }
    .side-feature-icon { width: 36px; height: 36px; background: #fff0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #ce1212; flex-shrink: 0; }
    .tip-bar { background: #fffbea; border-radius: 12px; padding: 1rem 1.5rem; display: flex; align-items: center; gap: .75rem; font-size: 1rem; color: #555; margin-top: 1.5rem; }
    .form-label { font-weight: 600; font-size: 1rem; margin-bottom: .5rem; }
    .form-control, .form-select { font-size: 1rem; padding: .65rem .9rem; }
    .form-text { font-size: .9rem; }
    .kcal-wrap { position: relative; }
    .kcal-wrap input { padding-right: 3.5rem; }
    .kcal-unit { position: absolute; right: .9rem; top: 50%; transform: translateY(-50%); color: #999; font-size: .95rem; pointer-events: none; }
    #cal-slider-track { user-select: none; }
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
    <div class="create-wrap">

      <a href="Plans.php" class="back-link"><i class="bi bi-arrow-left"></i> Back to My Plan</a>

      <h1 class="fw-bold mb-1" style="font-size:1.8rem;"><?php echo $isEditing ? 'Edit Your Plan' : 'Create Your Plan'; ?></h1>
      <p class="text-muted mb-4" style="font-size:1.05rem;">Set up your personalized meal plan in a few simple steps.</p>

      <?php if ($errors) : ?>
      <div class="alert alert-danger mb-4">
        <ul class="mb-0">
          <?php foreach ($errors as $e) : ?>
          <li><?php echo htmlspecialchars($e); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <div class="row g-4">

        <!-- Form -->
        <div class="col-lg-7">
          <div class="form-card">
            <form method="POST" action="create_plan.php" novalidate>
              <input type="hidden" name="action" value="save">

              <!-- Plan Name -->
              <div class="mb-4">
                <label class="form-label" for="nom">Plan Name <span style="color:#ce1212;">*</span></label>
                <div class="input-group-icon">
                  <div class="field-icon"><i class="bi bi-journal-text"></i></div>
                  <input type="text" class="form-control" id="nom" name="nom"
                    placeholder="e.g. Weight Loss Plan"
                    value="<?php echo htmlspecialchars($prefill['nom']); ?>">
                </div>
                <div class="form-text">Give your plan a name you'll recognize.</div>
              </div>

              <!-- Objective -->
              <div class="mb-4">
                <label class="form-label" for="objectif">Objective <span style="color:#ce1212;">*</span></label>
                <div class="input-group-icon mb-2">
                  <div class="field-icon"><i class="bi bi-bullseye"></i></div>
                  <select class="form-select" id="objectif" name="objectif">
                    <option value="">Select your main objective</option>
                    <?php foreach ($objectives as $val => $obj) : ?>
                    <option value="<?php echo $val; ?>" <?php echo $selectedObj === $val ? 'selected' : ''; ?>>
                      <?php echo $obj['icon'] . ' ' . $obj['label']; ?>
                    </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <!-- Quick-select buttons -->
                <div class="d-flex flex-wrap gap-2 mt-2">
                  <?php foreach ($objectives as $val => $obj) : ?>
                  <button type="button" class="obj-btn <?php echo $selectedObj === $val ? 'selected' : ''; ?>"
                    data-val="<?php echo $val; ?>">
                    <?php echo $obj['icon'] . ' ' . $obj['label']; ?>
                  </button>
                  <?php endforeach; ?>
                </div>
                <div class="form-text">Choose the goal you want to achieve with this plan.</div>
              </div>

              <!-- Duration -->
              <div class="mb-4">
                <label class="form-label" for="duree">Duration (Days) <span style="color:#ce1212;">*</span></label>
                <div class="input-group-icon">
                  <div class="field-icon"><i class="bi bi-calendar3"></i></div>
                  <select class="form-select" id="duree" name="duree">
                    <?php foreach ([7, 14, 21, 30, 60, 90] as $d) : ?>
                    <option value="<?php echo $d; ?>" <?php echo (int)$prefill['duree'] === $d ? 'selected' : ''; ?>>
                      <?php echo $d; ?> days
                    </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-text">Select how many days your plan will last.</div>
              </div>

              <!-- Daily Calorie Target -->
              <div class="mb-4">
                <label class="form-label" for="calories">Daily Calorie Target</label>
                <div class="input-group-icon">
                  <div class="field-icon"><i class="bi bi-fire"></i></div>
                  <div class="kcal-wrap flex-fill">
                    <input type="text" class="form-control" id="calories" name="calories"
                      placeholder="1800"
                      value="<?php echo htmlspecialchars($prefill['calories'] ?: '1800'); ?>">
                    <span class="kcal-unit">kcal</span>
                  </div>
                </div>
                <div id="cal-hint" class="form-text mt-1"></div>
                <div id="cal-range-bar" class="mt-2" style="display:none;">
                  <div id="cal-slider-track" style="position:relative;height:8px;background:#d4edda;border-radius:4px;cursor:pointer;margin:8px 4px;">
                    <div id="cal-slider-fill" style="position:absolute;left:0;top:0;height:8px;background:#2e7d32;border-radius:4px;width:0%;"></div>
                    <div id="cal-slider-thumb" style="position:absolute;top:50%;transform:translate(-50%,-50%);width:20px;height:20px;background:#2e7d32;border-radius:50%;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.2);cursor:grab;left:0%;"></div>
                  </div>
                </div>
              </div>



              <button type="submit" class="btn btn-danger w-100 py-3" style="font-size:1.1rem;border-radius:10px;">
                <i class="bi bi-calendar-plus me-2"></i> <?php echo $isEditing ? 'Save Changes' : 'Create My Plan'; ?>
              </button>

            </form>

            <?php if ($isEditing) : ?>
            <form method="POST" action="create_plan.php" class="mt-2" id="delete-plan-form">
              <input type="hidden" name="action" value="delete">
              <button type="button" id="btn-delete-plan" class="btn btn-outline-danger w-100 py-2" style="font-size:1rem;border-radius:10px;">
                <i class="bi bi-trash me-2"></i> Delete Plan
              </button>
            </form>
            <?php endif; ?>
          </div>
        </div>

        <!-- Side panel -->
        <div class="col-lg-5">
          <div class="side-card">
            <h3 class="fw-bold text-center mb-1" style="color:#ce1212;font-size:1.3rem;">Your Plan. Your Goals.</h3>
            <p class="text-center text-muted mb-4" style="font-size:1rem;">We'll help you stay on track every day.</p>

            <div class="text-center mb-4">
              <span style="font-size:5rem;">📋</span>
            </div>

            <div class="side-feature">
              <div class="side-feature-icon"><i class="bi bi-bullseye"></i></div>
              <div>
                <p class="fw-semibold mb-0" style="font-size:1rem;">Personalized for you</p>
                <p class="text-muted mb-0" style="font-size:.95rem;">Your plan is tailored to your goals and preferences.</p>
              </div>
            </div>

            <div class="side-feature">
              <div class="side-feature-icon"><i class="bi bi-calendar-check"></i></div>
              <div>
                <p class="fw-semibold mb-0" style="font-size:1rem;">Daily guidance</p>
                <p class="text-muted mb-0" style="font-size:.95rem;">Get daily meal suggestions and calorie targets.</p>
              </div>
            </div>

            <div class="side-feature">
              <div class="side-feature-icon"><i class="bi bi-graph-up-arrow"></i></div>
              <div>
                <p class="fw-semibold mb-0" style="font-size:1rem;">Track progress</p>
                <p class="text-muted mb-0" style="font-size:.95rem;">Monitor your progress and achieve your goals.</p>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Tip bar -->
      <div class="tip-bar">
        <span style="font-size:1.5rem;">💡</span>
        <span><strong>Tip:</strong> Be realistic with your goals and duration. Consistency is the key to success!</span>
      </div>

    </div>
  </main>

  <footer id="footer" class="footer dark-background">
    <div class="container copyright text-center py-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">Smart Meal Planner</strong> <span>All Rights Reserved</span></p>
    </div>
  </footer>

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
  <script src="<?php echo $assetPrefix; ?>js/main.js"></script>
  <script>
    var RANGES = {
      'lose_weight':     { min: 1400, max: 1800, def: 1600, label: 'Weight loss: 1,400 – 1,800 kcal/day' },
      'maintain_weight': { min: 1800, max: 2200, def: 2000, label: 'Maintain weight: 1,800 – 2,200 kcal/day' },
      'gain_muscle':     { min: 2200, max: 2800, def: 2500, label: 'Gain muscle: 2,200 – 2,800 kcal/day' },
      'eat_healthy':     { min: 1600, max: 2200, def: 1800, label: 'Eat healthy: 1,600 – 2,200 kcal/day' },
    };

    var calInput  = document.getElementById('calories');
    var calHint   = document.getElementById('cal-hint');
    var calBar    = document.getElementById('cal-range-bar');
    var track     = document.getElementById('cal-slider-track');
    var fill      = document.getElementById('cal-slider-fill');
    var thumb     = document.getElementById('cal-slider-thumb');
    var objSelect = document.getElementById('objectif');
    var currentRange = null;

    function pct(val, r) {
      return Math.min(100, Math.max(0, ((val - r.min) / (r.max - r.min)) * 100));
    }

    function updateSliderUI(val) {
      if (!currentRange) return;
      var p = pct(val, currentRange);
      fill.style.width  = p + '%';
      thumb.style.left  = p + '%';
    }

    function updateHint(val, r) {
      calHint.innerHTML = '👉 ' + r.label +
        ' &nbsp;<span style="color:#2e7d32;font-weight:700;">' + val + ' kcal/day</span>';
    }

    function applyRange(obj) {
      var r = RANGES[obj];
      if (!r) {
        calHint.textContent = '👉 Default: 1,800 kcal/day';
        calBar.style.display = 'none';
        currentRange = null;
        return;
      }
      currentRange = r;
      calBar.style.display = '';
      var current = parseInt(calInput.value, 10);
      var val = (!current || current < r.min || current > r.max) ? r.def : current;
      calInput.value = val;
      updateSliderUI(val);
      updateHint(val, r);
    }

    // Drag logic
    function sliderSetFromEvent(e) {
      if (!currentRange) return;
      var rect = track.getBoundingClientRect();
      var clientX = e.touches ? e.touches[0].clientX : e.clientX;
      var ratio = Math.min(1, Math.max(0, (clientX - rect.left) / rect.width));
      var step  = 50;
      var raw   = currentRange.min + ratio * (currentRange.max - currentRange.min);
      var val   = Math.round(raw / step) * step;
      val = Math.min(currentRange.max, Math.max(currentRange.min, val));
      calInput.value = val;
      updateSliderUI(val);
      updateHint(val, currentRange);
    }

    var dragging = false;
    track.addEventListener('mousedown',  function(e) { dragging = true; sliderSetFromEvent(e); });
    document.addEventListener('mousemove', function(e) { if (dragging) sliderSetFromEvent(e); });
    document.addEventListener('mouseup',   function()  { dragging = false; });
    track.addEventListener('touchstart', function(e) { dragging = true; sliderSetFromEvent(e); }, { passive: true });
    document.addEventListener('touchmove', function(e) { if (dragging) sliderSetFromEvent(e); }, { passive: true });
    document.addEventListener('touchend',  function()  { dragging = false; });

    // Typing in input
    calInput.addEventListener('input', function() {
      if (!currentRange) return;
      var v = parseInt(this.value, 10);
      if (!isNaN(v)) { updateSliderUI(v); updateHint(v, currentRange); }
    });
    calInput.addEventListener('blur', function() {
      if (!currentRange) return;
      var v = parseInt(this.value, 10);
      if (isNaN(v) || v < currentRange.min) v = currentRange.min;
      else if (v > currentRange.max)        v = currentRange.max;
      this.value = v;
      updateSliderUI(v);
      updateHint(v, currentRange);
    });

    // Objective change
    objSelect.addEventListener('change', function() { applyRange(this.value); });

    // Quick-select buttons
    document.querySelectorAll('.obj-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        document.querySelectorAll('.obj-btn').forEach(function(b) { b.classList.remove('selected'); });
        this.classList.add('selected');
        objSelect.value = this.dataset.val;
        applyRange(this.dataset.val);
      });
    });

    applyRange(objSelect.value);

    <?php if ($isEditing) : ?>
    document.getElementById('btn-delete-plan').addEventListener('click', function() {
      new bootstrap.Modal(document.getElementById('deletePlanModal')).show();
    });
    <?php endif; ?>
  </script>
</body>
</html>
