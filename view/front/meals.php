<?php

require_once __DIR__ . '/../../controller/MealController.php';
require_once __DIR__ . '/../../model/Plan.php';

$meals       = MealController::listMeals();
$assetPrefix = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST']
    . rtrim(str_replace('\\', '/', str_replace(
        str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])),
        '',
        str_replace('\\', '/', realpath(__DIR__ . '/../../'))
    )), '/') . '/view/assets/';

$favouriteIds = MealController::getFavouriteIds();
usort($meals, function($a, $b) use ($favouriteIds) {
    $aFav = in_array($a->id, $favouriteIds) ? 0 : 1;
    $bFav = in_array($b->id, $favouriteIds) ? 0 : 1;
    return $aFav - $bFav;
});

$searchQuery = trim($_GET['q']        ?? '');
$searchBy    = trim($_GET['searchBy'] ?? 'name');
if ($searchQuery !== '') $meals = MealController::searchMeals($searchQuery, $searchBy);

$autoFilter = trim($_GET['filter'] ?? '');
$plan       = Plan::first();
$planStart  = $plan ? $plan->dateDebut : date('Y-m-d');
$planEnd    = $plan ? $plan->dateFin   : date('Y-m-d', strtotime('+7 days'));

$planData = [];
foreach (MealController::listMealsWithPlan() as $row) {
    $planData[(int)$row['id_meal']] = ['plan_name'=>(string)($row['plan_name']??''),'objectif'=>(string)($row['objectif']??'')];
}

function resolveImageUrl(string $image, string $prefix): string {
    return $prefix . ltrim(preg_replace('#^assets/#', '', $image), '/');
}

$stats = ['breakfast'=>0,'lunch'=>0,'dinner'=>0,'snack'=>0];
foreach (MealController::listMeals() as $m) { if (isset($stats[$m->mealType])) $stats[$m->mealType]++; }
$total = array_sum($stats);

require_once __DIR__ . '/header.php';
?>

<link href="<?php echo $assetPrefix; ?>css/meals.css" rel="stylesheet">

