/**
 * Meals module + minimal Yummy header chrome (avoids full main.js AOS/Swiper deps).
 */
(function () {
  'use strict';

  function toggleScrolled() {
    const body = document.querySelector('body');
    const header = document.querySelector('#header');
    if (!body || !header) return;
    if (!header.classList.contains('scroll-up-sticky') && !header.classList.contains('sticky-top') && !header.classList.contains('fixed-top')) return;
    window.scrollY > 100 ? body.classList.add('scrolled') : body.classList.remove('scrolled');
  }

  document.addEventListener('scroll', toggleScrolled);
  window.addEventListener('load', toggleScrolled);

  const mobileNavToggleBtn = document.querySelector('.mobile-nav-toggle');
  if (mobileNavToggleBtn) {
    function mobileNavToggle() {
      document.querySelector('body').classList.toggle('mobile-nav-active');
      mobileNavToggleBtn.classList.toggle('bi-list');
      mobileNavToggleBtn.classList.toggle('bi-x');
    }
    mobileNavToggleBtn.addEventListener('click', mobileNavToggle);
    document.querySelectorAll('#navmenu a').forEach(function (navmenu) {
      navmenu.addEventListener('click', function () {
        if (document.querySelector('.mobile-nav-active')) {
          mobileNavToggle();
        }
      });
    });
    document.querySelectorAll('.navmenu .toggle-dropdown').forEach(function (el) {
      el.addEventListener('click', function (e) {
        e.preventDefault();
        this.parentNode.classList.toggle('active');
        const next = this.parentNode.nextElementSibling;
        if (next) next.classList.toggle('dropdown-active');
        e.stopImmediatePropagation();
      });
    });
  }

  const preloader = document.querySelector('#preloader');
  if (preloader) {
    window.addEventListener('load', function () {
      preloader.remove();
    });
  }

  const scrollTop = document.querySelector('.scroll-top');
  if (scrollTop) {
    function toggleScrollTopBtn() {
      window.scrollY > 100 ? scrollTop.classList.add('active') : scrollTop.classList.remove('active');
    }
    scrollTop.addEventListener('click', function (e) {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    window.addEventListener('load', toggleScrollTopBtn);
    document.addEventListener('scroll', toggleScrollTopBtn);
  }
})();

(function () {
  'use strict';

  const modalEl = document.getElementById('mealDetailModal');
  if (!modalEl || typeof bootstrap === 'undefined') return;

  const modal = new bootstrap.Modal(modalEl);
  const imgEl = modalEl.querySelector('[data-meal-detail="image"]');
  const nameEl = modalEl.querySelector('[data-meal-detail="name"]');
  const calEl = modalEl.querySelector('[data-meal-detail="calories"]');
  const typeEl = modalEl.querySelector('[data-meal-detail="type"]');
  const descEl = modalEl.querySelector('[data-meal-detail="description"]');
  const recipeBtn = modalEl.querySelector('[data-meal-detail="recipe"]');
  const addBtn = modalEl.querySelector('[data-meal-detail="add"]');

  const typeClassPrefix = 'meal-detail__type--';

  function fillModal(card) {
    const name = card.getAttribute('data-meal-name') || '';
    const calories = card.getAttribute('data-meal-calories') || '';
    const description = card.getAttribute('data-meal-description') || '';
    const image = card.getAttribute('data-meal-image') || '';
    const recipe = card.getAttribute('data-meal-recipe') || '#';
    const mealType = card.getAttribute('data-meal-type') || '';
    const mealTypeLabel = card.getAttribute('data-meal-type-label') || '';

    if (imgEl) {
      imgEl.src = image;
      imgEl.alt = name;
    }
    if (nameEl) nameEl.textContent = name;
    if (calEl) calEl.textContent = calories ? `${calories} kcal` : '';
    if (typeEl) {
      typeEl.textContent = mealTypeLabel;
      typeEl.className = 'meal-detail__type';
      if (mealType === 'breakfast' || mealType === 'lunch' || mealType === 'dinner' || mealType === 'snack') {
        typeEl.classList.add(typeClassPrefix + mealType);
      }
    }
    if (descEl) descEl.textContent = description;
    if (recipeBtn) {
      recipeBtn.href = recipe;
      recipeBtn.setAttribute('aria-label', `Open recipe for ${name}`);
    }
    if (addBtn) {
      addBtn.setAttribute('data-meal-id', card.getAttribute('data-meal-id') || '');
      addBtn.setAttribute('data-meal-type', mealType);
    }
  }

  document.querySelectorAll('.meal-card[data-meal-id]').forEach(function (card) {
    card.setAttribute('tabindex', '0');
    card.setAttribute('role', 'button');

    function open() {
      fillModal(card);
      modal.show();
    }

    card.addEventListener('click', open);
    card.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        open();
      }
    });
  });

  if (addBtn) {
    addBtn.addEventListener('click', function () {
      const id = addBtn.getAttribute('data-meal-id');
      const mealType = addBtn.getAttribute('data-meal-type');
      document.dispatchEvent(
        new CustomEvent('mealPlanner:add', { detail: { mealId: id, mealType: mealType } })
      );
      modal.hide();
    });
  }
})();

// Handle adding meals to plan
(function () {
  'use strict';

  document.addEventListener('mealPlanner:add', function (e) {
    const { mealId, mealType } = e.detail;
    
    if (!mealId || !mealType) {
      alert('Invalid meal data');
      return;
    }

    const formData = new FormData();
    formData.append('meal_id', mealId);
    formData.append('meal_type', mealType);

    fetch('plan_add_meal.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.ok) {
        alert(data.message || 'Meal added to your plan!');
        // Redirect to today's plan to see the updated meal
        window.location.href = 'day_plan.php?date=' + new Date().toISOString().split('T')[0];
      } else {
        alert(data.message || 'Failed to add meal');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred while adding the meal');
    });
  });
})();


