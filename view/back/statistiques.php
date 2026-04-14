<?php
defined('APP_ROOT') || require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Repas.php';
require_once __DIR__ . '/../../model/Recette.php';

$repasModel    = new Repas();
$totalRepas    = $repasModel->countRepas();
$totalRecettes = $repasModel->countRecettes();
$totalCal      = round($repasModel->totalCalories(), 1);
$avgCal        = round($repasModel->avgCalories(), 1);
$allRepas      = $repasModel->getAllRepas();
$statsByType   = $repasModel->statsByType();

$chartLabels = json_encode(array_map(fn($r) => $r['nom'], $allRepas));
$chartCal    = json_encode(array_map(fn($r) => (float)($r['calories'] ?? 0), $allRepas));
$typeLabels  = json_encode(array_column($statsByType, 'type_repas'));
$typeData    = json_encode(array_column($statsByType, 'total'));

$pageTitle = 'Statistiques - SmartMeal Admin';
require_once __DIR__ . '/partials/head.php';
require_once __DIR__ . '/partials/sidebar.php';
?>
<div class="admin-main">
    <div class="admin-topbar">
        <h5><i class="bi bi-bar-chart-line me-2" style="color:var(--accent)"></i>Statistiques</h5>
    </div>
    <div class="admin-content">
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="icon-box" style="background:#fde8e8;color:#ce1212;"><i class="bi bi-bowl-hot"></i></div>
                    <div><div class="text-muted small">Total Repas</div><div class="stat-value"><?= $totalRepas ?></div></div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="icon-box" style="background:#e8f0fe;color:#0d6efd;"><i class="bi bi-journal-richtext"></i></div>
                    <div><div class="text-muted small">Total Recettes</div><div class="stat-value"><?= $totalRecettes ?></div></div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="icon-box" style="background:#fff3cd;color:#fd7e14;"><i class="bi bi-fire"></i></div>
                    <div><div class="text-muted small">Calories Totales</div><div class="stat-value"><?= $totalCal ?></div></div>
                </div></div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="icon-box" style="background:#d1f2eb;color:#198754;"><i class="bi bi-graph-up"></i></div>
                    <div><div class="text-muted small">Moy. Cal/Repas</div><div class="stat-value"><?= $avgCal ?></div></div>
                </div></div>
            </div>
        </div>
        <div class="row g-4">
            <?php if (!empty($allRepas)): ?>
            <div class="col-lg-8">
                <div class="admin-card card">
                    <div class="card-header"><i class="bi bi-bar-chart me-2" style="color:var(--accent)"></i>Calories par Repas</div>
                    <div class="card-body"><canvas id="calChart" height="100"></canvas></div>
                </div>
            </div>
            <?php endif; ?>
            <?php if (!empty($statsByType)): ?>
            <div class="col-lg-4">
                <div class="admin-card card">
                    <div class="card-header"><i class="bi bi-pie-chart me-2" style="color:var(--accent)"></i>Répartition par Type</div>
                    <div class="card-body"><canvas id="typeChart"></canvas></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
$extraJs = <<<JS
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('calChart'),{type:'bar',data:{labels:{$chartLabels},datasets:[{label:'Calories (kcal)',data:{$chartCal},backgroundColor:'rgba(206,18,18,0.7)',borderColor:'#ce1212',borderWidth:1,borderRadius:8}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
new Chart(document.getElementById('typeChart'),{type:'doughnut',data:{labels:{$typeLabels},datasets:[{data:{$typeData},backgroundColor:['#ce1212','#0d6efd','#198754','#fd7e14','#6f42c1']}]},options:{responsive:true}});
</script>
JS;
require_once __DIR__ . '/partials/foot.php';
?>