<main class="main">
  <section id="meals" class="meals-module section light-background">
    <div class="container section-title">
      <h2>Meals</h2>
      <p><span>Browse</span> <span class="description-title">Your meal gallery</span></p>
    </div>
    <div class="container">

      <!-- Search -->
      <form method="GET" action="Meals.php" class="mb-4">
        <div class="d-flex flex-wrap gap-2 justify-content-center align-items-center">
          <div class="input-group" style="max-width:480px;">
            <input type="text" class="form-control" name="q" placeholder="Search meals..."
              value="<?php echo htmlspecialchars($searchQuery); ?>"
              style="border-radius:50px 0 0 50px;border-color:#ce1212;">
            <select name="searchBy" class="form-select" style="max-width:130px;border-color:#ce1212;border-left:0;">
              <option value="name"     <?php echo $searchBy==='name'    ?'selected':''; ?>>Name</option>
              <option value="calories" <?php echo $searchBy==='calories'?'selected':''; ?>>Calories</option>
            </select>
            <button class="btn btn-danger" type="submit" style="border-radius:0 50px 50px 0;">Search</button>
          </div>
          <?php if ($searchQuery !== ''): ?><a href="Meals.php" class="btn btn-outline-secondary rounded-pill">Clear</a><?php endif; ?>
        </div>
        <?php if ($searchQuery !== ''): ?>
        <p class="text-center text-muted mt-2" style="font-size:.9rem;"><?php echo count($meals); ?> result(s) for "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>"</p>
        <?php endif; ?>
      </form>

      <!-- Filters -->
      <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
        <button class="btn btn-danger rounded-pill meal-filter active" data-filter="all">All</button>
        <button class="btn btn-outline-danger rounded-pill meal-filter" data-filter="breakfast">Breakfast</button>
        <button class="btn btn-outline-danger rounded-pill meal-filter" data-filter="lunch">Lunch</button>
        <button class="btn btn-outline-danger rounded-pill meal-filter" data-filter="dinner">Dinner</button>
        <button class="btn btn-outline-danger rounded-pill meal-filter" data-filter="snack">Snack</button>
        <button class="btn btn-outline-success rounded-pill meal-filter" data-filter="low-cal">Low Calories</button>
      </div>

      <!-- Meal grid -->
      <div class="row g-4" id="meal-grid">
        <?php foreach ($meals as $meal):
          $imgSrc      = htmlspecialchars(resolveImageUrl($meal->image, $assetPrefix), ENT_QUOTES, 'UTF-8');
          $safeName    = htmlspecialchars($meal->name, ENT_QUOTES, 'UTF-8');
          $safeDesc    = htmlspecialchars($meal->description, ENT_QUOTES, 'UTF-8');
          $safeRecipe  = htmlspecialchars($meal->recipeUrl, ENT_QUOTES, 'UTF-8');
          $safeType    = htmlspecialchars($meal->mealType, ENT_QUOTES, 'UTF-8');
          $safeLabel   = htmlspecialchars($meal->mealTypeLabel(), ENT_QUOTES, 'UTF-8');
          $isFav       = in_array($meal->id, $favouriteIds);
        ?>
        <div class="col-lg-3 col-md-4 col-sm-6">
          <article class="meal-card"
            data-meal-id="<?php echo (int)$meal->id; ?>"
            data-meal-name="<?php echo $safeName; ?>"
            data-meal-calories="<?php echo (int)$meal->calories; ?>"
            data-meal-description="<?php echo $safeDesc; ?>"
            data-meal-image="<?php echo $imgSrc; ?>"
            data-meal-recipe="<?php echo $safeRecipe; ?>"
            data-meal-type="<?php echo $safeType; ?>"
            data-meal-type-label="<?php echo $safeLabel; ?>"
            style="position:relative;">
            <button class="fav-btn <?php echo $isFav?'active':''; ?>"
              onclick="toggleFav(event,this,<?php echo (int)$meal->id; ?>)"
              style="position:absolute;top:.6rem;right:.6rem;background:none;border:none;cursor:pointer;font-size:1.3rem;z-index:2;">
              <?php echo $isFav?'❤️':'🤍'; ?>
            </button>
            <div class="meal-card__media"><img src="<?php echo $imgSrc; ?>" alt="<?php echo $safeName; ?>" loading="lazy"></div>
            <div class="meal-card__body">
              <h3 class="meal-card__name"><?php echo $safeName; ?></h3>
              <p class="meal-card__calories"><strong><?php echo (int)$meal->calories; ?></strong> kcal</p>
              <?php if ($isFav): ?><span style="font-size:.75rem;color:#ce1212;font-weight:600;">❤️ Favourite</span><?php endif; ?>
            </div>
          </article>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Pie chart -->
      <div class="container mt-5 mb-4">
        <div class="text-center mb-4">
          <h3 style="font-size:1.4rem;font-weight:700;">Meal Type Distribution</h3>
          <p class="text-muted" style="font-size:.95rem;">Live breakdown of all <?php echo $total; ?> meals</p>
        </div>
        <div class="row align-items-center justify-content-center g-4">
          <div class="col-md-4 text-center">
            <canvas id="mealPieChart" width="260" height="260" style="max-width:260px;"></canvas>
          </div>
          <div class="col-md-4">
            <?php
              $colors = ['breakfast'=>'#f59e0b','lunch'=>'#10b981','dinner'=>'#ce1212','snack'=>'#6366f1'];
              $icons  = ['breakfast'=>'☀️','lunch'=>'🥗','dinner'=>'🍽️','snack'=>'🍎'];
              foreach ($stats as $type => $count):
                $pct = $total > 0 ? round(($count/$total)*100) : 0;
            ?>
            <div class="d-flex align-items-center gap-3 mb-3">
              <span style="font-size:1.4rem;"><?php echo $icons[$type]; ?></span>
              <div style="flex:1;">
                <div class="d-flex justify-content-between mb-1">
                  <span style="font-weight:600;font-size:.95rem;"><?php echo ucfirst($type); ?></span>
                  <span style="font-weight:700;color:<?php echo $colors[$type]; ?>;"><?php echo $count; ?> (<?php echo $pct; ?>%)</span>
                </div>
                <div style="height:8px;background:#f0f0f0;border-radius:4px;overflow:hidden;">
                  <div style="height:8px;background:<?php echo $colors[$type]; ?>;border-radius:4px;width:<?php echo $pct; ?>%;"></div>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

    </div>
  </section>
</main>

<!-- Meal detail modal -->
<div class="modal fade" id="mealDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title fs-5" style="font-size:1.15rem!important;font-weight:700;letter-spacing:.04em;text-transform:uppercase;">Meal details</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="meal-detail__top">
          <div class="meal-detail__image-wrap"><img src="" alt="" data-meal-detail="image"></div>
          <div class="meal-detail__info-top">
            <h3 class="meal-detail__title" data-meal-detail="name"></h3>
            <p class="meal-detail__calories" data-meal-detail="calories"></p>
          </div>
        </div>
        <div class="meal-detail__bottom">
          <div class="meal-detail__copy">
            <p class="meal-detail__type" data-meal-detail="type"></p>
            <p class="meal-detail__description" data-meal-detail="description"></p>
          </div>
          <div class="meal-detail__actions">
            <a class="btn btn-meal-recipe" data-meal-detail="recipe" href="#" rel="noopener noreferrer">Recipe</a>
            <button type="button" class="btn btn-meal-add" data-meal-detail="add" title="Add to plan"><i class="bi bi-plus-lg"></i></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Day picker modal -->
