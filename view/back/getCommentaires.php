<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../model/Database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Aucune donnée reçue']);
    exit;
}

$id_event = (int)($data['id_event'] ?? 0);
$auteur   = trim($data['auteur']  ?? '');
$contenu  = trim($data['contenu'] ?? '');

if (!$id_event || empty($auteur) || strlen($contenu) < 2) {
    echo json_encode(['success' => false, 'error' => 'Champs invalides']);
    exit;
}

try {
    $pdo  = Database::getConnection();
    $stmt = $pdo->prepare(
        "INSERT INTO commentaire_event (id_event, auteur, contenu, created_at) VALUES (?, ?, ?, NOW())"
    );
    $stmt->execute([$id_event, $auteur, $contenu]);
    $id = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'comment' => [
            'id'         => $id,
            'auteur'     => $auteur,
            'contenu'    => $contenu,
            'created_at' => date('Y-m-d H:i:s'),
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
