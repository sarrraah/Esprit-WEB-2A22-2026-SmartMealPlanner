<?php include("header.php"); ?>

<style>
.badge-pending  { background:#fff8e1;color:#f57f17;border-radius:20px;padding:3px 12px;font-size:11px;font-weight:600; }
.badge-reviewed { background:#e3f2fd;color:#1565c0;border-radius:20px;padding:3px 12px;font-size:11px;font-weight:600; }
.badge-resolved { background:#e8f5e9;color:#2e7d32;border-radius:20px;padding:3px 12px;font-size:11px;font-weight:600; }
</style>

<div class="page-body">

  <!-- BANNER -->
  <div class="dashboard-banner" style="background:linear-gradient(rgba(0,0,0,0.55),rgba(0,0,0,0.55)),url('https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=1200') center/cover no-repeat;border-radius:14px;padding:36px 32px;margin-bottom:28px;color:white;">
    <h2 style="font-family:'Raleway',sans-serif;font-size:1.8rem;font-weight:300;letter-spacing:5px;text-transform:uppercase;margin:0 0 8px;color:white;">
      Customer <span style="font-weight:700;color:#e74c3c;">Complaints</span>
    </h2>
    <p style="font-size:0.85rem;color:rgba(255,255,255,0.75);margin:0;font-weight:300;letter-spacing:1px;">
      Review and manage customer complaints submitted from the front office.
    </p>
  </div>

  <!-- STATS -->
  <div class="row g-3 mb-4" id="stats-row">
    <div class="col-md-3">
      <div class="stat-card" style="border-left:4px solid #f57f17;">
        <div class="stat-icon" style="background:#fff8e1;"><i class="bi bi-hourglass-split" style="color:#f57f17;"></i></div>
        <div>
          <div class="stat-label">Pending</div>
          <div class="stat-value" id="count-pending">—</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card" style="border-left:4px solid #1565c0;">
        <div class="stat-icon" style="background:#e3f2fd;"><i class="bi bi-eye" style="color:#1565c0;"></i></div>
        <div>
          <div class="stat-label">Reviewed</div>
          <div class="stat-value" id="count-reviewed">—</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card" style="border-left:4px solid #2e7d32;">
        <div class="stat-icon" style="background:#e8f5e9;"><i class="bi bi-check-circle" style="color:#2e7d32;"></i></div>
        <div>
          <div class="stat-label">Resolved</div>
          <div class="stat-value" id="count-resolved">—</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card" style="border-left:4px solid #e74c3c;">
        <div class="stat-icon" style="background:#fdecea;"><i class="bi bi-megaphone" style="color:#e74c3c;"></i></div>
        <div>
          <div class="stat-label">Total</div>
          <div class="stat-value" id="count-total">—</div>
        </div>
      </div>
    </div>
  </div>

  <!-- TABLE -->
  <div class="content-card">
    <div class="card-header-title">
      <i class="bi bi-megaphone-fill" style="color:#e74c3c;"></i> All Complaints
      <button onclick="clearAllReclamations()" class="ms-auto"
        style="background:#fdecea;color:#c62828;border:none;border-radius:6px;padding:5px 14px;font-size:11px;font-weight:600;cursor:pointer;">
        <i class="bi bi-trash me-1"></i> Clear All
      </button>
    </div>

    <!-- Filters -->
    <div class="row g-2 mb-3">
      <div class="col-md-5">
        <input type="text" id="search-rec" placeholder="Search by name, email or subject..."
          style="border-radius:8px;border:1px solid #e0e0e0;padding:8px 12px;font-size:0.82rem;width:100%;outline:none;"
          oninput="renderTable()">
      </div>
      <div class="col-md-3">
        <select id="filter-statut-rec"
          style="border-radius:8px;border:1px solid #e0e0e0;padding:8px 12px;font-size:0.82rem;width:100%;outline:none;background:white;"
          onchange="renderTable()">
          <option value="">All statuses</option>
          <option value="pending">Pending</option>
          <option value="reviewed">Reviewed</option>
          <option value="resolved">Resolved</option>
        </select>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table" style="font-family:'Raleway',sans-serif;">
        <thead>
          <tr>
            <th style="font-size:0.7rem;letter-spacing:1.5px;text-transform:uppercase;color:#999;font-weight:600;padding:10px 12px;border-bottom:2px solid #f0f0f0;">#</th>
            <th style="font-size:0.7rem;letter-spacing:1.5px;text-transform:uppercase;color:#999;font-weight:600;padding:10px 12px;border-bottom:2px solid #f0f0f0;">Name</th>
            <th style="font-size:0.7rem;letter-spacing:1.5px;text-transform:uppercase;color:#999;font-weight:600;padding:10px 12px;border-bottom:2px solid #f0f0f0;">Email</th>
            <th style="font-size:0.7rem;letter-spacing:1.5px;text-transform:uppercase;color:#999;font-weight:600;padding:10px 12px;border-bottom:2px solid #f0f0f0;">Subject</th>
            <th style="font-size:0.7rem;letter-spacing:1.5px;text-transform:uppercase;color:#999;font-weight:600;padding:10px 12px;border-bottom:2px solid #f0f0f0;">Message</th>
            <th style="font-size:0.7rem;letter-spacing:1.5px;text-transform:uppercase;color:#999;font-weight:600;padding:10px 12px;border-bottom:2px solid #f0f0f0;">Date</th>
            <th style="font-size:0.7rem;letter-spacing:1.5px;text-transform:uppercase;color:#999;font-weight:600;padding:10px 12px;border-bottom:2px solid #f0f0f0;">Status</th>
            <th style="font-size:0.7rem;letter-spacing:1.5px;text-transform:uppercase;color:#999;font-weight:600;padding:10px 12px;border-bottom:2px solid #f0f0f0;">Actions</th>
          </tr>
        </thead>
        <tbody id="tbody-reclamations">
          <tr><td colspan="8" class="text-center text-muted py-4">Loading...</td></tr>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script>
function getReclamations() {
  return JSON.parse(localStorage.getItem('reclamations') || '[]');
}
function saveReclamations(data) {
  localStorage.setItem('reclamations', JSON.stringify(data));
}

function renderStats() {
  var data = getReclamations();
  var pending  = data.filter(function(r){ return r.statut === 'pending'; }).length;
  var reviewed = data.filter(function(r){ return r.statut === 'reviewed'; }).length;
  var resolved = data.filter(function(r){ return r.statut === 'resolved'; }).length;
  document.getElementById('count-pending').textContent  = pending;
  document.getElementById('count-reviewed').textContent = reviewed;
  document.getElementById('count-resolved').textContent = resolved;
  document.getElementById('count-total').textContent    = data.length;
}

function renderTable() {
  var data    = getReclamations();
  var search  = document.getElementById('search-rec').value.toLowerCase();
  var statut  = document.getElementById('filter-statut-rec').value;
  var tbody   = document.getElementById('tbody-reclamations');

  var filtered = data.filter(function(r) {
    var matchSearch = !search
      || r.nom.toLowerCase().includes(search)
      || r.email.toLowerCase().includes(search)
      || r.sujet.toLowerCase().includes(search);
    var matchStatut = !statut || r.statut === statut;
    return matchSearch && matchStatut;
  });

  if (filtered.length === 0) {
    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4"><i class="bi bi-inbox" style="font-size:1.5rem;display:block;margin-bottom:6px;"></i>No complaints found.</td></tr>';
    return;
  }

  var html = '';
  filtered.forEach(function(r, i) {
    var badgeCls = r.statut === 'pending' ? 'badge-pending' : r.statut === 'reviewed' ? 'badge-reviewed' : 'badge-resolved';
    var badgeTxt = r.statut === 'pending' ? 'Pending' : r.statut === 'reviewed' ? 'Reviewed' : 'Resolved';
    var shortMsg = r.message.length > 60 ? r.message.substring(0, 60) + '...' : r.message;
    html += '<tr style="border-bottom:1px solid #f8f8f8;">';
    html += '<td style="padding:10px 12px;font-size:0.83rem;color:#999;">' + (i + 1) + '</td>';
    html += '<td style="padding:10px 12px;font-size:0.83rem;"><strong>' + escHtml(r.nom) + '</strong></td>';
    html += '<td style="padding:10px 12px;font-size:0.83rem;color:#555;">' + escHtml(r.email) + '</td>';
    html += '<td style="padding:10px 12px;font-size:0.83rem;">' + escHtml(r.sujet) + '</td>';
    html += '<td style="padding:10px 12px;font-size:0.82rem;color:#777;" title="' + escHtml(r.message) + '">' + escHtml(shortMsg) + '</td>';
    html += '<td style="padding:10px 12px;font-size:0.78rem;color:#999;white-space:nowrap;">' + escHtml(r.date) + '</td>';
    html += '<td style="padding:10px 12px;"><span class="' + badgeCls + '">' + badgeTxt + '</span></td>';
    html += '<td style="padding:10px 12px;">';
    html += '<select onchange="updateStatut(' + r.id + ', this.value)" style="border:1px solid #e0e0e0;border-radius:6px;padding:3px 8px;font-size:11px;outline:none;cursor:pointer;">';
    html += '<option value="pending"'   + (r.statut==='pending'  ?' selected':'') + '>Pending</option>';
    html += '<option value="reviewed"'  + (r.statut==='reviewed' ?' selected':'') + '>Reviewed</option>';
    html += '<option value="resolved"'  + (r.statut==='resolved' ?' selected':'') + '>Resolved</option>';
    html += '</select>';
    html += ' <button onclick="deleteReclamation(' + r.id + ')" style="background:#fdecea;color:#c62828;border:none;border-radius:6px;padding:4px 8px;font-size:11px;cursor:pointer;margin-left:4px;"><i class="bi bi-trash"></i></button>';
    html += '</td>';
    html += '</tr>';
  });
  tbody.innerHTML = html;
}

function updateStatut(id, statut) {
  var data = getReclamations();
  data = data.map(function(r) {
    if (r.id === id) r.statut = statut;
    return r;
  });
  saveReclamations(data);
  renderStats();
  renderTable();
}

function deleteReclamation(id) {
  if (!confirm('Delete this complaint?')) return;
  var data = getReclamations().filter(function(r){ return r.id !== id; });
  saveReclamations(data);
  renderStats();
  renderTable();
}

function clearAllReclamations() {
  if (!confirm('Clear ALL complaints? This cannot be undone.')) return;
  localStorage.removeItem('reclamations');
  renderStats();
  renderTable();
}

function escHtml(str) {
  return String(str)
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;');
}

// Init
renderStats();
renderTable();
</script>

<?php include("footer.php"); ?>
