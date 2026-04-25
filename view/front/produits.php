<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/ProduitController.php';
require_once __DIR__ . '/../../controller/CategorieController.php';

$produitController = new ProduitController();
$categorieController = new CategorieController();
$produits = $produitController->listProduits();
$categories = $categorieController->getAllCategories();

include("header.php");
?>

<section class="section">
<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Nos Produits</h2>
        <a href="categories.php" class="btn btn-outline-danger">Voir les catégories</a>
    </div>

    <!-- Barre de recherche -->
    <div class="mb-3">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="recherche-produit" class="form-control border-start-0" placeholder="Rechercher un produit...">
        </div>
    </div>

    <!-- Tri -->
    <div class="mb-4 d-flex align-items-center gap-2 flex-wrap">
        <label class="text-muted small mb-0 me-1"><i class="bi bi-funnel me-1"></i>Trier :</label>
        <select id="select-tri" class="form-select form-select-sm w-auto" onchange="appliquerTri()">
            <option value="">— Défaut —</option>
            <option value="nom-asc">Nom A → Z</option>
            <option value="nom-desc">Nom Z → A</option>
            <option value="prix-asc">Prix croissant ↑</option>
            <option value="prix-desc">Prix décroissant ↓</option>
            <option value="stock-asc">Stock croissant ↑</option>
            <option value="stock-desc">Stock décroissant ↓</option>
        </select>
        <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('select-tri').value='';appliquerTri()">
            <i class="bi bi-x me-1"></i>Réinitialiser
        </button>
    </div>

    <!-- Filtre par catégorie -->
    <div class="mb-4 d-flex flex-wrap gap-2">
        <a class="btn btn-sm btn-danger" href="produits.php">Tous</a>
        <?php foreach ($categories as $categorie): ?>
            <a class="btn btn-sm btn-outline-secondary" href="produitsParCategorie.php?id=<?= (int) $categorie['id_categorie'] ?>">
                <?= htmlspecialchars($categorie['nom']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Toast notification -->
    <div id="panier-toast" style="display:none;position:fixed;bottom:24px;right:24px;z-index:9999;" class="toast align-items-center text-bg-success border-0 show" role="alert">
        <div class="d-flex">
            <div class="toast-body"><i class="bi bi-cart-check me-2"></i><span id="panier-toast-msg">Produit ajouté au panier !</span></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="document.getElementById('panier-toast').style.display='none'"></button>
        </div>
    </div>

    <!-- Grille produits -->
    <div class="row g-4">
        <?php foreach ($produits as $produit): ?>
            <?php $imgSrc = !empty($produit['image']) ? (str_starts_with($produit['image'], 'http') ? $produit['image'] : UPLOAD_URL . $produit['image']) : ''; ?>
            <div class="col-md-4 col-sm-6"
                data-prix="<?= (float) $produit['prix'] ?>"
                data-nom="<?= htmlspecialchars(strtolower($produit['nom']), ENT_QUOTES) ?>"
                data-stock="<?= (int) $produit['quantiteStock'] ?>">
                <div class="card h-100 d-flex flex-column shadow-sm">
                    <?php if ($imgSrc): ?>
                        <img src="<?= htmlspecialchars($imgSrc) ?>" class="card-img-top" alt="<?= htmlspecialchars($produit['nom']) ?>" style="height:220px;object-fit:cover;">
                    <?php else: ?>
                        <div style="height:220px;background:#f5f5f5;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-image text-muted" style="font-size:3rem;"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($produit['nom']) ?></h5>
                        <p class="card-text text-muted small"><?= htmlspecialchars($produit['description'] ?? '') ?></p>
                        <p class="mb-1 small"><strong>Catégorie:</strong> <?= htmlspecialchars($produit['categorie_nom'] ?? 'Sans catégorie') ?></p>
                        <p class="mb-1"><strong class="text-danger"><?= number_format((float) $produit['prix'], 2, ',', ' ') ?> DT</strong></p>
                        <p class="mb-3 small text-muted"><strong>Stock:</strong> <?= (int) $produit['quantiteStock'] ?></p>
                        <div class="mt-auto">
                            <?php if ((int) $produit['quantiteStock'] > 0): ?>
                                <button class="btn btn-danger w-100 btn-ajouter-panier"
                                    data-id="<?= (int) $produit['id'] ?>"
                                    data-nom="<?= htmlspecialchars($produit['nom'], ENT_QUOTES) ?>"
                                    data-prix="<?= (float) $produit['prix'] ?>"
                                    data-image="<?= htmlspecialchars($imgSrc, ENT_QUOTES) ?>">
                                    <i class="bi bi-cart-plus me-2"></i>Ajouter au panier
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="bi bi-x-circle me-2"></i>Rupture de stock
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>
</section>

<!-- Modal Panier -->
<div class="modal fade" id="modalPanier" tabindex="-1" aria-labelledby="modalPanierLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalPanierLabel">🛒 Mon Panier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="panier-contenu">
        <p class="text-muted text-center">Votre panier est vide.</p>
      </div>
      <div class="modal-footer d-flex justify-content-between align-items-center">
        <strong>Total : <span id="panier-total">0,00</span> DT</strong>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-danger btn-sm" onclick="viderPanier()">Vider le panier</button>
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Continuer mes achats</button>
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
    var total = getPanier().reduce(function(s, p) { return s + p.quantite; }, 0);
    var badge = document.getElementById('panier-badge');
    if (badge) { badge.textContent = total; badge.style.display = total > 0 ? 'inline-block' : 'none'; }
}

