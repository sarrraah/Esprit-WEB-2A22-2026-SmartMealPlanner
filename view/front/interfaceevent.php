<?php
/**
 * SQL (run once):
 * ALTER TABLE evenement ADD COLUMN IF NOT EXISTS likes INT DEFAULT 0;
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
$ctrl       = new EvenementController();
$evenements = $ctrl->listEvenements();

$assetPrefix = '../assets/';

$typeConfig = [
    'Conférence'  => ['emoji' => '🎤', 'color' => '#ce1212'],
    'Atelier'     => ['emoji' => '🛠️', 'color' => '#f7971e'],
    'Compétition' => ['emoji' => '🏆', 'color' => '#e24b4a'],
    'Forum'       => ['emoji' => '💬', 'color' => '#11998e'],
    'Séminaire'   => ['emoji' => '📚', 'color' => '#8e44ad'],
    'Autre'       => ['emoji' => '📅', 'color' => '#636e72'],
];

$total = count($evenements);
$types = array_unique(array_map(fn($e) => $e->getType(), $evenements));
sort($types);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Events — Smart Meal Planner</title>

<link href="<?php echo $assetPrefix; ?>img/favicon.jpg" rel="icon">

<link href="https://fonts.googleapis.com" rel="preconnect">
<link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Amatic+SC:wght@400;700&display=swap" rel="stylesheet">

<link href="<?php echo $assetPrefix; ?>vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="<?php echo $assetPrefix; ?>vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="<?php echo $assetPrefix; ?>css/main.css" rel="stylesheet">

<style>
/* ── FILTERS ── */
.filters-section {
  background: white;
  padding: 20px;
  border-radius: 15px;
  margin-bottom: 30px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.filter-btn {
  margin: 5px;
  padding: 8px 20px;
  border-radius: 25px;
  border: 1px solid #ddd;
  background: white;
  font-family: 'Inter', sans-serif;
  font-size: 13px;
  transition: 0.3s;
  cursor: pointer;
}
.filter-btn.active, .filter-btn:hover {
  background: var(--accent-color, #ce1212);
  color: white;
  border-color: var(--accent-color, #ce1212);
}
.sort-select {
  padding: 8px 16px;
  border-radius: 25px;
  border: 1px solid #ddd;
  font-family: 'Inter', sans-serif;
  font-size: 13px;
  outline: none;
  cursor: pointer;
}

/* ── EVENT CARD ── */
.event-card {
  background: white;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 5px 20px rgba(0,0,0,0.08);
  transition: transform 0.3s, box-shadow 0.3s;
  margin-bottom: 30px;
  cursor: pointer;
  height: 100%;
}
.event-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 15px 40px rgba(206,18,18,0.15);
}
.event-img {
  width: 100%;
  height: 200px;
  object-fit: cover;
}
.event-img-placeholder {
  width: 100%;
  height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 60px;
  opacity: 0.4;
}
.event-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
}
.event-price-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  background: rgba(206,18,18,0.88);
  color: white;
}
.badge-actif   { background: #28a745; color: white; }
.badge-annule  { background: #dc3545; color: white; }
.badge-termine { background: #6c757d; color: white; }

.event-info { padding: 18px; }
.event-title {
  font-size: 1rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 10px;
  line-height: 1.4;
}
.event-meta {
  font-size: 0.82rem;
  color: #666;
  margin-bottom: 6px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.event-meta i { color: var(--accent-color, #ce1212); width: 14px; }
.event-price {
  font-size: 1.4rem;
  font-weight: 700;
  color: var(--accent-color, #ce1212);
  margin: 12px 0 8px;
}
.btn-register {
  background: var(--accent-color, #ce1212);
  color: white;
  border: none;
  padding: 9px 20px;
  border-radius: 25px;
  font-weight: 500;
  font-family: 'Inter', sans-serif;
  transition: 0.3s;
  width: 100%;
  font-size: 13px;
}
.btn-register:hover { background: #b00e0e; transform: scale(1.02); color: white; }
.btn-register.closed { background: #ccc; cursor: not-allowed; }
.btn-register.waiting { background: #b45309; }
.btn-register.waiting:hover { background: #92400e; }

/* ── LIKE BUTTON ── */
.like-row {
  margin-top: 10px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}
.like-btn {
  border: 1px solid #eee;
  background: #fff;
  border-radius: 999px;
  padding: 7px 12px;
  font-size: 13px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.2s;
  user-select: none;
  color: #aaa;
}
.like-btn:hover {
  transform: translateY(-1px);
  border-color: rgba(206,18,18,0.35);
  box-shadow: 0 10px 24px rgba(206,18,18,0.10);
}
.like-btn.liked {
  border-color: rgba(206,18,18,0.6);
  box-shadow: 0 12px 26px rgba(206,18,18,0.14);
  color: #e63946;
}
.like-heart {
  display: inline-block;
  transform-origin: center;
}
@keyframes likePulse {
  0%   { transform: scale(1); }
  35%  { transform: scale(1.28); }
  100% { transform: scale(1); }
}
.like-btn.pulse .like-heart {
  animation: likePulse .28s ease-in-out;
}

/* ── COMMENTS + REACTIONS ── */
.cr-toggle {
  margin-top: 12px;
  border: 1px solid #eee;
  background: #fff;
  border-radius: 999px;
  padding: 8px 12px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  color: #333;
  transition: all 0.2s;
}
.cr-toggle:hover { border-color: rgba(230,57,70,0.35); box-shadow: 0 10px 24px rgba(230,57,70,0.10); transform: translateY(-1px); }
.comment-count-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #e63946;
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  min-width: 20px;
  height: 20px;
  border-radius: 999px;
  padding: 0 6px;
  margin: 0 2px;
}
.cr-wrap {
  overflow: hidden;
  max-height: 0;
  opacity: 0;
  transform: translateY(-6px);
  transition: max-height 420ms ease, opacity 320ms ease, transform 320ms ease;
}
.cr-wrap.open {
  max-height: 1200px;
  opacity: 1;
  transform: translateY(0);
}
.cr-card {
  margin-top: 14px;
  background: #fff;
  border-radius: 12px;
  padding: 20px;
  border: 1px solid #f0f0f0;
  box-shadow: 0 8px 30px rgba(0,0,0,0.06);
}
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
.comment-form { margin-top: 14px; display:grid; gap: 10px; }
.comment-form input, .comment-form textarea {
  width: 100%;
  border: 1px solid #e5e5e5;
  border-radius: 10px;
  padding: 10px 12px;
  font-size: 13px;
  outline: none;
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

/* ── MODAL ── */
.modal-event-img {
  width: 100%;
  height: 250px;
  object-fit: cover;
  border-radius: 10px;
}
.modal-event-placeholder {
  width: 100%;
  height: 250px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 80px;
  opacity: 0.3;
}
.modal-price {
  font-size: 2rem;
  color: var(--accent-color, #ce1212);
  font-weight: 700;
}
.info-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 0;
  border-bottom: 1px solid #f0f0f0;
  font-size: 14px;
}
.info-row i { color: var(--accent-color, #ce1212); width: 18px; }
.info-row strong { color: #333; min-width: 80px; }

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #999;
}
.empty-state i { font-size: 3rem; color: #ddd; margin-bottom: 16px; display: block; }

/* Section title — match friend's style */
.section-title h2 {
  font-size: 14px;
  font-weight: 500;
  padding: 0;
  line-height: 1px;
  margin: 0;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: color-mix(in srgb, var(--default-color), transparent 50%);
  position: relative;
}
.section-title h2::after {
  content: "";
  width: 120px;
  height: 1px;
  display: inline-block;
  background: var(--accent-color, #ce1212);
  margin: 4px 10px;
}
.section-title p {
  color: var(--heading-color);
  margin: 0;
  font-size: 36px;
  font-weight: 700;
  font-family: "Amatic SC", sans-serif;
}
.section-title p span { color: var(--accent-color, #ce1212); }
</style>
</head>
<body class="index-page">

<!-- NAVBAR — same as friend -->
<header id="header" class="header d-flex align-items-center sticky-top">
  <div class="container position-relative d-flex align-items-center justify-content-between">

    <a href="../index.php" class="logo d-flex align-items-center me-auto me-xl-0">
      <img src="<?php echo $assetPrefix; ?>img/logo-smp.jpg" alt="SmartMealPlanner" height="44">
      <h1 class="sitename">SmartMealPlanner</h1>
    </a>

    <nav id="navmenu" class="navmenu">
      <ul>
        <li><a href="../index.php">Home</a></li>
        <li><a href="../view/Meals.php">Meals</a></li>
        <li><a href="#" class="active">Events</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="#contact">Contact</a></li>
      </ul>
      <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
    </nav>

    <a class="btn-getstarted" href="#events">Browse Events</a>

  </div>
</header>

<main class="main">

  <!-- HERO — match friend's section style -->
  <section id="hero" class="hero section light-background">
    <div class="container">
      <div class="row gy-4 justify-content-center justify-content-lg-between">
        <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column justify-content-center">
          <h1>Discover <span style="color:var(--accent-color,#ce1212)">exceptional</span> events</h1>
          <p>Conferences, workshops, competitions — register in just a few clicks.</p>
          <div class="d-flex">
            <a href="#events" class="btn-get-started">Browse Events</a>
          </div>
        </div>
        <div class="col-lg-5 order-1 order-lg-2 hero-img">
          <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800"
               class="img-fluid animated rounded-4 shadow" alt="Events">
        </div>
      </div>
    </div>
  </section>

  <!-- GOALS & REWARDS -->
  <section id="goals-rewards" class="section py-5" style="background:#fffbf5;">
    <div class="container section-title">
      <h2>Goals & Rewards</h2>
      <p><span>Register</span> <span class="description-title">& Unlock Discounts 🎯</span></p>
    </div>
    <div class="container">
      <div style="background:#fff;border:1px solid #fde8e8;border-radius:16px;padding:28px;max-width:700px;margin:0 auto">

        <!-- Email input -->
        <div style="margin-bottom:20px">
          <p style="font-size:14px;color:#666;margin-bottom:12px">Register for events and unlock exclusive discount codes. Enter your email to see your progress.</p>
          <div style="display:flex;gap:10px">
            <input type="email" id="goals-email" placeholder="your@email.com"
              style="flex:1;border:1px solid #fde8e8;border-radius:999px;padding:10px 16px;font-size:13px;outline:none;font-family:'Inter',sans-serif">
            <button onclick="loadGoals()" id="goals-check-btn"
              style="background:#ce1212;color:#fff;border:none;border-radius:999px;padding:10px 20px;font-size:13px;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;white-space:nowrap">
              Check Progress
            </button>
          </div>
        </div>

        <div id="goals-content">
          <!-- filled by JS -->
        </div>
      </div>
    </div>
  </section>

  <!-- AI RECOMMENDATIONS -->
  <section id="ai-picks" class="section py-5" style="background:#fff;">
    <div class="container section-title">
      <h2>AI Selection</h2>
      <p><span>Personalized</span> <span class="description-title">Picks</span></p>
    </div>
    <div class="container">
      <div style="background:linear-gradient(135deg,#fff5f5,#fff);border:1px solid #fde8e8;border-radius:16px;padding:24px;margin-bottom:10px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;flex-wrap:wrap;gap:10px">
          <p style="margin:0;font-size:14px;color:#666;max-width:600px">
            Our AI analyzes attendee ratings, popularity and event diversity to suggest the 3 best picks for you right now.
          </p>
          <button id="ai-refresh-btn" onclick="loadAIRecommendations()"
            style="background:#fff;border:1px solid #f7c1c1;border-radius:999px;padding:8px 18px;font-size:13px;color:#ce1212;cursor:pointer;font-family:'Inter',sans-serif;display:flex;align-items:center;gap:6px;transition:all .2s">
            🔄 Refresh
          </button>
        </div>
        <div id="ai-reco-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;margin-top:16px">
          <div style="color:#9a3535;font-size:13px;padding:20px 0">⏳ Loading AI recommendations...</div>
        </div>
      </div>
    </div>
  </section>

  <!-- EVENTS SECTION -->
  <section id="events" class="section light-background py-5">
    <div class="container section-title">
      <h2>Events</h2>
      <p><span>Browse</span> <span class="description-title">All Events</span></p>
    </div>
    <div class="container">

      <!-- FILTERS -->
      <div class="filters-section">
        <div class="row align-items-center">
          <div class="col-md-7">
            <strong style="font-size:13px">Filter by type :</strong>
            <button class="filter-btn active" data-filter="all">All (<?= $total ?>)</button>
            <?php foreach ($types as $type):
              $cnt = count(array_filter($evenements, fn($e) => $e->getType() === $type));
            ?>
            <button class="filter-btn" data-filter="<?= htmlspecialchars($type, ENT_QUOTES) ?>">
              <?= htmlspecialchars($type) ?> (<?= $cnt ?>)
            </button>
            <?php endforeach; ?>
          </div>
          <div class="col-md-3 mt-2 mt-md-0">
            <input type="text" id="searchInput" class="form-control" placeholder="🔍 Search events..."
                   oninput="filterAndRender()"
                   style="border-radius:25px;border:1px solid #ddd;font-family:'Inter',sans-serif;font-size:13px">
          </div>
          <div class="col-md-2 mt-2 mt-md-0">
            <select id="sortSel" class="sort-select w-100" onchange="filterAndRender()">
              <option value="date">By date</option>
              <option value="prix_asc">Price ↑</option>
              <option value="prix_desc">Price ↓</option>
              <option value="titre">A → Z</option>
            </select>
          </div>
        </div>
      </div>

      <!-- EVENTS GRID -->
      <div class="row" id="eventsGrid">
        <?php if (empty($evenements)): ?>
          <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            No events available at the moment.
          </div>
        <?php else: ?>
          <?php foreach ($evenements as $e):
            $tc        = $typeConfig[$e->getType()] ?? $typeConfig['Autre'];
            $dateDebut = date('d/m/Y', strtotime($e->getDateDebut()));
            $dateFin   = date('d/m/Y', strtotime($e->getDateFin()));
            $dateLabel = ($dateDebut === $dateFin) ? $dateDebut : "$dateDebut → $dateFin";
            $isFree    = ($e->getPrix() == 0);
            $priceLabel= $isFree ? 'Free' : number_format($e->getPrix(), 2) . ' TND';
            $imgPath   = $e->getImage() ? '../../uploads/evenements/' . $e->getImage() : null;
            $statut    = strtolower($e->getStatut());
            $badgeCls  = match(true) {
              str_contains($statut, 'actif')  => 'badge-actif',
              str_contains($statut, 'annul')  => 'badge-annule',
              default                         => 'badge-termine',
            };
            $statusLabel = match(true) {
              str_contains($statut, 'actif')  => 'Active',
              str_contains($statut, 'annul')  => 'Cancelled',
              default                         => 'Ended',
            };
          ?>
          <div class="col-md-6 col-lg-4 event-item"
               data-type="<?= htmlspecialchars($e->getType()) ?>"
               data-titre="<?= htmlspecialchars(strtolower($e->getTitre())) ?>"
               data-lieu="<?= htmlspecialchars(strtolower($e->getLieu())) ?>"
               data-date="<?= $e->getDateDebut() ?>"
               data-prix="<?= $e->getPrix() ?>">
            <div class="event-card" data-event-id="<?= (int)$e->getIdEvent() ?>"
                 onclick="window.location='detailEvent.php?id=<?= $e->getIdEvent() ?>'"
                 style="cursor:pointer">
              <div style="position:relative">
                <?php if ($imgPath): ?>
                  <img src="<?= htmlspecialchars($imgPath) ?>" class="event-img" alt="<?= htmlspecialchars($e->getTitre()) ?>">
                <?php else: ?>
                  <div class="event-img-placeholder" style="background:linear-gradient(135deg,<?= $tc['color'] ?>22,<?= $tc['color'] ?>44)">
                    <?= $tc['emoji'] ?>
                  </div>
                <?php endif; ?>
                <span class="event-badge <?= $badgeCls ?>"><?= $statusLabel ?></span>
                <span class="event-price-badge"><?= $priceLabel ?></span>
              </div>
              <div class="event-info">
                <h5 class="event-title"><?= htmlspecialchars($e->getTitre()) ?></h5>
                <div class="event-meta"><i class="fas fa-tag"></i><?= htmlspecialchars($e->getType()) ?></div>
                <div class="event-meta"><i class="fas fa-calendar"></i><?= $dateLabel ?></div>
                <div class="event-meta"><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($e->getLieu()) ?></div>
                <div class="event-meta"><i class="fas fa-users"></i><?= $e->getCapaciteMax() ?> max seats</div>
                <div class="event-price"><?= $priceLabel ?></div>
                <?php if (str_contains($statut, 'actif')): ?>
                  <button class="btn-register"
                          onclick="event.stopPropagation(); window.location='detailEvent.php?id=<?= $e->getIdEvent() ?>'">
                    <i class="fas fa-ticket-alt me-1"></i> Register Now
                  </button>
                <?php elseif (str_contains($statut, 'complet')): ?>
                  <button class="btn-register waiting"
                          onclick="event.stopPropagation(); window.location='detailEvent.php?id=<?= $e->getIdEvent() ?>'">
                    <i class="fas fa-clock me-1"></i> Join Waitlist
                  </button>
                <?php else: ?>
                  <button class="btn-register closed" disabled>
                    <i class="fas fa-lock me-1"></i> Registration Closed
                  </button>
                <?php endif; ?>
                <div class="like-row">
                  <button type="button"
                          class="like-btn"
                          data-id="<?= (int)$e->getIdEvent() ?>"
                          data-liked="false"
                          onclick="event.stopPropagation();">
                    <span class="like-heart">❤️</span>
                    <span class="like-count"><?= (int)$e->getLikes() ?></span>
                  </button>
                  <span style="font-size:12px;color:#999;display:flex;align-items:center;gap:4px">
                    🔥 <span class="reaction-total" id="rtotal-<?= (int)$e->getIdEvent() ?>">0</span> réactions
                  </span>
                </div>

                <button type="button" class="cr-toggle" data-toggle-cr="<?= (int)$e->getIdEvent() ?>"
                        onclick="event.stopPropagation();">
                  💬 <span class="comment-count-badge" id="ccount-<?= (int)$e->getIdEvent() ?>">0</span> Commentaires & Réactions
                </button>
                <div class="cr-wrap" id="cr-wrap-<?= (int)$e->getIdEvent() ?>" onclick="event.stopPropagation();">
                  <div class="cr-card">
                    <div class="reactions-bar" id="reactions-<?= (int)$e->getIdEvent() ?>">
                      <?php foreach (['❤️','😂','😮','😢','👏','🔥'] as $emoji): ?>
                        <button type="button" class="reaction-btn" data-type="<?= $emoji ?>" data-event="<?= (int)$e->getIdEvent() ?>">
                          <span><?= $emoji ?></span>
                          <span class="reaction-count" data-count-for="<?= $emoji ?>">0</span>
                        </button>
                      <?php endforeach; ?>
                    </div>

                    <div class="comments-list" id="comments-<?= (int)$e->getIdEvent() ?>">
                      <div class="comment-empty">Aucun commentaire pour l'instant.</div>
                    </div>

                    <div class="comment-form">
                      <div class="comment-error" id="comment-err-<?= (int)$e->getIdEvent() ?>"></div>
                      <input placeholder="Votre nom" id="auteur-<?= (int)$e->getIdEvent() ?>">
                      <textarea placeholder="Votre commentaire..." id="contenu-<?= (int)$e->getIdEvent() ?>" rows="3"></textarea>
                      <button type="button" class="comment-btn" data-submit-comment="<?= (int)$e->getIdEvent() ?>">Publier</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div id="noResults" class="empty-state" style="display:none">
        <i class="fas fa-search"></i>
        No results found.
      </div>
      <div class="text-center mt-2">
        <span id="countLabel" style="font-size:13px;color:#999"><?= $total ?> event(s)</span>
      </div>
    </div>
  </section>

  <!-- ABOUT — match friend's about section -->
  <section id="about" class="about section">
    <div class="container section-title">
      <h2>About Us</h2>
      <p><span>Learn More</span> <span class="description-title">About Smart Meal Planner</span></p>
    </div>
    <div class="container">
      <div class="row gy-4 align-items-center">
        <div class="col-lg-6">
          <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800"
               class="img-fluid rounded-4 shadow mb-4" alt="Events">
        </div>
        <div class="col-lg-6">
          <div class="content ps-0 ps-lg-5">
            <p class="fst-italic">
              Your go-to platform for discovering and joining the best events in Tunisia. Conferences, workshops, competitions and more — all in one place.
            </p>
            <ul>
              <li><i class="bi bi-check-circle-fill"></i> <span>Easy registration in seconds.</span></li>
              <li><i class="bi bi-check-circle-fill"></i> <span>Filter events by type: conferences, workshops, competitions.</span></li>
              <li><i class="bi bi-check-circle-fill"></i> <span>Never miss an event with instant notifications.</span></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CONTACT -->
  <section id="contact" class="section light-background">
    <div class="container section-title">
      <h2>Contact</h2>
      <p><span>Get in</span> <span class="description-title">Touch With Us</span></p>
    </div>
    <div class="container">
      <div class="row">
        <div class="col-md-4 text-center mb-4">
          <i class="bi bi-geo-alt-fill fa-3x mb-3" style="font-size:3rem;color:var(--accent-color,#ce1212)"></i>
          <h5>Address</h5>
          <p class="text-muted">Esprit Ghazela, Tunis, Tunisia</p>
        </div>
        <div class="col-md-4 text-center mb-4">
          <i class="bi bi-telephone-fill mb-3" style="font-size:3rem;color:var(--accent-color,#ce1212)"></i>
          <h5>Phone</h5>
          <p class="text-muted">+216 50 547 135</p>
        </div>
        <div class="col-md-4 text-center mb-4">
          <i class="bi bi-envelope-fill mb-3" style="font-size:3rem;color:var(--accent-color,#ce1212)"></i>
          <h5>Email</h5>
          <p class="text-muted">contact@smartmealplanner.tn</p>
        </div>
      </div>
    </div>
  </section>

</main>

<!-- MY ACTIVITY PANEL -->
<style>
#activity-bubble {
  position: fixed; bottom: 96px; right: 28px; z-index: 9998;
  width: 52px; height: 52px; border-radius: 50%;
  background: linear-gradient(135deg, #ce1212, #ff6b6b);
  color: #fff; border: none; cursor: pointer;
  box-shadow: 0 6px 20px rgba(206,18,18,0.4);
  font-size: 22px; display: flex; align-items: center; justify-content: center;
  transition: transform .2s, box-shadow .2s;
}
#activity-bubble:hover { transform: scale(1.1); box-shadow: 0 10px 28px rgba(206,18,18,0.5); }
#activity-bubble .notif {
  position: absolute; top: -4px; right: -4px;
  background: #fbbf24; color: #78350f;
  font-size: 10px; font-weight: 800;
  width: 18px; height: 18px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
}

#activity-panel {
  position: fixed; bottom: 160px; right: 28px; z-index: 9997;
  width: 360px; max-height: 560px;
  background: #fff; border-radius: 18px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.18);
  display: none; flex-direction: column;
  overflow: hidden; font-family: 'Inter', sans-serif;
}
#activity-panel.open { display: flex; }
.ap-header {
  background: linear-gradient(135deg, #ce1212, #ff6b6b);
  color: #fff; padding: 14px 18px;
  display: flex; align-items: center; justify-content: space-between;
}
.ap-header .ap-title { font-weight: 700; font-size: 15px; }
.ap-header .ap-sub   { font-size: 11px; opacity: .85; }
#ap-close {
  background: rgba(255,255,255,0.2); border: none; color: #fff;
  border-radius: 50%; width: 28px; height: 28px; cursor: pointer;
  font-size: 16px; display: flex; align-items: center; justify-content: center;
}
.ap-email-form { padding: 14px 16px; border-bottom: 1px solid #f0f0f0; display: flex; gap: 8px; }
.ap-email-form input {
  flex: 1; border: 1px solid #fde8e8; border-radius: 999px;
  padding: 8px 14px; font-size: 13px; outline: none; font-family: inherit;
}
.ap-email-form input:focus { border-color: #ce1212; }
.ap-email-form button {
  background: #ce1212; color: #fff; border: none;
  border-radius: 50%; width: 34px; height: 34px; cursor: pointer;
  font-size: 16px; display: flex; align-items: center; justify-content: center;
}
.ap-body { flex: 1; overflow-y: auto; padding: 14px 16px; }
.ap-tabs { display: flex; gap: 6px; margin-bottom: 14px; }
.ap-tab {
  flex: 1; padding: 7px; border: 1px solid #fde8e8; border-radius: 8px;
  background: #fff; font-size: 12px; font-weight: 600; color: #9a3535;
  cursor: pointer; font-family: inherit; text-align: center; transition: all .15s;
}
.ap-tab.active { background: #ce1212; color: #fff; border-color: #ce1212; }
.ap-tab-content { display: none; }
.ap-tab-content.active { display: block; }
.ap-stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px; }
.ap-stat {
  background: #fff5f5; border: 1px solid #fde8e8; border-radius: 10px;
  padding: 12px; text-align: center;
}
.ap-stat-val { font-size: 22px; font-weight: 800; color: #ce1212; }
.ap-stat-lbl { font-size: 11px; color: #9a3535; margin-top: 2px; }
.ap-item {
  background: #fff; border: 1px solid #fde8e8; border-radius: 10px;
  padding: 10px 12px; margin-bottom: 8px; font-size: 12px;
}
.ap-item-title { font-weight: 700; color: #1a0505; margin-bottom: 3px; font-size: 13px; }
.ap-item-meta  { color: #9a3535; font-size: 11px; }
.ap-badge {
  display: inline-block; padding: 2px 8px; border-radius: 20px;
  font-size: 10px; font-weight: 700; margin-left: 6px;
}
.ap-badge-ok  { background: #dcfce7; color: #166534; }
.ap-badge-wait{ background: #fef9c3; color: #854d0e; }
.ap-badge-no  { background: #fee2e2; color: #991b1b; }
.ap-empty { color: #9a3535; font-size: 13px; text-align: center; padding: 20px 0; }
</style>

<button id="activity-bubble" title="Mon Activité" onclick="toggleActivityPanel()">
  ❤️
  <span class="notif" id="activity-notif" style="display:none">!</span>
</button>

<div id="activity-panel">
  <div class="ap-header">
    <div>
      <div class="ap-title">❤️ Mon Activité</div>
      <div class="ap-sub">Votre résumé personnel</div>
    </div>
    <button id="ap-close" onclick="toggleActivityPanel()">✕</button>
  </div>

  <div class="ap-email-form">
    <input type="email" id="ap-email" placeholder="votre@email.com">
    <button onclick="loadActivity()" title="Rechercher">🔍</button>
  </div>

  <div class="ap-body" id="ap-body">
    <div class="ap-empty">Entrez votre email pour voir votre activité.</div>
  </div>
</div>

<script>
function toggleActivityPanel() {
  var panel = document.getElementById('activity-panel');
  panel.classList.toggle('open');
  if (panel.classList.contains('open')) {
    var saved = localStorage.getItem('smp_user_email');
    if (saved) {
      document.getElementById('ap-email').value = saved;
      loadActivity();
    }
    document.getElementById('ap-email').focus();
  }
}

function apTab(name) {
  document.querySelectorAll('.ap-tab').forEach(function(t) { t.classList.remove('active'); });
  document.querySelectorAll('.ap-tab-content').forEach(function(t) { t.classList.remove('active'); });
  document.querySelector('.ap-tab[data-tab="' + name + '"]').classList.add('active');
  document.getElementById('ap-tab-' + name).classList.add('active');
}

async function loadActivity() {
  var email = (document.getElementById('ap-email').value || '').trim();
  var body  = document.getElementById('ap-body');
  if (!email) { body.innerHTML = '<div class="ap-empty">Entrez votre email.</div>'; return; }

  localStorage.setItem('smp_user_email', email);
  body.innerHTML = '<div class="ap-empty">⏳ Chargement...</div>';

  try {
    var res  = await fetch('../back/getMyActivity.php?email=' + encodeURIComponent(email));
    var data = await res.json();

    if (data.error) { body.innerHTML = '<div class="ap-empty">❌ ' + data.error + '</div>'; return; }

    var s = data.stats;

    // Notif badge
    var notif = document.getElementById('activity-notif');
    if (s.total_events > 0) { notif.style.display = 'flex'; notif.textContent = s.total_events; }

    // Stats
    var statsHtml = '<div class="ap-stat-grid">'
      + '<div class="ap-stat"><div class="ap-stat-val">' + s.total_events + '</div><div class="ap-stat-lbl">🎟️ Inscriptions</div></div>'
      + '<div class="ap-stat"><div class="ap-stat-val">' + s.confirmed + '</div><div class="ap-stat-lbl">✅ Confirmées</div></div>'
      + '<div class="ap-stat"><div class="ap-stat-val">' + s.total_comments + '</div><div class="ap-stat-lbl">💬 Commentaires</div></div>'
      + '<div class="ap-stat"><div class="ap-stat-val">' + s.total_spent.toFixed(0) + ' TND</div><div class="ap-stat-lbl">💰 Dépensé</div></div>'
      + '</div>';

    // Participations
    var partHtml = '';
    if (data.participations.length === 0) {
      partHtml = '<div class="ap-empty">Aucune inscription trouvée.</div>';
    } else {
      data.participations.forEach(function(p) {
        var badgeCls = p.statut.includes('confirm') ? 'ap-badge-ok' : p.statut.includes('annul') ? 'ap-badge-no' : 'ap-badge-wait';
        var badgeLbl = p.statut.includes('confirm') ? '✅ Confirmé' : p.statut.includes('annul') ? '❌ Annulé' : '⏳ En attente';
        var prix     = p.prix == 0 ? 'Gratuit' : (p.prix * p.places).toFixed(2) + ' TND';
        partHtml += '<div class="ap-item">'
          + '<div class="ap-item-title">' + p.titre.replace(/</g,'&lt;') + '<span class="ap-badge ' + badgeCls + '">' + badgeLbl + '</span></div>'
          + '<div class="ap-item-meta">📅 ' + p.date + ' · 📍 ' + p.lieu.replace(/</g,'&lt;') + '</div>'
          + '<div class="ap-item-meta">🎟️ ' + p.places + ' place(s) · 💰 ' + prix + '</div>'
          + '</div>';
      });
    }

    // Comments
    var commHtml = '';
    if (data.comments.length === 0) {
      commHtml = '<div class="ap-empty">Aucun commentaire trouvé.</div>';
    } else {
      data.comments.forEach(function(c) {
        commHtml += '<div class="ap-item">'
          + '<div class="ap-item-title">' + c.event_titre.replace(/</g,'&lt;') + '</div>'
          + '<div class="ap-item-meta" style="color:#4a1515;margin:4px 0">"' + c.contenu.replace(/</g,'&lt;') + '"</div>'
          + '<div class="ap-item-meta">🕐 ' + c.created_at + '</div>'
          + '</div>';
      });
    }

    body.innerHTML = statsHtml
      + '<div class="ap-tabs">'
      + '<button class="ap-tab active" data-tab="events" onclick="apTab(\'events\')">🎟️ Événements (' + data.participations.length + ')</button>'
      + '<button class="ap-tab" data-tab="comments" onclick="apTab(\'comments\')">💬 Commentaires (' + data.comments.length + ')</button>'
      + '</div>'
      + '<div class="ap-tab-content active" id="ap-tab-events">' + partHtml + '</div>'
      + '<div class="ap-tab-content" id="ap-tab-comments">' + commHtml + '</div>';

  } catch(e) {
    body.innerHTML = '<div class="ap-empty">❌ Erreur de connexion.</div>';
  }
}

// Enter key on email input
document.addEventListener('DOMContentLoaded', function() {
  var inp = document.getElementById('ap-email');
  if (inp) inp.addEventListener('keydown', function(e) { if (e.key === 'Enter') loadActivity(); });
});
</script>

<!-- FOOTER — same as friend -->
<footer id="footer" class="footer dark-background">
  <div class="container copyright text-center py-4">
    <p>© <span>Copyright</span> <strong class="px-1 sitename">Smart Meal Planner</strong> <span>All Rights Reserved</span></p>
  </div>
</footer>

<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center">
  <i class="bi bi-arrow-up-short"></i>
</a>

<!-- ══════════════════════════════════════════
     CHATBOT WIDGET
══════════════════════════════════════════ -->
<style>
#chatbot-bubble {
  position: fixed; bottom: 28px; right: 28px; z-index: 9999;
  width: 56px; height: 56px; border-radius: 50%;
  background: linear-gradient(135deg, #ce1212, #ff6b6b);
  color: #fff; border: none; cursor: pointer;
  box-shadow: 0 6px 24px rgba(206,18,18,0.4);
  font-size: 26px; display: flex; align-items: center; justify-content: center;
  transition: transform .2s, box-shadow .2s;
}
#chatbot-bubble:hover { transform: scale(1.1); box-shadow: 0 10px 32px rgba(206,18,18,0.5); }

#chatbot-window {
  position: fixed; bottom: 96px; right: 28px; z-index: 9998;
  width: 360px; max-height: 520px;
  background: #fff; border-radius: 18px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.18);
  display: flex; flex-direction: column;
  overflow: hidden; display: none;
  font-family: 'Inter', sans-serif;
}
#chatbot-header {
  background: linear-gradient(135deg, #ce1212, #ff6b6b);
  color: #fff; padding: 14px 18px;
  display: flex; align-items: center; justify-content: space-between;
}
#chatbot-header .cb-title { font-weight: 700; font-size: 15px; }
#chatbot-header .cb-sub   { font-size: 11px; opacity: .85; }
#chatbot-close {
  background: rgba(255,255,255,0.2); border: none; color: #fff;
  border-radius: 50%; width: 28px; height: 28px; cursor: pointer;
  font-size: 16px; display: flex; align-items: center; justify-content: center;
}
#chatbot-messages {
  flex: 1; overflow-y: auto; padding: 16px;
  display: flex; flex-direction: column; gap: 10px;
  max-height: 340px;
}
.cb-msg {
  max-width: 82%; padding: 10px 14px; border-radius: 14px;
  font-size: 13px; line-height: 1.5; word-break: break-word;
}
.cb-msg.bot  { background: #f5f5f5; color: #222; align-self: flex-start; border-bottom-left-radius: 4px; }
.cb-msg.user { background: #ce1212; color: #fff; align-self: flex-end; border-bottom-right-radius: 4px; }
.cb-msg a    { color: #ce1212; font-weight: 600; }
.cb-msg.bot a{ color: #ce1212; }
.cb-msg.user a{ color: #ffe0e0; }
.cb-typing   { display: flex; gap: 4px; align-items: center; padding: 10px 14px; }
.cb-typing span {
  width: 7px; height: 7px; background: #ccc; border-radius: 50%;
  animation: cbBounce .9s infinite;
}
.cb-typing span:nth-child(2) { animation-delay: .15s; }
.cb-typing span:nth-child(3) { animation-delay: .30s; }
@keyframes cbBounce { 0%,60%,100%{transform:translateY(0)} 30%{transform:translateY(-6px)} }

#chatbot-input-row {
  padding: 12px 14px; border-top: 1px solid #f0f0f0;
  display: flex; gap: 8px;
}
#chatbot-input {
  flex: 1; border: 1px solid #e5e5e5; border-radius: 999px;
  padding: 9px 14px; font-size: 13px; outline: none; font-family: inherit;
}
#chatbot-input:focus { border-color: #ce1212; box-shadow: 0 0 0 3px rgba(206,18,18,.1); }
#chatbot-send {
  background: #ce1212; color: #fff; border: none;
  border-radius: 50%; width: 36px; height: 36px; cursor: pointer;
  font-size: 16px; display: flex; align-items: center; justify-content: center;
  transition: background .15s;
}
#chatbot-send:hover { background: #b00e0e; }

.cb-suggestions {
  display: flex; flex-wrap: wrap; gap: 6px; padding: 0 16px 10px;
}
.cb-suggestion {
  background: #fff0f0; color: #ce1212; border: 1px solid #fecaca;
  border-radius: 999px; padding: 5px 12px; font-size: 12px;
  cursor: pointer; font-family: inherit; transition: all .15s;
}
.cb-suggestion:hover { background: #ce1212; color: #fff; }

@media(max-width:420px) {
  #chatbot-window { width: calc(100vw - 32px); right: 16px; }
  #chatbot-bubble { right: 16px; bottom: 16px; }
}
</style>

<!-- Bubble button -->
<button id="chatbot-bubble" title="Assistant événements">🤖</button>

<!-- Chat window -->
<div id="chatbot-window">
  <div id="chatbot-header">
    <div>
      <div class="cb-title">🤖 Assistant Événements</div>
      <div class="cb-sub">Je vous aide à choisir votre événement</div>
    </div>
    <button id="chatbot-close">✕</button>
  </div>

  <div id="chatbot-messages"></div>

  <div class="cb-suggestions" id="cb-suggestions">
    <button class="cb-suggestion">Événements gratuits</button>
    <button class="cb-suggestion">Conférences disponibles</button>
    <button class="cb-suggestion">Événements ce mois</button>
    <button class="cb-suggestion">Ateliers pratiques</button>
  </div>

  <div id="chatbot-input-row">
    <input id="chatbot-input" type="text" placeholder="Posez votre question...">
    <button id="chatbot-send">➤</button>
  </div>
</div>

<script>
(function () {
  var CHATBOT_URL = '/projet_nutriplanner/view/back/chatbot.php';
  var history     = [];
  var isOpen      = false;
  var isTyping    = false;

  var bubble   = document.getElementById('chatbot-bubble');
  var win      = document.getElementById('chatbot-window');
  var closeBtn = document.getElementById('chatbot-close');
  var messages = document.getElementById('chatbot-messages');
  var input    = document.getElementById('chatbot-input');
  var sendBtn  = document.getElementById('chatbot-send');
  var suggs    = document.getElementById('cb-suggestions');

  function toggleChat() {
    isOpen = !isOpen;
    win.style.display = isOpen ? 'flex' : 'none';
    bubble.textContent = isOpen ? '✕' : '🤖';
    if (isOpen && messages.children.length === 0) {
      addMessage('bot', 'Bonjour ! 👋 Je suis votre assistant événements. Dites-moi ce qui vous intéresse et je vous recommande les meilleurs événements !');
    }
    if (isOpen) setTimeout(function () { input.focus(); }, 100);
  }

  function addMessage(role, text) {
    // Hide suggestions after first user message
    if (role === 'user' && suggs) suggs.style.display = 'none';

    var div = document.createElement('div');
    div.className = 'cb-msg ' + role;
    // Convert detailEvent.php?id=X links to clickable anchors
    var html = text
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
      .replace(/(detailEvent\.php\?id=\d+)/g, '<a href="$1">Voir l\'événement →</a>')
      .replace(/\n/g, '<br>');
    div.innerHTML = html;
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
    return div;
  }

  function showTyping() {
    var div = document.createElement('div');
    div.className = 'cb-msg bot cb-typing';
    div.id = 'cb-typing-indicator';
    div.innerHTML = '<span></span><span></span><span></span>';
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
  }

  function hideTyping() {
    var el = document.getElementById('cb-typing-indicator');
    if (el) el.remove();
  }

  async function sendMessage(text) {
    if (!text || isTyping) return;
    text = text.trim();
    if (!text) return;

    addMessage('user', text);
    input.value = '';
    isTyping = true;
    sendBtn.disabled = true;
    showTyping();

    try {
      var res  = await fetch(CHATBOT_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: text, history: history })
      });
      var data = await res.json();
      hideTyping();

      if (data.reply) {
        addMessage('bot', data.reply);
        history.push({ role: 'user',      content: text       });
        history.push({ role: 'assistant', content: data.reply });
        if (history.length > 20) history = history.slice(-20);
      } else {
        addMessage('bot', '❌ ' + (data.error || 'Une erreur est survenue.'));
      }
    } catch (e) {
      hideTyping();
      addMessage('bot', '❌ Impossible de contacter l\'assistant. Vérifiez votre connexion.');
    } finally {
      isTyping = false;
      sendBtn.disabled = false;
      input.focus();
    }
  }

  // Events
  bubble.addEventListener('click', toggleChat);
  closeBtn.addEventListener('click', toggleChat);
  sendBtn.addEventListener('click', function () { sendMessage(input.value); });
  input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(input.value); }
  });

  // Suggestion chips
  if (suggs) {
    suggs.querySelectorAll('.cb-suggestion').forEach(function (btn) {
      btn.addEventListener('click', function () { sendMessage(btn.textContent); });
    });
  }
})();
</script>

<script src="<?php echo $assetPrefix; ?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
const events = <?= json_encode(array_map(function($e) {
  return [
    'id'          => $e->getIdEvent(),
    'titre'       => $e->getTitre(),
    'description' => $e->getDescription(),
    'lieu'        => $e->getLieu(),
    'dateDebut'   => $e->getDateDebut(),
    'dateFin'     => $e->getDateFin(),
    'capacite'    => $e->getCapaciteMax(),
    'prix'        => (float)$e->getPrix(),
    'statut'      => $e->getStatut(),
    'type'        => $e->getType(),
    'image'       => $e->getImage() ? '../../uploads/evenements/' . $e->getImage() : null,
  ];
}, $evenements), JSON_UNESCAPED_UNICODE) ?>;

const typeEmojis = <?= json_encode(array_map(fn($t) => $t['emoji'], $typeConfig), JSON_UNESCAPED_UNICODE) ?>;

let currentFilter = 'all';

document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    currentFilter = this.dataset.filter;
    filterAndRender();
  });
});

function filterAndRender() {
  const q    = document.getElementById('searchInput').value.toLowerCase().trim();
  const sort = document.getElementById('sortSel').value;
  const items = Array.from(document.querySelectorAll('.event-item'));
  let visible = [];

  items.forEach(item => {
    const ok = (currentFilter === 'all' || item.dataset.type === currentFilter)
            && (!q || item.dataset.titre.includes(q)
                   || item.dataset.lieu.includes(q)
                   || item.dataset.type.toLowerCase().includes(q));
    item.style.display = ok ? '' : 'none';
    if (ok) visible.push(item);
  });

  const grid = document.getElementById('eventsGrid');
  visible.sort((a, b) => {
    if (sort === 'date')      return a.dataset.date.localeCompare(b.dataset.date);
    if (sort === 'prix_asc')  return parseFloat(a.dataset.prix) - parseFloat(b.dataset.prix);
    if (sort === 'prix_desc') return parseFloat(b.dataset.prix) - parseFloat(a.dataset.prix);
    if (sort === 'titre')     return a.dataset.titre.localeCompare(b.dataset.titre);
    return 0;
  });
  visible.forEach(item => grid.appendChild(item));

  document.getElementById('countLabel').textContent = visible.length + ' event(s)';
  document.getElementById('noResults').style.display = visible.length === 0 ? 'block' : 'none';
}

function formatLikes(n) {
  n = parseInt(n || 0, 10);
  return n >= 1000 ? (n/1000).toFixed(1)+'k' : n;
}

function initLikes() {
  document.querySelectorAll('.like-btn').forEach(function(btn){
    var countEl = btn.querySelector('.like-count');
    var id = parseInt(btn.dataset.id, 10);
    var key = 'liked_event_' + id;
    var liked = false;
    try { liked = localStorage.getItem(key) === 'true'; } catch (e) {}
    btn.dataset.liked = liked ? 'true' : 'false';
    btn.classList.toggle('liked', liked);
    if (countEl) countEl.textContent = formatLikes(countEl.textContent);

    btn.addEventListener('click', function(e){
      e.preventDefault();
      e.stopPropagation();

      var liked = btn.dataset.liked === 'true';
      var action = liked ? 'unlike' : 'like';

      fetch('/projet_nutriplanner/view/back/likeEvenement.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_event: id, action: action })
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          btn.dataset.liked = liked ? 'false' : 'true';
          try { localStorage.setItem(key, btn.dataset.liked); } catch (e2) {}
          countEl.textContent = formatLikes(data.likes);
          btn.classList.toggle('liked');
          // pulse animation
          btn.style.transform = 'scale(1.3)';
          setTimeout(() => btn.style.transform = 'scale(1)', 200);
        }
      });
    });
  });
}

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
    <div class="comment-item fade-in">
      <div>
        <span class="comment-author">${escapeHtml(c.auteur)}</span>
        <span class="comment-date">${escapeHtml(dateLabel)}</span>
      </div>
      <div class="comment-text">${escapeHtml(c.contenu)}</div>
    </div>
  `;
}

function setReactionCounts(id, counts) {
  var wrap = document.getElementById('reactions-' + id);
  if (!wrap) return;
  wrap.querySelectorAll('.reaction-btn').forEach(function (btn) {
    var type = btn.dataset.type;
    var el = btn.querySelector('[data-count-for]');
    var v = counts && typeof counts[type] !== 'undefined' ? counts[type] : 0;
    if (el) el.textContent = String(v);
  });
  // Update the total reaction counter in the card
  var totalSpan = document.getElementById('rtotal-' + id);
  if (totalSpan && counts) {
    var total = Object.values(counts).reduce(function(a, b) { return a + b; }, 0);
    totalSpan.textContent = total;
  }
}

async function loadCommentaires(id) {
  var list = document.getElementById('comments-' + id);
  if (!list) return;
  var res = await fetch('/projet_nutriplanner/view/back/getCommentaires.php?id_event=' + encodeURIComponent(id) + '&page=1&limit=6', { headers: { 'Accept':'application/json' }});
  var data = await res.json();

  // Update comment count badge
  var badge = document.getElementById('ccount-' + id);
  if (badge && data.total !== undefined) badge.textContent = data.total;

  if (!data.comments || data.comments.length === 0) {
    list.innerHTML = '<div class="comment-empty">Aucun commentaire pour linstant.</div>';
    return;
  }
  list.innerHTML = data.comments.map(renderCommentItem).join('');
}

async function loadReactions(id) {
  var res = await fetch('/projet_nutriplanner/view/back/getReactions.php?id_event=' + encodeURIComponent(id), { headers: { 'Accept':'application/json' }});
  var data = await res.json();
  if (data && data.counts) setReactionCounts(id, data.counts);
}

function restoreReactedState(id) {
  var wrap = document.getElementById('reactions-' + id);
  if (!wrap) return;
  wrap.querySelectorAll('.reaction-btn').forEach(function (btn) {
    var type = btn.dataset.type;
    var key = 'reacted_' + id + '_' + type;
    var reacted = false;
    try { reacted = localStorage.getItem(key) === 'true'; } catch (e) {}
    btn.classList.toggle('reacted', reacted);
  });
}

function initCommentsReactions() {
  var sessionId = ensureSessionId();

  document.querySelectorAll('[data-toggle-cr]').forEach(function (btn) {
    btn.addEventListener('click', async function (e) {
      e.preventDefault(); e.stopPropagation();
      var id = parseInt(btn.dataset.toggleCr, 10);
      var wrap = document.getElementById('cr-wrap-' + id);
      if (!wrap) return;
      var open = wrap.classList.contains('open');
      wrap.classList.toggle('open', !open);
      if (!open && !wrap.dataset.loaded) {
        wrap.dataset.loaded = '1';
        restoreReactedState(id);
        await Promise.allSettled([loadCommentaires(id), loadReactions(id)]);
      }
    });
  });

  document.querySelectorAll('.reaction-btn').forEach(function (btn) {
    btn.addEventListener('click', async function (e) {
      e.preventDefault(); e.stopPropagation();
      var id = parseInt(btn.dataset.event, 10);
      var type = btn.dataset.type;
      var key = 'reacted_' + id + '_' + type;
      var reacted = btn.classList.contains('reacted');
      try { localStorage.setItem(key, reacted ? 'false' : 'true'); } catch (e2) {}

      var res = await fetch('/projet_nutriplanner/view/back/addReaction.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_event: id, type: type, session_id: sessionId })
      });
      var data = await res.json();
      if (data && data.success && data.counts) {
        btn.classList.toggle('reacted', !reacted);
        setReactionCounts(id, data.counts);
      } else {
        try { localStorage.setItem(key, reacted ? 'true' : 'false'); } catch (e3) {}
      }
    });
  });

  document.querySelectorAll('[data-submit-comment]').forEach(function (btn) {
    btn.addEventListener('click', async function (e) {
      e.preventDefault(); e.stopPropagation();
      var id = parseInt(btn.dataset.submitComment, 10);
      var auteurEl = document.getElementById('auteur-' + id);
      var contenuEl = document.getElementById('contenu-' + id);
      var errEl = document.getElementById('comment-err-' + id);
      var list = document.getElementById('comments-' + id);
      if (!auteurEl || !contenuEl || !errEl || !list) return;

      var auteur = (auteurEl.value || '').trim();
      var contenu = (contenuEl.value || '').trim();
      errEl.classList.remove('show');
      errEl.textContent = '';

      if (!auteur) { errEl.textContent = 'Veuillez saisir votre nom.'; errEl.classList.add('show'); return; }
      if (contenu.length < 3) { errEl.textContent = 'Le commentaire doit contenir au moins 3 caractères.'; errEl.classList.add('show'); return; }
      if (contenu.length > 500) { errEl.textContent = 'Le commentaire ne doit pas dépasser 500 caractères.'; errEl.classList.add('show'); return; }

      var res = await fetch('/projet_nutriplanner/view/back/addCommentaire.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_event: id, auteur: auteur, contenu: contenu })
      });
      var data = await res.json();
      if (data && data.success && data.comment) {
        contenuEl.value = '';
        var empty = list.querySelector('.comment-empty');
        if (empty) empty.remove();
        list.insertAdjacentHTML('afterbegin', renderCommentItem(data.comment));
        // Increment count badge
        var badge = document.getElementById('ccount-' + id);
        if (badge) badge.textContent = parseInt(badge.textContent || '0') + 1;
      } else {
        errEl.textContent = (data && data.error) ? data.error : 'Erreur lors de la publication.';
        errEl.classList.add('show');
      }
    });
  });
}

// Scroll-top button
const scrollTop = document.getElementById('scroll-top');
if (scrollTop) {
  window.addEventListener('scroll', () => {
    scrollTop.classList.toggle('active', window.scrollY > 100);
  });
  scrollTop.addEventListener('click', e => {
    e.preventDefault();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

// Load comment counts for all events on page load
async function loadAllCommentCounts() {
  var badges = document.querySelectorAll('[id^="ccount-"]');
  badges.forEach(async function(badge) {
    var id = badge.id.replace('ccount-', '');
    try {
      var res  = await fetch('/projet_nutriplanner/view/back/getCommentaires.php?id_event=' + encodeURIComponent(id) + '&page=1&limit=1', { headers: { 'Accept': 'application/json' } });
      var data = await res.json();
      if (data && typeof data.total !== 'undefined') {
        badge.textContent = data.total;
      }
    } catch (e) {}
  });
}

// Load reaction totals for all events on page load
async function loadAllReactionTotals() {
  var spans = document.querySelectorAll('[id^="rtotal-"]');
  spans.forEach(async function(span) {
    var id = span.id.replace('rtotal-', '');
    try {
      var res  = await fetch('/projet_nutriplanner/view/back/getReactions.php?id_event=' + encodeURIComponent(id), { headers: { 'Accept': 'application/json' } });
      var data = await res.json();
      if (data && data.counts) {
        var total = Object.values(data.counts).reduce(function(a, b) { return a + b; }, 0);
        span.textContent = total;
      }
    } catch (e) {}
  });
}

document.addEventListener('DOMContentLoaded', function () {
  initLikes();
  initCommentsReactions();
  loadAllCommentCounts();
  loadAllReactionTotals();
});
</script>
<script>
// ── Goals & Rewards ───────────────────────────────────────────────────
async function loadGoals() {
  var email   = (document.getElementById('goals-email').value || '').trim();
  var content = document.getElementById('goals-content');
  var btn     = document.getElementById('goals-check-btn');
  if (!content) return;

  if (!email) {
    content.innerHTML = '<p style="color:#dc2626;font-size:13px">Please enter your email.</p>';
    return;
  }

  btn.textContent = '⏳ Loading...'; btn.disabled = true;

  try {
    var res  = await fetch('../back/getEventProgress.php?email=' + encodeURIComponent(email));
    var data = await res.json();

    var total      = data.total || 0;
    var milestones = data.milestones || [];
    var next       = data.next;

    // Progress bar to next milestone
    var progressHtml = '';
    if (next) {
      var pct = Math.round((total / next.count) * 100);
      progressHtml = `
        <div style="margin-bottom:20px">
          <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px">
            <span style="color:#666">Progress to <strong>${next.emoji} ${next.label}</strong></span>
            <span style="color:#ce1212;font-weight:700">${total} / ${next.count}</span>
          </div>
          <div style="background:#fce8e8;border-radius:999px;height:10px;overflow:hidden">
            <div style="background:linear-gradient(90deg,#ce1212,#ff6b6b);height:100%;width:${pct}%;border-radius:999px;transition:width .6s ease"></div>
          </div>
          <p style="font-size:12px;color:#9a3535;margin-top:8px">
            Only <strong>${next.need}</strong> more event${next.need > 1 ? 's' : ''} to unlock <strong>${next.discount}% off</strong> — ${next.emoji} ${next.label}!
          </p>
        </div>`;
    } else {
      progressHtml = '<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#166534;font-weight:600">🏆 You\'ve unlocked all rewards! You\'re a VIP Attendee!</div>';
    }

    // Milestones list
    var shown    = 4;
    var listHtml = milestones.map(function(m, i) {
      var unlocked = m.unlocked;
      var hidden   = i >= shown ? 'class="goal-extra" style="display:none"' : '';
      var rightCol = '';
      if (unlocked && m.promo_code) {
        rightCol = `<div style="text-align:right">
          <div style="font-size:10px;color:#166534;font-weight:600;margin-bottom:4px">✅ Unlocked</div>
          <div onclick="copyCode('${m.promo_code}', this)"
            style="font-size:11px;font-weight:800;color:#b91c1c;letter-spacing:1px;background:#fff5f5;border:1.5px dashed #f7c1c1;border-radius:6px;padding:3px 8px;cursor:pointer;user-select:all"
            title="Click to copy">
            ${m.promo_code}
          </div>
        </div>`;
      } else if (unlocked) {
        rightCol = `<div style="text-align:right">
          <div style="font-size:10px;color:#166534;font-weight:600">✅ Unlocked</div>
          <div style="font-size:11px;color:#9a3535">${m.discount}% off</div>
        </div>`;
      } else {
        rightCol = `<div style="text-align:right">
          <span style="font-size:18px;font-weight:800;color:#ccc">${m.discount}%</span>
          <div style="font-size:10px;color:#9a3535">${total}/${m.count} events</div>
        </div>`;
      }
      return `<div ${hidden} style="display:${i < shown ? 'flex' : 'none'};align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #fce8e8">
        <span style="font-size:22px">${m.emoji}</span>
        <div style="flex:1">
          <div style="font-size:13px;font-weight:600;color:${unlocked ? '#166534' : '#1a0505'}">${m.label}</div>
          <div style="font-size:11px;color:#9a3535">${m.count} event${m.count > 1 ? 's' : ''}</div>
        </div>
        ${rightCol}
      </div>`;
    }).join('');

    var toggleBtn = milestones.length > shown
      ? `<button onclick="toggleGoals(this)" style="background:none;border:none;color:#ce1212;font-size:13px;font-weight:600;cursor:pointer;margin-top:10px;font-family:'Inter',sans-serif">
           Show More ▼
         </button>`
      : '';

    content.innerHTML = `
      <div style="background:#fff5f5;border:1px solid #fde8e8;border-radius:10px;padding:14px 16px;margin-bottom:16px;display:flex;align-items:center;gap:12px">
        <span style="font-size:28px">🎯</span>
        <div>
          <div style="font-size:15px;font-weight:700;color:#1a0505">${total} Event${total !== 1 ? 's' : ''} Attended</div>
          <div style="font-size:12px;color:#9a3535">Keep registering to unlock more rewards!</div>
        </div>
      </div>
      ${progressHtml}
      <div id="goals-list">${listHtml}</div>
      ${toggleBtn}`;

  } catch(e) {
    content.innerHTML = '<p style="color:#dc2626;font-size:13px">❌ Could not load progress.</p>';
  } finally {
    btn.textContent = 'Check Progress'; btn.disabled = false;
  }
}

function toggleGoals(btn) {
  var extras = document.querySelectorAll('.goal-extra');
  var showing = btn.textContent.includes('More');
  extras.forEach(function(el) { el.style.display = showing ? 'flex' : 'none'; });
  btn.textContent = showing ? 'Show Less ▲' : 'Show More ▼';
}

function copyCode(code, el) {
  navigator.clipboard.writeText(code).then(function() {
    var orig = el.innerHTML;
    el.innerHTML = '✅ Copied!';
    el.style.color = '#166534';
    setTimeout(function() { el.innerHTML = orig; el.style.color = '#b91c1c'; }, 1500);
  }).catch(function() {
    // fallback
    var ta = document.createElement('textarea');
    ta.value = code; ta.style.position = 'fixed'; ta.style.opacity = '0';
    document.body.appendChild(ta); ta.select(); document.execCommand('copy');
    document.body.removeChild(ta);
    var orig = el.innerHTML;
    el.innerHTML = '✅ Copied!';
    setTimeout(function() { el.innerHTML = orig; }, 1500);
  });
}

// Auto-load if email in localStorage
document.addEventListener('DOMContentLoaded', function() {
  var saved = localStorage.getItem('smp_user_email');
  if (saved) {
    var inp = document.getElementById('goals-email');
    if (inp) { inp.value = saved; loadGoals(); }
  }
});
</script>

<script>
// ── AI Event Recommendations ──────────────────────────────────────────
async function loadAIRecommendations() {
  var grid = document.getElementById('ai-reco-grid');
  var btn  = document.getElementById('ai-refresh-btn');
  if (!grid) return;

  grid.innerHTML = '<div style="color:#9a3535;font-size:13px;padding:20px 0">⏳ Loading AI recommendations...</div>';
  if (btn) { btn.disabled = true; btn.textContent = '⏳ Loading...'; }

  try {
    var res  = await fetch('../back/getAIEventRecommendations.php');
    var data = await res.json();

    if (!Array.isArray(data) || data.length === 0) {
      grid.innerHTML = '<div style="color:#9a3535;font-size:13px;padding:20px 0">No recommendations available.</div>';
      return;
    }

    var typeColors = {
      'Conférence':'#ce1212','Atelier':'#f7971e','Compétition':'#e24b4a',
      'Forum':'#11998e','Séminaire':'#8e44ad','Autre':'#636e72'
    };

    grid.innerHTML = data.map(function(ev) {
      var prix   = ev.prix == 0 ? '<span style="color:#16a34a;font-weight:700">Free</span>' : '<span style="color:#ce1212;font-weight:700">' + parseFloat(ev.prix).toFixed(2) + ' TND</span>';
      var color  = typeColors[ev.type] || '#636e72';
      var stars  = ev.note > 0 ? '⭐ ' + ev.note.toFixed(1) + '/5' : '';
      var banner = ev.image
        ? '<img src="' + ev.image + '" alt="" style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0">'
        : '<span style="font-size:36px;opacity:.25">' + (ev.type === 'Conférence' ? '🎤' : ev.type === 'Atelier' ? '🛠️' : ev.type === 'Compétition' ? '🏆' : '📅') + '</span>';

      return '<a href="detailEvent.php?id=' + ev.id + '" style="text-decoration:none;color:inherit">'
        + '<div style="background:#fff;border:1px solid #fde8e8;border-radius:14px;overflow:hidden;transition:all .2s;cursor:pointer" '
        + 'onmouseover="this.style.transform=\'translateY(-4px)\';this.style.boxShadow=\'0 8px 24px rgba(206,18,18,.12)\'" '
        + 'onmouseout="this.style.transform=\'\';this.style.boxShadow=\'\'">'
        + '<div style="height:100px;background:linear-gradient(135deg,' + color + '22,' + color + '44);position:relative;display:flex;align-items:center;justify-content:center;overflow:hidden">'
        + banner
        + '<span style="position:absolute;top:8px;left:10px;font-size:22px;z-index:1">' + ev.medal + '</span>'
        + '</div>'
        + '<div style="padding:12px 14px">'
        + '<div style="font-size:13px;font-weight:700;color:#1a0505;margin-bottom:4px;line-height:1.3">' + ev.titre.replace(/</g,'&lt;') + '</div>'
        + '<div style="font-size:11px;color:#9a3535;margin-bottom:6px">📅 ' + ev.date + ' · 📍 ' + ev.lieu.replace(/</g,'&lt;') + '</div>'
        + '<div style="font-size:11px;color:#666;font-style:italic;margin-bottom:8px;line-height:1.4">"' + ev.reason.replace(/</g,'&lt;') + '"</div>'
        + '<div style="display:flex;align-items:center;justify-content:space-between">'
        + prix
        + (stars ? '<span style="font-size:11px;color:#f59e0b">' + stars + '</span>' : '')
        + '</div>'
        + '</div>'
        + '</div></a>';
    }).join('');

  } catch(e) {
    grid.innerHTML = '<div style="color:#dc2626;font-size:13px;padding:20px 0">❌ Could not load recommendations.</div>';
  } finally {
    if (btn) { btn.disabled = false; btn.innerHTML = '🔄 Refresh'; }
  }
}

document.addEventListener('DOMContentLoaded', loadAIRecommendations);
</script>
</body>
</html>