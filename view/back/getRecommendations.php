<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

$id = (int)($_GET['id_event'] ?? 0);

try {
    $pdo = config::getConnexion();

    // Build absolute image URL base dynamically
    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $docRoot  = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
    $projRoot = str_replace('\\', '/', realpath(__DIR__ . '/../../'));
    $basePath = str_replace($docRoot, '', $projRoot);
    $imgBase  = $scheme . '://' . $_SERVER['HTTP_HOST'] . $basePath . '/view/assets/img/events/';

    $stmt = $pdo->prepare("
        SELECT id_event, titre, type, date_debut, lieu, prix, image
        FROM evenement
        WHERE id_event != :id
        ORDER BY RAND()
        LIMIT 3
    ");
    $stmt->execute([':id' => $id ?: 0]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($rows as $r) {
        $img = ($r['image'] && trim($r['image']) !== '')
            ? $imgBase . $r['image']
            : null;

        // Format date properly
        $dateFormatted = '';
        if ($r['date_debut']) {
            $ts = strtotime($r['date_debut']);
            $dateFormatted = $ts ? date('d/m/Y', $ts) : '';
        }

        $result[] = [
            'id'    => (int) $r['id_event'],
            'titre' => $r['titre'],
            'type'  => $r['type'],
            'date'  => $dateFormatted,
            'lieu'  => $r['lieu'],
            'prix'  => (float) $r['prix'],
            'image' => $img,
        ];
    }

    echo json_encode($result);

} catch (Throwable $e) {
    echo json_encode([]);
}
