<?php
$pageTitle = 'Contenu Nutritionnel - SmartMeal Admin';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/sidebar.php';
?>
<div class="admin-main">
    <div class="admin-topbar"><h5><i class="bi bi-heart-pulse me-2" style="color:var(--accent)"></i>Contenu Nutritionnel</h5></div>
    <div class="admin-content">
        <div class="row justify-content-center"><div class="col-lg-8">
            <div class="admin-card card">
                <div class="card-header"><i class="bi bi-sliders me-2" style="color:var(--accent)"></i>Paramètres nutritionnels</div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label fw-medium">Protéines (g)</label><input type="number" class="form-control" value="20" min="0"></div>
                        <div class="col-md-3"><label class="form-label fw-medium">Glucides (g)</label><input type="number" class="form-control" value="50" min="0"></div>
                        <div class="col-md-3"><label class="form-label fw-medium">Lipides (g)</label><input type="number" class="form-control" value="15" min="0"></div>
                        <div class="col-md-3"><label class="form-label fw-medium">Fibres (g)</label><input type="number" class="form-control" value="8" min="0"></div>
                    </div>
                    <button class="btn btn-yummy mt-3"><i class="bi bi-save me-1"></i>Enregistrer</button>
                </div>
            </div>
        </div></div>
    </div>
</div>
<?php require_once __DIR__ . '/partials/foot.php'; ?>
