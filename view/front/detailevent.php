<?php
require_once __DIR__ . '/../../controller/EvenementController.php';

$ctrl = new EvenementController();
$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$e    = $ctrl->getEvenementById($id);

if (!$e) {
    header('Location: interfaceevent.php');
    exit;
}

$typeConfig = [
    'Conférence'  => ['from' => '#fce8e8', 'to' => '#f7c1c1', 'emoji' => '🎤'],
    'Atelier'     => ['from' => '#fde8e8', 'to' => '#f09595', 'emoji' => '🛠️'],
    'Compétition' => ['from' => '#fce8e8', 'to' => '#e24b4a', 'emoji' => '🏆'],
    'Forum'       => ['from' => '#fdf0f0', 'to' => '#f7c1c1', 'emoji' => '💬'],
    'Séminaire'   => ['from' => '#fff5f5', 'to' => '#f09595', 'emoji' => '📚'],
    'Autre'       => ['from' => '#fdf5f5', 'to' => '#e8d5d5', 'emoji' => '📅'],
];

$statusLabels = [
    'actif'   => 'Active',
    'annulé'  => 'Cancelled',
    'terminé' => 'Ended',
];

$tc            = $typeConfig[$e->getType()] ?? $typeConfig['Autre'];
$isFree        = ($e->getPrix() == 0);
$statut        = strtolower($e->getStatut());
$isActif       = str_contains($statut, 'actif');
$isComplet     = str_contains($statut, 'complet');
$statusDisplay = $statusLabels[$statut] ?? ucfirst($statut);
$dateDebut     = date('m/d/Y', strtotime($e->getDateDebut()));
$dateFin       = date('m/d/Y', strtotime($e->getDateFin()));
$dateLabel     = ($dateDebut === $dateFin) ? $dateDebut : "$dateDebut → $dateFin";
$heureDebut    = date('H:i', strtotime($e->getDateDebut()));
$heureFin      = date('H:i', strtotime($e->getDateFin()));
$badgeClass    = match(true) {
    str_contains($statut, 'actif')  => 's-actif',
    str_contains($statut, 'termin') => 's-termine',
    default                         => 's-complet',
};

