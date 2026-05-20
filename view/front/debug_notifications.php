<?php
session_start();
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Notification Debug — SmartMealPlanner</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', sans-serif; background: #f4f6fb; padding: 2rem; color: #212529; }
    h1 { font-size: 1.6rem; font-weight: 700; margin-bottom: .4rem; color: #ce1212; }
    .subtitle { color: #6b7280; font-size: .9rem; margin-bottom: 2rem; }

    .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; }

    .card { background: #fff; border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 18px rgba(0,0,0,.07); border: 1px solid #e5e7eb; }
    .card h2 { font-size: 1rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: .5rem; }
    .card h2 span { font-size: 1.3rem; }

    .row { display: flex; justify-content: space-between; align-items: center; padding: .5rem 0; border-bottom: 1px solid #f3f4f6; font-size: .88rem; }
    .row:last-child { border-bottom: none; }
    .label { color: #6b7280; font-weight: 600; }
    .val  { font-weight: 700; }
    .ok   { color: #15803d; }
    .warn { color: #b45309; }
    .err  { color: #ce1212; }

    .btn { display: inline-flex; align-items: center; gap: .4rem; background: #ce1212; color: #fff; border: none; border-radius: 999px; padding: .55rem 1.2rem; font-size: .85rem; font-weight: 600; cursor: pointer; transition: .2s; margin: .3rem .3rem 0 0; }
    .btn:hover { background: #b51010; }
    .btn.green { background: #15803d; }
    .btn.green:hover { background: #166534; }
    .btn.grey  { background: #6b7280; }
    .btn.grey:hover  { background: #4b5563; }

    #log { background: #0f172a; color: #94a3b8; border-radius: 12px; padding: 1rem 1.2rem; font-family: monospace; font-size: .8rem; line-height: 1.7; min-height: 120px; max-height: 260px; overflow-y: auto; margin-top: 1rem; }
    #log .ts  { color: #475569; }
    #log .ok  { color: #4ade80; }
    #log .err { color: #f87171; }
    #log .inf { color: #60a5fa; }

    .meal-row { display: flex; justify-content: space-between; align-items: center; padding: .55rem 0; border-bottom: 1px solid #f3f4f6; font-size: .88rem; }
    .meal-row:last-child { border-bottom: none; }
    .meal-name { font-weight: 600; }
    .meal-time { color: #6b7280; font-size: .82rem; }
    .meal-status { font-size: .78rem; font-weight: 700; padding: 3px 10px; border-radius: 999px; }
    .fired   { background: #dcfce7; color: #15803d; }
    .pending { background: #fef9c3; color: #854d0e; }
    .future  { background: #f3f4f6; color: #6b7280; }
  </style>
</head>
<body>

<h1>🔔 Notification Debug Panel</h1>
<p class="subtitle">Real-time diagnostics for the SmartMealPlanner meal notification system.</p>

<div class="grid">

  <!-- Environment -->
  <div class="card">
    <h2><span>🖥️</span> Environment</h2>
    <div class="row"><span class="label">PHP Version</span><span class="val ok"><?= PHP_VERSION ?></span></div>
    <div class="row"><span class="label">Server Time</span><span class="val" id="serverTime"><?= date('H:i:s') ?></span></div>
    <div class="row"><span class="label">Server Date</span><span class="val"><?= date('Y-m-d') ?></span></div>
    <div class="row"><span class="label">Timezone</span><span class="val"><?= date_default_timezone_get() ?></span></div>
    <div class="row"><span class="label">Session Active</span>
      <span class="val <?= session_status() === PHP_SESSION_ACTIVE ? 'ok' : 'err' ?>">
        <?= session_status() === PHP_SESSION_ACTIVE ? 'YES ✓' : 'NO ✗' ?>
      </span>
    </div>
    <div class="row"><span class="label">User Logged In</span>
      <span class="val <?= isset($_SESSION['user_id']) ? 'ok' : 'warn' ?>">
        <?= isset($_SESSION['user_id']) ? 'YES — ID '.$_SESSION['user_id'] : 'NO (not logged in)' ?>
      </span>
    </div>
  </div>

  <!-- JS File Check -->
  <div class="card">
    <h2><span>📁</span> JS File Paths</h2>
    <?php
    $files = [
      'view/assets/js/meal_notifications.js'       => __DIR__ . '/../assets/js/meal_notifications.js',
      '_project_files/assets/js/meal_notifications.js' => __DIR__ . '/../../_project_files/assets/js/meal_notifications.js',
    ];
    foreach ($files as $label => $path):
      $exists = file_exists($path);
      $size   = $exists ? filesize($path) . ' bytes' : '—';
      $mtime  = $exists ? date('H:i:s d/m/Y', filemtime($path)) : '—';
    ?>
    <div class="row">
      <span class="label" style="max-width:200px;word-break:break-all;"><?= $label ?></span>
      <span class="val <?= $exists ? 'ok' : 'err' ?>"><?= $exists ? '✓ exists' : '✗ MISSING' ?></span>
    </div>
    <?php if ($exists): ?>
    <div class="row"><span class="label" style="padding-left:1rem;">Size</span><span class="val"><?= $size ?></span></div>
    <div class="row"><span class="label" style="padding-left:1rem;">Modified</span><span class="val"><?= $mtime ?></span></div>
    <?php endif; endforeach; ?>

    <?php
    // Check if both files are identical
    $f1 = __DIR__ . '/../assets/js/meal_notifications.js';
    $f2 = __DIR__ . '/../../_project_files/assets/js/meal_notifications.js';
    if (file_exists($f1) && file_exists($f2)):
      $same = md5_file($f1) === md5_file($f2);
    ?>
    <div class="row">
      <span class="label">Files in sync</span>
      <span class="val <?= $same ? 'ok' : 'err' ?>"><?= $same ? '✓ Identical' : '✗ OUT OF SYNC — fix needed' ?></span>
    </div>
    <?php endif; ?>
  </div>

  <!-- MEAL_TIMES config parsed from file -->
  <div class="card">
    <h2><span>⏰</span> Configured Meal Times</h2>
    <?php
    $jsContent = file_exists($f1) ? file_get_contents($f1) : '';
    preg_match_all('/\{\s*type:\s*\'(\w+)\'.*?hour:\s*(\d+).*?minute:\s*(\d+)/s', $jsContent, $matches, PREG_SET_ORDER);
    $now = new DateTime();
    $nowMins = (int)$now->format('H') * 60 + (int)$now->format('i');
    foreach ($matches as $m):
      $type = $m[1]; $h = (int)$m[2]; $min = (int)$m[3];
      $mealMins = $h * 60 + $min;
      $diff = $mealMins - $nowMins;
      if ($diff < -5)       { $status = 'fired';   $statusLabel = 'Past'; }
      elseif ($diff <= 5)   { $status = 'pending'; $statusLabel = 'NOW ±5min'; }
      else                  { $status = 'future';  $statusLabel = 'Upcoming'; }
    ?>
    <div class="meal-row">
      <span class="meal-name"><?= ucfirst($type) ?></span>
      <span class="meal-time"><?= sprintf('%02d:%02d', $h, $min) ?></span>
      <span class="meal-status <?= $status ?>"><?= $statusLabel ?></span>
    </div>
    <?php endforeach; ?>
    <div class="row" style="margin-top:.5rem;">
      <span class="label">Current server time</span>
      <span class="val"><?= $now->format('H:i') ?></span>
    </div>
  </div>

  <!-- Browser / JS Tests -->
  <div class="card">
    <h2><span>🧪</span> Browser Tests</h2>
    <div class="row"><span class="label">Notification API</span><span class="val" id="notifApi">checking…</span></div>
    <div class="row"><span class="label">Permission</span><span class="val" id="notifPerm">checking…</span></div>
    <div class="row"><span class="label">MealNotif global</span><span class="val" id="globalCheck">checking…</span></div>
    <div class="row"><span class="label">localStorage</span><span class="val" id="lsCheck">checking…</span></div>

    <div style="margin-top:1rem;">
      <button class="btn" onclick="testToast('snack')">🍎 Test Snack Toast</button>
      <button class="btn" onclick="testToast('breakfast')">☀️ Test Breakfast</button>
      <button class="btn" onclick="testToast('lunch')">🥗 Test Lunch</button>
      <button class="btn" onclick="testToast('dinner')">🍽️ Test Dinner</button>
    </div>
    <div style="margin-top:.5rem;">
      <button class="btn green" onclick="requestPerm()">🔔 Request Browser Permission</button>
      <button class="btn grey"  onclick="resetAll()">🗑️ Reset All Today's Flags</button>
      <button class="btn grey"  onclick="showFlags()">📋 Show localStorage Flags</button>
    </div>

    <div id="log"><span class="ts">[ready]</span> Debug panel loaded. Run tests above.</div>
  </div>

</div>

<!-- Load the actual notification script -->
<script src="../assets/js/meal_notifications.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/meal_notifications.js'); ?>"></script>

<script>
  var log = document.getElementById('log');
  function addLog(msg, type) {
    var ts = new Date().toLocaleTimeString();
    var cls = type || 'inf';
    log.innerHTML += '\n<span class="ts">[' + ts + ']</span> <span class="' + cls + '">' + msg + '</span>';
    log.scrollTop = log.scrollHeight;
  }

  // Environment checks
  window.addEventListener('load', function () {
    // Notification API
    var notifApi = document.getElementById('notifApi');
    if ('Notification' in window) {
      notifApi.textContent = '✓ Supported';
      notifApi.className = 'val ok';
      addLog('Notification API: supported', 'ok');
    } else {
      notifApi.textContent = '✗ Not supported';
      notifApi.className = 'val err';
      addLog('Notification API: NOT supported in this browser', 'err');
    }

    // Permission
    var permEl = document.getElementById('notifPerm');
    var perm = 'Notification' in window ? Notification.permission : 'unsupported';
    permEl.textContent = perm;
    permEl.className = 'val ' + (perm === 'granted' ? 'ok' : perm === 'denied' ? 'err' : 'warn');
    addLog('Notification permission: ' + perm, perm === 'granted' ? 'ok' : 'inf');

    // MealNotif global
    var globalEl = document.getElementById('globalCheck');
    if (window.MealNotif && typeof window.MealNotif.test === 'function') {
      globalEl.textContent = '✓ Loaded';
      globalEl.className = 'val ok';
      addLog('MealNotif global: loaded correctly', 'ok');
    } else {
      globalEl.textContent = '✗ Not found — script failed to load';
      globalEl.className = 'val err';
      addLog('MealNotif global: MISSING — check script path', 'err');
    }

    // localStorage
    var lsEl = document.getElementById('lsCheck');
    try {
      localStorage.setItem('_test', '1');
      localStorage.removeItem('_test');
      lsEl.textContent = '✓ Available';
      lsEl.className = 'val ok';
      addLog('localStorage: available', 'ok');
    } catch(e) {
      lsEl.textContent = '✗ Blocked';
      lsEl.className = 'val err';
      addLog('localStorage: BLOCKED — ' + e.message, 'err');
    }

    // Show existing flags
    showFlags();
  });

  // Update server time display every second
  setInterval(function () {
    var el = document.getElementById('serverTime');
    if (el) {
      var d = new Date();
      el.textContent = d.toLocaleTimeString();
    }
  }, 1000);

  function testToast(type) {
    if (window.MealNotif && window.MealNotif.test) {
      MealNotif.reset(type);
      MealNotif.test(type);
      addLog('Fired test notification for: ' + type, 'ok');
    } else {
      addLog('ERROR: MealNotif not loaded — check script path', 'err');
    }
  }

  function requestPerm() {
    if (!('Notification' in window)) {
      addLog('Notification API not supported', 'err');
      return;
    }
    Notification.requestPermission().then(function (p) {
      document.getElementById('notifPerm').textContent = p;
      document.getElementById('notifPerm').className = 'val ' + (p === 'granted' ? 'ok' : p === 'denied' ? 'err' : 'warn');
      addLog('Permission result: ' + p, p === 'granted' ? 'ok' : 'err');
    });
  }

  function resetAll() {
    if (window.MealNotif) {
      MealNotif.reset();
      addLog('All today\'s notification flags cleared from localStorage', 'ok');
      showFlags();
    } else {
      addLog('MealNotif not loaded', 'err');
    }
  }

  function showFlags() {
    var today = new Date().toISOString().slice(0, 10);
    var types = ['breakfast', 'lunch', 'snack', 'dinner'];
    var found = [];
    types.forEach(function (t) {
      var val = localStorage.getItem('meal_notif_' + t + '_' + today);
      if (val) found.push(t + '=' + val);
    });
    if (found.length) {
      addLog('localStorage flags today: ' + found.join(', '), 'inf');
    } else {
      addLog('localStorage flags today: none (all clear — notifications will fire)', 'ok');
    }
  }
</script>

</body>
</html>
