<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/AvisController.php';
require_once __DIR__ . '/../../model/Avis.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$note        = (int)($_POST['note'] ?? 0);
$commentaire = trim($_POST['commentaire'] ?? '');
$id_produit  = (int)($_POST['id_produit'] ?? 0);

if ($note < 1 || $note > 5 || empty($commentaire) || $id_produit <= 0) {
    echo json_encode(['success' => false, 'error' => 'Données invalides']);
    exit;
}

$avisController = new AvisController();
$avis = new Avis(null, $note, $commentaire, date('Y-m-d'), $id_produit);

if ($avisController->addAvis($avis)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Erreur base de données']);
}
?>
