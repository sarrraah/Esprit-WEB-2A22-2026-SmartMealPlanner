<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

try {
    $pdo = config::getConnexion();
    $notifications = [];

    // 1. Pending participation requests
    $pending = (int) $pdo->query("
        SELECT COUNT(*) FROM participation WHERE statut = 'en_attente'
    ")->fetchColumn();
    if ($pending > 0) {
        $notifications[] = [
            'title'       => "$pending pending participation(s)",
            'description' => 'New registrations waiting for confirmation.',
            'href'        => 'listParticipations.php',
            'created_at'  => date('Y-m-d H:i:s'),
        ];
    }

    // 2. Events at full capacity
    $fullEvents = $pdo->query("
        SELECT e.titre
        FROM evenement e
        JOIN (SELECT id_event, COUNT(*) AS cnt FROM participation GROUP BY id_event) p
          ON p.id_event = e.id_event
        WHERE e.capacite_max > 0 AND p.cnt >= e.capacite_max
        LIMIT 3
    ")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($fullEvents as $titre) {
        $notifications[] = [
            'title'       => 'Event full: ' . $titre,
            'description' => 'This event has reached maximum capacity.',
            'href'        => 'listEvenements.php',
            'created_at'  => date('Y-m-d H:i:s'),
        ];
    }

    // 3. Pending user role requests
    $pendingUsers = (int) $pdo->query("
        SELECT COUNT(*) FROM user WHERE statut = 'pending'
    ")->fetchColumn();
    if ($pendingUsers > 0) {
        $notifications[] = [
            'title'       => "$pendingUsers role request(s) pending",
            'description' => 'Users waiting for coach/nutritionist approval.',
            'href'        => 'pending_requests.php',
            'created_at'  => date('Y-m-d H:i:s'),
        ];
    }

    // 4. Events starting today
    $todayEvents = $pdo->query("
        SELECT titre FROM evenement
        WHERE DATE(date_debut) = CURDATE()
        LIMIT 3
    ")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($todayEvents as $titre) {
        $notifications[] = [
            'title'       => 'Event today: ' . $titre,
            'description' => 'This event starts today.',
            'href'        => 'listEvenements.php',
            'created_at'  => date('Y-m-d H:i:s'),
        ];
    }

    echo json_encode(['notifications' => $notifications]);

} catch (Throwable $e) {
    echo json_encode(['notifications' => []]);
}
