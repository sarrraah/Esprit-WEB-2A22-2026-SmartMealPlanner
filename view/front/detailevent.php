<?php
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

/* ── RATING ── */
.rating-block { margin-bottom: 20px; }
.rating-title { font-size: 13px; font-weight: 600; color: #1a0505; margin-bottom: 10px; }
.stars-row { display:flex; gap:6px; align-items:center; margin-bottom:8px; }
.star-btn {
  font-size: 26px; cursor: pointer; background: none; border: none;
  transition: transform .15s; line-height:1; padding:0;
  filter: grayscale(1); opacity: .4;
}
.star-btn:hover, .star-btn.active { filter: grayscale(0); opacity: 1; transform: scale(1.2); }
.rating-summary { font-size: 13px; color: #9a3535; margin-top: 4px; }
.rating-avg { font-size: 22px; font-weight: 700; color: #b91c1c; }
.rating-msg { font-size: 12px; color: #166534; margin-top:4px; display:none; }
.rating-msg.show { display:block; }

/* ── RECOMMENDATIONS ── */
.reco-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:12px; }
.reco-card {
  background:#fff; border:1px solid #fde8e8; border-radius:12px;
  overflow:hidden; text-decoration:none; color:inherit;
  display:flex; flex-direction:column; transition:all .2s;
}
.reco-card:hover { border-color:#f7c1c1; transform:translateY(-2px); box-shadow:0 4px 16px rgba(185,28,28,.1); }
.reco-banner { height:80px; position:relative; display:flex; align-items:center; justify-content:center; overflow:hidden; }
.reco-banner img { width:100%; height:100%; object-fit:cover; position:absolute; inset:0; }
.reco-body { padding:10px 12px; }
.reco-title { font-size:12px; font-weight:600; color:#1a0505; margin-bottom:4px; line-height:1.3; }
.reco-meta  { font-size:11px; color:#9a3535; }
.reco-stars { font-size:11px; color:#f59e0b; margin-top:3px; }
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

/* ── PAGINATION ── */
.pagination-row {
  display: flex; align-items: center; justify-content: center;
  gap: 6px; margin-top: 14px; flex-wrap: wrap;
}
.page-btn {
  min-width: 32px; height: 32px; padding: 0 8px;
  border: 1px solid #fde8e8; border-radius: 8px;
  background: #fff; color: #9a3535; font-size: 13px;
  font-family: inherit; cursor: pointer; transition: all .15s;
  display: flex; align-items: center; justify-content: center;
}
.page-btn:hover   { background: #fce8e8; border-color: #f09595; color: #7f1d1d; }
.page-btn.active  { background: #b91c1c; color: #fff; border-color: #b91c1c; font-weight: 700; }
.page-btn:disabled{ opacity: .4; cursor: default; pointer-events: none; }
.page-info { font-size: 12px; color: #9a3535; }

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
      <div class="pagination-row" id="comments-pagination"></div>

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
      <h2>🤖 Événements recommandés pour vous</h2>
      <div class="reco-grid" id="reco-grid">
        <div style="color:#9a3535;font-size:13px">Chargement...</div>
      </div>
    </div>
    <?php endif; ?>

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
        <a href="#" class="reg-btn" id="open-checkout">Register for this event</a>
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
          <button class="share-btn" id="share-facebook">📘 Facebook</button>
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

    var myComments = [];
    try { myComments = JSON.parse(localStorage.getItem('my_comments_' + ID_EVENT) || '[]'); } catch(e) {}
    var isMine = myComments.indexOf(parseInt(c.id)) !== -1;

    return `
      <div class="comment-item fade-in" data-id="${escapeHtml(c.id)}">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <div>
            <span class="comment-author">${escapeHtml(c.auteur)}</span>
            <span class="comment-date">${escapeHtml(dateLabel)}</span>
          </div>
          ${isMine ? `<button class="comment-delete-btn" title="Supprimer mon commentaire" onclick="deleteComment(${c.id})">🗑</button>` : ''}
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

  var currentPage = 1;
  var totalPages  = 1;
  var COMMENTS_PER_PAGE = 6;

  async function loadCommentaires(page) {
    page = page || 1;
    var list = document.getElementById('comments');
    var pag  = document.getElementById('comments-pagination');
    if (!list) return;

    list.innerHTML = '<div style="color:#9a3535;font-size:13px;padding:10px 0">⏳ Chargement...</div>';
    if (pag) pag.innerHTML = '';

    try {
      var res  = await fetch(
        BASE + 'getCommentaires.php?id_event=' + encodeURIComponent(ID_EVENT)
        + '&page=' + page + '&limit=' + COMMENTS_PER_PAGE,
        { headers: { 'Accept': 'application/json' } }
      );
      var data = await res.json();

      if (!data.comments || data.comments.length === 0) {
        list.innerHTML = '<div class="comment-empty">Aucun commentaire pour linstant.</div>';
        return;
      }

      currentPage = data.page;
      totalPages  = data.pages;

      list.innerHTML = data.comments.map(renderCommentItem).join('');

      if (pag && totalPages > 1) {
        pag.innerHTML =
          '<button class="page-btn" '+ (currentPage <= 1 ? 'disabled' : '') +' onclick="window.loadCommentaires('+(currentPage-1)+')">‹ Précédent</button>'
          + '<span class="page-info">Page ' + currentPage + ' / ' + totalPages + ' · ' + data.total + ' commentaires</span>'
          + '<button class="page-btn" '+ (currentPage >= totalPages ? 'disabled' : '') +' onclick="window.loadCommentaires('+(currentPage+1)+')">Suivant ›</button>';
      } else if (pag) {
        pag.innerHTML = '<span class="page-info">' + data.total + ' commentaire(s)</span>';
      }

      list.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    } catch(e) {
      list.innerHTML = '<div class="comment-empty">Erreur de chargement.</div>';
    }
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

  function renderRatingStars(avgStars, userStars) {
    document.querySelectorAll('.star-btn').forEach(function(btn) {
      var s = parseInt(btn.dataset.star);
      btn.classList.toggle('active', s <= (userStars || Math.round(avgStars)));
    });
    var avgEl   = document.getElementById('rating-avg');
    var totalEl = document.getElementById('rating-total');
    if (avgEl) avgEl.textContent = avgStars > 0 ? avgStars.toFixed(1) : '–';
  }

  async function loadRating(sessionId) {
    try {
      var res  = await fetch(BASE + 'getRating.php?id_event=' + ID_EVENT + '&session_id=' + encodeURIComponent(sessionId));
      var data = await res.json();
      renderRatingStars(data.avg_stars, data.user_stars);
      var totalEl = document.getElementById('rating-total');
      if (totalEl) totalEl.textContent = ' / 5 (' + data.total + ' avis)';
    } catch(e) {}
  }

  function initRating(sessionId) {
    var starsRow = document.getElementById('stars-row');
    if (!starsRow) return;
    var btns = starsRow.querySelectorAll('.star-btn');

    btns.forEach(function(btn) {
      btn.addEventListener('mouseenter', function() {
        var s = parseInt(btn.dataset.star);
        btns.forEach(function(b) { b.style.filter = parseInt(b.dataset.star) <= s ? 'grayscale(0)' : 'grayscale(1)'; b.style.opacity = parseInt(b.dataset.star) <= s ? '1' : '0.4'; });
      });
      btn.addEventListener('mouseleave', function() {
        btns.forEach(function(b) { b.style.filter = ''; b.style.opacity = ''; });
      });
    });

    btns.forEach(function(btn) {
      btn.addEventListener('click', async function() {
        var stars = parseInt(btn.dataset.star);
        try {
          var res  = await fetch(BASE + 'addRating.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_event: ID_EVENT, stars: stars, session_id: sessionId })
          });
          var data = await res.json();
          if (data.success) {
            renderRatingStars(data.avg_stars, data.user_stars);
            var totalEl = document.getElementById('rating-total');
            if (totalEl) totalEl.textContent = ' / 5 (' + data.total + ' avis)';
            var msg = document.getElementById('rating-msg');
            if (msg) { msg.classList.add('show'); setTimeout(function(){ msg.classList.remove('show'); }, 3000); }
          }
        } catch(e) {}
      });
    });
  }

  async function loadRecommendations() {
    var grid = document.getElementById('reco-grid');
    if (!grid) return;
    try {
      var type = <?= json_encode($e->getType(), JSON_UNESCAPED_UNICODE) ?>;
      var res  = await fetch(BASE + 'getRecommendations.php?id_event=' + ID_EVENT + '&type=' + encodeURIComponent(type) + '&limit=3');
      var data = await res.json();
      if (!Array.isArray(data) || data.length === 0) {
        grid.closest('.section-block').style.display = 'none';
        return;
      }
      grid.innerHTML = data.map(function(ev) {
        var prix  = ev.prix == 0 ? 'Gratuit' : parseFloat(ev.prix).toFixed(2) + ' TND';
        var date  = new Date(ev.date_debut).toLocaleDateString('fr-FR');
        var img   = ev.image ? '../../uploads/evenements/' + ev.image : null;
        var stars = '';
        if (ev.avg_stars > 0) {
          for (var i = 1; i <= 5; i++) stars += i <= Math.round(ev.avg_stars) ? '⭐' : '☆';
          stars += ' (' + ev.avg_stars + ')';
        }
        var banner = img
          ? '<img src="' + img + '" alt="">'
          : '<span style="font-size:28px;opacity:.3">📅</span>';
        return '<a class="reco-card" href="detailEvent.php?id=' + ev.id_event + '">'
          + '<div class="reco-banner" style="background:#fce8e8">' + banner + '</div>'
          + '<div class="reco-body">'
          + '<div class="reco-title">' + ev.titre.replace(/</g,'&lt;') + '</div>'
          + '<div class="reco-meta">📅 ' + date + ' · ' + prix + '</div>'
          + (stars ? '<div class="reco-stars">' + stars + '</div>' : '')
          + '</div></a>';
      }).join('');
    } catch(e) {
      if (grid.closest('.section-block')) grid.closest('.section-block').style.display = 'none';
    }
  }

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

  window.loadCommentaires = loadCommentaires;

  function init() {
    var sessionId = ensureSessionId();
    restoreReactedState();
    loadCommentaires(1);
    loadReactions();
    loadRating(sessionId);
    loadRecommendations();

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
        try {
          var myComments = JSON.parse(localStorage.getItem('my_comments_' + ID_EVENT) || '[]');
          myComments.push(parseInt(data.comment.id));
          localStorage.setItem('my_comments_' + ID_EVENT, JSON.stringify(myComments));
        } catch(e) {}
        loadCommentaires(1);
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

  function shareFacebook() {
    var url = encodeURIComponent(PAGE_URL);
    window.open('https://www.facebook.com/sharer/sharer.php?u=' + url, '_blank', 'noopener,noreferrer,width=600,height=400');
  }

  document.addEventListener('DOMContentLoaded', function () {
    var btnLink      = document.getElementById('share-link');
    var btnEmail     = document.getElementById('share-email');
    var btnFacebook  = document.getElementById('share-facebook');
    if (btnLink)     btnLink.addEventListener('click',     copyLink);
    if (btnEmail)    btnEmail.addEventListener('click',    shareEmail);
    if (btnFacebook) btnFacebook.addEventListener('click', shareFacebook);

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

<!-- ══════════════════════════════════════════
     CHECKOUT MODAL
══════════════════════════════════════════ -->
<style>
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9000;display:none;align-items:center;justify-content:center;padding:16px}
.modal-overlay.open{display:flex}
.modal-box{background:#fff;border-radius:18px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;box-shadow:0 24px 60px rgba(0,0,0,0.2)}
.modal-header{background:linear-gradient(135deg,#b91c1c,#e63946);padding:24px 28px;border-radius:18px 18px 0 0;display:flex;justify-content:space-between;align-items:center}
.modal-header h2{color:#fff;font-size:18px;font-weight:700;margin:0}
.modal-close{background:rgba(255,255,255,0.2);border:none;color:#fff;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center}
.modal-body{padding:28px}
.co-group{margin-bottom:16px}
.co-label{font-size:13px;font-weight:600;color:#1a0505;margin-bottom:6px;display:block}
.co-input{width:100%;border:1.5px solid #fde8e8;border-radius:10px;padding:11px 14px;font-size:14px;font-family:inherit;outline:none;transition:border-color .2s,box-shadow .2s;color:#1a0505}
.co-input:focus{border-color:#b91c1c;box-shadow:0 0 0 3px rgba(185,28,28,.1)}
.co-input.err{border-color:#dc2626}
.co-err{font-size:11px;color:#dc2626;margin-top:4px;display:none}
.co-err.show{display:block}
.pay-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px}
.pay-opt input{display:none}
.pay-opt label{display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px;border:2px solid #fde8e8;border-radius:12px;cursor:pointer;font-size:13px;font-weight:600;color:#9a3535;transition:all .15s;text-align:center}
.pay-opt label:hover{border-color:#b91c1c;background:#fff5f5}
.pay-opt input:checked+label{border-color:#b91c1c;background:#fff5f5;color:#b91c1c}
.pay-opt .pay-icon{font-size:26px}
.card-fields{background:#fff5f5;border:1px solid #fde8e8;border-radius:12px;padding:16px;margin-bottom:16px;display:none}
.card-fields.show{display:block}
.card-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.co-btn{width:100%;background:#b91c1c;color:#fff;border:none;border-radius:12px;padding:14px;font-size:15px;font-weight:700;cursor:pointer;font-family:inherit;transition:background .15s}
.co-btn:hover{background:#991b1b}
.co-btn:disabled{background:#fce8e8;color:#9a3535;cursor:default}
.co-divider{border:none;border-top:1px solid #fce8e8;margin:16px 0}
.co-summary{background:#fff5f5;border:1px solid #fde8e8;border-radius:10px;padding:14px;margin-bottom:20px;font-size:13px}
.co-summary-row{display:flex;justify-content:space-between;padding:4px 0}
.co-summary-row.total{font-weight:700;font-size:15px;color:#b91c1c;border-top:1px solid #fde8e8;margin-top:6px;padding-top:8px}
.success-modal{text-align:center;padding:32px 20px;display:none}
.success-modal.show{display:block}
.success-modal .s-icon{font-size:60px;margin-bottom:16px}
.success-modal .s-num{font-size:22px;font-weight:800;color:#b91c1c;letter-spacing:2px;margin:10px 0}
.success-modal .s-msg{font-size:14px;color:#4a1515;line-height:1.7}
</style>

<!-- Modal -->
<div class="modal-overlay" id="checkout-modal">
  <div class="modal-box">
    <div class="modal-header">
      <h2>🎟️ Réserver votre place</h2>
      <button class="modal-close" id="close-modal">✕</button>
    </div>
    <div class="modal-body">

      <!-- Success state -->
      <div class="success-modal" id="success-state">
        <div class="s-icon">🎉</div>
        <div style="font-size:18px;font-weight:700;color:#1a0505;margin-bottom:8px">Réservation confirmée !</div>
        <div class="s-num" id="s-res-num">RES-XXXXXXXX</div>
        <div class="s-msg">
          Votre facture a été envoyée à <strong id="s-email"></strong><br>
          Vérifiez votre boîte mail (et les spams).
        </div>
        <button class="co-btn" style="margin-top:24px;max-width:200px;margin-left:auto;margin-right:auto" onclick="document.getElementById('checkout-modal').classList.remove('open')">Fermer</button>
      </div>

      <!-- Form state -->
      <div id="form-state">
        <!-- Summary -->
        <div class="co-summary">
          <div class="co-summary-row"><span>📅 <?= htmlspecialchars($e->getTitre()) ?></span></div>
          <div class="co-summary-row"><span>📍 <?= htmlspecialchars($e->getLieu()) ?></span><span><?= date('d/m/Y', strtotime($e->getDateDebut())) ?></span></div>
          <div class="co-summary-row total"><span>Prix / place</span><span id="co-price-display"><?= $isFree ? 'Gratuit' : number_format($e->getPrix(), 2) . ' TND' ?></span></div>
        </div>

        <!-- Prenom + Nom -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div class="co-group">
            <label class="co-label">Prénom *</label>
            <input class="co-input" id="co-prenom" placeholder="Ahmed">
            <div class="co-err" id="err-co-prenom">Prénom requis (min 2 car.)</div>
          </div>
          <div class="co-group">
            <label class="co-label">Nom *</label>
            <input class="co-input" id="co-nom" placeholder="Ben Ali">
            <div class="co-err" id="err-co-nom">Nom requis (min 2 car.)</div>
          </div>
        </div>

        <!-- Email -->
        <div class="co-group">
          <label class="co-label">Email *</label>
          <input class="co-input" id="co-email" type="email" placeholder="ahmed@email.com">
          <div class="co-err" id="err-co-email">Email invalide</div>
        </div>

        <!-- Téléphone -->
        <div class="co-group">
          <label class="co-label">Téléphone (Tunisie) *</label>
          <input class="co-input" id="co-tel" placeholder="22 123 456" maxlength="12">
          <div class="co-err" id="err-co-tel">Format invalide (ex: 22123456)</div>
        </div>

        <!-- Places -->
        <div class="co-group">
          <label class="co-label">Nombre de places *</label>
          <input class="co-input" id="co-places" type="number" min="1" max="10" value="1">
          <div class="co-err" id="err-co-places">Entre 1 et 10 places</div>
        </div>

        <?php if (!$isFree): ?>
        <!-- Code promo -->
        <div class="co-group">
          <label class="co-label">🎟️ Code promo (optionnel)</label>
          <div style="display:flex;gap:8px">
            <input class="co-input" id="co-promo" placeholder="EX: SUMMER20" style="text-transform:uppercase;flex:1">
            <button type="button" id="co-promo-btn" style="background:#fce8e8;border:1px solid #f7c1c1;border-radius:10px;padding:0 14px;font-size:13px;color:#9a3535;cursor:pointer;font-family:inherit;white-space:nowrap">Vérifier</button>
          </div>
          <div id="co-promo-result" style="font-size:12px;margin-top:4px;display:none"></div>
        </div>
        <?php endif; ?>

        <hr class="co-divider">

        <!-- Mode paiement -->
        <div class="co-group">
          <label class="co-label">Mode de paiement *</label>
          <?php if ($isFree): ?>
            <input type="hidden" id="co-mode" value="gratuit">
            <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:12px;font-size:13px;color:#166534;font-weight:600">✅ Événement gratuit — aucun paiement requis</div>
          <?php else: ?>
          <div class="pay-grid">
            <div class="pay-opt">
              <input type="radio" name="co-mode" id="mode-carte" value="carte">
              <label for="mode-carte"><span class="pay-icon">💳</span>Carte bancaire</label>
            </div>
            <div class="pay-opt">
              <input type="radio" name="co-mode" id="mode-livraison" value="livraison">
              <label for="mode-livraison"><span class="pay-icon">💵</span>Espèces</label>
            </div>
          </div>
          <div class="co-err" id="err-co-mode">Choisissez un mode de paiement</div>

          <!-- Card fields -->
          <div class="card-fields" id="card-fields">
            <div class="co-group" style="margin-bottom:12px">
              <label class="co-label">Numéro de carte *</label>
              <input class="co-input" id="co-card-num" placeholder="1234 5678 9012 3456" maxlength="19">
              <div class="co-err" id="err-co-card-num">16 chiffres requis</div>
            </div>
            <div class="card-row">
              <div class="co-group" style="margin-bottom:0">
                <label class="co-label">Expiration *</label>
                <input class="co-input" id="co-card-exp" placeholder="MM/YY" maxlength="5">
                <div class="co-err" id="err-co-card-exp">Format MM/YY</div>
              </div>
              <div class="co-group" style="margin-bottom:0">
                <label class="co-label">CVV *</label>
                <input class="co-input" id="co-card-cvv" placeholder="123" maxlength="3" type="password">
                <div class="co-err" id="err-co-card-cvv">3 chiffres requis</div>
              </div>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- Total -->
        <div class="co-summary" id="co-total-box" style="margin-bottom:16px">
          <div class="co-summary-row"><span>Places</span><span id="co-total-places">1 × <?= $isFree ? 'Gratuit' : number_format($e->getPrix(), 2) . ' TND' ?></span></div>
          <div class="co-summary-row total"><span>Total</span><span id="co-total-amount"><?= $isFree ? 'Gratuit' : number_format($e->getPrix(), 2) . ' TND' ?></span></div>
        </div>

        <button class="co-btn" id="co-submit-btn">🎟️ Confirmer la réservation</button>
        <div id="co-global-err" style="color:#dc2626;font-size:13px;text-align:center;margin-top:10px;display:none"></div>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  var ID_EVENT   = <?= (int)$e->getIdEvent() ?>;
  var PRIX       = <?= (float)$e->getPrix() ?>;
  var IS_FREE    = <?= $isFree ? 'true' : 'false' ?>;
  var BASE_URL   = '/projet_nutriplanner/view/back/';
  var promoDiscount = 0;
  var promoCode     = '';

  // Open / close modal
  var openBtn  = document.getElementById('open-checkout');
  var modal    = document.getElementById('checkout-modal');
  var closeBtn = document.getElementById('close-modal');
  if (openBtn)  openBtn.addEventListener('click',  function(e){ e.preventDefault(); modal.classList.add('open'); });
  if (closeBtn) closeBtn.addEventListener('click',  function(){ modal.classList.remove('open'); });
  modal.addEventListener('click', function(e){ if (e.target === modal) modal.classList.remove('open'); });

  // Card fields toggle
  document.querySelectorAll('input[name="co-mode"]').forEach(function(r) {
    r.addEventListener('change', function() {
      var cf = document.getElementById('card-fields');
      if (cf) cf.classList.toggle('show', r.value === 'carte');
    });
  });

  // Card number formatting
  var cardNum = document.getElementById('co-card-num');
  if (cardNum) cardNum.addEventListener('input', function() {
    var v = cardNum.value.replace(/\D/g,'').substring(0,16);
    cardNum.value = v.replace(/(.{4})/g,'$1 ').trim();
  });

  // Expiry formatting
  var cardExp = document.getElementById('co-card-exp');
  if (cardExp) cardExp.addEventListener('input', function() {
    var v = cardExp.value.replace(/\D/g,'');
    if (v.length >= 2) v = v.substring(0,2) + '/' + v.substring(2,4);
    cardExp.value = v;
  });

  // Update total on places change
  var placesInput = document.getElementById('co-places');
  function updateTotal() {
    var p = parseInt((placesInput && placesInput.value) || 1);
    if (isNaN(p) || p < 1) p = 1;
    var base  = PRIX * p;
    var total = Math.max(0, base - promoDiscount * p / (p || 1));
    total = Math.max(0, base - promoDiscount);
    var totalEl  = document.getElementById('co-total-amount');
    var placesEl = document.getElementById('co-total-places');
    if (totalEl)  totalEl.textContent  = IS_FREE ? 'Gratuit' : total.toFixed(2) + ' TND';
    if (placesEl) placesEl.textContent = p + ' × ' + (IS_FREE ? 'Gratuit' : PRIX.toFixed(2) + ' TND');
  }
  if (placesInput) placesInput.addEventListener('input', updateTotal);

  // Promo check
  var promoBtn = document.getElementById('co-promo-btn');
  if (promoBtn) promoBtn.addEventListener('click', async function() {
    var code = (document.getElementById('co-promo').value || '').trim().toUpperCase();
    var res  = document.getElementById('co-promo-result');
    if (!code) return;
    promoBtn.textContent = '…'; promoBtn.disabled = true;
    try {
      var r    = await fetch(BASE_URL + 'checkPromo.php', {
        method: 'POST', headers: {'Content-Type':'application/json'},
        body: JSON.stringify({code: code, id_event: ID_EVENT})
      });
      var data = await r.json();
      res.style.display = 'block';
      if (data.valid) {
        var disc = parseFloat(data.discount);
        promoDiscount = data.type === 'percent' ? PRIX * (disc/100) : Math.min(disc, PRIX);
        promoCode = code;
        res.style.color = '#166534'; res.textContent = '✅ ' + data.label;
        document.getElementById('co-promo').style.borderColor = '#16a34a';
      } else {
        promoDiscount = 0; promoCode = '';
        res.style.color = '#dc2626'; res.textContent = '❌ ' + (data.error || 'Code invalide');
        document.getElementById('co-promo').style.borderColor = '#dc2626';
      }
      updateTotal();
    } catch(e) { res.style.display='block'; res.textContent='❌ Erreur'; }
    finally { promoBtn.textContent='Vérifier'; promoBtn.disabled=false; }
  });

  // Validation helpers
  function setErr(id, show) {
    var el = document.getElementById(id);
    if (el) el.classList.toggle('show', show);
  }
  function setInputErr(id, show) {
    var el = document.getElementById(id);
    if (el) el.classList.toggle('err', show);
  }
  function markErr(inputId, errId, condition) {
    setInputErr(inputId, condition); setErr(errId, condition); return condition;
  }

  // Submit
  var submitBtn = document.getElementById('co-submit-btn');
  if (submitBtn) submitBtn.addEventListener('click', async function() {
    var prenom = (document.getElementById('co-prenom').value || '').trim();
    var nom    = (document.getElementById('co-nom').value    || '').trim();
    var email  = (document.getElementById('co-email').value  || '').trim();
    var tel    = (document.getElementById('co-tel').value    || '').trim().replace(/\s/g,'');
    var places = parseInt((placesInput && placesInput.value) || 1);
    var mode   = IS_FREE ? 'gratuit' : (document.querySelector('input[name="co-mode"]:checked') || {}).value || '';

    var hasErr = false;
    hasErr = markErr('co-prenom','err-co-prenom', prenom.length < 2) || hasErr;
    hasErr = markErr('co-nom',   'err-co-nom',    nom.length < 2)    || hasErr;
    hasErr = markErr('co-email', 'err-co-email',  !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) || hasErr;
    hasErr = markErr('co-tel',   'err-co-tel',    !/^(\+216)?[2-9]\d{7}$/.test(tel)) || hasErr;
    hasErr = markErr('co-places','err-co-places', isNaN(places)||places<1||places>10) || hasErr;
    if (!IS_FREE) {
      hasErr = markErr('co-mode', 'err-co-mode', !mode) || hasErr;
      if (mode === 'carte') {
        var cardN = (document.getElementById('co-card-num').value||'').replace(/\s/g,'');
        var cardE = (document.getElementById('co-card-exp').value||'');
        var cardC = (document.getElementById('co-card-cvv').value||'');
        hasErr = markErr('co-card-num','err-co-card-num', !/^\d{16}$/.test(cardN)) || hasErr;
        hasErr = markErr('co-card-exp','err-co-card-exp', !/^\d{2}\/\d{2}$/.test(cardE)) || hasErr;
        hasErr = markErr('co-card-cvv','err-co-card-cvv', !/^\d{3}$/.test(cardC)) || hasErr;
      }
    }
    if (hasErr) return;

    submitBtn.disabled = true; submitBtn.textContent = '⏳ Traitement...';
    var globalErr = document.getElementById('co-global-err');
    if (globalErr) globalErr.style.display = 'none';

    try {
      // Check availability
      var avRes  = await fetch(BASE_URL + 'update_places.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({id_event: ID_EVENT, places: places})
      });
      var avData = await avRes.json();
      if (!avData.success) {
        if (globalErr) { globalErr.textContent = avData.error || 'Places insuffisantes'; globalErr.style.display='block'; }
        submitBtn.disabled=false; submitBtn.textContent='🎟️ Confirmer la réservation';
        return;
      }

      // Send invoice & save
      var invRes  = await fetch(BASE_URL + 'send_invoice.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({
          id_event: ID_EVENT, prenom: prenom, nom: nom,
          email: email, telephone: tel, places: places,
          mode_paiement: mode, promo_code: promoCode
        })
      });
      var invData = await invRes.json();

      if (invData.success) {
        document.getElementById('form-state').style.display   = 'none';
        document.getElementById('success-state').style.display = 'block';
        document.getElementById('s-res-num').textContent = invData.reservation_num;
        document.getElementById('s-email').textContent   = email;
        // Save email for Goals & Rewards
        try { localStorage.setItem('smp_user_email', email); } catch(e) {}
        // Show milestone reward if unlocked
        if (invData.milestone_code) {
          var sState = document.getElementById('success-state');
          var rewardDiv = document.createElement('div');
          rewardDiv.style.cssText = 'margin-top:16px;background:linear-gradient(135deg,#fff7ed,#fef3c7);border:2px solid #fbbf24;border-radius:12px;padding:16px;text-align:center';
          rewardDiv.innerHTML = '<div style="font-size:13px;font-weight:700;color:#92400e;margin-bottom:8px">🎉 Milestone Unlocked: ' + invData.milestone_label + '!</div>'
            + '<div style="font-size:22px;font-weight:900;color:#b91c1c;letter-spacing:3px;background:#fff;border:2px dashed #fbbf24;border-radius:8px;padding:8px 16px;display:inline-block">' + invData.milestone_code + '</div>'
            + '<div style="font-size:12px;color:#78350f;margin-top:8px">' + invData.milestone_disc + '% off your next registration · Valid 30 days</div>';
          sState.appendChild(rewardDiv);
        }
      } else {
        var msg = invData.error || (invData.errors ? invData.errors.join(', ') : 'Erreur');
        if (globalErr) { globalErr.textContent = msg; globalErr.style.display='block'; }
        submitBtn.disabled=false; submitBtn.textContent='🎟️ Confirmer la réservation';
      }
    } catch(e) {
      if (globalErr) { globalErr.textContent='Erreur de connexion.'; globalErr.style.display='block'; }
      submitBtn.disabled=false; submitBtn.textContent='🎟️ Confirmer la réservation';
    }
  });
})();
</script>

</body>
</html>