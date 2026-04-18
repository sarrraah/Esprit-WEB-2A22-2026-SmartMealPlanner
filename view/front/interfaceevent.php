<?php
require_once __DIR__ . '/../../controller/EvenementController.php';
$ctrl       = new EvenementController();
$evenements = $ctrl->listEvenements();

$typeConfig = [
    'Conférence'  => ['from' => '#fce8e8', 'to' => '#f7c1c1', 'emoji' => '🎤'],
    'Atelier'     => ['from' => '#fde8e8', 'to' => '#f09595', 'emoji' => '🛠️'],
    'Compétition' => ['from' => '#fce8e8', 'to' => '#e24b4a', 'emoji' => '🏆'],
    'Forum'       => ['from' => '#fdf0f0', 'to' => '#f7c1c1', 'emoji' => '💬'],
    'Séminaire'   => ['from' => '#fff5f5', 'to' => '#f09595', 'emoji' => '📚'],
    'Autre'       => ['from' => '#fdf5f5', 'to' => '#e8d5d5', 'emoji' => '📅'],
];

$total    = count($evenements);
$actifs   = count(array_filter($evenements, fn($e) => $e->getStatut() === 'actif'));
$gratuits = count(array_filter($evenements, fn($e) => $e->getPrix() == 0));
$types    = array_unique(array_map(fn($e) => $e->getType(), $evenements));
sort($types);

