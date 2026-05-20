<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

try {
    $pdo = config::getConnexion();

    // 1. Revenue by event type
    $revenueByType = $pdo->query("
        SELECT e.type,
               COUNT(DISTINCT e.id_event)          AS nb_events,
               COALESCE(SUM(p.montant), 0)          AS revenue
        FROM evenement e
        LEFT JOIN participation p ON p.id_event = e.id_event
        GROUP BY e.type
        ORDER BY revenue DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // If montant is null in participation, fall back to prix * places
    if (array_sum(array_column($revenueByType, 'revenue')) == 0) {
        $revenueByType = $pdo->query("
            SELECT e.type,
                   COUNT(DISTINCT e.id_event) AS nb_events,
                   COALESCE(SUM(e.prix * COALESCE(p.nombre_places_reservees, 0)), 0) AS revenue
            FROM evenement e
            LEFT JOIN participation p ON p.id_event = e.id_event
            GROUP BY e.type
            ORDER BY revenue DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Participations per event (top 8)
    $participationsPerEvent = $pdo->query("
        SELECT e.titre,
               COUNT(p.id_participation) AS participations
        FROM evenement e
        LEFT JOIN participation p ON p.id_event = e.id_event
        GROUP BY e.id_event, e.titre
        ORDER BY participations DESC
        LIMIT 8
    ")->fetchAll(PDO::FETCH_ASSOC);

    // 3. Participations by month (last 12 months)
    $participationsByMonth = $pdo->query("
        SELECT DATE_FORMAT(date_participation, '%b %Y') AS month,
               YEAR(date_participation)  AS yr,
               MONTH(date_participation) AS mo,
               COUNT(*)                  AS participations
        FROM participation
        WHERE date_participation >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY yr, mo
        ORDER BY yr ASC, mo ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // If no participation dates, generate last 6 months with 0
    if (empty($participationsByMonth)) {
        for ($i = 5; $i >= 0; $i--) {
            $participationsByMonth[] = [
                'month'          => date('M Y', strtotime("-$i months")),
                'participations' => 0,
            ];
        }
    }

    // 4. Totals
    $totalEvents        = (int) $pdo->query("SELECT COUNT(*) FROM evenement")->fetchColumn();
    $totalParticipations= (int) $pdo->query("SELECT COUNT(*) FROM participation")->fetchColumn();

    // Total revenue
    $totalRevenue = (float) $pdo->query("
        SELECT COALESCE(SUM(e.prix * COALESCE(p.nombre_places_reservees, 1)), 0)
        FROM participation p
        JOIN evenement e ON e.id_event = p.id_event
    ")->fetchColumn();

    // Average occupancy %
    $avgOccupancy = 0;
    $occRow = $pdo->query("
        SELECT AVG(occ) AS avg_occ FROM (
            SELECT
                CASE WHEN e.capacite_max > 0
                     THEN (COUNT(p.id_participation) / e.capacite_max) * 100
                     ELSE 0 END AS occ
            FROM evenement e
            LEFT JOIN participation p ON p.id_event = e.id_event
            GROUP BY e.id_event
        ) sub
    ")->fetch(PDO::FETCH_ASSOC);
    if ($occRow) $avgOccupancy = round((float)$occRow['avg_occ'], 1);

    echo json_encode([
        'revenueByType'          => $revenueByType,
        'participationsPerEvent' => $participationsPerEvent,
        'participationsByMonth'  => $participationsByMonth,
        'totals' => [
            'totalEvents'         => $totalEvents,
            'totalParticipations' => $totalParticipations,
            'totalRevenue'        => round($totalRevenue, 2),
            'avgOccupancy'        => $avgOccupancy,
        ],
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
