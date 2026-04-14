<?php
$pageTitle = 'Utilisateurs - SmartMeal Admin';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/sidebar.php';
?>
<div class="admin-main">
    <div class="admin-topbar"><h5><i class="bi bi-people me-2" style="color:var(--accent)"></i>Gestion des Utilisateurs</h5></div>
    <div class="admin-content">
        <div class="admin-card card">
            <div class="card-header"><i class="bi bi-person-badge me-2" style="color:var(--accent)"></i>Liste des Utilisateurs</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead><tr><th>#</th><th>Nom</th><th>Email</th><th>Rôle</th><th class="text-center">Actions</th></tr></thead>
                        <tbody>
                            <tr>
                                <td>1</td><td class="fw-medium">Admin</td><td>admin@smartmealplanner.tn</td>
                                <td><span class="badge" style="background:var(--accent)">Administrateur</span></td>
                                <td class="text-center"><button class="btn btn-sm btn-outline-secondary" disabled><i class="bi bi-pencil"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/partials/foot.php'; ?>
