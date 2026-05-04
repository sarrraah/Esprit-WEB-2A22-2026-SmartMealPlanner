<?php
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = config::getConnexion();

    // 1) Participations per event (bar)
    $stmt = $db->prepare("
        SELECT e.id_event, e.titre, e.capacite_max, e.prix, COUNT(p.id_participation) AS participations
        FROM evenement e
        LEFT JOIN participation p ON p.id_event = e.id_event
        GROUP BY e.id_event, e.titre, e.capacite_max, e.prix
        ORDER BY participations DESC, e.date_debut DESC
    ");
    $stmt->execute();
    $perEvent = $stmt->fetchAll();

    // 2) Revenue distribution by event type (doughnut)
    // Enforce the requested 4 types even if empty.
    $wantedTypes = ['Atelier', 'Séminaire', 'Forum', 'Challenge'];
    $revenueMap = array_fill_keys($wantedTypes, 0.0);

    $stmt = $db->prepare("
        SELECT e.type, COALESCE(SUM(e.prix * p.nombre_places_reservees), 0) AS revenue
        FROM evenement e
        LEFT JOIN participation p ON p.id_event = e.id_event
        GROUP BY e.type
    ");
    $stmt->execute();
    foreach ($stmt->fetchAll() as $row) {
        $type = (string)($row['type'] ?? '');
        if (isset($revenueMap[$type])) {
            $revenueMap[$type] = (float)$row['revenue'];
        }
    }
    $revenueByType = [];
    foreach ($wantedTypes as $t) {
        $revenueByType[] = ['type' => $t, 'revenue' => (float)$revenueMap[$t]];
    }

    // 3) Participations over time (line, by month)
    $stmt = $db->prepare("
        SELECT DATE_FORMAT(date_participation, '%Y-%m') AS month, COUNT(*) AS participations
        FROM participation
        GROUP BY DATE_FORMAT(date_participation, '%Y-%m')
        ORDER BY month ASC
    ");
    $stmt->execute();
    $byMonth = $stmt->fetchAll();

    // Totals for counters
    $totalEvents = 0;
    $totalParticipations = 0;
    $totalCapacity = 0;
    $totalRevenue = 0.0;
    foreach ($perEvent as $r) {
        $totalEvents++;
        $pCount = (int)$r['participations'];
        $cap = (int)($r['capacite_max'] ?? 0);
        $price = (float)($r['prix'] ?? 0);
        $totalParticipations += $pCount;
        if ($cap > 0) $totalCapacity += $cap;
        // approximate revenue: price * reserved seats (we don't have seats count here),
        // so take accurate revenue from revenueByType sum.
    }
    foreach ($revenueByType as $rt) $totalRevenue += (float)$rt['revenue'];
    $avgOccupancy = $totalCapacity > 0 ? round(($totalParticipations / $totalCapacity) * 100, 1) : 0.0;

    echo json_encode([
        'success' => true,
        'participationsPerEvent' => array_map(function ($r) {
            return [
                'id_event' => (int)$r['id_event'],
                'titre' => (string)$r['titre'],
                'participations' => (int)$r['participations'],
                'capacite_max' => (int)($r['capacite_max'] ?? 0),
            ];
        }, $perEvent),
        'revenueByType' => array_map(function ($r) {
            return [
                'type' => (string)$r['type'],
                'revenue' => (float)$r['revenue'],
            ];
        }, $revenueByType),
        'participationsByMonth' => array_map(function ($r) {
            return [
                'month' => (string)$r['month'],
                'participations' => (int)$r['participations'],
            ];
        }, $byMonth),
        'totals' => [
            'totalEvents' => (int)$totalEvents,
            'totalParticipations' => (int)$totalParticipations,
            'totalRevenue' => (float)$totalRevenue,
            'avgOccupancy' => (float)$avgOccupancy,
        ],
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error',
    ]);
}


