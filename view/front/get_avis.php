<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/AvisController.php';

header('Content-Type: application/json');

$id_produit = (int)($_GET['id_produit'] ?? 0);
if ($id_produit <= 0) {
    echo json_encode(['avis' => [], 'avg' => 0, 'total' => 0]);
    exit;
}

$avisController = new AvisController();
$avis    = $avisController->getAvisByProduit($id_produit);
$avgData = $avisController->getAverageNote($id_produit);

// Format dates
foreach ($avis as &$a) {
    $a['date_avis'] = date('d/m/Y', strtotime($a['date_avis']));
}
unset($a);

echo json_encode([
    'avis'  => $avis,
    'avg'   => $avgData['avg_note'] ? round((float)$avgData['avg_note'], 1) : 0,
    'total' => (int)$avgData['total'],
]);
?>
