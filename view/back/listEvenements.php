<?php
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
            <div class="event-card" onclick="showEventModal(<?= $e->getIdEvent() ?>)">
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

<!-- EVENT MODAL -->
<div class="modal fade" id="eventModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="modalTitle"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-5">
            <img id="modalImage" class="modal-event-img d-none" alt="">
            <div id="modalPlaceholder" class="modal-event-placeholder"></div>
          </div>
          <div class="col-md-7">
            <div class="modal-price mb-2" id="modalPrice"></div>
            <div id="modalBadge" class="mb-3"></div>
            <div id="modalInfoRows"></div>
            <div class="mt-3" id="modalDescription"
                 style="font-size:14px;color:#555;line-height:1.7"></div>
            <div class="mt-4 d-flex gap-2">
              <a id="modalDetailBtn" href="#"
                 class="btn rounded-pill px-4"
                 style="background:var(--accent-color,#ce1212);color:white;border-color:var(--accent-color,#ce1212)">
                <i class="fas fa-info-circle me-1"></i> View Details
              </a>
              <a id="modalRegisterBtn" href="#"
                 class="btn rounded-pill px-4"
                 style="color:var(--accent-color,#ce1212);border:1px solid var(--accent-color,#ce1212)">
                <i class="fas fa-ticket-alt me-1"></i> Register
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- FOOTER — same as friend -->
<footer id="footer" class="footer dark-background">
  <div class="container copyright text-center py-4">
    <p>© <span>Copyright</span> <strong class="px-1 sitename">Smart Meal Planner</strong> <span>All Rights Reserved</span></p>
  </div>
</footer>

<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center">
  <i class="bi bi-arrow-up-short"></i>
</a>

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

function showEventModal(id) {
  const e = events.find(ev => ev.id === id);
  if (!e) return;

  const dateDebut = new Date(e.dateDebut).toLocaleDateString('en-GB');
  const dateFin   = new Date(e.dateFin).toLocaleDateString('en-GB');
  const dateLabel = dateDebut === dateFin ? dateDebut : dateDebut + ' → ' + dateFin;
  const isFree    = e.prix === 0;
  const statut    = e.statut.toLowerCase();

  document.getElementById('modalTitle').textContent = e.titre;
  document.getElementById('modalPrice').innerHTML   = isFree
    ? '<span style="color:#28a745">Free</span>'
    : e.prix.toFixed(2) + ' <small style="font-size:1rem">TND</small>';

  const badgeColor = statut.includes('actif') ? '#28a745' : statut.includes('annul') ? '#dc3545' : '#6c757d';
  const badgeLabel = statut.includes('actif') ? 'Active' : statut.includes('annul') ? 'Cancelled' : 'Ended';
  document.getElementById('modalBadge').innerHTML =
    `<span style="background:${badgeColor};color:white;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:600">${badgeLabel}</span>
     <span style="background:#f0f0f0;color:#333;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:600;margin-left:6px">${e.type}</span>`;

  const img = document.getElementById('modalImage');
  const ph  = document.getElementById('modalPlaceholder');
  if (e.image) {
    img.src = e.image;
    img.classList.remove('d-none');
    ph.style.display = 'none';
  } else {
    img.classList.add('d-none');
    ph.style.display = 'flex';
    ph.style.background = 'linear-gradient(135deg,#ce121222,#ce121244)';
    ph.innerHTML = typeEmojis[e.type] || '📅';
  }

  document.getElementById('modalInfoRows').innerHTML = `
    <div class="info-row"><i class="fas fa-calendar"></i><strong>Date</strong>${dateLabel}</div>
    <div class="info-row"><i class="fas fa-map-marker-alt"></i><strong>Location</strong>${e.lieu}</div>
    <div class="info-row"><i class="fas fa-users"></i><strong>Capacity</strong>${e.capacite} seats</div>
  `;

  document.getElementById('modalDescription').textContent =
    e.description ? e.description.substring(0, 200) + (e.description.length > 200 ? '...' : '') : '';

  document.getElementById('modalDetailBtn').href   = `detailEvent.php?id=${e.id}`;
  document.getElementById('modalRegisterBtn').href = statut.includes('actif')
    ? `../back/addParticipation.php?id_event=${e.id}`
    : `detailEvent.php?id=${e.id}`;

  new bootstrap.Modal(document.getElementById('eventModal')).show();
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
</script>
</body>
</html>