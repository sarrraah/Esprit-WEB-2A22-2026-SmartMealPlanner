<?php
require_once __DIR__ . '/../../controller/ProduitController.php';

$statutFilter = $_GET['statut'] ?? 'Tous';
$searchQuery = trim($_GET['search'] ?? '');

$produitController = new ProduitController();
$produits = $produitController->searchProduits($statutFilter, $searchQuery);
foreach ($produits as &$p) {
    $p['statut'] = determinerStatut($p['quantiteStock'], $p['dateExpiration']);
}
unset($p);

include("header.php");
?>
<section class="section">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Gestion des Produits</h2>
        <div class="d-flex gap-2">
            <a href="ajouterProduit.php" class="btn btn-success"><i class="fas fa-plus me-1"></i>Produit</a>
            <a href="afficherCategorie.php" class="btn btn-outline-dark"><i class="fas fa-tags me-1"></i>Catégories</a>
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" id="search-produit" class="form-control" placeholder="Rechercher un produit...">
        </div>
        <div class="col-md-3">
            <select id="filter-statut" class="form-select">
                <option value="">Tous</option>
                <option value="Disponible">Disponible</option>
                <option value="Rupture">Rupture</option>
                <option value="Épuisé">Épuisé</option>
            </select>
        </div>
        <div class="col-md-3">
            <select id="filter-tri" class="form-select">
                <option value="">— Trier —</option>
                <option value="nom-asc">Nom A → Z</option>
                <option value="nom-desc">Nom Z → A</option>
                <option value="prix-asc">Prix croissant ↑</option>
                <option value="prix-desc">Prix décroissant ↓</option>
                <option value="stock-asc">Stock croissant ↑</option>
                <option value="stock-desc">Stock décroissant ↓</option>
                <option value="expiration-asc">Expiration proche ↑</option>
                <option value="expiration-desc">Expiration lointaine ↓</option>
            </select>
        </div>
        <div class="col-md-2">
            <button id="btn-annuler" class="btn btn-outline-secondary w-100" onclick="annulerFiltres()">
                <i class="bi bi-x-circle me-1"></i>Annuler
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle" id="table-produits">
            <thead class="table-light">
            <tr><th>ID</th><th>Nom</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th>Expiration</th><th>Statut</th><th>Actions</th></tr>
            </thead>
            <tbody id="tbody-produits">
            <?php foreach ($produits as $p): ?>
                <tr
                    data-nom="<?= htmlspecialchars(strtolower($p['nom']), ENT_QUOTES) ?>"
                    data-categorie="<?= htmlspecialchars(strtolower($p['categorie_nom'] ?? ''), ENT_QUOTES) ?>"
                    data-statut="<?= htmlspecialchars($p['statut'], ENT_QUOTES) ?>"
                    data-prix="<?= (float) $p['prix'] ?>"
                    data-stock="<?= (int) $p['quantiteStock'] ?>"
                    data-expiration="<?= htmlspecialchars($p['dateExpiration'] ?? '', ENT_QUOTES) ?>">
                    <td><?= (int) $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['nom']) ?></td>
                    <td><?= htmlspecialchars($p['categorie_nom'] ?? 'Sans catégorie') ?></td>
                    <td><?= number_format((float) $p['prix'], 2, ',', ' ') ?> DT</td>
                    <td><?= (int) $p['quantiteStock'] ?></td>
                    <td><?= htmlspecialchars($p['dateExpiration']) ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($p['statut']) ?></span></td>
                    <td>
                        <a href="modifierProduit.php?id=<?= (int) $p['id'] ?>" class="btn btn-sm btn-outline-primary">Modifier</a>
                        <a href="supprimerProduit.php?id=<?= (int) $p['id'] ?>" class="btn btn-sm btn-outline-danger">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr id="no-result" style="display:none;">
                <td colspan="8" class="text-center text-muted py-3">Aucun produit trouvé.</td>
            </tr>
            </tbody>
        </table>
    </div>

    <script>
    // Stocker l'ordre original
    var tbody = document.getElementById('tbody-produits');
    Array.from(tbody.querySelectorAll('tr:not(#no-result)')).forEach(function(r, i) { r.dataset.index = i; });

    function filtrerEtTrier() {
        var q      = document.getElementById('search-produit').value.toLowerCase().trim();
        var statut = document.getElementById('filter-statut').value;
        var tri    = document.getElementById('filter-tri').value;
        var rows   = Array.from(tbody.querySelectorAll('tr:not(#no-result)'));
        var visible = 0;

        // Filtrage
        rows.forEach(function(row) {
            var nom = row.dataset.nom || '';
            var cat = row.dataset.categorie || '';
            var st  = row.dataset.statut || '';
            var matchSearch = !q || nom.includes(q) || cat.includes(q);
            var matchStatut = !statut || st === statut;
            row.style.display = (matchSearch && matchStatut) ? '' : 'none';
            if (matchSearch && matchStatut) visible++;
        });

        // Tri
        if (tri) {
            var field = tri.split('-')[0];
            var dir   = tri.split('-')[1];
            var visibleRows = rows.filter(function(r) { return r.style.display !== 'none'; });
            visibleRows.sort(function(a, b) {
                var va, vb;
                if (field === 'nom')        { va = a.dataset.nom||'';        vb = b.dataset.nom||'';        return dir==='asc' ? va.localeCompare(vb) : vb.localeCompare(va); }
                if (field === 'prix')       { va = parseFloat(a.dataset.prix)||0;       vb = parseFloat(b.dataset.prix)||0; }
                if (field === 'stock')      { va = parseInt(a.dataset.stock)||0;        vb = parseInt(b.dataset.stock)||0; }
                if (field === 'expiration') { va = a.dataset.expiration||''; vb = b.dataset.expiration||''; return dir==='asc' ? va.localeCompare(vb) : vb.localeCompare(va); }
                return dir === 'asc' ? va - vb : vb - va;
            });
            visibleRows.forEach(function(r) { tbody.insertBefore(r, document.getElementById('no-result')); });
        } else {
            // Remettre l'ordre original
            var allRows = rows.slice().sort(function(a,b){ return (parseInt(a.dataset.index)||0)-(parseInt(b.dataset.index)||0); });
            allRows.forEach(function(r) { tbody.insertBefore(r, document.getElementById('no-result')); });
        }

        document.getElementById('no-result').style.display = visible === 0 ? '' : 'none';
    }

    function annulerFiltres() {
        document.getElementById('search-produit').value = '';
        document.getElementById('filter-statut').value = '';
        document.getElementById('filter-tri').value = '';
        filtrerEtTrier();
    }

    document.getElementById('search-produit').addEventListener('input', filtrerEtTrier);
    document.getElementById('filter-statut').addEventListener('change', filtrerEtTrier);
    document.getElementById('filter-tri').addEventListener('change', filtrerEtTrier);
    </script>
</div>
</section>
<?php include("footer.php"); ?>