function ouvrirPanier() {
    var panier = getPanier();
    var contenu = document.getElementById('panier-contenu');
    var totalEl = document.getElementById('panier-total');
    if (panier.length === 0) {
        contenu.innerHTML = '<p class="text-muted text-center py-3">Votre panier est vide.</p>';
        totalEl.textContent = '0,00';
    } else {
        var html = '<table class="table align-middle"><thead><tr><th>Produit</th><th>Prix</th><th>Quantité</th><th>Sous-total</th><th></th></tr></thead><tbody>';
        var total = 0;
        panier.forEach(function(p) {
            var sous = p.prix * p.quantite; total += sous;
            html += '<tr><td><div class="d-flex align-items-center gap-2">';
            if (p.image) html += '<img src="' + p.image + '" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">';
            html += '<strong>' + p.nom + '</strong></div></td>';
            html += '<td>' + p.prix.toFixed(2).replace('.', ',') + ' DT</td>';
            html += '<td><div class="d-flex align-items-center gap-2"><button class="btn btn-sm btn-outline-secondary" onclick="changerQte(\'' + p.id + '\',-1)">−</button><span>' + p.quantite + '</span><button class="btn btn-sm btn-outline-secondary" onclick="changerQte(\'' + p.id + '\',1)">+</button></div></td>';
            html += '<td>' + sous.toFixed(2).replace('.', ',') + ' DT</td>';
            html += '<td><button class="btn btn-sm btn-outline-danger" onclick="supprimerArticle(\'' + p.id + '\')"><i class="bi bi-trash"></i></button></td></tr>';
        });
        html += '</tbody></table>';
        contenu.innerHTML = html;
        totalEl.textContent = total.toFixed(2).replace('.', ',');
    }
    new bootstrap.Modal(document.getElementById('modalPanier')).show();
}

function changerQte(id, delta) {
    var panier = getPanier();
    var item = panier.find(function(p) { return p.id === id; });
    if (item) { item.quantite += delta; if (item.quantite <= 0) panier = panier.filter(function(p) { return p.id !== id; }); }
    savePanier(panier); ouvrirPanier();
}
function supprimerArticle(id) { savePanier(getPanier().filter(function(p) { return p.id !== id; })); ouvrirPanier(); }
function viderPanier() { savePanier([]); ouvrirPanier(); }

function acheter() {
    var panier = getPanier();
    if (panier.length === 0) return;
    // Confirmation commande
    var total = panier.reduce(function(s, p) { return s + p.prix * p.quantite; }, 0);
    var msg = '✅ Commande confirmée !\n\n';
    panier.forEach(function(p) { msg += '• ' + p.nom + ' x' + p.quantite + ' — ' + (p.prix * p.quantite).toFixed(2) + ' DT\n'; });
    msg += '\nTotal : ' + total.toFixed(2) + ' DT\n\nMerci pour votre achat !';
    bootstrap.Modal.getInstance(document.getElementById('modalPanier')).hide();
    savePanier([]);
    setTimeout(function() { alert(msg); }, 300);
}

