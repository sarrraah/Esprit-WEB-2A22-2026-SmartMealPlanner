<?php
require_once __DIR__ . '/../../controller/EvenementController.php';

$controller = new EvenementController();

if (isset($_GET['delete'])) {
    $controller->deleteEvenement((int)$_GET['delete']);
    header('Location: listEvenements.php?msg=deleted');
    exit;
}

$evenements = $controller->listEvenements();
$msg        = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Event List</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#fff5f5;color:#1a0505;min-height:100vh}

nav{background:#fff;border-bottom:1.5px solid #f7c1c1;padding:0 32px;display:flex;align-items:center;justify-content:space-between;height:60px;position:sticky;top:0;z-index:100}
.logo{font-size:18px;font-weight:600;color:#1a0505;text-decoration:none}
.logo span{color:#b91c1c}
.nav-links{display:flex;gap:28px}
.nav-links a{font-size:14px;color:#9a3535;text-decoration:none;font-weight:500;transition:color .2s}
.nav-links a:hover{color:#b91c1c}

.page-hero{background:linear-gradient(135deg,#7f1d1d 0%,#b91c1c 100%);padding:36px 32px;text-align:center;color:#fff}
.page-hero h1{font-size:24px;font-weight:600;margin-bottom:6px}
.page-hero p{font-size:14px;color:rgba(255,255,255,0.6)}

.container{max-width:1200px;margin:0 auto;padding:32px 24px 60px}

.alert{padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:14px;font-weight:500}
.alert-success{background:#fce8e8;color:#7f1d1d;border:1px solid #f09595}
.alert-danger{background:#fff0e0;color:#7a4000;border:1px solid #fad99a}

.toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px}
.search-wrap{position:relative}
.search-wrap input{padding:9px 14px 9px 36px;border:1px solid #f7c1c1;border-radius:10px;background:#fff;color:#1a0505;font-size:14px;width:260px;outline:none;font-family:'Inter',sans-serif;transition:border-color .2s}
.search-wrap input:focus{border-color:#b91c1c;box-shadow:0 0 0 3px rgba(185,28,28,0.1)}
.search-wrap input::placeholder{color:#c9a0a0}
.search-wrap::before{content:'🔍';font-size:13px;position:absolute;left:11px;top:50%;transform:translateY(-50%);pointer-events:none}

.btn-new{display:inline-flex;align-items:center;gap:6px;background:#b91c1c;color:#fff;border:none;border-radius:10px;padding:10px 20px;font-size:14px;font-weight:500;font-family:'Inter',sans-serif;cursor:pointer;text-decoration:none;transition:background .15s}
.btn-new:hover{background:#991b1b}

.table-wrap{background:#fff;border:1px solid #fde8e8;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(185,28,28,0.06)}

table{width:100%;border-collapse:collapse}
thead{background:linear-gradient(135deg,#7f1d1d 0%,#b91c1c 100%)}
thead th{padding:14px 12px;text-align:center;font-size:13px;font-weight:500;color:#fff;white-space:nowrap}
tbody tr{border-bottom:1px solid #fce8e8;transition:background .15s}
tbody tr:last-child{border-bottom:none}
tbody tr:hover{background:#fff5f5}
td{padding:13px 12px;text-align:center;font-size:13px;color:#1a0505}
td b{font-weight:600;color:#7f1d1d}

.badge{padding:4px 12px;border-radius:20px;font-size:11px;font-weight:600}
.badge-active{background:#fce8e8;color:#7f1d1d;border:1px solid #f09595}
.badge-annule{background:#fff0e0;color:#7a4000;border:1px solid #fad99a}
.badge-termine{background:#f0f0f0;color:#555;border:1px solid #ccc}

.actions{display:flex;justify-content:center;align-items:center;gap:8px}
.action-btn{width:36px;height:36px;border:none;border-radius:10px;cursor:pointer;font-size:15px;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;transition:all .15s;box-shadow:0 2px 6px rgba(0,0,0,0.1)}
.action-btn:hover{transform:translateY(-2px);opacity:.9}
.btn-edit{background:#f7c1c1;color:#7f1d1d}
.btn-edit:hover{background:#f09595}
.btn-view{background:#fce8e8;color:#b91c1c}
.btn-view:hover{background:#f7c1c1}
.btn-delete{background:#b91c1c;color:#fff}
.btn-delete:hover{background:#991b1b}

.empty{text-align:center;padding:50px 20px;color:#9a3535;font-size:14px}
.empty-icon{font-size:36px;margin-bottom:10px}

@media(max-width:768px){
  nav{padding:0 16px}
  .page-hero{padding:24px 16px}
  .container{padding:20px 16px 40px}
  .toolbar{flex-direction:column;align-items:flex-start}
  .search-wrap input{width:100%}
  .table-wrap{overflow-x:auto}
}
</style>
</head>
<body>

<nav>
  <a href="interfaceevent.php" class="logo">Event <span>Management</span></a>
  <div class="nav-links">
    <a href="interfaceevent.php">Events</a>
    <a href="#about">About</a>
    <a href="#contact">Contact</a>
  </div>
</nav>

<div class="page-hero">
  <h1>📋 Event List</h1>
  <p>Manage all your events from one place</p>
</div>

<div class="container">

  <?php if ($msg === 'added'):   ?><div class="alert alert-success">✅ Event added successfully.</div><?php endif; ?>
  <?php if ($msg === 'updated'): ?><div class="alert alert-success">✅ Event updated successfully.</div><?php endif; ?>
  <?php if ($msg === 'deleted'): ?><div class="alert alert-danger">🗑️ Event deleted.</div><?php endif; ?>

  <div class="toolbar">
    <div class="search-wrap">
      <input type="text" id="searchInput" placeholder="Search event..." oninput="filterTable()">
    </div>
    <a class="btn-new" href="addEvenement.php">+ New Event</a>
  </div>

  <div class="table-wrap">
    <?php if (empty($evenements)): ?>
      <div class="empty">
        <div class="empty-icon">📅</div>
        No events registered.
      </div>
    <?php else: ?>
    <table id="eventsTable">
      <thead>
        <tr>
          <th>#</th>
          <th>Title</th>
          <th>Type</th>
          <th>Location</th>
          <th>Start</th>
          <th>End</th>
          <th>Capacity</th>
          <th>Price</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($evenements as $e): ?>
        <?php
          $s = strtolower($e->getStatut());
          $badge = match(true) {
              str_contains($s, 'actif')  => 'badge-active',
              str_contains($s, 'annul')  => 'badge-annule',
              str_contains($s, 'termin') => 'badge-termine',
              default => 'badge-active',
          };
        ?>
        <tr>
          <td><?= htmlspecialchars($e->getIdEvent()) ?></td>
          <td><b><?= htmlspecialchars($e->getTitre()) ?></b></td>
          <td><?= htmlspecialchars($e->getType()) ?></td>
          <td><?= htmlspecialchars($e->getLieu()) ?></td>
          <td><?= htmlspecialchars($e->getDateDebut()) ?></td>
          <td><?= htmlspecialchars($e->getDateFin()) ?></td>
          <td><?= htmlspecialchars($e->getCapaciteMax()) ?></td>
          <td><?= number_format($e->getPrix(), 2) ?> TND</td>
          <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($e->getStatut()) ?></span></td>
          <td>
            <div class="actions">
              <a class="action-btn btn-edit"
                 href="updateEvenement.php?id=<?= $e->getIdEvent() ?>"
                 title="Edit">✏️</a>
              <a class="action-btn btn-view"
                 href="listParticipations.php?id_event=<?= $e->getIdEvent() ?>"
                 title="Participants">👥</a>
              <a class="action-btn btn-delete"
                 href="listEvenements.php?delete=<?= $e->getIdEvent() ?>"
                 onclick="return confirm('Delete this event?')"
                 title="Delete">🗑️</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<script>
function filterTable() {
  const q = document.getElementById('searchInput').value.toLowerCase();
  document.querySelectorAll('#eventsTable tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}
</script>

</body>
</html>