/**
 * Smart Meal Planner - Meal Time Notifications
 * Shows browser notifications at meal times reminding the user to eat.
 */
(function () {
  'use strict';

  var MEAL_TIMES = [
    { type: 'breakfast', label: 'Breakfast', hour: 8,  minute: 0,  icon: '\u2600\uFE0F', msg: 'Time for breakfast! Start your day with a healthy meal.' },
    { type: 'lunch',     label: 'Lunch',     hour: 12, minute: 30, icon: '\uD83E\uDD57', msg: 'Lunch time! Don\'t skip your midday meal.' },
    { type: 'snack',     label: 'Snack',     hour: 14, minute: 0,  icon: '\uD83C\uDF4E', msg: 'Snack time! A light bite keeps your energy up.' },
    { type: 'dinner',    label: 'Dinner',    hour: 19, minute: 0,  icon: '\uD83C\uDF7D\uFE0F', msg: 'Dinner time! End your day with a nutritious meal.' }
  ];

  function showToast(meal) {
    var container = document.getElementById('meal-notif-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'meal-notif-container';
      container.style.cssText = 'position:fixed;top:80px;right:1.5rem;z-index:99999;display:flex;flex-direction:column;gap:.75rem;max-width:320px;';
      document.body.appendChild(container);
    }

    var toast = document.createElement('div');
    toast.style.cssText = [
      'background:#fff',
      'border-radius:14px',
      'box-shadow:0 8px 32px rgba(0,0,0,.15)',
      'padding:1rem 1.25rem',
      'display:flex',
      'align-items:flex-start',
      'gap:.75rem',
      'border-left:4px solid #ce1212',
      'opacity:0',
      'transform:translateX(40px)',
      'transition:all .35s ease',
      'cursor:pointer'
    ].join(';');

    toast.innerHTML =
      '<span style="font-size:1.8rem;flex-shrink:0;">' + meal.icon + '</span>' +
      '<div style="flex:1;">' +
        '<p style="font-weight:700;margin:0 0 .2rem;font-size:.95rem;color:#212529;">' + meal.label + ' Time! \uD83D\uDD14</p>' +
        '<p style="margin:0;font-size:.85rem;color:#666;">' + meal.msg + '</p>' +
        '<a href="day_plan.php" style="font-size:.8rem;color:#ce1212;font-weight:600;text-decoration:none;display:inline-block;margin-top:.4rem;">View Today\'s Plan \u2192</a>' +
      '</div>' +
      '<button onclick="this.parentElement.remove()" style="background:none;border:none;color:#aaa;font-size:1.1rem;cursor:pointer;padding:0;line-height:1;flex-shrink:0;">\u2715</button>';

    container.appendChild(toast);

    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
      });
    });

    setTimeout(function () {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(40px)';
      setTimeout(function () { if (toast.parentNode) toast.remove(); }, 350);
    }, 8000);
  }

  function showBrowserNotif(meal) {
    if (typeof Notification !== 'undefined' && Notification.permission === 'granted') {
      var scripts = document.querySelectorAll('script[src*="meal_notifications"]');
      var iconPath = scripts.length
        ? scripts[0].src.replace(/assets\/js\/meal_notifications\.js.*$/, 'assets/img/logo-smp.jpg')
        : '../assets/img/logo-smp.jpg';

      try {
        new Notification('\uD83C\uDF7D\uFE0F ' + meal.label + ' Time!', {
          body: meal.msg,
          icon: iconPath,
          tag: 'meal-' + meal.type,
          requireInteraction: false
        });
      } catch (e) {
        console.warn('[MealNotif] Browser notification failed:', e);
      }
    }
  }

  function wasShownToday(type) {
    try {
      var key = 'meal_notif_' + type + '_' + new Date().toISOString().slice(0, 10);
      return localStorage.getItem(key) === '1';
    } catch (e) { return false; }
  }

  function markShown(type) {
    try {
      var key = 'meal_notif_' + type + '_' + new Date().toISOString().slice(0, 10);
      localStorage.setItem(key, '1');
    } catch (e) {}
  }

  function checkMealTimes() {
    var now = new Date();
    var totalMinutes = now.getHours() * 60 + now.getMinutes();

    for (var i = 0; i < MEAL_TIMES.length; i++) {
      var meal = MEAL_TIMES[i];
      var mealMinutes = meal.hour * 60 + meal.minute;
      if (Math.abs(totalMinutes - mealMinutes) <= 5 && !wasShownToday(meal.type)) {
        markShown(meal.type);
        showToast(meal);
        showBrowserNotif(meal);
      }
    }
  }

  function init() {
    if (typeof Notification !== 'undefined' && Notification.permission === 'default') {
      Notification.requestPermission();
    }
    checkMealTimes();
    setInterval(checkMealTimes, 60000);
  }

  // Global helpers for testing/debugging
  window.MealNotif = {
    reset: function (type) {
      var today = new Date().toISOString().slice(0, 10);
      if (type) {
        try { localStorage.removeItem('meal_notif_' + type + '_' + today); } catch(e) {}
        console.log('[MealNotif] Reset ' + type + ' for today');
      } else {
        ['breakfast', 'lunch', 'snack', 'dinner'].forEach(function (t) {
          try { localStorage.removeItem('meal_notif_' + t + '_' + today); } catch(e) {}
        });
        console.log('[MealNotif] Reset all for today');
      }
    },
    test: function (type) {
      var meal = null;
      for (var i = 0; i < MEAL_TIMES.length; i++) {
        if (MEAL_TIMES[i].type === type) { meal = MEAL_TIMES[i]; break; }
      }
      if (!meal) { console.warn('[MealNotif] Unknown type: ' + type); return; }
      showToast(meal);
      showBrowserNotif(meal);
      console.log('[MealNotif] Test fired for: ' + type);
    },
    times: MEAL_TIMES
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
