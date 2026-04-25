<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/ProduitController.php';
require_once __DIR__ . '/../../controller/CategorieController.php';

$idCategorie = (int) ($_GET['id'] ?? 0);
$produitController = new ProduitController();
$categorieController = new CategorieController();
$categorie = $categorieController->getCategorieById($idCategorie);
$produits = $produitController->listProduitsByCategorie($idCategorie);

if (!$categorie) {
    header('Location: categories.php');
    exit;
}

include("header.php");
?>
<section class="section">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?= htmlspecialchars($categorie['nom']) ?></h2>
        <a href="categories.php" class="btn btn-outline-secondary">Retour catégories</a>
    </div>

    <!-- Toast notification -->
    <div id="panier-toast" style="display:none;position:fixed;bottom:24px;right:24px;z-index:9999;" class="toast align-items-center text-bg-success border-0 show" role="alert">
        <div class="d-flex">
            <div class="toast-body"><i class="bi bi-cart-check me-2"></i><span id="panier-toast-msg">Produit ajouté au panier !</span></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="document.getElementById('panier-toast').style.display='none'"></button>
        </div>
    </div>

    <div class="row g-3">
        <?php foreach ($produits as $produit): ?>
            <?php $imgSrc = !empty($produit['image']) ? (str_starts_with($produit['image'], 'http') ? $produit['image'] : UPLOAD_URL . $produit['image']) : ''; ?>
            <div class="col-md-4">
                <div class="card h-100 d-flex flex-column shadow-sm">
                    <?php if ($imgSrc): ?>
                        <img src="<?= htmlspecialchars($imgSrc) ?>" class="card-img-top" alt="<?= htmlspecialchars($produit['nom']) ?>" style="height:200px;object-fit:cover;">
                    <?php else: ?>
                        <div style="height:200px;background:#f5f5f5;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-image text-muted" style="font-size:3rem;"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($produit['nom']) ?></h5>
                        <p class="card-text text-muted small"><?= htmlspecialchars($produit['description'] ?? '') ?></p>
                        <p class="mb-1"><strong>Prix:</strong> <strong class="text-danger"><?= number_format((float) $produit['prix'], 2, ',', ' ') ?> DT</strong></p>
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

updateBadge();
</script>

<?php include("footer.php"); ?>
