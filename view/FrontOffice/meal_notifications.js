/**
 * Smart Meal Planner — Meal Time Notifications
 * Shows browser notifications at meal times reminding the user to eat.
 * Only runs on front office pages.
 */
(function () {
  'use strict';

  var MEAL_TIMES = [
    { type: 'breakfast', label: 'Breakfast',  hour: 8,  minute: 0,  icon: '☀️', msg: 'Time for breakfast! Start your day with a healthy meal.' },
    { type: 'lunch',     label: 'Lunch',      hour: 12, minute: 30, icon: '🥗', msg: 'Lunch time! Don\'t skip your midday meal.' },
    { type: 'snack',     label: 'Snack',      hour: 16, minute: 0,  icon: '🍎', msg: 'Snack time! A light bite keeps your energy up.' },
    { type: 'dinner',    label: 'Dinner',      hour: 19, minute: 0,  icon: '🍽️', msg: 'Dinner time! End your day with a nutritious meal.' },
  ];

  // In-page toast notification (always works, no permission needed)
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
        '<p style="font-weight:700;margin:0 0 .2rem;font-size:.95rem;color:#212529;">' + meal.label + ' Time! 🔔</p>' +
        '<p style="margin:0;font-size:.85rem;color:#666;">' + meal.msg + '</p>' +
        '<a href="day_plan.php" style="font-size:.8rem;color:#ce1212;font-weight:600;text-decoration:none;display:inline-block;margin-top:.4rem;">View Today\'s Plan →</a>' +
      '</div>' +
      '<button onclick="this.parentElement.remove()" style="background:none;border:none;color:#aaa;font-size:1.1rem;cursor:pointer;padding:0;line-height:1;flex-shrink:0;">✕</button>';

    container.appendChild(toast);

    // Animate in
    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
      });
    });

    // Auto-dismiss after 8 seconds
    setTimeout(function () {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(40px)';
      setTimeout(function () { toast.remove(); }, 350);
    }, 8000);
  }

  // Browser push notification (if permission granted)
  function showBrowserNotif(meal) {
    if (Notification.permission === 'granted') {
      new Notification('🍽️ ' + meal.label + ' Time!', {
        body: meal.msg,
        icon: '/3rdV/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/view/assets/img/logo-smp.jpg',
        tag: 'meal-' + meal.type,
        requireInteraction: false
      });
    }
  }

  // Check if a notification was already shown today
  function wasShownToday(type) {
    var key = 'meal_notif_' + type + '_' + new Date().toISOString().slice(0, 10);
    return localStorage.getItem(key) === '1';
  }

  function markShown(type) {
    var key = 'meal_notif_' + type + '_' + new Date().toISOString().slice(0, 10);
    localStorage.setItem(key, '1');
  }

  // Main check — runs every minute
  function checkMealTimes() {
    var now = new Date();
    var h = now.getHours();
    var min = now.getMinutes();

    MEAL_TIMES.forEach(function (meal) {
      if (h === meal.hour && min === meal.minute && !wasShownToday(meal.type)) {
        markShown(meal.type);
        showToast(meal);
        showBrowserNotif(meal);
      }
    });
  }

  // Request notification permission and start checking
  function init() {
    // Request browser notification permission
    if ('Notification' in window && Notification.permission === 'default') {
      Notification.requestPermission();
    }

    // Check immediately on load (in case user opens page at meal time)
    checkMealTimes();

    // Check every minute
    setInterval(checkMealTimes, 60000);
  }

  // Start when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
