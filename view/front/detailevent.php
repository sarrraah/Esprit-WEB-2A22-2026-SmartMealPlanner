<?php
/**
 * SQL (run once):
 * CREATE TABLE IF NOT EXISTS commentaire (
 *   id INT AUTO_INCREMENT PRIMARY KEY,
 *   id_event INT NOT NULL,
 *   auteur VARCHAR(100) NOT NULL,
 *   contenu TEXT NOT NULL,
 *   created_at DATETIME DEFAULT NOW(),
 *   FOREIGN KEY (id_event) REFERENCES evenement(id_event) ON DELETE CASCADE
 * );
 * CREATE TABLE IF NOT EXISTS reaction (
 *   id INT AUTO_INCREMENT PRIMARY KEY,
 *   id_event INT NOT NULL,
 *   type ENUM('❤️','😂','😮','😢','👏','🔥') NOT NULL,
 *   session_id VARCHAR(100) NOT NULL,
 *   UNIQUE KEY unique_reaction (id_event, session_id, type),
 *   FOREIGN KEY (id_event) REFERENCES evenement(id_event) ON DELETE CASCADE
 * );
 */
require_once __DIR__ . '/../../controller/EvenementController.php';

$ctrl = new EvenementController();
$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$e    = $ctrl->getEvenementById($id);

if (!$e) { header('Location: interfaceevent.php'); exit; }

$typeConfig = [
    'Conférence'  => ['from' => '#fce8e8', 'to' => '#f7c1c1', 'emoji' => '🎤'],
    'Atelier'     => ['from' => '#fde8e8', 'to' => '#f09595', 'emoji' => '🛠️'],
    'Compétition' => ['from' => '#fce8e8', 'to' => '#e24b4a', 'emoji' => '🏆'],
    'Forum'       => ['from' => '#fdf0f0', 'to' => '#f7c1c1', 'emoji' => '💬'],
    'Séminaire'   => ['from' => '#fff5f5', 'to' => '#f09595', 'emoji' => '📚'],
    'Autre'       => ['from' => '#fdf5f5', 'to' => '#e8d5d5', 'emoji' => '📅'],
];

$statusLabels = ['actif' => 'Active', 'annulé' => 'Cancelled', 'terminé' => 'Ended'];

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
$imgPath  = $e->getImage() ? '../../uploads/evenements/' . $e->getImage() : null;

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#fff5f5;color:#1a0505;min-height:100vh}

