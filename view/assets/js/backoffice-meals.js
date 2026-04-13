/**
 * Back office meals — HTML shell + PHP APIs (meals_list.php, meals_save.php, meals_delete.php).
 */
(function () {
  'use strict';

  var MAX_NAME_WORDS = 60;
  var MAX_DESC_WORDS = 1200;
  var MAX_CAL = 3000;

  var api = window.BO_MEALS_API || { list: 'meals_list.php', save: 'meals_save.php', del: 'meals_delete.php' };

  var form = document.getElementById('bo-meal-form');
  var fileInput = document.getElementById('meal_image');
  var insertBtn = document.getElementById('bo-btn-insert-image');
  var preview = document.getElementById('bo-image-preview');
  var existingImage = document.getElementById('existing_image');
  var editingId = document.getElementById('editing_id');
  var searchInput = document.getElementById('bo-search');
  var filterSelect = document.getElementById('bo-filter-type');
  var filterBtn = document.getElementById('bo-btn-filter');
  var tableBody = document.getElementById('bo-meals-tbody');
  var btnModify = document.getElementById('bo-btn-modify');
  var btnDelete = document.getElementById('bo-btn-delete');
  var btnClearForm = document.getElementById('bo-btn-clear-form');
  var feedback = document.getElementById('bo-feedback');
  var loadError = document.getElementById('bo-load-error');

  // ── Custom modal helpers ──────────────────────────────────────────────────

  var _confirmModal = null;
  var _confirmCallback = null;

  function getConfirmModal() {
    if (_confirmModal) return _confirmModal;

    var el = document.createElement('div');
    el.id = 'bo-confirm-modal';
    el.className = 'modal fade';
    el.tabIndex = -1;
    el.setAttribute('aria-modal', 'true');
    el.setAttribute('role', 'dialog');
    el.innerHTML = [
      '<div class="modal-dialog modal-dialog-centered">',
        '<div class="modal-content">',
          '<div class="modal-header border-0 pb-0">',
            '<h5 class="modal-title" id="bo-confirm-title"></h5>',
          '</div>',
          '<div class="modal-body pt-2" id="bo-confirm-body"></div>',
          '<div class="modal-footer border-0 pt-0">',
            '<button type="button" class="btn btn-outline-secondary" id="bo-confirm-cancel">Cancel</button>',
            '<button type="button" class="btn btn-danger" id="bo-confirm-ok">OK</button>',
          '</div>',
        '</div>',
      '</div>'
    ].join('');
    document.body.appendChild(el);

    document.getElementById('bo-confirm-ok').addEventListener('click', function () {
      bootstrap.Modal.getInstance(el).hide();
      if (typeof _confirmCallback === 'function') _confirmCallback(true);
    });
    document.getElementById('bo-confirm-cancel').addEventListener('click', function () {
      bootstrap.Modal.getInstance(el).hide();
      if (typeof _confirmCallback === 'function') _confirmCallback(false);
    });

    _confirmModal = el;
    return el;
  }

  function boConfirm(title, message, cb) {
    var el = getConfirmModal();
    document.getElementById('bo-confirm-title').textContent = title;
    document.getElementById('bo-confirm-body').textContent = message;
    _confirmCallback = cb;
    var m = bootstrap.Modal.getOrCreateInstance(el);
    m.show();
  }

  function boAlert(message) {
    boConfirm('Notice', message, null);
    // hide the OK/Cancel pair — just show OK as dismiss
    document.getElementById('bo-confirm-cancel').style.display = 'none';
    document.getElementById('bo-confirm-ok').textContent = 'OK';
    document.getElementById('bo-confirm-ok').className = 'btn btn-primary';
    _confirmCallback = null;
  }

  // restore cancel button visibility after alert reuse
  function _resetModalButtons() {
    var cancel = document.getElementById('bo-confirm-cancel');
    var ok = document.getElementById('bo-confirm-ok');
    if (cancel) cancel.style.display = '';
    if (ok) { ok.textContent = 'OK'; ok.className = 'btn btn-danger'; }
  }



  /** @type {Record<number, object>} */
  var mealsCache = {};

  function renumberVisibleIds() {
    if (!tableBody) return;
    var n = 0;
    tableBody.querySelectorAll('tr[data-meal-id]').forEach(function (tr) {
      if (tr.style.display === 'none') return;
      var td = tr.querySelector('.bo-display-id');
      if (!td) return;
      n += 1;
      td.textContent = String(n);
    });
  }

  function wordCount(text) {
    text = (text || '').trim().replace(/\s+/g, ' ');
    if (!text) return 0;
    return text.split(/\s+/).length;
  }

  function clearFeedback() {
    if (feedback) feedback.innerHTML = '';
  }

  function clearFieldErrors() {
    if (!form) return;
    form.querySelectorAll('.bo-field-error').forEach(function (el) { el.remove(); });
    form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
  }

  function setFieldError(fieldName, msg) {
    var field = form.querySelector('[name="' + fieldName + '"]');
    if (!field) return;
    field.classList.add('is-invalid');
    var container = field.classList.contains('d-none') ? field.closest('.mb-3') : field.parentNode;
    var err = document.createElement('div');
    err.className = 'bo-field-error invalid-feedback';
    err.style.display = 'block';
    err.textContent = msg;
    container.appendChild(err);
  }

  function showErrors(errors) {
    if (!feedback || !errors || !errors.length) return;
    var ul = document.createElement('ul');
    errors.forEach(function (e) {
      var li = document.createElement('li');
      li.textContent = e;
      ul.appendChild(li);
    });
    var div = document.createElement('div');
    div.className = 'bo-alert-errors';
    div.setAttribute('role', 'alert');
    div.appendChild(ul);
    feedback.innerHTML = '';
    feedback.appendChild(div);
  }

  function showOk(msg) {
    if (!feedback) return;
    var div = document.createElement('div');
    div.className = 'bo-alert-ok';
    div.setAttribute('role', 'status');
    div.textContent = msg;
    feedback.innerHTML = '';
    feedback.appendChild(div);
  }

  function renderTable(meals) {
    if (!tableBody) return;
    tableBody.textContent = '';
    mealsCache = {};

    if (!Array.isArray(meals) || meals.length === 0) {
      var trEmpty = document.createElement('tr');
      var td = document.createElement('td');
      td.colSpan = 7;
      td.className = 'text-muted text-center py-4';
      td.textContent = 'No meals yet. Add one using the form.';
      trEmpty.appendChild(td);
      tableBody.appendChild(trEmpty);
      return;
    }

    meals.forEach(function (m, idx) {
      mealsCache[m.id] = m;

      var tr = document.createElement('tr');
      tr.dataset.mealId = String(m.id);
      tr.dataset.mealType = m.mealType || '';
      tr.dataset.searchName = String(m.name || '').toLowerCase();

      var tdRadio = document.createElement('td');
      var radio = document.createElement('input');
      radio.type = 'radio';
      radio.name = 'selected_id';
      radio.value = String(m.id);
      radio.className = 'form-check-input';
      radio.setAttribute('aria-label', 'Select meal ' + m.id);
      tdRadio.appendChild(radio);

      var tdId = document.createElement('td');
      tdId.className = 'bo-display-id';
      // Will be renumbered after filtering / reload to always start at 1.
      tdId.textContent = String(m.displayId || (idx + 1));

      var tdImg = document.createElement('td');
      var img = document.createElement('img');
      img.className = 'bo-thumb';
      img.alt = '';
      // If DB row doesn't have an image path, show a safe placeholder.
      img.src = m.image ? ('../' + m.image) : '../assets/img/meals/meal-24.png';
      img.loading = 'lazy';
      tdImg.appendChild(img);

      var tdName = document.createElement('td');
      tdName.textContent = m.name || '';

      var tdType = document.createElement('td');
      tdType.textContent = m.mealTypeLabel || m.mealType || '';

      var tdCal = document.createElement('td');
      tdCal.textContent = String(m.calories != null ? m.calories : '');

      var tdDesc = document.createElement('td');
      tdDesc.className = 'bo-desc-preview';
      tdDesc.title = m.description || '';
      tdDesc.textContent = m.description || '';

      tr.appendChild(tdRadio);
      tr.appendChild(tdId);
      tr.appendChild(tdImg);
      tr.appendChild(tdName);
      tr.appendChild(tdType);
      tr.appendChild(tdCal);
      tr.appendChild(tdDesc);

      tableBody.appendChild(tr);
    });

    renumberVisibleIds();
  }

  function loadMeals() {
    if (!tableBody) return;
    if (loadError) loadError.classList.add('d-none');

    fetch(api.list, { method: 'GET', credentials: 'same-origin' })
      .then(function (r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      })
      .then(function (data) {
        if (!Array.isArray(data)) throw new Error('Invalid response');
        renderTable(data);
        applyRowFilter();
      })
      .catch(function () {
        if (loadError) loadError.classList.remove('d-none');
        tableBody.innerHTML = '';
      });
  }

  function getSelectedRadio() {
    return document.querySelector('input[name="selected_id"]:checked');
  }

  function applyRowFilter() {
    if (!tableBody) return;
    var q = (searchInput && searchInput.value || '').toLowerCase().trim();
    var ft = filterSelect && filterSelect.value ? filterSelect.value.toLowerCase() : '';
    tableBody.querySelectorAll('tr').forEach(function (tr) {
      if (tr.querySelector('td[colspan]')) {
        tr.style.display = '';
        return;
      }
      var name = (tr.dataset.searchName || '').toLowerCase();
      var type = (tr.dataset.mealType || '').toLowerCase();
      var matchSearch = !q || name.indexOf(q) !== -1;
      var matchType = !ft || type === ft;
      tr.style.display = matchSearch && matchType ? '' : 'none';
    });
    renumberVisibleIds();
  }

  function highlightSelected() {
    if (!tableBody) return;
    var sel = getSelectedRadio();
    tableBody.querySelectorAll('tr').forEach(function (tr) {
      tr.classList.toggle('bo-row-selected', !!(sel && tr.contains(sel)));
    });
  }

  function resetFormPartial() {
    if (!form) return;
    form.reset();
    clearFieldErrors();
    if (editingId) editingId.value = '';
    if (existingImage) existingImage.value = '';
    if (fileInput) fileInput.value = '';
    if (preview) {
      preview.removeAttribute('src');
      preview.classList.remove('is-visible');
    }
    document.querySelectorAll('input[name="selected_id"]').forEach(function (x) {
      x.checked = false;
    });
    highlightSelected();
  }

  if (insertBtn && fileInput) {
    insertBtn.addEventListener('click', function () {
      fileInput.click();
    });
  }

  if (fileInput && preview) {
    fileInput.addEventListener('change', function () {
      var f = fileInput.files && fileInput.files[0];
      if (!f) return;
      var url = URL.createObjectURL(f);
      preview.src = url;
      preview.classList.add('is-visible');
    });
  }

  if (searchInput) searchInput.addEventListener('input', applyRowFilter);
  if (filterBtn) filterBtn.addEventListener('click', applyRowFilter);
  if (filterSelect) filterSelect.addEventListener('change', applyRowFilter);

  if (tableBody) {
    tableBody.addEventListener('change', function (e) {
      if (e.target && e.target.name === 'selected_id') highlightSelected();
    });
    tableBody.addEventListener('click', function (e) {
      var tr = e.target.closest('tr[data-meal-id]');
      if (!tr) return;
      var radio = tr.querySelector('input[name="selected_id"]');
      if (radio && e.target !== radio) {
        radio.checked = true;
        highlightSelected();
      }
    });
  }

  if (btnModify && form) {
    btnModify.addEventListener('click', function () {
      var r = getSelectedRadio();
      if (!r) {
        boAlert('Select a meal in the list first.');
        return;
      }
      var id = parseInt(r.value, 10);
      var m = mealsCache[id];
      if (!m) return;

      form.querySelector('[name="name"]').value = m.name || '';
      form.querySelector('[name="meal_type"]').value = m.mealType || '';
      form.querySelector('[name="calories"]').value = m.calories != null ? String(m.calories) : '';
      form.querySelector('[name="description"]').value = m.description || '';
      form.querySelector('[name="recipe_url"]').value = m.recipeUrl && m.recipeUrl !== '#' ? m.recipeUrl : '';
      editingId.value = String(id);
      existingImage.value = m.image || '';
      if (fileInput) fileInput.value = '';
      if (preview) {
        if (m.image) {
          preview.src = '../' + m.image;
          preview.classList.add('is-visible');
        } else {
          preview.removeAttribute('src');
          preview.classList.remove('is-visible');
        }
      }
      clearFeedback();
      form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  }

  if (btnDelete) {
    btnDelete.addEventListener('click', function () {
      var r = getSelectedRadio();
      if (!r) {
        boAlert('Select a meal in the list first.');
        return;
      }
      boConfirm('Delete meal', 'Delete this meal? This cannot be undone.', function (confirmed) {
        _resetModalButtons();
        if (!confirmed) return;

        var fd = new FormData();
        fd.append('id', r.value);

        fetch(api.del, { method: 'POST', body: fd, credentials: 'same-origin' })
          .then(function (res) {
            if (!res.ok) {
              throw new Error('HTTP ' + res.status + ': ' + res.statusText);
            }
            return res.json().catch(function () {
              throw new Error('Invalid response format (expected JSON)');
            });
          })
          .then(function (data) {
            if (data.ok) {
              showOk(data.message || 'Deleted.');
              loadMeals();
              resetFormPartial();
            } else {
              showErrors(data.errors || ['Delete failed.']);
            }
          })
          .catch(function (err) {
            console.error('Delete error:', err);
            showErrors([err.message || 'Network error while deleting.']);
          });
      });
    });
  }

  if (btnClearForm) {
    btnClearForm.addEventListener('click', function () {
      resetFormPartial();
      clearFeedback();
      clearFieldErrors();
    });
  }

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearFeedback();
      clearFieldErrors();

      var name = form.querySelector('[name="name"]').value.trim();
      var mealType = form.querySelector('[name="meal_type"]').value;
      var desc = form.querySelector('[name="description"]').value.trim();
      var cal = form.querySelector('[name="calories"]').value.trim();
      var recipeUrl = form.querySelector('[name="recipe_url"]').value.trim();
      var isNew = !editingId.value;
      var hasNewFile = fileInput && fileInput.files && fileInput.files.length > 0;
      var hasExisting = existingImage && existingImage.value;
      var msgs = [];

      if (!name) {
        setFieldError('name', 'Le nom est obligatoire.');
        msgs.push('name');
      } else if (wordCount(name) > MAX_NAME_WORDS) {
        setFieldError('name', 'Le nom ne doit pas dépasser ' + MAX_NAME_WORDS + ' mots.');
        msgs.push('name');
      }

      if (!mealType) {
        setFieldError('meal_type', 'Le type de repas est obligatoire.');
        msgs.push('meal_type');
      }

      if (!cal) {
        setFieldError('calories', 'Les calories sont obligatoires.');
        msgs.push('calories');
      } else if (!/^\d+$/.test(cal)) {
        setFieldError('calories', 'Les calories doivent être un nombre entier.');
        msgs.push('calories');
      } else {
        var calInt = parseInt(cal, 10);
        if (calInt < 0 || calInt > MAX_CAL) {
          setFieldError('calories', 'Les calories doivent être entre 0 et ' + MAX_CAL + '.');
          msgs.push('calories');
        }
      }

      if (!desc) {
        setFieldError('description', 'La description est obligatoire.');
        msgs.push('description');
      } else if (wordCount(desc) > MAX_DESC_WORDS) {
        setFieldError('description', 'La description ne doit pas dépasser ' + MAX_DESC_WORDS + ' mots.');
        msgs.push('description');
      }

      if (isNew && !hasNewFile) {
        setFieldError('meal_image', 'L\'image est obligatoire.');
        msgs.push('meal_image');
      }

      if (recipeUrl && !/^https?:\/\/.+/.test(recipeUrl)) {
        setFieldError('recipe_url', 'L\'URL doit commencer par http:// ou https://.');
        msgs.push('recipe_url');
      }

      if (msgs.length) return;

      var fd = new FormData(form);
      var submitBtn = document.getElementById('bo-btn-submit');
      if (submitBtn) submitBtn.disabled = true;

      fetch(api.save, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (res) {
          return res.json();
        })
        .then(function (data) {
          if (data.ok) {
            showOk(data.message || 'Saved.');
            resetFormPartial();
            loadMeals();
          } else {
            showErrors(data.errors && data.errors.length ? data.errors : ['Save failed.']);
          }
        })
        .catch(function () {
          showErrors(['Network error while saving.']);
        })
        .finally(function () {
          if (submitBtn) submitBtn.disabled = false;
        });
    });
  }

  loadMeals();
})();
