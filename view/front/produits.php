<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/ProduitController.php';
require_once __DIR__ . '/../../controller/CategorieController.php';

$produitController  = new ProduitController();
$categorieController = new CategorieController();
$produits   = $produitController->listProduits();
$categories = $categorieController->getAllCategories();
$total      = count($produits);

include("header.php");
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Amatic+SC:wght@400;700&display=swap');

/* ── HERO ── */
.hero-produits {
  background: #f5f5f0;
  padding: 60px 0 50px;
}
.hero-produits h1 {
  font-family: "Amatic SC", sans-serif;
  font-size: 3rem;
  font-weight: 700;
  color: #2d2d2d;
  line-height: 1.2;
}
.hero-produits h1 span { color: #ce1212; }
.hero-produits p {
  font-family: 'Inter', sans-serif;
  font-size: 0.95rem;
  color: #666;
  margin: 14px 0 24px;
}
.hero-produits .btn-browse {
  background: #ce1212;
  color: white;
  border: none;
  padding: 11px 28px;
  border-radius: 25px;
  font-family: 'Inter', sans-serif;
  font-weight: 600;
  font-size: 14px;
  text-decoration: none;
  display: inline-block;
  transition: 0.3s;
}
.hero-produits .btn-browse:hover { background: #b00e0e; color: white; }
.hero-produits .hero-img {
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}
.hero-produits .hero-img img {
  width: 100%;
  height: 320px;
  object-fit: cover;
  display: block;
}

/* ── FILTERS ── */
.filters-section {
  background: white;
  padding: 20px 24px;
  border-radius: 15px;
  margin-bottom: 30px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.filter-btn {
  margin: 4px;
  padding: 7px 18px;
  border-radius: 25px;
  border: 1px solid #ddd;
  background: white;
  font-family: 'Inter', sans-serif;
  font-size: 13px;
  transition: 0.3s;
  cursor: pointer;
  text-decoration: none;
  color: #333;
  display: inline-block;
}
.filter-btn.active, .filter-btn:hover {
  background: #ce1212;
  color: white;
  border-color: #ce1212;
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
.search-input {
  border-radius: 25px !important;
  border: 1px solid #ddd !important;
  font-family: 'Inter', sans-serif;
  font-size: 13px;
  padding: 8px 16px !important;
}

/* ── PRODUCT CARD ── */
.product-card {
  background: white;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 5px 20px rgba(0,0,0,0.08);
  transition: transform 0.3s, box-shadow 0.3s;
  margin-bottom: 30px;
  height: 100%;
  display: flex;
  flex-direction: column;
}
.product-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 40px rgba(206,18,18,0.15);
}
.product-img { width: 100%; height: 210px; object-fit: cover; }
.product-img-placeholder {
  width: 100%; height: 210px; background: #f5f5f5;
  display: flex; align-items: center; justify-content: center;
  font-size: 3rem; color: #ccc;
}
.product-badge { position: absolute; top: 12px; left: 12px; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-dispo   { background: #28a745; color: white; }
.badge-rupture { background: #dc3545; color: white; }
.badge-epuise  { background: #6c757d; color: white; }
.product-info  { padding: 16px; flex: 1; display: flex; flex-direction: column; }
.product-title { font-size: 0.95rem; font-weight: 600; color: #333; margin-bottom: 6px; line-height: 1.4; }
.product-desc  { font-size: 0.8rem; color: #888; margin-bottom: 8px; flex: 1; }
.product-cat   { font-size: 0.75rem; color: #999; margin-bottom: 6px; display: flex; align-items: center; gap: 5px; }
.product-cat i { color: #ce1212; }
.product-price { font-size: 1.3rem; font-weight: 700; color: #ce1212; margin: 8px 0; }
.btn-cart {
  background: #ce1212; color: white; border: none; padding: 9px 20px;
  border-radius: 25px; font-weight: 500; font-family: 'Inter', sans-serif;
  transition: 0.3s; width: 100%; font-size: 13px; cursor: pointer;
}
.btn-cart:hover { background: #b00e0e; transform: scale(1.02); color: white; }
.btn-cart:disabled { background: #ccc; cursor: not-allowed; transform: none; }

/* ── SECTION TITLE ── */
.section-title h2 {
  font-size: 14px; font-weight: 500; padding: 0; line-height: 1px;
  margin: 0 0 8px; letter-spacing: 1.5px; text-transform: uppercase; color: #999; position: relative;
}
.section-title h2::after {
  content: ""; width: 120px; height: 1px; display: inline-block;
  background: #ce1212; margin: 4px 10px;
}
.section-title p {
  color: #333; margin: 0 0 24px; font-size: 36px;
  font-weight: 700; font-family: "Amatic SC", sans-serif;
}
.section-title p span { color: #ce1212; }
</style>

<!-- HERO PRODUITS -->
<section class="hero-produits">
  <div class="container">
    <div class="row gy-4 align-items-center justify-content-between">
      <div class="col-lg-6 order-2 order-lg-1">
        <h1>Découvrez nos <span>produits</span> frais &amp; sains</h1>
        <p>Ingrédients frais, packs intelligents et meal prep — tout ce qu'il vous faut pour cuisiner sain au quotidien.</p>
        <a href="#produits" class="btn-browse">Voir les produits</a>
      </div>
      <div class="col-lg-5 order-1 order-lg-2">
        <div class="hero-img">
          <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=800"
               alt="Produits frais">
        </div>
      </div>
    </div>
  </div>
</section>

<section id="produits" class="section light-background py-5">
  <div class="container section-title">
    <h2>Boutique</h2>
    <p><span>Nos</span> <span class="description-title">Produits</span></p>
  </div>

  <div class="container">

    <!-- FILTERS -->
    <div class="filters-section">
      <div class="row align-items-center gy-2">
        <div class="col-md-5">
          <strong style="font-size:13px;font-family:'Inter',sans-serif;">Filtrer par catégorie :</strong><br class="d-md-none">
          <a class="filter-btn active" href="produits.php" id="btn-all">Tous (<?= $total ?>)</a>
          <?php foreach ($categories as $cat): ?>
            <a class="filter-btn" href="categories.php?id=<?= (int)$cat['id_categorie'] ?>">
              <?= htmlspecialchars($cat['nom']) ?>
            </a>
          <?php endforeach; ?>
        </div>
        <div class="col-md-4">
          <input type="text" id="searchInput" class="form-control search-input"
            placeholder="Rechercher un produit..." oninput="filtrerProduits()">
        </div>
        <div class="col-md-3">
          <select id="sortSel" class="sort-select w-100" onchange="filtrerProduits()">
            <option value="">— Trier —</option>
            <option value="nom-asc">Nom A → Z</option>
            <option value="nom-desc">Nom Z → A</option>
            <option value="prix-asc">Prix croissant ↑</option>
            <option value="prix-desc">Prix décroissant ↓</option>
            <option value="stock-asc">Stock croissant ↑</option>
            <option value="stock-desc">Stock décroissant ↓</option>
          </select>
        </div>
      </div>
    </div>

    <!-- TOAST -->
    <div id="panier-toast" style="display:none;position:fixed;bottom:24px;right:24px;z-index:9999;"
         class="toast align-items-center text-bg-success border-0 show" role="alert">
      <div class="d-flex">
        <div class="toast-body"><i class="bi bi-cart-check me-2"></i><span id="panier-toast-msg">Produit ajouté !</span></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto"
                onclick="document.getElementById('panier-toast').style.display='none'"></button>
      </div>
    </div>

    <!-- GRID -->
    <div class="row" id="produitsGrid">
      <?php foreach ($produits as $i => $produit): ?>
        <?php
        $img = $produit['image'] ?? '';
        if (empty($img))                        $imgSrc = '';
        elseif (str_starts_with($img, 'http'))  $imgSrc = $img;
        elseif (str_starts_with($img, 'meals/')) $imgSrc = '../../view/assets/img/' . $img;
        else                                     $imgSrc = UPLOAD_URL . $img;

        $statut = determinerStatut($produit['quantiteStock'], $produit['dateExpiration']);
        $badgeCls = match($statut) {
          'Disponible' => 'badge-dispo',
          'Rupture'    => 'badge-rupture',
          default      => 'badge-epuise',
        };
        ?>
        <div class="col-lg-4 col-md-6 product-item"
             data-index="<?= $i ?>"
             data-nom="<?= htmlspecialchars(strtolower($produit['nom']), ENT_QUOTES) ?>"
             data-prix="<?= (float)$produit['prix'] ?>"
             data-stock="<?= (int)$produit['quantiteStock'] ?>">
          <div class="product-card" onclick="openProductModal(<?= (int)$produit['id'] ?>)" style="cursor:pointer;">
            <div class="position-relative">
              <?php if ($imgSrc): ?>
                <img src="<?= htmlspecialchars($imgSrc) ?>" class="product-img"
                     alt="<?= htmlspecialchars($produit['nom']) ?>">
              <?php else: ?>
                <div class="product-img-placeholder"><i class="bi bi-image"></i></div>
              <?php endif; ?>
              <span class="product-badge <?= $badgeCls ?>"><?= $statut ?></span>
              <span style="position:absolute;top:12px;right:12px;padding:4px 12px;border-radius:20px;
                           font-size:11px;font-weight:700;background:rgba(206,18,18,0.88);color:white;">
                <?= number_format((float)$produit['prix'], 2, ',', ' ') ?> DT
              </span>
            </div>
            <div class="product-info">
              <div class="product-title"><?= htmlspecialchars($produit['nom']) ?></div>
              <div class="product-desc"><?= htmlspecialchars($produit['description'] ?? '') ?></div>
              <div class="product-cat">
                <i class="bi bi-tag-fill"></i>
                <?= htmlspecialchars($produit['categorie_nom'] ?? 'Sans catégorie') ?>
              </div>
              <div class="product-price"><?= number_format((float)$produit['prix'], 2, ',', ' ') ?> DT</div>
              <?php if ($statut === 'Disponible'): ?>
                <button class="btn-cart btn-ajouter-panier"
                        onclick="event.stopPropagation();"
                        data-id="<?= (int)$produit['id'] ?>"
                        data-nom="<?= htmlspecialchars($produit['nom'], ENT_QUOTES) ?>"
                        data-prix="<?= (float)$produit['prix'] ?>"
                        data-image="<?= htmlspecialchars($imgSrc, ENT_QUOTES) ?>">
                  <i class="bi bi-cart-plus me-2"></i>Ajouter au panier
                </button>
              <?php else: ?>
                <button class="btn-cart" disabled onclick="event.stopPropagation();">
                  <i class="bi bi-x-circle me-2"></i>Rupture de stock
                </button>
              <?php endif; ?>
            </div>
          </div>
          <!-- Hidden data for modal -->
          <div class="product-data" style="display:none;"
               data-id="<?= (int)$produit['id'] ?>"
               data-nom="<?= htmlspecialchars($produit['nom'], ENT_QUOTES) ?>"
               data-desc="<?= htmlspecialchars($produit['description'] ?? '', ENT_QUOTES) ?>"
               data-prix="<?= (float)$produit['prix'] ?>"
               data-stock="<?= (int)$produit['quantiteStock'] ?>"
               data-expiration="<?= htmlspecialchars($produit['dateExpiration'] ?? '', ENT_QUOTES) ?>"
               data-categorie="<?= htmlspecialchars($produit['categorie_nom'] ?? 'Sans catégorie', ENT_QUOTES) ?>"
               data-statut="<?= htmlspecialchars($statut, ENT_QUOTES) ?>"
               data-image="<?= htmlspecialchars($imgSrc, ENT_QUOTES) ?>"
               data-calories="<?= rand(150, 550) ?>"
               data-proteines="<?= rand(8, 35) ?>"
               data-glucides="<?= rand(10, 60) ?>"
               data-lipides="<?= rand(3, 20) ?>"></div>
        </div>
      <?php endforeach; ?>
      <div id="no-result" style="display:none;" class="col-12 text-center py-5 text-muted">
        <i class="bi bi-search" style="font-size:2rem;display:block;margin-bottom:10px;"></i>
        Aucun produit trouvé.
      </div>
    </div>

  </div>
</section>

<!-- Modal Panier -->
<div class="modal fade" id="modalPanier" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">🛒 Mon Panier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="panier-contenu">
        <p class="text-muted text-center">Votre panier est vide.</p>
      </div>
      <div class="modal-footer d-flex justify-content-between align-items-center">
        <strong>Total : <span id="panier-total">0,00</span> DT</strong>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-danger btn-sm" onclick="viderPanier()">Vider</button>
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Continuer</button>
          <button class="btn btn-danger" onclick="acheter()"><i class="bi bi-bag-check me-2"></i>Acheter</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function getPanier() { return JSON.parse(localStorage.getItem('panier') || '[]'); }
function savePanier(p) { localStorage.setItem('panier', JSON.stringify(p)); updateBadge(); }
function updateBadge() {
  var total = getPanier().reduce(function(s,p){ return s+p.quantite; },0);
  var badge = document.getElementById('panier-badge');
  if (badge) { badge.textContent = total; badge.style.display = total > 0 ? 'inline-block' : 'none'; }
}
function ouvrirPanier() {
  var panier = getPanier(), contenu = document.getElementById('panier-contenu'), totalEl = document.getElementById('panier-total');
  if (!panier.length) { contenu.innerHTML = '<p class="text-muted text-center py-3">Votre panier est vide.</p>'; totalEl.textContent = '0,00'; }
  else {
    var html = '<table class="table align-middle"><thead><tr><th>Produit</th><th>Prix</th><th>Qté</th><th>Sous-total</th><th></th></tr></thead><tbody>';
    var total = 0;
    panier.forEach(function(p) {
      var sous = p.prix * p.quantite; total += sous;
      html += '<tr><td><div class="d-flex align-items-center gap-2">';
      if (p.image) html += '<img src="'+p.image+'" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">';
      html += '<strong>'+p.nom+'</strong></div></td>';
      html += '<td>'+p.prix.toFixed(2).replace('.',',')+' DT</td>';
      html += '<td><div class="d-flex align-items-center gap-2"><button class="btn btn-sm btn-outline-secondary" onclick="changerQte(\''+p.id+'\',-1)">−</button><span>'+p.quantite+'</span><button class="btn btn-sm btn-outline-secondary" onclick="changerQte(\''+p.id+'\',1)">+</button></div></td>';
      html += '<td>'+sous.toFixed(2).replace('.',',')+' DT</td>';
      html += '<td><button class="btn btn-sm btn-outline-danger" onclick="supprimerArticle(\''+p.id+'\')"><i class="bi bi-trash"></i></button></td></tr>';
    });
    html += '</tbody></table>';
    contenu.innerHTML = html; totalEl.textContent = total.toFixed(2).replace('.',',');
  }
  new bootstrap.Modal(document.getElementById('modalPanier')).show();
}
function changerQte(id,delta) {
  var panier = getPanier(), item = panier.find(function(p){ return p.id===id; });
  if (item) { item.quantite += delta; if (item.quantite <= 0) panier = panier.filter(function(p){ return p.id!==id; }); }
  savePanier(panier); ouvrirPanier();
}
function supprimerArticle(id) { savePanier(getPanier().filter(function(p){ return p.id!==id; })); ouvrirPanier(); }
function viderPanier() { savePanier([]); ouvrirPanier(); }
function acheter() {
  var panier = getPanier(); if (!panier.length) return;
  var total = panier.reduce(function(s,p){ return s+p.prix*p.quantite; },0);
  var msg = '✅ Commande confirmée !\n\n';
  panier.forEach(function(p){ msg += '• '+p.nom+' x'+p.quantite+' — '+(p.prix*p.quantite).toFixed(2)+' DT\n'; });
  msg += '\nTotal : '+total.toFixed(2)+' DT\n\nMerci !';
  bootstrap.Modal.getInstance(document.getElementById('modalPanier')).hide();
  savePanier([]); setTimeout(function(){ alert(msg); }, 300);
}
document.querySelectorAll('.btn-ajouter-panier').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var panier = getPanier();
    var id = this.dataset.id, nom = this.dataset.nom, prix = parseFloat(this.dataset.prix), image = this.dataset.image;
    var ex = panier.find(function(p){ return p.id===id; });
    if (ex) { ex.quantite += 1; } else { panier.push({id,nom,prix,image,quantite:1}); }
    savePanier(panier);
    document.getElementById('panier-toast-msg').textContent = '"'+nom+'" ajouté !';
    var t = document.getElementById('panier-toast'); t.style.display = 'flex';
    setTimeout(function(){ t.style.display='none'; }, 3000);
  });
});

function filtrerProduits() {
  var q   = document.getElementById('searchInput').value.toLowerCase().trim();
  var tri = document.getElementById('sortSel').value;
  var items = Array.from(document.querySelectorAll('.product-item'));
  var visible = 0;
  items.forEach(function(el) {
    var nom = el.dataset.nom || '';
    el.style.display = (!q || nom.includes(q)) ? '' : 'none';
    if (el.style.display !== 'none') visible++;
  });
  if (tri) {
    var field = tri.split('-')[0], dir = tri.split('-')[1];
    var vis = items.filter(function(el){ return el.style.display !== 'none'; });
    vis.sort(function(a,b) {
      if (field==='nom')   { return dir==='asc' ? (a.dataset.nom||'').localeCompare(b.dataset.nom||'') : (b.dataset.nom||'').localeCompare(a.dataset.nom||''); }
      if (field==='prix')  { var va=parseFloat(a.dataset.prix)||0, vb=parseFloat(b.dataset.prix)||0; return dir==='asc'?va-vb:vb-va; }
      if (field==='stock') { var va=parseInt(a.dataset.stock)||0,  vb=parseInt(b.dataset.stock)||0;  return dir==='asc'?va-vb:vb-va; }
    });
    var grid = document.getElementById('produitsGrid');
    vis.forEach(function(el){ grid.appendChild(el); });
  }
  document.getElementById('no-result').style.display = visible === 0 ? '' : 'none';
}

updateBadge();
</script>

<!-- ── PRODUCT DETAIL MODAL ── -->
<div class="modal fade" id="modalProduitDetail" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;overflow:hidden;border:none;">
      <div class="modal-body p-0">
        <div class="row g-0">
          <!-- Image -->
          <div class="col-md-5">
            <div id="modal-img-wrap" style="height:100%;min-height:320px;background:#f5f5f5;position:relative;">
              <img id="modal-img" src="" alt="" style="width:100%;height:100%;object-fit:cover;min-height:320px;display:none;">
              <div id="modal-img-placeholder" style="width:100%;height:100%;min-height:320px;display:flex;align-items:center;justify-content:center;font-size:4rem;color:#ccc;">
                <i class="bi bi-image"></i>
              </div>
              <span id="modal-badge" style="position:absolute;top:14px;left:14px;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:600;"></span>
            </div>
          </div>
          <!-- Details -->
          <div class="col-md-7 p-4 d-flex flex-column" style="max-height:90vh;overflow-y:auto;">
            <button type="button" class="btn-close ms-auto mb-2" data-bs-dismiss="modal"></button>
            <h4 id="modal-nom" style="font-weight:700;color:#2d2d2d;margin-bottom:6px;font-size:1.1rem;"></h4>
            <p id="modal-desc" style="font-size:0.85rem;color:#888;margin-bottom:14px;"></p>

            <!-- Info rows -->
            <div style="display:flex;flex-direction:column;gap:0;margin-bottom:16px;">
              <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #f5f5f5;">
                <i class="bi bi-tag-fill" style="color:#ce1212;width:18px;"></i>
                <span style="font-size:0.78rem;color:#999;min-width:90px;text-transform:uppercase;letter-spacing:0.5px;">Category</span>
                <strong id="modal-cat" style="font-size:0.85rem;color:#333;"></strong>
              </div>
              <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #f5f5f5;">
                <i class="bi bi-calendar3" style="color:#ce1212;width:18px;"></i>
                <span style="font-size:0.78rem;color:#999;min-width:90px;text-transform:uppercase;letter-spacing:0.5px;">Expiration</span>
                <strong id="modal-exp" style="font-size:0.85rem;color:#333;"></strong>
              </div>
              <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #f5f5f5;">
                <i class="bi bi-check-circle-fill" style="color:#ce1212;width:18px;"></i>
                <span style="font-size:0.78rem;color:#999;min-width:90px;text-transform:uppercase;letter-spacing:0.5px;">Status</span>
                <span id="modal-statut" style="font-size:0.82rem;font-weight:600;padding:3px 12px;border-radius:20px;"></span>
              </div>
            </div>

            <!-- Nutrition -->
            <div style="background:#f9f9f9;border-radius:12px;padding:14px;margin-bottom:16px;">
              <div style="font-size:0.72rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#999;margin-bottom:10px;">
                <i class="bi bi-lightning-charge-fill" style="color:#ce1212;"></i> Nutrition (per serving)
              </div>
              <div class="row g-2 text-center">
                <div class="col-3">
                  <div style="background:white;border-radius:10px;padding:10px 6px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                    <div style="font-size:1.1rem;font-weight:700;color:#ce1212;" id="modal-cal">—</div>
                    <div style="font-size:0.65rem;color:#999;text-transform:uppercase;letter-spacing:0.5px;">Calories</div>
                  </div>
                </div>
                <div class="col-3">
                  <div style="background:white;border-radius:10px;padding:10px 6px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                    <div style="font-size:1.1rem;font-weight:700;color:#1a73e8;" id="modal-prot">—</div>
                    <div style="font-size:0.65rem;color:#999;text-transform:uppercase;letter-spacing:0.5px;">Protein</div>
                  </div>
                </div>
                <div class="col-3">
                  <div style="background:white;border-radius:10px;padding:10px 6px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                    <div style="font-size:1.1rem;font-weight:700;color:#f57f17;" id="modal-carb">—</div>
                    <div style="font-size:0.65rem;color:#999;text-transform:uppercase;letter-spacing:0.5px;">Carbs</div>
                  </div>
                </div>
                <div class="col-3">
                  <div style="background:white;border-radius:10px;padding:10px 6px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                    <div style="font-size:1.1rem;font-weight:700;color:#2e7d32;" id="modal-fat">—</div>
                    <div style="font-size:0.65rem;color:#999;text-transform:uppercase;letter-spacing:0.5px;">Fat</div>
                  </div>
                </div>
              </div>
            </div>

            <div id="modal-price" style="font-size:1.8rem;font-weight:700;color:#ce1212;margin-bottom:16px;"></div>

            <div class="mt-auto d-flex gap-2">
              <button id="modal-btn-cart" class="btn-cart" style="flex:1;">
                <i class="bi bi-cart-plus me-2"></i>Ajouter au panier
              </button>
              <button class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius:25px;padding:9px 20px;font-size:13px;">
                Fermer
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function openProductModal(id) {
  var data = document.querySelector('.product-data[data-id="'+id+'"]');
  if (!data) return;

  var nom      = data.dataset.nom;
  var desc     = data.dataset.desc;
  var prix     = parseFloat(data.dataset.prix);
  var stock    = parseInt(data.dataset.stock);
  var exp      = data.dataset.expiration;
  var cat      = data.dataset.categorie;
  var statut   = data.dataset.statut;
  var image    = data.dataset.image;
  var cal      = data.dataset.calories;
  var prot     = data.dataset.proteines;
  var carb     = data.dataset.glucides;
  var fat      = data.dataset.lipides;

  document.getElementById('modal-nom').textContent   = nom;
  document.getElementById('modal-desc').textContent  = desc;
  document.getElementById('modal-cat').textContent   = cat;
  document.getElementById('modal-exp').textContent   = exp || '—';
  document.getElementById('modal-price').textContent = prix.toFixed(2).replace('.', ',') + ' DT';

  // Nutrition
  document.getElementById('modal-cal').textContent  = cal  ? cal + ' kcal' : '—';
  document.getElementById('modal-prot').textContent = prot ? prot + 'g'    : '—';
  document.getElementById('modal-carb').textContent = carb ? carb + 'g'    : '—';
  document.getElementById('modal-fat').textContent  = fat  ? fat + 'g'     : '—';

  // Status badge
  var badge = document.getElementById('modal-statut');
  badge.textContent = statut;
  badge.style.background = statut==='Disponible' ? '#e8f5e9' : statut==='Rupture' ? '#fdecea' : '#f5f5f5';
  badge.style.color      = statut==='Disponible' ? '#2e7d32' : statut==='Rupture' ? '#c62828' : '#757575';

  // Image badge
  var imgBadge = document.getElementById('modal-badge');
  imgBadge.textContent = statut;
  imgBadge.style.background = statut==='Disponible' ? '#28a745' : statut==='Rupture' ? '#dc3545' : '#6c757d';
  imgBadge.style.color = 'white';

  // Image
  var img = document.getElementById('modal-img');
  var placeholder = document.getElementById('modal-img-placeholder');
  if (image) { img.src=image; img.style.display='block'; placeholder.style.display='none'; }
  else       { img.style.display='none'; placeholder.style.display='flex'; }

  // Cart button
  var btn = document.getElementById('modal-btn-cart');
  btn.innerHTML = '<i class="bi bi-cart-plus me-2"></i>Ajouter au panier';
  if (statut === 'Disponible') {
    btn.disabled = false;
    btn.style.background = '#ce1212';
    btn.onclick = function() {
      var panier = getPanier();
      var ex = panier.find(function(p){ return p.id===String(id); });
      if (ex) { ex.quantite+=1; } else { panier.push({id:String(id),nom:nom,prix:prix,image:image,quantite:1}); }
      savePanier(panier);
      document.getElementById('panier-toast-msg').textContent = '"'+nom+'" ajouté !';
      var t=document.getElementById('panier-toast'); t.style.display='flex';
      setTimeout(function(){ t.style.display='none'; },3000);
      bootstrap.Modal.getInstance(document.getElementById('modalProduitDetail')).hide();
    };
  } else {
    btn.disabled = true;
    btn.style.background = '#ccc';
    btn.innerHTML = '<i class="bi bi-x-circle me-2"></i>Rupture de stock';
  }

  new bootstrap.Modal(document.getElementById('modalProduitDetail')).show();
}
</script>

<?php include("footer.php"); ?>