nav{background:#fff;border-bottom:1.5px solid #f7c1c1;padding:0 32px;display:flex;align-items:center;justify-content:space-between;height:60px;position:sticky;top:0;z-index:100}
.logo{font-size:18px;font-weight:600;color:#1a0505;text-decoration:none}.logo span{color:#b91c1c}
.nav-links{display:flex;gap:28px}
.nav-links a{font-size:14px;color:#9a3535;text-decoration:none;font-weight:500;transition:color .2s}
.nav-links a:hover{color:#b91c1c}
.back-link{display:flex;align-items:center;gap:6px;font-size:13px;color:#9a3535;text-decoration:none;font-weight:500}
.back-link:hover{color:#b91c1c}

.hero{height:320px;position:relative;display:flex;align-items:center;justify-content:center;overflow:hidden}
.hero-img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0}
.hero-bg{position:absolute;inset:0;background:linear-gradient(135deg,<?= $tc['from'] ?> 0%,<?= $tc['to'] ?> 100%)}
.hero-overlay{position:absolute;inset:0;background:linear-gradient(to top, rgba(0,0,0,0.45) 0%, rgba(0,0,0,0.1) 60%, transparent 100%)}
.hero-emoji{position:relative;z-index:1;font-size:80px;opacity:.25}
.hero-bottom{position:absolute;bottom:20px;left:32px;z-index:2;color:#fff}
.hero-bottom-title{font-size:22px;font-weight:600;line-height:1.3;text-shadow:0 2px 8px rgba(0,0,0,0.3)}
.hero-bottom-sub{font-size:13px;opacity:.8;margin-top:4px}

.breadcrumb{max-width:1100px;margin:0 auto;padding:16px 32px;display:flex;align-items:center;gap:8px;font-size:13px;color:#9a3535}
.breadcrumb a{color:#9a3535;text-decoration:none}.breadcrumb a:hover{color:#b91c1c}
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

/* ── SENTIMENT ── */
.sentiment-block { margin-top: 20px; padding-top: 16px; border-top: 1px solid #fce8e8; }
.sentiment-title { font-size: 13px; font-weight: 600; color: #1a0505; margin-bottom: 12px; display:flex; align-items:center; gap:6px; }
.sentiment-summary {
  background: #fff5f5; border: 1px solid #fde8e8; border-radius: 12px;
  padding: 14px 16px; margin-bottom: 12px;
}
.sentiment-score-row { display:flex; align-items:center; gap:12px; margin-bottom:10px; }
.sentiment-emoji-big { font-size: 36px; }
.sentiment-label { font-size: 16px; font-weight: 700; color: #1a0505; }
.sentiment-sub   { font-size: 12px; color: #9a3535; margin-top:2px; }
.sentiment-bar-wrap { margin-bottom: 6px; }
.sentiment-bar-row  { display:flex; align-items:center; gap:8px; font-size:12px; margin-bottom:5px; }
.sentiment-bar-row span:first-child { width: 70px; color: #9a3535; }
.sentiment-bar-row span:last-child  { width: 32px; text-align:right; color:#1a0505; font-weight:600; }
.sbar { flex:1; height:8px; background:#fce8e8; border-radius:8px; overflow:hidden; }
.sbar-fill { height:100%; border-radius:8px; transition: width .6s ease; }
.sbar-pos  { background: #22c55e; }
.sbar-neu  { background: #f59e0b; }
.sbar-neg  { background: #ef4444; }
.sentiment-details { display:grid; gap:6px; margin-top:10px; }
.sentiment-item {
  display:flex; align-items:flex-start; gap:8px;
  background:#fff; border:1px solid #fde8e8; border-radius:8px; padding:8px 10px;
  font-size:12px;
}
.sentiment-item-emoji { font-size:18px; flex-shrink:0; }
.sentiment-item-author { font-weight:700; color:#1a0505; margin-bottom:2px; }
.sentiment-item-text   { color:#4a1515; line-height:1.4; }
.sentiment-item-badge  {
  margin-left:auto; flex-shrink:0;
  padding:2px 8px; border-radius:20px; font-size:10px; font-weight:700;
}
.badge-pos { background:#dcfce7; color:#166534; }
.badge-neu { background:#fef9c3; color:#854d0e; }
.badge-neg { background:#fee2e2; color:#991b1b; }
.sentiment-btn {
  background: #fff; border: 1px solid #f7c1c1; border-radius: 8px;
  padding: 8px 14px; font-size: 12px; color: #9a3535; cursor: pointer;
  font-family: inherit; transition: all .15s; width:100%; margin-top:8px;
}
.sentiment-btn:hover { background:#fce8e8; color:#7f1d1d; }
.sentiment-loading { text-align:center; padding:16px; color:#9a3535; font-size:13px; }
.reactions-bar { display:flex; gap: 10px; flex-wrap: wrap; }
.reaction-btn {
  background: #f8f8f8; border: 2px solid transparent; border-radius: 50px;
  padding: 8px 16px; font-size: 18px; cursor: pointer; transition: all 0.2s;
  display: flex; flex-direction: column; align-items: center; gap: 2px;
}
.reaction-btn:hover { background: #fff0f0; border-color: #e63946; transform: scale(1.1); }
.reaction-btn.reacted { background: #ffe5e5; border-color: #e63946; }
.reaction-count { font-size: 11px; color: #888; font-weight: 600; }

.comments-list { margin-top: 16px; }
.comment-item { padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
.comment-item:last-child{ border-bottom:none; }
.comment-author { font-weight: 700; color: #111; font-size: 14px; }
.comment-date { font-size: 11px; color: #aaa; margin-left: 8px; }
.comment-text { font-size: 14px; color: #444; margin-top: 4px; }
.comment-empty { color:#999; font-size: 13px; padding: 10px 0; }
.comment-delete-btn {
  background: none; border: none; cursor: pointer;
  color: #ccc; font-size: 14px; padding: 2px 6px; border-radius: 6px;
  transition: all .15s; line-height: 1;
}
.comment-delete-btn:hover { color: #e63946; background: #fff0f0; }

.comment-form { margin-top: 14px; display:grid; gap: 10px; }
.comment-form input, .comment-form textarea {
  width: 100%;
  border: 1px solid #e5e5e5;
  border-radius: 10px;
  padding: 10px 12px;
  font-size: 13px;
  outline: none;
  font-family: inherit;
}
.comment-form input:focus, .comment-form textarea:focus { border-color: rgba(230,57,70,0.5); box-shadow: 0 0 0 3px rgba(230,57,70,0.10); }
.comment-btn {
  background: #e63946;
  color: #fff;
  border: none;
  border-radius: 999px;
  padding: 9px 16px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  width: fit-content;
}
.comment-error { color:#dc2626; font-size: 12px; display:none; }
.comment-error.show{ display:block; }
.fade-in { animation: fadeIn 220ms ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0);} }

.prog-row{display:flex;justify-content:space-between;font-size:13px;margin-bottom:8px}
.prog-row span:first-child{color:#9a3535}
.prog-row span:last-child{font-weight:600;color:#1a0505}
.prog-track{height:8px;background:#fce8e8;border-radius:8px;overflow:hidden;margin-bottom:8px}
.prog-fill{height:100%;border-radius:8px;background:#b91c1c;transition:width .4s}
.prog-fill.warn{background:#e8a020}.prog-fill.full{background:#7f1d1d}
.prog-sub{font-size:12px;color:#9a3535}

.suggests-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px}
.scard{background:#fff;border:1px solid #fde8e8;border-radius:12px;overflow:hidden;text-decoration:none;color:inherit;display:flex;flex-direction:column;transition:all .2s}
.scard:hover{border-color:#f7c1c1;transform:translateY(-2px);box-shadow:0 4px 16px rgba(185,28,28,.1)}
.scard-banner{height:90px;position:relative;display:flex;align-items:center;justify-content:center;overflow:hidden}
.scard-banner img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0}
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
.reg-btn.waiting{background:#e8a020;color:#fff}.reg-btn.waiting:hover{background:#c97a10}
.reg-divider{border:none;border-top:1px solid #fce8e8;margin:14px 0}
.reg-details{display:flex;flex-direction:column;gap:10px}
.reg-row{display:flex;justify-content:space-between;align-items:center;font-size:13px}
.reg-row-label{color:#9a3535}.reg-row-val{font-weight:500;color:#1a0505}
.share-section{margin-top:16px;padding-top:16px;border-top:1px solid #fce8e8}
.share-label{font-size:12px;color:#9a3535;margin-bottom:8px}
.share-btns{display:flex;gap:8px}
.share-btn{flex:1;padding:8px;border:1px solid #f7c1c1;border-radius:8px;background:transparent;font-size:12px;color:#9a3535;cursor:pointer;font-family:inherit;transition:all .15s;text-align:center}
.share-btn:hover{background:#fce8e8;border-color:#f09595;color:#7f1d1d}

@media(max-width:900px){
  .layout{grid-template-columns:1fr;padding:0 16px 40px}
  .sidebar-col{order:-1}.reg-card{position:static}
  .info-boxes{grid-template-columns:1fr}
  nav{padding:0 16px}.breadcrumb{padding:12px 16px}
  .hero-bottom{left:16px}
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
  <?php if ($imgPath): ?>
    <img class="hero-img" src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($e->getTitre()) ?>">
    <div class="hero-overlay"></div>
    <div class="hero-bottom">
      <div class="hero-bottom-title"><?= htmlspecialchars($e->getTitre()) ?></div>
      <div class="hero-bottom-sub"><?= htmlspecialchars($e->getLieu()) ?> · <?= $dateLabel ?></div>
    </div>
  <?php else: ?>
    <div class="hero-bg"></div>
    <span class="hero-emoji"><?= $tc['emoji'] ?></span>
  <?php endif; ?>
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
        <div><div class="info-box-label">Date</div><div class="info-box-val"><?= $dateLabel ?></div></div>
      </div>
      <div class="info-box">
        <div class="info-box-icon">🕐</div>
        <div><div class="info-box-label">Time</div><div class="info-box-val"><?= $heureDebut ?> → <?= $heureFin ?></div></div>
      </div>
      <div class="info-box">
        <div class="info-box-icon">📍</div>
        <div><div class="info-box-label">Location</div><div class="info-box-val"><?= htmlspecialchars($e->getLieu()) ?></div></div>
      </div>
      <div class="info-box">
        <div class="info-box-icon">👥</div>
        <div><div class="info-box-label">Capacity</div><div class="info-box-val"><?= $e->getCapaciteMax() ?> participants max</div></div>
      </div>
    </div>

    <div class="section-block">
      <h2>About this event</h2>
      <div class="desc-text">
        <?= nl2br(htmlspecialchars($e->getDescription() ?: 'No description available.')) ?>
      </div>
    </div>

    <div class="section-block" id="commentsReactions">
      <h2>💬 Commentaires & Réactions</h2>

      <div class="reactions-bar" id="reactions">
        <?php foreach (['❤️','😂','😮','😢','👏','🔥'] as $emoji): ?>
          <button type="button" class="reaction-btn" data-type="<?= $emoji ?>">
            <span><?= $emoji ?></span>
            <span class="reaction-count" data-count-for="<?= $emoji ?>">0</span>
          </button>
        <?php endforeach; ?>
      </div>

      <div class="comments-list" id="comments">
        <div class="comment-empty">Aucun commentaire pour l'instant.</div>
      </div>

      <div class="comment-form">
        <div class="comment-error" id="comment-err"></div>
        <input placeholder="Votre nom" id="auteur">
        <textarea placeholder="Votre commentaire..." id="contenu" rows="3"></textarea>
        <button type="button" class="comment-btn" id="submitComment">Publier</button>
      </div>

      <!-- Sentiment Analysis -->
      <div class="sentiment-block" id="sentiment-block">
        <div class="sentiment-title">🧠 Analyse Sentimentale</div>
        <div id="sentiment-content">
          <button class="sentiment-btn" id="sentiment-load-btn">
            🔍 Analyser les commentaires
          </button>
        </div>
      </div>
    </div>

    <div class="section-block">
      <h2>Available seats</h2>
      <?php $pct = 75; $fillClass = $pct >= 100 ? 'full' : ($pct >= 80 ? 'warn' : ''); ?>
      <div class="prog-row"><span>Reserved seats</span><span><?= $e->getCapaciteMax() ?> max</span></div>
      <div class="prog-track"><div class="prog-fill <?= $fillClass ?>" style="width:<?= $pct ?>%"></div></div>
      <div class="prog-sub">
        <?php if ($pct >= 100): ?>Fully booked — no seats remaining
        <?php elseif ($pct >= 80): ?>⚡ Hurry up, only a few seats left!
        <?php else: ?>Seats are still available<?php endif; ?>
      </div>
    </div>

    <?php if (!empty($suggests)): ?>
    <div class="section-block">
      <h2>Similar events</h2>
      <div class="suggests-grid">
        <?php foreach ($suggests as $s):
          $stc      = $typeConfig[$s->getType()] ?? $typeConfig['Autre'];
          $sd       = date('m/d/Y', strtotime($s->getDateDebut()));
          $sImgPath = $s->getImage() ? '../../uploads/evenements/' . $s->getImage() : null;
        ?>
        <a class="scard" href="detailEvent.php?id=<?= $s->getIdEvent() ?>">
          <div class="scard-banner" style="background:linear-gradient(135deg,<?= $stc['from'] ?>,<?= $stc['to'] ?>)">
            <?php if ($sImgPath): ?>
              <img src="<?= htmlspecialchars($sImgPath) ?>" alt="">
            <?php else: ?>
              <span class="scard-emoji"><?= $stc['emoji'] ?></span>
            <?php endif; ?>
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
        <a href="../back/addParticipation.php?id_event=<?= $e->getIdEvent() ?>" class="reg-btn">Register for this event</a>
      <?php elseif ($isComplet): ?>
        <a href="../back/addParticipation.php?id_event=<?= $e->getIdEvent() ?>" class="reg-btn waiting">Join waiting list</a>
      <?php else: ?>
        <span class="reg-btn disabled">Registration closed</span>
      <?php endif; ?>

      <hr class="reg-divider">
      <div class="reg-details">
        <div class="reg-row"><span class="reg-row-label">Date</span><span class="reg-row-val"><?= $dateLabel ?></span></div>
        <div class="reg-row"><span class="reg-row-label">Time</span><span class="reg-row-val"><?= $heureDebut ?> – <?= $heureFin ?></span></div>
        <div class="reg-row"><span class="reg-row-label">Location</span><span class="reg-row-val"><?= htmlspecialchars($e->getLieu()) ?></span></div>
        <div class="reg-row"><span class="reg-row-label">Capacity</span><span class="reg-row-val"><?= $e->getCapaciteMax() ?> seats</span></div>
        <div class="reg-row"><span class="reg-row-label">Type</span><span class="reg-row-val"><?= htmlspecialchars($e->getType()) ?></span></div>
        <div class="reg-row"><span class="reg-row-label">Status</span><span class="reg-row-val"><?= htmlspecialchars($statusDisplay) ?></span></div>
      </div>

      <div class="share-section">
        <div class="share-label">QR Code de l'événement</div>
        <div id="qrcode" style="display:flex;justify-content:center;padding:10px 0"></div>
        <div style="font-size:11px;color:#9a3535;text-align:center;margin-top:4px">Scannez pour accéder à cet événement</div>
        <button id="qr-download" style="width:100%;margin-top:8px;background:transparent;border:1px solid #f7c1c1;border-radius:8px;padding:7px;font-size:12px;color:#9a3535;cursor:pointer;font-family:inherit;transition:all .15s">
          ⬇️ Télécharger le QR Code
        </button>
      </div>

      <div class="share-section">
        <div class="share-label">Share this event</div>
        <div class="share-btns">
          <button class="share-btn" id="share-link">🔗 Link</button>
          <button class="share-btn" id="share-email">📧 Email</button>
          <button class="share-btn" id="share-whatsapp">💬 WhatsApp</button>
        </div>
        <div id="share-copied" style="display:none;font-size:11px;color:#166534;margin-top:6px">✅ Lien copié !</div>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var ID_EVENT = <?= (int)$e->getIdEvent() ?>;
  var REACTION_TYPES = ['❤️','😂','😮','😢','👏','🔥'];
  var BASE = '<?= (isset($_SERVER["HTTPS"])?"https":"http")."://".$_SERVER["HTTP_HOST"]."/projet_nutriplanner/view/back/"; ?>';

  function ensureSessionId() {
    try {
      var id = localStorage.getItem('smp_session_id');
      if (id) return id;
      var uuid = (crypto && crypto.randomUUID) ? crypto.randomUUID() : (Date.now().toString(16) + '-' + Math.random().toString(16).slice(2));
      localStorage.setItem('smp_session_id', uuid);
      return uuid;
    } catch (e) {
      return 'anon-' + Date.now();
    }
  }

  function escapeHtml(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  function renderCommentItem(c) {
    var dt = c.created_at ? new Date(c.created_at) : null;
    var dateLabel = (dt && !isNaN(dt.getTime())) ? dt.toLocaleString() : '';
    return `
      <div class="comment-item fade-in" data-id="${escapeHtml(c.id)}">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <div>
            <span class="comment-author">${escapeHtml(c.auteur)}</span>
            <span class="comment-date">${escapeHtml(dateLabel)}</span>
          </div>
          <button class="comment-delete-btn" title="Supprimer" onclick="deleteComment(${c.id})">🗑</button>
        </div>
        <div class="comment-text">${escapeHtml(c.contenu)}</div>
      </div>
    `;
  }

  function setReactionCounts(counts) {
    document.querySelectorAll('.reaction-btn').forEach(function (btn) {
      var type = btn.dataset.type;
      var el = btn.querySelector('[data-count-for]');
      var v = counts && typeof counts[type] !== 'undefined' ? counts[type] : 0;
      if (el) el.textContent = String(v);
    });
  }

  function restoreReactedState() {
    document.querySelectorAll('.reaction-btn').forEach(function (btn) {
      var type = btn.dataset.type;
      var key = 'reacted_' + ID_EVENT + '_' + type;
      var reacted = false;
      try { reacted = localStorage.getItem(key) === 'true'; } catch (e) {}
      btn.classList.toggle('reacted', reacted);
    });
  }

  async function loadCommentaires() {
    var list = document.getElementById('comments');
    if (!list) return;
    var res = await fetch(BASE + 'getCommentaires.php?id_event=' + encodeURIComponent(ID_EVENT), { headers: { 'Accept':'application/json' }});
    var data = await res.json();
    if (!Array.isArray(data)) return;
    if (data.length === 0) {
      list.innerHTML = '<div class="comment-empty">Aucun commentaire pour linstant.</div>';
      return;
    }
    list.innerHTML = data.map(renderCommentItem).join('');
  }

  async function loadReactions() {
    var res = await fetch(BASE + 'getReactions.php?id_event=' + encodeURIComponent(ID_EVENT), { headers: { 'Accept':'application/json' }});
    var data = await res.json();
    if (data && data.counts) setReactionCounts(data.counts);
  }

  function showErr(msg) {
    var errEl = document.getElementById('comment-err');
    if (!errEl) return;
    errEl.textContent = msg || '';
    errEl.classList.toggle('show', !!msg);
  }

  // ── Sentiment Analysis ────────────────────────────────────────────
  function renderSentiment(data) {
    var content = document.getElementById('sentiment-content');
    if (!content) return;

    if (data.total === 0) {
      content.innerHTML = '<div style="color:#9a3535;font-size:13px;padding:8px 0">Aucun commentaire à analyser.</div>';
      return;
    }

    var posW = data.total > 0 ? Math.round((data.positive / data.total) * 100) : 0;
    var neuW = data.total > 0 ? Math.round((data.neutral  / data.total) * 100) : 0;
    var negW = data.total > 0 ? Math.round((data.negative / data.total) * 100) : 0;

    var detailsHtml = '';
    if (data.details && data.details.length > 0) {
      data.details.forEach(function(d) {
        var badgeCls  = d.sentiment === 'positive' ? 'badge-pos' : (d.sentiment === 'negative' ? 'badge-neg' : 'badge-neu');
        var badgeLbl  = d.sentiment === 'positive' ? 'Positif'   : (d.sentiment === 'negative' ? 'Négatif'   : 'Neutre');
        var contenu   = String(d.contenu || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        var auteur    = String(d.auteur  || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        detailsHtml += `
          <div class="sentiment-item">
            <span class="sentiment-item-emoji">${d.emoji}</span>
            <div style="flex:1;min-width:0">
              <div class="sentiment-item-author">${auteur}</div>
              <div class="sentiment-item-text">${contenu}</div>
            </div>
            <span class="sentiment-item-badge ${badgeCls}">${badgeLbl}</span>
          </div>`;
      });
    }

    content.innerHTML = `
      <div class="sentiment-summary">
        <div class="sentiment-score-row">
          <span class="sentiment-emoji-big">${data.emoji}</span>
          <div>
            <div class="sentiment-label">${data.label}</div>
            <div class="sentiment-sub">${data.pct}% de commentaires positifs · ${data.total} analysés</div>
          </div>
        </div>
        <div class="sentiment-bar-wrap">
          <div class="sentiment-bar-row">
            <span>😊 Positif</span>
            <div class="sbar"><div class="sbar-fill sbar-pos" style="width:${posW}%"></div></div>
            <span>${data.positive}</span>
          </div>
          <div class="sentiment-bar-row">
            <span>😐 Neutre</span>
            <div class="sbar"><div class="sbar-fill sbar-neu" style="width:${neuW}%"></div></div>
            <span>${data.neutral}</span>
          </div>
          <div class="sentiment-bar-row">
            <span>😞 Négatif</span>
            <div class="sbar"><div class="sbar-fill sbar-neg" style="width:${negW}%"></div></div>
            <span>${data.negative}</span>
          </div>
        </div>
      </div>
      <div class="sentiment-details">${detailsHtml}</div>
      <button class="sentiment-btn" id="sentiment-reload-btn">🔄 Réanalyser</button>
    `;

    var reloadBtn = document.getElementById('sentiment-reload-btn');
    if (reloadBtn) reloadBtn.addEventListener('click', runSentimentAnalysis);
  }

  async function runSentimentAnalysis() {
    var content = document.getElementById('sentiment-content');
    if (!content) return;
    content.innerHTML = '<div class="sentiment-loading">⏳ Analyse en cours...</div>';
    try {
      var res  = await fetch(BASE + 'analyzeSentiment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_event: ID_EVENT })
      });
      var data = await res.json();
      if (data.error) {
        content.innerHTML = '<div style="color:#dc2626;font-size:12px">❌ ' + data.error + '</div>';
      } else {
        renderSentiment(data);
      }
    } catch (e) {
      content.innerHTML = '<div style="color:#dc2626;font-size:12px">❌ Erreur de connexion.</div>';
    }
  }

  window.deleteComment = async function(commentId) {
    if (!confirm('Supprimer ce commentaire ?')) return;
    try {
      var res  = await fetch(BASE + 'deleteCommentaire.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: commentId, id_event: ID_EVENT })
      });
      var data = await res.json();
      if (data && data.success) {
        var el = document.querySelector('.comment-item[data-id="' + commentId + '"]');
        if (el) {
          el.style.transition = 'opacity .2s';
          el.style.opacity = '0';
          setTimeout(function () {
            el.remove();
            var list = document.getElementById('comments');
            if (list && list.querySelectorAll('.comment-item').length === 0) {
              list.innerHTML = '<div class="comment-empty">Aucun commentaire pour linstant.</div>';
            }
          }, 200);
        }
      } else {
        alert((data && data.error) ? data.error : 'Erreur lors de la suppression.');
      }
    } catch (e) {
      alert('Erreur de connexion.');
    }
  };

  function init() {
    var sessionId = ensureSessionId();
    restoreReactedState();
    loadCommentaires();
    loadReactions();

    document.querySelectorAll('.reaction-btn').forEach(function (btn) {
      btn.addEventListener('click', async function (e) {
        e.preventDefault();
        var type = btn.dataset.type;
        if (!REACTION_TYPES.includes(type)) return;

        var key = 'reacted_' + ID_EVENT + '_' + type;
        var reacted = btn.classList.contains('reacted');
        try { localStorage.setItem(key, reacted ? 'false' : 'true'); } catch (e2) {}

        var res = await fetch(BASE + 'addReaction.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id_event: ID_EVENT, type: type, session_id: sessionId })
        });
        var data = await res.json();
        if (data && data.success && data.counts) {
          btn.classList.toggle('reacted', !reacted);
          setReactionCounts(data.counts);
        } else {
          try { localStorage.setItem(key, reacted ? 'true' : 'false'); } catch (e3) {}
        }
      });
    });

    var submitBtn = document.getElementById('submitComment');
    var auteurEl = document.getElementById('auteur');
    var contenuEl = document.getElementById('contenu');
    var list = document.getElementById('comments');

    // Sentiment load button
    var sentimentBtn = document.getElementById('sentiment-load-btn');
    if (sentimentBtn) sentimentBtn.addEventListener('click', runSentimentAnalysis);

    if (submitBtn) submitBtn.addEventListener('click', async function () {
      var auteur = (auteurEl && auteurEl.value ? auteurEl.value : '').trim();
      var contenu = (contenuEl && contenuEl.value ? contenuEl.value : '').trim();
      showErr('');

      if (!auteur) { showErr('Veuillez saisir votre nom.'); return; }
      if (contenu.length < 3) { showErr('Le commentaire doit contenir au moins 3 caractères.'); return; }
      if (contenu.length > 500) { showErr('Le commentaire ne doit pas dépasser 500 caractères.'); return; }

      var res = await fetch(BASE + 'addCommentaire.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_event: ID_EVENT, auteur: auteur, contenu: contenu })
      });
      var data = await res.json();
      if (data && data.success && data.comment) {
        if (contenuEl) contenuEl.value = '';
        if (list) {
          var empty = list.querySelector('.comment-empty');
          if (empty) empty.remove();
          list.insertAdjacentHTML('afterbegin', renderCommentItem(data.comment));
        }
      } else {
        showErr((data && data.error) ? data.error : 'Erreur lors de la publication.');
      }
    });
  }

  document.addEventListener('DOMContentLoaded', init);
})();

// ── Share buttons ──────────────────────────────────────────────────────
(function () {
  var EVENT_TITLE = <?= json_encode($e->getTitre(), JSON_UNESCAPED_UNICODE) ?>;
  var PAGE_URL    = window.location.href;

  function copyLink() {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(PAGE_URL).then(function () {
        var msg = document.getElementById('share-copied');
        if (!msg) return;
        msg.style.display = 'block';
        setTimeout(function () { msg.style.display = 'none'; }, 2500);
      });
    } else {
      // Fallback for older browsers
      var ta = document.createElement('textarea');
      ta.value = PAGE_URL;
      ta.style.position = 'fixed';
      ta.style.opacity  = '0';
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
      var msg = document.getElementById('share-copied');
      if (msg) { msg.style.display = 'block'; setTimeout(function () { msg.style.display = 'none'; }, 2500); }
    }
  }

  function shareEmail() {
    var subject = encodeURIComponent('Invitation : ' + EVENT_TITLE);
    var body    = encodeURIComponent('Bonjour,\n\nJe vous invite à découvrir cet événement :\n' + EVENT_TITLE + '\n\n' + PAGE_URL);
    window.location.href = 'mailto:?subject=' + subject + '&body=' + body;
  }

  function shareWhatsApp() {
    var text = encodeURIComponent('🎉 ' + EVENT_TITLE + '\n' + PAGE_URL);
    window.open('https://wa.me/?text=' + text, '_blank', 'noopener,noreferrer');
  }

  document.addEventListener('DOMContentLoaded', function () {
    var btnLink      = document.getElementById('share-link');
    var btnEmail     = document.getElementById('share-email');
    var btnWhatsApp  = document.getElementById('share-whatsapp');
    if (btnLink)     btnLink.addEventListener('click',     copyLink);
    if (btnEmail)    btnEmail.addEventListener('click',    shareEmail);
    if (btnWhatsApp) btnWhatsApp.addEventListener('click', shareWhatsApp);

    // ── QR Code ──
    var qrContainer = document.getElementById('qrcode');
    var qrDownload  = document.getElementById('qr-download');
    if (qrContainer && typeof QRCode !== 'undefined') {
      var qr = new QRCode(qrContainer, {
        text:         window.location.href,
        width:        150,
        height:       150,
        colorDark:    '#b91c1c',
        colorLight:   '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
      });

      // Download button
      if (qrDownload) {
        qrDownload.addEventListener('click', function () {
          setTimeout(function () {
            var canvas = qrContainer.querySelector('canvas');
            if (canvas) {
              var link    = document.createElement('a');
              link.download = 'qrcode-event-<?= (int)$e->getIdEvent() ?>.png';
              link.href   = canvas.toDataURL('image/png');
              link.click();
            }
          }, 100);
        });
      }
    }
  });
})();
</script>

</body>
</html>