<div class="modal fade" id="dayPickerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
    <div class="modal-content" style="border-radius:16px;">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Choose a day</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <p class="text-muted mb-3" style="font-size:.9rem;">Which day should <strong id="dp-meal-name"></strong> be added to?</p>
        <div id="dp-days" class="d-flex flex-column gap-2"></div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
  window.PLAN_START   = '<?php echo $planStart; ?>';
  window.PLAN_END     = '<?php echo $planEnd; ?>';
  window.AUTO_FILTER  = '<?php echo htmlspecialchars($autoFilter); ?>';
  window.REPLACE_DATE = '<?php echo htmlspecialchars($_GET['date'] ?? ''); ?>';

  function toggleFav(e, btn, mealId) {
    e.stopPropagation();
    var fd = new FormData(); fd.append('meal_id', mealId);
    fetch('toggle_favourite.php', { method:'POST', body:fd })
      .then(r=>r.json()).then(d=>{ if(d.ok){ btn.textContent=d.favourited?'❤️':'🤍'; setTimeout(()=>window.location.reload(),300); }});
  }

  new Chart(document.getElementById('mealPieChart'), {
    type: 'doughnut',
    data: { labels:['Breakfast','Lunch','Dinner','Snack'],
      datasets:[{ data:[<?php echo $stats['breakfast']; ?>,<?php echo $stats['lunch']; ?>,<?php echo $stats['dinner']; ?>,<?php echo $stats['snack']; ?>],
        backgroundColor:['#f59e0b','#10b981','#ce1212','#6366f1'], borderWidth:3, borderColor:'#fff', hoverOffset:8 }]
    },
    options:{ cutout:'65%', plugins:{ legend:{display:false} } }
  });

  document.querySelectorAll('.meal-filter').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.meal-filter').forEach(function(b) {
        b.classList.remove('active','btn-danger','btn-success');
        b.classList.add(b.dataset.filter==='low-cal'?'btn-outline-success':'btn-outline-danger');
      });
      this.classList.add('active');
      this.classList.remove('btn-outline-danger','btn-outline-success');
      this.classList.add(this.dataset.filter==='low-cal'?'btn-success':'btn-danger');
      var filter = this.dataset.filter;
      document.querySelectorAll('#meal-grid .col-lg-3').forEach(function(col) {
        var card = col.querySelector('.meal-card');
        var show = filter==='all'||filter===card.dataset.mealType||(filter==='low-cal'&&parseInt(card.dataset.mealCalories)<400);
        col.style.display = show?'':'none';
      });
    });
  });

  window.addEventListener('load', function() {
    var f = window.AUTO_FILTER;
    if (f) { var btn = document.querySelector('.meal-filter[data-filter="'+f+'"]'); if(btn) btn.click(); }
  });
</script>
<script src="../assets/js/meals.js?v=<?php echo time(); ?>"></script>
<script src="../assets/js/meal_notifications.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/meal_notifications.js'); ?>"></script>

<?php require_once __DIR__ . '/footer.php'; ?>

