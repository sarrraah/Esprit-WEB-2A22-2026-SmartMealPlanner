<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/CategorieController.php';

$categorieController = new CategorieController();
$searchQuery = trim($_GET['search'] ?? '');
$allCategories = $categorieController->getAllCategories();

// Filtre côté PHP
if ($searchQuery !== '') {
    $categories = array_filter($allCategories, function($c) use ($searchQuery) {
        return stripos($c['nom'], $searchQuery) !== false
            || stripos($c['description'] ?? '', $searchQuery) !== false;
    });
} else {
    $categories = $allCategories;
}

include("header.php");
?>
<section class="section">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Gestion des Catégories</h2>
        <a href="ajouterCategorie.php" class="btn btn-danger">Ajouter une catégorie</a>
    </div>

    <!-- Barre de recherche -->
    <div class="row g-2 mb-3">
        <div class="col-md-9">
            <input type="text" id="search-categorie" class="form-control" placeholder="Rechercher une catégorie...">
        </div>
        <div class="col-md-3">
            <button class="btn btn-outline-secondary w-100" onclick="annulerRecherche()">
                <i class="bi bi-x-circle me-1"></i>Annuler
            </button>
        </div>
    </div>

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light"><tr><th>ID</th><th>Image</th><th>Nom</th><th>Description</th><th>Actions</th></tr></thead>
        <tbody id="tbody-categories">
        <?php foreach ($categories as $categorie): ?>
            <?php
            $imgSrc = '';
            if (!empty($categorie['image'])) {
                $imgSrc = str_starts_with($categorie['image'], 'http')
                    ? $categorie['image']
                    : UPLOAD_URL . $categorie['image'];
            }
            ?>
            <tr
                data-nom="<?= htmlspecialchars(strtolower($categorie['nom']), ENT_QUOTES) ?>"
                data-desc="<?= htmlspecialchars(strtolower($categorie['description'] ?? ''), ENT_QUOTES) ?>">
                <td><?= (int) $categorie['id_categorie'] ?></td>
                <td>
                    <?php if ($imgSrc): ?>
                        <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($categorie['nom']) ?>" style="width:60px;height:60px;object-fit:cover;border-radius:6px;">
                    <?php else: ?>
                        <div style="width:60px;height:60px;background:#f0f0f0;border-radius:6px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-image text-muted"></i>
                        </div>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($categorie['nom']) ?></td>
                <td><?= htmlspecialchars($categorie['description'] ?? '') ?></td>
                <td>
                    <a href="modifierCategorie.php?id=<?= (int) $categorie['id_categorie'] ?>" class="btn btn-sm btn-outline-primary">Modifier</a>
                    <a href="supprimerCategorie.php?id=<?= (int) $categorie['id_categorie'] ?>" class="btn btn-sm btn-outline-danger">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <tr id="no-result-cat" style="display:none;">
            <td colspan="5" class="text-center text-muted py-3">Aucune catégorie trouvée.</td>
        </tr>
        </tbody>
    </table>

    <script>
    function filtrerCategories() {
        var q = document.getElementById('search-categorie').value.toLowerCase().trim();
        var rows = document.querySelectorAll('#tbody-categories tr:not(#no-result-cat)');
        var visible = 0;
        rows.forEach(function(row) {
            var nom  = row.dataset.nom  || '';
            var desc = row.dataset.desc || '';
            var match = !q || nom.includes(q) || desc.includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        document.getElementById('no-result-cat').style.display = visible === 0 ? '' : 'none';
    }

    function annulerRecherche() {
        document.getElementById('search-categorie').value = '';
        filtrerCategories();
    }

    document.getElementById('search-categorie').addEventListener('input', filtrerCategories);
    </script>
</div>
</section>
<?php include("footer.php"); ?>