$statusLabels = [
    'actif'   => 'Active',
    'annulé'  => 'Cancelled',
    'terminé' => 'Ended',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Event Management</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#fff5f5;color:#1a0505;min-height:100vh}

nav{background:#fff;border-bottom:1.5px solid #f7c1c1;padding:0 32px;display:flex;align-items:center;justify-content:space-between;height:60px;position:sticky;top:0;z-index:100}
.logo{font-size:18px;font-weight:600;color:#1a0505;text-decoration:none}.logo span{color:#b91c1c}
.nav-links{display:flex;gap:28px}
.nav-links a{font-size:14px;color:#9a3535;text-decoration:none;font-weight:500;transition:color .2s}
.nav-links a:hover,.nav-links a.active{color:#b91c1c}
.search-wrap{position:relative}
.search-wrap input{padding:8px 14px 8px 36px;border:1px solid #f7c1c1;border-radius:8px;background:#fff5f5;color:#1a0505;font-size:13px;width:220px;outline:none;font-family:inherit;transition:border-color .2s}
.search-wrap input:focus{border-color:#b91c1c;background:#fff}
.search-wrap::before{content:'🔍';font-size:13px;position:absolute;left:11px;top:50%;transform:translateY(-50%);pointer-events:none}

.hero{background:linear-gradient(135deg,#7f1d1d 0%,#b91c1c 100%);padding:50px 32px;text-align:center;color:#fff}
.hero-tag{display:inline-block;background:rgba(255,255,255,0.15);color:#fecaca;border:1px solid rgba(255,255,255,0.3);padding:4px 14px;border-radius:20px;font-size:12px;font-weight:500;letter-spacing:1px;text-transform:uppercase;margin-bottom:16px}
.hero h1{font-size:32px;font-weight:600;margin-bottom:10px;line-height:1.2}
.hero h1 em{color:#fca5a5;font-style:normal}
.hero p{color:rgba(255,255,255,0.6);font-size:15px;max-width:500px;margin:0 auto 28px;line-height:1.6}

.main{max-width:1200px;margin:0 auto;padding:28px 32px}
.section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
.section-title{font-size:16px;font-weight:600;color:#1a0505}
.section-count{font-size:13px;color:#9a3535}

.filters{display:flex;gap:8px;margin-bottom:22px;flex-wrap:wrap;align-items:center}
.chip{padding:6px 16px;border-radius:20px;font-size:13px;font-weight:500;cursor:pointer;border:1px solid #f7c1c1;background:#fff;color:#9a3535;transition:all .15s;font-family:inherit}
.chip:hover{border-color:#f09595;color:#b91c1c}
.chip.on{background:#fce8e8;border-color:#f09595;color:#7f1d1d}
.sort-sel{margin-left:auto;padding:7px 12px;border:1px solid #f7c1c1;border-radius:8px;background:#fff;color:#9a3535;font-size:13px;cursor:pointer;outline:none;font-family:inherit}

.events-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:18px}
.ecard{background:#fff;border:1px solid #fde8e8;border-radius:14px;overflow:hidden;cursor:pointer;transition:all .2s;display:flex;flex-direction:column;text-decoration:none;color:inherit}
.ecard:hover{box-shadow:0 6px 24px rgba(185,28,28,0.12);border-color:#f7c1c1;transform:translateY(-2px)}

/* Banner avec image réelle ou gradient */
.card-banner{height:180px;position:relative;display:flex;align-items:center;justify-content:center;overflow:hidden}
.card-banner img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0}
.cb-bg{position:absolute;inset:0}
.cb-overlay{position:absolute;inset:0;background:rgba(0,0,0,0.15)}
.cb-emoji{position:relative;z-index:1;font-size:48px;opacity:.22}
.cb-type{position:absolute;top:10px;left:10px;z-index:2;font-size:11px;font-weight:500;padding:4px 10px;border-radius:20px;background:rgba(255,255,255,0.92);color:#7f1d1d}
.cb-price{position:absolute;top:10px;right:10px;z-index:2;font-size:11px;font-weight:600;padding:4px 10px;border-radius:20px;background:rgba(127,29,29,0.85);color:#fff}

.card-body{padding:16px;flex:1;display:flex;flex-direction:column}
.card-title{font-size:14px;font-weight:600;color:#1a0505;margin-bottom:10px;line-height:1.4}
.card-meta{display:flex;flex-direction:column;gap:6px}
.cm{display:flex;align-items:center;gap:7px;font-size:12px;color:#9a3535}
.cm-icon{font-size:12px;width:15px;text-align:center;flex-shrink:0}
.card-cta{margin-top:14px;padding-top:12px;border-top:1px solid #fce8e8;display:flex;align-items:center;justify-content:space-between}
.places-left{font-size:12px;color:#9a3535}
.places-left strong{color:#b91c1c}
.cta-link{font-size:12px;font-weight:500;color:#b91c1c}

.empty{text-align:center;padding:60px 20px;grid-column:1/-1}
.empty-icon{font-size:40px;margin-bottom:12px}
.empty-txt{font-size:14px;color:#9a3535}

footer{background:#7f1d1d;color:rgba(255,255,255,.45);text-align:center;padding:24px;font-size:13px;margin-top:60px}

@media(max-width:768px){
  nav{padding:0 16px;flex-wrap:wrap;height:auto;gap:10px;padding:10px 16px}
  .nav-links{display:none}
  .hero{padding:32px 16px}.hero h1{font-size:24px}
  .main{padding:20px 16px}
}
</style>
</head>
<body>

<nav>
  <a href="interfaceevent.php" class="logo">Event <span>Management</span></a>
  <div class="nav-links">
    <a href="interfaceevent.php" class="active">Events</a>
    <a href="#about">About</a>
    <a href="#contact">Contact</a>
  </div>
  <div class="search-wrap">
    <input type="text" id="searchInput" placeholder="Search..." oninput="filterAndRender()">
  </div>
</nav>

<div class="hero">
  <div class="hero-tag">Esprit Event Platform</div>
  <h1>Discover <em>exceptional</em> events</h1>
  <p>Conferences, hackathons, workshops — register in just a few clicks</p>
</div>

<div class="main">
  <div class="section-header">
    <div class="section-title">All Events</div>
    <div class="section-count" id="countLabel"><?= $total ?> event(s)</div>
  </div>

  <div class="filters">
    <button class="chip on" onclick="setFilter('All',this)">All (<?= $total ?>)</button>
    <?php foreach ($types as $type):
      $cnt = count(array_filter($evenements, fn($e) => $e->getType() === $type));
    ?>
    <button class="chip" onclick="setFilter('<?= htmlspecialchars($type, ENT_QUOTES) ?>',this)">
      <?= htmlspecialchars($type) ?> (<?= $cnt ?>)
    </button>
    <?php endforeach; ?>
    <select class="sort-sel" id="sortSel" onchange="filterAndRender()">
      <option value="date">By date</option>
      <option value="prix_asc">Price: low to high</option>
      <option value="prix_desc">Price: high to low</option>
      <option value="titre">Alphabetical</option>
    </select>
  </div>

  <div class="events-grid" id="eventsGrid">
    <?php if (empty($evenements)): ?>
      <div class="empty">
        <div class="empty-icon">📅</div>
        <div class="empty-txt">No events available at the moment.</div>
      </div>
    <?php else: ?>
      <?php foreach ($evenements as $e):
        $tc        = $typeConfig[$e->getType()] ?? $typeConfig['Autre'];
        $dateDebut = date('m/d/Y', strtotime($e->getDateDebut()));
        $dateFin   = date('m/d/Y', strtotime($e->getDateFin()));
        $dateLabel = ($dateDebut === $dateFin) ? $dateDebut : "$dateDebut → $dateFin";
        $isFree    = ($e->getPrix() == 0);
        $priceLabel= $isFree ? 'Free' : number_format($e->getPrix(), 2) . ' TND';
        $imgPath   = $e->getImage() ? '../../../uploads/evenements/' . $e->getImage() : null;
      ?>
      <a class="ecard"
         href="detailEvent.php?id=<?= $e->getIdEvent() ?>"
         data-type="<?= htmlspecialchars($e->getType()) ?>"
         data-titre="<?= htmlspecialchars(strtolower($e->getTitre())) ?>"
         data-lieu="<?= htmlspecialchars(strtolower($e->getLieu())) ?>"
         data-date="<?= $e->getDateDebut() ?>"
         data-prix="<?= $e->getPrix() ?>">

        <div class="card-banner">
          <?php if ($imgPath): ?>
            <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($e->getTitre()) ?>">
            <div class="cb-overlay"></div>
          <?php else: ?>
            <div class="cb-bg" style="background:linear-gradient(135deg,<?= $tc['from'] ?> 0%,<?= $tc['to'] ?> 100%)"></div>
            <span class="cb-emoji"><?= $tc['emoji'] ?></span>
          <?php endif; ?>
          <span class="cb-type"><?= htmlspecialchars($e->getType()) ?></span>
          <span class="cb-price"><?= $priceLabel ?></span>
        </div>

        <div class="card-body">
          <div class="card-title"><?= htmlspecialchars($e->getTitre()) ?></div>
          <div class="card-meta">
            <div class="cm"><span class="cm-icon">📅</span><?= $dateLabel ?></div>
            <div class="cm"><span class="cm-icon">📍</span><?= htmlspecialchars($e->getLieu()) ?></div>
            <div class="cm"><span class="cm-icon">👥</span><?= $e->getCapaciteMax() ?> max seats</div>
          </div>
          <div class="card-cta">
            <div class="places-left">
              <?php if (str_contains(strtolower($e->getStatut()), 'actif')): ?>
                <strong><?= $e->getCapaciteMax() ?></strong> seats available
              <?php else: ?>
                Registration closed
              <?php endif; ?>
            </div>
            <span class="cta-link">View details →</span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<footer>© 2025 Event Management – All rights reserved</footer>

<script>
let currentFilter = 'All';
function setFilter(type, el) {
  currentFilter = type;
  document.querySelectorAll('.chip').forEach(c => c.classList.remove('on'));
  el.classList.add('on');
  filterAndRender();
}
function filterAndRender() {
  const q     = document.getElementById('searchInput').value.toLowerCase().trim();
  const sort  = document.getElementById('sortSel').value;
  const cards = Array.from(document.querySelectorAll('.ecard'));
  let visible = [];
  cards.forEach(card => {
    const ok = (currentFilter === 'All' || card.dataset.type === currentFilter)
            && (!q || card.dataset.titre.includes(q) || card.dataset.lieu.includes(q) || card.dataset.type.toLowerCase().includes(q));
    card.style.display = ok ? 'flex' : 'none';
    if (ok) visible.push(card);
  });
  const grid = document.getElementById('eventsGrid');
  visible.sort((a, b) => {
    if (sort === 'date')      return a.dataset.date.localeCompare(b.dataset.date);
    if (sort === 'prix_asc')  return parseFloat(a.dataset.prix) - parseFloat(b.dataset.prix);
    if (sort === 'prix_desc') return parseFloat(b.dataset.prix) - parseFloat(a.dataset.prix);
    if (sort === 'titre')     return a.dataset.titre.localeCompare(b.dataset.titre);
    return 0;
  });
  visible.forEach(card => grid.appendChild(card));
  document.getElementById('countLabel').textContent = visible.length + ' event(s)';
  let empty = document.querySelector('.empty');
  if (visible.length === 0) {
    if (!empty) {
      empty = document.createElement('div');
      empty.className = 'empty';
      empty.innerHTML = '<div class="empty-icon">🔍</div><div class="empty-txt">No results found.</div>';
      grid.appendChild(empty);
    }
  } else if (empty) empty.remove();
}
</script>
</body>
</html>