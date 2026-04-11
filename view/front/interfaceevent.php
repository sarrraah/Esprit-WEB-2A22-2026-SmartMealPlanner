<?php
require_once __DIR__ . '/../../controller/EvenementController.php';
$ctrl       = new EvenementController();
$evenements = $ctrl->listEvenements();

$typeConfig = [
    'Conférence'  => ['from' => '#dbe9fa', 'to' => '#B5D4F4', 'emoji' => '🎤'],
    'Atelier'     => ['from' => '#e0f0c4', 'to' => '#C0DD97', 'emoji' => '🛠️'],
    'Compétition' => ['from' => '#fbe0d4', 'to' => '#F5C4B3', 'emoji' => '🏆'],
    'Forum'       => ['from' => '#e6e4fb', 'to' => '#CECBF6', 'emoji' => '💬'],
    'Séminaire'   => ['from' => '#ccf0e4', 'to' => '#9FE1CB', 'emoji' => '📚'],
    'Autre'       => ['from' => '#eceae2', 'to' => '#D3D1C7', 'emoji' => '📅'],
];
$total    = count($evenements);
$actifs   = count(array_filter($evenements, fn($e) => $e->getStatut() === 'Actif'));
$gratuits = count(array_filter($evenements, fn($e) => $e->getPrix() == 0));
$types    = array_unique(array_map(fn($e) => $e->getType(), $evenements));
sort($types);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestion des Événements</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#f5f5f8;color:#1a1a2e;min-height:100vh}
nav{background:#fff;border-bottom:1px solid #e8e8f0;padding:0 32px;display:flex;align-items:center;justify-content:space-between;height:60px;position:sticky;top:0;z-index:100}
.logo{font-size:18px;font-weight:600;color:#1a1a2e;text-decoration:none}
.logo span{color:#7F77DD}
.nav-links{display:flex;gap:28px}
.nav-links a{font-size:14px;color:#7a7a99;text-decoration:none;font-weight:500;transition:color .2s}
.nav-links a:hover,.nav-links a.active{color:#1a1a2e}
.search-wrap{position:relative}
.search-wrap input{padding:8px 14px 8px 36px;border:1px solid #e0e0ee;border-radius:8px;background:#f8f8fc;color:#1a1a2e;font-size:13px;width:220px;outline:none;font-family:inherit;transition:border-color .2s}
.search-wrap input:focus{border-color:#7F77DD;background:#fff}
.search-wrap::before{content:'🔍';font-size:13px;position:absolute;left:11px;top:50%;transform:translateY(-50%);pointer-events:none}
.hero{background:linear-gradient(135deg,#1a1a2e 0%,#2d2b55 100%);padding:50px 32px;text-align:center;color:#fff}
.hero-tag{display:inline-block;background:rgba(127,119,221,0.25);color:#AFA9EC;border:1px solid rgba(127,119,221,0.4);padding:4px 14px;border-radius:20px;font-size:12px;font-weight:500;letter-spacing:1px;text-transform:uppercase;margin-bottom:16px}
.hero h1{font-size:32px;font-weight:600;margin-bottom:10px;line-height:1.2}
.hero h1 em{color:#7F77DD;font-style:normal}
.hero p{color:rgba(255,255,255,0.55);font-size:15px;max-width:500px;margin:0 auto 28px;line-height:1.6}
.hero-stats{display:flex;justify-content:center;gap:40px}
.hs-val{font-size:24px;font-weight:600}
.hs-lbl{font-size:12px;color:rgba(255,255,255,0.4);margin-top:3px}
.main{max-width:1200px;margin:0 auto;padding:28px 32px}
.section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
.section-title{font-size:16px;font-weight:600}
.section-count{font-size:13px;color:#9999b3}
.filters{display:flex;gap:8px;margin-bottom:22px;flex-wrap:wrap;align-items:center}
.chip{padding:6px 16px;border-radius:20px;font-size:13px;font-weight:500;cursor:pointer;border:1px solid #e0e0ee;background:#fff;color:#7a7a99;transition:all .15s;font-family:inherit}
.chip:hover{border-color:#AFA9EC;color:#534AB7}
.chip.on{background:#EEEDFE;border-color:#AFA9EC;color:#3C3489}
.sort-sel{margin-left:auto;padding:7px 12px;border:1px solid #e0e0ee;border-radius:8px;background:#fff;color:#7a7a99;font-size:13px;cursor:pointer;outline:none;font-family:inherit}
.sort-sel:focus{border-color:#7F77DD}
.events-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:18px}
.ecard{background:#fff;border:1px solid #e8e8f0;border-radius:14px;overflow:hidden;cursor:pointer;transition:all .2s;display:flex;flex-direction:column;text-decoration:none;color:inherit}
.ecard:hover{box-shadow:0 6px 24px rgba(127,119,221,0.14);border-color:#d0ceee;transform:translateY(-2px)}
.card-banner{height:150px;position:relative;display:flex;align-items:center;justify-content:center;overflow:hidden}
.cb-bg{position:absolute;inset:0}
.cb-emoji{position:relative;z-index:1;font-size:48px;opacity:.22}
.cb-type{position:absolute;top:10px;left:10px;z-index:2;font-size:11px;font-weight:500;padding:4px 10px;border-radius:20px;background:rgba(255,255,255,0.92);color:#3a3a5c}
.cb-status{position:absolute;top:10px;right:10px;z-index:2;font-size:11px;font-weight:500;padding:4px 10px;border-radius:20px}
.s-actif{background:#e8f8ee;color:#1e7a42;border:1px solid #b8e8cc}
.s-termine{background:#fce8e8;color:#a32d2d;border:1px solid #f0bbbb}
.s-complet{background:#fef3e2;color:#8a5a00;border:1px solid #fad99a}
.cb-price{position:absolute;bottom:10px;right:10px;z-index:2;font-size:13px;font-weight:600;padding:4px 12px;border-radius:20px;background:rgba(255,255,255,0.95);color:#1a1a2e}
.cb-price.free{background:#1e7a42;color:#fff}
.card-body{padding:16px;flex:1;display:flex;flex-direction:column}
.card-title{font-size:14px;font-weight:600;color:#1a1a2e;margin-bottom:10px;line-height:1.4}
.card-meta{display:flex;flex-direction:column;gap:6px}
.cm{display:flex;align-items:center;gap:7px;font-size:12px;color:#7a7a99}
.cm-icon{font-size:12px;width:15px;text-align:center;flex-shrink:0}
.card-cta{margin-top:14px;padding-top:12px;border-top:1px solid #f0f0f8;display:flex;align-items:center;justify-content:space-between}
.places-left{font-size:12px;color:#9999b3}
.places-left strong{color:#534AB7}
.cta-link{font-size:12px;font-weight:500;color:#534AB7}
.empty{text-align:center;padding:60px 20px;grid-column:1/-1}
.empty-icon{font-size:40px;margin-bottom:12px}
.empty-txt{font-size:14px;color:#9999b3}
footer{background:#1a1a2e;color:rgba(255,255,255,.4);text-align:center;padding:24px;font-size:13px;margin-top:60px}
@media(max-width:768px){
  nav{padding:0 16px;flex-wrap:wrap;height:auto;gap:10px;padding:10px 16px}
  .nav-links{display:none}
  .hero{padding:32px 16px}
  .hero h1{font-size:24px}
  .main{padding:20px 16px}
}
</style>
</head>
<body>

<nav>
  <a href="interfaceevent.php" class="logo">Gestion des <span>Événements</span></a>
  <div class="nav-links">
    <a href="interfaceevent.php" class="active">Événements</a>
    <a href="#about">À propos</a>
    <a href="#contact">Contact</a>
  </div>
  <div class="search-wrap">
    <input type="text" id="searchInput" placeholder="Rechercher..." oninput="filterAndRender()">
  </div>
</nav>

<div class="hero">
  <div class="hero-tag">Plateforme événementielle Esprit</div>
  <h1>Découvrez des événements <em>exceptionnels</em></h1>
  <p>Conférences, hackathons, ateliers — inscrivez-vous en quelques clics</p>
  <div class="hero-stats">
    <div><div class="hs-val"><?= $total ?></div><div class="hs-lbl">Événements</div></div>
    <div><div class="hs-val"><?= $actifs ?></div><div class="hs-lbl">Actifs</div></div>
    <div><div class="hs-val"><?= $gratuits ?></div><div class="hs-lbl">Gratuits</div></div>
  </div>
</div>

<div class="main">
  <div class="section-header">
    <div class="section-title">Tous les événements</div>
    <div class="section-count" id="countLabel"><?= $total ?> événement(s)</div>
  </div>

  <div class="filters">
    <button class="chip on" onclick="setFilter('Tous',this)">Tous (<?= $total ?>)</button>
    <?php foreach ($types as $type):
      $cnt = count(array_filter($evenements, fn($e) => $e->getType() === $type));
    ?>
    <button class="chip" onclick="setFilter('<?= htmlspecialchars($type, ENT_QUOTES) ?>',this)">
      <?= htmlspecialchars($type) ?> (<?= $cnt ?>)
    </button>
    <?php endforeach; ?>
    <select class="sort-sel" id="sortSel" onchange="filterAndRender()">
      <option value="date">Par date</option>
      <option value="prix_asc">Prix croissant</option>
      <option value="prix_desc">Prix décroissant</option>
      <option value="titre">Alphabétique</option>
    </select>
  </div>

  <div class="events-grid" id="eventsGrid">
    <?php if (empty($evenements)): ?>
      <div class="empty">
        <div class="empty-icon">📅</div>
        <div class="empty-txt">Aucun événement disponible pour le moment.</div>
      </div>
    <?php else: ?>
      <?php foreach ($evenements as $e):
        $tc = $typeConfig[$e->getType()] ?? $typeConfig['Autre'];
        $isFree = ($e->getPrix() == 0);
        $badgeClass = match($e->getStatut()) {
          'Actif'   => 's-actif',
          'Terminé' => 's-termine',
          default   => 's-complet'
        };
        $dateDebut = date('d/m/Y', strtotime($e->getDateDebut()));
        $dateFin   = date('d/m/Y', strtotime($e->getDateFin()));
        $dateLabel = ($dateDebut === $dateFin) ? $dateDebut : "$dateDebut → $dateFin";
      ?>
      <a class="ecard"
         href="detailEvent.php?id=<?= $e->getIdEvent() ?>"
         data-type="<?= htmlspecialchars($e->getType()) ?>"
         data-titre="<?= htmlspecialchars(strtolower($e->getTitre())) ?>"
         data-lieu="<?= htmlspecialchars(strtolower($e->getLieu())) ?>"
         data-date="<?= $e->getDateDebut() ?>"
         data-prix="<?= $e->getPrix() ?>">

        <div class="card-banner">
          <div class="cb-bg" style="background:linear-gradient(135deg,<?= $tc['from'] ?> 0%,<?= $tc['to'] ?> 100%)"></div>
          <span class="cb-emoji"><?= $tc['emoji'] ?></span>
          <span class="cb-type"><?= htmlspecialchars($e->getType()) ?></span>
          <span class="cb-status <?= $badgeClass ?>"><?= htmlspecialchars($e->getStatut()) ?></span>
          <span class="cb-price <?= $isFree ? 'free' : '' ?>">
            <?= $isFree ? '🎁 Gratuit' : number_format($e->getPrix(), 2) . ' DT' ?>
          </span>
        </div>

        <div class="card-body">
          <div class="card-title"><?= htmlspecialchars($e->getTitre()) ?></div>
          <div class="card-meta">
            <div class="cm"><span class="cm-icon">📅</span><?= $dateLabel ?></div>
            <div class="cm"><span class="cm-icon">📍</span><?= htmlspecialchars($e->getLieu()) ?></div>
            <div class="cm"><span class="cm-icon">👥</span><?= $e->getCapaciteMax() ?> places max</div>
          </div>
          <div class="card-cta">
            <div class="places-left">
              <?php if ($e->getStatut() === 'Actif'): ?>
                <strong><?= $e->getCapaciteMax() ?></strong> places disponibles
              <?php else: ?>
                Inscription fermée
              <?php endif; ?>
            </div>
            <span class="cta-link">Voir les détails →</span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<footer>© 2025 Gestion des Événements – Tous droits réservés</footer>

<script>
let currentFilter = 'Tous';

function setFilter(type, el) {
  currentFilter = type;
  document.querySelectorAll('.chip').forEach(c => c.classList.remove('on'));
  el.classList.add('on');
  filterAndRender();
}

function filterAndRender() {
  const q    = document.getElementById('searchInput').value.toLowerCase().trim();
  const sort = document.getElementById('sortSel').value;
  const cards = Array.from(document.querySelectorAll('.ecard'));
  let visible = [];

  cards.forEach(card => {
    const ok = (currentFilter === 'Tous' || card.dataset.type === currentFilter)
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

  document.getElementById('countLabel').textContent = visible.length + ' événement(s)';

  let empty = document.querySelector('.empty');
  if (visible.length === 0) {
    if (!empty) {
      empty = document.createElement('div');
      empty.className = 'empty';
      empty.innerHTML = '<div class="empty-icon">🔍</div><div class="empty-txt">Aucun résultat trouvé.</div>';
      grid.appendChild(empty);
    }
  } else if (empty) empty.remove();
}
</script>
</body>
</html>