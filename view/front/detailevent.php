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
    'Conférence'  => ['from' => '#dbe9fa', 'to' => '#B5D4F4', 'emoji' => '🎤'],
    'Atelier'     => ['from' => '#e0f0c4', 'to' => '#C0DD97', 'emoji' => '🛠️'],
    'Compétition' => ['from' => '#fbe0d4', 'to' => '#F5C4B3', 'emoji' => '🏆'],
    'Forum'       => ['from' => '#e6e4fb', 'to' => '#CECBF6', 'emoji' => '💬'],
    'Séminaire'   => ['from' => '#ccf0e4', 'to' => '#9FE1CB', 'emoji' => '📚'],
    'Autre'       => ['from' => '#eceae2', 'to' => '#D3D1C7', 'emoji' => '📅'],
];

$tc         = $typeConfig[$e->getType()] ?? $typeConfig['Autre'];
$isFree     = ($e->getPrix() == 0);
$isActif    = ($e->getStatut() === 'Actif');
$isComplet  = ($e->getStatut() === 'Complet');
$dateDebut  = date('d/m/Y', strtotime($e->getDateDebut()));
$dateFin    = date('d/m/Y', strtotime($e->getDateFin()));
$dateLabel  = ($dateDebut === $dateFin) ? $dateDebut : "$dateDebut → $dateFin";
$heureDebut = date('H:i', strtotime($e->getDateDebut()));
$heureFin   = date('H:i', strtotime($e->getDateFin()));
$badgeClass = match($e->getStatut()) { 'Actif' => 's-actif', 'Terminé' => 's-termine', default => 's-complet' };

