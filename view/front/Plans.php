<?php
require_once __DIR__ . '/../../controller/PlanController.php';

$plan        = Plan::first();
$assetPrefix = '../assets/';

require_once __DIR__ . '/header.php';
?>

<style>
  .plan-card       { border-radius:16px;border:1px solid #f0f0f0;background:#fff; }
  .plan-icon-wrap  { width:72px;height:72px;border-radius:12px;background:#fff3f3;display:flex;align-items:center;justify-content:center;font-size:2.2rem;flex-shrink:0; }
  .plan-badge      { display:inline-block;background:#e8f5e9;color:#2e7d32;font-size:.9rem;font-weight:600;padding:.25rem .75rem;border-radius:20px; }
  .plan-meta       { font-size:1rem;color:#444; }
  .plan-meta i     { color:#ce1212; }
  .progress-bar-custom { height:12px;border-radius:6px;background:#ce1212; }
  .progress-track  { height:12px;border-radius:6px;background:#f0f0f0;overflow:hidden; }
  .summary-box     { background:#fff8f8;border-radius:12px;padding:1.5rem 1.75rem; }
  .summary-row     { display:flex;justify-content:space-between;align-items:center;padding:.65rem 0;border-bottom:1px solid #f5e5e5;font-size:1rem; }
  .summary-row:last-child { border-bottom:none; }
  .summary-val     { font-weight:700;font-size:1rem; }
  .summary-val.green { color:#2e7d32; }
  .why-box         { background:#fff8f8;border-radius:16px;padding:2.25rem; }
  .why-item        { text-align:center;font-size:1rem;color:#333; }
  .why-item .icon  { font-size:2.2rem;margin-bottom:.6rem; }
  .no-plan-img     { width:130px;height:130px;background:#fff0f0;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:3.5rem;flex-shrink:0; }
</style>

<main class="main">
  <section class="section light-background py-5">
    <div class="container" style="max-width:900px;">

      <?php if ($plan === null) : ?>
      <div class="plan-card p-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div>
            <h2 class="fw-bold mb-1" style="font-size:1.55rem;">Your Meal Plan</h2>
            <p class="text-muted mb-0">Stay on track with your personalized meal plan.</p>
          </div>
        </div>
        <div class="d-flex align-items-center gap-4 p-4 rounded-3" style="background:#fafafa;border:1px solid #f0f0f0;">
          <div class="no-plan-img">📋</div>
          <div>
            <h3 class="fw-bold mb-1" style="font-size:1.35rem;">You don't have a plan yet!</h3>
            <p class="text-muted mb-3">Create your personalized meal plan based on your goals and daily calorie target.</p>
            <a href="create_plan.php" class="btn btn-danger rounded-pill px-4">
              <i class="bi bi-plus-lg me-1"></i> Create Your Plan
            </a>
          </div>
        </div>
      </div>

      <?php else :
        $progress = $plan->progressPercent();
        $elapsed  = $plan->daysElapsed();
      ?>
      <div class="plan-card p-4 mb-4">
        <h2 class="fw-bold mb-3" style="font-size:1.55rem;">Your Current Plan</h2>
        <div class="row g-4">
          <div class="col-md-4">
            <div class="d-flex align-items-start gap-3">
              <div class="plan-icon-wrap">🎯</div>
              <div>
                <h3 class="fw-bold mb-1" style="color:#ce1212;font-size:1.25rem;"><?php echo htmlspecialchars($plan->nom); ?></h3>
                <span class="plan-badge">Active</span>
                <div class="plan-meta mt-2 d-flex flex-column gap-1">
                  <?php if ($plan->objectif): ?><span><i class="bi bi-bullseye"></i> <?php echo htmlspecialchars($plan->objectif); ?></span><?php endif; ?>
                  <span><i class="bi bi-calendar3"></i> <?php echo $plan->duree; ?> Days</span>
                  <?php if ($plan->dateDebut): ?><span><i class="bi bi-play-circle"></i> <?php echo date('M j, Y', strtotime($plan->dateDebut)); ?></span><?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4 d-flex flex-column justify-content-center">
            <div class="d-flex justify-content-between mb-1">
              <span class="fw-semibold">Progress</span>
              <span style="color:#ce1212;font-weight:600;"><?php echo $elapsed; ?> / <?php echo $plan->duree; ?> days</span>
            </div>
            <div class="progress-track mb-2">
              <div class="progress-bar-custom" style="width:<?php echo $progress; ?>%;"></div>
            </div>
            <p class="text-muted mb-3"><?php echo $progress >= 100 ? '🎉 Plan complete!' : ($progress >= 50 ? 'Great job! Keep going.' : 'You\'re getting started!'); ?></p>
            <a href="view_plan.php" class="btn btn-outline-danger rounded-pill px-4" style="width:fit-content;">
              <i class="bi bi-calendar-check me-1"></i> View Plan
            </a>
          </div>
          <div class="col-md-4">
            <div class="summary-box">
              <p class="fw-bold mb-2">Plan Summary</p>
              <div class="summary-row"><span>📅 Start</span><span class="summary-val"><?php echo $plan->dateDebut ? date('M j', strtotime($plan->dateDebut)) : '—'; ?></span></div>
              <div class="summary-row"><span>🏁 End</span><span class="summary-val"><?php echo $plan->dateFin ? date('M j', strtotime($plan->dateFin)) : '—'; ?></span></div>
              <div class="summary-row"><span>✅ Days Done</span><span class="summary-val green"><?php echo $elapsed; ?> / <?php echo $plan->duree; ?></span></div>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div class="why-box">
        <h3 class="text-center fw-bold mb-4" style="color:#ce1212;font-size:1.25rem;">Why having a plan helps?</h3>
        <div class="row g-3 text-center">
          <div class="col-6 col-md-3 why-item"><div class="icon">🎯</div><p>Stay on track with your goals</p></div>
          <div class="col-6 col-md-3 why-item"><div class="icon">🔥</div><p>Control your daily calorie intake</p></div>
          <div class="col-6 col-md-3 why-item"><div class="icon">🥗</div><p>Eat balanced and healthy meals</p></div>
          <div class="col-6 col-md-3 why-item"><div class="icon">❤️</div><p>Build healthy habits for life</p></div>
        </div>
      </div>

    </div>
  </section>
</main>

<script src="../assets/js/meal_notifications.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/meal_notifications.js'); ?>"></script>
<?php require_once __DIR__ . '/footer.php'; ?>