<script>
/* Meal card click → detail modal — runs after Bootstrap is loaded by footer */
(function () {
  // Always clean up backdrop/body-lock whenever ANY modal finishes hiding
  function cleanModalState() {
    document.querySelectorAll('.modal-backdrop').forEach(function(el) { el.remove(); });
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
  }
  document.addEventListener('hidden.bs.modal', cleanModalState);

  var modalEl = document.getElementById('mealDetailModal');
  if (!modalEl) return;
  var modal = new bootstrap.Modal(modalEl);

  function fillModal(card) {
    modalEl.querySelector('[data-meal-detail="image"]').src = card.dataset.mealImage || '';
    modalEl.querySelector('[data-meal-detail="image"]').alt = card.dataset.mealName || '';
    modalEl.querySelector('[data-meal-detail="name"]').textContent = card.dataset.mealName || '';
    modalEl.querySelector('[data-meal-detail="calories"]').textContent = card.dataset.mealCalories ? card.dataset.mealCalories + ' kcal' : '';
    var typeEl = modalEl.querySelector('[data-meal-detail="type"]');
    typeEl.textContent = card.dataset.mealTypeLabel || '';
    typeEl.className = 'meal-detail__type';
    var t = card.dataset.mealType;
    if (t === 'breakfast' || t === 'lunch' || t === 'dinner' || t === 'snack') typeEl.classList.add('meal-detail__type--' + t);
    modalEl.querySelector('[data-meal-detail="description"]').textContent = card.dataset.mealDescription || '';
    var recipeBtn = modalEl.querySelector('[data-meal-detail="recipe"]');
    recipeBtn.href = card.dataset.mealRecipe || '#';
    var addBtn = modalEl.querySelector('[data-meal-detail="add"]');
    addBtn.setAttribute('data-meal-id', card.dataset.mealId || '');
    addBtn.setAttribute('data-meal-type', card.dataset.mealType || '');
  }

  document.querySelectorAll('.meal-card[data-meal-id]').forEach(function (card) {
    card.style.cursor = 'pointer';
    card.addEventListener('click', function (e) {
      // Don't open modal when clicking the fav button
      if (e.target.closest('.fav-btn')) return;
      fillModal(card);
      modal.show();
    });
  });

  var addBtn = modalEl.querySelector('[data-meal-detail="add"]');
  if (addBtn) {
    addBtn.addEventListener('click', function () {
      var id = addBtn.getAttribute('data-meal-id');
      var name = modalEl.querySelector('[data-meal-detail="name"]').textContent;
      var mealType = addBtn.getAttribute('data-meal-type') || '';
      if (!mealType) return;
      modal.hide();
      showDayPickerModal(id, name, mealType);
    });
  }

  function showDayPickerModal(mealId, mealName, mealType) {
    var dpModal = new bootstrap.Modal(document.getElementById('dayPickerModal'));
    document.getElementById('dp-meal-name').textContent = mealName;
    var container = document.getElementById('dp-days');
    container.innerHTML = '';
    var start = new Date(window.PLAN_START || new Date());
    var end   = new Date(window.PLAN_END   || new Date(start.getTime() + 13 * 86400000));
    var today = new Date().toISOString().slice(0, 10);
    var days   = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    for (var d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
      (function(dateStr) {
        var isToday = dateStr === today;
        var label = days[d.getDay()] + ', ' + months[d.getMonth()] + ' ' + d.getDate();
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.style.cssText = 'border:1.5px solid ' + (isToday ? '#ce1212' : '#eee') + ';background:' + (isToday ? '#fff8f8' : '#fff') + ';border-radius:10px;padding:.6rem 1rem;text-align:left;cursor:pointer;font-size:.9rem;font-weight:' + (isToday ? '700' : '500') + ';color:#212529;transition:.15s;';
        btn.innerHTML = label + (isToday ? ' <span style="color:#ce1212;font-size:.8rem;margin-left:.4rem;">Today</span>' : '');
        btn.addEventListener('click', function () { dpModal.hide(); saveMealToDay(mealId, mealType, mealName, dateStr); });
        container.appendChild(btn);
      })(d.toISOString().slice(0, 10));
    }
    dpModal.show();
  }

  function saveMealToDay(mealId, mealType, mealName, date) {
    var fd = new FormData();
    fd.append('meal_id', mealId);
    fd.append('meal_type', mealType);
    fd.append('meal_date', date);
    fetch('plan_add_meal.php', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.ok) {
          // Force-clean any lingering modal backdrop before navigating
          document.querySelectorAll('.modal-backdrop').forEach(function(el) { el.remove(); });
          document.body.classList.remove('modal-open');
          document.body.style.removeProperty('overflow');
          document.body.style.removeProperty('padding-right');

          // If coming from Replace button, go back to that day's plan page
          var replaceDate = window.REPLACE_DATE || date;
          window.location.href = 'day_plan.php?date=' + encodeURIComponent(replaceDate);
        } else {
          showMealToast('⚠ ' + (data.message || 'Could not add meal.'));
        }
      })
      .catch(function(err) { showMealToast('⚠ Network error: ' + err); });
  }

  function showMealToast(message) {
    var c = document.getElementById('plan-toast-container');
    if (!c) { c = document.createElement('div'); c.id = 'plan-toast-container'; c.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem;'; document.body.appendChild(c); }
    var t = document.createElement('div');
    t.style.cssText = 'background:#ce1212;color:#fff;padding:.75rem 1.25rem;border-radius:.5rem;box-shadow:0 4px 12px rgba(0,0,0,.2);font-size:.95rem;opacity:0;transition:opacity .3s;';
    t.textContent = message;
    c.appendChild(t);
    requestAnimationFrame(function() { t.style.opacity = '1'; });
    setTimeout(function() { t.style.opacity = '0'; setTimeout(function() { t.remove(); }, 300); }, 3000);
  }
})();
</script>