$tous     = $ctrl->listEvenements();
$suggests = array_filter($tous, fn($ev) => $ev->getType() === $e->getType() && $ev->getIdEvent() !== $e->getIdEvent());
$suggests = array_slice($suggests, 0, 3);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($e->getTitre()) ?> – Event Management</title>
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
.back-link{display:flex;align-items:center;gap:6px;font-size:13px;color:#9a3535;text-decoration:none;font-weight:500;transition:color .2s}
.back-link:hover{color:#b91c1c}

.hero{height:280px;position:relative;display:flex;align-items:center;justify-content:center;overflow:hidden}
.hero-bg{position:absolute;inset:0;background:linear-gradient(135deg,<?= $tc['from'] ?> 0%,<?= $tc['to'] ?> 100%)}
.hero-emoji{position:relative;z-index:1;font-size:80px;opacity:.25}

.breadcrumb{max-width:1100px;margin:0 auto;padding:16px 32px;display:flex;align-items:center;gap:8px;font-size:13px;color:#9a3535}
.breadcrumb a{color:#9a3535;text-decoration:none}
.breadcrumb a:hover{color:#b91c1c}
.breadcrumb span{color:#1a0505;font-weight:500}

.layout{max-width:1100px;margin:0 auto;padding:0 32px 60px;display:grid;grid-template-columns:1fr 320px;gap:32px;align-items:start}

.d-badges{display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap}
.badge{font-size:12px;font-weight:500;padding:4px 12px;border-radius:20px}
.s-actif{background:#fce8e8;color:#7f1d1d;border:1px solid #f09595}
.s-termine{background:#f5e8e8;color:#501313;border:1px solid #e24b4a}
.s-complet{background:#fef3e2;color:#8a5a00;border:1px solid #fad99a}
.badge-type{background:#fce8e8;color:#7f1d1d;border:1px solid #f7c1c1}

.d-title{font-size:26px;font-weight:600;line-height:1.25;margin-bottom:20px;color:#1a0505}

.info-boxes{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:28px}
.info-box{background:#fff;border:1px solid #fde8e8;border-radius:12px;padding:14px 16px;display:flex;align-items:flex-start;gap:12px}
.info-box-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;background:#fff5f5}
.info-box-label{font-size:11px;color:#9a3535;margin-bottom:3px;text-transform:uppercase;letter-spacing:.6px}
.info-box-val{font-size:13px;font-weight:500;color:#1a0505;line-height:1.4}

.section-block{background:#fff;border:1px solid #fde8e8;border-radius:12px;padding:22px;margin-bottom:16px}
.section-block h2{font-size:15px;font-weight:600;color:#1a0505;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid #fce8e8}
.desc-text{font-size:14px;color:#4a1515;line-height:1.8}

.prog-row{display:flex;justify-content:space-between;font-size:13px;margin-bottom:8px}
.prog-row span:first-child{color:#9a3535}
.prog-row span:last-child{font-weight:600;color:#1a0505}
.prog-track{height:8px;background:#fce8e8;border-radius:8px;overflow:hidden;margin-bottom:8px}
.prog-fill{height:100%;border-radius:8px;background:#b91c1c;transition:width .4s}
.prog-fill.warn{background:#e8a020}
.prog-fill.full{background:#7f1d1d}
.prog-sub{font-size:12px;color:#9a3535}

.suggests-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px}
.scard{background:#fff;border:1px solid #fde8e8;border-radius:12px;overflow:hidden;text-decoration:none;color:inherit;display:flex;flex-direction:column;transition:all .2s}
.scard:hover{border-color:#f7c1c1;transform:translateY(-2px);box-shadow:0 4px 16px rgba(185,28,28,.1)}
.scard-banner{height:90px;position:relative;display:flex;align-items:center;justify-content:center;overflow:hidden}
.scard-emoji{position:relative;z-index:1;font-size:30px;opacity:.25}
.scard-body{padding:12px}
.scard-title{font-size:13px;font-weight:500;color:#1a0505;margin-bottom:6px;line-height:1.3}
.scard-meta{font-size:12px;color:#9a3535;display:flex;align-items:center;gap:5px}

.reg-card{background:#fff;border:1px solid #fde8e8;border-radius:16px;padding:22px;position:sticky;top:76px}
.reg-price-wrap{margin-bottom:16px}
.reg-price{font-size:28px;font-weight:600;color:#1a0505}
.reg-price-sub{font-size:13px;color:#9a3535;margin-top:3px}

.reg-btn{width:100%;background:#b91c1c;color:#fff;border:none;border-radius:10px;padding:13px;font-size:15px;font-weight:600;cursor:pointer;font-family:inherit;transition:background .15s;margin-bottom:14px;display:block;text-align:center;text-decoration:none}
.reg-btn:hover{background:#991b1b}
.reg-btn.disabled{background:#fce8e8;color:#9a3535;cursor:default;pointer-events:none}
.reg-btn.waiting{background:#e8a020;color:#fff}
.reg-btn.waiting:hover{background:#c97a10}

.reg-divider{border:none;border-top:1px solid #fce8e8;margin:14px 0}
.reg-details{display:flex;flex-direction:column;gap:10px}
.reg-row{display:flex;justify-content:space-between;align-items:center;font-size:13px}
.reg-row-label{color:#9a3535}
.reg-row-val{font-weight:500;color:#1a0505}

.share-section{margin-top:16px;padding-top:16px;border-top:1px solid #fce8e8}
.share-label{font-size:12px;color:#9a3535;margin-bottom:8px}
.share-btns{display:flex;gap:8px}
.share-btn{flex:1;padding:8px;border:1px solid #f7c1c1;border-radius:8px;background:transparent;font-size:12px;color:#9a3535;cursor:pointer;font-family:inherit;transition:all .15s;text-align:center}
.share-btn:hover{background:#fce8e8;border-color:#f09595;color:#7f1d1d}

@media(max-width:900px){
  .layout{grid-template-columns:1fr;padding:0 16px 40px}
  .sidebar-col{order:-1}
  .reg-card{position:static}
  .info-boxes{grid-template-columns:1fr}
  nav{padding:0 16px}
  .breadcrumb{padding:12px 16px}
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
  <a href="interfaceevent.php" class="back-link">← Back to events</a>
</nav>

<div class="hero">
  <div class="hero-bg"></div>
  <span class="hero-emoji"><?= $tc['emoji'] ?></span>
</div>

<div class="breadcrumb">
  <a href="interfaceevent.php">Events</a>
  <span style="color:#f7c1c1">/</span>
  <span><?= htmlspecialchars($e->getTitre()) ?></span>
</div>

<div class="layout">

  <div class="main-col">

    <div class="d-badges">
      <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($statusDisplay) ?></span>
      <span class="badge badge-type"><?= htmlspecialchars($e->getType()) ?></span>
    </div>

    <h1 class="d-title"><?= htmlspecialchars($e->getTitre()) ?></h1>

    <div class="info-boxes">
      <div class="info-box">
        <div class="info-box-icon">📅</div>
        <div>
          <div class="info-box-label">Date</div>
          <div class="info-box-val"><?= $dateLabel ?></div>
        </div>
      </div>
      <div class="info-box">
        <div class="info-box-icon">🕐</div>
        <div>
          <div class="info-box-label">Time</div>
          <div class="info-box-val"><?= $heureDebut ?> → <?= $heureFin ?></div>
        </div>
      </div>
      <div class="info-box">
        <div class="info-box-icon">📍</div>
        <div>
          <div class="info-box-label">Location</div>
          <div class="info-box-val"><?= htmlspecialchars($e->getLieu()) ?></div>
        </div>
      </div>
      <div class="info-box">
        <div class="info-box-icon">👥</div>
        <div>
          <div class="info-box-label">Capacity</div>
          <div class="info-box-val"><?= $e->getCapaciteMax() ?> participants max</div>
        </div>
      </div>
    </div>

    <div class="section-block">
      <h2>About this event</h2>
      <div class="desc-text">
        <?= nl2br(htmlspecialchars($e->getDescription() ?: 'No description available for this event.')) ?>
      </div>
    </div>

    <div class="section-block">
      <h2>Available seats</h2>
      <?php
        $pct = 75;
        $fillClass = $pct >= 100 ? 'full' : ($pct >= 80 ? 'warn' : '');
      ?>
      <div class="prog-row">
        <span>Reserved seats</span>
        <span><?= $e->getCapaciteMax() ?> max</span>
      </div>
      <div class="prog-track">
        <div class="prog-fill <?= $fillClass ?>" style="width: <?= $pct ?>%"></div>
      </div>
      <div class="prog-sub">
        <?php if ($pct >= 100): ?>
          Fully booked — no seats remaining
        <?php elseif ($pct >= 80): ?>
          ⚡ Hurry up, only a few seats left!
        <?php else: ?>
          Seats are still available
        <?php endif; ?>
      </div>
    </div>

    <?php if (!empty($suggests)): ?>
    <div class="section-block">
      <h2>Similar events</h2>
      <div class="suggests-grid">
        <?php foreach ($suggests as $s):
          $stc = $typeConfig[$s->getType()] ?? $typeConfig['Autre'];
          $sd  = date('m/d/Y', strtotime($s->getDateDebut()));
        ?>
        <a class="scard" href="detailEvent.php?id=<?= $s->getIdEvent() ?>">
          <div class="scard-banner" style="background:linear-gradient(135deg,<?= $stc['from'] ?>,<?= $stc['to'] ?>)">
            <span class="scard-emoji"><?= $stc['emoji'] ?></span>
          </div>
          <div class="scard-body">
            <div class="scard-title"><?= htmlspecialchars($s->getTitre()) ?></div>
            <div class="scard-meta">📅 <?= $sd ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>

  <div class="sidebar-col">
    <div class="reg-card">

      <div class="reg-price-wrap">
        <div class="reg-price"><?= $isFree ? 'Free' : number_format($e->getPrix(), 2) . ' TND' ?></div>
        <div class="reg-price-sub"><?= $isFree ? 'Completely free entry' : 'Per participant' ?></div>
      </div>

      <?php if ($isActif): ?>
        <a href="register.php?id=<?= $e->getIdEvent() ?>" class="reg-btn">Register for this event</a>
      <?php elseif ($isComplet): ?>
        <a href="register.php?id=<?= $e->getIdEvent() ?>" class="reg-btn waiting">Join waiting list</a>
      <?php else: ?>
        <span class="reg-btn disabled">Registration closed</span>
      <?php endif; ?>

      <hr class="reg-divider">

      <div class="reg-details">
        <div class="reg-row">
          <span class="reg-row-label">Date</span>
          <span class="reg-row-val"><?= $dateLabel ?></span>
        </div>
        <div class="reg-row">
          <span class="reg-row-label">Time</span>
          <span class="reg-row-val"><?= $heureDebut ?> – <?= $heureFin ?></span>
        </div>
        <div class="reg-row">
          <span class="reg-row-label">Location</span>
          <span class="reg-row-val"><?= htmlspecialchars($e->getLieu()) ?></span>
        </div>
        <div class="reg-row">
          <span class="reg-row-label">Capacity</span>
          <span class="reg-row-val"><?= $e->getCapaciteMax() ?> seats</span>
        </div>
        <div class="reg-row">
          <span class="reg-row-label">Type</span>
          <span class="reg-row-val"><?= htmlspecialchars($e->getType()) ?></span>
        </div>
        <div class="reg-row">
          <span class="reg-row-label">Status</span>
          <span class="reg-row-val"><?= htmlspecialchars($statusDisplay) ?></span>
        </div>
      </div>

      <div class="share-section">
        <div class="share-label">Share this event</div>
        <div class="share-btns">
          <button class="share-btn">🔗 Link</button>
          <button class="share-btn">📧 Email</button>
          <button class="share-btn">💬 WhatsApp</button>
        </div>
      </div>

    </div>
  </div>

</div>

</body>
</html>