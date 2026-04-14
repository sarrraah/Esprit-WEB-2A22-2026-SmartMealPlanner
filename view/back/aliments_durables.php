<?php
$pageTitle = 'Aliments Durables - SmartMeal Admin';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/sidebar.php';
?>
<div class="admin-main">
    <div class="admin-topbar"><h5><i class="bi bi-leaf me-2" style="color:#198754"></i>Aliments Durables</h5></div>
    <div class="admin-content">
        <div class="row justify-content-center"><div class="col-lg-8">
            <div class="admin-card card mb-4">
                <div class="card-header"><i class="bi bi-plus-circle me-2" style="color:var(--accent)"></i>Ajouter un aliment durable</div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label fw-medium">Nom</label><input class="form-control" placeholder="Ex: Lentilles"></div>
                        <div class="col-md-4"><label class="form-label fw-medium">Origine</label><input class="form-control" placeholder="Ex: Tunisie"></div>
                        <div class="col-md-4"><label class="form-label fw-medium">Score écologique</label><input type="number" min="0" max="10" step="0.1" class="form-control" placeholder="Ex: 8.5"></div>
                    </div>
                    <button class="btn btn-yummy mt-3"><i class="bi bi-plus-circle me-1"></i>Ajouter</button>
                </div>
            </div>
            <div class="admin-card card"><div class="card-body p-5 text-center text-muted">
                <i class="bi bi-leaf fs-1 d-block mb-2" style="color:#198754;opacity:.4;"></i>Aucun aliment durable enregistré.
            </div></div>
        </div></div>
    </div>
</div>
<?php require_once __DIR__ . '/partials/foot.php'; ?>
