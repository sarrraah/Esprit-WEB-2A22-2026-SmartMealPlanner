(function () {
  'use strict';

  var api = window.BO_PLANS_API || { list: 'plans_list.php', save: 'plans_save.php', del: 'plans_delete.php' };

  var form       = document.getElementById('bo-plan-form');
  var editingId  = document.getElementById('editing_id');
  var searchInput = document.getElementById('bo-search');
  var filterSelect = document.getElementById('bo-filter-type');
  var filterBtn  = document.getElementById('bo-btn-filter');
  var tableBody  = document.getElementById('bo-plans-tbody');
  var btnModify  = document.getElementById('bo-btn-modify');
  var btnDelete  = document.getElementById('bo-btn-delete');
  var btnClear   = document.getElementById('bo-btn-clear-form');
  var feedback   = document.getElementById('bo-feedback');
  var loadError  = document.getElementById('bo-load-error');

  var plansCache = {};

  // ── Modal helpers (same pattern as meals) ────────────────────
  var _modal = null, _cb = null;
  function getModal() {
    if (_modal) return _modal;
    var el = document.createElement('div');
    el.className = 'modal fade'; el.tabIndex = -1;
    el.innerHTML = '<div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header border-0 pb-0"><h5 class="modal-title" id="pc-title"></h5></div><div class="modal-body pt-2" id="pc-body"></div><div class="modal-footer border-0 pt-0"><button class="btn btn-outline-secondary" id="pc-cancel">Cancel</button><button class="btn btn-danger" id="pc-ok">OK</button></div></div></div>';
    document.body.appendChild(el);
    document.getElementById('pc-ok').addEventListener('click', function () { bootstrap.Modal.getInstance(el).hide(); if (typeof _cb === 'function') _cb(true); });
    document.getElementById('pc-cancel').addEventListener('click', function () { bootstrap.Modal.getInstance(el).hide(); if (typeof _cb === 'function') _cb(false); });
    _modal = el; return el;
  }
  function boConfirm(title, msg, cb) {
    var el = getModal();
    document.getElementById('pc-title').textContent = title;
    document.getElementById('pc-body').textContent  = msg;
    _cb = cb;
    bootstrap.Modal.getOrCreateInstance(el).show();
  }
  function boAlert(msg) { boConfirm('Notice', msg, null); }

  // ── Feedback ─────────────────────────────────────────────────
  function showOk(msg) {
    if (!feedback) return;
    feedback.innerHTML = '<div class="bo-alert-ok" role="status">' + msg + '</div>';
  }
  function showErrors(errors) {
    if (!feedback || !errors.length) return;
    feedback.innerHTML = '<div class="bo-alert-errors" role="alert"><ul>' + errors.map(function(e){ return '<li>' + e + '</li>'; }).join('') + '</ul></div>';
  }

  // ── Table ─────────────────────────────────────────────────────
  function renderTable(plans) {
    tableBody.textContent = '';
    plansCache = {};
    if (!plans.length) {
      tableBody.innerHTML = '<tr><td colspan="6" class="text-muted text-center py-4">No plans yet.</td></tr>';
      return;
    }
    plans.forEach(function (p) {
      plansCache[p.id] = p;
      var tr = document.createElement('tr');
      tr.dataset.planId   = p.id;
      tr.dataset.planType = p.mealType || '';
      tr.dataset.searchName = (p.name || '').toLowerCase();
      tr.innerHTML =
        '<td><input type="radio" name="selected_id" value="' + p.id + '" class="form-check-input" aria-label="Select plan ' + p.id + '"></td>' +
        '<td>' + p.id + '</td>' +
        '<td>' + (p.name || '') + '</td>' +
        '<td>' + (p.mealTypeLabel || p.mealType || '') + '</td>' +
        '<td>' + (p.totalCalories || 0) + '</td>' +
        '<td class="bo-desc-preview" title="' + (p.description || '') + '">' + (p.description || '') + '</td>';
      tableBody.appendChild(tr);
    });
  }

  function loadPlans() {
    if (loadError) loadError.classList.add('d-none');
    fetch(api.list, { credentials: 'same-origin' })
      .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function (data) { renderTable(data); applyFilter(); })
      .catch(function () { if (loadError) loadError.classList.remove('d-none'); });
  }

  function getSelected() { return document.querySelector('input[name="selected_id"]:checked'); }

  function applyFilter() {
    var q  = (searchInput ? searchInput.value : '').toLowerCase().trim();
    var ft = filterSelect ? filterSelect.value.toLowerCase() : '';
    tableBody.querySelectorAll('tr').forEach(function (tr) {
      if (tr.querySelector('td[colspan]')) { tr.style.display = ''; return; }
      var match = (!q || (tr.dataset.searchName || '').includes(q)) && (!ft || tr.dataset.planType === ft);
      tr.style.display = match ? '' : 'none';
    });
  }

  function resetForm() {
    if (form) form.reset();
    if (editingId) editingId.value = '';
    if (feedback) feedback.innerHTML = '';
    document.querySelectorAll('input[name="selected_id"]').forEach(function (x) { x.checked = false; });
  }

  // ── Events ────────────────────────────────────────────────────
  if (searchInput) searchInput.addEventListener('input', applyFilter);
  if (filterBtn)   filterBtn.addEventListener('click', applyFilter);
  if (filterSelect) filterSelect.addEventListener('change', applyFilter);

  if (tableBody) {
    tableBody.addEventListener('click', function (e) {
      var tr = e.target.closest('tr[data-plan-id]');
      if (!tr) return;
      var radio = tr.querySelector('input[name="selected_id"]');
      if (radio && e.target !== radio) radio.checked = true;
    });
  }

  if (btnModify) {
    btnModify.addEventListener('click', function () {
      var r = getSelected();
      if (!r) { boAlert('Select a plan first.'); return; }
      var p = plansCache[parseInt(r.value, 10)];
      if (!p) return;
      form.querySelector('[name="name"]').value          = p.name || '';
      form.querySelector('[name="meal_type"]').value     = p.mealType || '';
      form.querySelector('[name="total_calories"]').value = p.totalCalories || 0;
      form.querySelector('[name="description"]').value   = p.description || '';
      editingId.value = p.id;
      if (feedback) feedback.innerHTML = '';
      form.scrollIntoView({ behavior: 'smooth' });
    });
  }

  if (btnDelete) {
    btnDelete.addEventListener('click', function () {
      var r = getSelected();
      if (!r) { boAlert('Select a plan first.'); return; }
      boConfirm('Delete plan', 'Delete this plan? This cannot be undone.', function (confirmed) {
        if (!confirmed) return;
        var fd = new FormData();
        fd.append('id', r.value);
        fetch(api.del, { method: 'POST', body: fd, credentials: 'same-origin' })
          .then(function (res) { return res.json(); })
          .then(function (data) { data.ok ? (showOk(data.message || 'Deleted.'), loadPlans(), resetForm()) : showErrors(data.errors || ['Delete failed.']); })
          .catch(function () { showErrors(['Network error.']); });
      });
    });
  }

  if (btnClear) btnClear.addEventListener('click', resetForm);

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (feedback) feedback.innerHTML = '';
      var name   = form.querySelector('[name="name"]').value.trim();
      var type   = form.querySelector('[name="meal_type"]').value;
      var cal    = form.querySelector('[name="total_calories"]').value.trim();
      var errors = [];
      if (!name) errors.push('Name is required.');
      if (!type) errors.push('Plan type is required.');
      if (!cal || !/^\d+$/.test(cal)) errors.push('Total calories must be a whole number.');
      if (errors.length) { showErrors(errors); return; }

      var fd = new FormData(form);
      var btn = document.getElementById('bo-btn-submit');
      if (btn) btn.disabled = true;
      fetch(api.save, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (res) { return res.json(); })
        .then(function (data) { data.ok ? (showOk(data.message || 'Saved.'), resetForm(), loadPlans()) : showErrors(data.errors || ['Save failed.']); })
        .catch(function () { showErrors(['Network error.']); })
        .finally(function () { if (btn) btn.disabled = false; });
    });
  }

  loadPlans();
})();