document.querySelectorAll('.btn-ajouter-panier').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var panier = getPanier();
        var id = this.dataset.id, nom = this.dataset.nom, prix = parseFloat(this.dataset.prix), image = this.dataset.image;
        var existant = panier.find(function(p) { return p.id === id; });
        if (existant) { existant.quantite += 1; } else { panier.push({ id, nom, prix, image, quantite: 1 }); }
        savePanier(panier);
        var toast = document.getElementById('panier-toast');
        document.getElementById('panier-toast-msg').textContent = '"' + nom + '" ajouté au panier !';
        toast.style.display = 'flex';
        setTimeout(function() { toast.style.display = 'none'; }, 3000);
    });
});

// Recherche en temps réel
document.getElementById('recherche-produit').addEventListener('input', function() {
    var q = this.value.toLowerCase().trim();
    document.querySelectorAll('.col-md-4.col-sm-6').forEach(function(card) {
        var nom = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
        var desc = card.querySelector('.card-text')?.textContent.toLowerCase() || '';
        card.style.display = (!q || nom.includes(q) || desc.includes(q)) ? '' : 'none';
    });
});

// Tri par prix
var triActuel = '';
function trierPrix(direction) {
    triActuel = direction;
    var grid = document.querySelector('.row.g-4');
    var cards = Array.from(grid.querySelectorAll('.col-md-4.col-sm-6'));
    if (direction === '') {
        // Remettre l'ordre original (index PHP)
        cards.sort(function(a, b) { return (parseInt(a.dataset.index)||0) - (parseInt(b.dataset.index)||0); });
    } else {
        cards.sort(function(a, b) {
            var pa = parseFloat(a.dataset.prix) || 0;
            var pb = parseFloat(b.dataset.prix) || 0;
            return direction === 'asc' ? pa - pb : pb - pa;
        });
    }
    cards.forEach(function(c) { grid.appendChild(c); });
    // Mettre à jour l'apparence des boutons
    document.getElementById('btn-tri-asc').classList.toggle('btn-danger', direction === 'asc');
    document.getElementById('btn-tri-asc').classList.toggle('btn-outline-danger', direction !== 'asc');
    document.getElementById('btn-tri-desc').classList.toggle('btn-danger', direction === 'desc');
    document.getElementById('btn-tri-desc').classList.toggle('btn-outline-danger', direction !== 'desc');
}

// Stocker l'index original
document.querySelectorAll('.col-md-4.col-sm-6').forEach(function(c, i) { c.dataset.index = i; });

// Recherche en temps réel
document.getElementById('recherche-produit').addEventListener('input', appliquerRecherche);

function appliquerRecherche() {
    var q = document.getElementById('recherche-produit').value.toLowerCase().trim();
    document.querySelectorAll('.col-md-4.col-sm-6').forEach(function(card) {
        var nom  = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
        var desc = card.querySelector('.card-text')?.textContent.toLowerCase() || '';
        card.style.display = (!q || nom.includes(q) || desc.includes(q)) ? '' : 'none';
    });
}

function appliquerTri() {
    var val   = document.getElementById('select-tri').value;
    var grid  = document.querySelector('.row.g-4');
    var cards = Array.from(grid.querySelectorAll('.col-md-4.col-sm-6'));

    cards.sort(function(a, b) {
        if (!val) return (parseInt(a.dataset.index)||0) - (parseInt(b.dataset.index)||0);
        var field = val.split('-')[0];
        var dir   = val.split('-')[1];
        var va, vb;
        if (field === 'prix')  { va = parseFloat(a.dataset.prix)||0;  vb = parseFloat(b.dataset.prix)||0; }
        if (field === 'stock') { va = parseInt(a.dataset.stock)||0;   vb = parseInt(b.dataset.stock)||0; }
        if (field === 'nom')   { va = a.dataset.nom || '';            vb = b.dataset.nom || ''; return dir === 'asc' ? va.localeCompare(vb) : vb.localeCompare(va); }
        return dir === 'asc' ? va - vb : vb - va;
    });
    cards.forEach(function(c) { grid.appendChild(c); });
}

updateBadge();
</script>

<?php include("footer.php"); ?>
