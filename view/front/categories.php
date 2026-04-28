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
      <a href="categories.php" class="btn-back"><i class="bi bi-arrow-left"></i> Retour aux catégories</a>
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

    <div class="row g-4">
      <?php foreach ($produits as $produit): ?>
        <?php
        $img = $produit['image'] ?? '';
        if (empty($img))                         $imgSrc = '';
        elseif (str_starts_with($img, 'http'))   $imgSrc = $img;
        elseif (str_starts_with($img, 'meals/')) $imgSrc = '../../view/assets/img/' . $img;
        else                                      $imgSrc = UPLOAD_URL . $img;
        $statut   = determinerStatut($produit['quantiteStock'], $produit['dateExpiration']);
        $badgeCls = match($statut) { 'Disponible'=>'badge-dispo','Rupture'=>'badge-rupture',default=>'badge-epuise' };
        ?>
        <div class="col-lg-4 col-md-6">
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
                  <i class="bi bi-cart-plus me-2"></i>Ajouter au panier
                </button>
              <?php else: ?>
                <button class="btn-cart" disabled onclick="event.stopPropagation();"><i class="bi bi-x-circle me-2"></i>Rupture de stock</button>
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
      <div class="modal-body" id="panier-contenu"><p class="text-muted text-center">Votre panier est vide.</p></div>
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
function getPanier(){return JSON.parse(localStorage.getItem('panier')||'[]');}
function savePanier(p){localStorage.setItem('panier',JSON.stringify(p));updateBadge();}
function updateBadge(){var t=getPanier().reduce(function(s,p){return s+p.quantite;},0);var b=document.getElementById('panier-badge');if(b){b.textContent=t;b.style.display=t>0?'inline-block':'none';}}
function ouvrirPanier(){var panier=getPanier(),c=document.getElementById('panier-contenu'),tEl=document.getElementById('panier-total');if(!panier.length){c.innerHTML='<p class="text-muted text-center py-3">Votre panier est vide.</p>';tEl.textContent='0,00';}else{var html='<table class="table align-middle"><thead><tr><th>Produit</th><th>Prix</th><th>Qté</th><th>Sous-total</th><th></th></tr></thead><tbody>';var total=0;panier.forEach(function(p){var sous=p.prix*p.quantite;total+=sous;html+='<tr><td><div class="d-flex align-items-center gap-2">';if(p.image)html+='<img src="'+p.image+'" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">';html+='<strong>'+p.nom+'</strong></div></td><td>'+p.prix.toFixed(2).replace('.',',')+' DT</td><td><div class="d-flex align-items-center gap-2"><button class="btn btn-sm btn-outline-secondary" onclick="changerQte(\''+p.id+'\',-1)">−</button><span>'+p.quantite+'</span><button class="btn btn-sm btn-outline-secondary" onclick="changerQte(\''+p.id+'\',1)">+</button></div></td><td>'+sous.toFixed(2).replace('.',',')+' DT</td><td><button class="btn btn-sm btn-outline-danger" onclick="supprimerArticle(\''+p.id+'\')"><i class="bi bi-trash"></i></button></td></tr>';});html+='</tbody></table>';c.innerHTML=html;tEl.textContent=total.toFixed(2).replace('.',',');}new bootstrap.Modal(document.getElementById('modalPanier')).show();}
function changerQte(id,d){var p=getPanier(),i=p.find(function(x){return x.id===id;});if(i){i.quantite+=d;if(i.quantite<=0)p=p.filter(function(x){return x.id!==id;});}savePanier(p);ouvrirPanier();}
function supprimerArticle(id){savePanier(getPanier().filter(function(p){return p.id!==id;}));ouvrirPanier();}
function viderPanier(){savePanier([]);ouvrirPanier();}
function acheter(){var p=getPanier();if(!p.length)return;var t=p.reduce(function(s,x){return s+x.prix*x.quantite;},0);var msg='✅ Commande confirmée !\n\n';p.forEach(function(x){msg+='• '+x.nom+' x'+x.quantite+' — '+(x.prix*x.quantite).toFixed(2)+' DT\n';});msg+='\nTotal : '+t.toFixed(2)+' DT\n\nMerci !';bootstrap.Modal.getInstance(document.getElementById('modalPanier')).hide();savePanier([]);setTimeout(function(){alert(msg);},300);}
document.querySelectorAll('.btn-ajouter-panier').forEach(function(btn){btn.addEventListener('click',function(){var p=getPanier();var id=this.dataset.id,nom=this.dataset.nom,prix=parseFloat(this.dataset.prix),image=this.dataset.image;var ex=p.find(function(x){return x.id===id;});if(ex){ex.quantite+=1;}else{p.push({id,nom,prix,image,quantite:1});}savePanier(p);document.getElementById('panier-toast-msg').textContent='"'+nom+'" ajouté !';var t=document.getElementById('panier-toast');t.style.display='flex';setTimeout(function(){t.style.display='none';},3000);});});
updateBadge();
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
            <p id="modal-desc" style="font-size:0.85rem;color:#888;margin-bottom:14px;"></p>

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

            <div class="mt-auto d-flex gap-2">
              <button id="modal-btn-cart" class="btn-cart" style="flex:1;">
                <i class="bi bi-cart-plus me-2"></i>Ajouter au panier
              </button>
              <button class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius:25px;padding:9px 20px;font-size:13px;">Fermer</button>
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
  var nom=data.dataset.nom, desc=data.dataset.desc, prix=parseFloat(data.dataset.prix);
  var exp=data.dataset.expiration, cat=data.dataset.categorie;
  var statut=data.dataset.statut, image=data.dataset.image;
  var cal=data.dataset.calories, prot=data.dataset.proteines;
  var carb=data.dataset.glucides, fat=data.dataset.lipides;

  document.getElementById('modal-nom').textContent  = nom;
  document.getElementById('modal-desc').textContent = desc;
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
  btn.innerHTML = '<i class="bi bi-cart-plus me-2"></i>Ajouter au panier';
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
    btn.innerHTML='<i class="bi bi-x-circle me-2"></i>Rupture de stock';
  }
  new bootstrap.Modal(document.getElementById('modalProduitDetail')).show();
}
</script>

<?php else: ?>
<!-- ── MODE: LISTE DES CATÉGORIES ── -->
<section class="section light-background py-5">
  <div class="container section-title">
    <h2>Boutique</h2>
    <p><span>Nos</span> <span class="description-title">Catégories</span></p>
  </div>
  <div class="container">
    <div class="row g-4">
      <?php foreach ($categories as $categorie): ?>
        <?php
        $imgSrc = '';
        if (!empty($categorie['image'])) {
          $imgSrc = str_starts_with($categorie['image'], 'http')
            ? $categorie['image']
            : UPLOAD_URL . $categorie['image'];
        }
        ?>
        <div class="col-lg-4 col-md-6">
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
    </div>
  </div>
</section>

<script>
function updateBadge(){var t=JSON.parse(localStorage.getItem('panier')||'[]').reduce(function(s,p){return s+p.quantite;},0);var b=document.getElementById('panier-badge');if(b){b.textContent=t;b.style.display=t>0?'inline-block':'none';}}
function ouvrirPanier(){window.location.href='produits.php';}
updateBadge();
</script>
<?php endif; ?>

<?php include("footer.php"); ?>
