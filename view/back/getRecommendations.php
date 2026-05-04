<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../model/Database.php';

$id_event = (int)($_GET['id_event'] ?? 0);
$type     = trim($_GET['type']      ?? '');
$limit    = min((int)($_GET['limit'] ?? 3), 6);

if (!$id_event || !$type) {
    echo json_encode([]);
    exit;
}

try {
    $pdo = Database::getConnection();

    // Get recommended events: same type, active, best rated, excluding current
    $stmt = $pdo->prepare(
        "SELECT e.id_event, e.titre, e.type, e.lieu, e.date_debut,
                e.prix, e.statut, e.image, e.capacite_max,
                COALESCE(ROUND(AVG(r.stars), 1), 0) AS avg_stars,
                COALESCE(COUNT(r.id), 0) AS rating_count
         FROM evenement e
         LEFT JOIN rating r ON r.id_event = e.id_event
         WHERE e.type = ?
           AND e.id_event != ?
           AND LOWER(e.statut) LIKE '%actif%'
         GROUP BY e.id_event
         ORDER BY avg_stars DESC, e.date_debut ASC
         LIMIT ?"
    );
    $stmt->execute([$type, $id_event, $limit]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If not enough same-type events, fill with other active events
    if (count($rows) < $limit) {
        $existing = array_column($rows, 'id_event');
        $existing[] = $id_event;
        $placeholders = implode(',', array_fill(0, count($existing), '?'));
        $remaining = $limit - count($rows);

        $stmt2 = $pdo->prepare(
            "SELECT e.id_event, e.titre, e.type, e.lieu, e.date_debut,
                    e.prix, e.statut, e.image, e.capacite_max,
                    COALESCE(ROUND(AVG(r.stars), 1), 0) AS avg_stars,
                    COALESCE(COUNT(r.id), 0) AS rating_count
             FROM evenement e
             LEFT JOIN rating r ON r.id_event = e.id_event
             WHERE e.id_event NOT IN ($placeholders)
               AND LOWER(e.statut) LIKE '%actif%'
             GROUP BY e.id_event
             ORDER BY avg_stars DESC, e.date_debut ASC
             LIMIT ?"
        );
        $params = array_merge($existing, [$remaining]);
        $stmt2->execute($params);
        $rows = array_merge($rows, $stmt2->fetchAll(PDO::FETCH_ASSOC));
    }

    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode([]);
}
