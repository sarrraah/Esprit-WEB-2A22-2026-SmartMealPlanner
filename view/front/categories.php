<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/CategorieController.php';
require_once __DIR__ . '/../../controller/ProduitController.php';

$categorieController = new CategorieController();
$produitController   = new ProduitController();
$idCategorie = (int)($_GET['id'] ?? 0);

if ($idCategorie > 0) {
    $categorie = $categorieController->getCategorieById($idCategorie);
    if (!$categorie) { header('Location: categories.php'); exit; }
    $produits = $produitController->listProduitsByCategorie($idCategorie);
} else {
    $categories = $categorieController->getAllCategories();
}

include("header.php");
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Amatic+SC:wght@400;700&display=swap');

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

/* Category cards */
.cat-card {
  background: white; border-radius: 15px; overflow: hidden;
  box-shadow: 0 5px 20px rgba(0,0,0,0.08);
  transition: transform 0.3s, box-shadow 0.3s;
  height: 100%; display: block; text-decoration: none; color: inherit;
}
.cat-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 40px rgba(206,18,18,0.15);
  color: inherit; text-decoration: none;
}
.cat-img { width: 100%; height: 200px; object-fit: cover; }
.cat-img-placeholder {
  width: 100%; height: 200px; background: #f5f5f5;
  display: flex; align-items: center; justify-content: center;
  font-size: 3rem; color: #ccc;
}
.cat-info { padding: 16px; }
.cat-title { font-size: 1rem; font-weight: 600; color: #333; margin-bottom: 6px; }
.cat-desc  { font-size: 0.8rem; color: #888; }

/* Product cards (same as produits.php) */
.product-card {
  background: white; border-radius: 15px; overflow: hidden;
  box-shadow: 0 5px 20px rgba(0,0,0,0.08);
  transition: transform 0.3s, box-shadow 0.3s;
  height: 100%; display: flex; flex-direction: column;
}
.product-card:hover { transform: translateY(-8px); box-shadow: 0 15px 40px rgba(206,18,18,0.15); }
.product-img { width: 100%; height: 210px; object-fit: cover; }
.product-img-placeholder {
  width: 100%; height: 210px; background: #f5f5f5;
  display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #ccc;
}
.product-badge { position: absolute; top: 12px; left: 12px; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-dispo   { background: #28a745; color: white; }
.badge-rupture { background: #dc3545; color: white; }
.badge-epuise  { background: #6c757d; color: white; }
.product-info  { padding: 16px; flex: 1; display: flex; flex-direction: column; }
.product-title { font-size: 0.95rem; font-weight: 600; color: #333; margin-bottom: 6px; line-height: 1.4; }
.product-desc  { font-size: 0.8rem; color: #888; margin-bottom: 8px; flex: 1; }
.product-price { font-size: 1.3rem; font-weight: 700; color: #ce1212; margin: 8px 0; }
.btn-cart {
  background: #ce1212; color: white; border: none; padding: 9px 20px;
  border-radius: 25px; font-weight: 500; font-family: 'Inter', sans-serif;
  transition: 0.3s; width: 100%; font-size: 13px; cursor: pointer;
}
.btn-cart:hover { background: #b00e0e; transform: scale(1.02); color: white; }
.btn-cart:disabled { background: #ccc; cursor: not-allowed; transform: none; }
.btn-back {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 8px 20px; border-radius: 25px; border: 1px solid #ddd;
  background: white; font-family: 'Inter', sans-serif; font-size: 13px;
  text-decoration: none; color: #333; transition: 0.3s;
}
.btn-back:hover { background: #ce1212; color: white; border-color: #ce1212; }

/* ── WISHLIST HEART ── */
.btn-wishlist {
  position: absolute; bottom: 10px; right: 10px;
  width: 32px; height: 32px; border-radius: 50%;
  background: rgba(255,255,255,0.92); border: none;
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem; cursor: pointer; transition: 0.2s;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15); z-index: 2;
  color: #ccc;
}
.btn-wishlist:hover { transform: scale(1.15); }
.btn-wishlist.active { color: #ce1212; }
</style>

<?php if ($idCategorie > 0): ?>
<!-- ── MODE: PRODUITS PAR CATÉGORIE ── -->
<section class="section light-background py-5">
  <div class="container section-title">
    <h2>Catégories</h2>
    <p><span><?= htmlspecialchars($categorie['nom']) ?></span></p>
  </div>
  <div class="container">
    <div class="mb-4">
      <a href="categories.php" class="btn-back"><i class="bi bi-arrow-left"></i> Back to Categories</a>
    </div>

    <!-- Search & Sort -->
    <div style="background:white;padding:16px 20px;border-radius:15px;margin-bottom:24px;box-shadow:0 2px 10px rgba(0,0,0,0.05);">
      <div class="row align-items-center gy-2">
        <div class="col-md-5">
          <input type="text" id="searchProdCat" class="form-control search-input"
            placeholder="Rechercher un produit..." oninput="filtrerProduitsCat()">
        </div>
        <div class="col-md-4">
          <select id="sortProdCat" class="sort-select w-100" onchange="filtrerProduitsCat()">
            <option value="">— Trier —</option>
            <option value="nom-asc">Nom A → Z</option>
            <option value="nom-desc">Nom Z → A</option>
            <option value="prix-asc">Prix croissant ↑</option>
            <option value="prix-desc">Prix décroissant ↓</option>
          </select>
        </div>
        <div class="col-md-3">
          <select id="filterStatutCat" class="sort-select w-100" onchange="filtrerProduitsCat()">
            <option value="">Tous les statuts</option>
            <option value="Disponible">Disponible</option>
            <option value="Rupture">Rupture</option>
            <option value="Épuisé">Épuisé</option>
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

    <div class="row g-4" id="produitsCatGrid">
      <?php foreach ($produits as $produit): ?>
        <?php
        $img = $produit['image'] ?? '';
        if (empty($img))                         $imgSrc = '';
        elseif (str_starts_with($img, 'http'))   $imgSrc = $img;
        elseif (str_starts_with($img, 'meals/')) $imgSrc = '../../view/assets/img/' . $img;
        else                                      $imgSrc = UPLOAD_URL . $img;
        $statut   = determinerStatut($produit['quantiteStock'], $produit['dateExpiration']);
        $badgeCls = match($statut) { 'Disponible'=>'badge-dispo','Rupture'=>'badge-rupture',default=>'badge-epuise' };
        $isMealPrep = (int)($produit['categorie'] ?? $produit['id_categorie'] ?? 0) === 3
                   || strtolower($produit['categorie_nom'] ?? '') === 'meal prep packs';
        ?>
        <div class="col-lg-4 col-md-6 produit-cat-item"
             data-nom="<?= htmlspecialchars(strtolower($produit['nom']), ENT_QUOTES) ?>"
             data-prix="<?= (float)$produit['prix'] ?>"
             data-stock="<?= (int)$produit['quantiteStock'] ?>"
             data-statut="<?= htmlspecialchars($statut, ENT_QUOTES) ?>">
          <div class="product-card" onclick="openProductModal(<?= (int)$produit['id'] ?>)" style="cursor:pointer;">
            <div class="position-relative">
              <?php if ($imgSrc): ?>
                <img src="<?= htmlspecialchars($imgSrc) ?>" class="product-img" alt="<?= htmlspecialchars($produit['nom']) ?>">
              <?php else: ?>
                <div class="product-img-placeholder"><i class="bi bi-image"></i></div>
              <?php endif; ?>
              <span class="product-badge <?= $badgeCls ?>"><?= $statut ?></span>
              <span style="position:absolute;top:12px;right:12px;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;background:rgba(206,18,18,0.88);color:white;">
                <?= number_format((float)$produit['prix'],2,',',' ') ?> DT
              </span>
              <!-- Wishlist heart button -->
              <button class="btn-wishlist" onclick="event.stopPropagation();toggleWishlist(this)"
                data-id="<?= (int)$produit['id'] ?>"
                data-nom="<?= htmlspecialchars($produit['nom'], ENT_QUOTES) ?>"
                data-prix="<?= (float)$produit['prix'] ?>"
                data-image="<?= htmlspecialchars($imgSrc, ENT_QUOTES) ?>"
                title="Add to Wishlist">
                <i class="bi bi-heart-fill"></i>
              </button>
              <!-- Like counter badge -->
              <span class="like-count-badge" id="like-badge-<?= (int)$produit['id'] ?>"
                style="display:none;position:absolute;bottom:10px;left:10px;background:rgba(0,0,0,0.6);
                       color:white;border-radius:20px;padding:3px 9px;font-size:11px;font-weight:700;">
                ❤️ <span class="like-num">0</span>
              </span>
            </div>
            <div class="product-info">
              <div class="product-title"><?= htmlspecialchars($produit['nom']) ?></div>
              <div class="product-desc"><?= htmlspecialchars($produit['description'] ?? '') ?></div>
              <div class="product-price"><?= number_format((float)$produit['prix'],2,',',' ') ?> DT</div>
              <?php if ($statut === 'Disponible'): ?>
                <button class="btn-cart btn-ajouter-panier"
                        onclick="event.stopPropagation();"
                        data-id="<?= (int)$produit['id'] ?>"
                        data-nom="<?= htmlspecialchars($produit['nom'],ENT_QUOTES) ?>"
                        data-prix="<?= (float)$produit['prix'] ?>"
                        data-image="<?= htmlspecialchars($imgSrc,ENT_QUOTES) ?>">
                  <?php if ($isMealPrep): ?>
                    <i class="bi bi-basket2 me-2"></i>Get Ingredients
                  <?php else: ?>
                    <i class="bi bi-cart-plus me-2"></i>Add to Cart
                  <?php endif; ?>
                </button>
              <?php else: ?>
                <button class="btn-cart" disabled onclick="event.stopPropagation();"><i class="bi bi-x-circle me-2"></i>Out of Stock</button>
              <?php endif; ?>
            </div>
          </div>
          <!-- Hidden data for modal -->
          <div class="product-data" style="display:none;"
               data-id="<?= (int)$produit['id'] ?>"
               data-nom="<?= htmlspecialchars($produit['nom'],ENT_QUOTES) ?>"
               data-desc="<?= htmlspecialchars($produit['description']??'',ENT_QUOTES) ?>"
               data-prix="<?= (float)$produit['prix'] ?>"
               data-expiration="<?= htmlspecialchars($produit['dateExpiration']??'',ENT_QUOTES) ?>"
               data-categorie="<?= htmlspecialchars($produit['categorie_nom']??'',ENT_QUOTES) ?>"
               data-statut="<?= htmlspecialchars($statut,ENT_QUOTES) ?>"
               data-image="<?= htmlspecialchars($imgSrc,ENT_QUOTES) ?>"
               data-calories="<?= rand(150,550) ?>"
               data-proteines="<?= rand(8,35) ?>"
               data-glucides="<?= rand(10,60) ?>"
               data-lipides="<?= rand(3,20) ?>"></div>
        </div>
      <?php endforeach; ?>
      <div id="no-result-prodcat" style="display:none;" class="col-12 text-center py-5 text-muted">
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
        <h5 class="modal-title">🛒 My Cart</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="panier-contenu"><p class="text-muted text-center">Your cart is empty.</p></div>
      <div class="modal-footer d-flex justify-content-between align-items-center">
        <strong>Total: <span id="panier-total">0.00</span> DT</strong>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-danger btn-sm" onclick="viderPanier()">Clear</button>
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Continue Shopping</button>
          <button class="btn btn-danger" onclick="acheter()"><i class="bi bi-bag-check me-2"></i>Checkout</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function getPanier(){return JSON.parse(localStorage.getItem('panier')||'[]');}
function savePanier(p){localStorage.setItem('panier',JSON.stringify(p));updateBadge();}
function updateBadge(){var t=getPanier().reduce(function(s,p){return s+p.quantite;},0);var b=document.getElementById('panier-badge');if(b){b.textContent=t;b.style.display=t>0?'inline-block':'none';}}
function ouvrirPanier(){var panier=getPanier(),c=document.getElementById('panier-contenu'),tEl=document.getElementById('panier-total');if(!panier.length){c.innerHTML='<p class="text-muted text-center py-3">Votre panier est vide.</p>';tEl.textContent='0,00';}else{var html='<table class="table align-middle"><thead><tr><th>Produit</th><th>Prix</th><th>Qté</th><th>Sous-total</th><th></th></tr></thead><tbody>';var total=0;panier.forEach(function(p){var sous=p.prix*p.quantite;total+=sous;html+='<tr><td><div class="d-flex align-items-center gap-2">';if(p.image)html+='<img src="'+p.image+'" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">';html+='<strong>'+p.nom+'</strong></div></td><td>'+p.prix.toFixed(2).replace('.',',')+' DT</td><td><div class="d-flex align-items-center gap-2"><button class="btn btn-sm btn-outline-secondary" onclick="changerQte(\''+p.id+'\',-1)">−</button><span>'+p.quantite+'</span><button class="btn btn-sm btn-outline-secondary" onclick="changerQte(\''+p.id+'\',1)">+</button></div></td><td>'+sous.toFixed(2).replace('.',',')+' DT</td><td><button class="btn btn-sm btn-outline-danger" onclick="supprimerArticle(\''+p.id+'\')"><i class="bi bi-trash"></i></button></td></tr>';});html+='</tbody></table>';c.innerHTML=html;tEl.textContent=total.toFixed(2).replace('.',',');}new bootstrap.Modal(document.getElementById('modalPanier')).show();}
function changerQte(id,d){var p=getPanier(),i=p.find(function(x){return x.id===id;});if(i){i.quantite+=d;if(i.quantite<=0)p=p.filter(function(x){return x.id!==id;});}savePanier(p);ouvrirPanier();}
function supprimerArticle(id){savePanier(getPanier().filter(function(p){return p.id!==id;}));ouvrirPanier();}
function viderPanier(){savePanier([]);ouvrirPanier();}
function acheter(){
  var panier=getPanier();if(!panier.length)return;
  var total=panier.reduce(function(s,p){return s+p.prix*p.quantite;},0);
  var lines='';
  panier.forEach(function(p){lines+='<tr><td style="font-size:0.82rem;">'+p.nom+'</td><td style="font-size:0.82rem;">x'+p.quantite+'</td><td style="font-size:0.82rem;font-weight:600;color:#ce1212;">'+(p.prix*p.quantite).toFixed(2).replace('.',',')+' DT</td></tr>';});
  document.getElementById('checkout-order-lines').innerHTML=lines;
  // Default: livraison → +8 DT (free if subtotal >= 100)
  var freeDelivery=total>=100;
  var livRow=document.getElementById('checkout-livraison-row');
  livRow.style.display='flex';
  livRow.innerHTML=freeDelivery
    ?'<span style="font-size:0.82rem;color:#2e7d32;"><i class="bi bi-truck me-1"></i>Delivery fee <span style="background:#e8f5e9;border-radius:10px;padding:1px 8px;font-size:0.72rem;font-weight:700;">FREE 🎁</span></span><span style="font-size:0.82rem;color:#2e7d32;font-weight:700;text-decoration:line-through;opacity:0.5;">8,00 DT</span>'
    :'<span style="font-size:0.82rem;color:#555;"><i class="bi bi-truck me-1" style="color:#ce1212;"></i>Delivery fee</span><span style="font-size:0.82rem;color:#555;">8,00 DT</span>';
  document.getElementById('checkout-order-total').textContent=freeDelivery
    ?total.toFixed(2).replace('.',',')+' DT'
    :(total+8).toFixed(2).replace('.',',')+' DT';
  var cartModal=bootstrap.Modal.getInstance(document.getElementById('modalPanier'));
  if(cartModal)cartModal.hide();
  setTimeout(function(){new bootstrap.Modal(document.getElementById('modalCheckout')).show();},300);
}
document.querySelectorAll('.btn-ajouter-panier').forEach(function(btn){btn.addEventListener('click',function(){var p=getPanier();var id=this.dataset.id,nom=this.dataset.nom,prix=parseFloat(this.dataset.prix),image=this.dataset.image;var ex=p.find(function(x){return x.id===id;});if(ex){ex.quantite+=1;}else{p.push({id,nom,prix,image,quantite:1});}savePanier(p);document.getElementById('panier-toast-msg').textContent='"'+nom+'" ajouté !';var t=document.getElementById('panier-toast');t.style.display='flex';setTimeout(function(){t.style.display='none';},3000);});});
updateBadge();

// ── WISHLIST ──
function getWishlist(){return JSON.parse(localStorage.getItem('wishlist')||'[]');}
function saveWishlist(w){localStorage.setItem('wishlist',JSON.stringify(w));updateWishlistBadge();}
function updateWishlistBadge(){var c=getWishlist().length;var b=document.getElementById('wishlist-badge');if(b){b.textContent=c;b.style.display=c>0?'inline-block':'none';}}
function toggleWishlist(btn){
  var id=btn.dataset.id,nom=btn.dataset.nom,prix=parseFloat(btn.dataset.prix),image=btn.dataset.image;
  var w=getWishlist(),idx=w.findIndex(function(x){return x.id===id;});
  if(idx>=0){w.splice(idx,1);btn.classList.remove('active');}
  else{w.push({id,nom,prix,image});btn.classList.add('active');btn.style.transform='scale(1.4)';setTimeout(function(){btn.style.transform='';},200);}
  saveWishlist(w);
  // Sync like to DB
  fetch('toggle_like.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id_produit:parseInt(id)})})
    .then(function(r){return r.json();})
    .then(function(data){
      var badge=document.getElementById('like-badge-'+id);
      if(badge){badge.querySelector('.like-num').textContent=data.total;badge.style.display=data.total>0?'block':'none';}
    }).catch(function(){});
}
function ouvrirWishlist(){
  var w=getWishlist(),contenu=document.getElementById('wishlist-contenu');
  if(!w.length){
    contenu.innerHTML='<p class="text-muted text-center py-4"><i class="bi bi-heart" style="font-size:2rem;display:block;margin-bottom:8px;"></i>Your wishlist is empty.</p>';
  } else {
    var html='<div class="row g-3">';
    w.forEach(function(p){
      var dataEl=document.querySelector('.product-data[data-id="'+p.id+'"]');
      var desc=dataEl?dataEl.dataset.desc:'';
      var cat=dataEl?dataEl.dataset.categorie:'';
      var statut=dataEl?dataEl.dataset.statut:'';
      var badgeBg=statut==='Disponible'?'#28a745':statut==='Rupture'?'#dc3545':'#6c757d';
      html+='<div class="col-12 col-md-6">';
      // Wrapper with position:relative for the ✕ button
      html+='<div style="position:relative;background:white;border-radius:14px;box-shadow:0 3px 12px rgba(0,0,0,0.08);">';
      // ── ✕ top-right corner button ──
      html+='<button onclick="event.stopPropagation();removeFromWishlist(\''+p.id+'\')" title="Remove from wishlist" '
          +'style="position:absolute;top:-8px;right:-8px;width:24px;height:24px;border-radius:50%;'
          +'background:#ce1212;color:white;border:2px solid white;font-size:0.75rem;font-weight:900;'
          +'cursor:pointer;display:flex;align-items:center;justify-content:center;line-height:1;'
          +'z-index:10;box-shadow:0 2px 6px rgba(0,0,0,0.2);">✕</button>';
      // Card content
      html+='<div style="display:flex;gap:0;cursor:pointer;border-radius:14px;overflow:hidden;" onclick="wishlistOpenDetail(\''+p.id+'\')">';
      html+='<div style="width:110px;flex-shrink:0;position:relative;">';
      if(p.image)html+='<img src="'+p.image+'" style="width:110px;height:100%;min-height:110px;object-fit:cover;">';
      else html+='<div style="width:110px;min-height:110px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;"><i class="bi bi-image" style="color:#ccc;font-size:1.5rem;"></i></div>';
      if(statut)html+='<span style="position:absolute;bottom:6px;left:6px;background:'+badgeBg+';color:white;border-radius:10px;padding:2px 7px;font-size:0.65rem;font-weight:700;">'+statut+'</span>';
      html+='</div>';
      html+='<div style="padding:12px 14px;flex:1;min-width:0;">';
      html+='<div style="font-size:0.85rem;font-weight:700;color:#2d2d2d;margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+p.nom+'</div>';
      if(cat)html+='<div style="font-size:0.72rem;color:#999;margin-bottom:4px;"><i class="bi bi-tag-fill me-1" style="color:#ce1212;"></i>'+cat+'</div>';
      if(desc)html+='<div style="font-size:0.75rem;color:#888;margin-bottom:6px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">'+desc+'</div>';
      html+='<div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;">';
      html+='<span style="font-size:0.95rem;font-weight:700;color:#ce1212;">'+p.prix.toFixed(2).replace('.',',')+' DT</span>';
      html+='<button onclick="event.stopPropagation();wishlistAddToCart(\''+p.id+'\')" style="background:#ce1212;color:white;border:none;border-radius:20px;padding:4px 12px;font-size:0.72rem;font-weight:600;cursor:pointer;"><i class="bi bi-cart-plus me-1"></i>Cart</button>';
      html+='</div></div></div></div></div>';
    });
    html+='</div>';
    contenu.innerHTML=html;
  }
  var ct=document.getElementById('wishlist-count-title');
  if(ct)ct.textContent=w.length?'('+w.length+' item'+(w.length>1?'s':'')+')'  :'';
  new bootstrap.Modal(document.getElementById('modalWishlist')).show();
}
function wishlistAddToCart(id){
  var p=getWishlist().find(function(x){return x.id===id;});
  if(!p)return;
  var panier=getPanier(),ex=panier.find(function(x){return x.id===id;});
  if(ex){ex.quantite+=1;}else{panier.push({id:p.id,nom:p.nom,prix:p.prix,image:p.image,quantite:1});}
  savePanier(panier);
  document.getElementById('panier-toast-msg').textContent='"'+p.nom+'" added to cart!';
  var t=document.getElementById('panier-toast');t.style.display='flex';
  setTimeout(function(){t.style.display='none';},3000);
}
function wishlistAddAllToCart(){
  var w=getWishlist();if(!w.length)return;
  var panier=getPanier();
  w.forEach(function(p){var ex=panier.find(function(x){return x.id===p.id;});if(ex){ex.quantite+=1;}else{panier.push({id:p.id,nom:p.nom,prix:p.prix,image:p.image,quantite:1});}});
  savePanier(panier);
  bootstrap.Modal.getInstance(document.getElementById('modalWishlist')).hide();
  document.getElementById('panier-toast-msg').textContent=w.length+' item'+(w.length>1?'s':'')+' added to cart!';
  var t=document.getElementById('panier-toast');t.style.display='flex';
  setTimeout(function(){t.style.display='none';},3000);
}
function wishlistClearAll(){
  getWishlist().forEach(function(p){var btn=document.querySelector('.btn-wishlist[data-id="'+p.id+'"]');if(btn)btn.classList.remove('active');});
  saveWishlist([]);ouvrirWishlist();
}
function wishlistOpenDetail(id){
  bootstrap.Modal.getInstance(document.getElementById('modalWishlist')).hide();
  setTimeout(function(){openProductModal(parseInt(id));},350);
}
function removeFromWishlist(id){
  saveWishlist(getWishlist().filter(function(x){return x.id!==id;}));
  var btn=document.querySelector('.btn-wishlist[data-id="'+id+'"]');
  if(btn)btn.classList.remove('active');
  ouvrirWishlist();
}
document.addEventListener('DOMContentLoaded',function(){
  var w=getWishlist();
  w.forEach(function(item){var btn=document.querySelector('.btn-wishlist[data-id="'+item.id+'"]');if(btn)btn.classList.add('active');});
  updateWishlistBadge();
});
</script>

<!-- ── WISHLIST MODAL ── -->
<div class="modal fade" id="modalWishlist" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content" style="border-radius:16px;border:none;">
      <div class="modal-header" style="border-bottom:1px solid #f0f0f0;padding:16px 24px;flex-wrap:wrap;gap:10px;">
        <h5 class="modal-title" style="font-family:'Amatic SC',cursive;font-size:1.6rem;font-weight:700;color:#2d2d2d;">
          <i class="bi bi-heart-fill me-2" style="color:#ce1212;"></i>My Wishlist
          <span id="wishlist-count-title" style="font-size:0.9rem;color:#999;font-family:'Inter',sans-serif;font-weight:400;margin-left:8px;"></span>
        </h5>
        <div style="display:flex;gap:8px;margin-left:auto;margin-right:32px;">
          <button onclick="wishlistAddAllToCart()" style="background:#ce1212;color:white;border:none;border-radius:20px;padding:7px 16px;font-size:0.78rem;font-weight:600;cursor:pointer;">
            <i class="bi bi-cart-plus me-1"></i>Add All to Cart
          </button>
          <button onclick="wishlistClearAll()" style="background:#f5f5f5;color:#666;border:none;border-radius:20px;padding:7px 16px;font-size:0.78rem;font-weight:600;cursor:pointer;">
            <i class="bi bi-trash me-1"></i>Clear All
          </button>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:20px 24px;" id="wishlist-contenu">
        <p class="text-muted text-center py-4">Your wishlist is empty.</p>
      </div>
    </div>
  </div>
</div>

<!-- ── CHECKOUT MODAL ── -->
<div class="modal fade" id="modalCheckout" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content" style="border-radius:16px;border:none;">
      <div class="modal-header" style="border-bottom:1px solid #f0f0f0;padding:18px 24px;">
        <h5 class="modal-title" style="font-family:'Amatic SC',cursive;font-size:1.6rem;font-weight:700;color:#2d2d2d;">
          <i class="bi bi-bag-check-fill me-2" style="color:#ce1212;"></i>Checkout
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:24px;">
        <div class="row g-4">
          <div class="col-md-7">
            <form id="checkoutForm" onsubmit="confirmerCommande(event)">
              <div style="font-size:0.7rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#999;margin-bottom:12px;">
                <i class="bi bi-person-fill" style="color:#ce1212;"></i> Personal Information
              </div>
              <div class="row g-2 mb-3">
                <div class="col-6">
                  <input type="text" class="form-control" id="co-prenom" placeholder="First name" required style="border-radius:10px;border:1px solid #e0e0e0;font-size:0.85rem;padding:10px 14px;">
                  <div id="co-prenom-err" style="display:none;font-size:0.75rem;color:#dc3545;margin-top:4px;"><i class="bi bi-exclamation-circle me-1"></i>Letters only (min. 2 characters)</div>
                </div>
                <div class="col-6">
                  <input type="text" class="form-control" id="co-nom" placeholder="Last name" required style="border-radius:10px;border:1px solid #e0e0e0;font-size:0.85rem;padding:10px 14px;">
                  <div id="co-nom-err" style="display:none;font-size:0.75rem;color:#dc3545;margin-top:4px;"><i class="bi bi-exclamation-circle me-1"></i>Letters only (min. 2 characters)</div>
                </div>
                <div class="col-12">
                  <input type="email" class="form-control" id="co-email" placeholder="Email address" required style="border-radius:10px;border:1px solid #e0e0e0;font-size:0.85rem;padding:10px 14px;">
                </div>
                <div class="col-12">
                  <input type="text" class="form-control" id="co-adresse" placeholder="Street address" required style="border-radius:10px;border:1px solid #e0e0e0;font-size:0.85rem;padding:10px 14px;">
                </div>
                <div class="col-12">
                  <input type="text" class="form-control" id="co-localisation" placeholder="City / Region" required style="border-radius:10px;border:1px solid #e0e0e0;font-size:0.85rem;padding:10px 14px;">
                </div>
              </div>
              <div style="font-size:0.7rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#999;margin-bottom:12px;">
                <i class="bi bi-truck" style="color:#ce1212;"></i> Delivery Method
              </div>
              <div class="row g-2 mb-3">
                <div class="col-6">
                  <label id="lbl-livraison" style="display:flex;align-items:center;gap:10px;border:2px solid #ce1212;border-radius:12px;padding:12px 14px;cursor:pointer;background:#fff8f8;">
                    <input type="radio" name="livraison" value="livraison" checked onchange="togglePaiement()" style="accent-color:#ce1212;">
                    <div>
                      <div style="font-size:0.82rem;font-weight:600;color:#2d2d2d;"><i class="bi bi-truck me-1" style="color:#ce1212;"></i>Home Delivery</div>
                      <div style="font-size:0.7rem;color:#999;">Pay on delivery</div>
                    </div>
                  </label>
                </div>
                <div class="col-6">
                  <label id="lbl-carte" style="display:flex;align-items:center;gap:10px;border:2px solid #e0e0e0;border-radius:12px;padding:12px 14px;cursor:pointer;background:white;">
                    <input type="radio" name="livraison" value="carte" onchange="togglePaiement()" style="accent-color:#ce1212;">
                    <div>
                      <div style="font-size:0.82rem;font-weight:600;color:#2d2d2d;"><i class="bi bi-credit-card me-1" style="color:#1a73e8;"></i>Credit Card</div>
                      <div style="font-size:0.7rem;color:#999;">Pay now online</div>
                    </div>
                  </label>
                </div>
              </div>
              <div id="card-details" style="display:none;">
                <div style="font-size:0.7rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#999;margin-bottom:12px;">
                  <i class="bi bi-credit-card-2-front" style="color:#1a73e8;"></i> Card Details
                </div>
                <div style="background:#f9f9f9;border-radius:12px;padding:16px;" class="mb-3">
                  <div class="mb-2">
                    <input type="text" class="form-control" id="co-card-num" placeholder="Card number (16 digits)" maxlength="19" oninput="formatCardNum(this)" style="border-radius:10px;border:1px solid #e0e0e0;font-size:0.85rem;padding:10px 14px;letter-spacing:2px;">
                    <div id="co-card-num-err" style="display:none;font-size:0.75rem;color:#dc3545;margin-top:4px;"><i class="bi bi-exclamation-circle me-1"></i>Enter a valid 16-digit card number</div>
                  </div>
                  <div class="row g-2">
                    <div class="col-6">
                      <input type="text" class="form-control" id="co-card-exp" placeholder="MM / YY" maxlength="7" oninput="formatExpiry(this)" style="border-radius:10px;border:1px solid #e0e0e0;font-size:0.85rem;padding:10px 14px;">
                      <div id="co-card-exp-err" style="display:none;font-size:0.75rem;color:#dc3545;margin-top:4px;"><i class="bi bi-exclamation-circle me-1"></i>Invalid or expired date</div>
                    </div>
                    <div class="col-6">
                      <input type="text" class="form-control" id="co-card-cvv" placeholder="CVV" maxlength="3" oninput="this.value=this.value.replace(/\D/g,'')" style="border-radius:10px;border:1px solid #e0e0e0;font-size:0.85rem;padding:10px 14px;">
                      <div id="co-card-cvv-err" style="display:none;font-size:0.75rem;color:#dc3545;margin-top:4px;"><i class="bi bi-exclamation-circle me-1"></i>3-digit CVV required</div>
                    </div>
                  </div>
                  <div id="card-type-icons" style="margin-top:10px;display:flex;gap:8px;align-items:center;">
                    <img id="icon-visa" src="https://upload.wikimedia.org/wikipedia/commons/0/04/Visa.svg" style="height:22px;opacity:0.35;transition:opacity 0.2s;">
                    <img id="icon-mc" src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" style="height:22px;opacity:0.35;transition:opacity 0.2s;">
                    <span id="card-type-label" style="font-size:0.72rem;color:#999;margin-left:4px;"></span>
                  </div>
                </div>
              </div>
              <button type="submit" class="btn btn-danger w-100" style="border-radius:25px;padding:12px;font-weight:600;font-size:0.9rem;">
                <i class="bi bi-check-circle me-2"></i>Confirm Order
              </button>
            </form>
          </div>
          <div class="col-md-5">
            <div style="background:#f9f9f9;border-radius:14px;padding:18px;">
              <div style="font-size:0.7rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#999;margin-bottom:12px;">
                <i class="bi bi-receipt" style="color:#ce1212;"></i> Order Summary
              </div>
              <table class="table table-borderless mb-0" style="font-size:0.82rem;">
                <tbody id="checkout-order-lines"></tbody>
              </table>
              <hr style="border-color:#e0e0e0;">
              <div id="checkout-livraison-row" style="display:none;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <span style="font-size:0.82rem;color:#555;"><i class="bi bi-truck me-1" style="color:#ce1212;"></i>Delivery fee</span>
                <span style="font-size:0.82rem;color:#555;">8,00 DT</span>
              </div>
              <div style="display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:0.85rem;font-weight:600;color:#2d2d2d;">Total</span>
                <span style="font-size:1.2rem;font-weight:700;color:#ce1212;" id="checkout-order-total">0,00 DT</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// ── Name validation ──
function validerNom(val) {
  return /^[A-Za-zÀ-ÖØ-öø-ÿ\s'\-]+$/.test(val.trim()) && val.trim().length >= 2;
}
function attachNomValidation(inputId) {
  var el = document.getElementById(inputId);
  if (!el) return;
  el.addEventListener('input', function() {
    var ok = validerNom(this.value);
    this.style.borderColor = ok || this.value === '' ? '#e0e0e0' : '#dc3545';
    var err = document.getElementById(inputId + '-err');
    if (err) err.style.display = ok || this.value === '' ? 'none' : 'block';
  });
}
attachNomValidation('co-prenom');
attachNomValidation('co-nom');

function togglePaiement(){
  var val=document.querySelector('input[name="livraison"]:checked').value;
  var cardDiv=document.getElementById('card-details');
  var lblLiv=document.getElementById('lbl-livraison');
  var lblCarte=document.getElementById('lbl-carte');
  var livRow=document.getElementById('checkout-livraison-row');
  var panier=getPanier();
  var subtotal=panier.reduce(function(s,p){return s+p.prix*p.quantite;},0);
  var freeDelivery=subtotal>=100;
  if(val==='carte'){
    cardDiv.style.display='block';
    lblCarte.style.border='2px solid #1a73e8';lblCarte.style.background='#f0f4ff';
    lblLiv.style.border='2px solid #e0e0e0';lblLiv.style.background='white';
    livRow.style.display='none';
    document.getElementById('checkout-order-total').textContent=subtotal.toFixed(2).replace('.',',')+' DT';
    document.getElementById('co-card-num').required=true;
    document.getElementById('co-card-exp').required=true;
    document.getElementById('co-card-cvv').required=true;
  }else{
    cardDiv.style.display='none';
    lblLiv.style.border='2px solid #ce1212';lblLiv.style.background='#fff8f8';
    lblCarte.style.border='2px solid #e0e0e0';lblCarte.style.background='white';
    livRow.style.display='flex';
    livRow.innerHTML=freeDelivery
      ?'<span style="font-size:0.82rem;color:#2e7d32;"><i class="bi bi-truck me-1"></i>Delivery fee <span style="background:#e8f5e9;border-radius:10px;padding:1px 8px;font-size:0.72rem;font-weight:700;">FREE 🎁</span></span><span style="font-size:0.82rem;color:#2e7d32;font-weight:700;text-decoration:line-through;opacity:0.5;">8,00 DT</span>'
      :'<span style="font-size:0.82rem;color:#555;"><i class="bi bi-truck me-1" style="color:#ce1212;"></i>Delivery fee</span><span style="font-size:0.82rem;color:#555;">8,00 DT</span>';
    document.getElementById('checkout-order-total').textContent=freeDelivery
      ?subtotal.toFixed(2).replace('.',',')+' DT'
      :(subtotal+8).toFixed(2).replace('.',',')+' DT';
    document.getElementById('co-card-num').required=false;
    document.getElementById('co-card-exp').required=false;
    document.getElementById('co-card-cvv').required=false;
  }
}
function formatCardNum(el){
  var raw=el.value.replace(/\D/g,'').substring(0,16);
  el.value=raw.replace(/(.{4})/g,'$1 ').trim();
  var visa=document.getElementById('icon-visa'),mc=document.getElementById('icon-mc'),lbl=document.getElementById('card-type-label');
  if(/^4/.test(raw)){visa.style.opacity='1';mc.style.opacity='0.2';lbl.textContent='Visa';}
  else if(/^5[1-5]/.test(raw)||/^2[2-7]/.test(raw)){mc.style.opacity='1';visa.style.opacity='0.2';lbl.textContent='Mastercard';}
  else{visa.style.opacity='0.35';mc.style.opacity='0.35';lbl.textContent='';}
  var ok=raw.length===16;
  el.style.borderColor=raw.length>0?(ok?'#28a745':'#dc3545'):'#e0e0e0';
  document.getElementById('co-card-num-err').style.display=raw.length>0&&!ok?'block':'none';
}
function formatExpiry(el){
  var raw=el.value.replace(/\D/g,'').substring(0,4);
  if(raw.length>=3)raw=raw.substring(0,2)+' / '+raw.substring(2);
  el.value=raw;
  var digits=el.value.replace(/\D/g,''),ok=false;
  if(digits.length===4){var mm=parseInt(digits.substring(0,2)),yy=parseInt(digits.substring(2,4)),now=new Date(),curYY=now.getFullYear()%100,curMM=now.getMonth()+1;ok=mm>=1&&mm<=12&&(yy>curYY||(yy===curYY&&mm>=curMM));}
  el.style.borderColor=digits.length>0?(ok?'#28a745':'#dc3545'):'#e0e0e0';
  document.getElementById('co-card-exp-err').style.display=digits.length>0&&!ok?'block':'none';
}
document.addEventListener('DOMContentLoaded',function(){
  var cvvEl=document.getElementById('co-card-cvv');
  if(cvvEl){cvvEl.addEventListener('input',function(){this.value=this.value.replace(/\D/g,'');var ok=this.value.length===3;this.style.borderColor=this.value.length>0?(ok?'#28a745':'#dc3545'):'#e0e0e0';document.getElementById('co-card-cvv-err').style.display=this.value.length>0&&!ok?'block':'none';});}
});
function confirmerCommande(e){
  e.preventDefault();
  var prenom=document.getElementById('co-prenom').value.trim();
  var nom=document.getElementById('co-nom').value.trim();

  if(!validerNom(prenom)){
    document.getElementById('co-prenom').style.borderColor='#dc3545';
    document.getElementById('co-prenom-err').style.display='block';
    document.getElementById('co-prenom').focus();
    return;
  }
  if(!validerNom(nom)){
    document.getElementById('co-nom').style.borderColor='#dc3545';
    document.getElementById('co-nom-err').style.display='block';
    document.getElementById('co-nom').focus();
    return;
  }

  var method=document.querySelector('input[name="livraison"]:checked').value;
  var panier=getPanier();
  var subtotal=panier.reduce(function(s,p){return s+p.prix*p.quantite;},0);
  var freeDelivery=subtotal>=100;
  var total=(method==='livraison'&&!freeDelivery)?subtotal+8:subtotal;
  var items=panier.map(function(p){return {id:p.id,quantite:p.quantite};});
  var methodLabel=method==='carte'?'Credit Card':(freeDelivery?'Home Delivery (FREE 🎁)':'Home Delivery (+8,00 DT)');

  if(method==='carte'){
    var cardNum=document.getElementById('co-card-num').value.replace(/\D/g,'');
    var expRaw=document.getElementById('co-card-exp').value.replace(/\D/g,'');
    var cvv=document.getElementById('co-card-cvv').value;
    var cardOk=cardNum.length===16;
    var expOk=false;
    if(expRaw.length===4){var mm=parseInt(expRaw.substring(0,2)),yy=parseInt(expRaw.substring(2,4)),now=new Date(),curYY=now.getFullYear()%100,curMM=now.getMonth()+1;expOk=mm>=1&&mm<=12&&(yy>curYY||(yy===curYY&&mm>=curMM));}
    var cvvOk=cvv.length===3;
    if(!cardOk){document.getElementById('co-card-num').style.borderColor='#dc3545';document.getElementById('co-card-num-err').style.display='block';document.getElementById('co-card-num').focus();return;}
    if(!expOk){document.getElementById('co-card-exp').style.borderColor='#dc3545';document.getElementById('co-card-exp-err').style.display='block';document.getElementById('co-card-exp').focus();return;}
    if(!cvvOk){document.getElementById('co-card-cvv').style.borderColor='#dc3545';document.getElementById('co-card-cvv-err').style.display='block';document.getElementById('co-card-cvv').focus();return;}
  }

  var checkoutModal=bootstrap.Modal.getInstance(document.getElementById('modalCheckout'));
  if(checkoutModal)checkoutModal.hide();

  savePanier([]);

  fetch('update_stock.php',{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({items:items})
  }).catch(function(){});

  document.getElementById('modalCheckout').addEventListener('hidden.bs.modal', function onHidden(){
    document.getElementById('modalCheckout').removeEventListener('hidden.bs.modal', onHidden);
    document.getElementById('order-success-name').textContent=prenom+' '+nom;
    document.getElementById('order-success-method').textContent=methodLabel;
    document.getElementById('order-success-total').textContent=total.toFixed(2).replace('.',',')+' DT';
    var t=document.getElementById('order-success-toast');
    t.style.display='flex';
    setTimeout(function(){t.style.display='none';},6000);
  },{once:true});
}
</script>

<!-- ── ORDER SUCCESS TOAST ── -->
<div id="order-success-toast" style="display:none;position:fixed;bottom:28px;right:28px;z-index:9999;
     background:white;border-radius:14px;box-shadow:0 8px 32px rgba(0,0,0,0.15);
     padding:20px 24px;max-width:340px;border-left:5px solid #28a745;animation:slideIn 0.3s ease;">
  <div style="display:flex;align-items:flex-start;gap:12px;">
    <div style="font-size:1.8rem;line-height:1;">✅</div>
    <div>
      <div style="font-weight:700;font-size:0.95rem;color:#2d2d2d;margin-bottom:4px;">Order Confirmed!</div>
      <div style="font-size:0.82rem;color:#555;">
        Thank you, <strong id="order-success-name"></strong>!<br>
        Payment: <span id="order-success-method"></span><br>
        Total: <strong style="color:#ce1212;" id="order-success-total"></strong>
      </div>
    </div>
    <button onclick="document.getElementById('order-success-toast').style.display='none'"
      style="background:none;border:none;font-size:1.1rem;color:#999;cursor:pointer;margin-left:auto;padding:0;">✕</button>
  </div>
</div>
<style>
@keyframes slideIn { from { transform: translateX(100px); opacity:0; } to { transform: translateX(0); opacity:1; } }
</style>

<script>
function filtrerProduitsCat() {
  var q      = document.getElementById('searchProdCat').value.toLowerCase().trim();
  var tri    = document.getElementById('sortProdCat').value;
  var statut = document.getElementById('filterStatutCat').value;
  var items  = Array.from(document.querySelectorAll('.produit-cat-item'));
  var visible = 0;
  items.forEach(function(el) {
    var matchQ = !q      || el.dataset.nom.includes(q);
    var matchS = !statut || el.dataset.statut === statut;
    el.style.display = (matchQ && matchS) ? '' : 'none';
    if (matchQ && matchS) visible++;
  });
  if (tri) {
    var field = tri.split('-')[0], dir = tri.split('-')[1];
    var vis = items.filter(function(el){ return el.style.display !== 'none'; });
    vis.sort(function(a, b) {
      if (field === 'nom')   return dir==='asc' ? (a.dataset.nom||'').localeCompare(b.dataset.nom||'') : (b.dataset.nom||'').localeCompare(a.dataset.nom||'');
      if (field === 'prix')  { var va=parseFloat(a.dataset.prix)||0, vb=parseFloat(b.dataset.prix)||0; return dir==='asc'?va-vb:vb-va; }
      if (field === 'stock') { var va=parseInt(a.dataset.stock)||0,  vb=parseInt(b.dataset.stock)||0;  return dir==='asc'?va-vb:vb-va; }
    });
    var grid = document.getElementById('produitsCatGrid');
    vis.forEach(function(el){ grid.appendChild(el); });
  }
  document.getElementById('no-result-prodcat').style.display = visible === 0 ? '' : 'none';
}
</script>

<!-- ── PRODUCT DETAIL MODAL ── -->
<div class="modal fade" id="modalProduitDetail" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;overflow:hidden;border:none;">
      <div class="modal-body p-0">
        <div class="row g-0">
          <div class="col-md-5">
            <div style="height:100%;min-height:320px;background:#f5f5f5;position:relative;">
              <img id="modal-img" src="" alt="" style="width:100%;height:100%;object-fit:cover;min-height:320px;display:none;">
              <div id="modal-img-placeholder" style="width:100%;height:100%;min-height:320px;display:flex;align-items:center;justify-content:center;font-size:4rem;color:#ccc;"><i class="bi bi-image"></i></div>
              <span id="modal-badge" style="position:absolute;top:14px;left:14px;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:600;"></span>
            </div>
          </div>
          <div class="col-md-7 p-4 d-flex flex-column" style="max-height:90vh;overflow-y:auto;">
            <button type="button" class="btn-close ms-auto mb-2" data-bs-dismiss="modal"></button>
            <h4 id="modal-nom" style="font-family:'Amatic SC',cursive;font-size:1.6rem;font-weight:700;color:#2d2d2d;margin-bottom:6px;"></h4>
            <div id="modal-desc-wrap" style="margin-bottom:14px;"></div>

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
              <div style="display:flex;align-items:center;gap:10px;padding:8px 0;">
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

            <!-- ── AVIS SECTION ── -->
            <div style="border-top:1px solid #f0f0f0;padding-top:14px;margin-bottom:16px;">
              <div style="font-size:0.7rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#999;margin-bottom:10px;display:flex;align-items:center;gap:6px;">
                <i class="bi bi-star-fill" style="color:#f39c12;"></i> Avis clients
                <span id="modal-avg-stars" style="color:#f39c12;font-size:0.9rem;margin-left:4px;"></span>
                <span id="modal-avg-text" style="font-size:0.72rem;color:#bbb;font-weight:400;text-transform:none;letter-spacing:0;"></span>
              </div>
              <div id="modal-avis-list" style="max-height:160px;overflow-y:auto;margin-bottom:12px;"></div>
              <form id="modal-avis-form" style="background:#f9f9f9;border-radius:10px;padding:12px;">
                <input type="hidden" id="modal-avis-produit-id" value="">
                <div style="margin-bottom:8px;">
                  <div id="modal-star-picker" style="display:flex;gap:4px;cursor:pointer;">
                    <span class="mstar" data-val="1" style="font-size:1.8rem;color:#ddd;transition:color 0.1s;">★</span>
                    <span class="mstar" data-val="2" style="font-size:1.8rem;color:#ddd;transition:color 0.1s;">★</span>
                    <span class="mstar" data-val="3" style="font-size:1.8rem;color:#ddd;transition:color 0.1s;">★</span>
                    <span class="mstar" data-val="4" style="font-size:1.8rem;color:#ddd;transition:color 0.1s;">★</span>
                    <span class="mstar" data-val="5" style="font-size:1.8rem;color:#ddd;transition:color 0.1s;">★</span>
                  </div>
                  <input type="hidden" id="modal-note-value" value="">
                  <div style="font-size:0.7rem;color:#bbb;margin-top:2px;" id="modal-star-hint">Cliquez pour noter</div>
                </div>
                <textarea id="modal-avis-comment" rows="2"
                  style="border-radius:6px;border:1px solid #e0e0e0;padding:8px 12px;font-size:0.82rem;width:100%;outline:none;resize:none;font-family:'Inter',sans-serif;margin-bottom:8px;"
                  placeholder="Votre commentaire..."></textarea>
                <button type="submit"
                  style="background:#ce1212;color:white;border:none;border-radius:20px;padding:7px 20px;font-size:0.8rem;font-weight:600;cursor:pointer;">
                  <i class="bi bi-send me-1"></i>Envoyer
                </button>
                <span id="modal-avis-success" style="display:none;font-size:0.8rem;color:#2e7d32;margin-left:10px;">
                  <i class="bi bi-check-circle-fill me-1"></i>Merci !
                </span>
              </form>
            </div>

            <div class="mt-auto d-flex gap-2">
              <button id="modal-btn-cart" class="btn-cart" style="flex:1;">
                <i class="bi bi-cart-plus me-2"></i>Add to Cart
              </button>
              <button class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius:25px;padding:9px 20px;font-size:13px;">Close</button>
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

  // Init avis section
  document.getElementById('modal-avis-produit-id').value = id;
  document.getElementById('modal-avis-success').style.display = 'none';
  document.getElementById('modal-avis-comment').value = '';
  resetModalStars();
  loadModalAvis(id);
  var nom=data.dataset.nom, desc=data.dataset.desc, prix=parseFloat(data.dataset.prix);
  var exp=data.dataset.expiration, cat=data.dataset.categorie;
  var statut=data.dataset.statut, image=data.dataset.image;
  var cal=data.dataset.calories, prot=data.dataset.proteines;
  var carb=data.dataset.glucides, fat=data.dataset.lipides;

  document.getElementById('modal-nom').textContent  = nom;

  // Description — adapt for Meal Prep
  var isMealPrepDesc = (cat.toLowerCase().indexOf('meal prep') !== -1);
  var cleanDesc = desc.replace(/\s*—\s*(repas|breakfast|lunch|dinner|préparez|dégustez).*/i, '');
  var descWrap = document.getElementById('modal-desc-wrap');
  if (isMealPrepDesc) {
    descWrap.innerHTML =
      '<div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:#ce1212;margin-bottom:4px;">' +
      '<i class="bi bi-basket2-fill me-1"></i>Ingredients included:</div>' +
      '<p style="font-size:0.85rem;color:#888;margin:0;">' + cleanDesc + '</p>';
  } else {
    descWrap.innerHTML = '<p style="font-size:0.85rem;color:#888;margin:0;">' + desc + '</p>';
  }

  document.getElementById('modal-cat').textContent  = cat;
  document.getElementById('modal-exp').textContent  = exp || '—';
  document.getElementById('modal-price').textContent = prix.toFixed(2).replace('.',',')+' DT';
  document.getElementById('modal-cal').textContent  = cal  ? cal+' kcal' : '—';
  document.getElementById('modal-prot').textContent = prot ? prot+'g' : '—';
  document.getElementById('modal-carb').textContent = carb ? carb+'g' : '—';
  document.getElementById('modal-fat').textContent  = fat  ? fat+'g'  : '—';

  var badge = document.getElementById('modal-statut');
  badge.textContent = statut;
  badge.style.background = statut==='Disponible'?'#e8f5e9':statut==='Rupture'?'#fdecea':'#f5f5f5';
  badge.style.color      = statut==='Disponible'?'#2e7d32':statut==='Rupture'?'#c62828':'#757575';

  var imgBadge = document.getElementById('modal-badge');
  imgBadge.textContent = statut;
  imgBadge.style.background = statut==='Disponible'?'#28a745':statut==='Rupture'?'#dc3545':'#6c757d';
  imgBadge.style.color = 'white';

  var img=document.getElementById('modal-img'), ph=document.getElementById('modal-img-placeholder');
  if (image) { img.src=image; img.style.display='block'; ph.style.display='none'; }
  else       { img.style.display='none'; ph.style.display='flex'; }

  var btn = document.getElementById('modal-btn-cart');
  var isMealPrep = (cat.toLowerCase().indexOf('meal prep') !== -1);
  btn.innerHTML = isMealPrep
    ? '<i class="bi bi-basket2 me-2"></i>Get Ingredients'
    : '<i class="bi bi-cart-plus me-2"></i>Add to Cart';
  if (statut==='Disponible') {
    btn.disabled=false; btn.style.background='#ce1212';
    btn.onclick = function() {
      var panier=getPanier(), ex=panier.find(function(p){return p.id===String(id);});
      if(ex){ex.quantite+=1;}else{panier.push({id:String(id),nom:nom,prix:prix,image:image,quantite:1});}
      savePanier(panier);
      document.getElementById('panier-toast-msg').textContent='"'+nom+'" ajouté !';
      var t=document.getElementById('panier-toast');t.style.display='flex';
      setTimeout(function(){t.style.display='none';},3000);
      bootstrap.Modal.getInstance(document.getElementById('modalProduitDetail')).hide();
    };
  } else {
    btn.disabled=true; btn.style.background='#ccc';
    btn.innerHTML='<i class="bi bi-x-circle me-2"></i>Out of Stock';
  }
  new bootstrap.Modal(document.getElementById('modalProduitDetail')).show();
}
</script>

<script>
// ── AVIS AJAX ──
var currentNote = 0;

function resetModalStars() {
  currentNote = 0;
  document.querySelectorAll('.mstar').forEach(function(s){ s.style.color = '#ddd'; });
  document.getElementById('modal-star-hint').textContent = 'Cliquez pour noter';
  document.getElementById('modal-note-value').value = '';
}

function loadModalAvis(produitId) {
  var list     = document.getElementById('modal-avis-list');
  var avgStars = document.getElementById('modal-avg-stars');
  var avgText  = document.getElementById('modal-avg-text');
  list.innerHTML = '<div style="font-size:0.8rem;color:#bbb;padding:6px 0;">Chargement...</div>';
  fetch('../front/get_avis.php?id_produit=' + produitId)
    .then(function(r){ return r.json(); })
    .then(function(data) {
      if (!data.avis || data.avis.length === 0) {
        list.innerHTML = '<div style="font-size:0.8rem;color:#bbb;font-style:italic;padding:4px 0;">Aucun avis. Soyez le premier !</div>';
        avgStars.textContent = ''; avgText.textContent = ''; return;
      }
      var avg = parseFloat(data.avg) || 0;
      avgStars.textContent = '★'.repeat(Math.round(avg)) + '☆'.repeat(5 - Math.round(avg));
      avgText.textContent  = avg.toFixed(1) + '/5 (' + data.total + ' avis)';
      var html = '';
      data.avis.forEach(function(a) {
        var stars = '★'.repeat(parseInt(a.note)) + '☆'.repeat(5 - parseInt(a.note));
        html += '<div style="background:white;border-radius:8px;padding:10px 12px;margin-bottom:8px;border:1px solid #f0f0f0;">';
        html += '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">';
        html += '<span style="color:#f39c12;font-size:0.95rem;">' + stars + '</span>';
        html += '<span style="font-size:0.7rem;color:#bbb;">' + a.date_avis + '</span></div>';
        html += '<p style="font-size:0.82rem;color:#555;margin:0;line-height:1.5;">' + escHtml(a.commentaire) + '</p></div>';
      });
      list.innerHTML = html;
    })
    .catch(function(){ list.innerHTML = '<div style="font-size:0.8rem;color:#bbb;">Impossible de charger les avis.</div>'; });
}

function escHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

document.querySelectorAll('.mstar').forEach(function(star) {
  star.addEventListener('mouseover', function() {
    var val = parseInt(this.dataset.val);
    document.querySelectorAll('.mstar').forEach(function(s){ s.style.color = parseInt(s.dataset.val) <= val ? '#f39c12' : '#ddd'; });
  });
  star.addEventListener('mouseout', function() {
    document.querySelectorAll('.mstar').forEach(function(s){ s.style.color = parseInt(s.dataset.val) <= currentNote ? '#f39c12' : '#ddd'; });
  });
  star.addEventListener('click', function() {
    currentNote = parseInt(this.dataset.val);
    document.getElementById('modal-note-value').value = currentNote;
    document.getElementById('modal-star-hint').textContent = currentNote + ' / 5';
    document.querySelectorAll('.mstar').forEach(function(s){ s.style.color = parseInt(s.dataset.val) <= currentNote ? '#f39c12' : '#ddd'; });
  });
});

document.getElementById('modal-avis-form').addEventListener('submit', function(e) {
  e.preventDefault();
  var note        = document.getElementById('modal-note-value').value;
  var commentaire = document.getElementById('modal-avis-comment').value.trim();
  var id_produit  = document.getElementById('modal-avis-produit-id').value;
  if (!note || !commentaire || !id_produit) return;
  var formData = new FormData();
  formData.append('note', note);
  formData.append('commentaire', commentaire);
  formData.append('id_produit', id_produit);
  fetch('../front/submit_avis.php', { method: 'POST', body: formData })
    .then(function(r){ return r.json(); })
    .then(function(data) {
      if (data.success) {
        document.getElementById('modal-avis-success').style.display = 'inline';
        document.getElementById('modal-avis-comment').value = '';
        resetModalStars();
        loadModalAvis(id_produit);
        setTimeout(function(){ document.getElementById('modal-avis-success').style.display = 'none'; }, 3000);
      }
    });
});
</script>

<?php else: ?>
<!-- ── MODE: LISTE DES CATÉGORIES ── -->
<section class="section light-background py-5">
  <div class="container section-title">
    <h2>Boutique</h2>
    <p><span>Nos</span> <span class="description-title">Catégories</span></p>
  </div>
  <div class="container">

    <!-- Search & Sort -->
    <div class="filters-section" style="background:white;padding:16px 20px;border-radius:15px;margin-bottom:24px;box-shadow:0 2px 10px rgba(0,0,0,0.05);">
      <div class="row align-items-center gy-2">
        <div class="col-md-8">
          <input type="text" id="searchCat" class="form-control search-input"
            placeholder="Rechercher une catégorie..." oninput="filtrerCategories()">
        </div>
        <div class="col-md-4">
          <select id="sortCat" class="sort-select w-100" onchange="filtrerCategories()">
            <option value="">— Trier —</option>
            <option value="nom-asc">Nom A → Z</option>
            <option value="nom-desc">Nom Z → A</option>
          </select>
        </div>
      </div>
    </div>

    <div class="row g-4" id="categoriesGrid">
      <?php foreach ($categories as $categorie): ?>
        <?php
        $imgSrc = '';
        if (!empty($categorie['image'])) {
          $imgSrc = str_starts_with($categorie['image'], 'http')
            ? $categorie['image']
            : UPLOAD_URL . $categorie['image'];
        }
        ?>
        <div class="col-lg-4 col-md-6 cat-item"
             data-nom="<?= htmlspecialchars(strtolower($categorie['nom']), ENT_QUOTES) ?>">
          <a href="categories.php?id=<?= (int)$categorie['id_categorie'] ?>" class="cat-card">
            <?php if ($imgSrc): ?>
              <img src="<?= htmlspecialchars($imgSrc) ?>" class="cat-img" alt="<?= htmlspecialchars($categorie['nom']) ?>">
            <?php else: ?>
              <div class="cat-img-placeholder"><i class="bi bi-tag"></i></div>
            <?php endif; ?>
            <div class="cat-info">
              <div class="cat-title"><?= htmlspecialchars($categorie['nom']) ?></div>
              <div class="cat-desc"><?= htmlspecialchars($categorie['description'] ?? '') ?></div>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
      <div id="no-result-cat" style="display:none;" class="col-12 text-center py-5 text-muted">
        <i class="bi bi-search" style="font-size:2rem;display:block;margin-bottom:10px;"></i>
        Aucune catégorie trouvée.
      </div>
    </div>
  </div>
</section>

<script>
function filtrerCategories() {
  var q   = document.getElementById('searchCat').value.toLowerCase().trim();
  var tri = document.getElementById('sortCat').value;
  var items = Array.from(document.querySelectorAll('.cat-item'));
  var visible = 0;
  items.forEach(function(el) {
    var match = !q || el.dataset.nom.includes(q);
    el.style.display = match ? '' : 'none';
    if (match) visible++;
  });
  if (tri) {
    var dir = tri.split('-')[1];
    var vis = items.filter(function(el){ return el.style.display !== 'none'; });
    vis.sort(function(a, b) {
      return dir === 'asc'
        ? (a.dataset.nom||'').localeCompare(b.dataset.nom||'')
        : (b.dataset.nom||'').localeCompare(a.dataset.nom||'');
    });
    var grid = document.getElementById('categoriesGrid');
    vis.forEach(function(el){ grid.appendChild(el); });
  }
  document.getElementById('no-result-cat').style.display = visible === 0 ? '' : 'none';
}
function updateBadge(){var t=JSON.parse(localStorage.getItem('panier')||'[]').reduce(function(s,p){return s+p.quantite;},0);var b=document.getElementById('panier-badge');if(b){b.textContent=t;b.style.display=t>0?'inline-block':'none';}}
function ouvrirPanier(){window.location.href='produits.php';}
updateBadge();
</script>
<?php endif; ?>

<?php include("footer.php"); ?>