$tous     = $ctrl->listEvenements();
$suggests = array_filter($tous, fn($ev) => $ev->getType() === $e->getType() && $ev->getIdEvent() !== $e->getIdEvent());
$suggests = array_slice($suggests, 0, 3);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($e->getTitre()) ?> – Gestion des Événements</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#f5f5f8;color:#1a1a2e;min-height:100vh}
nav{background:#fff;border-bottom:1px solid #e8e8f0;padding:0 32px;display:flex;align-items:center;justify-content:space-between;height:60px;position:sticky;top:0;z-index:100}
.logo{font-size:18px;font-weight:600;color:#1a1a2e;text-decoration:none}
.logo span{color:#7F77DD}
.nav-links{display:flex;gap:28px}
.nav-links a{font-size:14px;color:#7a7a99;text-decoration:none;font-weight:500;transition:color .2s}
.nav-links a:hover{color:#1a1a2e}
.back-link{display:flex;align-items:center;gap:6px;font-size:13px;color:#7a7a99;text-decoration:none;font-weight:500;transition:color .2s}
.back-link:hover{color:#534AB7}
.hero{height:280px;position:relative;display:flex;align-items:center;justify-content:center;overflow:hidden}
.hero-bg{position:absolute;inset:0;background:linear-gradient(135deg,<?= $tc['from'] ?> 0%,<?= $tc['to'] ?> 100%)}
.hero-emoji{position:relative;z-index:1;font-size:80px;opacity:.25}
.breadcrumb{max-width:1100px;margin:0 auto;padding:16px 32px;display:flex;align-items:center;gap:8px;font-size:13px;color:#9999b3}
.breadcrumb a{color:#9999b3;text-decoration:none}
.breadcrumb a:hover{color:#534AB7}
.breadcrumb span{color:#1a1a2e;font-weight:500}
.layout{max-width:1100px;margin:0 auto;padding:0 32px 60px;display:grid;grid-template-columns:1fr 320px;gap:32px;align-items:start}
.main-col{}
.d-badges{display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap}
.badge{font-size:12px;font-weight:500;padding:4px 12px;border-radius:20px}
.s-actif{background:#e8f8ee;color:#1e7a42;border:1px solid #b8e8cc}
.s-termine{background:#fce8e8;color:#a32d2d;border:1px solid #f0bbbb}
.s-complet{background:#fef3e2;color:#8a5a00;border:1px solid #fad99a}
.badge-type{background:#EEEDFE;color:#3C3489;border:1px solid #AFA9EC}
.d-title{font-size:26px;font-weight:600;line-height:1.25;margin-bottom:20px;color:#1a1a2e}
.info-boxes{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:28px}
.info-box{background:#fff;border:1px solid #e8e8f0;border-radius:12px;padding:14px 16px;display:flex;align-items:flex-start;gap:12px}
.info-box-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;background:#f0f0f8}
.info-box-label{font-size:11px;color:#9999b3;margin-bottom:3px;text-transform:uppercase;letter-spacing:.6px}
.info-box-val{font-size:13px;font-weight:500;color:#1a1a2e;line-height:1.4}
.section-block{background:#fff;border:1px solid #e8e8f0;border-radius:12px;padding:22px;margin-bottom:16px}
.section-block h2{font-size:15px;font-weight:600;color:#1a1a2e;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid #f0f0f8}
.desc-text{font-size:14px;color:#4a4a6a;line-height:1.8}
.prog-row{display:flex;justify-content:space-between;font-size:13px;margin-bottom:8px}
.prog-row span:first-child{color:#7a7a99}
.prog-row span:last-child{font-weight:600;color:#1a1a2e}
.prog-track{height:8px;background:#f0f0f8;border-radius:8px;overflow:hidden;margin-bottom:8px}
.prog-fill{height:100%;border-radius:8px;background:#7F77DD;transition:width .4s}
.prog-fill.warn{background:#e8a020}
.prog-fill.full{background:#dc4040}
.prog-sub{font-size:12px;color:#9999b3}
.suggests-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px}
.scard{background:#fff;border:1px solid #e8e8f0;border-radius:12px;overflow:hidden;text-decoration:none;color:inherit;display:flex;flex-direction:column;transition:all .2s}
.scard:hover{border-color:#d0ceee;transform:translateY(-2px);box-shadow:0 4px 16px rgba(127,119,221,.1)}
.scard-banner{height:90px;position:relative;display:flex;align-items:center;justify-content:center;overflow:hidden}
.scard-emoji{position:relative;z-index:1;font-size:30px;opacity:.25}
.scard-body{padding:12px}
.scard-title{font-size:13px;font-weight:500;color:#1a1a2e;margin-bottom:6px;line-height:1.3}
.scard-meta{font-size:12px;color:#7a7a99;display:flex;align-items:center;gap:5px}
.sidebar-col{}
.reg-card{background:#fff;border:1px solid #e8e8f0;border-radius:16px;padding:22px;position:sticky;top:76px}
.reg-price-wrap{margin-bottom:16px}
.reg-price{font-size:28px;font-weight:600;color:#1a1a2e}
.reg-price-sub{font-size:13px;color:#9999b3;margin-top:3px}
.reg-btn{width:100%;background:#7F77DD;color:#fff;border:none;border-radius:10px;padding:13px;font-size:15px;font-weight:600;cursor:pointer;font-family:inherit;transition:background .15s;margin-bottom:14px;display:block;text-align:center;text-decoration:none}
.reg-btn:hover{background:#534AB7}
.reg-btn.disabled{background:#f0f0f8;color:#9999b3;cursor:default;pointer-events:none}
.reg-btn.waiting{background:#e8a020;color:#fff}
.reg-btn.waiting:hover{background:#c97a10}
.reg-divider{border:none;border-top:1px solid #f0f0f8;margin:14px 0}
.reg-details{display:flex;flex-direction:column;gap:10px}
.reg-row{display:flex;justify-content:space-between;align-items:center;font-size:13px}
.reg-row-label{color:#9999b3}
.reg-row-val{font-weight:500;color:#1a1a2e}
.share-section{margin-top:16px;padding-top:16px;border-top:1px solid #f0f0f8}
.share-label{font-size:12px;color:#9999b3;margin-bottom:8px}
.share-btns{display:flex;gap:8px}
.share-btn{flex:1;padding:8px;border:1px solid #e0e0ee;border-radius:8px;background:transparent;font-size:12px;color:#7a7a99;cursor:pointer;font-family:inherit;transition:all .15s;text-align:center}
.share-btn:hover{background:#EEEDFE;border-color:#AFA9EC;color:#534AB7}
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
  <a href="interfaceevent.php" class="logo">Gestion des <span>Événements</span></a>
  <div class="nav-links">
    <a href="interfaceevent.php">Événements</a>
    <a href="#about">À propos</a>
    <a href="#contact">Contact</a>
  </div>
  <a href="interfaceevent.php" class="back-link">← Retour aux événements</a>
</nav>

<div class="hero">
  <div class="hero-bg"></div>
  <span class="hero-emoji"><?= $tc['emoji'] ?></span>
</div>

<div class="breadcrumb">
  <a href="interfaceevent.php">Événements</a>
  <span style="color:#d0d0e0">/</span>
  <span><?= htmlspecialchars($e->getTitre()) ?></span>
</div>

<div class="layout">

  <div class="main-col">

    <div class="d-badges">
      <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($e->getStatut()) ?></span>
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
          <div class="info-box-label">Horaire</div>
          <div class="info-box-val"><?= $heureDebut ?> → <?= $heureFin ?></div>
        </div>
      </div>
      <div class="info-box">
        <div class="info-box-icon">📍</div>
        <div>
          <div class="info-box-label">Lieu</div>
          <div class="info-box-val"><?= htmlspecialchars($e->getLieu()) ?></div>
        </div>
      </div>
      <div class="info-box">
        <div class="info-box-icon">👥</div>
        <div>
          <div class="info-box-label">Capacité</div>
          <div class="info-box-val"><?= $e->getCapaciteMax() ?> participants max</div>
        </div>
      </div>
    </div>

    <div class="section-block">
      <h2>À propos de l'événement</h2>
      <div class="desc-text">
        <?= nl2br(htmlspecialchars($e->getDescription() ?: 'Aucune description disponible pour cet événement.')) ?>
      </div>
    </div>

    <div class="section-block">
      <h2>Places disponibles</h2>
      <?php
        $pct = 75;
        $fillClass = $pct >= 100 ? 'full' : ($pct >= 80 ? 'warn' : '');
      ?>
      <div class="prog-row">
        <span>Places réservées</span>
        <span><?= $e->getCapaciteMax() ?> max</span>
      </div>
      <div class="prog-track">
        <div class="prog-fill <?= $fillClass ?>" style="width: <?= $pct ?>%"></div>
      </div>
      <div class="prog-sub">
        <?php if ($pct >= 100): ?>
          Complet — plus de places disponibles
        <?php elseif ($pct >= 80): ?>
          ⚡ Dépêchez-vous, il reste peu de places !
        <?php else: ?>
          Des places sont encore disponibles
        <?php endif; ?>
      </div>
    </div>

    <?php if (!empty($suggests)): ?>
    <div class="section-block">
      <h2>Événements similaires</h2>
      <div class="suggests-grid">
        <?php foreach ($suggests as $s):
          $stc = $typeConfig[$s->getType()] ?? $typeConfig['Autre'];
          $sd  = date('d/m/Y', strtotime($s->getDateDebut()));
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
        <div class="reg-price"><?= $isFree ? 'Gratuit' : number_format($e->getPrix(), 2) . ' DT' ?></div>
        <div class="reg-price-sub"><?= $isFree ? 'Entrée entièrement gratuite' : 'Par participant' ?></div>
      </div>

      <?php if ($isActif): ?>
        <a href="register.php?id=<?= $e->getIdEvent() ?>" class="reg-btn">S'inscrire à l'événement</a>
      <?php elseif ($isComplet): ?>
        <a href="register.php?id=<?= $e->getIdEvent() ?>" class="reg-btn waiting">Liste d'attente</a>
      <?php else: ?>
        <span class="reg-btn disabled">Inscriptions fermées</span>
      <?php endif; ?>

      <hr class="reg-divider">

      <div class="reg-details">
        <div class="reg-row">
          <span class="reg-row-label">Date</span>
          <span class="reg-row-val"><?= $dateLabel ?></span>
        </div>
        <div class="reg-row">
          <span class="reg-row-label">Horaire</span>
          <span class="reg-row-val"><?= $heureDebut ?> – <?= $heureFin ?></span>
        </div>
        <div class="reg-row">
          <span class="reg-row-label">Lieu</span>
          <span class="reg-row-val"><?= htmlspecialchars($e->getLieu()) ?></span>
        </div>
        <div class="reg-row">
          <span class="reg-row-label">Capacité</span>
          <span class="reg-row-val"><?= $e->getCapaciteMax() ?> places</span>
        </div>
        <div class="reg-row">
          <span class="reg-row-label">Type</span>
          <span class="reg-row-val"><?= htmlspecialchars($e->getType()) ?></span>
        </div>
        <div class="reg-row">
          <span class="reg-row-label">Statut</span>
          <span class="reg-row-val"><?= htmlspecialchars($e->getStatut()) ?></span>
        </div>
      </div>

      <div class="share-section">
        <div class="share-label">Partager l'événement</div>
        <div class="share-btns">
          <button class="share-btn">🔗 Lien</button>
          <button class="share-btn">📧 Email</button>
          <button class="share-btn">💬 WhatsApp</button>
        </div>
      </div>

    </div>
  </div>

</div>

</body>
</html>